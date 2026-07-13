<?php
/**
 * Handler: Vouchers — Quản lý mã giảm giá
 */

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $admin->deleteVoucher(intval($_POST['id']));
    header('Location: ' . BASE_URL . 'admin/?page=vouchers&success=deleted');
    exit;
}
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $admin->toggleVoucherStatus(intval($_POST['id']));
    header('Location: ' . BASE_URL . 'admin/?page=vouchers');
    exit;
}
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['code'] ?? '')) || empty(trim($_POST['name'] ?? ''))) {
        $error = 'Vui lòng nhập mã voucher và tên.';
    } else {
        if ($admin->createVoucher($_POST)) {
            // Náº¿u lÃ  voucher cÃ¡ nhÃ¢n, gá»­i thÃ´ng bÃ¡o
            if (!empty($_POST['user_id'])) {
                try {
                    $personalUserId = intval($_POST['user_id']);
                    $voucherCode = strtoupper(trim($_POST['code']));
                    $userInfo = $db->prepare("SELECT full_name FROM users WHERE id=?");
                    $userInfo->execute([$personalUserId]);
                    $userName = $userInfo->fetchColumn() ?: 'NgÆ°á»i dÃ¹ng';
                    // Gá»­i thÃ´ng bÃ¡o trong app
                    NotificationHelper::send($db, $personalUserId,
                        'ðŸŽ‰ Báº¡n nháº­n Ä‘Æ°á»£c voucher Ä‘áº·c quyá»n!',
                        'MÃ£ ' . $voucherCode . ' - ' . htmlspecialchars(trim($_POST['name'])) . '. HÃ£y sá»­ dá»¥ng ngay!',
                        'promotion',
                        BASE_URL . 'giohang.php?voucher=' . $voucherCode
                    );
                    // Ghi log
                    $db->prepare("UPDATE vouchers SET sent_at=NOW() WHERE code=?")->execute([$voucherCode]);
                } catch (Exception $e) {
                    Logger::warning('Failed to update voucher sent_at', ['code' => $voucherCode ?? '', 'error' => $e->getMessage()]);
                }
            }
            header('Location: ' . BASE_URL . 'admin/?page=vouchers&success=created');
            exit;
        }
        $error = 'Không thể thêm voucher. Mã voucher có thể đã tồn tại.';
    }
}
if ($action === 'update' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid = intval($_GET['id']);
    if (empty(trim($_POST['code'] ?? '')) || empty(trim($_POST['name'] ?? ''))) {
        $error = 'Vui lòng nhập mã voucher và tên.';
        $editVoucherId = $vid;
    } elseif ($admin->updateVoucher($vid, $_POST)) {
        header('Location: ' . BASE_URL . 'admin/?page=vouchers&success=updated&edit_id=' . $vid);
        exit;
    } else {
        $error = 'Không thể cập nhật voucher.';
    }
}
if (isset($_GET['success'])) {
    $successMap = ['created' => 'Thêm voucher thành công!', 'updated' => 'Cập nhật voucher thành công!', 'deleted' => 'Xóa voucher thành công!'];
    $successMessage = $successMap[$_GET['success']] ?? '';
}

include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/vouchers/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
