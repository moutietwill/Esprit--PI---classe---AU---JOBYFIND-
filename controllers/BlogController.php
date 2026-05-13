<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/Controller.php';

/**
 * BlogController
 * Gère tous les articles de blog, commentaires, likes et évaluations
 */
class BlogController extends Controller {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── AFFICHAGE DES POSTS ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Affiche la liste de tous les posts publiés
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $search = isset($_GET['search']) ? trim($_GET['search']) : null;
            $postsPerPage = 6;

            $posts = $this->getPosts($category, $search, $page, $postsPerPage);
            $totalPosts = $this->getPostsCount($category, $search);
            $totalPages = ceil($totalPosts / $postsPerPage);
            $categories = $this->getAllCategories();

            $this->render('blog/index', [
                'posts' => $posts,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'categories' => $categories,
                'selectedCategory' => $category,
                'searchQuery' => $search
            ]);
        } catch (Exception $e) {
            error_log('Blog Index Error: ' . $e->getMessage());
            $this->render('errors/500', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Affiche un post spécifique avec ses commentaires
     */
    public function show($id) {
        try {
            $post = $this->getPostById($id);
            
            if (!$post) {
                $this->render('errors/404', ['message' => 'Article non trouvé']);
                return;
            }

            // Incrémenter les vues
            $this->incrementViews($id);

            $comments = $this->getPostComments($id);
            $rating = $this->getPostAverageRating($id);
            $likesCount = $this->getPostLikesCount($id);

            $this->render('blog/show', [
                'post' => $post,
                'comments' => $comments,
                'rating' => $rating,
                'likesCount' => $likesCount
            ]);
        } catch (Exception $e) {
            error_log('Blog Show Error: ' . $e->getMessage());
            $this->render('errors/500', ['error' => $e->getMessage()]);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── CRÉATION DE POSTS ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Affiche le formulaire de création
     */
    public function create() {
        $categories = $this->getAllCategories();
        $this->render('blog/create', ['categories' => $categories]);
    }

    /**
     * Stocke un nouveau post
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/blog');
            return;
        }

        try {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $excerpt = trim($_POST['excerpt'] ?? '');

            // Validation
            if (empty($title) || strlen($title) < 5) {
                $this->redirect('/blog/create?error=title_invalid');
                return;
            }

            if (empty($content) || strlen($content) < 20) {
                $this->redirect('/blog/create?error=content_invalid');
                return;
            }

            // Upload image
            $coverImage = 'public/assets/images/blog/default.jpg';
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $coverImage = $this->uploadImage($_FILES['cover_image']);
            }

            // Créer le post
            $post = new Post([
                'title' => $title,
                'content' => $content,
                'category_id' => $category_id,
                'cover_image' => $coverImage,
                'excerpt' => $excerpt ?: substr(strip_tags($content), 0, 150),
                'status' => 'published'
            ]);

            $postId = $this->savePost($post);

            if ($postId) {
                $this->redirect('/blog/' . $postId . '?success=created');
            } else {
                $this->redirect('/blog/create?error=save_failed');
            }
        } catch (Exception $e) {
            error_log('Blog Store Error: ' . $e->getMessage());
            $this->redirect('/blog/create?error=exception');
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── MODIFICATION DE POSTS ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Affiche le formulaire d'édition
     */
    public function edit($id) {
        try {
            $post = $this->getPostById($id);
            if (!$post) {
                $this->redirect('/blog');
                return;
            }

            $categories = $this->getAllCategories();
            $this->render('blog/edit', ['post' => $post, 'categories' => $categories]);
        } catch (Exception $e) {
            error_log('Blog Edit Error: ' . $e->getMessage());
            $this->redirect('/blog');
        }
    }

    /**
     * Met à jour un post
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/blog/' . $id);
            return;
        }

        try {
            $post = $this->getPostById($id);
            if (!$post) {
                $this->redirect('/blog');
                return;
            }

            $post->setTitle(trim($_POST['title'] ?? ''));
            $post->setContent(trim($_POST['content'] ?? ''));
            $post->setCategoryId(isset($_POST['category_id']) ? (int)$_POST['category_id'] : null);
            $post->setExcerpt(trim($_POST['excerpt'] ?? ''));

            // Upload nouvelle image si fournie
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $post->setCoverImage($this->uploadImage($_FILES['cover_image']));
            }

            $this->persistPost($post);
            $this->redirect('/blog/' . $id . '?success=updated');
        } catch (Exception $e) {
            error_log('Blog Update Error: ' . $e->getMessage());
            $this->redirect('/blog/' . $id . '?error=exception');
        }
    }

    /**
     * Supprime un post
     */
    public function delete($id) {
        try {
            $this->deletePost($id);
            $this->redirect('/blog?success=deleted');
        } catch (Exception $e) {
            error_log('Blog Delete Error: ' . $e->getMessage());
            $this->redirect('/blog?error=delete_failed');
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── COMMENTAIRES ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Ajoute un commentaire (AJAX)
     */
    public function addComment() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        try {
            $post_id = (int)($_POST['post_id'] ?? 0);
            $author_name = trim($_POST['author_name'] ?? '');
            $author_email = trim($_POST['author_email'] ?? '');
            $content = trim($_POST['content'] ?? '');

            if (!$post_id || !$author_name || !$author_email || !$content) {
                echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
                exit;
            }

            if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email invalide']);
                exit;
            }

            $stmt = $this->db->prepare("
                INSERT INTO comments (post_id, author_name, author_email, content, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$post_id, $author_name, $author_email, $content]);

            echo json_encode(['success' => true, 'message' => 'Commentaire ajouté avec succès']);
        } catch (Exception $e) {
            error_log('Add Comment Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
        }
    }

    /**
     * Récupère les commentaires d'un post (AJAX)
     */
    public function getComments($postId) {
        header('Content-Type: application/json');
        try {
            $comments = $this->getPostComments($postId);
            echo json_encode(['success' => true, 'comments' => $comments]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur']);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── LIKES ET RATINGS ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Toggle like (AJAX)
     */
    public function toggleLike() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        try {
            $post_id = (int)($_POST['post_id'] ?? 0);
            $user_ip = $_SERVER['REMOTE_ADDR'];

            if (!$post_id) {
                echo json_encode(['success' => false, 'message' => 'Post ID invalide']);
                exit;
            }

            // Vérifier si l'utilisateur a déjà liké
            $stmt = $this->db->prepare("SELECT id, liked FROM post_likes WHERE post_id = ? AND user_ip = ?");
            $stmt->execute([$post_id, $user_ip]);
            $like = $stmt->fetch();

            if ($like) {
                // Toggle le like
                $newLiked = !$like['liked'];
                $stmt = $this->db->prepare("UPDATE post_likes SET liked = ? WHERE id = ?");
                $stmt->execute([$newLiked, $like['id']]);
            } else {
                // Créer un nouveau like
                $stmt = $this->db->prepare("INSERT INTO post_likes (post_id, user_ip, liked) VALUES (?, ?, 1)");
                $stmt->execute([$post_id, $user_ip]);
            }

            $likesCount = $this->getPostLikesCount($post_id);
            echo json_encode(['success' => true, 'likesCount' => $likesCount]);
        } catch (Exception $e) {
            error_log('Toggle Like Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
        }
    }

    /**
     * Ajouter une évaluation (AJAX)
     */
    public function addRating() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        try {
            $post_id = (int)($_POST['post_id'] ?? 0);
            $rating = (int)($_POST['rating'] ?? 0);
            $user_ip = $_SERVER['REMOTE_ADDR'];

            if (!$post_id || $rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Données invalides']);
                exit;
            }

            // Insérer ou mettre à jour l'évaluation
            $stmt = $this->db->prepare("
                INSERT INTO post_ratings (post_id, user_ip, rating) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = ?
            ");
            $stmt->execute([$post_id, $user_ip, $rating, $rating]);

            $avgRating = $this->getPostAverageRating($post_id);
            echo json_encode(['success' => true, 'avgRating' => $avgRating]);
        } catch (Exception $e) {
            error_log('Add Rating Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── REQUÊTES BASE DE DONNÉES ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Récupère les posts avec pagination
     */
    private function getPosts($category = null, $search = null, $page = 1, $limit = 6) {
        $offset = ($page - 1) * $limit;
        $limit = max(1, (int) $limit);
        $offset = max(0, (int) $offset);
        $sql = "SELECT * FROM posts WHERE status = 'published'";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($search) {
            $sql .= " AND (title LIKE ? OR content LIKE ?)";
            $search = "%$search%";
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        return array_map(fn($row) => new Post($row), $results);
    }

    /**
     * Compte les posts
     */
    private function getPostsCount($category = null, $search = null) {
        $sql = "SELECT COUNT(*) FROM posts WHERE status = 'published'";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($search) {
            $sql .= " AND (title LIKE ? OR content LIKE ?)";
            $search = "%$search%";
            $params[] = $search;
            $params[] = $search;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Récupère un post par ID
     */
    private function getPostById($id) {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new Post($row) : null;
    }

    /**
     * Récupère toutes les catégories
     */
    private function getAllCategories() {
        $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Sauvegarde un nouveau post
     */
    private function savePost(Post $post) {
        $stmt = $this->db->prepare("
            INSERT INTO posts (title, content, category_id, category, cover_image, excerpt, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $post->getTitle(),
            $post->getContent(),
            $post->getCategoryId(),
            $post->getCategory(),
            $post->getCoverImage(),
            $post->getExcerpt(),
            $post->getStatus()
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Met à jour un post
     */
    private function persistPost(Post $post) {
        $stmt = $this->db->prepare("
            UPDATE posts 
            SET title = ?, content = ?, category_id = ?, category = ?, cover_image = ?, excerpt = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $post->getTitle(),
            $post->getContent(),
            $post->getCategoryId(),
            $post->getCategory(),
            $post->getCoverImage(),
            $post->getExcerpt(),
            $post->getId()
        ]);
    }

    /**
     * Supprime un post
     */
    private function deletePost($id) {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Récupère les commentaires d'un post
     */
    private function getPostComments($postId) {
        $stmt = $this->db->prepare("
            SELECT * FROM comments 
            WHERE post_id = ? AND status = 'approved'
            ORDER BY created_at DESC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère le nombre de likes
     */
    private function getPostLikesCount($postId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ? AND liked = 1");
        $stmt->execute([$postId]);
        return $stmt->fetchColumn();
    }

    /**
     * Récupère la note moyenne
     */
    private function getPostAverageRating($postId) {
        $stmt = $this->db->prepare("SELECT AVG(rating) FROM post_ratings WHERE post_id = ?");
        $stmt->execute([$postId]);
        return round($stmt->fetchColumn(), 1);
    }

    /**
     * Incrémente les vues
     */
    private function incrementViews($postId) {
        $stmt = $this->db->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    /**
     * Upload une image
     */
    private function uploadImage($file) {
        $uploadDir = __DIR__ . '/../public/assets/images/blog/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'public/assets/images/blog/' . $filename;
        }

        throw new Exception('Erreur lors du téléchargement de l\'image');
    }
}
