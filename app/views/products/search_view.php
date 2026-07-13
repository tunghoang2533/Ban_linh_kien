<?php
// Helper: build URL giữ nguyên params, chỉ đổi 1 param
function searchUrl($overrides = []) {
    $params = [
        'key'   => $_GET['key']   ?? '',
        'cat'   => $_GET['cat']   ?? '',
        'brand' => $_GET['brand'] ?? '',
        'pmin'  => $_GET['pmin']  ?? '',
        'pmax'  => $_GET['pmax']  ?? '',
        'sort'  => $_GET['sort']  ?? 'newest',
        'page'  => $_GET['page']  ?? 1,
    ];
    foreach ($overrides as $k => $v) {
        $params[$k] = $v;
    }
    // Xóa params rỗng để URL gọn
    $params = array_filter($params, fn($v) => $v !== '' && $v !== '0' && $v !== 0);
    return BASE_URL . 'search.php?' . http_build_query($params);
}

$keyword    = trim($_GET['key']    ?? '');
$categoryId = (int)($_GET['cat']   ?? 0);
$brandId    = (int)($_GET['brand'] ?? 0);
$priceMin   = $_GET['pmin']        ?? '';
$priceMax   = $_GET['pmax']        ?? '';
$sortBy     = $_GET['sort']        ?? 'newest';
$currentPage = (int)($_GET['page'] ?? 1);

$sortLabels = [
    'newest'     => 'Mới nhất',
    'price_asc'  => 'Giá thấp → cao',
    'price_desc' => 'Giá cao → thấp',
    'bestsell'   => 'Bán chạy nhất',
    'rating'     => 'Đánh giá cao nhất',
];

$priceRanges = [
    ''              => 'Tất cả mức giá',
    '0-2000000'     => 'Dưới 2 triệu',
    '2000000-5000000' => '2 – 5 triệu',
    '5000000-10000000' => '5 – 10 triệu',
    '10000000-20000000' => '10 – 20 triệu',
    '20000000-'     => 'Trên 20 triệu',
];
$currentRange = '';
if ($priceMin !== '' || $priceMax !== '') {
    $currentRange = $priceMin . '-' . $priceMax;
}

$defaultImg = 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200" fill="%23f3f4f6"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23aaa" font-size="14">No image</text></svg>';
?>

<style>
/* ===== SEARCH PAGE ===== */
.search-page { max-width: 1200px; margin: 32px auto; padding: 0 20px 60px; }

/* Breadcrumb */
.search-breadcrumb {
    font-size: 13px; color: #64748b; margin-bottom: 20px;
    display: flex; align-items: center; gap: 6px;
}
.search-breadcrumb a { color: #3b82f6; text-decoration: none; }
.search-breadcrumb a:hover { text-decoration: underline; }

/* Search hero banner */
.search-hero {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0ea5e9 100%);
    border-radius: 20px;
    padding: 32px 36px;
    margin-bottom: 28px;
    color: white;
    position: relative;
    overflow: hidden;
}
.search-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.search-hero h1 { font-size: 22px; font-weight: 700; margin: 0 0 6px; position: relative; }
.search-hero p  { font-size: 14px; opacity: 0.85; margin: 0; position: relative; }
.search-hero form {
    display: flex; gap: 0; margin-top: 20px; position: relative;
    max-width: 600px;
}
.search-hero form input {
    flex: 1; padding: 13px 18px; border: none; border-radius: 12px 0 0 12px;
    font-size: 15px; font-family: inherit; outline: none;
    background: rgba(255,255,255,0.97); color: #1e293b;
}
.search-hero form button {
    padding: 13px 24px; background: #f59e0b; border: none; border-radius: 0 12px 12px 0;
    color: white; font-size: 15px; font-weight: 700; cursor: pointer;
    transition: background .2s; display: flex; align-items: center; gap: 8px;
}
.search-hero form button:hover { background: #d97706; }

/* Layout */
.search-layout { display: grid; grid-template-columns: 260px 1fr; gap: 24px; }

/* Sidebar */
.search-sidebar { display: flex; flex-direction: column; gap: 16px; }
.filter-card {
    background: white; border-radius: 16px; padding: 20px;
    box-shadow: 0 2px 12px rgba(15,23,42,0.06);
    border: 1px solid #e8eef4;
}
.filter-card h3 {
    font-size: 14px; font-weight: 700; color: #1e293b;
    margin: 0 0 14px; padding-bottom: 10px;
    border-bottom: 2px solid #f1f5f9;
    display: flex; align-items: center; gap: 7px;
}
.filter-card h3 i { color: #3b82f6; font-size: 13px; }

/* Category / Brand list */
.filter-list { list-style: none; margin: 0; padding: 0; }
.filter-list li a {
    display: flex; align-items: center; justify-content: space-between;
    padding: 7px 10px; border-radius: 8px; text-decoration: none;
    font-size: 13px; color: #475569; transition: background .15s, color .15s;
}
.filter-list li a:hover { background: #eff6ff; color: #2563eb; }
.filter-list li a.active { background: #dbeafe; color: #1d4ed8; font-weight: 700; }
.filter-list li a .count {
    font-size: 11px; background: #f1f5f9; color: #64748b;
    padding: 1px 7px; border-radius: 20px; font-weight: 600;
}
.filter-list li a.active .count { background: #bfdbfe; color: #1d4ed8; }

/* Price range */
.price-range-list { display: flex; flex-direction: column; gap: 4px; }
.price-range-item {
    padding: 7px 10px; border-radius: 8px; font-size: 13px;
    color: #475569; cursor: pointer; text-decoration: none; display: block;
    transition: background .15s, color .15s;
}
.price-range-item:hover { background: #eff6ff; color: #2563eb; }
.price-range-item.active { background: #dbeafe; color: #1d4ed8; font-weight: 700; }

/* Reset filter */
.filter-reset {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; background: #fef2f2; color: #dc2626;
    border-radius: 8px; font-size: 12.5px; font-weight: 600;
    text-decoration: none; border: 1px solid #fecaca;
    transition: background .15s;
}
.filter-reset:hover { background: #fee2e2; }

/* Main content */
.search-main {}
.search-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 18px; flex-wrap: wrap; gap: 12px;
}
.search-toolbar .result-info { font-size: 14px; color: #64748b; }
.search-toolbar .result-info strong { color: #1e293b; }
.sort-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
.sort-tab {
    padding: 6px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
    text-decoration: none; color: #64748b; background: #f1f5f9;
    transition: background .15s, color .15s; border: 1.5px solid transparent;
}
.sort-tab:hover { background: #e2e8f0; color: #1e293b; }
.sort-tab.active { background: #3b82f6; color: white; border-color: #3b82f6; }

/* Product grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(195px, 1fr));
    gap: 16px;
}
.product-card {
    background: white; border-radius: 16px; overflow: hidden;
    box-shadow: 0 2px 10px rgba(15,23,42,0.06);
    border: 1.5px solid #e8eef4;
    transition: transform .2s, box-shadow .2s;
    position: relative; display: flex; flex-direction: column;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(15,23,42,0.13);
    border-color: #bfdbfe;
}
.product-card-img-wrap {
    position: relative; padding-top: 75%; background: #f8fafc; overflow: hidden;
}
.product-card-img-wrap img {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: contain; padding: 10px; transition: transform .3s;
}
.product-card:hover .product-card-img-wrap img { transform: scale(1.06); }
.badge-discount {
    position: absolute; top: 10px; left: 10px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white; font-size: 11px; font-weight: 700;
    padding: 3px 8px; border-radius: 20px;
}
.badge-hot {
    position: absolute; top: 10px; right: 10px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white; font-size: 11px; font-weight: 700;
    padding: 3px 8px; border-radius: 20px;
}
.badge-out {
    position: absolute; top: 10px; left: 10px;
    background: #94a3b8; color: white; font-size: 11px; font-weight: 700;
    padding: 3px 8px; border-radius: 20px;
}
.product-card-body { padding: 12px 14px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
.product-card-cat { font-size: 11px; color: #3b82f6; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.product-card-name {
    font-size: 13.5px; font-weight: 700; color: #1e293b;
    line-height: 1.4; flex: 1;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.product-card-rating { display: flex; align-items: center; gap: 4px; font-size: 12px; }
.stars { color: #f59e0b; }
.stars-empty { color: #e2e8f0; }
.rating-count { color: #94a3b8; font-size: 11px; }
.product-card-price { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.price-final { font-size: 16px; font-weight: 800; color: #dc2626; }
.price-original { font-size: 12px; color: #94a3b8; text-decoration: line-through; }
.product-card-footer { padding: 0 14px 14px; display: flex; gap: 8px; }
.btn-addcart {
    flex: 1; padding: 9px 0; background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white; border: none; border-radius: 10px; font-size: 13px; font-weight: 700;
    cursor: pointer; text-align: center; text-decoration: none; display: block;
    transition: opacity .15s; line-height: 1.2;
}
.btn-addcart:hover { opacity: 0.88; }
.btn-addcart.disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; pointer-events: none; }
.btn-detail {
    padding: 9px 11px; background: #f1f5f9; color: #475569;
    border: none; border-radius: 10px; font-size: 13px; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: background .15s;
}
.btn-detail:hover { background: #e2e8f0; }

/* Pagination */
.pagination {
    display: flex; align-items: center; justify-content: center;
    gap: 6px; margin-top: 36px; flex-wrap: wrap;
}
.page-btn {
    min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600;
    border: 1.5px solid #e2e8f0; color: #475569; background: white;
    transition: all .15s;
}
.page-btn:hover { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
.page-btn.active { background: #3b82f6; color: white; border-color: #3b82f6; }
.page-btn.disabled { opacity: 0.4; pointer-events: none; }

/* Empty state */
.search-empty {
    text-align: center; padding: 64px 20px;
    background: white; border-radius: 20px;
    border: 1.5px dashed #e2e8f0;
}
.search-empty .empty-icon { font-size: 56px; margin-bottom: 16px; }
.search-empty h3 { font-size: 20px; color: #1e293b; margin: 0 0 8px; }
.search-empty p { font-size: 14px; color: #64748b; margin: 0 0 20px; }
.search-empty a {
    display: inline-block; padding: 10px 24px; background: #3b82f6;
    color: white; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px;
}

/* Active filters pills */
.active-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
.filter-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; background: #dbeafe; color: #1d4ed8;
    border-radius: 20px; font-size: 12px; font-weight: 600;
    text-decoration: none; border: 1px solid #bfdbfe;
}
.filter-pill:hover { background: #bfdbfe; }
.filter-pill i { font-size: 10px; }

@media (max-width: 768px) {
    .search-layout { grid-template-columns: 1fr; }
    .search-sidebar { display: none; }
    .product-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="search-page">
    <!-- Breadcrumb -->
    <div class="search-breadcrumb">
        <a href="<?php echo BASE_URL; ?>index.php"><i class="fa fa-home"></i> Trang chủ</a>
        <i class="fa fa-angle-right"></i>
        <span>Tìm kiếm<?php echo $keyword ? ': "' . htmlspecialchars($keyword) . '"' : ''; ?></span>
    </div>

    <!-- Search Hero -->
    <div class="search-hero">
        <h1><i class="fa fa-search"></i> Tìm kiếm linh kiện</h1>
        <p>Tìm kiếm trong hàng nghìn sản phẩm linh kiện máy tính chính hãng</p>
        <form action="<?php echo BASE_URL; ?>search.php" method="GET">
            <input type="text" name="key" value="<?php echo htmlspecialchars($keyword); ?>"
                   placeholder="Tên sản phẩm, thương hiệu, danh mục..." autofocus>
            <?php if ($categoryId): ?><input type="hidden" name="cat" value="<?php echo $categoryId; ?>"><?php endif; ?>
            <?php if ($brandId): ?><input type="hidden" name="brand" value="<?php echo $brandId; ?>"><?php endif; ?>
            <button type="submit"><i class="fa fa-search"></i> Tìm kiếm</button>
        </form>
    </div>

    <div class="search-layout">
        <!-- ===== SIDEBAR ===== -->
        <aside class="search-sidebar">
            <!-- Danh mục -->
            <div class="filter-card">
                <h3><i class="fa fa-th-large"></i> Danh mục</h3>
                <ul class="filter-list">
                    <li>
                        <a href="<?php echo searchUrl(['cat' => '', 'page' => 1]); ?>"
                           class="<?php echo !$categoryId ? 'active' : ''; ?>">
                            Tất cả danh mục
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?php echo searchUrl(['cat' => $cat['id'], 'page' => 1]); ?>"
                           class="<?php echo $categoryId == $cat['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Thương hiệu -->
            <?php if (!empty($brands)): ?>
            <div class="filter-card">
                <h3><i class="fa fa-tag"></i> Thương hiệu</h3>
                <ul class="filter-list">
                    <li>
                        <a href="<?php echo searchUrl(['brand' => '', 'page' => 1]); ?>"
                           class="<?php echo !$brandId ? 'active' : ''; ?>">
                            Tất cả thương hiệu
                        </a>
                    </li>
                    <?php foreach ($brands as $brand): ?>
                    <li>
                        <a href="<?php echo searchUrl(['brand' => $brand['id'], 'page' => 1]); ?>"
                           class="<?php echo $brandId == $brand['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Khoảng giá -->
            <div class="filter-card">
                <h3><i class="fa fa-money"></i> Khoảng giá</h3>
                <div class="price-range-list">
                    <?php foreach ($priceRanges as $range => $label):
                        [$rMin, $rMax] = $range ? explode('-', $range, 2) : ['', ''];
                        $isActive = ($priceMin == $rMin && $priceMax == $rMax);
                        $url = searchUrl(['pmin' => $rMin, 'pmax' => $rMax, 'page' => 1]);
                    ?>
                    <a href="<?php echo $url; ?>"
                       class="price-range-item <?php echo $isActive ? 'active' : ''; ?>">
                        <?php echo $label; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reset filter -->
            <?php if ($categoryId || $brandId || $priceMin || $priceMax): ?>
            <a href="<?php echo BASE_URL . 'search.php' . ($keyword ? '?key=' . urlencode($keyword) : ''); ?>"
               class="filter-reset">
                <i class="fa fa-times-circle"></i> Xóa tất cả bộ lọc
            </a>
            <?php endif; ?>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="search-main">
            <!-- Active filter pills -->
            <?php
            $hasFilter = $categoryId || $brandId || $priceMin || $priceMax;
            if ($hasFilter): ?>
            <div class="active-filters">
                <?php if ($categoryId):
                    $catName = '';
                    foreach ($categories as $c) { if ($c['id'] == $categoryId) { $catName = $c['name']; break; } }
                ?>
                <a href="<?php echo searchUrl(['cat' => '', 'page' => 1]); ?>" class="filter-pill">
                    <?php echo htmlspecialchars($catName); ?> <i class="fa fa-times"></i>
                </a>
                <?php endif; ?>
                <?php if ($brandId):
                    $brandName = '';
                    foreach ($brands as $b) { if ($b['id'] == $brandId) { $brandName = $b['name']; break; } }
                ?>
                <a href="<?php echo searchUrl(['brand' => '', 'page' => 1]); ?>" class="filter-pill">
                    <?php echo htmlspecialchars($brandName); ?> <i class="fa fa-times"></i>
                </a>
                <?php endif; ?>
                <?php if ($priceMin || $priceMax): ?>
                <a href="<?php echo searchUrl(['pmin' => '', 'pmax' => '', 'page' => 1]); ?>" class="filter-pill">
                    <?php
                    if ($priceMin && $priceMax) echo number_format($priceMin,0,',','.') . '₫ – ' . number_format($priceMax,0,',','.') . '₫';
                    elseif ($priceMin) echo 'Từ ' . number_format($priceMin,0,',','.') . '₫';
                    else echo 'Đến ' . number_format($priceMax,0,',','.') . '₫';
                    ?> <i class="fa fa-times"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="search-toolbar">
                <div class="result-info">
                    <?php if ($keyword): ?>
                        Tìm thấy <strong><?php echo number_format($total); ?></strong> kết quả cho <strong>"<?php echo htmlspecialchars($keyword); ?>"</strong>
                    <?php else: ?>
                        Hiển thị <strong><?php echo number_format($total); ?></strong> sản phẩm
                    <?php endif; ?>
                </div>
                <div class="sort-tabs">
                    <?php foreach ($sortLabels as $key => $label): ?>
                    <a href="<?php echo searchUrl(['sort' => $key, 'page' => 1]); ?>"
                       class="sort-tab <?php echo $sortBy === $key ? 'active' : ''; ?>">
                        <?php echo $label; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Product Grid -->
            <?php if (empty($products)): ?>
            <div class="search-empty">
                <div class="empty-icon">🔍</div>
                <h3>Không tìm thấy sản phẩm nào</h3>
                <p>Hãy thử từ khóa khác hoặc xóa bộ lọc để xem nhiều sản phẩm hơn</p>
                <a href="<?php echo BASE_URL; ?>index.php">← Quay về trang chủ</a>
            </div>

            <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p):
                    $discountPct = (float)($p['discount_percent'] ?? 0);
                    $finalPrice  = (float)($p['final_price']      ?? $p['price']);
                    $origPrice   = (float)$p['price'];
                    $isOutOfStock = ((int)($p['quantity'] ?? 0) <= 0);
                    $avgRating   = (float)($p['avg_rating'] ?? 0);
                    $totalSold   = (int)($p['total_sold'] ?? 0);

                    // Xây dựng URL ảnh
                    $imgSrc = $defaultImg;
                    if (!empty($p['image'])) {
                        if (strpos($p['image'], 'data:') === 0) {
                            $imgSrc = $p['image'];
                        } elseif (file_exists(__DIR__ . '/../../../public/img/products/' . $p['image'])) {
                            $imgSrc = BASE_URL . 'public/img/products/' . $p['image'];
                        }
                    }

                    // Stars
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= $i <= round($avgRating)
                            ? '<i class="fa fa-star stars"></i>'
                            : '<i class="fa fa-star stars-empty"></i>';
                    }
                ?>
                <div class="product-card">
                    <div class="product-card-img-wrap" style="position:relative;">
                        <a href="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $p['id']; ?>">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                 alt="<?php echo htmlspecialchars($p['name']); ?>"
                                 loading="lazy">
                            <?php if ($isOutOfStock): ?>
                                <span class="badge-out">Hết hàng</span>
                            <?php elseif ($discountPct > 0): ?>
                                <span class="badge-discount">-<?php echo (int)$discountPct; ?>%</span>
                            <?php endif; ?>
                            <?php if ($totalSold >= 10 && !$isOutOfStock): ?>
                                <span class="badge-hot">🔥 Hot</span>
                            <?php endif; ?>
                        </a>
                        <button class="qv-trigger" onclick="openQuickView(<?php echo $p['id']; ?>);" title="Xem nhanh">
                            <i class="fa fa-eye"></i> Xem nhanh
                        </button>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-cat"><?php echo htmlspecialchars($p['category_name'] ?? ''); ?></div>
                        <a href="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $p['id']; ?>"
                           style="text-decoration:none;">
                            <div class="product-card-name"><?php echo htmlspecialchars($p['name']); ?></div>
                        </a>
                        <?php if ($avgRating > 0): ?>
                        <div class="product-card-rating">
                            <?php echo $stars; ?>
                            <span class="rating-count">(<?php echo round($avgRating, 1); ?>)</span>
                        </div>
                        <?php endif; ?>
                        <div class="product-card-price">
                            <span class="price-final"><?php echo number_format($finalPrice, 0, ',', '.'); ?>₫</span>
                            <?php if ($discountPct > 0): ?>
                            <span class="price-original"><?php echo number_format($origPrice, 0, ',', '.'); ?>₫</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-card-footer">
                        <?php if ($isOutOfStock): ?>
                            <span class="btn-addcart disabled">Hết hàng</span>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo $p['id']; ?>"
                               class="btn-addcart">🛒 Thêm vào giỏ</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $p['id']; ?>"
                           class="btn-detail" title="Xem chi tiết">
                            <i class="fa fa-eye"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                <a href="<?php echo searchUrl(['page' => $currentPage - 1]); ?>" class="page-btn">
                    <i class="fa fa-angle-left"></i>
                </a>
                <?php endif; ?>

                <?php
                $start = max(1, $currentPage - 2);
                $end   = min($totalPages, $currentPage + 2);
                if ($start > 1): ?>
                    <a href="<?php echo searchUrl(['page' => 1]); ?>" class="page-btn">1</a>
                    <?php if ($start > 2): ?><span class="page-btn disabled">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?php echo searchUrl(['page' => $i]); ?>"
                   class="page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?><span class="page-btn disabled">…</span><?php endif; ?>
                    <a href="<?php echo searchUrl(['page' => $totalPages]); ?>" class="page-btn"><?php echo $totalPages; ?></a>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                <a href="<?php echo searchUrl(['page' => $currentPage + 1]); ?>" class="page-btn">
                    <i class="fa fa-angle-right"></i>
                </a>
                <?php endif; ?>
            </div>

            <!-- Info phân trang -->
            <p style="text-align:center; color:#94a3b8; font-size:13px; margin-top:12px;">
                Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?> 
                · Tổng <?php echo number_format($total); ?> sản phẩm
            </p>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>
