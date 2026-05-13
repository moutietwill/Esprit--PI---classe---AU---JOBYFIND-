<?php

class Router
{
    private array $routes = [
        '' => ['controller' => 'EventsController', 'action' => 'index'],
        'events' => ['controller' => 'EventsController', 'action' => 'index'],
        'admin' => ['controller' => 'AdminController', 'action' => 'index'],
    ];

    public function dispatch(string $requestUri, string $scriptName): void
    {
        $path = $this->extractPath($requestUri, $scriptName);
        $segments = $path === '' ? [] : array_values(array_filter(explode('/', $path)));

        $routeKey = strtolower($segments[0] ?? '');
        $route = $this->routes[$routeKey] ?? $this->routes[''];

        $controllerName = $route['controller'];
        $action = $segments[1] ?? $route['action'];
        $params = array_slice($segments, $routeKey === '' ? 0 : 2);

        $this->invoke($controllerName, $action, $params);
    }

    private function extractPath(string $requestUri, string $scriptName): string
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '';

        // Strip the base directory (e.g. /projetweb_avec_evenements_comp/public)
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
