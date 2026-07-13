<?php
// Admin Audit Log View - Enhanced with Charts & Visual Stats
$auditCtrl = new AuditController($db);
$filters   = [
    'module'  => $_GET['module']  ?? '',
    'action'  => $_GET['action_filter'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'from'    => $_GET['from']    ?? '',
    'to'      => $_GET['to']      ?? date('Y-m-d'),
];
$page    = max(1, intval($_GET['p'] ?? 1));
$perPage = 50;
$logs    = $auditCtrl->getLogs($filters, $page, $perPage);
$total   = $auditCtrl->countLogs($filters);
$modules = $auditCtrl->getModules();
$admins  = $auditCtrl->getAdmins();

$actionColors = [
    'create' => ['bg'=>'#d1fae5','color'=>'#065f46','icon'=>'fa-plus'],
    'update' => ['bg'=>'#fef3c7','color'=>'#92400e','icon'=>'fa-edit'],
    'delete' => ['bg'=>'#fee2e2','color'=>'#991b1b','icon'=>'fa-trash'],
    'login'  => ['bg'=>'#dbeafe','color'=>'#1d4ed8','icon'=>'fa-sign-in-alt'],
    'logout' => ['bg'=>'#f1f5f9','color'=>'#475569','icon'=>'fa-sign-out-alt'],
    'upload' => ['bg'=>'#ede9fe','color'=>'#5b21b6','icon'=>'fa-upload'],
];

// ── Stats tổng quan ──
$totalAll   = (int)$db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$todayCount = (int)$db->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$moduleCount= count($modules);
$adminCount = count($admins);

// ── Data cho biểu đồ — Activity by Module ──
$moduleStats = $db->query("
    SELECT module, COUNT(*) AS cnt, COUNT(DISTINCT user_id) AS admins
    FROM audit_logs
    GROUP BY module ORDER BY cnt DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ── Data cho biểu đồ — Activity 7 ngày gần ──
$dailyStats = $db->query("
    SELECT DATE(created_at) AS day_key, DATE_FORMAT(created_at,'%d/%m') AS day_label,
           COUNT(*) AS cnt,
           COUNT(DISTINCT user_id) AS active_admins
    FROM audit_logs
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY day_key ORDER BY day_key ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Điền đủ 7 ngày
$dayMap = [];
for ($i = 6; $i >= 0; $i--) {
    $key = date('Y-m-d', strtotime("-$i days"));
    $dayMap[$key] = ['day_label' => date('d/m', strtotime("-$i days")), 'cnt' => 0, 'active_admins' => 0];
}
foreach ($dailyStats as $d) {
    if (isset($dayMap[$d['day_key']])) {
        $dayMap[$d['day_key']] = $d;
    }
}
$dailyStats = array_values($dayMap);

// ── Data — Admin activity ranking ──
$adminRanking = $db->query("
    SELECT user_id, username, COUNT(*) AS cnt,
           COUNT(DISTINCT DATE(created_at)) AS active_days
    FROM audit_logs
    WHERE user_id IS NOT NULL
    GROUP BY user_id, username
    ORDER BY cnt DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ── Module colors ──
$moduleColorPalette = ['#6366f1','#f59e0b','#10b981','#ef4444','#06b6d4','#8b5cf6','#ec4899','#14b8a6','#f97316','#64748b'];
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.audit-heatmap {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(32px, 1fr));
    gap: 3px;
    padding: 8px;
}
.audit-heatmap .hm-cell {
    aspect-ratio: 1;
    border-radius: 4px;
    cursor: help;
    transition: transform .15s;
}
.audit-heatmap .hm-cell:hover { transform: scale(1.25); }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-history" style="color:#7c3aed;"></i> Lịch sử hoạt động</h1>
    <div style="display:flex;gap:8px;align-items:center;">
        <span style="background:rgba(99,102,241,0.12);color:#818cf8;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:700;">
            <?php echo number_format($totalAll); ?> bản ghi
        </span>
        <span style="background:rgba(16,185,129,0.12);color:#4ade80;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:700;">
            <i class="fas fa-calendar-day"></i> Hôm nay: <?php echo $todayCount; ?>
        </span>
    </div>
</div>

<!-- ── Stats Cards ── -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
    <?php
    $auditCards = [
        ['icon'=>'fa-database','label'=>'Tổng số bản ghi','val'=>number_format($totalAll),'color'=>'#6366f1','bg'=>'rgba(99,102,241,0.12)'],
        ['icon'=>'fa-calendar-day','label'=>'Hôm nay','val'=>$todayCount,'color'=>'#10b981','bg'=>'rgba(16,185,129,0.12)'],
        ['icon'=>'fa-cubes','label'=>'Module','val'=>$moduleCount,'color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.12)'],
        ['icon'=>'fa-users-cog','label'=>'Admin hoạt động','val'=>$adminCount,'color'=>'#06b6d4','bg'=>'rgba(6,182,212,0.12)'],
    ];
    foreach ($auditCards as $ac):
    ?>
    <div style="background:var(--bg-surface);border-radius:14px;padding:18px 20px;border:1px solid var(--border-subtle);display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:<?php echo $ac['bg']; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas <?php echo $ac['icon']; ?>" style="color:<?php echo $ac['color']; ?>;font-size:18px;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-primary);"><?php echo $ac['val']; ?></div>
            <div style="font-size:12px;color:var(--text-muted);"><?php echo $ac['label']; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Charts Row ── -->
<?php if (!empty($moduleStats) || !empty($dailyStats)): ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
    <?php if (!empty($moduleStats)): ?>
    <!-- Activity by Module Donut -->
    <div style="background:var(--bg-surface);border-radius:14px;border:1px solid var(--border-subtle);padding:18px;">
        <h3 style="margin:0 0 14px;font-size:14px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-chart-pie" style="color:#6366f1;"></i> Hoạt động theo Module
        </h3>
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="flex:0 0 140px;">
                <canvas id="auditModuleChart"></canvas>
            </div>
            <div style="flex:1;min-width:0;">
                <?php foreach ($moduleStats as $i => $ms): 
                    $color = $moduleColorPalette[$i % count($moduleColorPalette)];
                ?>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:5px;font-size:12px;">
                    <span style="width:8px;height:8px;border-radius:2px;background:<?php echo $color; ?>;flex-shrink:0;"></span>
                    <span style="color:var(--text-secondary);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($ms['module']); ?></span>
                    <span style="color:var(--text-primary);font-weight:700;"><?php echo $ms['cnt']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($dailyStats)): ?>
    <!-- Activity 7 Days Bar -->
    <div style="background:var(--bg-surface);border-radius:14px;border:1px solid var(--border-subtle);padding:18px;">
        <h3 style="margin:0 0 14px;font-size:14px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-chart-bar" style="color:#22d3ee;"></i> Hoạt động 7 ngày qua
        </h3>
        <canvas id="auditDailyChart" height="100"></canvas>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Admin Ranking ── -->
<?php if (!empty($adminRanking)): ?>
<div style="background:var(--bg-surface);border-radius:14px;border:1px solid var(--border-subtle);padding:18px;margin-bottom:20px;">
    <h3 style="margin:0 0 14px;font-size:14px;font-weight:700;color:var(--text-primary);">
        <i class="fas fa-trophy" style="color:#f59e0b;"></i> Xếp hạng Admin hoạt động
    </h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
        <?php 
        $maxCnt = max(array_column($adminRanking, 'cnt') ?: [1]);
        foreach ($adminRanking as $i => $ar): 
            $pct = $ar['cnt'] / $maxCnt * 100;
            $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : ''));
        ?>
        <div style="background:var(--bg-elevated);border-radius:10px;padding:12px 14px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <span style="font-size:16px;"><?php echo $medal ?: '👤'; ?></span>
                <div>
                    <span style="font-weight:700;color:var(--text-primary);font-size:13px;">
                        <?php echo htmlspecialchars($ar['username'] ?: 'Hệ thống'); ?>
                    </span>
                    <span style="font-size:11px;color:var(--text-faint);display:block;">
                        <?php echo $ar['active_days']; ?> ngày hoạt động
                    </span>
                </div>
                <span style="margin-left:auto;font-weight:900;color:#6366f1;font-size:16px;"><?php echo $ar['cnt']; ?></span>
            </div>
            <div style="height:5px;background:var(--border-subtle);border-radius:99px;overflow:hidden;">
                <div style="height:100%;width:<?php echo $pct; ?>%;background:linear-gradient(90deg,#6366f1,#818cf8);border-radius:99px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Filters ── -->
<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);margin-bottom:20px;">
<div class="card-body" style="padding:20px;">
<form method="GET" action="">
    <input type="hidden" name="page" value="audit">
    <div style="display:grid;grid-template-columns:repeat(5,1fr) auto;gap:12px;align-items:end;">
        <div>
            <label style="font-size:12px;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">MODULE</label>
            <select name="module" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:8px;">
                <option value="">Tất cả</option>
                <?php foreach ($modules as $m): ?><option value="<?php echo htmlspecialchars($m); ?>" <?php echo ($filters['module']===$m)?'selected':''; ?>><?php echo htmlspecialchars($m); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">HÀNH ĐỘNG</label>
            <select name="action_filter" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:8px;">
                <option value="">Tất cả</option>
                <?php foreach (array_keys($actionColors) as $ac): ?><option value="<?php echo $ac; ?>" <?php echo ($filters['action']===$ac)?'selected':''; ?>><?php echo ucfirst($ac); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">ADMIN</label>
            <select name="user_id" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:8px;">
                <option value="">Tất cả</option>
                <?php foreach ($admins as $adm): ?><option value="<?php echo $adm['user_id']; ?>" <?php echo ($filters['user_id']==$adm['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($adm['username']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">TỪ NGÀY</label>
            <input type="date" name="from" value="<?php echo $filters['from']; ?>" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:8px;">
        </div>
        <div>
            <label style="font-size:12px;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">ĐẾN NGÀY</label>
            <input type="date" name="to" value="<?php echo $filters['to']; ?>" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:8px;">
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="border-radius:10px;padding:9px 18px;font-weight:700;background:linear-gradient(135deg,#7c3aed,#6d28d9);border:none;white-space:nowrap;"><i class="fas fa-filter"></i> Lọc</button>
        </div>
    </div>
</form>
</div></div>

<!-- ── Log Table ── -->
<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
<div class="card-body" style="padding:0;">
<table style="width:100%;border-collapse:collapse;">
    <thead><tr style="background:var(--bg-elevated);">
        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);text-align:left;">THỜI GIAN</th>
        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);">ADMIN</th>
        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);">HÀNH ĐỘNG</th>
        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);">MODULE</th>
        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);">MỤC TIÊU</th>
        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);">IP</th>
        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:var(--text-muted);"></th>
    </tr></thead>
    <tbody>
    <?php if (empty($logs)): ?>
    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-faint);"><i class="fas fa-history" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>Không có bản ghi nào</td></tr>
    <?php else: foreach ($logs as $log):
        $ac = $actionColors[$log['action']] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','icon'=>'fa-circle'];
    ?>
    <tr style="border-bottom:1px solid var(--border-subtle);" class="log-row" data-id="<?php echo $log['id']; ?>">
        <td style="padding:12px 16px;font-size:12px;color:var(--text-muted);white-space:nowrap;"><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
        <td style="padding:12px 16px;font-size:13px;font-weight:700;color:var(--text-primary);"><?php echo htmlspecialchars($log['username'] ?? '—'); ?></td>
        <td style="padding:12px 16px;">
            <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $ac['bg']; ?>;color:<?php echo $ac['color']; ?>;display:inline-flex;align-items:center;gap:5px;">
                <i class="fas <?php echo $ac['icon']; ?>" style="font-size:10px;"></i><?php echo htmlspecialchars($log['action']); ?>
            </span>
        </td>
        <td style="padding:12px 16px;font-size:13px;"><code style="background:var(--bg-elevated);padding:2px 8px;border-radius:6px;"><?php echo htmlspecialchars($log['module'] ?? ''); ?></code></td>
        <td style="padding:12px 16px;font-size:13px;color:var(--text-secondary);"><?php echo htmlspecialchars($log['target_name'] ?? ($log['target_id'] ? '#'.$log['target_id'] : '—')); ?></td>
        <td style="padding:12px 16px;font-size:12px;color:var(--text-faint);"><?php echo htmlspecialchars($log['ip_address'] ?? '—'); ?></td>
        <td style="padding:12px 16px;">
            <?php if ($log['old_data'] || $log['new_data']): ?>
            <button onclick="toggleDetails(<?php echo $log['id']; ?>)" style="background:var(--bg-elevated);border:none;border-radius:8px;padding:4px 10px;font-size:11px;cursor:pointer;color:var(--text-muted);">
                <i class="fas fa-code"></i>
            </button>
            <?php endif; ?>
        </td>
    </tr>
    <?php if ($log['old_data'] || $log['new_data']): ?>
    <tr id="details-<?php echo $log['id']; ?>" style="display:none;background:var(--bg-surface);">
        <td colspan="7" style="padding:14px 20px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <?php if ($log['old_data']): ?>
                <div><label style="font-size:11px;font-weight:700;color:#dc2626;display:block;margin-bottom:6px;">TRƯỚC KHI THAY ĐỔI</label>
                    <pre style="background:#fef2f2;padding:12px;border-radius:10px;font-size:11px;overflow-x:auto;margin:0;color:var(--text-secondary);"><?php echo htmlspecialchars(json_encode(json_decode($log['old_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                </div>
                <?php endif; ?>
                <?php if ($log['new_data']): ?>
                <div><label style="font-size:11px;font-weight:700;color:#16a34a;display:block;margin-bottom:6px;">SAU KHI THAY ĐỔI</label>
                    <pre style="background:var(--success-bg);padding:12px;border-radius:10px;font-size:11px;overflow-x:auto;margin:0;color:var(--text-secondary);"><?php echo htmlspecialchars(json_encode(json_decode($log['new_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                </div>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endif; ?>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div>

<!-- Pagination -->
<?php if ($total > $perPage):
    $totalPages = ceil($total / $perPage);
    $q = array_merge($_GET, ['page'=>'audit']);
?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
    <?php for ($i = max(1,$page-3); $i <= min($totalPages,$page+3); $i++):
        $q['p'] = $i;
    ?>
    <a href="?<?php echo http_build_query($q); ?>" style="padding:7px 14px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;<?php echo $i===$page ? 'background:#7c3aed;color:white;' : 'background:var(--bg-elevated);color:var(--text-muted);'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<script>
function toggleDetails(id) {
    const el = document.getElementById('details-' + id);
    if (el) el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
}

// ── Charts ──
document.addEventListener('DOMContentLoaded', function() {
    // Module Donut
    const mc = document.getElementById('auditModuleChart');
    if (mc) {
        new Chart(mc.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($moduleStats, 'module')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($moduleStats, 'cnt')); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($moduleColorPalette, 0, count($moduleStats))); ?>,
                    borderWidth: 2, borderColor: '#f4f4f5',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true, cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // Daily Bar
    const dc = document.getElementById('auditDailyChart');
    if (dc) {
        new Chart(dc.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($dailyStats, 'day_label')); ?>,
                datasets: [{
                    label: 'Hoạt động',
                    data: <?php echo json_encode(array_column($dailyStats, 'cnt')); ?>,
                    backgroundColor: 'rgba(6,182,212,0.7)',
                    borderColor: '#06b6d4', borderWidth: 1.5,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: '#71717a', font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, border: { display: false }, ticks: { precision: 0, color: '#71717a' } }
                }
            }
        });
    }
});
</script>
