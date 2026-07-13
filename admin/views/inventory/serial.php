<?php
/**
 * Admin: Quản lý Serial Number
 * Bảng: serial_numbers (id, product_id, serial, status, order_item_id, receipt_item_id, note, created_at)
 * URL: admin/?page=serial
 */

// Lấy filter
$filterStatus    = $_GET['status']     ?? '';
$filterProductId = intval($_GET['pid'] ?? 0);
$search          = trim($_GET['q']     ?? '');

// Xử lý thêm serial mới
$addSuccess = $addError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'add_serial') {
    $pid    = intval($_POST['product_id'] ?? 0);
    $serials = array_filter(array_map('trim', preg_split('/[\n,;]+/', $_POST['serials'] ?? '')));
    $note   = trim($_POST['note'] ?? '');
    $added  = 0; $dup = 0;
    foreach ($serials as $s) {
        if (!$s) continue;
        // Kiểm tra trùng
        $chk = $db->prepare("SELECT COUNT(*) FROM serial_numbers WHERE serial=?");
        $chk->execute([$s]);
        if ($chk->fetchColumn() > 0) { $dup++; continue; }
        $db->prepare("INSERT INTO serial_numbers (product_id, serial, status, note) VALUES (?,?,?,?)")
           ->execute([$pid, $s, 'in_stock', $note]);
        $added++;
    }
    if ($added > 0) {
        $addSuccess = "Đã thêm $added serial. " . ($dup > 0 ? "$dup serial trùng bị bỏ qua." : '');
    } else {
        $addError = $dup > 0 ? "Tất cả serial đã tồn tại trong hệ thống." : "Không có serial hợp lệ để thêm.";
    }
}

// Xóa serial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'delete_serial') {
    $sid = intval($_POST['serial_id'] ?? 0);
    // Chỉ xóa nếu in_stock
    $db->prepare("DELETE FROM serial_numbers WHERE id=? AND status='in_stock'")->execute([$sid]);
    header('Location: ' . BASE_URL . 'admin/?page=serial&success=deleted'); exit;
}

// Cập nhật note/status thủ công
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'update_serial') {
    $sid    = intval($_POST['serial_id']);
    $status = in_array($_POST['status'], ['in_stock','sold','returned','defective']) ? $_POST['status'] : 'in_stock';
    $note   = trim($_POST['note'] ?? '');
    $db->prepare("UPDATE serial_numbers SET status=?, note=? WHERE id=?")->execute([$status, $note, $sid]);
    header('Location: ' . BASE_URL . 'admin/?page=serial&success=updated'); exit;
}

// Danh sách products để dropdown
$allProducts = $db->query("SELECT id, name FROM products WHERE quantity >= 0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stats = $db->query("
    SELECT
        COUNT(*) AS total,
        SUM(status='in_stock')  AS in_stock,
        SUM(status='sold')      AS sold,
        SUM(status='returned')  AS returned,
        SUM(status='defective') AS defective
    FROM serial_numbers
")->fetch(PDO::FETCH_ASSOC);

// Query danh sách serial
$where  = [];
$params = [];
if ($filterStatus)    { $where[] = "sn.status=?";     $params[] = $filterStatus; }
if ($filterProductId) { $where[] = "sn.product_id=?"; $params[] = $filterProductId; }
if ($search)          { $where[] = "sn.serial LIKE ?"; $params[] = "%$search%"; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$serialStmt = $db->prepare("
    SELECT sn.*, p.name AS product_name
    FROM serial_numbers sn
    JOIN products p ON p.id = sn.product_id
    $whereSQL
    ORDER BY sn.created_at DESC
    LIMIT 200
");
$serialStmt->execute($params);
$serials = $serialStmt->fetchAll(PDO::FETCH_ASSOC);

$statusMap   = ['in_stock'=>'Còn hàng','sold'=>'Đã bán','returned'=>'Đã trả','defective'=>'Lỗi/Hỏng'];
$statusColor = ['in_stock'=>['#f0fdf4','#16a34a'],'sold'=>['#eff6ff','#2563eb'],'returned'=>['#fff7ed','#ea580c'],'defective'=>['#fef2f2','#dc2626']];
?>

<main class="admin-main">
  <div class="page-header">
    <div class="page-header-left">
      <h1><i class="fas fa-barcode" style="color:#6366f1;"></i> Quản lý Serial Number</h1>
      <p>Theo dõi số serial từng thiết bị — tích hợp với đơn hàng & nhập hàng</p>
    </div>
  </div>

  <?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_GET['success']==='deleted'?'Đã xóa serial.':'Đã cập nhật serial.'; ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;">
    <?php
    $sl = [
        ['Tổng serial',  $stats['total'],    '#6366f1','#f5f3ff','fa-list'],
        ['Còn hàng',     $stats['in_stock'], '#16a34a','#f0fdf4','fa-box'],
        ['Đã bán',       $stats['sold'],     '#2563eb','#eff6ff','fa-shopping-bag'],
        ['Đã trả',       $stats['returned'], '#ea580c','#fff7ed','fa-undo'],
        ['Lỗi/Hỏng',    $stats['defective'],'#dc2626','#fef2f2','fa-exclamation-triangle'],
    ];
    foreach ($sl as [$l,$v,$c,$b,$i]):
    ?>
    <a href="?page=serial<?php echo ($l !== 'Tổng serial' ? '&status=' . array_search($l, ['Còn hàng'=>'in_stock','Đã bán'=>'sold','Đã trả'=>'returned','Lỗi/Hỏng'=>'defective']) : ''); ?>"
       style="background:var(--bg-surface);border-radius:14px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:12px;text-decoration:none;transition:.2s;border:2px solid transparent;"
       onmouseover="this.style.borderColor='<?php echo $c; ?>'" onmouseout="this.style.borderColor='transparent'">
        <div style="width:42px;height:42px;border-radius:12px;background:<?php echo $b; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas <?php echo $i; ?>" style="color:<?php echo $c; ?>;font-size:18px;"></i>
        </div>
        <div>
            <p style="margin:0;font-size:10px;font-weight:700;color:var(--text-faint);text-transform:uppercase;"><?php echo $l; ?></p>
            <p style="margin:0;font-size:20px;font-weight:900;color:var(--text-primary);"><?php echo number_format($v); ?></p>
        </div>
    </a>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:22px;align-items:start;">

    <!-- Bảng serial -->
    <div>
      <!-- Filter -->
      <form method="GET" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
        <input type="hidden" name="page" value="serial">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Tìm mã serial..." class="form-control" style="flex:1;min-width:160px;max-width:260px;">
        <select name="pid" class="form-control" style="flex:1;min-width:160px;max-width:220px;">
          <option value="">Tất cả sản phẩm</option>
          <?php foreach ($allProducts as $p): ?>
          <option value="<?php echo $p['id']; ?>" <?php echo $filterProductId==$p['id']?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
          <?php endforeach; ?>
        </select>
        <select name="status" class="form-control" style="width:140px;">
          <option value="">Mọi trạng thái</option>
          <?php foreach ($statusMap as $v=>$l): ?>
          <option value="<?php echo $v; ?>" <?php echo $filterStatus===$v?'selected':''; ?>><?php echo $l; ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:8px 18px;">Lọc</button>
        <a href="?page=serial" class="btn btn-secondary" style="padding:8px 14px;">Reset</a>
      </form>

      <div style="background:var(--bg-surface);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
          <h3 style="margin:0;font-size:15px;font-weight:800;color:var(--text-primary);">Danh sách serial (<?php echo count($serials); ?>)</h3>
        </div>
        <div style="overflow-x:auto;max-height:65vh;overflow-y:auto;">
        <table class="admin-table" style="margin:0;">
          <thead style="position:sticky;top:0;z-index:1;background:var(--bg-surface);">
            <tr>
              <th>Serial</th>
              <th>Sản phẩm</th>
              <th>Trạng thái</th>
              <th>Ghi chú</th>
              <th>Ngày nhập</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($serials)): ?>
          <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-faint);">Không có serial nào</td></tr>
          <?php else: foreach ($serials as $s):
            $sc = $statusColor[$s['status']] ?? ['#f1f5f9','#64748b'];
          ?>
          <tr>
            <td><code style="font-size:13px;font-weight:700;color:var(--text-primary);background:var(--bg-elevated);padding:3px 8px;border-radius:6px;"><?php echo htmlspecialchars($s['serial']); ?></code></td>
            <td style="font-size:13px;color:var(--text-secondary);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($s['product_name']); ?></td>
            <td>
              <span style="padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $sc[0]; ?>;color:<?php echo $sc[1]; ?>;">
                <?php echo $statusMap[$s['status']] ?? $s['status']; ?>
              </span>
            </td>
            <td style="font-size:12px;color:var(--text-faint);max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($s['note'] ?? ''); ?></td>
            <td style="font-size:12px;color:var(--text-faint);white-space:nowrap;"><?php echo date('d/m/Y', strtotime($s['created_at'])); ?></td>
            <td>
              <div style="display:flex;gap:6px;">
                <!-- Inline update form -->
                <button onclick="document.getElementById('edit-<?php echo $s['id']; ?>').style.display=document.getElementById('edit-<?php echo $s['id']; ?>').style.display==='none'?'block':'none'"
                        class="btn btn-sm" style="padding:4px 10px;font-size:12px;background:#eff6ff;color:#2563eb;border:none;">
                  <i class="fas fa-edit"></i>
                </button>
                <?php if ($s['status'] === 'in_stock'): ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa serial này?')">
                  <input type="hidden" name="form_action" value="delete_serial">
                  <input type="hidden" name="serial_id" value="<?php echo $s['id']; ?>">
                  <button type="submit" class="btn btn-sm" style="padding:4px 10px;font-size:12px;background:#fef2f2;color:#dc2626;border:none;">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
                <?php endif; ?>
              </div>
              <!-- Edit form (hidden) -->
              <div id="edit-<?php echo $s['id']; ?>" style="display:none;margin-top:8px;padding:10px;background:var(--bg-elevated);border-radius:8px;min-width:240px;">
                <form method="POST">
                  <input type="hidden" name="form_action" value="update_serial">
                  <input type="hidden" name="serial_id" value="<?php echo $s['id']; ?>">
                  <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <select name="status" class="form-control" style="font-size:12px;padding:4px 8px;flex:1;min-width:120px;">
                      <?php foreach ($statusMap as $sv=>$sl): ?>
                      <option value="<?php echo $sv; ?>" <?php echo $s['status']===$sv?'selected':''; ?>><?php echo $sl; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <input type="text" name="note" value="<?php echo htmlspecialchars($s['note']??''); ?>" placeholder="Ghi chú..." class="form-control" style="font-size:12px;padding:4px 8px;flex:1;min-width:100px;">
                    <button type="submit" class="btn btn-sm btn-primary" style="font-size:12px;padding:4px 10px;">Lưu</button>
                  </div>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Form thêm serial -->
    <div>
      <div class="form-card" style="max-width:100%;margin:0;">
        <h2 class="form-section-title"><i class="fas fa-plus-circle" style="color:#6366f1;"></i> Thêm Serial mới</h2>

        <?php if ($addSuccess): ?>
        <div class="alert alert-success" style="margin-bottom:16px;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($addSuccess); ?></div>
        <?php elseif ($addError): ?>
        <div class="alert alert-error" style="margin-bottom:16px;"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($addError); ?></div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="form_action" value="add_serial">
          <div class="form-group">
            <label class="form-label">Sản phẩm <span style="color:#e10c00">*</span></label>
            <select name="product_id" class="form-control" required>
              <option value="">— Chọn sản phẩm —</option>
              <?php foreach ($allProducts as $p): ?>
              <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Mã serial <span style="font-size:11px;color:var(--text-faint);">(nhiều serial, mỗi dòng hoặc phân cách bằng dấu phẩy)</span></label>
            <textarea name="serials" class="form-control" rows="6"
                      placeholder="SN001234&#10;SN001235&#10;SN001236&#10;..." required></textarea>
            <p class="form-note">Hỗ trợ nhập nhiều serial cùng lúc, mỗi dòng 1 serial.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Ghi chú</label>
            <input type="text" name="note" class="form-control" placeholder="VD: Lô hàng tháng 7/2026">
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;">
            <i class="fas fa-plus"></i> Thêm Serial
          </button>
        </form>
      </div>

      <!-- Hướng dẫn -->
      <div style="background:var(--success-bg);border-radius:14px;padding:16px 18px;margin-top:16px;">
        <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#15803d;"><i class="fas fa-info-circle"></i> Luồng hoạt động</p>
        <ul style="margin:0;padding-left:18px;font-size:12px;color:#4ade80;line-height:2;">
          <li>Nhập hàng → thêm serial với trạng thái <strong>Còn hàng</strong></li>
          <li>Xuất bán → serial tự chuyển <strong>Đã bán</strong></li>
          <li>Khách trả → chuyển <strong>Đã trả</strong> để nhập lại</li>
          <li>Sản phẩm hỏng → chuyển <strong>Lỗi/Hỏng</strong></li>
        </ul>
      </div>
    </div>
  </div>
</main>
