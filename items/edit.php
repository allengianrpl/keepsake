<?php
// items/edit.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/validate.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$item_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
$error = '';
$success = '';

if (!$item_id) {
    header('Location: ../dashboard.php');
    exit;
}

// Fetch the item ensuring it belongs to the logged-in user (Ownership Check)
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: ../dashboard.php');
    exit;
}

// Fetch categories for dropdown
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title        = trim($_POST['title'] ?? '');
    $category_id  = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    $description  = trim($_POST['description'] ?? '');
    $external_url = clean_url($_POST['external_url'] ?? '');
    $visibility   = clean_visibility($_POST['visibility'] ?? 'private');

    $error = validate_item($title, $description, $external_url);
    if (!$error && !$category_id) {
        $error = 'Please select a category.';
    }

    if (!$error) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE items
                SET category_id = ?, title = ?, description = ?, external_url = ?,
                    visibility = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $updateStmt->execute([$category_id, $title, $description, $external_url ?: null, $visibility, $item_id, $user_id]);
            $success = 'Item updated successfully!';

            $stmt->execute([$item_id, $user_id]);
            $item = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('update item failed: ' . $e->getMessage());
            $error = 'Could not update the item. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item - Keepsake</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Edit Record</h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../logout.php">Log Out</a>
        </nav>
    </header>
    <main>
        <?php if ($error): ?><p style="color: red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color: green;"><?php echo htmlspecialchars($success); ?> <a href="../dashboard.php">Return to Dashboard</a></p><?php endif; ?>
        
        <form action="edit.php?id=<?php echo $item['id']; ?>" method="POST">
             <?php echo csrf_field(); ?>
            <div>
                <label>Category:</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $item['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Title:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
            </div>
            <div>
                <label>External URL:</label>
                <input type="url" name="external_url" value="<?php echo htmlspecialchars($item['external_url']); ?>">
            </div>
            <div>
                <label>Description / Personal Notes:</label>
                <textarea name="description"><?php echo htmlspecialchars($item['description']); ?></textarea>
            </div>
            <div>
                <label>Visibility:</label>
                <select name="visibility">
                    <option value="private" <?php echo ($item['visibility'] == 'private') ? 'selected' : ''; ?>>Private</option>
                    <option value="public" <?php echo ($item['visibility'] == 'public') ? 'selected' : ''; ?>>Public</option>
                </select>
            </div>
            <button type="submit">Update Item</button>
        </form>
    </main>
</body>
</html>