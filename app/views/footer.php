<?php
// AssetHelper autoloaded via PSR-4 + class_alias
// ── Load shop settings (nếu chưa được load bởi header.php) ──────
if (!isset($shopSettings)) {
    $shopSettings = [];
    try {
        if (isset($db) && $db instanceof PDO) {
            $shopSettings = $db->query("SELECT setting_key, setting_value FROM shop_settings")
                               ->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    } catch (Exception $e) { /* silent fail */ }
}
if (!isset($shopName))    $shopName    = htmlspecialchars($shopSettings['shop_name']    ?? 'PC Store');
if (!isset($shopHotline)) $shopHotline = htmlspecialchars($shopSettings['shop_hotline'] ?? '1900 100x');
if (!isset($shopEmail))   $shopEmail   = htmlspecialchars($shopSettings['shop_email']   ?? 'contact@pcstore.vn');
if (!isset($shopAddress)) $shopAddress = htmlspecialchars($shopSettings['shop_address'] ?? '123 Đường ABC, Hà Nội');
if (!isset($shopFb))      $shopFb      = htmlspecialchars($shopSettings['shop_facebook'] ?? '#');
if (!isset($shopYt))      $shopYt      = htmlspecialchars($shopSettings['shop_youtube']  ?? '#');
if (!isset($shopZalo))    $shopZalo    = htmlspecialchars($shopSettings['shop_zalo']     ?? '#');
?>

<style>
    /* ════════════════════════════════════════════════════════════
       PRODUCT QUICK VIEW MODAL — Dark Gaming v3.0
       ════════════════════════════════════════════════════════════ */
    .qv-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(4,5,8,0.72);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
        padding: 16px;
    }
    .qv-overlay.open { display: flex; }

    .qv-modal {
        background: var(--bg-surface);
        border: 1px solid var(--border-strong);
        border-radius: 5px;
        width: 820px;
        max-width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-xl);
        animation: qvIn .24s cubic-bezier(.34,1.4,.64,1);
        overflow: hidden;
        position: relative;
    }
    @keyframes qvIn {
        from { opacity: 0; transform: scale(.95) translateY(20px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .qv-close {
        position: absolute;
        top: 12px; right: 12px;
        z-index: 10;
        width: 34px; height: 34px;
        border-radius: 3px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-strong);
        cursor: pointer;
        font-size: 19px;
        color: var(--txt-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .18s, color .18s, border-color .18s;
        line-height: 1;
    }
    .qv-close:hover { background: var(--danger); border-color: var(--danger); color: #fff; }

    .qv-body {
        display: flex;
        gap: 0;
        overflow-y: auto;
        flex: 1;
    }
    .qv-body::-webkit-scrollbar { width: 4px; }
    .qv-body::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 2px; }

    /* Gallery column */
    .qv-gallery {
        width: 380px;
        flex-shrink: 0;
        padding: 22px 20px;
        background: var(--bg-muted);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    .qv-gallery-main {
        width: 100%;
        aspect-ratio: 1/1;
        border-radius: 3px;
        background: var(--bg-page);
        border: 1px solid var(--border);
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
    .qv-gallery-thumbs::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 2px; }
    .qv-thumb {
        width: 56px;
        height: 56px;
        border-radius: 3px;
        overflow: hidden;
        cursor: pointer;
        border: 1px solid var(--border-strong);
        flex-shrink: 0;
        transition: border-color .2s;
        background: var(--bg-page);
    }
    .qv-thumb:hover { border-color: var(--txt-tertiary); }
    .qv-thumb.active { border-color: var(--accent); }
    .qv-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .qv-out-of-stock-badge {
        position: absolute;
        inset: 12px;
        background: rgba(8,9,13,0.6);
        border-radius: 3px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        pointer-events: none;
    }
    .qv-out-of-stock-badge span {
        background: #c1121f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        padding: 5px 16px;
        border-radius: 2px;
        text-transform: uppercase;
    }

    /* Info column */
    .qv-info {
        flex: 1;
        padding: 22px 22px 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-width: 0;
    }
    .qv-category {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--accent);
        background: var(--accent-light);
        display: inline-block;
        padding: 3px 10px;
        border-radius: 2px;
        width: fit-content;
    }
    .qv-name {
        font-family: var(--font-sans);
        font-size: 17px;
        font-weight: 700;
        color: var(--txt-primary);
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
    .qv-stars { color: var(--warn); font-size: 13px; }
    .qv-stars-empty { color: var(--border-strong); font-size: 13px; }
    .qv-review-count { color: var(--txt-tertiary); font-size: 12px; }

    .qv-divider {
        border: none;
        border-top: 1px solid var(--border);
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
        font-weight: 700;
        color: var(--color-price);
        letter-spacing: 0;
    }
    .qv-price-original {
        font-size: 14px;
        color: var(--txt-tertiary);
        text-decoration: line-through;
    }
    .qv-price-badge {
        background: #c1121f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 2px;
    }
    .qv-description {
        font-size: 13px;
        color: var(--txt-secondary);
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
        background: var(--bg-muted);
        border: 1px solid var(--border);
        border-radius: 3px;
        padding: 10px 14px;
    }
    .qv-spec-item {
        display: flex;
        gap: 6px;
        font-size: 12px;
        line-height: 1.6;
    }
    .qv-spec-name {
        color: var(--txt-tertiary);
        font-weight: 400;
        flex-shrink: 0;
    }
    .qv-spec-name::after { content: ':'; }
    .qv-spec-value {
        color: var(--txt-primary);
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .qv-specs-more {
        font-size: 11px;
        color: var(--accent);
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
        border: 1px solid var(--border-strong);
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
        color: var(--txt-secondary);
        background: var(--bg-surface);
        cursor: pointer;
        transition: border-color .2s, background .2s, color .2s;
    }
    .qv-variant-chip:hover { border-color: var(--accent); background: var(--accent-light); color: var(--accent); }

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
        padding: 11px 14px;
        border-radius: 3px;
        font-family: var(--font-display);
        font-size: 13.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: transform .15s, background .15s, border-color .15s, color .15s;
    }
    .qv-btn:hover { transform: translateY(-1px); }
    .qv-btn-cart {
        background: var(--bg-muted);
        border-color: var(--border-strong);
        color: var(--txt-primary);
    }
    .qv-btn-cart:hover { border-color: var(--accent); color: var(--accent); }
    .qv-btn-buy {
        background: var(--accent);
        border-color: var(--accent);
        color: #14161c;
    }
    .qv-btn-buy:hover { background: var(--accent-hover); border-color: var(--accent-hover); }
    .qv-btn-detail {
        grid-column: 1 / -1;
        background: transparent;
        border-color: var(--border);
        color: var(--txt-secondary);
        font-size: 12.5px;
        padding: 9px;
    }
    .qv-btn-detail:hover { border-color: var(--border-strong); color: var(--txt-primary); }
    .qv-btn-disabled {
        background: var(--bg-muted);
        border-color: var(--border);
        color: var(--txt-tertiary);
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
        color: var(--txt-tertiary);
    }
    .qv-loading .qv-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--border-strong);
        border-top-color: var(--accent);
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
        color: var(--txt-secondary);
        text-align: center;
    }
    .qv-error i { font-size: 40px; color: var(--danger); }

    /* Quick View button on product cards */
    .qv-trigger {
        position: absolute;
        left: 50%;
        bottom: 10px;
        top: auto;
        transform: translateX(-50%) translateY(6px);
        background: rgba(10,12,16,0.88);
        color: #e8ebf2;
        border: 1px solid rgba(255,255,255,0.14);
        border-radius: 3px;
        padding: 6px 14px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s, transform .18s, background .18s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        z-index: 5;
        white-space: nowrap;
        box-shadow: none;
    }
    .product-item:hover .qv-trigger,
    .product-card:hover .qv-trigger,
    .sec-card:hover .qv-trigger,
    .img-wrap:hover .qv-trigger {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) translateY(0);
    }
    .qv-trigger:hover {
        background: var(--accent);
        border-color: var(--accent);
        color: #14161c;
        transform: translateX(-50%) translateY(0);
    }
    .qv-trigger i { font-size: 11px; }

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

    /* ── Footer v3.0 — Dark Gaming ── */
    .footer {
        background: #08090d;
        color: #8a93a5;
        padding: 48px 0 0;
        margin-top: 64px;
        font-family: var(--font-sans);
        font-size: 13.5px;
        line-height: 1.65;
        border-top: 1px solid #1b1f29;
    }
    [data-theme="light"] .footer {
        background: #101320;
        border-top-color: #232a3a;
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
        gap: 44px;
        padding-bottom: 40px;
    }

    @media (max-width: 900px) {
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }
    }
    @media (max-width: 540px) {
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 26px;
        }
        .footer { padding: 40px 0 0; }
    }

    /* Brand column */
    .footer-brand .brand-name {
        font-family: var(--font-display);
        font-size: 22px;
        font-weight: 800;
        color: #f1f3f8;
        letter-spacing: .02em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        margin-bottom: 14px;
    }
    .footer-brand .brand-name .brand-icon {
        width: 34px;
        height: 34px;
        background: var(--accent);
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: #14161c;
        flex-shrink: 0;
    }
    .footer-brand p {
        color: #697181;
        font-size: 13px;
        line-height: 1.65;
        margin-bottom: 18px;
        max-width: 280px;
    }

    /* Social links */
    .footer-social {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .footer-social a {
        width: 34px;
        height: 34px;
        border-radius: 3px;
        background: #14171f;
        border: 1px solid #262c3a;
        color: #8a93a5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .footer-social a:hover {
        background: var(--accent);
        border-color: var(--accent);
        color: #14161c;
        transform: translateY(-2px);
    }

    /* Footer column headings */
    .footer-col h4 {
        color: #f1f3f8;
        margin-bottom: 18px;
        font-family: var(--font-display);
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        position: relative;
        padding-bottom: 8px;
    }
    .footer-col h4::after {
        content: '';
        display: block;
        width: 26px;
        height: 2px;
        background: var(--accent);
        margin-top: 8px;
    }

    /* Footer links */
    .footer-col ul { list-style: none; padding: 0; margin: 0; }
    .footer-col ul li { margin-bottom: 10px; }
    .footer-col ul li a {
        color: #8a93a5;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 400;
        transition: color 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .footer-col ul li a:hover { color: var(--accent); }
    .footer-col ul li a::before {
        content: '';
        display: inline-block;
        width: 5px;
        height: 5px;
        border-radius: 1px;
        background: #333b4a;
        flex-shrink: 0;
        transition: background 0.18s ease;
    }
    .footer-col ul li a:hover::before { background: var(--accent); }

    /* Contact info */
    .footer-contact li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
        color: #8a93a5;
        font-size: 13px;
    }
    .footer-contact li i {
        color: var(--accent);
        font-size: 13px;
        margin-top: 2px;
        flex-shrink: 0;
        width: 14px;
        text-align: center;
    }
    .footer-contact li::before { display: none; }
    .footer-contact strong { color: #f1f3f8 !important; }

    /* Divider */
    .footer-divider {
        border: none;
        border-top: 1px solid #1b1f29;
        margin: 0;
    }

    /* Copyright bar */
    .footer-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 0;
        gap: 14px;
        flex-wrap: wrap;
    }
    .footer-bottom .copyright {
        color: #565e6e;
        font-size: 12.5px;
    }
    .footer-bottom .footer-bottom-links {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .footer-bottom .footer-bottom-links a {
        color: #565e6e;
        font-size: 12.5px;
        text-decoration: none;
        transition: color 0.18s ease;
    }
    .footer-bottom .footer-bottom-links a:hover { color: var(--accent); }

    /* Back to top button */
    #goto-top-page {
        position: fixed;
        right: 24px;
        bottom: 24px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-strong);
        color: var(--txt-secondary);
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        cursor: pointer;
        font-size: 15px;
        box-shadow: var(--shadow-md);
        transition: all 0.18s ease;
        z-index: 100;
        text-decoration: none;
    }
    #goto-top-page:hover {
        background: var(--accent);
        border-color: var(--accent);
        color: #14161c;
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
                    <?php echo $shopName; ?>
                </a>
                <p>Chuyên cung cấp linh kiện máy tính chính hãng. Giao hàng toàn quốc, bảo hành tận nơi.</p>
                <div class="footer-social">
                    <a href="<?php echo $shopFb !== '#' ? $shopFb : '#'; ?>" title="Facebook" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
                    <a href="<?php echo $shopYt !== '#' ? $shopYt : '#'; ?>" title="YouTube" aria-label="YouTube"><i class="fa fa-youtube"></i></a>
                    <?php
                    $zaloHref = '#';
                    if (!empty($shopSettings['shop_zalo']) && $shopSettings['shop_zalo'] !== '#') {
                        $zaloNum = preg_replace('/[^0-9]/', '', $shopSettings['shop_zalo']);
                        $zaloHref = $zaloNum ? 'https://zalo.me/' . $zaloNum : '#';
                    }
                    ?>
                    <a href="<?php echo $zaloHref; ?>" title="Zalo" aria-label="Zalo"><i class="fa fa-comment"></i></a>
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
                        <span><?php echo $shopAddress; ?></span>
                    </li>
                    <li>
                        <i class="fa fa-phone"></i>
                        <span>Hotline: <strong style="color:var(--txt-primary);"><?php echo $shopHotline; ?></strong></span>
                    </li>
                    <li>
                        <i class="fa fa-envelope-o"></i>
                        <span><?php echo $shopEmail; ?></span>
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
            <p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo $shopName; ?> &mdash; Đồ án tốt nghiệp CNTT</p>
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
                galleryHtml += '<div class="qv-out-of-stock-badge"><span>Hết hàng</span></div>';
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


<!-- ════════════════════════════════════════════════════════════
     SIDE BANNERS — Fixed quảng cáo 2 bên trang
     Chiếm toàn bộ khoảng trắng 2 bên content
     ════════════════════════════════════════════════════════════ -->
<style>
    /* ── Ẩn mặc định ── */
    .side-banners { display: none; }

    @media (min-width: 1500px) {
        .side-banners { display: block; }

        /* Mỗi banner column chiếm FULL khoảng trắng 2 bên, nằm tĩnh ở đầu trang */
        body { position: relative; }
        .side-banner {
            position: absolute;
            top: 124px;   /* Ngang hàng với slider ở đầu trang */
            height: 560px; /* Chiều cao vừa vặn cụm slider/banner đầu trang */
            width: calc((100vw - 1240px) / 2 - 16px);
            z-index: 50;  /* Nằm dưới header (z-index: 1000) khi cuộn lên */
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 0;
        }
        .side-banner-left  { left: 8px; }
        .side-banner-right { right: 8px; }

        /* Card flex: chia đôi chiều cao */
        .side-banner-card {
            flex: 1;
            min-height: 0;
            background: var(--bg-surface);
            border-radius: 3px;
            border: 1px solid var(--border);
            overflow: hidden;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: border-color .2s ease, transform .2s ease;
            cursor: pointer;
        }
        .side-banner-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        /* Phần icon chiếm 52% card */
        .side-banner-img {
            flex: 0 0 52%;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 10px;
            text-decoration: none;
            background: var(--bg-muted);
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        .side-banner-img::after {
            content: '';
            position: absolute;
            left: 0; bottom: -1px;
            width: 44px; height: 2px;
            background: var(--sb-accent, var(--accent));
        }
        .banner-emoji {
            font-size: clamp(30px, 2.6vw, 44px);
            line-height: 1;
            color: var(--sb-accent, var(--accent));
        }
        .banner-tag {
            font-size: clamp(8px, 0.6vw, 10px);
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: 2px;
            white-space: nowrap;
            border: 1px solid var(--sb-accent, var(--accent));
            color: var(--sb-accent, var(--accent));
        }
        .banner-product-name {
            font-size: clamp(11px, 0.85vw, 13.5px);
            font-weight: 700;
            line-height: 1.35;
            text-align: center;
            color: var(--txt-primary);
            font-family: var(--font-display);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* Phần text chiếm 48% còn lại */
        .side-banner-body {
            flex: 1;
            padding: 8px 12px 12px;
            width: 100%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }
        .side-banner-title {
            font-size: clamp(13px, 1vw, 17px);
            font-weight: 800;
            color: var(--txt-primary);
            line-height: 1.25;
            font-family: var(--font-display);
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .side-banner-sub {
            font-size: clamp(9.5px, 0.75vw, 11.5px);
            color: var(--txt-tertiary);
            line-height: 1.45;
        }
        .side-banner-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 6px;
            font-size: clamp(9.5px, 0.78vw, 12px);
            font-weight: 700;
            font-family: var(--font-display);
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 7px 14px;
            border-radius: 3px;
            border: 1px solid var(--border-strong);
            color: var(--txt-secondary);
            transition: border-color .15s, color .15s, background .15s;
            text-decoration: none;
            width: calc(100% - 16px);
        }
        .side-banner-card:hover .side-banner-cta {
            border-color: var(--sb-accent, var(--accent));
            color: var(--sb-accent, var(--accent));
        }

        /* ── Màu nhấn từng banner (tông trầm, đồng nhất) ── */
        .banner-left-1  { --sb-accent: #ff7a1b; }
        .banner-left-2  { --sb-accent: #f04438; }
        .banner-right-1 { --sb-accent: #fbbf24; }
        .banner-right-2 { --sb-accent: #34d399; }
    }
</style>

<div class="side-banners" id="sideBanners">

    <!-- ══ BANNER TRÁI ══ -->
    <div class="side-banner side-banner-left">

        <!-- Banner 1: Build PC -->
        <a href="<?php echo BASE_URL; ?>buildpc.php" class="side-banner-card banner-left-1">
            <div class="side-banner-img">
                <span class="banner-emoji"><i class="fa fa-desktop"></i></span>
                <span class="banner-tag">MỚI 2026</span>
                <span class="banner-product-name">Tự Build PC<br>theo ý bạn</span>
            </div>
            <div class="side-banner-body">
                <div class="side-banner-title">Build PC<br>Chuẩn Gu</div>
                <div class="side-banner-sub">Kiểm tra tương thích<br>linh kiện thông minh</div>
                <span class="side-banner-cta">Build ngay <i class="fa fa-arrow-right"></i></span>
            </div>
        </a>

        <!-- Banner 2: Flash Sale -->
        <a href="<?php echo BASE_URL; ?>index.php?sort=discount" class="side-banner-card banner-left-2">
            <div class="side-banner-img">
                <span class="banner-emoji"><i class="fa fa-fire"></i></span>
                <span class="banner-tag">HOT DEAL</span>
                <span class="banner-product-name">Giảm đến<br>50% hôm nay</span>
            </div>
            <div class="side-banner-body">
                <div class="side-banner-title">Sale Khủng<br>Linh Kiện</div>
                <div class="side-banner-sub">CPU · GPU · RAM<br>Giá siêu tốt</div>
                <span class="side-banner-cta">Xem ngay <i class="fa fa-arrow-right"></i></span>
            </div>
        </a>

    </div>

    <!-- ══ BANNER PHẢI ══ -->
    <div class="side-banner side-banner-right">

        <!-- Banner 3: Combo giờ vàng -->
        <a href="<?php echo BASE_URL; ?>combo.php" class="side-banner-card banner-right-1">
            <div class="side-banner-img">
                <span class="banner-emoji"><i class="fa fa-gift"></i></span>
                <span class="banner-tag">GIỜ VÀNG</span>
                <span class="banner-product-name">Combo PC<br>Gaming cực hot</span>
            </div>
            <div class="side-banner-body">
                <div class="side-banner-title">Combo Ưu<br>Đãi Đặc Biệt</div>
                <div class="side-banner-sub">Tiết kiệm hơn<br>mua lẻ 30%</div>
                <span class="side-banner-cta">Xem Combo <i class="fa fa-arrow-right"></i></span>
            </div>
        </a>

        <!-- Banner 4: Bảo hành chính hãng -->
        <a href="<?php echo BASE_URL; ?>chinh_sach.php" class="side-banner-card banner-right-2">
            <div class="side-banner-img">
                <span class="banner-emoji"><i class="fa fa-shield"></i></span>
                <span class="banner-tag">CHÍNH HÃNG</span>
                <span class="banner-product-name">Bảo hành<br>36 tháng</span>
            </div>
            <div class="side-banner-body">
                <div class="side-banner-title">Cam Kết<br>Chính Hãng</div>
                <div class="side-banner-sub">Bảo hành tận nơi<br>Đổi trả 7 ngày</div>
                <span class="side-banner-cta">Xem chính sách <i class="fa fa-arrow-right"></i></span>
            </div>
        </a>

    </div>

</div>

</body>
</html>
