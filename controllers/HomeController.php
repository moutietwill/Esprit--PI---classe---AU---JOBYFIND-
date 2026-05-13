<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/Controller.php';

/**
 * HomeController
 * Gère la page d'accueil et les vues publiques
 */
class HomeController extends Controller {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Page d'accueil
     */
    public function index() {
        try {
            $this->render('home', [
                'pageTitle' => 'JobyFind'
            ]);
        } catch (Exception $e) {
            error_log('Home Index Error: ' . $e->getMessage());
            $this->render('errors/500', ['error' => $e->getMessage()]);
        }
    }

    public function legacyBlog(...$segments) {
        $firstSegment = strtolower((string)($segments[0] ?? ''));

        if ($firstSegment === 'create') {
            $this->redirectRaw($this->legacyBlogUrl('view/backoffice.php?page=posts&action=add'));
        }

        if ($firstSegment === 'admin' || $firstSegment === 'backoffice' || $firstSegment === 'gestion') {
            $this->redirectRaw($this->legacyBlogUrl('view/backoffice.php'));
        }

        if ($firstSegment === 'edit' && !empty($segments[1])) {
            $this->redirectRaw($this->legacyBlogUrl('view/backoffice.php?page=posts&action=edit&id=' . urlencode((string)$segments[1])));
        }

        $target = $this->legacyBlogUrl('view/frontoffice.php');
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        if ($queryString !== '') {
            $target .= '?' . $queryString;
        }

        $this->redirectRaw($target);
    }

    public function login(): void
    {
        $this->redirectRaw($this->userUrl('frontoffice/signin.php'));
    }

    public function register(): void
    {
        $this->redirectRaw($this->userUrl('frontoffice/register.php'));
    }

    public function profile(): void
    {
        if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Admin', 'Tutor', 'Mentor'])) {
            $this->redirectRaw($this->userUrl('backoffice/admine.php'));
            return;
        }
        $this->redirectRaw($this->userUrl('frontoffice/profile.php'));
    }

    private function redirectRaw(string $target): void
    {
        header('Location: ' . $target);
        exit;
    }

    private function userUrl(string $path): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');

        if (basename($baseDir) === 'public') {
            $baseDir = rtrim(dirname($baseDir), '/');
        }

        $baseDir = ($baseDir && $baseDir !== '.' && $baseDir !== '/') ? $baseDir : '';
        return $baseDir . '/View/' . ltrim($path, '/');
    }

    /**
     * Page 404
     */
    public function notFound() {
        http_response_code(404);
        $this->render('errors/404');
    }
}
