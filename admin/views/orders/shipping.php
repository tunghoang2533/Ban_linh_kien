<?php
/**
 * Admin: Vận đơn GHN/GHTK Mock UI
 * Available: $orderId, $orderDetail, $shippingOrder (null nếu chưa có)
 */
$carriers = [
    'GHN'    => ['name'=>'Giao Hàng Nhanh',   'color'=>'#e10c00', 'track'=>'https://donhang.ghn.vn/?order_code='],
    'GHTK'   => ['name'=>'Giao Hàng Tiết Kiệm','color'=>'#ff6b00', 'track'=>'https://i.ghtk.vn/'],
    'VNPOST' => ['name'=>'Vietnam Post',        'color'=>'#e32529', 'track'=>'https://www.vnpost.vn/en-us/dinh-vi/buu-pham?ms='],
    'JT'     => ['name'=>'J&T Express',         'color'=>'#e02020', 'track'=>'https://jtexpress.vn/vi/tracking?bills='],
    'NINJA'  => ['name'=>'Ninja Van',           'color'=>'#7c3aed', 'track'=>'https://www.ninjavan.co/vi-vn/tracking?id='],
];
$selectedCarrier = $shippingOrder['carrier'] ?? 'GHN';
?>
<main class="admin-main">
  <div class="page-header">
    <div class="page-header-left">
      <h1><i class="fas fa-truck" style="color:#f59e0b;"></i> Vận đơn giao hàng</h1>
      <p>Đơn hàng #<?php echo $orderId; ?> &nbsp;·&nbsp; <?php echo htmlspecialchars($orderDetail['customer_name'] ?? $orderDetail['full_name'] ?? ''); ?></p>
    </div>
    <a href="?page=orders&action=detail&id=<?php echo $orderId; ?>" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Chi tiết đơn
    </a>
  </div>

  <?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> Đã lưu vận đơn thành công!</div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

    <!-- Form -->
    <div class="form-card" style="max-width:100%;">
      <h2 class="form-section-title"><i class="fas fa-edit" style="color:#f59e0b;"></i> Thông tin vận đơn</h2>
      <form method="POST">

        <div class="form-group">
          <label class="form-label">Đơn vị vận chuyển</label>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px;">
            <?php foreach ($carriers as $code => $c): ?>
            <label style="border:2px solid <?php echo $selectedCarrier===$code?$c['color']:'#e2e8f0'; ?>;border-radius:12px;padding:12px 14px;cursor:pointer;transition:.2s;background:<?php echo $selectedCarrier===$code?$c['color'].'18':'white'; ?>;"
                   onmouseover="this.style.borderColor='<?php echo $c['color']; ?>'" onmouseout="if(!this.querySelector('input').checked)this.style.borderColor='#e2e8f0'">
              <input type="radio" name="carrier" value="<?php echo $code; ?>" <?php echo $selectedCarrier===$code?'checked':''; ?> style="display:none;"
                     onchange="document.querySelectorAll('[data-carrier]').forEach(el=>el.style.borderColor='#e2e8f0');this.closest('label').style.borderColor='<?php echo $c['color']; ?>'">
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:10px;height:10px;border-radius:50%;background:<?php echo $c['color']; ?>;flex-shrink:0;"></span>
                <span style="font-size:12px;font-weight:700;color:var(--text-primary);line-height:1.3;"><?php echo htmlspecialchars($c['name']); ?></span>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Mã vận đơn (Tracking Code)</label>
          <input type="text" name="tracking_code" class="form-control"
                 value="<?php echo htmlspecialchars($shippingOrder['tracking_code'] ?? ''); ?>"
                 placeholder="VD: GHN12345678, J0000123456...">
          <p class="form-note">Nhập mã từ phần mềm của đơn vị vận chuyển.</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="form-group">
            <label class="form-label">Phí vận chuyển (đ)</label>
            <input type="number" name="shipping_fee" class="form-control"
                   value="<?php echo intval($shippingOrder['shipping_fee'] ?? 0); ?>" min="0" step="1000">
          </div>
          <div class="form-group">
            <label class="form-label">Cân nặng (gram)</label>
            <input type="number" name="weight_gram" class="form-control"
                   value="<?php echo intval($shippingOrder['weight_gram'] ?? 0); ?>" min="0" step="100">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Ngày giao dự kiến</label>
          <input type="date" name="estimated_date" class="form-control"
                 value="<?php echo htmlspecialchars($shippingOrder['estimated_date'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Ghi chú cho shipper</label>
          <textarea name="note" class="form-control" rows="2" placeholder="Giao hàng cẩn thận, gọi trước 30 phút..."><?php echo htmlspecialchars($shippingOrder['note'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;">
          <i class="fas fa-save"></i>
          <?php echo $shippingOrder ? 'Cập nhật vận đơn' : 'Tạo vận đơn'; ?>
        </button>
      </form>
    </div>

    <!-- Panel bên phải -->
    <div>
      <?php if ($shippingOrder && !empty($shippingOrder['tracking_code'])): ?>
      <?php $cc = $carriers[$shippingOrder['carrier']] ?? ['name'=>$shippingOrder['carrier'],'color'=>'#6366f1','track'=>'']; ?>
      <div style="background:linear-gradient(135deg,#1e293b,#334155);color:white;border-radius:18px;padding:28px;margin-bottom:16px;box-shadow:0 8px 32px rgba(0,0,0,.2);">
        <p style="margin:0 0 4px;font-size:10px;opacity:.5;text-transform:uppercase;letter-spacing:.1em;">VẬN ĐƠN HIỆN TẠI</p>
        <p style="margin:0 0 18px;font-size:14px;font-weight:700;color:<?php echo $cc['color']; ?>;"><?php echo htmlspecialchars($cc['name']); ?></p>

        <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:16px;margin-bottom:16px;">
          <p style="margin:0 0 4px;font-size:10px;opacity:.5;">MÃ VẬN ĐƠN</p>
          <p style="margin:0;font-size:22px;font-weight:900;letter-spacing:1.5px;word-break:break-all;"><?php echo htmlspecialchars($shippingOrder['tracking_code']); ?></p>
        </div>

        <?php if (!empty($cc['track'])): ?>
        <a href="<?php echo $cc['track'] . urlencode($shippingOrder['tracking_code']); ?>" target="_blank"
           style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px;background:<?php echo $cc['color']; ?>;color:white;border-radius:12px;text-decoration:none;font-weight:700;font-size:14px;box-sizing:border-box;margin-bottom:14px;">
          <i class="fas fa-search"></i> Tra cứu vận đơn
        </a>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div style="background:rgba(255,255,255,.07);border-radius:10px;padding:12px;">
            <p style="margin:0 0 4px;font-size:10px;opacity:.5;">PHÍ SHIP</p>
            <p style="margin:0;font-weight:700;"><?php echo number_format($shippingOrder['shipping_fee']??0,0,',','.'); ?>đ</p>
          </div>
          <?php if (!empty($shippingOrder['estimated_date'])): ?>
          <div style="background:rgba(255,255,255,.07);border-radius:10px;padding:12px;">
            <p style="margin:0 0 4px;font-size:10px;opacity:.5;">DỰ KIẾN</p>
            <p style="margin:0;font-weight:700;"><?php echo date('d/m/Y', strtotime($shippingOrder['estimated_date'])); ?></p>
          </div>
          <?php endif; ?>
          <?php if (!empty($shippingOrder['weight_gram'])): ?>
          <div style="background:rgba(255,255,255,.07);border-radius:10px;padding:12px;">
            <p style="margin:0 0 4px;font-size:10px;opacity:.5;">CÂN NẶNG</p>
            <p style="margin:0;font-weight:700;"><?php echo number_format($shippingOrder['weight_gram']); ?>g</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="form-card" style="max-width:100%;text-align:center;padding:32px;">
        <i class="fas fa-truck" style="font-size:40px;color:#e2e8f0;margin-bottom:14px;display:block;"></i>
        <p style="color:var(--text-faint);margin:0 0 16px;">Chưa có vận đơn. Điền thông tin và nhấn Tạo vận đơn.</p>
        <div style="font-size:12px;color:var(--text-faint);text-align:left;">
          <p style="font-weight:700;margin-bottom:8px;color:var(--text-muted);">🔗 Link tra cứu nhanh:</p>
          <?php foreach ($carriers as $code => $c): if (!$c['track']) continue; ?>
          <p style="margin:4px 0;">
            <span style="font-weight:700;color:var(--text-primary);"><?php echo $code; ?>:</span>
            <a href="<?php echo $c['track']; ?>" target="_blank" style="color:#6366f1;text-decoration:none;"><?php echo parse_url($c['track'], PHP_URL_HOST); ?> ↗</a>
          </p>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Thông tin giao hàng đơn -->
      <?php if (!empty($orderDetail)): ?>
      <div class="form-card" style="max-width:100%;margin-top:0;">
        <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Địa chỉ nhận hàng</p>
        <p style="margin:0;font-size:14px;font-weight:700;color:var(--text-primary);"><?php echo htmlspecialchars($orderDetail['customer_name'] ?? $orderDetail['full_name'] ?? ''); ?></p>
        <p style="margin:4px 0;font-size:13px;color:var(--text-muted);"><?php echo htmlspecialchars($orderDetail['customer_phone'] ?? $orderDetail['phone'] ?? ''); ?></p>
        <p style="margin:4px 0;font-size:13px;color:var(--text-secondary);"><?php echo htmlspecialchars($orderDetail['customer_address'] ?? $orderDetail['address'] ?? ''); ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
