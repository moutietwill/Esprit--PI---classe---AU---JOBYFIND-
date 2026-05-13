<?php
class User {
    private $id;
    private $prenom;
    private $nom;
    private $email;
    private $role;
    private $status;
    private $date;
    private $last;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->prenom = $data['prenom'] ?? '';
        $this->nom = $data['nom'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->role = $data['role'] ?? '';
        $this->status = $data['status'] ?? '';
        $this->date = $data['date'] ?? '';
        $this->last = $data['last'] ?? '';
    }

    // Getters
    public function getId() { return $this->id; }
    public function getPrenom() { return $this->prenom; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }
    public function getDate() { return $this->date; }
    public function getLast() { return $this->last; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setEmail($email) { $this->email = $email; }
    public function setRole($role) { $this->role = $role; }
    public function setStatus($status) { $this->status = $status; }
    public function setDate($date) { $this->date = $date; }
    public function setLast($last) { $this->last = $last; }

    public function toArray() {
        return [
            'id' => $this->id,
            'prenom' => $this->prenom,
            'nom' => $this->nom,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'date' => $this->date,
            'last' => $this->last,
        ];
    }

    // DATABASE METHODS (Active Record Pattern)

    private static function getDb() {
        require_once __DIR__ . '/../config/Database.php';
        return Database::getInstance()->getConnection();
    }

    private static function mapToUser($row) {
        $user = new User();
        $user->setId($row['idUtilisateur'] ?? null);
        $user->setPrenom($row['prenom'] ?? '');
        $user->setNom($row['nom'] ?? '');
        $user->setEmail($row['email'] ?? '');
        $user->setRole($row['role'] ?? '');
        $user->setStatus($row['status'] ?? 'Actif');
        $user->setDate($row['date_creation'] ?? date('d M Y'));
        $user->setLast($row['date_derniere_activite'] ?? 'Jamais');
        return $user;
    }

    public static function getAll() {
        $db = self::getDb();
        $query = "SELECT * FROM utilisateur ORDER BY idUtilisateur DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $users = [];
        foreach ($results as $row) {
            $users[] = self::mapToUser($row);
        }
        return $users;
    }

    public static function getById($id) {
        $db = self::getDb();
        $query = "SELECT * FROM utilisateur WHERE idUtilisateur = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? self::mapToUser($result) : null;
    }

    public static function getByEmail($email) {
        $db = self::getDb();
        $query = "SELECT * FROM utilisateur WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? self::mapToUser($result) : null;
    }

    public function save() {
        $db = self::getDb();
        if ($this->id) {
            // Update
            $query = "UPDATE utilisateur SET prenom = :prenom, nom = :nom, email = :email, role = :role, status = :status WHERE idUtilisateur = :id";
            $stmt = $db->prepare($query);
            return $stmt->execute([
                ':id' => $this->id,
                ':prenom' => $this->prenom,
                ':nom' => $this->nom,
                ':email' => $this->email,
                ':role' => $this->role,
                ':status' => $this->status
            ]);
        } else {
            // Create
            $query = "INSERT INTO utilisateur (prenom, nom, email, role, status) VALUES (:prenom, :nom, :email, :role, :status)";
            $stmt = $db->prepare($query);
            $success = $stmt->execute([
                ':prenom' => $this->prenom,
                ':nom' => $this->nom,
                ':email' => $this->email,
                ':role' => $this->role,
                ':status' => $this->status
            ]);
            if ($success) {
                $this->id = $db->lastInsertId();
                return $this;
            }
            return false;
        }
    }

    public static function delete($id) {
        $db = self::getDb();
        $query = "DELETE FROM utilisateur WHERE idUtilisateur = :id";
        $stmt = $db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>