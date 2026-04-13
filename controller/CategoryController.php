<?php
require_once __DIR__ . '/../connexion.php';

class CategoryController {
    
    public function getCategories() {
        $db = Config::GetConnexion();
        $query = $db->query("SELECT * FROM categories ORDER BY name ASC");
        return $query->fetchAll();
    }

    public function getCategory($id) {
        $db = Config::GetConnexion();
        $query = $db->prepare("SELECT * FROM categories WHERE id = :id");
        $query->execute([':id' => $id]);
        return $query->fetch();
    }

    public function addCategory($name) {
        $db = Config::GetConnexion();
        $query = $db->prepare("INSERT INTO categories (name) VALUES (:name)");
        try {
            return $query->execute([':name' => $name]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updateCategory($id, $name) {
        $db = Config::GetConnexion();
        $query = $db->prepare("UPDATE categories SET name = :name WHERE id = :id");
        try {
            return $query->execute([':name' => $name, ':id' => $id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function deleteCategory($id) {
        $db = Config::GetConnexion();
        $query = $db->prepare("DELETE FROM categories WHERE id = :id");
        return $query->execute([':id' => $id]);
    }
}
?>