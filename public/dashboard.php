<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

$userId = $_SESSION['user_id'];

// Fetch top-level categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = :user_id AND category_id IS NULL ORDER BY is_pinned DESC, name ASC");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subcategories
$subcategories = [];
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = :user_id AND category_id IS NOT NULL ORDER BY name ASC");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $sub) {
    $subcategories[$sub['category_id']][] = $sub;
}

// Fetch bookmarks (grouped by category)
$bookmarks = [];
$stmt = $pdo->prepare("SELECT * FROM bookmarks WHERE user_id = :user_id ORDER BY id DESC");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $bm) {
    $bookmarks[$bm['category_id']][] = $bm;
}

require_once '../includes/header.php';
?>
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold accent-yellow">Metora Bookmarks</h1>
            </div>
            <div class="flex gap-4">
                <button id="toggle-edit"
                    class="px-4 py-2 border border-white/20 rounded-lg hover:bg-white/10 transition">
                    Edit Mode
                </button>
                <a href="login.php?logout=1"
                    class="px-4 py-2 border border-white/20 rounded-lg hover:bg-white/10 transition">
                    Logout
                </a>
            </div>
        </div>

        <div id="edit-panel" class="glass rounded-2xl p-6 mb-8 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-3">Add Category</h3>
                    <form id="add-category-form" class="flex gap-2">
                        <input type="text" name="name" placeholder="Category name" required
                            class="flex-1 px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                        <button type="submit"
                            class="px-4 py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                            Add
                        </button>
                    </form>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Add Bookmark</h3>
                    <form id="add-bookmark-form" class="flex flex-col md:flex-row gap-2">
                        <select name="category_id" required
                            class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="title" placeholder="Title" required
                            class="flex-1 px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                        <input type="url" name="url" placeholder="URL" required
                            class="flex-1 px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                        <button type="submit"
                            class="px-4 py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                            Add
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categories as $category): ?>
                <div class="glass rounded-2xl p-6" data-category-id="<?= $category['id'] ?>">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-semibold flex items-center gap-2">
                                <?php if ($category['is_pinned']): ?>
                                    <span class="accent-yellow">📌</span>
                                <?php endif; ?>
                                <?= htmlspecialchars($category['name']) ?>
                            </h2>
                        </div>
                        <div class="edit-controls hidden flex gap-2">
                            <button class="pin-btn text-yellow-400 hover:text-yellow-300" data-category-id="<?= $category['id'] ?>">
                                <?= $category['is_pinned'] ? 'Unpin' : 'Pin' ?>
                            </button>
                            <button class="delete-category-btn text-red-400 hover:text-red-300" data-category-id="<?= $category['id'] ?>">
                                Delete
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm text-gray-400 mb-2">Subcategories:</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php if (isset($subcategories[$category['id']])): ?>
                                <?php foreach ($subcategories[$category['id']] as $subcat): ?>
                                    <span class="px-2 py-1 bg-white/5 rounded text-sm">
                                        <?= htmlspecialchars($subcat['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <?php if (isset($bookmarks[$category['id']])): ?>
                            <?php foreach ($bookmarks[$category['id']] as $bm): ?>
                                <div class="flex items-center justify-between bg-white/5 p-3 rounded-lg">
                                    <a href="<?= htmlspecialchars($bm['url']) ?>" target="_blank"
                                        class="accent-yellow hover:underline truncate">
                                        <?= htmlspecialchars($bm['title']) ?>
                                    </a>
                                    <button class="delete-bookmark-btn edit-controls hidden text-red-400 hover:text-red-300" data-bookmark-id="<?= $bm['id'] ?>">
                                        ×
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
let editMode = false;

document.getElementById('toggle-edit').addEventListener('click', async function() {
    editMode = !editMode;
    
    try {
        const response = await fetch('../actions/toggle_edit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ edit_mode: editMode })
        });
        
        if (response.ok) {
            const editPanel = document.getElementById('edit-panel');
            const editControls = document.querySelectorAll('.edit-controls');
            
            if (editMode) {
                editPanel.classList.remove('hidden');
                editControls.forEach(el => el.classList.remove('hidden'));
                this.textContent = 'Exit Edit Mode';
                this.classList.add('border-yellow-400');
            } else {
                editPanel.classList.add('hidden');
                editControls.forEach(el => el.classList.add('hidden'));
                this.textContent = 'Edit Mode';
                this.classList.remove('border-yellow-400');
            }
        }
    } catch (err) {
        console.error(err);
    }
});

document.getElementById('add-category-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'create');
    
    try {
        const response = await fetch('../actions/category_crud.php', {
            method: 'POST',
            body: formData
        });
        window.location.reload();
    } catch (err) {
        console.error(err);
    }
});

document.getElementById('add-bookmark-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'create');
    
    try {
        const response = await fetch('../actions/bookmark_crud.php', {
            method: 'POST',
            body: formData
        });
        window.location.reload();
    } catch (err) {
        console.error(err);
    }
});

document.querySelectorAll('.delete-category-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Delete this category and all bookmarks inside?')) return;
        
        const formData = new FormData();
        formData.set('action', 'delete');
        formData.set('category_id', this.dataset.categoryId);
        
        try {
            await fetch('../actions/category_crud.php', { method: 'POST', body: formData });
            window.location.reload();
        } catch (err) {
            console.error(err);
        }
    });
});

document.querySelectorAll('.pin-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const formData = new FormData();
        formData.set('action', 'pin');
        formData.set('category_id', this.dataset.categoryId);
        
        try {
            await fetch('../actions/category_crud.php', { method: 'POST', body: formData });
            window.location.reload();
        } catch (err) {
            console.error(err);
        }
    });
});

document.querySelectorAll('.delete-bookmark-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Delete this bookmark?')) return;
        
        const formData = new FormData();
        formData.set('action', 'delete');
        formData.set('bookmark_id', this.dataset.bookmarkId);
        
        try {
            await fetch('../actions/bookmark_crud.php', { method: 'POST', body: formData });
            window.location.reload();
        } catch (err) {
            console.error(err);
        }
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>
