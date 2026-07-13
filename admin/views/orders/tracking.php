<?php
/**
 * Shipping Tracking Management — Theo dõi vận chuyển realtime
 * URL: ?page=shipping_carriers
 */

$statusBadges = [
    'pending'   => ['label'=>'Chờ lấy hàng', 'bg'=>'#fef3c7', 'text'=>'#92400e', 'icon'=>'fa-clock'],
    'picked_up' => ['label'=>'Đã lấy hàng',  'bg'=>'#dbeafe', 'text'=>'#1e40af', 'icon'=>'fa-box'],
    'in_transit'=> ['label'=>'Đang vận chuyển','bg'=>'#ede9fe','text'=>'#5b21b6', 'icon'=>'fa-truck'],
    'delivered' => ['label'=>'Đã giao hàng',  'bg'=>'#d1fae5', 'text'=>'#065f46', 'icon'=>'fa-check-circle'],
    'failed'    => ['label'=>'Giao thất bại', 'bg'=>'#fee2e2', 'text'=>'#991b1b', 'icon'=>'fa-exclamation-circle'],
    'returned'  => ['label'=>'Hoàn hàng',     'bg'=>'#f1f5f9', 'text'=>'#64748b', 'icon'=>'fa-undo-alt'],
];

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tracking'])) {
    $soId = intval($_POST['shipping_order_id']);
    $newStatus = $_POST['new_status'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if ($soId && $newStatus) {
        // Cập nhật trạng thái shipping_order
        $db->prepare("UPDATE shipping_orders SET status=?, carrier_status=?, carrier_status_text=?, carrier_status_updated_at=NOW() WHERE id=?")
           ->execute([$newStatus, $newStatus, $description, $soId]);
        
        // Thêm event vào tracking
        $orderId = $db->prepare("SELECT order_id FROM shipping_orders WHERE id=?");
        $orderId->execute([$soId]);
        $oid = $orderId->fetchColumn();
        
        $db->prepare("INSERT INTO shipping_tracking_events (shipping_order_id, order_id, status, location, description, event_date) VALUES (?,?,?,?,?,NOW())")
           ->execute([$soId, $oid, $newStatus, $location, $description]);
        
        header('Location: ?page=shipping_carriers&success=updated');
        exit;
    }
}
?>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-truck" style="color:#22d3ee;margin-right:10px;"></i>Theo dõi vận chuyển</h1>
            <p>Quản lý trạng thái vận đơn realtime</p>
        </div>
        <a href="?page=orders" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Đơn hàng</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success" style="margin-bottom:20px;"><i class="fas fa-check-circle"></i> Đã cập nhật trạng thái vận chuyển!</div>
    <?php endif; ?>

    <!-- Carriers Status -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
        <?php foreach ($carriers as $c): ?>
        <div style="background:var(--bg-surface);border-radius:12px;padding:16px;border:1px solid var(--border-subtle);text-align:center;">
            <div style="font-weight:700;color:var(--text-primary);font-size:14px;"><?php echo htmlspecialchars($c['name']); ?></div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px;"><?php echo htmlspecialchars($c['code']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tracking Orders Table -->
    <div style="background:var(--bg-surface);border-radius:14px;border:1px solid var(--border-subtle);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--text-primary);">
                <i class="fas fa-shipping-fast"></i> Vận đơn đang theo dõi (<?php echo count($trackingOrders); ?>)
            </h3>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table" style="margin:0;">
                <thead>
                    <tr>
                        <th>Đơn hàng</th>
                        <th>Đơn vị VC</th>
                        <th>Mã vận đơn</th>
                        <th>Trạng thái</th>
                        <th>Mô tả</th>
                        <th>Cập nhật gần nhất</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trackingOrders)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-faint);">
                        <i class="fas fa-box-open" style="font-size:36px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                        Chưa có vận đơn nào. Tạo vận đơn từ trang Quản lý đơn hàng → Vận đơn.
                    </td></tr>
                    <?php else: foreach ($trackingOrders as $to): 
                        $st = $statusBadges[$to['status'] ?? 'pending'] ?? $statusBadges['pending'];
                        $orderId = $to['order_id'];
                        
                        // Lấy events
                        $evtStmt = $db->prepare("SELECT * FROM shipping_tracking_events WHERE shipping_order_id=? ORDER BY event_date DESC LIMIT 5");
                        $evtStmt->execute([$to['id']]);
                        $events = $evtStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <tr>
                        <td>
                            <a href="?page=orders&action=detail&id=<?php echo $orderId; ?>" style="font-weight:700;color:#6366f1;text-decoration:none;">
                                #<?php echo $orderId; ?>
                            </a>
                            <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($to['customer_name'] ?? ''); ?></div>
                        </td>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($to['carrier_name'] ?? $to['carrier']); ?></td>
                        <td>
                            <span style="font-family:monospace;font-weight:700;font-size:13px;color:var(--text-primary);"><?php echo htmlspecialchars($to['tracking_code']); ?></span>
                            <?php if (!empty($to['carrier_url'])): ?>
                            <a href="<?php echo $to['carrier_url'] . urlencode($to['tracking_code']); ?>" target="_blank" style="display:block;font-size:10px;color:#6366f1;">
                                <i class="fas fa-external-link-alt"></i> Tra cứu
                            </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $st['bg']; ?>;color:<?php echo $st['text']; ?>;">
                                <i class="fas <?php echo $st['icon']; ?>"></i> <?php echo $st['label']; ?>
                            </span>
                        </td>
                        <td style="max-width:200px;font-size:12px;color:var(--text-secondary);">
                            <?php echo htmlspecialchars(mb_strimwidth($to['carrier_status_text'] ?? '', 0, 50, '...')); ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            <?php echo $to['carrier_status_updated_at'] ? date('d/m/Y H:i', strtotime($to['carrier_status_updated_at'])) : date('d/m/Y', strtotime($to['created_at'])); ?>
                            <?php if (!empty($events)): ?>
                            <div style="margin-top:6px;padding:6px;background:var(--bg-elevated);border-radius:6px;font-size:10px;max-height:80px;overflow-y:auto;">
                                <?php foreach ($events as $evt): ?>
                                <div style="display:flex;gap:4px;margin:2px 0;">
                                    <span style="color:var(--text-faint);white-space:nowrap;"><?php echo date('H:i', strtotime($evt['event_date'])); ?></span>
                                    <span style="color:var(--text-muted);"><?php echo htmlspecialchars(mb_strimwidth($evt['description'], 0, 40, '...')); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="openTrackingModal(<?php echo $to['id']; ?>, '<?php echo $to['status'] ?? 'pending'; ?>')">
                                <i class="fas fa-edit"></i> Cập nhật
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal cập nhật trạng thái -->
<div id="trackingModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px;">
    <div style="background:var(--bg-surface);border-radius:18px;padding:28px;width:100%;max-width:440px;box-shadow:0 25px 60px rgba(0,0,0,.2);">
        <h3 style="margin:0 0 20px;font-size:16px;font-weight:800;"><i class="fas fa-truck" style="color:#6366f1;"></i> Cập nhật trạng thái vận chuyển</h3>
        <form method="POST">
            <input type="hidden" name="shipping_order_id" id="trackingSoId">
            <input type="hidden" name="update_tracking" value="1">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">Trạng thái</label>
                <select name="new_status" class="form-control" id="trackingStatus">
                    <?php foreach ($statusBadges as $k => $sb): ?>
                    <option value="<?php echo $k; ?>"><?php echo $sb['label']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">Địa điểm</label>
                <input type="text" name="location" class="form-control" placeholder="VD: Trạm trung chuyển Bình Thạnh">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">Mô tả chi tiết</label>
                <textarea name="description" rows="2" class="form-control" placeholder="VD: Hàng đã đến kho trung chuyển, dự kiến giao trong 2 ngày..."></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="document.getElementById('trackingModal').style.display='none'" class="btn btn-secondary" style="flex:1;">Hủy</button>
                <button type="submit" class="btn btn-primary" style="flex:2;"><i class="fas fa-save"></i> Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTrackingModal(id, currentStatus) {
    document.getElementById('trackingSoId').value = id;
    document.getElementById('trackingStatus').value = currentStatus;
    document.getElementById('trackingModal').style.display = 'flex';
}
// Close modal on overlay click
document.getElementById('trackingModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
