<div class="container mt-5">
    <h2>Chat với Admin</h2>
    <div class="card">
        <div class="card-body">
            <div id="chat-messages" style="height: 400px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
                <!-- Messages will be loaded here -->
            </div>
            <div class="mt-3">
                <form id="chat-form">
                    <div class="input-group">
                        <input type="text" id="message-input" class="form-control" placeholder="Nhập tin nhắn...">
                        <button type="submit" class="btn btn-primary">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadMessages();

    $('#chat-form').submit(function(e) {
        e.preventDefault();
        var message = $('#message-input').val().trim();
        if (!message) return;

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);

        $.post('<?php echo BASE_URL; ?>chat.php', {
            action: 'send',
            message: message
        }, function(response) {
            if (response.success) {
                $('#message-input').val('');
                loadMessages();
            } else if (response.error) {
                alert('Lỗi: ' + response.error);
            }
            $btn.prop('disabled', false);
        }, 'json').fail(function(xhr) {
            console.error('Lỗi gửi:', xhr.status, xhr.responseText);
            $btn.prop('disabled', false);
        });
    });

    function loadMessages() {
        $.get('<?php echo BASE_URL; ?>chat.php?action=get', function(response) {
            if (response.success) {
                displayMessages(response.messages);
            }
        }, 'json');
    }

    function displayMessages(messages) {
        var chatMessages = $('#chat-messages');
        chatMessages.empty();
        messages.forEach(function(msg) {
            var messageClass = msg.is_admin_reply ? 'text-end' : 'text-start';
            var sender = msg.is_admin_reply ? 'Admin' : '<?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name'], ENT_QUOTES) : 'Bạn'; ?>';
            var safeMsg = $('<div>').text(msg.message).html();
            chatMessages.append('<div class="' + messageClass + '"><strong>' + sender + ':</strong> ' + safeMsg + '<br><small>' + msg.created_at + '</small></div><br>');
        });
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }

    // Auto refresh messages every 3 seconds
    setInterval(loadMessages, 3000);
});
</script>