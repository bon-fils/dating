<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$current_user_id = (int) $_SESSION['user_id'];
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $bio = trim(filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
        $occupation = trim(filter_input(INPUT_POST, 'occupation', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
        $relationship_goal = $_POST['relationship_goal'] ?? 'dating';

        $profile_check = $pdo->prepare('SELECT user_id FROM profiles WHERE user_id = ?');
        $profile_check->execute([$current_user_id]);

        if ($profile_check->fetch()) {
            $profile_stmt = $pdo->prepare('UPDATE profiles SET bio = ?, occupation = ?, relationship_goal = ? WHERE user_id = ?');
            $profile_stmt->execute([$bio, $occupation, $relationship_goal, $current_user_id]);
        } else {
            $insert_profile = $pdo->prepare('INSERT INTO profiles (user_id, bio, occupation, relationship_goal) VALUES (?, ?, ?, ?)');
            $insert_profile->execute([$current_user_id, $bio, $occupation, $relationship_goal]);
        }

        $preferred_gender = $_POST['preferred_gender'] ?? 'both';
        $min_age = filter_input(INPUT_POST, 'min_age', FILTER_VALIDATE_INT) ?: 18;
        $max_age = filter_input(INPUT_POST, 'max_age', FILTER_VALIDATE_INT) ?: 99;
        $preferred_education = $_POST['preferred_education_level'] ?? 'any';
        $preferred_outfit = $_POST['preferred_outfit_style'] ?? 'any';
        $preferred_meal = $_POST['preferred_meal_type'] ?? 'any';
        $preferred_location = trim(filter_input(INPUT_POST, 'preferred_location', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');

        $pref_check = $pdo->prepare('SELECT user_id FROM preferences WHERE user_id = ?');
        $pref_check->execute([$current_user_id]);

        if ($pref_check->fetch()) {
            $pref_stmt = $pdo->prepare('
                UPDATE preferences SET
                    preferred_gender = ?, min_age = ?, max_age = ?,
                    preferred_education_level = ?, preferred_outfit_style = ?,
                    preferred_meal_type = ?, preferred_location = ?
                WHERE user_id = ?
            ');
            $pref_stmt->execute([
                $preferred_gender, $min_age, $max_age,
                $preferred_education, $preferred_outfit,
                $preferred_meal, $preferred_location, $current_user_id,
            ]);
        } else {
            $insert_pref = $pdo->prepare('
                INSERT INTO preferences (
                    user_id, preferred_gender, min_age, max_age,
                    preferred_education_level, preferred_outfit_style,
                    preferred_meal_type, preferred_location
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $insert_pref->execute([
                $current_user_id, $preferred_gender, $min_age, $max_age,
                $preferred_education, $preferred_outfit, $preferred_meal, $preferred_location,
            ]);
        }

        $pdo->commit();
        $_SESSION['profile_saved'] = true;
        header('Location: profile.php?updated=1');
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Profile update error: ' . $e->getMessage());
        $msg = 'Could not update profile. Please try again.';
        $msg_type = 'error';
    }
}

if (isset($_GET['updated'])) {
    $msg = 'Profile updated successfully.';
    $msg_type = 'success';
}

try {
    $stmt = $pdo->prepare("
        SELECT p.bio, p.occupation, p.relationship_goal,
               pr.preferred_gender, pr.min_age, pr.max_age,
               pr.preferred_education_level, pr.preferred_outfit_style,
               pr.preferred_meal_type, pr.preferred_location,
               u.username, u.email
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        LEFT JOIN preferences pr ON u.id = pr.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$current_user_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Profile load error: ' . $e->getMessage());
    $data = false;
}

if (!$data) {
    $data = [
        'username' => $_SESSION['username'] ?? 'User',
        'email' => '',
        'bio' => '',
        'occupation' => '',
        'relationship_goal' => 'dating',
        'preferred_gender' => 'both',
        'min_age' => 18,
        'max_age' => 35,
        'preferred_education_level' => 'any',
        'preferred_outfit_style' => 'any',
        'preferred_meal_type' => 'any',
        'preferred_location' => '',
    ];
}

$has_saved_profile = !empty($_SESSION['profile_saved'])
    || !empty(trim($data['bio'] ?? ''))
    || !empty(trim($data['occupation'] ?? ''))
    || !empty(trim($data['preferred_location'] ?? ''));

$edit_mode = !$has_saved_profile || isset($_GET['edit']);

function profile_label(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function format_goal(?string $goal): string
{
    return profile_label(ucfirst(str_replace('_', ' ', $goal ?? 'dating')));
}

$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50/50 flex justify-center">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 flex flex-col sm:flex-row items-center sm:items-start justify-between gap-4 text-center sm:text-left">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Profile &amp; Preferences</h1>
                <p class="text-slate-500 text-sm mt-0.5">
                    <?= $edit_mode ? 'Complete your profile and matching preferences.' : 'Your saved profile. Tap Edit to make changes.'; ?>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($has_saved_profile && !$edit_mode): ?>
                    <a href="profile.php?edit=1" class="inline-flex items-center gap-1.5 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md hover:opacity-95 transition">
                        <i class="fa-solid fa-pen text-[10px]"></i> Edit Profile
                    </a>
                <?php endif; ?>
                <a href="settings.php" class="text-xs font-bold text-pink-500 hover:text-rose-600">Quick settings</a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="mb-6 p-4 rounded-xl border font-medium text-sm shadow-sm <?= $msg_type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'; ?>">
                <?= profile_label($msg); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm text-center">
                    <div class="w-20 h-20 bg-gradient-to-tr from-pink-500 to-rose-400 rounded-full mx-auto flex items-center justify-center text-white text-3xl font-bold uppercase shadow-inner">
                        <?= profile_label(strtoupper(substr($data['username'] ?? 'U', 0, 1))); ?>
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg mt-3">@<?= profile_label($data['username'] ?? ''); ?></h3>
                    <p class="text-slate-400 text-xs mt-0.5"><?= profile_label($data['email'] ?? ''); ?></p>
                    <?php if ($has_saved_profile && !$edit_mode): ?>
                        <p class="mt-3 text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 inline-flex px-2 py-1 rounded-full">Profile saved</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="md:col-span-3">
                <?php if (!$edit_mode): ?>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-50 pb-2">About Me</h2>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Occupation</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?= profile_label($data['occupation'] ?: '—'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Relationship Goal</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?= format_goal($data['relationship_goal'] ?? null); ?></dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Bio</dt>
                                    <dd class="mt-1 text-slate-700 leading-relaxed"><?= nl2br(profile_label($data['bio'] ?: '—')); ?></dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-50 pb-2">Partner Preferences</h2>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Preferred Gender</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?php
                                        $genderLabels = ['male' => 'Men', 'female' => 'Women', 'both' => 'Everyone'];
                                        echo profile_label($genderLabels[$data['preferred_gender'] ?? 'both'] ?? 'Everyone');
                                    ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Age Range</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?= (int) ($data['min_age'] ?? 18); ?> – <?= (int) ($data['max_age'] ?? 35); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Education</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?= format_goal($data['preferred_education_level'] ?? 'any'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Outfit Style</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?= format_goal($data['preferred_outfit_style'] ?? 'any'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Meal Type</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?= format_goal(str_replace('_', ' ', $data['preferred_meal_type'] ?? 'any')); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Location</dt>
                                    <dd class="mt-1 font-medium text-slate-800"><?= profile_label($data['preferred_location'] ?: '—'); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                <?php else: ?>
                    <form action="profile.php" method="POST" class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-50 pb-2">About Me</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Occupation</label>
                                    <input type="text" name="occupation" value="<?= profile_label($data['occupation'] ?? ''); ?>" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Relationship Goal</label>
                                    <select name="relationship_goal" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm bg-white">
                                        <?php
                                        foreach (['friendship', 'dating', 'serious_relationship', 'marriage'] as $goal):
                                            $sel = ($data['relationship_goal'] ?? '') === $goal ? 'selected' : '';
                                        ?>
                                            <option value="<?= $goal; ?>" <?= $sel; ?>><?= ucfirst(str_replace('_', ' ', $goal)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Bio</label>
                                <textarea name="bio" rows="4" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm"><?= profile_label($data['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-50 pb-2">Partner Preferences</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Preferred Gender</label>
                                    <select name="preferred_gender" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                                        <option value="male" <?= ($data['preferred_gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Men</option>
                                        <option value="female" <?= ($data['preferred_gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Women</option>
                                        <option value="both" <?= ($data['preferred_gender'] ?? '') === 'both' ? 'selected' : ''; ?>>Everyone</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Min Age</label>
                                    <input type="number" name="min_age" min="18" max="100" value="<?= profile_label((string) ($data['min_age'] ?? 18)); ?>" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Max Age</label>
                                    <input type="number" name="max_age" min="18" max="100" value="<?= profile_label((string) ($data['max_age'] ?? 35)); ?>" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Education</label>
                                    <select name="preferred_education_level" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                        <?php foreach (['any', 'high_school', 'bachelor', 'master', 'phd'] as $edu): ?>
                                            <option value="<?= $edu; ?>" <?= ($data['preferred_education_level'] ?? '') === $edu ? 'selected' : ''; ?>><?= ucfirst(str_replace('_', ' ', $edu)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Outfit Style</label>
                                    <select name="preferred_outfit_style" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                        <?php foreach (['any', 'casual', 'formal', 'streetwear', 'sporty', 'minimalist'] as $style): ?>
                                            <option value="<?= $style; ?>" <?= ($data['preferred_outfit_style'] ?? '') === $style ? 'selected' : ''; ?>><?= ucfirst($style); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Meal Type</label>
                                    <select name="preferred_meal_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                        <?php foreach (['any', 'vegetarian', 'vegan', 'fast_food', 'traditional'] as $meal): ?>
                                            <option value="<?= $meal; ?>" <?= ($data['preferred_meal_type'] ?? '') === $meal ? 'selected' : ''; ?>><?= ucfirst(str_replace('_', ' ', $meal)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Preferred Location</label>
                                <input type="text" name="preferred_location" value="<?= profile_label($data['preferred_location'] ?? ''); ?>" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm" placeholder="e.g., Kigali">
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <?php if ($has_saved_profile): ?>
                                <a href="profile.php" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition">Cancel</a>
                            <?php endif; ?>
                            <button type="submit" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold px-8 py-3 rounded-xl hover:opacity-95 shadow-md transition">
                                <?= $has_saved_profile ? 'Save Changes' : 'Save Profile'; ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
