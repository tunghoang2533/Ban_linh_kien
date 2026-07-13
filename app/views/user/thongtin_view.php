<style>
    .profile-page {
        min-height: calc(100vh - 190px);
        padding: 40px 15px 60px;
        background: #f4f7fb;
    }
    .profile-card {
        max-width: 1100px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 26px;
        box-shadow: 0 22px 70px rgba(28, 72, 122, 0.12);
        overflow: hidden;
    }
    .profile-card-header {
        padding: 36px 40px;
        border-bottom: 1px solid #edf2f7;
    }
    .profile-card-header h1 {
        margin: 0;
        font-size: 32px;
        color: #121828;
    }
    .profile-card-header p {
        margin: 10px 0 0;
        color: #667085;
        font-size: 15px;
        line-height: 1.75;
    }
    .profile-card-body {
        display: grid;
        grid-template-columns: 1.5fr 0.9fr;
        gap: 30px;
        padding: 35px 40px 45px;
    }
    .profile-form {
        display: grid;
        gap: 18px;
    }
    .profile-form label {
        font-size: 14px;
        color: #344054;
        margin-bottom: 10px;
        display: block;
    }
    .profile-form input,
    .profile-form textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid #d2d6dc;
        background: #f8fafc;
        color: #102a43;
        font-size: 15px;
        transition: border-color .25s ease, box-shadow .25s ease;
    }
    .profile-form input:focus,
    .profile-form textarea:focus {
        outline: none;
        border-color: #288ad6;
        box-shadow: 0 0 0 4px rgba(56, 134, 230, 0.14);
        background: #ffffff;
    }
    .profile-form textarea {
        resize: vertical;
        min-height: 130px;
    }
    .profile-form .field-group {
        display: grid;
        gap: 8px;
    }
    .profile-form .field-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
    .profile-form .field-row .field-group {
        margin-bottom: 0;
    }
    .profile-submit {
        margin-top: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 28px;
        background: linear-gradient(135deg, #2869d4 0%, #1f5ebf 100%);
        color: #ffffff;
        border: none;
        border-radius: 16px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .profile-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 36px rgba(30, 86, 209, 0.18);
    }
    .profile-aside {
        background: #f8fafc;
        border-radius: 24px;
        padding: 30px;
        display: grid;
        gap: 24px;
        align-items: start;
    }
    .avatar-preview {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #d2d6dc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin: 0 auto;
    }
    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        text-align: center;
    }
    .avatar-action label {
        padding: 12px 24px;
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid #d2d6dc;
        color: #344054;
        cursor: pointer;
        font-weight: 600;
    }
    .avatar-action input {
        display: none;
    }
    .avatar-note {
        text-align: center;
        color: #667085;
        font-size: 13px;
        line-height: 1.6;
    }
    .success-message {
        margin: 0 0 18px;
        padding: 16px 18px;
        background: #ecfdf5;
        border: 1px solid #d1fae5;
        color: #166534;
        border-radius: 14px;
    }
    .error-message {
        margin: 0 0 18px;
        padding: 16px 18px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        border-radius: 14px;
    }
    @media (max-width: 980px) {
        .profile-card-body {
            grid-template-columns: 1fr;
        }
    }
    /* === STATS CARDS === */
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px 12px;
        text-align: center;
        border: 1px solid #e8edf5;
        box-shadow: 0 2px 10px rgba(40,105,212,0.06);
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40,105,212,0.12);
    }
    .stat-card .stat-val {
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-card .stat-label {
        font-size: 11px;
        color: #667085;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    /* === QUICK LINKS === */
    .profile-quicklinks {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .quicklink-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e8edf5;
        color: #344054;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all .2s;
    }
    .quicklink-item:hover {
        background: #f0f7ff;
        border-color: #288ad6;
        color: #288ad6;
        transform: translateX(4px);
    }
    .quicklink-item i {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .ql-blue  i { background: #e8f3ff; color: #288ad6; }
    .ql-green i { background: #d1fae5; color: #059669; }
    .ql-red   i { background: #fee2e2; color: #dc2626; }
    .ql-purple i { background: #ede9fe; color: #7c3aed; }
</style>

<div class="profile-page">
    <div class="profile-card">
        <div class="profile-card-header">
            <h1>Hồ Sơ Của Tôi</h1>
            <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
        </div>

        <form class="profile-card-body" method="POST" enctype="multipart/form-data">
            <?php echo CsrfHelper::field(); ?>
            <div>
                <?php if (!empty($message)): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="profile-form">
                    <div class="field-group">
                        <label>Email <small style="color:#64748b;font-weight:400;">(có thể thay đổi)</small></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" placeholder="your@email.com">
                        <small style="color:#94a3b8;font-size:12px;margin-top:4px;display:block;">⚠️ Email mới phải chưa được dùng bởi tài khoản khác</small>
                    </div>
                    <div class="field-group">
                        <label>Tên</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? ''); ?>">
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>" placeholder="Ví dụ: 0865385392">
                        </div>
                        <div class="field-group">
                            <label>Địa chỉ</label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($_SESSION['user']['address'] ?? ''); ?>" placeholder="Hà Nội">
                        </div>
                    </div>
                    <button type="submit" class="profile-submit">Lưu</button>
                </div>
            </div>

            <aside class="profile-aside">
                <!-- Thống kê đơn hàng -->
                <div class="profile-stats">
                    <div class="stat-card">
                        <div class="stat-val" style="color:#288ad6;"><?php echo (int)($stats['total'] ?? 0); ?></div>
                        <div class="stat-label">Tổng đơn</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-val" style="color:#059669;"><?php echo (int)($stats['completed'] ?? 0); ?></div>
                        <div class="stat-label">Hoàn thành</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-val" style="color:#f59e0b;"><?php echo (int)($stats['pending'] ?? 0); ?></div>
                        <div class="stat-label">Chờ xử lý</div>
                    </div>
                </div>
                <?php if (($stats['total_spent'] ?? 0) > 0): ?>
                <div style="text-align:center;padding:10px 0 4px;">
                    <div style="font-size:11px;color:#667085;text-transform:uppercase;letter-spacing:.4px;font-weight:600;margin-bottom:4px;">Tổng đã chi</div>
                    <div style="font-size:20px;font-weight:900;color:#e10c00;"><?php echo number_format($stats['total_spent'] ?? 0, 0, ',', '.'); ?>&#8363;</div>
                </div>
                <?php endif; ?>

                <!-- Avatar -->
                <div class="avatar-preview" id="avatarPreview">
                    <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar'] ?? BASE_URL . 'public/img/avatar.png'); ?>" alt="Avatar" loading="lazy">
                </div>
                <div class="avatar-action">
                    <label for="avatarInput">Chọn ảnh</label>
                    <input type="file" id="avatarInput" name="avatar" accept="image/png, image/jpeg, image/jpg">
                </div>
                <div class="avatar-note">
                    Dung lượng tối đa 1 MB<br>
                    Định dạng: JPG, PNG
                </div>

                <!-- Quick links -->
                <div class="profile-quicklinks">
                    <a href="lichsu.php" class="quicklink-item ql-blue">
                        <i class="fa fa-history"></i> Lịch sử đơn hàng
                    </a>
                    <a href="doimatkhau.php" class="quicklink-item ql-green">
                        <i class="fa fa-lock"></i> Đổi mật khẩu
                    </a>
                    <a href="giohang.php" class="quicklink-item ql-purple">
                        <i class="fa fa-shopping-cart"></i> Giỏ hàng
                    </a>
                    <a href="dangxuat.php" class="quicklink-item ql-red">
                        <i class="fa fa-sign-out"></i> Đăng xuất
                    </a>
                </div>
            </aside>
        </form>
    </div>
</div>

<script>
    document.getElementById('avatarInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('#avatarPreview img').src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
</script>
