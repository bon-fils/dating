<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please sign in securely to access your match engine workspace.";
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$current_user_id = (int) $_SESSION['user_id'];
$user_name = 'User Workspace';
$match_count = 0;
$compatibility_index = 0;
$is_verified = false;
$profile_vibe = 'Not set';
$profile_style = 'Not set';

try {
    $stmt_user = $pdo->prepare("
        SELECT u.username, u.is_verified, u.outfit_style, u.meal_type, p.occupation
        FROM users u
        LEFT JOIN profiles p ON p.user_id = u.id
        WHERE u.id = ?
    ");
    $stmt_user->execute([$current_user_id]);
    $user_row = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user_row && !empty($user_row['username'])) {
        $user_name = htmlspecialchars($user_row['username'], ENT_QUOTES, 'UTF-8');
        $_SESSION['username'] = $user_row['username'];
        $_SESSION['user_name'] = $user_row['username'];
    }

    $is_verified = !empty($user_row['is_verified']);

    if (!empty($user_row['occupation'])) {
        $profile_vibe = htmlspecialchars($user_row['occupation'], ENT_QUOTES, 'UTF-8');
    } elseif (!empty($_SESSION['user_vibe'])) {
        $profile_vibe = htmlspecialchars($_SESSION['user_vibe'], ENT_QUOTES, 'UTF-8');
    }

    if (!empty($user_row['outfit_style'])) {
        $profile_style = htmlspecialchars($user_row['outfit_style'], ENT_QUOTES, 'UTF-8');
    } elseif (!empty($_SESSION['user_style'])) {
        $profile_style = htmlspecialchars($_SESSION['user_style'], ENT_QUOTES, 'UTF-8');
    }

    $stmt_count = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM matches
        WHERE user1_id = ? OR user2_id = ?
    ");
    $stmt_count->execute([$current_user_id, $current_user_id]);
    $match_row = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $match_count = $match_row ? (int) $match_row['total'] : 0;

    $stmt_avg = $pdo->prepare("
        SELECT ROUND(AVG(
            (CASE WHEN partner.outfit_style = cur.outfit_style
                  AND cur.outfit_style IS NOT NULL AND cur.outfit_style != '' THEN 50 ELSE 0 END) +
            (CASE WHEN partner.meal_type = cur.meal_type
                  AND cur.meal_type IS NOT NULL AND cur.meal_type != '' THEN 50 ELSE 0 END)
        )) AS avg_score
        FROM matches m
        INNER JOIN users cur ON cur.id = ?
        INNER JOIN users partner ON partner.id = IF(m.user1_id = cur.id, m.user2_id, m.user1_id)
        WHERE m.user1_id = cur.id OR m.user2_id = cur.id
    ");
    $stmt_avg->execute([$current_user_id]);
    $avg_row = $stmt_avg->fetch(PDO::FETCH_ASSOC);
    $compatibility_index = ($avg_row && $avg_row['avg_score'] !== null) ? (int) $avg_row['avg_score'] : 0;

} catch (PDOException $e) {
    error_log('Dashboard metrics error: ' . $e->getMessage());
    if (!empty($_SESSION['username'])) {
        $user_name = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
    }
    if (!empty($_SESSION['user_vibe'])) {
        $profile_vibe = htmlspecialchars($_SESSION['user_vibe'], ENT_QUOTES, 'UTF-8');
    }
    if (!empty($_SESSION['user_style'])) {
        $profile_style = htmlspecialchars($_SESSION['user_style'], ENT_QUOTES, 'UTF-8');
    }
}

$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50/70 flex justify-center font-sans">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm text-center sm:text-left">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                    Welcome back, <span class="text-[#e93266]"><?= $user_name; ?></span>!
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-0.5">Your lifestyle tracking engine is actively checking for new profile alignments.</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 rounded-full border border-emerald-100">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Engine Online</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 w-full">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Connections</p>
                    <p class="text-3xl font-black text-slate-800 mt-1"><?= $match_count; ?></p>
                </div>
                <div class="w-12 h-12 bg-pink-50 text-[#e93266] rounded-xl flex items-center justify-center text-lg"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Avg Compatibility</p>
                    <p class="text-3xl font-black text-slate-800 mt-1"><?= $compatibility_index; ?>%</p>
                </div>
                <div class="w-12 h-12 bg-rose-50 text-[#e93266] rounded-xl flex items-center justify-center text-lg"><i class="fa-solid fa-heart-pulse"></i></div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Verification Status</p>
                    <?php if ($is_verified): ?>
                        <p class="text-xs font-bold text-emerald-600 mt-2 bg-emerald-50 inline-flex items-center px-2.5 py-1 rounded-full border border-emerald-100"><i class="fa-solid fa-circle-check mr-1.5 text-[11px]"></i> Verified</p>
                    <?php else: ?>
                        <p class="text-xs font-bold text-amber-600 mt-2 bg-amber-50 inline-flex items-center px-2.5 py-1 rounded-full border border-amber-100"><i class="fa-solid fa-clock mr-1.5 text-[11px]"></i> Pending</p>
                    <?php endif; ?>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-lg"><i class="fa-solid fa-user-shield"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start w-full">

            <div class="lg:col-span-8 w-full">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-fire text-[#e93266]"></i> Top Live Match Suggestions
                        </h2>
                        <button id="refreshBtn" type="button" class="text-xs font-bold text-[#e93266] hover:text-[#d22655] flex items-center gap-1 transition focus:outline-none">
                            <i class="fa-solid fa-rotate text-[10px]"></i> Refresh List
                        </button>
                    </div>

                    <div id="matchContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-4 transition-opacity duration-200 w-full"></div>
                </div>
            </div>

            <div class="lg:col-span-4 w-full">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                    <div class="text-center pb-4 border-b border-slate-100">
                        <div class="w-16 h-16 bg-gradient-to-tr from-pink-50 to-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-slate-100 shadow-sm">
                            <i class="fa-solid fa-user text-[#e93266] text-2xl"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-800"><?= $user_name; ?></h2>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wider uppercase mt-0.5">Workspace Profile</p>
                    </div>

                    <div class="overflow-hidden mt-4">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-5/12">Property</th>
                                    <th class="pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-7/12">Current Engine Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/70 text-xs text-slate-700">
                                <tr>
                                    <td class="py-3 font-medium text-slate-400"><i class="fa-solid fa-laptop-code mr-1.5 text-[10px]"></i> Vibe</td>
                                    <td class="py-3 font-semibold text-slate-800 truncate max-w-[140px]" title="<?= $profile_vibe; ?>"><?= $profile_vibe; ?></td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400"><i class="fa-solid fa-shirt mr-1.5 text-[10px]"></i> Theme</td>
                                    <td class="py-3 font-semibold text-slate-800 truncate max-w-[140px]" title="<?= $profile_style; ?>"><?= $profile_style; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <a href="settings.php" class="block w-full mt-5 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 text-slate-600 text-center font-bold text-xs py-2.5 rounded-xl transition tracking-wide">
                        Modify Properties <i class="fa-solid fa-sliders ml-1"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("matchContainer");
    const refreshBtn = document.getElementById("refreshBtn");

    function escapeHtml(value) {
        if (value === null || value === undefined) return "";
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    async function loadLiveMatches() {
        container.style.opacity = "0.4";
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = `<i class="fa-solid fa-spinner animate-spin text-[10px]"></i> Matching...`;

        try {
            const response = await fetch("../api/get_suggestions.php");
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || "Could not load suggestions.");
            }

            if (!Array.isArray(data)) {
                throw new Error("Invalid suggestions response.");
            }

            container.innerHTML = "";

            if (data.length === 0) {
                container.innerHTML = `<p class="col-span-full w-full text-center py-12 text-slate-400 text-xs font-semibold">No new lifestyle alignments found right now.</p>`;
                return;
            }

            data.forEach(peer => {
                let statusColor = "bg-slate-400";
                if (peer.status === "Online") statusColor = "bg-emerald-500";
                if (peer.status === "Away") statusColor = "bg-yellow-500";

                const username = escapeHtml(peer.username || "User");
                const age = escapeHtml(peer.age || "??");
                const score = escapeHtml(peer.score || "0");
                const outfit = escapeHtml(peer.outfit_style || "Not Set");
                const meal = escapeHtml(peer.meal_type || "Not Set");
                const profileId = escapeHtml(peer.id || "");
                const imgSrc = peer.img
                    ? escapeHtml(peer.img)
                    : "https://ui-avatars.com/api/?name=" + encodeURIComponent(peer.username || "User") + "&background=random&size=150";
                const verifiedBadge = Number(peer.is_verified) === 1
                    ? '<i class="fa-solid fa-circle-check text-[11px] text-emerald-500 ml-0.5"></i>'
                    : "";

                const cardMarkup = `
                    <div class="w-full bg-white p-4 rounded-xl border border-slate-200/70 flex items-start space-x-4 hover:border-pink-200 transition duration-200 shadow-sm">
                        <div class="relative flex-shrink-0">
                            <img src="${imgSrc}" alt="Profile" class="w-14 h-14 rounded-xl object-cover bg-slate-100 border border-slate-100">
                            <span class="absolute bottom-[-2px] right-[-2px] w-3.5 h-3.5 border-2 border-white rounded-full ${statusColor}"></span>
                        </div>
                        <div class="flex-grow space-y-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-slate-800 truncate">
                                    ${username}, <span class="text-slate-400 font-medium">${age}</span>
                                    ${verifiedBadge}
                                </h3>
                                <span class="text-[11px] font-extrabold bg-pink-50 text-[#e93266] px-1.5 py-0.5 rounded border border-pink-100/30">${score}%</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate"><span class="font-semibold text-slate-600">Style:</span> ${outfit}</p>
                            <p class="text-xs text-slate-500 truncate"><span class="font-semibold text-slate-600">Diet:</span> ${meal}</p>
                            <div class="pt-2 flex items-center gap-2">
                                <button type="button" data-connect-id="${profileId}" class="connect-trigger flex-grow bg-[#e93266] text-white font-bold text-xs py-1.5 rounded-lg hover:bg-[#d22655] transition">Send Request</button>
                                <a href="discover.php" class="px-2 py-1.5 bg-white border border-slate-200 text-slate-400 hover:text-[#e93266] hover:border-pink-100 rounded-lg text-xs transition" title="View Profile"><i class="fa-regular fa-heart"></i></a>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += cardMarkup;
            });
        } catch (error) {
            console.error(error);
            container.innerHTML = `<p class="col-span-full w-full text-center py-12 text-rose-500 text-xs font-semibold"><i class="fa-solid fa-circle-exclamation mr-1.5"></i>${escapeHtml(error.message || "Failed to load match suggestions.")}</p>`;
        } finally {
            container.style.opacity = "1";
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = `<i class="fa-solid fa-rotate text-[10px]"></i> Refresh List`;
        }
    }

    container.addEventListener("click", (e) => {
        const connectBtn = e.target.closest(".connect-trigger");
        if (!connectBtn || connectBtn.disabled) return;

        const profileId = connectBtn.getAttribute("data-connect-id");
        if (profileId) {
            triggerConnection(profileId, connectBtn);
        }
    });

    refreshBtn.addEventListener("click", (e) => {
        e.preventDefault();
        loadLiveMatches();
    });

    async function triggerConnection(profileId, buttonEl) {
        const originalLabel = buttonEl.textContent;
        buttonEl.disabled = true;
        buttonEl.textContent = "Sending...";

        try {
            const response = await fetch("../api/save_swipe.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    target_id: parseInt(profileId, 10),
                    action: "like"
                })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || "Could not send connection request.");
            }

            const card = buttonEl.closest("[class*='rounded-xl']");
            if (card) {
                card.style.transition = "opacity 0.25s ease";
                card.style.opacity = "0";
                setTimeout(() => card.remove(), 250);
            }

            if (result.already_requested_you) {
                buttonEl.textContent = "View Request";
                MatchMeToast.show("They already sent you a request. Review it in Requests.", "info", {
                    actionUrl: "requests.php",
                    actionLabel: "Open Requests",
                    duration: 8000,
                });
            } else if (result.request_sent) {
                buttonEl.textContent = "Sent";
                MatchMeToast.show("Connection request sent. Waiting for their response.", "success");
            } else {
                MatchMeToast.show("Response saved.", "success");
            }
        } catch (error) {
            buttonEl.disabled = false;
            buttonEl.textContent = originalLabel;
            MatchMeToast.show(error.message || "Connection request failed.", "error");
        }
    }

    loadLiveMatches();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>