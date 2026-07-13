<?php
/**
 * Abandoned Cart Recovery — Quản lý giỏ hàng bị bỏ quên
 * Theo dõi khách hàng đã thêm sản phẩm vào giỏ nhưng chưa thanh toán
 */

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xóa giỏ
    if (isset($_POST['delete_cart'])) {
        $db->prepare("DELETE FROM abandoned_carts WHERE id = ?")->execute([intval($_POST['cart_id'])]);
        header('Location: ?page=abandoned_carts&success=deleted');
        exit;
    }
    // Gửi email nhắc nhở
    if (isset($_POST['send_reminder'])) {
        $cartId = intval($_POST['cart_id']);
        $stmt = $db->prepare("SELECT * FROM abandoned_carts WHERE id = ?");
        $stmt->execute([$cartId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cart && !empty($cart['user_email'])) {
            // Gửi email nhắc nhở
            $subject = "🛒 Bạn còn sản phẩm trong giỏ hàng - " . SITE_NAME;
            $items = json_decode($cart['cart_data'], true) ?: [];
            $itemList = '';
            foreach ($items as $item) {
                $itemList .= "<tr><td>" . htmlspecialchars($item['name'] ?? '') . "</td><td>x" . intval($item['quantity'] ?? 1) . "</td><td>" . number_format($item['price'] ?? 0, 0, ',', '.') . "₫</td></tr>";
            }
            $totalFormatted = number_format($cart['cart_total'], 0, ',', '.');
            $htmlBody = "
            <div style='max-width:600px;margin:0 auto;font-family:Arial,sans-serif;'>
                <div style='background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;border-radius:16px 16px 0 0;'>
                    <h1 style='color:white;margin:0;font-size:24px;'>🛒 Bỏ quên giỏ hàng?</h1>
                </div>
                <div style='padding:30px;background:white;border:1px solid #e5e7eb;'>
                    <p>Xin chào <strong>" . htmlspecialchars($cart['user_name'] ?? 'bạn') . "</strong>,</p>
                    <p>Chúng tôi thấy bạn có <strong>" . intval($cart['item_count']) . " sản phẩm</strong> trong giỏ hàng nhưng chưa thanh toán. Đừng bỏ lỡ!</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr style='background:#f3f4f6;'><th style='padding:8px;text-align:left;'>Sản phẩm</th><th style='padding:8px;'>SL</th><th style='padding:8px;text-align:right;'>Giá</th></tr>
                        $itemList
                        <tr><td colspan='2' style='padding:8px;text-align:right;font-weight:700;'>Tổng cộng:</td><td style='padding:8px;text-align:right;font-weight:800;color:#6366f1;font-size:18px;'>$totalFormatted ₫</td></tr>
                    </table>
                    <a href='" . BASE_URL . "giohang.php' style='display:block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;padding:14px 24px;border-radius:10px;text-align:center;text-decoration:none;font-size:16px;font-weight:700;margin:20px 0;'>
                        🛒 Thanh toán ngay
                    </a>
                </div>
                <div style='background:#f9fafb;padding:15px;text-align:center;font-size:12px;color:#9ca3af;border-radius:0 0 16px 16px;'>
                    &copy; 2025 " . SITE_NAME . " — Hotline: 0909 000 000
                </div>
            </div>";
            
            EmailHelper::send($cart['user_email'], $cart['user_name'] ?? '', $subject, $htmlBody);
            
            // Cập nhật reminder count
            $db->prepare("UPDATE abandoned_carts SET reminder_count = reminder_count + 1, last_reminder_at = NOW(), status = 'contacted' WHERE id = ?")
               ->execute([$cartId]);
            header('Location: ?page=abandoned_carts&success=reminded');
            exit;
        }
    }
    // Đánh dấu đã phục hồi
    if (isset($_POST['mark_recovered'])) {
        $db->prepare("UPDATE abandoned_carts SET status = 'recovered', recovered_at = NOW() WHERE id = ?")->execute([intval($_POST['cart_id'])]);
        header('Location: ?page=abandoned_carts&success=recovered');
        exit;
    }
}

// Filters
$acStatus = $_GET['status'] ?? 'active';
$acSearch = trim($_GET['q'] ?? '');

// Stats
$acStats = [
    'active'   => (int)$db->query("SELECT COUNT(*) FROM abandoned_carts WHERE status='active'")->fetchColumn(),
    'contacted' => (int)$db->query("SELECT COUNT(*) FROM abandoned_carts WHERE status='contacted'")->fetchColumn(),
    'recovered' => (int)$db->query("SELECT COUNT(*) FROM abandoned_carts WHERE status='recovered'")->fetchColumn(),
    'total'    => (int)$db->query("SELECT COUNT(*) FROM abandoned_carts")->fetchColumn(),
];

// Query
$where = "1=1";
$params = [];
if ($acStatus !== 'all') {
    $where .= " AND status = :status";
    $params[':status'] = $acStatus;
}
if ($acSearch) {
    $where .= " AND (user_name LIKE :q OR user_email LIKE :q2)";
    $params[':q'] = "%$acSearch%";
    $params[':q2'] = "%$acSearch%";
}

$acSql = "SELECT * FROM abandoned_carts WHERE $where ORDER BY created_at DESC LIMIT 50";
$stmt = $db->prepare($acSql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$abandonedCarts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Success messages
$acSuccess = '';
if (isset($_GET['success'])) {
    $map = ['deleted' => 'Đã xóa giỏ hàng!', 'reminded' => '✅ Đã gửi email nhắc nhở!', 'recovered' => '✅ Đã đánh dấu phục hồi!'];
    $acSuccess = $map[$_GET['success']] ?? '';
}

// Tổng doanh thu có thể phục hồi
$potentialRevenue = array_sum(array_column($abandonedCarts, 'cart_total'));
?>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-shopping-cart" style="color:#f59e0b;margin-right:10px;"></i>Giỏ hàng bị bỏ quên</h1>
            <p>Theo dõi và phục hồi khách hàng đã thêm sản phẩm nhưng chưa thanh toán</p>
        </div>
    </div>

    <?php if ($acSuccess): ?>
    <div class="alert alert-success" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:4px solid #10b981;padding:14px 20px;border-radius:10px;margin-bottom:20px;">
        <i class="fas fa-check-circle" style="color:#10b981;"></i>
        <?php echo htmlspecialchars($acSuccess); ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
        <div class="stat-card" onclick="window.location='?page=abandoned_carts&status=active'" style="cursor:pointer;border-left:4px solid #f59e0b;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value" style="color:#f59e0b;"><?php echo $acStats['active']; ?></div>
                <div class="stat-label">Chưa xử lý</div>
            </div>
        </div>
        <div class="stat-card" onclick="window.location='?page=abandoned_carts&status=contacted'" style="cursor:pointer;border-left:4px solid #6366f1;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"><i class="fas fa-envelope"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $acStats['contacted']; ?></div>
                <div class="stat-label">Đã liên hệ</div>
            </div>
        </div>
        <div class="stat-card" onclick="window.location='?page=abandoned_carts&status=recovered'" style="cursor:pointer;border-left:4px solid #10b981;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value" style="color:#10b981;"><?php echo $acStats['recovered']; ?></div>
                <div class="stat-label">Đã phục hồi</div>
            </div>
        </div>
        <div class="stat-card" style="border-left:4px solid #ec4899;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#ec4899,#f472b6);"><i class="fas fa-coins"></i></div>
            <div class="stat-content">
                <div class="stat-value" style="font-size:18px;color:#ec4899;"><?php echo number_format($potentialRevenue, 0, ',', '.'); ?>₫</div>
                <div class="stat-label">Tiềm năng phục hồi</div>
            </div>
        </div>
    </div>

    <!-- Search + Filters -->
    <div style="background:var(--bg-surface);border-radius:12px;border:1px solid var(--border-subtle);padding:16px 20px;margin-bottom:20px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="page" value="abandoned_carts">
            <div style="flex:1;min-width:180px;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($acSearch); ?>" class="form-control" placeholder="Tìm theo tên hoặc email..." style="width:100%;">
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php foreach (['all'=>'Tất cả','active'=>'Chưa xử lý','contacted'=>'Đã liên hệ','recovered'=>'Đã phục hồi'] as $v => $l): ?>
                <a href="?page=abandoned_carts&status=<?php echo $v; ?>&q=<?php echo urlencode($acSearch); ?>"
                   style="padding:7px 14px;border-radius:99px;font-size:12px;font-weight:600;text-decoration:none;<?php echo $acStatus===$v ? 'background:#6366f1;color:white;' : 'background:var(--bg-elevated);color:var(--text-muted);'; ?>">
                    <?php echo $l; ?>
                </a>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-sm btn-primary">Tìm</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="dashboard-section">
        <div class="table-responsive" style="border-radius:0;box-shadow:none;border:none;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Giỏ hàng</th>
                        <th>Tổng tiền</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th>Đã nhắc</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($abandonedCarts)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:50px;color:var(--text-faint);">
                            <i class="fas fa-check-circle" style="font-size:40px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                            Tuyệt vời! Không có giỏ hàng bị bỏ quên nào!
                        </td>
                    </tr>
                    <?php else: foreach ($abandonedCarts as $cart): 
                        $cartItems = json_decode($cart['cart_data'], true) ?: [];
                        $itemPreview = array_slice($cartItems, 0, 3);
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($cart['user_name'] ?? 'Khách vãng lai'); ?></div>
                            <div style="font-size:11px;color:var(--text-muted);">
                                <?php if ($cart['user_email']): ?>
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($cart['user_email']); ?><br>
                                <?php endif; ?>
                                <?php if ($cart['user_phone']): ?>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($cart['user_phone']); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:600;"><?php echo intval($cart['item_count']); ?> sản phẩm</div>
                            <div style="font-size:11px;color:var(--text-muted);">
                                <?php foreach ($itemPreview as $it): ?>
                                <div>• <?php echo htmlspecialchars(mb_strimwidth($it['name'] ?? $it['product_name'] ?? '', 0, 25, '...')); ?> (x<?php echo intval($it['quantity'] ?? 1); ?>)</div>
                                <?php endforeach; ?>
                                <?php if (count($cartItems) > 3): ?>
                                <div style="color:var(--text-faint);">+<?php echo count($cartItems) - 3; ?> sản phẩm khác</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight:800;color:#6366f1;font-size:16px;"><?php echo number_format($cart['cart_total'], 0, ',', '.'); ?>₫</span>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            <div><?php echo date('d/m/Y', strtotime($cart['created_at'])); ?></div>
                            <div style="font-size:10px;"><?php echo date('H:i', strtotime($cart['created_at'])); ?></div>
                        </td>
                        <td>
                            <?php
                            $acStMap = [
                                'active'    => ['bg'=>'#fef3c7','text'=>'#92400e','label'=>'Chưa xử lý'],
                                'contacted' => ['bg'=>'#dbeafe','text'=>'#1e40af','label'=>'Đã liên hệ'],
                                'recovered' => ['bg'=>'#d1fae5','text'=>'#065f46','label'=>'Đã phục hồi'],
                                'expired'   => ['bg'=>'#f1f5f9','text'=>'#64748b','label'=>'Hết hạn'],
                            ];
                            $acSt = $acStMap[$cart['status']] ?? $acStMap['expired'];
                            ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $acSt['bg']; ?>;color:<?php echo $acSt['text']; ?>;">
                                <i class="fas fa-circle" style="font-size:6px;"></i> <?php echo $acSt['label']; ?>
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div style="font-weight:700;font-size:18px;color:var(--text-primary);"><?php echo intval($cart['reminder_count']); ?></div>
                            <?php if ($cart['last_reminder_at']): ?>
                            <div style="font-size:10px;color:var(--text-muted);"><?php echo date('d/m', strtotime($cart['last_reminder_at'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <?php if ($cart['status'] === 'active' && !empty($cart['user_email'])): ?>
                                <form method="POST">
                                    <input type="hidden" name="cart_id" value="<?php echo $cart['id']; ?>">
                                    <button type="submit" name="send_reminder" class="btn btn-sm btn-primary" title="Gửi email nhắc nhở">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($cart['status'] !== 'recovered'): ?>
                                <form method="POST">
                                    <input type="hidden" name="cart_id" value="<?php echo $cart['id']; ?>">
                                    <button type="submit" name="mark_recovered" class="btn btn-sm btn-success" title="Đánh dấu đã phục hồi">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('Xóa giỏ hàng này?')">
                                    <input type="hidden" name="cart_id" value="<?php echo $cart['id']; ?>">
                                    <button type="submit" name="delete_cart" class="btn btn-sm btn-danger" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
