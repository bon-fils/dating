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
require_once __DIR__ . '/../includes/discovery_exclude.php';

$current_user_id = (int) $_SESSION['user_id'];

try {
    $query = "
        SELECT
            u.id,
            u.username,
            u.age,
            u.outfit_style,
            u.meal_type,
            u.is_verified,
            FLOOR(70 + (RAND() * 25)) AS score,
            'Online' AS status
        FROM users u
        WHERE u.id != :current_user
          AND u.is_active = 1
    ";

    $query .= discoveryExcludeClause();
    $query .= ' ORDER BY RAND() LIMIT 4';

    $stmt = $pdo->prepare($query);
    $stmt->execute(array_merge(
        [':current_user' => $current_user_id],
        discoveryExcludeBindings($current_user_id)
    ));

    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($suggestions as &$row) {
        $row['id'] = (int) $row['id'];
        $row['age'] = (int) $row['age'];
        $row['is_verified'] = (int) $row['is_verified'] === 1;
        $row['img'] = 'https://ui-avatars.com/api/?name=' . urlencode($row['username']) . '&background=random&size=150';
    }
    unset($row);

    echo json_encode($suggestions ?: [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    error_log('Suggestions error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Could not load suggestions.']);
}
