<?php
/**
 * Admin: Email Queue Manager
 * URL: ?page=settings&action=email_queue
 */

// Lấy filter
$statusFilter = $_GET['status'] ?? 'all';
$pageNum      = max(1, intval($_GET['qpage'] ?? 1));
$perPage      = 25;
$offset       = ($pageNum - 1) * $perPage;

// Action: retry email thất bại
if (isset($_GET['retry_id'])) {
    $retryId = intval($_GET['retry_id']);
    $db->prepare("UPDATE email_queue SET status='pending', attempts=0, error_message=NULL WHERE id=:id AND status='failed'")
       ->execute([':id' => $retryId]);
    header('Location: ?page=settings&action=email_queue&success=retried');
    exit;
}

// Action: xóa email đã gửi
if (isset($_GET['delete_sent']) && $_GET['delete_sent'] === '1') {
    $db->exec("DELETE FROM email_queue WHERE status='sent'");
    header('Location: ?page=settings&action=email_queue&success=cleared');
    exit;
}

// Đếm theo status
$counts = [];
$cntRows = $db->query("SELECT status, COUNT(*) AS cnt FROM email_queue GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cntRows as $r) $counts[$r['status']] = (int)$r['cnt'];
$counts['all'] = array_sum($counts);

// Lấy danh sách
$where = ($statusFilter !== 'all') ? "WHERE status = " . $db->quote($statusFilter) : '';
$total  = (int)$db->query("SELECT COUNT(*) FROM email_queue {$where}")->fetchColumn();
$emails = $db->query("SELECT * FROM email_queue {$where} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}")->fetchAll(PDO::FETCH_ASSOC);
$totalPages = ceil($total / $perPage);

$statusMap = [
    'pending' => ['label'=>'Chờ gửi',    'bg'=>'#fef3c7','text'=>'#92400e'],
    'sent'    => ['label'=>'Đã gửi',     'bg'=>'#d1fae5','text'=>'#065f46'],
    'failed'  => ['label'=>'Thất bại',   'bg'=>'#fee2e2','text'=>'#991b1b'],
];
?>
<style>
.eq-stat { background: var(--bg-surface); border-radius: 14px; padding: 18px 22px; box-shadow: none; display: flex; align-items: center; gap: 14px; }
.eq-stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.eq-stat-val { font-size: 24px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.eq-stat-lbl { font-size: 12px; color: var(--text-faint); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
</style>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-envelope-open-text" style="color:#6366f1;"></i> Email Queue</h1>
            <p>Xem trạng thái và quản lý hàng đợi gửi email</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?php echo BASE_URL; ?>email_queue_worker.php?token=banlinh_email_secret_2026"
               target="_blank"
               class="btn btn-primary">
                <i class="fas fa-play"></i> Chạy worker ngay
            </a>
            <?php if (!empty($counts['sent'])): ?>
            <a href="?page=settings&action=email_queue&delete_sent=1"
               class="btn btn-sm btn-warning"
               onclick="return confirm('Xóa tất cả email đã gửi?')">
                <i class="fas fa-trash"></i> Xóa đã gửi
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo ['retried'=>'Đã đặt lại để thử gửi lại.','cleared'=>'Đã xóa các email đã gửi.'][$_GET['success']] ?? ''; ?>
    </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        <?php
        $stats = [
            ['label'=>'Tổng cộng',   'value'=>$counts['all']     ?? 0, 'bg'=>'#eff6ff', 'icon_bg'=>'#2563eb', 'icon'=>'envelope'],
            ['label'=>'Chờ gửi',     'value'=>$counts['pending'] ?? 0, 'bg'=>'#fffbeb', 'icon_bg'=>'#f59e0b', 'icon'=>'clock'],
            ['label'=>'Đã gửi',      'value'=>$counts['sent']    ?? 0, 'bg'=>'#f0fdf4', 'icon_bg'=>'#10b981', 'icon'=>'check-circle'],
            ['label'=>'Thất bại',    'value'=>$counts['failed']  ?? 0, 'bg'=>'#fef2f2', 'icon_bg'=>'#ef4444', 'icon'=>'times-circle'],
        ];
        foreach ($stats as $s): ?>
        <div class="eq-stat" style="background:<?php echo $s['bg']; ?>;">
            <div class="eq-stat-icon" style="background:<?php echo $s['icon_bg']; ?>22;">
                <i class="fas fa-<?php echo $s['icon']; ?>" style="color:<?php echo $s['icon_bg']; ?>;"></i>
            </div>
            <div>
                <div class="eq-stat-val"><?php echo number_format($s['value']); ?></div>
                <div class="eq-stat-lbl"><?php echo $s['label']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Worker Info -->
    <div class="card" style="border-radius:14px;border:none;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:20px;">
        <div class="card-body" style="padding:16px 20px;">
            <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;">
                <div style="flex:1;min-width:280px;">
                    <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:var(--text-primary);"><i class="fas fa-info-circle" style="color:#6366f1;"></i> Cách thiết lập tự động</p>
                    <p style="margin:0;font-size:12px;color:var(--text-muted);line-height:1.7;">
                        <strong>Windows Task Scheduler:</strong> Chạy file <code>email_queue_worker.php</code> bằng PHP mỗi 5 phút.<br>
                        <strong>Linux Cron:</strong> <code>*/5 * * * * php <?php echo realpath(__DIR__ . '/../../../../email_queue_worker.php'); ?> >> /tmp/email_queue.log</code>
                    </p>
                </div>
                <div style="flex:1;min-width:280px;">
                    <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:var(--text-primary);"><i class="fas fa-cog" style="color:#f59e0b;"></i> Cấu hình SMTP</p>
                    <p style="margin:0;font-size:12px;color:var(--text-muted);line-height:1.7;">
                        Vào <strong>Cài đặt cửa hàng</strong> → tab SMTP để nhập thông tin Gmail/Zoho/Mailgun.<br>
                        Hoặc để trống nếu máy chủ hosting đã có cấu hình <code>PHP mail()</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <?php
        $tabs = ['all'=>'Tất cả','pending'=>'Chờ gửi','sent'=>'Đã gửi','failed'=>'Thất bại'];
        foreach ($tabs as $val => $label):
            $cnt   = $counts[$val] ?? 0;
            $isAct = $statusFilter === $val;
            $bgC   = $isAct ? '#6366f1' : '#f8fafc';
            $txC   = $isAct ? 'white'   : '#64748b';
            $border= $isAct ? '#6366f1' : '#e2e8f0';
        ?>
        <a href="?page=settings&action=email_queue&status=<?php echo $val; ?>"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:20px;font-size:12px;font-weight:700;text-decoration:none;background:<?php echo $bgC; ?>;color:<?php echo $txC; ?>;border:1.5px solid <?php echo $border; ?>;transition:all .15s;">
            <?php echo $label; ?>
            <span style="background:rgba(0,0,0,.1);border-radius:20px;padding:1px 7px;font-size:10px;"><?php echo $cnt; ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>Người nhận</th>
                    <th>Tiêu đề</th>
                    <th style="width:100px;">Trạng thái</th>
                    <th style="width:70px;">Thử</th>
                    <th>Lập lịch</th>
                    <th>Gửi lúc</th>
                    <th style="width:80px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($emails)): ?>
                <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-faint);">Không có email nào</td></tr>
                <?php else: foreach ($emails as $em):
                    $stCfg = $statusMap[$em['status']] ?? ['label'=>$em['status'],'bg'=>'#f1f5f9','text'=>'#64748b'];
                ?>
                <tr>
                    <td style="color:var(--text-faint);font-size:12px;">#<?php echo $em['id']; ?></td>
                    <td>
                        <p style="margin:0;font-size:13px;font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($em['to_name'] ?: '—'); ?></p>
                        <p style="margin:0;font-size:12px;color:var(--text-muted);"><?php echo htmlspecialchars($em['to_email']); ?></p>
                    </td>
                    <td>
                        <p style="margin:0;font-size:13px;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:280px;"><?php echo htmlspecialchars($em['subject']); ?></p>
                        <?php if (!empty($em['error_message'])): ?>
                        <p style="margin:2px 0 0;font-size:11px;color:#ef4444;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:280px;"><?php echo htmlspecialchars($em['error_message']); ?></p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $stCfg['bg']; ?>;color:<?php echo $stCfg['text']; ?>;">
                            <?php echo $stCfg['label']; ?>
                        </span>
                    </td>
                    <td style="text-align:center;font-weight:700;color:<?php echo $em['attempts'] >= 3 ? '#dc2626' : '#64748b'; ?>;">
                        <?php echo $em['attempts']; ?>/3
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);"><?php echo $em['scheduled_at'] ? date('d/m H:i', strtotime($em['scheduled_at'])) : '—'; ?></td>
                    <td style="font-size:12px;color:var(--text-muted);"><?php echo $em['sent_at'] ? date('d/m H:i', strtotime($em['sent_at'])) : '—'; ?></td>
                    <td>
                        <?php if ($em['status'] === 'failed'): ?>
                        <a href="?page=settings&action=email_queue&retry_id=<?php echo $em['id']; ?>"
                           class="btn btn-sm btn-warning" title="Thử lại"
                           onclick="return confirm('Thử gửi lại email này?')">
                            <i class="fas fa-redo"></i>
                        </a>
                        <?php else: ?>
                        <span style="color:#e2e8f0;font-size:18px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
        <?php for ($p = 1; $p <= $totalPages; $p++):
            $isAct = ($p === $pageNum);
        ?>
        <a href="?page=settings&action=email_queue&status=<?php echo $statusFilter; ?>&qpage=<?php echo $p; ?>"
           style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;background:<?php echo $isAct?'#6366f1':'white'; ?>;color:<?php echo $isAct?'white':'#64748b'; ?>;border:1.5px solid <?php echo $isAct?'#6366f1':'#e2e8f0'; ?>;">
            <?php echo $p; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</main>
