<?php
if (!isset($buildCategories)) $buildCategories = [];
$totalPrice   = 0;
$selectedCount = isset($_SESSION['buildpc']) ? count(array_filter($_SESSION['buildpc'])) : 0;
?>

<!-- ════════════════════════════════════════
     SELECTOR PANEL (slide-in modal)
════════════════════════════════════════ -->
<style>
.selector-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;display:none;align-items:center;justify-content:center;}
.selector-overlay.open{display:flex;}
.selector-panel{width:900px;max-width:calc(100vw - 24px);height:86vh;max-height:680px;background:#fff;
    display:flex;flex-direction:column;border-radius:16px;overflow:hidden;
    box-shadow:0 24px 60px rgba(0,0,0,.28);
    opacity:0;transform:scale(.95) translateY(10px);transition:opacity .22s ease,transform .22s ease;z-index:9001;}
.selector-overlay.open .selector-panel{opacity:1;transform:scale(1) translateY(0);}
.sp-header{background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;padding:16px 22px;
    display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.sp-header h3{font-size:16px;font-weight:700;margin:0;}
.sp-close-btn{background:rgba(255,255,255,.2);border:none;color:#fff;width:32px;height:32px;
    border-radius:50%;cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;}
.sp-close-btn:hover{background:rgba(255,255,255,.35);}
.sp-body{display:flex;flex:1;overflow:hidden;}
/* Filter sidebar */
.sp-filter{width:200px;flex-shrink:0;border-right:1px solid #e8edf3;padding:16px 14px;
    overflow-y:auto;background:#fafbfc;}
.sp-filter .ftitle{font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase;
    letter-spacing:.8px;margin-bottom:14px;}
.sp-filter .fsec{margin-bottom:16px;}
.sp-filter .fsec h4{font-size:12px;font-weight:700;color:#334155;margin-bottom:8px;}
.sp-filter label{display:flex;align-items:center;gap:6px;font-size:12px;color:#475569;
    margin-bottom:5px;cursor:pointer;}
.sp-filter input[type=radio]{accent-color:#0284c7;}
/* Product list */
.sp-products{flex:1;overflow-y:auto;padding:14px 16px;}
.sp-searchbar{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
.sp-searchbar input{flex:1;padding:9px 13px;border:1.5px solid #e2e8f0;border-radius:9px;
    font-size:13px;outline:none;}
.sp-searchbar input:focus{border-color:#0284c7;}
.sock-badge{background:#fef9c3;color:#92400e;padding:6px 12px;border-radius:6px;
    font-size:12px;font-weight:600;margin-bottom:10px;display:none;}
.sp-loading{text-align:center;padding:50px;color:#94a3b8;font-size:14px;}
.sp-empty{text-align:center;padding:50px;color:#94a3b8;}
.sp-empty i{font-size:40px;display:block;margin-bottom:10px;color:#cbd5e1;}
.prow{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #e8edf3;
    border-radius:12px;padding:12px 16px;margin-bottom:8px;transition:border-color .15s,box-shadow .15s;}
.prow:hover{border-color:#bae6fd;box-shadow:0 2px 12px rgba(2,132,199,.08);}
.prow img{width:64px;height:64px;object-fit:contain;border-radius:8px;border:1px solid #e8edf3;
    flex-shrink:0;background:#f8fafc;}
.pinfo{flex:1;min-width:0;}
.pname{font-size:13px;font-weight:600;color:#1e293b;margin-bottom:3px;}
.pprice{font-size:15px;font-weight:700;color:#e10c00;}
.psock{display:inline-block;background:#eff6ff;color:#0284c7;padding:2px 8px;border-radius:5px;
    font-size:11px;font-weight:600;margin-bottom:3px;}
.btn-padd{background:#0284c7;color:#fff;border:none;padding:9px 16px;border-radius:9px;
    cursor:pointer;font-size:13px;font-weight:600;white-space:nowrap;flex-shrink:0;
    display:inline-flex;align-items:center;gap:5px;transition:background .15s;}
.btn-padd:hover{background:#0369a1;}
.btn-padd.added{background:#16a34a;}

/* ══════════════════════════════════════════════════
   NEW 3-COLUMN BUILD PC LAYOUT
   ══════════════════════════════════════════════════ */
.bpc-main-container {
    max-width: 1280px;
    margin: 25px auto 60px;
    padding: 0 16px;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.bpc-top-header {
    margin-bottom: 22px;
}
.bpc-top-header h1 {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bpc-top-header p {
    color: #64748b;
    font-size: 13.5px;
    margin: 0;
}
.bpc-grid-layout {
    display: grid;
    grid-template-columns: 285px 1fr 285px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 1200px) {
    .bpc-grid-layout {
        grid-template-columns: 260px 1fr 260px;
        gap: 16px;
    }
}
@media (max-width: 992px) {
    .bpc-grid-layout {
        grid-template-columns: 1fr;
    }
}

/* CỘT 1: AI TRỢ LÝ TƯ VẤN PC */
.bpc-ai-widget {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 20px;
}
.bpc-ai-header {
    background: #0284c7;
    padding: 13px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
}
.bpc-ai-header .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 6px #22c55e;
    flex-shrink: 0;
}
.bpc-ai-header .ai-title {
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #fff;
}
.bpc-ai-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #fff;
}
.bpc-chat-msgs {
    max-height: 380px;
    min-height: 140px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-right: 4px;
    scroll-behavior: smooth;
}
.bpc-chat-msgs::-webkit-scrollbar {
    width: 4px;
}
.bpc-chat-msgs::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 4px;
}

/* Chat bubble styling */
.cb-row {
    display: flex;
    gap: 8px;
    align-items: flex-start;
}
.cb-row.user {
    flex-direction: row-reverse;
}
.cb-bubble {
    max-width: 90%;
    padding: 10px 13px;
    border-radius: 14px;
    font-size: 12.5px;
    line-height: 1.55;
    word-wrap: break-word;
}
.cb-row.bot .cb-bubble {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    border-top-left-radius: 4px;
}
.cb-row.user .cb-bubble {
    background: #0284c7;
    color: #fff;
    border-top-right-radius: 4px;
}
.cb-bubble strong { font-weight: 700; }
.cb-bubble em { font-style: italic; }
.cb-bubble hr { border: none; border-top: 1px solid rgba(0,0,0,.08); margin: 6px 0; }
.cb-row.user .cb-bubble hr { border-top-color: rgba(255,255,255,.3); }

.cb-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    border-top-left-radius: 4px;
    width: fit-content;
}
.cb-typing span {
    width: 6px;
    height: 6px;
    background: #94a3b8;
    border-radius: 50%;
    animation: cbTyping .9s infinite;
}
.cb-typing span:nth-child(2) { animation-delay: .2s; }
.cb-typing span:nth-child(3) { animation-delay: .4s; }
@keyframes cbTyping { 0%,80%,100%{transform:translateY(0);} 40%{transform:translateY(-6px);} }

.cb-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.cb-chip {
    background: #f0f9ff;
    color: #0284c7;
    border: 1px solid #bae6fd;
    border-radius: 18px;
    padding: 5px 12px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    font-family: inherit;
    white-space: nowrap;
}
.cb-chip:hover {
    background: #e0f2fe;
    border-color: #7dd3fc;
    transform: translateY(-1px);
}
.cb-chip.chip-apply {
    background: #10b981;
    color: #fff;
    border-color: transparent;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(16,185,129,.25);
}
.cb-chip.chip-apply:hover {
    background: #059669;
}

.bpc-ai-input-wrap {
    display: flex;
    gap: 6px;
    align-items: center;
    margin-top: 4px;
}
.bpc-ai-input {
    flex: 1;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12.5px;
    outline: none;
    background: #fff;
    color: #1e293b;
    transition: border-color .15s;
    font-family: inherit;
}
.bpc-ai-input:focus {
    border-color: #0284c7;
}
.bpc-ai-send-btn {
    background: #0284c7;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, opacity .15s;
    flex-shrink: 0;
    font-family: inherit;
}
.bpc-ai-send-btn:hover {
    background: #0369a1;
}
.bpc-ai-send-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
}

/* CỘT 2: DANH SÁCH LINH KIỆN */
.bpc-rows-container {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.bpc-item-row {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    transition: border-color .15s, box-shadow .15s;
}
.bpc-item-row:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}
.bpc-col-cat {
    width: 175px;
    flex-shrink: 0;
}
.bpc-cat-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0284c7;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.bpc-cat-status {
    font-size: 12.5px;
    color: #64748b;
}
.bpc-col-content {
    flex: 1;
    min-width: 0;
}
.bpc-empty-slot {
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    padding: 12px 18px;
    color: #94a3b8;
    font-size: 12.5px;
    background: #fff;
}
.bpc-selected-slot {
    display: flex;
    align-items: center;
    gap: 14px;
}
.bpc-selected-slot img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    flex-shrink: 0;
}
.bpc-selected-info {
    flex: 1;
    min-width: 0;
}
.bpc-selected-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
    line-height: 1.35;
}
.bpc-socket-badge {
    display: inline-block;
    background: #eff6ff;
    color: #0284c7;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
}
.bpc-selected-price {
    font-size: 15.5px;
    font-weight: 700;
    color: #dc2626;
}
.bpc-col-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.btn-bpc-select {
    background: #0284c7;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: background .15s;
    font-family: inherit;
}
.btn-bpc-select:hover {
    background: #0369a1;
}
.btn-bpc-remove {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 8px 14px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s;
    font-family: inherit;
}
.btn-bpc-remove:hover {
    background: #fecaca;
}

/* CỘT 3: CHI PHÍ ƯỚC TÍNH */
.bpc-summary-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    position: sticky;
    top: 20px;
}
.bpc-summary-title {
    font-size: 13px;
    font-weight: 700;
    color: #0284c7;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 14px;
}
.bpc-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-size: 13.5px;
}
.bpc-summary-count-label {
    color: #64748b;
}
.bpc-summary-count-val {
    font-weight: 700;
    color: #0f172a;
}
.bpc-summary-total-price {
    font-size: 26px;
    font-weight: 800;
    color: #dc2626;
    margin-bottom: 6px;
}
.bpc-summary-note {
    font-size: 12px;
    color: #94a3b8;
    line-height: 1.5;
    margin-bottom: 18px;
}
.bpc-summary-empty {
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    padding: 18px 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 12.5px;
    line-height: 1.5;
}
.btn-bpc-cart {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    background: #ff9800;
    color: #fff;
    text-decoration: none;
    border-radius: 9px;
    padding: 12px 16px;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 4px 14px rgba(255,152,0,.2);
    margin-bottom: 10px;
    transition: background .15s;
    font-family: inherit;
}
.btn-bpc-cart:hover {
    background: #f57c00;
}
.btn-bpc-buynow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    background: #0284c7;
    color: #fff;
    text-decoration: none;
    border-radius: 9px;
    padding: 12px 16px;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 4px 14px rgba(2,132,199,.2);
    transition: background .15s;
    font-family: inherit;
}
.btn-bpc-buynow:hover {
    background: #0369a1;
}
</style>

<!-- Overlay + Modal Panel khi chọn linh kiện -->
<div class="selector-overlay" id="spOverlay" onclick="closeSelectorOutside(event)">
<div class="selector-panel" id="selectorPanel">
    <div class="sp-header">
        <h3 id="spPanelTitle">Chọn linh kiện</h3>
        <button class="sp-close-btn" onclick="closeSelector()">×</button>
    </div>
    <div class="sp-body">
        <div class="sp-filter">
            <div class="ftitle">Lọc sản phẩm theo</div>
            <div class="fsec">
                <h4>Khoảng giá</h4>
                <label><input type="radio" name="fprice" value="" checked onchange="applyFilter()"> Tất cả</label>
                <label><input type="radio" name="fprice" value="0-1000000" onchange="applyFilter()"> Dưới 1 triệu</label>
                <label><input type="radio" name="fprice" value="1000000-2000000" onchange="applyFilter()"> 1 – 2 triệu</label>
                <label><input type="radio" name="fprice" value="2000000-5000000" onchange="applyFilter()"> 2 – 5 triệu</label>
                <label><input type="radio" name="fprice" value="5000000-10000000" onchange="applyFilter()"> 5 – 10 triệu</label>
                <label><input type="radio" name="fprice" value="10000000-999999999" onchange="applyFilter()"> Trên 10 triệu</label>
            </div>
        </div>
        <div class="sp-products">
            <div class="sp-searchbar">
                <i class="fa fa-search" style="color:#94a3b8"></i>
                <input type="text" id="spSearch" placeholder="Tìm kiếm sản phẩm..." oninput="applyFilter()">
            </div>
            <div class="sock-badge" id="spSockBadge"></div>
            <div id="spList"><div class="sp-loading"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div></div>
        </div>
    </div>
</div>
</div>

<!-- Thông báo lỗi (Toast) -->
<?php if (!empty($_SESSION['buildpc_error'])): ?>
<div id="bpcErrorToast" style="
    position: fixed;
    top: 24px; right: 24px;
    z-index: 99999;
    background: #fff1f2;
    border: 1.5px solid #fca5a5;
    border-left: 5px solid #ef4444;
    border-radius: 14px;
    padding: 16px 20px;
    max-width: 420px;
    box-shadow: 0 10px 40px rgba(220,38,38,0.18);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    animation: bpcSlideIn .35s cubic-bezier(.4,0,.2,1);
">
    <span style="font-size:24px;line-height:1.2;">⚠️</span>
    <div style="flex:1;">
        <div style="font-weight:700;color:#b91c1c;font-size:14px;margin-bottom:5px;">Không thể thực hiện</div>
        <div style="color:#7f1d1d;font-size:13.5px;line-height:1.6;"><?php echo $_SESSION['buildpc_error']; ?></div>
    </div>
    <button onclick="document.getElementById('bpcErrorToast').remove()"
            style="background:none;border:none;font-size:20px;color:#b91c1c;cursor:pointer;padding:0;line-height:1;flex-shrink:0;">×</button>
</div>
<style>
@keyframes bpcSlideIn {
    from { opacity:0; transform:translateX(60px); }
    to   { opacity:1; transform:translateX(0); }
}
</style>
<script>
setTimeout(function() {
    var t = document.getElementById('bpcErrorToast');
    if (t) { t.style.transition = 'opacity .4s'; t.style.opacity = '0'; setTimeout(function(){ t.remove(); }, 400); }
}, 6000);
</script>
<?php unset($_SESSION['buildpc_error']); ?>
<?php endif; ?>

<!-- ════════════════════════════════════════
     MAIN CONTENT (3 COLUMNS)
════════════════════════════════════════ -->
<div class="bpc-main-container">
    <!-- Tiêu đề trang -->
    <div class="bpc-top-header">
        <h1><i class="fa fa-wrench"></i> Xây dựng cấu hình PC</h1>
        <p>Lựa chọn linh kiện máy tính đồng bộ, tương thích 100% chuẩn chân cắm (Socket), tối ưu hiệu năng và ngân sách.</p>
    </div>

    <div class="bpc-grid-layout">
        <!-- CỘT 1: AI TRỢ LÝ TƯ VẤN PC -->
        <aside class="bpc-ai-widget">
            <div class="bpc-ai-header">
                <span class="status-dot"></span>
                <i class="fa fa-comments-o"></i>
                <span class="ai-title">AI Trợ Lý Tư Vấn PC</span>
            </div>
            <div class="bpc-ai-body">
                <div class="bpc-chat-msgs" id="cbMsgs">
                    <div class="cb-row bot">
                        <div class="cb-bubble">
                            Xin chào! Tôi có thể tư vấn cấu hình tối ưu theo ngân sách và mục đích sử dụng (Gaming, Văn phòng, Đồ họa). Hãy nhập yêu cầu hoặc chọn gợi ý bên dưới!
                        </div>
                    </div>
                </div>
                <div class="cb-chips" id="cbChips">
                    <button class="cb-chip" onclick="cbSend('PC Gaming 15Tr')">PC Gaming 15Tr</button>
                    <button class="cb-chip" onclick="cbSend('PC Đồ Họa 25Tr')">PC Đồ Họa 25Tr</button>
                    <button class="cb-chip" onclick="cbSend('Văn Phòng 8Tr')">Văn Phòng 8Tr</button>
                </div>
                <div class="bpc-ai-input-wrap">
                    <input class="bpc-ai-input" id="cbInput" type="text"
                           placeholder="Hỏi AI cấu hình mong muốn..."
                           onkeydown="if(event.key==='Enter')cbSend()">
                    <button class="bpc-ai-send-btn" id="cbSendBtn" onclick="cbSend()">Gửi</button>
                </div>
            </div>
        </aside>

        <!-- CỘT 2: DANH SÁCH LINH KIỆN (7 MỤC) -->
        <section class="bpc-rows-container" id="buildRows">
            <?php foreach ($buildCategories as $cat_id => $cat_name):
                $selectedItem = $_SESSION['buildpc'][$cat_id] ?? null;
                if ($selectedItem) $totalPrice += $selectedItem['price'];
                $selectedImageSrc = '';
                $itemOutOfStock = false;
                if ($selectedItem) {
                    $dbItem = $productModel->getProductById($selectedItem['id']);
                    $itemOutOfStock = !$dbItem || (int)($dbItem['quantity'] ?? 0) <= 0;
                    if (!empty($selectedItem['image'])) {
                        if (strpos($selectedItem['image'], 'data:') === 0) {
                            $selectedImageSrc = $selectedItem['image'];
                        } elseif (file_exists(__DIR__ . '/../../../public/img/products/' . $selectedItem['image'])) {
                            $selectedImageSrc = BASE_URL . 'public/img/products/' . $selectedItem['image'];
                        }
                    }
                }
            ?>
            <div class="bpc-item-row" id="row-<?php echo $cat_id; ?>" style="<?php echo $itemOutOfStock ? 'border-color:#fca5a5; background:#fff8f8;' : ''; ?>">
                <!-- Tên danh mục -->
                <div class="bpc-col-cat">
                    <div class="bpc-cat-name"><?php echo htmlspecialchars(mb_strtoupper($cat_name, 'UTF-8')); ?></div>
                    <div class="bpc-cat-status">
                        <?php if ($selectedItem && $itemOutOfStock): ?>
                            <span style="background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">⛔ Hết hàng</span>
                        <?php else: ?>
                            <?php echo $selectedItem ? 'Đã chọn linh kiện' : 'Chưa có sản phẩm'; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ô sản phẩm -->
                <div class="bpc-col-content">
                    <?php if ($selectedItem): ?>
                        <div class="bpc-selected-slot">
                            <img src="<?php echo $selectedImageSrc; ?>" alt="<?php echo htmlspecialchars($selectedItem['name']); ?>" loading="lazy">
                            <div class="bpc-selected-info">
                                <div class="bpc-selected-title"><?php echo htmlspecialchars($selectedItem['name']); ?></div>
                                <?php if (!empty($selectedItem['socket'])): ?>
                                    <span class="bpc-socket-badge">Socket: <?php echo htmlspecialchars($selectedItem['socket']); ?></span>
                                <?php endif; ?>
                                <div class="bpc-selected-price"><?php echo number_format($selectedItem['price'], 0, ',', '.'); ?> đ</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bpc-empty-slot">Chưa có sản phẩm. Nhấn "Chọn" để thêm linh kiện.</div>
                    <?php endif; ?>
                </div>

                <!-- Nút thao tác -->
                <div class="bpc-col-actions">
                    <button class="btn-bpc-select" onclick="openSelector(<?php echo $cat_id; ?>, '<?php echo addslashes($cat_name); ?>')">
                        <?php echo $selectedItem ? 'Đổi' : 'Chọn'; ?>
                    </button>
                    <?php if ($selectedItem): ?>
                        <a href="buildpc.php?action=remove&cat_id=<?php echo $cat_id; ?>" class="btn-bpc-remove">Xóa</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- CỘT 3: CHI PHÍ ƯỚC TÍNH -->
        <aside class="bpc-summary-card">
            <div class="bpc-summary-title">CHI PHÍ ƯỚC TÍNH</div>
            <div class="bpc-summary-row">
                <span class="bpc-summary-count-label">Linh kiện đã chọn:</span>
                <span class="bpc-summary-count-val" id="bpcSummaryCount"><?php echo $selectedCount; ?>/<?php echo count($buildCategories); ?></span>
            </div>
            <div class="bpc-summary-total-price" id="bpcSummaryTotalPrice"><?php echo number_format($totalPrice, 0, ',', '.'); ?> đ</div>
            <div class="bpc-summary-note">Đã bao gồm VAT. Chưa bao gồm chi phí lắp ráp và vận chuyển.</div>
            
            <div id="bpcSummaryActionBox">
                <?php if ($totalPrice > 0): ?>
                    <a href="buildpc.php?action=add_to_cart" class="btn-bpc-cart">Thêm tất cả vào giỏ hàng</a>
                    <a href="buildpc.php?action=buy_now" class="btn-bpc-buynow">Mua ngay</a>
                <?php else: ?>
                    <div class="bpc-summary-empty">Chưa có linh kiện nào trong cấu hình. Hãy chọn linh kiện ở giữa để bắt đầu!</div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<script>
var BASE_URL    = '<?php echo BASE_URL; ?>';
var allProducts = [];
var curCatId    = 0;
var noImg = 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="%23f3f3f3"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23bbb" font-size="10">IMG</text></svg>';

function openSelector(catId, catName) {
    curCatId = catId;
    document.getElementById('spPanelTitle').textContent = 'Chọn: ' + catName;
    document.getElementById('spSearch').value = '';
    document.querySelector('input[name="fprice"][value=""]').checked = true;
    document.getElementById('spList').innerHTML = '<div class="sp-loading"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';
    document.getElementById('spSockBadge').style.display = 'none';

    fetch(BASE_URL + 'buildpc_modal.php?ajax_products=1&cat_id=' + catId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            allProducts = data.products || [];
            if (data.req_sock) {
                var b = document.getElementById('spSockBadge');
                b.textContent = 'Đang lọc Socket: ' + data.req_sock;
                b.style.display = 'block';
            }
            renderProducts(allProducts);
        });

    document.getElementById('spOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeSelector() {
    document.getElementById('spOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

function closeSelectorOutside(e) {
    if (e.target === document.getElementById('spOverlay')) closeSelector();
}

function renderProducts(list) {
    if (!list.length) {
        document.getElementById('spList').innerHTML = '<div class="sp-empty"><i class="fa fa-exclamation-circle"></i>Không tìm thấy sản phẩm phù hợp</div>';
        return;
    }
    var html = '';
    list.forEach(function(p) {
        html += '<div class="prow">'
             + '<img src="' + (p.image || noImg) + '" alt="">'
             + '<div class="pinfo">'
             + (p.socket ? '<span class="psock">Socket: ' + p.socket + '</span>' : '')
             + '<div class="pname">' + p.name + '</div>'
             + '<div class="pprice">' + p.price.toLocaleString('vi-VN') + ' ₫</div>'
             + '</div>'
             + '<button class="btn-padd" onclick="bpcAdd(' + curCatId + ',' + p.id + ',this)">'
             + '<i class="fa fa-plus"></i> Thêm</button>'
             + '</div>';
    });
    document.getElementById('spList').innerHTML = html;
}

function applyFilter() {
    var q     = document.getElementById('spSearch').value.toLowerCase();
    var price = document.querySelector('input[name="fprice"]:checked').value;
    var filtered = allProducts.filter(function(p) {
        var ok = p.name.toLowerCase().includes(q);
        if (price) {
            var pts = price.split('-');
            ok = ok && p.price >= parseInt(pts[0]) && p.price <= parseInt(pts[1]);
        }
        return ok;
    });
    renderProducts(filtered);
}

function bpcAdd(catId, prodId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    var fd = new FormData();
    fd.append('ajax_add', '1');
    fd.append('cat_id', catId);
    fd.append('product_id', prodId);
    fetch(BASE_URL + 'buildpc_modal.php', {method:'POST', body:fd})
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        })
        .then(function(text) {
            try { JSON.parse(text); } catch(e) {
                console.error('JSON parse fail:', text);
                throw e;
            }
            btn.classList.add('added');
            btn.innerHTML = '<i class="fa fa-check"></i> Đã thêm';
            setTimeout(function(){ closeSelector(); location.reload(); }, 500);
        })
        .catch(function(err) {
            console.error('bpcAdd error:', err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-plus"></i> Thêm';
            alert('Có lỗi khi thêm sản phẩm. Vui lòng thử lại.');
        });
}

function bpcRemove(catId) {
    var fd = new FormData();
    fd.append('ajax_remove', '1');
    fd.append('cat_id', catId);
    fetch(BASE_URL + 'buildpc_modal.php', {method:'POST', body:fd})
        .then(function(r){ return r.text(); })
        .then(function(){ location.reload(); })
        .catch(function(err){ console.error('bpcRemove error:', err); location.reload(); });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSelector();
    }
});

// ══════════════════════════════════════════════════════════════
//  AI TRỢ LÝ TƯ VẤN PC
// ══════════════════════════════════════════════════════════════
var cbHistory = [];
var cbPending = null;

function cbSend(textOverride) {
    var input = document.getElementById('cbInput');
    var msg = (textOverride !== undefined) ? textOverride : input.value.trim();
    if (!msg) return;
    input.value = '';
    document.getElementById('cbChips').innerHTML = '';

    cbUserMsg(msg);
    cbHistory.push({role:'user', content:msg});

    // Áp dụng cấu hình
    if (msg === 'Áp dụng cấu hình này' && cbPending) {
        cbApply(cbPending);
        return;
    }

    var typingId = cbShowTyping();
    document.getElementById('cbSendBtn').disabled = true;

    fetch(BASE_URL + 'chatbot_buildpc.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({message: msg, history: cbHistory})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        cbHideTyping(typingId);
        document.getElementById('cbSendBtn').disabled = false;
        var chips = data.suggestions || [];
        if (data.build_suggestion && data.build_suggestion.length) {
            cbPending = data.build_suggestion;
            chips = ['Áp dụng cấu hình này'].concat(chips.filter(function(s){ return s !== 'Áp dụng cấu hình này'; }));
        }
        cbBotMsg(data.reply, chips);
        cbHistory.push({role:'assistant', content:data.reply});
    })
    .catch(function() {
        cbHideTyping(typingId);
        document.getElementById('cbSendBtn').disabled = false;
        cbBotMsg('❌ Xin lỗi, có lỗi xảy ra khi kết nối trợ lý AI. Vui lòng thử lại!', []);
    });
}

function cbUserMsg(text) {
    var el = document.createElement('div');
    el.className = 'cb-row user';
    el.innerHTML = '<div class="cb-bubble">' + cbEsc(text) + '</div>';
    document.getElementById('cbMsgs').appendChild(el);
    cbScroll();
}

function cbBotMsg(text, chips) {
    var el = document.createElement('div');
    el.className = 'cb-row bot';
    el.innerHTML = '<div class="cb-bubble">' + cbMd(text) + '</div>';
    document.getElementById('cbMsgs').appendChild(el);
    cbRenderChips(chips);
    cbScroll();
}

function cbShowTyping() {
    var id = 'cbt_' + Date.now();
    var el = document.createElement('div');
    el.className = 'cb-row bot'; el.id = id;
    el.innerHTML = '<div class="cb-typing"><span></span><span></span><span></span></div>';
    document.getElementById('cbMsgs').appendChild(el);
    cbScroll();
    return id;
}

function cbHideTyping(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
}

function cbRenderChips(chips) {
    var cont = document.getElementById('cbChips');
    cont.innerHTML = '';
    if (!chips || !chips.length) return;
    chips.forEach(function(s) {
        var btn = document.createElement('button');
        btn.className = 'cb-chip' + (s === 'Áp dụng cấu hình này' ? ' chip-apply' : '');
        btn.textContent = s;
        btn.onclick = function() { cbSend(s); };
        cont.appendChild(btn);
    });
}

function cbScroll() {
    var m = document.getElementById('cbMsgs');
    m.scrollTop = m.scrollHeight;
}

function cbEsc(t) {
    return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function cbMd(text) {
    var s = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    s = s.replace(/\*\*([^*]+)\*\*/g,'<strong>$1</strong>');
    s = s.replace(/\*([^*\n]+)\*/g,'<em>$1</em>');
    s = s.replace(/^---$/gm,'<hr>');
    s = s.replace(/^[•\-] (.+)$/gm,'&bull; $1');
    s = s.replace(/^↳ (.+)$/gm,'<span style="color:#0284c7;font-weight:600;padding-left:4px">↳ $1</span>');
    s = s.replace(/\n/g,'<br>');
    return s;
}

function cbApply(items) {
    document.getElementById('cbChips').innerHTML = '';
    cbBotMsg('⏳ Đang áp dụng cấu hình vào bảng...', []);

    // Sắp xếp: CPU (1) trước Mainboard (3) để tránh xóa do socket check
    var sorted = items.slice().sort(function(a, b) {
        var ord = {1:0, 3:1, 2:2, 4:3, 5:4, 6:5, 7:6};
        return (ord[a.cat_id]||9) - (ord[b.cat_id]||9);
    });

    // Gửi tuần tự
    sorted.reduce(function(chain, item) {
        return chain.then(function() {
            var fd = new FormData();
            fd.append('ajax_add','1');
            fd.append('cat_id', item.cat_id);
            fd.append('product_id', item.product_id);
            return fetch(BASE_URL + 'buildpc_modal.php', {method:'POST', body:fd})
                   .then(function(r){ return r.json(); });
        });
    }, Promise.resolve())
    .then(function() {
        cbPending = null;
        cbUpdateRows(sorted);
        cbBotMsg('✅ **Áp dụng thành công!** Cấu hình đã được điền vào danh sách.<br>Bạn có thể nhấn **Mua ngay** hoặc **Thêm vào giỏ hàng** bên phải để thanh toán.', ['Build cấu hình khác', 'Tư vấn thêm']);
    })
    .catch(function() {
        cbBotMsg('❌ Có lỗi khi áp dụng. Vui lòng thử lại!', ['Thử lại']);
    });
}

function cbUpdateRows(items) {
    var catNames = {
        1:'VI XỬ LÝ (CPU)', 3:'BO MẠCH CHỦ (MAINBOARD)',
        2:'BỘ NHỚ TRONG (RAM)', 4:'CARD MÀN HÌNH (VGA)',
        5:'Ổ CỨNG (SSD/HDD)', 6:'NGUỒN MÁY TÍNH (PSU)', 7:'VỎ MÁY TÍNH (CASE)'
    };
    var fallbackImg = 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A//www.w3.org/2000/svg%22 width%3D%2264%22 height%3D%2264%22%3E%3Crect width%3D%2264%22 height%3D%2264%22 fill%3D%22%23f3f3f3%22/%3E%3Ctext x%3D%2250%25%22 y%3D%2250%25%22 dominant-baseline%3D%22middle%22 text-anchor%3D%22middle%22 fill%3D%22%23bbb%22 font-size%3D%2210%22%3EIMG%3C/text%3E%3C/svg%3E';

    items.forEach(function(item) {
        var row = document.getElementById('row-' + item.cat_id);
        if (!row) return;

        var imgSrc = fallbackImg;
        if (item.image) {
            if (item.image.indexOf('data:') === 0 || item.image.indexOf('http') === 0) {
                imgSrc = item.image;
            } else {
                imgSrc = BASE_URL + 'public/img/products/' + item.image;
            }
        }

        var priceStr = Number(item.price).toLocaleString('vi-VN') + ' đ';
        var catName  = catNames[item.cat_id] || '';
        var catEsc   = catName.replace(/'/g, "\\'");

        // Đổi trạng thái
        var statusEl = row.querySelector('.bpc-cat-status');
        if (statusEl) statusEl.textContent = 'Đã chọn linh kiện';

        // Đổi nội dung giữa
        var contentEl = row.querySelector('.bpc-col-content');
        if (contentEl) {
            contentEl.innerHTML =
                '<div class="bpc-selected-slot">'
                + '<img src="' + imgSrc + '" alt="' + cbEsc(item.name) + '">'
                + '<div class="bpc-selected-info">'
                + '<div class="bpc-selected-title">' + cbEsc(item.name) + '</div>'
                + (item.socket ? '<span class="bpc-socket-badge">Socket: ' + cbEsc(item.socket) + '</span>' : '')
                + '<div class="bpc-selected-price">' + priceStr + '</div>'
                + '</div>'
                + '</div>';
        }

        // Đổi nút bấm
        var actionsEl = row.querySelector('.bpc-col-actions');
        if (actionsEl) {
            actionsEl.innerHTML =
                '<button class="btn-bpc-select" onclick="openSelector(' + item.cat_id + ',\'' + catEsc + '\')">Đổi</button>'
                + '<a href="buildpc.php?action=remove&cat_id=' + item.cat_id + '" class="btn-bpc-remove">Xóa</a>';
        }
    });

    cbUpdateSidebar(items);
}

function cbUpdateSidebar(newItems) {
    var appliedPrices = {};
    if (newItems && newItems.length) {
        newItems.forEach(function(i){ appliedPrices[i.cat_id] = Number(i.price); });
    }

    var total = 0, count = 0;
    [1,2,3,4,5,6,7].forEach(function(cid) {
        if (appliedPrices[cid] !== undefined) {
            total += appliedPrices[cid]; count++;
        } else {
            var row = document.getElementById('row-' + cid);
            if (!row) return;
            var priceEl = row.querySelector('.bpc-selected-price');
            if (priceEl) {
                var n = parseInt(priceEl.textContent.replace(/[^\d]/g,''));
                if (n) { total += n; count++; }
            }
        }
    });

    // Cập nhật giá
    var totalEl = document.getElementById('bpcSummaryTotalPrice');
    if (totalEl) totalEl.textContent = total.toLocaleString('vi-VN') + ' đ';

    // Cập nhật số món
    var countEl = document.getElementById('bpcSummaryCount');
    if (countEl) countEl.textContent = count + '/7';

    // Cập nhật khối hành động
    var actionBox = document.getElementById('bpcSummaryActionBox');
    if (actionBox) {
        if (total > 0) {
            actionBox.innerHTML =
                '<a href="buildpc.php?action=add_to_cart" class="btn-bpc-cart">Thêm tất cả vào giỏ hàng</a>'
                + '<a href="buildpc.php?action=buy_now" class="btn-bpc-buynow">Mua ngay</a>';
        } else {
            actionBox.innerHTML =
                '<div class="bpc-summary-empty">Chưa có linh kiện nào trong cấu hình. Hãy chọn linh kiện ở giữa để bắt đầu!</div>';
        }
    }
}
</script>