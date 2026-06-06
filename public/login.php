<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="glass rounded-2xl p-8 max-w-md w-full">
        <h1 class="text-3xl font-bold mb-2 text-center">Metora Bookmarks</h1>
        <p class="text-gray-400 text-center mb-8">Your personal bookmark manager</p>
        
        <div id="error-message" class="hidden bg-red-500/20 border border-red-500 text-red-200 px-4 py-2 rounded mb-4"></div>
        <div id="lockout-message" class="hidden bg-orange-500/20 border border-orange-500 text-orange-200 px-4 py-2 rounded mb-4"></div>
        
        <form id="login-form" class="space-y-4">
            <div>
                <label class="block text-sm mb-1">4-digit PIN</label>
                <input type="password" name="pin" id="pin" maxlength="4" required
                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
            </div>
            <button type="submit"
                class="w-full py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                Login
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const errorMessage = document.getElementById('error-message');
    const lockoutMessage = document.getElementById('lockout-message');
    errorMessage.classList.add('hidden');
    lockoutMessage.classList.add('hidden');
    
    try {
        const response = await fetch('../actions/auth_verify.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                pin: document.getElementById('pin').value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = 'dashboard.php';
        } else {
            if (data.lockout) {
                lockoutMessage.textContent = data.message;
                lockoutMessage.classList.remove('hidden');
            } else {
                errorMessage.textContent = data.message;
                errorMessage.classList.remove('hidden');
            }
        }
    } catch (err) {
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.classList.remove('hidden');
    }
});
</script>
<?php require_once '../includes/footer.php'; ?>
