<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Check lockout
if (isset($_SESSION['lockout_time']) && (time() - $_SESSION['lockout_time'] < 300)) {
    $remaining = 300 - (time() - $_SESSION['lockout_time']);
    echo json_encode([
        'success' => false,
        'lockout' => true,
        'message' => "Too many attempts. Please try again in " . ceil($remaining / 60) . " minutes."
    ]);
    exit;
}

$pin = $_POST['pin'] ?? '';

// Get all users and check each pin (since multiple users could have same pin but we'll use first match)
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$authenticatedUser = null;

foreach ($users as $user) {
    if (password_verify($pin, $user['pin_hash'])) {
        $authenticatedUser = $user;
        break;
    }
}

if ($authenticatedUser) {
    // Success
    $_SESSION['user_id'] = $authenticatedUser['id'];
    $_SESSION['login_attempts'] = 0;
    unset($_SESSION['lockout_time']);
    echo json_encode(['success' => true]);
} else {
    // Failed
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    
    if ($_SESSION['login_attempts'] >= 3) {
        $_SESSION['lockout_time'] = time();
        echo json_encode([
            'success' => false,
            'lockout' => true,
            'message' => "Too many attempts. Please try again in 5 minutes."
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Invalid PIN. Attempts remaining: " . (3 - $_SESSION['login_attempts'])
        ]);
    }
}
