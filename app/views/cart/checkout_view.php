<?php
$selectedIdsRaw  = trim($_GET['ids'] ?? '');
$selectedIds     = $selectedIdsRaw !== ''
    ? array_filter(array_map('intval', explode(',', $selectedIdsRaw)))
    : [];
$fullCart    = $_SESSION['cart'] ?? [];
$cartItems   = (!empty($selectedIds))
    ? array_filter($fullCart, fn($item) => in_array((int)$item['id'], $selectedIds))
    : $fullCart;
$voucherCode    = $_GET['voucher']  ?? '';
$discountAmount = floatval($_GET['discount'] ?? 0);
$shipFee        = floatval($_GET['ship'] ?? 50000);
$cartTotal      = 0;
foreach ($cartItems as $item) $cartTotal += $item['price'] * $item['quantity'];
$finalTotal = max(0, $cartTotal + $shipFee - $discountAmount);
?>
<style>
/* ===== CHECKOUT PAGE ===== */
.checkout-page {
    margin: 48px auto 80px;
    max-width: 1100px;
    padding: 0 16px;
    font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e364f;
}

/* Hero */
.checkout-hero {
    display: flex;
    align-items: center;
    gap: 14px;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    border-radius: 20px;
    padding: 22px 30px;
    color: #fff;
    margin-bottom: 30px;
    box-shadow: 0 16px 40px rgba(29,78,216,.18);
}
.checkout-hero-icon {
    width: 52px; height: 52px;
    background: rgba(255,255,255,.15);
    border-radius: 14px;
    display: grid; place-items: center;
    font-size: 24px; flex-shrink: 0;
}
.checkout-hero h2 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: .02em; }
.checkout-hero p  { margin: 4px 0 0; font-size: 13.5px; opacity: .85; }

/* 2-column grid */
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 24px;
    align-items: start;
}

/* ---- CARD base ---- */
.co-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(24,81,153,.07);
    overflow: hidden;
}
.co-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid #edf2f8;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #1c344f;
    background: #f8fbff;
}
.co-card-header .icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: grid; place-items: center;
    font-size: 16px;
    flex-shrink: 0;
}
.co-card-body { padding: 24px; }

/* ---- FORM FIELDS ---- */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}
.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    letter-spacing: .02em;
}
.form-group label span.req { color: #e10c00; margin-left: 2px; }
.form-control {
    padding: 11px 14px;
    border: 1.5px solid #dce6f5;
    border-radius: 12px;
    font-size: 14px;
    color: #1e364f;
    background: #fff;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
    width: 100%;
    box-sizing: border-box;
}
.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.form-control::placeholder { color: #94a3b8; }
textarea.form-control { resize: vertical; min-height: 90px; }

/* ---- PAYMENT METHOD ---- */
.pay-options { display: flex; flex-direction: column; gap: 10px; }
.pay-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 16px;
    border: 1.5px solid #dce6f5;
    border-radius: 12px;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.pay-option:has(input:checked) {
    border-color: #2563eb;
    background: #eff6ff;
}
.pay-option input[type=radio] {
    accent-color: #2563eb;
    width: 17px; height: 17px;
    flex-shrink: 0;
}
.pay-option-icon { font-size: 22px; }
.pay-option-label { flex: 1; }
.pay-option-label strong { display: block; font-size: 14px; color: #1e364f; }
.pay-option-label span   { font-size: 12px; color: #64748b; }

/* ---- RIGHT COLUMN ---- */
/* Product list */
.order-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #edf2f8;
}
.order-item:last-child { border-bottom: none; }
.order-item-img {
    width: 52px; height: 52px;
    border-radius: 10px;
    background: #f0f5ff;
    border: 1px solid #e2eaf6;
    overflow: hidden;
    flex-shrink: 0;
    display: grid; place-items: center;
}
.order-item-img img { max-width: 100%; max-height: 100%; object-fit: cover; }
.order-item-info { flex: 1; min-width: 0; }
.order-item-name {
    font-size: 13.5px; font-weight: 600; color: #1e364f;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.order-item-qty { font-size: 12px; color: #64748b; margin-top: 2px; }
.order-item-price { font-size: 14px; font-weight: 700; color: #1f4d8a; white-space: nowrap; }

/* Price breakdown */
.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13.5px;
    color: #5f6d86;
    padding: 7px 0;
}
.price-row .val { font-weight: 600; color: #1e364f; }
.price-row.discount .val { color: #16a34a; font-weight: 700; }
.price-row.ship    .val { color: #e10c00; font-weight: 700; }
.price-divider { border: none; border-top: 1.5px dashed #dce6f5; margin: 6px 0; }
.price-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0 0;
}
.price-total .label { font-size: 15px; font-weight: 700; color: #1c344f; }
.price-total .amount { font-size: 22px; font-weight: 800; color: #e10c00; }

/* Action buttons */
.checkout-actions { display: flex; gap: 12px; margin-top: 16px; }
.btn-back {
    flex: 0 0 auto;
    padding: 14px 20px;
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background .2s;
}
.btn-back:hover { background: #e2e8f0; }
.btn-confirm {
    flex: 1;
    padding: 14px 20px;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;
    border: none;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 10px 28px rgba(22,163,74,.22);
    transition: transform .15s, box-shadow .15s;
    letter-spacing: .02em;
}
.btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(22,163,74,.28); }

/* Voucher badge */
.voucher-badge-display {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #dcfce7;
    border: 1.5px solid #86efac;
    border-radius: 10px;
    padding: 8px 13px;
    font-size: 13px;
    color: #15803d;
    font-weight: 600;
    margin-bottom: 10px;
}

/* Steps indicator */
.checkout-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    margin-bottom: 26px;
}
.step {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
}
.step.active { color: #1d4ed8; }
.step.done   { color: #16a34a; }
.step-dot {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    display: grid; place-items: center;
    font-size: 13px; font-weight: 700;
    flex-shrink: 0;
}
.step.active .step-dot { background: #2563eb; color: #fff; }
.step.done   .step-dot { background: #16a34a; color: #fff; }
.step-line { width: 50px; height: 2px; background: #e2e8f0; margin: 0 4px; }
.step-line.done { background: #16a34a; }

@media (max-width: 900px) {
    .checkout-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="checkout-page">

    <!-- Hero -->
    <div class="checkout-hero">
        <div class="checkout-hero-icon">📦</div>
        <div>
            <h2>Xác nhận đặt hàng</h2>
            <p>Vui lòng kiểm tra thông tin và hoàn tất đơn hàng của bạn</p>
        </div>
    </div>

    <?php if ($isGuest ?? false): ?>
    <!-- Guest checkout banner -->
    <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #f59e0b;border-radius:14px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
        <span style="font-size:28px;">👤</span>
        <div>
            <strong style="color:#92400e;font-size:14px;">Bạn đang đặt hàng không cần đăng nhập</strong>
            <p style="margin:4px 0 0;font-size:13px;color:#78350f;">Điền thông tin bên dưới để hoàn tất đơn hàng.
            <a href="<?php echo BASE_URL; ?>dangnhap.php?redirect=thanhtoan.php" style="color:#2563eb;font-weight:700;">Đăng nhập ngay</a>
            để nhận điểm tích lũy và theo dõi đơn hàng dễ dàng hơn.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Steps -->
    <div class="checkout-steps">
        <div class="step done">
            <div class="step-dot">✓</div>
            <span>Giỏ hàng</span>
        </div>
        <div class="step-line done"></div>
        <div class="step active">
            <div class="step-dot">2</div>
            <span>Thông tin giao hàng</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-dot">3</div>
            <span>Hoàn tất</span>
        </div>
    </div>

    <form action="thanhtoan.php?ids=<?php echo htmlspecialchars($selectedIdsRaw); ?>" method="POST" id="checkoutForm">
        <?php echo CsrfHelper::field(); ?>
        <input type="hidden" name="voucher_code" value="<?php echo htmlspecialchars($voucherCode); ?>">
        <input type="hidden" name="ship_fee"     value="<?php echo $shipFee; ?>">

        <div class="checkout-grid">

            <!-- ===== LEFT: FORM ===== -->
            <div style="display:flex;flex-direction:column;gap:20px;">

                <!-- Thông tin người nhận -->
                <div class="co-card">
                    <div class="co-card-header">
                        <div class="icon" style="background:#eff6ff;color:#2563eb;">👤</div>
                        Thông tin người nhận
                    </div>
                    <div class="co-card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Họ và tên <span class="req">*</span></label>
                                <input type="text" name="fullname" class="form-control"
                                       placeholder="Nguyễn Văn A"
                                       value="<?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? ''); ?>"
                                       required>
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại <span class="req">*</span></label>
                                <input type="tel" name="phone" class="form-control"
                                       placeholder="0912 345 678"
                                       required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="example@email.com"
                                   value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Địa chỉ giao hàng -->
                <div class="co-card">
                    <div class="co-card-header">
                        <div class="icon" style="background:#fef3c7;color:#d97706;">📍</div>
                        Địa chỉ giao hàng
                    </div>
                    <div class="co-card-body">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Địa chỉ nhận hàng <span class="req">*</span></label>
                            <textarea name="address" class="form-control"
                                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                                      required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="co-card">
                    <div class="co-card-header">
                        <div class="icon" style="background:#fdf4ff;color:#9333ea;">💳</div>
                        Phương thức thanh toán
                    </div>
                    <div class="co-card-body">
                        <div class="pay-options">
                            <label class="pay-option">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <span class="pay-option-icon">💵</span>
                                <span class="pay-option-label">
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <span>Trả tiền mặt khi shipper giao hàng đến tay bạn</span>
                                </span>
                            </label>
                            <label class="pay-option">
                                <input type="radio" name="payment_method" value="bank">
                                <span class="pay-option-icon">🏦</span>
                                <span class="pay-option-label">
                                    <strong>Chuyển khoản ngân hàng</strong>
                                    <span>Chúng tôi sẽ gửi thông tin tài khoản qua email</span>
                                </span>
                            </label>
                            <label class="pay-option">
                                <input type="radio" name="payment_method" value="vnpay">
                                <span class="pay-option-icon">⚡</span>
                                <span class="pay-option-label">
                                    <strong style="color:#1a56db;">VNPay — Thanh toán trực tuyến</strong>
                                    <span>ATM nội địa, Visa/Mastercard, QR Code, Ví điện tử</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ===== RIGHT: ORDER SUMMARY ===== -->
            <div style="display:flex;flex-direction:column;gap:20px;">

                <!-- Sản phẩm -->
                <div class="co-card">
                    <div class="co-card-header">
                        <div class="icon" style="background:#f0fdf4;color:#16a34a;">🛒</div>
                        Sản phẩm đặt hàng
                        <span style="margin-left:auto;font-size:12.5px;color:#64748b;font-weight:500;"><?php echo count($cartItems); ?> sản phẩm</span>
                    </div>
                    <div class="co-card-body" style="padding:16px 20px;">
                        <?php
                        $defaultImg = 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="52" height="52"><rect width="52" height="52" fill="%23f0f5ff"/><text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" fill="%2394a3b8" font-size="20">📦</text></svg>';
                        foreach ($cartItems as $item):
                            if (strpos($item['image'], 'data:') === 0) {
                                $src = $item['image'];
                            } elseif (!empty($item['image'])) {
                                $src = BASE_URL . 'public/img/products/' . $item['image'];
                            } else {
                                $src = $defaultImg;
                            }
                        ?>
                        <div class="order-item">
                            <div class="order-item-img">
                                <img src="<?php echo $src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" loading="lazy">
                            </div>
                            <div class="order-item-info">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="order-item-qty">Số lượng: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="order-item-price"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Chi tiết thanh toán -->
                <div class="co-card">
                    <div class="co-card-header">
                        <div class="icon" style="background:#fff7ed;color:#ea580c;">📋</div>
                        Chi tiết thanh toán
                    </div>
                    <div class="co-card-body" style="padding:16px 20px;">

                        <?php if ($voucherCode): ?>
                        <div class="voucher-badge-display">
                            <i class="fa fa-tag"></i>
                            Voucher <strong><?php echo htmlspecialchars($voucherCode); ?></strong> đã được áp dụng
                        </div>
                        <?php endif; ?>

                        <div class="price-row">
                            <span>Tổng tiền hàng</span>
                            <span class="val"><?php echo number_format($cartTotal, 0, ',', '.'); ?>₫</span>
                        </div>
                        <div class="price-row ship">
                            <span>Phí vận chuyển</span>
                            <span class="val"><?php echo number_format($shipFee, 0, ',', '.'); ?>₫</span>
                        </div>
                        <?php if ($discountAmount > 0): ?>
                        <div class="price-row discount">
                            <span>Giảm giá voucher</span>
                            <span class="val">-<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫</span>
                        </div>
                        <?php endif; ?>

                        <hr class="price-divider">

                        <div class="price-total">
                            <span class="label">Tổng thanh toán</span>
                            <span class="amount"><?php echo number_format($finalTotal, 0, ',', '.'); ?>₫</span>
                        </div>

                        <div class="checkout-actions">
                            <a href="giohang.php" class="btn-back">
                                <i class="fa fa-arrow-left"></i> Giỏ hàng
                            </a>
                            <button type="submit" class="btn-confirm">
                                <i class="fa fa-check-circle"></i> Xác nhận đặt hàng
                            </button>
                        </div>

                        <p style="margin:12px 0 0;font-size:12px;color:#94a3b8;text-align:center;line-height:1.6;">
                            🔒 Thông tin của bạn được bảo mật tuyệt đối.<br>
                            Chúng tôi cam kết giao hàng nhanh và đảm bảo chất lượng.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>