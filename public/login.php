<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// --- AUTHENTICATION BACKEND CONTROLLER LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the dynamic layout identifier (can be username or email string)
    $login_identity = trim($_POST['login_identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_identity) || empty($password)) {
        $_SESSION['error'] = "All fields are required to authenticate your workspace profile.";
        header("Location: login.php");
        exit();
    }

    try {
        // Query to match either username OR email column to match what is written on the input field
        $stmt = $pdo->prepare("
            SELECT id, username, password, role 
            FROM users 
            WHERE email = ? OR username = ? 
            LIMIT 1
        ");
        $stmt->execute([$login_identity, $login_identity]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Prevent Session Fixation Vulnerabilities
            session_regenerate_id(true);

            $_SESSION['user_id']  = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role']; 

            // Secure Role-Based Conditional Navigation Routing
            if ($_SESSION['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../public/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "The credentials provided do not match our database registry.";
            header("Location: login.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Login processing failure: " . $e->getMessage());
        $_SESSION['error'] = "An internal engine error occurred. Please contact an system administrator.";
        header("Location: login.php");
        exit();
    }
}

// --- FRONTEND UI WORKSPACE VIEW LAYOUT ---
$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full grid grid-cols-1 lg:grid-cols-12 overflow-hidden bg-slate-50 flex-grow">
    <div class="hidden lg:flex lg:col-span-7 relative flex-col justify-between p-12 overflow-hidden bg-cover bg-center text-white"
         style="background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.85)), url('../assets/couples-bg.jpg');">
        <div class="absolute inset-0 bg-[radial-gradient(#ffffff10_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
        <div class="relative z-10 flex items-center space-x-2">
            <div class="w-8 h-8 bg-white/10 backdrop-blur-md rounded-lg flex items-center justify-center border border-white/20">
                <i class="fa-solid fa-heart-pulse text-pink-400 text-sm"></i>
            </div>
            <span class="text-lg font-black tracking-wider uppercase text-white">MatchMe</span>
        </div>
        <div class="relative z-10 max-w-lg space-y-6">
            <h2 class="text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                Connect with peers who truly understand your lifestyle.
            </h2>
            <p class="text-slate-200 text-base leading-relaxed">
                Join verified people matching on aligned routines, style aesthetics, and compatibility metrics.
            </p>
        </div>
        <p class="relative z-10 text-xs text-slate-400">&copy; <?= date('Y'); ?> MatchMe Engine Portal.</p>
    </div>

    <div class="col-span-1 lg:col-span-5 flex items-center justify-center p-6 sm:p-12 md:p-16 bg-white relative">
        <div class="lg:hidden absolute inset-0 bg-gradient-to-tr from-pink-50/50 via-white to-rose-50/30 pointer-events-none z-0"></div>
        <div class="w-full max-w-md space-y-8 relative z-10">
            <div class="text-center lg:text-left">
                <h1 class="text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500 inline-block">MATCHME</h1>
                <p class="text-slate-500 text-sm mt-2 font-medium">Welcome back</p>
            </div>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="p-4 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl text-sm font-medium flex items-start space-x-2.5 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-xl text-sm font-medium flex items-start space-x-2.5 shadow-sm">
                    <i class="fa-solid fa-circle-check mt-0.5 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email or Username</label>
                    <input type="text" name="login_identity" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 text-sm font-medium text-slate-800 bg-slate-50/50 transition"
                           placeholder="Enter your email or username">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 text-sm font-medium text-slate-800 bg-slate-50/50 transition"
                           placeholder="Your password">
                </div>
                <button type="submit" class="w-full bg-[#e93266] text-white font-bold py-3.5 rounded-xl hover:opacity-95 shadow-lg shadow-pink-500/20 active:scale-[0.99] transition text-sm">
                    Sign In Securely <i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i>
                </button>
            </form>

            <div class="text-center lg:text-left pt-6 border-t border-slate-100">
                <p class="text-slate-500 text-sm font-medium">
                    New to MATCHME? <a href="register.php" class="text-[#e93266] font-bold hover:underline transition ml-1">Create an account</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>