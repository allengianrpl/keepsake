<?php
// items/favorite.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$item_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($item_id) {
    // First, verify ownership and check current favorite status
    $stmt = $pdo->prepare("SELECT is_favorite FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$item_id, $user_id]);
    $item = $stmt->fetch();

    if ($item) {
        $new_status = $item['is_favorite'] ? 0 : 1;
        // Update favorite status securely using prepared statements[cite: 3]
        $updateStmt = $pdo->prepare("UPDATE items SET is_favorite = ? WHERE id = ? AND user_id = ?");
        $updateStmt->execute([$new_status, $item_id, $user_id]);
    }
}

header('Location: ../dashboard.php');
exit;