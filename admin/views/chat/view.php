<?php
$userName    = isset($userName)       ? $userName       : 'User';
$messages    = isset($messages) && is_array($messages) ? $messages : [];
$conversationId = isset($conversationId) ? $conversationId : 0;
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Chat với <?php echo htmlspecialchars($userName); ?></h1>
            <p>Lịch sử cuộc trò chuyện</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/?page=chat" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="chat-panel" style="display:flex;flex-direction:column;height:calc(100vh-230px);min-height:520px;border-radius:20px;overflow:hidden;max-width:900px;margin:0 auto;">
        <!-- Header -->
        <div style="padding:16px 24px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;gap:12px;background:var(--bg-elevated);">
            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-size:16px;font-weight:700;">
                <?php echo strtoupper(mb_substr($userName, 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight:700;font-size:15px;color:var(--text-primary);"><?php echo htmlspecialchars($userName); ?></div>
                <div style="font-size:12px;color:#10b981;"><i class="fas fa-circle" style="font-size:8px;"></i> Đang hoạt động</div>
            </div>
        </div>

        <!-- Messages -->
        <div id="chat-messages" style="flex:1;overflow-y:auto;padding:24px;background:var(--bg-elevated);display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($messages as $msg): ?>
                <?php
                $isAdmin = $msg['is_admin_reply'];
                $rawMsg  = $msg['message'];
                $productRef = null;
                $displayText = $rawMsg;

                // Parse [PRODUCT_REF:{...}]
                if (preg_match('/^\[PRODUCT_REF:(\{[^\]]+\})\]\n?/u', $rawMsg, $m)) {
                    $decoded = json_decode($m[1], true);
                    if (is_array($decoded) && !empty($decoded['name'])) {
                        $productRef  = $decoded;
                        $displayText = preg_replace('/^\[PRODUCT_REF:(\{[^\]]+\})\]\n?/u', '', $rawMsg);
                    }
                }
                ?>
                <div style="display:flex;justify-content:<?php echo $isAdmin ? 'flex-end' : 'flex-start'; ?>;">
                    <div style="max-width:70%;">
                        <?php if ($productRef): ?>
                        <!-- Product Card -->
                        <a href="<?php echo htmlspecialchars($productRef['url'] ?? '#'); ?>" target="_blank"
                           style="display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1.5px solid #fcd34d;border-radius:12px;padding:10px 12px;margin-bottom:6px;text-decoration:none;transition:box-shadow .2s;"
                           onmouseover="this.style.boxShadow='0 4px 16px rgba(245,158,11,0.25)'" onmouseout="this.style.boxShadow='none'">
                            <?php if (!empty($productRef['image'])): ?>
                            <img src="<?php echo htmlspecialchars($productRef['image']); ?>"
                                 onerror="this.style.display='none'"
                                 style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid #fde68a;flex-shrink:0;">
                            <?php else: ?>
                            <div style="width:48px;height:48px;border-radius:8px;background:#fde68a;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">📦</div>
                            <?php endif; ?>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:10px;color:#fbbf24;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">📌 Sản phẩm đang hỏi</div>
                                <div style="font-size:13px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px;"><?php echo htmlspecialchars($productRef['name']); ?></div>
                                <?php if (!empty($productRef['price'])): ?>
                                <div style="font-size:12px;color:#e10c00;font-weight:700;margin-top:2px;"><?php echo htmlspecialchars($productRef['price']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:16px;color:#f59e0b;flex-shrink:0;">🔗</div>
                        </a>
                        <?php endif; ?>
                        <!-- Bubble tin nhắn -->
                        <div style="padding:12px 16px;border-radius:<?php echo $isAdmin ? '16px 16px 4px 16px' : '16px 16px 16px 4px'; ?>;
                             background:<?php echo $isAdmin ? 'linear-gradient(135deg,#6366f1,#8b5cf6)' : 'white'; ?>;
                             color:<?php echo $isAdmin ? 'white' : '#1e293b'; ?>;
                             box-shadow:0 2px 8px rgba(15,23,42,0.07);
                             <?php echo $isAdmin ? '' : 'border:1px solid #f1f5f9;'; ?>
                             font-size:14px;line-height:1.5;">
                            <p style="margin:0 0 4px;"><?php echo nl2br(htmlspecialchars($displayText)); ?></p>
                            <small style="opacity:0.65;font-size:11px;"><?php echo $msg['is_admin_reply'] ? 'Admin' : htmlspecialchars($msg['full_name'] ?? $userName); ?> · <?php echo $msg['created_at']; ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Input -->
        <div style="padding:16px 24px;border-top:1px solid #f1f5f9;background:var(--bg-surface);">
            <form id="chat-form" style="display:flex;gap:10px;">
                <input type="hidden" id="conversation-id" value="<?php echo intval($conversationId); ?>">
                <input type="text" id="message-input" class="form-control"
                       placeholder="Nhập tin nhắn và nhấn Enter..." required style="flex:1;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Gửi
                </button>
            </form>
        </div>
    </div>
</main>

<script>
$(document).ready(function() {
    // Scroll to bottom on load
    var $chat = $('#chat-messages');
    $chat.scrollTop($chat[0].scrollHeight);

    $('#message-input').keydown(function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#chat-form').submit();
        }
    });

    $('#chat-form').submit(function(e) {
        e.preventDefault();
        var conversationId = $('#conversation-id').val();
        var message = $('#message-input').val().trim();
        if (!message) return;
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);
        $.post('<?php echo BASE_URL; ?>admin/?page=chat&action=send',
            { conversation_id: conversationId, message: message },
            function(response) {
                if (response.success) {
                    $('#message-input').val('');
                    loadMessages();
                }
                $btn.prop('disabled', false);
            }, 'json'
        ).fail(function() { $btn.prop('disabled', false); });
    });

    function loadMessages() {
        var cid = $('#conversation-id').val();
        $.get('<?php echo BASE_URL; ?>admin/?page=chat&action=get&id=' + cid,
            function(response) {
                if (response.success) displayMessages(response.messages);
            }, 'json'
        );
    }

    function displayMessages(messages) {
        $chat.empty();
        messages.forEach(function(msg) {
            var isAdmin = msg.is_admin_reply;
            var $wrap = $('<div>').css({ display:'flex', justifyContent: isAdmin ? 'flex-end' : 'flex-start' });
            var $container = $('<div>').css({ maxWidth: '70%' });

            // Parse product ref
            var parsed = parseMessageWithRef(msg.message);

            // Product card
            if (parsed.ref) {
                var ref = parsed.ref;
                var imgHtml = ref.image
                    ? '<img src="' + escapeHtml(ref.image) + '" onerror="this.style.display=\'none\'" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid #fde68a;flex-shrink:0;">'
                    : '<div style="width:48px;height:48px;border-radius:8px;background:#fde68a;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">\uD83D\uDCE6</div>';
                var $card = $('<a>').attr({ href: ref.url || '#', target: '_blank' }).css({
                    display: 'flex', alignItems: 'center', gap: '10px',
                    background: 'linear-gradient(135deg,#fffbeb,#fef3c7)',
                    border: '1.5px solid #fcd34d', borderRadius: '12px',
                    padding: '10px 12px', marginBottom: '6px',
                    textDecoration: 'none', transition: 'box-shadow .2s'
                }).hover(
                    function() { $(this).css('boxShadow','0 4px 16px rgba(245,158,11,0.25)'); },
                    function() { $(this).css('boxShadow','none'); }
                );
                var priceHtml = ref.price ? '<div style="font-size:12px;color:#e10c00;font-weight:700;margin-top:2px;">' + escapeHtml(ref.price) + '</div>' : '';
                $card.html(
                    imgHtml +
                    '<div style="flex:1;min-width:0;">'
                        + '<div style="font-size:10px;color:#fbbf24;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">\uD83D\uDCCC S\u1EA3n ph\u1EA9m \u0111ang h\u1ECFi</div>'
                        + '<div style="font-size:13px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px;">' + escapeHtml(ref.name) + '</div>'
                        + priceHtml
                    + '</div>'
                    + '<div style="font-size:16px;color:#f59e0b;flex-shrink:0;">\uD83D\uDD17</div>'
                );
                $container.append($card);
            }

            // Bubble
            var $bubble = $('<div>').css({
                padding:'12px 16px',
                borderRadius: isAdmin ? '16px 16px 4px 16px' : '16px 16px 16px 4px',
                background: isAdmin ? 'linear-gradient(135deg,#6366f1,#8b5cf6)' : 'white',
                color: isAdmin ? 'white' : '#1e293b',
                boxShadow: '0 2px 8px rgba(15,23,42,0.07)',
                border: isAdmin ? 'none' : '1px solid #f1f5f9',
                fontSize: '14px', lineHeight: '1.5'
            });
            $bubble.html('<p style="margin:0 0 4px;">' + escapeHtml(parsed.text) + '</p>' +
                '<small style="opacity:0.65;font-size:11px;">' + (isAdmin ? 'Admin' : escapeHtml(msg.full_name || '')) + ' \u00b7 ' + msg.created_at + '</small>');
            $container.append($bubble);
            $wrap.append($container);
            $chat.append($wrap);
        });
        $chat.scrollTop($chat[0].scrollHeight);
    }

    function parseMessageWithRef(rawMessage) {
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

    function escapeHtml(t) {
        return String(t).replace(/[&<>"']/g, function(m){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
        });
    }

    setInterval(loadMessages, 4000);
});
</script>