<?php
// dashboard.php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Agent';
$search = trim($_GET['search'] ?? '');
$category_filter = filter_var($_GET['category_id'] ?? 0, FILTER_VALIDATE_INT);
$favorites_only = ($_GET['favorites'] ?? '') === '1'; 

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$query = "
     SELECT items.*, categories.name AS category_name,
           (SELECT COUNT(*) FROM item_entries WHERE item_entries.item_id = items.id) AS entry_count
    FROM items 
    JOIN categories ON items.category_id = categories.id 
    WHERE items.user_id = ?
";
$params = [$user_id];

if ($category_filter) {
    $query .= " AND items.category_id = ?";
    $params[] = $category_filter;
}

if ($favorites_only) {
    $query .= " AND items.is_favorite = 1";
}

if (!empty($search)) {
    $query .= " AND (items.title LIKE ? OR items.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY items.is_favorite DESC, items.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

$favCount = count(array_filter($items, function($i) { return $i['is_favorite']; }));
$itemCount = count($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KEEPSAKE V2.0 // DASHBOARD</title>
    <link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>
<body>
    <header>
        <div class="logo-brand">
            <h1>💎 KEEPSAKE</h1>
            <span class="version-tag">V2.0</span>
        </div>
        <nav>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="collections/index.php">Collections</a>
            <a href="login.php">Auth Mode</a>
            <a href="items/create.php" class="btn-add">+ Add Keepsake</a>
            <a href="logout.php">Log Out</a>
        </nav>
    </header>

    <main>
        <!-- 3-Column Top Widgets Row -->
        <div class="top-widgets">
            <!-- Clock Box -->
            <div class="widget-box">
                <div style="font-size:0.7rem; color:var(--text-muted); display:flex; justify-content:space-between;">
                    <span>TIME // SYSTEM CLOCK</span>
                    <span>🌐</span>
                </div>
                <div class="clock-display"><?php echo date('H:i'); ?></div>
                <div style="color:var(--neon-pink); font-size:0.85rem; margin-top:0.3rem;"><?php echo date('D, m/d'); ?></div>
                <div style="font-size:0.65rem; color:var(--text-muted); margin-top:0.6rem;">SYS.UPTIME: ONLINE // USER: <?php echo strtoupper(htmlspecialchars($user_name)); ?></div>
            </div>

            <!-- Featured Banner Box -->
            <div class="featured-widget">
                <span style="font-size:0.6rem; background:var(--neon-pink); color:#fff; padding:2px 6px; border-radius:3px; font-weight:bold; width:fit-content;">FEATURED KEEPSAKE</span>
                <h2>Cyberpunk Soundtracks & Visual Archives</h2>
                <p style="font-size:0.75rem; color:var(--text-muted); margin:0;">Curated collection of lo-fi wave, synthwave beats, anime wallpaper references, and developer bookmarks.</p>
            </div>

            <!-- 4 Quick Actions Box -->
            <div class="widget-box" style="border-color:var(--border-pink);">
                <div class="quick-grid">
                   <a href="dashboard.php?favorites=1" class="quick-card">
                        <span class="icon">❤️</span>
                        <span>Favorites (<?php echo $favCount; ?>)</span>
                    </a>
                    <a href="collections/index.php" class="quick-card">
                        <span class="icon">📂</span>
                        <span>Collections</span>
                    </a>
                    <a href="items/create.php" class="quick-card">
                        <span class="icon">➕</span>
                        <span>New Item</span>
                    </a>
                  <a href="items/export.php" class="quick-card">
                        <span class="icon">⬇️</span>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Bar Toolbar -->
        <form method="GET" action="dashboard.php" class="search-toolbar">
              <?php if ($favorites_only): ?>
                    <input type="hidden" name="favorites" value="1">
                <?php endif; ?>
            <div class="search-input-group">
                <span style="color:var(--neon-cyan);">🔍</span>
               <input type="text" name="search" placeholder="Search title or description..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="search-actions">
                <select name="category_id" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-nav">Filter</button>
                 <?php if (!empty($search) || $category_filter || $favorites_only): ?>
                    <a href="dashboard.php" class="btn-nav" style="border-color:var(--neon-pink); color:var(--neon-pink);">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Saved Keepsakes Title Bar -->
        <div class="section-title">
                <span>🔖 Saved Keepsakes</span>
                <span style="font-size:0.75rem; border:1px solid var(--neon-cyan); color:var(--neon-cyan); padding:2px 8px; border-radius:12px;">
                    <?php echo $itemCount; ?> <?php echo $itemCount === 1 ? 'Item' : 'Items'; ?> Found
                </span>
            </div>

        <!-- Saved Keepsakes Grid Cards -->
        <?php if (empty($items)): ?>
            <div class="widget-box" style="text-align:center; padding:3rem; border-color:var(--border-pink);">
                <p style="color:var(--text-muted);">No records found in database. Click <strong>+ Add Keepsake</strong> to save your first bookmark!</p>
            </div>
        <?php else: ?>
            <div class="keepsake-grid">
                <?php foreach ($items as $item): ?>
                    <div class="keepsake-card">
                        <div class="card-header">
                            <span class="badge-category"><?php echo strtoupper(htmlspecialchars($item['category_name'])); ?></span>
                            <a href="items/favorite.php?id=<?php echo $item['id']; ?>" class="fav-btn">
                                <?php echo $item['is_favorite'] ? '❤️' : '🖤'; ?>
                            </a>
                        </div>
                        
                        <h3 class="card-title">
                            <a href="items/view.php?id=<?php echo (int)$item['id']; ?>" class="card-title-link"><?php echo htmlspecialchars($item['title']); ?></a>
                        </h3>
                        <p class="card-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                        <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.6rem 0 0;">
                                📋 <?php echo (int)$item['entry_count']; ?> <?php echo (int)$item['entry_count'] === 1 ? 'entry' : 'entries'; ?>
                            </p>
                            <div class="card-footer">
                            <span style="color:var(--text-muted);">🕒 <?php echo date('M d', strtotime($item['created_at'])); ?></span>
                            <div>
                                <a href="items/delete.php?id=<?php echo $item['id']; ?>" onclick="return confirm('Delete this keepsake?');" style="color:var(--text-muted); text-decoration:none; margin-right:8px;">Delete</a>
                                <?php if (!empty($item['external_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($item['external_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn-open-link">Open Link ↗</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer Section -->
    <footer class="site-footer">
        KEEPSAKE ARCHIVE SYSTEM // DESIGNED FOR <span><?php echo strtoupper(htmlspecialchars($user_name)); ?></span> // ALL SYSTEMS NOMINAL
    </footer>
</body>
</html>
</body>
</html>