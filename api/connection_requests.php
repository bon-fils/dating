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
$method = $_SERVER['REQUEST_METHOD'];

function formatRequestUser(array $row): array
{
    return [
        'request_id' => (int) $row['request_id'],
        'user_id' => (int) $row['user_id'],
        'username' => $row['username'],
        'age' => (int) $row['age'],
        'gender' => $row['gender'],
        'location' => $row['location'],
        'outfit_style' => $row['outfit_style'],
        'meal_type' => $row['meal_type'],
        'occupation' => $row['occupation'] ?? '',
        'bio' => $row['bio'] ?? '',
        'is_verified' => (int) ($row['is_verified'] ?? 0) === 1,
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'img' => 'https://ui-avatars.com/api/?name=' . urlencode($row['username']) . '&background=random&size=150',
    ];
}

function createMatchIfNeeded(PDO $pdo, int $userA, int $userB): int
{
    $user1 = min($userA, $userB);
    $user2 = max($userA, $userB);

    $check = $pdo->prepare('SELECT id FROM matches WHERE user1_id = ? AND user2_id = ?');
    $check->execute([$user1, $user2]);

    $existing = $check->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        return (int) $existing['id'];
    }

    $insert = $pdo->prepare('INSERT INTO matches (user1_id, user2_id) VALUES (?, ?)');
    $insert->execute([$user1, $user2]);

    return (int) $pdo->lastInsertId();
}

if ($method === 'GET') {
    $type = $_GET['type'] ?? 'incoming';

    try {
        if ($type === 'count') {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS total FROM connection_requests
                WHERE receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$current_user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['count' => (int) ($row['total'] ?? 0)]);
            exit;
        }

        if ($type === 'incoming') {
            $stmt = $pdo->prepare("
                SELECT cr.id AS request_id, cr.status, cr.created_at,
                       u.id AS user_id, u.username, u.age, u.gender, u.location,
                       u.outfit_style, u.meal_type, u.is_verified,
                       p.occupation, p.bio
                FROM connection_requests cr
                JOIN users u ON u.id = cr.sender_id
                LEFT JOIN profiles p ON p.user_id = u.id
                WHERE cr.receiver_id = ? AND cr.status = 'pending'
                ORDER BY cr.created_at DESC
            ");
            $stmt->execute([$current_user_id]);
        } else {
            $stmt = $pdo->prepare("
                SELECT cr.id AS request_id, cr.status, cr.created_at,
                       u.id AS user_id, u.username, u.age, u.gender, u.location,
                       u.outfit_style, u.meal_type, u.is_verified,
                       p.occupation, p.bio
                FROM connection_requests cr
                JOIN users u ON u.id = cr.receiver_id
                LEFT JOIN profiles p ON p.user_id = u.id
                WHERE cr.sender_id = ? AND cr.status IN ('pending', 'accepted', 'rejected')
                ORDER BY cr.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$current_user_id]);
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $list = array_map('formatRequestUser', $rows);

        echo json_encode(['status' => 'success', 'data' => $list]);
    } catch (PDOException $e) {
        error_log('Requests list error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Could not load requests.']);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    try {
        if ($action === 'accept' || $action === 'reject') {
            $request_id = (int) ($input['request_id'] ?? 0);
            if ($request_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request id.']);
                exit;
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT id, sender_id, receiver_id, status
                FROM connection_requests WHERE id = ? AND receiver_id = ?
            ");
            $stmt->execute([$request_id, $current_user_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request || $request['status'] !== 'pending') {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['error' => 'Request not found or already handled.']);
                exit;
            }

            $sender_id = (int) $request['sender_id'];

            if ($action === 'accept') {
                $accept = $pdo->prepare("
                    UPDATE connection_requests SET status = 'accepted', responded_at = NOW() WHERE id = ?
                ");
                $accept->execute([$request_id]);

                $acceptReverse = $pdo->prepare("
                    UPDATE connection_requests
                    SET status = 'accepted', responded_at = NOW()
                    WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'
                ");
                $acceptReverse->execute([$current_user_id, $sender_id]);

                $match_id = createMatchIfNeeded($pdo, $current_user_id, $sender_id);

                $notify = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, content) VALUES (?, 'match', ?)
                ");
                $name = $_SESSION['username'] ?? 'Someone';
                $notify->execute([$sender_id, $name . ' accepted your connection request.']);

                $pdo->commit();

                echo json_encode([
                    'status' => 'success',
                    'action' => 'accepted',
                    'match_id' => $match_id,
                ]);
            } else {
                $reject = $pdo->prepare("
                    UPDATE connection_requests SET status = 'rejected', responded_at = NOW() WHERE id = ?
                ");
                $reject->execute([$request_id]);
                $pdo->commit();

                echo json_encode(['status' => 'success', 'action' => 'rejected']);
            }
            exit;
        }

        if ($action === 'terminate') {
            $match_id = (int) ($input['match_id'] ?? 0);
            if ($match_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid match id.']);
                exit;
            }

            $pdo->beginTransaction();

            $matchStmt = $pdo->prepare('
                SELECT id, user1_id, user2_id FROM matches
                WHERE id = ? AND (user1_id = ? OR user2_id = ?)
            ');
            $matchStmt->execute([$match_id, $current_user_id, $current_user_id]);
            $match = $matchStmt->fetch(PDO::FETCH_ASSOC);

            if (!$match) {
                $pdo->rollBack();
                http_response_code(403);
                echo json_encode(['error' => 'Match not found.']);
                exit;
            }

            $partner_id = (int) $match['user1_id'] === $current_user_id
                ? (int) $match['user2_id']
                : (int) $match['user1_id'];

            $pdo->prepare('DELETE FROM messages WHERE match_id = ?')->execute([$match_id]);
            $pdo->prepare('DELETE FROM matches WHERE id = ?')->execute([$match_id]);

            $pdo->prepare('
                UPDATE connection_requests SET status = ?, responded_at = NOW()
                WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ')->execute(['rejected', $current_user_id, $partner_id, $partner_id, $current_user_id]);

            $pdo->prepare('
                DELETE FROM swipes
                WHERE (swiper_id = ? AND target_id = ?) OR (swiper_id = ? AND target_id = ?)
            ')->execute([$current_user_id, $partner_id, $partner_id, $current_user_id]);

            $pdo->commit();

            echo json_encode([
                'status' => 'success',
                'action' => 'terminated',
                'message' => 'Connection ended. You can discover each other again.',
            ]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Unknown action.']);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Connection request action error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Could not complete action.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
