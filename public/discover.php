<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please sign in securely to access the match discovery engine.';
    header('Location: login.php');
    exit();
}

$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50/50 flex justify-center">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <div class="flex flex-col md:flex-row items-center md:items-center justify-between gap-4 text-center md:text-left bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                    Discover <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">New Profiles</span>
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-0.5">Explore community alignments using your preference filters below.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start w-full mx-auto">

            <form id="filterForm" class="lg:col-span-4 w-full max-w-md lg:max-w-none mx-auto bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-5 lg:sticky lg:top-24">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-pink-500"></i> Adjust Discovery Engine
                </h2>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Show Me</label>
                    <select name="gender" class="w-full bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 p-3 outline-none focus:border-pink-300 transition cursor-pointer">
                        <option value="all">Everyone</option>
                        <option value="female">Women</option>
                        <option value="male">Men</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Location / City Area</label>
                    <select name="location" class="w-full bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 p-3 outline-none focus:border-pink-300 transition cursor-pointer">
                        <option value="all">Everywhere (Global Mix)</option>
                        <option value="Kigali">Kigali</option>
                        <option value="Musanze">Musanze</option>
                        <option value="Huye">Huye</option>
                        <option value="Rubavu">Rubavu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Look Theme Style</label>
                    <select name="style" class="w-full bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 p-3 outline-none focus:border-pink-300 transition cursor-pointer">
                        <option value="all">All Styles (Broad Stream)</option>
                        <option value="Minimalist">Minimalist</option>
                        <option value="Streetwear">Streetwear</option>
                        <option value="Vintage">Vintage</option>
                        <option value="Casual Chic">Casual Chic</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Dietary Alignments</label>
                    <select name="diet" class="w-full bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 p-3 outline-none focus:border-pink-300 transition cursor-pointer">
                        <option value="all">Any Diet Framework</option>
                        <option value="Traditional">Traditional</option>
                        <option value="Vegan">Vegan / Vegetarian</option>
                        <option value="Healthy/Organic">Healthy / Organic</option>
                        <option value="Fast Food">Fast Food</option>
                    </select>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Maximum Age Span</label>
                        <span id="ageDisplay" class="text-xs font-extrabold text-pink-500 bg-pink-50 px-2 py-0.5 rounded-md">30</span>
                    </div>
                    <input type="range" name="max_age" min="18" max="50" value="30" class="w-full accent-pink-500 h-1.5 bg-slate-100 rounded-lg cursor-pointer appearance-none">
                </div>

                <button id="filterSubmitBtn" type="submit" class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white font-bold text-xs py-3 rounded-xl shadow-md hover:opacity-95 active:scale-[0.99] transition tracking-wide">
                    Apply Filter Profiles <i class="fa-solid fa-satellite-dish ml-1"></i>
                </button>
            </form>

            <div class="lg:col-span-8 space-y-6">
                <div id="discoveryStream" class="grid grid-cols-1 sm:grid-cols-2 gap-4 justify-items-center transition-opacity duration-200 w-full"></div>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filterForm');
    const streamContainer = document.getElementById('discoveryStream');
    const filterSubmitBtn = document.getElementById('filterSubmitBtn');
    const ageSlider = filterForm.querySelector("input[name='max_age']");
    const ageDisplay = document.getElementById('ageDisplay');

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    ageSlider.addEventListener('input', (e) => {
        ageDisplay.textContent = e.target.value;
    });

    async function streamDiscoveryProfiles() {
        streamContainer.style.opacity = '0.4';
        filterSubmitBtn.disabled = true;

        const formData = new FormData(filterForm);
        const searchParams = new URLSearchParams(formData).toString();

        try {
            const response = await fetch(`../api/get_discovery_stream.php?${searchParams}`);
            const profiles = await response.json();

            if (!response.ok) {
                throw new Error(profiles.error || 'Discovery data stream failed.');
            }

            if (!Array.isArray(profiles)) {
                throw new Error('Invalid discovery response.');
            }

            streamContainer.innerHTML = '';

            if (profiles.length === 0) {
                streamContainer.innerHTML = `
                    <div class="col-span-full w-full text-center bg-white border border-slate-100 rounded-2xl p-12 shadow-sm">
                        <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-box-open text-lg"></i></div>
                        <p class="text-slate-500 text-sm font-bold">No profiles found matching these criteria.</p>
                        <p class="text-slate-400 text-xs mt-0.5">Try loosening your filters or check back later.</p>
                    </div>`;
                return;
            }

            profiles.forEach(item => {
                const username = escapeHtml(item.username || 'User');
                const age = Number.isFinite(item.age) ? item.age : '??';
                const location = escapeHtml(item.location || 'Not specified');
                const outfit = escapeHtml(item.outfit_style || 'Not set');
                const meal = escapeHtml(item.meal_type || 'Not set');
                const profileId = escapeHtml(item.id);
                const imgSrc = item.img
                    ? escapeHtml(item.img)
                    : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(item.username || 'User') + '&background=random&size=150';
                const verifiedBadge = item.is_verified
                    ? '<i class="fa-solid fa-circle-check text-[11px] text-emerald-500"></i>'
                    : '';

                const profileCard = `
                    <div class="w-full max-w-md sm:max-w-none bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between hover:border-pink-200 transition duration-200 group">
                        <div class="flex items-start space-x-4">
                            <div class="relative flex-shrink-0">
                                <img src="${imgSrc}" alt="Discovery avatar" class="w-16 h-16 rounded-xl object-cover shadow-sm bg-slate-200">
                            </div>
                            <div class="space-y-1 min-w-0 flex-grow">
                                <h3 class="text-sm font-black text-slate-800 truncate flex items-center gap-1">
                                    ${username}, <span class="text-slate-400 font-medium">${age}</span>
                                    ${verifiedBadge}
                                </h3>
                                <p class="text-xs text-slate-500 truncate"><span class="font-bold text-slate-600">Location:</span> ${location}</p>
                                <p class="text-xs text-slate-500 truncate"><span class="font-bold text-slate-600">Style:</span> ${outfit}</p>
                                <p class="text-xs text-slate-500 truncate"><span class="font-bold text-slate-600">Diet:</span> ${meal}</p>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-slate-50 flex items-center gap-2">
                            <button type="button" data-like-id="${profileId}" class="like-trigger flex-grow bg-slate-50 group-hover:bg-gradient-to-r group-hover:from-pink-500 group-hover:to-rose-500 group-hover:text-white border border-slate-200/60 group-hover:border-transparent text-slate-700 font-bold text-xs py-2 rounded-xl transition duration-200">
                                Say Hello <i class="fa-solid fa-hand-wave ml-0.5 text-[10px]"></i>
                            </button>
                            <button type="button" data-pass-id="${profileId}" class="pass-trigger p-2 bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 rounded-xl text-xs transition duration-200" title="Pass">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                `;
                streamContainer.innerHTML += profileCard;
            });
        } catch (error) {
            console.error(error);
            streamContainer.innerHTML = `
                <div class="col-span-1 sm:col-span-2 text-center bg-white border border-slate-100 rounded-2xl p-12 shadow-sm">
                    <p class="text-rose-500 text-xs font-bold"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i>${escapeHtml(error.message || 'Failed to load discovery profiles.')}</p>
                </div>`;
        } finally {
            streamContainer.style.opacity = '1';
            filterSubmitBtn.disabled = false;
        }
    }

    async function sendSwipe(profileId, action, buttonEl) {
        const originalHtml = buttonEl.innerHTML;
        buttonEl.disabled = true;
        buttonEl.innerHTML = '<i class="fa-solid fa-spinner animate-spin text-[10px]"></i>';

        try {
            const response = await fetch('../api/save_swipe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    target_id: parseInt(profileId, 10),
                    action: action
                })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || 'Could not save your response.');
            }

            const card = buttonEl.closest('.group') || buttonEl.closest('[class*="rounded-2xl"]');
            if (card) {
                card.style.transition = 'opacity 0.25s ease';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 250);
            }

            if (action === 'like' && result.already_requested_you) {
                MatchMeToast.show('They already requested you. Open Requests to accept or reject.', 'info', {
                    actionUrl: 'requests.php',
                    actionLabel: 'Open Requests',
                    duration: 8000,
                });
            } else if (action === 'like' && result.request_sent) {
                MatchMeToast.show('Connection request sent.', 'success');
            } else if (action === 'dislike') {
                MatchMeToast.show('Profile passed.', 'info', { duration: 2500 });
            }
        } catch (error) {
            buttonEl.disabled = false;
            buttonEl.innerHTML = originalHtml;
            MatchMeToast.show(error.message || 'Action failed.', 'error');
        }
    }

    streamContainer.addEventListener('click', (e) => {
        const likeBtn = e.target.closest('.like-trigger');
        if (likeBtn && !likeBtn.disabled) {
            const profileId = likeBtn.getAttribute('data-like-id');
            if (profileId) sendSwipe(profileId, 'like', likeBtn);
            return;
        }

        const passBtn = e.target.closest('.pass-trigger');
        if (passBtn && !passBtn.disabled) {
            const profileId = passBtn.getAttribute('data-pass-id');
            if (profileId) sendSwipe(profileId, 'dislike', passBtn);
        }
    });

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        streamDiscoveryProfiles();
    });

    streamDiscoveryProfiles();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
