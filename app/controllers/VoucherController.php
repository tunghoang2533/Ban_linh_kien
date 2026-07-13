<?php
namespace App\Controllers;

use App\Models\VoucherModel;

class VoucherController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // AJAX: POST giohang.php?action=check_voucher
    public function checkVoucher() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['ok' => false, 'msg' => 'Bạn cần đăng nhập để sử dụng voucher.']);
            exit();
        }

        $code  = trim($_POST['code'] ?? '');
        $total = floatval($_POST['total'] ?? 0);

        if (!$code) {
            echo json_encode(['ok' => false, 'msg' => 'Vui lòng nhập mã voucher.']);
            exit();
        }

        $model  = new VoucherModel($this->db);
        $result = $model->validate($code, $_SESSION['user']['id'], $total);

        if ($result['ok']) {
            $v = $result['voucher'];
            echo json_encode([
                'ok'       => true,
                'code'     => $v['code'],
                'name'     => $v['name'],
                'type'     => $v['type'],
                'discount' => $result['discount'],
                'msg'      => 'Áp dụng voucher thành công!'
            ]);
        } else {
            echo json_encode(['ok' => false, 'msg' => $result['msg']]);
        }
        exit();
    }

    // AJAX: GET giohang.php?action=list_vouchers
    public function listVouchers() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['ok' => false, 'vouchers' => []]);
            exit();
        }

        $total = floatval($_GET['total'] ?? 0);
        $model = new VoucherModel($this->db);
        $list  = $model->getAvailableForUser($_SESSION['user']['id']);

        $out = [];
        foreach ($list as $v) {
            $discount = $model->calcDiscount($v, $total);
            $out[] = [
                'code'       => $v['code'],
                'name'       => $v['name'],
                'description'=> $v['description'],
                'type'       => $v['type'],
                'value'      => (float)$v['value'],
                'max_discount'=> $v['max_discount'] ? (float)$v['max_discount'] : null,
                'min_order'  => (float)$v['min_order'],
                'expire_date'=> date('d.m.Y', strtotime($v['expire_date'])),
                'discount'   => $discount,
            ];
        }

        echo json_encode(['ok' => true, 'vouchers' => $out]);
        exit();
    }
}
?>
