<?php
// items/create.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/validate.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'YOU';
$error = '';
$success = '';

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$colStmt = $pdo->prepare("SELECT * FROM collections WHERE user_id = ? ORDER BY name ASC");
$colStmt->execute([$user_id]);
$collections = $colStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title         = trim($_POST['title'] ?? '');
    $category_id   = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    $collection_id = filter_var($_POST['collection_id'] ?? 0, FILTER_VALIDATE_INT);
    $description   = trim($_POST['description'] ?? '');
    $external_url  = clean_url($_POST['external_url'] ?? '');
    $visibility    = clean_visibility($_POST['visibility'] ?? 'private');

    $error = validate_item($title, $description, $external_url);
    if (!$error && !$category_id) {
        $error = 'Please select a category.';
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO items (user_id, category_id, title, description, external_url, visibility)
                VALUES (?, ?, ?, ?, ?, ?)
                RETURNING id
            ");
            $stmt->execute([$user_id, $category_id, $title, $description, $external_url ?: null, $visibility]);
            $item_id = $stmt->fetchColumn();

            if ($collection_id) {
                $check = $pdo->prepare("SELECT id FROM collections WHERE id = ? AND user_id = ?");
                $check->execute([$collection_id, $user_id]);
                if ($check->fetch()) {
                    $link = $pdo->prepare("INSERT INTO collection_items (collection_id, item_id) VALUES (?, ?)");
                    $link->execute([$collection_id, $item_id]);
                }
            }

            $pdo->commit();
            $success = 'Keepsake saved successfully into database!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('create item failed: ' . $e->getMessage());
            $error = 'Could not save the record. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ADD KEEPSAKE // V2.0</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=4.2">
</head>
<body>
    <header class="header-add-record">
        <div class="logo-brand">
            <h1>💎 KEEPSAKE</h1>
            <span class="version-tag">V2.0</span>
        </div>
        <nav>
            <a href="../dashboard.php">⚙️ Dashboard</a>
            <a href="../collections/index.php">📂 Collections</a>
            <a href="../login.php">👤 Auth Mode</a>
            <a href="create.php" class="active btn-add">+ Add Keepsake</a>
            <a href="../logout.php">Log Out</a>
        </nav>
    </header>

    <!-- Main Container Set to Flex Center -->
    <main style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; min-height: 80vh;">
        
        <!-- Centered Hero Header -->
        <div class="collections-hero" style="border-color: var(--neon-cyan); margin-bottom: 1.5rem; width: 100%; max-width: 750px; text-align: left;">
            <span style="font-size:0.65rem; background:var(--neon-cyan); color:#000; padding:2px 8px; border-radius:3px; font-family:'Orbitron', sans-serif; font-weight:bold;">ARCHIVE PROTOCOL</span>
            <h2 style="color:var(--neon-cyan); text-shadow: 0 0 10px var(--neon-cyan); margin: 0.5rem 0 0.3rem 0;">➕ Add New Keepsake</h2>
            <p style="color: var(--text-main); margin:0; font-size: 0.85rem;">Record a new bookmark, media link, or developer reference into your secure database.</p>
        </div>

        <!-- Centered Wide Widget Container Box -->
        <div class="widget-box" style="border-color: var(--border-purple); padding: 2rem; margin-bottom: 2rem; width: 100%; max-width: 750px;">
            <div style="border-bottom: 1px solid var(--border-purple); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-family:'Orbitron', sans-serif; color: var(--neon-cyan); margin: 0; font-size: 1.1rem; text-shadow: 0 0 8px var(--neon-cyan);">
                    + NEW BOOKMARK ENTRY
                </h3>
            </div>

            <?php if ($error): ?>
                <p style="color: var(--neon-pink); font-size: 0.85rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p style="color: var(--neon-cyan); font-size: 0.85rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($success); ?> <a href="../dashboard.php" style="color:var(--neon-pink)">View Dashboard ↗</a></p>
            <?php endif; ?>

            <form action="create.php" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem;">
                    <div class="form-group" style="margin: 0;">
                        <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">CATEGORY:</label>
                        <select name="category_id" required style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px; width: 100%;">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">CUSTOM COLLECTION (OPTIONAL):</label>
                        <select name="collection_id" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px; width: 100%;">
                            <option value="">None</option>
                            <?php foreach ($collections as $col): ?>
                                <option value="<?php echo $col['id']; ?>"><?php echo htmlspecialchars($col['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">RECORD TITLE / NAME:</label>
                    <input type="text" name="title" required placeholder="e.g. Cyberpunk Synth Playlist" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px; width: 100%;">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">EXTERNAL URL (Spotify, YouTube, Maps):</label>
                    <input type="url" name="external_url" placeholder="https://..." style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px; width: 100%;">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">NOTES / DESCRIPTION:</label>
                    <textarea name="description" placeholder="Why do you want to save this bookmark?" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px; height: 100px; width: 100%;"></textarea>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">VISIBILITY:</label>
                    <select name="visibility" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px; width: 100%;">
                        <option value="private">Private</option>
                        <option value="public">Public</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="submit" class="btn-nav" style="background: var(--neon-cyan); border-color: var(--neon-cyan); color: #000; padding: 0.75rem 2rem; cursor: pointer;">
                        SAVE TO DATABASE ↗
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        KEEPSAKE ARCHIVE SYSTEM // DESIGNED FOR <span><?php echo strtoupper(htmlspecialchars($user_name)); ?></span> // ALL SYSTEMS NOMINAL
    </footer>
</body>
</html>