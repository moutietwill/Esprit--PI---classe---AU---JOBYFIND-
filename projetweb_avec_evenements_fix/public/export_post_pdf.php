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
    // Header minimaliste
    function Header() {
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(66, 103, 178); // Bleu Facebook
        $this->Cell(0, 10, 'JobyFind', 0, 1, 'L');
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    // Footer minimaliste
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb} - JobyFind.tn', 0, 0, 'C');
    }
}

$pdf = new PostPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// --- EN-TÊTE DE LA PUBLICATION (Auteur & Date) ---
$author = $post['instructor'] ? $post['instructor'] : 'Auteur Inconnu';
$datePost = $post['created_at'] ? date('d/m/Y H:i', strtotime($post['created_at'])) : date('d/m/Y H:i');

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor(5, 5, 5); // Noir FB
$pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $author), 0, 1, 'L');

$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor(101, 103, 107); // Gris FB
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $datePost . ' · JobyFind'), 0, 1, 'L');
$pdf->Ln(4);

// --- TITRE DE LA PUBLICATION ---
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetTextColor(5, 5, 5);
$title = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $post['title']);
$pdf->MultiCell(0, 7, $title, 0, 'L');
$pdf->Ln(2);

// --- CONTENU / STATUT ---
$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(5, 5, 5);
$content = strip_tags($post['content']);
$content = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $content);
$pdf->MultiCell(0, 6, $content, 0, 'L');
$pdf->Ln(5);

// --- IMAGE DE LA PUBLICATION ---
if ($post['cover_image']) {
    $imagePath = __DIR__ . '/uploads/' . $post['cover_image'];
    if (file_exists($imagePath)) {
        $imgInfo = @getimagesize($imagePath);
        if ($imgInfo) {
            $w = $imgInfo[0];
            $h = $imgInfo[1];
            $ratio = $h / $w;
            $newW = 190;
            $newH = $newW * $ratio;
            
            // Si l'image est trop grande pour le reste de la page, nouvelle page
            if ($pdf->GetY() + $newH > 270) {
                $pdf->AddPage();
            }
            $pdf->Image($imagePath, 10, $pdf->GetY(), $newW, $newH);
            $pdf->Ln($newH + 5);
        } else {
            // Fallback si getimagesize échoue
            $pdf->Image($imagePath, 10, $pdf->GetY(), 190);
            $pdf->Ln(110);
        }
    }
}

// --- BARRE DES LIKES ET COMMENTAIRES ---
$likesCount = $blogController->GetLikesCount($id);
$comments = $blogController->GetCommentsByPost($id);
$commentsCount = count($comments);

$pdf->Ln(2);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(101, 103, 107);
$statsText = $likesCount . " J'aime   ·   " . $commentsCount . " commentaires";
$pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $statsText), 0, 1, 'L');

// Ligne de séparation
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// --- LISTE DES COMMENTAIRES ---
if ($commentsCount > 0) {
    foreach ($comments as $c) {
        // Nom de l'auteur du commentaire
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(5, 5, 5);
        $commentAuthor = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $c['user_name']);
        
        // Date
        $cDate = date('d/m/Y H:i', strtotime($c['created_at']));
        
        $pdf->Cell(0, 5, $commentAuthor . ' - ' . $cDate, 0, 1, 'L');
        
        // Texte du commentaire
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(5, 5, 5);
        $cContent = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $c['content']);
        
        // Affichage avec un léger retrait (indentation)
        $pdf->SetX(15);
        $pdf->MultiCell(185, 5, $cContent, 0, 'L');
        $pdf->Ln(4);
    }
} else {
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->SetTextColor(101, 103, 107);
    $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Aucun commentaire pour le moment.'), 0, 1, 'C');
}

// Sortie
$filename = 'Publication_FB_' . $id . '.pdf';
$pdf->Output('I', $filename); // 'I' pour l'afficher dans le navigateur (Inline)
exit;
