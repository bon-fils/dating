<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/discovery_exclude.php';

$current_user_id = (int) $_SESSION['user_id'];

$gender   = $_GET['gender'] ?? 'all';
$location = $_GET['location'] ?? 'all';
$style    = $_GET['style'] ?? 'all';
$diet     = $_GET['diet'] ?? 'all';
$max_age  = isset($_GET['max_age']) ? (int) $_GET['max_age'] : 50;

if ($max_age < 18) {
    $max_age = 18;
}
if ($max_age > 99) {
    $max_age = 99;
}

$query = "
    SELECT
        u.id,
        u.username,
        u.gender,
        u.age,
        u.education_level,
        u.outfit_style,
        u.meal_type,
        u.location,
        u.is_verified,
        u.is_active
    FROM users u
    WHERE u.id != :current_user_id
      AND u.is_active = 1
      AND u.age <= :max_age
";

$query .= discoveryExcludeClause();

$bindings = array_merge(
    [
        ':current_user_id' => $current_user_id,
        ':max_age' => $max_age,
    ],
    discoveryExcludeBindings($current_user_id)
);

if ($gender !== 'all') {
    $query .= ' AND u.gender = :gender';
    $bindings[':gender'] = $gender;
}

if ($location !== 'all' && trim($location) !== '') {
    $query .= ' AND u.location LIKE :location';
    $bindings[':location'] = '%' . trim($location) . '%';
}

if ($style !== 'all') {
    $query .= ' AND u.outfit_style = :style';
    $bindings[':style'] = $style;
}

if ($diet !== 'all') {
    $query .= ' AND u.meal_type = :diet';
    $bindings[':diet'] = $diet;
}

$query .= ' ORDER BY u.id DESC LIMIT 40';

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($bindings);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as &$user) {
        $user['id'] = (int) $user['id'];
        $user['age'] = (int) $user['age'];
        $user['is_verified'] = (int) $user['is_verified'] === 1;
        $user['is_active'] = (int) $user['is_active'] === 1;
        $user['img'] = 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=random&size=150';
    }
    unset($user);

    echo json_encode($users, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    error_log('Discovery stream error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Could not load discovery profiles.']);
}
