<?php
// Lấy danh sách ảnh phụ của sản phẩm đang sửa
$existingExtraImages = [];
if (!empty($product['id'])) {
    $existingExtraImages = $admin->getProductImages($product['id']);
}
?>
<main class="admin-main">
    <div class="page-header">
        <h1>Chỉnh sửa sản phẩm</h1>
        <a href="?page=products" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <section class="form-card">
        <form method="post" enctype="multipart/form-data">
            <?php echo CsrfHelper::field(); ?>
            <input type="hidden" name="product_id" value="<?php echo intval($product['id'] ?? 0); ?>">
            <div class="form-group">
                <label class="form-label" for="name">Tên sản phẩm</label>
                <input class="form-control" type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="price">Giá bán <span style="color:#e10c00">*</span></label>
                <input class="form-control" type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" step="1000" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="cost_price">Giá nhập (Giá vốn) <span style="font-size:11px;color:var(--text-faint);font-weight:400;">(tùy chọn)</span></label>
                <input class="form-control" type="number" id="cost_price" name="cost_price" value="<?php echo htmlspecialchars($product['cost_price'] ?? '0'); ?>" step="1000" min="0">
                <?php if (!empty($product['cost_price']) && $product['cost_price'] > 0 && !empty($product['price'])): ?>
                <p class="form-note" style="color:#16a34a;">
                    Margin hiện tại: <?php echo round(($product['price'] - $product['cost_price']) / $product['price'] * 100, 1); ?>%
                    &nbsp;(+<?php echo number_format($product['price'] - $product['cost_price'], 0, ',', '.'); ?>đ/sp)
                </p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="category_id">Mã danh mục</label>
                <input class="form-control" type="text" id="category_id" name="category_id" value="<?php echo htmlspecialchars($product['category_id'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="brand_id">Mã thương hiệu</label>
                <input class="form-control" type="text" id="brand_id" name="brand_id" value="<?php echo htmlspecialchars($product['brand_id'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="quantity">Số lượng</label>
                <input class="form-control" type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity'] ?? '0'); ?>" min="0" step="1">
            </div>

            <!-- ẢNH CHÍNH -->
            <div class="form-group">
                <label class="form-label" for="image">Ảnh chính (bìa) — để trống nếu không thay đổi</label>
                <input class="form-control" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp" onchange="previewMainImage(this)">
                <p class="form-note">Chấp nhận các định dạng: JPG, JPEG, PNG, GIF, WEBP</p>
                <?php if (!empty($product['image'])): ?>
                    <div style="margin-top:8px;">
                        <span class="form-note">Ảnh bìa hiện tại:</span><br>
                        <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($product['image']); ?>"
                             style="max-height:130px;border-radius:8px;border:2px solid #ff9800;object-fit:cover;margin-top:6px;">
                    </div>
                <?php endif; ?>
                <div id="main-image-preview" style="margin-top:10px;"></div>
            </div>

            <!-- ẢNH PHỤ HIỆN CÓ -->
            <?php if (!empty($existingExtraImages)): ?>
            <div class="form-group">
                <label class="form-label">Ảnh phụ hiện có</label>
                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px;">
                    <?php foreach ($existingExtraImages as $img): ?>
                    <div style="position:relative;display:inline-block;">
                        <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($img['image']); ?>"
                             style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:2px solid #e5e7eb;">
                        <a href="?page=products&action=delete_image&image_id=<?php echo $img['id']; ?>&pid=<?php echo $product['id']; ?>"
                           onclick="return confirm('Xóa ảnh này?')"
                           style="position:absolute;top:-8px;right:-8px;background:#e10c00;color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:bold;text-decoration:none;line-height:1;"
                           title="Xóa ảnh này">×</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- THÊM ẢNH PHỤ MỚI -->
            <div class="form-group">
                <label class="form-label" for="extra_images">Thêm ảnh phụ mới (có thể chọn nhiều)</label>
                <input class="form-control" type="file" id="extra_images" name="extra_images[]" accept=".jpg,.jpeg,.png,.gif,.webp" multiple onchange="previewExtraImages(this)">
                <p class="form-note">Giữ Ctrl+Click để chọn nhiều ảnh. Chấp nhận: JPG, JPEG, PNG, GIF, WEBP</p>
                <div id="extra-images-preview" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
        </form>
    </section>
</main>

<script>
function previewMainImage(input) {
    const preview = document.getElementById('main-image-preview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" style="max-height:150px;border-radius:8px;border:2px solid #e10c00;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewExtraImages(input) {
    const preview = document.getElementById('extra-images-preview');
    preview.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'position:relative;display:inline-block;';
                wrapper.innerHTML = `<img src="${e.target.result}" style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">`;
                preview.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>
