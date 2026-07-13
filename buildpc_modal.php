<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;

$db = Database::getInstance();
$productModel = new ProductModel($db);

include 'app/views/header.php';

// ... rest of file unchanged ...

// Build PC modal content
$categories = [
    1 => 'Vi xử lý (CPU)',
    3 => 'Bo mạch chủ (Mainboard)',
    2 => 'Bộ nhớ trong (RAM)',
    4 => 'Card màn hình (VGA)',
    5 => 'Ổ cứng (SSD/HDD)',
    6 => 'Nguồn máy tính (PSU)',
    7 => 'Vỏ máy tính (Case)'
];

$buildpc = $_SESSION['buildpc'] ?? [];
?>
<style>
/* Build PC Modal Styles */
.buildpc-modal { display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center; }
.buildpc-modal.open { display:flex; }
.buildpc-modal-content { background:#fff; border-radius:20px; width:95%; max-width:900px; max-height:90vh; overflow-y:auto; padding:30px; position:relative; box-shadow:0 25px 80px rgba(0,0,0,.3); animation:modalIn .25s ease; }
@keyframes modalIn { from { opacity:0; transform:scale(0.95) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
.buildpc-close { position:absolute; top:16px; right:20px; font-size:28px; cursor:pointer; color:#94a3b8; background:none; border:none; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:.15s; }
.buildpc-close:hover { background:#f1f5f9; color:#1e293b; }
.buildpc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:16px; margin-top:16px; }
.buildpc-card { border:2px solid #e2e8f0; border-radius:14px; padding:16px; transition:.15s; cursor:pointer; text-align:center; }
.buildpc-card:hover { border-color:#6366f1; box-shadow:0 4px 16px rgba(99,102,241,.15); }
.buildpc-card.selected { border-color:#22c55e; background:#f0fdf4; }
.buildpc-card img { width:100%; height:120px; object-fit:contain; margin-bottom:10px; }
.buildpc-card h4 { font-size:13px; color:#1e293b; margin:0 0 6px; }
.buildpc-card .price { font-size:15px; font-weight:800; color:#e10c00; }
.buildpc-card .remove-btn { margin-top:8px; padding:6px 14px; border-radius:8px; border:1px solid #fecaca; background:#fef2f2; color:#dc2626; cursor:pointer; font-size:12px; font-weight:600; }
</style>

<div class="buildpc-modal" id="buildpcModal">
    <div class="buildpc-modal-content">
        <button class="buildpc-close" onclick="closeBuildPcModal()">&times;</button>
        <h2 style="margin:0 0 4px;font-size:22px;">🔧 Build PC của bạn</h2>
        <p style="margin:0 0 20px;color:#64748b;font-size:14px;">Chọn linh kiện tương thích cho cấu hình máy tính</p>
        <div class="buildpc-grid" id="buildpcGrid">
            <?php foreach ($categories as $catId => $catName):
                $selected = $buildpc[$catId] ?? null; ?>
                <div class="buildpc-card <?php echo $selected?'selected':''; ?>" data-cat="<?php echo $catId; ?>">
                    <div style="font-size:28px;margin-bottom:8px;">
                        <?php echo match($catId) { 1=>'💻', 3=>'📦', 2=>'🧠', 4=>'🎮', 5=>'💾', 6=>'🔌', 7=>'🖥️', default=>'📦' }; ?>
                    </div>
                    <h4><?php echo htmlspecialchars($catName); ?></h4>
                    <?php if ($selected): ?>
                        <p style="font-size:12px;color:#22c55e;margin:4px 0;"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($selected['name']); ?></p>
                        <p class="price"><?php echo number_format($selected['price'],0,',','.'); ?>₫</p>
                        <button class="remove-btn" onclick="event.stopPropagation();removeBuildPc(<?php echo $catId; ?>)">
                            <i class="fa fa-trash"></i> Bỏ chọn
                        </button>
                    <?php else: ?>
                        <p style="font-size:12px;color:#94a3b8;margin:4px 0;">Chưa chọn</p>
                        <a href="buildpc.php?action=select&cat_id=<?php echo $catId; ?>" style="display:inline-block;margin-top:8px;padding:6px 16px;background:#6366f1;color:#fff;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;">
                            Chọn linh kiện
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end;border-top:1px solid #e2e8f0;padding-top:20px;">
            <button onclick="closeBuildPcModal()" style="padding:10px 24px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-weight:600;">Đóng</button>
            <?php if (!empty($buildpc)): ?>
                <a href="buildpc.php?action=add_to_cart" style="padding:10px 24px;border-radius:10px;background:#ff9800;color:#fff;text-decoration:none;font-weight:700;">
                    <i class="fa fa-cart-plus"></i> Thêm vào giỏ
                </a>
                <a href="buildpc.php?action=buy_now" style="padding:10px 24px;border-radius:10px;background:#2563eb;color:#fff;text-decoration:none;font-weight:700;">
                    <i class="fa fa-bolt"></i> Mua ngay
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openBuildPcModal() { document.getElementById('buildpcModal').classList.add('open'); }
function closeBuildPcModal() { document.getElementById('buildpcModal').classList.remove('open'); }
function removeBuildPc(catId) {
    window.location.href = 'buildpc.php?action=remove&cat_id=' + catId;
}
// Close on backdrop click
document.getElementById('buildpcModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBuildPcModal();
});
</script>

<?php include 'app/views/footer.php'; ?>
