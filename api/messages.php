<?php
// api/messages.php

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authorization Gate
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

require_once '../config/database.php';
$current_user = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// ROUTE A: FETCH MESSAGES (GET REQUESTS)
// ==========================================
if ($method === 'GET') {
    $match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;

    if ($match_id <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing match parameter identity."]);
        exit();
    }

    try {
        // Verification: Isolate security boundaries to participants within this conversation room
        $auth_stmt = $pdo->prepare('SELECT id FROM matches WHERE id = ? AND (user1_id = ? OR user2_id = ?)');
        $auth_stmt->execute([$match_id, $current_user, $current_user]);
        
        if (!$auth_stmt->fetch()) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied to message channel history."]);
            exit();
        }

        // Automatic Status Transformation: Mark incoming unread messages as read
        $update_read_stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE match_id = ? AND sender_id != ? AND is_read = 0
        ");
        $update_read_stmt->execute([$match_id, $current_user]);

        // Fetch Room logs using your precise schema names (sent_at, message, is_read)
        $msg_stmt = $pdo->prepare("
            SELECT 
                id, 
                match_id, 
                sender_id, 
                message, 
                is_read,
                DATE_FORMAT(sent_at, '%H:%i') as timestamp 
            FROM messages 
            WHERE match_id = ? 
            ORDER BY sent_at ASC
        ");
        $msg_stmt->execute([$match_id]);
        $messages = $msg_stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "data" => $messages
        ]);
        exit();

    } catch (PDOException $e) {
        error_log('Messages GET error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Could not load messages.']);
        exit();
    }
}

// ==========================================
// ROUTE B: TRANSMIT MESSAGE (POST REQUESTS)
// ==========================================
if ($method === 'POST') {
    $raw_input = file_get_contents('php://input');
    $payload = json_decode($raw_input, true);

    $match_id = isset($payload['match_id']) ? (int)$payload['match_id'] : 0;
    $message_text = isset($payload['message']) ? trim($payload['message']) : '';

    if ($match_id <= 0 || $message_text === '') {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete package data parameters."]);
        exit();
    }

    try {
        // Verification: Confirm user permissions within this channel
        $auth_stmt = $pdo->prepare('SELECT id FROM matches WHERE id = ? AND (user1_id = ? OR user2_id = ?)');
        $auth_stmt->execute([$match_id, $current_user, $current_user]);
        
        if (!$auth_stmt->fetch()) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Unauthorized channel submission attempt."]);
            exit();
        }

        // Insert new entry. 'is_read' defaults safely to 0 based on your table constraint.
        // 'sent_at' handles auto-population via CURRENT_TIMESTAMP.
        $insert_stmt = $pdo->prepare("
            INSERT INTO messages (match_id, sender_id, message) 
            VALUES (?, ?, ?)
        ");
        $insert_stmt->execute([$match_id, $current_user, $message_text]);

        echo json_encode(["status" => "success", "message" => "Message sent successfully."]);
        exit();

    } catch (PDOException $e) {
        error_log('Messages POST error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Could not send message.']);
        exit();
    }
}

// Fallback response handling configuration
http_response_code(405);
echo json_encode(["status" => "error", "message" => "HTTP routing method signature unsupported."]);