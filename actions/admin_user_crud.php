<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/portal.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $username = $_POST['username'] ?? '';
    $pin = $_POST['pin'] ?? '';
    
    if (strlen($username) > 0 && strlen($pin) === 4 && is_numeric($pin)) {
        $pinHash = password_hash($pin, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, pin_hash) VALUES (:username, :pin_hash)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':pin_hash', $pinHash);
        $stmt->execute();
    }
} elseif ($action === 'delete') {
    $userId = $_POST['user_id'] ?? '';
    
    if (is_numeric($userId)) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}

header('Location: ../admin/portal.php');
exit;
