<style>
/* =============================================
   LỊCH SỬ MUA HÀNG - PREMIUM STYLE
   ============================================= */
.hs-wrapper {
    background: #f4f6fa;
    min-height: 100vh;
    padding: 40px 0 60px;
}
.hs-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 20px;
}
.hs-title {
    font-size: 26px;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.hs-title i { color: #288ad6; }
.hs-subtitle {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 20px;
}

/* === FILTER TABS === */
.hs-filter-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.hs-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 99px;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.hs-filter-btn:hover {
    border-color: #288ad6;
    color: #288ad6;
}
.hs-filter-btn.active {
    background: #288ad6;
    border-color: #288ad6;
    color: #fff;
    box-shadow: 0 4px 14px rgba(40,138,214,0.3);
}

/* === ORDER CARD === */
.hs-order-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    margin-bottom: 18px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(40,138,214,0.06);
    transition: box-shadow .22s, transform .22s;
}
.hs-order-card:hover {
    box-shadow: 0 8px 32px rgba(40,138,214,0.13);
    transform: translateY(-2px);
}

.hs-order-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: linear-gradient(90deg, #f0f7ff 0%, #fafcff 100%);
    border-bottom: 1px solid #e8f0fb;
    gap: 12px;
    flex-wrap: wrap;
}
.hs-order-id {
    font-size: 15px;
    font-weight: 800;
    color: #288ad6;
}
.hs-order-date {
    font-size: 13px;
    color: #64748b;
    margin-left: 4px;
}
.hs-status-badge {
    padding: 5px 14px;
    border-radius: 99px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
}
.hs-status-pending    { background: #fff7e0; color: #b45309; }
.hs-status-processing { background: #eff6ff; color: #1d4ed8; }
.hs-status-shipped    { background: #f5f3ff; color: #7c3aed; }
.hs-status-completed  { background: #d1fae5; color: #059669; }
.hs-status-cancelled  { background: #fee2e2; color: #dc2626; }

.hs-order-body {
    padding: 18px 24px 16px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.hs-order-info p {
    margin: 0 0 5px;
    font-size: 14px;
    color: #334155;
}
.hs-order-info strong { color: #1e293b; }
.hs-order-total {
    font-size: 18px;
    font-weight: 900;
    color: #e10c00;
    margin-top: 4px;
}

.hs-detail-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 10px;
    background: linear-gradient(135deg, #288ad6, #1a6eb5);
    color: #fff !important;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 700;
    white-space: nowrap;
    transition: opacity .2s, transform .2s, box-shadow .2s;
    box-shadow: 0 4px 12px rgba(40,138,214,0.25);
    flex-shrink: 0;
}
.hs-detail-btn:hover {
    opacity: .92;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(40,138,214,0.35);
}

/* === INVOICE BUTTON === */
.hs-invoice-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 10px;
    background: #eef2ff;
    color: #4f46e5 !important;
    border: 1.5px solid #c7d2fe;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s;
    white-space: nowrap;
    flex-shrink: 0;
}
.hs-invoice-btn:hover {
    background: #4f46e5;
    color: #fff !important;
    border-color: #4f46e5;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(79,70,229,0.25);
}

/* === EMPTY STATE === */
.hs-empty {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}
.hs-empty-icon {
    font-size: 60px;
    margin-bottom: 16px;
    display: block;
    opacity: .5;
}
.hs-empty h3 { color: #64748b; font-size: 18px; margin-bottom: 8px; }
.hs-empty a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    padding: 12px 28px;
    background: #288ad6;
    color: #fff;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    transition: background .2s;
}
.hs-empty a:hover { background: #1a6eb5; }

/* === CANCEL BUTTON === */
.hs-cancel-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 10px;
    background: #fff0f0;
    color: #dc2626;
    border: 1.5px solid #fecaca;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s, border-color .2s, transform .15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.hs-cancel-btn:hover {
    background: #fee2e2;
    border-color: #dc2626;
    transform: translateY(-1px);
}

/* === PAGINATION === */
.hs-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 32px 0 16px;
    flex-wrap: wrap;
}
.hs-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 10px;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s;
    cursor: pointer;
    user-select: none;
}
.hs-page-btn:hover:not(.disabled):not(.active) {
    border-color: #288ad6;
    color: #288ad6;
    background: #f0f7ff;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(40,138,214,0.15);
}
.hs-page-btn.active {
    background: #288ad6;
    border-color: #288ad6;
    color: #fff;
    box-shadow: 0 4px 12px rgba(40,138,214,0.3);
}
.hs-page-btn.disabled {
    opacity: .35;
    cursor: not-allowed;
    background: #f8fafc;
}
.hs-page-dots {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    color: #94a3b8;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 2px;
}

/* === NOTIFY === */
.hs-notify {
    padding: 14px 20px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}
.hs-notify.success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.hs-notify.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

/* === CONFIRM MODAL === */
.cancel-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.cancel-overlay.active { display: flex; }
.cancel-modal {
    background: #fff;
    border-radius: 20px;
    padding: 32px 28px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: popIn .25s ease;
}
@keyframes popIn {
    from { transform: scale(.9); opacity: 0; }
    to   { transform: scale(1);  opacity: 1; }
}
.cancel-modal .icon { font-size: 44px; margin-bottom: 12px; }
.cancel-modal h3 { margin: 0 0 8px; font-size: 20px; color: #1e293b; }
.cancel-modal p  { margin: 0 0 24px; color: #64748b; font-size: 14px; line-height: 1.6; }
.cancel-modal-btns { display: flex; gap: 12px; justify-content: center; }
.cancel-modal-btns .btn-no {
    padding: 11px 24px;
    border-radius: 10px;
    border: 1.5px solid #d1d5db;
    background: #f9fafb;
    color: #374151;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s;
}
.cancel-modal-btns .btn-no:hover { background: #f3f4f6; }
.cancel-modal-btns .btn-yes {
    padding: 11px 24px;
    border-radius: 10px;
    border: none;
    background: #dc2626;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s;
}
.cancel-modal-btns .btn-yes:hover { background: #b91c1c; }
</style>

<div class="hs-wrapper">
    <div class="hs-container">
        <h2 class="hs-title"><i class="fa fa-history"></i> Lịch sử mua hàng</h2>
        <p class="hs-subtitle">Theo dõi các đơn hàng bạn đã đặt</p>

        <!-- Bộ lọc trạng thái (server-side, kết hợp phân trang) -->
        <?php $currentFilter = $status; ?>
        <div class="hs-filter-tabs">
            <a href="?page=1" class="hs-filter-btn <?php echo is_null($currentFilter) ? 'active' : ''; ?>"><i class="fa fa-list"></i> Tất cả</a>
            <a href="?status=pending&page=1" class="hs-filter-btn <?php echo $currentFilter === 'pending' ? 'active' : ''; ?>"><i class="fa fa-clock-o"></i> Chờ xử lý</a>
            <a href="?status=processing&page=1" class="hs-filter-btn <?php echo $currentFilter === 'processing' ? 'active' : ''; ?>"><i class="fa fa-cogs"></i> Đang xử lý</a>
            <a href="?status=shipped&page=1" class="hs-filter-btn <?php echo $currentFilter === 'shipped' ? 'active' : ''; ?>"><i class="fa fa-truck"></i> Đang giao</a>
            <a href="?status=completed&page=1" class="hs-filter-btn <?php echo $currentFilter === 'completed' ? 'active' : ''; ?>"><i class="fa fa-check-circle"></i> Hoàn thành</a>
            <a href="?status=cancelled&page=1" class="hs-filter-btn <?php echo $currentFilter === 'cancelled' ? 'active' : ''; ?>"><i class="fa fa-times-circle"></i> Đã hủy</a>
        </div>

        <?php if (!empty($_SESSION['cancel_success'])): ?>
            <div class="hs-notify success"><i class="fa fa-check-circle"></i><?php echo htmlspecialchars($_SESSION['cancel_success']); unset($_SESSION['cancel_success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['cancel_error'])): ?>
            <div class="hs-notify error"><i class="fa fa-exclamation-circle"></i><?php echo htmlspecialchars($_SESSION['cancel_error']); unset($_SESSION['cancel_error']); ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="hs-empty">
                <?php if ($currentFilter): ?>
                    <span class="hs-empty-icon">📭</span>
                    <h3>Không có đơn hàng nào</h3>
                    <p>Bạn chưa có đơn hàng nào ở trạng thái này.</p>
                    <a href="?page=1"><i class="fa fa-list"></i> Xem tất cả đơn hàng</a>
                <?php else: ?>
                    <span class="hs-empty-icon">🛒</span>
                    <h3>Bạn chưa có đơn hàng nào</h3>
                    <p>Hãy khám phá các sản phẩm hấp dẫn của chúng tôi!</p>
                    <a href="index.php"><i class="fa fa-shopping-bag"></i> Mua sắm ngay</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <?php
                    $orderSt = strtolower($order['status']);
                    // Đơn đã thanh toán thì luôn là đang xử lý
                    if (($order['payment_status'] ?? '') === 'paid' && $orderSt === 'pending') {
                        $orderSt = 'processing';
                    }
                    $statusText = [
                        'pending'    => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'shipped'    => 'Đang giao hàng',
                        'completed'  => 'Hoàn thành',
                        'cancelled'  => 'Đã hủy'
                    ];
                    $displayStatus = $statusText[$orderSt] ?? strtoupper($orderSt);

                    // Phương thức thanh toán
                    $methodNames = [
                        'cod'   => 'Tiền mặt khi nhận hàng (COD)',
                        'bank'  => 'Chuyển khoản VietQR (MB Bank)',
                        'vnpay' => 'VNPay (VNPAY-QR)'
                    ];
                    $pmKey = strtolower($order['payment_method'] ?? 'cod');
                    $displayMethod = $methodNames[$pmKey] ?? strtoupper($pmKey);
                    $pStatus = strtolower($order['payment_status'] ?? 'unpaid');
                ?>
                <div class="hs-order-card">
                    <!-- Header đơn hàng -->
                    <div class="hs-order-head">
                        <div>
                            <span class="hs-order-id">Đơn hàng #<?php echo $order['id']; ?></span>
                            <span class="hs-order-date"> &nbsp;|&nbsp; Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <span class="hs-status-badge hs-status-<?php echo $orderSt; ?>"><?php echo $displayStatus; ?></span>
                    </div>

                    <!-- Body đơn hàng -->
                    <div class="hs-order-body">
                        <div class="hs-order-info">
                            <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['customer_name']); ?> &mdash; <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                            <p>
                                <strong>Phương thức TT:</strong> <?php echo htmlspecialchars($displayMethod); ?>
                            </p>
                            <p>
                                <strong>Trạng thái TT:</strong> 
                                <?php if ($pStatus === 'paid'): ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;font-size:12px;font-weight:700;background:#d1fae5;color:#059669;border:1px solid #a7f3d0;">
                                        <i class="fa fa-check-circle"></i> Đã thanh toán
                                    </span>
                                <?php elseif ($pStatus === 'refunded'): ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;font-size:12px;font-weight:700;background:#fee2e2;color:#dc2626;border:1px solid #fecaca;">
                                        <i class="fa fa-undo"></i> Đã hoàn tiền
                                    </span>
                                <?php else: ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;font-size:12px;font-weight:700;background:#fef3c7;color:#b45309;border:1px solid #fde68a;">
                                        <i class="fa fa-clock-o"></i> Chưa thanh toán
                                    </span>
                                <?php endif; ?>
                            </p>
                            <div class="hs-order-total">Tổng tiền: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>&#8363;</div>
                        </div>

                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                            <!-- Nút tiếp tục thanh toán QR (nếu chưa thanh toán) -->
                            <?php if ($pmKey === 'bank' && $pStatus !== 'paid' && $orderSt === 'pending'): ?>
                            <a href="thanhtoan_success.php?id=<?php echo $order['id']; ?>"
                               style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;background:#ecfdf5;color:#059669;border:1.5px solid #a7f3d0;font-size:13px;font-weight:700;text-decoration:none;"
                               title="Quét mã QR thanh toán MB Bank">
                                <i class="fa fa-qrcode"></i> Thanh toán ngay
                            </a>
                            <?php endif; ?>
                            <!-- Nút hủy đơn (chỉ hiện khi pending) -->
                            <?php if ($orderSt === 'pending'): ?>
                            <button class="hs-cancel-btn" onclick="openCancelModal(<?php echo $order['id']; ?>)">
                                <i class="fa fa-times"></i> Hủy đơn
                            </button>
                            <?php endif; ?>
                            <!-- Nút đổi trả (chỉ hiện khi completed) -->
                            <?php if ($orderSt === 'completed'): ?>
                            <a href="<?php echo BASE_URL; ?>doisanpham.php?order_id=<?php echo $order['id']; ?>"
                               style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;transition:.15s;"
                               onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                <i class="fa fa-undo"></i> Đổi trả / BH
                            </a>
                            <?php endif; ?>
                            <!-- Nút in hóa đơn -->
                            <a href="inhoadon.php?id=<?php echo $order['id']; ?>" target="_blank" class="hs-invoice-btn" title="Xem và in hóa đơn">
                                <i class="fa fa-print"></i> In hóa đơn
                            </a>
                            <!-- Nút xem chi tiết -->
                            <a href="chitietdonhang.php?id=<?php echo $order['id']; ?>" class="hs-detail-btn">
                                <i class="fa fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination (giữ nguyên status filter) -->
            <?php
                $paginationQuery = $currentFilter ? 'status=' . urlencode($currentFilter) . '&' : '';
            ?>
            <?php if ($totalPages > 1): ?>
            <div class="hs-pagination">
                <!-- Nút Trang trước -->
                <?php if ($page > 1): ?>
                    <a href="?<?php echo $paginationQuery; ?>page=<?php echo $page - 1; ?>" class="hs-page-btn" title="Trang trước">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="hs-page-btn disabled"><i class="fa fa-chevron-left"></i></span>
                <?php endif; ?>

                <!-- Các trang -->
                <?php
                    $startPage = max(1, $page - 2);
                    $endPage   = min($totalPages, $page + 2);

                    if ($startPage > 1) {
                        echo '<a href="?' . $paginationQuery . 'page=1" class="hs-page-btn">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="hs-page-dots">...</span>';
                        }
                    }

                    for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <a href="?<?php echo $paginationQuery; ?>page=<?php echo $i; ?>" class="hs-page-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="hs-page-dots">...</span>';
                        }
                        echo '<a href="?' . $paginationQuery . 'page=' . $totalPages . '" class="hs-page-btn">' . $totalPages . '</a>';
                    }
                ?>

                <!-- Nút Trang sau -->
                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo $paginationQuery; ?>page=<?php echo $page + 1; ?>" class="hs-page-btn" title="Trang sau">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="hs-page-btn disabled"><i class="fa fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xác nhận hủy đơn -->
<div class="cancel-overlay" id="cancelOverlay">
    <div class="cancel-modal">
        <div class="icon">⚠️</div>
        <h3>Xác nhận hủy đơn?</h3>
        <p>Bạn có chắc chắn muốn hủy đơn hàng này không?<br><strong>Hành động này không thể hoàn tác.</strong></p>
        <div class="cancel-modal-btns">
            <button class="btn-no" onclick="closeCancelModal()">Không, giữ lại</button>
            <form id="cancelForm" method="POST" action="huydondang.php" style="margin:0;">
                <?php echo CsrfHelper::field(); ?>
                <input type="hidden" name="order_id" id="cancelOrderId">
                <button type="submit" class="btn-yes"><i class="fa fa-times"></i> Có, hủy đơn</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openCancelModal(orderId) {
        document.getElementById('cancelOrderId').value = orderId;
        document.getElementById('cancelOverlay').classList.add('active');
    }
    function closeCancelModal() {
        document.getElementById('cancelOverlay').classList.remove('active');
    }
    // Click ra ngoài modal để đóng
    document.getElementById('cancelOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeCancelModal();
    });

    // Bộ lọc trạng thái đã chuyển sang server-side (link-based) để tương thích phân trang
</script>