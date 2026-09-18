<style>
.combo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
.combo-card {
    background:var(--bg-surface); border-radius:var(--radius-lg);
    border:1px solid var(--border-subtle); overflow:hidden;
    transition:border-color .2s, transform .2s, box-shadow .2s;
}
.combo-card:hover {
    border-color:var(--accent); transform:translateY(-2px);
    box-shadow:0 12px 32px rgba(99,102,241,0.10);
}
.combo-card-img {
    height:140px; background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex; align-items:center; justify-content:center;
    color:white; font-size:36px; position:relative;
}
.combo-card-body { padding:16px 18px 18px; }
.combo-card-body h3 { margin:0 0 4px; font-size:15px; font-weight:700; color:var(--text-primary); }
.combo-card-meta { font-size:12px; color:var(--text-muted); margin-bottom:10px; }
.combo-price-row { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.combo-price { font-size:18px; font-weight:800; color:#10b981; }
.combo-price-orig { font-size:13px; color:var(--text-faint); text-decoration:line-through; }
.combo-badge { font-size:11px; font-weight:700; padding:2px 8px; border-radius:99px; }

/* Product selector */
.product-selector { border:1.5px dashed var(--border-muted); border-radius:10px; padding:16px; margin:12px 0; }
.selected-product-row {
    display:flex; align-items:center; gap:10px;
    padding:8px 12px; background:var(--bg-elevated); border-radius:8px;
    border:1px solid var(--border-subtle); margin-bottom:8px;
}
.selected-product-row .info { flex:1; min-width:0; }
.selected-product-row .info .name { font-size:13px; font-weight:600; color:var(--text-primary); }
.selected-product-row .info .price { font-size:12px; color:var(--text-muted); }
.selected-product-row input[type=number] { width:60px; padding:5px 8px; border:1px solid var(--border-muted); border-radius:6px; font-size:13px; text-align:center; }
</style>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-gift" style="color:#f59e0b;margin-right:8px;"></i>Combo / Bundle sản phẩm</h1>
            <p>Tạo gói combo ưu đãi bằng cách ghép nhiều sản phẩm với giá đặc biệt</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="showComboForm()">
            <i class="fas fa-plus"></i> Thêm combo mới
        </button>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- ─── Form thêm/sửa combo ─── -->
    <div id="comboFormSection" class="form-card <?php echo $editCombo ? '' : 'hidden'; ?>" style="margin-bottom:24px;max-width:100%;">
        <h2 class="form-section-title">
            <i class="fas fa-<?php echo $editCombo ? 'edit' : 'plus-circle'; ?>" style="color:#f59e0b;margin-right:8px;"></i>
            <?php echo $editCombo ? 'Chỉnh sửa combo' : 'Thêm combo mới'; ?>
        </h2>
        <form method="POST" id="comboForm">
            <input type="hidden" name="save_combo" value="1">
            <input type="hidden" name="combo_id" id="comboId" value="<?php echo $editCombo['id'] ?? ''; ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tên combo <span class="req">*</span></label>
                    <input type="text" name="name" id="comboName" class="form-control"
                           value="<?php echo htmlspecialchars($editCombo['name'] ?? ''); ?>" required
                           placeholder="VD: Combo Gaming R5 5600 + RTX 4060">
                </div>
                <div class="form-group">
                    <label class="form-label">Giảm giá combo (%)</label>
                    <input type="number" name="discount_percent" id="comboDiscount" class="form-control"
                           value="<?php echo htmlspecialchars($editCombo['discount_percent'] ?? '5'); ?>"
                           min="0" max="100" step="0.5">
                    <p class="form-note">Phần trăm giảm thêm so với tổng giá gốc</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Thứ tự</label>
                    <input type="number" name="sort_order" class="form-control"
                           value="<?php echo (int)($editCombo['sort_order'] ?? 0); ?>" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Ảnh (URL)</label>
                    <input type="text" name="image" id="comboImage" class="form-control"
                           value="<?php echo htmlspecialchars($editCombo['image'] ?? ''); ?>"
                           placeholder="https://example.com/combo.jpg">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Mô tả ngắn về combo này..."><?php echo htmlspecialchars($editCombo['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Chọn sản phẩm -->
            <div style="margin:16px 0;">
                <label class="form-label" style="font-size:14px;font-weight:700;margin-bottom:10px;display:block;">
                    <i class="fas fa-boxes"></i> Sản phẩm trong combo
                </label>
                <div id="selectedProducts">
                    <?php if (!empty($editComboItems)): ?>
                        <?php foreach ($editComboItems as $idx => $item): ?>
                        <div class="selected-product-row" data-idx="<?php echo $idx; ?>">
                            <input type="hidden" name="product_ids[]" value="<?php echo $item['product_id']; ?>">
                            <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                                <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($item['image']); ?>" 
                                     style="width:40px;height:40px;border-radius:6px;object-fit:cover;flex-shrink:0;">
                                <?php else: ?>
                                <div style="width:40px;height:40px;border-radius:6px;background:var(--bg-elevated);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">📦</div>
                                <?php endif; ?>
                                <div class="info">
                                    <div class="name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="price"><?php echo number_format($item['price'],0,',','.'); ?>₫</div>
                                </div>
                            </div>
                            <label style="font-size:12px;color:var(--text-muted);white-space:nowrap;">SL:
                                <input type="number" name="quantities[]" value="<?php echo (int)$item['quantity']; ?>" min="1" max="99">
                            </label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.selected-product-row').remove()" style="padding:4px 8px;font-size:11px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="product-selector">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:8px;">
                        <i class="fas fa-search"></i> Thêm sản phẩm vào combo
                    </label>
                    <div style="display:flex;gap:8px;">
                        <select id="productSelect" class="form-control" style="flex:1;">
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php foreach ($allProducts as $p): ?>
                            <option value="<?php echo $p['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                    data-price="<?php echo $p['price']; ?>"
                                    data-image="<?php echo htmlspecialchars($p['image'] ?? ''); ?>">
                                [#<?php echo $p['id']; ?>] <?php echo htmlspecialchars(mb_strimwidth($p['name'],0,60,'...')); ?> — <?php echo number_format($p['price'],0,',','.'); ?>₫
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" id="productQty" class="form-control" value="1" min="1" max="99" style="width:70px;text-align:center;" title="Số lượng">
                        <button type="button" class="btn btn-primary" onclick="addProductToCombo()" style="white-space:nowrap;">
                            <i class="fas fa-plus"></i> Thêm
                        </button>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;font-weight:500;">
                    <input type="checkbox" name="is_active" value="1" <?php echo (!isset($editCombo) || $editCombo['is_active']) ? 'checked' : ''; ?> style="width:16px;height:16px;accent-color:#6366f1;">
                    Kích hoạt
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $editCombo ? 'Cập nhật combo' : 'Tạo combo'; ?>
                </button>
                <?php if ($editCombo): ?>
                <a href="?page=combos" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ─── Danh sách combo ─── -->
    <?php if (empty($combos)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-faint);">
        <i class="fas fa-gift" style="font-size:48px;display:block;margin-bottom:16px;opacity:.25;"></i>
        <p style="font-size:16px;color:var(--text-muted);margin-bottom:6px;">Chưa có combo nào</p>
        <p style="font-size:13px;">Nhấn "Thêm combo mới" để tạo gói ưu đãi đầu tiên!</p>
    </div>
    <?php else: ?>
    <div class="combo-grid">
        <?php foreach ($combos as $c): 
            $savingsPct = ($c['original_price'] > 0 && $c['combo_price'] > 0) 
                ? round((1 - $c['combo_price'] / $c['original_price']) * 100) 
                : 0;
        ?>
        <div class="combo-card" style="<?php echo !$c['is_active'] ? 'opacity:.5;' : ''; ?>">
            <div class="combo-card-img" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                <span><?php echo $c['item_count'] ?? 0; ?> 🎁</span>
            </div>
            <div class="combo-card-body">
                <h3><?php echo htmlspecialchars($c['name']); ?></h3>
                <div class="combo-card-meta">
                    <?php echo (int)$c['item_count']; ?> sản phẩm
                    <?php if (!$c['is_active']): ?>
                        · <span style="color:#ef4444;font-weight:600;">Đã ẩn</span>
                    <?php endif; ?>
                </div>
                <?php if ($c['original_price'] > 0): ?>
                <div class="combo-price-row">
                    <span class="combo-price"><?php echo number_format($c['combo_price'],0,',','.'); ?>₫</span>
                    <span class="combo-price-orig"><?php echo number_format($c['original_price'],0,',','.'); ?>₫</span>
                    <?php if ($savingsPct > 0): ?>
                    <span class="combo-badge" style="background:#10b981;color:white;">-<?php echo $savingsPct; ?>%</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="?page=combos&edit_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="toggle_combo" value="1">
                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="btn btn-sm" 
                            style="background:<?php echo $c['is_active'] ? 'rgba(245,158,11,0.12);color:#fbbf24;' : 'rgba(34,197,94,0.12);color:#4ade80;';?>border:1px solid <?php echo $c['is_active'] ? 'rgba(245,158,11,0.3)' : 'rgba(34,197,94,0.3)';?>">
                            <i class="fas fa-<?php echo $c['is_active'] ? 'eye-slash' : 'eye'; ?>"></i> <?php echo $c['is_active'] ? 'Ẩn' : 'Hiện'; ?>
                        </button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xoá combo này?')">
                        <input type="hidden" name="delete_combo" value="1">
                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<script>
let productCounter = <?php echo !empty($editComboItems) ? count($editComboItems) : 0; ?>;

function addProductToCombo() {
    const select = document.getElementById('productSelect');
    const qtyInput = document.getElementById('productQty');
    if (!select.value) return;

    const opt = select.options[select.selectedIndex];
    const pid = opt.value;
    const name = opt.dataset.name;
    const price = opt.dataset.price;
    const image = opt.dataset.image || '';
    const qty = parseInt(qtyInput.value) || 1;

    // Kiểm tra trùng
    const existing = document.querySelectorAll('#selectedProducts input[name="product_ids[]"]');
    for (let inp of existing) {
        if (inp.value === pid) {
            alert('Sản phẩm này đã có trong combo!');
            return;
        }
    }

    const idx = productCounter++;
    const div = document.createElement('div');
    div.className = 'selected-product-row';
    div.dataset.idx = idx;
    div.innerHTML = `
        <input type="hidden" name="product_ids[]" value="${pid}">
        <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
            ${image ? `<img src="<?php echo BASE_URL; ?>public/img/products/${image}" style="width:40px;height:40px;border-radius:6px;object-fit:cover;flex-shrink:0;">` 
                     : `<div style="width:40px;height:40px;border-radius:6px;background:var(--bg-elevated);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">📦</div>`}
            <div class="info">
                <div class="name">${name}</div>
                <div class="price">${parseInt(price).toLocaleString('vi-VN')}₫</div>
            </div>
        </div>
        <label style="font-size:12px;color:var(--text-muted);white-space:nowrap;">SL:
            <input type="number" name="quantities[]" value="${qty}" min="1" max="99">
        </label>
        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.selected-product-row').remove()" style="padding:4px 8px;font-size:11px;">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.getElementById('selectedProducts').appendChild(div);
    select.value = '';
    qtyInput.value = 1;
}

function showComboForm() {
    const section = document.getElementById('comboFormSection');
    section.classList.toggle('hidden');
    if (!section.classList.contains('hidden')) {
        section.scrollIntoView({behavior:'smooth', block:'start'});
        document.getElementById('comboName')?.focus();
    }
}
</script>
