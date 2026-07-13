<style>
    .password-page {
        min-height: calc(100vh - 190px);
        padding: 40px 15px 60px;
        background: #f4f7fb;
    }
    .password-card {
        max-width: 540px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 26px;
        box-shadow: 0 22px 70px rgba(28, 72, 122, 0.12);
        overflow: hidden;
    }
    .password-card-header {
        padding: 36px 30px;
        border-bottom: 1px solid #edf2f7;
    }
    .password-card-header h1 {
        margin: 0;
        font-size: 30px;
        color: #111827;
    }
    .password-card-header p {
        margin: 10px 0 0;
        color: #667085;
        font-size: 15px;
        line-height: 1.7;
    }
    .password-card-body {
        padding: 34px 30px 36px;
    }
    .field-group {
        margin-bottom: 18px;
    }
    .field-group label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
    }
    .field-group input {
        width: 100%;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid #d2d6dc;
        background: #f8fafc;
        color: #102a43;
        font-size: 15px;
    }
    .field-group input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
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
    .toggle-pw:hover { color: #2563eb; }
    /* Password strength */
    .pw-strength-bar {
        height: 4px;
        border-radius: 99px;
        background: #e2e8f0;
        margin-top: 8px;
        overflow: hidden;
    }
    .pw-strength-fill {
        height: 100%;
        border-radius: 99px;
        transition: width .3s ease, background .3s ease;
        width: 0%;
    }
    .pw-strength-label {
        font-size: 12px;
        margin-top: 4px;
        font-weight: 600;
        min-height: 16px;
    }
    .auth-button {
        width: 100%;
        padding: 16px 0;
        border: none;
        border-radius: 16px;
        color: #ffffff;
        background-image: linear-gradient(135deg, #3f80f3 0%, #1e61d1 100%);
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .25s ease;
    }
    .auth-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 20px 36px rgba(30, 86, 209, 0.24);
    }
    .message-box {
        margin-bottom: 22px;
        padding: 16px 18px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.6;
    }
    .message-box.error {
        background: #fff0f1;
        border: 1px solid #f1c0c8;
        color: #9a1b2b;
    }
    .message-box.success {
        background: #ecfdf5;
        border: 1px solid #d1fae5;
        color: #166534;
    }
</style>

<div class="password-page">
    <div class="password-card">
        <div class="password-card-header">
            <h1>Đổi mật khẩu</h1>
            <p>Nhập mật khẩu hiện tại và mật khẩu mới để bảo vệ tài khoản.</p>
        </div>
        <div class="password-card-body">
            <?php if (!empty($error)): ?>
                <div class="message-box error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="message-box success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php echo CsrfHelper::field(); ?>
                <div class="field-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)" title="Hiện/ẩn"><i class="fa fa-eye"></i></button>
                    </div>
                </div>
                <div class="field-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" required oninput="checkStrength(this.value)">
                        <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)" title="Hiện/ẩn"><i class="fa fa-eye"></i></button>
                    </div>
                    <div class="pw-strength-bar"><div class="pw-strength-fill" id="strengthFill"></div></div>
                    <div class="pw-strength-label" id="strengthLabel"></div>
                </div>
                <div class="field-group">
                    <label for="confirm_password">Nhập lại mật khẩu mới</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)" title="Hiện/ẩn"><i class="fa fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="auth-button">Cập nhật mật khẩu</button>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePw(inputId, btn) {
        var inp = document.getElementById(inputId);
        var icon = btn.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function checkStrength(pw) {
        var fill = document.getElementById('strengthFill');
        var label = document.getElementById('strengthLabel');
        var score = 0;
        if (pw.length >= 6) score++;
        if (pw.length >= 10) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;

        var levels = [
            { pct: '0%',   color: '#e2e8f0', text: '' },
            { pct: '25%',  color: '#ef4444', text: 'Độ mạnh: Yếu' },
            { pct: '50%',  color: '#f59e0b', text: 'Độ mạnh: Trung bình' },
            { pct: '75%',  color: '#3b82f6', text: 'Độ mạnh: Khá' },
            { pct: '88%',  color: '#10b981', text: 'Độ mạnh: Mạnh' },
            { pct: '100%', color: '#059669', text: 'Độ mạnh: Rất mạnh 💪' },
        ];
        var lvl = levels[Math.min(score, 5)];
        fill.style.width = pw.length ? lvl.pct : '0%';
        fill.style.background = lvl.color;
        label.textContent = pw.length ? lvl.text : '';
        label.style.color = lvl.color;
    }
</script>
