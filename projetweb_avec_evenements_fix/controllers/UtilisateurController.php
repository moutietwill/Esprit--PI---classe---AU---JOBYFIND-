<?php
require_once(__DIR__ . '/../config/Database.php');
require_once(__DIR__ . '/../models/Utilisateur.php');

class UtilisateurController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listUsers($sort = null, $order = 'ASC')
    {
        $sql = "SELECT * FROM utilisateurs";
        
        $allowedSortFields = ['status', 'role', 'created_at', 'first_name', 'last_name', 'email'];
        if ($sort && in_array($sort, $allowedSortFields)) {
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY $sort $order";
        }
        
        try {
            $liste = $this->db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getMonthlyActiveUsersStats()
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(id) as count 
                FROM utilisateurs 
                WHERE status = 'Actif' 
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                ORDER BY month ASC";
        try {
            $query = $this->db->query($sql);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getMonthlyEntrepreneurStats()
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(id) as count 
                FROM utilisateurs 
                WHERE role = 'Entrepreneur' AND status = 'Actif'
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                ORDER BY month ASC";
        try {
            $query = $this->db->query($sql);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function login($email, $password)
    {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        try {
            $query = $this->db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'Suspendu') {
                    return 'suspended';
                }
                $updateLogin = $this->db->prepare('UPDATE utilisateurs SET last_login = NOW() WHERE id = :id');
                $updateLogin->execute(['id' => $user['id']]);
                
                return $user;
            }
            return false;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteUser($id)
    {
        try {
            $this->db->beginTransaction();

            // 1. Get email for clearing password resets
            $stmtEmail = $this->db->prepare("SELECT email FROM utilisateurs WHERE id = :id");
            $stmtEmail->execute(['id' => $id]);
            $email = $stmtEmail->fetchColumn();

            if ($email) {
                $sqlResets = "DELETE FROM password_resets WHERE email = :email";
                $reqResets = $this->db->prepare($sqlResets);
                $reqResets->execute(['email' => $email]);
            }

            // 2. Delete Profile
            $sqlProfile = "DELETE FROM profiles WHERE Id_utilisateur = :id";
            $reqProfile = $this->db->prepare($sqlProfile);
            $reqProfile->execute(['id' => $id]);

            // 3. Delete User
            $sqlUser = "DELETE FROM utilisateurs WHERE id = :id";
            $reqUser = $this->db->prepare($sqlUser);
            $reqUser->execute(['id' => $id]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addUser($utilisateur)
    {
        $sql = "INSERT INTO utilisateurs (first_name, last_name, username, date_of_birth, phone, city, email, password, role, status) 
                VALUES (:first_name, :last_name, :username, :date_of_birth, :phone, :city, :email, :password, :role, :status)";
        try {
            $password = $utilisateur->getPassword();
            if (strlen($password) < 60) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }

            $query = $this->db->prepare($sql);
            $query->execute([
                'first_name' => $utilisateur->getFirst_name(),
                'last_name' => $utilisateur->getLast_name(),
                'username' => $utilisateur->getUsername(),
                'date_of_birth' => $utilisateur->getDate_of_birth(),
                'phone' => $utilisateur->getPhone(),
                'city' => $utilisateur->getCity(),
                'email' => $utilisateur->getEmail(),
                'password' => $password,
                'role' => $utilisateur->getRole(),
                'status' => $utilisateur->getStatus()
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new Exception("Cette adresse e-mail est déjà utilisée.");
            }
            throw new Exception("Erreur lors de la création du compte : " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création du compte : " . $e->getMessage());
        }
    }

    public function updateUser($utilisateur, $id)
    {
        try {
            $password = $utilisateur->getPassword();
            if (!empty($password) && $password !== 'default123') {
                $sql = 'UPDATE utilisateurs SET 
                            first_name = :first_name, 
                            last_name = :last_name, 
                            username = :username, 
                            date_of_birth = :date_of_birth, 
                            phone = :phone, 
                            city = :city, 
                            email = :email, 
                            password = :password,
                            role = :role,
                            status = :status
                        WHERE id = :id';
            } else {
                $sql = 'UPDATE utilisateurs SET 
                            first_name = :first_name, 
                            last_name = :last_name, 
                            username = :username, 
                            date_of_birth = :date_of_birth, 
                            phone = :phone, 
                            city = :city, 
                            email = :email, 
                            role = :role,
                            status = :status
                        WHERE id = :id';
            }

            $query = $this->db->prepare($sql);
            
            $params = [
                'first_name' => $utilisateur->getFirst_name(),
                'last_name' => $utilisateur->getLast_name(),
                'username' => $utilisateur->getUsername(),
                'date_of_birth' => $utilisateur->getDate_of_birth(),
                'phone' => $utilisateur->getPhone(),
                'city' => $utilisateur->getCity(),
                'email' => $utilisateur->getEmail(),
                'role' => $utilisateur->getRole(),
                'status' => $utilisateur->getStatus(),
                'id' => $id
            ];

            if (!empty($password) && $password !== 'default123') {
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $query->execute($params);
        } catch (PDOException $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getUserById($id)
    {
        $sql = "SELECT * from utilisateurs where id = :id";
        try {
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            $user = $query->fetch();
            return $user;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1";
        try {
            $query = $this->db->prepare($sql);
            $query->execute(['email' => $email]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updatePassword($id, $newPassword)
    {
        $sql = "UPDATE utilisateurs SET password = :password WHERE id = :id";
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $query  = $this->db->prepare($sql);
            $query->execute(['password' => $hashed, 'id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
