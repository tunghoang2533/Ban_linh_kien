<?php
$users = $admin->getUsers();
// Đếm số user bị khoá
$blockedCount = array_sum(array_column($users, 'is_blocked'));
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Quản lý người dùng</h1>
            <p>Tổng cộng <strong><?php echo count($users); ?></strong> người dùng
                <?php if ($blockedCount > 0): ?>
                    &nbsp;·&nbsp;<span style="color:#ef4444;font-weight:700;"><?php echo $blockedCount; ?> bị khoá</span>
                <?php endif; ?>
            </p>
        </div>
        <!-- Search box -->
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="position:relative;">
                <input type="text" id="userSearch" placeholder="Tìm kiếm người dùng..."
                    style="padding:9px 12px 9px 38px;border-radius:10px;border:1px solid var(--border-subtle);
                           font-size:13px;width:240px;background:var(--bg-elevated);color:var(--text-primary);font-family:inherit;"
                    oninput="filterUsers(this.value)"
                    onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.12)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-faint);font-size:13px;pointer-events:none;"></i>
            </div>
            <select id="statusFilter" onchange="filterUsers(document.getElementById('userSearch').value)"
                style="padding:9px 12px;border-radius:10px;border:1px solid var(--border-subtle);font-size:13px;
                       background:var(--bg-elevated);color:var(--text-primary);font-family:inherit;cursor:pointer;">
                <option value="all">Tất cả</option>
                <option value="active">Hoạt động</option>
                <option value="blocked">Đã khoá</option>
            </select>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="admin-table" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Đơn hàng</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user):
                    $isBlocked = !empty($user['is_blocked']);
                ?>
                    <tr data-name="<?php echo strtolower(htmlspecialchars($user['full_name'] . ' ' . $user['username'] . ' ' . $user['email'])); ?>"
                        data-status="<?php echo $isBlocked ? 'blocked' : 'active'; ?>"
                        style="<?php echo $isBlocked ? 'opacity:0.72;' : ''; ?>">

                        <td><span style="font-weight:700;color:#6366f1;">#<?php echo $user['id']; ?></span></td>

                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:38px;height:38px;border-radius:50%;
                                    background:<?php echo $isBlocked ? 'linear-gradient(135deg,#ef4444,#f97316)' : 'linear-gradient(135deg,#10b981,#06b6d4)'; ?>;
                                    display:flex;align-items:center;justify-content:center;
                                    color:white;font-size:14px;font-weight:700;flex-shrink:0;position:relative;">
                                    <?php echo strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)); ?>
                                    <?php if ($isBlocked): ?>
                                        <span style="position:absolute;bottom:-2px;right:-2px;background:#ef4444;border:2px solid white;
                                            border-radius:50%;width:14px;height:14px;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-lock" style="font-size:7px;color:white;"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></div>
                                    <?php if (!empty($user['username'])): ?>
                                        <div style="font-size:12px;color:var(--text-faint);">@<?php echo htmlspecialchars($user['username']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <td style="color:var(--text-muted);"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                        <td style="color:var(--text-muted);"><?php echo htmlspecialchars($user['phone'] ?? '—'); ?></td>

                        <td>
                            <span style="font-weight:700;color:#6366f1;"><?php echo number_format($user['order_count'] ?? 0); ?></span>
                            <?php if (($user['order_count'] ?? 0) > 0): ?>
                                <span style="font-size:11px;color:var(--text-faint);"> đơn</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($isBlocked): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 12px;border-radius:20px;
                                    font-size:11px;font-weight:700;background:rgba(239,68,68,0.12);color:#f87171;border:1px solid #fecaca;"
                                    <?php if (!empty($user['blocked_reason'])): ?>
                                        title="Lý do: <?php echo htmlspecialchars($user['blocked_reason']); ?>"
                                    <?php endif; ?>>
                                    <i class="fas fa-lock"></i> Đã khoá
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 12px;border-radius:20px;
                                    font-size:11px;font-weight:700;background:rgba(34,197,94,0.12);color:#4ade80;border:1px solid #a7f3d0;">
                                    <i class="fas fa-check-circle"></i> Hoạt động
                                </span>
                            <?php endif; ?>
                        </td>

                        <td style="color:var(--text-muted);"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>

                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="?page=users&action=view&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info"
                                   title="Xem chi tiết">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                                <?php if ($isBlocked): ?>
                                    <form method="POST" action="?page=users&action=unblock&id=<?php echo $user['id']; ?>" style="margin:0;"
                                          onsubmit="return confirm('Mở khoá tài khoản này?')">
                                        <button type="submit" class="btn btn-sm btn-success" title="Mở khoá">
                                            <i class="fas fa-lock-open"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="?page=users&action=view&id=<?php echo $user['id']; ?>#block-section"
                                       class="btn btn-sm btn-danger" title="Khoá tài khoản">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function filterUsers(q) {
    q = q.toLowerCase().trim();
    const statusVal = document.getElementById('statusFilter').value;
    document.querySelectorAll('#usersTable tbody tr').forEach(function(row) {
        const name    = row.dataset.name || '';
        const status  = row.dataset.status || '';
        const matchQ  = !q || name.includes(q);
        const matchSt = statusVal === 'all' || status === statusVal;
        row.style.display = (matchQ && matchSt) ? '' : 'none';
    });
}
</script>
