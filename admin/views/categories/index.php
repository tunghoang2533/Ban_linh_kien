<?php
/*
 * Quản lý Danh mục & Thương hiệu
 * URL: ?page=categories
 */
$cats    = $admin->getAllCategories();
$brands  = $admin->getAllBrands();

$editCatId   = isset($_GET['edit_cat'])   ? intval($_GET['edit_cat'])   : null;
$editBrandId = isset($_GET['edit_brand']) ? intval($_GET['edit_brand']) : null;
$activeTab   = isset($_GET['tab']) && $_GET['tab'] === 'brands' ? 'brands' : 'categories';
?>
<style>
/* ── Categories & Brands Page ── */
.cb-tabs {
    display: flex; gap: 0; margin-bottom: 24px;
    background: var(--bg-surface); border-radius: 14px; padding: 6px;
    box-shadow: 0 2px 12px rgba(15,23,42,.07);
    border: 1px solid rgba(148,163,184,.12);
    width: fit-content;
}
.cb-tab {
    padding: 10px 28px; border-radius: 10px; font-size: 14px;
    font-weight: 700; cursor: pointer; text-decoration: none;
    color: var(--text-muted); transition: all .18s; display: flex; align-items: center; gap: 8px;
}
.cb-tab.active { background: #6366f1; color: white; box-shadow: 0 4px 12px rgba(99,102,241,.3); }
.cb-tab:not(.active):hover { background: var(--bg-elevated); color: var(--text-primary); }

.cb-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px; align-items: start; }
@media(max-width:900px){ .cb-grid { grid-template-columns: 1fr; } }

.cb-card {
    background: var(--bg-surface); border-radius: 18px;
    box-shadow: 0 2px 16px rgba(15,23,42,.07);
    border: 1px solid rgba(148,163,184,.12);
    overflow: hidden;
}
.cb-card-header {
    padding: 18px 22px; border-bottom: 1px solid var(--border-subtle);
    font-weight: 800; font-size: 15px; color: var(--text-primary);
    display: flex; align-items: center; gap: 8px;
}
.cb-card-body { padding: 20px 22px; }

/* List items */
.cb-list { display: flex; flex-direction: column; gap: 8px; }
.cb-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 14px; border-radius: 12px;
    border: 1px solid #f1f5f9; background: #fafafa;
    transition: all .15s;
}
.cb-item:hover { border-color: #e0e7ff; background: #f5f3ff; }
.cb-item.editing { border-color: #6366f1; background: #eef2ff; }
.cb-item-icon {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 800; color: white;
}
.cb-item-info { flex: 1; min-width: 0; }
.cb-item-name { font-weight: 700; font-size: 14px; color: var(--text-primary); }
.cb-item-sub  { font-size: 11px; color: var(--text-faint); margin-top: 2px; }
.cb-item-actions { display: flex; gap: 6px; }

/* Brand logo */
.brand-logo {
    width: 38px; height: 38px; border-radius: 10px;
    object-fit: contain; border: 1px solid var(--border-subtle);
    background: var(--bg-elevated); padding: 4px;
}
.brand-placeholder {
    width: 38px; height: 38px; border-radius: 10px;
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 800; font-size: 14px; flex-shrink: 0;
}

/* Form styles */
.cb-form { display: flex; flex-direction: column; gap: 14px; }
.cb-input {
    width: 100%; padding: 10px 14px; border: 1px solid var(--border-muted);
    border-radius: 10px; font-size: 14px; font-family: inherit;
    color: var(--text-primary); background: var(--bg-elevated);
    transition: border-color .2s, box-shadow .2s;
}
.cb-input:focus {
    outline: none; border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: var(--bg-surface);
}
.cb-label { font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px; display: block; }
.cb-form-actions { display: flex; gap: 8px; }
.cb-form-actions .btn { flex: 1; justify-content: center; }
</style>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Danh mục & Thương hiệu</h1>
            <p>Quản lý phân loại và nhãn hàng sản phẩm</p>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="cb-tabs">
        <a href="?page=categories&tab=categories" class="cb-tab <?php echo $activeTab === 'categories' ? 'active' : ''; ?>">
            <i class="fas fa-layer-group"></i> Danh mục
            <span style="background:<?php echo $activeTab==='categories'?'rgba(255,255,255,.25)':'#ede9fe'; ?>;color:<?php echo $activeTab==='categories'?'white':'#6366f1'; ?>;border-radius:20px;padding:1px 8px;font-size:11px;">
                <?php echo count($cats); ?>
            </span>
        </a>
        <a href="?page=categories&tab=brands" class="cb-tab <?php echo $activeTab === 'brands' ? 'active' : ''; ?>">
            <i class="fas fa-trademark"></i> Thương hiệu
            <span style="background:<?php echo $activeTab==='brands'?'rgba(255,255,255,.25)':'#ede9fe'; ?>;color:<?php echo $activeTab==='brands'?'white':'#6366f1'; ?>;border-radius:20px;padding:1px 8px;font-size:11px;">
                <?php echo count($brands); ?>
            </span>
        </a>
    </div>

    <?php if ($activeTab === 'categories'): ?>
    <!-- ══════════ CATEGORIES TAB ══════════ -->
    <div class="cb-grid">

        <!-- Form thêm/sửa danh mục -->
        <div class="cb-card">
            <div class="cb-card-header">
                <i class="fas fa-<?php echo $editCatId ? 'edit' : 'plus-circle'; ?>" style="color:#6366f1;"></i>
                <?php echo $editCatId ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới'; ?>
            </div>
            <div class="cb-card-body">
                <?php
                $editCat = $editCatId ? $admin->getCategoryById($editCatId) : null;
                ?>
                <form method="POST" action="?page=categories&tab=categories<?php echo $editCatId ? '&action=update_cat&id='.$editCatId : '&action=create_cat'; ?>" class="cb-form">
                    <div>
                        <label class="cb-label">Tên danh mục <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="cat_name" class="cb-input" required
                               placeholder="VD: CPU, RAM, Màn hình..."
                               value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="cb-label">Slug <span style="color:var(--text-faint);font-weight:400;">(tự động tạo nếu để trống)</span></label>
                        <input type="text" name="cat_slug" class="cb-input"
                               placeholder="vd: cpu-processor"
                               value="<?php echo htmlspecialchars($editCat['slug'] ?? ''); ?>"
                               id="catSlugInput">
                    </div>
                    <div class="cb-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $editCatId ? 'save' : 'plus'; ?>"></i>
                            <?php echo $editCatId ? 'Lưu thay đổi' : 'Thêm danh mục'; ?>
                        </button>
                        <?php if ($editCatId): ?>
                            <a href="?page=categories&tab=categories" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Huỷ
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách danh mục -->
        <div class="cb-card">
            <div class="cb-card-header">
                <i class="fas fa-list" style="color:#6366f1;"></i>
                Danh sách danh mục
                <span style="margin-left:auto;font-size:12px;font-weight:600;color:var(--text-faint);"><?php echo count($cats); ?> danh mục</span>
            </div>
            <div class="cb-card-body">
                <?php if (empty($cats)): ?>
                    <div style="text-align:center;padding:32px;color:var(--text-faint);">
                        <i class="fas fa-layer-group" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px;"></i>
                        Chưa có danh mục nào
                    </div>
                <?php else: ?>
                <div class="cb-list">
                    <?php
                    $catColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#f97316','#0ea5e9','#84cc16','#ec4899'];
                    foreach ($cats as $i => $cat):
                        $bg = $catColors[$i % count($catColors)];
                        $isEditing = ($editCatId == $cat['id']);
                    ?>
                    <div class="cb-item <?php echo $isEditing ? 'editing' : ''; ?>">
                        <div class="cb-item-icon" style="background:<?php echo $bg; ?>;">
                            <?php echo strtoupper(mb_substr($cat['name'], 0, 1)); ?>
                        </div>
                        <div class="cb-item-info">
                            <div class="cb-item-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                            <div class="cb-item-sub">
                                <i class="fas fa-box" style="margin-right:3px;"></i><?php echo $cat['product_count']; ?> sản phẩm
                                &nbsp;·&nbsp; <?php echo htmlspecialchars($cat['slug'] ?? ''); ?>
                            </div>
                        </div>
                        <div class="cb-item-actions">
                            <a href="?page=categories&tab=categories&edit_cat=<?php echo $cat['id']; ?>"
                               class="btn btn-sm btn-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($cat['product_count'] == 0): ?>
                                <form method="POST"
                                      action="?page=categories&tab=categories&action=delete_cat"
                                      style="display:inline;"
                                      onsubmit="return confirm('Xoá danh mục &quot;<?php echo addslashes($cat['name']); ?>&quot;?')">
                                    <?php echo CsrfHelper::field(); ?>
                                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xoá">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-sm btn-danger" disabled title="Không thể xoá: có <?php echo $cat['product_count']; ?> sản phẩm" style="opacity:.4;cursor:not-allowed;">
                                    <i class="fas fa-lock"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ══════════ BRANDS TAB ══════════ -->
    <div class="cb-grid">

        <!-- Form thêm/sửa thương hiệu -->
        <div class="cb-card">
            <div class="cb-card-header">
                <i class="fas fa-<?php echo $editBrandId ? 'edit' : 'plus-circle'; ?>" style="color:#6366f1;"></i>
                <?php echo $editBrandId ? 'Chỉnh sửa thương hiệu' : 'Thêm thương hiệu mới'; ?>
            </div>
            <div class="cb-card-body">
                <?php $editBrand = $editBrandId ? $admin->getBrandById($editBrandId) : null; ?>
                <form method="POST" enctype="multipart/form-data"
                      action="?page=categories&tab=brands<?php echo $editBrandId ? '&action=update_brand&id='.$editBrandId : '&action=create_brand'; ?>"
                      class="cb-form">
                    <div>
                        <label class="cb-label">Tên thương hiệu <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="brand_name" class="cb-input" required
                               placeholder="VD: ASUS, Intel, Samsung..."
                               value="<?php echo htmlspecialchars($editBrand['name'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="cb-label">Logo thương hiệu <span style="color:var(--text-faint);font-weight:400;">(jpg/png/webp)</span></label>
                        <?php if (!empty($editBrand['image'])): ?>
                            <div style="margin-bottom:8px;display:flex;align-items:center;gap:10px;">
                                <img src="<?php echo BASE_URL; ?>public/img/brands/<?php echo htmlspecialchars($editBrand['image']); ?>"
                                     style="width:48px;height:48px;object-fit:contain;border-radius:8px;border:1px solid var(--border-subtle);padding:4px;background:var(--bg-elevated);"
                                     onerror="this.style.display='none'">
                                <span style="font-size:12px;color:var(--text-faint);">Logo hiện tại</span>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="brand_logo" class="cb-input" accept="image/*" style="padding:6px;">
                    </div>
                    <div class="cb-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $editBrandId ? 'save' : 'plus'; ?>"></i>
                            <?php echo $editBrandId ? 'Lưu thay đổi' : 'Thêm thương hiệu'; ?>
                        </button>
                        <?php if ($editBrandId): ?>
                            <a href="?page=categories&tab=brands" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Huỷ
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách thương hiệu -->
        <div class="cb-card">
            <div class="cb-card-header">
                <i class="fas fa-trademark" style="color:#6366f1;"></i>
                Danh sách thương hiệu
                <span style="margin-left:auto;font-size:12px;font-weight:600;color:var(--text-faint);"><?php echo count($brands); ?> thương hiệu</span>
            </div>
            <div class="cb-card-body">
                <?php if (empty($brands)): ?>
                    <div style="text-align:center;padding:32px;color:var(--text-faint);">
                        <i class="fas fa-trademark" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px;"></i>
                        Chưa có thương hiệu nào
                    </div>
                <?php else: ?>
                <div class="cb-list">
                    <?php foreach ($brands as $brand):
                        $isEditing = ($editBrandId == $brand['id']);
                    ?>
                    <div class="cb-item <?php echo $isEditing ? 'editing' : ''; ?>">
                        <?php if (!empty($brand['image'])): ?>
                            <img src="<?php echo BASE_URL; ?>public/img/brands/<?php echo htmlspecialchars($brand['image']); ?>"
                                 class="brand-logo"
                                 onerror="this.outerHTML='<div class=\'brand-placeholder\'><?php echo strtoupper(mb_substr($brand['name'],0,1)); ?></div>'">
                        <?php else: ?>
                            <div class="brand-placeholder"><?php echo strtoupper(mb_substr($brand['name'], 0, 1)); ?></div>
                        <?php endif; ?>

                        <div class="cb-item-info">
                            <div class="cb-item-name"><?php echo htmlspecialchars($brand['name']); ?></div>
                            <div class="cb-item-sub">
                                <i class="fas fa-box" style="margin-right:3px;"></i><?php echo $brand['product_count']; ?> sản phẩm
                            </div>
                        </div>
                        <div class="cb-item-actions">
                            <a href="?page=categories&tab=brands&edit_brand=<?php echo $brand['id']; ?>"
                               class="btn btn-sm btn-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($brand['product_count'] == 0): ?>
                                <form method="POST"
                                      action="?page=categories&tab=brands&action=delete_brand"
                                      style="display:inline;"
                                      onsubmit="return confirm('Xoá thương hiệu &quot;<?php echo addslashes($brand['name']); ?>&quot;?')">
                                    <?php echo CsrfHelper::field(); ?>
                                    <input type="hidden" name="id" value="<?php echo $brand['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xoá">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-sm btn-danger" disabled title="Không thể xoá: có <?php echo $brand['product_count']; ?> sản phẩm" style="opacity:.4;cursor:not-allowed;">
                                    <i class="fas fa-lock"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<script>
// Auto-generate slug từ tên danh mục
var catNameInput = document.querySelector('[name="cat_name"]');
var catSlugInput = document.getElementById('catSlugInput');
if (catNameInput && catSlugInput) {
    catNameInput.addEventListener('input', function() {
        if (catSlugInput.dataset.manualEdit) return;
        catSlugInput.value = makeSlug(this.value);
    });
    catSlugInput.addEventListener('input', function() {
        this.dataset.manualEdit = '1';
    });
}
function makeSlug(str) {
    var map = {'á':'a','à':'a','ả':'a','ã':'a','ạ':'a','ă':'a','ắ':'a','ặ':'a','ằ':'a','ẩ':'a','ẫ':'a','ấ':'a','ầ':'a','ậ':'a','â':'a',
               'é':'e','è':'e','ẻ':'e','ẽ':'e','ẹ':'e','ê':'e','ế':'e','ề':'e','ể':'e','ễ':'e','ệ':'e',
               'í':'i','ì':'i','ỉ':'i','ĩ':'i','ị':'i',
               'ó':'o','ò':'o','ỏ':'o','õ':'o','ọ':'o','ô':'o','ố':'o','ồ':'o','ổ':'o','ỗ':'o','ộ':'o','ơ':'o','ớ':'o','ờ':'o','ở':'o','ỡ':'o','ợ':'o',
               'ú':'u','ù':'u','ủ':'u','ũ':'u','ụ':'u','ư':'u','ứ':'u','ừ':'u','ử':'u','ữ':'u','ự':'u',
               'ý':'y','ỳ':'y','ỷ':'y','ỹ':'y','ỵ':'y','đ':'d'};
    str = str.toLowerCase();
    for (var k in map) str = str.split(k).join(map[k]);
    return str.replace(/[^a-z0-9\s-]/g,'').replace(/[\s-]+/g,'-').replace(/^-+|-+$/g,'');
}
</script>
