<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$current_user_id = (int) $_SESSION['user_id'];
$matches = [];

try {
    $stmt = $pdo->prepare("
        SELECT
            m.id AS match_id,
            m.matched_at AS matched_on,
            u.id AS partner_id,
            u.username AS partner_name,
            p.occupation,
            p.profile_image
        FROM matches m
        JOIN users u ON u.id = CASE
            WHEN m.user1_id = :uid THEN m.user2_id
            ELSE m.user1_id
        END
        LEFT JOIN profiles p ON p.user_id = u.id
        WHERE m.user1_id = :uid OR m.user2_id = :uid
        ORDER BY m.matched_at DESC
    ");
    $stmt->execute([':uid' => $current_user_id]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Matches query error: ' . $e->getMessage());
}

$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50/50 flex justify-center">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Your Connections</h1>
            <p class="text-slate-500 text-sm mt-0.5">Select a match to open the conversation thread.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-[60vh]">
            <aside class="lg:col-span-4 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Inbox</p>
                </div>
                <div class="flex-1 overflow-y-auto divide-y divide-slate-50" id="matchList">
                    <?php if (empty($matches)): ?>
                        <div class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-user-group text-3xl mb-3 block opacity-60"></i>
                            <p class="text-sm font-medium text-slate-600">No active connections yet.</p>
                            <a href="discover.php" class="inline-block mt-3 text-xs font-bold text-pink-500 hover:text-rose-600">Discover profiles</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($matches as $match):
                            $avatar = !empty($match['profile_image'])
                                ? '../uploads/' . htmlspecialchars($match['profile_image'], ENT_QUOTES, 'UTF-8')
                                : 'https://ui-avatars.com/api/?name=' . urlencode($match['partner_name']) . '&background=random&size=150';
                        ?>
                            <button type="button"
                                    class="match-sidebar-row w-full text-left p-4 flex items-center space-x-3 hover:bg-slate-50 transition"
                                    data-match-id="<?= (int) $match['match_id']; ?>"
                                    data-partner-name="<?= htmlspecialchars($match['partner_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <img src="<?= $avatar; ?>" alt="" class="w-12 h-12 rounded-xl object-cover ring-2 ring-slate-100 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate"><?= htmlspecialchars($match['partner_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($match['occupation'] ?? 'Member', ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <section id="chat-window-pane" class="hidden lg:flex lg:col-span-8 flex-col bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <header class="border-b border-slate-100 px-6 py-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 bg-pink-50 text-pink-500 rounded-xl flex items-center justify-center font-bold flex-shrink-0" id="chat-header-initial">?</div>
                        <div class="min-w-0">
                            <h2 id="chat-pane-title" class="text-sm font-bold text-slate-800 truncate">Select a conversation</h2>
                            <span class="text-xs text-emerald-600 font-medium">Connected</span>
                        </div>
                    </div>
                    <button type="button" id="terminateBtn" class="hidden text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                        End Connection
                    </button>
                </header>
                <div id="messages-stream-box" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/40 min-h-[320px]"></div>
                <form id="chat-delivery-form" class="border-t border-slate-100 p-4 flex items-center gap-3">
                    <input type="hidden" id="active-match-id-cache" value="">
                    <input type="text" id="chat-message-input-node" required autocomplete="off" disabled
                           placeholder="Select a match to start chatting..."
                           class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                    <button type="submit" id="chat-send-btn" disabled class="w-10 h-10 bg-gradient-to-r from-pink-500 to-rose-500 text-white rounded-xl flex items-center justify-center shadow hover:opacity-95 transition disabled:opacity-40">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </form>
            </section>

            <section id="chat-pane-fallback" class="lg:col-span-8 flex flex-col items-center justify-center bg-white rounded-2xl border border-slate-100 shadow-sm text-slate-400 p-8 text-center min-h-[320px]">
                <i class="fa-regular fa-comments text-5xl mb-3 opacity-40"></i>
                <h3 class="text-lg font-bold text-slate-700">No chat selected</h3>
                <p class="text-xs max-w-xs mt-1">Choose a connection from the list to load messages.</p>
            </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const currentUserId = <?= $current_user_id; ?>;
    let currentActiveMatchId = null;
    let pollTimer = null;

    const chatPane = document.getElementById('chat-window-pane');
    const chatFallback = document.getElementById('chat-pane-fallback');
    const messagesBox = document.getElementById('messages-stream-box');
    const messageInput = document.getElementById('chat-message-input-node');
    const sendBtn = document.getElementById('chat-send-btn');
    const chatForm = document.getElementById('chat-delivery-form');
    const matchList = document.getElementById('matchList');
    const terminateBtn = document.getElementById('terminateBtn');

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showChatPane() {
        chatFallback.classList.add('hidden');
        chatPane.classList.remove('hidden');
        chatPane.classList.add('flex');
        messageInput.disabled = false;
        sendBtn.disabled = false;
        messageInput.placeholder = 'Type your message...';
    }

    function activateChatThread(matchId, partnerName) {
        document.querySelectorAll('.match-sidebar-row').forEach((row) => {
            row.classList.remove('bg-white', 'border-l-4', 'border-pink-500');
        });

        const selected = document.querySelector(`.match-sidebar-row[data-match-id="${matchId}"]`);
        if (selected) {
            selected.classList.add('bg-white', 'border-l-4', 'border-pink-500');
        }

        currentActiveMatchId = matchId;
        document.getElementById('active-match-id-cache').value = matchId;
        document.getElementById('chat-pane-title').textContent = partnerName;
        document.getElementById('chat-header-initial').textContent = partnerName.charAt(0).toUpperCase();

        showChatPane();
        terminateBtn.classList.remove('hidden');
        fetchMessageHistory();

        clearInterval(pollTimer);
        pollTimer = setInterval(fetchMessageHistory, 3000);
    }

    terminateBtn.addEventListener('click', async () => {
        if (!currentActiveMatchId) return;
        if (!confirm('End this connection? You can find each other again in Discover.')) return;

        terminateBtn.disabled = true;
        try {
            const response = await fetch('../api/connection_requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'terminate', match_id: currentActiveMatchId })
            });
            const res = await response.json();
            if (!response.ok) throw new Error(res.error || 'Could not end connection.');

            MatchMeToast.show(res.message || 'Connection ended.', 'success', {
                actionUrl: 'discover.php',
                actionLabel: 'Discover',
                duration: 8000,
            });

            const row = document.querySelector(`.match-sidebar-row[data-match-id="${currentActiveMatchId}"]`);
            if (row) row.remove();
            currentActiveMatchId = null;
            terminateBtn.classList.add('hidden');
            chatPane.classList.add('hidden');
            chatPane.classList.remove('flex');
            chatFallback.classList.remove('hidden');
            messagesBox.innerHTML = '';
            clearInterval(pollTimer);
        } catch (err) {
            MatchMeToast.show(err.message || 'Failed to end connection.', 'error');
        } finally {
            terminateBtn.disabled = false;
        }
    });

    async function fetchMessageHistory() {
        if (!currentActiveMatchId) return;

        try {
            const response = await fetch(`../api/messages.php?match_id=${currentActiveMatchId}`);
            const res = await response.json();

            if (res.status === 'success') {
                renderMessages(res.data || []);
            }
        } catch (err) {
            console.error(err);
        }
    }

    function renderMessages(messagePackets) {
        const isNearBottom = messagesBox.scrollHeight - messagesBox.clientHeight - messagesBox.scrollTop < 150;
        messagesBox.innerHTML = '';

        if (!messagePackets.length) {
            messagesBox.innerHTML = '<div class="text-center text-slate-400 text-xs py-8">No messages yet. Say hello!</div>';
            return;
        }

        messagePackets.forEach((msg) => {
            const isMe = parseInt(msg.sender_id, 10) === currentUserId;
            const wrapper = document.createElement('div');
            wrapper.className = `flex w-full ${isMe ? 'justify-end' : 'justify-start'}`;
            wrapper.innerHTML = `
                <div class="max-w-[70%] rounded-2xl px-4 py-2 shadow-sm text-sm ${
                    isMe ? 'bg-gradient-to-r from-pink-500 to-rose-500 text-white rounded-br-none' : 'bg-white text-slate-800 border border-slate-200 rounded-bl-none'
                }">
                    <p class="leading-relaxed break-words">${escapeHtml(msg.message)}</p>
                    <span class="text-[10px] block mt-1 text-right opacity-70">${escapeHtml(msg.timestamp || '')}</span>
                </div>`;
            messagesBox.appendChild(wrapper);
        });

        if (isNearBottom) {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }
    }

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text || !currentActiveMatchId) return;

        messageInput.value = '';

        try {
            const response = await fetch('../api/messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ match_id: currentActiveMatchId, message: text })
            });
            const res = await response.json();
            if (res.status === 'success') {
                fetchMessageHistory();
            } else {
                MatchMeToast.show(res.message || 'Could not send message.', 'error');
            }
        } catch (err) {
            console.error(err);
            MatchMeToast.show('Could not send message.', 'error');
        }
    });

    if (matchList) {
        matchList.addEventListener('click', (e) => {
            const row = e.target.closest('.match-sidebar-row');
            if (!row) return;
            const matchId = parseInt(row.getAttribute('data-match-id'), 10);
            const partnerName = row.getAttribute('data-partner-name') || 'Match';
            if (matchId) activateChatThread(matchId, partnerName);
        });
    }

});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
