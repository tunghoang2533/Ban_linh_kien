<?php
// Admin Shipping View
// Lưu ý: POST/GET handling và redirect đã được xử lý trong admin/index.php
// trước khi HTML được render để tránh lỗi "headers already sent".
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-truck" style="color:#6366f1;"></i> Quản lý phí vận chuyển</h1>
    <button onclick="document.getElementById('modal-add').style.display='flex'" class="btn btn-primary" style="background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;border-radius:12px;padding:10px 20px;font-weight:700;">
        <i class="fas fa-plus"></i> Thêm vùng giao hàng
    </button>
</div>

<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);margin-bottom:20px;">
<div class="card-body" style="padding:20px;">
    <div style="display:flex;align-items:center;gap:10px;background:#eff6ff;padding:14px 18px;border-radius:12px;">
        <i class="fas fa-info-circle" style="color:#2563eb;font-size:18px;"></i>
        <div style="font-size:13px;color:#1d4ed8;">
            <strong>Cách hoạt động:</strong> Hệ thống tìm vùng phù hợp từ trên xuống. Nếu <strong>tỉnh/thành phố</strong> để trống → áp dụng cho tất cả địa điểm. Khi đơn đạt ngưỡng <strong>miễn phí ship</strong> → phí = 0.
        </div>
    </div>
</div>
</div>

<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
<div class="card-body" style="padding:0;">
<table style="width:100%;border-collapse:collapse;">
    <thead><tr style="background:var(--bg-elevated);">
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);text-align:left;">TÊN VÙNG</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">TỈNH/THÀNH ÁP DỤNG</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">PHÍ CƠ BẢN</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">MIỄN PHÍ TỪ</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">TRẠNG THÁI</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);"></th>
    </tr></thead>
    <tbody>
    <?php if (empty($zones)): ?>
    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-faint);">Chưa có vùng giao hàng nào</td></tr>
    <?php else: foreach ($zones as $z):
        $provArr = json_decode($z['provinces'] ?? '[]', true) ?? [];
    ?>
    <tr style="border-bottom:1px solid var(--border-subtle);">
        <td style="padding:14px 16px;font-weight:700;color:var(--text-primary);"><?php echo htmlspecialchars($z['zone_name']); ?></td>
        <td style="padding:14px 16px;font-size:13px;color:var(--text-secondary);">
            <?php if (empty($provArr)): ?>
                <span style="background:#dbeafe;color:#1d4ed8;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;">Tất cả địa điểm</span>
            <?php else: ?>
                <?php foreach (array_slice($provArr, 0, 3) as $p): ?><span style="background:var(--bg-elevated);color:var(--text-secondary);padding:2px 8px;border-radius:6px;font-size:12px;margin:2px;"><?php echo htmlspecialchars($p); ?></span><?php endforeach; ?>
                <?php if (count($provArr) > 3): ?><span style="font-size:11px;color:var(--text-faint);">+<?php echo count($provArr)-3; ?> khác</span><?php endif; ?>
            <?php endif; ?>
        </td>
        <td style="padding:14px 16px;font-weight:700;color:#2563eb;"><?php echo number_format($z['base_fee'], 0, ',', '.'); ?>đ</td>
        <td style="padding:14px 16px;font-size:13px;">
            <?php echo $z['free_shipping_min'] > 0 ? '<span style="color:#16a34a;font-weight:700;">' . number_format($z['free_shipping_min'], 0, ',', '.') . 'đ</span>' : '<span style="color:var(--text-faint);">Không có</span>'; ?>
        </td>
        <td style="padding:14px 16px;">
            <span style="padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;<?php echo $z['is_active'] ? 'background:rgba(34,197,94,0.12);color:#4ade80;' : 'background:rgba(239,68,68,0.12);color:#f87171;'; ?>">
                <?php echo $z['is_active'] ? 'Hoạt động' : 'Tắt'; ?>
            </span>
        </td>
        <td style="padding:14px 16px;white-space:nowrap;">
            <a href="?page=shipping&edit=<?php echo $z['id']; ?>" class="btn btn-sm" style="background:#dbeafe;color:#1d4ed8;border:none;border-radius:8px;padding:5px 10px;font-size:12px;margin-right:4px;"><i class="fas fa-edit"></i></a>
            <a href="?page=shipping&toggle=<?php echo $z['id']; ?>" class="btn btn-sm" style="background:rgba(245,158,11,0.12);color:#fbbf24;border:none;border-radius:8px;padding:5px 10px;font-size:12px;margin-right:4px;"><i class="fas fa-<?php echo $z['is_active'] ? 'pause' : 'play'; ?>"></i></a>
            <a href="?page=shipping&delete=<?php echo $z['id']; ?>" class="btn btn-sm" style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:5px 10px;font-size:12px;" onclick="return confirm('Xóa vùng này?')"><i class="fas fa-trash"></i></a>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div>

<!-- Modal Add -->
<div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface);border-radius:20px;padding:32px;width:100%;max-width:520px;box-shadow:0 24px 60px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <h3 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-plus-circle" style="color:#6366f1;"></i> Thêm vùng giao hàng</h3>
            <button onclick="document.getElementById('modal-add').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-faint);">&times;</button>
        </div>
        <form method="POST">
            <?php echo renderShippingForm(); ?>
            <button type="submit" name="create_zone" class="btn btn-primary" style="width:100%;margin-top:20px;border-radius:12px;padding:12px;font-weight:700;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;">
                <i class="fas fa-save"></i> Lưu vùng giao hàng
            </button>
        </form>
    </div>
</div>

<?php if ($editZone):
    $editProvArr = json_decode($editZone['provinces'] ?? '[]', true) ?? [];
?>
<div id="modal-edit" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface);border-radius:20px;padding:32px;width:100%;max-width:520px;box-shadow:0 24px 60px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <h3 style="margin:0;font-size:18px;font-weight:800;"><i class="fas fa-edit" style="color:#2563eb;"></i> Sửa vùng giao hàng</h3>
            <a href="?page=shipping" style="color:var(--text-faint);font-size:22px;text-decoration:none;">&times;</a>
        </div>
        <form method="POST">
            <input type="hidden" name="zone_id" value="<?php echo $editZone['id']; ?>">
            <?php echo renderShippingForm($editZone, $editProvArr); ?>
            <button type="submit" name="update_zone" class="btn btn-primary" style="width:100%;margin-top:20px;border-radius:12px;padding:12px;font-weight:700;background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
function renderShippingForm($zone = [], $provArr = []) {
    ob_start(); ?>
    <div style="margin-bottom:16px;">
        <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">Tên vùng *</label>
        <input type="text" name="zone_name" value="<?php echo htmlspecialchars($zone['zone_name'] ?? ''); ?>" required class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="VD: Nội thành TP.HCM">
    </div>
    <div style="margin-bottom:16px;">
        <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">Tỉnh/Thành phố (phân cách bằng dấu phẩy)</label>
        <input type="text" name="provinces" value="<?php echo htmlspecialchars(implode(', ', $provArr)); ?>" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="Để trống = áp dụng tất cả">
        <p style="font-size:11px;color:var(--text-faint);margin:4px 0 0;">VD: Hồ Chí Minh, Bình Dương, Đồng Nai</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div>
            <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">Phí cơ bản (đ)</label>
            <input type="number" name="base_fee" value="<?php echo $zone['base_fee'] ?? 50000; ?>" min="0" step="1000" required class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
        </div>
        <div>
            <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">Miễn phí từ (đ)</label>
            <input type="number" name="free_shipping_min" value="<?php echo $zone['free_shipping_min'] ?? 0; ?>" min="0" step="10000" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
            <p style="font-size:11px;color:var(--text-faint);margin:4px 0 0;">0 = không miễn phí</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
