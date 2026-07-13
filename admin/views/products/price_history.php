<?php
/**
 * Price History — Lịch sử thay đổi giá sản phẩm
 * URL: ?page=products&action=price_history&id=X
 */
use App\Helpers\PriceHistoryHelper;

$phProductId = intval($_GET['id'] ?? 0);
if (!$phProductId) {
    header('Location: ?page=products');
    exit;
}

// Get product info
$phProduct = $admin->getProductById($phProductId);
if (!$phProduct) {
    echo '<div style="padding:40px;text-align:center;color:#ef4444;">Không tìm thấy sản phẩm.</div>';
    exit;
}

$priceHistory = PriceHistoryHelper::getHistory($db, $phProductId);
$priceRange   = PriceHistoryHelper::getPriceRange($db, $phProductId);

// Chart data
$chartLabels = array_reverse(array_map(fn($h) => date('d/m', strtotime($h['created_at'])), $priceHistory));
$chartPrices = array_reverse(array_map(fn($h) => (int)$h['new_price'], $priceHistory));
?>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-chart-line" style="color:#6366f1;margin-right:10px;"></i>Lịch sử giá</h1>
            <p><?php echo htmlspecialchars($phProduct['name']); ?></p>
        </div>
        <a href="?page=products&edit_id=<?php echo $phProductId; ?>#edit-<?php echo $phProductId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại sản phẩm
        </a>
    </div>

    <!-- Product info card -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
        <div class="stat-card" style="border-left:4px solid #6366f1;">
            <div class="stat-content">
                <div class="stat-label">Giá hiện tại</div>
                <div class="stat-value" style="font-size:22px;color:#6366f1;"><?php echo number_format($phProduct['price'], 0, ',', '.'); ?>₫</div>
            </div>
        </div>
        <div class="stat-card" style="border-left:4px solid #10b981;">
            <div class="stat-content">
                <div class="stat-label">Giá thấp nhất</div>
                <div class="stat-value" style="font-size:18px;color:#10b981;">
                    <?php echo $priceRange['min_price'] ? number_format($priceRange['min_price'], 0, ',', '.') . '₫' : '—'; ?>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left:4px solid #ef4444;">
            <div class="stat-content">
                <div class="stat-label">Giá cao nhất</div>
                <div class="stat-value" style="font-size:18px;color:#ef4444;">
                    <?php echo $priceRange['max_price'] ? number_format($priceRange['max_price'], 0, ',', '.') . '₫' : '—'; ?>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-content">
                <div class="stat-label">Số lần thay đổi</div>
                <div class="stat-value" style="color:#f59e0b;"><?php echo count($priceHistory); ?></div>
            </div>
        </div>
    </div>

    <!-- Price Trend Chart -->
    <?php if (count($priceHistory) >= 2): ?>
    <div style="background:var(--bg-surface);border-radius:14px;border:1px solid var(--border-subtle);padding:20px;margin-bottom:20px;">
        <h3 style="margin:0 0 16px;font-size:14px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-chart-line" style="color:#6366f1;"></i> Biểu đồ xu hướng giá
        </h3>
        <canvas id="priceChart" height="120"></canvas>
    </div>
    <?php endif; ?>

    <!-- History Table -->
    <div style="background:var(--bg-surface);border-radius:14px;border:1px solid var(--border-subtle);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--text-primary);">
                <i class="fas fa-history"></i> Lịch sử thay đổi (<?php echo count($priceHistory); ?> lần)
            </h3>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table" style="margin:0;">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th style="text-align:right;">Giá cũ</th>
                        <th style="text-align:right;">Giá mới</th>
                        <th style="text-align:center;">Thay đổi</th>
                        <th style="text-align:right;">Giá vốn cũ</th>
                        <th style="text-align:right;">Giá vốn mới</th>
                        <th style="text-align:center;">Giảm giá cũ</th>
                        <th style="text-align:center;">Giảm giá mới</th>
                        <th>Người thay đổi</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($priceHistory)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center;padding:40px;color:var(--text-faint);">
                            <i class="fas fa-box-open" style="font-size:36px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                            Chưa có lịch sử thay đổi giá. Giá sẽ được ghi lại tự động khi bạn cập nhật sản phẩm.
                        </td>
                    </tr>
                    <?php else: foreach ($priceHistory as $h): 
                        $diff = $h['new_price'] - $h['old_price'];
                        $diffClass = $diff > 0 ? '#ef4444' : ($diff < 0 ? '#10b981' : '#64748b');
                        $diffIcon  = $diff > 0 ? '▲' : ($diff < 0 ? '▼' : '—');
                    ?>
                    <tr>
                        <td style="font-size:12px;color:var(--text-muted);">
                            <?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>
                        </td>
                        <td style="text-align:right;color:#64748b;">
                            <?php echo number_format($h['old_price'], 0, ',', '.'); ?>₫
                        </td>
                        <td style="text-align:right;font-weight:700;color:#6366f1;">
                            <?php echo number_format($h['new_price'], 0, ',', '.'); ?>₫
                        </td>
                        <td style="text-align:center;">
                            <span style="color:<?php echo $diffClass; ?>;font-weight:700;font-size:15px;">
                                <?php echo $diffIcon; ?> <?php echo $diff != 0 ? number_format(abs($diff), 0, ',', '.') . '₫' : '—'; ?>
                            </span>
                        </td>
                        <td style="text-align:right;color:var(--text-muted);font-size:13px;">
                            <?php echo $h['old_cost'] ? number_format($h['old_cost'], 0, ',', '.') . '₫' : '<span style="color:#d1d5db;">—</span>'; ?>
                        </td>
                        <td style="text-align:right;color:var(--text-muted);font-size:13px;">
                            <?php echo $h['new_cost'] ? number_format($h['new_cost'], 0, ',', '.') . '₫' : '<span style="color:#d1d5db;">—</span>'; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($h['old_discount'] !== null): ?>
                            <span style="background:rgba(245,158,11,0.12);color:#f59e0b;padding:2px 8px;border-radius:20px;font-size:11px;"><?php echo $h['old_discount']; ?>%</span>
                            <?php else: ?>
                            <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($h['new_discount'] !== null): ?>
                            <span style="background:rgba(245,158,11,0.12);color:#f59e0b;padding:2px 8px;border-radius:20px;font-size:11px;"><?php echo $h['new_discount']; ?>%</span>
                            <?php else: ?>
                            <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            <?php echo htmlspecialchars($h['changed_by_name'] ?? 'Hệ thống'); ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-secondary);max-width:150px;">
                            <?php echo htmlspecialchars($h['change_note'] ?? ''); ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php if (count($priceHistory) >= 2): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('priceChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Giá (₫)',
            data: <?php echo json_encode($chartPrices); ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.10)',
            borderWidth: 2.5,
            pointBackgroundColor: '#6366f1',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#fff',
                titleColor: '#09090b',
                bodyColor: '#52525b',
                borderColor: 'rgba(0,0,0,0.10)',
                borderWidth: 1,
                padding: 10,
                cornerRadius: 10,
                callbacks: {
                    label: ctx => Number(ctx.raw).toLocaleString('vi-VN') + '₫'
                }
            }
        },
        scales: {
            x: { grid: { color: 'rgba(0,0,0,0.05)' }, border: { display: false }, ticks: { color: '#71717a', font: { size: 10 } } },
            y: { 
                beginAtZero: false,
                grid: { color: 'rgba(0,0,0,0.05)' },
                border: { display: false },
                ticks: { color: '#71717a', callback: v => (v/1000).toFixed(0) + 'k' }
            }
        }
    }
});
</script>
<?php endif; ?>
