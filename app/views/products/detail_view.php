<?php
$product = $product ?? [];
$specs = $specs ?? [];
$comments = $comments ?? [];
$productImages = $productImages ?? [];
$averageRating = $averageRating ?? 0;
$totalReviews = $totalReviews ?? 0;
$productVariants = $productVariants ?? [];
if (isset($_SESSION['comment_error'])) {
    $commentError = $_SESSION['comment_error'];
    unset($_SESSION['comment_error']);
} else {
    $commentError = null;
}
if (isset($_SESSION['comment_success'])) {
    $commentSuccess = $_SESSION['comment_success'];
    unset($_SESSION['comment_success']);
} else {
    $commentSuccess = null;
}

// ── SEO meta ──────────────────────────────────────────────────
if (!empty($product['name'])) {
    $pageTitle       = htmlspecialchars($product['name']);
    $pageDescription = !empty($product['description'])
        ? mb_substr(strip_tags($product['description']), 0, 160)
        : 'Mua ' . $product['name'] . ' chính hãng, giá tốt tại PC Store. Giao hàng nhanh, bảo hành chính hãng.';
    if (!empty($product['image']) && strpos($product['image'], 'data:') !== 0) {
        $pageImage = BASE_URL . 'public/img/products/' . $product['image'];
    }
    $pageCanonical = BASE_URL . 'chitietsanpham.php?id=' . (int)($product['id'] ?? 0);

    // JSON-LD Product schema
    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => $product['name'],
        'image'    => $pageImage ?? '',
        'description' => strip_tags($product['description'] ?? ''),
        'sku'      => 'SP' . ($product['id'] ?? ''),
        'brand'    => ['@type' => 'Brand', 'name' => $product['brand_name'] ?? 'PC Store'],
        'offers'   => [
            '@type'         => 'Offer',
            'price'         => (string)($product['price'] ?? 0),
            'priceCurrency' => 'VND',
            'availability'  => ((int)($product['quantity'] ?? 0) > 0)
                                ? 'https://schema.org/InStock'
                                : 'https://schema.org/OutOfStock',
            'url'           => $pageCanonical,
        ],
        'aggregateRating' => $totalReviews > 0 ? [
            '@type'       => 'AggregateRating',
            'ratingValue' => (string) round($averageRating, 1),
            'reviewCount' => (string) $totalReviews,
        ] : null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
// Xây dựng danh sách tất cả ảnh (ảnh chính + ảnh phụ)
$defaultImage = 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" fill="%23f3f3f3"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23999" font-size="24">No image</text></svg>';

$allImages = [];
if (!empty($product['image'])) {
    if (strpos($product['image'], 'data:') === 0) {
        $allImages[] = $product['image'];
    } elseif (file_exists(__DIR__ . '/../../../public/img/products/' . $product['image'])) {
        $allImages[] = BASE_URL . 'public/img/products/' . $product['image'];
    }
}
foreach ($productImages as $pi) {
    if (!empty($pi['image']) && file_exists(__DIR__ . '/../../../public/img/products/' . $pi['image'])) {
        $allImages[] = BASE_URL . 'public/img/products/' . $pi['image'];
    }
}
if (empty($allImages)) {
    $allImages[] = $defaultImage;
}

$isOutOfStock = ((int)($product['quantity'] ?? 0) <= 0);
$allImagesJson = json_encode($allImages);
?>
<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <div style="display: flex; gap: 40px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

        <!-- PHẦN GALLERY -->
        <div style="flex: 1; display: flex; gap: 14px; min-width: 0;">

            <!-- THUMBNAIL SIDEBAR TRÁI -->
            <div id="pg-thumbs" style="display:flex;flex-direction:column;gap:8px;max-height:500px;overflow-y:auto;scrollbar-width:thin;flex-shrink:0;">
                <?php foreach ($allImages as $idx => $imgSrc): ?>
                <div class="pg-thumb<?php echo $idx === 0 ? ' pg-thumb-active' : ''; ?>"
                     onclick="pgSetImage(<?php echo $idx; ?>)"
                     style="width:82px;height:82px;border-radius:7px;overflow:hidden;cursor:pointer;border:2px solid <?php echo $idx === 0 ? '#e10c00' : '#e5e7eb'; ?>;transition:border-color .2s,transform .2s;flex-shrink:0;">
                    <img src="<?php echo $imgSrc; ?>" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ẢNH LỚN + NÚT PREV/NEXT -->
            <div style="flex:1;position:relative;min-width:0;">
                <div id="pg-main-wrap" style="position:relative;border-radius:10px;overflow:hidden;background:#f9f9f9;border:1px solid #eee;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;">
                    <img id="pg-main-img" src="<?php echo $allImages[0]; ?>"
                         style="max-width:100%;max-height:100%;object-fit:contain;transition:opacity .25s ease;display:block;">

                    <?php if ($isOutOfStock): ?>
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.52);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;pointer-events:none;">
                        <span style="background:#ef4444;color:#fff;font-size:18px;font-weight:800;letter-spacing:0.1em;padding:10px 28px;border-radius:999px;text-transform:uppercase;box-shadow:0 6px 20px rgba(239,68,68,0.5);">🚫 HẾT HÀNG</span>
                        <span style="color:#fecaca;font-size:13px;font-weight:600;">Sản phẩm hiện tại đã hết, vui lòng quay lại sau</span>
                    </div>
                    <?php endif; ?>

                    <?php if (count($allImages) > 1): ?>
                    <!-- Nút PREV -->
                    <button onclick="pgPrev()" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.9);border:none;border-radius:50%;width:40px;height:40px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.15);transition:background .2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.9)'">&#8249;</button>
                    <!-- Nút NEXT -->
                    <button onclick="pgNext()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.9);border:none;border-radius:50%;width:40px;height:40px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.15);transition:background .2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.9)'">&#8250;</button>
                    <?php endif; ?>
                </div>

                <!-- Chấm chỉ thị vị trí -->
                <?php if (count($allImages) > 1): ?>
                <div id="pg-dots" style="display:flex;justify-content:center;gap:6px;margin-top:10px;">
                    <?php foreach ($allImages as $idx => $imgSrc): ?>
                    <span class="pg-dot<?php echo $idx === 0 ? ' pg-dot-active' : ''; ?>"
                          onclick="pgSetImage(<?php echo $idx; ?>)"
                          style="width:8px;height:8px;border-radius:50%;background:<?php echo $idx === 0 ? '#e10c00' : '#d1d5db'; ?>;cursor:pointer;transition:background .2s,transform .2s;display:inline-block;"></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- THÔNG TIN SẢN PHẨM -->


        <div style="flex: 1;">
            <h1 style="font-size: 28px; color: #333; margin-top: 0;"><?php echo $product['name']; ?></h1>
            <p style="color: #666;">Mã sản phẩm: #<?php echo $product['id']; ?></p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
            
            <div style="margin: 20px 0;">
                <?php
                $hasDis  = !empty($product['discount_percent']) && $product['discount_percent'] > 0;
                $saleAmt = $hasDis ? round($product['price'] * (1 - $product['discount_percent'] / 100)) : $product['price'];
                ?>
                <?php if ($hasDis): ?>
                    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                        <span style="font-size:32px;color:#e10c00;font-weight:800;"><?php echo number_format($saleAmt, 0, ',', '.'); ?> ₫</span>
                        <span style="text-decoration:line-through;color:#94a3b8;font-size:18px;"><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</span>
                        <span style="background:#e10c00;color:#fff;font-size:13px;font-weight:800;padding:4px 12px;border-radius:99px;">-<?php echo $product['discount_percent']; ?>%</span>
                    </div>
                    <div style="margin-top:6px;font-size:13px;color:#16a34a;font-weight:600;">
                        💰 Tiết kiệm <?php echo number_format($product['price'] - $saleAmt, 0, ',', '.'); ?> ₫
                    </div>
                <?php else: ?>
                    <span style="font-size: 30px; color: #e10c00; font-weight: bold;">
                        <?php echo number_format($product['price'], 0, ',', '.'); ?> ₫
                    </span>
                <?php endif; ?>
            </div>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ff9800;">
                <h4 style="margin-top: 0; margin-bottom: 10px; color: #333;">Đặc điểm nổi bật:</h4>
                <p style="margin: 0; line-height: 1.6; color: #555;"><?php echo $product['description'] ? $product['description'] : 'Đang cập nhật nội dung cho sản phẩm này...'; ?></p>
            </div>

            <?php if(!empty($specs)): ?>
            <div style="margin-bottom: 25px;">
                <h4 style="margin-top: 0; font-size: 16px; text-transform: uppercase; color: #333; margin-bottom: 10px;">
                    <i class="fa fa-list-alt"></i> Thông số kỹ thuật
                </h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px; border: 1px solid #eee;">
                    <tbody>
                        <?php foreach($specs as $index => $spec): ?>
                        <tr style="background: <?php echo ($index % 2 == 0) ? '#fff' : '#f9f9f9'; ?>;">
                            <td style="padding: 10px 15px; border: 1px solid #eee; width: 35%; color: #666; font-weight: bold;">
                                <?php echo htmlspecialchars($spec['spec_name']); ?>
                            </td>
                            <td style="padding: 10px 15px; border: 1px solid #eee; color: #333;">
                                <?php echo htmlspecialchars($spec['spec_value']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!empty($productVariants)): ?>
            <!-- ── BIẾN THỂ SẢN PHẨM ── -->
            <div style="margin-bottom:20px;padding:16px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;" id="variantBlock">
                <h4 style="margin:0 0 12px;font-size:14px;font-weight:700;color:#374151;">&#9881;&#65039; Chọn phiên bản:</h4>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php foreach ($productVariants as $v): ?>
                    <label style="cursor:pointer;">
                        <input type="radio" name="selected_variant" value="<?php echo $v['id']; ?>"
                               data-price-mod="<?php echo (int)$v['price_modifier']; ?>"
                               data-name="<?php echo htmlspecialchars($v['name']); ?>"
                               data-stock="<?php echo (int)$v['stock']; ?>"
                               style="display:none;"
                               onchange="applyVariant(this)">
                        <span class="variant-btn" style="display:inline-block;padding:8px 16px;border:2px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;color:#374151;background:#fff;transition:all .2s;">
                            <?php echo htmlspecialchars($v['name']); ?>
                            <?php if ($v['price_modifier'] != 0): ?>
                            <small style="color:<?php echo $v['price_modifier'] > 0 ? '#dc2626' : '#16a34a'; ?>">
                                <?php echo ($v['price_modifier'] > 0 ? '+' : '') . number_format($v['price_modifier'], 0, ',', '.'); ?>&#x20AB;
                            </small>
                            <?php endif; ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p id="variantStock" style="margin:10px 0 0;font-size:12px;color:#64748b;"></p>
            </div>
            <script>
            function applyVariant(radio) {
                // Hiện active style
                document.querySelectorAll('.variant-btn').forEach(b => {
                    b.style.borderColor = '#d1d5db'; b.style.background = '#fff'; b.style.color = '#374151';
                });
                radio.nextElementSibling.style.borderColor = '#2563eb';
                radio.nextElementSibling.style.background  = '#eff6ff';
                radio.nextElementSibling.style.color       = '#1d4ed8';

                const basePriceEl = document.querySelector('#basePrice');
                const mod = parseInt(radio.dataset.priceMod) || 0;
                const base = parseInt(basePriceEl?.dataset?.base || 0);
                if (basePriceEl) {
                    basePriceEl.textContent = (base + mod).toLocaleString('vi-VN') + ' ₫';
                }
                document.getElementById('variantStock').textContent =
                    'Tồn kho phiên bản này: ' + radio.dataset.stock + ' sản phẩm';

                // Gửi variant_id vào form thêm giỏ hàng
                let hiddenInput = document.getElementById('variantIdInput');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id   = 'variantIdInput';
                    hiddenInput.name = 'variant_id';
                    document.querySelector('.addCartForm')?.appendChild(hiddenInput);
                }
                hiddenInput.value = radio.value;
            }
            </script>
            <?php endif; ?>                    <div style="display:flex; flex-direction: column; gap: 15px; margin-top: 25px;">
                
                <?php if ($isOutOfStock): ?>
                    <!-- Thông báo hết hàng + nút Đăng ký -->
                    <div style="
                        display:flex; align-items:center; gap:12px;
                        background:#fef2f2; border:1.5px solid #fca5a5;
                        border-left:5px solid #ef4444;
                        border-radius:10px; padding:16px 20px;
                    ">
                        <span style="font-size:28px;">⚠️</span>
                        <div>
                            <div style="font-weight:700;color:#b91c1c;font-size:15px;">Sản phẩm đã hết hàng</div>
                            <div style="color:#7f1d1d;font-size:13px;margin-top:3px;">Hiện tại kho đã hết sản phẩm này. Đăng ký để nhận thông báo ngay khi có hàng!</div>
                        </div>
                    </div>
                    <div style="display:flex; gap:15px;">
                        <button onclick="openBisModal(<?php echo (int)$product['id']; ?>)" 
                                style="flex:1;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border:none;padding:14px;font-size:15px;font-weight:bold;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:transform .2s,box-shadow .2s;box-shadow:0 4px 14px rgba(99,102,241,0.35);"
                                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(99,102,241,0.45)'"
                                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(99,102,241,0.35)'">
                            <i class="fa fa-bell"></i> 🔔 BÁO KHI CÓ HÀNG
                        </button>
                        <button disabled style="flex:1;background:#e2e8f0;color:#94a3b8;border:none;padding:12px;font-size:16px;font-weight:bold;border-radius:5px;cursor:not-allowed;display:flex;align-items:center;justify-content:center;gap:8px;">
                            <i class="fa fa-ban"></i> Hết hàng
                        </button>
                    </div>
                <?php else: ?>
                    <div style="display: flex; gap: 15px;">
                        <button onclick="location.href='<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo $product['id']; ?>'" 
                                style="flex: 1; background: #fff; color: #ff9800; border: 2px solid #ff9800; padding: 12px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa fa-cart-plus"></i> THÊM VÀO GIỎ
                        </button>

                        <button onclick="location.href='<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo $product['id']; ?>&checkout=1'" 
                                style="flex: 1; background: #e10c00; color: white; border: none; padding: 12px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 10px rgba(225, 12, 0, 0.3);">
                            <i class="fa fa-bolt"></i> MUA NGAY
                        </button>
                    </div>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>buildpc.php?action=add&cat_id=<?php echo $product['category_id']; ?>&product_id=<?php echo $product['id']; ?>" 
                   style="width: 100%; box-sizing: border-box; background: linear-gradient(to right, #ff4b2b, #ff416c); color: white; padding: 15px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; text-decoration: none; text-align: center; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 10px rgba(255, 65, 108, 0.3);">
                    <i class="fa fa-cogs"></i> BUILD PC VỚI MÓN NÀY
                </a>

                <?php if (!$isOutOfStock): ?>
                <button onclick="addToCompare(<?php echo $product['id']; ?>, '<?php echo addslashes(htmlspecialchars($product['name'])); ?>')"
                        style="width:100%;padding:11px;border:2px dashed #c7d2fe;background:#f5f3ff;color:#6366f1;font-weight:700;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;" onmouseover="this.style.background='#ede9fe'" onmouseout="this.style.background='#f5f3ff'">
                    <i class="fa fa-balance-scale"></i> Thêm vào so sánh
                </button>
                <?php endif; ?>

            </div>
            </div>
    </div>
</div>

<script>
// Lưu thông tin sản phẩm hiện tại để chat widget có thể tham chiếu
window.currentProduct = {
    id:    <?php echo intval($product['id']); ?>,
    name:  <?php echo json_encode($product['name']); ?>,
    price: <?php echo json_encode(number_format($product['price'], 0, ',', '.') . ' ₫'); ?>,
    url:   window.location.href,
    image: <?php echo json_encode(!empty($allImages[0]) ? $allImages[0] : ''); ?>
};
</script>

<script>
(function() {
    var pgImages = <?php echo $allImagesJson; ?>;
    var pgCurrent = 0;

    function pgSetImage(idx) {
        if (idx < 0) idx = pgImages.length - 1;
        if (idx >= pgImages.length) idx = 0;
        pgCurrent = idx;

        var mainImg = document.getElementById('pg-main-img');
        if (mainImg) {
            mainImg.style.opacity = '0';
            setTimeout(function() {
                mainImg.src = pgImages[idx];
                mainImg.style.opacity = '1';
            }, 200);
        }

        // Cập nhật thumbnail active
        var thumbs = document.querySelectorAll('.pg-thumb');
        thumbs.forEach(function(t, i) {
            t.style.borderColor = (i === idx) ? '#e10c00' : '#e5e7eb';
            t.style.transform   = (i === idx) ? 'scale(1.05)' : 'scale(1)';
        });

        // Cuộn thumbnail vào tầm nhìn
        if (thumbs[idx]) {
            thumbs[idx].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Cập nhật dots
        var dots = document.querySelectorAll('.pg-dot');
        dots.forEach(function(d, i) {
            d.style.background   = (i === idx) ? '#e10c00' : '#d1d5db';
            d.style.transform    = (i === idx) ? 'scale(1.3)' : 'scale(1)';
        });
    }

    function pgPrev() { pgSetImage(pgCurrent - 1); }
    function pgNext() { pgSetImage(pgCurrent + 1); }

    // Expose globally (called from onclick)
    window.pgSetImage = pgSetImage;
    window.pgPrev     = pgPrev;
    window.pgNext     = pgNext;

    // Swipe support (mobile)
    var touchStartX = 0;
    var wrap = document.getElementById('pg-main-wrap');
    if (wrap) {
        wrap.addEventListener('touchstart', function(e) { touchStartX = e.touches[0].clientX; }, { passive: true });
        wrap.addEventListener('touchend',   function(e) {
            var diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) { diff > 0 ? pgNext() : pgPrev(); }
        }, { passive: true });
    }
})();
</script>

<div class="container" id="product-comments" style="margin-bottom: 50px;">

    <div style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.08);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0; font-size: 24px;">Bình luận và đánh giá</h2>
                <p style="margin: 5px 0 0; color: #666;"><?php echo $totalReviews; ?> đánh giá - điểm trung bình <?php echo $averageRating; ?>/5</p>
            </div>
            <div style="display: flex; align-items: center; gap: 5px; font-size: 18px; color: #ffb400;">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa <?php echo $i <= round($averageRating) ? 'fa-star' : 'fa-star-o'; ?>"></i>
                <?php endfor; ?>
            </div>
        </div>

        <?php if (!empty($_SESSION['comment_success'])): ?>
            <div style="margin-bottom: 15px; padding: 12px; border-radius: 8px; background: #e8f8ed; color: #217a3b; border: 1px solid #d4efdf;">
                <?php echo $_SESSION['comment_success']; unset($_SESSION['comment_success']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['comment_error'])): ?>
            <div style="margin-bottom: 15px; padding: 12px; border-radius: 8px; background: #fdeaec; color: #b03a2e; border: 1px solid #f5b7b1;">
                <?php echo $_SESSION['comment_error']; unset($_SESSION['comment_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $product['id']; ?>#product-comments">
                <?php echo CsrfHelper::field(); ?>
                <input type="hidden" name="comment_submit" value="1">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; min-width: 240px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Đánh giá của bạn</label>
                        <select name="rating" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                            <option value="5">5 sao - Tuyệt vời</option>
                            <option value="4">4 sao - Rất tốt</option>
                            <option value="3">3 sao - Tạm được</option>
                            <option value="2">2 sao - Chưa tốt</option>
                            <option value="1">1 sao - Không hài lòng</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 240px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Tên hiển thị</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;" readonly>
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Bình luận</label>
                    <textarea name="comment" rows="5" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; resize: vertical;" placeholder="Viết cảm nhận của bạn về sản phẩm này..."></textarea>
                </div>
                <button type="submit" style="background: #e10c00; color: #fff; border: none; padding: 14px 24px; border-radius: 6px; font-size: 16px; cursor: pointer;">Gửi đánh giá</button>
            </form>
        <?php else: ?>
            <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #eee;">
                <p style="margin: 0; color: #555;">Bạn cần <a href="<?php echo BASE_URL; ?>taikhoan.php">đăng nhập</a> để bình luận sản phẩm.</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div style="padding: 18px; border: 1px solid #eee; border-radius: 10px; margin-bottom: 15px; background: #fafafa;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 15px; flex-wrap: wrap;">
                            <strong style="color: #333;"><?php echo htmlspecialchars($comment['name'] ?: ($comment['full_name'] ?? 'Khách hàng')); ?></strong>
                            <div style="color: #ffb400; font-size: 14px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa <?php echo $i <= $comment['rating'] ? 'fa-star' : 'fa-star-o'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p style="margin: 0 0 10px; color: #555; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        <small style="color: #999;"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #555; margin: 0;">Chưa có bình luận nào cho sản phẩm này. Hãy để lại đánh giá đầu tiên!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($fbtProducts)): ?>
<div class="container" style="margin-bottom:40px;">
<div style="background:white;border-radius:14px;padding:28px 30px;box-shadow:0 2px 16px rgba(0,0,0,.07);">
  <h3 style="margin:0 0 20px;font-size:18px;font-weight:800;color:#1e293b;display:flex;align-items:center;gap:10px;">
    <span style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;width:38px;height:38px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;">🛒</span>
    Thường mua cùng nhau
  </h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:16px;">
  <?php foreach ($fbtProducts as $fp):
    $fpSale = !empty($fp['discount_percent']) && $fp['discount_percent']>0 ? round($fp['price']*(1-$fp['discount_percent']/100)) : $fp['price'];
    $fpImg  = !empty($fp['image']) ? BASE_URL.'public/img/products/'.$fp['image'] : '';
  ?>
  <div style="border:1.5px solid #f1f5f9;border-radius:12px;padding:14px;text-align:center;transition:.2s;" onmouseover="this.style.borderColor='#6366f1';this.style.boxShadow='0 4px 16px rgba(99,102,241,.15)'" onmouseout="this.style.borderColor='#f1f5f9';this.style.boxShadow='none'">
    <?php if ($fpImg): ?><img src="<?php echo htmlspecialchars($fpImg); ?>" style="width:100%;height:110px;object-fit:contain;border-radius:8px;margin-bottom:8px;" loading="lazy"><?php endif; ?>
    <p style="font-size:13px;font-weight:700;color:#1e293b;margin:0 0 5px;line-height:1.4;"><?php echo htmlspecialchars($fp['name']); ?></p>
    <?php if ($fp['discount_percent']>0): ?>
    <p style="font-size:11px;color:#94a3b8;text-decoration:line-through;margin:0;"><?php echo number_format($fp['price'],0,',','.'); ?>₫</p>
    <?php endif; ?>
    <p style="font-size:16px;font-weight:800;color:#e10c00;margin:0 0 10px;"><?php echo number_format($fpSale,0,',','.'); ?>₫</p>
    <div style="display:flex;gap:6px;">
      <a href="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $fp['id']; ?>" style="flex:1;padding:7px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:11px;font-weight:600;color:#475569;text-decoration:none;text-align:center;">Xem</a>
      <a href="<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo $fp['id']; ?>" style="flex:2;padding:7px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;text-align:center;">+ Giỏ hàng</a>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
</div>
</div>
<?php endif; ?>

<?php // ===== SO SANH ===== ?>
<!-- Thanh so sánh sticky -->
<div id="compare-bar" style="display:none;position:fixed;bottom:0;left:0;right:0;background:white;border-top:3px solid #6366f1;padding:12px 20px;z-index:9999;box-shadow:0 -4px 20px rgba(0,0,0,.12);align-items:center;gap:12px;flex-wrap:wrap;">
    <div style="font-weight:800;color:#6366f1;white-space:nowrap;"><i class="fa fa-balance-scale"></i> So sánh</div>
    <div id="compare-count" style="font-size:12px;color:#94a3b8;white-space:nowrap;"></div>
    <div id="compare-items" style="flex:1;display:flex;gap:8px;flex-wrap:wrap;"></div>
    <button onclick="localStorage.removeItem('compare_list');updateCompareBar();" style="padding:6px 14px;border:1.5px solid #e2e8f0;background:white;border-radius:8px;cursor:pointer;font-size:13px;color:#64748b;white-space:nowrap;">Xóa hết</button>
    <a id="compare-link" href="#" style="padding:10px 22px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border-radius:10px;font-weight:700;text-decoration:none;font-size:14px;white-space:nowrap;">So sánh ngay →</a>
</div>
<!-- ── Back-in-Stock Modal ── -->
<div id="bisModal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);align-items:center;justify-content:center;animation:fadeIn .25s ease;">
    <div style="background:#fff;border-radius:20px;padding:32px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;animation:slideUp .3s ease;">
        <button onclick="closeBisModal()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
        <div style="text-align:center;margin-bottom:20px;">
            <span style="font-size:48px;">🔔</span>
            <h3 style="margin:12px 0 4px;font-size:20px;font-weight:800;color:#1e293b;">Báo khi có hàng</h3>
            <p style="margin:0;color:#64748b;font-size:14px;">Nhập email để nhận thông báo ngay khi <strong id="bisProductName" style="color:#6366f1;">sản phẩm</strong> về kho!</p>
        </div>
        <form id="bisForm" onsubmit="submitBis(event)">
            <?php echo CsrfHelper::field(); ?>
            <input type="hidden" name="product_id" id="bisProductId" value="">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;">Email của bạn</label>
                <input type="email" name="email" id="bisEmail" required
                       placeholder="VD: email@example.com"
                       value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>"
                       style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:15px;transition:border-color .2s;outline:none;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <button type="submit" id="bisSubmitBtn"
                    style="width:100%;padding:14px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border:none;border-radius:12px;font-size:16px;font-weight:700;cursor:pointer;transition:opacity .2s;box-shadow:0 4px 14px rgba(99,102,241,0.3);">
                🔔 Gửi đăng ký
            </button>
            <p style="margin:12px 0 0;font-size:11px;color:#94a3b8;text-align:center;">Chúng tôi sẽ chỉ gửi email khi sản phẩm này có hàng trở lại. Không spam.</p>
        </form>
        <div id="bisResult" style="display:none;text-align:center;padding:20px 0;">
            <span id="bisResultIcon" style="font-size:48px;">✅</span>
            <p id="bisResultMsg" style="font-size:15px;color:#1e293b;font-weight:600;margin:12px 0 0;"></p>
            <button onclick="closeBisModal()" style="margin-top:16px;padding:10px 24px;background:#f1f5f9;border:none;border-radius:10px;font-weight:700;color:#475569;cursor:pointer;">Đóng</button>
        </div>
    </div>
</div>

<script>
function openBisModal(productId) {
    document.getElementById('bisProductId').value = productId;
    document.getElementById('bisModal').style.display = 'flex';
    document.getElementById('bisForm').style.display = 'block';
    document.getElementById('bisResult').style.display = 'none';
    document.getElementById('bisSubmitBtn').disabled = false;
    document.getElementById('bisSubmitBtn').innerHTML = '🔔 Gửi đăng ký';
    document.getElementById('bisEmail').focus();
    // Set product name from page
    var nameEl = document.querySelector('h1');
    if (nameEl) document.getElementById('bisProductName').textContent = nameEl.textContent;
}
function closeBisModal() {
    document.getElementById('bisModal').style.display = 'none';
}
function submitBis(e) {
    e.preventDefault();
    var form = document.getElementById('bisForm');
    var btn = document.getElementById('bisSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Đang gửi...';
    var data = new FormData(form);
    fetch('<?php echo BASE_URL; ?>back_in_stock.php', {
        method: 'POST',
        body: data
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        document.getElementById('bisForm').style.display = 'none';
        document.getElementById('bisResult').style.display = 'block';
        document.getElementById('bisResultIcon').textContent = res.ok ? '✅' : '❌';
        document.getElementById('bisResultMsg').textContent = res.message;
        if (!res.ok) {
            var closeBtn = document.getElementById('bisResult').querySelector('button');
            if (closeBtn) closeBtn.textContent = 'Thử lại';
        }
    })
    .catch(function() {
        document.getElementById('bisForm').style.display = 'none';
        document.getElementById('bisResult').style.display = 'block';
        document.getElementById('bisResultIcon').textContent = '❌';
        document.getElementById('bisResultMsg').textContent = 'Lỗi kết nối. Vui lòng thử lại sau.';
    });
}
// Đóng modal khi click ra ngoài
document.addEventListener('click', function(e) {
    var modal = document.getElementById('bisModal');
    if (modal && e.target === modal) closeBisModal();
});
</script>

<style>
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(30px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>

<script>
var BASE_URL_CP = '<?php echo BASE_URL; ?>';
function addToCompare(id, name) {
    var list = JSON.parse(localStorage.getItem('compare_list') || '[]');
    if (list.find(function(x){return x.id==id;})) {
        if(confirm('Sản phẩm đã có trong danh sách. Xem so sánh ngay?')) {
            var ids = list.map(function(x){return x.id;}).join(',');
            window.open(BASE_URL_CP + 'sosanpham.php?ids=' + ids, '_blank');
        }
        return;
    }
    if (list.length >= 3) {
        if (!confirm('Tối đa 3 sản phẩm. Xóa sản phẩm đầu tiên?')) return;
        list.shift();
    }
    list.push({id:id, name:name});
    localStorage.setItem('compare_list', JSON.stringify(list));
    updateCompareBar();
}
function updateCompareBar() {
    var list = JSON.parse(localStorage.getItem('compare_list') || '[]');
    var bar = document.getElementById('compare-bar');
    if (!bar) return;
    if (list.length === 0) { bar.style.display = 'none'; return; }
    bar.style.display = 'flex';
    var ids = list.map(function(x){return x.id;}).join(',');
    var names = list.map(function(x){
        var n = x.name.length>22 ? x.name.substring(0,22)+'...' : x.name;
        return '<span style="background:#f1f5f9;padding:4px 10px;border-radius:20px;font-size:12px;color:#1e293b;white-space:nowrap;">'+n+'</span>';
    }).join('');
    document.getElementById('compare-items').innerHTML = names;
    document.getElementById('compare-link').href = BASE_URL_CP + 'sosanpham.php?ids=' + ids;
    document.getElementById('compare-count').textContent = list.length + '/3';
}
document.addEventListener('DOMContentLoaded', updateCompareBar);
</script>