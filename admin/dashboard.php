<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Guardrail: Verify user is logged in AND has an Admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access. Secure administrator credentials are required.";
    header("Location: ../public/login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$admin_name = htmlspecialchars($_SESSION['username'] ?? 'System Admin', ENT_QUOTES, 'UTF-8');

// Initialize Admin KPI Metrics
$total_users = 0;
$pending_verifications = 0;
$active_matches_count = 0;
$verification_rate = 0;

try {
    // 1. Fetch Total Users Count
    $total_users = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // 2. Fetch Pending Verifications
    $pending_verifications = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 0")->fetchColumn();

    // 3. Fetch Active Interconnected Matches
    $active_matches_count = (int)$pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();

    // 4. Calculate Verification Rate %
    $verified_users = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn();
    $verification_rate = $total_users > 0 ? round(($verified_users / $total_users) * 100) : 0;

    // 5. Fetch Recent Users Data Matrix for the Primary Registry Table
    $stmt_users = $pdo->query("
        SELECT id, username, email, is_verified, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Admin Engine Metric Fail: ' . $e->getMessage());
    $recent_users = [];
}

$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50/70 flex justify-center font-sans">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Admin Control Heading Panel -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm text-center sm:text-left">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                    Admin <span class="text-[#e93266]">Control Hub</span>
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-0.5">Welcome back, <?= $admin_name; ?>. You have absolute system-wide execution capabilities.</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 bg-rose-50 rounded-full border border-rose-100">
                <span class="w-2 h-2 bg-[#e93266] rounded-full animate-ping"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#e93266]">System Superuser</span>
            </div>
        </div>

        <!-- Global Operational KPIs Panel -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 w-full">
            <!-- KPI 1 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Registered</p>
                    <p class="text-2xl font-black text-slate-800 mt-0.5"><?= $total_users; ?></p>
                </div>
                <div class="w-10 h-10 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center text-md border border-slate-100"><i class="fa-solid fa-users-gear"></i></div>
            </div>
            <!-- KPI 2 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pending Approvals</p>
                    <p class="text-2xl font-black text-amber-600 mt-0.5"><?= $pending_verifications; ?></p>
                </div>
                <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-md border border-amber-100"><i class="fa-solid fa-user-clock"></i></div>
            </div>
            <!-- KPI 3 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Platform Matches</p>
                    <p class="text-2xl font-black text-emerald-600 mt-0.5"><?= $active_matches_count; ?></p>
                </div>
                <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-md border border-emerald-100"><i class="fa-solid fa-hands-holding-heart"></i></div>
            </div>
            <!-- KPI 4 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Verification Rate</p>
                    <p class="text-2xl font-black text-[#e93266] mt-0.5"><?= $verification_rate; ?>%</p>
                </div>
                <div class="w-10 h-10 bg-pink-50 text-[#e93266] rounded-xl flex items-center justify-center text-md border border-pink-100/30"><i class="fa-solid fa-id-card-clip"></i></div>
            </div>
        </div>

        <!-- Layout Partition Configuration Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start w-full">

            <!-- Primary Workspace Content: User Registry Table Panel -->
            <div class="lg:col-span-8 w-full">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-address-book text-[#e93266]"></i> Live User Management Matrix
                        </h2>
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">Realtime</span>
                    </div>

                    <!-- User Account Matrix Database Table Layout -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50">
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">UID</th>
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Profile Identity</th>
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Verification</th>
                                    <th class="p-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Root Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                                <?php if (!empty($recent_users)): ?>
                                    <?php foreach ($recent_users as $row): ?>
                                        <tr class="hover:bg-slate-50/40 transition-colors">
                                            <td class="p-3 font-semibold text-slate-400">#<?= $row['id']; ?></td>
                                            <td class="p-3">
                                                <div class="font-bold text-slate-800"><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-[11px] text-slate-400"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td class="p-3">
                                                <?php if ((int)$row['is_verified'] === 1): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100"><i class="fa-solid fa-shield-check mr-1"></i> Passing</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="p-3 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <a href="edit_user.php?id=<?= $row['id']; ?>" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 rounded-md transition text-[11px] font-semibold" title="Edit Metadata">Manage</a>
                                                    <?php if ((int)$row['is_verified'] === 0): ?>
                                                        <button type="button" onclick="verifyUser(<?= $row['id']; ?>)" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition text-[11px] font-semibold" title="Instantly Verify User">Verify</button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-slate-400 font-medium">No live user matrix registries exist in data banks.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Side Widget: Structured Engine Status Table Grid Panel -->
            <div class="lg:col-span-4 w-full">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                    <div class="text-center pb-4 border-b border-slate-100">
                        <div class="w-14 h-14 bg-gradient-to-tr from-pink-50 to-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-pink-100/20 shadow-sm">
                            <i class="fa-solid fa-server text-[#e93266] text-xl"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">System Constants</h2>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wider uppercase mt-0.5">Core Matching Engine Parameters</p>
                    </div>

                    <!-- Environment Metrics Layout Table -->
                    <div class="overflow-hidden mt-4">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-6/12">Metric Property</th>
                                    <th class="pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-6/12">Operational Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/70 text-xs text-slate-700">
                                <tr>
                                    <td class="py-3 font-medium text-slate-400"><i class="fa-solid fa-chart-simple mr-1.5 text-[10px]"></i> Outfit Weight</td>
                                    <td class="py-3 font-bold text-slate-800">50% Coefficient</td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400"><i class="fa-solid fa-bowl-food mr-1.5 text-[10px]"></i> Meal Weight</td>
                                    <td class="py-3 font-bold text-slate-800">50% Coefficient</td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400"><i class="fa-solid fa-database mr-1.5 text-[10px]"></i> PDO Engine</td>
                                    <td class="py-3 font-bold text-emerald-600 inline-flex items-center gap-1 bg-emerald-50 px-2 py-0.5 rounded mt-2 border border-emerald-100/50">Active Connected</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <a href="system_settings.php" class="block w-full mt-5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 text-center font-bold text-xs py-2.5 rounded-xl transition tracking-wide">
                        Configure Core Variables <i class="fa-solid fa-gear ml-1"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
/**
 * Executes a seamless background asynchronous validation action for user accounts
 * @param {number} userId - The unique user id database key identifier
 */
async function verifyUser(userId) {
    if (!confirm("Are you sure you want to grant instant profile verification access to user #" + userId + "?")) {
        return;
    }

    try {
        const response = await fetch("../api/admin_verify_user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: parseInt(userId, 10) })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || "Execution error processing admin user clearance.");
        }

        if (typeof MatchMeToast !== 'undefined') {
            MatchMeToast.show("User verification successfully committed to the database.", "success");
        } else {
            alert("Verification committed successfully.");
        }
        
        // Dynamic state update logic
        setTimeout(() => window.location.reload(), 1000);

    } catch (error) {
        console.error(error);
        if (typeof MatchMeToast !== 'undefined') {
            MatchMeToast.show(error.message || "Admin state transaction process crashed.", "error");
        } else {
            alert("Error: " + error.message);
        }
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>