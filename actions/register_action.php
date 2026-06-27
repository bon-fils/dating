<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Clean and sanitize all string inputs
    $username        = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $email           = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $password        = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender          = $_POST['gender'] ?? '';
    $age             = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $location        = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS));
    $height          = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_FLOAT);
    $weight          = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $education_level = $_POST['education_level'] ?? 'other';
    $outfit_style    = $_POST['outfit_style'] ?? 'other';
    $meal_type       = $_POST['meal_type'] ?? 'other';

    // 2. Validate essential criteria
    if (!$username || !$email || strlen($password) < 6 || !$age || $age < 18) {
        $_SESSION['error'] = 'Invalid input details. Password must be at least 6 characters and age must be 18 or older.';
        header('Location: ../public/register.php');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: ../public/register.php');
        exit;
    }

    try {
        // 3. Confirm uniqueness of username/email
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $checkStmt->execute([$email, $username]);
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "Username or Email is already taken.";
            header("Location: ../public/register.php");
            exit;
        }

        // 4. Transform password with modern cryptographically secure standard hashing
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // 5. Fire transaction query onto users database table
        $sql = "INSERT INTO users (username, email, password, gender, age, height, weight, education_level, outfit_style, meal_type, location) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $pdo->prepare($sql);
        $insertStmt->execute([
            $username, $email, $hashed_password, $gender, $age, 
            $height, $weight, $education_level, $outfit_style, $meal_type, $location
        ]);

        // Get the newly registered User ID
        $newUserId = $pdo->lastInsertId();

        // 6. Pre-populate empty profile and preference rows to satisfy 1:1 constraints safely
        $profileSql = "INSERT INTO profiles (user_id) VALUES (?)";
        $prefSql    = "INSERT INTO preferences (user_id, preferred_gender) VALUES (?, ?)";
        
        $pdo->prepare($profileSql)->execute([$newUserId]);
        
        // Pick an intuitive fallback gender default preference based on biological entry
        $defaultPrefGender = ($gender === 'male') ? 'female' : (($gender === 'female') ? 'male' : 'both');
        $pdo->prepare($prefSql)->execute([$newUserId, $defaultPrefGender]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['username'] = $username;
        $_SESSION['user_name'] = $username;

        header('Location: ../public/dashboard.php');
        exit;

    } catch (PDOException $e) {
        error_log("Registration DB Error: " . $e->getMessage());
        $_SESSION['error'] = "System failure during record creation. Please try again later.";
        header("Location: ../public/register.php");
        exit;
    }
} else {
    header("Location: ../public/register.php");
    exit;
}