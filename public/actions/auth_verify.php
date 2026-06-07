<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

// Logging function
function logLoginAction($message) {
    $logFile = '../../logs/login.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $logEntry = "[{$timestamp}] [IP: {$ip}] {$message}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logLoginAction("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Check lockout
if (isset($_SESSION['lockout_time']) && (time() - $_SESSION['lockout_time'] < 300)) {
    $remaining = 300 - (time() - $_SESSION['lockout_time']);
    logLoginAction("Lockout active. Remaining time: " . ceil($remaining / 60) . " minutes");
    echo json_encode([
        'success' => false,
        'lockout' => true,
        'message' => "Too many attempts. Please try again in " . ceil($remaining / 60) . " minutes."
    ]);
    exit;
}

$pin = $_POST['pin'] ?? '';
logLoginAction("Login attempt received");

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
    logLoginAction("Login successful for user: " . $authenticatedUser['username'] . " (ID: " . $authenticatedUser['id'] . ")");
    echo json_encode(['success' => true]);
} else {
    // Failed
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    
    if ($_SESSION['login_attempts'] >= 3) {
        $_SESSION['lockout_time'] = time();
        logLoginAction("Login failed, lockout triggered after 3 attempts");
        echo json_encode([
            'success' => false,
            'lockout' => true,
            'message' => "Too many attempts. Please try again in 5 minutes."
        ]);
    } else {
        logLoginAction("Login failed, attempts remaining: " . (3 - $_SESSION['login_attempts']));
        echo json_encode([
            'success' => false,
            'message' => "Invalid PIN. Attempts remaining: " . (3 - $_SESSION['login_attempts'])
        ]);
    }
}
