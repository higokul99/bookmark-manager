<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$editMode = isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'true';
$_SESSION['edit_mode'] = $editMode;

http_response_code(200);
exit;
