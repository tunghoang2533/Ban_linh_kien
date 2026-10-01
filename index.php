<?php 
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;
require_once __DIR__ . '/admin/controllers/BannerController.php';

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
        $pageTitle    = $categoryName;
    }
    $listProducts = $productModel->getProductsByCategory($categoryId);
    foreach ($listProducts as &$p) { $p['category_name'] = $categoryName; }
    unset($p);
} elseif ($sectionKey) {
    // Trang "Xem tất cả" của từng section
    switch ($sectionKey) {
        case 'latest':
            $pageTitle    = 'Linh kiện mới nhất';
            $listProducts = $productModel->getLatestProducts(100);
            break;
        case 'top_selling':
            $pageTitle    = 'Top lượt mua';
            $listProducts = $productModel->getTopSelling(100);
            break;
        case 'featured':
            $pageTitle    = 'Sản phẩm nổi bật';
            $listProducts = $productModel->getFeatured(100);
            break;
        case 'on_sale':
            $pageTitle    = 'Đang giảm giá';
            $listProducts = $productModel->getOnSale(100);
            break;
        case 'recommended':
            $pageTitle    = 'Có thể bạn cũng thích';
            $listProducts = $productModel->getRecommendedProducts(100, $_SESSION['user_id'] ?? null, $_SESSION['recently_viewed'] ?? []);
            break;
        default:
            $listProducts = $productModel->getLatestProducts(8);
    }
} else {
    $listProducts = $productModel->getLatestProducts(8);
    $topSelling   = $productModel->getTopSelling(10);
    $featured     = $productModel->getFeatured(10);
    $onSale       = $productModel->getOnSale(10);
    $recommendedProducts = $productModel->getRecommendedProducts(10, $_SESSION['user_id'] ?? null, $_SESSION['recently_viewed'] ?? []);
}

/**
 * Render 1 card sản phẩm — dùng chung cho grid danh mục và các section trang chủ.
 *
 * @param array $row    1 dòng sản phẩm (name, image, price, sale_price|flash_price,
 *                      discount_percent, quantity, category_name...)
 * @param array $opts   [sold(int), discount(int), oos(bool), price(int), price_old(int)]
 */
function render_pcard(array $row, array $opts = []) {
    $svgPlaceholder = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" rx="12" fill="#f1f5f9"/><rect x="150" y="90" width="100" height="80" rx="10" fill="#e2e8f0"/><circle cx="175" cy="115" r="12" fill="#cbd5e1"/><polygon points="150,170 185,130 210,155 230,135 250,170" fill="#cbd5e1"/><text x="200" y="220" font-family="Arial" font-size="14" fill="#94a3b8" text-anchor="middle">Chua co anh</text></svg>';
    $noImg = 'data:image/svg+xml;base64,' . base64_encode($svgPlaceholder);

    if (!empty($row['image']) && strpos($row['image'], 'data:') === 0) {
        $img = $row['image'];
    } elseif (!empty($row['image']) && file_exists(__DIR__ . '/public/img/products/' . $row['image'])) {
        $img = BASE_URL . 'public/img/products/' . $row['image'];
    } else {
        $img = $noImg;
    }

    $name    = htmlspecialchars($row['name']);
    $href    = BASE_URL . 'chitietsanpham.php?id=' . (int)$row['id'];
    $oos     = $opts['oos'] ?? ((int)($row['quantity'] ?? 0) <= 0);
    $price   = $opts['price']   ?? (int)$row['price'];
    $priceOld = $opts['price_old'] ?? null;
    $discount = (int)($opts['discount'] ?? ($row['discount_percent'] ?? 0));
    $sold    = (int)($opts['sold'] ?? ($row['total_sold'] ?? 0));
    $onSale  = $priceOld !== null && $priceOld > $price;
    ?>
    <div class="pcard<?php echo $onSale ? ' pcard--sale' : ''; ?>">
        <a class="pcard-media" href="<?php echo $href; ?>">
            <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" loading="lazy">
            <?php if ($discount > 0): ?>
                <span class="pcard-badge pcard-badge--sale">-<?php echo $discount; ?>%</span>
            <?php elseif ($sold > 0): ?>
                <span class="pcard-badge pcard-badge--sold"><i class="fa fa-line-chart"></i> <?php echo $sold; ?> đã bán</span>
            <?php endif; ?>
            <?php if ($oos): ?>
                <span class="pcard-oos"><i class="fa fa-ban"></i>Hết hàng</span>
            <?php endif; ?>
            <button type="button" class="qv-trigger" onclick="event.preventDefault();event.stopPropagation();openQuickView(<?php echo (int)$row['id']; ?>);" title="Xem nhanh">
                <i class="fa fa-eye"></i> Xem nhanh
            </button>
        </a>
        <div class="pcard-body">
            <?php if (!empty($row['category_name'])): ?>
                <div class="pcard-cat"><?php echo htmlspecialchars($row['category_name']); ?></div>
            <?php endif; ?>
            <h3 class="pcard-name"><a href="<?php echo $href; ?>"><?php echo $name; ?></a></h3>
            <div class="pcard-price">
                <span class="pcard-price-now"><?php echo number_format($price, 0, ',', '.'); ?> ₫</span>
                <?php if ($onSale): ?>
                    <span class="pcard-price-old"><?php echo number_format($priceOld, 0, ',', '.'); ?> ₫</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($opts['soldbar'])): ?>
                <?php
                    $maxQty = (int)($opts['soldbar']['max'] ?? 0);
                    $soldQty = (int)($opts['soldbar']['sold'] ?? 0);
                    $pct = $maxQty > 0 ? min(100, (int)round($soldQty / $maxQty * 100)) : 0;
                ?>
                <div class="pcard-soldbar">
                    <div class="pcard-soldbar-track"><div class="pcard-soldbar-fill" style="width:<?php echo $pct; ?>%"></div></div>
                    <span class="pcard-soldbar-text">Đã bán <?php echo $soldQty; ?>/<?php echo $maxQty > 0 ? $maxQty : '∞'; ?></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="pcard-actions">
            <?php if ($oos): ?>
                <span class="btn btn-ghost btn-disabled"><i class="fa fa-ban"></i> Hết hàng</span>
            <?php else: ?>
                <a class="btn btn-ghost pcard-btn-cart" href="<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo (int)$row['id']; ?>" title="Thêm vào giỏ" aria-label="Thêm vào giỏ">
                    <i class="fa fa-cart-plus"></i>
                </a>
                <a class="btn btn-primary" href="<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo (int)$row['id']; ?>&checkout=1">
                    <i class="fa fa-bolt"></i><span>Mua ngay</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render 1 section trang chủ: header (icon + tiêu đề + link xem tất cả) + hàng card cuộn ngang.
 */
function renderSection($title, $icon, $sectionSlug, $products, $extraBadge = null) {
    if (empty($products)) return;
    echo '<section class="hp-section">';
    echo '<header class="hp-section-head">';
    echo '<span class="hp-sec-ico"><i class="fa ' . $icon . '"></i></span>';
    echo '<h2>' . htmlspecialchars($title) . '</h2>';
    if (!empty($sectionSlug)) {
        echo '<a class="hp-view-all" href="' . BASE_URL . 'index.php?section=' . htmlspecialchars($sectionSlug) . '">Xem tất cả <i class="fa fa-angle-right"></i></a>';
    }
    echo '</header>';
    echo '<div class="hp-scroll-row">';
    foreach ($products as $row) {
        $price = null; $priceOld = null;
        if ($extraBadge === 'sale' && !empty($row['sale_price'])) {
            $price = (int)$row['sale_price'];
            $priceOld = (int)$row['price'];
        }
        render_pcard($row, [
            'discount' => (int)($row['discount_percent'] ?? 0),
            'sold'     => ($extraBadge === 'sold') ? (int)($row['total_sold'] ?? 0) : 0,
            'price'    => $price,
            'price_old'=> $priceOld,
        ]);
    }
    echo '</div>';
    echo '</section>';
}

include 'app/views/header.php';
?>

<link rel="stylesheet" href="<?php echo AssetHelper::url('public/css/home.css', true); ?>">

<div class="main-content home-shell">
  <div class="container">

    <?php
    // ════════════════════════════════════════════════════════════
    //  HÀNG HERO: slider banner (trái) + card Build PC (phải)
    // ════════════════════════════════════════════════════════════
    $bannerCtrl = new BannerController($db);
    $dbBanners  = $bannerCtrl->getActiveBanners();

    // Fallback nếu chưa có banner nào trong DB
    if (empty($dbBanners)) {
        $dbBanners = [
            ['id'=>0,'title'=>'Linh kiện máy tính<br>chính hãng 100%','subtitle'=>'CPU, GPU, RAM, SSD chính hãng — bảo hành chuẩn đến 36 tháng.','tag'=>'Mới về','btn_text'=>'Mua sắm ngay','btn_url'=>'index.php','accent_color'=>'#2563eb','image'=>'banner1.png'],
            ['id'=>0,'title'=>'Giảm giá sốc<br>lên đến 40%','subtitle'=>'Chương trình sale mọi ngày — nhanh tay kẻo lỡ.','tag'=>'Khuyến mãi','btn_text'=>'Xem khuyến mãi','btn_url'=>'index.php?section=on_sale','accent_color'=>'#dc2626','image'=>'banner2.png'],
            ['id'=>0,'title'=>'Tự build PC<br>theo chuẩn gu của bạn','subtitle'=>'Kiểm tra tương thích linh kiện thông minh — dễ dàng và nhanh chóng.','tag'=>'Công cụ','btn_text'=>'Bắt đầu build','btn_url'=>'buildpc.php','accent_color'=>'#f59e0b','image'=>'banner3.png'],
        ];
    }
    $slides = $dbBanners;
    ?>

    <section class="home-hero">
        <div class="hero-slider" id="heroSlider">
            <div class="hero-track" id="heroTrack">
                <?php foreach ($slides as $i => $s): ?>
                <div class="hero-slide<?php echo $i === 0 ? ' active' : ''; ?>" style="--slide-accent:<?php echo htmlspecialchars($s['accent_color']); ?>">
                    <img src="<?php echo BASE_URL . 'public/img/banners/' . htmlspecialchars($s['image']); ?>"
                         alt="<?php echo htmlspecialchars(strip_tags($s['title'])); ?>"
                         loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                    <div class="hero-slide-overlay">
                        <?php if (!empty($s['tag'])): ?>
                        <span class="hero-slide-tag"><?php echo htmlspecialchars($s['tag']); ?></span>
                        <?php endif; ?>
                        <h2 class="hero-slide-title"><?php echo nl2br(htmlspecialchars(str_replace(['<br>','<br/>','<br />'], "\n", $s['title']))); ?></h2>
                        <?php if (!empty($s['subtitle'])): ?>
                        <p class="hero-slide-sub"><?php echo htmlspecialchars($s['subtitle']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($s['btn_text']) && !empty($s['btn_url'])): ?>
                        <a href="<?php echo htmlspecialchars(BASE_URL . $s['btn_url']); ?>" class="hero-slide-btn">
                            <?php echo htmlspecialchars($s['btn_text']); ?> <i class="fa fa-angle-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="hero-arrow hero-arrow-prev" id="heroPrev" aria-label="Trước"><i class="fa fa-chevron-left"></i></button>
            <button type="button" class="hero-arrow hero-arrow-next" id="heroNext" aria-label="Sau"><i class="fa fa-chevron-right"></i></button>

            <div class="hero-dots" id="heroDots">
                <?php foreach ($slides as $i => $s): ?>
                <button type="button" class="hero-dot<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
                <?php endforeach; ?>
            </div>

            <div class="hero-progress" id="heroProgress" style="--slide-accent:<?php echo htmlspecialchars($slides[0]['accent_color'] ?: '#2563eb'); ?>"></div>
        </div>

        <aside class="hero-aside">
            <div class="buildpc-promo">
                <span class="buildpc-promo-icon"><i class="fa fa-microchip"></i></span>
                <h3>Tự build PC theo cấu hình của bạn</h3>
                <p>Chọn linh kiện từng bước, hệ thống tự kiểm tra tương thích trước khi thêm vào giỏ.</p>
                <ul class="buildpc-promo-list">
                    <li><i class="fa fa-check"></i> Lựa chọn đồng bộ, đúng chuẩn chân cắm (socket)</li>
                    <li><i class="fa fa-check"></i> Tự động kiểm tra tương thích linh kiện</li>
                    <li><i class="fa fa-check"></i> Thêm cả cấu hình vào giỏ trong 1 lần</li>
                </ul>
                <a href="<?php echo BASE_URL; ?>buildpc.php" class="btn btn-primary">
                    <i class="fa fa-wrench"></i> Build PC ngay
                </a>
            </div>
            <a href="<?php echo BASE_URL; ?>lienhe.php" class="hero-support">
                <span class="hero-support-icon"><i class="fa fa-headphones"></i></span>
                <span>
                    <small>Hỗ trợ tư vấn</small>
                    <strong><?php echo $shopHotline; ?></strong>
                </span>
            </a>
        </aside>
    </section>

    <?php if ($categoryId > 0 && $categoryName): ?>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo BASE_URL; ?>index.php"><i class="fa fa-home"></i> Trang chủ</a>
            <span class="sep"><i class="fa fa-angle-right"></i></span>
            <span class="current"><?php echo htmlspecialchars($categoryName); ?></span>
        </nav>
    <?php endif; ?>

    <?php if (!empty($sectionKey) || $categoryId > 0): ?>
        <!-- Chế độ grid đầy đủ khi xem section hoặc danh mục -->
        <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <ul class="pcard-grid">
            <?php if (!empty($listProducts)): ?>
                <?php foreach ($listProducts as $row): ?>
                    <li>
                        <?php
                            $hasDiscount = !empty($row['discount_percent']) && $row['discount_percent'] > 0;
                            render_pcard($row, [
                                'price'     => $hasDiscount ? (int)round($row['price'] * (1 - $row['discount_percent'] / 100)) : (int)$row['price'],
                                'price_old' => $hasDiscount ? (int)$row['price'] : null,
                                'discount'  => $hasDiscount ? (int)$row['discount_percent'] : 0,
                            ]);
                        ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="pcard-empty">Chưa có dữ liệu sản phẩm.</li>
            <?php endif; ?>
        </ul>

    <?php else: ?>

        <?php
        // ── Recently Viewed (Đã xem gần đây) ──
        $recentlyViewed = [];
        if (!empty($_SESSION['recently_viewed'])) {
            $recentlyViewed = $productModel->getProductsByIds($_SESSION['recently_viewed']);
        }

        // ── Flash Sale Section (trên cùng, ưu tiên nhất) ──
        try {
            require_once 'admin/controllers/FlashSaleController.php';
            $fsCtrl = new FlashSaleController($db);
            $flashProducts = $fsCtrl->getActiveFlashSaleProducts();
            if (!empty($flashProducts)) {
                $campaignName = $flashProducts[0]['campaign_name'] ?? 'Flash Sale';
                $endTime     = $flashProducts[0]['end_time'] ?? '';
                echo '<section class="hp-section hp-flash">';
                echo '<header class="hp-section-head">';
                echo '<span class="hp-sec-ico"><i class="fa fa-bolt"></i></span>';
                echo '<h2>' . htmlspecialchars($campaignName) . '</h2>';
                if ($endTime) {
                    echo '<span class="flash-countdown" data-end="' . htmlspecialchars($endTime) . '"><i class="fa fa-clock-o"></i> <span class="flash-countdown-timer"></span></span>';
                }
                echo '</header>';
                echo '<div class="hp-scroll-row">';
                foreach ($flashProducts as $fp) {
                    render_pcard($fp, [
                        'oos'      => ((int)($fp['quantity'] ?? 0) <= 0),
                        'price'    => (int)$fp['flash_price'],
                        'price_old'=> (int)$fp['price'],
                        'discount' => (int)$fp['flash_discount'],
                        'soldbar'  => [
                            'sold' => (int)($fp['sold_quantity'] ?? 0),
                            'max'  => (int)($fp['max_quantity'] ?? 0),
                        ],
                    ]);
                }
                echo '</div>';
                echo '</section>';
            }
        } catch (Exception $e) {
            Logger::warning('Flash sale section render failed', ['error' => $e->getMessage()]);
        }
        ?>

        <script>
        // Flash Sale Countdown
        (function() {
            document.querySelectorAll('.flash-countdown').forEach(function(el) {
                var endTime = new Date(el.dataset.end.replace(' ', 'T')).getTime();
                var timer = el.querySelector('.flash-countdown-timer');
                function update() {
                    var diff = endTime - new Date().getTime();
                    if (diff <= 0) { timer.textContent = 'Đã kết thúc'; return; }
                    var h = Math.floor(diff / (1000*60*60));
                    var m = Math.floor((diff % (1000*60*60)) / (1000*60));
                    var s = Math.floor((diff % (1000*60)) / 1000);
                    timer.textContent = h + 'h ' + m + 'm ' + s + 's';
                }
                update();
                setInterval(update, 1000);
            });
        })();
        </script>

        <?php
        if (!empty($recommendedProducts)) {
            renderSection('Có thể bạn cũng thích', 'fa-thumbs-up', 'recommended', $recommendedProducts);
        }
        if (!empty($recentlyViewed)) {
            renderSection('Đã xem gần đây', 'fa-history', '', $recentlyViewed);
        }
        renderSection('Đang giảm giá',    'fa-tags',        'on_sale',     $onSale,      'sale');
        renderSection('Linh kiện mới nhất','fa-cubes',      'latest',      $listProducts, null);
        renderSection('Top lượt mua',     'fa-line-chart',  'top_selling', $topSelling,  'sold');
        renderSection('Nổi bật',          'fa-star',        'featured',    $featured,    null);
        ?>
    <?php endif; ?>

  </div><!-- /.container -->
</div><!-- /.home-shell -->

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
        progress.style.setProperty('--slide-accent', accents[current] ?? '#2563eb');

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
