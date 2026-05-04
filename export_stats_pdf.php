<?php
require_once __DIR__ . '/fpdf.php';
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

function pdfText($text) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string) $text);
}

class StatsPDF extends FPDF {
    function Header() {
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(45, 121, 255);
        $this->Cell(0, 10, 'JobyFind - Statistiques avancees', 0, 1, 'L');
        $this->SetDrawColor(229, 231, 235);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb} - JobyFind.tn', 0, 0, 'C');
    }
}

function addSectionTitle($pdf, $title) {
    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetTextColor(17, 24, 39);
    $pdf->Cell(0, 8, pdfText($title), 0, 1, 'L');
}

function addStatsTable($pdf, $title, $rows, $valueKey, $valueLabel) {
    addSectionTitle($pdf, $title);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(249, 250, 251);
    $pdf->Cell(140, 8, pdfText('Post'), 1, 0, 'L', true);
    $pdf->Cell(40, 8, pdfText($valueLabel), 1, 1, 'R', true);
    $pdf->SetFont('Helvetica', '', 9);

    if (empty($rows)) {
        $pdf->Cell(180, 8, pdfText('Aucune donnee.'), 1, 1, 'C');
        return;
    }

    foreach ($rows as $row) {
        $pdf->Cell(140, 8, pdfText(substr($row['title'], 0, 70)), 1, 0, 'L');
        $pdf->Cell(40, 8, (string) (int) $row[$valueKey], 1, 1, 'R');
    }
}

$pdf = new StatsPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 18);

$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(75, 85, 99);
$pdf->Cell(0, 7, pdfText('Rapport genere le ' . date('d/m/Y H:i')), 0, 1, 'L');
$pdf->Ln(3);

addSectionTitle($pdf, 'Resume global');
$pdf->SetFont('Helvetica', '', 10);
$summaryRows = [
    ['Blogs', count($posts)],
    ['Categories', count($categories)],
    ['Commentaires', $commentsTotal],
    ['Vues', $stats['total_views']],
    ['Likes', $stats['total_likes']]
];

foreach ($summaryRows as $row) {
    $pdf->Cell(90, 8, pdfText($row[0]), 1, 0, 'L');
    $pdf->Cell(35, 8, (string) (int) $row[1], 1, 1, 'R');
}

addStatsTable($pdf, 'Nombre de vues par post', $stats['top_viewed'], 'views_count', 'Vues');
addStatsTable($pdf, 'Posts les plus likes', $stats['top_liked'], 'likes_count', 'Likes');
addStatsTable($pdf, 'Formations les plus commentees', $stats['top_commented'], 'comments_count', 'Commentaires');

addSectionTitle($pdf, 'Evolution des commentaires par jour');
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->SetFillColor(249, 250, 251);
$pdf->Cell(90, 8, pdfText('Jour'), 1, 0, 'L', true);
$pdf->Cell(35, 8, pdfText('Commentaires'), 1, 1, 'R', true);
$pdf->SetFont('Helvetica', '', 9);
foreach ($stats['comments_evolution'] as $dayStats) {
    $pdf->Cell(90, 8, pdfText($dayStats['day']), 1, 0, 'L');
    $pdf->Cell(35, 8, (string) (int) $dayStats['comments_count'], 1, 1, 'R');
}

$pdf->Output('I', 'statistiques_jobyfind.pdf');
exit;
?>
