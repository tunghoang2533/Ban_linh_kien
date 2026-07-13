<?php
require_once 'session_check.php';
require_once 'config.php';
include 'app/views/header.php';
?>
<style>
.page-hero { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); padding: 56px 20px 48px; text-align: center; color: #fff; }
.page-hero h1 { margin: 0 0 10px; font-size: clamp(24px, 5vw, 38px); font-weight: 800; }
.page-hero p { margin: 0; font-size: 15px; opacity: .85; max-width: 540px; margin: 0 auto; line-height: 1.7; }
.page-content { max-width: 860px; margin: 40px auto 60px; padding: 0 16px; }
.info-card { background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(24,81,153,.08); overflow: hidden; margin-bottom: 24px; }
.info-card-header { background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 18px 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #bfdbfe; }
.info-card-header .icon { font-size: 22px; }
.info-card-header h2 { margin: 0; font-size: 17px; color: #1d4ed8; font-weight: 700; }
.info-card-body { padding: 22px 24px; }
.info-card-body p { margin: 0 0 14px; color: #475569; line-height: 1.75; font-size: 14.5px; }
.info-card-body p:last-child { margin: 0; }
.news-empty { text-align: center; padding: 60px 20px; }
.news-empty .icon { font-size: 64px; display: block; margin-bottom: 18px; }
.news-empty h2 { font-size: 22px; color: #1e293b; margin: 0 0 8px; }
.news-empty p { color: #64748b; font-size: 14px; max-width: 400px; margin: 0 auto 24px; line-height: 1.7; }
.btn-back { display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; background: #2563eb; color: #fff; border-radius: 10px; font-weight: 700; font-size: 14px; text-decoration: none; transition: background .18s, transform .18s; }
.btn-back:hover { background: #1d4ed8; transform: translateY(-1px); }
</style>

<!-- Hero -->
<div class="page-hero">
    <h1><i class="fa fa-newspaper-o"></i> Tin Tức & Công Nghệ</h1>
    <p>Cập nhật những tin tức mới nhất về linh kiện máy tính, GPU, CPU và xu hướng công nghệ 2026</p>
</div>

<div class="page-content">
    <div class="info-card">
        <div class="info-card-header">
            <span class="icon">📌</span>
            <h2>Sắp có tin tức mới</h2>
        </div>
        <div class="info-card-body">
            <p>Chúng tôi đang chuẩn bị nội dung tin tức về các sản phẩm linh kiện máy tính mới nhất trên thị trường.</p>
            <p>Hãy quay lại sớm để cập nhật những thông tin hữu ích về GPU RTX 50 series, CPU AMD Ryzen 9000, DDR5 và các xu hướng công nghệ mới nhất.</p>
        </div>
    </div>

    <div class="news-empty">
        <span class="icon">📰</span>
        <h2>Chưa có bài đăng nào</h2>
        <p>Mục tin tức đang trong giai đoạn phát triển. Theo dõi fanpage để không bỏ lỡ thông tin!</p>
        <a href="index.php" class="btn-back"><i class="fa fa-home"></i> Về trang chủ</a>
    </div>
</div>

<?php include 'app/views/footer.php'; ?>
