<?php
require_once 'session_check.php';
require_once 'config.php';

use App\Helpers\CsrfHelper;
use App\Helpers\RateLimiter;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { CsrfHelper::verify(); } catch (Exception $e) { $error = $e->getMessage(); }
    if (!$error) {
        $rlKey = RateLimiter::ipKey('contact');
        try { RateLimiter::check($rlKey); } catch (Exception $e) { $error = $e->getMessage(); }
        if (!$error) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $message_txt = trim($_POST['message'] ?? '');
            if ($name && $email && $message_txt) {
                // TODO: save contact message to DB or send email
                $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
                RateLimiter::record($rlKey);
            } else {
                $error = 'Vui lòng điền đầy đủ thông tin.';
            }
        }
    }
}

include 'app/views/header.php';
?>
<style>
.contact-page { max-width:720px; margin:40px auto; padding:0 20px; }
.contact-card { background:#fff; border-radius:20px; box-shadow:0 8px 30px rgba(0,0,0,.07); overflow:hidden; }
.contact-header { background:linear-gradient(135deg,#1e293b,#334155); padding:36px; text-align:center; color:#fff; }
.contact-header h1 { margin:0 0 6px; font-size:26px; }
.contact-header p { margin:0; color:rgba(255,255,255,.7); font-size:14px; }
.contact-body { padding:32px; }
.contact-info { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:28px; }
.contact-info-item { background:#f8fafc; border-radius:12px; padding:16px; text-align:center; }
.contact-info-item i { font-size:24px; color:#6366f1; margin-bottom:8px; }
.contact-info-item h4 { margin:0 0 4px; font-size:14px; color:#1e293b; }
.contact-info-item p { margin:0; font-size:13px; color:#64748b; }
</style>
<div class="contact-page">
    <div class="contact-card">
        <div class="contact-header">
            <h1><i class="fa fa-headset"></i> Liên hệ</h1>
            <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
        </div>
        <div class="contact-body">
            <div class="contact-info">
                <div class="contact-info-item"><i class="fa fa-phone"></i><h4>Hotline</h4><p>0909 000 000</p></div>
                <div class="contact-info-item"><i class="fa fa-envelope"></i><h4>Email</h4><p>contact@banlinh.vn</p></div>
                <div class="contact-info-item"><i class="fa fa-map-marker"></i><h4>Địa chỉ</h4><p>TP. Hồ Chí Minh</p></div>
                <div class="contact-info-item"><i class="fa fa-clock"></i><h4>Giờ làm việc</h4><p>8:00 - 18:00 (T2-T7)</p></div>
            </div>
            <?php if ($error): ?>
                <div style="background:#fef2f2;color:#dc2626;padding:12px 16px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="background:#f0fdf4;color:#16a34a;padding:12px 16px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST">
                <?php echo CsrfHelper::field(); ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <input type="text" name="name" placeholder="Họ tên" required style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;">
                    <input type="email" name="email" placeholder="Email" required style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;">
                </div>
                <textarea name="message" rows="5" placeholder="Nội dung..." required style="width:100%;padding:12px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:12px;"></textarea>
                <button type="submit" style="padding:12px 28px;background:#6366f1;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
                    <i class="fa fa-paper-plane"></i> Gửi tin nhắn
                </button>
            </form>
        </div>
    </div>
</div>
<?php include 'app/views/footer.php'; ?>
