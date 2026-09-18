        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="brand-icon">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div class="brand-text">
                        <h2>Admin Panel</h2>
                        <span>Ban Linh Kiện</span>
                    </div>
                </div>
            </div>

            <?php
            // ── Load permissions 1 lần cho sidebar ─────────────────────────
            $sidebarUserId   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
            $sidebarIsSuper  = false;
            $sidebarPerms    = [];

            if ($sidebarUserId > 0) {
                $roleCacheKey = 'admin_role_' . $sidebarUserId;
                $permCacheKey = 'admin_permissions_' . $sidebarUserId;

                // Super admin check (từ session cache của PermissionMiddleware)
                if (isset($_SESSION[$roleCacheKey]) && !empty($_SESSION[$roleCacheKey]['is_super'])) {
                    $sidebarIsSuper = true;
                } elseif (!isset($_SESSION[$roleCacheKey])) {
                    // Chưa cache (VD: page=dashboard skip PermissionMiddleware) → query trực tiếp
                    try {
                        $sidebarRoleStmt = $db->prepare(
                            "SELECT u.role_id, u.is_admin, r.name as role_key, r.permissions
                             FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?"
                        );
                        $sidebarRoleStmt->execute([$sidebarUserId]);
                        $sidebarRoleRow = $sidebarRoleStmt->fetch(PDO::FETCH_ASSOC);
                        if ($sidebarRoleRow) {
                            if ($sidebarRoleRow['role_key'] === 'super_admin') {
                                $sidebarIsSuper = true;
                            } elseif (!empty($sidebarRoleRow['is_admin']) && empty($sidebarRoleRow['role_id'])) {
                                $sidebarIsSuper = true;
                            } else {
                                $sidebarPerms = json_decode($sidebarRoleRow['permissions'] ?? '{}', true) ?? [];
                            }
                        }
                    } catch (Exception $e) {
                        $sidebarIsSuper = true; // Bảng chưa tồn tại → không chặn
                    }
                } else {
                    // Đã cache, đọc perms từ cache
                    if (isset($_SESSION[$permCacheKey])) {
                        $sidebarPerms = $_SESSION[$permCacheKey];
                    }
                }

                if (!empty($sidebarPerms['all'])) $sidebarIsSuper = true;
            } else {
                $sidebarIsSuper = true; // Fallback an toàn
            }

            /**
             * Hàm check quyền cho sidebar
             * @param string $perm  Permission key (e.g. 'products', 'orders')
             */
            function sidebarCan(string $perm): bool {
                global $sidebarIsSuper, $sidebarPerms;
                return $sidebarIsSuper || !empty($sidebarPerms[$perm]);
            }
            ?>

            <nav class="sidebar-nav">
                <span class="nav-section-label">Menu chính</span>

                <ul>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/" class="nav-link <?php echo !isset($_GET['page']) || $_GET['page'] === 'dashboard' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <?php if (sidebarCan('products')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=products" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'products' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-box"></i></span>
                            <span>Sản phẩm</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('orders')): ?>
                    <li>
                        <?php
                        $pendingCount = 0;
                        try {
                            $pendingCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
                        } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=orders" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'orders' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-shopping-bag"></i></span>
                            <span>Đơn hàng</span>
                            <?php if ($pendingCount > 0): ?>
                                <span class="nav-badge" style="background:#ef4444;color:white;animation:pulse-badge 1.5s infinite;">
                                    <?php echo $pendingCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('users')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=users" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'users' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-users"></i></span>
                            <span>Người dùng</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=loyalty" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'loyalty' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-star" style="color:#f59e0b;"></i></span>
                            <span>Tích điểm KH</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('vouchers')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=vouchers" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'vouchers' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-ticket-alt"></i></span>
                            <span>Voucher</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('products')): ?>
                    <li>
                        <?php
                        $saleCount = 0;
                        try {
                            $saleStmt = $db->query("SELECT COUNT(*) FROM products WHERE discount_percent > 0 AND is_active = 1");
                            $saleCount = (int)$saleStmt->fetchColumn();
                        } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=sale" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'sale' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-tags"></i></span>
                            <span>Giảm giá</span>
                            <?php if ($saleCount > 0): ?>
                                <span class="nav-badge" style="background:#ef4444;color:white;">
                                    <?php echo $saleCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (sidebarCan('inventory')): ?>
                    <li>
                        <?php
                        $lowCount = 0;
                        try { $lowCount = $admin->getLowStockCount(); } catch(Exception $e) {}
                        $isInventoryPage = isset($_GET['page']) && $_GET['page'] === 'inventory';
                        $invAction = $_GET['action'] ?? 'index';
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=inventory" class="nav-link <?php echo $isInventoryPage && !in_array($invAction, ['receipts','receipt_form','receipt_detail','purchase_orders','po_form','stocktake','stocktake_count']) ? 'active' : ''; ?>" style="position:relative;">
                            <span class="nav-icon"><i class="fas fa-warehouse"></i></span>
                            <span>Kho hàng</span>
                            <?php if ($lowCount > 0): ?>
                                <span class="nav-badge" style="background:#ef4444;color:white;animation:pulse-badge 1.5s infinite;">
                                    <?php echo $lowCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <?php if ($isInventoryPage): ?>
                        <ul style="list-style:none;margin:2px 0 4px 0;padding:0 0 0 16px;">
                            <?php
                            $pendingReceipts = 0;
                            try { $pendingReceipts = (int)$db->query("SELECT COUNT(*) FROM warehouse_receipts WHERE status='pending'")->fetchColumn(); } catch(Exception $e) {}
                            $pendingPOs = 0;
                            try { $pendingPOs = (int)$db->query("SELECT COUNT(*) FROM purchase_orders WHERE status='pending'")->fetchColumn(); } catch(Exception $e) {}
                            $activeSessions = 0;
                            try { $activeSessions = (int)$db->query("SELECT COUNT(*) FROM stocktake_sessions WHERE status IN ('open','counting','reviewing')")->fetchColumn(); } catch(Exception $e) {}
                            ?>
                            <li>
                                <a href="<?php echo BASE_URL; ?>admin/?page=inventory&action=receipts"
                                   class="nav-link <?php echo $invAction === 'receipts' || $invAction === 'receipt_form' || $invAction === 'receipt_detail' ? 'active' : ''; ?>"
                                   style="font-size:12px;padding:6px 10px;">
                                    <span class="nav-icon" style="font-size:11px;width:24px;height:24px;"><i class="fas fa-file-import"></i></span>
                                    <span>Phiếu Nhập Kho</span>
                                    <?php if ($pendingReceipts > 0): ?>
                                    <span style="background:#f59e0b;color:#1a1000;font-size:10px;padding:1px 6px;border-radius:12px;margin-left:auto;font-weight:700;"><?= $pendingReceipts ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>admin/?page=inventory&action=purchase_orders"
                                   class="nav-link <?php echo in_array($invAction, ['purchase_orders','po_form']) ? 'active' : ''; ?>"
                                   style="font-size:12px;padding:6px 10px;">
                                    <span class="nav-icon" style="font-size:11px;width:24px;height:24px;"><i class="fas fa-file-signature"></i></span>
                                    <span>Đặt Hàng NCC</span>
                                    <?php if ($pendingPOs > 0): ?>
                                    <span style="background:#f59e0b;color:#1a1000;font-size:10px;padding:1px 6px;border-radius:12px;margin-left:auto;font-weight:700;"><?= $pendingPOs ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>admin/?page=inventory&action=stocktake"
                                   class="nav-link <?php echo in_array($invAction, ['stocktake','stocktake_count']) ? 'active' : ''; ?>"
                                   style="font-size:12px;padding:6px 10px;">
                                    <span class="nav-icon" style="font-size:11px;width:24px;height:24px;"><i class="fas fa-clipboard-check"></i></span>
                                    <span>Kiểm Kê Kho</span>
                                    <?php if ($activeSessions > 0): ?>
                                    <span style="background:#6366f1;color:white;font-size:10px;padding:1px 6px;border-radius:12px;margin-left:auto;font-weight:700;"><?= $activeSessions ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>admin/?page=inventory&action=logs"
                                   class="nav-link <?php echo $invAction === 'logs' ? 'active' : ''; ?>"
                                   style="font-size:12px;padding:6px 10px;">
                                    <span class="nav-icon" style="font-size:11px;width:24px;height:24px;"><i class="fas fa-history"></i></span>
                                    <span>Lịch Sử Kho</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>admin/?page=serial"
                                   class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'serial') ? 'active' : ''; ?>"
                                   style="font-size:12px;padding:6px 10px;">
                                    <span class="nav-icon" style="font-size:11px;width:24px;height:24px;"><i class="fas fa-barcode"></i></span>
                                    <span>Serial Number</span>
                                </a>
                            </li>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('categories')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=categories" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'categories' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-layer-group"></i></span>
                            <span>Danh mục &amp; Nhãn hàng</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('banners')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=banners" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'banners' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-images"></i></span>
                            <span>Banner Slideshow</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('shipping')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=shipping" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'shipping' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-truck"></i></span>
                            <span>Phí vận chuyển</span>
                        </a>
                    </li>
                    <li>
                        <?php
                        $trackingCount = 0;
                        try {
                            $trackingCount = (int)$db->query("SELECT COUNT(*) FROM shipping_orders WHERE tracking_code IS NOT NULL AND tracking_code != '' AND status NOT IN ('delivered','returned')")->fetchColumn();
                        } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=shipping_carriers" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'shipping_carriers' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-shipping-fast" style="color:#22d3ee;"></i></span>
                            <span>Theo dõi vận đơn</span>
                            <?php if ($trackingCount > 0): ?>
                                <span class="nav-badge" style="background:#22d3ee;color:#083344;"><?php echo $trackingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('suppliers')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=suppliers" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'suppliers' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-industry"></i></span>
                            <span>Nhà cung cấp</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <span class="nav-section-label">Tiếp thị &amp; Bán hàng</span>
                <ul>
                    <?php if (sidebarCan('products')): ?>
                    <li>
                        <?php
                        $fsActiveCount = 0;
                        try {
                            $fsActiveCount = (int)$db->query("SELECT COUNT(*) FROM flash_sale_campaigns WHERE is_active=1 AND start_time<=NOW() AND end_time>=NOW()")->fetchColumn();
                        } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=flash_sale" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'flash_sale' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-bolt" style="color:#f59e0b;"></i></span>
                            <span>Flash Sale</span>
                            <?php if ($fsActiveCount > 0): ?>
                                <span class="nav-badge" style="background:#f59e0b;color:#1a1000;animation:pulse-badge 1.5s infinite;"><?php echo $fsActiveCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('orders')): ?>
                    <li>
                        <?php
                        $acCount = 0;
                        try { $acCount = (int)$db->query("SELECT COUNT(*) FROM abandoned_carts WHERE status='active'")->fetchColumn(); } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=abandoned_carts" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'abandoned_carts' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-shopping-cart" style="color:#f59e0b;"></i></span>
                            <span>Giỏ hàng bỏ quên</span>
                            <?php if ($acCount > 0): ?>
                                <span class="nav-badge" style="background:#f59e0b;color:#1a1000;"><?php echo $acCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('products')): ?>
                    <li>
                        <?php
                        $comboCount = 0;
                        try { $comboCount = (int)$db->query("SELECT COUNT(*) FROM product_combos WHERE is_active=1")->fetchColumn(); } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=combos" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'combos' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-gift" style="color:#f59e0b;"></i></span>
                            <span>Combo sản phẩm</span>
                            <?php if ($comboCount > 0): ?>
                                <span class="nav-badge" style="background:#f59e0b;color:#1a1000;"><?php echo $comboCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <span class="nav-section-label">Tương tác</span>
                <ul>
                    <?php if (sidebarCan('notifications')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=notifications" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'notifications' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-bell" style="color:#f59e0b;"></i></span>
                            <span>Thông báo</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('chat')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=chat" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'chat' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-comments"></i></span>
                            <span>Chat</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('comments')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=comments" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'comments' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-star"></i></span>
                            <span>Bình luận</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('inventory')): ?>
                    <li>
                        <?php
                        $bisTotal = 0;
                        try { $bisTotal = (int)$db->query("SELECT COUNT(*) FROM back_in_stock_subscriptions WHERE status='pending'")->fetchColumn(); } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=back_in_stock" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'back_in_stock' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-bell" style="color:#6366f1;"></i></span>
                            <span>Báo có hàng</span>
                            <?php if ($bisTotal > 0): ?>
                                <span class="nav-badge" style="background:#6366f1;color:white;"><?php echo $bisTotal; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('returns')): ?>
                    <li>
                        <?php
                        $returnCount = 0;
                        try { $returnCount = (int)$db->query("SELECT COUNT(*) FROM return_requests WHERE status='pending'")->fetchColumn(); } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=returns" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'returns' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-undo-alt"></i></span>
                            <span>Đổi trả / Bảo hành</span>
                            <?php if ($returnCount > 0): ?>
                                <span class="nav-badge" style="background:#ef4444;color:white;animation:pulse-badge 1.5s infinite;"><?php echo $returnCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <span class="nav-section-label">Công cụ</span>
                <ul>
                    <?php if (sidebarCan('reports')): ?>
                    <li>
                        <div style="padding:6px 10px;">
                            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                <i class="fas fa-file-excel" style="color:#22c55e;"></i> Xuất Excel
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:5px;">
                                <?php if (sidebarCan('orders')): ?>
                                <a href="<?php echo BASE_URL; ?>admin/?page=export&type=orders_excel" class="btn btn-sm" style="background:rgba(59,130,246,0.12);color:#60a5fa;border:1px solid rgba(59,130,246,0.25);font-size:11px;padding:4px 10px;">
                                    <i class="fas fa-file-excel"></i> Đơn hàng
                                </a>
                                <?php endif; ?>
                                <?php if (sidebarCan('products')): ?>
                                <a href="<?php echo BASE_URL; ?>admin/?page=export&type=products_excel" class="btn btn-sm" style="background:rgba(34,197,94,0.10);color:#4ade80;border:1px solid rgba(34,197,94,0.25);font-size:11px;padding:4px 10px;">
                                    <i class="fas fa-file-excel"></i> Sản phẩm
                                </a>
                                <?php endif; ?>
                                <?php if (sidebarCan('users')): ?>
                                <a href="<?php echo BASE_URL; ?>admin/?page=export&type=users_excel" class="btn btn-sm" style="background:rgba(139,92,246,0.12);color:#a78bfa;border:1px solid rgba(139,92,246,0.25);font-size:11px;padding:4px 10px;">
                                    <i class="fas fa-file-excel"></i> Khách hàng
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>admin/?page=export&type=report_revenue" class="btn btn-sm" style="background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,0.25);font-size:11px;padding:4px 10px;">
                                    <i class="fas fa-file-excel"></i> Báo cáo DT
                                </a>
                                <a href="<?php echo BASE_URL; ?>admin/?page=export&type=report_profit" class="btn btn-sm" style="background:rgba(22,163,74,0.12);color:#4ade80;border:1px solid rgba(22,163,74,0.25);font-size:11px;padding:4px 10px;">
                                    <i class="fas fa-file-excel"></i> Báo cáo LN
                                </a>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>

                <li class="nav-divider"></li>
                <ul>
                    <?php if (sidebarCan('roles')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=roles" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'roles' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-user-shield"></i></span>
                            <span>Phân quyền Admin</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (sidebarCan('settings')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=settings" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'settings' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-cog"></i></span>
                            <span>Cài đặt shop</span>
                        </a>
                    </li>
                    <li>
                        <?php
                        $pendingEmails = 0;
                        try {
                            $pendingEmails = (int)$db->query("SELECT COUNT(*) FROM email_queue WHERE status='pending'")->fetchColumn();
                        } catch(Exception $e) {}
                        ?>
                        <a href="<?php echo BASE_URL; ?>admin/?page=settings&action=email_queue" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'settings' && isset($_GET['action']) && $_GET['action'] === 'email_queue') ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-envelope"></i></span>
                            <span>Email Queue</span>
                            <?php if ($pendingEmails > 0): ?>
                                <span class="nav-badge" style="background:#f59e0b;color:#1a1000;font-weight:700;">
                                    <?php echo $pendingEmails; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/?page=password" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'password' ? 'active' : ''; ?>">
                            <span class="nav-icon"><i class="fas fa-key"></i></span>
                            <span>Đổi mật khẩu</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>" class="nav-link" target="_blank">
                            <span class="nav-icon"><i class="fas fa-globe"></i></span>
                            <span>Xem trang web</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/logout.php" class="nav-link logout">
                            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                            <span>Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                © 2025 Ban Linh Kiện
            </div>
        </aside>

        <!-- Mobile overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        <div class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="header-right">
                    <div class="user-chip">
                        <div class="user-avatar">
                            <?php
                                $name = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'A';
                                echo strtoupper(substr($name, 0, 1));
                            ?>
                        </div>
                        <?php echo htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin'); ?>
                    </div>
                </div>
            </header>

