<?php
/**
 * Back-in-Stock Admin — Quản lý đăng ký báo khi có hàng
 */
$bisProducts  = $bisProducts ?? [];
$bisPage      = $bisPage ?? 'list';
$bisDetail    = $bisDetail ?? [];
$bisSubs      = $bisSubs ?? [];
$bisSentCount = $bisSentCount ?? 0;
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-bell" style="color:#6366f1;margin-right:8px;"></i>Báo khi có hàng</h1>
            <p>Quản lý email đăng ký nhận thông báo khi sản phẩm hết hàng có lại</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="?page=back_in_stock" class="btn btn-secondary btn-sm <?php echo $bisPage === 'list' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Tất cả sản phẩm
            </a>
        </div>
    </div>

    <?php if (!empty($bisSuccess)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($bisSuccess); ?>
    </div>
    <?php endif; ?>

    <?php if ($bisPage === 'detail' && $bisDetail): ?>
        <!-- Chi tiết sản phẩm + subscribers -->
        <div style="display:flex;align-items:center;gap:16px;background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);padding:20px;margin-bottom:20px;">
            <?php if (!empty($bisDetail['image'])): ?>
            <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($bisDetail['image']); ?>"
                 style="width:64px;height:64px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;flex-shrink:0;">
            <?php else: ?>
            <div style="width:64px;height:64px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;">📦</div>
            <?php endif; ?>
            <div style="flex:1;">
                <h3 style="margin:0 0 4px;font-size:16px;font-weight:800;color:var(--text-primary);"><?php echo htmlspecialchars($bisDetail['name']); ?></h3>
                <div style="display:flex;gap:16px;font-size:13px;color:var(--text-muted);">
                    <span>Tồn kho: <strong style="<?php echo (int)$bisDetail['quantity'] > 0 ? 'color:#16a34a;' : 'color:#ef4444;'; ?>"><?php echo (int)$bisDetail['quantity']; ?></strong></span>
                    <span>Đăng ký chờ: <strong style="color:#6366f1;"><?php echo count($bisSubs); ?></strong></span>
                    <?php if ($bisSentCount > 0): ?>
                    <span>Đã gửi: <strong style="color:#16a34a;"><?php echo $bisSentCount; ?></strong></span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <a href="?page=products&edit_id=<?php echo $bisDetail['id']; ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Cập nhật kho
                </a>
                <a href="?page=back_in_stock" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <!-- Danh sách subscribers -->
        <div style="background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
                <h3 style="margin:0;font-size:14px;font-weight:800;color:var(--text-primary);">
                    <i class="fas fa-envelope" style="margin-right:6px;color:#6366f1;"></i>
                    Danh sách đăng ký (<?php echo count($bisSubs); ?>)
                </h3>
                <?php if (!empty($bisSubs) && isset($_GET['notify_all'])): ?>
                <div style="font-size:12px;color:var(--text-muted);background:#fffbeb;border:1px solid #fde68a;padding:6px 12px;border-radius:8px;">
                    ⚠️ Chỉ gửi thông báo khi sản phẩm đã có hàng!
                </div>
                <?php endif; ?>
            </div>
            <?php if (empty($bisSubs)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--text-faint);">
                <p style="font-size:40px;margin:0 0 10px;">🔕</p>
                <p style="font-size:15px;font-weight:600;margin:0;">Chưa có ai đăng ký nhận thông báo cho sản phẩm này.</p>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="admin-table" style="margin:0;">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Email</th>
                        <th>User</th>
                        <th>Trạng thái</th>
                        <th>Đăng ký lúc</th>
                        <th>Thông báo lúc</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bisSubs as $i => $s): ?>
                    <tr>
                        <td style="color:var(--text-faint);font-weight:700;"><?php echo $i + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($s['email']); ?></strong></td>
                        <td>
                            <?php if (!empty($s['user_id'])): ?>
                            <a href="?page=users&id=<?php echo (int)$s['user_id']; ?>" style="color:#6366f1;">
                                <i class="fas fa-user"></i> #<?php echo (int)$s['user_id']; ?>
                            </a>
                            <?php else: ?>
                            <span style="color:var(--text-faint);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['status'] === 'pending'): ?>
                            <span style="background:rgba(245,158,11,0.12);color:#fbbf24;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                                ⏳ Chờ gửi
                            </span>
                            <?php elseif ($s['status'] === 'notified'): ?>
                            <span style="background:rgba(34,197,94,0.12);color:#4ade80;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                                ✅ Đã gửi
                            </span>
                            <?php else: ?>
                            <span style="background:rgba(239,68,68,0.12);color:#f87171;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                                ❌ Đã hủy
                            </span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);"><?php echo date('d/m/Y H:i', strtotime($s['created_at'])); ?></td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            <?php echo $s['notified_at'] ? date('d/m/Y H:i', strtotime($s['notified_at'])) : '—'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Danh sách sản phẩm có subscriber -->
        <div style="background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);">
                <h3 style="margin:0;font-size:14px;font-weight:800;color:var(--text-primary);">
                    <i class="fas fa-box" style="margin-right:6px;color:#6366f1;"></i>
                    Sản phẩm có người đăng ký chờ
                </h3>
            </div>
            <?php if (empty($bisProducts)): ?>
            <div style="text-align:center;padding:60px 20px;color:var(--text-faint);">
                <p style="font-size:48px;margin:0 0 12px;">🔕</p>
                <p style="font-size:16px;font-weight:600;margin:0 0 4px;">Chưa có sản phẩm nào có đăng ký báo khi có hàng</p>
                <p style="font-size:13px;color:var(--text-muted);margin:0;">Khi khách hàng đăng ký trên trang sản phẩm hết hàng, họ sẽ xuất hiện ở đây.</p>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="admin-table" style="margin:0;">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Sản phẩm</th>
                        <th>Tồn kho</th>
                        <th>Đăng ký chờ</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bisProducts as $bp): ?>
                    <tr>
                        <td>
                            <?php if (!empty($bp['image'])): ?>
                            <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($bp['image']); ?>"
                                 style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                            <?php else: ?>
                            <span style="font-size:24px;">📦</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=back_in_stock&id=<?php echo (int)$bp['id']; ?>" style="font-weight:600;color:var(--text-primary);text-decoration:none;">
                                <?php echo htmlspecialchars($bp['name']); ?>
                            </a>
                        </td>
                        <td>
                            <span style="font-weight:700;<?php echo (int)$bp['quantity'] > 0 ? 'color:#16a34a;' : 'color:#ef4444;'; ?>">
                                <?php echo (int)$bp['quantity']; ?>
                            </span>
                            <?php if ((int)$bp['quantity'] > 0): ?>
                            <span style="font-size:11px;color:#16a34a;margin-left:4px;">✅ Có hàng</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background:rgba(99,102,241,0.12);color:#818cf8;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:700;">
                                <?php echo (int)$bp['subscriber_count']; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="?page=back_in_stock&id=<?php echo (int)$bp['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="?page=products&edit_id=<?php echo (int)$bp['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Cập nhật kho
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
