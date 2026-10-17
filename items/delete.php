<?php
// items/delete.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$item_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($item_id) {
    // Delete query enforcing ownership check so users can only delete their own records[cite: 3]
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$item_id, $user_id]);
}

header('Location: ../dashboard.php');
exit;