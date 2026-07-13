<?php
/* ─────────────────────────────────────────
   Chi tiết người dùng + khoá / mở tài khoản
   ───────────────────────────────────────── */
$userDetail = isset($userDetail) ? $userDetail : null;
$userStats  = isset($userStats)  ? $userStats  : [];
$userOrders = isset($userOrders) ? $userOrders : [];

$statusMap = [
    'pending'    => 'Chờ xử lý',
    'processing' => 'Đang xử lý',
    'shipped'    => 'Đang giao',
    'completed'  => 'Hoàn thành',
    'cancelled'  => 'Đã hủy',
];
$statusColor = [
    'pending'    => '#f59e0b',
    'processing' => '#6366f1',
    'shipped'    => '#06b6d4',
    'completed'  => '#10b981',
    'cancelled'  => '#ef4444',
];
?>
<style>
/* ── User Detail Page ── */
.ud-hero {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
    border-radius: 20px;
    padding: 32px;
    color: white;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(99,102,241,0.28);
}
.ud-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(0,0,0,0.05);
}
.ud-hero::after {
    content: '';
    position: absolute;
    bottom: -50px; left: 30%;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(0,0,0,0.06);
}
.ud-avatar {
    width: 88px; height: 88px;
    border-radius: 50%;
    background: rgba(0,0,0,0.08);
    border: 3px solid rgba(0,0,0,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 36px; font-weight: 800;
    flex-shrink: 0;
    position: relative; z-index: 1;
}
.ud-hero-info { flex: 1; position: relative; z-index: 1; }
.ud-hero-info h2 { font-size: 24px; font-weight: 800; margin-bottom: 4px; }
.ud-hero-info .ud-username { opacity: 0.75; font-size: 14px; margin-bottom: 12px; }
.ud-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; position: relative; z-index: 1; }

.ud-status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
    margin-bottom: 8px;
}
.ud-status-active  { background: rgba(16,185,129,0.2); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }
.ud-status-blocked { background: rgba(239,68,68,0.2);  color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }

/* Stats row */
.ud-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
@media(max-width:900px){ .ud-stats { grid-template-columns: repeat(2,1fr); } }
.ud-stat {
    background: var(--bg-surface);
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: none;
    border: 1px solid rgba(148,163,184,0.12);
    display: flex; align-items: center; gap: 14px;
}
.ud-stat-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.ud-stat-val { font-size: 22px; font-weight: 800; color: var(--text-primary); }
.ud-stat-lbl { font-size: 12px; color: var(--text-faint); font-weight: 600; margin-top: 2px; }

/* Grid layout */
.ud-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; align-items: start; }
@media(max-width:1000px){ .ud-grid { grid-template-columns: 1fr; } }

/* Info card */
.ud-card {
    background: var(--bg-surface);
    border-radius: 16px;
    box-shadow: none;
    border: 1px solid rgba(148,163,184,0.12);
    overflow: hidden;
}
.ud-card-header {
    padding: 16px 22px;
    border-bottom: 1px solid var(--border-subtle);
    font-weight: 700; font-size: 14px; color: var(--text-primary);
    display: flex; align-items: center; gap: 8px;
}
.ud-card-body { padding: 20px 22px; }

.ud-info-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f8fafc;
}
.ud-info-row:last-child { border-bottom: none; }
.ud-info-label { font-size: 12px; color: var(--text-faint); font-weight: 600; min-width: 110px; padding-top: 2px; }
.ud-info-value { font-size: 14px; color: var(--text-primary); font-weight: 500; flex: 1; word-break: break-all; }

/* Block form */
.block-form { background: #fef2f2; border-radius: 12px; padding: 16px; margin-top: 16px; border: 1px solid #fecaca; }
.block-form label { font-size: 13px; font-weight: 600; color: #f87171; margin-bottom: 6px; display: block; }
.block-form textarea {
    width: 100%; padding: 8px 12px; border-radius: 8px;
    border: 1px solid #fca5a5; font-size: 13px; resize: vertical;
    min-height: 70px; background: var(--bg-surface); color: var(--text-primary);
    font-family: inherit;
}
.block-form textarea:focus { outline: none; border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.12); }

/* Order table inside detail */
.ud-order-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.ud-order-table th {
    background: var(--bg-elevated); padding: 10px 14px;
    text-align: left; font-size: 11px; font-weight: 700;
    color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border-subtle);
}
.ud-order-table td {
    padding: 10px 14px; border-bottom: 1px solid var(--border-subtle);
    color: var(--text-secondary); vertical-align: middle;
}
.ud-order-table tr:last-child td { border-bottom: none; }
.ud-order-table tr:hover td { background: var(--bg-elevated); }

/* Confirm modal */
.ud-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 9000;
    align-items: center; justify-content: center;
}
.ud-modal-overlay.open { display: flex; }
.ud-modal {
    background: var(--bg-surface); border-radius: 20px;
    padding: 32px; max-width: 440px; width: 90%;
    box-shadow: 0 24px 80px rgba(0,0,0,0.22);
    animation: popIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes popIn {
    from { opacity:0; transform:scale(0.85); }
    to   { opacity:1; transform:scale(1); }
}
.ud-modal-icon { font-size: 48px; margin-bottom: 16px; text-align: center; }
.ud-modal h3 { font-size: 20px; font-weight: 800; color: var(--text-primary); text-align: center; margin-bottom: 8px; }
.ud-modal p { font-size: 14px; color: var(--text-muted); text-align: center; margin-bottom: 20px; line-height: 1.6; }
.ud-modal-actions { display: flex; gap: 10px; }
.ud-modal-actions .btn { flex: 1; justify-content: center; }

.blocked-banner {
    background: linear-gradient(135deg,#fef2f2,#fff5f5);
    border: 1.5px solid #fecaca;
    border-radius: 12px; padding: 14px 18px;
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 20px;
}
.blocked-banner i { color: #ef4444; font-size: 20px; flex-shrink: 0; }
.blocked-banner-text strong { font-size: 14px; color: #f87171; }
.blocked-banner-text p { font-size: 12px; color: #b91c1c; margin-top: 2px; }
</style>

<main class="admin-main">
    <!-- Page header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Chi tiết người dùng</h1>
            <p>Thông tin tài khoản và lịch sử hoạt động</p>
        </div>
        <a href="?page=users" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <?php if (!$userDetail): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> Không tìm thấy người dùng.
        </div>
    <?php else:
        $isBlocked = !empty($userDetail['is_blocked']);
        $initials  = strtoupper(mb_substr($userDetail['full_name'] ?? $userDetail['username'] ?? 'U', 0, 1));
    ?>

    <!-- Hero Banner -->
    <div class="ud-hero">
        <div class="ud-avatar"><?php echo $initials; ?></div>
        <div class="ud-hero-info">
            <div class="ud-status-badge <?php echo $isBlocked ? 'ud-status-blocked' : 'ud-status-active'; ?>">
                <i class="fas fa-<?php echo $isBlocked ? 'lock' : 'check-circle'; ?>"></i>
                <?php echo $isBlocked ? 'Đã bị khoá' : 'Hoạt động'; ?>
            </div>
            <h2><?php echo htmlspecialchars($userDetail['full_name'] ?? 'Chưa cập nhật'); ?></h2>
            <div class="ud-username">@<?php echo htmlspecialchars($userDetail['username'] ?? '—'); ?> · #<?php echo $userDetail['id']; ?></div>
        </div>
        <div class="ud-hero-actions">
            <?php if ($isBlocked): ?>
                <form method="POST" action="?page=users&action=unblock&id=<?php echo $userDetail['id']; ?>" style="margin:0;">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Mở khoá tài khoản này?')">
                        <i class="fas fa-lock-open"></i> Mở khoá
                    </button>
                </form>
            <?php else: ?>
                <button type="button" class="btn btn-danger" onclick="openBlockModal()">
                    <i class="fas fa-ban"></i> Khoá tài khoản
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Blocked reason banner -->
    <?php if ($isBlocked && !empty($userDetail['blocked_reason'])): ?>
    <div class="blocked-banner">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="blocked-banner-text">
            <strong>Lý do bị khoá:</strong>
            <p><?php echo htmlspecialchars($userDetail['blocked_reason']); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="ud-stats">
        <div class="ud-stat">
            <div class="ud-stat-icon" style="background:rgba(99,102,241,0.12);">
                <i class="fas fa-shopping-bag" style="color:#6366f1;"></i>
            </div>
            <div>
                <div class="ud-stat-val"><?php echo number_format($userStats['total_orders'] ?? 0); ?></div>
                <div class="ud-stat-lbl">Tổng đơn hàng</div>
            </div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat-icon" style="background:rgba(34,197,94,0.12);">
                <i class="fas fa-wallet" style="color:#10b981;"></i>
            </div>
            <div>
                <div class="ud-stat-val" style="font-size:16px;"><?php echo number_format($userStats['total_spent'] ?? 0, 0, ',', '.'); ?> ₫</div>
                <div class="ud-stat-lbl">Đã chi tiêu</div>
            </div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat-icon" style="background:rgba(245,158,11,0.12);">
                <i class="fas fa-clock" style="color:#f59e0b;"></i>
            </div>
            <div>
                <div class="ud-stat-val"><?php echo number_format($userStats['pending_orders'] ?? 0); ?></div>
                <div class="ud-stat-lbl">Đang xử lý</div>
            </div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat-icon" style="background:rgba(239,68,68,0.12);">
                <i class="fas fa-times-circle" style="color:#ef4444;"></i>
            </div>
            <div>
                <div class="ud-stat-val"><?php echo number_format($userStats['cancelled_orders'] ?? 0); ?></div>
                <div class="ud-stat-lbl">Đã huỷ</div>
            </div>
        </div>
    </div>

    <!-- Main grid -->
    <div class="ud-grid">

        <!-- Left: info card -->
        <div class="ud-card">
            <div class="ud-card-header">
                <i class="fas fa-id-card" style="color:#6366f1;"></i> Thông tin cá nhân
            </div>
            <div class="ud-card-body">
                <div class="ud-info-row">
                    <span class="ud-info-label"><i class="fas fa-user" style="color:var(--text-faint);margin-right:5px;"></i>Họ tên</span>
                    <span class="ud-info-value"><?php echo htmlspecialchars($userDetail['full_name'] ?? '—'); ?></span>
                </div>
                <div class="ud-info-row">
                    <span class="ud-info-label"><i class="fas fa-at" style="color:var(--text-faint);margin-right:5px;"></i>Username</span>
                    <span class="ud-info-value">@<?php echo htmlspecialchars($userDetail['username'] ?? '—'); ?></span>
                </div>
                <div class="ud-info-row">
                    <span class="ud-info-label"><i class="fas fa-envelope" style="color:var(--text-faint);margin-right:5px;"></i>Email</span>
                    <span class="ud-info-value"><?php echo htmlspecialchars($userDetail['email'] ?? '—'); ?></span>
                </div>
                <div class="ud-info-row">
                    <span class="ud-info-label"><i class="fas fa-phone" style="color:var(--text-faint);margin-right:5px;"></i>Điện thoại</span>
                    <span class="ud-info-value"><?php echo htmlspecialchars($userDetail['phone'] ?? '—'); ?></span>
                </div>
                <div class="ud-info-row">
                    <span class="ud-info-label"><i class="fas fa-map-marker-alt" style="color:var(--text-faint);margin-right:5px;"></i>Địa chỉ</span>
                    <span class="ud-info-value"><?php echo htmlspecialchars($userDetail['address'] ?? '—'); ?></span>
                </div>
                <div class="ud-info-row">
                    <span class="ud-info-label"><i class="fas fa-calendar" style="color:var(--text-faint);margin-right:5px;"></i>Ngày tạo</span>
                    <span class="ud-info-value"><?php echo isset($userDetail['created_at']) ? date('d/m/Y H:i', strtotime($userDetail['created_at'])) : '—'; ?></span>
                </div>
                <div class="ud-info-row">
                    <span class="ud-info-label"><i class="fas fa-shield-alt" style="color:var(--text-faint);margin-right:5px;"></i>Trạng thái</span>
                    <span class="ud-info-value">
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;
                            <?php echo $isBlocked ? 'background:rgba(239,68,68,0.12);color:#f87171;' : 'background:rgba(34,197,94,0.12);color:#4ade80;'; ?>">
                            <i class="fas fa-<?php echo $isBlocked ? 'lock' : 'check-circle'; ?>"></i>
                            <?php echo $isBlocked ? 'Đã khoá' : 'Hoạt động'; ?>
                        </span>
                    </span>
                </div>

                <?php if (!$isBlocked): ?>
                <!-- Block form inline -->
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-danger" style="width:100%;" onclick="openBlockModal()">
                        <i class="fas fa-ban"></i> Khoá tài khoản này
                    </button>
                </div>
                <?php else: ?>
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;">
                    <form method="POST" action="?page=users&action=unblock&id=<?php echo $userDetail['id']; ?>">
                        <button type="submit" class="btn btn-success" style="width:100%;" onclick="return confirm('Xác nhận mở khoá tài khoản này?')">
                            <i class="fas fa-lock-open"></i> Mở khoá tài khoản
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: order history + personal vouchers -->
        <?php
        // Láº¥y voucher cÃ¡ nhÃ¢n cho user nÃ y
        $personalVouchers = [];
        try {
            $pvStmt = $db->prepare("SELECT * FROM vouchers WHERE user_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 10");
            $pvStmt->execute([$userDetail['id'] ?? 0]);
            $personalVouchers = $pvStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {}
        ?>
        <div class="ud-card">
            <div class="ud-card-header">
                <i class="fas fa-history" style="color:#6366f1;"></i>
                Lịch sử đơn hàng
                <span style="margin-left:auto;background:rgba(99,102,241,0.12);color:#6366f1;font-size:11px;padding:2px 10px;border-radius:20px;font-weight:700;">
                    <?php echo count($userOrders); ?> đơn
                </span>
            </div>
            <div class="ud-card-body" style="padding:0;">
                <?php if (empty($userOrders)): ?>
                    <div style="padding:40px;text-align:center;color:var(--text-faint);">
                        <i class="fas fa-shopping-bag" style="font-size:36px;margin-bottom:12px;display:block;opacity:0.3;"></i>
                        Chưa có đơn hàng nào
                    </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="ud-order-table">
                        <thead>
                            <tr>
                                <th>Mã ĐH</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userOrders as $ord): ?>
                            <tr>
                                <td><span style="font-weight:700;color:#6366f1;">#<?php echo $ord['id']; ?></span></td>
                                <td style="color:var(--text-muted);"><?php echo date('d/m/Y', strtotime($ord['created_at'])); ?></td>
                                <td style="font-weight:700;color:var(--text-primary);"><?php echo number_format($ord['total_amount'], 0, ',', '.'); ?> ₫</td>
                                <td>
                                    <?php
                                    $st = strtolower($ord['status']);
                                    $col = $statusColor[$st] ?? '#64748b';
                                    $lbl = $statusMap[$st] ?? $ord['status'];
                                    ?>
                                    <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;
                                        background:<?php echo $col; ?>22;color:<?php echo $col; ?>;border:1px solid <?php echo $col; ?>44;">
                                        <?php echo $lbl; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?page=orders&action=detail&id=<?php echo $ord['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <!-- Personal Vouchers Card -->
    <?php if (!empty($personalVouchers)): ?>
    <div class="ud-card" style="margin-top:24px;">
        <div class="ud-card-header">
            <i class="fas fa-ticket-alt" style="color:#f59e0b;"></i>
            Voucher cá nhân
            <span style="margin-left:auto;background:rgba(245,158,11,0.12);color:#fbbf24;font-size:11px;padding:2px 10px;border-radius:20px;font-weight:700;">
                <?php echo count($personalVouchers); ?> mã
            </span>
        </div>
        <div class="ud-card-body" style="padding:0;">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;padding:16px 20px;">
                <?php foreach ($personalVouchers as $pv): 
                    $expired = strtotime($pv['expire_date']) < time();
                ?>
                <div style="border:1px solid <?php echo $expired ? 'var(--border-subtle)' : 'rgba(245,158,11,0.3)'; ?>;border-radius:12px;padding:14px 16px;background:<?php echo $expired ? 'var(--bg-elevated)' : 'rgba(255,255,255,0.5)'; ?>;">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-family:monospace;font-weight:900;font-size:15px;color:<?php echo $expired ? 'var(--text-faint)' : '#4f46e5'; ?>;letter-spacing:.05em;">
                            <?php echo htmlspecialchars($pv['code']); ?>
                        </span>
                        <?php if ($expired): ?>
                            <span style="font-size:10px;color:#ef4444;font-weight:700;">HẾT HẠN</span>
                        <?php else: ?>
                            <span style="font-size:10px;color:#10b981;font-weight:700;">CÒN HIỆU LỰC</span>
                        <?php endif; ?>
                    </div>
                    <p style="margin:6px 0 0;font-size:13px;font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($pv['name']); ?></p>
                    <?php if (!empty($pv['personal_note'])): ?>
                    <p style="margin:4px 0 0;font-size:12px;color:var(--text-muted);font-style:italic;">
                        <i class="fas fa-comment"></i> <?php echo htmlspecialchars($pv['personal_note']); ?>
                    </p>
                    <?php endif; ?>
                    <div style="margin-top:8px;display:flex;gap:12px;font-size:11px;color:var(--text-faint);">
                        <span>HSD: <?php echo date('d/m/Y', strtotime($pv['expire_date'])); ?></span>
                        <span>Đã dùng: <?php echo intval($pv['used_count']); ?>/<?php echo $pv['usage_limit'] ?? '∞'; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- Block Confirm Modal -->
<div class="ud-modal-overlay" id="blockModal">
    <div class="ud-modal">
        <div class="ud-modal-icon">🔒</div>
        <h3>Khoá tài khoản</h3>
        <p>Tài khoản này sẽ bị khoá và không thể đăng nhập. Bạn có thể mở khoá bất cứ lúc nào.</p>

        <form method="POST" action="?page=users&action=block&id=<?php echo $userDetail['id'] ?? 0; ?>">
            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">
                    <i class="fas fa-comment-alt" style="color:#6366f1;"></i> Lý do khoá <span style="color:var(--text-faint);font-weight:400;">(tuỳ chọn)</span>
                </label>
                <textarea name="blocked_reason" placeholder="VD: Vi phạm điều khoản sử dụng, spam, v.v."
                    style="width:100%;padding:10px;border:1px solid var(--border-subtle);border-radius:10px;font-size:13px;
                           min-height:80px;resize:vertical;font-family:inherit;color:var(--text-primary);"
                    onfocus="this.style.borderColor='#ef4444';this.style.boxShadow='0 0 0 3px rgba(239,68,68,0.12)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"></textarea>
            </div>
            <div class="ud-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeBlockModal()">
                    <i class="fas fa-times"></i> Huỷ
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban"></i> Xác nhận khoá
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openBlockModal() {
    document.getElementById('blockModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeBlockModal() {
    document.getElementById('blockModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('blockModal').addEventListener('click', function(e) {
    if (e.target === this) closeBlockModal();
});
</script>
