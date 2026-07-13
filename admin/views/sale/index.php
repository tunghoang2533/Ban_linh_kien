<?php
$allProducts = $admin->getAllProductsWithDiscount();
$saleProducts = array_filter($allProducts, fn($p) => $p['discount_percent'] > 0);
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-tags" style="color:#ef4444;margin-right:10px;"></i>Quản lý giảm giá</h1>
            <p>Đang có <strong><?php echo count($saleProducts); ?></strong> sản phẩm giảm giá</p>
        </div>
        <button type="button" id="showSetDiscountBtn" class="btn btn-primary">
            <i class="fas fa-percent"></i> Thiết lập giảm giá
        </button>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Form thiết lập giảm giá nhanh -->
    <section id="setDiscountSection" class="form-card hidden" style="margin-bottom:24px;">
        <h2 class="form-section-title"><i class="fas fa-tag" style="color:#ef4444;margin-right:8px;"></i>Thiết lập giảm giá cho sản phẩm</h2>
        <form method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Chọn sản phẩm <span class="req">*</span></label>
                    <select class="form-control" name="product_id" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php foreach ($allProducts as $p): ?>
                        <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" data-discount="<?php echo $p['discount_percent']; ?>">
                            #<?php echo $p['id']; ?> - <?php echo htmlspecialchars($p['name']); ?>
                            (<?php echo number_format($p['price'],0,',','.'); ?>₫)
                            <?php if ($p['discount_percent'] > 0): ?> — đang giảm <?php echo $p['discount_percent']; ?>%<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Phần trăm giảm giá (%) <span class="req">*</span></label>
                    <input class="form-control" type="number" name="discount_percent" id="discountInput"
                           min="0" max="100" step="0.5" placeholder="VD: 10 = giảm 10%" required>
                    <p class="form-note"><i class="fas fa-info-circle"></i> Nhập 0 để bỏ giảm giá. Tối đa 100%.</p>
                </div>
                <div class="form-group" id="discountPreviewGroup" style="display:none;">
                    <label class="form-label">Giá sau giảm (xem trước)</label>
                    <div id="discountPreview" style="padding:12px;background:var(--success-bg);border:1.5px solid #86efac;border-radius:10px;font-size:16px;font-weight:700;color:#16a34a;"></div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu giảm giá</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('setDiscountSection').classList.add('hidden')"><i class="fas fa-times"></i> Hủy</button>
            </div>
        </form>
    </section>

    <!-- Danh sách sản phẩm đang giảm giá -->
    <?php if (empty($saleProducts)): ?>
    <div style="text-align:center;padding:60px 20px;background:var(--bg-surface);border-radius:16px;border:2px dashed #e2e8f0;">
        <i class="fas fa-tags" style="font-size:48px;color:#cbd5e1;display:block;margin-bottom:16px;"></i>
        <h3 style="color:var(--text-muted);margin:0 0 8px;">Chưa có sản phẩm nào giảm giá</h3>
        <p style="color:var(--text-faint);margin:0;">Nhấn "Thiết lập giảm giá" để bắt đầu.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá gốc</th>
                    <th>Giảm giá</th>
                    <th>Giá sau giảm</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($saleProducts as $sp): ?>
                <tr>
                    <td><span style="font-weight:700;color:#6366f1;">#<?php echo $sp['id']; ?></span></td>
                    <td>
                        <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($sp['image']); ?>"
                             style="width:48px;height:48px;object-fit:cover;border-radius:8px;"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 48 48\'%3E%3Crect width=\'48\' height=\'48\' fill=\'%23f1f5f9\' rx=\'10\'/%3E%3Ctext x=\'24\' y=\'30\' font-size=\'20\' text-anchor=\'middle\' fill=\'%2394a3b8\'%3E&#128230;%3C/text%3E%3C/svg%3E'">
                    </td>
                    <td><span style="font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($sp['name']); ?></span></td>
                    <td>
                        <span style="background:#eff6ff;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;color:#3b82f6;">
                            <?php echo htmlspecialchars($sp['category_name'] ?? '—'); ?>
                        </span>
                    </td>
                    <td>
                        <span style="text-decoration:line-through;color:var(--text-faint);font-size:13px;">
                            <?php echo number_format($sp['price'], 0, ',', '.'); ?>₫
                        </span>
                    </td>
                    <td>
                        <span style="background:rgba(239,68,68,0.12);color:#f87171;padding:5px 12px;border-radius:99px;font-size:13px;font-weight:800;">
                            -<?php echo $sp['discount_percent']; ?>%
                        </span>
                    </td>
                    <td>
                        <span style="color:#16a34a;font-weight:800;font-size:15px;">
                            <?php echo number_format($sp['sale_price'], 0, ',', '.'); ?>₫
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <button onclick="quickEdit(<?php echo $sp['id']; ?>, <?php echo $sp['discount_percent']; ?>, '<?php echo htmlspecialchars($sp['name'], ENT_QUOTES); ?>')"
                                    class="btn btn-sm btn-warning" title="Sửa % giảm giá">
                                <i class="fas fa-edit"></i> Sửa %
                            </button>
                            <a href="?page=sale&action=remove&id=<?php echo $sp['id']; ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Xóa giảm giá của sản phẩm này?')">
                                <i class="fas fa-times"></i> Bỏ giảm
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>

<!-- Modal sửa nhanh % -->
<div id="quickEditModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-surface);border-radius:16px;padding:32px;width:380px;max-width:calc(100vw - 32px);box-shadow:0 24px 60px rgba(0,0,0,.2);">
        <h3 style="margin:0 0 16px;font-size:18px;color:var(--text-primary);"><i class="fas fa-percent" style="color:#ef4444;margin-right:8px;"></i>Sửa % giảm giá</h3>
        <p id="qeProductName" style="color:var(--text-muted);margin:0 0 20px;font-size:14px;"></p>
        <form method="post">
            <input type="hidden" name="product_id" id="qeProductId">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Phần trăm giảm giá (%)</label>
                <input class="form-control" type="number" name="discount_percent" id="qeDiscount"
                       min="0" max="100" step="0.5" style="font-size:18px;font-weight:700;text-align:center;">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Lưu</button>
                <button type="button" class="btn btn-secondary" onclick="closeQuickEdit()" style="flex:1;"><i class="fas fa-times"></i> Hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('showSetDiscountBtn').addEventListener('click', function() {
    document.getElementById('setDiscountSection').classList.remove('hidden');
});

// Preview giá sau giảm
const productSelect = document.querySelector('select[name="product_id"]');
const discountInput = document.getElementById('discountInput');
const previewGroup  = document.getElementById('discountPreviewGroup');
const previewDiv    = document.getElementById('discountPreview');

function updatePreview() {
    const opt = productSelect.options[productSelect.selectedIndex];
    const price = parseFloat(opt.dataset.price || 0);
    const disc  = parseFloat(discountInput.value || 0);
    if (price > 0 && disc > 0) {
        const salePrice = Math.round(price * (1 - disc / 100));
        previewDiv.textContent = new Intl.NumberFormat('vi-VN').format(salePrice) + '₫  (tiết kiệm ' + new Intl.NumberFormat('vi-VN').format(price - salePrice) + '₫)';
        previewGroup.style.display = 'block';
    } else {
        previewGroup.style.display = 'none';
    }
}

if (productSelect) productSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.discount > 0) discountInput.value = opt.dataset.discount;
    updatePreview();
});
if (discountInput) discountInput.addEventListener('input', updatePreview);

// Quick edit modal
function quickEdit(id, discount, name) {
    document.getElementById('qeProductId').value = id;
    document.getElementById('qeDiscount').value  = discount;
    document.getElementById('qeProductName').textContent = 'Sản phẩm: ' + name;
    const modal = document.getElementById('quickEditModal');
    modal.style.display = 'flex';
}
function closeQuickEdit() {
    document.getElementById('quickEditModal').style.display = 'none';
}
document.getElementById('quickEditModal').addEventListener('click', function(e) {
    if (e.target === this) closeQuickEdit();
});
</script>
