<?php
// collections/view.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$collection_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$collection_id) {
    header('Location: index.php');
    exit;
}

$colStmt = $pdo->prepare("SELECT * FROM collections WHERE id = ? AND user_id = ?");
$colStmt->execute([$collection_id, $user_id]);
$collection = $colStmt->fetch();

if (!$collection) {
    header('Location: index.php');
    exit;
}

$itemStmt = $pdo->prepare("
    SELECT items.*, categories.name AS category_name 
    FROM items 
    JOIN collection_items ON items.id = collection_items.item_id 
    JOIN categories ON items.category_id = categories.id 
    WHERE collection_items.collection_id = ? AND items.user_id = ?
    ORDER BY items.created_at DESC
");
$itemStmt->execute([$collection_id, $user_id]);
$items = $itemStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($collection['name']); ?> - Keepsake</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>FOLDER // <?php echo strtoupper(htmlspecialchars($collection['name'])); ?></h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="index.php">Collections</a>
            <a href="../logout.php">Log Out</a>
        </nav>
    </header>
    <main>
        <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 2rem;"><?php echo htmlspecialchars($collection['description']); ?></p>
        
        <h2 style="color: var(--border-cyan); text-shadow: 0 0 6px var(--border-cyan);">ASSIGNED RECORDS</h2>
        <?php if (empty($items)): ?>
            <p style="color: var(--text-muted);">No items found in this collection.</p>
        <?php else: ?>
            <div class="grid-cards" style="margin-top: 1.5rem;">
                <?php foreach ($items as $item): ?>
                    <div class="cyber-card">
                        <span class="badge"><?php echo htmlspecialchars($item['category_name']); ?></span>
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="card-actions">
                            <?php if (!empty($item['external_url'])): ?>
                                <a href="<?php echo htmlspecialchars($item['external_url']); ?>" target="_blank" rel="noopener noreferrer">↗ Open Link</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>