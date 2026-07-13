<?php
// Admin Notifications View
$notifCtrl = new NotificationAdminController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_notif'])) { $notifCtrl->create($_POST, $_SESSION['user_id'] ?? null); header('Location: ?page=notifications'); exit; }
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) { $notifCtrl->delete(intval($_GET['delete'])); header('Location: ?page=notifications'); exit; }
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) { $notifCtrl->toggle(intval($_GET['toggle'])); header('Location: ?page=notifications'); exit; }

$notifications = $notifCtrl->getAll();
$typeConfig = [
    'info'    => ['label'=>'Thông tin',  'color'=>'#2563eb','bg'=>'#dbeafe','icon'=>'fa-info-circle'],
    'warning' => ['label'=>'Cảnh báo',   'color'=>'#d97706','bg'=>'#fef3c7','icon'=>'fa-exclamation-triangle'],
    'promo'   => ['label'=>'Khuyến mãi', 'color'=>'#7c3aed','bg'=>'#ede9fe','icon'=>'fa-tag'],
    'system'  => ['label'=>'Hệ thống',   'color'=>'#dc2626','bg'=>'#fee2e2','icon'=>'fa-cog'],
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-bell" style="color:#f59e0b;"></i> Thông báo hệ thống</h1>
</div>    <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:20px;">
        <!-- Create Form -->
        <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);height:fit-content;">
        <div class="card-body" style="padding:24px;">
            <h5 style="margin:0 0 20px;font-weight:700;color:var(--text-primary);"><i class="fas fa-plus-circle" style="color:#f59e0b;"></i> Tạo thông báo mới</h5>
            <form method="POST">
                <div style="margin-bottom:14px;">
                    <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tiêu đề *</label>
                    <input type="text" name="title" required class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="Tiêu đề thông báo...">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Nội dung *</label>
                    <textarea name="message" required rows="3" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:vertical;" placeholder="Nội dung thông báo..."></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Loại</label>
                        <select name="type" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
                            <?php foreach ($typeConfig as $k=>$tc): ?>
                            <option value="<?php echo $k; ?>"><?php echo $tc['icon']??''; ?> <?php echo $tc['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Đối tượng</label>
                        <select name="target" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
                            <option value="all">Tất cả người dùng</option>
                            <option value="registered">Đã đăng ký</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:18px;">
                    <label class="form-check-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="send_push" value="1" checked>
                        <i class="fas fa-bell" style="color:#6366f1;"></i>
                        <strong>Gửi cả Push Notification (trình duyệt)</strong>
                    </label>
                    <p class="form-note" style="margin:4px 0 0 28px;">Người dùng sẽ nhận thông báo ngay cả khi không mở trang web.</p>
                </div>
                <div style="margin-bottom:18px;">
                    <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Hết hạn lúc (để trống = không hết hạn)</label>
                    <input type="datetime-local" name="expires_at" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
                </div>
                <button type="submit" name="create_notif" class="btn btn-primary" style="width:100%;border-radius:12px;padding:11px;font-weight:700;background:linear-gradient(135deg,#f59e0b,#d97706);border:none;">
                    <i class="fas fa-paper-plane"></i> Gửi thông báo & Push
                </button>
            </form>
        </div></div>

    <!-- List -->
    <div>
        <?php if (empty($notifications)): ?>
        <div style="text-align:center;padding:60px;color:var(--text-faint);background:var(--bg-surface);border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
            <i class="fas fa-bell-slash" style="font-size:48px;margin-bottom:12px;display:block;opacity:.3;"></i>
            Chưa có thông báo nào
        </div>
        <?php else: foreach ($notifications as $n):
            $tc = $typeConfig[$n['type']] ?? ['label'=>$n['type'],'color'=>'#64748b','bg'=>'#f1f5f9','icon'=>'fa-bell'];
            $isExpired = !empty($n['expires_at']) && strtotime($n['expires_at']) < time();
        ?>
        <div style="background:var(--bg-surface);border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.06);padding:18px 20px;margin-bottom:14px;border-left:4px solid <?php echo $tc['color']; ?>;<?php echo !$n['is_active']?'opacity:.6;':''; ?>">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                        <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $tc['bg']; ?>;color:<?php echo $tc['color']; ?>;display:inline-flex;align-items:center;gap:5px;">
                            <i class="fas <?php echo $tc['icon']; ?>" style="font-size:10px;"></i><?php echo $tc['label']; ?>
                        </span>
                        <?php if (!$n['is_active']): ?><span style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:var(--bg-elevated);color:var(--text-faint);">TẮT</span><?php endif; ?>
                        <?php if ($isExpired): ?><span style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#fef2f2;color:#dc2626;">HẾT HẠN</span><?php endif; ?>
                    </div>
                    <h4 style="margin:0 0 4px;font-size:14px;font-weight:800;color:var(--text-primary);"><?php echo htmlspecialchars($n['title']); ?></h4>
                    <p style="margin:0;font-size:13px;color:var(--text-muted);"><?php echo htmlspecialchars(mb_substr($n['message'],0,100)); ?></p>
                    <div style="display:flex;gap:16px;margin-top:8px;">
                        <span style="font-size:11px;color:var(--text-faint);"><i class="fas fa-users"></i> <?php echo $n['target']==='all'?'Tất cả':'Đã đăng ký'; ?></span>
                        <span style="font-size:11px;color:var(--text-faint);"><i class="fas fa-eye"></i> <?php echo number_format($n['read_count']??0); ?> lượt đọc</span>
                        <span style="font-size:11px;color:var(--text-faint);"><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($n['created_at'])); ?></span>
                    </div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <a href="?page=notifications&toggle=<?php echo $n['id']; ?>" class="btn btn-sm" style="background:rgba(245,158,11,0.12);color:#fbbf24;border:none;border-radius:8px;padding:6px 10px;font-size:12px;" title="<?php echo $n['is_active']?'Tắt':'Bật'; ?>">
                        <i class="fas fa-<?php echo $n['is_active']?'pause':'play'; ?>"></i>
                    </a>
                    <a href="?page=notifications&delete=<?php echo $n['id']; ?>" class="btn btn-sm" style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:6px 10px;font-size:12px;" onclick="return confirm('Xóa thông báo này?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
