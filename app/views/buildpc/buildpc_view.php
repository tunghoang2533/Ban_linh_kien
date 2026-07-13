<?php
if (!isset($buildCategories)) $buildCategories = [];
$totalPrice   = 0;
$selectedCount = isset($_SESSION['buildpc']) ? count(array_filter($_SESSION['buildpc'])) : 0;
?>

<!-- ════════════════════════════════════════
     SELECTOR PANEL (slide-in từ phải)
════════════════════════════════════════ -->
<style>
.selector-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;display:none;align-items:center;justify-content:center;}
.selector-overlay.open{display:flex;}
.selector-panel{width:900px;max-width:calc(100vw - 24px);height:86vh;max-height:680px;background:#fff;
    display:flex;flex-direction:column;border-radius:16px;overflow:hidden;
    box-shadow:0 24px 60px rgba(0,0,0,.28);
    opacity:0;transform:scale(.95) translateY(10px);transition:opacity .22s ease,transform .22s ease;z-index:9001;}
.selector-overlay.open .selector-panel{opacity:1;transform:scale(1) translateY(0);}
.sp-header{background:linear-gradient(135deg,#1e3a6e,#2563eb);color:#fff;padding:16px 22px;
    display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.sp-header h3{font-size:16px;font-weight:700;margin:0;}
.sp-close-btn{background:rgba(255,255,255,.2);border:none;color:#fff;width:32px;height:32px;
    border-radius:50%;cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;}
.sp-close-btn:hover{background:rgba(255,255,255,.35);}
.sp-body{display:flex;flex:1;overflow:hidden;}
/* Filter sidebar */
.sp-filter{width:200px;flex-shrink:0;border-right:1px solid #e8edf3;padding:16px 14px;
    overflow-y:auto;background:#fafbfc;}
.sp-filter .ftitle{font-size:11px;font-weight:700;color:#2563eb;text-transform:uppercase;
    letter-spacing:.8px;margin-bottom:14px;}
.sp-filter .fsec{margin-bottom:16px;}
.sp-filter .fsec h4{font-size:12px;font-weight:700;color:#334155;margin-bottom:8px;}
.sp-filter label{display:flex;align-items:center;gap:6px;font-size:12px;color:#475569;
    margin-bottom:5px;cursor:pointer;}
.sp-filter input[type=radio]{accent-color:#2563eb;}
/* Product list */
.sp-products{flex:1;overflow-y:auto;padding:14px 16px;}
.sp-searchbar{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
.sp-searchbar input{flex:1;padding:9px 13px;border:1.5px solid #e2e8f0;border-radius:9px;
    font-size:13px;outline:none;}
.sp-searchbar input:focus{border-color:#2563eb;}
.sock-badge{background:#fef9c3;color:#92400e;padding:6px 12px;border-radius:6px;
    font-size:12px;font-weight:600;margin-bottom:10px;display:none;}
.sp-loading{text-align:center;padding:50px;color:#94a3b8;font-size:14px;}
.sp-empty{text-align:center;padding:50px;color:#94a3b8;}
.sp-empty i{font-size:40px;display:block;margin-bottom:10px;color:#cbd5e1;}
.prow{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #e8edf3;
    border-radius:12px;padding:12px 16px;margin-bottom:8px;transition:border-color .15s,box-shadow .15s;}
.prow:hover{border-color:#bfdbfe;box-shadow:0 2px 12px rgba(37,99,235,.08);}
.prow img{width:64px;height:64px;object-fit:contain;border-radius:8px;border:1px solid #e8edf3;
    flex-shrink:0;background:#f8fafc;}
.pinfo{flex:1;min-width:0;}
.pname{font-size:13px;font-weight:600;color:#1e293b;margin-bottom:3px;}
.pprice{font-size:15px;font-weight:700;color:#e10c00;}
.psock{display:inline-block;background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:5px;
    font-size:11px;font-weight:600;margin-bottom:3px;}
.btn-padd{background:#2563eb;color:#fff;border:none;padding:9px 16px;border-radius:9px;
    cursor:pointer;font-size:13px;font-weight:600;white-space:nowrap;flex-shrink:0;
    display:inline-flex;align-items:center;gap:5px;transition:background .15s;}
.btn-padd:hover{background:#1d4ed8;}
.btn-padd.added{background:#16a34a;}

/* ══ Chatbot ══════════════════════════════════════════════════ */
#chatbotFab{
    position:fixed;bottom:28px;right:28px;z-index:9999;
    width:58px;height:58px;border-radius:50%;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    border:none;cursor:pointer;
    box-shadow:0 6px 24px rgba(99,102,241,.55);
    display:flex;align-items:center;justify-content:center;
    transition:transform .2s,box-shadow .2s;
}
#chatbotFab:hover{transform:scale(1.12);box-shadow:0 8px 32px rgba(99,102,241,.7);}
#chatbotFab svg{width:28px;height:28px;fill:#fff;}
#chatbotFab .fab-pulse{
    position:absolute;top:-3px;right:-3px;
    width:18px;height:18px;
    background:#ef4444;border-radius:50%;border:2.5px solid #fff;
    animation:fabPulse 2s infinite;
}
@keyframes fabPulse{0%,100%{transform:scale(1);}50%{transform:scale(1.35);}}

#chatbotTooltip{
    position:fixed;bottom:34px;right:96px;z-index:9998;
    background:#1e293b;color:#fff;font-size:13px;font-weight:600;
    padding:9px 16px;border-radius:10px;white-space:nowrap;
    box-shadow:0 4px 16px rgba(0,0,0,.2);
    opacity:0;transform:translateX(10px);pointer-events:none;
    transition:opacity .2s,transform .2s;
}
#chatbotTooltip.show{opacity:1;transform:translateX(0);}
#chatbotTooltip::after{
    content:'';position:absolute;right:-6px;top:50%;transform:translateY(-50%);
    border:6px solid transparent;border-right:none;border-left-color:#1e293b;
}

#chatbotWindow{
    position:fixed;bottom:98px;right:28px;z-index:9998;
    width:390px;max-width:calc(100vw - 48px);
    height:580px;max-height:calc(100vh - 130px);
    background:#fff;border-radius:22px;
    box-shadow:0 24px 72px rgba(0,0,0,.18);
    display:flex;flex-direction:column;overflow:hidden;
    transform:scale(0) translateY(20px);transform-origin:bottom right;
    transition:transform .28s cubic-bezier(.34,1.56,.64,1),opacity .22s;
    opacity:0;pointer-events:none;
}
#chatbotWindow.open{transform:scale(1) translateY(0);opacity:1;pointer-events:all;}

.cb-head{
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    padding:15px 18px;display:flex;align-items:center;gap:12px;flex-shrink:0;
}
.cb-head-avatar{
    width:40px;height:40px;background:rgba(255,255,255,.2);
    border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cb-head-avatar svg{width:22px;height:22px;fill:#fff;}
.cb-head-info{flex:1;}
.cb-head-info strong{display:block;font-size:14px;font-weight:700;color:#fff;}
.cb-head-info span{font-size:11.5px;color:rgba(255,255,255,.78);}
.cb-head-status{width:8px;height:8px;background:#4ade80;border-radius:50%;border:1.5px solid #fff;flex-shrink:0;}
.cb-head-close{background:rgba(255,255,255,.18);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s;}
.cb-head-close:hover{background:rgba(255,255,255,.35);}

.cb-msgs{flex:1;overflow-y:auto;padding:16px 14px;display:flex;flex-direction:column;gap:12px;scroll-behavior:smooth;}
.cb-msgs::-webkit-scrollbar{width:4px;}
.cb-msgs::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:4px;}

.cb-row{display:flex;gap:8px;align-items:flex-end;}
.cb-row.user{flex-direction:row-reverse;}
.cb-bubble{max-width:82%;padding:10px 14px;border-radius:18px;font-size:13px;line-height:1.6;word-wrap:break-word;}
.cb-row.bot .cb-bubble{background:#f1f5f9;color:#1e293b;border-bottom-left-radius:4px;}
.cb-row.user .cb-bubble{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-bottom-right-radius:4px;}
.cb-avatar-mini{width:30px;height:30px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.cb-avatar-mini svg{width:15px;height:15px;fill:#fff;}
.cb-bubble strong{font-weight:700;}
.cb-bubble em{font-style:italic;}
.cb-bubble hr{border:none;border-top:1px solid rgba(0,0,0,.1);margin:6px 0;}
.cb-row.user .cb-bubble hr{border-top-color:rgba(255,255,255,.3);}

.cb-typing{display:flex;align-items:center;gap:5px;padding:12px 16px;background:#f1f5f9;border-radius:18px;border-bottom-left-radius:4px;width:fit-content;}
.cb-typing span{width:7px;height:7px;background:#94a3b8;border-radius:50%;animation:cbTyping .9s infinite;}
.cb-typing span:nth-child(2){animation-delay:.2s;}
.cb-typing span:nth-child(3){animation-delay:.4s;}
@keyframes cbTyping{0%,80%,100%{transform:translateY(0);}40%{transform:translateY(-7px);}}

.cb-chips{display:flex;flex-wrap:wrap;gap:6px;padding:0 14px 10px;flex-shrink:0;}
.cb-chip{
    background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;
    border-radius:20px;padding:6px 13px;font-size:12px;
    cursor:pointer;transition:all .15s;white-space:nowrap;font-weight:500;
}
.cb-chip:hover{background:#dbeafe;border-color:#93c5fd;transform:translateY(-1px);}
.cb-chip.chip-apply{
    background:linear-gradient(135deg,#10b981,#059669);color:#fff;
    border-color:transparent;font-weight:700;
    box-shadow:0 3px 10px rgba(16,185,129,.3);
}
.cb-chip.chip-apply:hover{opacity:.9;transform:translateY(-1px);}

.cb-foot{border-top:1px solid #f1f5f9;padding:12px 14px;display:flex;gap:8px;align-items:center;flex-shrink:0;background:#fff;}
.cb-input{
    flex:1;border:1.5px solid #e2e8f0;border-radius:24px;
    padding:10px 16px;font-size:13px;outline:none;
    transition:border-color .15s;background:#fafbfc;
    font-family:inherit;
}
.cb-input:focus{border-color:#6366f1;background:#fff;}
.cb-send-btn{
    width:40px;height:40px;background:linear-gradient(135deg,#6366f1,#8b5cf6);
    border:none;border-radius:50%;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;transition:transform .15s,opacity .15s;
    box-shadow:0 4px 12px rgba(99,102,241,.35);
}
.cb-send-btn:hover{transform:scale(1.1);}
.cb-send-btn:disabled{opacity:.45;cursor:default;transform:none;}
.cb-send-btn svg{width:17px;height:17px;fill:#fff;}
</style>

<!-- Overlay + Panel -->
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
        </div><!-- end sp-products -->
    </div><!-- end sp-body -->
</div><!-- end selector-panel -->
</div><!-- end selector-overlay -->

<!-- ════════════════════════════════════════
     MAIN BUILD PC VIEW
════════════════════════════════════════ -->

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

<div class="container" style="margin: 30px auto 50px; font-family: Arial, sans-serif; max-width: 1180px;">
    <!-- Banner -->
    <div style="background: linear-gradient(135deg, #0c70c1 0%, #25a7d8 100%); border-radius: 24px; padding: 34px 34px 28px; color: #fff; box-shadow: 0 20px 45px rgba(11, 48, 88, 0.18); margin-bottom: 30px;">
        <div style="max-width: 760px;">
            <p style="text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.8); font-size: 13px; margin-bottom: 14px;">Build PC Chuyên Nghiệp</p>
            <h1 style="font-size: 34px; line-height: 1.1; margin: 0 0 12px; font-weight: 700;">Tạo cấu hình máy tính vừa mạnh mẽ vừa cân bằng</h1>
            <p style="font-size: 16px; color: rgba(255,255,255,0.92); max-width: 700px; margin: 0 0 24px;">Chọn linh kiện tương thích, tối ưu hiệu năng và mua ngay bộ cấu hình hoàn chỉnh với chỉ một cú nhấp.</p>
        </div>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 24px;">
        <!-- Danh sách linh kiện -->
        <section style="flex: 1 1 660px; min-width: 320px;">
            <div style="display: grid; gap: 18px;" id="buildRows">
                <?php foreach ($buildCategories as $cat_id => $cat_name):
                    $selectedItem = $_SESSION['buildpc'][$cat_id] ?? null;
                    if ($selectedItem) $totalPrice += $selectedItem['price'];
                    $selectedImageSrc = '';
                    $itemOutOfStock = false;
                    if ($selectedItem) {
                        // Kiểm tra tồn kho thực tế từ DB
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
                <div style="display:flex;align-items:center;justify-content:space-between;gap:20px;background:#fff;border:1px solid <?php echo $itemOutOfStock ? '#fca5a5' : '#e8eff7'; ?>;border-radius:18px;padding:24px;box-shadow:0 12px 35px rgba(38,79,119,.06);<?php echo $itemOutOfStock ? 'background:#fff8f8;' : ''; ?>" id="row-<?php echo $cat_id; ?>">
                    <div style="min-width:180px;flex:0 0 220px;">
                        <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#1768b5;margin-bottom:8px;"><?php echo $cat_name; ?></div>
                        <div style="font-size:14px;color:#5a6984;">
                            <?php if ($selectedItem && $itemOutOfStock): ?>
                                <span style="background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px;">⛔ Hết hàng</span>
                            <?php else: ?>
                                <?php echo $selectedItem ? 'Đã chọn linh kiện' : 'Chưa có sản phẩm'; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="flex:1;display:flex;align-items:center;gap:18px;min-width:260px;">
                        <?php if ($selectedItem): ?>
                            <img src="<?php echo $selectedImageSrc; ?>" alt="<?php echo $selectedItem['name']; ?>" style="width:84px;height:84px;object-fit:cover;border-radius:16px;border:1px solid #e7eff6;background:#f7fbff;" loading="lazy">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:16px;font-weight:700;color:#1f314d;margin-bottom:6px;"><?php echo $selectedItem['name']; ?></div>
                                <?php if (!empty($selectedItem['socket'])): ?>
                                    <span style="display:inline-block;background:#eff6ff;color:#1768b5;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600;margin-bottom:8px;">Socket: <?php echo $selectedItem['socket']; ?></span>
                                <?php endif; ?>
                                <div style="font-size:18px;font-weight:700;color:#e10c00;"><?php echo number_format($selectedItem['price'],0,',','.'); ?> ₫</div>
                            </div>
                        <?php else: ?>
                            <div style="flex:1;min-width:0;padding:18px 16px;border:1px dashed #d8e3f1;border-radius:16px;color:#8b98ac;font-size:14px;">Chưa có sản phẩm. Hãy chọn để hoàn thiện bộ máy.</div>
                        <?php endif; ?>
                    </div>

                    <div style="display:grid;gap:10px;flex-shrink:0;min-width:140px;">
                        <button onclick="openSelector(<?php echo $cat_id; ?>, '<?php echo addslashes($cat_name); ?>')"
                                style="display:inline-flex;justify-content:center;align-items:center;background:<?php echo $selectedItem ? '#0c70c1' : '#2e8af6'; ?>;color:#fff;border:none;cursor:pointer;padding:11px 14px;border-radius:12px;font-weight:700;font-size:13px;box-shadow:0 8px 20px rgba(12,112,193,.16);">
                            <?php echo $selectedItem ? 'Đổi' : 'Chọn'; ?>
                        </button>
                        <?php if ($selectedItem): ?>
                        <a href="buildpc.php?action=remove&cat_id=<?php echo $cat_id; ?>" style="display:inline-flex;justify-content:center;align-items:center;background:#f03f3f;color:#fff;text-decoration:none;padding:11px 14px;border-radius:12px;font-weight:700;font-size:13px;">Xóa</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Sidebar tổng giá -->
        <aside style="flex:0 0 320px;min-width:280px;background:#fff;border-radius:24px;border:1px solid #edf2f8;padding:28px;box-shadow:0 18px 40px rgba(38,79,119,.07);">
            <div style="margin-bottom:22px;">
                <div style="font-size:13px;font-weight:700;color:#0c73c1;letter-spacing:1px;text-transform:uppercase;margin-bottom:10px;">Tổng cấu hình</div>
                <div style="display:flex;justify-content:space-between;color:#5e6f85;margin-bottom:10px;"><span>Đã chọn</span><span><?php echo $selectedCount; ?>/<?php echo count($buildCategories); ?></span></div>
                <div style="font-size:28px;font-weight:700;color:#162a46;margin-bottom:6px;"><?php echo number_format($totalPrice,0,',','.'); ?> ₫</div>
                <div style="font-size:13px;color:#7a8ba8;line-height:1.6;">Giá gồm linh kiện đã chọn. Chưa bao gồm phí lắp ráp và giao hàng.</div>
            </div>
            <?php if ($totalPrice > 0): ?>
                <a href="buildpc.php?action=add_to_cart" style="display:inline-flex;align-items:center;justify-content:center;width:100%;background:#ff9800;color:#fff;text-decoration:none;border-radius:14px;padding:14px 18px;font-weight:700;font-size:15px;box-shadow:0 14px 30px rgba(255,152,0,.18);margin-bottom:12px;">Thêm tất cả vào giỏ hàng</a>
                <a href="buildpc.php?action=buy_now" style="display:inline-flex;align-items:center;justify-content:center;width:100%;background:#0c70c1;color:#fff;text-decoration:none;border-radius:14px;padding:14px 18px;font-weight:700;font-size:15px;box-shadow:0 14px 30px rgba(12,112,193,.16);">Mua ngay</a>
            <?php else: ?>
                <button onclick="openSelector(1,'Vi xử lý (CPU)')" style="display:inline-flex;align-items:center;justify-content:center;width:100%;background:#2e8af6;color:#fff;border:none;cursor:pointer;border-radius:14px;padding:14px 18px;font-weight:700;font-size:15px;">Bắt đầu chọn linh kiện</button>
            <?php endif; ?>
        </aside>
    </div>
</div>

<!-- ══ Chatbot Floating Button ══════════════════════════════ -->
<button id="chatbotFab" onclick="cbToggle()" title="Trợ lý Build PC">
    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 10H6V10h12v2zm0-3H6V7h12v2z"/></svg>
    <div class="fab-pulse"></div>
</button>
<div id="chatbotTooltip">💬 Tư vấn Build PC</div>

<!-- ══ Chatbot Window ══════════════════════════════════════════ -->
<div id="chatbotWindow">
    <div class="cb-head">
        <div class="cb-head-avatar">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9v-2h2v2zm3.07-7.75-.9.92C12.45 9.9 12 10.5 12 12h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H9c0-1.66 1.34-3 3-3s3 1.34 3 3c0 .66-.27 1.26-.69 1.69z"/></svg>
        </div>
        <div class="cb-head-info">
            <strong>Trợ lý Build PC 🤖</strong>
            <span>Tư vấn cấu hình • Gợi ý linh kiện</span>
        </div>
        <div class="cb-head-status"></div>
        <button class="cb-head-close" onclick="cbToggle()">×</button>
    </div>
    <div class="cb-msgs" id="cbMsgs"></div>
    <div class="cb-chips" id="cbChips"></div>
    <div class="cb-foot">
        <input class="cb-input" id="cbInput" type="text"
               placeholder="Nhập câu hỏi... (vd: build gaming 15 triệu)"
               onkeydown="if(event.key==='Enter')cbSend()">
        <button class="cb-send-btn" id="cbSendBtn" onclick="cbSend()">
            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
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
        .then(r => r.json())
        .then(data => {
            allProducts = data.products || [];
            if (data.req_sock) {
                var b = document.getElementById('spSockBadge');
                b.textContent = '🔌 Đang lọc Socket: ' + data.req_sock;
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
    // Chỉ đóng khi click đúng vào overlay (nền mờ), không đóng khi click bên trong panel
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
        // Nếu selector đang đóng thì đóng chatbot
        if (!document.getElementById('spOverlay').classList.contains('open')) {
            document.getElementById('chatbotWindow').classList.remove('open');
            cbOpen = false;
        }
    }
});

// ══════════════════════════════════════════════════════════════
//  CHATBOT
// ══════════════════════════════════════════════════════════════
var cbOpen        = false;
var cbHistory     = [];
var cbReady       = false;
var cbPending     = null; // build_suggestion đang chờ áp dụng

// Tooltip tự động hiện sau 1.5s
setTimeout(function(){
    var tip = document.getElementById('chatbotTooltip');
    if(tip) tip.classList.add('show');
    setTimeout(function(){ if(tip) tip.classList.remove('show'); }, 4000);
}, 1500);

function cbToggle() {
    cbOpen = !cbOpen;
    var win = document.getElementById('chatbotWindow');
    var tip = document.getElementById('chatbotTooltip');
    var dot = document.querySelector('#chatbotFab .fab-pulse');
    if (cbOpen) {
        win.classList.add('open');
        if (tip) tip.classList.remove('show');
        if (dot) dot.style.display = 'none';
        if (!cbReady) {
            cbReady = true;
            cbBotMsg(
                '👋 **Xin chào!** Tôi là trợ lý Build PC.\n\nTôi có thể giúp bạn:\n• 🔧 Gợi ý cấu hình theo ngân sách\n• 💡 Tư vấn linh kiện phù hợp\n• ❓ Giải đáp thắc mắc phần cứng\n\nHãy thử: *"Gợi ý PC gaming 15 triệu"*',
                ['Build PC gaming 15 triệu', 'Build PC văn phòng 10 triệu', 'Build PC đồ họa 20 triệu', 'Xem CPU có sẵn']
            );
        }
        setTimeout(function(){ document.getElementById('cbInput').focus(); }, 320);
    } else {
        win.classList.remove('open');
    }
}

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
        cbBotMsg('❌ Xin lỗi, có lỗi xảy ra. Vui lòng thử lại!', []);
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
    el.innerHTML =
        '<div class="cb-avatar-mini"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" fill="#fff"/></svg></div>'
        + '<div class="cb-bubble">' + cbMd(text) + '</div>';
    document.getElementById('cbMsgs').appendChild(el);
    cbRenderChips(chips);
    cbScroll();
}

function cbShowTyping() {
    var id = 'cbt_' + Date.now();
    var el = document.createElement('div');
    el.className = 'cb-row bot'; el.id = id;
    el.innerHTML =
        '<div class="cb-avatar-mini"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" fill="#fff"/></svg></div>'
        + '<div class="cb-typing"><span></span><span></span><span></span></div>';
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
    s = s.replace(/^↳ (.+)$/gm,'<span style="color:#64748b;padding-left:8px">↳ $1</span>');
    s = s.replace(/\n/g,'<br>');
    return s;
}

function cbApply(items) {
    document.getElementById('cbChips').innerHTML = '';
    cbBotMsg('⏳ Đang áp dụng cấu hình...', []);

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
        cbUpdateRows(sorted);   // cập nhật UI không reload
        cbBotMsg('✅ **Áp dụng thành công!** Cấu hình đã được điền vào danh sách.\n\nBạn có thể tiếp tục hỏi hoặc nhấn **Thêm vào giỏ** để đặt hàng.', ['Đặt cấu hình khác', 'Tư vấn thêm']);
    })
    .catch(function() {
        cbBotMsg('❌ Có lỗi khi áp dụng. Vui lòng thử lại!', ['Thử lại']);
    });
}

// Cập nhật DOM từng row – không cần reload trang
function cbUpdateRows(items) {
    var catNames = {
        1:'Vi xử lý (CPU)', 3:'Bo mạch chủ (Mainboard)',
        2:'Bộ nhớ trong (RAM)', 4:'Card màn hình (VGA)',
        5:'Ổ cứng (SSD/HDD)', 6:'Nguồn máy tính (PSU)', 7:'Vỏ máy tính (Case)'
    };
    var noImg = 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A//www.w3.org/2000/svg%22 width%3D%2284%22 height%3D%2284%22%3E%3Crect width%3D%2284%22 height%3D%2284%22 fill%3D%22%23f3f3f3%22/%3E%3Ctext x%3D%2250%25%22 y%3D%2250%25%22 dominant-baseline%3D%22middle%22 text-anchor%3D%22middle%22 fill%3D%22%23bbb%22 font-size%3D%2211%22%3EIMG%3C/text%3E%3C/svg%3E';

    items.forEach(function(item) {
        var row = document.getElementById('row-' + item.cat_id);
        if (!row) return;

        // Xây dựng src ảnh
        var imgSrc = noImg;
        if (item.image) {
            if (item.image.indexOf('data:') === 0 || item.image.indexOf('http') === 0) {
                imgSrc = item.image;
            } else {
                imgSrc = BASE_URL + 'public/img/products/' + item.image;
            }
        }

        var priceStr = Number(item.price).toLocaleString('vi-VN') + ' ₫';
        var catName  = catNames[item.cat_id] || '';
        var catEsc   = catName.replace(/'/g, "\\'");

        // Cập nhật style row
        row.style.border          = '1px solid #e8eff7';
        row.style.background      = '#fff';

        // Cột 1: status
        var col1Status = row.querySelector('div:first-child > div:last-child');
        if (col1Status) col1Status.textContent = 'Đã chọn linh kiện';

        // Cột 2: thông tin sản phẩm
        var col2 = row.children[1];
        if (col2) {
            col2.innerHTML =
                '<img src="' + imgSrc + '" alt="' + item.name + '" style="width:84px;height:84px;object-fit:cover;border-radius:16px;border:1px solid #e7eff6;background:#f7fbff;flex-shrink:0;">'
                + '<div style="flex:1;min-width:0;">'
                + '<div style="font-size:16px;font-weight:700;color:#1f314d;margin-bottom:6px;">' + item.name + '</div>'
                + '<div style="font-size:18px;font-weight:700;color:#e10c00;">' + priceStr + '</div>'
                + '</div>';
        }

        // Cột 3: nút Đổi + Xóa
        var col3 = row.children[2];
        if (col3) {
            col3.innerHTML =
                '<button onclick="openSelector(' + item.cat_id + ',\'' + catEsc + '\')" style="display:inline-flex;justify-content:center;align-items:center;background:#0c70c1;color:#fff;border:none;cursor:pointer;padding:11px 14px;border-radius:12px;font-weight:700;font-size:13px;box-shadow:0 8px 20px rgba(12,112,193,.16);">Đổi</button>'
                + '<a href="buildpc.php?action=remove&cat_id=' + item.cat_id + '" style="display:inline-flex;justify-content:center;align-items:center;background:#f03f3f;color:#fff;text-decoration:none;padding:11px 14px;border-radius:12px;font-weight:700;font-size:13px;">Xóa</a>';
        }
    });

    // Cập nhật sidebar
    cbUpdateSidebar(items);
}

// Cập nhật tổng giá và đếm linh kiện trong sidebar
function cbUpdateSidebar(newItems) {
    var appliedPrices = {};
    newItems.forEach(function(i){ appliedPrices[i.cat_id] = Number(i.price); });

    var total = 0, count = 0;
    [1,2,3,4,5,6,7].forEach(function(cid) {
        if (appliedPrices[cid] !== undefined) {
            total += appliedPrices[cid]; count++;
        } else {
            var row = document.getElementById('row-' + cid);
            if (!row) return;
            var priceEl = row.querySelector('[style*="e10c00"]');
            if (priceEl) {
                var n = parseInt(priceEl.textContent.replace(/[^\d]/g,''));
                if (n) { total += n; count++; }
            }
        }
    });

    // Tổng giá
    var totalEl = document.querySelector('aside [style*="28px"][style*="162a46"]');
    if (totalEl) totalEl.textContent = total.toLocaleString('vi-VN') + ' ₫';

    // Số linh kiện
    var countEl = document.querySelector('aside [style*="5e6f85"] span:last-child');
    if (countEl) countEl.textContent = count + '/7';

    // Hiện nút Thêm giỏ / Mua ngay nếu chưa có
    if (total > 0) {
        var aside = document.querySelector('aside');
        if (aside) {
            var startBtn = aside.querySelector('button[onclick*="openSelector"]');
            if (startBtn) {
                var p = startBtn.parentNode;
                p.innerHTML =
                    '<a href="buildpc.php?action=add_to_cart" style="display:inline-flex;align-items:center;justify-content:center;width:100%;background:#ff9800;color:#fff;text-decoration:none;border-radius:14px;padding:14px 18px;font-weight:700;font-size:15px;box-shadow:0 14px 30px rgba(255,152,0,.18);margin-bottom:12px;">Thêm tất cả vào giỏ hàng</a>'
                    + '<a href="buildpc.php?action=buy_now" style="display:inline-flex;align-items:center;justify-content:center;width:100%;background:#0c70c1;color:#fff;text-decoration:none;border-radius:14px;padding:14px 18px;font-weight:700;font-size:15px;box-shadow:0 14px 30px rgba(12,112,193,.16);">Mua ngay</a>';
            }
        }
    }
}
</script>