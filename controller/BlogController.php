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

    /**
     * Ajoute automatiquement la colonne de vues si elle n'existe pas encore.
     */
    private function EnsureAnalyticsSchema() {
        $db = Config::GetConnexion();
        try {
            $columnCheck = $db->query("SHOW COLUMNS FROM posts LIKE 'views_count'");
            if (!$columnCheck->fetch()) {
                $db->exec("ALTER TABLE posts ADD COLUMN views_count INT UNSIGNED NOT NULL DEFAULT 0");
            }
        } catch (Exception $e) {
            // Les statistiques restent optionnelles si la migration echoue.
        }
    }

    /**
     * Incrémente le nombre de vues d'un post.
     */
    public function IncrementPostView($postId) {
        $this->EnsureAnalyticsSchema();
        $sql = "UPDATE posts SET views_count = views_count + 1 WHERE id = :id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $postId]);
            return $this->GetPostViewCount($postId);
        } catch (Exception $e) {
            return 0;
        }
    }

    public function GetPostViewCount($postId) {
        $this->EnsureAnalyticsSchema();
        $sql = "SELECT COALESCE(views_count, 0) FROM posts WHERE id = :id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $postId]);
            return (int) $query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function GetTotalViews() {
        $this->EnsureAnalyticsSchema();
        $sql = "SELECT COALESCE(SUM(views_count), 0) FROM posts";
        $db = Config::GetConnexion();
        try {
            return (int) $db->query($sql)->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function GetTotalLikes() {
        $sql = "SELECT COUNT(*) FROM likes";
        $db = Config::GetConnexion();
        try {
            return (int) $db->query($sql)->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function GetMostViewedPosts($limit = 5) {
        $this->EnsureAnalyticsSchema();
        $limit = max(1, (int) $limit);
        $sql = "SELECT id, title, COALESCE(views_count, 0) AS views_count
                FROM posts
                ORDER BY views_count DESC, created_at DESC
                LIMIT $limit";
        $db = Config::GetConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function GetMostLikedPosts($limit = 5) {
        $limit = max(1, (int) $limit);
        $sql = "SELECT p.id, p.title, COUNT(l.id) AS likes_count
                FROM posts p
                LEFT JOIN likes l ON l.post_id = p.id
                GROUP BY p.id, p.title, p.created_at
                ORDER BY likes_count DESC, p.created_at DESC
                LIMIT $limit";
        $db = Config::GetConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function GetMostCommentedPosts($limit = 5) {
        $limit = max(1, (int) $limit);
        $sql = "SELECT p.id, p.title, COUNT(c.id) AS comments_count
                FROM posts p
                LEFT JOIN comments c ON c.post_id = p.id
                GROUP BY p.id, p.title, p.created_at
                ORDER BY comments_count DESC, p.created_at DESC
                LIMIT $limit";
        $db = Config::GetConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function GetCommentsEvolution($days = 7) {
        $days = max(1, (int) $days);
        $start = new DateTime("-" . ($days - 1) . " days");
        $start->setTime(0, 0, 0);
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $day = clone $start;
            $day->modify("+$i days");
            $key = $day->format('Y-m-d');
            $series[$key] = [
                'day' => $key,
                'label' => $day->format('d/m'),
                'comments_count' => 0
            ];
        }

        $sql = "SELECT DATE(created_at) AS day, COUNT(*) AS comments_count
                FROM comments
                WHERE created_at >= :start_date
                GROUP BY DATE(created_at)
                ORDER BY day ASC";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':start_date' => $start->format('Y-m-d H:i:s')]);
            foreach ($query->fetchAll() as $row) {
                if (isset($series[$row['day']])) {
                    $series[$row['day']]['comments_count'] = (int) $row['comments_count'];
                }
            }
        } catch (Exception $e) {
            return array_values($series);
        }

        return array_values($series);
    }

    public function GetAdvancedStats($days = 7, $limit = 5) {
        return [
            'total_views' => $this->GetTotalViews(),
            'total_likes' => $this->GetTotalLikes(),
            'top_viewed' => $this->GetMostViewedPosts($limit),
            'top_liked' => $this->GetMostLikedPosts($limit),
            'top_commented' => $this->GetMostCommentedPosts($limit),
            'comments_evolution' => $this->GetCommentsEvolution($days)
        ];
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

    // ═══════════════════════════════════════════════════════════
    // ─────────────── SYSTÈME DE NOTATION (RATING) ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * S'assure que la table post_ratings existe (migration automatique).
     */
    private function EnsureRatingSchema() {
        $db = Config::GetConnexion();
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `post_ratings` (
                    `id`         INT(11) NOT NULL AUTO_INCREMENT,
                    `post_id`    INT(11) NOT NULL,
                    `user_id`    INT(11) NOT NULL DEFAULT 1,
                    `user_ip`    VARCHAR(45),
                    `rating`     TINYINT(1) NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_rating` (`post_id`, `user_ip`),
                    KEY `idx_post_rating` (`post_id`),
                    CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            // Silencieux si déjà créée
        }
    }

    /**
     * Ajoute ou met à jour la note d'un utilisateur pour un post.
     * Recalcule automatiquement la moyenne et le nombre d'avis dans `posts`.
     * Retourne ['avg' => float, 'count' => int] ou false en cas d'erreur.
     */
    public function AddRating($postId, $userId, $rating) {
        $this->EnsureRatingSchema();
        $rating = max(1, min(5, (int)$rating));
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db = Config::GetConnexion();
        try {
            // INSERT ou UPDATE si l'IP a déjà voté
            $sql = "INSERT INTO post_ratings (post_id, user_id, user_ip, rating)
                    VALUES (:post_id, :user_id, :user_ip, :rating)
                    ON DUPLICATE KEY UPDATE rating = :rating2, updated_at = NOW()";
            $query = $db->prepare($sql);
            $query->execute([
                ':post_id'  => $postId,
                ':user_id'  => $userId,
                ':user_ip'  => $userIp,
                ':rating'   => $rating,
                ':rating2'  => $rating,
            ]);

            // Recalculer la moyenne et le nombre total de votes
            $avgSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total
                       FROM post_ratings WHERE post_id = :post_id";
            $avgQuery = $db->prepare($avgSql);
            $avgQuery->execute([':post_id' => $postId]);
            $stats = $avgQuery->fetch();

            $newAvg   = round((float)$stats['avg_rating'], 1);
            $newCount = (int)$stats['total'];

            // Mettre à jour la table posts
            $updateSql = "UPDATE posts SET rating = :rating, reviews_count = :count WHERE id = :id";
            $updateQuery = $db->prepare($updateSql);
            $updateQuery->execute([
                ':rating' => $newAvg,
                ':count'  => $newCount,
                ':id'     => $postId,
            ]);

            return ['avg' => $newAvg, 'count' => $newCount];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retourne la note donnée par un utilisateur (via IP) pour un post, ou 0 si pas encore voté.
     */
    public function GetUserRating($postId, $userId) {
        $this->EnsureRatingSchema();
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db = Config::GetConnexion();
        try {
            $sql = "SELECT rating FROM post_ratings WHERE post_id = :post_id AND user_ip = :user_ip LIMIT 1";
            $query = $db->prepare($sql);
            $query->execute([':post_id' => $postId, ':user_ip' => $userIp]);
            $row = $query->fetch();
            return $row ? (int)$row['rating'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Retourne la moyenne et le nombre d'avis d'un post.
     */
    public function GetPostRating($postId) {
        $db = Config::GetConnexion();
        try {
            $sql = "SELECT COALESCE(rating, 0) as avg_rating, COALESCE(reviews_count, 0) as total
                    FROM posts WHERE id = :id";
            $query = $db->prepare($sql);
            $query->execute([':id' => $postId]);
            $row = $query->fetch();
            return ['avg' => (float)($row['avg_rating'] ?? 0), 'count' => (int)($row['total'] ?? 0)];
        } catch (Exception $e) {
            return ['avg' => 0, 'count' => 0];
        }
    }
}
?>
