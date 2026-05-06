<?php
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

/**
 * Classe PDF personnalisée pour l'export des offres Jobyfind
 */
class OffrePDF extends FPDF {
    function Header() {
        // Bandeau bleu foncé
        $this->SetFillColor(11, 31, 75);
        $this->Rect(0, 0, 210, 22, 'F');
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(6);
        $this->Cell(0, 10, "Jobyfind - Rapport des Offres d'emploi", 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '  |  Genere le ' . date('d/m/Y a H:i'), 0, 0, 'C');
    }
}
?>
