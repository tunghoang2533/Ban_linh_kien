<?php
/**
 * Flash Sale Campaigns Admin View
 * Quản lý chiến dịch flash sale: tạo, sửa, xóa, thêm sản phẩm
 */
require_once __DIR__ . '/../../controllers/FlashSaleController.php';
$flashSaleCtrl = new FlashSaleController($db);

// Xử lý actions
$fsAction = $_GET['fs_action'] ?? 'list';

// ── POST handlers ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tạo chiến dịch mới
    if (isset($_POST['create_campaign'])) {
        $result = $flashSaleCtrl->createCampaign($_POST, $_SESSION['user_id'] ?? null);
        if ($result) {
            header('Location: ?page=flash_sale&success=created');
            exit;
        }
        $fsError = 'Không thể tạo chiến dịch. Vui lòng kiểm tra lại thông tin.';
    }
    // Cập nhật chiến dịch
    elseif (isset($_POST['update_campaign'])) {
        $result = $flashSaleCtrl->updateCampaign(intval($_POST['campaign_id']), $_POST);
        if ($result) {
            header('Location: ?page=flash_sale&success=updated');
            exit;
        }
        $fsError = 'Không thể cập nhật chiến dịch.';
    }
    // Xóa chiến dịch
    elseif (isset($_POST['delete_campaign'])) {
        $flashSaleCtrl->deleteCampaign(intval($_POST['campaign_id']));
        header('Location: ?page=flash_sale&success=deleted');
        exit;
    }
    // Thêm sản phẩm vào chiến dịch
    elseif (isset($_POST['add_product'])) {
        $result = $flashSaleCtrl->addProduct(
            intval($_POST['campaign_id']),
            intval($_POST['product_id']),
            $_POST['product_discount_type'] ?? null,
            $_POST['product_discount_value'] ?? null,
            intval($_POST['max_quantity'] ?? 0)
        );
        header('Location: ?page=flash_sale&fs_action=edit&id=' . intval($_POST['campaign_id']) . '&success=product_added');
        exit;
    }
    // Xóa sản phẩm khỏi chiến dịch
    elseif (isset($_POST['remove_product'])) {
        $flashSaleCtrl->removeProduct(intval($_POST['fsp_id']));
        header('Location: ?page=flash_sale&fs_action=edit&id=' . intval($_POST['campaign_id']) . '&success=product_removed');
        exit;
    }
    // Toggle trạng thái
    elseif (isset($_POST['toggle_campaign'])) {
        $flashSaleCtrl->toggleCampaign(intval($_POST['campaign_id']));
        header('Location: ?page=flash_sale&success=toggled');
        exit;
    }
}

// ── GET handlers ──
if ($fsAction === 'edit' && isset($_GET['id'])) {
    $editCampaign = $flashSaleCtrl->getCampaignById(intval($_GET['id']));
    if (!$editCampaign) {
        $fsError = 'Không tìm thấy chiến dịch.';
        $fsAction = 'list';
    } else {
        $campaignProducts = $flashSaleCtrl->getCampaignProducts(intval($_GET['id']));
    }
}

if ($fsAction === 'add_product_search' && isset($_GET['campaign_id'])) {
    $searchProducts = $flashSaleCtrl->searchProducts($_GET['q'] ?? '');
}

// Success messages
$fsSuccessMsg = '';
if (isset($_GET['success'])) {
    $msgMap = [
        'created'         => '✅ Đã tạo chiến dịch flash sale thành công!',
        'updated'         => '✅ Đã cập nhật chiến dịch!',
        'deleted'         => '✅ Đã xóa chiến dịch!',
        'toggled'         => '✅ Đã thay đổi trạng thái!',
        'product_added'   => '✅ Đã thêm sản phẩm vào chiến dịch!',
        'product_removed' => '✅ Đã xóa sản phẩm khỏi chiến dịch!',
    ];
    $fsSuccessMsg = $msgMap[$_GET['success']] ?? '';
}

// Stats
$fsStats = $flashSaleCtrl->getStats();
$campaigns = $flashSaleCtrl->getAllCampaigns();
?>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-bolt" style="color:#f59e0b;margin-right:10px;"></i>Flash Sale</h1>
            <p>Tạo chiến dịch giảm giá trong thời gian nhất định — tạo cảm giác khan hiếm, tăng doanh số</p>
        </div>
        <div class="page-header-right">
            <a href="?page=flash_sale&fs_action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo chiến dịch mới
            </a>
        </div>
    </div>

    <?php if ($fsSuccessMsg): ?>
    <div class="alert alert-success" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:4px solid #10b981;padding:14px 20px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-check-circle" style="color:#10b981;font-size:18px;"></i>
        <span><?php echo htmlspecialchars($fsSuccessMsg); ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($fsError)): ?>
    <div class="alert alert-error" style="background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:4px solid #ef4444;padding:14px 20px;border-radius:10px;margin-bottom:20px;">
        <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
        <?php echo htmlspecialchars($fsError); ?>
    </div>
    <?php endif; ?>

    <!-- Stats cards -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
        <div class="stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);"><i class="fas fa-bolt"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $fsStats['active']; ?></div>
                <div class="stat-label">Đang diễn ra</div>
            </div>
        </div>
        <div class="stat-card" style="border-left:4px solid #6366f1;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $fsStats['upcoming']; ?></div>
                <div class="stat-label">Sắp diễn ra</div>
            </div>
        </div>
        <div class="stat-card" style="border-left:4px solid #10b981;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);"><i class="fas fa-box"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $fsStats['products']; ?></div>
                <div class="stat-label">Sản phẩm Flash</div>
            </div>
        </div>
        <div class="stat-card" style="border-left:4px solid #ec4899;">
            <div class="stat-icon" style="background:linear-gradient(135deg,#ec4899,#f472b6);"><i class="fas fa-calendar"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $fsStats['total']; ?></div>
                <div class="stat-label">Tổng chiến dịch</div>
            </div>
        </div>
    </div>

    <?php if ($fsAction === 'create' || ($fsAction === 'edit' && isset($editCampaign))): ?>
    <?php $isEdit = ($fsAction === 'edit' && isset($editCampaign)); ?>
    <?php $camp = $isEdit ? $editCampaign : []; ?>

    <!-- Form tạo/sửa chiến dịch -->
    <section class="form-card" style="max-width:100%;margin-bottom:24px;">
        <h2 class="form-section-title">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-plus-circle'; ?>" style="color:#f59e0b;margin-right:8px;"></i>
            <?php echo $isEdit ? 'Chỉnh sửa chiến dịch' : 'Tạo chiến dịch Flash Sale mới'; ?>
        </h2>
        <form method="POST" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <?php if ($isEdit): ?>
            <input type="hidden" name="campaign_id" value="<?php echo $camp['id']; ?>">
            <?php endif; ?>

            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Tên chiến dịch <span class="req">*</span></label>
                <input type="text" name="name" class="form-control" required
                       value="<?php echo htmlspecialchars($camp['name'] ?? ''); ?>"
                       placeholder="VD: Flash Sale Thứ 6 Đen Tối / Sale Mùa Hè">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Mô tả ngắn về chiến dịch..."><?php echo htmlspecialchars($camp['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Loại giảm giá</label>
                <select name="discount_type" class="form-control">
                    <option value="percent" <?php echo ($camp['discount_type'] ?? 'percent') === 'percent' ? 'selected' : ''; ?>>Phần trăm (%)</option>
                    <option value="fixed" <?php echo ($camp['discount_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Số tiền cố định (₫)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Giá trị giảm <span class="req">*</span></label>
                <input type="number" name="discount_value" class="form-control" required step="0.5" min="0"
                       value="<?php echo htmlspecialchars($camp['discount_value'] ?? '0'); ?>"
                       placeholder="VD: 20 (nếu %) hoặc 50000 (nếu ₫)">
                <p class="form-note">Nếu là %, nhập số từ 1-100. Nếu là số tiền, nhập số VND.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Thời gian bắt đầu <span class="req">*</span></label>
                <input type="datetime-local" name="start_time" class="form-control" required
                       value="<?php echo $isEdit ? date('Y-m-d\TH:i', strtotime($camp['start_time'])) : ''; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Thời gian kết thúc <span class="req">*</span></label>
                <input type="datetime-local" name="end_time" class="form-control" required
                       value="<?php echo $isEdit ? date('Y-m-d\TH:i', strtotime($camp['end_time'])) : ''; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Thứ tự hiển thị</label>
                <input type="number" name="sort_order" class="form-control" min="0" value="<?php echo intval($camp['sort_order'] ?? 0); ?>">
            </div>
            <div class="form-group">
                <label class="form-check-label">
                    <input type="checkbox" name="is_active" value="1" <?php echo (!isset($camp['is_active']) || $camp['is_active']) ? 'checked' : ''; ?>>
                    Kích hoạt ngay
                </label>
            </div>
            <div class="form-actions" style="grid-column:1/-1;display:flex;gap:10px;padding-top:10px;">
                <button type="submit" name="<?php echo $isEdit ? 'update_campaign' : 'create_campaign'; ?>" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $isEdit ? 'Cập nhật' : 'Tạo chiến dịch'; ?>
                </button>
                <a href="?page=flash_sale" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
            </div>
        </form>
    </section>

    <?php if ($isEdit): ?>
    <!-- Quản lý sản phẩm trong chiến dịch -->
    <section class="form-card" style="max-width:100%;margin-bottom:24px;">
        <h2 class="form-section-title" style="display:flex;align-items:center;justify-content:space-between;">
            <span><i class="fas fa-box" style="color:#10b981;margin-right:8px;"></i>Sản phẩm trong chiến dịch (<?php echo count($campaignProducts ?? []); ?>)</span>
        </h2>

        <!-- Form thêm sản phẩm -->
        <div style="background:var(--bg-elevated);border-radius:10px;padding:16px;margin-bottom:16px;">
            <h4 style="margin:0 0 12px;font-size:14px;font-weight:700;">➕ Thêm sản phẩm</h4>
            <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
                <input type="hidden" name="campaign_id" value="<?php echo $camp['id']; ?>">
                <div style="flex:2;min-width:200px;">
                    <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Chọn sản phẩm</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php
                        $allProds = $flashSaleCtrl->searchProducts('');
                        foreach ($allProds as $p):
                        ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo number_format($p['price'], 0, ',', '.'); ?>₫)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1;min-width:120px;">
                    <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Loại giảm (riêng)</label>
                    <select name="product_discount_type" class="form-control">
                        <option value="">Dùng chung chiến dịch</option>
                        <option value="percent">%</option>
                        <option value="fixed">₫</option>
                    </select>
                </div>
                <div style="flex:1;min-width:100px;">
                    <label style="font-size:11px;font-weight:600;color:var(--text-muted);">Giảm riêng</label>
                    <input type="number" name="product_discount_value" class="form-control" placeholder="0" step="0.5" min="0">
                </div>
                <div style="flex:1;min-width:100px;">
                    <label style="font-size:11px;font-weight:600;color:var(--text-muted);">SL tối đa</label>
                    <input type="number" name="max_quantity" class="form-control" value="0" min="0" placeholder="0 = không giới hạn">
                </div>
                <button type="submit" name="add_product" class="btn btn-sm btn-primary" style="margin-bottom:0;">
                    <i class="fas fa-plus"></i> Thêm
                </button>
            </form>
        </div>

        <!-- Danh sách sản phẩm -->
        <?php if (empty($campaignProducts)): ?>
        <p style="text-align:center;padding:30px;color:var(--text-faint);">
            <i class="fas fa-box-open" style="font-size:32px;display:block;margin-bottom:8px;opacity:0.3;"></i>
            Chưa có sản phẩm nào. Thêm sản phẩm vào chiến dịch!
        </p>
        <?php else: ?>
        <div class="table-responsive" style="border-radius:0;box-shadow:none;border:none;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá gốc</th>
                        <th>Giảm</th>
                        <th>Giá Flash</th>
                        <th style="text-align:center;">Đã bán / Tối đa</th>
                        <th style="text-align:center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaignProducts as $fp): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php if ($fp['image']): ?>
                                <img src="<?php echo BASE_URL; ?>public/img/products/<?php echo htmlspecialchars($fp['image']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:600;font-size:13px;"><?php echo htmlspecialchars($fp['product_name']); ?></div>
                                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($fp['category_name'] ?? ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:700;"><?php echo number_format($fp['price'], 0, ',', '.'); ?>₫</td>
                        <td>
                            <span style="background:rgba(245,158,11,0.12);color:#f59e0b;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                                -<?php echo $fp['discount_amount'] ?? $fp['discount_value']; ?><?php echo ($fp['discount_type'] ?? ($camp['discount_type'] ?? 'percent')) === 'percent' ? '%' : '₫'; ?>
                            </span>
                        </td>
                        <td style="font-weight:800;color:#ef4444;font-size:15px;"><?php echo number_format($fp['sale_price'], 0, ',', '.'); ?>₫</td>
                        <td style="text-align:center;">
                            <span style="font-weight:700;"><?php echo intval($fp['sold_quantity']); ?></span>
                            <?php if (intval($fp['max_quantity']) > 0): ?>
                            / <?php echo $fp['max_quantity']; ?>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <form method="POST" onsubmit="return confirm('Xóa sản phẩm này khỏi chiến dịch?')">
                                <input type="hidden" name="campaign_id" value="<?php echo $camp['id']; ?>">
                                <input type="hidden" name="fsp_id" value="<?php echo $fp['fsp_id']; ?>">
                                <button type="submit" name="remove_product" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php else: ?>

    <!-- Danh sách chiến dịch -->
    <div class="dashboard-section">
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <a href="?page=flash_sale" class="filter-tab <?php echo $fsAction==='list'?'active':''; ?>"><i class="fas fa-list"></i> Tất cả</a>
            <a href="?page=flash_sale&fs_action=list&filter=active" class="filter-tab <?php echo ($_GET['filter']??'')==='active'?'active':''; ?>"><i class="fas fa-bolt"></i> Đang chạy</a>
            <a href="?page=flash_sale&fs_action=list&filter=upcoming" class="filter-tab"><i class="fas fa-clock"></i> Sắp tới</a>
        </div>

        <?php if (empty($campaigns)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--text-faint);">
            <i class="fas fa-bolt" style="font-size:56px;display:block;margin-bottom:16px;opacity:0.2;"></i>
            <h3 style="color:var(--text-muted);margin:0 0 8px;">Chưa có chiến dịch Flash Sale nào</h3>
            <p style="font-size:14px;margin-bottom:20px;">Tạo chiến dịch giảm giá theo thời gian để tăng doanh số!</p>
            <a href="?page=flash_sale&fs_action=create" class="btn btn-primary" style="display:inline-flex;">
                <i class="fas fa-plus"></i> Tạo chiến dịch đầu tiên
            </a>
        </div>
        <?php else: ?>
        <div style="display:grid;gap:14px;">
            <?php foreach ($campaigns as $c): 
                $now = time();
                $start = strtotime($c['start_time']);
                $end = strtotime($c['end_time']);
                $status = $c['is_active'] ? ($now >= $start && $now <= $end ? 'running' : ($now < $start ? 'upcoming' : 'ended')) : 'disabled';
                $statusData = [
                    'running'  => ['bg' => '#d1fae5', 'text' => '#065f46', 'icon' => 'fa-bolt', 'label' => 'Đang diễn ra'],
                    'upcoming' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'icon' => 'fa-clock', 'label' => 'Sắp diễn ra'],
                    'ended'    => ['bg' => '#f1f5f9', 'text' => '#64748b', 'icon' => 'fa-calendar-check', 'label' => 'Đã kết thúc'],
                    'disabled' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'icon' => 'fa-pause-circle', 'label' => 'Đã tắt'],
                ];
                $sd = $statusData[$status] ?? $statusData['disabled'];
            ?>
            <div style="background:var(--bg-surface);border-radius:14px;border:1.5px solid <?php echo $status === 'running' ? '#86efac' : 'var(--border-subtle)'; ?>;overflow:hidden;">
                <div style="display:grid;grid-template-columns:1fr auto;gap:16px;padding:18px 20px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                            <h3 style="margin:0;font-size:16px;font-weight:700;color:var(--text-primary);"><?php echo htmlspecialchars($c['name']); ?></h3>
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $sd['bg']; ?>;color:<?php echo $sd['text']; ?>;">
                                <i class="fas <?php echo $sd['icon']; ?>"></i> <?php echo $sd['label']; ?>
                            </span>
                        </div>
                        <?php if ($c['description']): ?>
                        <p style="margin:0 0 8px;font-size:13px;color:var(--text-muted);"><?php echo htmlspecialchars($c['description']); ?></p>
                        <?php endif; ?>
                        <div style="display:flex;gap:16px;font-size:12px;color:var(--text-secondary);flex-wrap:wrap;">
                            <span><i class="fas fa-tag"></i> Giảm <?php echo $c['discount_type'] === 'percent' ? $c['discount_value'] . '%' : number_format($c['discount_value'],0,',','.') . '₫'; ?></span>
                            <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', $start); ?> → <?php echo date('d/m/Y H:i', $end); ?></span>
                            <span><i class="fas fa-box"></i> <?php echo intval($c['product_count']); ?> sản phẩm</span>
                        </div>
                        <?php if ($status === 'running'): ?>
                        <div style="margin-top:8px;">
                            <div style="font-size:11px;font-weight:600;color:#16a34a;">Còn lại: <span class="fs-countdown" data-end="<?php echo $c['end_time']; ?>"></span></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;gap:6px;align-items:start;">
                        <a href="?page=flash_sale&fs_action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="campaign_id" value="<?php echo $c['id']; ?>">
                            <button type="submit" name="toggle_campaign" class="btn btn-sm <?php echo $c['is_active'] ? 'btn-secondary' : 'btn-success'; ?>" title="<?php echo $c['is_active'] ? 'Tắt' : 'Bật'; ?>">
                                <i class="fas <?php echo $c['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Xóa chiến dịch này? Hành động không thể hoàn tác.')">
                            <input type="hidden" name="campaign_id" value="<?php echo $c['id']; ?>">
                            <button type="submit" name="delete_campaign" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<script>
// ── Flash Sale Countdown ──
(function() {
    document.querySelectorAll('.fs-countdown').forEach(function(el) {
        var endTime = new Date(el.dataset.end.replace(' ', 'T')).getTime();
        function update() {
            var now = new Date().getTime();
            var diff = endTime - now;
            if (diff <= 0) { el.textContent = 'Đã kết thúc'; return; }
            var hours = Math.floor(diff / (1000 * 60 * 60));
            var mins  = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var secs  = Math.floor((diff % (1000 * 60)) / 1000);
            el.textContent = hours + 'h ' + mins + 'm ' + secs + 's';
        }
        update();
        setInterval(update, 1000);
    });
})();
</script>

<style>
.flash-badge {
    display: inline-flex; align-items: center; gap: 4px;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: white; padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
}
</style>
