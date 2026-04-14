<?php
class Event {
    private $idEvenement;
    private $titre;
    private $description;
    private $date;
    private $lieu;
    private $idOrganisateur;

    public function __construct($data = []) {
        $this->idEvenement = $data['idEvenement'] ?? null;
        $this->titre = $data['titre'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->date = $data['date'] ?? '';
        $this->lieu = $data['lieu'] ?? '';
        $this->idOrganisateur = $data['idOrganisateur'] ?? 0;
    }

    // GETTERS
    public function getId() { return $this->idEvenement; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getDate() { return $this->date; }
    public function getLieu() { return $this->lieu; }
    public function getIdOrganisateur() { return $this->idOrganisateur; }

    // SETTERS
    public function setTitre($v) { $this->titre = $v; }
    public function setDescription($v) { $this->description = $v; }
    public function setDate($v) { $this->date = $v; }
    public function setLieu($v) { $this->lieu = $v; }
    public function setIdOrganisateur($v) { $this->idOrganisateur = $v; }
    public function setId($id) { $this->idEvenement = $id; }

    public function toArray() {
        return [
            'idEvenement' => $this->idEvenement,
            'titre' => $this->titre,
            'description' => $this->description,
            'date' => $this->date,
            'lieu' => $this->lieu,
            'idOrganisateur' => $this->idOrganisateur,
        ];
    }

    // DATABASE METHODS (Active Record Pattern)

    private static function getDb() {
        require_once __DIR__ . '/../config/Database.php';
        return Database::getInstance()->getConnection();
    }

    private static function mapToEvent($row) {
        $event = new Event();
        $event->setId($row['idEvenement'] ?? null);
        $event->setTitre($row['titre'] ?? '');
        $event->setDescription($row['description'] ?? '');
        $event->setDate($row['date'] ?? '');
        $event->setLieu($row['lieu'] ?? '');
        $event->setIdOrganisateur($row['idOrganisateur'] ?? null);
        return $event;
    }

    public static function getAll() {
        $db = self::getDb();
        $query = "SELECT * FROM evenement ORDER BY date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $events = [];
        foreach ($results as $row) {
            $events[] = self::mapToEvent($row);
        }
        return $events;
    }

    public static function getById($id) {
        $db = self::getDb();
        $query = "SELECT * FROM evenement WHERE idEvenement = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? self::mapToEvent($result) : null;
    }

    public function save() {
        $db = self::getDb();
        if ($this->idEvenement) {
            // Update
            $query = "UPDATE evenement SET titre = :titre, description = :description, date = :date, lieu = :lieu, idOrganisateur = :idOrganisateur WHERE idEvenement = :id";
            $stmt = $db->prepare($query);
            return $stmt->execute([
                ':id' => $this->idEvenement,
                ':titre' => $this->titre,
                ':description' => $this->description,
                ':date' => $this->date,
                ':lieu' => $this->lieu,
                ':idOrganisateur' => $this->idOrganisateur,
            ]);
        } else {
            // Create
            $query = "INSERT INTO evenement (titre, description, date, lieu, idOrganisateur) VALUES (:titre, :description, :date, :lieu, :idOrganisateur)";
            $stmt = $db->prepare($query);
            $success = $stmt->execute([
                ':titre' => $this->titre,
                ':description' => $this->description,
                ':date' => $this->date,
                ':lieu' => $this->lieu,
                ':idOrganisateur' => $this->idOrganisateur,
            ]);
            if ($success) {
                $this->idEvenement = $db->lastInsertId();
                return $this;
            }
            return false;
        }
    }

    public static function delete($id) {
        $db = self::getDb();
        $query = "DELETE FROM evenement WHERE idEvenement = :id";
        $stmt = $db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public static function getByCategory($category) {
        $db = self::getDb();
        $query = "SELECT * FROM evenement WHERE categorie = :category ORDER BY date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([':category' => $category]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $events = [];
        foreach ($results as $row) {
            $events[] = self::mapToEvent($row);
        }
        return $events;
    }

    public static function search($term) {
        $db = self::getDb();
        $query = "SELECT * FROM evenement WHERE titre LIKE :term ORDER BY date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([':term' => '%' . $term . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $events = [];
        foreach ($results as $row) {
            $events[] = self::mapToEvent($row);
        }
        return $events;
    }
}
