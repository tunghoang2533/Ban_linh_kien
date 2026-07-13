<?php
/**
 * Admin: Quản lý điểm tích lũy khách hàng
 * URL: admin/?page=loyalty
 */

// Top users theo điểm
$topUsers = $db->query("
    SELECT u.id, u.full_name, u.email, u.phone,
           COALESCE(SUM(lp.points), 0) AS balance,
           SUM(CASE WHEN lp.type='earned'   THEN lp.points ELSE 0 END) AS total_earned,
           SUM(CASE WHEN lp.type='redeemed' THEN ABS(lp.points) ELSE 0 END) AS total_redeemed
    FROM users u
    LEFT JOIN loyalty_points lp ON lp.user_id = u.id
    GROUP BY u.id, u.full_name, u.email, u.phone
    HAVING balance > 0
    ORDER BY balance DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

// Tất cả users cho dropdown
$allUsers = $db->query("SELECT id, full_name, email FROM users ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stats = $db->query("
    SELECT
        COUNT(DISTINCT user_id)                         AS users_with_points,
        SUM(CASE WHEN points > 0 THEN points ELSE 0 END) AS total_earned,
        SUM(CASE WHEN points < 0 THEN ABS(points) ELSE 0 END) AS total_redeemed
    FROM loyalty_points
")->fetch(PDO::FETCH_ASSOC);
?>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-star" style="color:#f59e0b;"></i> Tích điểm khách hàng</h1>
            <p>Quản lý điểm tích lũy — 1 điểm = 1.000đ</p>
        </div>
        <a href="<?php echo BASE_URL; ?>loyalty.php" target="_blank" class="btn btn-secondary">
            <i class="fas fa-eye"></i> Xem trang khách
        </a>
    </div>

    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <?php
        $ls = [
            ['Khách có điểm',  number_format($stats['users_with_points']??0),     '#6366f1','#f5f3ff','fa-users'],
            ['Tổng đã tích',   number_format($stats['total_earned']??0).' điểm',  '#16a34a','#f0fdf4','fa-arrow-up'],
            ['Tổng đã dùng',   number_format($stats['total_redeemed']??0).' điểm','#f59e0b','#fffbeb','fa-arrow-down'],
        ];
        foreach ($ls as [$lbl,$val,$clr,$bg,$ico]):
        ?>
        <div style="background:var(--bg-surface);border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:center;gap:16px;">
            <div style="width:48px;height:48px;border-radius:14px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas <?php echo $ico; ?>" style="color:<?php echo $clr; ?>;font-size:20px;"></i>
            </div>
            <div>
                <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:var(--text-faint);text-transform:uppercase;"><?php echo $lbl; ?></p>
                <p style="margin:0;font-size:22px;font-weight:900;color:var(--text-primary);"><?php echo $val; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

        <!-- Bảng top users -->
        <div style="background:var(--bg-surface);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;">
            <div style="padding:18px 22px;border-bottom:1px solid var(--border-subtle);">
                <h3 style="margin:0;font-size:15px;font-weight:800;color:var(--text-primary);">🏆 Top khách hàng có điểm</h3>
            </div>
            <div style="overflow-x:auto;">
            <table class="admin-table" style="margin:0;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Khách hàng</th>
                        <th style="text-align:center;">Điểm hiện có</th>
                        <th style="text-align:center;">Đã tích</th>
                        <th style="text-align:center;">Đã dùng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($topUsers)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-faint);">Chưa có dữ liệu điểm</td></tr>
                <?php else: foreach ($topUsers as $i => $u):
                    $tierColor = $u['balance'] >= 5000 ? '#f59e0b' : ($u['balance'] >= 1000 ? '#64748b' : '#b45309');
                ?>
                <tr>
                    <td style="color:var(--text-faint);font-size:12px;"><?php echo $i+1; ?></td>
                    <td>
                        <p style="margin:0;font-size:13px;font-weight:700;color:var(--text-primary);"><?php echo htmlspecialchars($u['full_name']); ?></p>
                        <p style="margin:0;font-size:12px;color:var(--text-faint);"><?php echo htmlspecialchars($u['email']); ?></p>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-size:18px;font-weight:900;color:<?php echo $tierColor; ?>;"><?php echo number_format($u['balance']); ?></span>
                        <p style="margin:0;font-size:11px;color:var(--text-faint);"><?php echo number_format($u['balance']*1000,0,',','.'); ?>đ</p>
                    </td>
                    <td style="text-align:center;color:#16a34a;font-weight:700;">+<?php echo number_format($u['total_earned']); ?></td>
                    <td style="text-align:center;color:#dc2626;font-weight:700;">-<?php echo number_format($u['total_redeemed']); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>admin/?page=loyalty&view_user=<?php echo $u['id']; ?>"
                           class="btn btn-sm" style="font-size:12px;padding:4px 10px;">
                            <i class="fas fa-history"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Form điều chỉnh điểm -->
        <div>
        <div class="form-card" style="max-width:100%;margin:0;">
            <h2 class="form-section-title"><i class="fas fa-edit" style="color:#6366f1;"></i> Điều chỉnh điểm thủ công</h2>
            <form method="POST" action="?page=loyalty&action=adjust">
                <div class="form-group">
                    <label class="form-label">Khách hàng <span style="color:#e10c00">*</span></label>
                    <select name="user_id" class="form-control" required>
                        <option value="">— Chọn khách hàng —</option>
                        <?php foreach ($allUsers as $u): ?>
                        <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['full_name']); ?> (<?php echo htmlspecialchars($u['email']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Số điểm <span style="font-size:11px;color:var(--text-faint);">(âm để trừ, dương để cộng)</span></label>
                    <input type="number" name="points" class="form-control" required placeholder="VD: 100 hoặc -50">
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <input type="text" name="note" class="form-control" placeholder="Lý do điều chỉnh...">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-save"></i> Lưu điều chỉnh
                </button>
            </form>
        </div>

        <!-- Hướng dẫn -->
        <div style="background:#eff6ff;border-radius:14px;padding:16px 18px;margin-top:16px;">
            <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1d4ed8;"><i class="fas fa-info-circle"></i> Quy tắc tích điểm</p>
            <ul style="margin:0;padding-left:18px;font-size:12px;color:#1e40af;line-height:2;">
                <li>Mỗi đơn hàng hoàn thành → tích <strong>1 điểm / 1.000đ</strong></li>
                <li>Tối thiểu <strong>50 điểm</strong> mới được dùng</li>
                <li>Giảm tối đa <strong>30%</strong> giá trị đơn bằng điểm</li>
                <li>Điểm <strong>không có hạn</strong> sử dụng</li>
            </ul>
        </div>
        </div>
    </div>

    <?php
    // Xem lịch sử của 1 user cụ thể
    if (!empty($_GET['view_user'])):
        $vu = intval($_GET['view_user']);
        $vuInfo = $db->prepare("SELECT full_name, email FROM users WHERE id=?");
        $vuInfo->execute([$vu]);
        $vuInfo = $vuInfo->fetch(PDO::FETCH_ASSOC);
        $vuHistory = $db->prepare("SELECT * FROM loyalty_points WHERE user_id=? ORDER BY created_at DESC LIMIT 30");
        $vuHistory->execute([$vu]);
        $vuHistory = $vuHistory->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div style="margin-top:24px;background:var(--bg-surface);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:15px;font-weight:800;color:var(--text-primary);">
                Lịch sử điểm: <?php echo htmlspecialchars($vuInfo['full_name'] ?? ''); ?>
            </h3>
            <a href="?page=loyalty" class="btn btn-sm btn-secondary">Đóng</a>
        </div>
        <table class="admin-table" style="margin:0;">
            <thead><tr><th>Thời gian</th><th>Mô tả</th><th>Loại</th><th style="text-align:right;">Điểm</th></tr></thead>
            <tbody>
            <?php foreach ($vuHistory as $h):
                $isPos = $h['points'] > 0;
            ?>
            <tr>
                <td style="font-size:12px;color:var(--text-muted);"><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($h['note'] ?: $h['type']); ?></td>
                <td><span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;background:<?php echo $isPos?'#f0fdf4':'#fef2f2'; ?>;color:<?php echo $isPos?'#16a34a':'#dc2626'; ?>;"><?php echo $h['type']; ?></span></td>
                <td style="text-align:right;font-weight:800;font-size:15px;color:<?php echo $isPos?'#16a34a':'#dc2626'; ?>;">
                    <?php echo ($isPos?'+':'').number_format($h['points']); ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
