<?php
/**
 * AdminBlogController - Gestion du blog
 * Relie les articles du blog aux utilisateurs et aux evenements.
 */

require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../config/Mailer.php';

class AdminBlogController extends Controller {
    private $uploadDir;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->uploadDir = __DIR__ . '/../public/uploads/blog/';
        $this->ensureBlogTables();
        $this->ensureUploadDir();
    }

    private function ensureBlogTables() {
        try {
            $db = Database::getInstance()->getConnection();

            $db->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `titre` varchar(255) NOT NULL,
                `slug` varchar(255) UNIQUE,
                `contenu` longtext NOT NULL,
                `resume` text,
                `auteur_id` int(11) DEFAULT NULL,
                `categorie` varchar(100),
                `image_couverture` varchar(255),
                `event_id` int(11) DEFAULT NULL,
                `statut` varchar(20) DEFAULT 'brouillon',
                `vues` int(11) DEFAULT 0,
                `date_creation` timestamp DEFAULT CURRENT_TIMESTAMP,
                `date_modification` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `date_publication` datetime,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`),
                KEY `auteur_id` (`auteur_id`),
                KEY `event_id` (`event_id`),
                KEY `statut` (`statut`),
                FULLTEXT KEY `recherche` (`titre`, `contenu`, `resume`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $this->ensureBlogPostColumn('auteur_id', "ALTER TABLE blog_posts ADD COLUMN auteur_id int(11) DEFAULT NULL AFTER resume");
            $this->ensureBlogPostColumn('event_id', "ALTER TABLE blog_posts ADD COLUMN event_id int(11) DEFAULT NULL AFTER image_couverture");

            $db->exec("CREATE TABLE IF NOT EXISTS `blog_commentaires` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `post_id` int(11) NOT NULL,
                `auteur_id` int(11) DEFAULT NULL,
                `nom` varchar(100),
                `email` varchar(100),
                `contenu` text NOT NULL,
                `approuve` tinyint(1) DEFAULT 0,
                `date_creation` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `post_id` (`post_id`),
                CONSTRAINT `blog_commentaires_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $db->exec("CREATE TABLE IF NOT EXISTS `blog_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `nom` varchar(100) NOT NULL UNIQUE,
                `slug` varchar(100) UNIQUE,
                `description` text,
                `icone` varchar(50),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $check = $db->query("SELECT COUNT(*) FROM blog_categories");
            if ((int) $check->fetchColumn() === 0) {
                $db->exec("INSERT INTO blog_categories (nom, slug, description) VALUES
                    ('Tutoriels', 'tutoriels', 'Guides et tutoriels pratiques'),
                    ('Actualites', 'actualites', 'Les dernieres nouvelles'),
                    ('Conseils', 'conseils', 'Conseils et bonnes pratiques')");
            }
        } catch (Exception $e) {
            error_log('Erreur creation tables blog: ' . $e->getMessage());
        }
    }

    private function ensureBlogPostColumn(string $column, string $sql): void
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SHOW COLUMNS FROM blog_posts LIKE :columnName");
        $stmt->execute([':columnName' => $column]);
        if (!$stmt->fetch()) {
            $db->exec($sql);
        }
    }

    private function ensureUploadDir() {
        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0777, true);
        }
    }

    public function index() {
        try {
            $posts = $this->fetchAdminPosts();
            $this->render('admin/blog/index', ['posts' => $posts]);
        } catch (Exception $e) {
            error_log('Erreur index blog: ' . $e->getMessage());
            $this->render('errors/500');
        }
    }

    public function create() {
        $currentUser = $this->requireAuthenticatedUser();

        try {
            $db = Database::getInstance()->getConnection();
            $categories = $db->query("SELECT * FROM blog_categories ORDER BY nom")->fetchAll();
            $events = $this->fetchBlogEvents();
            $this->render('admin/blog/create', [
                'categories' => $categories,
                'events' => $events,
                'currentUser' => $currentUser,
            ]);
        } catch (Exception $e) {
            error_log('Erreur creation blog: ' . $e->getMessage());
            $this->render('errors/500');
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/blog');
        }

        try {
            $currentUser = $this->requireAuthenticatedUser();
            $titre = trim($_POST['titre'] ?? '');
            $contenu = trim($_POST['contenu'] ?? '');
            $resume = trim($_POST['resume'] ?? '');
            $categorie = trim($_POST['categorie'] ?? '');
            $statut = $_POST['statut'] ?? 'brouillon';
            $auteurId = (int) $currentUser['id'];
            $eventId = $this->normalizeOptionalInt($_POST['event_id'] ?? null);

            if ($titre === '' || $contenu === '') {
                $this->redirect('/admin/blog/create?error=Titres et contenu requis');
            }

            $slug = $this->genererSlug($titre);
            $image = $this->traiterUploadImage($_FILES['image'] ?? null);
            $datePublication = $statut === 'publie' ? date('Y-m-d H:i:s') : null;

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO blog_posts
                (titre, slug, contenu, resume, auteur_id, categorie, image_couverture, event_id, statut, date_publication)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $titre,
                $slug,
                $contenu,
                $resume,
                $auteurId,
                $categorie !== '' ? $categorie : null,
                $image,
                $eventId,
                $statut,
                $datePublication,
            ]);

            if ($statut === 'publie') {
                Mailer::notifyNewBlogPost($titre, $auteurId ?: 0);
            }

            $this->redirect('/admin/blog?success=Post cree avec succes');
        } catch (Exception $e) {
            error_log('Erreur store blog: ' . $e->getMessage());
            $this->redirect('/admin/blog?error=' . urlencode($e->getMessage()));
        }
    }

    public function edit($id) {
        $currentUser = $this->requireAuthenticatedUser();

        try {
            $db = Database::getInstance()->getConnection();
            $post = $this->fetchPostById((int) $id);
            if (!$post) {
                $this->render('errors/404');
                return;
            }

            $categories = $db->query("SELECT * FROM blog_categories ORDER BY nom")->fetchAll();
            $events = $this->fetchBlogEvents();

            $this->render('admin/blog/edit', [
                'post' => $post,
                'categories' => $categories,
                'events' => $events,
                'currentUser' => $currentUser,
            ]);
        } catch (Exception $e) {
            error_log('Erreur edit blog: ' . $e->getMessage());
            $this->render('errors/500');
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/blog');
        }

        try {
            $currentUser = $this->requireAuthenticatedUser();
            $titre = trim($_POST['titre'] ?? '');
            $contenu = trim($_POST['contenu'] ?? '');
            $resume = trim($_POST['resume'] ?? '');
            $categorie = trim($_POST['categorie'] ?? '');
            $statut = $_POST['statut'] ?? 'brouillon';
            $auteurId = (int) $currentUser['id'];
            $eventId = $this->normalizeOptionalInt($_POST['event_id'] ?? null);

            if ($titre === '' || $contenu === '') {
                $this->redirect("/admin/blog/edit/$id?error=Champs requis");
            }

            $post = $this->fetchPostById((int) $id);
            if (!$post) {
                $this->render('errors/404');
                return;
            }

            $image = $post['image_couverture'];
            if (!empty($_FILES['image']['name'])) {
                if ($post['image_couverture']) {
                    @unlink($this->uploadDir . $post['image_couverture']);
                }
                $image = $this->traiterUploadImage($_FILES['image']);
            }

            $slug = ($post['slug'] === $this->genererSlug($post['titre']))
                ? $this->genererSlug($titre)
                : $post['slug'];
            $datePublication = $post['date_publication'];
            if ($statut === 'publie' && empty($datePublication)) {
                $datePublication = date('Y-m-d H:i:s');
            }
            if ($statut !== 'publie') {
                $datePublication = null;
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE blog_posts
                SET titre = ?, slug = ?, contenu = ?, resume = ?, auteur_id = ?, categorie = ?, image_couverture = ?, event_id = ?, statut = ?, date_publication = ?
                WHERE id = ?");

            $stmt->execute([
                $titre,
                $slug,
                $contenu,
                $resume,
                $auteurId,
                $categorie !== '' ? $categorie : null,
                $image,
                $eventId,
                $statut,
                $datePublication,
                $id,
            ]);

            $this->redirect('/admin/blog?success=Post mis a jour');
        } catch (Exception $e) {
            error_log('Erreur update blog: ' . $e->getMessage());
            $this->redirect("/admin/blog/edit/$id?error=" . urlencode($e->getMessage()));
        }
    }

    public function delete($id) {
        try {
            $this->requireAuthenticatedUser();
            $db = Database::getInstance()->getConnection();
            $post = $this->fetchPostById((int) $id);

            if ($post) {
                if ($post['image_couverture']) {
                    @unlink($this->uploadDir . $post['image_couverture']);
                }

                $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
                $stmt->execute([$id]);
            }

            $this->redirect('/admin/blog?success=Post supprime');
        } catch (Exception $e) {
            error_log('Erreur delete blog: ' . $e->getMessage());
            $this->redirect('/admin/blog?error=' . urlencode($e->getMessage()));
        }
    }

    public function publish($id) {
        try {
            $this->requireAuthenticatedUser();
            $db = Database::getInstance()->getConnection();
            $post = $this->fetchPostById((int) $id);

            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'Post non trouve']);
                return;
            }

            $newStatus = ($post['statut'] === 'publie') ? 'brouillon' : 'publie';
            $datePub = ($newStatus === 'publie') ? date('Y-m-d H:i:s') : null;

            $stmt = $db->prepare("UPDATE blog_posts SET statut = ?, date_publication = ? WHERE id = ?");
            $stmt->execute([$newStatus, $datePub, $id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'status' => $newStatus]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getAll() {
        try {
            $statut = $_GET['statut'] ?? null;
            $categorie = $_GET['categorie'] ?? null;
            $search = trim($_GET['search'] ?? '');

            $sql = "SELECT p.*,
                           e.titre AS event_titre,
                           CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom,
                           u.username AS auteur_username
                    FROM blog_posts p
                    LEFT JOIN evenement e ON e.idEvenement = p.event_id
                    LEFT JOIN utilisateurs u ON u.id = p.auteur_id
                    WHERE 1 = 1";
            $params = [];

            if ($statut) {
                $sql .= " AND p.statut = ?";
                $params[] = $statut;
            } else {
                $sql .= " AND p.statut = 'publie'";
            }

            if ($categorie && $categorie !== 'tous') {
                $sql .= " AND p.categorie = ?";
                $params[] = $categorie;
            }

            if ($search !== '') {
                $sql .= " AND (p.titre LIKE ? OR p.resume LIKE ? OR p.contenu LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY COALESCE(p.date_publication, p.date_creation) DESC LIMIT 100";

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $posts = $stmt->fetchAll();

            header('Content-Type: application/json');
            echo json_encode(['posts' => $posts]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getPost($id) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->prepare("UPDATE blog_posts SET vues = vues + 1 WHERE id = ?")->execute([$id]);

            $post = $this->fetchPostById((int) $id);
            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'Post non trouve']);
                return;
            }

            $commentsStmt = $db->prepare("SELECT * FROM blog_commentaires WHERE post_id = ? AND approuve = 1 ORDER BY date_creation DESC");
            $commentsStmt->execute([$id]);

            header('Content-Type: application/json');
            echo json_encode([
                'post' => $post,
                'comments' => $commentsStmt->fetchAll(),
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function fetchAdminPosts(): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*,
                       e.titre AS event_titre,
                       e.date AS event_date,
                       u.username AS auteur_username,
                       u.email AS auteur_email,
                       CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom
                FROM blog_posts p
                LEFT JOIN evenement e ON e.idEvenement = p.event_id
                LEFT JOIN utilisateurs u ON u.id = p.auteur_id
                ORDER BY p.date_creation DESC
                LIMIT 50";
        return $db->query($sql)->fetchAll();
    }

    private function fetchPostById(int $id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT p.*,
                                     e.titre AS event_titre,
                                     e.date AS event_date,
                                     e.lieu AS event_lieu,
                                     u.username AS auteur_username,
                                     u.email AS auteur_email,
                                     CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom
                              FROM blog_posts p
                              LEFT JOIN evenement e ON e.idEvenement = p.event_id
                              LEFT JOIN utilisateurs u ON u.id = p.auteur_id
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function fetchAuthenticatedUser(): ?array
    {
        $userId = $this->resolveCurrentAuthorId();
        if ($userId === null) {
            return null;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, role
                              FROM utilisateurs
                              WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    private function fetchBlogEvents(): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT idEvenement, titre, date, lieu
                FROM evenement
                ORDER BY date DESC, titre ASC";
        return $db->query($sql)->fetchAll();
    }

    private function resolveCurrentAuthorId(): ?int
    {
        $sessionKeys = ['user_id', 'utilisateur_id'];
        foreach ($sessionKeys as $key) {
            if (isset($_SESSION[$key]) && ctype_digit((string) $_SESSION[$key])) {
                return (int) $_SESSION[$key];
            }
        }

        return null;
    }

    private function requireAuthenticatedUser(): array
    {
        $user = $this->fetchAuthenticatedUser();
        if (!$user) {
            $this->redirect('../../views/frontoffice/signin.php');
        }

        return $user;
    }

    private function normalizeOptionalInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function traiterUploadImage($file) {
        if (!$file || empty($file['name'])) {
            return null;
        }

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $extensions, true)) {
            throw new Exception('Format d\'image non autorise');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Image trop grande (max 5MB)');
        }

        if (!getimagesize($file['tmp_name'])) {
            throw new Exception('Fichier invalide');
        }

        $nom = uniqid('blog_', true) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $nom)) {
            return $nom;
        }

        throw new Exception('Erreur lors du telechargement');
    }

    private function genererSlug($texte) {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte);
        if ($slug === false) {
            $slug = $texte;
        }

        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim((string) $slug, '-');
    }
}
?>
