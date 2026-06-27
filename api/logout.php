<?php
// Initialize or intercept the active session state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Unset all active session superglobal variables
$_SESSION = array();

// 2. Destruct the session cookie actively in the client browser if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, // Expire timestamp set aggressively in the past
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Clear and destroy the session server storage space completely
session_destroy();

// 4. Clean start a brand new session context to securely flash a status update message
session_start();
$_SESSION['success'] = "You have been logged out securely. See you soon!";

// 5. Instantly redirect back to our split-screen modern login portal
header("Location: ../public/login.php");
exit();