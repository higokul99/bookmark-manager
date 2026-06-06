<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metora Bookmarks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #071954;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .glass {
            background: rgba(7, 25, 84, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .accent-yellow {
            color: #facc15;
        }
        .accent-yellow-bg {
            background-color: #facc15;
        }
        .accent-yellow-border {
            border-color: #facc15;
        }
        select {
            color: white;
        }
        select option {
            background-color: #071954;
            color: white;
        }
    </style>
</head>
<body class="text-white">
