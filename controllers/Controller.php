<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../models/Formation.php';

class Controller {
    protected function render($view, $data = []) {
        extract($data);
        $url = fn(string $path = '') => $this->routeUrl($path);
        $asset = fn(string $path = '') => $this->assetUrl($path);
        $legacyBlog = fn(string $path = 'view/frontoffice.php') => $this->legacyBlogUrl($path);
        $legacyBlogUrl = $legacyBlog('view/frontoffice.php');
        $legacyBlogAdminUrl = $legacyBlog('view/backoffice.php');
        $legacyBlogCreateUrl = $legacyBlog('view/backoffice.php?page=posts&action=add');
        
        require_once __DIR__ . '/../config/session.php';
        startAppSession();
        $isLoggedIn = isset($_SESSION['user_id']);
        $data['isLoggedIn'] = $isLoggedIn;
        $data['url'] = $url;
        $data['legacyBlogUrl'] = $legacyBlogUrl;
        
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!is_file($viewPath)) {
            http_response_code(404);
            echo "Vue '{$view}' introuvable.";
            return;
        }

        require $viewPath;
    }

    protected function redirect($url) {
        if (strpos($url, '/') === 0 && !preg_match('#^https?://#i', $url)) {
            $url = $this->routeUrl($url);
        }
        header('Location: ' . $url);
        exit;
    }

    protected function baseUrl(string $path = ''): string
    {
        return $this->routeUrl($path);
    }

    protected function routeUrl(string $path = ''): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        $baseDir = ($baseDir && $baseDir !== '.' && $baseDir !== '/') ? $baseDir : '';
        $path = '/' . ltrim($path, '/');

        if ($path === '/') {
            return $baseDir . '/index.php';
        }

        return $baseDir . '/index.php' . $path;
    }

    protected function assetUrl(string $path = ''): string
    {
        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        $baseDir = ($baseDir && $baseDir !== '.' && $baseDir !== '/') ? $baseDir : '';
        $path = preg_replace('#^/?public/#', '', $path);

        return $baseDir . '/' . ltrim($path, '/');
    }

    protected function legacyBlogUrl(string $path = 'view/frontoffice.php'): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');

        if (basename($baseDir) === 'public') {
            $baseDir = rtrim(dirname($baseDir), '/');
        }

        $baseDir = ($baseDir && $baseDir !== '.' && $baseDir !== '/') ? $baseDir : '';
        return $baseDir . '/projetweb/' . ltrim($path, '/');
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    protected function getEventDb()
    {
        return Database::getInstance()->getConnection();
    }

    protected function uploadEventImage(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors du telechargement de l\'image');
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new Exception('L\'image doit etre au format JPG, PNG, WEBP ou GIF');
        }

        $uploadDir = __DIR__ . '/../public/assets/images/event/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $safeExtension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $extension : 'jpg';
        $filename = 'ev_' . uniqid('', true) . '.' . $safeExtension;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Impossible d\'enregistrer l\'image');
        }

        return 'public/assets/images/event/' . $filename;
    }

    protected function mapRowToEvent(array $row): Event
    {
        $event = new Event();
        $event->setId($row['idEvenement'] ?? null);
        $event->setTitre($row['titre'] ?? '');
        $event->setDescription($row['description'] ?? '');
        $event->setDate($row['date'] ?? '');
        $event->setLieu($row['lieu'] ?? '');
        $event->setIdOrganisateur($row['idOrganisateur'] ?? null);
        $event->setImage($row['image'] ?? 'public/assets/images/event/default.jpg');
        return $event;
    }

    protected function fetchAllEvents(): array
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM evenement ORDER BY date DESC");
        $stmt->execute();

        $events = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $events[] = $this->mapRowToEvent($row);
        }

        return $events;
    }

    protected function fetchEventById($id): ?Event
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM evenement WHERE idEvenement = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToEvent($row) : null;
    }

    protected function persistEvent(Event $event)
    {
        $db = $this->getEventDb();
        $db->exec("ALTER TABLE evenement ADD COLUMN IF NOT EXISTS image VARCHAR(255) NULL DEFAULT 'public/assets/images/event/default.jpg'");

        if ($event->getId()) {
            $stmt = $db->prepare(
                "UPDATE evenement
                 SET titre = :titre, description = :description, date = :date, lieu = :lieu, idOrganisateur = :idOrganisateur, image = :image
                 WHERE idEvenement = :id"
            );

            return $stmt->execute([
                ':id' => $event->getId(),
                ':titre' => $event->getTitre(),
                ':description' => $event->getDescription(),
                ':date' => $event->getDate(),
                ':lieu' => $event->getLieu(),
                ':idOrganisateur' => $event->getIdOrganisateur(),
                ':image' => $event->getImage(),
            ]);
        }

        $stmt = $db->prepare(
            "INSERT INTO evenement (titre, description, date, lieu, idOrganisateur, image)
             VALUES (:titre, :description, :date, :lieu, :idOrganisateur, :image)"
        );

        $success = $stmt->execute([
            ':titre' => $event->getTitre(),
            ':description' => $event->getDescription(),
            ':date' => $event->getDate(),
            ':lieu' => $event->getLieu(),
            ':idOrganisateur' => $event->getIdOrganisateur(),
            ':image' => $event->getImage(),
        ]);

        if ($success) {
            $event->setId($db->lastInsertId());
            return $event;
        }

        return false;
    }

    protected function removeEventRecord($id): bool
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("DELETE FROM evenement WHERE idEvenement = :id");
        return $stmt->execute([':id' => $id]);
    }

    protected function fetchEventsByCategory($category): array
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM evenement WHERE categorie = :category ORDER BY date DESC");
        $stmt->execute([':category' => $category]);

        $events = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $events[] = $this->mapRowToEvent($row);
        }

        return $events;
    }

    protected function searchEventRecords($term): array
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM evenement WHERE titre LIKE :term ORDER BY date DESC");
        $stmt->execute([':term' => '%' . $term . '%']);

        $events = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $events[] = $this->mapRowToEvent($row);
        }

        return $events;
    }

    /**
     * Save inscription to DB
     */
    protected function saveInscription(Inscription $inscription): bool
    {
        $db = $this->getEventDb();

        // Ensure inscription table exists with correct structure
        $db->exec("CREATE TABLE IF NOT EXISTS `inscription` (
            `idInscription` int(11) NOT NULL AUTO_INCREMENT,
            `idEvenement` int(11) NOT NULL,
            `nom` varchar(100) NOT NULL,
            `prenom` varchar(100) NOT NULL,
            `email` varchar(255) NOT NULL,
            `dateInscription` date NOT NULL,
            `statut` varchar(50) NOT NULL DEFAULT 'confirmée',
            PRIMARY KEY (`idInscription`),
            KEY `idEvenement` (`idEvenement`),
            KEY `idx_email` (`email`),
            UNIQUE KEY `unique_inscription` (`idEvenement`, `email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $stmt = $db->prepare(
            "INSERT INTO inscription (idEvenement, nom, prenom, email, dateInscription, statut)
             VALUES (:e, :nom, :prenom, :email, :d, :s)"
        );
        $ok = $stmt->execute([
            ':e' => $inscription->getIdEvenement(),
            ':nom' => $inscription->getNom(),
            ':prenom' => $inscription->getPrenom(),
            ':email' => $inscription->getEmail(),
            ':d' => $inscription->getDateInscription(),
            ':s' => $inscription->getStatut(),
        ]);

        if ($ok) {
            $inscription->setId($db->lastInsertId());
        }

        return $ok;
    }

    /**
     * Count inscriptions for a given event
     */
    protected function countInscriptions(int $idEvenement): int
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM inscription WHERE idEvenement = :e");
        $stmt->execute([':e' => $idEvenement]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Map row to Inscription
     */
    protected function mapRowToInscription(array $row): Inscription
    {
        $inscription = new Inscription();
        $inscription->setId($row['idInscription'] ?? null);
        $inscription->setIdEvenement($row['idEvenement'] ?? null);
        $inscription->setTitreEvenement($row['titre_evenement'] ?? '');
        $inscription->setNom($row['nom'] ?? '');
        $inscription->setPrenom($row['prenom'] ?? '');
        $inscription->setEmail($row['email'] ?? '');
        $inscription->setDateInscription($row['dateInscription'] ?? '');
        $inscription->setStatut($row['statut'] ?? 'confirmée');
        return $inscription;
    }

    /**
     * Fetch all inscriptions
     */
    protected function fetchAllInscriptions(): array
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT i.*, e.titre as titre_evenement FROM inscription i 
                              LEFT JOIN evenement e ON i.idEvenement = e.idEvenement 
                              ORDER BY i.dateInscription DESC");
        $stmt->execute();

        $inscriptions = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $inscriptions[] = $this->mapRowToInscription($row);
        }

        return $inscriptions;
    }

    /**
     * Fetch inscription by ID
     */
    protected function fetchInscriptionById($id): ?Inscription
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT i.*, e.titre as titre_evenement FROM inscription i 
                              LEFT JOIN evenement e ON i.idEvenement = e.idEvenement 
                              WHERE i.idInscription = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToInscription($row) : null;
    }

    /**
     * Fetch inscriptions by event
     */
    protected function fetchInscriptionsByEvent($idEvenement): array
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT i.*, e.titre as titre_evenement FROM inscription i 
                              LEFT JOIN evenement e ON i.idEvenement = e.idEvenement 
                              WHERE i.idEvenement = :e 
                              ORDER BY i.dateInscription DESC");
        $stmt->execute([':e' => $idEvenement]);

        $inscriptions = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $inscriptions[] = $this->mapRowToInscription($row);
        }

        return $inscriptions;
    }

    /**
     * Update inscription
     */
    protected function updateInscriptionData(Inscription $inscription): bool
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare(
            "UPDATE inscription 
             SET nom = :nom, prenom = :prenom, email = :email, statut = :statut
             WHERE idInscription = :id"
        );

        return $stmt->execute([
            ':id' => $inscription->getId(),
            ':nom' => $inscription->getNom(),
            ':prenom' => $inscription->getPrenom(),
            ':email' => $inscription->getEmail(),
            ':statut' => $inscription->getStatut(),
        ]);
    }

    /**
     * Delete inscription
     */
    protected function removeInscription($id): bool
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("DELETE FROM inscription WHERE idInscription = :id");
        return $stmt->execute([':id' => $id]);
    }

    // FORMATION DAO METHODS
    protected function mapRowToFormation(array $row): Formation
    {
        return new Formation([
            'id' => $row['id'] ?? null,
            'titre' => $row['titre'] ?? '',
            'prix' => $row['prix'] ?? 0.0,
            'date' => $row['date'] ?? '',
            'duree' => $row['duree'] ?? '',
            'description' => $row['description'] ?? '',
            'categorie' => $row['categorie'] ?? '',
        ]);
    }

    protected function fetchAllFormations(): array
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM formation ORDER BY id DESC");
        $stmt->execute();

        $formations = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $formations[] = $this->mapRowToFormation($row);
        }

        return $formations;
    }

    protected function fetchFormationById($id): ?Formation
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM formation WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToFormation($row) : null;
    }

    protected function persistFormation(Formation $formation)
    {
        $db = $this->getEventDb();

        if ($formation->getId()) {
            $stmt = $db->prepare(
                "UPDATE formation
                 SET titre = :titre, prix = :prix, date = :date, duree = :duree, description = :description, categorie = :categorie
                 WHERE id = :id"
            );

            return $stmt->execute([
                ':id' => $formation->getId(),
                ':titre' => $formation->getTitre(),
                ':prix' => $formation->getPrix(),
                ':date' => $formation->getDate(),
                ':duree' => $formation->getDuree(),
                ':description' => $formation->getDescription(),
                ':categorie' => $formation->getCategorie(),
            ]);
        }

        $stmt = $db->prepare(
            "INSERT INTO formation (titre, prix, date, duree, description, categorie)
             VALUES (:titre, :prix, :date, :duree, :description, :categorie)"
        );

        $success = $stmt->execute([
            ':titre' => $formation->getTitre(),
            ':prix' => $formation->getPrix(),
            ':date' => $formation->getDate(),
            ':duree' => $formation->getDuree(),
            ':description' => $formation->getDescription(),
            ':categorie' => $formation->getCategorie(),
        ]);

        if ($success) {
            $formation->setId($db->lastInsertId());
            return $formation;
        }

        return false;
    }

    protected function removeFormationRecord($id): bool
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("DELETE FROM formation WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>
