<?php
/*
 * Trang đổi mật khẩu Admin
 * URL: ?page=password
 */
$adminUser = null;
try {
    $s = $db->prepare('SELECT username, email, full_name, created_at FROM users WHERE id = :id');
    $s->execute([':id' => $_SESSION['user_id']]);
    $adminUser = $s->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {}
?>
<style>
.pw-wrap {
    max-width: 560px;
    margin: 0 auto;
}
.pw-profile-card {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
    border-radius: 20px;
    padding: 28px 32px;
    color: white;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 32px rgba(99,102,241,.35);
}
.pw-avatar {
    width: 68px; height: 68px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; font-weight: 900; border: 3px solid rgba(255,255,255,.4);
    flex-shrink: 0;
}
.pw-profile-name  { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
.pw-profile-email { font-size: 13px; opacity: .8; }
.pw-profile-badge {
    margin-left: auto;
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 20px; padding: 4px 14px;
    font-size: 12px; font-weight: 700;
    display: flex; align-items: center; gap: 6px;
}

.pw-card {
    background: var(--bg-surface);
    border-radius: 20px;
    box-shadow: 0 2px 20px rgba(15,23,42,.08);
    border: 1px solid rgba(148,163,184,.12);
    overflow: hidden;
}
.pw-card-header {
    padding: 20px 28px 0;
    font-size: 16px; font-weight: 800; color: var(--text-primary);
    display: flex; align-items: center; gap: 10px;
}
.pw-card-body { padding: 24px 28px 28px; }

.pw-form { display: flex; flex-direction: column; gap: 18px; }
.pw-field { display: flex; flex-direction: column; gap: 6px; }
.pw-label {
    font-size: 13px; font-weight: 700; color: var(--text-secondary);
    display: flex; align-items: center; gap: 6px;
}
.pw-input-wrap { position: relative; }
.pw-input {
    width: 100%; padding: 11px 44px 11px 14px;
    border: 1px solid var(--border-muted); border-radius: 10px;
    font-size: 14px; font-family: inherit; color: var(--text-primary); background: var(--bg-elevated);
    transition: border-color .2s, box-shadow .2s;
}
.pw-input:focus {
    outline: none; border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: var(--bg-surface);
}
.pw-input.error { border-color: #ef4444; background: #fff5f5; }
.pw-eye {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    color: var(--text-faint); cursor: pointer; font-size: 14px; background: none; border: none;
    padding: 0; transition: color .15s;
}
.pw-eye:hover { color: #6366f1; }

.pw-strength {
    height: 4px; border-radius: 4px; background: #e2e8f0;
    margin-top: 6px; overflow: hidden; transition: all .3s;
}
.pw-strength-bar { height: 100%; border-radius: 4px; transition: width .3s, background .3s; width: 0; }
.pw-strength-label { font-size: 11px; font-weight: 600; margin-top: 4px; }

.pw-divider {
    border: none; border-top: 1px solid #f1f5f9; margin: 4px 0;
}

.pw-tips {
    background: var(--success-bg); border: 1px solid #bbf7d0; border-radius: 10px;
    padding: 12px 16px; font-size: 12px; color: #4ade80;
}
.pw-tips ul { margin: 6px 0 0 16px; }
.pw-tips li { margin-bottom: 3px; }
</style>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Đổi mật khẩu</h1>
            <p>Bảo mật tài khoản quản trị viên</p>
        </div>
    </div>

    <div class="pw-wrap">

        <!-- Profile card -->
        <div class="pw-profile-card">
            <div class="pw-avatar">
                <?php echo strtoupper(mb_substr($adminUser['username'] ?? 'A', 0, 1)); ?>
            </div>
            <div>
                <div class="pw-profile-name"><?php echo htmlspecialchars($adminUser['full_name'] ?? $adminUser['username'] ?? 'Admin'); ?></div>
                <div class="pw-profile-email"><?php echo htmlspecialchars($adminUser['email'] ?? ''); ?></div>
            </div>
            <div class="pw-profile-badge">
                <i class="fas fa-shield-alt"></i> Quản trị viên
            </div>
        </div>

        <!-- Alert -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success" style="margin-bottom:20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom:20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="pw-card">
            <div class="pw-card-header">
                <i class="fas fa-lock" style="color:#6366f1;font-size:18px;"></i>
                Thay đổi mật khẩu
            </div>
            <div class="pw-card-body">
                <form method="POST" action="?page=password" class="pw-form" id="pwForm">

                    <!-- Mật khẩu hiện tại -->
                    <div class="pw-field">
                        <label class="pw-label" for="current_password">
                            <i class="fas fa-key" style="color:#6366f1;"></i>
                            Mật khẩu hiện tại <span style="color:#ef4444;">*</span>
                        </label>
                        <div class="pw-input-wrap">
                            <input type="password" id="current_password" name="current_password"
                                   class="pw-input <?php echo !empty($error) ? 'error' : ''; ?>"
                                   placeholder="Nhập mật khẩu hiện tại" autocomplete="current-password">
                            <button type="button" class="pw-eye" onclick="togglePw('current_password',this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="pw-divider">

                    <!-- Mật khẩu mới -->
                    <div class="pw-field">
                        <label class="pw-label" for="new_password">
                            <i class="fas fa-lock" style="color:#10b981;"></i>
                            Mật khẩu mới <span style="color:#ef4444;">*</span>
                        </label>
                        <div class="pw-input-wrap">
                            <input type="password" id="new_password" name="new_password"
                                   class="pw-input"
                                   placeholder="Ít nhất 6 ký tự" autocomplete="new-password"
                                   oninput="checkStrength(this.value)">
                            <button type="button" class="pw-eye" onclick="togglePw('new_password',this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <!-- Strength indicator -->
                        <div class="pw-strength" id="strengthBar">
                            <div class="pw-strength-bar" id="strengthFill"></div>
                        </div>
                        <div class="pw-strength-label" id="strengthLabel" style="color:var(--text-faint);"></div>
                    </div>

                    <!-- Xác nhận mật khẩu -->
                    <div class="pw-field">
                        <label class="pw-label" for="confirm_password">
                            <i class="fas fa-check-circle" style="color:#10b981;"></i>
                            Xác nhận mật khẩu mới <span style="color:#ef4444;">*</span>
                        </label>
                        <div class="pw-input-wrap">
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="pw-input"
                                   placeholder="Nhập lại mật khẩu mới" autocomplete="new-password"
                                   oninput="checkMatch()">
                            <button type="button" class="pw-eye" onclick="togglePw('confirm_password',this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="matchMsg" style="font-size:11px;margin-top:3px;"></div>
                    </div>

                    <!-- Tips -->
                    <div class="pw-tips">
                        <strong><i class="fas fa-lightbulb"></i> Mật khẩu mạnh nên có:</strong>
                        <ul>
                            <li>Ít nhất 8 ký tự</li>
                            <li>Kết hợp chữ hoa, chữ thường và số</li>
                            <li>Ký tự đặc biệt (!@#$%^&*)</li>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
                            <i class="fas fa-save"></i> Lưu mật khẩu mới
                        </button>
                        <a href="?page=dashboard" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Huỷ
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<script>
function togglePw(id, btn) {
    var inp = document.getElementById(id);
    var icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function checkStrength(val) {
    var bar  = document.getElementById('strengthFill');
    var lbl  = document.getElementById('strengthLabel');
    if (!val) { bar.style.width='0'; lbl.textContent=''; return; }
    var score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var map = [
        [0,  '#ef4444', '0%',   ''],
        [1,  '#ef4444', '20%',  'Rất yếu'],
        [2,  '#f59e0b', '40%',  'Yếu'],
        [3,  '#f59e0b', '60%',  'Trung bình'],
        [4,  '#10b981', '80%',  'Mạnh'],
        [5,  '#059669', '100%', 'Rất mạnh ✓'],
    ];
    var m = map[Math.min(score, 5)];
    bar.style.background = m[1];
    bar.style.width = m[2];
    lbl.textContent = m[3];
    lbl.style.color = m[1];
}

function checkMatch() {
    var np = document.getElementById('new_password').value;
    var cp = document.getElementById('confirm_password').value;
    var msg = document.getElementById('matchMsg');
    if (!cp) { msg.textContent = ''; return; }
    if (np === cp) {
        msg.textContent = '✓ Mật khẩu khớp';
        msg.style.color = '#10b981';
    } else {
        msg.textContent = '✗ Mật khẩu không khớp';
        msg.style.color = '#ef4444';
    }
}
</script>
