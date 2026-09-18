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

$db = Database::getInstance();
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
/* ===== ADDRESS MANAGEMENT PAGE ===== */
.addr-page {
    max-width: 860px;
    margin: 40px auto 80px;
    padding: 0 20px;
    font-family: 'Outfit', 'Segoe UI', Arial, sans-serif;
}

/* Hero Banner */
.addr-hero {
    display: flex;
    align-items: center;
    gap: 16px;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    border-radius: 20px;
    padding: 24px 30px;
    color: #fff;
    margin-bottom: 28px;
    box-shadow: 0 16px 40px rgba(29,78,216,.18);
}
.addr-hero-icon {
    width: 52px;
    height: 52px;
    background: rgba(255,255,255,.15);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    flex-shrink: 0;
}
.addr-hero h1 { margin: 0; font-size: 24px; font-weight: 800; }
.addr-hero p  { margin: 4px 0 0; font-size: 14px; opacity: .85; }

/* Toolbar */
.addr-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.addr-toolbar h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
}
.addr-toolbar h2 span {
    font-weight: 500;
    color: #64748b;
    font-size: 14px;
}

/* Buttons */
.addr-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    text-decoration: none;
    font-family: inherit;
    transition: all .2s ease;
    white-space: nowrap;
}
.addr-btn-primary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    box-shadow: 0 6px 18px rgba(37,99,235,.25);
}
.addr-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(37,99,235,.32);
}
.addr-btn-success {
    background: #f0fdf4;
    color: #16a34a;
    border: 1.5px solid #bbf7d0;
}
.addr-btn-success:hover {
    background: #dcfce7;
    border-color: #86efac;
}
.addr-btn-outline {
    background: #fff;
    color: #64748b;
    border: 1.5px solid #e2e8f0;
}
.addr-btn-outline:hover {
    border-color: #94a3b8;
    color: #334155;
}
.addr-btn-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1.5px solid #fecaca;
}
.addr-btn-danger:hover {
    background: #fee2e2;
    border-color: #fca5a5;
}

/* Messages */
.addr-msg {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}
.addr-msg.error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
.addr-msg.success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

/* Address Cards */
.addr-grid {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.addr-card {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 24px;
    transition: all .2s ease;
    position: relative;
}
.addr-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 16px rgba(0,0,0,.05);
}
.addr-card.is-default {
    border-color: #22c55e;
    background: linear-gradient(135deg, #f0fdf4, #f8fffa);
}
.addr-card.is-default::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #22c55e;
    border-radius: 4px 0 0 4px;
}

.addr-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.addr-name {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.addr-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    line-height: 1.4;
}
.addr-badge-default {
    background: #22c55e;
    color: #fff;
}
.addr-badge-normal {
    background: #f1f5f9;
    color: #64748b;
}

.addr-detail {
    margin-top: 6px;
}
.addr-detail p {
    margin: 4px 0;
    font-size: 13.5px;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 8px;
}
.addr-detail p i {
    width: 16px;
    color: #94a3b8;
    font-size: 13px;
    text-align: center;
}

.addr-actions {
    display: flex;
    gap: 8px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
    flex-wrap: wrap;
}

/* Empty state */
.addr-empty {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 2px dashed #e2e8f0;
    border-radius: 20px;
}
.addr-empty-icon {
    font-size: 56px;
    margin-bottom: 16px;
    display: block;
}
.addr-empty h3 {
    margin: 0 0 8px;
    font-size: 20px;
    color: #1e293b;
}
.addr-empty p {
    margin: 0 0 20px;
    color: #64748b;
    font-size: 14px;
}

/* Add/Edit Form Card */
.addr-form-card {
    display: none;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 18px;
    padding: 28px 30px;
    margin-top: 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,.06);
    animation: addrSlideDown .25s ease;
}
.addr-form-card.open { display: block; }

@keyframes addrSlideDown {
    from { opacity: 0; transform: translateY(-12px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.addr-form-card h3 {
    margin: 0 0 18px;
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.addr-form-card h3 i { color: #2563eb; }

.addr-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.addr-form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.addr-form-group.full-width {
    grid-column: 1 / -1;
}
.addr-form-group label {
    font-size: 12.5px;
    font-weight: 600;
    color: #475569;
}
.addr-form-group label .req { color: #e10c00; }
.addr-form-group input {
    padding: 11px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: #0f172a;
    background: #fafbfc;
    outline: none;
    transition: all .2s;
    width: 100%;
    box-sizing: border-box;
}
.addr-form-group input:focus {
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.addr-form-actions {
    display: flex;
    gap: 12px;
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}

/* Select styling */
.addr-select {
    padding: 11px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: #0f172a;
    background: #fafbfc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 12px center;
    outline: none;
    transition: all .2s;
    width: 100%;
    box-sizing: border-box;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    padding-right: 36px;
}
.addr-select:focus {
    border-color: #2563eb;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.addr-select:disabled {
    opacity: .55;
    cursor: not-allowed;
    background-color: #f1f5f9;
}
.addr-select option { color: #0f172a; }
.addr-loading {
    opacity: .6;
    pointer-events: none;
}

/* Quick note */
.addr-note {
    margin-top: 20px;
    padding: 16px 20px;
    background: #f0f7ff;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
}
.addr-note p {
    margin: 0;
    font-size: 13px;
    color: #1e40af;
    display: flex;
    align-items: center;
    gap: 8px;
}
.addr-note i { font-size: 18px; }

/* Responsive */
@media (max-width: 640px) {
    .addr-form-grid { grid-template-columns: 1fr; }
    .addr-card-top { flex-direction: column; }
    .addr-hero { flex-direction: column; text-align: center; }
}
</style>

<div class="addr-page">
    <!-- Hero -->
    <div class="addr-hero">
        <div class="addr-hero-icon">📍</div>
        <div>
            <h1>Địa chỉ giao hàng</h1>
            <p>Quản lý địa chỉ giao hàng để thanh toán nhanh chóng hơn. Địa chỉ mặc định sẽ được tự động chọn khi đặt hàng.</p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="addr-toolbar">
        <h2>
            <i class="fa fa-bookmark" style="color:#2563eb;"></i>
            Địa chỉ của tôi
            <span>(<?php echo count($addresses); ?>)</span>
        </h2>
        <button onclick="openAddrForm()" class="addr-btn addr-btn-primary">
            <i class="fa fa-plus"></i> Thêm địa chỉ mới
        </button>
    </div>

    <!-- Messages -->
    <?php if ($error): ?>
        <div class="addr-msg error"><i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="addr-msg success"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Address List -->
    <?php if (empty($addresses)): ?>
        <div class="addr-empty">
            <span class="addr-empty-icon">📭</span>
            <h3>Bạn chưa có địa chỉ nào</h3>
            <p>Thêm địa chỉ giao hàng để tiết kiệm thời gian khi mua sắm sau này!</p>
            <button onclick="openAddrForm()" class="addr-btn addr-btn-primary">
                <i class="fa fa-plus"></i> Thêm địa chỉ đầu tiên
            </button>
        </div>
    <?php else: ?>
        <div class="addr-grid">
            <?php foreach ($addresses as $addr): 
                $isDefault = !empty($addr['is_default']);
                $fullAddr = trim($addr['address_detail'] . ', ' . $addr['ward'] . ', ' . $addr['district'] . ', ' . $addr['province'], ', ');
            ?>
                <div class="addr-card <?php echo $isDefault ? 'is-default' : ''; ?>">
                    <div class="addr-card-top">
                        <div>
                            <div class="addr-name">
                                <?php echo htmlspecialchars($addr['full_name']); ?>
                                <?php if ($isDefault): ?>
                                    <span class="addr-badge addr-badge-default"><i class="fa fa-check-circle"></i> Mặc định</span>
                                <?php else: ?>
                                    <span class="addr-badge addr-badge-normal">Phụ</span>
                                <?php endif; ?>
                            </div>
                            <div class="addr-detail">
                                <p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($addr['phone']); ?></p>
                                <p><i class="fa fa-map-pin"></i> <?php echo htmlspecialchars($fullAddr); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="addr-actions">
                        <?php if (!$isDefault): ?>
                            <form method="POST" style="display:inline;">
                                <?php echo CsrfHelper::field(); ?>
                                <input type="hidden" name="action" value="set_default">
                                <input type="hidden" name="id" value="<?php echo $addr['id']; ?>">
                                <button type="submit" class="addr-btn addr-btn-success">
                                    <i class="fa fa-check"></i> Đặt làm mặc định
                                </button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">
                            <?php echo CsrfHelper::field(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $addr['id']; ?>">
                            <button type="submit" class="addr-btn addr-btn-danger">
                                <i class="fa fa-trash"></i> Xóa
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Add Address Form -->
    <div class="addr-form-card" id="addrFormCard">
        <h3><i class="fa fa-plus-circle"></i> Thêm địa chỉ mới</h3>
        <form method="POST">
            <?php echo CsrfHelper::field(); ?>
            <input type="hidden" name="action" value="create">
            <div class="addr-form-grid">
                <div class="addr-form-group">
                    <label>Họ và tên <span class="req">*</span></label>
                    <input type="text" name="full_name" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="addr-form-group">
                    <label>Số điện thoại <span class="req">*</span></label>
                    <input type="text" name="phone" placeholder="0912 345 678" required>
                </div>
                <div class="addr-form-group">
                    <label>Tỉnh/Thành phố <span class="req">*</span></label>
                    <select name="province_id" id="addrProvince" class="addr-select" required onchange="loadDistricts(this.value); addrUpdateName('province', this)">
                        <option value="">-- Chọn tỉnh/thành --</option>
                    </select>
                    <input type="hidden" name="province" id="addrProvinceName" value="">
                </div>
                <div class="addr-form-group">
                    <label>Quận/Huyện <span class="req">*</span></label>
                    <select name="district_id" id="addrDistrict" class="addr-select" required onchange="loadWards(this.value); addrUpdateName('district', this)" disabled>
                        <option value="">-- Chọn quận/huyện --</option>
                    </select>
                    <input type="hidden" name="district" id="addrDistrictName" value="">
                </div>
                <div class="addr-form-group">
                    <label>Phường/Xã <span class="req">*</span></label>
                    <select name="ward_id" id="addrWard" class="addr-select" required onchange="addrUpdateName('ward', this)" disabled>
                        <option value="">-- Chọn phường/xã --</option>
                    </select>
                    <input type="hidden" name="ward" id="addrWardName" value="">
                </div>
                <div class="addr-form-group">
                    <label>Địa chỉ chi tiết <span class="req">*</span></label>
                    <input type="text" name="address_detail" placeholder="Số nhà, tên đường..." required>
                </div>
            </div>
            <div class="addr-form-actions">
                <button type="submit" class="addr-btn addr-btn-primary">
                    <i class="fa fa-save"></i> Lưu địa chỉ
                </button>
                <button type="button" onclick="closeAddrForm()" class="addr-btn addr-btn-outline">
                    Hủy
                </button>
            </div>
        </form>
    </div>

    <!-- Note -->
    <div class="addr-note">
        <p>
            <i class="fa fa-info-circle"></i>
            Địa chỉ mặc định sẽ được tự động chọn khi bạn tiến hành thanh toán. Bạn có thể chọn địa chỉ khác tại trang thanh toán.
            <a href="<?php echo BASE_URL; ?>thanhtoan.php" style="color:#2563eb;font-weight:700;text-decoration:none;margin-left:4px;">
                Đi đến thanh toán <i class="fa fa-arrow-right"></i>
            </a>
        </p>
    </div>
</div>

<script>
// ── Cascading Address Dropdown ──
var ADDR_API = BASE_URL + 'api_get_addresses.php';

// Load provinces on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProvinces();
});

// Helper: update hidden text name field when selection changes
function addrUpdateName(field, sel) {
    var name = sel.options[sel.selectedIndex]?.getAttribute('data-name') || '';
    document.getElementById('addr' + field.charAt(0).toUpperCase() + field.slice(1) + 'Name').value = name;
}

function loadProvinces() {
    var sel = document.getElementById('addrProvince');
    sel.innerHTML = '<option value="">-- Đang tải... --</option>';
    sel.disabled = true;
    fetch(ADDR_API + '?level=provinces')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            sel.innerHTML = '<option value="">-- Chọn tỉnh/thành --</option>';
            data.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.code;
                opt.setAttribute('data-name', p.name);
                opt.textContent = p.type + ' ' + p.name;
                sel.appendChild(opt);
            });
            sel.disabled = false;
        })
        .catch(function() {
            sel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            sel.disabled = false;
        });
}

function loadDistricts(provinceId) {
    var sel = document.getElementById('addrDistrict');
    var wardSel = document.getElementById('addrWard');
    wardSel.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
    wardSel.disabled = true;
    document.getElementById('addrWardName').value = '';
    if (!provinceId) {
        sel.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
        sel.disabled = true;
        document.getElementById('addrDistrictName').value = '';
        return;
    }
    sel.innerHTML = '<option value="">-- Đang tải... --</option>';
    sel.disabled = true;
    sel.classList.add('addr-loading');
    // Reset district and ward names
    document.getElementById('addrDistrictName').value = '';
    document.getElementById('addrWardName').value = '';
    fetch(ADDR_API + '?level=districts&province_id=' + encodeURIComponent(provinceId))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            sel.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
            data.forEach(function(d) {
                var opt = document.createElement('option');
                opt.value = d.code;
                opt.setAttribute('data-name', d.name);
                opt.textContent = d.type + ' ' + d.name;
                sel.appendChild(opt);
            });
            sel.disabled = false;
            sel.classList.remove('addr-loading');
        })
        .catch(function() {
            sel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            sel.disabled = false;
            sel.classList.remove('addr-loading');
        });
}

function loadWards(districtId) {
    var sel = document.getElementById('addrWard');
    if (!districtId) {
        sel.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
        sel.disabled = true;
        document.getElementById('addrWardName').value = '';
        return;
    }
    sel.innerHTML = '<option value="">-- Đang tải... --</option>';
    sel.disabled = true;
    sel.classList.add('addr-loading');
    document.getElementById('addrWardName').value = '';
    fetch(ADDR_API + '?level=wards&district_id=' + encodeURIComponent(districtId))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            sel.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
            data.forEach(function(w) {
                var opt = document.createElement('option');
                opt.value = w.code;
                opt.setAttribute('data-name', w.name);
                opt.textContent = w.type + ' ' + w.name;
                sel.appendChild(opt);
            });
            sel.disabled = false;
            sel.classList.remove('addr-loading');
        })
        .catch(function() {
            sel.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            sel.disabled = false;
            sel.classList.remove('addr-loading');
        });
}

function openAddrForm() {
    var form = document.getElementById('addrFormCard');
    form.classList.add('open');
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
function closeAddrForm() {
    document.getElementById('addrFormCard').classList.remove('open');
}
</script>

<?php include 'app/views/footer.php'; ?>
