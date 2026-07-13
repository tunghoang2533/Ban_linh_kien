<?php
/**
 * Trang quản lý Banner Slideshow
 * Cho phép: xem danh sách, thêm mới, sửa, xóa, bật/tắt, kéo thả thứ tự
 */
$banners = $bannerCtrl->getAllBanners();
?>
<main class="admin-main">
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-images" style="color:#6366f1;margin-right:10px;"></i>Quản lý Banner Slideshow</h1>
        <p>Quản lý ảnh quảng cáo hiển thị trên trang chủ · <strong><?php echo count($banners); ?></strong> banner</p>
    </div>
    <button type="button" id="btnShowAdd" class="btn btn-primary" onclick="document.getElementById('addSection').classList.toggle('hidden')">
        <i class="fas fa-plus"></i> Thêm banner mới
    </button>
</div>

<?php if (!empty($successMessage)): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- ===== FORM THÊM BANNER ===== -->
<section id="addSection" class="form-card hidden" style="margin-bottom:24px;">
    <h2 class="form-section-title"><i class="fas fa-plus-circle" style="color:#6366f1;margin-right:8px;"></i>Thêm banner mới</h2>
    <form method="POST" enctype="multipart/form-data" action="?page=banners&action=add">
        <?php echo renderBannerForm(); ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu banner</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('addSection').classList.add('hidden')">
                <i class="fas fa-times"></i> Hủy
            </button>
        </div>
    </form>
</section>

<!-- ===== DANH SÁCH BANNER ===== -->
<div class="dashboard-section">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i> Danh sách banner</h2>
        <span style="font-size:13px;color:var(--text-muted);">Kéo ☰ để sắp xếp thứ tự hiển thị</span>
    </div>

    <?php if (empty($banners)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-faint);">
        <i class="fas fa-images" style="font-size:48px;opacity:.3;display:block;margin-bottom:12px;"></i>
        Chưa có banner nào. Hãy thêm banner đầu tiên!
    </div>
    <?php else: ?>

    <div style="display:grid;gap:16px;" id="bannerSortList">
        <?php foreach ($banners as $b): ?>
        <?php
            $imgSrc = BASE_URL . 'public/img/banners/' . htmlspecialchars($b['image']);
            $isActive = (bool)$b['is_active'];
        ?>
        <div class="banner-card" data-id="<?php echo $b['id']; ?>" style="
            background:var(--bg-surface);border-radius:14px;border:1.5px solid <?php echo $isActive ? '#e0e7ff' : '#f1f5f9'; ?>;
            box-shadow:0 4px 16px rgba(0,0,0,.06);overflow:hidden;
            display:grid;grid-template-columns:auto 1fr auto;align-items:stretch;
            <?php echo !$isActive ? 'opacity:.6;' : ''; ?>
        ">
            <!-- Drag handle -->
            <div class="drag-handle" style="
                width:40px;display:flex;align-items:center;justify-content:center;
                background:var(--bg-elevated);border-right:1px solid #e2e8f0;cursor:grab;color:#cbd5e1;font-size:18px;
            " title="Kéo để sắp xếp">☰</div>

            <!-- Preview ảnh + info -->
            <div style="display:grid;grid-template-columns:240px 1fr;align-items:center;gap:0;">
                <!-- Thumbnail -->
                <div style="height:120px;overflow:hidden;position:relative;background:#0f172a;">
                    <img src="<?php echo $imgSrc; ?>"
                         alt="<?php echo htmlspecialchars($b['title']); ?>"
                         style="width:100%;height:100%;object-fit:cover;display:block;"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'240\' height=\'120\'%3E%3Crect width=\'240\' height=\'120\' fill=\'%23f1f5f9\'/%3E%3Ctext x=\'50%25\' y=\'55%25\' font-size=\'13\' fill=\'%2394a3b8\' text-anchor=\'middle\'%3EKhông tải được ảnh%3C/text%3E%3C/svg%3E'">
                    <!-- Accent badge -->
                    <span style="position:absolute;bottom:6px;left:6px;width:20px;height:20px;border-radius:50%;background:<?php echo htmlspecialchars($b['accent_color']); ?>;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.2);" title="Màu accent: <?php echo $b['accent_color']; ?>"></span>
                    <!-- Order badge -->
                    <span style="position:absolute;top:6px;left:6px;background:rgba(0,0,0,.6);color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;">
                        #<?php echo (int)$b['sort_order']; ?>
                    </span>
                </div>
                <!-- Info -->
                <div style="padding:14px 20px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                        <?php if ($b['tag']): ?>
                        <span style="background:rgba(99,102,241,0.12);color:#7c3aed;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;">
                            <?php echo htmlspecialchars($b['tag']); ?>
                        </span>
                        <?php endif; ?>
                        <span style="background:<?php echo $isActive ? '#d1fae5' : '#fee2e2'; ?>;color:<?php echo $isActive ? '#065f46' : '#991b1b'; ?>;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;">
                            <?php echo $isActive ? '✓ Đang hiển thị' : '✗ Đã ẩn'; ?>
                        </span>
                    </div>
                    <div style="font-weight:700;color:var(--text-primary);font-size:15px;margin-bottom:4px;line-height:1.3;">
                        <?php echo $b['title']; ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px;line-height:1.4;">
                        <?php echo htmlspecialchars($b['subtitle']); ?>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-faint);">
                        <i class="fas fa-link"></i>
                        <span><?php echo htmlspecialchars($b['btn_url'] ?: '—'); ?></span>
                        <span style="margin-left:8px;">Nút: <strong style="color:var(--text-secondary);"><?php echo htmlspecialchars($b['btn_text']); ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div style="padding:14px 16px;display:flex;flex-direction:column;gap:8px;justify-content:center;border-left:1px solid #f1f5f9;min-width:130px;">
                <button type="button" class="btn btn-sm btn-warning" onclick="toggleEditForm(<?php echo $b['id']; ?>)">
                    <i class="fas fa-edit"></i> Sửa
                </button>
                <form method="POST" action="?page=banners&action=toggle" style="display:inline;">
                    <?php echo CsrfHelper::field(); ?>
                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                    <button type="submit" class="btn btn-sm <?php echo $isActive ? 'btn-secondary' : 'btn-success'; ?>"
                            title="<?php echo $isActive ? 'Nhấn để ẩn' : 'Nhấn để hiện'; ?>">
                        <i class="fas <?php echo $isActive ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                        <?php echo $isActive ? 'Ẩn' : 'Hiện'; ?>
                    </button>
                </form>
                <form method="POST" action="?page=banners&action=delete" style="display:inline;"
                      onsubmit="return confirm('Xóa banner này? Hành động không thể hoàn tác!')">
                    <?php echo CsrfHelper::field(); ?>
                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </form>
            </div>
        </div>

        <!-- Edit form (hidden by default) -->
        <div id="editForm-<?php echo $b['id']; ?>" class="form-card hidden" style="margin-top:-8px;border-radius:0 0 14px 14px;border-top:none;margin-bottom:8px;">
            <h3 class="form-section-title" style="font-size:16px;">
                <i class="fas fa-edit" style="color:#f59e0b;margin-right:8px;"></i>Chỉnh sửa banner #<?php echo $b['id']; ?>
            </h3>
            <form method="POST" enctype="multipart/form-data" action="?page=banners&action=edit&id=<?php echo $b['id']; ?>">
                <?php echo renderBannerForm($b); ?>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                    <button type="button" class="btn btn-secondary" onclick="toggleEditForm(<?php echo $b['id']; ?>)">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:16px;text-align:right;">
        <button id="saveSortBtn" class="btn btn-primary" style="display:none;" onclick="saveSortOrder()">
            <i class="fas fa-save"></i> Lưu thứ tự mới
        </button>
    </div>
    <?php endif; ?>
</div>
</main>

<style>
.banner-card { transition: box-shadow .2s, transform .2s; }
.banner-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.1); }
.banner-card.sortable-ghost { opacity: .4; background: #f0f4ff; }
.drag-handle:active { cursor: grabbing; }
</style>

<script>
function toggleEditForm(id) {
    const el = document.getElementById('editForm-' + id);
    el.classList.toggle('hidden');
    if (!el.classList.contains('hidden')) {
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Preview ảnh khi chọn file
document.querySelectorAll('.banner-img-input').forEach(function(inp) {
    inp.addEventListener('change', function() {
        const previewId = this.dataset.preview;
        const preview = document.getElementById(previewId);
        if (!preview) return;
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-height:120px;border-radius:8px;border:2px solid #6366f1;margin-top:8px;">';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// Kéo thả sắp xếp (native HTML5 drag & drop)
(function() {
    const list = document.getElementById('bannerSortList');
    if (!list) return;
    let dragEl = null;

    list.querySelectorAll('.banner-card').forEach(function(card) {
        card.setAttribute('draggable', 'true');

        card.addEventListener('dragstart', function(e) {
            dragEl = this;
            setTimeout(() => this.classList.add('sortable-ghost'), 0);
            e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', function() {
            this.classList.remove('sortable-ghost');
            dragEl = null;
            document.getElementById('saveSortBtn').style.display = 'inline-flex';
        });
        card.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (dragEl && dragEl !== this) {
                const rect = this.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    list.insertBefore(dragEl, this);
                } else {
                    list.insertBefore(dragEl, this.nextSibling);
                }
            }
        });
    });
})();

function saveSortOrder() {
    const cards = document.querySelectorAll('#bannerSortList .banner-card');
    const orders = {};
    cards.forEach(function(card, idx) {
        orders[card.dataset.id] = idx + 1;
    });
    fetch('?page=banners&action=sort', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(orders)
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.success) {
            document.getElementById('saveSortBtn').style.display = 'none';
            // Cập nhật badge số thứ tự
            document.querySelectorAll('#bannerSortList .banner-card').forEach(function(card, idx) {
                const badge = card.querySelector('[style*="top:6px"]');
                if (badge) badge.textContent = '#' + (idx + 1);
            });
        }
    })
    .catch(() => alert('Lỗi khi lưu thứ tự. Vui lòng thử lại.'));
}
</script>

<?php
/**
 * Render form fields chung cho add/edit
 * $banner = null khi thêm mới, array khi sửa
 */
function renderBannerForm($banner = null) {
    $v = function($key, $default = '') use ($banner) {
        return htmlspecialchars($banner[$key] ?? $default);
    };
    $isEdit   = $banner !== null;
    $imgSrc   = $isEdit ? BASE_URL . 'public/img/banners/' . htmlspecialchars($banner['image']) : '';
    $previewId = 'imgPreview' . ($banner['id'] ?? 'new');
    ob_start();
    ?>
    <div class="form-grid">
        <div class="form-group form-group-full">
            <label class="form-label">Ảnh banner <?php echo $isEdit ? '' : '<span class="req">*</span>'; ?></label>
            <input class="form-control banner-img-input" type="file" name="image" accept="image/*"
                   data-preview="<?php echo $previewId; ?>" <?php echo $isEdit ? '' : 'required'; ?>>
            <p class="form-note"><i class="fas fa-info-circle"></i> Tỉ lệ khuyến nghị 16:5 (VD: 1200×420px). Định dạng: JPG, PNG, WEBP, GIF.</p>
            <div id="<?php echo $previewId; ?>">
                <?php if ($isEdit): ?>
                <img src="<?php echo $imgSrc; ?>" style="max-height:120px;border-radius:8px;border:2px solid #e2e8f0;margin-top:8px;"
                     onerror="this.style.display='none'">
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Tiêu đề <span class="req">*</span></label>
            <input class="form-control" type="text" name="title" value="<?php echo $v('title'); ?>"
                   placeholder="VD: LINH KIỆN MÁY TÍNH&lt;br&gt;CHÍNH HÃNG 100%" required>
            <p class="form-note"><i class="fas fa-info-circle"></i> Dùng &lt;br&gt; để xuống dòng.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Nhãn tag</label>
            <input class="form-control" type="text" name="tag" value="<?php echo $v('tag'); ?>"
                   placeholder="VD: 🔥 Mới nhất 2026">
        </div>

        <div class="form-group form-group-full">
            <label class="form-label">Mô tả phụ</label>
            <input class="form-control" type="text" name="subtitle" value="<?php echo $v('subtitle'); ?>"
                   placeholder="VD: CPU, GPU, RAM, SSD chính hãng — bảo hành đến 36 tháng.">
        </div>

        <div class="form-group">
            <label class="form-label">Nút CTA</label>
            <input class="form-control" type="text" name="btn_text" value="<?php echo $v('btn_text', 'Xem ngay'); ?>"
                   placeholder="VD: Mua sắm ngay">
        </div>

        <div class="form-group">
            <label class="form-label">Link nút</label>
            <input class="form-control" type="text" name="btn_url" value="<?php echo $v('btn_url'); ?>"
                   placeholder="VD: index.php?section=on_sale">
        </div>

        <div class="form-group">
            <label class="form-label">Màu accent</label>
            <div style="display:flex;gap:10px;align-items:center;">
                <input class="form-control" type="color" name="accent_color"
                       value="<?php echo $v('accent_color', '#6366f1'); ?>"
                       style="width:60px;height:42px;padding:2px;cursor:pointer;">
                <input class="form-control" type="text" id="accentText<?php echo $banner['id'] ?? 'new'; ?>"
                       value="<?php echo $v('accent_color', '#6366f1'); ?>"
                       placeholder="#6366f1" style="flex:1;"
                       oninput="syncColor(this,'color')" readonly>
            </div>
            <p class="form-note"><i class="fas fa-info-circle"></i> Màu của tag, nút, progress bar trên slide này.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Thứ tự hiển thị</label>
            <input class="form-control" type="number" name="sort_order"
                   value="<?php echo $v('sort_order', '0'); ?>" min="0" step="1" placeholder="0">
        </div>

        <div class="form-group" style="display:flex;align-items:center;gap:12px;padding-top:28px;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600;color:var(--text-secondary);">
                <input type="checkbox" name="is_active" value="1" <?php echo ($banner === null || $banner['is_active']) ? 'checked' : ''; ?>
                       style="width:18px;height:18px;accent-color:#10b981;cursor:pointer;">
                Hiển thị ngay
            </label>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
