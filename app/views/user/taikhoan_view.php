<?php $activeTab = 'login';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activeTab = isset($_POST['register']) ? 'register' : 'login';
}
?>

<style>
    .account-page {
        min-height: calc(100vh - 190px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 15px 60px;
        background: #f4f7fb;
    }
    .account-card {
        width: 100%;
        max-width: 560px;
        background: #ffffff;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 22px 70px rgba(28, 72, 122, 0.12);
    }
    .account-card-header {
        padding: 38px 28px 36px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }
    .account-card.theme-blue .account-card-header {
        background: linear-gradient(135deg, #2869d4 0%, #1f5ebf 100%);
    }
    .account-card.theme-green .account-card-header {
        background: linear-gradient(135deg, #28a745 0%, #1f7d33 100%);
    }
    .account-card-header::before,
    .account-card-header::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        opacity: 0.25;
        pointer-events: none;
    }
    .account-card.theme-blue .account-card-header::before {
        width: 120px;
        height: 120px;
        background: rgba(255,255,255,0.15);
        top: -30px;
        right: -30px;
    }
    .account-card.theme-blue .account-card-header::after {
        width: 180px;
        height: 180px;
        background: rgba(255,255,255,0.10);
        bottom: -60px;
        left: -60px;
    }
    .account-card.theme-green .account-card-header::before {
        width: 120px;
        height: 120px;
        background: rgba(255,255,255,0.20);
        top: -30px;
        right: -30px;
    }
    .account-card.theme-green .account-card-header::after {
        width: 180px;
        height: 180px;
        background: rgba(255,255,255,0.12);
        bottom: -60px;
        left: -60px;
    }
    .account-card-header h1 {
        margin: 0;
        font-size: 32px;
        line-height: 1.05;
        letter-spacing: 0.02em;
        text-shadow: 0 4px 20px rgba(0,0,0,0.12);
        position: relative;
        z-index: 1;
    }
    .account-card-header p {
        display: none;
    }
    .account-card-tabs {
        display: flex;
        border-bottom: 1px solid #e9eef4;
        background: #fafbfc;
    }
    .account-card-tabs button {
        flex: 1;
        padding: 18px 14px;
        border: none;
        background: transparent;
        color: #5f6d82;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all .25s ease;
    }
    .account-card-tabs button.active:not(.register-active) {
        color: #1d3058;
        background: #ffffff;
        box-shadow: inset 0 -3px 0 #2869d4;
    }
    .account-card-tabs button.register-active {
        color: #1a7d32;
        background: #ffffff;
        box-shadow: inset 0 -3px 0 #28a745;
    }
    .account-card-tabs button.register-active:hover {
        background: #ffffff;
    }
    .account-card-body {
        padding: 30px 30px 34px;
    }
    .message-box {
        margin-bottom: 22px;
        padding: 15px 18px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.5;
    }
    .message-box.error {
        background: #fff0f1;
        border: 1px solid #f1c0c8;
        color: #9a1b2b;
    }
    .message-box.success {
        background: #f0fbf3;
        border: 1px solid #b8dfc2;
        color: #216b35;
    }
    .field-group {
        margin-bottom: 18px;
    }
    .field-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        color: #5f6d82;
    }
    .field-group input {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #dde2ea;
        border-radius: 14px;
        background: #f9fbff;
        color: #23304d;
        font-size: 15px;
        transition: border-color .25s ease, box-shadow .25s ease;
    }
    .field-group input:focus {
        outline: none;
        border-color: #2869d4;
        box-shadow: 0 0 0 4px rgba(40,105,212,0.14);
        background: #ffffff;
    }
    .password-wrapper {
        position: relative;
    }
    .password-wrapper input {
        padding-right: 48px;
    }
    .toggle-pw {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #8a99b3;
        font-size: 16px;
        padding: 4px;
        transition: color .2s;
        line-height: 1;
    }
    .toggle-pw:hover { color: #2869d4; }
    .auth-button {
        width: 100%;
        padding: 16px 0;
        border: none;
        border-radius: 16px;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .25s ease;
        box-shadow: 0 16px 30px rgba(0,0,0,0.10);
        background-size: 200% 100%;
    }
    .account-card.theme-blue .auth-button {
        background-image: linear-gradient(135deg, #3f80f3 0%, #1e61d1 100%);
    }
    .account-card.theme-blue .auth-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 20px 36px rgba(30,86,209,0.24);
    }
    .account-card.theme-green .auth-button {
        background-image: linear-gradient(135deg, #3dc55c 0%, #1b7c2e 100%);
    }
    .account-card.theme-green .auth-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 20px 36px rgba(27,124,46,0.24);
    }
    .auth-helper {
        margin-top: 12px;
        font-size: 14px;
        color: #697589;
        text-align: center;
    }
    .auth-helper a {
        color: #2869d4;
        text-decoration: none;
        font-weight: 600;
    }
    .auth-helper a.register-link {
        color: #28a745;
    }
    .logout-panel {
        text-align: center;
        padding: 40px 30px;
    }
    .logout-panel h2 {
        margin: 0 0 10px;
        font-size: 26px;
        color: #20334f;
    }
    .logout-panel p {
        margin: 0 0 24px;
        color: #5f6d82;
        line-height: 1.6;
    }
    .logout-panel .btn-logout {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 24px;
        border: none;
        border-radius: 14px;
        background: #e74c3c;
        color: #ffffff;
        font-weight: 700;
        text-decoration: none;
        transition: background .25s ease;
    }
    .logout-panel .btn-logout:hover {
        background: #cf4334;
    }
</style>

<div class="account-page">
<div class="account-card <?php echo $activeTab === 'register' ? 'theme-green' : 'theme-blue'; ?>">
        <div class="account-card-header">
            <h1 id="account-heading"><?php echo $activeTab === 'login' ? 'Chào mừng bạn quay trở lại!' : 'Rất vui được gặp bạn!'; ?></h1>
        </div>

        <?php if(!isset($_SESSION['user'])): ?>
            <div class="account-card-tabs">
                <button type="button" id="btn-login" class="<?php echo $activeTab === 'login' ? 'active' : ''; ?>" onclick="toggleForm('login')">Đăng nhập</button>
                <button type="button" id="btn-register" class="<?php echo $activeTab === 'register' ? 'active register-active' : ''; ?>" onclick="toggleForm('register')">Đăng ký</button>
            </div>

            <div class="account-card-body">
                <?php if(isset($_SESSION['timeout_msg'])): ?>
                    <div class="message-box error" style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:18px;">&#9203;</span>
                        <span><?php echo $_SESSION['timeout_msg']; unset($_SESSION['timeout_msg']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="message-box error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="message-box success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form id="form-login" method="POST" style="display: <?php echo $activeTab === 'login' ? 'block' : 'none'; ?>;">
                    <?php echo CsrfHelper::field(); ?>
                    <div class="field-group">
                        <label for="login-username">Tên đăng nhập</label>
                        <input id="login-username" type="text" name="username" placeholder="Nhập tên đăng nhập" required>
                    </div>
                    <div class="field-group">
                        <label for="login-password">Mật khẩu</label>
                        <div class="password-wrapper">
                            <input id="login-password" type="password" name="password" placeholder="Nhập mật khẩu" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('login-password', this)" title="Hiện/ẩn mật khẩu"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" name="login" class="auth-button">ĐĂNG NHẬP</button>
                    <div class="auth-helper">Bạn chưa có tài khoản? <a class="register-link" href="javascript:void(0)" onclick="toggleForm('register')">Đăng ký ngay</a></div>
                    <div class="auth-helper" style="margin-top:8px;">
                        <a href="javascript:void(0)" id="forgotPwLink" style="color:#94a3b8;font-size:13px;">
                            <i class="fa fa-question-circle"></i> Quên mật khẩu?
                        </a>
                    </div>
                </form>

                <form id="form-register" method="POST" style="display: <?php echo $activeTab === 'register' ? 'block' : 'none'; ?>;">
                    <?php echo CsrfHelper::field(); ?>
                    <div class="field-group">
                        <label for="register-fullname">Họ và tên</label>
                        <input id="register-fullname" type="text" name="fullname" placeholder="Nhập họ và tên" required>
                    </div>
                    <div class="field-group">
                        <label for="register-email">Email</label>
                        <input id="register-email" type="email" name="email" placeholder="Nhập email" required>
                    </div>
                    <div class="field-group">
                        <label for="register-username">Tên đăng nhập</label>
                        <input id="register-username" type="text" name="username" placeholder="Chọn tên đăng nhập" required>
                    </div>
                    <div class="field-group">
                        <label for="register-password">Mật khẩu</label>
                        <div class="password-wrapper">
                            <input id="register-password" type="password" name="password" placeholder="Tạo mật khẩu (ít nhất 6 ký tự)" required minlength="6">
                            <button type="button" class="toggle-pw" onclick="togglePw('register-password', this)" title="Hiện/ẩn mật khẩu"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="register-confirm-password">Xác nhận mật khẩu</label>
                        <div class="password-wrapper">
                            <input id="register-confirm-password" type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('register-confirm-password', this)" title="Hiện/ẩn mật khẩu"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" name="register" class="auth-button">ĐĂNG KÝ NGAY</button>
                    <div class="auth-helper">Đã có tài khoản? <a href="javascript:void(0)" onclick="toggleForm('login')">Đăng nhập</a></div>
                </form>
            </div>
        <?php else: ?>
            <div class="logout-panel">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#e8f3ff,#c7e3ff);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:28px;">
                    <?php
                        $av = $_SESSION['user']['avatar'] ?? '';
                        if ($av): ?>
                        <img src="<?php echo htmlspecialchars($av); ?>" style="width:72px;height:72px;border-radius:50%;object-fit:cover;" alt="Avatar" loading="lazy">
                    <?php else: ?>
                        <i class="fa fa-user" style="color:#288ad6;"></i>
                    <?php endif; ?>
                </div>
                <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['fullname']); ?>!</h2>
                <p>Bạn đang đăng nhập. Điều hướng nhanh đến các chức năng:</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:22px;">
                    <a href="thongtin.php" style="display:flex;align-items:center;gap:9px;padding:13px 14px;border-radius:13px;background:#f0f7ff;border:1.5px solid #d0e8ff;color:#1a6eb5;font-weight:700;font-size:13.5px;text-decoration:none;transition:all .2s;">
                        <i class="fa fa-user-circle" style="font-size:17px;"></i> Hồ sơ
                    </a>
                    <a href="lichsu.php" style="display:flex;align-items:center;gap:9px;padding:13px 14px;border-radius:13px;background:#f0f7ff;border:1.5px solid #d0e8ff;color:#1a6eb5;font-weight:700;font-size:13.5px;text-decoration:none;transition:all .2s;">
                        <i class="fa fa-history" style="font-size:17px;"></i> Đơn hàng
                    </a>
                    <a href="giohang.php" style="display:flex;align-items:center;gap:9px;padding:13px 14px;border-radius:13px;background:#f5f0ff;border:1.5px solid #ddd0ff;color:#6d28d9;font-weight:700;font-size:13.5px;text-decoration:none;transition:all .2s;">
                        <i class="fa fa-shopping-cart" style="font-size:17px;"></i> Giỏ hàng
                    </a>
                    <a href="doimatkhau.php" style="display:flex;align-items:center;gap:9px;padding:13px 14px;border-radius:13px;background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534;font-weight:700;font-size:13.5px;text-decoration:none;transition:all .2s;">
                        <i class="fa fa-lock" style="font-size:17px;"></i> Đổi mật khẩu
                    </a>
                </div>
                <a class="btn-logout" href="<?php echo BASE_URL; ?>dangxuat.php"><i class="fa fa-sign-out"></i> ĐĂNG XUẤT</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Quên Mật Khẩu -->
<div id="forgotPwModal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,0.5);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:22px;padding:36px 28px;max-width:440px;width:calc(100% - 32px);box-shadow:0 24px 80px rgba(0,0,0,0.2);position:relative;animation:fpIn .25s ease;">
        <style>@keyframes fpIn { from{opacity:0;transform:scale(.95)translateY(10px)} to{opacity:1;transform:scale(1)translateY(0)} }</style>
        <button onclick="document.getElementById('forgotPwModal').style.display='none'" style="position:absolute;top:14px;right:14px;border:none;background:#f1f5f9;border-radius:50%;width:30px;height:30px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;">&times;</button>
        <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:48px;margin-bottom:10px;">🔐</div>
            <h2 style="margin:0 0 8px;font-size:20px;color:#1e293b;">Quên mật khẩu?</h2>
            <p style="margin:0;color:#64748b;font-size:14px;line-height:1.6;">Hiện tại chưa có tính năng tự reset mật khẩu tự động. Vui lòng liên hệ quản trị viên để được hỗ trợ đặt lại mật khẩu.</p>
        </div>
        <div style="background:#eff6ff;border-radius:14px;padding:16px 18px;margin-bottom:20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <i class="fa fa-envelope" style="color:#2563eb;font-size:16px;"></i>
                <div><strong style="display:block;font-size:13px;color:#1e293b;">Email hỗ trợ</strong><span style="font-size:14px;color:#2563eb;">support@pcstore.vn</span></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <i class="fa fa-phone" style="color:#2563eb;font-size:16px;"></i>
                <div><strong style="display:block;font-size:13px;color:#1e293b;">Hotline</strong><span style="font-size:14px;color:#2563eb;">1800 6975</span></div>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>lienhe.php" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:13px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:12px;font-weight:700;font-size:14px;text-decoration:none;">
            <i class="fa fa-headphones"></i> Liên hệ hỗ trợ ngay
        </a>
    </div>
</div>

<script>
    // Tab switching
    function toggleForm(type) {
        document.getElementById('form-login').style.display    = type === 'login'    ? 'block' : 'none';
        document.getElementById('form-register').style.display = type === 'register' ? 'block' : 'none';
        var btnLogin    = document.getElementById('btn-login');
        var btnRegister = document.getElementById('btn-register');
        var accountCard = document.querySelector('.account-card');
        btnLogin.classList.toggle('active', type === 'login');
        btnRegister.classList.toggle('active', type === 'register');
        btnRegister.classList.toggle('register-active', type === 'register');
        btnLogin.classList.remove('register-active');
        accountCard.classList.toggle('theme-green', type === 'register');
        accountCard.classList.toggle('theme-blue',  type === 'login');
        document.getElementById('account-heading').textContent =
            type === 'login' ? 'Chào mừng bạn quay trở lại!' : 'Rất vui được gặp bạn!';
    }

    // Toggle show/hide password
    function togglePw(inputId, btn) {
        var inp  = document.getElementById(inputId);
        var icon = btn.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Fix #10: Forgot password modal
    var forgotLink  = document.getElementById('forgotPwLink');
    var forgotModal = document.getElementById('forgotPwModal');
    if (forgotLink && forgotModal) {
        forgotLink.addEventListener('click', function() {
            forgotModal.style.display = 'flex';
        });
        forgotModal.addEventListener('click', function(e) {
            if (e.target === forgotModal) forgotModal.style.display = 'none';
        });
    }

    // Fix #6 client-side fallback validation
    document.getElementById('form-register').addEventListener('submit', function(e) {
        var pw  = document.getElementById('register-password').value;
        var cpw = document.getElementById('register-confirm-password').value;
        if (pw.length < 6) {
            e.preventDefault();
            alert('Mật khẩu phải có ít nhất 6 ký tự!');
            document.getElementById('register-password').focus();
            return;
        }
        if (pw !== cpw) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp! Vui lòng kiểm tra lại.');
            document.getElementById('register-confirm-password').focus();
        }
    });
</script>
