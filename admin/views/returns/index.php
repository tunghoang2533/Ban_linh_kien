<?php
// Admin Returns View
$returnCtrl = new ReturnAdminController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $returnCtrl->updateStatus(
        intval($_POST['return_id']),
        $_POST['status'],
        $_POST['admin_note'] ?? '',
        floatval($_POST['refund_amount'] ?? 0),
        $_SESSION['user_id']
    );
    header('Location: ?page=returns');
    exit;
}

$action = $_GET['action'] ?? 'list';
$stats  = $returnCtrl->getStats();
$filterStatus = $_GET['status'] ?? 'all';

$statCards = [
    'pending'    => ['label'=>'Chờ xử lý',  'color'=>'#f97316','bg'=>'#fff7ed','icon'=>'fas fa-clock'],
    'approved'   => ['label'=>'Đã duyệt',    'color'=>'#2563eb','bg'=>'#eff6ff','icon'=>'fas fa-check'],
    'processing' => ['label'=>'Đang xử lý',  'color'=>'#7c3aed','bg'=>'#f5f3ff','icon'=>'fas fa-cogs'],
    'completed'  => ['label'=>'Hoàn thành',  'color'=>'#16a34a','bg'=>'#f0fdf4','icon'=>'fas fa-check-double'],
    'rejected'   => ['label'=>'Từ chối',     'color'=>'#dc2626','bg'=>'#fef2f2','icon'=>'fas fa-times'],
];

if ($action === 'detail' && isset($_GET['id'])) {
    $rq = $returnCtrl->getById(intval($_GET['id']));
    if (!$rq) { echo '<div class="card"><div class="card-body">Không tìm thấy yêu cầu.</div></div>'; return; }
    $images = json_decode($rq['images'] ?? '[]', true) ?? [];
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="?page=returns" style="color:var(--text-muted);font-size:20px;"><i class="fas fa-arrow-left"></i></a>
    <h1 class="page-title" style="margin:0;"><i class="fas fa-undo-alt" style="color:#ef4444;"></i> Chi tiết yêu cầu #<?php echo $rq['id']; ?></h1>
</div>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <div>
        <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);margin-bottom:20px;">
            <div class="card-body" style="padding:24px;">
                <h5 style="margin:0 0 20px;font-weight:700;">Thông tin yêu cầu</h5>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <?php $typeMap = ['return'=>'Hoàn tiền','exchange'=>'Đổi hàng','warranty'=>'Bảo hành']; ?>
                    <div><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;">Khách hàng</label><p style="margin:4px 0 0;font-weight:700;"><?php echo htmlspecialchars($rq['full_name'] ?? ''); ?></p></div>
                    <div><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;">Số điện thoại</label><p style="margin:4px 0 0;"><?php echo htmlspecialchars($rq['phone'] ?? ''); ?></p></div>
                    <div><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;">Email</label><p style="margin:4px 0 0;"><?php echo htmlspecialchars($rq['email'] ?? ''); ?></p></div>
                    <div><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;">Đơn hàng</label><p style="margin:4px 0 0;"><a href="?page=orders&id=<?php echo $rq['order_id']; ?>">#<?php echo htmlspecialchars($rq['tracking_code'] ?? $rq['order_id']); ?></a></p></div>
                    <div><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;">Loại yêu cầu</label><p style="margin:4px 0 0;"><?php echo $typeMap[$rq['type']] ?? $rq['type']; ?></p></div>
                    <div><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;">Ngày gửi</label><p style="margin:4px 0 0;"><?php echo date('d/m/Y H:i', strtotime($rq['created_at'])); ?></p></div>
                </div>
                <div style="margin-top:18px;"><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;">Lý do</label>
                    <p style="margin:6px 0 0;background:var(--bg-elevated);padding:14px;border-radius:10px;color:var(--text-secondary);"><?php echo nl2br(htmlspecialchars($rq['reason'] ?? '')); ?></p>
                </div>
                <?php if ($images): ?>
                <div style="margin-top:18px;"><label style="font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;display:block;margin-bottom:8px;">Ảnh minh chứng</label>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <?php foreach ($images as $img): ?>
                        <a href="<?php echo BASE_URL; ?>public/img/returns/<?php echo htmlspecialchars($img); ?>" target="_blank">
                            <img src="<?php echo BASE_URL; ?>public/img/returns/<?php echo htmlspecialchars($img); ?>" style="width:90px;height:90px;object-fit:cover;border-radius:10px;border:2px solid #e2e8f0;">
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div>
        <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
            <div class="card-body" style="padding:24px;">
                <h5 style="margin:0 0 20px;font-weight:700;">Xử lý yêu cầu</h5>
                <form method="POST">
                    <input type="hidden" name="return_id" value="<?php echo $rq['id']; ?>">
                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">Trạng thái</label>
                        <select name="status" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
                            <option value="pending"    <?php selected($rq['status'],'pending'); ?>>⏳ Chờ xử lý</option>
                            <option value="approved"   <?php selected($rq['status'],'approved'); ?>>✅ Đã duyệt</option>
                            <option value="processing" <?php selected($rq['status'],'processing'); ?>>⚙️ Đang xử lý</option>
                            <option value="completed"  <?php selected($rq['status'],'completed'); ?>>🎉 Hoàn thành</option>
                            <option value="rejected"   <?php selected($rq['status'],'rejected'); ?>>❌ Từ chối</option>
                        </select>
                    </div>
                    <?php if ($rq['type'] === 'return'): ?>
                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">Số tiền hoàn trả (đ)</label>
                        <input type="number" name="refund_amount" value="<?php echo $rq['refund_amount'] ?? 0; ?>" min="0" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
                    </div>
                    <?php endif; ?>
                    <div style="margin-bottom:18px;">
                        <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">Ghi chú nội bộ</label>
                        <textarea name="admin_note" rows="4" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:vertical;"><?php echo htmlspecialchars($rq['admin_note'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary" style="width:100%;border-radius:10px;padding:11px;font-weight:700;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
    return;
}
?>
<div class="page-header" style="margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-undo-alt" style="color:#ef4444;"></i> Đổi trả / Bảo hành</h1>
</div>

<!-- Stat Cards -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;">
    <?php foreach ($statCards as $key => $sc): ?>
    <a href="?page=returns&status=<?php echo $key; ?>" style="text-decoration:none;">
    <div style="background:var(--bg-surface);border-radius:14px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:2px solid <?php echo $filterStatus===$key?$sc['color']:'transparent'; ?>;transition:all .2s;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div style="width:38px;height:38px;border-radius:10px;background:<?php echo $sc['bg']; ?>;display:flex;align-items:center;justify-content:center;">
                <i class="<?php echo $sc['icon']; ?>" style="color:<?php echo $sc['color']; ?>;"></i>
            </div>
            <span style="font-size:22px;font-weight:800;color:var(--text-primary);"><?php echo $stats[$key] ?? 0; ?></span>
        </div>
        <p style="margin:8px 0 0;font-size:12px;font-weight:600;color:var(--text-muted);"><?php echo $sc['label']; ?></p>
    </div></a>
    <?php endforeach; ?>
</div>

<!-- Filter bar -->
<div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
    <a href="?page=returns" style="padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?php echo !isset($_GET['status']) ? 'background:#6366f1;color:white;' : 'background:var(--bg-elevated);color:var(--text-muted);'; ?>">Tất cả</a>
    <?php foreach ($statCards as $key => $sc): ?>
    <a href="?page=returns&status=<?php echo $key; ?>" style="padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;<?php echo $filterStatus===$key ? "background:{$sc['color']};color:white;" : 'background:var(--bg-elevated);color:var(--text-muted);'; ?>"><?php echo $sc['label']; ?></a>
    <?php endforeach; ?>
</div>

<!-- Table -->
<?php
$returns = $returnCtrl->getAll($filterStatus);
$typeMap = ['return'=>'Hoàn tiền','exchange'=>'Đổi hàng','warranty'=>'Bảo hành'];
?>
<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
<div class="card-body" style="padding:0;">
<table class="admin-table" style="width:100%;border-collapse:collapse;">
    <thead><tr style="background:var(--bg-elevated);">
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);text-align:left;">#</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">Khách hàng</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">Đơn hàng</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">Loại</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">Lý do</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">Ngày tạo</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">Trạng thái</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);"></th>
    </tr></thead>
    <tbody>
    <?php if (empty($returns)): ?>
    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-faint);">Không có yêu cầu nào</td></tr>
    <?php else: foreach ($returns as $r):
        $sc = $statCards[$r['status']] ?? ['color'=>'#64748b','bg'=>'#f8fafc','label'=>$r['status']];
    ?>
    <tr style="border-bottom:1px solid var(--border-subtle);">
        <td style="padding:14px 16px;font-weight:700;color:var(--text-muted);">#<?php echo $r['id']; ?></td>
        <td style="padding:14px 16px;"><strong><?php echo htmlspecialchars($r['full_name'] ?? ''); ?></strong><br><small style="color:var(--text-faint);"><?php echo htmlspecialchars($r['phone'] ?? ''); ?></small></td>
        <td style="padding:14px 16px;"><a href="?page=returns&action=detail&id=<?php echo $r['id']; ?>" style="color:#2563eb;font-weight:700;"><?php echo htmlspecialchars($r['tracking_code'] ?? '#'.$r['order_id']); ?></a></td>
        <td style="padding:14px 16px;"><span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;background:var(--bg-elevated);color:var(--text-secondary);"><?php echo $typeMap[$r['type']] ?? $r['type']; ?></span></td>
        <td style="padding:14px 16px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;color:var(--text-secondary);"><?php echo htmlspecialchars(mb_substr($r['reason'] ?? '', 0, 60)); ?></td>
        <td style="padding:14px 16px;font-size:13px;color:var(--text-muted);"><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></td>
        <td style="padding:14px 16px;"><span style="padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;background:<?php echo $sc['bg']??'#f8fafc'; ?>;color:<?php echo $sc['color']??'#64748b'; ?>;"><?php echo $sc['label']; ?></span></td>
        <td style="padding:14px 16px;"><a href="?page=returns&action=detail&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary" style="border-radius:8px;font-size:12px;padding:5px 12px;"><i class="fas fa-eye"></i> Chi tiết</a></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div>
<?php
function selected($val, $check) { if ($val === $check) echo 'selected'; }
