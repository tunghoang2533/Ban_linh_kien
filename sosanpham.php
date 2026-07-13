<?php
require_once 'config.php';
require_once 'core/Database.php';
$db = (new Database())->connect();

$ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')));
$ids = array_slice(array_unique(array_values($ids)), 0, 3);

$products  = [];
$specsData = [];
$allSpecs  = [];

if (!empty($ids)) {
    $ph   = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT p.*, c.name AS cat_name, b.name AS brand_name
                          FROM products p
                          LEFT JOIN categories c ON c.id = p.category_id
                          LEFT JOIN brands b ON b.id = p.brand_id
                          WHERE p.id IN ($ph) AND p.is_active = 1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $pr) {
        $s = $db->prepare("SELECT spec_name, spec_value FROM product_specs WHERE product_id = ? ORDER BY id");
        $s->execute([$pr['id']]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $specsData[$pr['id']][$row['spec_name']] = $row['spec_value'];
            $allSpecs[$row['spec_name']] = true;
        }
    }
}

$title = 'So sanh san pham - Ban Linh Kien';
include 'app/views/header.php';
?>

<div class="container" style="max-width:1100px;margin:36px auto 60px;">
  <div style="background:white;border-radius:18px;overflow:hidden;box-shadow:0 4px 28px rgba(0,0,0,.09);">
    <!-- Header -->
    <div style="background:linear-gradient(135deg,#1e293b,#334155);padding:24px 32px;display:flex;align-items:center;justify-content:space-between;">
      <div>
        <h1 style="margin:0;color:white;font-size:22px;font-weight:800;">
          <i class="fa fa-balance-scale" style="color:#6366f1;margin-right:8px;"></i>So sanh san pham
        </h1>
        <p style="margin:4px 0 0;color:rgba(255,255,255,.55);font-size:13px;"><?php echo count($products); ?> san pham dang so sanh</p>
      </div>
      <a href="javascript:history.back()" style="color:rgba(255,255,255,.6);font-size:13px;text-decoration:none;padding:8px 16px;border:1px solid rgba(255,255,255,.2);border-radius:8px;">&#8592; Quay lai</a>
    </div>

    <?php if (empty($products)): ?>
    <div style="padding:70px;text-align:center;color:#94a3b8;">
      <i class="fa fa-balance-scale" style="font-size:52px;opacity:.25;display:block;margin-bottom:18px;"></i>
      <p style="font-size:16px;margin:0 0 16px;">Chua co san pham nao de so sanh.</p>
      <a href="<?php echo BASE_URL; ?>" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border-radius:12px;text-decoration:none;font-weight:700;">Ve trang chu</a>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;min-width:560px;">
      <thead>
        <tr>
          <td style="width:175px;padding:16px 20px;background:#f8fafc;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;border-bottom:2px solid #e2e8f0;">Thong so</td>
          <?php
          $colColors = ['#6366f1','#10b981','#f59e0b'];
          foreach ($products as $i => $pr):
            $c     = $colColors[$i % 3];
            $saleP = (!empty($pr['discount_percent']) && $pr['discount_percent'] > 0)
                     ? round($pr['price'] * (1 - $pr['discount_percent']/100)) : $pr['price'];
            $img   = !empty($pr['image']) ? BASE_URL.'public/img/products/'.htmlspecialchars($pr['image']) : '';
          ?>
          <td style="padding:0;border-bottom:2px solid #e2e8f0;border-left:1px solid #f1f5f9;vertical-align:top;">
            <div style="background:linear-gradient(180deg,<?php echo $c; ?>15 0%,transparent 100%);padding:22px 18px;text-align:center;">
              <?php if ($img): ?><img src="<?php echo $img; ?>" style="width:130px;height:130px;object-fit:contain;border-radius:10px;margin-bottom:12px;"><?php endif; ?>
              <a href="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $pr['id']; ?>" style="font-weight:800;font-size:14px;color:#1e293b;text-decoration:none;display:block;line-height:1.4;margin-bottom:8px;"><?php echo htmlspecialchars($pr['name']); ?></a>
              <?php if ($pr['discount_percent'] > 0): ?>
              <span style="text-decoration:line-through;color:#94a3b8;font-size:12px;"><?php echo number_format($pr['price'],0,',','.'); ?>d</span><br>
              <?php endif; ?>
              <span style="font-size:22px;font-weight:900;color:#e10c00;"><?php echo number_format($saleP,0,',','.'); ?>d</span>
              <?php if ($pr['discount_percent'] > 0): ?>
              <span style="background:#e10c00;color:white;font-size:10px;font-weight:800;padding:2px 7px;border-radius:20px;margin-left:4px;">-<?php echo $pr['discount_percent']; ?>%</span>
              <?php endif; ?>
              <div style="margin-top:14px;display:flex;gap:6px;">
                <a href="<?php echo BASE_URL; ?>chitietsanpham.php?id=<?php echo $pr['id']; ?>" style="flex:1;padding:8px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:700;color:#475569;text-decoration:none;text-align:center;">Xem</a>
                <a href="<?php echo BASE_URL; ?>giohang.php?action=add&id=<?php echo $pr['id']; ?>" style="flex:2;padding:8px;background:<?php echo $c; ?>;color:white;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;text-align:center;">+ Gio hang</a>
              </div>
            </div>
          </td>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php
        $fixedRows = [
            'Danh muc'    => array_map(fn($p) => htmlspecialchars($p['cat_name'] ?? '-'), $products),
            'Thuong hieu' => array_map(fn($p) => htmlspecialchars($p['brand_name'] ?? '-'), $products),
            'Ton kho'     => array_map(function($p) {
                return $p['quantity'] > 0
                    ? '<span style="color:#16a34a;font-weight:700;">Con hang ('.intval($p['quantity']).')</span>'
                    : '<span style="color:#dc2626;font-weight:700;">Het hang</span>';
            }, $products),
        ];
        $rowIdx = 0;
        foreach ($fixedRows as $rowLabel => $vals):
            $rowIdx++;
        ?>
        <tr style="<?php echo $rowIdx%2===0 ? 'background:#fafbff;' : ''; ?>">
          <td style="padding:12px 20px;font-weight:700;font-size:13px;color:#475569;border-bottom:1px solid #f1f5f9;"><?php echo $rowLabel; ?></td>
          <?php foreach ($vals as $v): ?>
          <td style="padding:12px 18px;font-size:13px;color:#1e293b;text-align:center;border-bottom:1px solid #f1f5f9;border-left:1px solid #f1f5f9;"><?php echo $v; ?></td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        <?php if (!empty($allSpecs)): ?>
        <tr><td colspan="<?php echo count($products)+1; ?>" style="padding:10px 20px;background:#6366f112;font-size:11px;font-weight:800;color:#6366f1;text-transform:uppercase;border-top:2px solid #6366f120;">Thong so ky thuat</td></tr>
        <?php foreach (array_keys($allSpecs) as $specKey):
            $rowIdx++;
        ?>
        <tr style="<?php echo $rowIdx%2===0 ? 'background:#fafbff;' : ''; ?>">
          <td style="padding:12px 20px;font-weight:600;font-size:13px;color:#475569;border-bottom:1px solid #f1f5f9;"><?php echo htmlspecialchars($specKey); ?></td>
          <?php foreach ($products as $pr):
            $val = $specsData[$pr['id']][$specKey] ?? null;
          ?>
          <td style="padding:12px 18px;font-size:13px;color:<?php echo $val?'#1e293b':'#cbd5e1'; ?>;text-align:center;border-bottom:1px solid #f1f5f9;border-left:1px solid #f1f5f9;"><?php echo $val ? htmlspecialchars($val) : '-'; ?></td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>
  <div style="text-align:center;margin-top:20px;">
    <a href="<?php echo BASE_URL; ?>" style="color:#6366f1;font-size:14px;font-weight:600;text-decoration:none;">&#8592; Tiep tuc mua sam</a>
  </div>
</div>
<?php include 'app/views/footer.php'; ?>
