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
                <div class="co-card" id="addressSection">
                    <div class="co-card-header">
                        <div class="icon" style="background:#fef3c7;color:#d97706;">📍</div>
                        Địa chỉ giao hàng
                        <?php if (!empty($savedAddresses)): ?>
                            <span style="margin-left:auto;font-size:12px;color:#64748b;">
                                <i class="fa fa-bookmark"></i> <?php echo count($savedAddresses); ?> địa chỉ đã lưu
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="co-card-body">
                        <?php if (!empty($savedAddresses)): ?>
                        <!-- ── Danh sách địa chỉ đã lưu ── -->
                        <div style="margin-bottom:16px;">
                            <label style="font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;display:block;">
                                <i class="fa fa-bookmark"></i> Chọn địa chỉ có sẵn
                            </label>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                <?php foreach ($savedAddresses as $addr): 
                                    $isDefault = !empty($addr['is_default']);
                                    $fullAddr = trim($addr['address_detail'] . ', ' . $addr['ward'] . ', ' . $addr['district'] . ', ' . $addr['province'], ', ');
                                ?>
                                <label class="address-option <?php echo $isDefault ? 'selected' : ''; ?>"
                                       onclick="selectSavedAddress(this, <?php echo $addr['id']; ?>)"
                                       data-id="<?php echo $addr['id']; ?>"
                                       data-name="<?php echo htmlspecialchars($addr['full_name']); ?>"
                                       data-phone="<?php echo htmlspecialchars($addr['phone']); ?>"
                                       data-address="<?php echo htmlspecialchars($fullAddr); ?>">
                                    <input type="radio" name="saved_address_id" value="<?php echo $addr['id']; ?>"
                                           <?php echo $isDefault ? 'checked' : ''; ?>
                                           style="display:none;"
                                           onchange="this.closest('label').classList.toggle('selected', this.checked)">
                                    <div class="address-radio">
                                        <div class="address-radio-dot <?php echo $isDefault ? 'active' : ''; ?>"></div>
                                    </div>
                                    <div class="address-info">
                                        <div class="address-name">
                                            <?php echo htmlspecialchars($addr['full_name']); ?>
                                            <?php if ($isDefault): ?>
                                                <span class="address-default-badge">Mặc định</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="address-phone"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($addr['phone']); ?></div>
                                        <div class="address-detail"><i class="fa fa-map-pin"></i> <?php echo htmlspecialchars($fullAddr); ?></div>
                                    </div>
                                    <div class="address-check">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <div style="margin-top:10px;">
                                <button type="button" class="btn-toggle-new-address" onclick="toggleNewAddressForm()">
                                    <i class="fa fa-plus-circle"></i> Nhập địa chỉ mới
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ── Form nhập địa chỉ mới (hiển thị khi không có địa chỉ lưu hoặc click 'Nhập mới') ── -->
                        <div id="newAddressForm" style="<?php echo empty($savedAddresses) ? '' : 'display:none;'; ?>">
                            <?php if (!empty($savedAddresses)): ?>
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                                <button type="button" class="btn-back-address" onclick="showAddressList()">
                                    <i class="fa fa-arrow-left"></i> Chọn địa chỉ đã lưu
                                </button>
                                <span style="font-size:13px;color:#64748b;">hoặc nhập địa chỉ mới</span>
                            </div>
                            <?php endif; ?>

                            <!-- Cascading address dropdowns -->
                            <div class="form-row" style="margin-bottom:0;">
                                <div class="form-group">
                                    <label>Tỉnh/Thành phố <span class="req">*</span></label>
                                    <select name="province_id" id="coProvince" class="form-control co-select" required onchange="coLoadDistricts(this.value)">
                                        <option value="">-- Chọn tỉnh/thành --</option>
                                    </select>
                                    <input type="hidden" name="province" id="coProvinceName" value="">
                                </div>
                                <div class="form-group">
                                    <label>Quận/Huyện <span class="req">*</span></label>
                                    <select name="district_id" id="coDistrict" class="form-control co-select" required onchange="coLoadWards(this.value)" disabled>
                                        <option value="">-- Chọn quận/huyện --</option>
                                    </select>
                                    <input type="hidden" name="district" id="coDistrictName" value="">
                                </div>
                            </div>
                            <div class="form-row" style="margin-bottom:0;">
                                <div class="form-group">
                                    <label>Phường/Xã <span class="req">*</span></label>
                                    <select name="ward_id" id="coWard" class="form-control co-select" required disabled>
                                        <option value="">-- Chọn phường/xã --</option>
                                    </select>
                                    <input type="hidden" name="ward" id="coWardName" value="">
                                </div>
                                <div class="form-group">
                                    <label>Số nhà, tên đường <span class="req">*</span></label>
                                    <input type="text" name="address_detail" class="form-control" placeholder="Số 123, đường ABC..." required>
                                </div>
                            </div>
                            <input type="hidden" name="address" id="coFullAddress" value="">

                            <p style="font-size:12px;color:#64748b;margin:4px 0 0;">
                                <i class="fa fa-info-circle"></i>
                                Tên người nhận và số điện thoại sẽ lấy từ thông tin phía trên. 
                                Bạn có thể thay đổi ở mục <strong>Thông tin người nhận</strong>.
                            </p>

                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#475569;cursor:pointer;margin-top:4px;">
                                <input type="checkbox" name="save_address" value="1" style="accent-color:#2563eb;width:16px;height:16px;" <?php echo empty($savedAddresses) ? 'checked' : ''; ?>>
                                <i class="fa fa-save"></i> Lưu địa chỉ này để dùng sau
                            </label>
                        </div>
                    </div>
                </div>

                <style>
                /* ── Address Option Styles ── */
                .address-option {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    padding: 14px 16px;
                    border: 1.5px solid #e2e8f0;
                    border-radius: 14px;
                    cursor: pointer;
                    transition: all .2s ease;
                    background: #fff;
                    position: relative;
                }
                .address-option:hover {
                    border-color: #93c5fd;
                    background: #f8faff;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(37,99,235,0.08);
                }
                .address-option.selected {
                    border-color: #2563eb;
                    background: #eff6ff;
                    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
                }
                .address-radio {
                    flex-shrink: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .address-radio-dot {
                    width: 22px;
                    height: 22px;
                    border-radius: 50%;
                    border: 2.5px solid #cbd5e1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all .2s;
                }
                .address-radio-dot::after {
                    content: '';
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background: #2563eb;
                    display: none;
                    transition: all .2s;
                }
                .address-option.selected .address-radio-dot {
                    border-color: #2563eb;
                }
                .address-option.selected .address-radio-dot::after {
                    display: block;
                }
                .address-info {
                    flex: 1;
                    min-width: 0;
                }
                .address-name {
                    font-size: 14px;
                    font-weight: 700;
                    color: #1e293b;
                    margin-bottom: 4px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .address-default-badge {
                    display: inline-block;
                    font-size: 10px;
                    font-weight: 700;
                    background: #22c55e;
                    color: #fff;
                    padding: 2px 8px;
                    border-radius: 20px;
                    line-height: 1.4;
                }
                .address-phone {
                    font-size: 13px;
                    color: #475569;
                    margin-bottom: 2px;
                }
                .address-phone i, .address-detail i {
                    color: #94a3b8;
                    width: 16px;
                    margin-right: 4px;
                    font-size: 12px;
                }
                .address-detail {
                    font-size: 13px;
                    color: #64748b;
                    line-height: 1.5;
                }
                .address-check {
                    flex-shrink: 0;
                    color: #94a3b8;
                    font-size: 20px;
                    transition: all .2s;
                }
                .address-option.selected .address-check {
                    color: #2563eb;
                }
                .btn-toggle-new-address {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 10px 16px;
                    background: #f8fafc;
                    border: 1.5px dashed #cbd5e1;
                    border-radius: 10px;
                    color: #64748b;
                    font-size: 13px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all .2s;
                    font-family: inherit;
                }
                .btn-toggle-new-address:hover {
                    border-color: #93c5fd;
                    background: #eff6ff;
                    color: #2563eb;
                }
                .btn-back-address {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 8px 14px;
                    background: #f1f5f9;
                    border: none;
                    border-radius: 10px;
                    color: #475569;
                    font-size: 13px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all .2s;
                    font-family: inherit;
                }
                .btn-back-address:hover {
                    background: #e2e8f0;
                    color: #1e293b;
                }
                </style>

                <style>
                /* Select styling for checkout */
                .co-select {
                    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 12px center !important;
                    appearance: none !important;
                    -webkit-appearance: none !important;
                    -moz-appearance: none !important;
                    padding-right: 36px !important;
                    cursor: pointer !important;
                }
                .co-select:disabled {
                    opacity: .55;
                    cursor: not-allowed;
                    background-color: #f8fafc !important;
                }
                </style>

                <script>
                // ── Cascading address dropdowns for Checkout ──
                // Đảm bảo BASE_URL luôn có giá trị (guest không có BASE_URL JS từ header)
                var CO_BASE_URL = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '<?php echo BASE_URL; ?>';
                var CO_API = CO_BASE_URL + 'api_get_addresses.php';

                document.addEventListener('DOMContentLoaded', function() {
                    coLoadProvinces();
                });

                function coLoadProvinces() {
                    var sel = document.getElementById('coProvince');
                    sel.innerHTML = '<option value="">-- Đang tải... --</option>';
                    sel.disabled = true;
                    fetch(CO_API + '?level=provinces')
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            sel.innerHTML = '<option value="">-- Chọn tỉnh/thành --</option>';
                            data.forEach(function(p) {
                                var opt = document.createElement('option');
                                opt.value = p.code;
                                opt.setAttribute('data-name', p.name);
                                opt.textContent = p.name;
                                sel.appendChild(opt);
                            });
                            sel.disabled = false;
                        })
                        .catch(function() {
                            sel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
                            sel.disabled = false;
                        });
                }

                function coLoadDistricts(provinceId) {
                    var sel = document.getElementById('coDistrict');
                    var wardSel = document.getElementById('coWard');
                    wardSel.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
                    wardSel.disabled = true;
                    document.getElementById('coWardName').value = '';
                    if (!provinceId) {
                        sel.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
                        sel.disabled = true;
                        document.getElementById('coDistrictName').value = '';
                        coUpdateFullAddress();
                        // Set province name from selected option
                        var provSel = document.getElementById('coProvince');
                        document.getElementById('coProvinceName').value = provSel.options[provSel.selectedIndex]?.getAttribute('data-name') || '';
                        return;
                    }
                    sel.innerHTML = '<option value="">-- Đang tải... --</option>';
                    sel.disabled = true;
                    // Save province name
                    var provSel = document.getElementById('coProvince');
                    document.getElementById('coProvinceName').value = provSel.options[provSel.selectedIndex]?.getAttribute('data-name') || '';
                    fetch(CO_API + '?level=districts&province_id=' + encodeURIComponent(provinceId))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            sel.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
                            data.forEach(function(d) {
                                var opt = document.createElement('option');
                                opt.value = d.code;
                                opt.setAttribute('data-name', d.name);
                                opt.textContent = d.name;
                                sel.appendChild(opt);
                            });
                            sel.disabled = false;
                        })
                        .catch(function() {
                            sel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
                            sel.disabled = false;
                        });
                    coUpdateFullAddress();
                }

                function coLoadWards(districtId) {
                    var sel = document.getElementById('coWard');
                    if (!districtId) {
                        sel.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
                        sel.disabled = true;
                        document.getElementById('coWardName').value = '';
                        // Save district name
                        var distSel = document.getElementById('coDistrict');
                        document.getElementById('coDistrictName').value = distSel.options[distSel.selectedIndex]?.getAttribute('data-name') || '';
                        coUpdateFullAddress();
                        return;
                    }
                    sel.innerHTML = '<option value="">-- Đang tải... --</option>';
                    sel.disabled = true;
                    // Save district name
                    var distSel = document.getElementById('coDistrict');
                    document.getElementById('coDistrictName').value = distSel.options[distSel.selectedIndex]?.getAttribute('data-name') || '';
                    fetch(CO_API + '?level=wards&district_id=' + encodeURIComponent(districtId))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            sel.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
                            data.forEach(function(w) {
                                var opt = document.createElement('option');
                                opt.value = w.code;
                                opt.setAttribute('data-name', w.name);
                                opt.textContent = w.name;
                                sel.appendChild(opt);
                            });
                            sel.disabled = false;
                        })
                        .catch(function() {
                            sel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
                            sel.disabled = false;
                        });
                    coUpdateFullAddress();
                }

                // Update ward name on ward select change
                document.addEventListener('change', function(e) {
                    if (e.target.id === 'coWard') {
                        var sel = e.target;
                        document.getElementById('coWardName').value = sel.options[sel.selectedIndex]?.getAttribute('data-name') || '';
                        coUpdateFullAddress();
                    }
                });

                function coUpdateFullAddress() {
                    var addrDetail = document.querySelector('input[name="address_detail"]')?.value || '';
                    var ward = document.getElementById('coWardName').value;
                    var district = document.getElementById('coDistrictName').value;
                    var province = document.getElementById('coProvinceName').value;
                    var parts = [addrDetail];
                    if (ward) parts.push(ward);
                    if (district) parts.push(district);
                    if (province) parts.push(province);
                    document.getElementById('coFullAddress').value = parts.join(', ');
                }

                // Listen for address_detail changes
                document.addEventListener('input', function(e) {
                    if (e.target.name === 'address_detail') {
                        coUpdateFullAddress();
                    }
                });

                document.addEventListener('DOMNodeInserted', function() {
                    var addrInput = document.querySelector('input[name="address_detail"]');
                    if (addrInput && !addrInput.dataset.listener) {
                        addrInput.dataset.listener = '1';
                        addrInput.addEventListener('input', coUpdateFullAddress);
                    }
                });

                // ── Toggle required on new address fields ──
                function setNewAddressRequired(required) {
                    var fields = ['coProvince', 'coDistrict', 'coWard'];
                    fields.forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el) {
                            if (required) el.setAttribute('required', '');
                            else el.removeAttribute('required');
                        }
                    });
                    var detail = document.querySelector('input[name="address_detail"]');
                    if (detail) {
                        if (required) detail.setAttribute('required', '');
                        else detail.removeAttribute('required');
                    }
                }

                // ── Address Selection Logic ──
                function selectSavedAddress(el, id) {
                    // Deselect all
                    document.querySelectorAll('.address-option').forEach(function(opt) {
                        opt.classList.remove('selected');
                        opt.querySelector('input[type=radio]').checked = false;
                        opt.querySelector('.address-radio-dot').classList.remove('active');
                    });
                    // Select this
                    el.classList.add('selected');
                    el.querySelector('input[type=radio]').checked = true;
                    el.querySelector('.address-radio-dot').classList.add('active');

                    // Auto-fill fullname & phone
                    var fullnameInput = document.querySelector('input[name="fullname"]');
                    var phoneInput    = document.querySelector('input[name="phone"]');
                    if (fullnameInput && el.dataset.name) {
                        fullnameInput.value = el.dataset.name;
                        fullnameInput.style.borderColor = '#22c55e';
                        setTimeout(function() { fullnameInput.style.borderColor = ''; }, 1500);
                    }
                    if (phoneInput && el.dataset.phone) {
                        phoneInput.value = el.dataset.phone;
                        phoneInput.style.borderColor = '#22c55e';
                        setTimeout(function() { phoneInput.style.borderColor = ''; }, 1500);
                    }

                    // Hide new address form & remove required from its fields
                    var newForm = document.getElementById('newAddressForm');
                    if (newForm) newForm.style.display = 'none';
                    setNewAddressRequired(false);
                }

                function toggleNewAddressForm() {
                    var newForm = document.getElementById('newAddressForm');
                    if (newForm) {
                        newForm.style.display = 'block';
                        // Deselect all saved addresses
                        document.querySelectorAll('.address-option').forEach(function(opt) {
                            opt.classList.remove('selected');
                            var radio = opt.querySelector('input[type=radio]');
                            if (radio) radio.checked = false;
                            opt.querySelector('.address-radio-dot').classList.remove('active');
                        });
                        // Add required back to new address fields
                        setNewAddressRequired(true);
                        newForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                function showAddressList() {
                    var newForm = document.getElementById('newAddressForm');
                    if (newForm) newForm.style.display = 'none';
                    setNewAddressRequired(false);
                }

                // Auto-select default address on page load
                document.addEventListener('DOMContentLoaded', function() {
                    var defaultAddr = document.querySelector('.address-option.selected');
                    if (defaultAddr) {
                        selectSavedAddress(defaultAddr, defaultAddr.dataset.id);
                    } else {
                        // Không có địa chỉ lưu → đang dùng form mới → giữ required
                        setNewAddressRequired(true);
                    }
                });
                </script>

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