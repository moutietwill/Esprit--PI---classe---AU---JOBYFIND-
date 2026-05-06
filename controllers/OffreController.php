<?php
require_once __DIR__ . '/../models/Offre.php';

class OffreController {

    // ─── LISTE AVEC PAGINATION ──────────────────────────────────────────────
    public function index() {
        require_once __DIR__ . '/../config/Database.php';
        $db = Database::getInstance();

        // Tri dynamique sécurisé
        $allowed_sort = ['id_offre', 'titre', 'datePublication', 'type', 'statut'];
        $sort  = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort))
                 ? $_GET['sort'] : 'datePublication';
        $order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC') ? 'ASC' : 'DESC';

        // Pagination
        $per_page    = 5;
        $total_count = $db->query("SELECT COUNT(*) FROM offre")->fetchColumn();
        $total_pages = max(1, (int)ceil($total_count / $per_page));
        $page        = (isset($_GET['page']) && is_numeric($_GET['page']))
                       ? max(1, min((int)$_GET['page'], $total_pages)) : 1;
        $offset      = ($page - 1) * $per_page;

        $stmt = $db->query("SELECT * FROM offre ORDER BY `$sort` $order LIMIT $per_page OFFSET $offset");
        $offres = $stmt->fetchAll();

        $current_sort  = $sort;
        $current_order = $order;

        require_once __DIR__ . '/../views/back/offres/list.php';
    }

    // ─── CRÉER ──────────────────────────────────────────────────────────────
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $offre = new Offre();
            $offre->setTitre($_POST['titre']);
            $offre->setDescription($_POST['description']);
            $offre->setDatePublication($_POST['datePublication']);
            $offre->setStatut($_POST['statut']);
            $offre->setType($_POST['type']);
            require_once __DIR__ . '/../config/Database.php';
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO offre (titre, description, datePublication, statut, type) 
                                  VALUES (:titre, :description, :datePublication, :statut, :type)");
            $result = $stmt->execute([
                'titre'           => $offre->getTitre(),
                'description'     => $offre->getDescription(),
                'datePublication' => $offre->getDatePublication(),
                'statut'          => $offre->getStatut(),
                'type'            => $offre->getType()
            ]);
            if ($result) {
                header('Location: index.php?action=list_offres');
                exit();
            }
        }
        $offreData = null;
        require_once __DIR__ . '/../views/back/offres/form.php';
    }

    // ─── MODIFIER ───────────────────────────────────────────────────────────
    public function edit() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=list_offres');
            exit();
        }
        $id = $_GET['id'];
        require_once __DIR__ . '/../config/Database.php';
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM offre WHERE id_offre = :id");
        $stmt->execute(['id' => $id]);
        $offreData = $stmt->fetch();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $offre = new Offre();
            $offre->setIdOffre($id);
            $offre->setTitre($_POST['titre']);
            $offre->setDescription($_POST['description']);
            $offre->setDatePublication($_POST['datePublication']);
            $offre->setStatut($_POST['statut']);
            $offre->setType($_POST['type']);

            $stmt = $db->prepare("UPDATE offre SET 
                                  titre = :titre, description = :description,
                                  datePublication = :datePublication, statut = :statut,
                                  type = :type WHERE id_offre = :id");
            $result = $stmt->execute([
                'titre'           => $offre->getTitre(),
                'description'     => $offre->getDescription(),
                'datePublication' => $offre->getDatePublication(),
                'statut'          => $offre->getStatut(),
                'type'            => $offre->getType(),
                'id'              => $offre->getIdOffre()
            ]);
            if ($result) {
                header('Location: index.php?action=list_offres');
                exit();
            }
        }
        require_once __DIR__ . '/../views/back/offres/form.php';
    }

    // ─── SUPPRIMER ──────────────────────────────────────────────────────────
    public function delete() {
        if (isset($_GET['id'])) {
            require_once __DIR__ . '/../config/Database.php';
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM offre WHERE id_offre = :id");
            $stmt->execute(['id' => $_GET['id']]);
        }
        header('Location: index.php?action=list_offres');
        exit();
    }

    // ─── STATISTIQUES PAR TYPE ──────────────────────────────────────────────
    public function stats() {
        require_once __DIR__ . '/../config/Database.php';
        $db = Database::getInstance();

        // Stats par type (nombre d'offres)
        $stmtType = $db->query("SELECT type, COUNT(*) as total FROM offre GROUP BY type ORDER BY total DESC");
        $statsByType = $stmtType->fetchAll();

        // Stats par statut
        $stmtStatut = $db->query("SELECT statut, COUNT(*) as total FROM offre GROUP BY statut");
        $statsByStatut = $stmtStatut->fetchAll();

        // Offres par mois (12 derniers mois)
        $stmtMonth = $db->query("SELECT DATE_FORMAT(datePublication, '%Y-%m') as mois, COUNT(*) as total
                                  FROM offre
                                  WHERE datePublication >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                                  GROUP BY mois ORDER BY mois ASC");
        $statsByMonth = $stmtMonth->fetchAll();

        // Total global
        $totalOffres    = $db->query("SELECT COUNT(*) FROM offre")->fetchColumn();
        $totalActives   = $db->query("SELECT COUNT(*) FROM offre WHERE statut='Actif'")->fetchColumn();
        $totalInactives = $db->query("SELECT COUNT(*) FROM offre WHERE statut='Inactif'")->fetchColumn();

        // Candidatures totales
        $totalCandidatures = $db->query("SELECT COUNT(*) FROM candidature")->fetchColumn();

        require_once __DIR__ . '/../views/back/offres/stats.php';
    }

    // ─── EXPORT PDF ─────────────────────────────────────────────────────────
    public function exportPdf() {
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../lib/OffrePDF.php';
        $db = Database::getInstance();

        // Toutes les offres + nb candidatures
        $stmt = $db->query("SELECT o.*, 
                                   (SELECT COUNT(*) FROM candidature c WHERE c.id_offre = o.id_offre) as nb_candidatures
                            FROM offre o ORDER BY datePublication DESC");
        $offres = $stmt->fetchAll();

        // Stats par type
        $stmtT = $db->query("SELECT type, COUNT(*) as total FROM offre GROUP BY type ORDER BY total DESC");
        $statsByType = $stmtT->fetchAll();
        $totalOffres = count($offres);

        // Créer le PDF
        $pdf = new OffrePDF('P', 'mm', 'A4');
        $pdf->SetMargins(12, 28, 12);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        // ── Résumé ───────────────────────────────────────────────────────────
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetFillColor(240, 242, 248);
        $pdf->SetTextColor(11, 31, 75);
        $pdf->Cell(0, 8, 'Resume', 0, 1, 'L', true);
        $pdf->Ln(2);

        $totalActif   = count(array_filter($offres, fn($o) => $o['statut'] === 'Actif'));
        $totalInactif = $totalOffres - $totalActif;

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(62, 7, 'Total des offres : ' . $totalOffres, 0, 0);
        $pdf->Cell(62, 7, 'Actives : ' . $totalActif, 0, 0);
        $pdf->Cell(62, 7, 'Inactives : ' . $totalInactif, 0, 1);
        $pdf->Ln(5);

        // ── Tableau offres ────────────────────────────────────────────────────
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetFillColor(45, 121, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Cell(12, 8, 'ID',        1, 0, 'C', true);
        $pdf->Cell(62, 8, 'Titre',     1, 0, 'L', true);
        $pdf->Cell(28, 8, 'Date Pub.', 1, 0, 'C', true);
        $pdf->Cell(26, 8, 'Type',      1, 0, 'C', true);
        $pdf->Cell(24, 8, 'Statut',    1, 0, 'C', true);
        $pdf->Cell(22, 8, 'Candidats', 1, 1, 'C', true);

        $pdf->SetFont('Helvetica', '', 9);
        $fill = false;
        foreach ($offres as $o) {
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 255 : 255);
            $pdf->SetTextColor(55, 65, 81);
            $pdf->Cell(12, 7, $o['id_offre'], 1, 0, 'C', true);
            $titre = mb_strlen($o['titre']) > 35 ? mb_substr($o['titre'], 0, 33) . '..' : $o['titre'];
            $pdf->Cell(62, 7, $titre, 1, 0, 'L', true);
            $pdf->Cell(28, 7, $o['datePublication'], 1, 0, 'C', true);
            $pdf->Cell(26, 7, $o['type'], 1, 0, 'C', true);
            if ($o['statut'] === 'Actif')  $pdf->SetTextColor(22, 163, 74);
            else                           $pdf->SetTextColor(239, 68, 68);
            $pdf->Cell(24, 7, $o['statut'], 1, 0, 'C', true);
            $pdf->SetTextColor(55, 65, 81);
            $pdf->Cell(22, 7, $o['nb_candidatures'], 1, 1, 'C', true);
            $fill = !$fill;
        }

        $pdf->Ln(8);

        // ── Stats par type ────────────────────────────────────────────────────
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetFillColor(240, 242, 248);
        $pdf->SetTextColor(11, 31, 75);
        $pdf->Cell(0, 8, 'Repartition par type de contrat', 0, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetFillColor(45, 121, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(70, 7, 'Type de contrat', 1, 0, 'C', true);
        $pdf->Cell(50, 7, "Nombre d'offres", 1, 0, 'C', true);
        $pdf->Cell(56, 7, 'Pourcentage',     1, 1, 'C', true);

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        foreach ($statsByType as $s) {
            $pct = $totalOffres > 0 ? round($s['total'] / $totalOffres * 100, 1) : 0;
            $pdf->Cell(70, 7, $s['type'] ?: 'Non defini', 1, 0, 'C');
            $pdf->Cell(50, 7, $s['total'], 1, 0, 'C');
            $pdf->Cell(56, 7, $pct . ' %', 1, 1, 'C');
        }

        $pdf->Output('D', 'rapport_offres_' . date('Y-m-d') . '.pdf');
        exit();
    }
}
