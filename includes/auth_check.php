<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please sign in to access that page.';
    header('Location: login.php');
    exit;
}
