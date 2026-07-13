<?php
// ── Lấy filter từ GET ──
$filters = [
    'q'         => trim($_GET['q']         ?? ''),
    'status'    => trim($_GET['status']    ?? 'all'),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to']   ?? ''),
];

$hasFilter = $filters['q'] !== '' || $filters['status'] !== 'all'
           || $filters['date_from'] !== '' || $filters['date_to'] !== '';

// Dùng filtered query nếu có filter, ngược lại lấy tất cả
$orders = $hasFilter
    ? $admin->getOrdersFiltered($filters)
    : $admin->getOrders();

// Đếm theo trạng thái cho tabs
$statusCounts = $admin->getOrderCountByStatus();

$statusMap = [
    'all'        => 'Tất cả',
    'pending'    => 'Chờ xử lý',
    'processing' => 'Đang xử lý',
    'shipped'    => 'Đang giao',
    'completed'  => 'Hoàn thành',
    'cancelled'  => 'Đã hủy',
];
$statusColor = [
    'pending'    => ['bg' => 'rgba(245,158,11,0.12)', 'text' => '#fbbf24', 'border' => 'rgba(245,158,11,0.3)'],
    'processing' => ['bg' => 'rgba(99,102,241,0.12)', 'text' => '#818cf8', 'border' => 'rgba(99,102,241,0.3)'],
    'shipped'    => ['bg' => 'rgba(6,182,212,0.12)',  'text' => '#22d3ee', 'border' => 'rgba(6,182,212,0.3)'],
    'completed'  => ['bg' => 'rgba(34,197,94,0.12)',  'text' => '#4ade80', 'border' => 'rgba(34,197,94,0.3)'],
    'cancelled'  => ['bg' => 'rgba(239,68,68,0.12)',  'text' => '#f87171', 'border' => 'rgba(239,68,68,0.3)'],
];

// Tổng doanh thu từ kết quả đang hiển thị
$totalRevenue = array_sum(array_column(
    array_filter($orders, fn($o) => strtolower($o['status']) === 'completed'),
    'total_amount'
));
?>
<style>
/* ── Order Filter Bar ── */
.order-filter-bar {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 18px 22px;
    margin-bottom: 20px;
    border: 1px solid var(--border-subtle);
}
.filter-top-row {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.filter-search-wrap {
    position: relative;
    flex: 1;
    min-width: 220px;
}
.filter-search-wrap i {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 13px;
    pointer-events: none;
}
.filter-search-input {
    width: 100%;
    padding: 10px 14px 10px 38px;
    border: 1px solid var(--border-muted);
    border-radius: var(--radius-sm);
    font-size: 13.5px;
    font-family: inherit;
    background: var(--bg-elevated);
    color: var(--text-primary);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.filter-search-input::placeholder { color: var(--text-faint); }
.filter-search-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}
.filter-date-input {
    padding: 10px 12px;
    border: 1px solid var(--border-muted);
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-family: inherit;
    background: var(--bg-elevated);
    color: var(--text-primary);
    cursor: pointer;
    transition: border-color .2s;
    outline: none;
}
.filter-date-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}
.filter-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Status tab pills */
.status-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}
.status-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 13px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid var(--border-subtle);
    transition: all .18s;
    white-space: nowrap;
    background: var(--bg-elevated);
    color: var(--text-secondary);
}
.status-tab:hover { border-color: var(--border-strong); color: var(--text-primary); }
.status-tab.active-all  { background: var(--accent); color: white; border-color: var(--accent); }
.status-tab.active-pending    { background: rgba(245,158,11,0.12);  color: #fbbf24; border-color: rgba(245,158,11,0.3); }
.status-tab.active-processing { background: rgba(99,102,241,0.12);  color: #818cf8; border-color: rgba(99,102,241,0.3); }
.status-tab.active-shipped    { background: rgba(6,182,212,0.12);   color: #22d3ee; border-color: rgba(6,182,212,0.3); }
.status-tab.active-completed  { background: rgba(34,197,94,0.12);   color: #4ade80; border-color: rgba(34,197,94,0.3); }
.status-tab.active-cancelled  { background: rgba(239,68,68,0.12);   color: #f87171; border-color: rgba(239,68,68,0.3); }
.status-tab .tab-count {
    background: rgba(0,0,0,0.08);
    border-radius: 99px;
    padding: 1px 6px;
    font-size: 10px;
}

/* Result info bar */
.result-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 10px;
}
.result-count { font-size: 13px; color: var(--text-muted); }
.result-count strong { color: var(--text-primary); }
.result-revenue {
    font-size: 13px;
    font-weight: 600;
    color: var(--success);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Clear filter chip */
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--accent-dim);
    color: var(--accent-light);
    border: 1px solid var(--accent-border);
    border-radius: 99px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s;
}
.filter-chip:hover { background: rgba(99,102,241,0.25); }

/* Empty state */
.order-empty {
    padding: 60px 20px;
    text-align: center;
    color: var(--text-faint);
}
.order-empty i { font-size: 48px; margin-bottom: 16px; display: block; opacity: .25; }
.order-empty p { font-size: 14px; margin-bottom: 4px; color: var(--text-muted); }
.order-empty small { font-size: 12px; opacity: .6; }

/* Amount highlight in table */
.amt-high { color: var(--success); font-weight: 700; }
</style>

<main class="admin-main">

    <!-- Page header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Quản lý đơn hàng</h1>
            <p>Tìm kiếm, lọc và xử lý tất cả đơn hàng</p>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <!-- ── Filter Bar ── -->
    <form method="GET" action="" id="filterForm">
        <input type="hidden" name="page" value="orders">
        <div class="order-filter-bar">

            <!-- Row 1: Search + Date range + Nút lọc -->
            <div class="filter-top-row">
                <!-- Ô tìm kiếm -->
                <div class="filter-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" id="searchInput"
                           class="filter-search-input"
                           placeholder="Tìm theo tên, email, mã đơn hàng..."
                           value="<?php echo htmlspecialchars($filters['q']); ?>"
                           autocomplete="off">
                </div>

                <!-- Từ ngày -->
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="filter-label"><i class="fas fa-calendar-alt"></i> Từ</span>
                    <input type="date" name="date_from" class="filter-date-input"
                           value="<?php echo htmlspecialchars($filters['date_from']); ?>"
                           title="Từ ngày">
                </div>

                <!-- Đến ngày -->
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="filter-label">Đến</span>
                    <input type="date" name="date_to" class="filter-date-input"
                           value="<?php echo htmlspecialchars($filters['date_to']); ?>"
                           title="Đến ngày">
                </div>

                <!-- Nút lọc -->
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="fas fa-filter"></i> Lọc
                </button>

                <?php if ($hasFilter): ?>
                    <a href="?page=orders" class="filter-chip">
                        <i class="fas fa-times"></i> Xoá bộ lọc
                    </a>
                <?php endif; ?>
            </div>

            <!-- Row 2: Status tabs -->
            <div class="status-tabs">
                <?php
                $tabStatuses = ['all', 'pending', 'processing', 'shipped', 'completed', 'cancelled'];
                foreach ($tabStatuses as $st):
                    $isActive = ($filters['status'] === $st);
                    $cnt      = $statusCounts[$st] ?? 0;
                    $label    = $statusMap[$st];
                    // Build URL giữ nguyên filter khác
                    $qs = http_build_query(array_merge($filters, ['status' => $st, 'page' => 'orders']));
                    $tabClass = $isActive ? 'status-tab active-' . $st : 'status-tab';
                ?>
                    <a href="?<?php echo $qs; ?>" class="<?php echo $tabClass; ?>">
                        <?php echo $label; ?>
                        <?php if ($st !== 'all' || $cnt > 0): ?>
                            <span class="tab-count"><?php echo $cnt; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </form>

    <!-- ── Bulk Actions Toolbar ── -->
    <div id="bulkActions" style="display:none;background:linear-gradient(135deg,#ede9fe,#e0e7ff);border:1.5px solid #a5b4fc;border-radius:12px;padding:12px 18px;margin-bottom:16px;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
            <i class="fas fa-check-square" style="color:#6366f1;"></i>
            Đã chọn <strong id="selectedCount">0</strong> đơn hàng
        </div>
        <div style="display:flex;gap:8px;">
            <button onclick="bulkPrint()" class="btn btn-sm" style="background:linear-gradient(135deg,#10b981,#059669);color:white;border:none;">
                <i class="fas fa-print"></i> In hóa đơn (nhiều)
            </button>
            <button onclick="clearSelection()" class="btn btn-sm btn-secondary">
                <i class="fas fa-times"></i> Bỏ chọn
            </button>
        </div>
    </div>

    <!-- ── Result info bar ── -->
    <div class="result-bar">
        <div class="result-count">
            Hiển thị <strong><?php echo count($orders); ?></strong> đơn hàng
            <?php if ($hasFilter): ?>
                &nbsp;(đã lọc)
            <?php else: ?>
                / <strong><?php echo $statusCounts['all'] ?? 0; ?></strong> tổng cộng
            <?php endif; ?>
        </div>
        <?php if ($totalRevenue > 0): ?>
        <div class="result-revenue">
            <i class="fas fa-check-circle"></i>
            Doanh thu hoàn thành: <?php echo number_format($totalRevenue, 0, ',', '.'); ?> ₫
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Table ── -->
    <div class="table-responsive">
        <table class="admin-table" id="ordersTable">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="selectAllOrders" onchange="toggleAllOrders(this)" style="accent-color:#6366f1;cursor:pointer;">
                    </th>
                    <th>Mã ĐH</th>
                    <th>Khách hàng</th>
                    <th>Email</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="order-empty">
                                <i class="fas fa-box-open"></i>
                                <p>Không tìm thấy đơn hàng nào</p>
                                <small>
                                    <?php if ($hasFilter): ?>
                                        Thử thay đổi từ khoá hoặc bộ lọc
                                    <?php else: ?>
                                        Chưa có đơn hàng nào trong hệ thống
                                    <?php endif; ?>
                                </small>
                            </div>
                        </td>
                    </tr>                    <?php else: ?>
                    <?php foreach ($orders as $order):
                        $st  = strtolower($order['status']);
                        $col = $statusColor[$st] ?? ['bg'=>'#f1f5f9','text'=>'#64748b','border'=>'#e2e8f0'];
                    ?>
                    <tr>
                        <td style="text-align:center;">
                            <input type="checkbox" class="order-checkbox" value="<?php echo $order['id']; ?>" style="accent-color:#6366f1;cursor:pointer;">
                        </td>
                        <td>
                            <span style="font-weight:800;color:#6366f1;font-size:15px;">#<?php echo $order['id']; ?></span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:50%;
                                    background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                    display:flex;align-items:center;justify-content:center;
                                    color:white;font-size:13px;font-weight:700;flex-shrink:0;">
                                    <?php echo strtoupper(substr($order['full_name'] ?? $order['customer_name'] ?? 'N', 0, 1)); ?>
                                </div>
                                <span style="font-weight:600;color:var(--text-primary);">
                                    <?php echo htmlspecialchars($order['full_name'] ?? $order['customer_name'] ?? 'Khách vãng lai'); ?>
                                </span>
                            </div>
                        </td>
                        <td style="color:var(--text-muted);font-size:13px;">
                            <?php echo htmlspecialchars($order['email'] ?? $order['customer_email'] ?? '—'); ?>
                        </td>
                        <td>
                            <span class="amt-high"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</span>
                        </td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:5px;
                                padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;
                                background:<?php echo $col['bg']; ?>;
                                color:<?php echo $col['text']; ?>;
                                border:1px solid <?php echo $col['border']; ?>;">
                                <?php
                                $icons = [
                                    'pending'    => 'clock',
                                    'processing' => 'cog fa-spin',
                                    'shipped'    => 'truck',
                                    'completed'  => 'check-circle',
                                    'cancelled'  => 'times-circle',
                                ];
                                $icon = $icons[$st] ?? 'circle';
                                ?>
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                                <?php echo $statusMap[$st] ?? $order['status']; ?>
                            </span>
                        </td>
                        <td>
                            <div style="color:var(--text-muted);font-size:13px;">
                                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                            </div>
                            <div style="color:var(--text-faint);font-size:11px;">
                                <?php echo date('H:i', strtotime($order['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="?page=orders&action=detail&id=<?php echo $order['id']; ?>"
                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                                <a href="?page=orders&action=edit&id=<?php echo $order['id']; ?>"
                                   class="btn btn-sm btn-warning" title="Cập nhật trạng thái">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="?page=orders&action=invoice&id=<?php echo $order['id']; ?>" target="_blank"
                                   class="btn btn-sm"
                                   style="background:rgba(34,197,94,0.12);color:#4ade80;border:1px solid #6ee7b7;"
                                   title="In hóa đơn">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
// ── Bulk Selection ──
function toggleAllOrders(master) {
    document.querySelectorAll('.order-checkbox').forEach(function(cb) {
        cb.checked = master.checked;
    });
    updateBulkBar();
}

function updateBulkBar() {
    var checked = document.querySelectorAll('.order-checkbox:checked');
    var bar = document.getElementById('bulkActions');
    var count = document.getElementById('selectedCount');
    if (checked.length > 0) {
        bar.style.display = 'flex';
        count.textContent = checked.length;
    } else {
        bar.style.display = 'none';
    }
}

function clearSelection() {
    document.querySelectorAll('.order-checkbox').forEach(function(cb) { cb.checked = false; });
    document.getElementById('selectAllOrders').checked = false;
    updateBulkBar();
}

function bulkPrint() {
    var checked = document.querySelectorAll('.order-checkbox:checked');
    if (checked.length === 0) { alert('Vui lòng chọn ít nhất 1 đơn hàng.'); return; }
    var ids = Array.from(checked).map(function(cb) { return cb.value; }).join(',');
    window.open('?page=orders&action=bulk_invoice&ids=' + ids, '_blank');
}

// Listen for checkbox changes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.order-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateBulkBar);
    });
});

// Submit form khi nhấn Enter trong ô search
document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('filterForm').submit();
    }
});

// Auto-submit khi thay đổi ngày (UX mượt hơn)
document.querySelectorAll('.filter-date-input').forEach(function(inp) {
    inp.addEventListener('change', function() {
        // Nếu cả 2 đã có giá trị thì submit luôn
        var from = document.querySelector('[name="date_from"]').value;
        var to   = document.querySelector('[name="date_to"]').value;
        if (from && to) {
            document.getElementById('filterForm').submit();
        }
    });
});

// Highlight từ khóa tìm kiếm trong bảng
(function() {
    var q = <?php echo json_encode($filters['q']); ?>;
    if (!q) return;
    var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    document.querySelectorAll('#ordersTable tbody td').forEach(function(td) {
        if (td.querySelector('a,button,span.amt-high')) return; // bỏ qua cell nút/số tiền
        td.innerHTML = td.innerHTML.replace(re, '<mark style="background:#fef08a;color:#713f12;border-radius:3px;padding:0 2px;">$1</mark>');
    });
})();
</script>
