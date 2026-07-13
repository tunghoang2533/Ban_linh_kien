<?php
$conversations = isset($conversations) && is_array($conversations) ? $conversations : [];
$productComments = isset($productComments) && is_array($productComments) ? $productComments : [];
?>
<style>
    .unread-badge {
        display: inline-flex;
        min-width: 20px; height: 20px;
        align-items: center; justify-content: center;
        border-radius: 999px;
        background: #ef4444;
        color: white;
        font-size: 11px; font-weight: 700;
        padding: 0 6px;
    }
    #toast-message {
        position: fixed;
        right: 24px; bottom: 28px;
        background: #10b981;
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(16,185,129,0.3);
        display: none;
        z-index: 9999;
        font-size: 14px; font-weight: 600;
    }
    /* === Product Picker Modal === */
    #productPickerOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.45);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }
    #productPickerOverlay.open { display: flex; }
    #productPickerModal {
        background: var(--bg-surface);
        width: 520px;
        max-width: calc(100vw - 32px);
        max-height: 80vh;
        border-radius: 20px;
        box-shadow: 0 32px 80px rgba(15,23,42,0.22);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: ppIn .18s ease;
    }
    @keyframes ppIn {
        from { opacity:0; transform:translateY(-12px) scale(.97); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }
    #productPickerModal .pp-header {
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #productPickerModal .pp-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
    }
    #productPickerModal .pp-header button {
        width: 32px; height: 32px;
        border: none; border-radius: 50%;
        background: var(--bg-elevated);
        color: var(--text-muted);
        font-size: 18px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background .15s;
    }
    #productPickerModal .pp-header button:hover { background: #e2e8f0; color: var(--text-primary); }
    #productPickerSearch {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-bottom: 1px solid var(--border-subtle);
        font-size: 14px;
        outline: none;
        background: var(--bg-elevated);
        color: var(--text-primary);
    }
    #productPickerSearch::placeholder { color: var(--text-faint); }
    #productPickerList {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
        min-height: 200px;
    }
    .pp-product-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        cursor: pointer;
        transition: background .15s;
        border: 2px solid transparent;
    }
    .pp-product-item:hover { background: var(--bg-elevated); }
    .pp-product-item.selected {
        background: #fffbeb;
        border-color: #fcd34d;
    }
    .pp-product-item img {
        width: 52px; height: 52px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
        background: #f3f4f6;
    }
    .pp-product-item .pp-img-placeholder {
        width: 52px; height: 52px;
        border-radius: 10px;
        background: #fde68a;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .pp-product-item .pp-info { flex: 1; min-width: 0; }
    .pp-product-item .pp-name {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 3px;
    }
    .pp-product-item .pp-price {
        font-size: 13px;
        color: #e10c00;
        font-weight: 700;
    }
    .pp-product-item .pp-stock {
        font-size: 11px;
        color: #10b981;
        font-weight: 600;
        margin-left: 8px;
    }
    .pp-product-item .pp-stock.out { color: #ef4444; }
    .pp-footer {
        padding: 14px 16px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    #ppSelectBtn {
        flex: 1;
        padding: 10px 16px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: opacity .2s;
    }
    #ppSelectBtn:disabled { opacity: .45; cursor: default; }
    #ppCancelBtn {
        padding: 10px 16px;
        background: var(--bg-elevated);
        color: var(--text-secondary);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
    }
    /* Product preview card in input area */
    #chatProductPreview {
        display: none;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1.5px solid #fcd34d;
        border-radius: 10px;
        padding: 8px 12px;
        margin-bottom: 8px;
    }
    #chatProductPreview img {
        width: 38px; height: 38px;
        border-radius: 7px;
        object-fit: cover;
        flex-shrink: 0;
    }
    #chatProductPreview .cpp-info { flex: 1; min-width: 0; }
    #chatProductPreview .cpp-label {
        font-size: 9px; color: #fbbf24;
        font-weight: 700; text-transform: uppercase;
    }
    #chatProductPreview .cpp-name {
        font-size: 12px; font-weight: 700; color: var(--text-primary);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    #chatProductPreview .cpp-price { font-size: 11px; color: #e10c00; font-weight: 700; }
    #chatProductPreview .cpp-remove {
        width: 22px; height: 22px;
        border-radius: 50%;
        border: none;
        background: #fca5a5;
        color: #dc2626;
        font-size: 13px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
</style>

<div id="toast-message"></div>

<!-- Product Picker Modal -->
<div id="productPickerOverlay">
    <div id="productPickerModal">
        <div class="pp-header">
            <h3>📦 Chọn sản phẩm gợi ý</h3>
            <button id="ppCloseBtn" type="button">&times;</button>
        </div>
        <input type="text" id="productPickerSearch" placeholder="🔍 Tìm kiếm sản phẩm..." autocomplete="off">
        <div id="productPickerList">
            <div style="text-align:center;padding:40px 16px;color:var(--text-faint);font-size:13px;">
                <div style="font-size:32px;margin-bottom:8px;">🔍</div>
                Nhập tên sản phẩm để tìm kiếm
            </div>
        </div>
        <div class="pp-footer">
            <button id="ppCancelBtn" type="button">Hủy</button>
            <button id="ppSelectBtn" type="button" disabled>Gắn sản phẩm này</button>
        </div>
    </div>
</div>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Chat</h1>
            <p>Quản lý cuộc trò chuyện với khách hàng</p>
        </div>
    </div>

    <!-- Chat Layout -->
    <div class="chat-panel" style="display:flex;height:calc(100vh - 220px);min-height:480px;border-radius:20px;overflow:hidden;">
        <!-- Sidebar: conversation list -->
        <div style="width:280px;border-right:1px solid #f1f5f9;display:flex;flex-direction:column;flex-shrink:0;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);font-weight:700;font-size:14px;color:var(--text-primary);background:var(--bg-elevated);">
                <i class="fas fa-comments" style="color:#6366f1;margin-right:8px;"></i>
                Cuộc trò chuyện
            </div>
            <div id="conversations-list" style="flex:1;overflow-y:auto;padding:8px;">
                <?php if (!empty($conversations)): ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item" data-id="<?php echo $conv['id']; ?>" data-user="<?php echo htmlspecialchars($conv['full_name']); ?>"
                             style="padding:12px 14px;border-radius:12px;cursor:pointer;margin-bottom:4px;transition:background 0.15s;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:700;flex-shrink:0;">
                                        <?php echo strtoupper(mb_substr($conv['full_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <span style="font-weight:600;font-size:14px;color:var(--text-primary);"><?php echo htmlspecialchars($conv['full_name']); ?></span>
                                </div>
                                <?php if (!empty($conv['unread_count']) && $conv['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo intval($conv['unread_count']); ?></span>
                                <?php endif; ?>
                            </div>
                            <p style="font-size:12px;color:var(--text-faint);margin:4px 0 0 40px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php
                                $lastMsg = $conv['last_message'] ?? 'Chưa có tin nhắn';
                                // Strip [PRODUCT_REF:...] khỏi preview
                                if (preg_match('/^\[PRODUCT_REF:\{[^\]]+\}\]\n?(.*)$/us', $lastMsg, $pm)) {
                                    $productPreview = '';
                                    if (preg_match('/"name":"([^"]+)"/', $lastMsg, $nm)) {
                                        $productPreview = '📦 ' . mb_substr($nm[1], 0, 30);
                                    }
                                    $lastMsg = $productPreview . (trim($pm[1]) ? ': ' . trim($pm[1]) : '');
                                }
                                echo htmlspecialchars($lastMsg ?: 'Chưa có tin nhắn');
                                ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding:32px 16px;">
                        <i class="fas fa-comment-slash"></i>
                        Chưa có cuộc trò chuyện nào.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main chat area -->
        <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;">
            <!-- Empty state -->
            <div id="chat-empty" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-faint);">
                <i class="fas fa-comments" style="font-size:48px;margin-bottom:16px;opacity:0.3;"></i>
                <p style="font-size:15px;font-weight:600;">Chọn một cuộc trò chuyện</p>
                <p style="font-size:13px;margin-top:4px;">để xem và trả lời tin nhắn của khách hàng</p>
            </div>

            <!-- Active chat -->
            <div id="chat-area" style="flex:1;display:none;flex-direction:column;overflow:hidden;">
                <div id="chat-header" style="padding:14px 20px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;gap:10px;background:var(--bg-surface);">
                    <div id="chat-user-avatar" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-size:14px;font-weight:700;"></div>
                    <div>
                        <div style="font-weight:700;font-size:14px;color:var(--text-primary);" id="chat-user-name"></div>
                        <div style="font-size:12px;color:#10b981;"><i class="fas fa-circle" style="font-size:8px;"></i> Đang online</div>
                    </div>
                </div>

                <div id="chat-messages" style="flex:1;overflow-y:auto;padding:20px;background:var(--bg-elevated);display:flex;flex-direction:column;gap:10px;">
                    <!-- Messages rendered by JS -->
                </div>

                <div style="padding:14px 20px;border-top:1px solid #f1f5f9;background:var(--bg-surface);">
                    <!-- Product Preview Banner -->
                    <div id="chatProductPreview">
                        <img id="cppImg" src="" alt="" onerror="this.style.display='none'" style="display:none;">
                        <div id="cppImgPlaceholder" style="width:38px;height:38px;border-radius:7px;background:#fde68a;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">📦</div>
                        <div class="cpp-info">
                            <div class="cpp-label">📌 Sản phẩm gợi ý</div>
                            <div class="cpp-name" id="cppName"></div>
                            <div class="cpp-price" id="cppPrice"></div>
                        </div>
                        <button class="cpp-remove" id="cppRemoveBtn" type="button" title="Bỏ sản phẩm">&times;</button>
                    </div>
                    <form id="chat-form" style="display:flex;gap:8px;align-items:center;">
                        <input type="hidden" id="conversation-id">
                        <button type="button" id="attachProductBtn" title="Gợi ý sản phẩm"
                            style="width:40px;height:40px;border:none;border-radius:10px;background:linear-gradient(135deg,#fef3c7,#fde68a);color:#d97706;font-size:18px;cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:background .2s;"
                            onmouseover="this.style.background='linear-gradient(135deg,#fde68a,#fbbf24)'" onmouseout="this.style.background='linear-gradient(135deg,#fef3c7,#fde68a)'"
                        >📦</button>
                        <input type="text" id="message-input" class="form-control"
                               placeholder="Nhập tin nhắn..." required
                               style="flex:1;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Gửi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
$(document).ready(function() {
    var currentConversationId = null;

    // Hover style for conversation items
    $(document).on('mouseenter', '.conversation-item', function() {
        if (!$(this).hasClass('active')) $(this).css('background', '#f8fafc');
    }).on('mouseleave', '.conversation-item', function() {
        if (!$(this).hasClass('active')) $(this).css('background', '');
    });

    function activateConversation(item) {
        $('.conversation-item').removeClass('active').css('background', '');
        item.addClass('active').css('background', 'rgba(99,102,241,0.08)');
        currentConversationId = item.data('id');
        var userName = item.data('user') || 'Khách hàng';
        $('#conversation-id').val(currentConversationId);
        $('#chat-user-name').text(userName);
        $('#chat-user-avatar').text(userName.charAt(0).toUpperCase());
        $('#chat-empty').hide();
        $('#chat-area').css('display', 'flex');
        loadMessages(currentConversationId);
        // Clear unread badge
        item.find('.unread-badge').remove();
    }

    $(document).on('click', '.conversation-item', function() {
        activateConversation($(this));
    });

    if ($('.conversation-item').length > 0) {
        activateConversation($('.conversation-item').first());
    } else {
        $('#chat-empty').show();
    }

    // ===== Product Picker State =====
    var selectedProduct = null;

    // Mở modal khi bấm nút 📦
    $('#attachProductBtn').on('click', function() {
        selectedProduct = null;
        $('#productPickerSearch').val('');
        $('#productPickerList').html('<div style="text-align:center;padding:40px 16px;color:var(--text-faint);font-size:13px;"><div style="font-size:32px;margin-bottom:8px;">🔍</div>Nhập tên sản phẩm để tìm kiếm</div>');
        $('#ppSelectBtn').prop('disabled', true);
        $('.pp-product-item').removeClass('selected');
        $('#productPickerOverlay').addClass('open');
        setTimeout(function(){ $('#productPickerSearch').focus(); }, 120);
    });

    // Đóng modal
    function closeProductPicker() {
        $('#productPickerOverlay').removeClass('open');
    }
    $('#ppCloseBtn, #ppCancelBtn').on('click', closeProductPicker);
    $('#productPickerOverlay').on('click', function(e) {
        if ($(e.target).is('#productPickerOverlay')) closeProductPicker();
    });

    // Tìm kiếm sản phẩm (debounce)
    var _ppTimer = null;
    $('#productPickerSearch').on('input', function() {
        clearTimeout(_ppTimer);
        var kw = $(this).val().trim();
        if (!kw) {
            $('#productPickerList').html('<div style="text-align:center;padding:40px 16px;color:var(--text-faint);font-size:13px;"><div style="font-size:32px;margin-bottom:8px;">🔍</div>Nhập tên sản phẩm để tìm kiếm</div>');
            return;
        }
        $('#productPickerList').html('<div style="text-align:center;padding:32px;color:var(--text-faint);">⏳ Đang tìm...</div>');
        _ppTimer = setTimeout(function() {
            $.get('<?php echo BASE_URL; ?>admin/?page=chat&action=search_products&q=' + encodeURIComponent(kw), function(res) {
                if (!res.success || !res.products.length) {
                    $('#productPickerList').html('<div style="text-align:center;padding:40px 16px;color:var(--text-faint);font-size:13px;"><div style="font-size:32px;margin-bottom:8px;">😔</div>Không tìm thấy sản phẩm nào</div>');
                    return;
                }
                var html = '';
                res.products.forEach(function(p) {
                    var imgTag = p.image
                        ? '<img src="' + p.image + '" onerror="this.style.display=\'none\'" alt="">'
                        : '<div class="pp-img-placeholder">📦</div>';
                    var stockTag = p.in_stock
                        ? '<span class="pp-stock">Còn hàng</span>'
                        : '<span class="pp-stock out">Hết hàng</span>';
                    html += '<div class="pp-product-item" data-product="' + escapeAttr(JSON.stringify(p)) + '">' +
                        imgTag +
                        '<div class="pp-info">' +
                            '<div class="pp-name">' + escapeHtml(p.name) + '</div>' +
                            '<div class="pp-price">' + escapeHtml(p.price) + stockTag + '</div>' +
                        '</div>' +
                        '<div style="font-size:12px;color:var(--text-faint);">✓ Chọn</div>' +
                        '</div>';
                });
                $('#productPickerList').html(html);
            }, 'json');
        }, 280);
    });

    // Chọn sản phẩm trong danh sách
    $(document).on('click', '.pp-product-item', function() {
        $('.pp-product-item').removeClass('selected');
        $(this).addClass('selected');
        selectedProduct = JSON.parse($(this).attr('data-product'));
        $('#ppSelectBtn').prop('disabled', false);
    });

    // Xác nhận chọn sản phẩm → hiện preview
    $('#ppSelectBtn').on('click', function() {
        if (!selectedProduct) return;
        // Hiện banner preview
        var $preview = $('#chatProductPreview');
        $('#cppName').text(selectedProduct.name);
        $('#cppPrice').text(selectedProduct.price);
        if (selectedProduct.image) {
            $('#cppImg').attr('src', selectedProduct.image).show();
            $('#cppImgPlaceholder').hide();
        } else {
            $('#cppImg').hide();
            $('#cppImgPlaceholder').show();
        }
        $preview.css('display', 'flex');
        closeProductPicker();
        $('#message-input').focus();
    });

    // Xoá sản phẩm đã chọn
    $('#cppRemoveBtn').on('click', function() {
        selectedProduct = null;
        $('#chatProductPreview').hide();
    });

    // ===== Gửi tin nhắn (có hoặc không kèm product_ref) =====
    $('#chat-form').submit(function(e) {
        e.preventDefault();
        var conversationId = $('#conversation-id').val();
        var message = $('#message-input').val().trim();
        if (!message && !selectedProduct) return;
        if (!conversationId) return;

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);

        var postData = { conversation_id: conversationId, message: message || '' };
        if (selectedProduct) {
            postData.product_ref = JSON.stringify(selectedProduct);
        }

        $.post('<?php echo BASE_URL; ?>admin/?page=chat&action=send', postData,
            function(response) {
                if (response.success) {
                    $('#message-input').val('');
                    selectedProduct = null;
                    $('#chatProductPreview').hide();
                    showToast('✓ Đã gửi tin nhắn');
                    loadMessages(conversationId);
                }
                $btn.prop('disabled', false);
            }, 'json'
        ).fail(function() { $btn.prop('disabled', false); });
    });

    // Enter to send
    $('#message-input').keydown(function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#chat-form').submit();
        }
    });

    function loadMessages(conversationId) {
        $.get('<?php echo BASE_URL; ?>admin/?page=chat&action=get&id=' + conversationId,
            function(response) {
                if (response.success) displayMessages(response.messages);
            }, 'json'
        );
    }

    function displayMessages(messages) {
        var $chat = $('#chat-messages');
        $chat.empty();
        messages.forEach(function(msg) {
            var isAdmin = msg.is_admin_reply;
            var parsed = parseMessageWithRef(msg.message);

            var $wrap = $('<div>').css({
                display: 'flex',
                justifyContent: isAdmin ? 'flex-end' : 'flex-start'
            });
            var $container = $('<div>').css({ maxWidth: '72%' });

            // Product card nếu có ref
            if (parsed.ref) {
                var ref = parsed.ref;
                var imgHtml = ref.image
                    ? '<img src="' + escapeHtml(ref.image) + '" onerror="this.style.display=\'none\'" style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid #fde68a;flex-shrink:0;">'
                    : '<div style="width:44px;height:44px;border-radius:8px;background:#fde68a;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">\uD83D\uDCE6</div>';
                var priceHtml = ref.price
                    ? '<div style="font-size:12px;color:#e10c00;font-weight:700;margin-top:2px;">' + escapeHtml(ref.price) + '</div>'
                    : '';
                var $card = $('<a>').attr({ href: ref.url || '#', target: '_blank' }).css({
                    display: 'flex', alignItems: 'center', gap: '10px',
                    borderRadius: '12px',
                    padding: '10px 12px', marginBottom: '6px',
                    textDecoration: 'none', transition: 'box-shadow .2s',
                    cursor: 'pointer'
                });
                var cardLabel = isAdmin
                    ? '\uD83D\uDCA1 S\u1EA3n ph\u1EA9m g\u1EE3i \u00FD'  // 💡 Sản phẩm gợi ý
                    : '\uD83D\uDCCC S\u1EA3n ph\u1EA9m \u0111ang h\u1ECFi'; // 📌 Sản phẩm đang hỏi
                var cardBg = isAdmin
                    ? 'linear-gradient(135deg,#eff6ff,#dbeafe)'
                    : 'linear-gradient(135deg,#fffbeb,#fef3c7)';
                var cardBorder = isAdmin ? '1.5px solid #93c5fd' : '1.5px solid #fcd34d';
                var labelColor = isAdmin ? '#1e40af' : '#92400e';
                var shadowColor = isAdmin ? 'rgba(59,130,246,0.3)' : 'rgba(245,158,11,0.3)';
                $card.css({
                    background: cardBg,
                    border: cardBorder
                }).hover(
                    function() { $(this).css('boxShadow','0 4px 16px ' + shadowColor); },
                    function() { $(this).css('boxShadow','none'); }
                );
                $card.html(
                    imgHtml +
                    '<div style="flex:1;min-width:0;">'
                        + '<div style="font-size:9px;color:' + labelColor + ';font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">' + cardLabel + '</div>'
                        + '<div style="font-size:13px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">' + escapeHtml(ref.name) + '</div>'
                        + priceHtml
                    + '</div>'
                    + '<div style="font-size:15px;color:' + (isAdmin ? '#3b82f6' : '#f59e0b') + ';flex-shrink:0;">\uD83D\uDD17</div>'
                );
                $container.append($card);
            }

            // Bubble tin nhắn
            if (parsed.text) {
                var $bubble = $('<div>').css({
                    padding: '10px 14px',
                    borderRadius: isAdmin ? '16px 16px 4px 16px' : '16px 16px 16px 4px',
                    background: isAdmin ? 'linear-gradient(135deg,#6366f1,#8b5cf6)' : 'white',
                    color: isAdmin ? 'white' : '#1e293b',
                    boxShadow: '0 2px 8px rgba(15,23,42,0.07)',
                    border: isAdmin ? 'none' : '1px solid #f1f5f9',
                    fontSize: '14px',
                    lineHeight: '1.5'
                });
                $bubble.html(
                    '<p style="margin:0 0 4px;">' + escapeHtml(parsed.text) + '</p>' +
                    '<small style="opacity:0.65;font-size:11px;">' + msg.created_at + '</small>'
                );
                $container.append($bubble);
            } else if (!parsed.ref) {
                // fallback nếu không có gì
                var $bubble = $('<div>').css({
                    padding: '10px 14px',
                    borderRadius: isAdmin ? '16px 16px 4px 16px' : '16px 16px 16px 4px',
                    background: isAdmin ? 'linear-gradient(135deg,#6366f1,#8b5cf6)' : 'white',
                    color: isAdmin ? 'white' : '#1e293b',
                    boxShadow: '0 2px 8px rgba(15,23,42,0.07)',
                    border: isAdmin ? 'none' : '1px solid #f1f5f9',
                    fontSize: '14px', lineHeight: '1.5'
                });
                $bubble.html('<p style="margin:0 0 4px;">' + escapeHtml(msg.message) + '</p><small style="opacity:0.65;font-size:11px;">' + msg.created_at + '</small>');
                $container.append($bubble);
            }

            $wrap.append($container);
            $chat.append($wrap);
        });
        $chat.scrollTop($chat[0].scrollHeight);
    }

    function showToast(text) {
        var $t = $('#toast-message');
        $t.text(text).fadeIn(200);
        clearTimeout(window._toastTimer);
        window._toastTimer = setTimeout(function() { $t.fadeOut(300); }, 2500);
    }

    // Auto-refresh messages every 3s
    setInterval(function() {
        if (currentConversationId) loadMessages(currentConversationId);
    }, 3000);

    // Auto-refresh conversation list every 6s
    setInterval(function() {
        $.get('<?php echo BASE_URL; ?>admin/?page=chat&action=list', function(response) {
            if (response.success && response.conversations) {
                updateConversationsList(response.conversations);
            }
        }, 'json');
    }, 6000);

    function updateConversationsList(conversations) {
        var $list = $('#conversations-list');
        if (conversations.length === 0) {
            $list.html('<div class="empty-state" style="padding:32px 16px;"><i class="fas fa-comment-slash"></i> Chưa có cuộc trò chuyện.</div>');
            return;
        }
        $list.empty();
        conversations.forEach(function(conv) {
            var badge = conv.unread_count > 0 ? '<span class="unread-badge">' + conv.unread_count + '</span>' : '';
            var isActive = currentConversationId == conv.id;
            var $item = $('<div class="conversation-item' + (isActive ? ' active' : '') + '">')
                .attr({ 'data-id': conv.id, 'data-user': conv.full_name })
                .css({
                    padding: '12px 14px',
                    borderRadius: '12px',
                    cursor: 'pointer',
                    marginBottom: '4px',
                    background: isActive ? 'rgba(99,102,241,0.08)' : ''
                });
            var initial = (conv.full_name || 'U').charAt(0).toUpperCase();
            $item.html(
                '<div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">' +
                '<div style="display:flex;align-items:center;gap:8px;">' +
                '<div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:700;flex-shrink:0;">' + initial + '</div>' +
                '<span style="font-weight:600;font-size:14px;color:var(--text-primary);">' + escapeHtml(conv.full_name) + '</span>' +
                '</div>' + badge + '</div>' +
                '<p style="font-size:12px;color:var(--text-faint);margin:4px 0 0 40px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                escapeHtml(formatLastMessage(conv.last_message)) + '</p>'
            );
            $list.append($item);
        });
    }

    function parseMessageWithRef(rawMessage) {
        if (!rawMessage) return { ref: null, text: '' };
        var refMatch = rawMessage.match(/^\[PRODUCT_REF:(\{[^\]]+\})\]\n?/);
        if (!refMatch) return { ref: null, text: rawMessage };
        try {
            var ref = JSON.parse(refMatch[1]);
            var text = rawMessage.substring(refMatch[0].length);
            return { ref: ref, text: text };
        } catch(e) {
            return { ref: null, text: rawMessage };
        }
    }

    function formatLastMessage(rawMessage) {
        if (!rawMessage) return 'Chưa có tin nhắn';
        var parsed = parseMessageWithRef(rawMessage);
        if (parsed.ref) {
            var productPart = '\uD83D\uDCE6 ' + (parsed.ref.name || 'Sản phẩm');
            return parsed.text ? productPart + ': ' + parsed.text : productPart;
        }
        return rawMessage;
    }

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
        });
    }

    function escapeAttr(text) {
        return String(text).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
});
</script>