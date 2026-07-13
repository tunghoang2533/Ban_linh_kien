<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Helpers\LoyaltyHelper;

if (!isset($_SESSION['user'])) {
    header('Location: taikhoan.php');
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user']['id'];
$balance = LoyaltyHelper::getBalance($db, $userId);
$history = LoyaltyHelper::getHistory($db, $userId, 30);

include 'app/views/header.php';
?>
<style>
.loyalty-page { max-width:800px; margin:40px auto; padding:0 20px; }
.loyalty-card { background:linear-gradient(135deg,#4f46e5,#6366f1); border-radius:20px; padding:32px; color:#fff; box-shadow:0 8px 30px rgba(99,102,241,.3); }
.loyalty-card h2 { margin:0; font-size:14px; opacity:.8; font-weight:600; text-transform:uppercase; letter-spacing:1px; }
.loyalty-card .points { font-size:48px; font-weight:900; margin:8px 0 4px; }
.loyalty-card .points-label { margin:0; font-size:14px; opacity:.75; }
.loyalty-history { margin-top:28px; }
.loyalty-history h3 { margin:0 0 16px; font-size:18px; }
.history-item { display:flex; justify-content:space-between; align-items:center; padding:14px 16px; border-bottom:1px solid #f1f5f9; }
.history-item:last-child { border-bottom:none; }
.history-points { font-weight:800; font-size:15px; }
.history-points.earned { color:#22c55e; }
.history-points.redeemed { color:#ef4444; }
.history-points.adjusted { color:#f59e0b; }
.history-note { color:#64748b; font-size:13px; }
.history-date { color:#94a3b8; font-size:12px; }
</style>
<div class="loyalty-page">
    <div class="loyalty-card">
        <h2><i class="fa fa-star"></i> Điểm tích lũy</h2>
        <div class="points"><?php echo number_format($balance, 0, ',', '.'); ?></div>
        <p class="points-label">điểm (1.000đ = 1 điểm)</p>
        <p style="margin:12px 0 0;font-size:13px;opacity:.8;">Có thể đổi khi đạt tối thiểu 50 điểm</p>
    </div>
    <div class="loyalty-history">
        <h3><i class="fa fa-clock-rotate-left"></i> Lịch sử giao dịch</h3>
        <div style="background:#fff;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);overflow:hidden;">
            <?php if (empty($history)): ?>
                <p style="text-align:center;padding:40px;color:#94a3b8;">Chưa có giao dịch nào.</p>
            <?php else: ?>
                <?php foreach ($history as $h): ?>
                    <div class="history-item">
                        <div>
                            <div class="history-note"><?php echo htmlspecialchars($h['note'] ?? ''); ?></div>
                            <div class="history-date"><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></div>
                        </div>
                        <div class="history-points <?php echo $h['type']; ?>">
                            <?php echo ((int)$h['points'] > 0 ? '+' : '') . number_format((int)$h['points'], 0, ',', '.'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'app/views/footer.php'; ?>
