<?php
/**
 * Handler: Other — Các trang admin nhỏ (chat, comments, export, sale, password,
 *   settings, shipping, returns, suppliers, audit, reports, notifications,
 *   roles, loyalty, serial, seo, dashboard)
 *
 * Biến có sẵn: $db, $admin, $chatAdmin, $page, $action, $error, $successMessage, $projectRoot
 */

switch ($page):

    // ═══════════════════════════════════════════════════
    case 'chat':
        if ($action === 'send') { $chatAdmin->sendMessage(); exit; }
        elseif ($action === 'search_products') {
            $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
            $chatAdmin->searchProducts($keyword); exit;
        } elseif ($action === 'get' && isset($_GET['id'])) {
            $chatAdmin->getMessages($_GET['id'], true, true); exit;
        } elseif ($action === 'list') {
            echo json_encode(['success' => true, 'conversations' => $chatAdmin->getAllConversations()]); exit;
        } elseif ($action === 'view' && isset($_GET['id'])) {
            $conversationId = $_GET['id'];
            $conversation = $chatAdmin->getConversationById($conversationId);
            $messages = $chatAdmin->getMessages($conversationId, false);
            $userName = $conversation['full_name'] ?? $conversation['username'] ?? 'User';
            $chatAdmin->markAsRead($conversationId, true);
            include __DIR__ . '/../views/layout/header.php';
            include __DIR__ . '/../views/layout/sidebar.php';
            include __DIR__ . '/../views/chat/view.php';
            include __DIR__ . '/../views/layout/footer.php'; exit;
        } else {
            $conversations = $chatAdmin->getAllConversations();
            include __DIR__ . '/../views/layout/header.php';
            include __DIR__ . '/../views/layout/sidebar.php';
            include __DIR__ . '/../views/chat/index.php';
            include __DIR__ . '/../views/layout/footer.php'; exit;
        }
        break;

    // ═══════════════════════════════════════════════════
    case 'comments':
        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $chatAdmin->deleteComment(intval($_POST['id']));
            header('Location: ' . BASE_URL . 'admin/?page=comments&success=deleted&' . http_build_query(array_filter([
                'status' => $_POST['filter_status'] ?? '', 'rating' => $_POST['filter_rating'] ?? '', 'q' => $_POST['q'] ?? '',
            ]))); exit;
        }
        if ($action === 'hide' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $chatAdmin->hideComment(intval($_POST['id']));
            header('Location: ' . BASE_URL . 'admin/?page=comments&success=hidden&' . http_build_query(array_filter([
                'status' => $_POST['filter_status'] ?? '', 'rating' => $_POST['filter_rating'] ?? '', 'q' => $_POST['q'] ?? '',
            ]))); exit;
        }
        if ($action === 'show' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $chatAdmin->showComment(intval($_POST['id']));
            header('Location: ' . BASE_URL . 'admin/?page=comments&success=shown&' . http_build_query(array_filter([
                'status' => $_POST['filter_status'] ?? '', 'rating' => $_POST['filter_rating'] ?? '', 'q' => $_POST['q'] ?? '',
            ]))); exit;
        }
        $commentFilterStatus = trim($_GET['status'] ?? 'all');
        $commentFilterRating = trim($_GET['rating'] ?? 'all');
        $commentFilterQ      = trim($_GET['q'] ?? '');
        if (isset($_GET['success'])) {
            $msgMap = ['deleted' => 'Đã xoá bình luận.', 'hidden' => 'Đã ẩn bình luận.', 'shown' => 'Đã hiện lại bình luận.'];
            $successMessage = $msgMap[$_GET['success']] ?? '';
        }
        $productComments = $chatAdmin->getAllProductComments($commentFilterStatus, $commentFilterRating, $commentFilterQ);
        $commentStats    = $chatAdmin->getCommentStats();
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/comments/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'sale':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
            $saleId = intval($_POST['product_id']);
            $discount = max(0, min(100, floatval($_POST['discount_percent'] ?? 0)));
            if ($admin->updateDiscount($saleId, $discount)) {
                header('Location: ' . BASE_URL . 'admin/?page=sale&success=updated'); exit;
            }
            $error = 'Không thể cập nhật giảm giá.';
        }
        if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
            $admin->updateDiscount(intval($_GET['id']), 0);
            header('Location: ' . BASE_URL . 'admin/?page=sale&success=removed'); exit;
        }
        if (isset($_GET['success'])) {
            $successMap = ['updated' => 'Cập nhật giảm giá thành công!', 'removed' => 'Đã xóa giảm giá khỏi sản phẩm.'];
            $successMessage = $successMap[$_GET['success']] ?? '';
        }
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/sale/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'export':
        $type = $_GET['type'] ?? 'orders';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $type . '_' . date('Y-m-d') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        if ($type === 'orders') {
            fputcsv($out, ['Mã ĐH','Khách hàng','Email','SĐT','Địa chỉ','Tổng tiền','Giảm giá','Voucher','Phí ship','Trạng thái','Ngày tạo']);
            $rows = $db->query("SELECT o.*, u.full_name, u.email as uemail FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) fputcsv($out, [
                '#' . $r['id'], $r['full_name'] ?? $r['customer_name'] ?? '', $r['uemail'] ?? $r['customer_email'] ?? '',
                $r['customer_phone'] ?? '', $r['customer_address'] ?? '',
                number_format($r['total_amount'], 0, ',', '.') . ' VND',
                number_format($r['discount_amount'] ?? 0, 0, ',', '.') . ' VND',
                $r['voucher_code'] ?? '', number_format($r['shipping_fee'] ?? 0, 0, ',', '.') . ' VND',
                $r['status'], date('d/m/Y H:i', strtotime($r['created_at'])),
            ]);
        } elseif ($type === 'products') {
            fputcsv($out, ['ID','Tên sản phẩm','Danh mục','Thương hiệu','Giá','Tồn kho','Giảm giá %','Trạng thái']);
            $rows = $db->query("SELECT p.*, c.name as cat_name, b.name as brand_name FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id ORDER BY p.id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) fputcsv($out, [$r['id'], $r['name'], $r['cat_name']??'', $r['brand_name']??'', number_format($r['price'],0,',','.').' VND', $r['quantity'], $r['discount_percent'].'%', $r['is_active']?'Đang bán':'Ẩn']);
        } elseif ($type === 'users') {
            fputcsv($out, ['ID','Họ tên','Username','Email','SĐT','Trạng thái','Ngày đăng ký']);
            $rows = $db->query("SELECT * FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) fputcsv($out, [$r['id'], $r['full_name']??'', $r['username'], $r['email'], $r['phone']??'', $r['is_blocked']?'Bị khoá':'Hoạt động', date('d/m/Y', strtotime($r['created_at']))]);
        }
        fclose($out); exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPw = trim($_POST['current_password'] ?? '');
            $newPw     = trim($_POST['new_password'] ?? '');
            $confirmPw = trim($_POST['confirm_password'] ?? '');
            if (!$currentPw || !$newPw || !$confirmPw) $error = 'Vui lòng điền đầy đủ các trường.';
            elseif (strlen($newPw) < 6) $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            elseif ($newPw !== $confirmPw) $error = 'Mật khẩu mới và xác nhận không khớp.';
            else {
                $adminId = $_SESSION['user_id'];
                $stmt = $db->prepare('SELECT password FROM users WHERE id = :id');
                $stmt->execute([':id' => $adminId]);
                $row = $stmt->fetch();
                if (!$row || !password_verify($currentPw, $row['password'])) $error = 'Mật khẩu hiện tại không đúng.';
                else {
                    $hash = password_hash($newPw, PASSWORD_BCRYPT);
                    $db->prepare('UPDATE users SET password = :pw WHERE id = :id')->execute([':pw' => $hash, ':id' => $adminId]);
                    $successMessage = 'Đổi mật khẩu thành công!';
                }
            }
        }
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/password/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'settings':
        require_once __DIR__ . '/../controllers/SettingsController.php';
        $settingsCtrl = new SettingsController($db);

        if ($action === 'email_queue') {
            include __DIR__ . '/../views/layout/header.php';
            include __DIR__ . '/../views/layout/sidebar.php';
            include __DIR__ . '/../views/settings/email_queue.php';
            include __DIR__ . '/../views/layout/footer.php'; exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
            $allowedKeys = ['shop_name','shop_hotline','shop_email','shop_address','shop_facebook','shop_zalo','shop_youtube','policy_return','policy_warranty','policy_shipping','free_shipping_min','default_shipping_fee','meta_title_home','meta_description_home'];
            $saveData = [];
            foreach ($allowedKeys as $k) { if (isset($_POST[$k])) $saveData[$k] = $_POST[$k]; }
            if (!empty($_FILES['shop_logo_file']['name']) && $_FILES['shop_logo_file']['error'] === UPLOAD_ERR_OK) {
                $logoFn = $settingsCtrl->uploadLogo($_FILES['shop_logo_file']);
                if ($logoFn) $saveData['shop_logo'] = $logoFn;
            }
            $settingsCtrl->updateSettings($saveData);
            $successMessage = 'Đã lưu cài đặt thành công!';
        }
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/settings/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'shipping':
        require_once __DIR__ . '/../controllers/ShippingAdminController.php';
        $shippingCtrl = new ShippingAdminController($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['create_zone'])) { $shippingCtrl->createZone($_POST); header('Location: ' . BASE_URL . 'admin/?page=shipping&success=created'); exit; }
            if (isset($_POST['update_zone'])) { $shippingCtrl->updateZone(intval($_POST['zone_id']), $_POST); header('Location: ' . BASE_URL . 'admin/?page=shipping&success=updated'); exit; }
        }
        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) { $shippingCtrl->deleteZone(intval($_GET['delete'])); header('Location: ' . BASE_URL . 'admin/?page=shipping&success=deleted'); exit; }
        if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) { $shippingCtrl->toggleZone(intval($_GET['toggle'])); header('Location: ' . BASE_URL . 'admin/?page=shipping'); exit; }
        $zones = $shippingCtrl->getZones();
        $editZone = isset($_GET['edit']) ? $shippingCtrl->getZoneById(intval($_GET['edit'])) : null;
        if (isset($_GET['success'])) {
            $msgs = ['created'=>'Đã thêm vùng giao hàng!','updated'=>'Đã cập nhật!','deleted'=>'Đã xóa!'];
            $successMessage = $msgs[$_GET['success']] ?? '';
        }
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/shipping/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'returns':
        require_once __DIR__ . '/../controllers/ReturnAdminController.php';
        $returnCtrl = new ReturnAdminController($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
            $returnCtrl->updateStatus(intval($_POST['return_id']), $_POST['status'], $_POST['admin_note'] ?? '', floatval($_POST['refund_amount'] ?? 0), $_SESSION['user_id'] ?? null);
            header('Location: ' . BASE_URL . 'admin/?page=returns&success=updated'); exit;
        }
        if (isset($_GET['success'])) $successMessage = 'Đã cập nhật yêu cầu đổi trả!';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/returns/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'suppliers':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $supplierCtrl = new SupplierController($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['create_supplier'])) { $supplierCtrl->create($_POST); header('Location: ' . BASE_URL . 'admin/?page=suppliers&success=created'); exit; }
            if (isset($_POST['update_supplier'])) { $supplierCtrl->update(intval($_POST['supplier_id']), $_POST); header('Location: ' . BASE_URL . 'admin/?page=suppliers&success=updated'); exit; }
        }
        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) { $supplierCtrl->delete(intval($_GET['delete'])); header('Location: ' . BASE_URL . 'admin/?page=suppliers&success=deleted'); exit; }
        if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) { $supplierCtrl->toggle(intval($_GET['toggle'])); header('Location: ' . BASE_URL . 'admin/?page=suppliers'); exit; }
        $suppliers = $supplierCtrl->getAll();
        $supStats  = $supplierCtrl->getStats();
        $editSup   = isset($_GET['edit']) ? $supplierCtrl->getById(intval($_GET['edit'])) : null;
        if (isset($_GET['success'])) {
            $msgs = ['created'=>'Đã thêm nhà cung cấp!','updated'=>'Đã cập nhật!','deleted'=>'Đã xóa!'];
            $successMessage = $msgs[$_GET['success']] ?? '';
        }
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/suppliers/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'audit':
        require_once __DIR__ . '/../controllers/AuditController.php';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/audit/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'reports':
        require_once __DIR__ . '/../controllers/ReportController.php';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        if (($action ?? '') === 'profit' || ($_GET['tab'] ?? '') === 'profit') {
            include __DIR__ . '/../views/reports/profit.php';
        } else {
            include __DIR__ . '/../views/reports/index.php';
        }
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'notifications':
        require_once __DIR__ . '/../controllers/NotificationAdminController.php';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/notifications/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'roles':
        require_once __DIR__ . '/../controllers/RoleController.php';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/roles/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'loyalty':
        // LoyaltyHelper autoloaded via PSR-4 + class_alias
        if ($action === 'adjust' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid  = intval($_POST['user_id'] ?? 0);
            $pts  = intval($_POST['points'] ?? 0);
            $note = trim($_POST['note'] ?? 'Admin dieu chinh');
            if ($uid && $pts !== 0) LoyaltyHelper::adjust($db, $uid, $pts, $note);
            header('Location: ' . BASE_URL . 'admin/?page=loyalty&success=adjusted'); exit;
        }
        if (isset($_GET['success'])) $successMessage = 'Đã điều chỉnh điểm thành công!';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/users/loyalty.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'flash_sale':
        require_once __DIR__ . '/../controllers/FlashSaleController.php';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/flash_sale/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'abandoned_carts':
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/abandoned_carts/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'shipping_carriers':
        require_once __DIR__ . '/../controllers/ShippingAdminController.php';
        require_once __DIR__ . '/../controllers/OrderController.php';
        $orderCtrl = new OrderController($db);
        
        // Lấy danh sách carriers
        $carriers = $db->query("SELECT * FROM shipping_carriers WHERE is_active=1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy danh sách vận đơn có tracking
        $trackingOrders = $db->query("
            SELECT so.*, o.customer_name, o.customer_phone, o.tracking_code AS order_code,
                   c.name AS carrier_name, c.tracking_url AS carrier_url
            FROM shipping_orders so
            LEFT JOIN orders o ON so.order_id = o.id
            LEFT JOIN shipping_carriers c ON so.carrier = c.code
            WHERE so.tracking_code IS NOT NULL AND so.tracking_code != ''
            ORDER BY so.created_at DESC LIMIT 30
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/orders/tracking.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'serial':
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/inventory/serial.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'seo':
        require_once __DIR__ . '/../controllers/SettingsController.php';
        $settingsCtrl = new SettingsController($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_seo_home'])) {
                $settingsCtrl->updateSettings(['meta_title_home'=>$_POST['meta_title_home']??'','meta_description_home'=>$_POST['meta_description_home']??'']);
                $successMessage = 'Đã lưu meta trang chủ!';
            }
            if (isset($_POST['save_product_meta']) && !empty($_POST['product_ids'])) {
                foreach ($_POST['product_ids'] as $pid) {
                    $db->prepare("UPDATE products SET meta_title=?, meta_description=?, meta_keywords=? WHERE id=?")->execute([
                        $_POST['meta_title'][$pid] ?? '', $_POST['meta_description'][$pid] ?? '', $_POST['meta_keywords'][$pid] ?? '', $pid
                    ]);
                }
                $successMessage = 'Đã lưu meta sản phẩm!';
            }
        }
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/seo/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'back_in_stock':
        $bisModel = new ProductModel($db);

        if (isset($_GET['id'])) {
            $bisPage   = 'detail';
            $bisDetail = $db->prepare("SELECT id, name, quantity, image FROM products WHERE id = :id");
            $bisDetail->execute([':id' => (int)$_GET['id']]);
            $bisDetail = $bisDetail->fetch(PDO::FETCH_ASSOC);
            $bisSubs   = $bisDetail ? $bisModel->getBackInStockSubscribers((int)$_GET['id']) : [];
            $bisSentCount = 0;
            if ($bisDetail) {
                $st = $db->prepare("SELECT COUNT(*) FROM back_in_stock_subscriptions WHERE product_id = :pid AND status = 'notified'");
                $st->execute([':pid' => (int)$_GET['id']]);
                $bisSentCount = (int)$st->fetchColumn();
            }
        } else {
            $bisPage     = 'list';
            $bisProducts = $bisModel->getProductsWithSubscribers();
        }

        if (isset($_GET['success'])) {
            $bisSuccess = 'Đã gửi thông báo thành công!';
        }

        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/back_in_stock/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

    // ═══════════════════════════════════════════════════
    case 'dashboard':
    default:
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/layout/sidebar.php';
        include __DIR__ . '/../views/dashboard/index.php';
        include __DIR__ . '/../views/layout/footer.php'; exit;
        break;

endswitch;
