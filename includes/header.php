<?php
// Securely initialize session context globally across all application layers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Track if a user is logged in based on session states
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? ($_SESSION['username'] ?? $_SESSION['user_name'] ?? '') : '';
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
$avatarInitials = !empty($username) ? htmlspecialchars(strtoupper(substr($username, 0, 2)), ENT_QUOTES, 'UTF-8') : 'ME';

// Routing path optimization logic for brand destination tracking
$brand_destination = $isLoggedIn ? 'dashboard.php' : 'index.php';
$pendingRequestCount = 0;

if ($isLoggedIn) {
    require_once __DIR__ . '/../config/database.php';
    try {
        $pendingStmt = $pdo->prepare("
            SELECT COUNT(*) FROM connection_requests
            WHERE receiver_id = ? AND status = 'pending'
        ");
        $pendingStmt->execute([(int) $_SESSION['user_id']]);
        $pendingRequestCount = (int) $pendingStmt->fetchColumn();
    } catch (PDOException $e) {
        $pendingRequestCount = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MATCHME - Find Your Perfect Match</title>
    
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome Icons for clean, expressive UI symbols -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/toast.js" defer></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <!-- REUSABLE APP NAVIGATION BAR -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                
                <!-- Left Wing: Brand Identity & Nav Items -->
                <div class="flex items-center space-x-5 lg:space-x-8">
                    <a href="<?= $brand_destination; ?>" id="brandLogo" class="flex items-center space-x-2 group flex-shrink-0">
                        <div class="w-9 h-9 bg-gradient-to-tr from-pink-500 to-rose-500 rounded-xl flex items-center justify-center shadow-md shadow-pink-500/20 transform group-hover:scale-105 transition">
                            <i class="fa-solid fa-heart-pulse text-white text-sm" aria-hidden="true"></i>
                        </div>
                        <span class="text-xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">
                            MATCHME
                        </span>
                    </a>

                    <!-- Core Public Discovery Links (Smart Routing Supported) -->
                    <?php $path_prefix = isset($is_subpage) ? 'index.php' : ''; ?>

                    <div class="hidden md:flex items-center space-x-1 border-l border-slate-100 pl-4 lg:pl-6">
                        <a href="<?= $path_prefix; ?>#features" class="px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-pink-500 hover:bg-pink-50/30 transition">
                            Features
                        </a>
                        <a href="<?= $path_prefix; ?>#about" class="px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-pink-500 hover:bg-pink-50/30 transition">
                            About Us
                        </a>
                        <a href="<?= $path_prefix; ?>#contact" class="px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-pink-500 hover:bg-pink-50/30 transition">
                            Contact
                        </a>
                        <a href="<?= $path_prefix; ?>#faq" class="px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-pink-500 hover:bg-pink-50/30 transition">
                            FAQ
                        </a>
                    </div>
                </div>

                <!-- Right Wing: Dynamic Context Status Controls -->
                <div class="flex items-center space-x-4">
                    
                    <!-- Authorized Engine Links -->
                    <?php if ($isLoggedIn): ?>
                        <div class="hidden sm:flex items-center space-x-1 mr-2 border-r border-slate-100 pr-4">
                            <a href="discover.php" class="px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-pink-500 transition">
                                <i class="fa-solid fa-compass mr-1 text-slate-400" aria-hidden="true"></i> Discover
                            </a>
                            <a href="requests.php" class="relative px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-pink-500 transition">
                                <i class="fa-solid fa-inbox mr-1 text-slate-400" aria-hidden="true"></i> Requests
                                <?php if ($pendingRequestCount > 0): ?>
                                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"><?= $pendingRequestCount; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="matches.php" class="px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-pink-500 transition">
                                <i class="fa-solid fa-comment-heart mr-1 text-slate-400" aria-hidden="true"></i> Matches
                            </a>
                            <a href="profile.php" class="hidden lg:inline-flex px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-pink-500 transition">
                                <i class="fa-solid fa-user mr-1 text-slate-400" aria-hidden="true"></i> Profile
                            </a>
                            <a href="settings.php" class="hidden lg:inline-flex px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-pink-500 transition">
                                <i class="fa-solid fa-sliders mr-1 text-slate-400" aria-hidden="true"></i> Settings
                            </a>
                        </div>
                        
                        <!-- Account Ribbon Panel -->
                        <div class="flex items-center space-x-2.5">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-pink-50 via-slate-50 to-rose-50 border border-slate-200 flex items-center justify-center text-pink-500 font-bold text-sm shadow-sm">
                                <?= $avatarInitials; ?>
                            </div>
                            <a href="../api/logout.php" 
                               class="px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200/60 transition" aria-label="Sign out">
                                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                            </a>
                        </div>

                    <?php else: ?>
                        
                        <!-- Guest State System Entry Links -->
                        <div class="flex items-center space-x-1.5">
                            <a href="login.php" class="px-3.5 py-2 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-900 transition">
                                Sign In
                            </a>
                            <a href="register.php" class="px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-md shadow-pink-500/10 hover:opacity-95 active:scale-[0.98] transition">
                                Join
                            </a>
                        </div>

                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>

    <!-- Global Interceptor for Smooth Scroll Navigation Tracking -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const brandLogo = document.getElementById("brandLogo");
        
        if (brandLogo) {
            brandLogo.addEventListener("click", (e) => {
                // Parse out file pointers instantly
                const currentFile = window.location.pathname.split("/").pop();
                const targetFile = brandLogo.getAttribute("href");

                // Handle routing intercept calculations for identical targets
                if (currentFile === targetFile || (currentFile === "" && targetFile === "index.php")) {
                    e.preventDefault(); 
                    
                    // Smooth viewport scrolling up loop execution
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth"
                    });
                }
            });
        }
    });
    </script>

    <!-- Main dynamic body content begins right below this line -->
    <main class="flex-grow flex flex-col w-full items-center pt-16">