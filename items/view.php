<?php
// items/view.php  -  one keepsake with its related entries
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/validate.php';
requireLogin();

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'YOU';
$item_id   = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$item_id) {
    header('Location: ../dashboard.php');
    exit;
}

// Load the keepsake, only if it belongs to this user
$stmt = $pdo->prepare("
    SELECT items.*, categories.name AS category_name
    FROM items
    JOIN categories ON items.category_id = categories.id
    WHERE items.id = ? AND items.user_id = ?
");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: ../dashboard.php');
    exit;
}

// What to call the entries, based on the category
$nouns = [
    'Music & Playlists' => 'song',
    'Favorite Places'   => 'place',
    'Movies & Series'   => 'movie or episode',
    'Food & Cafés'      => 'dish or café',
    'Books & Learning'  => 'book or lesson',
];
$noun = $nouns[$item['category_name']] ?? 'entry';

$messages = ['added' => 'Entry added.', 'updated' => 'Entry updated.', 'deleted' => 'Entry deleted.'];
$success  = $messages[$_GET['msg'] ?? ''] ?? '';
$error    = '';
$edit_id  = filter_var($_GET['edit'] ?? 0, FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action   = $_POST['action'] ?? '';
    $entry_id = filter_var($_POST['entry_id'] ?? 0, FILTER_VALIDATE_INT);

    if ($action === 'delete' && $entry_id) {
        $del = $pdo->prepare("DELETE FROM item_entries WHERE id = ? AND item_id = ?");
        $del->execute([$entry_id, $item_id]);
        header("Location: view.php?id=$item_id&msg=deleted");
        exit;
    }

    if ($action === 'add' || $action === 'update') {
        $title = trim($_POST['title'] ?? '');
        $note  = trim($_POST['note'] ?? '');
        $url   = clean_url($_POST['external_url'] ?? '');

        $error = validate_item($title, $note, $url);

        if (!$error) {
            try {
                if ($action === 'add') {
                    $ins = $pdo->prepare("INSERT INTO item_entries (item_id, title, note, external_url) VALUES (?, ?, ?, ?)");
                    $ins->execute([$item_id, $title, $note, $url ?: null]);
                    header("Location: view.php?id=$item_id&msg=added");
                    exit;
                } elseif ($entry_id) {
                    $upd = $pdo->prepare("
                        UPDATE item_entries
                        SET title = ?, note = ?, external_url = ?, updated_at = NOW()
                        WHERE id = ? AND item_id = ?
                    ");
                    $upd->execute([$title, $note, $url ?: null, $entry_id, $item_id]);
                    header("Location: view.php?id=$item_id&msg=updated");
                    exit;
                }
            } catch (PDOException $e) {
                    error_log('entry save failed: ' . $e->getMessage());
                    $error = 'Could not save the entry. Please try again.';
                }
                        }

        if ($error && $action === 'update') {
            $edit_id = $entry_id; // keep the edit form open
        }
    }
}

// Refill the add form if it failed validation
$form = (($_POST['action'] ?? '') === 'add') ? $_POST : [];

$entStmt = $pdo->prepare("SELECT * FROM item_entries WHERE item_id = ? ORDER BY created_at ASC, id ASC");
$entStmt->execute([$item_id]);
$entries = $entStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($item['title']); ?> // KEEPSAKE V2.0</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=5.1">
</head>
<body>
    <header>
        <div class="logo-brand">
            <h1>💎 KEEPSAKE</h1>
            <span class="version-tag">V2.0</span>
        </div>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../collections/index.php">Collections</a>
            <a href="create.php" class="btn-add">+ Add Keepsake</a>
            <a href="../logout.php">Log Out</a>
        </nav>
    </header>

    <main>
        <a href="../dashboard.php" class="back-link">← Back to dashboard</a>

        <!-- Keepsake summary -->
        <div class="collections-hero">
            <span class="badge-category"><?php echo strtoupper(htmlspecialchars($item['category_name'])); ?></span>
            <h2 style="color: var(--neon-cyan);"><?php echo htmlspecialchars($item['title']); ?></h2>
            <?php if (!empty($item['description'])): ?>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
            <?php endif; ?>
            <p style="margin-top: 0.8rem;">
                <a href="edit.php?id=<?php echo (int)$item['id']; ?>" class="btn-open-link">Edit keepsake</a>
                <?php if (!empty($item['external_url'])): ?>
                    <a href="<?php echo htmlspecialchars($item['external_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn-open-link">Open link ↗</a>
                <?php endif; ?>
            </p>
        </div>

        <?php if ($error): ?><p style="color: var(--neon-pink);"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color: var(--neon-cyan);"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

        <!-- Add entry -->
        <div class="widget-box" style="margin-bottom: 1.5rem; border-color: var(--border-purple);">
            <h3 style="font-family:'Orbitron', sans-serif; color: var(--neon-cyan); margin-top: 0;">
                + ADD A <?php echo strtoupper(htmlspecialchars($noun)); ?>
            </h3>
            <form action="view.php?id=<?php echo (int)$item_id; ?>" method="POST" class="entry-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add">
                <input type="text" name="title" required maxlength="255"
                       placeholder="Name of the <?php echo htmlspecialchars($noun); ?>"
                       value="<?php echo htmlspecialchars($form['title'] ?? ''); ?>">
                <input type="url" name="external_url" placeholder="Link (optional)"
                       value="<?php echo htmlspecialchars($form['external_url'] ?? ''); ?>">
                <input type="text" name="note" placeholder="Note (optional)"
                       value="<?php echo htmlspecialchars($form['note'] ?? ''); ?>">
                <button type="submit" class="btn-nav">Add</button>
            </form>
        </div>

        <!-- Entries -->
        <div class="section-title">
            <span>📋 Inside this keepsake</span>
            <span style="font-size:0.75rem; border:1px solid var(--neon-cyan); color:var(--neon-cyan); padding:2px 8px; border-radius:12px;">
                <?php echo count($entries); ?> <?php echo count($entries) === 1 ? 'Entry' : 'Entries'; ?>
            </span>
        </div>

        <?php if (empty($entries)): ?>
            <div class="widget-box" style="text-align:center; padding:2.5rem;">
                <p style="color: var(--text-muted); margin: 0;">
                    Nothing here yet. Add your first <?php echo htmlspecialchars($noun); ?> above!
                </p>
            </div>
        <?php else: ?>
            <ul class="entry-list">
                <?php foreach ($entries as $i => $e): ?>
                    <li class="entry-row">
                        <?php if ($edit_id === (int)$e['id']): ?>
                            <!-- Edit mode -->
                            <form action="view.php?id=<?php echo (int)$item_id; ?>" method="POST" class="entry-form" style="width:100%;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="entry_id" value="<?php echo (int)$e['id']; ?>">
                                <input type="text" name="title" required maxlength="255" value="<?php echo htmlspecialchars($e['title']); ?>">
                                <input type="url" name="external_url" placeholder="Link (optional)" value="<?php echo htmlspecialchars($e['external_url'] ?? ''); ?>">
                                <input type="text" name="note" placeholder="Note (optional)" value="<?php echo htmlspecialchars($e['note'] ?? ''); ?>">
                                <span class="entry-actions">
                                    <button type="submit" class="btn-nav">Save</button>
                                    <a href="view.php?id=<?php echo (int)$item_id; ?>">Cancel</a>
                                </span>
                            </form>
                        <?php else: ?>
                            <!-- Normal view -->
                            <div class="entry-main">
                                <div class="entry-title">
                                    <span style="color: var(--text-muted);"><?php echo $i + 1; ?>.</span>
                                    <?php if (!empty($e['external_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($e['external_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($e['title']); ?> ↗</a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($e['title']); ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($e['note'])): ?>
                                    <p class="entry-note"><?php echo htmlspecialchars($e['note']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="entry-actions">
                                <a href="view.php?id=<?php echo (int)$item_id; ?>&edit=<?php echo (int)$e['id']; ?>">Edit</a>
                                <form action="view.php?id=<?php echo (int)$item_id; ?>" method="POST" class="inline-form"
                                      onsubmit="return confirm('Delete this entry?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="entry_id" value="<?php echo (int)$e['id']; ?>">
                                    <button type="submit" class="link-btn">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        KEEPSAKE ARCHIVE SYSTEM // DESIGNED FOR <span><?php echo strtoupper(htmlspecialchars($user_name)); ?></span> // ALL SYSTEMS NOMINAL
    </footer>
</body>
</html>