<?php
namespace App\Controllers;

use App\Helpers\CsrfHelper;
use App\Helpers\RateLimiter;
use App\Models\UserModel;

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    public function account() {
        $error     = "";
        $success   = "";
        $activeTab = 'login';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                CsrfHelper::verify();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if (!$error) {
                // ── Xử lý Đăng nhập ──────────────────────────────────
                if (isset($_POST['login'])) {
                    $rlKey = RateLimiter::ipKey('login');
                    try {
                        RateLimiter::check($rlKey);
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }

                    if (!$error) {
                        $user = $this->userModel->login($_POST['username'], $_POST['password']);
                        if ($user) {
                            if (!empty($user['is_blocked'])) {
                                $reason = !empty($user['blocked_reason'])
                                    ? $user['blocked_reason']
                                    : 'Liên hệ admin để biết thêm chi tiết.';
                                $error = "Tài khoản của bạn đã bị khoá. " . htmlspecialchars($reason);
                                RateLimiter::record($rlKey);
                            } else {
                                RateLimiter::reset($rlKey);

                                // Chống session fixation: tạo session ID mới sau login
                                session_regenerate_id(true);

                                $_SESSION['user'] = [
                                    'id'       => $user['id'],
                                    'username' => $user['username'],
                                    'fullname' => $user['fullname'] ?? $user['full_name'] ?? '',
                                    'email'    => $user['email']   ?? '',
                                    'phone'    => $user['phone']   ?? '',
                                    'address'  => $user['address'] ?? '',
                                    'avatar'   => $user['avatar']  ?? '',
                                    'is_admin' => (int)($user['is_admin'] ?? 0),
                                ];
                                $_SESSION['user_id']       = $user['id'];
                                $_SESSION['username']      = $user['username'];
                                $_SESSION['full_name']     = $user['fullname'] ?? $user['full_name'] ?? '';
                                $_SESSION['is_admin']      = (int)($user['is_admin'] ?? 0);
                                $_SESSION['last_activity'] = time();

                                if ($_SESSION['is_admin']) {
                                    header("Location: " . BASE_URL . "admin/");
                                } else {
                                    header("Location: " . BASE_URL . "index.php");
                                }
                                exit();
                            }
                        } else {
                            // Sai mật khẩu — ghi nhận attempt
                            try {
                                RateLimiter::record($rlKey);
                            } catch (Exception $e) {
                                $error = $e->getMessage();
                            }
                            if (!$error) {
                                $remaining = RateLimiter::remainingAttempts($rlKey);
                                $error = "Sai tài khoản hoặc mật khẩu!"
                                    . ($remaining > 0 ? " (còn {$remaining} lần thử)" : '');
                            }
                        }
                    } // end rate limit check
                } // end if login

                // ── Xử lý Đăng ký ────────────────────────────────────
                if (isset($_POST['register'])) {
                    $username        = trim($_POST['username']         ?? '');
                    $password        = trim($_POST['password']         ?? '');
                    $confirmPassword = trim($_POST['confirm_password'] ?? '');
                    $fullname        = trim($_POST['fullname']         ?? '');
                    $email           = trim($_POST['email']            ?? '');

                    if (strlen($username) < 3) {
                        $error = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
                    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                        $error = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới.';
                    } elseif (strlen($password) < 6) {
                        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
                    } elseif ($password !== $confirmPassword) {
                        $error     = 'Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.';
                        $activeTab = 'register';
                    } elseif (empty($fullname)) {
                        $error = 'Vui lòng nhập họ và tên.';
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = 'Địa chỉ email không hợp lệ.';
                    } else {
                        $result = $this->userModel->register($username, $password, $fullname, $email);
                        if ($result === true) {
                            $success = 'Đăng ký thành công! Hãy đăng nhập.';
                        } else {
                            $error = $result;
                        }
                    }

                    if ($error) {
                        $activeTab = 'register';
                    }
                } // end if register
            } // end if (!error CSRF)
        } // end if (POST)

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/user/taikhoan_view.php';
        include __DIR__ . '/../views/footer.php';
    }

    public function changePassword() {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'taikhoan.php');
            exit;
        }

        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfHelper::verify();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if (!$error) {
                $currentPassword = trim($_POST['current_password'] ?? '');
                $newPassword     = trim($_POST['new_password']     ?? '');
                $confirmPassword = trim($_POST['confirm_password'] ?? '');

                if (!$currentPassword || !$newPassword || !$confirmPassword) {
                    $error = 'Vui lòng điền đầy đủ các trường.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Mật khẩu mới và xác nhận không khớp.';
                } else {
                    $result = $this->userModel->changePassword(
                        $_SESSION['user']['id'],
                        $currentPassword,
                        $newPassword
                    );
                    if ($result === true) {
                        $success = 'Đổi mật khẩu thành công.';
                    } else {
                        $error = $result;
                    }
                }
            } // end if (!error)
        } // end if (POST)

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/user/doimatkhau_view.php';
        include __DIR__ . '/../views/footer.php';
    }
}