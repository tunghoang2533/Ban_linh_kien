<style>
/* =============================================
   ORDER DETAIL PAGE - PREMIUM STYLE
   ============================================= */
:root {
    --blue-main: #288ad6;
    --blue-dark: #1a6eb5;
    --blue-light: #e8f3ff;
    --green: #10b981;
    --green-light: #d1fae5;
    --red: #e10c00;
    --orange: #f59e0b;
    --gray-bg: #f4f6fa;
    --card-bg: #ffffff;
    --border: #e2e8f0;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --radius: 16px;
    --shadow: 0 4px 24px rgba(40,138,214,0.09);
    --shadow-hover: 0 8px 32px rgba(40,138,214,0.16);
}

.od-wrapper {
    background: var(--gray-bg);
    min-height: 100vh;
    padding: 40px 0 60px;
}

.od-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 20px;
}

/* === BREADCRUMB === */
.od-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 28px;
    font-size: 14px;
    color: var(--text-muted);
}
.od-breadcrumb a {
    color: var(--blue-main);
    text-decoration: none;
    font-weight: 600;
    transition: color .2s;
}
.od-breadcrumb a:hover { color: var(--blue-dark); }
.od-breadcrumb i { font-size: 11px; }

/* === HEADER CARD === */
.od-header-card {
    background: linear-gradient(135deg, #1a6eb5 0%, #288ad6 60%, #38b6ff 100%);
    border-radius: var(--radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 8px 32px rgba(40,138,214,0.3);
    position: relative;
    overflow: hidden;
}
.od-header-card::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.od-header-card::after {
    content: '';
    position: absolute;
    bottom: -60px; right: 80px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.od-header-left h1 {
    margin: 0 0 6px;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: .3px;
}
.od-header-left .od-date {
    font-size: 13.5px;
    opacity: .85;
    display: flex;
    align-items: center;
    gap: 6px;
}
.od-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 99px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 2px solid rgba(255,255,255,0.4);
    backdrop-filter: blur(8px);
    position: relative;
    z-index: 1;
}
.od-status-badge.pending  { background: rgba(245,158,11,0.25); border-color: rgba(245,158,11,0.5); }
.od-status-badge.completed { background: rgba(16,185,129,0.25); border-color: rgba(16,185,129,0.5); }
.od-status-badge.cancelled { background: rgba(239,68,68,0.25); border-color: rgba(239,68,68,0.5); }
.od-status-dot {
    width: 9px; height: 9px;
    border-radius: 50%;
    background: currentColor;
    animation: pulse-dot 1.6s infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: .5; transform: scale(.7); }
}

/* === INFO GRID === */
.od-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
@media (max-width: 640px) { .od-info-grid { grid-template-columns: 1fr; } }

.od-info-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 22px 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}
.od-info-card-title {
    display: flex;
    align-items: center;
    gap: 9px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .7px;
    margin-bottom: 14px;
}
.od-info-card-title i {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: var(--blue-light);
    color: var(--blue-main);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
}
.od-info-row {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.od-info-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 14px;
    color: var(--text-main);
}
.od-info-item .label {
    min-width: 90px;
    color: var(--text-muted);
    font-size: 13px;
    padding-top: 1px;
}
.od-info-item .value { font-weight: 600; }

/* === PRODUCTS TABLE === */
.od-products-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 24px;
}
.od-products-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 20px 24px 18px;
    border-bottom: 1px solid var(--border);
}
.od-products-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--text-main);
}
.od-products-header .count-badge {
    background: var(--blue-light);
    color: var(--blue-main);
    border-radius: 99px;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 10px;
}

.od-product-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 24px;
    border-bottom: 1px solid #f8fafc;
    transition: background .18s;
}
.od-product-item:last-child { border-bottom: none; }
.od-product-item:hover { background: #fafcff; }

.od-product-img {
    width: 76px; height: 76px;
    border-radius: 12px;
    object-fit: cover;
    border: 1px solid var(--border);
    background: #f8fafc;
    flex-shrink: 0;
}
.od-product-img-placeholder {
    width: 76px; height: 76px;
    border-radius: 12px;
    background: linear-gradient(135deg, #e8f3ff, #c7e3ff);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 28px;
    color: var(--blue-main);
}

.od-product-info { flex: 1; min-width: 0; }
.od-product-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.od-product-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.od-product-price {
    font-size: 14px;
    color: var(--text-muted);
}
.od-product-qty {
    font-size: 13px;
    background: #f1f5f9;
    color: var(--text-muted);
    padding: 2px 10px;
    border-radius: 99px;
    font-weight: 600;
}
.od-product-subtotal {
    text-align: right;
    flex-shrink: 0;
}
.od-product-subtotal-label {
    font-size: 11px;
    color: var(--text-muted);
    margin-bottom: 3px;
}
.od-product-subtotal-value {
    font-size: 16px;
    font-weight: 800;
    color: var(--red);
}

/* === SUMMARY CARD === */
.od-summary-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 22px 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    margin-bottom: 24px;
}
.od-summary-card h3 {
    margin: 0 0 16px;
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 8px;
}
.od-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
    color: var(--text-muted);
}
.od-summary-row:last-child { border-bottom: none; }
.od-summary-row .val { font-weight: 600; color: var(--text-main); }
.od-summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0 0;
    margin-top: 4px;
    border-top: 2px solid var(--border);
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
}
.od-summary-total .val {
    font-size: 22px;
    font-weight: 900;
    color: var(--red);
}

/* === BACK BUTTON === */
.od-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 12px;
    background: var(--blue-main);
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: background .2s, transform .2s, box-shadow .2s;
    box-shadow: 0 4px 14px rgba(40,138,214,0.3);
}
.od-back-btn:hover {
    background: var(--blue-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(40,138,214,0.4);
    color: #fff;
}

/* === REORDER BUTTON === */
.od-reorder-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 12px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s, transform .2s, box-shadow .2s;
    box-shadow: 0 4px 14px rgba(16,185,129,0.3);
}
.od-reorder-btn:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16,185,129,0.4);
}

/* Discount badge */
.od-discount-row .val { color: var(--green); }
.od-voucher-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--green-light);
    color: #059669;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 8px;
    margin-left: 8px;
}
</style>

<div class="od-wrapper">
    <div class="od-container">

        <!-- Breadcrumb -->
        <div class="od-breadcrumb">
            <a href="index.php"><i class="fa fa-home"></i> Trang chủ</a>
            <i class="fa fa-chevron-right"></i>
            <a href="lichsu.php">Lịch sử mua hàng</a>
            <i class="fa fa-chevron-right"></i>
            <span>Chi tiết đơn #<?php echo $order['id']; ?></span>
        </div>

        <!-- Header Card -->
        <div class="od-header-card">
            <div class="od-header-left">
                <h1><i class="fa fa-shopping-bag"></i> &nbsp;Đơn hàng #<?php echo $order['id']; ?></h1>
                <div class="od-date">
                    <i class="fa fa-calendar"></i>
                    Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                </div>
            </div>
            <?php
                $status = strtolower($order['status']);
                $statusText = ['pending' => 'Chờ xử lý', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
                $statusIcons = ['pending' => 'fa-clock-o', 'completed' => 'fa-check-circle', 'cancelled' => 'fa-times-circle'];
                $displayText = $statusText[$status] ?? strtoupper($status);
                $displayIcon = $statusIcons[$status] ?? 'fa-circle';
            ?>
            <div class="od-status-badge <?php echo $status; ?>">
                <span class="od-status-dot"></span>
                <i class="fa <?php echo $displayIcon; ?>"></i>
                <?php echo $displayText; ?>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="od-info-grid">
            <!-- Thông tin người nhận -->
            <div class="od-info-card">
                <div class="od-info-card-title">
                    <i class="fa fa-user" style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:#e8f3ff;color:#288ad6;"></i>
                    Thông tin người nhận
                </div>
                <div class="od-info-row">
                    <div class="od-info-item">
                        <span class="label">Họ tên:</span>
                        <span class="value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </div>
                    <div class="od-info-item">
                        <span class="label">Điện thoại:</span>
                        <span class="value"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                    </div>
                    <div class="od-info-item">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                    </div>
                    <div class="od-info-item">
                        <span class="label">Địa chỉ:</span>
                        <span class="value"><?php echo htmlspecialchars($order['customer_address']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Thông tin đơn hàng -->
            <div class="od-info-card">
                <div class="od-info-card-title">
                    <i class="fa fa-info-circle" style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:#e8f3ff;color:#288ad6;"></i>
                    Thông tin đơn hàng
                </div>
                <div class="od-info-row">
                    <div class="od-info-item">
                        <span class="label">Mã đơn:</span>
                        <span class="value">#<?php echo $order['id']; ?></span>
                    </div>
                    <div class="od-info-item">
                        <span class="label">Ngày đặt:</span>
                        <span class="value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="od-info-item">
                        <span class="label">Trạng thái:</span>
                        <span class="value" style="color:<?php echo $status === 'completed' ? '#10b981' : ($status === 'cancelled' ? '#e10c00' : '#f59e0b'); ?>">
                            <?php echo $displayText; ?>
                        </span>
                    </div>
                    <div class="od-info-item">
                        <span class="label">Số sản phẩm:</span>
                        <span class="value"><?php echo count($orderItems); ?> sản phẩm</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="od-products-card">
            <div class="od-products-header">
                <i class="fa fa-list-alt" style="color:#288ad6;font-size:18px;"></i>
                <h2>Sản phẩm trong đơn hàng</h2>
                <span class="count-badge"><?php echo count($orderItems); ?> sản phẩm</span>
            </div>

            <?php if (empty($orderItems)): ?>
                <div style="padding: 40px; text-align: center; color: #94a3b8;">
                    <i class="fa fa-inbox" style="font-size: 40px; display:block; margin-bottom: 12px;"></i>
                    Không tìm thấy sản phẩm trong đơn hàng này.
                </div>
            <?php else: ?>
                <?php foreach ($orderItems as $item): ?>
                    <div class="od-product-item">
                        <!-- Ảnh sản phẩm -->
                        <?php
                            $imgPath = '';
                            if (!empty($item['image'])) {
                                // Hỗ trợ cả đường dẫn JSON array hoặc chuỗi thường
                                $decoded = json_decode($item['image'], true);
                                if (is_array($decoded) && !empty($decoded[0])) {
                                    $imgPath = $decoded[0];
                                } else {
                                    $imgPath = $item['image'];
                                }
                            }
                        ?>
                        <?php if ($imgPath): ?>
                            <img class="od-product-img" loading="lazy" 
                                 src="<?php echo BASE_URL . ltrim($imgPath, '/'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="od-product-img-placeholder" style="display:none;">🖥️</div>
                        <?php else: ?>
                            <div class="od-product-img-placeholder">🖥️</div>
                        <?php endif; ?>

                        <!-- Thông tin sản phẩm -->
                        <div class="od-product-info">
                            <div class="od-product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="od-product-meta">
                                <span class="od-product-price">
                                    Đơn giá: <strong><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</strong>
                                </span>
                                <span class="od-product-qty">x<?php echo $item['quantity']; ?></span>
                            </div>
                        </div>

                        <!-- Thành tiền -->
                        <div class="od-product-subtotal">
                            <div class="od-product-subtotal-label">Thành tiền</div>
                            <div class="od-product-subtotal-value">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tóm tắt thanh toán -->
        <div class="od-summary-card">
            <h3><i class="fa fa-calculator" style="color:#288ad6;"></i> Tóm tắt thanh toán</h3>

            <?php
                $subtotal = 0;
                foreach ($orderItems as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }
                $discount    = isset($order['discount_amount']) ? (float)$order['discount_amount'] : 0;
                $shippingFee = isset($order['shipping_fee'])    ? (float)$order['shipping_fee']    : 0;
                $voucher     = $order['voucher_code'] ?? '';
            ?>

            <div class="od-summary-row">
                <span>Tổng giá trị sản phẩm</span>
                <span class="val"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</span>
            </div>

            <div class="od-summary-row">
                <span><i class="fa fa-truck" style="color:#288ad6;margin-right:5px;"></i>Phí vận chuyển</span>
                <span class="val" style="color:#e10c00;"><?php echo $shippingFee > 0 ? number_format($shippingFee, 0, ',', '.') . '₫' : 'Miễn phí'; ?></span>
            </div>

            <?php if ($discount > 0): ?>
            <div class="od-summary-row od-discount-row">
                <span>
                    Giảm giá voucher
                    <?php if ($voucher): ?>
                        <span class="od-voucher-tag"><i class="fa fa-tag"></i> <?php echo htmlspecialchars($voucher); ?></span>
                    <?php endif; ?>
                </span>
                <span class="val">-<?php echo number_format($discount, 0, ',', '.'); ?>₫</span>
            </div>
            <?php endif; ?>


            <div class="od-summary-total">
                <span>Tổng thanh toán</span>
                <span class="val"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</span>
            </div>
        </div>

        <!-- Nút hành động -->
        <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:center;">
            <a href="lichsu.php" class="od-back-btn">
                <i class="fa fa-arrow-left"></i> Quay lại lịch sử mua hàng
            </a>
            <form method="POST" action="mualaisan.php" style="margin:0;">
                <?php echo CsrfHelper::field(); ?>
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <button type="submit" class="od-reorder-btn">
                    <i class="fa fa-repeat"></i> Mua lại
                </button>
            </form>
        </div>

    </div>
</div>
