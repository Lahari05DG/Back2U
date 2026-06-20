/* ============================================
   Back2U Lost & Found — Chat System
   AJAX-powered real-time messaging for item
   discussions between finders and claimers.
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

    // Only initialise if the chat interface is present on the page
    var chatMessages = document.querySelector('.chat-messages');
    if (!chatMessages) return;

    // ========================================
    // VARIABLES
    // ========================================
    var itemId = getItemIdFromUrl();
    var lastMessageId = 0;
    var pollInterval = 2000; // 2 seconds
    var pollTimer = null;
    var isSending = false;

    var chatInput = document.querySelector('.chat-input-bar input');
    var sendBtn = document.querySelector('.chat-input-bar button');

    if (!itemId) {
        chatMessages.innerHTML =
            '<div class="empty-state">' +
            '  <i class="fa-solid fa-circle-exclamation"></i>' +
            '  <h3>No item selected</h3>' +
            '  <p>Could not determine which item to load messages for.</p>' +
            '</div>';
        return;
    }

    // ========================================
    // HELPER: Get item_id from URL params
    // ========================================
    function getItemIdFromUrl() {
        var params = new URLSearchParams(window.location.search);
        return params.get('item_id') || params.get('id') || null;
    }

    // ========================================
    // HELPER: Format MySQL datetime → readable
    // ========================================
    function formatTime(mysqlDatetime) {
        if (!mysqlDatetime) return '';
        try {
            // Handle MySQL format: "2025-06-17 14:30:00"
            var date = new Date(mysqlDatetime.replace(' ', 'T'));
            if (isNaN(date.getTime())) return mysqlDatetime;

            var hours = date.getHours();
            var minutes = date.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12; // 0 → 12
            var minutesStr = minutes < 10 ? '0' + minutes : minutes;

            // Check if today
            var today = new Date();
            var isToday = date.toDateString() === today.toDateString();

            if (isToday) {
                return hours + ':' + minutesStr + ' ' + ampm;
            }

            // Check if yesterday
            var yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            var isYesterday = date.toDateString() === yesterday.toDateString();

            if (isYesterday) {
                return 'Yesterday ' + hours + ':' + minutesStr + ' ' + ampm;
            }

            // Otherwise show date
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return months[date.getMonth()] + ' ' + date.getDate() + ', ' +
                hours + ':' + minutesStr + ' ' + ampm;
        } catch (e) {
            return mysqlDatetime;
        }
    }

    // ========================================
    // HELPER: Scroll to bottom of chat
    // ========================================
    function scrollToBottom(smooth) {
        if (smooth) {
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // ========================================
    // HELPER: Escape HTML to prevent XSS
    // ========================================
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // ========================================
    // CREATE CHAT BUBBLE HTML
    // ========================================
    function createBubble(msg) {
        var isOwn = msg.is_mine || false;
        var bubbleClass = isOwn ? 'mine' : 'other';

        var bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + bubbleClass;

        var html = '';

        // Show sender name for other people's messages
        if (!isOwn && msg.sender_name) {
            html += '<div class="chat-sender">' + escapeHtml(msg.sender_name) + '</div>';
        }

        html += '<div class="chat-text">' + escapeHtml(msg.message) + '</div>';
        html += '<div class="chat-time">' + formatTime(msg.created_at) + '</div>';

        bubble.innerHTML = html;
        return bubble;
    }

    // ========================================
    // FETCH MESSAGES
    // ========================================
    function fetchMessages() {
        var xhr = new XMLHttpRequest();
        var url = 'php/api_chat.php?item_id=' + encodeURIComponent(itemId) +
            '&after=' + encodeURIComponent(lastMessageId);

        xhr.open('GET', url, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;

            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);

                    if (data.messages && data.messages.length > 0) {
                        var wasAtBottom = isScrolledToBottom();

                        data.messages.forEach(function (msg) {
                            var bubble = createBubble(msg);
                            chatMessages.appendChild(bubble);

                            // Track latest message ID
                            var msgId = parseInt(msg.id, 10);
                            if (msgId > lastMessageId) {
                                lastMessageId = msgId;
                            }
                        });

                        // Auto-scroll if user was at bottom or if it's their own message
                        if (wasAtBottom) {
                            scrollToBottom(true);
                        }
                    }
                } catch (e) {
                    console.error('Back2U Chat: Error parsing messages', e);
                }
            }
        };
        xhr.send();
    }

    // ========================================
    // CHECK IF SCROLLED TO BOTTOM
    // ========================================
    function isScrolledToBottom() {
        var threshold = 100; // px from bottom
        return chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight < threshold;
    }

    // ========================================
    // SEND MESSAGE
    // ========================================
    function sendMessage() {
        if (!chatInput || isSending) return;

        var text = chatInput.value.trim();
        if (!text) return;

        isSending = true;
        if (sendBtn) sendBtn.disabled = true;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/api_chat.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;

            isSending = false;
            if (sendBtn) sendBtn.disabled = false;

            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        chatInput.value = '';
                        chatInput.focus();
                        // Immediately fetch new messages to show the sent message
                        fetchMessages();
                    } else {
                        if (window.showToast) {
                            window.showToast(data.error || 'Failed to send message.', 'error');
                        }
                    }
                } catch (e) {
                    if (window.showToast) {
                        window.showToast('Error sending message.', 'error');
                    }
                }
            } else {
                if (window.showToast) {
                    window.showToast('Network error. Please try again.', 'error');
                }
            }
        };

        var params = 'item_id=' + encodeURIComponent(itemId) +
            '&message=' + encodeURIComponent(text);
        xhr.send(params);
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================

    // Send button click
    if (sendBtn) {
        sendBtn.addEventListener('click', function (e) {
            e.preventDefault();
            sendMessage();
        });
    }

    // Enter key in chat input
    if (chatInput) {
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    // ========================================
    // INITIALISE
    // ========================================

    // Fetch initial messages
    fetchMessages();

    // Scroll to bottom on first load (slight delay for rendering)
    setTimeout(function () {
        scrollToBottom(false);
    }, 500);

    // Start polling for new messages
    pollTimer = setInterval(fetchMessages, pollInterval);

    // Stop polling when page is not visible (saves resources)
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        } else {
            // Resume polling and fetch immediately
            fetchMessages();
            if (!pollTimer) {
                pollTimer = setInterval(fetchMessages, pollInterval);
            }
        }
    });

}); // end DOMContentLoaded
