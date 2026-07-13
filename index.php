<?php 
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;
use App\Controllers\BannerController;

$db           = Database::getInstance();
$productModel = new ProductModel($db);

// Đọc category_id và section từ URL
$categoryId   = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$sectionKey   = isset($_GET['section']) ? $_GET['section'] : '';
$categoryName = '';
$pageTitle    = 'Linh kiện mới nhất';
$topSelling   = $featured = $onSale = [];

if ($categoryId > 0) {
    $catStmt = $db->prepare("SELECT name FROM categories WHERE id = :id");
    $catStmt->execute(['id' => $categoryId]);
    $catRow = $catStmt->fetch(PDO::FETCH_ASSOC);
    if ($catRow) {
        $categoryName = $catRow['name'];
        $pageTitle    = 'Danh mục: ' . $categoryName;
    }
    $listProducts = $productModel->getProductsByCategory($categoryId);
    foreach ($listProducts as &$p) { $p['category_name'] = $categoryName; }
    unset($p);
} elseif ($sectionKey) {
    // Trang "Xem tất cả" của từng section
    switch ($sectionKey) {
        case 'latest':
            $pageTitle    = '🆕 Linh kiện mới nhất';
            $listProducts = $productModel->getLatestProducts(100);
            break;
        case 'top_selling':
            $pageTitle    = '🔥 Top lượt mua';
            $listProducts = $productModel->getTopSelling(100);
            break;
        case 'featured':
            $pageTitle    = '⭐ Nổi bật';
            $listProducts = $productModel->getFeatured(100);
            break;
        case 'on_sale':
            $pageTitle    = '🏷️ Đang giảm giá';
            $listProducts = $productModel->getOnSale(100);
            break;
        default:
            $listProducts = $productModel->getLatestProducts(8);
    }
} else {
    $listProducts = $productModel->getLatestProducts(8);
    $topSelling   = $productModel->getTopSelling(10);
    $featured     = $productModel->getFeatured(10);
    $onSale       = $productModel->getOnSale(10);
}

include 'app/views/header.php'; 
?>

<style>
    /* --- CSS SẢN PHẨM --- */
    .product-grid { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; justify-content: center; list-style: none; max-width: 1200px; margin: 0 auto; }
    .product-item { width: 260px; border: 1px solid #eee; padding: 15px; background: #fff; border-radius: 8px; text-align: center; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: flex-start; min-height: 520px; }
    .product-item:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); transform: translateY(-5px); }
    .product-item img { width: 100%; height: 200px; object-fit: contain; margin-bottom: 10px; }
    .product-item h3 { font-size: 16px; color: #333; height: 40px; overflow: hidden; margin: 5px 0; }
    .product-item .price { color: #e10c00; font-weight: bold; font-size: 18px; margin-bottom: 15px; }
    .btn-cart { background: #ff9800; color: white; border: none; padding: 12px 18px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; transition: 0.2s; box-sizing: border-box; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; min-height: 48px; }
    .btn-cart:hover { background: #e68a00; }
    .btn-cart.btn-buy-now { background: #00c2ff; }
    .btn-cart.btn-buy-now:hover { background: #00b0e6; }
    .btn-cart i { margin-right: 8px; font-size: 16px; }
    .action-buttons { display: grid; grid-template-columns: 1fr; gap: 10px; width: 100%; margin-top: 8px; }
    /* Nút bị vô hiệu hoá khi hết hàng */
    .btn-cart.disabled { background: #cbd5e1; color: #94a3b8; cursor: not-allowed; pointer-events: none; }
    .btn-cart.btn-buy-now.disabled { background: #cbd5e1; }

    /* --- CSS MỚI: TÊN DANH MỤC VÀ BANNER BUILD PC --- */
    .product-category { font-size: 12px; color: #6c757d; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 5px; background: #f8f9fa; display: inline-block; padding: 3px 10px; border-radius: 15px; }
    .product-card-link { text-decoration: none; color: inherit; flex: 1; display: flex; flex-direction: column; }
    .product-card-link img { margin-bottom: 14px; }
    .product-card-link h3 { margin: 10px 0 6px; }
    .product-card-link .price { margin: 0 0 10px; }
    .action-buttons { display: grid; grid-template-columns: 1fr; gap: 10px; width: 100%; margin-top: 6px; }

    /* --- OVERLAY HẾT HÀNG --- */
    .img-wrap { position: relative; display: block; }
    .img-wrap img { display: block; width: 100%; }
    .out-of-stock-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.48);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 8px;
        pointer-events: none;
    }
    .out-of-stock-overlay .oos-label {
        background: #ef4444;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.08em;
        padding: 6px 16px;
        border-radius: 999px;
        text-transform: uppercase;
        box-shadow: 0 4px 14px rgba(239,68,68,0.45);
    }
    /* Nhỏ hơn cho horizontal scroll cards */
    .sec-card .out-of-stock-overlay .oos-label { font-size: 11px; padding: 4px 12px; }
    .sec-card .img-wrap img { height: 150px; object-fit: contain; border-radius: 8px; background: #f8fafc; margin-bottom: 0; }
    
    .build-pc-banner { 
        max-width: 1160px; 
        margin: 30px auto 10px auto; 
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); /* Màu nền dải gradient sang trọng */
        color: white; 
        padding: 40px 30px; 
        border-radius: 12px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 10px 20px rgba(0,0,0,0.15); 
    }
    .build-pc-banner-text h2 { margin: 0 0 10px 0; font-size: 32px; color: #00d2ff; }
    .build-pc-banner-text p { margin: 0; font-size: 16px; color: #e0e0e0; }
    
    .btn-buildpc { 
        background: linear-gradient(to right, #ff4b2b, #ff416c); 
        color: white; 
        padding: 15px 35px; 
        text-decoration: none; 
        font-weight: bold; 
        border-radius: 50px; 
        font-size: 18px; 
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(255, 65, 108, 0.4);
        border: 2px solid transparent;
    }
    .btn-buildpc:hover { 
        background: transparent;
        color: #ff416c;
        border: 2px solid #ff416c;
        box-shadow: 0 0 20px rgba(255, 65, 108, 0.6); 
    }

    /* ── Homepage sections ── */
    .hp-section { max-width: 1220px; margin: 0 auto 44px; padding: 0 20px; }
    .hp-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 3px solid var(--sec-color, #288ad6);
    }
    .hp-sec-icon { font-size: 26px; line-height: 1; }
    .hp-sec-title {
        font-size: 21px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: .5px;
        flex: 1;
    }
    /* Horizontal scroll row */
    .sec-scroll-wrap { position: relative; }
    .sec-scroll-row {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        padding: 8px 4px 14px;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .sec-scroll-row::-webkit-scrollbar { height: 5px; }
    .sec-scroll-row::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }
    .sec-card {
        flex: 0 0 210px;
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e8edf3;
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
        overflow: hidden;
        scroll-snap-align: start;
        transition: transform .18s, box-shadow .18s;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .sec-card:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(0,0,0,.12); }
    .sec-card a.sec-card-link { display:block; text-decoration:none; color:inherit; padding: 12px 12px 6px; flex:1; }
    .sec-card img { width:100%; height:150px; object-fit:contain; border-radius:8px; background:#f8fafc; margin-bottom:8px; }
    .sec-card .sec-cat { font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px; }
    .sec-card .sec-name { font-size:13px; font-weight:700; color:#1e293b; line-height:1.35; margin-bottom:6px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .sec-card .sec-price { font-size:15px; font-weight:700; color:#e10c00; }
    .sec-card .sec-price-old { font-size:11px; color:#94a3b8; text-decoration:line-through; }
    .sec-card .sec-actions { display:flex; gap:6px; padding: 8px 10px 12px; }
    .sec-card .sec-btn { flex:1; font-size:11px; font-weight:700; padding:7px 4px; border-radius:8px; text-align:center; text-decoration:none; border:none; cursor:pointer; }
    .sec-btn-cart { background:#2563eb; color:#fff; }
    .sec-btn-cart:hover { background:#1d4ed8; }
    .sec-btn-buy { background:#ff9800; color:#fff; }
    .sec-btn-buy:hover { background:#e68a00; }
    /* Badges */
    .item-badge {
        position: absolute; top: 8px; left: 8px;
        font-size: 11px; font-weight: 700;
        padding: 3px 8px; border-radius: 20px; z-index: 2; white-space: nowrap;
    }
    .badge-sold { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .badge-sale { background: #e10c00; color: #fff; }
    /* Nút Xem tất cả */
    .sec-view-all {
        display: flex; justify-content: center; margin-top: 18px;
    }
    .sec-view-all a {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--sec-color, #2563eb); color: #fff;
        text-decoration: none; font-weight: 700; font-size: 14px;
        padding: 11px 32px; border-radius: 30px;
        box-shadow: 0 6px 20px rgba(0,0,0,.13);
        transition: opacity .15s, transform .15s;
    }
    .sec-view-all a:hover { opacity:.9; transform:translateY(-2px); }
    /* ── SLIDESHOW BANNER ── */
    .hero-slider {
        max-width: 1200px;
        margin: 20px auto 32px;
        border-radius: 18px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 20px 60px rgba(0,0,0,.22);
        background: #0f172a;
        aspect-ratio: 1200/420;
    }
    .hero-track {
        display: flex;
        transition: transform .65s cubic-bezier(.77,0,.175,1);
        height: 100%;
    }
    .hero-slide {
        flex: 0 0 100%;
        position: relative;
        overflow: hidden;
    }
    .hero-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 8s ease;
    }
    .hero-slider.playing .hero-slide.active img {
        transform: scale(1.06);
    }
    /* Gradient overlay */
    .hero-slide-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,.72) 0%, rgba(0,0,0,.25) 55%, transparent 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0 60px;
        gap: 14px;
    }
    .hero-slide-tag {
        display: inline-block;
        background: var(--slide-accent, #6366f1);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 30px;
        width: fit-content;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity .5s .3s, transform .5s .3s;
    }
    .hero-slide-title {
        font-size: clamp(22px, 3.2vw, 42px);
        font-weight: 900;
        color: #fff;
        line-height: 1.15;
        text-shadow: 0 4px 20px rgba(0,0,0,.5);
        margin: 0;
        opacity: 0;
        transform: translateY(24px);
        transition: opacity .55s .45s, transform .55s .45s;
    }
    .hero-slide-sub {
        font-size: clamp(13px, 1.5vw, 17px);
        color: rgba(255,255,255,.85);
        margin: 0;
        max-width: 440px;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity .5s .6s, transform .5s .6s;
    }
    .hero-slide-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--slide-accent, #6366f1);
        color: #fff;
        text-decoration: none;
        font-weight: 800;
        font-size: 14px;
        padding: 13px 28px;
        border-radius: 50px;
        width: fit-content;
        box-shadow: 0 8px 24px rgba(0,0,0,.3);
        transition: transform .2s, box-shadow .2s, opacity .5s .75s;
        opacity: 0;
        transform: translateY(20px);
    }
    .hero-slide-btn:hover { transform: translateY(-2px) !important; box-shadow: 0 14px 36px rgba(0,0,0,.4); }
    /* Active slide animation */
    .hero-slide.active .hero-slide-tag,
    .hero-slide.active .hero-slide-title,
    .hero-slide.active .hero-slide-sub,
    .hero-slide.active .hero-slide-btn {
        opacity: 1;
        transform: translateY(0);
    }
    /* Arrows */
    .hero-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 46px; height: 46px;
        border-radius: 50%;
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(6px);
        border: 1.5px solid rgba(255,255,255,.3);
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: background .2s, transform .2s;
        outline: none;
    }
    .hero-arrow:hover { background: rgba(255,255,255,.3); transform: translateY(-50%) scale(1.1); }
    .hero-arrow-prev { left: 18px; }
    .hero-arrow-next { right: 18px; }
    /* Dots */
    .hero-dots {
        position: absolute;
        bottom: 18px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 10;
    }
    .hero-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,.45);
        cursor: pointer;
        transition: background .3s, width .3s;
        border: none;
        padding: 0;
        outline: none;
    }
    .hero-dot.active { width: 28px; border-radius: 4px; background: #fff; }
    /* Progress bar */
    .hero-progress {
        position: absolute;
        bottom: 0; left: 0;
        height: 3px;
        background: var(--slide-accent, #6366f1);
        z-index: 10;
        animation: heroProgress 5s linear;
        transform-origin: left;
    }
    @keyframes heroProgress { from { width: 0; } to { width: 100%; } }
    @media (max-width: 640px) {
        .hero-slider { border-radius: 0; margin: 0 0 20px; aspect-ratio: 16/9; }
        .hero-slide-overlay { padding: 0 20px; }
        .hero-arrow { width: 34px; height: 34px; font-size: 14px; }
    }
</style>

<div class="main-content">
    
    <!-- ===== HERO SLIDESHOW BANNER ===== -->
    <?php
    // Đọc banners từ database
    $bannerCtrl  = new BannerController($db);
    $dbBanners   = $bannerCtrl->getActiveBanners();

    // Fallback nếu chưa có banner nào trong DB
    if (empty($dbBanners)) {
        $dbBanners = [
            ['id'=>0,'title'=>'LINH KIỆN MÁY TÍNH<br>CHÍNH HÃNG 100%','subtitle'=>'CPU, GPU, RAM, SSD chính hãng — bảo hành chuẩn đến 36 tháng.','tag'=>'🔥 Mới nhất 2026','btn_text'=>'Mua sắm ngay','btn_url'=>'index.php','accent_color'=>'#6366f1','image'=>'banner1.png'],
            ['id'=>0,'title'=>'GIẢM GIÁ SỐC<br>LÊN ĐẾN 40%','subtitle'=>'Chương trình sale mọi ngày — nhanh tay kẻo lỡ!','tag'=>'⚡ Flash Sale','btn_text'=>'Xem khuyến mãi','btn_url'=>'index.php?section=on_sale','accent_color'=>'#e10c00','image'=>'banner2.png'],
            ['id'=>0,'title'=>'TỰ BUILD PC<br>CHUẨN GU CỦA BẠN','subtitle'=>'Kiểm tra tương thích linh kiện thông minh — Dễ dàng & Nhanh chóng.','tag'=>'⚙️ Tự lắp ráp','btn_text'=>'Bắt đầu build','btn_url'=>'buildpc.php','accent_color'=>'#0ea5e9','image'=>'banner3.png'],
        ];
    }

    $slides = $dbBanners;
    ?>
    <div class="hero-slider playing" id="heroSlider">
        <div class="hero-track" id="heroTrack">
            <?php foreach ($slides as $i => $s): ?>
            <div class="hero-slide <?php echo $i===0?'active':''; ?>" style="--slide-accent:<?php echo htmlspecialchars($s['accent_color']);?>">
                <img src="<?php echo BASE_URL . 'public/img/banners/' . htmlspecialchars($s['image']); ?>"
                     alt="<?php echo htmlspecialchars(strip_tags($s['title'])); ?>"
                     loading="<?php echo $i===0?'eager':'lazy'; ?>">
                <div class="hero-slide-overlay" style="--slide-accent:<?php echo htmlspecialchars($s['accent_color']);?>">
                    <?php if (!empty($s['tag'])): ?>
                    <span class="hero-slide-tag"><?php echo htmlspecialchars($s['tag']); ?></span>
                    <?php endif; ?>
                    <h2 class="hero-slide-title"><?php echo nl2br(htmlspecialchars(str_replace(['<br>','<br/>','<br />'], "\n", $s['title']))); ?></h2>
                    <?php if (!empty($s['subtitle'])): ?>
                    <p class="hero-slide-sub"><?php echo htmlspecialchars($s['subtitle']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($s['btn_text']) && !empty($s['btn_url'])): ?>
                    <a href="<?php echo htmlspecialchars(BASE_URL . $s['btn_url']); ?>" class="hero-slide-btn" style="background:<?php echo htmlspecialchars($s['accent_color']); ?>">
                        <?php echo htmlspecialchars($s['btn_text']); ?> <i class="fa fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Arrows -->
        <button class="hero-arrow hero-arrow-prev" id="heroPrev" aria-label="Trước">
            <i class="fa fa-chevron-left"></i>
        </button>
        <button class="hero-arrow hero-arrow-next" id="heroNext" aria-label="Sau">
            <i class="fa fa-chevron-right"></i>
        </button>

        <!-- Dots -->
        <div class="hero-dots" id="heroDots">
            <?php foreach ($slides as $i => $s): ?>
            <button class="hero-dot <?php echo $i===0?'active':''; ?>" data-index="<?php echo $i; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
            <?php endforeach; ?>
        </div>

        <!-- Progress bar -->
        <div class="hero-progress" id="heroProgress" style="--slide-accent:<?php echo htmlspecialchars($slides[0]['accent_color']); ?>"></div>
    </div>


    <div class="build-pc-banner">
        <div class="build-pc-banner-text">
            <h2><i class="fa fa-cogs"></i> TỰ BUILD PC - CHUẨN GU CỦA BẠN</h2>
            <p>Hệ thống tự động kiểm tra tương thích linh kiện thông minh. Dễ dàng - Nhanh chóng!</p>
        </div>
        <a href="<?php echo BASE_URL; ?>buildpc.php" class="btn-buildpc">BẮT ĐẦU BUILD NGAY <i class="fa fa-arrow-right"></i></a>
    </div>

    <?php if ($categoryId > 0 && $categoryName): ?>
        <div style="max-width:1160px;margin:20px auto 0;padding:0 20px;">
            <a href="<?php echo BASE_URL; ?>index.php" style="color:#288ad6;text-decoration:none;font-size:14px;">
                <i class="fa fa-arrow-left"></i> Trang chủ
            </a>
            <span style="color:#94a3b8;margin:0 8px;">/</span>
            <span style="color:#334155;font-weight:600;"><?php echo htmlspecialchars($categoryName); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($sectionKey) || $categoryId > 0): ?>
        <!-- Chế độ grid đầy đủ khi xem section hoặc danh mục -->
        <h3 style="text-align:center;padding-top:24px;font-size:26px;color:#333;text-transform:uppercase;">
            <?php echo htmlspecialchars($pageTitle); ?>
        </h3>
        <ul class="product-grid">
            <?php if(!empty($listProducts)): ?>
                <?php foreach($listProducts as $row): ?>
                    <li class="product-item" style="position:relative;">
                        <?php
                            $svgPlaceholder = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" rx="12" fill="#f1f5f9"/><rect x="150" y="90" width="100" height="80" rx="10" fill="#e2e8f0"/><circle cx="175" cy="115" r="12" fill="#cbd5e1"/><polygon points="150,170 185,130 210,155 230,135 250,170" fill="#cbd5e1"/><text x="200" y="220" font-family="Arial" font-size="14" fill="#94a3b8" text-anchor="middle">Chua co anh</text></svg>';
                            $noImg = 'data:image/svg+xml;base64,' . base64_encode($svgPlaceholder);
                            if (strpos($row['image'], 'data:') === 0) $imageSrc = $row['image'];
                            elseif (!empty($row['image']) && file_exists(__DIR__ . '/public/img/products/' . $row['image']))
                                $imageSrc = BASE_URL . 'public/img/products/' . $row['image'];
                            else $imageSrc = $noImg;
                            $isOutOfStock = ((int)$row['quantity'] <= 0);
                        ?>
                        <?php if (!empty($row['discount_percent'])): ?>
                            <span class="item-badge badge-sale">-<?php echo $row['discount_percent']; ?>%</span>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $row['id']; ?>" class="product-card-link">
                            <span class="img-wrap" style="height:200px;">
                                <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="height:200px;object-fit:contain;">
                                <?php if ($isOutOfStock): ?>
                                <span class="out-of-stock-overlay">
                                    <span class="oos-label">🚫 Hết hàng</span>
                                </span>
                                <?php endif; ?>
                                <button class="qv-trigger" onclick="event.preventDefault();event.stopPropagation();openQuickView(<?php echo $row['id']; ?>);" title="Xem nhanh">
                                    <i class="fa fa-eye"></i> Xem nhanh
                                </button>
                            </span>
                            <div class="product-category"><?php echo htmlspecialchars($row['category_name']); ?></div>
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <?php
                            $hasDiscount = !empty($row['discount_percent']) && $row['discount_percent'] > 0;
                            $displayPrice = $hasDiscount
                                ? round($row['price'] * (1 - $row['discount_percent'] / 100))
                                : $row['price'];
                            ?>
                            <?php if ($hasDiscount): ?>
                                <div class="price" style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                                    <span style="color:#e10c00;font-weight:800;font-size:18px;"><?php echo number_format($displayPrice, 0, ',', '.'); ?> ₫</span>
                                    <span style="text-decoration:line-through;color:#94a3b8;font-size:13px;font-weight:400;"><?php echo number_format($row['price'], 0, ',', '.'); ?> ₫</span>
                                </div>
                            <?php else: ?>
                                <div class="price"><?php echo number_format($row['price'], 0, ',', '.'); ?> ₫</div>
                            <?php endif; ?>
                        </a>
                        <div class="action-buttons">
                            <?php if ($isOutOfStock): ?>
                                <span class="btn-cart disabled"><i class="fa fa-ban"></i> Hết hàng</span>
                                <span class="btn-cart btn-buy-now disabled"><i class="fa fa-ban"></i> Hết hàng</span>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo $row['id']; ?>" class="btn-cart">
                                    <i class="fa fa-cart-plus"></i> Thêm vào giỏ
                                </a>
                                <a href="<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo $row['id']; ?>&checkout=1" class="btn-cart btn-buy-now">
                                    <i class="fa fa-bolt"></i> Mua ngay
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;width:100%;padding:50px;">Chưa có dữ liệu sản phẩm.</p>
            <?php endif; ?>
        </ul>

    <?php else: ?>
        <!-- Trang chủ: các sections hiển thị bên dưới -->
    <?php endif; ?>



    <?php
    // Helper render section
    function renderSection($title, $icon, $color, $sectionSlug, $products, $extraBadge = null) {
        if (empty($products)) return;
        $svgPlaceholder = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" rx="12" fill="#f1f5f9"/><rect x="150" y="90" width="100" height="80" rx="10" fill="#e2e8f0"/><circle cx="175" cy="115" r="12" fill="#cbd5e1"/><polygon points="150,170 185,130 210,155 230,135 250,170" fill="#cbd5e1"/><text x="200" y="220" font-family="Arial" font-size="14" fill="#94a3b8" text-anchor="middle">Chua co anh</text></svg>';
        $noImg = 'data:image/svg+xml;base64,' . base64_encode($svgPlaceholder);
        $uid = 'sec_'.uniqid();
        echo '<div class="hp-section">';
        // Header
        echo '<div class="hp-section-header" style="--sec-color:'.$color.'">';
        echo '<span class="hp-sec-icon">'.$icon.'</span>';
        echo '<h2 class="hp-sec-title">'.$title.'</h2>';
        echo '</div>';
        // Scroll row
        echo '<div class="sec-scroll-wrap"><div class="sec-scroll-row" id="'.$uid.'">';
        foreach ($products as $row) {
            if (strpos($row['image'], 'data:') === 0) $img = $row['image'];
            elseif (!empty($row['image']) && file_exists(__DIR__ . '/public/img/products/' . $row['image']))
                $img = BASE_URL . 'public/img/products/' . $row['image'];
            else $img = $noImg;

            $oos = ((int)($row['quantity'] ?? 0) <= 0);

            echo '<div class="sec-card">';
            if ($extraBadge === 'sold' && !empty($row['total_sold']) && $row['total_sold'] > 0)
                echo '<span class="item-badge badge-sold">🔥 '.$row['total_sold'].' đã bán</span>';
            if ($extraBadge === 'sale' && !empty($row['discount_percent']))
                echo '<span class="item-badge badge-sale">-'.$row['discount_percent'].'%</span>';
            echo '<a class="sec-card-link" href="'.BASE_URL.'chitietsanpham.php?id='.$row['id'].'">';
            // Ảnh + overlay hết hàng
            echo '<span class="img-wrap">';
            echo '<img src="'.$img.'" alt="'.htmlspecialchars($row['name']).'" loading="lazy">';
            if ($oos) echo '<span class="out-of-stock-overlay"><span class="oos-label">🚫 Hết hàng</span></span>';
            echo '<button class="qv-trigger" onclick="event.preventDefault();event.stopPropagation();openQuickView('.$row['id'].');" title="Xem nhanh" style="font-size:11px;padding:6px 12px;"><i class="fa fa-eye"></i> Xem nhanh</button>';
            echo '</span>';
            echo '<div class="sec-cat">'.htmlspecialchars($row['category_name']).'</div>';
            echo '<div class="sec-name">'.htmlspecialchars($row['name']).'</div>';
            if ($extraBadge === 'sale' && !empty($row['sale_price'])) {
                echo '<div class="sec-price">'.number_format($row['sale_price'],0,',','.').' ₫</div>';
                echo '<div class="sec-price-old">'.number_format($row['price'],0,',','.').' ₫</div>';
            } else {
                echo '<div class="sec-price">'.number_format($row['price'],0,',','.').' ₫</div>';
            }
            echo '</a>';
            echo '<div class="sec-actions">';
            if ($oos) {
                echo '<span class="sec-btn" style="flex:2;background:#e2e8f0;color:#94a3b8;cursor:not-allowed;text-align:center;"><i class="fa fa-ban"></i> Hết hàng</span>';
            } else {
                echo '<a class="sec-btn sec-btn-cart" href="'.BASE_URL.'giohang.php?action=add&id='.$row['id'].'"><i class="fa fa-cart-plus"></i> Thêm</a>';
                echo '<a class="sec-btn sec-btn-buy" href="'.BASE_URL.'giohang.php?action=add&id='.$row['id'].'&checkout=1"><i class="fa fa-bolt"></i> Mua</a>';
            }
            echo '</div></div>';
        }
        echo '</div></div>'; // end sec-scroll-row & sec-scroll-wrap
        // Nút Xem tất cả (chỉ hiển thị nếu có sectionSlug)
        if (!empty($sectionSlug)) {
            echo '<div class="sec-view-all" style="--sec-color:'.$color.'">';
            echo '<a href="'.BASE_URL.'index.php?section='.$sectionSlug.'">';
            echo 'Xem tất cả '.$title.' <i class="fa fa-arrow-right"></i></a></div>';
        }
        echo '</div>'; // end hp-section
    }

    // ── Recently Viewed (Đã xem gần đây) ──
    $recentlyViewed = [];
    if (!empty($_SESSION['recently_viewed'])) {
        $recentlyViewed = $productModel->getProductsByIds($_SESSION['recently_viewed']);
    }

    // ── Flash Sale Section (trên cùng, ưu tiên nhất) ──
    if (empty($categoryId) && empty($sectionKey)) {
        try {
            require_once 'admin/controllers/FlashSaleController.php';
            $fsCtrl = new FlashSaleController($db);
            $flashProducts = $fsCtrl->getActiveFlashSaleProducts();
            if (!empty($flashProducts)) {
                $campaignName = $flashProducts[0]['campaign_name'] ?? 'Flash Sale';
                $endTime = $flashProducts[0]['end_time'] ?? '';
                echo '<div class="hp-section">';
                echo '<div class="hp-section-header" style="--sec-color:#ef4444;background:linear-gradient(135deg,#fef2f2,#fee2e2);border-radius:14px;padding:12px 18px;">';
                echo '<span class="hp-sec-icon"><i class="fa fa-bolt" style="color:#dc2626;"></i></span>';
                echo '<h2 class="hp-sec-title" style="color:#dc2626;display:flex;align-items:center;gap:8px;">⚡ ' . htmlspecialchars($campaignName) . '</h2>';
                if ($endTime) {
                    echo '<span class="fs-countdown-badge" data-end="'.$endTime.'" style="font-size:13px;font-weight:700;color:#991b1b;background:rgba(239,68,68,0.12);padding:6px 14px;border-radius:20px;">⏱ <span class="fs-countdown-timer"></span></span>';
                }
                echo '</div>';
                // Render flash products in a scroll row
                echo '<div class="sec-scroll-wrap"><div class="sec-scroll-row">';
                $noImg = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" rx="12" fill="#f1f5f9"/><text x="200" y="160" font-family="Arial" font-size="14" fill="#94a3b8" text-anchor="middle">Chua co anh</text></svg>');
                foreach ($flashProducts as $fp) {
                    $img = (!empty($fp['image']) && strpos($fp['image'], 'data:') !== 0 && file_exists(__DIR__ . '/public/img/products/' . $fp['image']))
                        ? BASE_URL . 'public/img/products/' . $fp['image'] : $noImg;
                    $oos = ((int)($fp['quantity'] ?? 0) <= 0);
                    $flashPrice = (int)$fp['flash_price'];
                    $origPrice = (int)$fp['price'];
                    $soldPct = intval($fp['max_quantity']) > 0 ? round(intval($fp['sold_quantity']) / intval($fp['max_quantity']) * 100) : 0;
                    echo '<div class="sec-card" style="border-color:#fecaca;">';
                    echo '<span class="item-badge badge-sale">-'.htmlspecialchars($fp['flash_discount']).'%</span>';
                    echo '<a class="sec-card-link" href="'.BASE_URL.'chitietsanpham.php?id='.$fp['id'].'">';
                    echo '<span class="img-wrap"><img src="'.$img.'" alt="'.htmlspecialchars($fp['name']).'" loading="lazy">';
                    if ($oos) echo '<span class="out-of-stock-overlay"><span class="oos-label">🚫 Hết hàng</span></span>';
                    echo '</span>';
                    echo '<div class="sec-cat" style="color:#dc2626;">Flash Sale</div>';
                    echo '<div class="sec-name">'.htmlspecialchars($fp['name']).'</div>';
                    echo '<div class="sec-price" style="color:#dc2626;">'.number_format($flashPrice,0,',','.').' ₫</div>';
                    echo '<div class="sec-price-old">'.number_format($origPrice,0,',','.').' ₫</div>';
                    if ($soldPct > 0) {
                        echo '<div style="margin-top:6px;"><div style="height:4px;background:#fee2e2;border-radius:4px;"><div style="height:4px;background:#dc2626;border-radius:4px;width:'.$soldPct.'%;"></div></div>';
                        echo '<div style="font-size:10px;color:#dc2626;margin-top:2px;">🔥 Đã bán '.$fp['sold_quantity'].'/'.($fp['max_quantity'] > 0 ? $fp['max_quantity'] : '∞').'</div></div>';
                    }
                    echo '</a>';
                    echo '<div class="sec-actions">';
                    if ($oos) echo '<span class="sec-btn" style="flex:2;background:#e2e8f0;color:#94a3b8;cursor:not-allowed;"><i class="fa fa-ban"></i> Hết hàng</span>';
                    else {
                        echo '<a class="sec-btn sec-btn-cart" href="'.BASE_URL.'giohang.php?action=add&id='.$fp['id'].'"><i class="fa fa-cart-plus"></i> Thêm</a>';
                        echo '<a class="sec-btn sec-btn-buy" style="background:#dc2626;color:#fff;" href="'.BASE_URL.'giohang.php?action=add&id='.$fp['id'].'&checkout=1"><i class="fa fa-bolt"></i> Mua ngay</a>';
                    }
                    echo '</div></div>';
                }
                echo '</div></div></div>';
            }
        } catch (Exception $e) {
            Logger::warning('Flash sale section render failed', ['error' => $e->getMessage()]);
        }
        ?>

        <script>
        // Flash Sale Countdown
        (function() {
            document.querySelectorAll('.fs-countdown-timer').forEach(function(el) {
                var endTime = new Date(el.closest('.fs-countdown-badge').dataset.end.replace(' ', 'T')).getTime();
                function update() {
                    var now = new Date().getTime();
                    var diff = endTime - now;
                    if (diff <= 0) { el.textContent = 'Đã kết thúc'; return; }
                    var h = Math.floor(diff / (1000*60*60));
                    var m = Math.floor((diff % (1000*60*60)) / (1000*60));
                    var s = Math.floor((diff % (1000*60)) / 1000);
                    el.textContent = h+'h '+m+'m '+s+'s';
                }
                update();
                setInterval(update, 1000);
            });
        })();
        </script>

        <?php
        // Hiển thị section Đã xem gần đây (ưu tiên lên đầu nếu có)
        if (!empty($recentlyViewed)) {
            renderSection('Đã xem gần đây', '👁️', '#8b5cf6', '', $recentlyViewed, null);
        }
        renderSection('Đang giảm giá', '🏷️', '#e10c00', 'on_sale',     $onSale,      'sale');
        renderSection('Linh kiện mới nhất', '🆕', '#288ad6', 'latest',   $listProducts, null);
        renderSection('Top lượt mua',     '🔥', '#ff6b35', 'top_selling', $topSelling,  'sold');
        renderSection('Nổi bật',         '⭐', '#7c3aed', 'featured',   $featured,    null);
    } elseif (!empty($recentlyViewed)) {
        // Trang danh mục / section — hiển thị ngay trên cùng nếu có
        echo '<div class="hp-section">';
        echo '<div class="hp-section-header" style="--sec-color:#8b5cf6">';
        echo '<span class="hp-sec-icon">👁️</span>';
        echo '<h2 class="hp-sec-title">Đã xem gần đây</h2>';
        echo '</div>';
        echo '<div class="sec-scroll-wrap"><div class="sec-scroll-row">';
        $noImg = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" rx="12" fill="#f1f5f9"/><rect x="150" y="90" width="100" height="80" rx="10" fill="#e2e8f0"/><circle cx="175" cy="115" r="12" fill="#cbd5e1"/><polygon points="150,170 185,130 210,155 230,135 250,170" fill="#cbd5e1"/><text x="200" y="220" font-family="Arial" font-size="14" fill="#94a3b8" text-anchor="middle">Chua co anh</text></svg>');
        foreach ($recentlyViewed as $rv):
            $rvImg = (!empty($rv['image']) && strpos($rv['image'], 'data:') !== 0 && file_exists(__DIR__ . '/public/img/products/' . $rv['image']))
                ? BASE_URL . 'public/img/products/' . $rv['image'] : $noImg;
            $rvOos = ((int)($rv['quantity'] ?? 0) <= 0);
            echo '<div class="sec-card">';
            echo '<a class="sec-card-link" href="'.BASE_URL.'chitietsanpham.php?id='.$rv['id'].'">';
            echo '<span class="img-wrap">';
            echo '<img src="'.$rvImg.'" alt="'.htmlspecialchars($rv['name']).'" loading="lazy">';
            if ($rvOos) echo '<span class="out-of-stock-overlay"><span class="oos-label">🚫 Hết hàng</span></span>';
            echo '<button class="qv-trigger" onclick="event.preventDefault();event.stopPropagation();openQuickView('.$rv['id'].');" title="Xem nhanh" style="font-size:11px;padding:6px 12px;"><i class="fa fa-eye"></i> Xem nhanh</button>';
            echo '</span>';
            echo '<div class="sec-cat">'.htmlspecialchars($rv['category_name']).'</div>';
            echo '<div class="sec-name">'.htmlspecialchars($rv['name']).'</div>';
            echo '<div class="sec-price">'.number_format($rv['price'],0,',','.').' ₫</div>';
            echo '</a>';
            echo '<div class="sec-actions">';
            if ($rvOos) {
                echo '<span class="sec-btn" style="flex:2;background:#e2e8f0;color:#94a3b8;cursor:not-allowed;text-align:center;"><i class="fa fa-ban"></i> Hết hàng</span>';
            } else {
                echo '<a class="sec-btn sec-btn-cart" href="'.BASE_URL.'giohang.php?action=add&id='.$rv['id'].'"><i class="fa fa-cart-plus"></i> Thêm</a>';
                echo '<a class="sec-btn sec-btn-buy" href="'.BASE_URL.'giohang.php?action=add&id='.$rv['id'].'&checkout=1"><i class="fa fa-bolt"></i> Mua</a>';
            }
            echo '</div></div>';
        endforeach;
        echo '</div></div>'; // end sec-scroll-row & sec-scroll-wrap
        echo '</div>'; // end hp-section
    }
    ?>

</div>

<?php include 'app/views/footer.php'; ?>

<script>
(function() {
    const slider  = document.getElementById('heroSlider');
    const track   = document.getElementById('heroTrack');
    const slides  = document.querySelectorAll('.hero-slide');
    const dots    = document.querySelectorAll('.hero-dot');
    const progress = document.getElementById('heroProgress');
    const total   = slides.length;
    let current   = 0;
    let timer     = null;
    const DELAY   = 5000;

    const accents = <?php echo json_encode(array_column($slides, 'accent_color')); ?>;

    function goTo(idx, restart = true) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');

        current = (idx + total) % total;

        slides[current].classList.add('active');
        dots[current].classList.add('active');
        track.style.transform = `translateX(-${current * 100}%)`;

        // Update progress bar accent
        progress.style.setProperty('--slide-accent', accents[current] ?? '#6366f1');

        // Restart progress animation
        progress.style.animation = 'none';
        void progress.offsetHeight; // reflow
        progress.style.animation = `heroProgress ${DELAY}ms linear`;

        if (restart) resetTimer();
    }

    function resetTimer() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), DELAY);
    }

    // Init
    resetTimer();

    // Arrows
    document.getElementById('heroPrev').addEventListener('click', () => goTo(current - 1));
    document.getElementById('heroNext').addEventListener('click', () => goTo(current + 1));

    // Dots
    dots.forEach(d => d.addEventListener('click', () => goTo(+d.dataset.index)));

    // Pause on hover
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', resetTimer);

    // Touch / swipe
    let tx = 0;
    slider.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
    slider.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - tx;
        if (Math.abs(dx) > 40) goTo(dx < 0 ? current + 1 : current - 1);
    }, { passive: true });
})();
</script>