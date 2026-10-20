<?php
// items/export.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

// Stops spreadsheet apps from running cells that start with = + - @
function csv_safe($value) {
    $value = (string)$value;
    return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
}

$stmt = $pdo->prepare("
    SELECT items.title, categories.name AS category, items.description,
           items.external_url, items.is_favorite, items.visibility, items.created_at
    FROM items
    JOIN categories ON items.category_id = categories.id
    WHERE items.user_id = ?
    ORDER BY items.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="keepsake-export-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows emojis and accents correctly
fputcsv($out, ['Title', 'Category', 'Notes', 'Link', 'Favorite', 'Visibility', 'Saved on']);

while ($row = $stmt->fetch()) {
    fputcsv($out, [
        csv_safe($row['title']),
        csv_safe($row['category']),
        csv_safe($row['description']),
        csv_safe($row['external_url']),
        $row['is_favorite'] ? 'Yes' : 'No',
        $row['visibility'],
        $row['created_at'],
    ]);
}

fclose($out);
exit;