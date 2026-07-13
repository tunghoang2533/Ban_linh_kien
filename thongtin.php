<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Helpers\CsrfHelper;

if (!isset($_SESSION['user'])) {
    header('Location: taikhoan.php');
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user']['id'];
$error = '';
$success = '';

$stmt = $db->prepare("SELECT fullname, full_name, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { CsrfHelper::verify(); } catch (Exception $e) { $error = $e->getMessage(); }
    if (!$error) {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($fullname) || empty($email)) {
            $error = 'Vui lòng điền đầy đủ họ tên và email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không hợp lệ.';
        } else {
            $update = $db->prepare("UPDATE users SET fullname = ?, full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            if ($update->execute([$fullname, $fullname, $email, $phone, $address, $userId])) {
                $_SESSION['user']['fullname'] = $fullname;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['address'] = $address;
                $_SESSION['full_name'] = $fullname;
                $success = 'Cập nhật thông tin thành công!';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}

include 'app/views/header.php';
include 'app/views/user/thongtin_view.php';
include 'app/views/footer.php';
