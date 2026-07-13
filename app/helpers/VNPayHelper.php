<?php
namespace App\Helpers;

/**
 * VNPayHelper — Tích hợp VNPay Payment Gateway
 *
 * Cách bắt đầu:
 *  1. Đăng ký tài khoản merchant tại https://vnpay.vn
 *  2. Nhận TmnCode và HashSecret từ VNPay
 *  3. Điền vào .env:
 *       VNPAY_TMN_CODE=your_tmn_code
 *       VNPAY_HASH_SECRET=your_hash_secret
 *       VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
 *       VNPAY_RETURN_URL=http://yourdomain/Ban_linh_kien/vnpay_return.php
 *  4. Khi live, đổi VNPAY_URL sang:
 *       https://pay.vnpay.vn/vpcpay.html
 *
 * Tài liệu chính thức: https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/
 */
class VNPayHelper
{
    // ── Lấy config từ environment ──────────────────────────────
    private static function config(): array
    {
        return [
            'tmn_code'   => getenv('VNPAY_TMN_CODE')   ?: 'YOUR_TMN_CODE',
            'hash_secret'=> getenv('VNPAY_HASH_SECRET') ?: 'YOUR_HASH_SECRET',
            'url'        => getenv('VNPAY_URL')         ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'return_url' => getenv('VNPAY_RETURN_URL')  ?: (defined('BASE_URL') ? BASE_URL : '') . 'vnpay_return.php',
            'version'    => '2.1.0',
            'command'    => 'pay',
            'curr_code'  => 'VND',
            'locale'     => 'vn',
        ];
    }

    /**
     * Tạo URL thanh toán VNPay và redirect người dùng.
     *
     * @param int    $orderId      Mã đơn hàng trong DB
     * @param int    $amount       Số tiền (VND, không có đầu chấm/phẩy)
     * @param string $orderInfo    Mô tả đơn hàng (VD: "Thanh toan don hang #123")
     * @param string $ipAddr       IP của khách hàng
     * @return string              URL redirect sang trang thanh toán VNPay
     */
    public static function createPaymentUrl(int $orderId, int $amount, string $orderInfo, string $ipAddr): string
    {
        $cfg = self::config();

        $inputData = [
            'vnp_Version'    => $cfg['version'],
            'vnp_TmnCode'    => $cfg['tmn_code'],
            'vnp_Amount'     => $amount * 100,           // VNPay nhận giá trị * 100
            'vnp_Command'    => $cfg['command'],
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode'   => $cfg['curr_code'],
            'vnp_IpAddr'     => $ipAddr,
            'vnp_Locale'     => $cfg['locale'],
            'vnp_OrderInfo'  => preg_replace('/[^a-zA-Z0-9\s]/', '', $orderInfo), // VNPay chỉ chấp nhận ASCII
            'vnp_OrderType'  => '250006',                // Mã loại hàng: điện tử, linh kiện
            'vnp_ReturnUrl'  => $cfg['return_url'],
            'vnp_TxnRef'     => $orderId . '_' . time(), // Mã giao dịch duy nhất
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];

        ksort($inputData);

        $query      = http_build_query($inputData);
        $hashData   = urldecode($query); // VNPay cần raw (không encode)
        $secureHash = hash_hmac('sha512', $hashData, $cfg['hash_secret']);

        return $cfg['url'] . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    }

    /**
     * Xác thực callback từ VNPay sau khi thanh toán.
     *
     * @param array $params  $_GET hoặc $_POST từ VNPay callback
     * @return array ['valid' => bool, 'success' => bool, 'order_id' => int, 'amount' => int, 'message' => string]
     */
    public static function verifyCallback(array $params): array
    {
        $cfg = self::config();

        $vnpSecureHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        ksort($params);
        $hashData   = urldecode(http_build_query($params));
        $secureHash = hash_hmac('sha512', $hashData, $cfg['hash_secret']);

        if ($secureHash !== $vnpSecureHash) {
            return [
                'valid'    => false,
                'success'  => false,
                'order_id' => 0,
                'amount'   => 0,
                'message'  => 'Chữ ký không hợp lệ. Giao dịch có thể bị giả mạo.',
            ];
        }

        $responseCode = $params['vnp_ResponseCode'] ?? '';
        $txnRef       = $params['vnp_TxnRef']       ?? '';
        $amount       = (int)($params['vnp_Amount'] ?? 0) / 100; // Chia 100 để về VND

        // vnp_TxnRef format: "{orderId}_{timestamp}"
        $orderId = (int) explode('_', $txnRef)[0];

        $success = ($responseCode === '00');

        return [
            'valid'    => true,
            'success'  => $success,
            'order_id' => $orderId,
            'amount'   => (int) $amount,
            'txn_ref'  => $txnRef,
            'bank_code'=> $params['vnp_BankCode']       ?? '',
            'bank_txn' => $params['vnp_BankTranNo']     ?? '',
            'message'  => $success ? 'Thanh toán thành công' : self::responseCodeMessage($responseCode),
        ];
    }

    /**
     * Lấy IP của client (hỗ trợ proxy/load balancer).
     */
    public static function getClientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '127.0.0.1';
    }

    // ── Bảng mã lỗi VNPay → tiếng Việt ──────────────────────
    private static function responseCodeMessage(string $code): string
    {
        $messages = [
            '00' => 'Giao dịch thành công',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Xác thực thông tin thẻ/tài khoản không đúng quá 3 lần.',
            '11' => 'Đã hết hạn chờ thanh toán. Vui lòng thực hiện lại giao dịch.',
            '12' => 'Thẻ/Tài khoản bị khóa.',
            '13' => 'Sai mật khẩu xác thực OTP. Vui lòng thực hiện lại giao dịch.',
            '24' => 'Khách hàng hủy giao dịch.',
            '51' => 'Tài khoản không đủ số dư để thực hiện giao dịch.',
            '65' => 'Tài khoản đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Sai mật khẩu nhiều lần. Vui lòng thực hiện lại sau 24 giờ.',
            '99' => 'Lỗi không xác định. Vui lòng liên hệ hỗ trợ.',
        ];
        return $messages[$code] ?? "Giao dịch thất bại (mã lỗi: {$code})";
    }
}
