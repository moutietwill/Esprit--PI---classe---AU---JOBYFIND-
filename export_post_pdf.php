<?php
require_once __DIR__ . '/fpdf.php';
require_once __DIR__ . '/controller/BlogController.php';

// Vérifier l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de publication manquant.");
}

$id = (int)$_GET['id'];
$blogController = new BlogController();
$post = $blogController->RecupererPost($id);

if (!$post) {
    die("Publication introuvable.");
}

class PostPDF extends FPDF {
    function Header() {
        $this->SetFont('Helvetica', 'B', 20); // Utilisation de Helvetica (définie manuellement)
        $this->SetTextColor(25, 33, 53);
        $this->Cell(0, 10, 'JOBYFIND - FICHE PUBLICATION', 0, 1, 'C');
        $this->Ln(5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb} - JobyFind.tn - Rapport genere le ' . date('d/m/Y'), 0, 0, 'C');
    }
}

$pdf = new PostPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// --- TITRE ---
$pdf->SetFont('Helvetica', 'B', 22);
$pdf->SetTextColor(17, 24, 39);
$title = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $post['title']);
$pdf->MultiCell(0, 12, strtoupper($title), 0, 'L');
$pdf->Ln(5);

// --- INFORMATIONS PRINCIPALES (Grille) ---
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetFillColor(243, 244, 246); // Gris clair
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'DÉTAILS DE LA PUBLICATION'), 1, 1, 'L', true);

$pdf->SetFont('Helvetica', '', 11);
$pdf->Cell(95, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' Catégorie : ' . ($post['category'] ?: 'N/A')), 1, 0);
$pdf->Cell(95, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' Instructeur : ' . ($post['instructor'] ?: 'N/A')), 1, 1);

$pdf->Cell(95, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' Prix : ' . ($post['price'] > 0 ? $post['price'] . ' TND' : 'Gratuit')), 1, 0);
$pdf->Cell(95, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' Statut : ' . strtoupper($post['status'])), 1, 1);

$pdf->Cell(95, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' Évaluation : ' . ($post['rating'] ?: '0') . '/5'), 1, 0);
$pdf->Cell(95, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' Étudiants : ' . ($post['students_count'] ?: '0')), 1, 1);
$pdf->Ln(10);

// --- IMAGE ---
if ($post['cover_image']) {
    $imagePath = __DIR__ . '/uploads/' . $post['cover_image'];
    if (file_exists($imagePath)) {
        $pdf->Image($imagePath, 10, $pdf->GetY(), 190);
        $pdf->Ln(110); // Espace après l'image
    }
}

// --- DESCRIPTION / CONTENU ---
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CONTENU / DESCRIPTION :'), 0, 1);
$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(55, 65, 81);

$content = strip_tags($post['content']);
$content = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $content);
$pdf->MultiCell(0, 7, $content, 0, 'J');

// --- PIED DE PAGE FINAL ---
$pdf->Ln(20);
$pdf->SetFont('Helvetica', 'I', 10);
$pdf->SetTextColor(107, 114, 128);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fin du document - JobyFind.tn'), 0, 1, 'C');

// Sortie
$filename = 'Publication_' . $id . '.pdf';
$pdf->Output('D', $filename);
exit;
