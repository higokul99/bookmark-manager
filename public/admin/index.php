<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === 'Nego@2026') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: portal.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}

require_once '../../includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="glass rounded-2xl p-8 max-w-md w-full">
        <h1 class="text-2xl font-bold mb-6 accent-yellow text-center">Admin Login</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-2 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm mb-1">Admin Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
            </div>
            <button type="submit"
                class="w-full py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                Login
            </button>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
