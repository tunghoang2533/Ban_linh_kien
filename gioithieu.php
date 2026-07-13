<?php
require_once 'session_check.php';
require_once 'config.php';
include 'app/views/header.php';
?>
<style>
.page-hero { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); padding: 56px 20px 48px; text-align: center; color: #fff; }
.page-hero h1 { margin: 0 0 10px; font-size: clamp(24px, 5vw, 38px); font-weight: 800; }
.page-hero p { margin: 0 auto; font-size: 15px; opacity: .85; max-width: 580px; line-height: 1.7; }
.about-wrap { max-width: 980px; margin: 48px auto 80px; padding: 0 16px; }
.about-card { background: #fff; border-radius: 22px; box-shadow: 0 8px 32px rgba(24,81,153,.08); overflow: hidden; margin-bottom: 28px; }
.about-card-header { padding: 20px 28px; background: linear-gradient(135deg, #eff6ff, #dbeafe); display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #bfdbfe; }
.about-card-header .icon { font-size: 24px; }
.about-card-header h2 { margin: 0; font-size: 18px; color: #1d4ed8; font-weight: 700; }
.about-card-body { padding: 28px; }
.about-card-body p { margin: 0 0 14px; color: #475569; line-height: 1.8; font-size: 14.5px; }
.about-card-body p:last-child { margin: 0; }
.about-card-body ul { margin: 0; padding: 0 0 0 20px; color: #475569; line-height: 2; font-size: 14.5px; }
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; margin-top: 24px; }
.stat-box { background: linear-gradient(135deg, #1d4ed8, #2563eb); border-radius: 16px; padding: 24px 20px; text-align: center; color: #fff; }
.stat-box .stat-num { font-size: 36px; font-weight: 900; display: block; margin-bottom: 4px; }
.stat-box .stat-label { font-size: 13px; opacity: .85; }
.team-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.team-card { background: #f8fafc; border-radius: 16px; padding: 28px 20px; text-align: center; border: 1px solid #e2e8f0; transition: transform .2s, box-shadow .2s; }
.team-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(24,81,153,.1); }
.team-card .team-avatar { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #dbeafe, #bfdbfe); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 30px; }
.team-card strong { display: block; font-size: 16px; color: #1e293b; margin-bottom: 4px; }
.team-card span { font-size: 13px; color: #64748b; }
</style>

<div class="page-hero">
    <h1><i class="fa fa-info-circle"></i> Giới Thiệu PC Store</h1>
    <p>Chuyên cung cấp linh kiện máy tính chính hãng, giá tốt nhất thị trường. Uy tín – Chất lượng – Tận tâm.</p>
</div>

<div class="about-wrap">
    <!-- Về chúng tôi -->
    <div class="about-card">
        <div class="about-card-header">
            <span class="icon">🏪</span>
            <h2>Về PC Store</h2>
        </div>
        <div class="about-card-body">
            <p>PC Store được thành lập với sứ mệnh mang đến cho người dùng Việt Nam những sản phẩm linh kiện máy tính chính hãng, chất lượng cao với mức giá cạnh tranh nhất thị trường.</p>
            <p>Chúng tôi phân phối các thương hiệu uy tín hàng đầu thế giới như Intel, AMD, NVIDIA, ASUS, Gigabyte, MSI, Kingston, Samsung, Western Digital và nhiều thương hiệu khác.</p>
            <div class="stat-grid">
                <div class="stat-box"><span class="stat-num">5+</span><span class="stat-label">Năm kinh nghiệm</span></div>
                <div class="stat-box"><span class="stat-num">10K+</span><span class="stat-label">Khách hàng tin tưởng</span></div>
                <div class="stat-box"><span class="stat-num">500+</span><span class="stat-label">Sản phẩm</span></div>
                <div class="stat-box"><span class="stat-num">99%</span><span class="stat-label">Hài lòng khách hàng</span></div>
            </div>
        </div>
    </div>

    <!-- Cam kết -->
    <div class="about-card">
        <div class="about-card-header">
            <span class="icon">✅</span>
            <h2>Cam Kết Của Chúng Tôi</h2>
        </div>
        <div class="about-card-body">
            <ul>
                <li>🛡️ <strong>Hàng chính hãng 100%</strong> – Có tem bảo hành từ nhà sản xuất</li>
                <li>🚀 <strong>Giao hàng nhanh</strong> – Nội thành 2–4 giờ, toàn quốc 1–3 ngày</li>
                <li>🔧 <strong>Bảo hành tận tâm</strong> – Hỗ trợ đổi trả trong vòng 30 ngày</li>
                <li>💰 <strong>Giá cạnh tranh</strong> – Cam kết hoàn tiền nếu tìm được giá tốt hơn</li>
                <li>📞 <strong>Hỗ trợ 24/7</strong> – Tư vấn kỹ thuật miễn phí qua hotline</li>
            </ul>
        </div>
    </div>

    <!-- Đội ngũ -->
    <div class="about-card">
        <div class="about-card-header">
            <span class="icon">👨‍💻</span>
            <h2>Đội Ngũ Của Chúng Tôi</h2>
        </div>
        <div class="about-card-body">
            <div class="team-grid">
                <div class="team-card"><div class="team-avatar">👨‍💼</div><strong>Nguyễn Văn A</strong><span>Giám đốc điều hành</span></div>
                <div class="team-card"><div class="team-avatar">👩‍💻</div><strong>Trần Thị B</strong><span>Quản lý kỹ thuật</span></div>
                <div class="team-card"><div class="team-avatar">👨‍🔧</div><strong>Lê Văn C</strong><span>Kỹ thuật viên</span></div>
                <div class="team-card"><div class="team-avatar">👩‍🎨</div><strong>Phạm Thị D</strong><span>Chăm sóc khách hàng</span></div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/footer.php'; ?>
