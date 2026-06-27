<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please sign in securely to access your profile settings.';
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$current_user_id = (int) $_SESSION['user_id'];
$user_name = 'User Workspace';
$current_style = 'Minimalist';
$current_diet = 'Traditional';
$current_vibe = 'Not set';
$current_bio = '';
$success_message = '';
$error_message = '';

try {
    $stmt = $pdo->prepare("
        SELECT u.username, u.outfit_style, u.meal_type, p.occupation, p.bio
        FROM users u
        LEFT JOIN profiles p ON p.user_id = u.id
        WHERE u.id = ?
    ");
    $stmt->execute([$current_user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $user_name = htmlspecialchars($row['username'] ?? 'User Workspace', ENT_QUOTES, 'UTF-8');
        $current_style = htmlspecialchars($row['outfit_style'] ?? 'Minimalist', ENT_QUOTES, 'UTF-8');
        $current_diet = htmlspecialchars($row['meal_type'] ?? 'Traditional', ENT_QUOTES, 'UTF-8');
        $current_vibe = htmlspecialchars($row['occupation'] ?? 'Not set', ENT_QUOTES, 'UTF-8');
        $current_bio = htmlspecialchars($row['bio'] ?? '', ENT_QUOTES, 'UTF-8');
    }
} catch (PDOException $e) {
    error_log('Settings load error: ' . $e->getMessage());
    $error_message = 'Could not load your settings from the database.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name_raw = trim($_POST['username'] ?? '');
    $current_style_raw = trim($_POST['style'] ?? 'Minimalist');
    $current_diet_raw = trim($_POST['diet'] ?? 'Traditional');
    $current_vibe_raw = trim($_POST['vibe'] ?? '');
    $current_bio_raw = trim($_POST['bio'] ?? '');

    if ($user_name_raw === '') {
        $error_message = 'Display name is required.';
    } else {
        try {
            $pdo->beginTransaction();

            $updateUser = $pdo->prepare('UPDATE users SET username = ?, outfit_style = ?, meal_type = ? WHERE id = ?');
            $updateUser->execute([$user_name_raw, $current_style_raw, $current_diet_raw, $current_user_id]);

            $checkProfile = $pdo->prepare('SELECT user_id FROM profiles WHERE user_id = ?');
            $checkProfile->execute([$current_user_id]);

            if ($checkProfile->fetch()) {
                $updateProfile = $pdo->prepare('UPDATE profiles SET occupation = ?, bio = ? WHERE user_id = ?');
                $updateProfile->execute([$current_vibe_raw, $current_bio_raw, $current_user_id]);
            } else {
                $insertProfile = $pdo->prepare('INSERT INTO profiles (user_id, occupation, bio) VALUES (?, ?, ?)');
                $insertProfile->execute([$current_user_id, $current_vibe_raw, $current_bio_raw]);
            }

            $pdo->commit();

            $user_name = htmlspecialchars($user_name_raw, ENT_QUOTES, 'UTF-8');
            $current_style = htmlspecialchars($current_style_raw, ENT_QUOTES, 'UTF-8');
            $current_diet = htmlspecialchars($current_diet_raw, ENT_QUOTES, 'UTF-8');
            $current_vibe = htmlspecialchars($current_vibe_raw, ENT_QUOTES, 'UTF-8');
            $current_bio = htmlspecialchars($current_bio_raw, ENT_QUOTES, 'UTF-8');

            $_SESSION['username'] = $user_name_raw;
            $_SESSION['user_name'] = $user_name_raw;
            $_SESSION['user_style'] = $current_style_raw;
            $_SESSION['user_diet'] = $current_diet_raw;
            $_SESSION['user_vibe'] = $current_vibe_raw;
            $_SESSION['user_bio'] = $current_bio_raw;

            $success_message = 'Your profile matching properties have been updated successfully!';
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Settings save error: ' . $e->getMessage());
            $error_message = 'Could not save your settings. Please try again.';
        }
    }
}

$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50/50 flex justify-center">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                    <i class="fa-solid fa-sliders text-pink-500"></i> Profile Settings
                </h1>
                <p class="text-slate-500 text-sm mt-0.5">Update how you appear on your dashboard and in discovery.</p>
            </div>
            <a href="dashboard.php" class="bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($success_message): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-800 shadow-sm">
                <div class="w-7 h-7 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs"><i class="fa-solid fa-check"></i></div>
                <p class="text-xs font-bold tracking-wide"><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-3 text-rose-700 shadow-sm">
                <i class="fa-solid fa-circle-exclamation"></i>
                <p class="text-xs font-bold tracking-wide"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <form action="settings.php" method="POST" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Display Name</label>
                        <input type="text" name="username" value="<?= $user_name; ?>" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-pink-500 text-slate-800 font-semibold text-xs rounded-xl transition focus:outline-none focus:ring-1 focus:ring-pink-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Professional Vibe / Focus</label>
                        <input type="text" name="vibe" value="<?= $current_vibe; ?>" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-pink-500 text-slate-800 font-semibold text-xs rounded-xl transition focus:outline-none focus:ring-1 focus:ring-pink-500">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Short Bio</label>
                    <textarea name="bio" rows="3" class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-pink-500 text-slate-800 font-medium text-xs rounded-xl transition focus:outline-none focus:ring-1 focus:ring-pink-500"><?= $current_bio; ?></textarea>
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-slate-50/40 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Preferred Style</label>
                    <select name="style" class="w-full px-3.5 py-3 bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl">
                        <?php
                        $styles = ['Minimalist', 'Vintage', 'Streetwear', 'Casual Chic', 'casual', 'formal', 'sporty', 'traditional', 'luxury', 'other'];
                        foreach (array_unique($styles) as $styleOption):
                            $selected = ($current_style === $styleOption) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($styleOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>><?= htmlspecialchars($styleOption, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Dietary Settings</label>
                    <select name="diet" class="w-full px-3.5 py-3 bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl">
                        <?php
                        $diets = ['Traditional', 'Vegan', 'Healthy/Organic', 'Fast Food', 'mixed', 'vegetarian', 'vegan', 'fast_food', 'halal', 'kosher', 'other'];
                        foreach (array_unique($diets) as $dietOption):
                            $selected = ($current_diet === $dietOption) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($dietOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>><?= htmlspecialchars($dietOption, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="profile.php" class="px-5 py-2.5 text-slate-500 hover:text-slate-700 text-xs font-bold">Advanced preferences</a>
                <button type="submit" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-md hover:opacity-95 transition">Save Properties</button>
            </div>
        </form>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
