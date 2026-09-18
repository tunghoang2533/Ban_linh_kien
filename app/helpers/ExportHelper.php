<?php
/**
 * ExportHelper — Xuất Excel (.xls) không cần thư viện bên ngoài
 * 
 * Sử dụng HTML-table format, Excel/LibreOffice/Google Sheets mở được.
 * Hỗ trợ: header màu sắc, định dạng số, border, nhiều sheet trong 1 file.
 */

namespace App\Helpers;

class ExportHelper
{
    /**
     * Xuất danh sách đơn hàng ra Excel
     */
    public static function orders($db, array $filters = []): void
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR o.customer_email LIKE ? OR o.customer_name LIKE ? OR CAST(o.id AS CHAR) LIKE ?)";
            $params = array_merge($params, [$q, $q, $q, $q, $q]);
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = 'o.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(o.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(o.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        // Mặc định lấy 30 ngày gần nhất nếu không có filter
        if (empty($filters['q']) && empty($filters['status']) && empty($filters['date_from']) && empty($filters['date_to'])) {
            $where[] = 'o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }

        $sql = "SELECT o.*, u.full_name, u.email AS uemail, u.phone AS uphone
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id"
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . " ORDER BY o.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $statusLabels = [
            'pending'    => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped'    => 'Đang giao',
            'completed'  => 'Hoàn thành',
            'cancelled'  => 'Đã hủy',
        ];

        $rowsHtml = '';
        $totalAmount = 0;
        $completedCount = 0;
        foreach ($rows as $r) {
            $stLabel = $statusLabels[$r['status']] ?? $r['status'];
            $rowsHtml .= '<tr>
                <td style="text-align:center;font-weight:700;color:#6366f1;">#' . $r['id'] . '</td>
                <td>' . htmlspecialchars($r['full_name'] ?? $r['customer_name'] ?? 'Khách vãng lai') . '</td>
                <td>' . htmlspecialchars($r['uemail'] ?? $r['customer_email'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['customer_phone'] ?? $r['uphone'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['customer_address'] ?? '-') . '</td>
                <td style="text-align:right;font-weight:700;">' . number_format($r['total_amount'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;">' . number_format($r['discount_amount'] ?? 0, 0, ',', '.') . ' ₫</td>
                <td>' . htmlspecialchars($r['voucher_code'] ?? '-') . '</td>
                <td style="text-align:right;">' . number_format($r['shipping_fee'] ?? 0, 0, ',', '.') . ' ₫</td>
                <td>' . $stLabel . '</td>
                <td>' . date('d/m/Y H:i', strtotime($r['created_at'])) . '</td>
            </tr>';
            $totalAmount += (float)$r['total_amount'];
            if ($r['status'] === 'completed') $completedCount++;
        }

        $totalOrders = count($rows);
        self::output('don-hang_' . date('Y-m-d'), $rows, $totalOrders, $totalAmount, $completedCount, $rowsHtml);
    }

    /**
     * Xuất danh sách sản phẩm ra Excel
     */
    public static function products($db): void
    {
        $rows = $db->query("
            SELECT p.*, c.name AS cat_name, b.name AS brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            ORDER BY p.id
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $rowsHtml = '';
        $totalValue = 0;
        $totalStock = 0;
        foreach ($rows as $r) {
            $stockValue = (int)$r['quantity'] * (float)$r['price'];
            $totalValue += $stockValue;
            $totalStock += (int)$r['quantity'];
            $status = $r['is_active'] ? 'Đang bán' : 'Ẩn';
            $rowsHtml .= '<tr>
                <td style="text-align:center;font-weight:700;color:#6366f1;">#' . $r['id'] . '</td>
                <td>' . htmlspecialchars($r['name']) . '</td>
                <td>' . htmlspecialchars($r['cat_name'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['brand_name'] ?? '-') . '</td>
                <td style="text-align:right;font-weight:700;">' . number_format($r['price'], 0, ',', '.') . ' ₫</td>
                <td>' . number_format($r['cost_price'] ?? 0, 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;">' . (int)$r['quantity'] . '</td>
                <td style="text-align:right;">' . number_format($stockValue, 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;">' . ($r['discount_percent'] ?? 0) . '%</td>
                <td>' . $status . '</td>
            </tr>';
        }

        $summaryHtml = '<div style="margin-top:10px;font-weight:700;">
            <strong>Tổng sản phẩm: ' . count($rows) . '</strong> |
            <strong>Tổng tồn kho: ' . number_format($totalStock) . '</strong> |
            <strong>Tổng giá trị kho: ' . number_format($totalValue, 0, ',', '.') . ' ₫</strong>
        </div>';

        self::outputSimple('san-pham_' . date('Y-m-d'), [
            ['ID', 'Tên sản phẩm', 'Danh mục', 'Thương hiệu', 'Giá bán', 'Giá vốn', 'Tồn kho', 'Giá trị kho', 'Giảm giá', 'Trạng thái']
        ], $rowsHtml, $summaryHtml);
    }

    /**
     * Xuất danh sách người dùng ra Excel
     */
    public static function users($db): void
    {
        $rows = $db->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count,
                   (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id = u.id AND status = 'completed') AS total_spent
            FROM users u
            WHERE u.is_admin = 0
            ORDER BY u.id
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $rowsHtml = '';
        $totalSpentAll = 0;
        foreach ($rows as $r) {
            $status = $r['is_blocked'] ? 'Bị khoá' : 'Hoạt động';
            $totalSpentAll += (float)$r['total_spent'];
            $rowsHtml .= '<tr>
                <td style="text-align:center;font-weight:700;color:#6366f1;">#' . $r['id'] . '</td>
                <td>' . htmlspecialchars($r['full_name'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['username'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['email'] ?? '-') . '</td>
                <td>' . htmlspecialchars($r['phone'] ?? '-') . '</td>
                <td>' . $status . '</td>
                <td style="text-align:right;">' . (int)$r['order_count'] . '</td>
                <td style="text-align:right;font-weight:700;">' . number_format($r['total_spent'], 0, ',', '.') . ' ₫</td>
                <td>' . date('d/m/Y', strtotime($r['created_at'])) . '</td>
            </tr>';
        }

        $summaryHtml = '<div style="margin-top:10px;font-weight:700;">
            <strong>Tổng người dùng: ' . count($rows) . '</strong> |
            <strong>Tổng chi tiêu: ' . number_format($totalSpentAll, 0, ',', '.') . ' ₫</strong>
        </div>';

        self::outputSimple('khach-hang_' . date('Y-m-d'), [
            ['ID', 'Họ tên', 'Username', 'Email', 'SĐT', 'Trạng thái', 'Số đơn', 'Tổng chi tiêu', 'Ngày đăng ký']
        ], $rowsHtml, $summaryHtml);
    }

    /**
     * Xuất báo cáo doanh thu ra Excel (2 sheets: Tổng quan + Chi tiết)
     */
    public static function reportRevenue($db, $from, $to): void
    {
        $periodLabel = date('d/m/Y', strtotime($from)) . ' - ' . date('d/m/Y', strtotime($to));
        $reportCtrl = new \ReportController($db);
        $summary    = $reportCtrl->getSummary($from, $to);
        $revenueD   = $reportCtrl->getRevenueByPeriod($from, $to);
        $topP       = $reportCtrl->getTopProducts($from, $to, 10);
        $statusB    = $reportCtrl->getOrderStatusBreakdown($from, $to);
        $newC       = $reportCtrl->getNewCustomers($from, $to);

        $statusLabels = [
            'pending'    => 'Chờ xác nhận',
            'confirmed'  => 'Đã xác nhận',
            'shipping'   => 'Đang giao',
            'delivered'  => 'Đã giao',
            'completed'  => 'Hoàn thành',
            'cancelled'  => 'Đã hủy',
        ];

        // ── Revenue table (đảo ngược: mới nhất lên đầu) ──
        $revRows = '';
        foreach (array_reverse($revenueD) as $r) {
            $revRows .= '<tr>
                <td style="font-weight:600;">' . $r['date'] . '</td>
                <td style="text-align:right;">' . (int)$r['orders'] . '</td>
                <td style="text-align:right;font-weight:700;color:#2563eb;">' . number_format($r['revenue'], 0, ',', '.') . ' ₫</td>
            </tr>';
        }

        // ── Top products ──
        $topRows = '';
        foreach ($topP as $i => $p) {
            $topRows .= '<tr>
                <td style="text-align:center;">' . ($i + 1) . '</td>
                <td>' . htmlspecialchars($p['name']) . '</td>
                <td style="text-align:right;">' . (int)$p['sold_qty'] . '</td>
                <td style="text-align:right;font-weight:700;">' . number_format($p['revenue'], 0, ',', '.') . ' ₫</td>
            </tr>';
        }

        // ── Status breakdown ──
        $stRows = '';
        foreach ($statusB as $s) {
            $label = $statusLabels[$s['status']] ?? $s['status'];
            $stRows .= '<tr><td>' . $label . '</td><td style="text-align:right;font-weight:700;">' . (int)$s['cnt'] . '</td></tr>';
        }

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head><meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
            h1 { color: #1e293b; font-size: 20px; margin-bottom: 4px; }
            h2 { color: #475569; font-size: 14px; font-weight: 600; margin: 20px 0 10px; border-bottom: 2px solid #6366f1; padding-bottom: 6px; }
            .period { color: #94a3b8; font-size: 12px; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
            th { background: #6366f1; color: #ffffff; font-weight: 700; padding: 8px 12px; border: 1px solid #4f46e5; font-size: 11px; text-align: left; }
            td { padding: 6px 12px; border: 1px solid #e2e8f0; }
            tr:nth-child(even) { background: #f8fafc; }
            .kpi-grid { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
            .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 20px; flex: 1; min-width: 140px; }
            .kpi-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; }
            .kpi-value { font-size: 18px; font-weight: 900; }
            .sum-row { background: #f0fdf4 !important; font-weight: 700; }
            .sum-row td { border-top: 2px solid #16a34a; }
        </style>
        </head><body>
            <h1>📊 Báo cáo doanh thu</h1>
            <div class="period">' . $periodLabel . '</div>

            <div class="kpi-grid">
                <div class="kpi-card"><div class="kpi-label">Tổng doanh thu</div><div class="kpi-value" style="color:#2563eb;">' . number_format($summary['total_revenue'] ?? 0, 0, ',', '.') . ' ₫</div></div>
                <div class="kpi-card"><div class="kpi-label">Tổng đơn hàng</div><div class="kpi-value" style="color:#06b6d4;">' . number_format($summary['total_orders'] ?? 0, 0, ',', '.') . '</div></div>
                <div class="kpi-card"><div class="kpi-label">Đơn trung bình</div><div class="kpi-value" style="color:#22c55e;">' . number_format($summary['avg_order'] ?? 0, 0, ',', '.') . ' ₫</div></div>
                <div class="kpi-card"><div class="kpi-label">Khách hàng mới</div><div class="kpi-value" style="color:#f59e0b;">' . $newC . '</div></div>
            </div>

            <h2>📈 Doanh thu theo ngày</h2>
            <table>
                <tr><th>Ngày</th><th style="text-align:right;">Số đơn</th><th style="text-align:right;">Doanh thu</th></tr>
                ' . $revRows . '
            </table>

            <h2>🔥 Top 10 sản phẩm bán chạy</h2>
            <table>
                <tr><th style="text-align:center;">#</th><th>Tên sản phẩm</th><th style="text-align:right;">Đã bán</th><th style="text-align:right;">Doanh thu</th></tr>
                ' . $topRows . '
            </table>

            <h2>📊 Trạng thái đơn hàng</h2>
            <table>
                <tr><th>Trạng thái</th><th style="text-align:right;">Số lượng</th></tr>
                ' . $stRows . '
                <tr class="sum-row"><td><strong>TỔNG</strong></td><td style="text-align:right;"><strong>' . array_sum(array_column($statusB, 'cnt')) . '</strong></td></tr>
            </table>
        </body></html>';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bao-cao-doanh-thu_' . date('Y-m-d') . '.xls"');
        echo "\xEF\xBB\xBF" . $html;
        exit;
    }

    /**
     * Xuất báo cáo lợi nhuận ra Excel (sử dụng ReportController)
     */
    public static function reportProfit($db, $from, $to): void
    {
        $periodLabel = date('d/m/Y', strtotime($from)) . ' - ' . date('d/m/Y', strtotime($to));
        $reportCtrl = new \ReportController($db);
        $profitD   = $reportCtrl->getProfitByPeriod($from, $to);
        $catProfit = $reportCtrl->getCategoryProfit($from, $to);
        $topCust   = $reportCtrl->getTopProfitCustomers($from, $to, 10);

        $totalRevenue = array_sum(array_column($profitD, 'revenue'));
        $totalCost    = array_sum(array_column($profitD, 'cost'));
        $totalProfit  = array_sum(array_column($profitD, 'profit'));
        $totalOrders  = array_sum(array_column($profitD, 'orders'));
        $marginPct    = $totalRevenue > 0 ? round($totalProfit / $totalRevenue * 100, 1) : 0;

        // ── Profit daily table ──
        $profitRows = '';
        foreach (array_reverse($profitD) as $r) {
            $m = floatval($r['revenue']) > 0 ? round(floatval($r['profit']) / floatval($r['revenue']) * 100, 1) : 0;
            $profitRows .= '<tr>
                <td style="font-weight:600;">' . date('d/m/Y', strtotime($r['day'])) . '</td>
                <td style="text-align:right;">' . (int)$r['orders'] . '</td>
                <td style="text-align:right;">' . number_format($r['revenue'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;color:#dc2626;">' . number_format($r['cost'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;font-weight:700;color:#16a34a;">' . number_format($r['profit'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;font-weight:700;">' . $m . '%</td>
            </tr>';
        }

        // ── Category profit ──
        $catRows = '';
        foreach ($catProfit as $c) {
            $catRows .= '<tr>
                <td>' . htmlspecialchars($c['cat_name'] ?? '-') . '</td>
                <td style="text-align:right;">' . number_format($c['revenue'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;color:#dc2626;">' . number_format($c['cost'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;font-weight:700;color:#16a34a;">' . number_format($c['profit'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;">' . ($c['margin_pct'] ?? 0) . '%</td>
                <td style="text-align:right;">' . (int)$c['orders'] . '</td>
            </tr>';
        }

        // ── Top customers ──
        $custRows = '';
        foreach ($topCust as $i => $c) {
            $cm = floatval($c['revenue']) > 0 ? round(floatval($c['profit']) / floatval($c['revenue']) * 100, 1) : 0;
            $custRows .= '<tr>
                <td style="text-align:center;">' . ($i + 1) . '</td>
                <td>' . htmlspecialchars($c['full_name'] ?? 'Khách lẻ') . '</td>
                <td>' . htmlspecialchars($c['email'] ?? '-') . '</td>
                <td style="text-align:right;">' . (int)$c['order_count'] . '</td>
                <td style="text-align:right;">' . number_format($c['revenue'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;font-weight:700;color:#16a34a;">' . number_format($c['profit'], 0, ',', '.') . ' ₫</td>
                <td style="text-align:right;">' . $cm . '%</td>
            </tr>';
        }

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head><meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
            h1 { color: #1e293b; font-size: 20px; margin-bottom: 4px; }
            h2 { color: #475569; font-size: 14px; font-weight: 600; margin: 24px 0 10px; border-bottom: 2px solid #16a34a; padding-bottom: 6px; }
            .period { color: #94a3b8; font-size: 12px; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
            th { background: #16a34a; color: #ffffff; font-weight: 700; padding: 8px 12px; border: 1px solid #15803d; font-size: 11px; text-align: left; }
            td { padding: 6px 12px; border: 1px solid #e2e8f0; }
            tr:nth-child(even) { background: #f8fafc; }
            .kpi-grid { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
            .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 20px; flex: 1; min-width: 140px; }
            .kpi-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; }
            .kpi-value { font-size: 18px; font-weight: 900; }
            .sum-row { background: #f0fdf4 !important; font-weight: 700; }
            .sum-row td { border-top: 2px solid #16a34a; }
        </style>
        </head><body>
            <h1>💰 Báo cáo lợi nhuận</h1>
            <div class="period">' . $periodLabel . '</div>

            <div class="kpi-grid">
                <div class="kpi-card"><div class="kpi-label">Doanh thu</div><div class="kpi-value" style="color:#2563eb;">' . number_format($totalRevenue, 0, ',', '.') . ' ₫</div></div>
                <div class="kpi-card"><div class="kpi-label">Giá vốn</div><div class="kpi-value" style="color:#dc2626;">' . number_format($totalCost, 0, ',', '.') . ' ₫</div></div>
                <div class="kpi-card"><div class="kpi-label">Lợi nhuận</div><div class="kpi-value" style="color:#16a34a;">' . number_format($totalProfit, 0, ',', '.') . ' ₫</div></div>
                <div class="kpi-card"><div class="kpi-label">Tỷ suất LN</div><div class="kpi-value" style="color:#7c3aed;">' . $marginPct . '%</div></div>
                <div class="kpi-card"><div class="kpi-label">Tổng đơn</div><div class="kpi-value" style="color:#06b6d4;">' . number_format($totalOrders) . '</div></div>
            </div>

            <h2>📈 Chi tiết lợi nhuận theo ngày</h2>
            <table>
                <tr><th>Ngày</th><th style="text-align:right;">Đơn hàng</th><th style="text-align:right;">Doanh thu</th><th style="text-align:right;">Giá vốn</th><th style="text-align:right;">Lợi nhuận</th><th style="text-align:right;">Margin</th></tr>
                ' . $profitRows . '
                <tr class="sum-row">
                    <td><strong>TỔNG</strong></td>
                    <td style="text-align:right;"><strong>' . $totalOrders . '</strong></td>
                    <td style="text-align:right;"><strong>' . number_format($totalRevenue, 0, ',', '.') . ' ₫</strong></td>
                    <td style="text-align:right;"><strong>' . number_format($totalCost, 0, ',', '.') . ' ₫</strong></td>
                    <td style="text-align:right;"><strong>' . number_format($totalProfit, 0, ',', '.') . ' ₫</strong></td>
                    <td style="text-align:right;"><strong>' . $marginPct . '%</strong></td>
                </tr>
            </table>

            <h2>📊 Lợi nhuận theo danh mục</h2>
            <table>
                <tr><th>Danh mục</th><th style="text-align:right;">Doanh thu</th><th style="text-align:right;">Giá vốn</th><th style="text-align:right;">Lợi nhuận</th><th style="text-align:right;">Margin</th><th style="text-align:right;">Đơn hàng</th></tr>
                ' . ($catRows ?: '<tr><td colspan="6" style="text-align:center;color:#94a3b8;">Chưa có dữ liệu</td></tr>') . '
            </table>

            <h2>👤 Top khách hàng theo lợi nhuận</h2>
            <table>
                <tr><th style="text-align:center;">#</th><th>Tên khách hàng</th><th>Email</th><th style="text-align:right;">Đơn hàng</th><th style="text-align:right;">Doanh thu</th><th style="text-align:right;">Lợi nhuận</th><th style="text-align:right;">Margin</th></tr>
                ' . ($custRows ?: '<tr><td colspan="7" style="text-align:center;color:#94a3b8;">Chưa có dữ liệu</td></tr>') . '
            </table>
        </body></html>';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bao-cao-loi-nhuan_' . date('Y-m-d') . '.xls"');
        echo "\xEF\xBB\xBF" . $html;
        exit;
    }

    // ══════════════════════════════════════════
    //  Internal helpers
    // ══════════════════════════════════════════

    /**
     * Xuất file Excel với header theo dạng orders (có KPI)
     */
    private static function output(string $filename, array $rows, int $totalOrders, float $totalAmount, int $completedCount, string $rowsHtml): void
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head><meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
            h1 { color: #1e293b; font-size: 20px; margin-bottom: 4px; }
            .subtitle { color: #94a3b8; font-size: 12px; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
            th { background: #6366f1; color: #fff; font-weight: 700; padding: 8px 12px; border: 1px solid #4f46e5; font-size: 11px; text-align: left; }
            td { padding: 6px 12px; border: 1px solid #e2e8f0; }
            tr:nth-child(even) { background: #f8fafc; }
            .summary { background: #f0fdf4; padding: 12px 16px; border-radius: 8px; margin-top: 16px; display: flex; gap: 24px; flex-wrap: wrap; }
            .summary-item { font-size: 13px; }
            .summary-item strong { font-size: 15px; }
        </style>
        </head><body>
            <h1>📋 Danh sách đơn hàng</h1>
            <div class="subtitle">Xuất ngày: ' . date('d/m/Y H:i') . ' | Tổng số: ' . $totalOrders . ' đơn</div>
            <table>
                <tr>
                    <th style="text-align:center;">Mã ĐH</th><th>Khách hàng</th><th>Email</th><th>SĐT</th>
                    <th>Địa chỉ</th><th style="text-align:right;">Tổng tiền</th><th style="text-align:right;">Giảm giá</th>
                    <th>Voucher</th><th style="text-align:right;">Phí ship</th><th>Trạng thái</th><th>Ngày tạo</th>
                </tr>
                ' . $rowsHtml . '
            </table>
            <div class="summary">
                <div class="summary-item">📦 <strong>' . $totalOrders . '</strong> đơn hàng</div>
                <div class="summary-item">✅ <strong>' . $completedCount . '</strong> hoàn thành</div>
                <div class="summary-item">💰 Tổng doanh thu: <strong style="color:#16a34a;">' . number_format($totalAmount, 0, ',', '.') . ' ₫</strong></div>
            </div>
        </body></html>';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        echo "\xEF\xBB\xBF" . $html;
        exit;
    }

    /**
     * Xuất file Excel dạng đơn giản (products, users)
     */
    private static function outputSimple(string $filename, array $headers, string $rowsHtml, string $summaryHtml = ''): void
    {
        $headerRow = '';
        foreach ($headers[0] as $h) {
            $headerRow .= '<th>' . htmlspecialchars($h) . '</th>';
        }

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head><meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
            h1 { color: #1e293b; font-size: 20px; margin-bottom: 4px; }
            .subtitle { color: #94a3b8; font-size: 12px; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; }
            th { background: #6366f1; color: #fff; font-weight: 700; padding: 8px 12px; border: 1px solid #4f46e5; font-size: 11px; text-align: left; }
            td { padding: 6px 12px; border: 1px solid #e2e8f0; }
            tr:nth-child(even) { background: #f8fafc; }
            .summary { margin-top: 16px; }
        </style>
        </head><body>
            <h1>📋 ' . htmlspecialchars($filename) . '</h1>
            <div class="subtitle">Xuất ngày: ' . date('d/m/Y H:i') . '</div>
            <table>
                <tr>' . $headerRow . '</tr>
                ' . $rowsHtml . '
            </table>
            ' . $summaryHtml . '
        </body></html>';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        echo "\xEF\xBB\xBF" . $html;
        exit;
    }
}

// ── class_alias để gọi từ global namespace (admin/handlers/other.php) ──
class_alias('App\\Helpers\\ExportHelper', '\\ExportHelper');
