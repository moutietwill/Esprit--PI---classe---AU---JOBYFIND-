<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Inscription.php';

class Controller {
    protected $eventImageColumnChecked = false;

    protected function render($view, $data = []) {
        extract($data);
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
            $script = $_SERVER['SCRIPT_NAME'] ?? '';
            $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');
            // Use index.php in the redirect path so it works with or without mod_rewrite
            $url = ($baseDir && $baseDir !== '.' ? $baseDir : '') . '/index.php' . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    protected function baseUrl(string $path = ''): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        return ($baseDir && $baseDir !== '.' ? $baseDir : '') . $path;
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

    protected function ensureEventImageColumn(): void
    {
        if ($this->eventImageColumnChecked) {
            return;
        }

        try {
            $db = $this->getEventDb();
            $stmt = $db->query("SHOW COLUMNS FROM evenement LIKE 'image'");
            $hasImageColumn = $stmt && $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$hasImageColumn) {
                $db->exec("ALTER TABLE evenement ADD COLUMN image VARCHAR(255) NULL AFTER idOrganisateur");
            }
        } catch (Exception $e) {
            error_log('ensureEventImageColumn error: ' . $e->getMessage());
        }

        $this->eventImageColumnChecked = true;
    }

    protected function normalizeImageStoragePath(string $imagePath): string
    {
        $imagePath = str_replace('\\', '/', trim($imagePath));
        if ($imagePath === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $imagePath) === 1 || stripos($imagePath, 'data:image/') === 0) {
            return $imagePath;
        }
        return ltrim($imagePath, '/');
    }

    protected function saveEventImagePath($idEvenement, string $imagePath): void
    {
        $eventId = (int) $idEvenement;
        if ($eventId <= 0) {
            return;
        }

        $this->ensureEventImageColumn();
        $db = $this->getEventDb();
        $stmt = $db->prepare("UPDATE evenement SET image = :image WHERE idEvenement = :id");
        $stmt->execute([
            ':id' => $eventId,
            ':image' => $this->normalizeImageStoragePath($imagePath),
        ]);
    }

    protected function handleImageUpload($fileArray, $default = 'public/assets/images/event/e1.png') {
        if (isset($fileArray['error']) && $fileArray['error'] === UPLOAD_ERR_OK) {
            $tmpName = $fileArray['tmp_name'];
            $name = basename($fileArray['name']);
            
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                return $default;
            }
            
            $newName = uniqid('ev_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../public/assets/images/event/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $destination = $uploadDir . $newName;
            if (move_uploaded_file($tmpName, $destination)) {
                return 'public/assets/images/event/' . $newName;
            }
        }
        return $default;
    }

    protected function getEventImagePool(): array
    {
        return [
            'public/assets/images/event/e1.png',
            'public/assets/images/event/e2.png',
            'public/assets/images/event/e3.png',
            'public/assets/images/event/e4.png',
            'public/assets/images/event/e5.png',
            'public/assets/images/event/e6.png',
            'public/assets/images/event/e11.png',
            'public/assets/images/event/e22.png',
            'public/assets/images/event/e33.png',
        ];
    }

    protected function pickEventImageBySeed($seed): string
    {
        $pool = $this->getEventImagePool();
        if (empty($pool)) {
            return 'public/assets/images/event/e1.png';
        }

        $seedValue = trim((string) $seed);
        if ($seedValue === '') {
            $seedValue = 'event-default-seed';
        }

        if (preg_match('/^-?\d+$/', $seedValue) === 1) {
            $hash = abs((int) $seedValue);
        } else {
            $hash = (int) sprintf('%u', crc32($seedValue));
        }
        return $pool[$hash % count($pool)];
    }

    protected function resolveEventImagePath(array $row): string
    {
        $rawImage = str_replace('\\', '/', trim((string) ($row['image'] ?? '')));
        $normalizedImage = ltrim($rawImage, '/');

        $seed = trim((string) ($row['idEvenement'] ?? ''));
        if ($seed === '') {
            $seed = implode('|', [
                (string) ($row['titre'] ?? ''),
                (string) ($row['date'] ?? ''),
                (string) ($row['lieu'] ?? ''),
            ]);
        }
        $fallbackImage = $this->pickEventImageBySeed($seed);

        $isCustomUpload = $this->isCustomEventImagePath($normalizedImage);
        if ($isCustomUpload) {
            return $rawImage;
        }

        $isStockOrLegacyImage = $rawImage === ''
            || preg_match('#(^|/)assets/images/event/default\.jpg$#i', $normalizedImage) === 1
            || preg_match('#(^|/)assets/images/event/e[0-9]+\.png$#i', $normalizedImage) === 1;
        if ($isStockOrLegacyImage) {
            return $fallbackImage;
        }

        return $rawImage;
    }

    protected function isCustomEventImagePath(string $imagePath): bool
    {
        $normalizedImage = ltrim(str_replace('\\', '/', trim($imagePath)), '/');
        return preg_match(
            '#(^|/)assets/images/event/ev_[^/]+\.(jpg|jpeg|png|gif|webp)$#i',
            $normalizedImage
        ) === 1;
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
        $event->setImage($this->resolveEventImagePath($row));
        return $event;
    }

    protected function fetchAllEvents(): array
    {
        $this->ensureEventImageColumn();
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM evenement ORDER BY date DESC");
        $stmt->execute();

        $events = [];
        $fallbackPool = $this->getEventImagePool();
        $fallbackIndex = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $event = $this->mapRowToEvent($row);

            $rawImage = $this->normalizeImageStoragePath((string) ($row['image'] ?? ''));
            if (!empty($fallbackPool) && !$this->isCustomEventImagePath($rawImage)) {
                $assignedImage = $fallbackPool[$fallbackIndex % count($fallbackPool)];
                $event->setImage($assignedImage);
                if ($rawImage !== $assignedImage) {
                    $this->saveEventImagePath($event->getId(), $assignedImage);
                }
                $fallbackIndex++;
            }

            $events[] = $event;
        }

        return $events;
    }

    protected function fetchEventById($id): ?Event
    {
        $this->ensureEventImageColumn();
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM evenement WHERE idEvenement = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToEvent($row) : null;
    }

    protected function persistEvent(Event $event)
    {
        $this->ensureEventImageColumn();
        $db = $this->getEventDb();
        $imagePath = $this->normalizeImageStoragePath((string) $event->getImage());
        $event->setImage($imagePath);

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
                ':image' => $imagePath,
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
            ':image' => $imagePath,
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
        $this->ensureEventImageColumn();
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
        $this->ensureEventImageColumn();
        $db = $this->getEventDb();
        $term = trim((string) $term);

        if ($term === '') {
            $stmt = $db->prepare("SELECT * FROM evenement ORDER BY date DESC");
            $stmt->execute();
        } else {
            // Split into tokens and match any token in description (requires at least one token match)
            $parts = preg_split('/\s+/', $term);
            $conds = [];
            $params = [];
            foreach ($parts as $i => $p) {
                $k = ':t' . $i;
                // match in description OR lieu OR titre
                $conds[] = "(description LIKE " . $k . " OR lieu LIKE " . $k . " OR titre LIKE " . $k . ")";
                $params[$k] = '%' . $p . '%';
            }
            $sql = "SELECT * FROM evenement WHERE (" . implode(' OR ', $conds) . ") ORDER BY date DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }

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
}
?>
