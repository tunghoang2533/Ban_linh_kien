<main class="admin-main">
    <div class="page-header">
        <h1>Thêm sản phẩm</h1>
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
            <div class="form-group">
                <label class="form-label" for="name">Tên sản phẩm</label>
                <input class="form-control" type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="price">Giá bán <span style="color:#e10c00">*</span></label>
                <input class="form-control" type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" step="1000" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="cost_price">Giá nhập (Giá vốn) <span style="font-size:11px;color:var(--text-faint);font-weight:400;">(tùy chọn, dùng tính lợi nhuận)</span></label>
                <input class="form-control" type="number" id="cost_price" name="cost_price" value="<?php echo htmlspecialchars($product['cost_price'] ?? '0'); ?>" step="1000" min="0" placeholder="0">
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

            <div class="form-group">
                <label class="form-label" for="image">Ảnh chính (bìa) <span style="color:#e10c00">*</span></label>
                <input class="form-control" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp" required onchange="previewMainImage(this)">
                <p class="form-note">Chấp nhận các định dạng: JPG, JPEG, PNG, GIF, WEBP</p>
                <div id="main-image-preview" style="margin-top:10px;"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="extra_images">Ảnh phụ (có thể chọn nhiều ảnh)</label>
                <input class="form-control" type="file" id="extra_images" name="extra_images[]" accept=".jpg,.jpeg,.png,.gif,.webp" multiple onchange="previewExtraImages(this)">
                <p class="form-note">Chọn nhiều ảnh cùng lúc bằng Ctrl+Click. Chấp nhận: JPG, JPEG, PNG, GIF, WEBP</p>
                <div id="extra-images-preview" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
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
