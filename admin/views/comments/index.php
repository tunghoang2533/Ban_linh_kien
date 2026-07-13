<?php
$productComments      = isset($productComments) && is_array($productComments) ? $productComments : [];
$commentStats         = isset($commentStats)     && is_array($commentStats)    ? $commentStats    : [];
$commentFilterStatus  = $commentFilterStatus ?? 'all';
$commentFilterRating  = $commentFilterRating ?? 'all';
$commentFilterQ       = $commentFilterQ      ?? '';

$hasFilter = $commentFilterStatus !== 'all' || $commentFilterRating !== 'all' || $commentFilterQ !== '';

// Build base URL giữ nguyên filter khi action
function commentFilterUrl($extra = []) {
    global $commentFilterStatus, $commentFilterRating, $commentFilterQ;
    $params = array_filter([
        'page'   => 'comments',
        'status' => $commentFilterStatus !== 'all' ? $commentFilterStatus : '',
        'rating' => $commentFilterRating !== 'all' ? $commentFilterRating : '',
        'q'      => $commentFilterQ,
    ]);
    return '?' . http_build_query(array_merge($params, $extra));
}
?>
<style>
/* ── Comments Page ── */
.cmt-stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
@media(max-width:900px){ .cmt-stats-row { grid-template-columns: repeat(3,1fr); } }
@media(max-width:600px){ .cmt-stats-row { grid-template-columns: repeat(2,1fr); } }

.cmt-stat {
    background: var(--bg-surface);
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 2px 12px rgba(15,23,42,0.07);
    border: 1px solid rgba(148,163,184,0.12);
    display: flex; align-items: center; gap: 12px;
    transition: transform .18s, box-shadow .18s;
}
.cmt-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(15,23,42,0.12); }
.cmt-stat-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.cmt-stat-val { font-size: 22px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.cmt-stat-lbl { font-size: 11px; font-weight: 600; color: var(--text-faint); margin-top: 3px; }

/* Filter bar */
.cmt-filter-bar {
    background: var(--bg-surface);
    border-radius: 16px;
    padding: 18px 22px;
    margin-bottom: 20px;
    box-shadow: 0 2px 12px rgba(15,23,42,0.06);
    border: 1px solid rgba(148,163,184,0.12);
    display: flex; flex-wrap: wrap; gap: 12px; align-items: center;
}
.cmt-filter-search {
    position: relative; flex: 1; min-width: 200px;
}
.cmt-filter-search i {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%); color: var(--text-faint); font-size: 13px;
    pointer-events: none;
}
.cmt-filter-search input {
    width: 100%; padding: 9px 12px 9px 36px;
    border: 1px solid var(--border-muted); border-radius: 10px;
    font-size: 13px; font-family: inherit; background: var(--bg-elevated); color: var(--text-primary);
    transition: border-color .2s, box-shadow .2s;
}
.cmt-filter-search input:focus {
    outline: none; border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: var(--bg-surface);
}
.cmt-select {
    padding: 9px 12px; border: 1px solid var(--border-muted); border-radius: 10px;
    font-size: 13px; font-family: inherit; background: var(--bg-elevated); color: var(--text-primary);
    cursor: pointer; transition: border-color .2s;
}
.cmt-select:focus { outline: none; border-color: #6366f1; }

/* Status tabs */
.cmt-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 18px; }
.cmt-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700;
    text-decoration: none; border: 1px solid var(--border-muted);
    background: var(--bg-elevated); color: var(--text-muted); transition: all .18s; white-space: nowrap;
}
.cmt-tab:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,.08); }
.cmt-tab.tab-all.active       { background: #6366f1; color: white; border-color: #6366f1; }
.cmt-tab.tab-visible.active   { background: rgba(34,197,94,0.12); color: #4ade80; border-color: #6ee7b7; }
.cmt-tab.tab-hidden.active    { background: rgba(239,68,68,0.12); color: #f87171; border-color: #fca5a5; }
.tab-cnt { background: rgba(0,0,0,.1); border-radius: 20px; padding: 1px 7px; font-size: 10px; }

/* Comment card grid */
.cmt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 16px;
}
@media(max-width:600px){ .cmt-grid { grid-template-columns: 1fr; } }

.cmt-card {
    background: var(--bg-surface);
    border-radius: 16px;
    border: 1px solid rgba(148,163,184,0.14);
    box-shadow: 0 2px 12px rgba(15,23,42,0.06);
    overflow: hidden;
    transition: transform .18s, box-shadow .18s;
    position: relative;
}
.cmt-card:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(15,23,42,0.12); }
.cmt-card.is-hidden {
    opacity: .65;
    border-color: #fecaca;
    background: #fff5f5;
}
.cmt-card-header {
    padding: 14px 18px 0;
    display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
}
.cmt-user-row { display: flex; align-items: center; gap: 10px; }
.cmt-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 15px; font-weight: 800; flex-shrink: 0;
}
.cmt-username { font-weight: 700; font-size: 14px; color: var(--text-primary); }
.cmt-stars { display: flex; gap: 2px; margin-top: 3px; }
.cmt-stars i { font-size: 11px; }

.cmt-hidden-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700;
    background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid #fecaca;
    white-space: nowrap;
}

.cmt-body { padding: 12px 18px; }
.cmt-product-tag {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(99,102,241,0.12); color: #6366f1; border-radius: 6px;
    padding: 3px 10px; font-size: 11px; font-weight: 700;
    margin-bottom: 10px; max-width: 100%;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.cmt-text {
    font-size: 13px; color: var(--text-secondary); line-height: 1.7;
    background: var(--bg-elevated); border-radius: 8px; padding: 10px 14px;
    border-left: 3px solid #e2e8f0;
}

.cmt-footer {
    padding: 10px 18px 14px;
    display: flex; align-items: center; justify-content: space-between;
    border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 8px;
}
.cmt-date { font-size: 11px; color: var(--text-faint); display: flex; align-items: center; gap: 4px; }
.cmt-actions { display: flex; gap: 6px; }

/* Delete confirm modal */
.del-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 9000;
    align-items: center; justify-content: center;
}
.del-modal-overlay.open { display: flex; }
.del-modal {
    background: var(--bg-surface); border-radius: 20px;
    padding: 32px; max-width: 400px; width: 90%;
    box-shadow: 0 24px 80px rgba(0,0,0,.22);
    animation: popIn .25s cubic-bezier(.34,1.56,.64,1);
    text-align: center;
}
@keyframes popIn { from{opacity:0;transform:scale(.85)} to{opacity:1;transform:scale(1)} }
.del-modal-icon { font-size: 52px; margin-bottom: 14px; }
.del-modal h3 { font-size: 20px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
.del-modal p  { font-size: 14px; color: var(--text-muted); margin-bottom: 22px; line-height: 1.6; }
.del-modal-actions { display: flex; gap: 10px; }
.del-modal-actions .btn { flex: 1; justify-content: center; }

/* Empty state */
.cmt-empty {
    background: var(--bg-surface); border-radius: 20px;
    border: 1px solid rgba(148,163,184,.14);
    padding: 60px 24px; text-align: center;
}
.cmt-empty i { font-size: 52px; color: #e2e8f0; display: block; margin-bottom: 16px; }
.cmt-empty p  { font-size: 16px; font-weight: 700; color: var(--text-faint); }
.cmt-empty small { font-size: 13px; color: #cbd5e1; }
</style>

<main class="admin-main">

    <!-- Page header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Quản lý bình luận</h1>
            <p>Duyệt, ẩn và xoá bình luận từ khách hàng</p>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <!-- ── Stats row ── -->
    <?php if (!empty($commentStats)): ?>
    <div class="cmt-stats-row">
        <div class="cmt-stat">
            <div class="cmt-stat-icon" style="background:rgba(99,102,241,0.12);">
                <i class="fas fa-comments" style="color:#6366f1;"></i>
            </div>
            <div>
                <div class="cmt-stat-val"><?php echo number_format($commentStats['total'] ?? 0); ?></div>
                <div class="cmt-stat-lbl">Tổng bình luận</div>
            </div>
        </div>
        <div class="cmt-stat">
            <div class="cmt-stat-icon" style="background:rgba(34,197,94,0.12);">
                <i class="fas fa-eye" style="color:#10b981;"></i>
            </div>
            <div>
                <div class="cmt-stat-val"><?php echo number_format($commentStats['visible'] ?? 0); ?></div>
                <div class="cmt-stat-lbl">Đang hiện</div>
            </div>
        </div>
        <div class="cmt-stat">
            <div class="cmt-stat-icon" style="background:rgba(239,68,68,0.12);">
                <i class="fas fa-eye-slash" style="color:#ef4444;"></i>
            </div>
            <div>
                <div class="cmt-stat-val"><?php echo number_format($commentStats['hidden'] ?? 0); ?></div>
                <div class="cmt-stat-lbl">Đã ẩn</div>
            </div>
        </div>
        <div class="cmt-stat">
            <div class="cmt-stat-icon" style="background:rgba(245,158,11,0.12);">
                <i class="fas fa-star" style="color:#f59e0b;"></i>
            </div>
            <div>
                <div class="cmt-stat-val"><?php echo number_format($commentStats['avg_rating'] ?? 0, 1); ?></div>
                <div class="cmt-stat-lbl">Đánh giá TB</div>
            </div>
        </div>
        <div class="cmt-stat">
            <div class="cmt-stat-icon" style="background:rgba(239,68,68,0.12);">
                <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
            </div>
            <div>
                <div class="cmt-stat-val"><?php echo number_format($commentStats['star_low'] ?? 0); ?></div>
                <div class="cmt-stat-lbl">Đánh giá thấp (≤2★)</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Filter bar ── -->
    <form method="GET" id="cmtFilterForm" action="">
        <input type="hidden" name="page" value="comments">
        <div class="cmt-filter-bar">
            <!-- Search -->
            <div class="cmt-filter-search">
                <i class="fas fa-search"></i>
                <input type="text" name="q" placeholder="Tìm theo tên, nội dung, sản phẩm..."
                       value="<?php echo htmlspecialchars($commentFilterQ); ?>"
                       autocomplete="off">
            </div>
            <!-- Rating filter -->
            <select name="rating" class="cmt-select" onchange="this.form.submit()">
                <option value="all"   <?php echo $commentFilterRating === 'all' ? 'selected' : ''; ?>>⭐ Tất cả sao</option>
                <option value="5"     <?php echo $commentFilterRating === '5'   ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ 5 sao</option>
                <option value="4"     <?php echo $commentFilterRating === '4'   ? 'selected' : ''; ?>>⭐⭐⭐⭐ 4 sao</option>
                <option value="3"     <?php echo $commentFilterRating === '3'   ? 'selected' : ''; ?>>⭐⭐⭐ 3 sao</option>
                <option value="2"     <?php echo $commentFilterRating === '2'   ? 'selected' : ''; ?>>⭐⭐ 2 sao</option>
                <option value="1"     <?php echo $commentFilterRating === '1'   ? 'selected' : ''; ?>>⭐ 1 sao</option>
            </select>
            <!-- Submit & clear -->
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
            <?php if ($hasFilter): ?>
                <a href="?page=comments" class="btn btn-secondary"><i class="fas fa-times"></i> Xoá lọc</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ── Status tabs ── -->
    <div class="cmt-tabs">
        <?php
        $tabs = [
            'all'     => ['label' => 'Tất cả',       'cnt' => ($commentStats['total']   ?? 0), 'class' => 'tab-all'],
            'visible' => ['label' => 'Đang hiện',    'cnt' => ($commentStats['visible'] ?? 0), 'class' => 'tab-visible'],
            'hidden'  => ['label' => 'Đã ẩn',        'cnt' => ($commentStats['hidden']  ?? 0), 'class' => 'tab-hidden'],
        ];
        foreach ($tabs as $val => $tab):
            $isActive = ($commentFilterStatus === $val);
            $qs = http_build_query(array_filter([
                'page'   => 'comments',
                'status' => $val,
                'rating' => $commentFilterRating !== 'all' ? $commentFilterRating : '',
                'q'      => $commentFilterQ,
            ]));
        ?>
            <a href="?<?php echo $qs; ?>"
               class="cmt-tab <?php echo $tab['class']; ?> <?php echo $isActive ? 'active' : ''; ?>">
                <?php echo $tab['label']; ?>
                <span class="tab-cnt"><?php echo $tab['cnt']; ?></span>
            </a>
        <?php endforeach; ?>

        <!-- Kết quả hiện tại -->
        <span style="margin-left:auto;font-size:13px;color:var(--text-muted);display:flex;align-items:center;gap:4px;">
            Hiển thị <strong style="color:var(--text-primary);margin:0 3px;"><?php echo count($productComments); ?></strong> bình luận
        </span>
    </div>

    <!-- ── Cards ── -->
    <?php if (empty($productComments)): ?>
        <div class="cmt-empty">
            <i class="fas fa-comment-slash"></i>
            <p>Không có bình luận nào</p>
            <small><?php echo $hasFilter ? 'Thử thay đổi bộ lọc để xem thêm' : 'Bình luận của khách hàng sẽ xuất hiện tại đây'; ?></small>
        </div>
    <?php else: ?>
        <div class="cmt-grid" id="cmtGrid">
            <?php foreach ($productComments as $comment):
                $isHidden  = !empty($comment['is_hidden']);
                $authorName = $comment['name'] ?: ($comment['full_name'] ?? 'Khách hàng');
                $initial    = strtoupper(mb_substr($authorName, 0, 1));
                $rating     = intval($comment['rating']);
                $avatarGrad = $isHidden
                    ? 'linear-gradient(135deg,#94a3b8,#64748b)'
                    : 'linear-gradient(135deg,#ec4899,#8b5cf6)';
            ?>
            <div class="cmt-card <?php echo $isHidden ? 'is-hidden' : ''; ?>" id="cmt-<?php echo $comment['id']; ?>">
                <!-- Header -->
                <div class="cmt-card-header">
                    <div class="cmt-user-row">
                        <div class="cmt-avatar" style="background:<?php echo $avatarGrad; ?>;">
                            <?php echo $initial; ?>
                        </div>
                        <div>
                            <div class="cmt-username"><?php echo htmlspecialchars($authorName); ?></div>
                            <div class="cmt-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color:<?php echo $i <= $rating ? '#fbbf24' : '#e2e8f0'; ?>;"></i>
                                <?php endfor; ?>
                                <span style="font-size:11px;color:var(--text-faint);margin-left:4px;"><?php echo $rating; ?>/5</span>
                            </div>
                        </div>
                    </div>
                    <?php if ($isHidden): ?>
                        <span class="cmt-hidden-badge"><i class="fas fa-eye-slash"></i> Đã ẩn</span>
                    <?php endif; ?>
                </div>

                <!-- Body -->
                <div class="cmt-body">
                    <div class="cmt-product-tag" title="<?php echo htmlspecialchars($comment['product_name'] ?? ''); ?>">
                        <i class="fas fa-tag"></i>
                        <?php echo htmlspecialchars(mb_strimwidth($comment['product_name'] ?? 'N/A', 0, 45, '...')); ?>
                    </div>
                    <div class="cmt-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                </div>

                <!-- Footer -->
                <div class="cmt-footer">
                    <div class="cmt-date">
                        <i class="fas fa-clock"></i>
                        <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>
                    </div>
                    <div class="cmt-actions">
                        <?php if ($isHidden): ?>
                            <!-- Mở lại bình luận -->
                            <a href="<?php echo commentFilterUrl(['action' => 'show', 'id' => $comment['id']]); ?>"
                               class="btn btn-sm btn-success" title="Hiện lại bình luận">
                                <i class="fas fa-eye"></i> Hiện
                            </a>
                        <?php else: ?>
                            <!-- Ẩn bình luận -->
                            <a href="<?php echo commentFilterUrl(['action' => 'hide', 'id' => $comment['id']]); ?>"
                               class="btn btn-sm btn-warning" title="Ẩn bình luận"
                               onclick="return confirm('Ẩn bình luận này? Khách hàng sẽ không thấy nó nữa.')">
                                <i class="fas fa-eye-slash"></i> Ẩn
                            </a>
                        <?php endif; ?>
                        <!-- Xoá bình luận -->
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="openDeleteModal(<?php echo $comment['id']; ?>, '<?php echo addslashes(mb_strimwidth($authorName, 0, 20, '...')); ?>')"
                                title="Xoá vĩnh viễn">
                            <i class="fas fa-trash"></i> Xoá
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- ── Delete confirm modal ── -->
<div class="del-modal-overlay" id="deleteModal">
    <div class="del-modal">
        <div class="del-modal-icon">🗑️</div>
        <h3>Xoá bình luận?</h3>
        <p id="deleteModalText">Hành động này không thể hoàn tác.</p>
        <div class="del-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i> Huỷ
            </button>
            <a id="deleteModalLink" href="#" class="btn btn-danger">
                <i class="fas fa-trash"></i> Xoá vĩnh viễn
            </a>
        </div>
    </div>
</div>

<script>
var cmtBaseUrl = <?php echo json_encode(
    '?page=comments&action=delete' .
    ($commentFilterStatus !== 'all' ? '&status=' . urlencode($commentFilterStatus) : '') .
    ($commentFilterRating !== 'all' ? '&rating=' . urlencode($commentFilterRating) : '') .
    ($commentFilterQ      !== ''    ? '&q='      . urlencode($commentFilterQ)      : '')
); ?>;

function openDeleteModal(id, name) {
    document.getElementById('deleteModalText').textContent =
        'Bạn đang xoá bình luận của "' + name + '". Hành động này không thể hoàn tác.';
    document.getElementById('deleteModalLink').href = cmtBaseUrl + '&id=' + id;
    document.getElementById('deleteModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

// Enter to submit search
document.querySelector('.cmt-filter-search input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('cmtFilterForm').submit(); }
});
</script>
