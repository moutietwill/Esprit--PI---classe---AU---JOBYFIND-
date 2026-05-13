<?php
require_once __DIR__ . '/controller/BlogController.php';
require_once __DIR__ . '/controller/CategoryController.php';
require_once __DIR__ . '/controller/CommentController.php';

$blogController = new BlogController();
$catController = new CategoryController();
$commentController = new CommentController();

$posts = $blogController->AfficherPosts();
$categories = $catController->getCategories();
$commentsTotal = $commentController->countComments();
$stats = $blogController->GetAdvancedStats(7, 10);

$filename = 'statistiques_jobyfind_' . date('Y-m-d_H-i') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
fwrite($output, "sep=;\r\n");

function csvRow($output, $row) {
    fputcsv($output, $row, ';');
}

csvRow($output, ['JobyFind - Statistiques avancees']);
csvRow($output, ['Rapport genere le', date('d/m/Y H:i')]);
csvRow($output, []);

csvRow($output, ['Resume global']);
csvRow($output, ['Blogs', count($posts)]);
csvRow($output, ['Categories', count($categories)]);
csvRow($output, ['Commentaires', $commentsTotal]);
csvRow($output, ['Vues', $stats['total_views']]);
csvRow($output, ['Likes', $stats['total_likes']]);
csvRow($output, []);

csvRow($output, ['Nombre de vues par post']);
csvRow($output, ['Post', 'Vues']);
foreach ($stats['top_viewed'] as $row) {
    csvRow($output, [$row['title'], (int) $row['views_count']]);
}
csvRow($output, []);

csvRow($output, ['Posts les plus likes']);
csvRow($output, ['Post', 'Likes']);
foreach ($stats['top_liked'] as $row) {
    csvRow($output, [$row['title'], (int) $row['likes_count']]);
}
csvRow($output, []);

csvRow($output, ['Formations les plus commentees']);
csvRow($output, ['Post', 'Commentaires']);
foreach ($stats['top_commented'] as $row) {
    csvRow($output, [$row['title'], (int) $row['comments_count']]);
}
csvRow($output, []);

csvRow($output, ['Evolution des commentaires par jour']);
csvRow($output, ['Jour', 'Commentaires']);
foreach ($stats['comments_evolution'] as $row) {
    csvRow($output, [$row['day'], (int) $row['comments_count']]);
}

fclose($output);
exit;
?>
