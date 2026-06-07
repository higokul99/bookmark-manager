<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: ../public/actions/admin_user_crud.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>
<div class="min-h-screen p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold accent-yellow">Admin Portal</h1>
            <a href="index.php?logout=1" class="px-4 py-2 border border-white/20 rounded-lg hover:bg-white/10 transition">
                Logout
            </a>
        </div>
        
        <div class="glass rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Create New User</h2>
            <form action="../public/actions/admin_user_crud.php" method="POST" class="flex gap-4">
                <input type="hidden" name="action" value="create">
                <div class="flex-1">
                    <input type="text" name="username" placeholder="Username" required
                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                </div>
                <div class="w-48">
                    <input type="text" name="pin" placeholder="4-digit PIN" maxlength="4" required
                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                </div>
                <button type="submit"
                    class="px-6 py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                    Create
                </button>
            </form>
        </div>
        
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-semibold mb-4">Existing Users</h2>
            <div class="space-y-3">
                <?php foreach ($users as $user): ?>
                    <div class="flex items-center justify-between bg-white/5 p-4 rounded-lg">
                        <div>
                            <div class="font-medium"><?= htmlspecialchars($user['username']) ?></div>
                            <div class="text-sm text-gray-400">Created: <?= $user['created_at'] ?></div>
                        </div>
                        <form action="../public/actions/admin_user_crud.php" method="POST" class="inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit"
                                class="px-4 py-2 bg-red-500/20 border border-red-500/50 text-red-300 rounded-lg hover:bg-red-500/30 transition">
                                Delete
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
