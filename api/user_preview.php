<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$current_user_id = (int) $_SESSION['user_id'];
$profile_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($profile_id <= 0 || $profile_id === $current_user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user id.']);
    exit;
}

try {
    $allowed = $pdo->prepare("
        SELECT 1 FROM connection_requests
        WHERE status IN ('pending', 'accepted')
          AND (
              (sender_id = ? AND receiver_id = ?)
              OR (sender_id = ? AND receiver_id = ?)
          )
        LIMIT 1
    ");
    $allowed->execute([$profile_id, $current_user_id, $current_user_id, $profile_id]);

    if (!$allowed->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Profile not available.']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.age, u.gender, u.location, u.outfit_style, u.meal_type,
               u.education_level, u.is_verified, p.occupation, p.bio, p.relationship_goal
        FROM users u
        LEFT JOIN profiles p ON p.user_id = u.id
        WHERE u.id = ? AND u.is_active = 1
    ");
    $stmt->execute([$profile_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found.']);
        exit;
    }

    $user['id'] = (int) $user['id'];
    $user['age'] = (int) $user['age'];
    $user['is_verified'] = (int) $user['is_verified'] === 1;
    $user['img'] = 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=random&size=200';

    echo json_encode(['status' => 'success', 'data' => $user]);
} catch (PDOException $e) {
    error_log('User preview error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Could not load profile.']);
}
