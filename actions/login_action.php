<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identity = trim($_POST['login_identity'] ?? '');
    $password       = $_POST['password'] ?? '';

    if (empty($login_identity) || empty($password)) {
        $_SESSION['error'] = "Please fill in all layout fields.";
        header('Location: ../public/login.php');
        exit;
    }

    try {
        // Find user by either their unique username or email configuration
        $stmt = $pdo->prepare("SELECT id, username, password, is_active FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$login_identity, $login_identity]);
        $user = $stmt->fetch();

        // Perform security validation verification check
        if ($user && password_verify($password, $user['password'])) {
            
            // Administrative check to ensure account is not suspended or banned
            if (!$user['is_active']) {
                $_SESSION['error'] = "Your account has been restricted by an administrator.";
                header('Location: ../public/login.php');
                exit;
            }

            // Regenerate session ID to mitigate session fixation exploits
            session_regenerate_id(true);

            // Establish authorization state flags
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Clear any stray error flags and step cleanly onto dashboard
            unset($_SESSION['error']);
            header("Location: ../public/dashboard.php");
            exit;
        } else {
            // Provide generalized failure notification to obscure vector targets
            $_SESSION['error'] = "Invalid login credentials provided.";
            header('Location: ../public/login.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['error'] = 'Authentication system error. Please try again later.';
        header('Location: ../public/login.php');
        exit;
    }
} else {
    header('Location: ../public/login.php');
    exit;
}