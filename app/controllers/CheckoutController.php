<?php
namespace App\Controllers;

use App\Helpers\CsrfHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\LoyaltyHelper;
use App\Helpers\VNPayHelper;
use App\Models\OrderModel;
use App\Models\VoucherModel;
use App\Models\ProductModel;

class CheckoutController {
    private $orderModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->orderModel = new OrderModel($db);
    }

    public function index() {
        // Guest checkout được phép — không bắt buộc đăng nhập
        $isGuest  = !isset($_SESSION['user']);
        $userId   = $isGuest ? null : $_SESSION['user']['id'];

        // Lấy danh sách ID sản phẩm được chọn từ giỏ hàng
        $selectedIdsRaw = trim($_GET['ids'] ?? '');
        $selectedIds = $selectedIdsRaw !== ''
            ? array_filter(array_map('intval', explode(',', $selectedIdsRaw)))
            : [];

        // Lọc giỏ hàng theo ID được chọn (nếu có), ngược lại dùng toàn bộ
        $fullCart = $_SESSION['cart'] ?? [];
        $cartItems = (!empty($selectedIds))
            ? array_filter($fullCart, fn($item) => in_array((int)$item['id'], $selectedIds))
            : $fullCart;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try { CsrfHelper::verify(); } catch (Exception $e) {
                $_SESSION['checkout_error'] = $e->getMessage();
                header("Location: " . BASE_URL . "thanhtoan.php" . (!empty($selectedIdsRaw) ? "?ids=$selectedIdsRaw" : ''));
                exit();
            }
            if (empty($cartItems)) {
                // Fix #8: dùng session flash thay vì alert()
                $_SESSION['checkout_error'] = 'Không có sản phẩm nào được chọn!';
                header("Location: " . BASE_URL . "giohang.php");
                exit();
            }

            // ====== KIỂM TRA TỒN KHO TRƯỚC KHI ĐẶT HÀNG ======
            $productModel = new ProductModel($this->db);
            $stockErrors  = [];
            foreach ($cartItems as $item) {
                $product = $productModel->getProductById($item['id']);
                if (!$product) {
                    $stockErrors[] = "Sản phẩm '{$item['name']}' không tồn tại hoặc đã bị xóa.";
                    continue;
                }
                if ((int)$item['quantity'] > (int)$product['quantity']) {
                    $stockErrors[] = "Sản phẩm '{$item['name']}' chỉ còn {$product['quantity']} cái trong kho, nhưng bạn đặt {$item['quantity']} cái.";
                }
            }
            if (!empty($stockErrors)) {
                // Fix #8: dùng session flash thay vì alert()
                $_SESSION['checkout_error'] = 'Không thể đặt hàng:<br>• ' . implode('<br>• ', $stockErrors);
                header("Location: " . BASE_URL . "giohang.php");
                exit();
            }
            // ================================================

            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['price'] * $item['quantity'];
            }

            // Đọc phí vận chuyển từ form (mặc định 50.000đ)
            $shippingFee = max(0, floatval($_POST['ship_fee'] ?? 50000));

            // Đọc phương thức thanh toán (thêm vnpay)
            $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'bank', 'vnpay'])
                ? $_POST['payment_method']
                : 'cod';

            // Xác thực voucher server-side (chỉ áp dụng khi đã login)
            $voucherCode    = null;
            $discountAmount = 0;

            $rawCode = trim($_POST['voucher_code'] ?? '');
            if ($rawCode !== '' && !$isGuest) {
                $voucherModel = new VoucherModel($this->db);
                $result = $voucherModel->validate($rawCode, $userId, $cartTotal);

                if (!$result['ok']) {
                    $_SESSION['checkout_error'] = 'Voucher không hợp lệ: ' . $result['msg'];
                    header("Location: " . BASE_URL . "thanhtoan.php" . (!empty($selectedIdsRaw) ? "?ids=$selectedIdsRaw" : ''));
                    exit();
                }

                $voucherCode    = $result['voucher']['code'];
                $discountAmount = $result['discount'];
            }

            $orderId = $this->orderModel->createOrder(
                $userId,                // null nếu là guest
                $_POST['fullname'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['address'],
                $cartTotal,
                $cartItems,
                $voucherCode,
                $discountAmount,
                $shippingFee,
                $paymentMethod
            );

            if ($orderId) {
                // Đánh dấu voucher đã dùng (chỉ khi login)
                if ($voucherCode && !$isGuest) {
                    $voucherModel = new VoucherModel($this->db);
                    $voucher = $voucherModel->getByCode($voucherCode);
                    if ($voucher) {
                        $voucherModel->markUsed($voucher['id'], $userId, $orderId);
                    }
                }

                // ── VNPay: redirect ngay sang trang thanh toán ──
                if ($paymentMethod === 'vnpay') {
                    $finalTotal = (int)($cartTotal + $shippingFee - $discountAmount);
                    $orderInfo  = 'Thanh toan don hang ' . $orderId;
                    $payUrl     = VNPayHelper::createPaymentUrl($orderId, $finalTotal, $orderInfo, VNPayHelper::getClientIp());
                    // Xoá giỏ hàng trước khi redirect
                    if (!empty($selectedIds)) {
                        foreach ($selectedIds as $sid) { unset($_SESSION['cart'][$sid]); }
                    } else {
                        unset($_SESSION['cart']);
                    }
                    header('Location: ' . $payUrl);
                    exit();
                }

                // Chỉ xóa các sản phẩm được chọn khỏi giỏ hàng
                if (!empty($selectedIds)) {
                    foreach ($selectedIds as $sid) {
                        unset($_SESSION['cart'][$sid]);
                    }
                } else {
                    unset($_SESSION['cart']);
                }
                unset($_SESSION['applied_voucher']);

                // === GỬI THÔNG BÁO ĐẶT HÀNG THÀNH CÔNG ===
                NotificationHelper::orderPlaced(
                    $this->db,
                    $_SESSION['user']['id'],
                    $orderId,
                    BASE_URL
                );

                // === INSERT EMAIL XÁC NHẬN VÀO QUEUE ===
                $this->queueOrderConfirmEmail(
                    $orderId,
                    $_POST['email'],
                    $_POST['fullname'],
                    $cartItems,
                    $cartTotal + $shippingFee - $discountAmount,
                    $shippingFee,
                    $discountAmount,
                    $voucherCode,
                    $paymentMethod
                );

                // === TÍCH ĐIỂM LOYALTY ===
                try {
                    $earnTotal = $cartTotal + $shippingFee - $discountAmount;
                    $earned    = LoyaltyHelper::earnPoints($this->db, $_SESSION['user']['id'], $orderId, $earnTotal);
                    if ($earned > 0) {
                        $_SESSION['order_earned_points'] = $earned;
                    }
                } catch (Exception $e) {
                    error_log('Loyalty earnPoints failed: ' . $e->getMessage());
                }

                // Fix #8: trang thành công thân thiện (không dùng inline echo thô)
                $_SESSION['order_success'] = [
                    'order_id'       => $orderId,
                    'final_total'    => $cartTotal + $shippingFee - $discountAmount,
                    'voucher_code'   => $voucherCode,
                    'discount'       => $discountAmount,
                    'payment_method' => $paymentMethod,
                ];
                header("Location: " . BASE_URL . "thanhtoan_success.php");
                exit();
            } else {
                // createOrder trả về false (rollback)
                $_SESSION['checkout_error'] = 'Có lỗi xảy ra khi xử lý đơn hàng. Vui lòng thử lại hoặc liên hệ hỗ trợ.';
                header("Location: " . BASE_URL . "thanhtoan.php" . (!empty($selectedIdsRaw) ? "?ids=$selectedIdsRaw" : ''));
                exit();
            }
        }

        // Truyền voucher đã áp dụng (từ session nếu có) sang view
        $appliedVoucher = $_SESSION['applied_voucher'] ?? null;

        include 'app/views/header.php';
        include 'app/views/cart/checkout_view.php';
        include 'app/views/footer.php';
    }

    /**
     * Fix #4: Hiển thị trang yêu cầu đăng nhập thân thiện thay vì redirect thẳng
     */
    private function renderLoginPrompt() {
        $returnUrl = BASE_URL . 'thanhtoan.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        ?>
        <style>
        .login-prompt-wrap {
            min-height: calc(100vh - 220px);
            display: flex; align-items: center; justify-content: center;
            padding: 40px 16px;
            background: #f4f7fb;
        }
        .login-prompt-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 22px 70px rgba(28,72,122,.12);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            text-align: center;
        }
        .login-prompt-header {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            padding: 40px 32px 36px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .login-prompt-header::before {
            content: ''; position: absolute; width: 180px; height: 180px;
            background: rgba(255,255,255,.1); border-radius: 50%;
            top: -60px; right: -60px;
        }
        .login-prompt-header .prompt-icon { font-size: 52px; display: block; margin-bottom: 14px; position: relative; z-index: 1; }
        .login-prompt-header h1 { margin: 0 0 8px; font-size: 24px; font-weight: 800; position: relative; z-index: 1; }
        .login-prompt-header p { margin: 0; font-size: 14px; opacity: .85; line-height: 1.6; position: relative; z-index: 1; }
        .login-prompt-body { padding: 32px 28px 36px; }
        .prompt-feature { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #f8fafc; border-radius: 12px; margin-bottom: 10px; text-align: left; font-size: 13.5px; color: #475569; }
        .prompt-feature i { color: #2563eb; font-size: 16px; flex-shrink: 0; }
        .prompt-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 22px; }
        .btn-login-prompt {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 14px 20px;
            border-radius: 14px; font-size: 15px; font-weight: 700;
            text-decoration: none; transition: transform .18s, box-shadow .18s;
        }
        .btn-login-prompt.primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff; box-shadow: 0 8px 24px rgba(37,99,235,.25);
        }
        .btn-login-prompt.primary:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(37,99,235,.32); }
        .btn-login-prompt.secondary {
            background: #f0f7ff; color: #2563eb;
            border: 2px solid #bfdbfe;
        }
        .btn-login-prompt.secondary:hover { background: #e0eefe; transform: translateY(-1px); }
        .btn-login-prompt.ghost { background: transparent; color: #64748b; border: 1.5px solid #e2e8f0; }
        .btn-login-prompt.ghost:hover { background: #f8fafc; }
        </style>

        <div class="login-prompt-wrap">
            <div class="login-prompt-card">
                <div class="login-prompt-header">
                    <span class="prompt-icon">🛒</span>
                    <h1>Đăng nhập để thanh toán</h1>
                    <p>Bạn cần đăng nhập để hoàn tất đặt hàng và theo dõi đơn hàng của mình</p>
                </div>
                <div class="login-prompt-body">
                    <div class="prompt-feature"><i class="fa fa-check-circle"></i> Theo dõi trạng thái đơn hàng realtime</div>
                    <div class="prompt-feature"><i class="fa fa-history"></i> Xem lại lịch sử mua hàng</div>
                    <div class="prompt-feature"><i class="fa fa-tag"></i> Sử dụng voucher và ưu đãi độc quyền</div>
                    <div class="prompt-feature"><i class="fa fa-shield"></i> Bảo mật thông tin cá nhân</div>

                    <div class="prompt-actions">
                        <a href="<?php echo BASE_URL; ?>taikhoan.php?redirect=<?php echo urlencode($returnUrl); ?>" class="btn-login-prompt primary">
                            <i class="fa fa-sign-in"></i> Đăng nhập ngay
                        </a>
                        <a href="<?php echo BASE_URL; ?>taikhoan.php?tab=register&redirect=<?php echo urlencode($returnUrl); ?>" class="btn-login-prompt secondary">
                            <i class="fa fa-user-plus"></i> Tạo tài khoản miễn phí
                        </a>
                        <a href="<?php echo BASE_URL; ?>giohang.php" class="btn-login-prompt ghost">
                            <i class="fa fa-arrow-left"></i> Quay lại giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    /**
     * Insert email xác nhận đơn hàng vào queue để worker gửi sau
     */
    private function queueOrderConfirmEmail($orderId, $toEmail, $toName, $cartItems, $total, $shippingFee, $discount, $voucherCode, $paymentMethod) {
        if (empty($toEmail)) return;

        $orderCode  = 'ORD' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
        $trackingUrl = BASE_URL . 'tracking.php';
        $payLabel   = strtoupper($paymentMethod ?: 'COD');
        $totalFmt   = number_format($total, 0, ',', '.') . '₫';

        // Build bảng sản phẩm HTML
        $itemsHtml = '';
        foreach ($cartItems as $item) {
            $lineTotal = number_format($item['price'] * $item['quantity'], 0, ',', '.');
            $price     = number_format($item['price'], 0, ',', '.');
            $itemsHtml .= "
            <tr>
                <td style='padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:14px;color:#1e293b;font-weight:600;'>" . htmlspecialchars($item['name'] ?? 'Sản phẩm') . "</td>
                <td style='padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:14px;text-align:center;color:#6366f1;font-weight:700;'>" . $item['quantity'] . "</td>
                <td style='padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:14px;text-align:right;color:#475569;'>{$price}₫</td>
                <td style='padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:14px;text-align:right;color:#1e293b;font-weight:700;'>{$lineTotal}₫</td>
            </tr>";
        }

        $voucherRow = '';
        if ($voucherCode && $discount > 0) {
            $discFmt = '-' . number_format($discount, 0, ',', '.') . '₫';
            $voucherRow = "<tr>
                <td colspan='3' style='padding:8px 16px;text-align:right;font-size:13px;color:#64748b;'>Giảm giá ({$voucherCode}):</td>
                <td style='padding:8px 16px;text-align:right;font-size:13px;color:#dc2626;font-weight:700;'>{$discFmt}</td>
            </tr>";
        }
        $shipRow = '';
        if ($shippingFee > 0) {
            $shipFmt = '+' . number_format($shippingFee, 0, ',', '.') . '₫';
            $shipRow = "<tr>
                <td colspan='3' style='padding:8px 16px;text-align:right;font-size:13px;color:#64748b;'>Phí vận chuyển:</td>
                <td style='padding:8px 16px;text-align:right;font-size:13px;color:#475569;font-weight:600;'>{$shipFmt}</td>
            </tr>";
        }

        $subject = "✅ Xác nhận đơn hàng #{$orderId} - Ban Linh Kiện";

        $body = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Inter',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:40px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:white;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#1e293b,#334155);padding:36px 40px;text-align:center;">
      <p style="margin:0 0 4px;font-size:28px;font-weight:900;color:white;letter-spacing:-1px;">Ban <span style="color:#6366f1;">Linh Kiện</span></p>
      <p style="margin:0;font-size:13px;color:rgba(255,255,255,.6);">Linh kiện máy tính chính hãng</p>
    </td>
  </tr>

  <!-- Success Banner -->
  <tr>
    <td style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);padding:28px 40px;text-align:center;border-bottom:2px solid #86efac;">
      <p style="margin:0 0 8px;font-size:40px;">🎉</p>
      <p style="margin:0 0 4px;font-size:22px;font-weight:800;color:#065f46;">Đặt hàng thành công!</p>
      <p style="margin:0;font-size:14px;color:#16a34a;">Cảm ơn <strong>{$toName}</strong> đã tin tưởng mua hàng tại Ban Linh Kiện</p>
    </td>
  </tr>

  <!-- Order Info -->
  <tr>
    <td style="padding:28px 40px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
        <tr>
          <td style="padding:16px 20px;border-bottom:1px solid #e2e8f0;">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Mã đơn hàng</span><br>
            <span style="font-size:20px;font-weight:800;color:#6366f1;">#{$orderId}</span>
          </td>
          <td style="padding:16px 20px;border-bottom:1px solid #e2e8f0;border-left:1px solid #e2e8f0;">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Mã tra cứu</span><br>
            <span style="font-size:16px;font-weight:700;color:#1e293b;">{$orderCode}</span>
          </td>
          <td style="padding:16px 20px;border-bottom:1px solid #e2e8f0;border-left:1px solid #e2e8f0;">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Thanh toán</span><br>
            <span style="font-size:14px;font-weight:700;color:#1e293b;">{$payLabel}</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Products Table -->
  <tr>
    <td style="padding:0 40px 28px;">
      <p style="margin:0 0 14px;font-size:15px;font-weight:700;color:#1e293b;"><i>📦</i> Sản phẩm đặt mua</p>
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <thead>
          <tr style="background:#f8fafc;">
            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;">Sản phẩm</th>
            <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;">SL</th>
            <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;">Đơn giá</th>
            <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;">Thành tiền</th>
          </tr>
        </thead>
        <tbody>{$itemsHtml}</tbody>
        <tfoot>
          {$voucherRow}
          {$shipRow}
          <tr style="background:#f0fdf4;">
            <td colspan="3" style="padding:14px 16px;text-align:right;font-size:15px;font-weight:800;color:#065f46;">TỔNG CỘNG:</td>
            <td style="padding:14px 16px;text-align:right;font-size:20px;font-weight:900;color:#16a34a;">{$totalFmt}</td>
          </tr>
        </tfoot>
      </table>
    </td>
  </tr>

  <!-- Tracking CTA -->
  <tr>
    <td style="padding:0 40px 36px;text-align:center;">
      <p style="margin:0 0 16px;font-size:14px;color:#64748b;">Theo dõi trạng thái đơn hàng của bạn bằng cách nhấn nút bên dưới:</p>
      <a href="{$trackingUrl}"
         style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border-radius:12px;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 16px rgba(99,102,241,.35);">
        🔍 Theo dõi đơn hàng
      </a>
      <p style="margin:12px 0 0;font-size:12px;color:#94a3b8;">Mã tra cứu: <strong>{$orderCode}</strong></p>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#f8fafc;padding:24px 40px;border-top:1px solid #e2e8f0;text-align:center;">
      <p style="margin:0 0 8px;font-size:13px;color:#64748b;">Liên hệ hỗ trợ: <a href="mailto:contact@banlinh.vn" style="color:#6366f1;">contact@banlinh.vn</a> · Hotline: 0909 000 000</p>
      <p style="margin:0;font-size:12px;color:#94a3b8;">© 2026 Ban Linh Kiện. Đây là email tự động, vui lòng không reply.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO email_queue (to_email, to_name, subject, body, status, scheduled_at)
                VALUES (:email, :name, :subject, :body, 'pending', NOW())
            ");
            $stmt->execute([
                ':email'   => $toEmail,
                ':name'    => $toName,
                ':subject' => $subject,
                ':body'    => $body,
            ]);
        } catch (Exception $e) {
            // Lỗi insert email queue không nên làm hỏng quá trình đặt hàng
            error_log('Email queue insert failed: ' . $e->getMessage());
        }
    }
}
?>