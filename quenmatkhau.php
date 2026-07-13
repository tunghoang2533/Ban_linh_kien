<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\PasswordResetModel;
use App\Helpers\EmailHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\RateLimiter;

$error = '';
$success = '';
$step = isset($_GET['token']) ? 'reset' : (isset($_GET['done']) ? 'done' : 'request');
$token = $_GET['token'] ?? '';

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { CsrfHelper::verify(); } catch (Exception $e) { $error = $e->getMessage(); }

    if (!$error) {
        $model = new PasswordResetModel($db);

        if (isset($_POST['request'])) {
            $rlKey = RateLimiter::ipKey('password_reset');
            try { RateLimiter::check($rlKey); } catch (Exception $e) { $error = $e->getMessage(); }
            if (!$error) {
                $email = trim($_POST['email'] ?? '');
                $user = $model->getUserByEmail($email);
                if ($user) {
                    $token = $model->createToken($email);
                    $resetLink = BASE_URL . 'quenmatkhau.php?token=' . urlencode($token);
                    EmailHelper::passwordReset($email, $user['full_name'] ?? 'User', $resetLink);
                    $success = 'Link đặt lại mật khẩu đã được gửi đến email của bạn.';
                } else {
                    $error = 'Email không tồn tại trong hệ thống.';
                }
                RateLimiter::record($rlKey);
            }
        } elseif (isset($_POST['reset']) && $token) {
            $password = trim($_POST['password'] ?? '');
            $confirm = trim($_POST['confirm_password'] ?? '');
            if (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
            } elseif ($password !== $confirm) {
                $error = 'Mật khẩu xác nhận không khớp.';
            } else {
                $record = $model->validateToken($token);
                if ($record) {
                    $model->resetPassword($record['user_id'], $password);
                    $model->markUsed($token);
                    header('Location: quenmatkhau.php?done=1');
                    exit;
                } else {
                    $error = 'Token không hợp lệ hoặc đã hết hạn.';
                }
            }
        }
    }
}

include 'app/views/header.php';
?>
<div style="max-width:480px;margin:60px auto;padding:0 20px;">
    <div style="background:#fff;border-radius:20px;box-shadow:0 8px 30px rgba(0,0,0,.07);padding:36px;">
        <?php if ($step === 'done'): ?>
            <div style="text-align:center;">
                <p style="font-size:48px;margin:0 0 12px;">✅</p>
                <h2 style="margin:0 0 8px;">Đặt lại mật khẩu thành công!</h2>
                <p style="color:#64748b;margin:0 0 20px;">Bạn có thể đăng nhập bằng mật khẩu mới.</p>
                <a href="taikhoan.php" style="display:inline-block;padding:12px 28px;background:#2563eb;color:#fff;border-radius:10px;text-decoration:none;font-weight:700;">Đăng nhập</a>
            </div>
        <?php elseif ($step === 'reset'): ?>
            <h2 style="margin:0 0 20px;"><i class="fa fa-key"></i> Đặt lại mật khẩu</h2>
            <?php if ($error): ?><div style="background:#fef2f2;color:#dc2626;padding:12px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <?php echo CsrfHelper::field(); ?>
                <div style="margin-bottom:12px;">
                    <label style="font-size:13px;font-weight:600;color:#475569;">Mật khẩu mới</label>
                    <input type="password" name="password" required minlength="6" style="width:100%;padding:12px;border:1px solid #e2e8f0;border-radius:10px;margin-top:4px;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="font-size:13px;font-weight:600;color:#475569;">Xác nhận mật khẩu</label>
                    <input type="password" name="confirm_password" required minlength="6" style="width:100%;padding:12px;border:1px solid #e2e8f0;border-radius:10px;margin-top:4px;">
                </div>
                <button type="submit" name="reset" style="width:100%;padding:12px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
                    <i class="fa fa-save"></i> Đặt lại mật khẩu
                </button>
            </form>
        <?php else: ?>
            <h2 style="margin:0 0 8px;"><i class="fa fa-lock"></i> Quên mật khẩu</h2>
            <p style="color:#64748b;margin:0 0 24px;">Nhập email của bạn để nhận link đặt lại mật khẩu.</p>
            <?php if ($error): ?><div style="background:#fef2f2;color:#dc2626;padding:12px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div style="background:#f0fdf4;color:#16a34a;padding:12px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <form method="POST">
                <?php echo CsrfHelper::field(); ?>
                <div style="margin-bottom:20px;">
                    <label style="font-size:13px;font-weight:600;color:#475569;">Email</label>
                    <input type="email" name="email" required style="width:100%;padding:12px;border:1px solid #e2e8f0;border-radius:10px;margin-top:4px;">
                </div>
                <button type="submit" name="request" style="width:100%;padding:12px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
                    <i class="fa fa-paper-plane"></i> Gửi yêu cầu
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'app/views/footer.php'; ?>
