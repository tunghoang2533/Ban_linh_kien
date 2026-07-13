<style>
    .cart-page {
        margin: 50px auto 80px;
        max-width: 1160px;
        font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #333;
    }
    .cart-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        padding: 24px 28px;
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(40,138,214,0.95), rgba(35,124,206,0.95));
        color: #fff;
        box-shadow: 0 20px 50px rgba(15,64,122,0.15);
        margin-bottom: 24px;
    }
    .cart-hero h2 {
        margin: 0;
        font-size: 30px;
        line-height: 1.1;
        letter-spacing: 0.02em;
    }
    .cart-hero span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        opacity: 0.9;
    }
    .cart-grid {
        display: grid;
        grid-template-columns: 1.5fr 0.8fr;
        gap: 24px;
    }
    .cart-list {
        background: #fff;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(24,81,153,0.08);
    }
    .cart-table {
        width: 100%;
        border-collapse: collapse;
    }
    .cart-table thead {
        background: #f4f8ff;
    }
    .cart-table th,
    .cart-table td {
        padding: 18px 16px;
        text-align: left;
        vertical-align: middle;
    }
    .cart-table th {
        font-weight: 700;
        color: #2a4f7d;
        font-size: 14px;
        letter-spacing: 0.03em;
    }
    .cart-table tbody tr {
        border-bottom: 1px solid #edf2f8;
    }
    .cart-table tbody tr:last-child {
        border-bottom: none;
    }
    .product-cell {
        display: flex;
        gap: 16px;
        align-items: center;
    }
    .product-image {
        width: 90px;
        height: 90px;
        border-radius: 18px;
        overflow: hidden;
        flex-shrink: 0;
        background: #f5f8ff;
        border: 1px solid #eef3fb;
        display: grid;
        place-items: center;
    }
    .product-image img {
        max-width: 100%;
        max-height: 100%;
    }
    .product-name {
        font-weight: 700;
        font-size: 15px;
        color: #1e364f;
        margin-bottom: 6px;
    }
    .product-meta {
        color: #6f7d94;
        font-size: 13px;
    }
    .price-cell,
    .qty-cell,
    .subtotal-cell {
        font-weight: 700;
        color: #1f4d8a;
        font-size: 15px;
    }
    .qty-cell {
        text-align: center;
    }
    /* ===== QTY STEPPER ===== */
    .qty-stepper {
        display: inline-flex;
        align-items: center;
        gap: 0;
        border: 1.5px solid #dce6f5;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }
    .qty-btn {
        width: 32px;
        height: 34px;
        border: none;
        background: #f0f5ff;
        color: #1f4d8a;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, color .15s;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        user-select: none;
    }
    .qty-btn:hover:not(:disabled) { background: #dbeafe; color: #1d40af; }
    .qty-btn:disabled { opacity: .4; cursor: not-allowed; }
    .qty-btn:active:not(:disabled) { background: #bfdbfe; }
    .qty-input {
        width: 38px;
        height: 34px;
        border: none;
        border-left: 1.5px solid #dce6f5;
        border-right: 1.5px solid #dce6f5;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        color: #1e364f;
        background: #fff;
        outline: none;
        -moz-appearance: textfield;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .qty-loading { opacity: .5; pointer-events: none; }
    .remove-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #ffe8ea;
        color: #d43d4d;
        border-radius: 12px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        transition: background .2s ease;
    }
    .remove-button:hover {
        background: #ffd5d9;
    }
    .cart-summary {
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 20px 40px rgba(24,81,153,0.08);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .summary-title {
        font-size: 18px;
        font-weight: 700;
        color: #1c344f;
        margin: 0;
    }
    .summary-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #5f6d86;
        font-size: 15px;
    }
    .summary-line.total {
        color: #1f4d8a;
        font-size: 22px;
        font-weight: 800;
    }
    .summary-line.total span {
        color: #1f4d8a;
    }
    .summary-note {
        font-size: 14px;
        color: #64748b;
        line-height: 1.7;
    }
    .action-buttons {
        display: grid;
        gap: 14px;
    }
    .btn-secondary,
    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 22px;
        border-radius: 14px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .btn-secondary {
        background: #f1f5f9;
        color: #3b4f6e;
    }
    .btn-secondary:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(33,41,54,0.08);
    }
    .btn-primary {
        background: #28a745;
        color: #ffffff;
        box-shadow: 0 20px 30px rgba(40,167,69,0.18);
    }
    .btn-primary:hover {
        transform: translateY(-1px);
    }
    .cart-empty {
        padding: 80px 40px;
        text-align: center;
        border: 1px dashed #cbd5e1;
        border-radius: 20px;
        background: #f8fbff;
        color: #475569;
    }
    .cart-empty h3 {
        margin: 0 0 12px;
        font-size: 22px;
        color: #1f3a5d;
    }
    .cart-empty a {
        display: inline-flex;
        margin-top: 18px;
        padding: 12px 26px;
        background: #288ad6;
        color: #fff;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
    }
    @media (max-width: 980px) {
        .cart-grid {
            grid-template-columns: 1fr;
        }
        .cart-summary {
            order: -1;
        }
    }

    /* ===== VOUCHER SECTION ===== */
    .voucher-section {
        background: #f0f7ff;
        border: 1.5px dashed #93c5fd;
        border-radius: 16px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .voucher-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        color: #1d4ed8;
    }
    .voucher-section-title i { font-size: 16px; }
    .voucher-input-row {
        display: flex;
        gap: 8px;
    }
    .voucher-input {
        flex: 1;
        padding: 10px 14px;
        border: 1.5px solid #bfdbfe;
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        background: #fff;
        color: #1e3a5f;
        transition: border-color .2s;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .voucher-input:focus { border-color: #3b82f6; }
    .voucher-input::placeholder { text-transform: none; letter-spacing: 0; color: #94a3b8; }
    .btn-apply-voucher {
        padding: 10px 16px;
        background: #1d4ed8;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s, transform .15s;
        white-space: nowrap;
    }
    .btn-apply-voucher:hover { background: #1e40af; transform: translateY(-1px); }
    .btn-pick-voucher {
        background: none;
        border: none;
        color: #2563eb;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color .2s;
    }
    .btn-pick-voucher:hover { color: #1d4ed8; }
    .voucher-applied {
        display: none;
        align-items: center;
        justify-content: space-between;
        background: #dcfce7;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        color: #166534;
        font-weight: 600;
    }
    .voucher-applied.show { display: flex; }
    .btn-remove-voucher {
        background: none;
        border: none;
        color: #dc2626;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        padding: 0 2px;
        font-weight: 700;
    }
    .summary-line.discount { color: #16a34a; }
    .summary-line.discount span:last-child { font-weight: 700; }

    /* ===== CHI TIẾT THANH TOÁN ===== */
    .payment-detail-box {
        background: #f8faff;
        border: 1px solid #e2eaf6;
        border-radius: 14px;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .payment-detail-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #1c344f;
        padding-bottom: 8px;
        border-bottom: 1.5px dashed #cbd5e1;
        margin-bottom: 2px;
        letter-spacing: 0.01em;
    }
    .payment-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13.5px;
        color: #5f6d86;
    }
    .payment-detail-row .val { font-weight: 600; color: #1e364f; }
    .payment-detail-row.freeship-discount .val { color: #7c3aed; font-weight: 700; }
    .payment-detail-row.voucher-discount  .val { color: #16a34a; font-weight: 700; }
    .payment-detail-row.row-total {
        border-top: 1.5px dashed #cbd5e1;
        padding-top: 10px;
        margin-top: 2px;
        font-weight: 700;
        font-size: 14.5px;
        color: #1f4d8a;
    }
    .payment-detail-row.row-total .val { color: #e10c00; font-size: 15px; }

    /* ===== VOUCHER MODAL ===== */
    .voucher-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,30,60,0.55);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(3px);
    }
    .voucher-modal-overlay.open { display: flex; }
    .voucher-modal {
        background: #fff;
        border-radius: 22px;
        width: 100%;
        max-width: 480px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 30px 80px rgba(15,30,80,0.22);
        overflow: hidden;
        animation: modalIn .25s cubic-bezier(.4,0,.2,1);
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(30px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .voucher-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .voucher-modal-header h4 {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        color: #1e3a5f;
    }
    .modal-close {
        background: #f1f5f9;
        border: none;
        border-radius: 50%;
        width: 32px; height: 32px;
        font-size: 18px;
        cursor: pointer;
        color: #64748b;
        display: grid;
        place-items: center;
        transition: background .2s;
    }
    .modal-close:hover { background: #e2e8f0; }
    .voucher-modal-search {
        display: flex;
        gap: 8px;
        padding: 14px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .voucher-modal-search input {
        flex: 1;
        padding: 9px 14px;
        border: 1.5px solid #cbd5e1;
        border-radius: 9px;
        font-size: 13.5px;
        outline: none;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .voucher-modal-search input::placeholder { text-transform: none; letter-spacing: 0; }
    .voucher-modal-search input:focus { border-color: #3b82f6; }
    .btn-modal-apply {
        padding: 9px 16px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s;
    }
    .btn-modal-apply:hover { background: #1d4ed8; }
    .voucher-list { overflow-y: auto; padding: 14px 20px; display: flex; flex-direction: column; gap: 12px; }
    .voucher-card {
        display: flex;
        gap: 14px;
        align-items: center;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        cursor: pointer;
        transition: border-color .2s, background .2s, transform .15s;
        position: relative;
    }
    .voucher-card:hover { border-color: #93c5fd; background: #f0f7ff; transform: translateY(-1px); }
    .voucher-card.selected { border-color: #2563eb; background: #eff6ff; }
    .voucher-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .voucher-info { flex: 1; min-width: 0; }
    .voucher-name { font-weight: 700; font-size: 14px; color: #1e3a5f; margin-bottom: 4px; }
    .voucher-desc { font-size: 12.5px; color: #64748b; margin-bottom: 3px; }
    .voucher-expire { font-size: 11.5px; color: #94a3b8; }
    .voucher-badge {
        position: absolute;
        top: 10px; right: 12px;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
    }
    .radio-dot {
        width: 20px; height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        transition: border-color .2s;
    }
    .radio-dot::after {
        content: '';
        width: 10px; height: 10px;
        border-radius: 50%;
        background: #2563eb;
        display: none;
    }
    .voucher-card.selected .radio-dot { border-color: #2563eb; }
    .voucher-card.selected .radio-dot::after { display: block; }
    .voucher-modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .btn-modal-cancel {
        padding: 10px 20px;
        background: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: background .2s;
    }
    .btn-modal-cancel:hover { background: #e2e8f0; }
    .btn-modal-confirm {
        padding: 10px 24px;
        background: #16a34a;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: background .2s, transform .15s;
    }
    .btn-modal-confirm:hover { background: #15803d; transform: translateY(-1px); }

    /* Shopee-style section headers inside modal */
    .voucher-section-header {
        padding: 6px 0 10px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .voucher-section-header .section-count {
        background: #e2e8f0;
        border-radius: 999px;
        font-size: 11px;
        padding: 1px 7px;
        color: #64748b;
        font-weight: 700;
    }
    .voucher-group { margin-bottom: 20px; }
    .voucher-group:last-child { margin-bottom: 0; }
    .no-voucher-in-group {
        text-align: center;
        color: #cbd5e1;
        font-size: 13px;
        padding: 14px 0 4px;
        font-style: italic;
    }
</style>

<?php if (!empty($_SESSION['cart_error'])): ?>
<div id="stockErrorToast" style="
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 99999;
    background: #fff1f2;
    border: 1.5px solid #fca5a5;
    border-left: 5px solid #ef4444;
    border-radius: 14px;
    padding: 16px 20px;
    max-width: 380px;
    box-shadow: 0 10px 40px rgba(220,38,38,0.18);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    animation: slideInRight .35s cubic-bezier(.4,0,.2,1);
">
    <span style="font-size:22px;line-height:1;">⚠️</span>
    <div style="flex:1;">
        <div style="font-weight:700;color:#b91c1c;font-size:14px;margin-bottom:4px;">Không thể thêm sản phẩm</div>
        <div style="color:#7f1d1d;font-size:13.5px;line-height:1.5;"><?php echo $_SESSION['cart_error']; ?></div>
    </div>
    <button onclick="document.getElementById('stockErrorToast').remove()" style="background:none;border:none;font-size:18px;color:#b91c1c;cursor:pointer;padding:0;line-height:1;">×</button>
</div>
<style>
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(60px); }
    to   { opacity: 1; transform: translateX(0); }
}
</style>
<script>
// Tự động ẩn sau 5 giây
setTimeout(() => {
    const t = document.getElementById('stockErrorToast');
    if (t) { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; setTimeout(() => t.remove(), 400); }
}, 5000);
</script>
<?php unset($_SESSION['cart_error']); ?>
<?php endif; ?>

<div class="cart-page">
    <div class="cart-hero">
        <div>
            <h2>Giỏ hàng của bạn</h2>
            <span><i class="fa fa-shopping-cart"></i> Có <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?> sản phẩm trong giỏ</span>
        </div>
        <div>
            <span>Chào mừng bạn đến với trải nghiệm mua sắm chuẩn chuyên nghiệp.</span>
        </div>
    </div>

    <div class="cart-grid">
        <div class="cart-list">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th style="width:44px;text-align:center;">
                            <input type="checkbox" id="selectAll" title="Chọn tất cả"
                                style="width:18px;height:18px;cursor:pointer;accent-color:#288ad6;">
                        </th>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    $buyNowId = $_SESSION['buy_now_id'] ?? null;
                    // Xóa flag sau khi đọc (để lần sau vào giỏ thường thì tick hết)
                    unset($_SESSION['buy_now_id']);
                    if(!empty($_SESSION['cart'])):
                        foreach($_SESSION['cart'] as $item):
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                    ?>
                        <tr class="cart-row" data-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>" data-qty="<?php echo $item['quantity']; ?>">
                            <td style="text-align:center;vertical-align:middle;">
                                <input type="checkbox" class="item-check"
                                    data-id="<?php echo $item['id']; ?>"
                                    data-subtotal="<?php echo $subtotal; ?>"
                                    <?php echo ($buyNowId === null || (string)$buyNowId === (string)$item['id']) ? 'checked' : ''; ?>
                                    style="width:18px;height:18px;cursor:pointer;accent-color:#288ad6;">
                            </td>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <?php
                                            $defaultImage = 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" fill="%23f3f3f3"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23999" font-size="24">No image</text></svg>';
                                            
                                            if (strpos($item['image'], 'data:') === 0) {
                                                $itemImageSrc = $item['image'];
                                            } elseif (!empty($item['image'])) {
                                                // Sử dụng BASE_URL trực tiếp nếu có tên ảnh
                                                $itemImageSrc = BASE_URL . 'public/img/products/' . $item['image'];
                                            } else {
                                                $itemImageSrc = $defaultImage;
                                            }
                                        ?>
                                        <img src="<?php echo $itemImageSrc; ?>" alt="<?php echo $item['name']; ?>" loading="lazy">
                                    </div>
                                    <div>
                                        <div class="product-name"><?php echo $item['name']; ?></div>
                                        <div class="product-meta">Mã SP: <?php echo $item['id']; ?> • Bảo hành 12 tháng</div>
                                    </div>
                                </div>
                            </td>
                            <td class="price-cell"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                            <td class="qty-cell">
                                <div class="qty-stepper" id="stepper-<?php echo $item['id']; ?>">
                                    <button class="qty-btn" onclick="changeQty('<?php echo $item['id']; ?>', -1)" title="Giảm">−</button>
                                    <input  class="qty-input"
                                            id="qty-<?php echo $item['id']; ?>"
                                            type="number" min="1" max="999"
                                            value="<?php echo $item['quantity']; ?>"
                                            onchange="setQty('<?php echo $item['id']; ?>', this.value)"
                                            onkeydown="if(event.key==='Enter') this.blur()">
                                    <button class="qty-btn" onclick="changeQty('<?php echo $item['id']; ?>', +1)" title="Tăng">+</button>
                                </div>
                            </td>
                            <td class="subtotal-cell"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</td>
                            <td>
                                <a href="giohang.php?action=remove&id=<?php echo $item['id']; ?>" 
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"
                                   class="remove-button">
                                   <i class="fa fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="cart-empty">
                                    <h3>Giỏ hàng đang trống</h3>
                                    <p>Hãy tiếp tục mua sắm để chọn những sản phẩm tốt nhất cho hệ thống máy tính của bạn.</p>
                                    <a href="index.php">Tiếp tục mua sắm</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <aside class="cart-summary">
            <h3 class="summary-title">Tóm tắt giỏ hàng</h3>

            <!-- Số sản phẩm -->
            <div class="summary-line">
                <span>Số sản phẩm được chọn</span>
                <span><span id="selectedCount">0</span> món</span>
            </div>

            <!-- VOUCHER SECTION -->
            <div class="voucher-section">
                <div class="voucher-section-title">
                    <i class="fa fa-ticket"></i>
                    <span>Voucher giảm giá</span>
                </div>
                <div class="voucher-input-row">
                    <input type="text" id="voucherCode" class="voucher-input" placeholder="Nhập mã voucher..." maxlength="30">
                    <button class="btn-apply-voucher" onclick="applyVoucherCode()">Áp dụng</button>
                </div>
                <button class="btn-pick-voucher" onclick="openVoucherModal()">
                    <i class="fa fa-tags"></i> Chọn voucher có sẵn
                </button>
                <div class="voucher-applied" id="voucherApplied">
                    <span><i class="fa fa-check-circle"></i> &nbsp;<span id="voucherAppliedLabel"></span></span>
                    <button class="btn-remove-voucher" onclick="removeVoucher()" title="Hủy voucher">×</button>
                </div>
            </div>

            <!-- CHI TIẾT THANH TOÁN -->
            <div class="payment-detail-box" id="paymentDetailBox">
                <div class="payment-detail-title">📋 Chi tiết thanh toán</div>
                <div class="payment-detail-row">
                    <span>Tổng tiền hàng</span>
                    <span class="val" id="detailSubtotal">0₫</span>
                </div>
                <div class="payment-detail-row">
                    <span>Tổng phí vận chuyển</span>
                    <span class="val" id="detailShipping">0₫</span>
                </div>
                <div class="payment-detail-row freeship-discount" id="detailFreeshipRow" style="display:none;">
                    <span>Giảm giá phí vận chuyển</span>
                    <span class="val" id="detailFreeship">-0₫</span>
                </div>
                <div class="payment-detail-row voucher-discount" id="detailVoucherRow" style="display:none;">
                    <span>Voucher giảm giá</span>
                    <span class="val" id="detailVoucher">-0₫</span>
                </div>
                <div class="payment-detail-row row-total">
                    <span>Tổng cộng</span>
                    <span class="val" id="detailTotal">0₫</span>
                </div>
            </div>

            <!-- Hidden elements for JS compatibility -->
            <span id="shippingFee" style="display:none;">50.000₫</span>
            <span id="shippingNote" style="display:none;"></span>
            <span id="subtotalAmount" style="display:none;"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
            <div id="discountLine" style="display:none;"><span id="discountAmount">-0₫</span></div>

            <!-- TỔNG THANH TOÁN NỔI BẬT -->
            <div class="summary-line total">
                <span>Tổng thanh toán</span>
                <span id="totalAmount"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
            </div>

            <p class="summary-note">Kiểm tra lại số lượng và sản phẩm trước khi thanh toán. Chúng tôi cam kết giao hàng nhanh và đảm bảo chất lượng.</p>
            <div class="action-buttons">
                <a href="index.php" class="btn-secondary">Tiếp tục mua sắm</a>
                <button type="button" class="btn-primary" id="checkoutBtn" onclick="goCheckout()">Thanh toán ngay <i class="fa fa-arrow-right" style="margin-left:8px;"></i></button>
            </div>
        </aside>
    </div>
</div>

<!-- VOUCHER MODAL -->
<div class="voucher-modal-overlay" id="voucherModalOverlay" onclick="handleOverlayClick(event)">
    <div class="voucher-modal">
        <div class="voucher-modal-header">
            <h4><i class="fa fa-ticket" style="color:#2563eb;margin-right:8px;"></i>Chọn Voucher</h4>
            <button class="modal-close" onclick="closeVoucherModal()">×</button>
        </div>
        <div class="voucher-modal-search">
            <input type="text" id="modalVoucherInput" placeholder="Mã Voucher" maxlength="30">
            <button class="btn-modal-apply" onclick="applyFromModalInput()">ÁP DỤNG</button>
        </div>
        <!-- 2 group sections like Shopee -->
        <div class="voucher-list" id="voucherList">
            <div class="voucher-group" id="groupFreeship">
                <div class="voucher-section-header">
                    <i class="fa fa-truck" style="color:#7c3aed;"></i>
                    Mã Miễn Phí Vận Chuyển
                    <span class="section-count" id="countFreeship">0</span>
                </div>
                <div id="listFreeship"></div>
            </div>
            <div class="voucher-group" id="groupDiscount">
                <div class="voucher-section-header">
                    <i class="fa fa-tag" style="color:#dc2626;"></i>
                    Mã Giảm Giá
                    <span class="section-count" id="countDiscount">0</span>
                </div>
                <div id="listDiscount"></div>
            </div>
        </div>
        <div class="voucher-modal-footer">
            <button class="btn-modal-cancel" onclick="closeVoucherModal()">TRỞ LẠI</button>
            <button class="btn-modal-confirm" onclick="confirmVoucher()">ĐỒNG Ý</button>
        </div>
    </div>
</div>

<script>
const CART_TOTAL_ALL = <?php echo $total; ?>;
let CART_TOTAL = CART_TOTAL_ALL;
const VOUCHER_API = 'giohang.php';

// ===== PHÍ VẬN CHUYỂN =====
const SHIP_NORMAL = 50000;   // ≤3 sản phẩm
const SHIP_BULK   = 100000;  // >3 sản phẩm
function calcShipping(count) {
    return count > 3 ? SHIP_BULK : SHIP_NORMAL;
}

// ===== CHECKBOX LOGIC =====
function recalcSelected() {
    const boxes = document.querySelectorAll('.item-check');
    const selectAll = document.getElementById('selectAll');
    let sum = 0, count = 0;
    const ids = [];
    boxes.forEach(cb => {
        if (cb.checked) {
            sum += parseFloat(cb.dataset.subtotal);
            count++;
            ids.push(cb.dataset.id);
        }
    });
    CART_TOTAL = sum;

    // Tính phí ship
    const ship = count > 0 ? calcShipping(count) : 0;
    document.getElementById('shippingFee').textContent = count > 0 ? formatMoney(ship) + '₫' : '—';

    document.getElementById('selectedCount').textContent = count;
    document.getElementById('subtotalAmount').textContent = formatMoney(sum) + '₫';

    // Reset voucher khi thay đổi lựa chọn

    document.getElementById('voucherApplied').classList.remove('show');
    document.getElementById('voucherCode').value = '';
    appliedVoucher = null;
    tempSelected   = null;

    document.getElementById('totalAmount').textContent = formatMoney(sum + ship) + '₫';

    // Cập nhật chi tiết thanh toán
    updatePaymentDetail(sum, ship, 0, 0);

    // Update select-all state
    selectAll.checked = count === boxes.length && boxes.length > 0;
    selectAll.indeterminate = count > 0 && count < boxes.length;
    // Enable/disable checkout
    document.getElementById('checkoutBtn').disabled = count === 0;
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.item-check').forEach(cb => cb.checked = this.checked);
            recalcSelected();
        });
    }
    document.querySelectorAll('.item-check').forEach(cb => {
        cb.addEventListener('change', recalcSelected);
    });
    recalcSelected(); // init
});

function goCheckout() {
    const ids = [];
    document.querySelectorAll('.item-check:checked').forEach(cb => ids.push(cb.dataset.id));
    if (ids.length === 0) { alert('Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.'); return; }
    const ship = calcShipping(ids.length);
    let url = 'thanhtoan.php?ids=' + ids.join(',') + '&ship=' + ship;
    if (appliedVoucher) url += '&voucher=' + encodeURIComponent(appliedVoucher.code) + '&discount=' + appliedVoucher.discount;
    window.location.href = url;
}
// ========================

let appliedVoucher = null;
let tempSelected   = null;
let availableList  = [];

/* ---- AJAX helpers ---- */
function ajaxCheckVoucher(code, callback) {
    const fd = new FormData();
    fd.append('code', code);
    fd.append('total', CART_TOTAL);
    fetch(VOUCHER_API + '?action=check_voucher', { method: 'POST', body: fd })
        .then(r => r.json()).then(callback)
        .catch(() => callback({ ok: false, msg: 'Lỗi kết nối. Vui lòng thử lại.' }));
}

function ajaxListVouchers(callback) {
    fetch(VOUCHER_API + '?action=list_vouchers&total=' + CART_TOTAL)
        .then(r => r.json()).then(callback)
        .catch(() => callback({ ok: false, vouchers: [] }));
}

/* ---- UI helpers ---- */
function iconForType(type) {
    return { percent: '🎟️', fixed: '💰', freeship: '🚚' }[type] || '🎫';
}
function iconBgForType(type) {
    return { percent: '#2563eb', fixed: '#16a34a', freeship: '#7c3aed' }[type] || '#64748b';
}

function buildVoucherCard(v) {
    const isSelected = tempSelected === v.code;
    const card = document.createElement('div');
    card.className = 'voucher-card' + (isSelected ? ' selected' : '');
    card.onclick = () => selectVoucherCard(v.code);
    card.innerHTML = `
        <div class="voucher-icon" style="background:${iconBgForType(v.type)};color:#fff;">${iconForType(v.type)}</div>
        <div class="voucher-info">
            <div class="voucher-name">${v.name}</div>
            <div class="voucher-desc">${v.description}</div>
            <div class="voucher-expire">HSD: ${v.expire_date}</div>
        </div>
        ${v.discount > 0 ? `<span class="voucher-badge" style="color:#15803d;background:#dcfce7;">-${formatMoney(v.discount)}₫</span>` : ''}
        <div class="radio-dot"></div>
    `;
    return card;
}

function renderVoucherList(filter) {
    let items = availableList;
    if (filter) {
        const q = filter.toUpperCase();
        items = items.filter(v => v.code.includes(q) || v.name.toLowerCase().includes(filter.toLowerCase()));
    }

    const freeship = items.filter(v => v.type === 'freeship');
    const discount  = items.filter(v => v.type !== 'freeship');

    // --- Freeship group ---
    const listFs = document.getElementById('listFreeship');
    document.getElementById('countFreeship').textContent = freeship.length;
    listFs.innerHTML = '';
    if (freeship.length) {
        freeship.forEach(v => listFs.appendChild(buildVoucherCard(v)));
    } else {
        listFs.innerHTML = '<p class="no-voucher-in-group">Không có mã miễn phí vận chuyển khả dụng</p>';
    }

    // --- Discount group ---
    const listDc = document.getElementById('listDiscount');
    document.getElementById('countDiscount').textContent = discount.length;
    listDc.innerHTML = '';
    if (discount.length) {
        discount.forEach(v => listDc.appendChild(buildVoucherCard(v)));
    } else {
        listDc.innerHTML = '<p class="no-voucher-in-group">Không có mã giảm giá khả dụng</p>';
    }
}

function selectVoucherCard(code) {
    tempSelected = code;
    renderVoucherList(document.getElementById('modalVoucherInput').value);
}

/* ---- Modal ---- */
function openVoucherModal() {
    tempSelected = appliedVoucher ? appliedVoucher.code : null;
    document.getElementById('modalVoucherInput').value = '';

    // Show loading state inside each group
    const loadingHtml = '<p class="no-voucher-in-group" style="color:#94a3b8;">Đang tải...</p>';
    document.getElementById('listFreeship').innerHTML = loadingHtml;
    document.getElementById('listDiscount').innerHTML  = loadingHtml;
    document.getElementById('countFreeship').textContent = '';
    document.getElementById('countDiscount').textContent  = '';

    document.getElementById('voucherModalOverlay').classList.add('open');

    ajaxListVouchers(res => {
        availableList = res.vouchers || [];
        if (!res.ok && !availableList.length) {
            const errHtml = `<p class="no-voucher-in-group" style="color:#ef4444;">${res.msg || 'Bạn cần đăng nhập để dùng voucher.'}</p>`;
            document.getElementById('listFreeship').innerHTML = errHtml;
            document.getElementById('listDiscount').innerHTML  = errHtml;
            document.getElementById('countFreeship').textContent = '0';
            document.getElementById('countDiscount').textContent  = '0';
            return;
        }
        renderVoucherList('');
    });
}

function closeVoucherModal() {
    document.getElementById('voucherModalOverlay').classList.remove('open');
}
function handleOverlayClick(e) {
    if (e.target === document.getElementById('voucherModalOverlay')) closeVoucherModal();
}

function applyFromModalInput() {
    const val = document.getElementById('modalVoucherInput').value.trim();
    if (!val) return;
    renderVoucherList(val);
    const found = availableList.find(v => v.code === val.toUpperCase());
    if (found) selectVoucherCard(found.code);
}

function confirmVoucher() {
    if (!tempSelected) { closeVoucherModal(); return; }
    const btn = document.querySelector('.btn-modal-confirm');
    btn.textContent = 'Đang kiểm tra...';
    btn.disabled = true;

    ajaxCheckVoucher(tempSelected, res => {
        btn.textContent = 'Đồng ý';
        btn.disabled = false;
        if (!res.ok) { alert(res.msg); return; }
        applyVoucherResult(res);
        closeVoucherModal();
    });
}

/* ---- Inline input ---- */
function applyVoucherCode() {
    const code = document.getElementById('voucherCode').value.trim();
    if (!code) { alert('Vui lòng nhập mã voucher.'); return; }
    const applyBtn = document.querySelector('.btn-apply-voucher');
    applyBtn.textContent = '...';
    applyBtn.disabled = true;

    ajaxCheckVoucher(code, res => {
        applyBtn.textContent = 'Áp dụng';
        applyBtn.disabled = false;
        if (!res.ok) { alert(res.msg); return; }
        applyVoucherResult(res);
    });
}

/* ---- Shared apply result ---- */
function applyVoucherResult(res) {
    appliedVoucher = res;
    document.getElementById('voucherCode').value = res.code;
    document.getElementById('voucherAppliedLabel').textContent =
        res.name + (res.discount > 0 ? ' (-' + formatMoney(res.discount) + '₫)' : '');
    document.getElementById('voucherApplied').classList.add('show');

    document.getElementById('totalAmount').textContent = formatMoney(CART_TOTAL - res.discount) + '₫';
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn)
        checkoutBtn.href = 'thanhtoan.php?voucher=' + encodeURIComponent(res.code) + '&discount=' + res.discount;

    // Cập nhật chi tiết thanh toán khi áp voucher
    const boxes = document.querySelectorAll('.item-check:checked');
    const ship = boxes.length > 0 ? calcShipping(boxes.length) : 0;
    // Nếu là freeship voucher thì giảm ship, ngược lại giảm hàng
    const freeshipDiscount = (res.type === 'freeship') ? Math.min(res.discount, ship) : 0;
    const voucherDiscount  = (res.type !== 'freeship') ? res.discount : 0;
    updatePaymentDetail(CART_TOTAL, ship, freeshipDiscount, voucherDiscount);
}

/* ---- Cập nhật bảng chi tiết thanh toán ---- */
function updatePaymentDetail(subtotal, ship, freeshipDiscount, voucherDiscount) {
    document.getElementById('detailSubtotal').textContent = formatMoney(subtotal) + '₫';
    document.getElementById('detailShipping').textContent  = ship > 0 ? formatMoney(ship) + '₫' : '—';

    if (freeshipDiscount > 0) {
        document.getElementById('detailFreeshipRow').style.display = 'flex';
        document.getElementById('detailFreeship').textContent = '-' + formatMoney(freeshipDiscount) + '₫';
    } else {
        document.getElementById('detailFreeshipRow').style.display = 'none';
    }

    if (voucherDiscount > 0) {
        document.getElementById('detailVoucherRow').style.display = 'flex';
        document.getElementById('detailVoucher').textContent = '-' + formatMoney(voucherDiscount) + '₫';
    } else {
        document.getElementById('detailVoucherRow').style.display = 'none';
    }

    const total = subtotal + ship - freeshipDiscount - voucherDiscount;
    document.getElementById('detailTotal').textContent = formatMoney(Math.max(0, total)) + '₫';
}

function removeVoucher() {
    appliedVoucher = null;
    tempSelected   = null;
    document.getElementById('voucherCode').value = '';
    document.getElementById('voucherApplied').classList.remove('show');
    document.getElementById('totalAmount').textContent = formatMoney(CART_TOTAL) + '₫';
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) checkoutBtn.href = 'thanhtoan.php';

    // Reset chi tiết thanh toán
    const boxes = document.querySelectorAll('.item-check:checked');
    const ship = boxes.length > 0 ? calcShipping(boxes.length) : 0;
    updatePaymentDetail(CART_TOTAL, ship, 0, 0);
}

function formatMoney(n) {
    return Math.round(n).toLocaleString('vi-VN');
}

document.getElementById('voucherCode').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') applyVoucherCode();
});
document.getElementById('modalVoucherInput').addEventListener('input', function() {
    renderVoucherList(this.value);
});

/* ===== SỐ LƯỢNG SẢN PHẨM ===== */
function changeQty(id, delta) {
    const input = document.getElementById('qty-' + id);
    if (!input) return;
    const newVal = Math.max(1, parseInt(input.value || 1) + delta);
    input.value = newVal;
    setQty(id, newVal);
}

function setQty(id, rawVal) {
    const qty = Math.max(1, parseInt(rawVal) || 1);
    const input   = document.getElementById('qty-' + id);
    const stepper = document.getElementById('stepper-' + id);
    if (!input || !stepper) return;

    input.value = qty;
    stepper.classList.add('qty-loading');

    const fd = new FormData();
    fd.append('id', id);
    fd.append('qty', qty);

    fetch('giohang.php?action=update_qty', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            stepper.classList.remove('qty-loading');
            if (!res.ok) {
                alert(res.msg || 'Cập nhật thất bại.');
                if (res.max) { input.value = res.max; }
                return;
            }
            // Cập nhật subtotal cell
            const row = stepper.closest('tr');
            if (row) {
                const subCell = row.querySelector('.subtotal-cell');
                if (subCell) subCell.textContent = formatMoney(res.subtotal) + '₫';

                // Cập nhật data-subtotal trên checkbox
                const cb = row.querySelector('.item-check');
                if (cb) {
                    cb.dataset.subtotal = res.subtotal;
                }
            }
            // Tính lại tổng
            recalcSelected();
        })
        .catch(() => {
            stepper.classList.remove('qty-loading');
            alert('Lỗi kết nối. Vui lòng thử lại.');
        });
}
</script>
    </div>
</div>