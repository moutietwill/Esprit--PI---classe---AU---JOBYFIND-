<?php

class Router
{
    private array $routes = [
        '' => ['controller' => 'HomeController', 'action' => 'index'],
        'home' => ['controller' => 'HomeController', 'action' => 'index'],
        'blog' => ['controller' => 'HomeController', 'action' => 'legacyBlog'],
        'login' => ['controller' => 'HomeController', 'action' => 'login'],
        'register' => ['controller' => 'HomeController', 'action' => 'register'],
        'profile' => ['controller' => 'HomeController', 'action' => 'profile'],
        'user' => ['controller' => 'HomeController', 'action' => 'login'],
        'events' => ['controller' => 'EventsController', 'action' => 'index'],
        'quiz' => ['controller' => 'QuizzController', 'action' => 'index'],
        'formation' => ['controller' => 'FormationController', 'action' => 'index'],
        'admin' => ['controller' => 'AdminController', 'action' => 'index'],
        'admin/formations' => ['controller' => 'AdminController', 'action' => 'formations'],
    ];

    public function dispatch(string $requestUri, string $scriptName): void
    {
        $path = $this->extractPath($requestUri, $scriptName);
        $segments = $path === '' ? [] : array_values(array_filter(explode('/', $path)));

        $routeKey = strtolower($segments[0] ?? '');
        $route = $this->routes[$routeKey] ?? $this->routes[''];

        $controllerName = $route['controller'];
        if ($routeKey === 'blog') {
            $action = $route['action'];
            $params = array_slice($segments, 1);
        } else {
            $action = $segments[1] ?? $route['action'];
            $params = array_slice($segments, $routeKey === '' ? 0 : 2);
        }

        if (str_contains($action, '-')) {
            $action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action))));
        }

        $this->invoke($controllerName, $action, $params);
    }

    private function extractPath(string $requestUri, string $scriptName): string
    {
        // Fallback for servers that don't support PATH_INFO or mod_rewrite correctly
        if (isset($_GET['url'])) {
            return trim($_GET['url'], '/');
        }

        $path = parse_url($requestUri, PHP_URL_PATH) ?? '';

        // Strip the base directory (e.g. /projetweb/public)
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseDir !== '' && $baseDir !== '.' && strpos($path, $baseDir) === 0) {
            $path = substr($path, strlen($baseDir));
        }

        $path = trim($path, '/');

        // Remove index.php prefix if present (when mod_rewrite is off)
        if (strpos($path, 'index.php') === 0) {
            $path = trim(substr($path, strlen('index.php')), '/');
        }

        // If mod_rewrite is disabled the URL still contains the literal "public"
        // as the first segment (e.g. public/admin/events). Strip it.
        if (strncasecmp($path, 'public/', 7) === 0) {
            $path = substr($path, 7);
        } elseif (strcasecmp($path, 'public') === 0) {
            $path = '';
        }

        return $path;
    }

    private function invoke(string $controllerName, string $action, array $params): void
    {
        if (!class_exists($controllerName)) {
            http_response_code(404);
            echo "Controller '{$controllerName}' introuvable.";
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo "Action '{$action}' introuvable.";
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }
}
