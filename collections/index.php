<?php
// collections/index.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Agent';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Collection name is required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO collections (user_id, name, description) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $name, $description])) {
            $success = 'Collection directory created successfully!';
        } else {
            $error = 'Failed to create collection. Please try again.';
        }
    }
}

// Fetch user's collections with item count
$stmt = $pdo->prepare("
    SELECT collections.*, COUNT(collection_items.item_id) AS item_count 
    FROM collections 
    LEFT JOIN collection_items ON collections.id = collection_items.collection_id 
    WHERE collections.user_id = ? 
    GROUP BY collections.id 
    ORDER BY collections.created_at DESC
");
$stmt->execute([$user_id]);
$collections = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CUSTOM COLLECTIONS // KEEPSAKE V2.0</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
</head>
<body>
    <header>
        <div class="logo-brand">
            <h1>💎 KEEPSAKE</h1>
            <span class="version-tag">V2.0</span>
        </div>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="index.php" class="active">Collections</a>
            <a href="../login.php">Auth Mode</a>
            <a href="../items/create.php" class="btn-add">+ Add Keepsake</a>
            <a href="../logout.php">Log Out</a>
        </nav>
    </header>

    <main>
        <!-- Collections Image Hero Banner -->
        <div class="collections-hero">
            <span style="font-size:0.65rem; background:var(--neon-purple); color:#fff; padding:2px 8px; border-radius:3px; font-family:'Orbitron', sans-serif;">DIRECTORY SYSTEM</span>
            <h2>📂 Custom Collections</h2>
            <p>Organize your saved bookmarks, media archives, and personal links into structured folders.</p>
        </div>

        <!-- Create Directory Form -->
        <div class="widget-box" style="margin-bottom: 2rem; border-color: var(--border-purple);">
            <h3 style="font-family:'Orbitron', sans-serif; color: var(--neon-cyan); margin-top:0;">+ NEW FOLDER DIRECTORY</h3>
            
            <?php if ($error): ?><p style="color: var(--neon-pink); font-size: 0.85rem;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <?php if ($success): ?><p style="color: var(--neon-cyan); font-size: 0.85rem;"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

            <form action="index.php" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="name" placeholder="Collection Name (e.g. Bicol Spots & Travel)" required style="flex: 1; min-width: 200px; background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.6rem 1rem; border-radius: 4px; font-family: inherit;">
                <input type="text" name="description" placeholder="Description / Notes" style="flex: 2; min-width: 250px; background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.6rem 1rem; border-radius: 4px; font-family: inherit;">
                <button type="submit" class="btn-nav" style="background: var(--neon-purple); border-color: var(--neon-purple); color: #fff;">Create Folder</button>
            </form>
        </div>

        <!-- Active Collections Grid -->
        <h3 style="font-family:'Orbitron', sans-serif; color: var(--neon-pink); margin-bottom: 1rem;">ACTIVE DIRECTORIES</h3>
        
        <?php if (empty($collections)): ?>
            <div class="widget-box" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-muted);">No custom collections found. Create your first directory above!</p>
            </div>
        <?php else: ?>
            <div class="keepsake-grid">
                <?php foreach ($collections as $col): ?>
                    <div class="keepsake-card" style="border-color: var(--border-purple);">
                        <div class="card-header">
                            <span class="badge-category" style="border-color: var(--neon-purple); color: var(--neon-purple); background: rgba(157,0,255,0.1);">
                                <?php echo $col['item_count']; ?> ITEMS
                            </span>
                        </div>
                        <h3 class="card-title" style="color: #fff;">📁 <?php echo htmlspecialchars($col['name']); ?></h3>
                        <p class="card-desc"><?php echo htmlspecialchars($col['description']); ?></p>
                        
                        <div class="card-footer" style="margin-top: 1.5rem;">
                            <a href="view.php?id=<?php echo $col['id']; ?>" class="btn-open-link" style="border-color: var(--neon-purple); color: var(--neon-purple); background: rgba(157,0,255,0.1); width: 100%; text-align: center;">View Collection Items ↗</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

   <footer class="site-footer">
    KEEPSAKE ARCHIVE SYSTEM // DESIGNED FOR <span><?php echo isset($user_name) && !empty($user_name) ? strtoupper(htmlspecialchars($user_name)) : 'YOU'; ?></span> // ALL SYSTEMS NOMINAL
</footer>
</body>
</html>