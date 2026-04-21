<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/User.php';

class Controller {
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
            $url = ($baseDir && $baseDir !== '.' ? $baseDir : '') . $url;
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

    protected function mapRowToEvent(array $row): Event
    {
        $images = ['e1.png', 'e2.png', 'e3.png', 'e4.png', 'e5.png', 'e6.png', 'e11.png', 'e22.png', 'e33.png'];
        $imageIndex = ($row['idEvenement'] ?? 0) % count($images);
        $image = 'assets/images/event/' . $images[$imageIndex];
        
        $event = new Event([
            'idEvenement' => $row['idEvenement'] ?? null,
            'titre' => $row['titre'] ?? '',
            'description' => $row['description'] ?? '',
            'date' => $row['date'] ?? '',
            'lieu' => $row['lieu'] ?? '',
            'idOrganisateur' => $row['idOrganisateur'] ?? null,
            'image' => $image
        ]);
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

        if ($event->getId()) {
            $stmt = $db->prepare(
                "UPDATE evenement
                 SET titre = :titre, description = :description, date = :date, lieu = :lieu, idOrganisateur = :idOrganisateur
                 WHERE idEvenement = :id"
            );

            return $stmt->execute([
                ':id' => $event->getId(),
                ':titre' => $event->getTitre(),
                ':description' => $event->getDescription(),
                ':date' => $event->getDate(),
                ':lieu' => $event->getLieu(),
                ':idOrganisateur' => $event->getIdOrganisateur(),
            ]);
        }

        $stmt = $db->prepare(
            "INSERT INTO evenement (titre, description, date, lieu, idOrganisateur)
             VALUES (:titre, :description, :date, :lieu, :idOrganisateur)"
        );

        $success = $stmt->execute([
            ':titre' => $event->getTitre(),
            ':description' => $event->getDescription(),
            ':date' => $event->getDate(),
            ':lieu' => $event->getLieu(),
            ':idOrganisateur' => $event->getIdOrganisateur(),
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

    protected function mapRowToUser(array $row): User
    {
        $user = new User();
        $user->setId($row['idUtilisateur'] ?? null);
        $user->setPrenom($row['prenom'] ?? '');
        $user->setNom($row['nom'] ?? '');
        $user->setEmail($row['email'] ?? '');
        $user->setRole($row['role'] ?? '');
        $user->setStatus($row['status'] ?? '');
        $user->setDate($row['date_creation'] ?? '');
        $user->setLast($row['date_derniere_activite'] ?? '');
        return $user;
    }

    protected function fetchAllUsers(): array
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM utilisateur ORDER BY prenom, nom ASC");
        $stmt->execute();

        $users = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $users[] = $this->mapRowToUser($row);
        }

        return $users;
    }

    protected function fetchUserById($id): ?User
    {
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT * FROM utilisateur WHERE idUtilisateur = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }
}
?>
