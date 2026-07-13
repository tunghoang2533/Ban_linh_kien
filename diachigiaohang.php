<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\AddressModel;
use App\Helpers\CsrfHelper;

if (!isset($_SESSION['user'])) {
    header('Location: taikhoan.php');
    exit;
}

$db = (new Database())->connect();
$addressModel = new AddressModel($db);
$userId = $_SESSION['user']['id'];

$error = '';
$success = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { CsrfHelper::verify(); } catch (Exception $e) { $error = $e->getMessage(); }

    if (!$error) {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $addressModel->create($userId, $_POST);
            $success = 'Thêm địa chỉ thành công!';
        } elseif ($action === 'update' && isset($_POST['id'])) {
            $addressModel->update((int)$_POST['id'], $userId, $_POST);
            $success = 'Cập nhật địa chỉ thành công!';
        } elseif ($action === 'delete' && isset($_POST['id'])) {
            $addressModel->delete((int)$_POST['id'], $userId);
            $success = 'Xóa địa chỉ thành công!';
        } elseif ($action === 'set_default' && isset($_POST['id'])) {
            $addressModel->setDefault((int)$_POST['id'], $userId);
            $success = 'Đã đặt làm mặc định!';
        }
    }
}

$addresses = $addressModel->getByUser($userId);

include 'app/views/header.php';

// Keep this inline since the full HTML is complex with forms, modals etc.
?>
<style>
.address-list { max-width: 800px; margin: 20px auto; padding: 0 16px; }
.address-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.address-card.default { border-color: #22c55e; background: #f0fdf4; }
.address-card h3 { margin: 0 0 4px; font-size: 16px; }
.address-card p { margin: 2px 0; color: #64748b; font-size: 13px; }
.address-actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
.btn-address { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.btn-address-primary { background: #2563eb; color: #fff; }
.btn-address-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.btn-address-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.btn-address-outline { background: #fff; color: #64748b; border: 1px solid #e2e8f0; }
</style>

<div class="address-list">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="margin:0;"><i class="fa fa-location-dot"></i> Địa chỉ giao hàng</h2>
        <button onclick="document.getElementById('addAddressForm').style.display='block'" class="btn-address btn-address-primary">
            <i class="fa fa-plus"></i> Thêm địa chỉ
        </button>
    </div>

    <?php if ($error): ?>
        <div style="background:#fef2f2;color:#dc2626;padding:12px 16px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="background:#f0fdf4;color:#16a34a;padding:12px 16px;border-radius:10px;margin-bottom:16px;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($addresses)): ?>
        <div style="text-align:center;padding:40px;color:#94a3b8;">
            <p style="font-size:40px;margin:0 0 12px;">📍</p>
            <p>Bạn chưa có địa chỉ giao hàng nào.</p>
        </div>
    <?php else: ?>
        <?php foreach ($addresses as $addr): ?>
            <div class="address-card <?php echo $addr['is_default'] ? 'default' : ''; ?>">
                <div style="display:flex;justify-content:space-between;align-items:start;">
                    <div>
                        <h3><?php echo htmlspecialchars($addr['full_name']); ?>
                            <?php if ($addr['is_default']): ?>
                                <span style="font-size:11px;background:#22c55e;color:#fff;padding:2px 8px;border-radius:20px;margin-left:8px;">Mặc định</span>
                            <?php endif; ?>
                        </h3>
                        <p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($addr['phone']); ?></p>
                        <p><i class="fa fa-map-pin"></i> <?php echo htmlspecialchars($addr['address_detail'] . ', ' . $addr['ward'] . ', ' . $addr['district'] . ', ' . $addr['province']); ?></p>
                    </div>
                </div>
                <div class="address-actions">
                    <?php if (!$addr['is_default']): ?>
                        <form method="POST" style="display:inline;">
                            <?php echo CsrfHelper::field(); ?>
                            <input type="hidden" name="action" value="set_default">
                            <input type="hidden" name="id" value="<?php echo $addr['id']; ?>">
                            <button type="submit" class="btn-address btn-address-success"><i class="fa fa-check"></i> Đặt mặc định</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa địa chỉ này?')">
                        <?php echo CsrfHelper::field(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $addr['id']; ?>">
                        <button type="submit" class="btn-address btn-address-danger"><i class="fa fa-trash"></i> Xóa</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Add Address Form -->
    <div id="addAddressForm" style="display:none;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-top:20px;">
        <h3 style="margin:0 0 16px;"><i class="fa fa-plus-circle"></i> Thêm địa chỉ mới</h3>
        <form method="POST">
            <?php echo CsrfHelper::field(); ?>
            <input type="hidden" name="action" value="create">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;">Họ tên</label>
                    <input type="text" name="full_name" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;">Số điện thoại</label>
                    <input type="text" name="phone" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;">Tỉnh/Thành phố</label>
                    <input type="text" name="province" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;">Quận/Huyện</label>
                    <input type="text" name="district" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;">Phường/Xã</label>
                    <input type="text" name="ward" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;">Địa chỉ chi tiết</label>
                    <input type="text" name="address_detail" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;">
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:12px;">
                <button type="submit" class="btn-address btn-address-primary"><i class="fa fa-save"></i> Lưu</button>
                <button type="button" onclick="document.getElementById('addAddressForm').style.display='none'" class="btn-address btn-address-outline">Hủy</button>
            </div>
        </form>
    </div>
</div>

<?php include 'app/views/footer.php'; ?>
