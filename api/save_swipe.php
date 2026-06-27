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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$current_user_id = (int) $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$target_id = isset($input['target_id']) ? (int) $input['target_id'] : 0;
$action = $input['action'] ?? null;

if ($target_id <= 0 || $target_id === $current_user_id || !in_array($action, ['like', 'dislike'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request parameters.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $checkDuplicate = $pdo->prepare('SELECT id FROM swipes WHERE swiper_id = ? AND target_id = ?');
    $checkDuplicate->execute([$current_user_id, $target_id]);

    if ($checkDuplicate->fetch()) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'You already responded to this profile.']);
        exit;
    }

    $logSwipe = $pdo->prepare('INSERT INTO swipes (swiper_id, target_id, action) VALUES (?, ?, ?)');
    $logSwipe->execute([$current_user_id, $target_id, $action]);

    $request_sent = false;
    $already_requested_you = false;

    if ($action === 'like') {
        $incoming = $pdo->prepare("
            SELECT id FROM connection_requests
            WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'
        ");
        $incoming->execute([$target_id, $current_user_id]);

        if ($incoming->fetch()) {
            $already_requested_you = true;
        } else {
            $outgoing = $pdo->prepare("
                SELECT id, status FROM connection_requests
                WHERE sender_id = ? AND receiver_id = ?
            ");
            $outgoing->execute([$current_user_id, $target_id]);
            $myRequest = $outgoing->fetch(PDO::FETCH_ASSOC);

            if (!$myRequest) {
                $insertReq = $pdo->prepare("
                    INSERT INTO connection_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')
                ");
                $insertReq->execute([$current_user_id, $target_id]);
                $request_sent = true;

                $senderName = $_SESSION['username'] ?? 'Someone';
                $notify = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, content) VALUES (?, 'like', ?)
                ");
                $notify->execute([$target_id, $senderName . ' sent you a connection request.']);
            } elseif ($myRequest['status'] === 'rejected') {
                $renew = $pdo->prepare("
                    UPDATE connection_requests
                    SET status = 'pending', responded_at = NULL, created_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $renew->execute([$myRequest['id']]);
                $request_sent = true;
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'request_sent' => $request_sent,
        'already_requested_you' => $already_requested_you,
        'is_match' => false,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Swipe error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Could not save your response. Please try again.']);
}
