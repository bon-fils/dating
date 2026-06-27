<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please sign in to view connection requests.';
    header('Location: login.php');
    exit();
}

$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50/50 flex justify-center">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="text-center sm:text-left bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Connection Requests</h1>
            <p class="text-slate-500 text-sm mt-1">Review profiles, accept to match, or reject. End connections anytime from Matches to discover again.</p>
        </div>

        <div class="flex justify-center sm:justify-start gap-2">
            <button type="button" data-tab="incoming" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-pink-500 text-white shadow-sm">Incoming</button>
            <button type="button" data-tab="outgoing" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-200 text-slate-600">Sent</button>
        </div>

        <div id="requestsList" class="grid grid-cols-1 sm:grid-cols-2 gap-4 justify-items-center w-full"></div>

    </div>
</div>

<div id="profileModal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">Profile Preview</h2>
            <button type="button" id="closeModal" class="text-slate-400 hover:text-slate-600 p-1"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="modalBody" class="p-6 space-y-4"></div>
        <div id="modalActions" class="p-4 border-t border-slate-100 flex gap-2 hidden">
            <button type="button" id="rejectBtn" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">Reject</button>
            <button type="button" id="acceptBtn" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-pink-500 to-rose-500 text-white text-xs font-bold hover:opacity-95">Accept</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('requestsList');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const modal = document.getElementById('profileModal');
    const modalBody = document.getElementById('modalBody');
    const modalActions = document.getElementById('modalActions');
    const acceptBtn = document.getElementById('acceptBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const closeModal = document.getElementById('closeModal');

    let activeTab = 'incoming';
    let activeRequestId = null;
    let activeUserId = null;

    function escapeHtml(v) {
        if (v == null) return '';
        return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            activeTab = btn.getAttribute('data-tab');
            tabBtns.forEach(b => {
                b.classList.remove('bg-pink-500', 'text-white', 'shadow-sm');
                b.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-600');
            });
            btn.classList.add('bg-pink-500', 'text-white', 'shadow-sm');
            btn.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-600');
            loadRequests();
        });
    });

    async function loadRequests() {
        listEl.style.opacity = '0.5';
        try {
            const res = await fetch(`../api/connection_requests.php?type=${activeTab}`);
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to load');

            const items = data.data || [];
            listEl.innerHTML = '';

            if (!items.length) {
                listEl.innerHTML = `<p class="col-span-full text-center py-16 text-slate-400 text-sm font-semibold w-full">
                    ${activeTab === 'incoming' ? 'No incoming requests right now.' : 'You have not sent any requests yet.'}
                </p>`;
                return;
            }

            items.forEach(item => {
                const statusBadge = item.status === 'pending'
                    ? '<span class="text-[10px] font-bold uppercase text-amber-600 bg-amber-50 px-2 py-0.5 rounded">Pending</span>'
                    : item.status === 'accepted'
                    ? '<span class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Accepted</span>'
                    : '<span class="text-[10px] font-bold uppercase text-slate-500 bg-slate-100 px-2 py-0.5 rounded">Rejected</span>';

                const actions = activeTab === 'incoming' && item.status === 'pending'
                    ? `<div class="flex gap-2 mt-4 pt-3 border-t border-slate-50">
                        <button type="button" data-view-id="${item.user_id}" data-request-id="${item.request_id}" class="view-profile flex-1 text-xs font-bold py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-pink-200">View Profile</button>
                        <button type="button" data-accept-id="${item.request_id}" class="quick-accept px-3 py-2 rounded-xl bg-pink-500 text-white text-xs font-bold">Accept</button>
                       </div>`
                    : `<button type="button" data-view-id="${item.user_id}" data-request-id="${item.request_id}" class="view-profile w-full mt-4 text-xs font-bold py-2 rounded-xl border border-slate-200 text-slate-600">View Profile</button>`;

                listEl.innerHTML += `
                    <div class="w-full max-w-md sm:max-w-none bg-white p-5 rounded-2xl border border-slate-100 shadow-sm" data-request-row="${item.request_id}">
                        <div class="flex gap-4">
                            <img src="${escapeHtml(item.img)}" class="w-14 h-14 rounded-xl object-cover" alt="">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-bold text-slate-800 truncate">${escapeHtml(item.username)}, ${item.age}</h3>
                                    ${statusBadge}
                                </div>
                                <p class="text-xs text-slate-500 truncate mt-0.5">${escapeHtml(item.location || '')} · ${escapeHtml(item.outfit_style || '')}</p>
                            </div>
                        </div>
                        ${actions}
                    </div>`;
            });
        } catch (e) {
            listEl.innerHTML = `<p class="col-span-full text-center text-rose-500 text-xs font-bold py-12">${escapeHtml(e.message)}</p>`;
        } finally {
            listEl.style.opacity = '1';
        }
    }

    async function openProfile(userId, requestId) {
        activeUserId = userId;
        activeRequestId = requestId;
        modalBody.innerHTML = '<p class="text-center text-slate-400 text-sm py-8"><i class="fa-solid fa-spinner animate-spin"></i> Loading...</p>';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modalActions.classList.add('hidden');

        try {
            const res = await fetch(`../api/user_preview.php?user_id=${userId}`);
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Could not load profile');
            const p = data.data;

            modalBody.innerHTML = `
                <div class="text-center">
                    <img src="${escapeHtml(p.img)}" class="w-24 h-24 rounded-2xl mx-auto object-cover shadow-sm" alt="">
                    <h3 class="text-xl font-black text-slate-800 mt-3">${escapeHtml(p.username)}, ${p.age}</h3>
                    <p class="text-xs text-slate-500">${escapeHtml(p.location || '')} · ${escapeHtml(p.gender || '')}</p>
                </div>
                <div class="space-y-2 text-xs">
                    <p><span class="font-bold text-slate-600">Style:</span> ${escapeHtml(p.outfit_style || '—')}</p>
                    <p><span class="font-bold text-slate-600">Diet:</span> ${escapeHtml(p.meal_type || '—')}</p>
                    <p><span class="font-bold text-slate-600">Work:</span> ${escapeHtml(p.occupation || '—')}</p>
                    <p><span class="font-bold text-slate-600">Bio:</span> ${escapeHtml(p.bio || '—')}</p>
                </div>`;

            if (activeTab === 'incoming') {
                modalActions.classList.remove('hidden');
            }
        } catch (e) {
            modalBody.innerHTML = `<p class="text-rose-500 text-xs font-bold text-center">${escapeHtml(e.message)}</p>`;
        }
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeRequestId = null;
        activeUserId = null;
    }

    async function respond(action) {
        if (!activeRequestId) return;
        acceptBtn.disabled = true;
        rejectBtn.disabled = true;
        try {
            const res = await fetch('../api/connection_requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, request_id: activeRequestId })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Action failed');

            hideModal();
            if (action === 'accept') {
                MatchMeToast.show('Connection accepted! You can chat in Matches.', 'match', {
                    actionUrl: 'matches.php',
                    actionLabel: 'Open Matches',
                    duration: 8000,
                });
            } else {
                MatchMeToast.show('Request rejected.', 'info');
            }
            loadRequests();
        } catch (e) {
            MatchMeToast.show(e.message, 'error');
        } finally {
            acceptBtn.disabled = false;
            rejectBtn.disabled = false;
        }
    }

    listEl.addEventListener('click', async (e) => {
        const viewBtn = e.target.closest('.view-profile');
        if (viewBtn) {
            openProfile(viewBtn.getAttribute('data-view-id'), viewBtn.getAttribute('data-request-id'));
            return;
        }
        const acceptQuick = e.target.closest('.quick-accept');
        if (acceptQuick) {
            activeRequestId = acceptQuick.getAttribute('data-accept-id');
            await respond('accept');
        }
    });

    acceptBtn.addEventListener('click', () => respond('accept'));
    rejectBtn.addEventListener('click', () => respond('reject'));
    closeModal.addEventListener('click', hideModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); });

    loadRequests();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
