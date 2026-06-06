<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/dashboard.php');
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name = $_POST['name'] ?? '';
    
    if (strlen($name) > 0) {
        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, category_id) VALUES (:user_id, :name, NULL)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
    }
} elseif ($action === 'delete') {
    $categoryId = $_POST['category_id'] ?? '';
    
    if (is_numeric($categoryId)) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :category_id AND user_id = :user_id");
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
} elseif ($action === 'pin') {
    $categoryId = $_POST['category_id'] ?? '';
    
    if (is_numeric($categoryId)) {
        // Toggle is_pinned
        $stmt = $pdo->prepare("SELECT is_pinned FROM categories WHERE id = :category_id AND user_id = :user_id");
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category) {
            $newPinned = $category['is_pinned'] ? 0 : 1;
            $updateStmt = $pdo->prepare("UPDATE categories SET is_pinned = :is_pinned WHERE id = :category_id AND user_id = :user_id");
            $updateStmt->bindParam(':is_pinned', $newPinned, PDO::PARAM_INT);
            $updateStmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $updateStmt->execute();
        }
    }
}

header('Location: ../public/dashboard.php');
exit;
