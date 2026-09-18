<?php
/**
 * Handler: Combos — Quản lý Combo / Bundle sản phẩm
 * Được require từ admin/index.php
 * Biến có sẵn: $db, $admin, $error, $successMessage, $projectRoot
 */

require_once $projectRoot . '/app/helpers/ComboHelper.php';
$comboHelper = new \App\Helpers\ComboHelper($db);

// ── XỬ LÝ FORM ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Thêm / Cập nhật combo
    if (isset($_POST['save_combo'])) {
        $comboId = isset($_POST['combo_id']) ? (int)$_POST['combo_id'] : 0;
        $data = [
            'name'             => trim($_POST['name'] ?? ''),
            'description'      => trim($_POST['description'] ?? ''),
            'image'            => trim($_POST['image'] ?? ''),
            'discount_percent' => (float)($_POST['discount_percent'] ?? 0),
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
            'sort_order'       => (int)($_POST['sort_order'] ?? 0),
        ];

        if (empty($data['name'])) {
            $error = 'Vui lòng nhập tên combo.';
        } else {
            try {
                if ($comboId > 0) {
                    // Cập nhật
                    $comboHelper->updateCombo($comboId, $data);

                    // Xoá items cũ và thêm lại
                    $db->prepare("DELETE FROM product_combo_items WHERE combo_id = ?")->execute([$comboId]);

                    if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
                        foreach ($_POST['product_ids'] as $i => $pid) {
                            $qty = (int)($_POST['quantities'][$i] ?? 1);
                            $comboHelper->addComboItem($comboId, (int)$pid, max(1, $qty), $i);
                        }
                    }

                    // Tính lại giá
                    $comboHelper->calculateComboPrice($comboId);
                    $successMessage = '✅ Đã cập nhật combo thành công!';
                } else {
                    // Thêm mới
                    $newId = $comboHelper->createCombo($data, $_SESSION['user_id'] ?? null);

                    if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
                        foreach ($_POST['product_ids'] as $i => $pid) {
                            $qty = (int)($_POST['quantities'][$i] ?? 1);
                            $comboHelper->addComboItem($newId, (int)$pid, max(1, $qty), $i);
                        }
                    }

                    $comboHelper->calculateComboPrice($newId);
                    $successMessage = '✅ Đã thêm combo mới thành công!';
                }
            } catch (Exception $e) {
                $error = 'Lỗi: ' . $e->getMessage();
            }
        }
    }

    // Xoá combo
    if (isset($_POST['delete_combo']) && isset($_POST['id'])) {
        $comboHelper->deleteCombo((int)$_POST['id']);
        $successMessage = '🗑️ Đã xoá combo.';
    }

    // Toggle trạng thái
    if (isset($_POST['toggle_combo']) && isset($_POST['id'])) {
        $combo = $comboHelper->getComboById((int)$_POST['id']);
        if ($combo) {
            $db->prepare("UPDATE product_combos SET is_active = ?, updated_at = NOW() WHERE id = ?")
               ->execute([$combo['is_active'] ? 0 : 1, $combo['id']]);
            $successMessage = $combo['is_active'] ? '⛔ Đã ẩn combo.' : '✅ Đã hiện combo.';
        }
    }
}

// ── LẤY DỮ LIỆU ──
$combos = $comboHelper->getAllCombos();
$allProducts = $comboHelper->getAllProducts();

// Nếu đang sửa, lấy thông tin combo
$editCombo = null;
$editComboItems = [];
if (isset($_GET['edit_id'])) {
    $editCombo = $comboHelper->getComboById((int)$_GET['edit_id']);
    if ($editCombo) {
        $editComboItems = $comboHelper->getComboItems((int)$_GET['edit_id']);
    }
}

include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/combos/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
