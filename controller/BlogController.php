<?php

require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/../model/PostModel.php';

class BlogController {

    private $uploadDir = __DIR__ . '/../uploads/';

    // ═══════════════════════════════════════════════════════════
    // ─────────────── LIRE / AFFICHER ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Récupère tous les posts
     */
    function AfficherPosts($status = null) {
        $sql = "SELECT * FROM posts";
        if ($status) {
            $sql .= " WHERE status = :status";
        }
        $sql .= " ORDER BY created_at DESC";
        
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            if ($status) {
                $query->execute([':status' => $status]);
            } else {
                $query->execute();
            }
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un post par ID
     */
    function RecupererPost($id) {
        $sql = "SELECT * FROM posts WHERE id = :id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── CRÉER ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Crée un nouveau post
     */
    function AjouterPost($post, $file = null) {
        $coverImage = null;
        if ($file && !empty($file['name'])) {
            $coverImage = $this->UploadImage($file);
        }

        // Get category name
        $db = Config::GetConnexion();
        $catStmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
        $catStmt->execute([$post->getCategoryId()]);
        $cat = $catStmt->fetch();
        $categoryName = $cat ? $cat['name'] : null;

        $sql = "INSERT INTO posts (title, content, category_id, category, cover_image, status, created_at, updated_at)
                VALUES (:title, :content, :category_id, :category, :cover_image, :status, NOW(), NOW())";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'title'          => $post->getTitle(),
                'content'        => $post->getContent(),
                'category_id'    => $post->getCategoryId(),
                'category'       => $categoryName,
                'cover_image'    => $coverImage,
                'status'         => $post->getStatus(),
            ]);
            $postId = $db->lastInsertId();

            // Envoyer notification par email
            require_once __DIR__ . '/../mailer.php';
            Mailer::notifyNewPost($post->getTitle(), $post->getContent(), $categoryName, $post->getStatus());

            return $postId;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la création: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── METTRE À JOUR ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Met à jour un post existant
     */
    function ModifierPost($post, $id, $file = null) {

        // Traiter l'image si une nouvelle est fournie
        if ($file && !empty($file['name'])) {
            $coverImage = $this->UploadImage($file);
            // Supprimer l'ancienne image
            $oldPost = $this->RecupererPost($id);
            if ($oldPost && $oldPost['cover_image']) {
                @unlink($this->uploadDir . $oldPost['cover_image']);
            }
        } else {
            $coverImage = $post->getCoverImage();
        }

        $db = Config::GetConnexion();
        $catStmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
        $catStmt->execute([$post->getCategoryId()]);
        $cat = $catStmt->fetch();
        $categoryName = $cat ? $cat['name'] : null;

        $sql = "UPDATE posts 
                SET title          = :title,
                    content        = :content,
                    category_id    = :category_id,
                    category       = :category,
                    cover_image    = :cover_image,
                    status         = :status,
                    updated_at     = NOW()
                WHERE id = :id";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'title'          => $post->getTitle(),
                'content'        => $post->getContent(),
                'category_id'    => $post->getCategoryId(),
                'category'       => $categoryName,
                'cover_image'    => $coverImage,
                'status'         => $post->getStatus(),
                'id'             => $id,
            ]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
    // ═══════════════════════════════════════════════════════════
    // ─────────────── SUPPRIMER ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Supprime un post
     */
    function SupprimerPost($id) {
        $sql = "DELETE FROM posts WHERE id = :id";
        $db = Config::GetConnexion();
        
        // Récupérer les infos du post avant suppression
        $post = $this->RecupererPost($id);
        
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            
            // Supprimer l'image associée si elle existe
            if ($post && $post['cover_image']) {
                @unlink($this->uploadDir . $post['cover_image']);
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── FONCTIONS UTILITAIRES ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Génère un slug à partir d'un titre
     */
    function genererSlug($title) {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', trim($slug));
        $slug = preg_replace('/-{2,}/', '-', $slug);
        return $slug;
    }

    /**
     * Upload une image
     */
    function UploadImage($file) {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees)) {
            throw new Exception('Extension non autorisée. Formats acceptés: jpg, png, gif, webp');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Image trop lourde. Maximum 5MB');
        }

        if (!getimagesize($file['tmp_name'])) {
            throw new Exception('Le fichier n\'est pas une image valide');
        }

        $nouveauNom = uniqid('post_', true) . '.' . $extension;

        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $nouveauNom)) {
            return $nouveauNom;
        } else {
            throw new Exception('Échec du téléchargement de l\'image');
        }
    }

    /**
     * Recherche des posts
     */
    function RecherchePost($keyword) {
        $sql = "SELECT * FROM posts 
                WHERE title LIKE :keyword OR content LIKE :keyword
                ORDER BY created_at DESC";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $keyword = "%{$keyword}%";
            $query->execute([':keyword' => $keyword]);
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Recherche des posts par catégorie
     */
    function RechercheParCategorie($categoryId) {
        $sql = "SELECT * FROM posts 
                WHERE category_id = :category_id
                ORDER BY created_at DESC";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':category_id' => $categoryId]);
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Trier les posts
     */
    function TrierPosts($critere, $ordre = 'ASC') {
        $criteresAutorises = ['title', 'created_at', 'category', 'status'];
        if (!in_array($critere, $criteresAutorises)) {
            $critere = 'created_at';
        }
        
        $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM posts ORDER BY $critere $ordre";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Affiche les posts publiés seulement
     */
    function AfficherPublies() {
        return $this->AfficherPosts('published');
    }

    /**
     * Pagination des posts
     */
    function Pagination($page = 1, $itemsPerPage = 9) {
        $start = ($page - 1) * $itemsPerPage;
        $sql = "SELECT * FROM posts WHERE status = 'published' 
                ORDER BY created_at DESC 
                LIMIT :start, :limit";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':start', $start, PDO::PARAM_INT);
            $query->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Compte le nombre total de posts
     */
    function NombreDesPosts() {
        $sql = "SELECT COUNT(*) AS nb_posts FROM posts";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetch();
            return (int) $result['nb_posts'];
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer les commentaires d'un post
     */
    function GetCommentsByPost($postId) {
        $sql = "SELECT id, post_id, user_id, user_name, content, created_at FROM comments WHERE post_id = :post_id ORDER BY created_at ASC";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':post_id' => $postId]);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Ajouter un commentaire
     */
    function AddComment($postId, $content, $userName = 'Utilisateur') {
        $sql = "INSERT INTO comments (post_id, user_id, user_name, content, created_at) VALUES (:post_id, :user_id, :user_name, :content, NOW())";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':post_id' => $postId,
                ':user_id' => 0,
                ':user_name' => $userName,
                ':content' => $content
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function GetLikesCount($postId) {
        $sql = "SELECT COUNT(*) FROM likes WHERE post_id = :post_id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':post_id' => $postId]);
            return $query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function HasLiked($postId, $userId) {
        $sql = "SELECT COUNT(*) FROM likes WHERE post_id = :post_id AND user_id = :user_id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':post_id' => $postId, ':user_id' => $userId]);
            return $query->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function ToggleLike($postId, $userId) {
        $db = Config::GetConnexion();
        if ($this->HasLiked($postId, $userId)) {
            $sql = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
            $query = $db->prepare($sql);
            $query->execute([':post_id' => $postId, ':user_id' => $userId]);
            return false; // unliked
        } else {
            $sql = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
            $query = $db->prepare($sql);
            $query->execute([':post_id' => $postId, ':user_id' => $userId]);
            return true; // liked
        }
    }
}
?>