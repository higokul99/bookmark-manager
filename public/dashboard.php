<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

$userId = $_SESSION['user_id'];

// Fetch all categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = :user_id ORDER BY is_pinned DESC, name ASC");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate into main categories and subcategories
$mainCategories = [];
$subcategoriesByParent = [];
foreach ($allCategories as $cat) {
    if ($cat['category_id'] === null) {
        $mainCategories[] = $cat;
    } else {
        $subcategoriesByParent[$cat['category_id']][] = $cat;
    }
}

// Fetch all bookmarks
$bookmarksByCategory = [];
$stmt = $pdo->prepare("SELECT * FROM bookmarks WHERE user_id = :user_id ORDER BY id DESC");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $bm) {
    $bookmarksByCategory[$bm['category_id']][] = $bm;
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
                    <form id="add-category-form" class="flex flex-col gap-2">
                        <input type="text" name="name" placeholder="Category name" required
                            class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                        <select name="parent_id" class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                            <option value="">No parent (main category)</option>
                            <?php foreach ($mainCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit"
                            class="px-4 py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                            Add
                        </button>
                    </form>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Add Bookmark</h3>
                    <form id="add-bookmark-form" class="flex flex-col gap-2">
                        <select name="category_id" required
                            class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                            <option value="">Select category</option>
                            <?php foreach ($mainCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php if (isset($subcategoriesByParent[$cat['id']])): ?>
                                    <?php foreach ($subcategoriesByParent[$cat['id']] as $sub): ?>
                                        <option value="<?= $sub['id'] ?>">– <?= htmlspecialchars($sub['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="title" placeholder="Title" required
                            class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                        <input type="url" name="url" placeholder="URL" required
                            class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                        <button type="submit"
                            class="px-4 py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                            Add
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Bookmark Modal -->
        <div id="edit-bookmark-modal" class="fixed inset-0 bg-black/70 flex items-center justify-center hidden z-50">
            <div class="glass rounded-2xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-semibold mb-4 accent-yellow">Edit Bookmark</h3>
                <form id="edit-bookmark-form" class="flex flex-col gap-3">
                    <input type="hidden" name="bookmark_id" id="edit-bookmark-id">
                    <select name="category_id" id="edit-category-id" required
                        class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                        <option value="">Select category</option>
                        <?php foreach ($mainCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php if (isset($subcategoriesByParent[$cat['id']])): ?>
                                <?php foreach ($subcategoriesByParent[$cat['id']] as $sub): ?>
                                    <option value="<?= $sub['id'] ?>">– <?= htmlspecialchars($sub['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="title" id="edit-title" placeholder="Title" required
                        class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                    <input type="url" name="url" id="edit-url" placeholder="URL" required
                        class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                    <div class="flex gap-2 mt-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                            Save
                        </button>
                        <button type="button" id="cancel-edit-bookmark"
                            class="flex-1 px-4 py-2 border border-white/20 rounded-lg hover:bg-white/10 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div id="edit-category-modal" class="fixed inset-0 bg-black/70 flex items-center justify-center hidden z-50">
            <div class="glass rounded-2xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-semibold mb-4 accent-yellow">Edit Category</h3>
                <form id="edit-category-form" class="flex flex-col gap-3">
                    <input type="hidden" name="category_id" id="edit-cat-id">
                    <input type="text" name="name" id="edit-cat-name" placeholder="Category name" required
                        class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-yellow-400">
                    <div class="flex gap-2 mt-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2 accent-yellow-bg text-black font-semibold rounded-lg hover:brightness-110 transition">
                            Save
                        </button>
                        <button type="button" id="cancel-edit-category"
                            class="flex-1 px-4 py-2 border border-white/20 rounded-lg hover:bg-white/10 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($mainCategories as $mainCat): ?>
                <div class="glass rounded-2xl p-6" data-category-id="<?= $mainCat['id'] ?>">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold flex items-center gap-2 cursor-pointer"
                                onclick="toggleCategory(<?= $mainCat['id'] ?>)">
                                <span class="toggle-icon transition-transform" id="toggle-icon-<?= $mainCat['id'] ?>">▶</span>
                                <?php if ($mainCat['is_pinned']): ?>
                                    <span class="accent-yellow">📌</span>
                                <?php endif; ?>
                                <?= htmlspecialchars($mainCat['name']) ?>
                            </h2>
                        </div>
                        <div class="edit-controls hidden flex gap-2 ml-2">
                            <button class="edit-category-btn text-blue-400 hover:text-blue-300"
                                data-category-id="<?= $mainCat['id'] ?>"
                                data-name="<?= htmlspecialchars($mainCat['name']) ?>">
                                ✏️
                            </button>
                            <button class="pin-btn text-yellow-400 hover:text-yellow-300" data-category-id="<?= $mainCat['id'] ?>">
                                <?= $mainCat['is_pinned'] ? 'Unpin' : 'Pin' ?>
                            </button>
                            <button class="delete-category-btn text-red-400 hover:text-red-300" data-category-id="<?= $mainCat['id'] ?>">
                                ×
                            </button>
                        </div>
                    </div>

                    <!-- Main category bookmarks -->
                    <div id="category-<?= $mainCat['id'] ?>" class="space-y-2 hidden">
                        <?php if (isset($bookmarksByCategory[$mainCat['id']])): ?>
                            <?php foreach ($bookmarksByCategory[$mainCat['id']] as $bm): ?>
                                <div class="flex items-center justify-between bg-white/5 p-3 rounded-lg">
                                    <a href="<?= htmlspecialchars($bm['url']) ?>" target="_blank"
                                        class="accent-yellow hover:underline truncate flex-1">
                                        <?= htmlspecialchars($bm['title']) ?>
                                    </a>
                                    <div class="edit-controls hidden flex gap-2 ml-2">
                                        <button class="edit-bookmark-btn text-blue-400 hover:text-blue-300"
                                            data-bookmark-id="<?= $bm['id'] ?>"
                                            data-category-id="<?= $bm['category_id'] ?>"
                                            data-title="<?= htmlspecialchars($bm['title']) ?>"
                                            data-url="<?= htmlspecialchars($bm['url']) ?>">
                                            ✏️
                                        </button>
                                        <button class="delete-bookmark-btn text-red-400 hover:text-red-300" data-bookmark-id="<?= $bm['id'] ?>">
                                            ×
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Subcategories -->
                        <?php if (isset($subcategoriesByParent[$mainCat['id']])): ?>
                            <?php foreach ($subcategoriesByParent[$mainCat['id']] as $sub): ?>
                                <div class="mt-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-medium cursor-pointer text-gray-300"
                                            onclick="toggleSubcategory(<?= $sub['id'] ?>)">
                                            <span class="toggle-icon transition-transform inline-block" id="sub-toggle-icon-<?= $sub['id'] ?>">▶</span>
                                            <?= htmlspecialchars($sub['name']) ?>
                                        </h3>
                                        <div class="edit-controls hidden flex gap-2">
                                            <button class="edit-category-btn text-blue-400 hover:text-blue-300"
                                                data-category-id="<?= $sub['id'] ?>"
                                                data-name="<?= htmlspecialchars($sub['name']) ?>">
                                                ✏️
                                            </button>
                                            <button class="delete-category-btn text-red-400 hover:text-red-300" data-category-id="<?= $sub['id'] ?>">
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                    <div id="subcategory-<?= $sub['id'] ?>" class="ml-4 mt-2 space-y-2 hidden">
                                        <?php if (isset($bookmarksByCategory[$sub['id']])): ?>
                                            <?php foreach ($bookmarksByCategory[$sub['id']] as $bm): ?>
                                                <div class="flex items-center justify-between bg-white/5 p-3 rounded-lg">
                                                    <a href="<?= htmlspecialchars($bm['url']) ?>" target="_blank"
                                                        class="accent-yellow hover:underline truncate flex-1">
                                                        <?= htmlspecialchars($bm['title']) ?>
                                                    </a>
                                                    <div class="edit-controls hidden flex gap-2 ml-2">
                                                        <button class="edit-bookmark-btn text-blue-400 hover:text-blue-300"
                                                            data-bookmark-id="<?= $bm['id'] ?>"
                                                            data-category-id="<?= $bm['category_id'] ?>"
                                                            data-title="<?= htmlspecialchars($bm['title']) ?>"
                                                            data-url="<?= htmlspecialchars($bm['url']) ?>">
                                                            ✏️
                                                        </button>
                                                        <button class="delete-bookmark-btn text-red-400 hover:text-red-300" data-bookmark-id="<?= $bm['id'] ?>">
                                                            ×
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
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

function toggleCategory(categoryId) {
    const content = document.getElementById(`category-${categoryId}`);
    const icon = document.getElementById(`toggle-icon-${categoryId}`);
    if (content) {
        content.classList.toggle('hidden');
        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
    }
}

function toggleSubcategory(subcategoryId) {
    const content = document.getElementById(`subcategory-${subcategoryId}`);
    const icon = document.getElementById(`sub-toggle-icon-${subcategoryId}`);
    if (content) {
        content.classList.toggle('hidden');
        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
    }
}

// Open edit bookmark modal
document.querySelectorAll('.edit-bookmark-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit-bookmark-id').value = this.dataset.bookmarkId;
        document.getElementById('edit-category-id').value = this.dataset.categoryId;
        document.getElementById('edit-title').value = this.dataset.title;
        document.getElementById('edit-url').value = this.dataset.url;
        document.getElementById('edit-bookmark-modal').classList.remove('hidden');
    });
});

// Cancel edit bookmark
document.getElementById('cancel-edit-bookmark').addEventListener('click', function() {
    document.getElementById('edit-bookmark-modal').classList.add('hidden');
});

// Close bookmark modal when clicking outside
document.getElementById('edit-bookmark-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// Open edit category modal
document.querySelectorAll('.edit-category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit-cat-id').value = this.dataset.categoryId;
        document.getElementById('edit-cat-name').value = this.dataset.name;
        document.getElementById('edit-category-modal').classList.remove('hidden');
    });
});

// Cancel edit category
document.getElementById('cancel-edit-category').addEventListener('click', function() {
    document.getElementById('edit-category-modal').classList.add('hidden');
});

// Close category modal when clicking outside
document.getElementById('edit-category-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

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

document.getElementById('edit-bookmark-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'update');
    
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

document.getElementById('edit-category-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'update');
    
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