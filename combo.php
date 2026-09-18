<?php
/**
 * Combo / Bundle sản phẩm — Trang dành cho người dùng
 * URL: /combo.php
 */

require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';
require_once 'app/helpers/ComboHelper.php';

use App\Core\Database;
use App\Helpers\ComboHelper;

$db = Database::getInstance();
$comboHelper = new ComboHelper($db);

$action = $_GET['action'] ?? 'list';

// ── AJAX: Thêm combo vào giỏ hàng ──
if ($action === 'add_to_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $comboId = (int)($_POST['combo_id'] ?? 0);
    if ($comboId <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'ID combo không hợp lệ.']);
        exit;
    }

    $items = $comboHelper->getComboItems($comboId);
    if (empty($items)) {
        echo json_encode(['ok' => false, 'msg' => 'Combo không có sản phẩm nào.']);
        exit;
    }

    $combo = $comboHelper->getComboById($comboId);
    $discountPct = (float)($combo['discount_percent'] ?? 0);

    $added = 0;
    $errors = [];

    foreach ($items as $item) {
        $pid = (int)$item['product_id'];
        $qty = (int)$item['quantity'];
        $stock = (int)$item['stock'];

        if ($stock <= 0) {
            $errors[] = htmlspecialchars($item['name']) . ' (hết hàng)';
            continue;
        }

        // Giá combo = giá sản phẩm * (1 - discount%)
        $basePrice = (float)($item['sale_price'] ?: $item['price']);
        $comboUnitPrice = $discountPct > 0 ? round($basePrice * (1 - $discountPct / 100)) : $basePrice;

        $actualQty = min($qty, $stock);

        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['quantity'] += $actualQty;
        } else {
            $_SESSION['cart'][$pid] = [
                'id'       => $pid,
                'name'     => $item['name'],
                'price'    => $comboUnitPrice,
                'image'    => $item['image'] ?? '',
                'quantity' => $actualQty,
            ];
        }
        $added++;
    }

    $msg = "✅ Đã thêm $added sản phẩm từ combo vào giỏ hàng!";
    if (!empty($errors)) {
        $msg .= ' (Bỏ qua: ' . implode(', ', $errors) . ')';
    }

    echo json_encode(['ok' => true, 'msg' => $msg, 'added' => $added]);
    exit;
}

// ── Lấy danh sách combo ──
$combos = $comboHelper->getActiveCombos();
$comboDetails = [];
foreach ($combos as $c) {
    $items = $comboHelper->getComboItems((int)$c['id']);
    $pricing = $comboHelper->calculateComboPrice((int)$c['id']);
    $comboDetails[] = [
        'combo'   => $c,
        'items'   => $items,
        'pricing' => $pricing,
    ];
}

include 'app/views/header.php';
?>

<style>
/* ── Combo page styles ── */
.cp-wrapper {
    background: #f4f6fa;
    min-height: 100vh;
    padding: 40px 0 60px;
}
.cp-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
}
.cp-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px 36px;
    margin-bottom: 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.cp-hero::before {
    content: '🎁';
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 80px;
    opacity: .15;
}
.cp-hero h1 {
    margin: 0 0 8px;
    font-size: 28px;
    font-weight: 800;
}
.cp-hero p {
    margin: 0;
    font-size: 15px;
    opacity: .85;
    max-width: 500px;
    line-height: 1.6;
}

/* Combo card */
.cp-card {
    background: #fff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    margin-bottom: 24px;
    border: 1px solid #e2e8f0;
    transition: box-shadow .25s, transform .25s;
}
.cp-card:hover {
    box-shadow: 0 12px 40px rgba(99,102,241,0.12);
    transform: translateY(-2px);
}
.cp-card-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.cp-card-header h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cp-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 14px;
    border-radius: 99px;
    font-size: 13px;
    font-weight: 700;
    background: rgba(255,255,255,0.2);
    color: #fff;
}

.cp-card-body {
    padding: 20px 24px;
}
.cp-desc {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 18px;
}

/* Product list inside combo */
.cp-items {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 18px;
}
.cp-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
}
.cp-item-img {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    object-fit: cover;
    background: #fff;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
}
.cp-item-img-placeholder {
    width: 56px; height: 56px;
    border-radius: 10px;
    background: #e8f3ff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.cp-item-info { flex: 1; min-width: 0; }
.cp-item-name {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cp-item-price {
    font-size: 13px;
    color: #64748b;
}
.cp-item-qty {
    font-size: 12px;
    color: #94a3b8;
    background: #f1f5f9;
    padding: 2px 9px;
    border-radius: 99px;
    font-weight: 600;
    white-space: nowrap;
}

/* Pricing row */
.cp-pricing {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    border-radius: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.cp-pricing-label { font-size: 13px; font-weight: 600; color: #065f46; }
.cp-price-original { font-size: 14px; color: #94a3b8; text-decoration: line-through; }
.cp-price-combo { font-size: 22px; font-weight: 900; color: #059669; }
.cp-savings {
    background: #059669;
    color: #fff;
    padding: 3px 10px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 700;
}

.cp-add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    box-shadow: 0 4px 14px rgba(16,185,129,0.25);
}
.cp-add-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(16,185,129,0.35);
}
.cp-add-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
    transform: none;
}

.cp-toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 99999;
    background: #fff;
    border-radius: 14px;
    padding: 16px 20px;
    max-width: 380px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.12);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    animation: slideInRight .35s ease;
    border-left: 5px solid #10b981;
}
.cp-toast.error { border-left-color: #ef4444; }
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(60px); }
    to { opacity: 1; transform: translateX(0); }
}

/* Empty state */
.cp-empty {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}
.cp-empty i { font-size: 48px; display: block; margin-bottom: 12px; opacity: .3; }
.cp-empty h3 { color: #64748b; font-size: 18px; margin-bottom: 8px; }
.cp-empty a { display:inline-flex; margin-top:16px; padding:12px 28px; background:#288ad6; color:#fff; border-radius:10px; text-decoration:none; font-weight:700; }
</style>

<div class="cp-wrapper">
    <div class="cp-container">

        <div class="cp-hero">
            <h1>🎁 Combo ưu đãi</h1>
            <p>Ghép các linh kiện thành combo tiết kiệm hơn! Mua combo giúp bạn tiết kiệm đáng kể so với mua lẻ từng sản phẩm.</p>
        </div>

        <?php if (empty($comboDetails)): ?>
        <div class="cp-empty">
            <i class="fa fa-gift"></i>
            <h3>Chưa có combo nào</h3>
            <p>Hiện tại chưa có combo ưu đãi nào. Quay lại sau nhé!</p>
            <a href="index.php"><i class="fa fa-shopping-bag"></i> Tiếp tục mua sắm</a>
        </div>
        <?php else: ?>
        <div id="comboList">
            <?php foreach ($comboDetails as $cd): 
                $c = $cd['combo'];
                $items = $cd['items'];
                $p = $cd['pricing'];
                $savingsPct = $p['discount_percent'];
            ?>
            <div class="cp-card" id="combo-<?php echo $c['id']; ?>">
                <div class="cp-card-header">
                    <h2>🎁 <?php echo htmlspecialchars($c['name']); ?></h2>
                    <span class="cp-badge"><?php echo count($items); ?> sản phẩm</span>
                </div>
                <div class="cp-card-body">
                    <?php if (!empty($c['description'])): ?>
                    <div class="cp-desc"><?php echo nl2br(htmlspecialchars($c['description'])); ?></div>
                    <?php endif; ?>

                    <div class="cp-items">
                        <?php foreach ($items as $item): ?>
                        <div class="cp-item">
                            <?php if (!empty($item['image'])): ?>
                            <img class="cp-item-img" src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($item['image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="cp-item-img-placeholder" style="display:none;">🖥️</div>
                            <?php else: ?>
                            <div class="cp-item-img-placeholder">🖥️</div>
                            <?php endif; ?>
                            <div class="cp-item-info">
                                <div class="cp-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cp-item-price">Đơn giá: <strong><?php echo number_format($item['price'],0,',','.'); ?>₫</strong></div>
                            </div>
                            <span class="cp-item-qty">x<?php echo (int)$item['quantity']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cp-pricing">
                        <span class="cp-pricing-label">Tổng giá trị:</span>
                        <span class="cp-price-original"><?php echo number_format($p['original_price'],0,',','.'); ?>₫</span>
                        <span class="cp-price-combo"><?php echo number_format($p['combo_price'],0,',','.'); ?>₫</span>
                        <?php if ($savingsPct > 0): ?>
                        <span class="cp-savings">-<?php echo $savingsPct; ?>%</span>
                        <span style="font-size:13px;color:#065f46;font-weight:600;">Tiết kiệm: <?php echo number_format($p['discount_amount'],0,',','.'); ?>₫</span>
                        <?php endif; ?>
                    </div>

                    <button class="cp-add-btn" onclick="addComboToCart(<?php echo $c['id']; ?>, this)">
                        <i class="fa fa-cart-plus"></i> Thêm combo vào giỏ hàng
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function addComboToCart(comboId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang thêm...';

    const fd = new FormData();
    fd.append('combo_id', comboId);

    fetch('<?php echo BASE_URL; ?>combo.php?action=add_to_cart', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-cart-plus"></i> Thêm combo vào giỏ hàng';

        if (res.ok) {
            showToast(res.msg, 'success');
            // Update cart count in header
            const cartBadge = document.querySelector('.cart-count-badge');
            if (cartBadge) {
                let count = parseInt(cartBadge.textContent) || 0;
                cartBadge.textContent = count + res.added;
                cartBadge.style.display = 'inline-flex';
            }
        } else {
            showToast(res.msg, 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-cart-plus"></i> Thêm combo vào giỏ hàng';
        showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
    });
}

function showToast(msg, type) {
    const existing = document.querySelector('.cp-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'cp-toast' + (type === 'error' ? ' error' : '');
    toast.innerHTML = `
        <span style="font-size:20px;line-height:1;">${type === 'error' ? '❌' : '✅'}</span>
        <div style="flex:1;">
            <div style="font-weight:700;color:#1e293b;font-size:14px;margin-bottom:4px;">${type === 'error' ? 'Thất bại' : 'Thành công'}</div>
            <div style="color:#64748b;font-size:13px;">${msg}</div>
        </div>
        <button onclick="this.closest('.cp-toast').remove()" style="background:none;border:none;font-size:18px;color:#94a3b8;cursor:pointer;padding:0;line-height:1;">×</button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity .4s';
        setTimeout(() => toast.remove(), 400);
    }, 6000);
}
</script>

<?php include 'app/views/footer.php'; ?>
