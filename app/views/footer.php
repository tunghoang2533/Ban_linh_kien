<?php
// AssetHelper autoloaded via PSR-4 + class_alias
?>
<style>
    /* ════════════════════════════════════════════════════════════
       PRODUCT QUICK VIEW MODAL
       ════════════════════════════════════════════════════════════ */
    .qv-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.55);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(6px);
        padding: 16px;
    }
    .qv-overlay.open { display: flex; }

    .qv-modal {
        background: #fff;
        border-radius: 24px;
        width: 820px;
        max-width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 40px 100px rgba(15,23,42,0.25);
        animation: qvIn .28s cubic-bezier(.34,1.56,.64,1);
        overflow: hidden;
        position: relative;
    }
    @keyframes qvIn {
        from { opacity: 0; transform: scale(.92) translateY(24px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .qv-close {
        position: absolute;
        top: 14px; right: 14px;
        z-index: 10;
        width: 36px; height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.9);
        border: none;
        cursor: pointer;
        font-size: 20px;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: background .18s, color .18s, transform .18s;
        line-height: 1;
    }
    .qv-close:hover { background: #ef4444; color: #fff; transform: scale(1.1); }

    .qv-body {
        display: flex;
        gap: 0;
        overflow-y: auto;
        flex: 1;
    }
    .qv-body::-webkit-scrollbar { width: 4px; }
    .qv-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    /* Gallery column */
    .qv-gallery {
        width: 380px;
        flex-shrink: 0;
        padding: 24px 20px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    .qv-gallery-main {
        width: 100%;
        aspect-ratio: 1/1;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e8edf3;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }
    .qv-gallery-main img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: opacity .25s ease;
        display: block;
        padding: 8px;
    }
    .qv-gallery-thumbs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        max-width: 100%;
        padding: 4px 0;
        scrollbar-width: thin;
    }
    .qv-gallery-thumbs::-webkit-scrollbar { height: 3px; }
    .qv-gallery-thumbs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .qv-thumb {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        flex-shrink: 0;
        transition: border-color .2s;
        background: #fff;
    }
    .qv-thumb:hover { border-color: #94a3b8; }
    .qv-thumb.active { border-color: #2563eb; }
    .qv-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .qv-out-of-stock-badge {
        position: absolute;
        inset: 12px;
        background: rgba(0,0,0,0.48);
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        pointer-events: none;
    }
    .qv-out-of-stock-badge span {
        background: #ef4444;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.08em;
        padding: 5px 16px;
        border-radius: 999px;
        text-transform: uppercase;
        box-shadow: 0 4px 14px rgba(239,68,68,0.45);
    }

    /* Info column */
    .qv-info {
        flex: 1;
        padding: 24px 24px 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-width: 0;
    }
    .qv-category {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #2563eb;
        background: #eff6ff;
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        width: fit-content;
    }
    .qv-name {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .qv-rating {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
    }
    .qv-stars { color: #f59e0b; font-size: 14px; }
    .qv-stars-empty { color: #e2e8f0; font-size: 14px; }
    .qv-review-count { color: #64748b; font-size: 12px; }

    .qv-divider {
        border: none;
        border-top: 1px solid #f1f5f9;
        margin: 2px 0;
    }

    .qv-price-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .qv-price-final {
        font-size: 26px;
        font-weight: 800;
        color: #dc2626;
        letter-spacing: -0.02em;
    }
    .qv-price-original {
        font-size: 15px;
        color: #94a3b8;
        text-decoration: line-through;
    }
    .qv-price-badge {
        background: #dc2626;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 9px;
        border-radius: 20px;
    }
    .qv-description {
        font-size: 13px;
        color: #64748b;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Specs mini table */
    .qv-specs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 16px;
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 14px;
    }
    .qv-spec-item {
        display: flex;
        gap: 6px;
        font-size: 12px;
        line-height: 1.6;
    }
    .qv-spec-name {
        color: #64748b;
        font-weight: 500;
        flex-shrink: 0;
    }
    .qv-spec-name::after { content: ':'; }
    .qv-spec-value {
        color: #1e293b;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .qv-specs-more {
        font-size: 11px;
        color: #2563eb;
        font-weight: 600;
        text-decoration: none;
        grid-column: 1 / -1;
        text-align: center;
        padding-top: 4px;
    }
    .qv-specs-more:hover { text-decoration: underline; }

    /* Variants */
    .qv-variants { display: flex; flex-wrap: wrap; gap: 6px; }
    .qv-variant-chip {
        padding: 5px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        background: #fff;
        cursor: pointer;
        transition: border-color .2s, background .2s;
    }
    .qv-variant-chip:hover { border-color: #93c5fd; background: #eff6ff; }

    /* Actions */
    .qv-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: auto;
        padding-top: 8px;
    }
    .qv-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: transform .15s, box-shadow .15s, background .15s;
    }
    .qv-btn:hover { transform: translateY(-1px); }
    .qv-btn-cart {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        box-shadow: 0 6px 18px rgba(37,99,235,0.25);
    }
    .qv-btn-cart:hover { box-shadow: 0 8px 24px rgba(37,99,235,0.35); }
    .qv-btn-buy {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #fff;
        box-shadow: 0 6px 18px rgba(249,115,22,0.25);
    }
    .qv-btn-buy:hover { box-shadow: 0 8px 24px rgba(249,115,22,0.35); }
    .qv-btn-detail {
        grid-column: 1 / -1;
        background: #f1f5f9;
        color: #475569;
        font-size: 13px;
        padding: 10px;
    }
    .qv-btn-detail:hover { background: #e2e8f0; }
    .qv-btn-disabled {
        background: #e2e8f0;
        color: #94a3b8;
        cursor: not-allowed;
        pointer-events: none;
        box-shadow: none;
        grid-column: 1 / -1;
    }

    /* Loading state */
    .qv-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 80px 40px;
        gap: 14px;
        flex-direction: column;
        color: #94a3b8;
    }
    .qv-loading .qv-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #e2e8f0;
        border-top-color: #2563eb;
        border-radius: 50%;
        animation: qvSpin .7s linear infinite;
    }
    @keyframes qvSpin {
        to { transform: rotate(360deg); }
    }

    /* Error state */
    .qv-error {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 40px;
        flex-direction: column;
        gap: 12px;
        color: #64748b;
        text-align: center;
    }
    .qv-error i { font-size: 40px; color: #ef4444; }

    /* Quick View button on product cards */
    .qv-trigger {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.9);
        background: rgba(255,255,255,0.95);
        color: #1e293b;
        border: none;
        border-radius: 12px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s, transform .2s;
        box-shadow: 0 4px 16px rgba(0,0,0,0.18);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        z-index: 5;
        white-space: nowrap;
    }
    .product-item:hover .qv-trigger,
    .product-card:hover .qv-trigger,
    .sec-card:hover .qv-trigger {
        opacity: 1;
        pointer-events: auto;
        transform: translate(-50%, -50%) scale(1);
    }
    .qv-trigger:hover {
        background: #fff;
        box-shadow: 0 6px 24px rgba(0,0,0,0.25);
        transform: translate(-50%, -50%) scale(1.05);
    }

    @media (max-width: 768px) {
        .qv-body { flex-direction: column; }
        .qv-gallery {
            width: 100%;
            padding: 16px;
        }
        .qv-gallery-main { aspect-ratio: 4/3; }
        .qv-info { padding: 16px; }
        .qv-specs { grid-template-columns: 1fr; }
        .qv-price-final { font-size: 22px; }
        .qv-trigger { display: none; }
    }

    /* ── Footer v2.0 ── */
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap');

    .footer {
        background: #0f172a;
        color: #94a3b8;
        padding: 64px 0 0;
        margin-top: 80px;
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        line-height: 1.7;
    }

    .footer-inner {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Grid layout */
    .footer-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr 1fr 1fr;
        gap: 48px;
        padding-bottom: 48px;
    }

    @media (max-width: 900px) {
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 36px;
        }
    }
    @media (max-width: 540px) {
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }
        .footer { padding: 48px 0 0; }
    }

    /* Brand column */
    .footer-brand .brand-name {
        font-size: 22px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.03em;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        margin-bottom: 14px;
    }
    .footer-brand .brand-name .brand-icon {
        width: 36px;
        height: 36px;
        background: #2563eb;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        flex-shrink: 0;
    }
    .footer-brand p {
        color: #64748b;
        font-size: 13.5px;
        line-height: 1.65;
        margin-bottom: 20px;
        max-width: 280px;
    }

    /* Social links */
    .footer-social {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .footer-social a {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        border: 1.5px solid rgba(255,255,255,0.1);
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .footer-social a:hover {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        transform: translateY(-3px);
    }

    /* Footer column headings */
    .footer-col h4 {
        color: #e2e8f0;
        margin-bottom: 20px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        position: relative;
        padding-bottom: 0;
    }
    .footer-col h4::after { display: none; } /* Remove old underline */

    /* Footer links */
    .footer-col ul { list-style: none; padding: 0; margin: 0; }
    .footer-col ul li { margin-bottom: 10px; }
    .footer-col ul li a {
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        font-weight: 400;
        transition: color 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .footer-col ul li a:hover { color: #e2e8f0; }
    .footer-col ul li a::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: #334155;
        flex-shrink: 0;
        transition: background 0.18s ease;
    }
    .footer-col ul li a:hover::before { background: #2563eb; }

    /* Contact info */
    .footer-contact li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
        color: #64748b;
        font-size: 13.5px;
    }
    .footer-contact li i {
        color: #2563eb;
        font-size: 13px;
        margin-top: 2px;
        flex-shrink: 0;
    }
    .footer-contact li::before { display: none; }

    /* Divider */
    .footer-divider {
        border: none;
        border-top: 1px solid rgba(255,255,255,0.06);
        margin: 0;
    }

    /* Copyright bar */
    .footer-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 0;
        gap: 16px;
        flex-wrap: wrap;
    }
    .footer-bottom .copyright {
        color: #475569;
        font-size: 13px;
    }
    .footer-bottom .footer-bottom-links {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .footer-bottom .footer-bottom-links a {
        color: #475569;
        font-size: 13px;
        text-decoration: none;
        transition: color 0.18s ease;
    }
    .footer-bottom .footer-bottom-links a:hover { color: #e2e8f0; }

    /* Back to top button */
    #goto-top-page {
        position: fixed;
        right: 24px;
        bottom: 24px;
        background: #1e293b;
        border: 1.5px solid rgba(255,255,255,0.1);
        color: #94a3b8;
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        cursor: pointer;
        font-size: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        transition: all 0.18s ease;
        z-index: 100;
        text-decoration: none;
    }
    #goto-top-page:hover {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        transform: translateY(-3px);
    }
</style>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-col footer-brand">
                <a href="<?php echo BASE_URL; ?>index.php" class="brand-name">
                    <span class="brand-icon"><i class="fa fa-microchip"></i></span>
                    PC Store
                </a>
                <p>Chuyên cung cấp linh kiện máy tính chính hãng. Giao hàng toàn quốc, bảo hành tận nơi.</p>
                <div class="footer-social">
                    <a href="#" title="Facebook" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
                    <a href="#" title="YouTube" aria-label="YouTube"><i class="fa fa-youtube"></i></a>
                    <a href="#" title="Zalo" aria-label="Zalo"><i class="fa fa-comment"></i></a>
                </div>
            </div>

            <!-- Links Column -->
            <div class="footer-col">
                <h4>Thông tin</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>gioithieu.php">Giới thiệu</a></li>
                    <li><a href="<?php echo BASE_URL; ?>tintuc.php">Tin tức</a></li>
                    <li><a href="<?php echo BASE_URL; ?>lienhe.php">Liên hệ</a></li>
                    <li><a href="<?php echo BASE_URL; ?>sitemap.php">Sitemap</a></li>
                </ul>
            </div>

            <!-- Policy Column -->
            <div class="footer-col">
                <h4>Chính sách</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>chinh_sach.php#privacy">Bảo mật</a></li>
                    <li><a href="<?php echo BASE_URL; ?>dieukhoan.php">Điều khoản</a></li>
                    <li><a href="<?php echo BASE_URL; ?>chinh_sach.php#shipping">Vận chuyển</a></li>
                    <li><a href="<?php echo BASE_URL; ?>doitra.php">Đổi trả</a></li>
                </ul>
            </div>

            <!-- Contact Column -->
            <div class="footer-col">
                <h4>Liên hệ</h4>
                <ul class="footer-contact">
                    <li>
                        <i class="fa fa-map-marker"></i>
                        <span>123 Đường ABC, Hà Nội</span>
                    </li>
                    <li>
                        <i class="fa fa-phone"></i>
                        <span>Hotline: <strong style="color:#e2e8f0;">1900 100x</strong></span>
                    </li>
                    <li>
                        <i class="fa fa-envelope-o"></i>
                        <span>contact@pcstore.vn</span>
                    </li>
                    <li>
                        <i class="fa fa-clock-o"></i>
                        <span>8:00 – 22:00 mỗi ngày</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <hr class="footer-divider">

    <div class="footer-inner">
        <div class="footer-bottom">
            <p class="copyright">&copy; 2026 PC Store &mdash; Đồ án tốt nghiệp CNTT</p>
            <div class="footer-bottom-links">
                <a href="<?php echo BASE_URL; ?>dieukhoan.php">Điều khoản</a>
                <a href="<?php echo BASE_URL; ?>chinh_sach.php#privacy">Bảo mật</a>
                <a href="<?php echo BASE_URL; ?>lienhe.php">Hỗ trợ</a>
            </div>
        </div>
    </div>
</footer>

<a class="fa fa-arrow-up" id="goto-top-page" onclick="window.scrollTo({top:0,behavior:'smooth'});" href="#" aria-label="Lên đầu trang"></a>

<script src="<?php echo AssetHelper::url('public/js/dungchung.js', true); ?>"></script>

<!-- ════════════════════════════════════════════════════════════
     PRODUCT QUICK VIEW MODAL
     ════════════════════════════════════════════════════════════ -->
<div class="qv-overlay" id="qvOverlay" onclick="if(event.target===this)closeQuickView()">
    <div class="qv-modal" id="qvModal">
        <button class="qv-close" onclick="closeQuickView()" aria-label="Đóng">&times;</button>
        <div class="qv-body" id="qvBody">
            <div class="qv-loading" id="qvLoading">
                <div class="qv-spinner"></div>
                <span>Đang tải sản phẩm...</span>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var QV_BASE = '<?php echo BASE_URL; ?>';
    var qvOverlay = document.getElementById('qvOverlay');
    var qvBody    = document.getElementById('qvBody');
    var qvLoading = document.getElementById('qvLoading');

    // Mở Quick View
    window.openQuickView = function(productId) {
        qvOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        qvBody.innerHTML = '<div class="qv-loading" id="qvLoading"><div class="qv-spinner"></div><span>Đang tải sản phẩm...</span></div>';

        fetch(QV_BASE + 'quickview.php?id=' + productId)
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                renderQuickView(data);
            })
            .catch(function(err) {
                qvBody.innerHTML = '<div class="qv-error"><i class="fa fa-exclamation-circle"></i><p>Không thể tải thông tin sản phẩm. Vui lòng thử lại.</p></div>';
            });
    };

    // Đóng Quick View
    window.closeQuickView = function() {
        qvOverlay.classList.remove('open');
        document.body.style.overflow = '';
    };

    // ESC to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && qvOverlay.classList.contains('open')) {
            closeQuickView();
        }
    });

    // Render Quick View content
    function renderQuickView(p) {
        var starsHtml = '';
        var rating = p.average_rating || 0;
        for (var i = 1; i <= 5; i++) {
            starsHtml += '<i class="fa ' + (i <= Math.round(rating) ? 'fa-star qv-stars' : 'fa-star-o qv-stars-empty') + '"></i>';
        }

        var galleryHtml = '';
        if (p.images && p.images.length) {
            var mainImg = escHtml(p.images[0]);
            galleryHtml = '<div class="qv-gallery-main" id="qvGalleryMain">';
            galleryHtml += '<img id="qvMainImg" src="' + mainImg + '" alt="' + escHtml(p.name) + '" onerror="this.src=\'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A//www.w3.org/2000/svg%22 width%3D%22200%22 height%3D%22200%22%3E%3Crect width%3D%22200%22 height%3D%22200%22 fill%3D%22%23f3f4f6%22/%3E%3Ctext x%3D%2250%%22 y%3D%2250%%22 dominant-baseline%3D%22middle%22 text-anchor%3D%22middle%22 fill%3D%22%23aaa%22 font-size%3D%2214%22%3ENo image%3C/text%3E%3C/svg%3E\'">';
            if (!p.in_stock) {
                galleryHtml += '<div class="qv-out-of-stock-badge"><span>🚫 Hết hàng</span></div>';
            }
            galleryHtml += '</div>';

            if (p.images.length > 1) {
                galleryHtml += '<div class="qv-gallery-thumbs" id="qvThumbs">';
                p.images.forEach(function(img, idx) {
                    galleryHtml += '<div class="qv-thumb' + (idx === 0 ? ' active' : '') + '" onclick="qvSetImage(' + idx + ')">';
                    galleryHtml += '<img src="' + escHtml(img) + '" alt="" loading="lazy">';
                    galleryHtml += '</div>';
                });
                galleryHtml += '</div>';
            }
        }

        var priceHtml = '';
        if (p.discount_percent > 0) {
            priceHtml = '<div class="qv-price-row">';
            priceHtml += '<span class="qv-price-final">' + formatMoney(p.final_price) + '₫</span>';
            priceHtml += '<span class="qv-price-original">' + formatMoney(p.price) + '₫</span>';
            priceHtml += '<span class="qv-price-badge">-' + p.discount_percent + '%</span>';
            priceHtml += '</div>';
        } else {
            priceHtml = '<div class="qv-price-row"><span class="qv-price-final">' + formatMoney(p.price) + '₫</span></div>';
        }

        var specsHtml = '';
        if (p.specs && p.specs.length) {
            specsHtml = '<div class="qv-specs">';
            p.specs.forEach(function(s) {
                specsHtml += '<div class="qv-spec-item">';
                specsHtml += '<span class="qv-spec-name">' + escHtml(s.spec_name) + '</span>';
                specsHtml += '<span class="qv-spec-value">' + escHtml(s.spec_value) + '</span>';
                specsHtml += '</div>';
            });
            specsHtml += '<a href="' + escHtml(p.detail_url) + '" class="qv-specs-more">Xem thêm thông số &rarr;</a>';
            specsHtml += '</div>';
        }

        var actionsHtml = '';
        if (p.in_stock) {
            actionsHtml = '<div class="qv-actions">';
            actionsHtml += '<a href="' + escHtml(p.add_to_cart_url) + '" class="qv-btn qv-btn-cart"><i class="fa fa-cart-plus"></i> Thêm vào giỏ</a>';
            actionsHtml += '<a href="' + escHtml(p.buy_now_url) + '" class="qv-btn qv-btn-buy"><i class="fa fa-bolt"></i> Mua ngay</a>';
            actionsHtml += '<a href="' + escHtml(p.detail_url) + '" class="qv-btn qv-btn-detail"><i class="fa fa-external-link"></i> Xem chi tiết đầy đủ</a>';
            actionsHtml += '</div>';
        } else {
            actionsHtml = '<div class="qv-actions">';
            actionsHtml += '<span class="qv-btn qv-btn-disabled"><i class="fa fa-ban"></i> Sản phẩm đã hết hàng</span>';
            actionsHtml += '<a href="' + escHtml(p.detail_url) + '" class="qv-btn qv-btn-detail" style="grid-column:1/-1;"><i class="fa fa-external-link"></i> Xem chi tiết sản phẩm</a>';
            actionsHtml += '</div>';
        }

        var html = '';
        html += '<div class="qv-gallery">' + galleryHtml + '</div>';
        html += '<div class="qv-info">';
        html += '<div class="qv-category">' + escHtml(p.category_name || '') + '</div>';
        html += '<h3 class="qv-name">' + escHtml(p.name) + '</h3>';
        if (p.total_reviews > 0) {
            html += '<div class="qv-rating">' + starsHtml + '<span class="qv-review-count">(' + p.total_reviews + ' đánh giá)</span></div>';
        }
        html += '<hr class="qv-divider">';
        html += priceHtml;
        if (p.description) {
            html += '<p class="qv-description">' + escHtml(p.description) + '</p>';
        }
        if (specsHtml) html += specsHtml;
        html += actionsHtml;
        html += '</div>';

        qvBody.innerHTML = html;

        // Lưu product images để chuyển ảnh
        qvBody._qvImages = p.images || [];
    }

    // Chuyển ảnh gallery trong quick view
    window.qvSetImage = function(idx) {
        var images = (qvBody._qvImages || []);
        if (!images.length) return;
        if (idx < 0) idx = images.length - 1;
        if (idx >= images.length) idx = 0;

        var mainImg = document.getElementById('qvMainImg');
        if (!mainImg) return;
        mainImg.style.opacity = '0';
        setTimeout(function() {
            mainImg.src = images[idx];
            mainImg.style.opacity = '1';
        }, 180);

        var thumbs = document.querySelectorAll('#qvThumbs .qv-thumb');
        thumbs.forEach(function(t, i) {
            t.classList.toggle('active', i === idx);
        });
    };

    function formatMoney(n) {
        return Math.round(n).toLocaleString('vi-VN');
    }

    function escHtml(t) {
        if (!t) return '';
        return String(t).replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
        });
    }
})();
</script>

</body>
</html>