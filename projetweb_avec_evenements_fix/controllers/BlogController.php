<?php
/**
 * BlogController - Frontend du blog
 * Affiche les articles publies, leurs auteurs, leurs evenements lies,
 * et expose les utilitaires AJAX du module blog d'origine.
 */

class BlogController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $search = trim((string) ($_GET['search'] ?? ''));
        $categorie = trim((string) ($_GET['categorie'] ?? ''));
        $perPage = 9;

        $this->ensureBlogTables();

        try {
            $db = Database::getInstance()->getConnection();
            $categories = $db->query("SELECT * FROM blog_categories ORDER BY nom")->fetchAll();

            $countSql = "SELECT COUNT(*) FROM blog_posts p WHERE p.statut = 'publie'";
            $listSql = "SELECT p.*,
                               e.titre AS event_titre,
                               e.date AS event_date,
                               e.lieu AS event_lieu,
                               u.username AS auteur_username,
                               u.email AS auteur_email,
                               CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom
                        FROM blog_posts p
                        LEFT JOIN evenement e ON e.idEvenement = p.event_id
                        LEFT JOIN utilisateurs u ON u.id = p.auteur_id
                        WHERE p.statut = 'publie'";
            $where = [];
            $params = [];

            if ($search !== '') {
                $where[] = "(p.titre LIKE ? OR p.resume LIKE ? OR p.contenu LIKE ?)";
                $searchTerm = '%' . $search . '%';
                array_push($params, $searchTerm, $searchTerm, $searchTerm);
            }

            if ($categorie !== '') {
                $where[] = "p.categorie = ?";
                $params[] = $categorie;
            }

            if (!empty($where)) {
                $suffix = ' AND ' . implode(' AND ', $where);
                $countSql .= $suffix;
                $listSql .= $suffix;
            }

            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $totalPages = max(1, (int) ceil($total / $perPage));
            $page = min($page, $totalPages);

            $offset = ($page - 1) * $perPage;
            $listSql .= " ORDER BY COALESCE(p.date_publication, p.date_creation) DESC LIMIT ? OFFSET ?";
            $listParams = $params;
            $listParams[] = $perPage;
            $listParams[] = $offset;

            $listStmt = $db->prepare($listSql);
            $listStmt->execute($listParams);
            $posts = $listStmt->fetchAll();

            $featuredEvent = $this->fetchFeaturedEvent();
            $activeStories = class_exists('StoryController') ? (new StoryController())->GetActiveStories(8) : [];

            $this->render('blog/index', [
                'posts' => $posts,
                'categories' => $categories,
                'page' => $page,
                'search' => $search,
                'categorie' => $categorie,
                'total' => $total,
                'totalPages' => $totalPages,
                'featuredEvent' => $featuredEvent,
                'activeStories' => $activeStories,
                'currentUser' => $this->fetchAuthenticatedUser(),
            ]);
        } catch (Exception $e) {
            error_log('Erreur blog index: ' . $e->getMessage());
            $this->render('blog/index', [
                'posts' => [],
                'categories' => [],
                'page' => 1,
                'search' => $search,
                'categorie' => $categorie,
                'total' => 0,
                'totalPages' => 1,
                'featuredEvent' => null,
                'activeStories' => [],
                'currentUser' => $this->fetchAuthenticatedUser(),
            ]);
        }
    }

    public function post($id = '') {
        $postId = (int) $id;
        if ($postId <= 0) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->ensureBlogTables();

        try {
            $db = Database::getInstance()->getConnection();
            $db->prepare("UPDATE blog_posts SET vues = vues + 1 WHERE id = ? AND statut = 'publie'")->execute([$postId]);

            $post = $this->RecupererPost($postId);
            if (!$post || $post['statut'] !== 'publie') {
                http_response_code(404);
                $this->render('errors/404');
                return;
            }

            $relatedPostsStmt = $db->prepare("SELECT id, titre
                                              FROM blog_posts
                                              WHERE statut = 'publie' AND id <> ? AND (
                                                  (event_id IS NOT NULL AND event_id = ?)
                                                  OR (categorie IS NOT NULL AND categorie = ?)
                                              )
                                              ORDER BY COALESCE(date_publication, date_creation) DESC
                                              LIMIT 3");
            $relatedPostsStmt->execute([
                $postId,
                $post['event_id'],
                $post['categorie'],
            ]);

            $this->render('blog/post', [
                'post' => $post,
                'comments' => $this->GetCommentsByPost($postId),
                'relatedPosts' => $relatedPostsStmt->fetchAll(),
                'currentUser' => $this->fetchAuthenticatedUser(),
            ]);
        } catch (Exception $e) {
            error_log('Erreur blog post: ' . $e->getMessage());
            $this->render('errors/500');
        }
    }

    public function category($categorie = '') {
        $_GET['categorie'] = $categorie;
        $this->index();
    }

    public function search($search = '') {
        $_GET['search'] = urldecode($search);
        $this->index();
    }

    public function comment($id = '') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/blog/post/' . (int) $id);
        }

        $postId = (int) $id;
        if ($postId <= 0) {
            $this->redirect('/blog');
        }

        try {
            $contenu = trim((string) ($_POST['contenu'] ?? ''));
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $this->AddComment($postId, $contenu, $nom);
            $this->redirect('/blog/post/' . $postId . '?success=comment');
        } catch (Exception $e) {
            error_log('Erreur ajout commentaire blog: ' . $e->getMessage());
            $this->redirect('/blog/post/' . $postId . '?error=comment');
        }
    }

    public function AfficherPosts($status = null) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*,
                       e.titre AS event_titre,
                       e.date AS event_date,
                       e.lieu AS event_lieu,
                       u.username AS auteur_username,
                       u.email AS auteur_email,
                       CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom
                FROM blog_posts p
                LEFT JOIN evenement e ON e.idEvenement = p.event_id
                LEFT JOIN utilisateurs u ON u.id = p.auteur_id";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE p.statut = ?";
            $params[] = $this->normalizeStatus($status);
        }

        $sql .= " ORDER BY COALESCE(p.date_publication, p.date_creation) DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function AfficherPublies() {
        return $this->AfficherPosts('published');
    }

    public function RecupererPost($id) {
        $this->ensureBlogTables();
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
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    public function RecherchePost($keyword) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $keyword = '%' . trim((string) $keyword) . '%';
        $stmt = $db->prepare("SELECT p.*,
                                     e.titre AS event_titre,
                                     u.username AS auteur_username,
                                     u.email AS auteur_email,
                                     CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom
                              FROM blog_posts p
                              LEFT JOIN evenement e ON e.idEvenement = p.event_id
                              LEFT JOIN utilisateurs u ON u.id = p.auteur_id
                              WHERE p.statut = 'publie'
                                AND (p.titre LIKE ? OR p.resume LIKE ? OR p.contenu LIKE ?)
                              ORDER BY COALESCE(p.date_publication, p.date_creation) DESC");
        $stmt->execute([$keyword, $keyword, $keyword]);
        return $stmt->fetchAll();
    }

    public function Pagination($page = 1, $itemsPerPage = 9) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $page = max(1, (int) $page);
        $itemsPerPage = max(1, (int) $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $stmt = $db->prepare("SELECT p.*,
                                     e.titre AS event_titre,
                                     u.username AS auteur_username,
                                     u.email AS auteur_email,
                                     CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom
                              FROM blog_posts p
                              LEFT JOIN evenement e ON e.idEvenement = p.event_id
                              LEFT JOIN utilisateurs u ON u.id = p.auteur_id
                              WHERE p.statut = 'publie'
                              ORDER BY COALESCE(p.date_publication, p.date_creation) DESC
                              LIMIT ? OFFSET ?");
        $stmt->execute([$itemsPerPage, $offset]);
        return $stmt->fetchAll();
    }

    public function NombreDesPosts() {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        return (int) $db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    }

    public function GetCommentsByPost($postId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT c.*,
                                     u.username AS auteur_username,
                                     CONCAT_WS(' ', u.first_name, u.last_name) AS auteur_nom
                              FROM blog_commentaires c
                              LEFT JOIN utilisateurs u ON u.id = c.auteur_id
                              WHERE c.post_id = ? AND c.approuve = 1
                              ORDER BY c.date_creation ASC");
        $stmt->execute([(int) $postId]);
        return $stmt->fetchAll();
    }

    public function AddComment($postId, $content, $userName = 'Utilisateur') {
        $this->ensureBlogTables();
        $content = trim((string) $content);
        if ($content === '') {
            throw new Exception('Le commentaire ne peut pas etre vide.');
        }
        if (mb_strlen($content) > 500) {
            throw new Exception('Le commentaire est trop long.');
        }

        $auteurId = $this->resolveCommentAuthorId();
        $user = $this->fetchAuthenticatedUser();
        $email = '';
        $nom = trim((string) $userName);

        if ($user) {
            $resolvedName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $nom = $resolvedName !== '' ? $resolvedName : (($user['username'] ?? '') !== '' ? $user['username'] : $nom);
            $email = (string) ($user['email'] ?? '');
        }

        if ($nom === '') {
            $nom = 'Anonyme';
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO blog_commentaires (post_id, auteur_id, nom, email, contenu, approuve)
                              VALUES (?, ?, ?, ?, ?, 1)");
        return $stmt->execute([(int) $postId, $auteurId, $nom, $email, $content]);
    }

    public function IncrementPostView($postId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE blog_posts SET vues = vues + 1 WHERE id = ?");
        $stmt->execute([(int) $postId]);
        return $this->GetPostViewCount($postId);
    }

    public function GetPostViewCount($postId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COALESCE(vues, 0) FROM blog_posts WHERE id = ?");
        $stmt->execute([(int) $postId]);
        return (int) $stmt->fetchColumn();
    }

    public function GetLikesCount($postId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM blog_likes WHERE post_id = ?");
        $stmt->execute([(int) $postId]);
        return (int) $stmt->fetchColumn();
    }

    public function HasLiked($postId, $userId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $userId = (int) $userId;
        $userIp = $this->getClientIp();

        if ($userId > 0) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM blog_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([(int) $postId, $userId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM blog_likes WHERE post_id = ? AND user_ip = ?");
            $stmt->execute([(int) $postId, $userIp]);
        }

        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function ToggleLike($postId, $userId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $postId = (int) $postId;
        $userId = (int) $userId;
        $userIp = $this->getClientIp();

        if ($this->HasLiked($postId, $userId)) {
            if ($userId > 0) {
                $stmt = $db->prepare("DELETE FROM blog_likes WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$postId, $userId]);
            } else {
                $stmt = $db->prepare("DELETE FROM blog_likes WHERE post_id = ? AND user_ip = ?");
                $stmt->execute([$postId, $userIp]);
            }
            return false;
        }

        $stmt = $db->prepare("INSERT INTO blog_likes (post_id, user_id, user_ip) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $userId > 0 ? $userId : null, $userIp]);
        return true;
    }

    public function AddRating($postId, $userId, $rating) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $postId = (int) $postId;
        $userId = (int) $userId;
        $rating = max(1, min(5, (int) $rating));
        $userIp = $this->getClientIp();

        $sql = "INSERT INTO blog_post_ratings (post_id, user_id, user_ip, rating)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), updated_at = CURRENT_TIMESTAMP";
        $stmt = $db->prepare($sql);
        $stmt->execute([$postId, $userId > 0 ? $userId : null, $userIp, $rating]);

        $statsStmt = $db->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
                                   FROM blog_post_ratings
                                   WHERE post_id = ?");
        $statsStmt->execute([$postId]);
        $stats = $statsStmt->fetch();

        return [
            'avg' => round((float) ($stats['avg_rating'] ?? 0), 1),
            'count' => (int) ($stats['total'] ?? 0),
        ];
    }

    public function GetUserRating($postId, $userId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $postId = (int) $postId;
        $userId = (int) $userId;
        $userIp = $this->getClientIp();

        if ($userId > 0) {
            $stmt = $db->prepare("SELECT rating FROM blog_post_ratings WHERE post_id = ? AND user_id = ? LIMIT 1");
            $stmt->execute([$postId, $userId]);
        } else {
            $stmt = $db->prepare("SELECT rating FROM blog_post_ratings WHERE post_id = ? AND user_ip = ? LIMIT 1");
            $stmt->execute([$postId, $userIp]);
        }

        $value = $stmt->fetchColumn();
        return $value !== false ? (int) $value : 0;
    }

    public function GetPostRating($postId) {
        $this->ensureBlogTables();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
                              FROM blog_post_ratings
                              WHERE post_id = ?");
        $stmt->execute([(int) $postId]);
        $stats = $stmt->fetch();
        return [
            'avg' => round((float) ($stats['avg_rating'] ?? 0), 1),
            'count' => (int) ($stats['total'] ?? 0),
        ];
    }

    private function resolveCommentAuthorId(): ?int
    {
        foreach (['user_id', 'utilisateur_id'] as $key) {
            if (isset($_SESSION[$key]) && ctype_digit((string) $_SESSION[$key])) {
                return (int) $_SESSION[$key];
            }
        }

        return null;
    }

    private function fetchAuthenticatedUser(): ?array
    {
        $userId = $this->resolveCommentAuthorId();
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

    private function fetchFeaturedEvent()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT idEvenement, titre, date, lieu
                            FROM evenement
                            WHERE date >= CURDATE()
                            ORDER BY date ASC
                            LIMIT 1");
        return $stmt->fetch();
    }

    private function ensureBlogTables(): void
    {
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
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

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
            KEY `post_id` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->exec("CREATE TABLE IF NOT EXISTS `blog_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nom` varchar(100) NOT NULL UNIQUE,
            `slug` varchar(100) UNIQUE,
            `description` text,
            `icone` varchar(50),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->exec("CREATE TABLE IF NOT EXISTS `blog_likes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `post_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `user_ip` varchar(45) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_blog_like_user` (`post_id`, `user_id`),
            UNIQUE KEY `unique_blog_like_ip` (`post_id`, `user_ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->exec("CREATE TABLE IF NOT EXISTS `blog_post_ratings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `post_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `user_ip` varchar(45) DEFAULT NULL,
            `rating` tinyint(1) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_blog_rating_user` (`post_id`, `user_id`),
            UNIQUE KEY `unique_blog_rating_ip` (`post_id`, `user_ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->ensureColumn($db, 'blog_posts', 'auteur_id', "ALTER TABLE blog_posts ADD COLUMN auteur_id int(11) DEFAULT NULL AFTER resume");
        $this->ensureColumn($db, 'blog_posts', 'event_id', "ALTER TABLE blog_posts ADD COLUMN event_id int(11) DEFAULT NULL AFTER image_couverture");
    }

    private function ensureColumn(PDO $db, string $table, string $column, string $sql): void
    {
        $stmt = $db->prepare("SHOW COLUMNS FROM {$table} LIKE :columnName");
        $stmt->execute([':columnName' => $column]);
        if (!$stmt->fetch()) {
            $db->exec($sql);
        }
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        if (in_array($status, ['publie', 'published'], true)) {
            return 'publie';
        }
        return 'brouillon';
    }

    private function getClientIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }
}
?>
