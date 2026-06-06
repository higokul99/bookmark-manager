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
    $categoryId = $_POST['category_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $url = $_POST['url'] ?? '';
    
    if (is_numeric($categoryId) && strlen($title) > 0 && strlen($url) > 0) {
        $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, category_id, title, url) VALUES (:user_id, :category_id, :title, :url)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->execute();
    }
} elseif ($action === 'update') {
    $bookmarkId = $_POST['bookmark_id'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $url = $_POST['url'] ?? '';
    
    if (is_numeric($bookmarkId) && is_numeric($categoryId) && strlen($title) > 0 && strlen($url) > 0) {
        $stmt = $pdo->prepare("UPDATE bookmarks SET category_id = :category_id, title = :title, url = :url WHERE id = :bookmark_id AND user_id = :user_id");
        $stmt->bindParam(':bookmark_id', $bookmarkId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
} elseif ($action === 'delete') {
    $bookmarkId = $_POST['bookmark_id'] ?? '';
    
    if (is_numeric($bookmarkId)) {
        $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE id = :bookmark_id AND user_id = :user_id");
        $stmt->bindParam(':bookmark_id', $bookmarkId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}

header('Location: ../public/dashboard.php');
exit;
