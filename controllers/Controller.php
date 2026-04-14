<?php
class Controller {
    protected function render($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!is_file($viewPath)) {
            http_response_code(404);
            echo "Vue '{$view}' introuvable.";
            return;
        }

        require $viewPath;
    }

    protected function redirect($url) {
        if (strpos($url, '/') === 0 && !preg_match('#^https?://#i', $url)) {
            $script = $_SERVER['SCRIPT_NAME'] ?? '';
            $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');
            $url = ($baseDir && $baseDir !== '.' ? $baseDir : '') . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    protected function baseUrl(string $path = ''): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        return ($baseDir && $baseDir !== '.' ? $baseDir : '') . $path;
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
?>
