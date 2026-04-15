<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $pdo;

    // Propriétés
    public $id;
    public $first_name;
    public $last_name;
    public $username;
    public $email;
    public $password;
    public $role;
    public $phone;
    public $city;
    public $bio;
    public $linkedin_url;
    public $date_of_birth;
    public $status;
    public $created_at;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // Créer un utilisateur (Register & Add from Admin)
    public function create() {
        $query = "INSERT INTO users (first_name, last_name, username, email, password, role, phone, city, date_of_birth, status) 
                  VALUES (:first_name, :last_name, :username, :email, :password, :role, :phone, :city, :date_of_birth, :status)";
        
        $stmt = $this->pdo->prepare($query);

        // Sanitize & Bind
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        
        // Hash password before saving if not already hashed
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashed_password);
        
        // Définir des valeurs par défaut si vide
        $role = $this->role ?? 'Entrepreneur';
        $status = $this->status ?? 'En attente';
        
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Lire tous les utilisateurs (pour le backoffice)
    public function readAll() {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lire un utilisateur par ID (Profil, Modification par admin)
    public function readOne($id) {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Connexion
    public function login($email, $password) {
        $query = "SELECT id, password, role, status FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if ($row) {
            if (password_verify($password, $row['password'])) {
                return $row; // Renvoie les infos pour la session
            }
        }
        return false;
    }

    // Mettre à jour (Profil ou Admin)
    public function update() {
        $query = "UPDATE users SET 
                    first_name = :first_name, 
                    last_name = :last_name, 
                    email = :email, 
                    role = :role, 
                    phone = :phone, 
                    city = :city, 
                    bio = :bio, 
                    linkedin_url = :linkedin_url, 
                    date_of_birth = :date_of_birth,
                    status = :status";
                    
        // Ne modifier le mot de passe que s'il est fourni
        if (!empty($this->password)) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':bio', $this->bio);
        $stmt->bindParam(':linkedin_url', $this->linkedin_url);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if (!empty($this->password)) {
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer (Admin)
    public function delete($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Vérifier si un email existe déjà (Contrôle PHP)
    public function emailExists($email) {
        $query = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Vérifier si un username existe déjà (Contrôle PHP)
    public function usernameExists($username) {
        $query = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
