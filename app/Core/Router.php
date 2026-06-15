<?php
namespace App\Core;

final class Router
{
    private array $routes = [];
    public function get(string $pattern, callable|array $handler): void { $this->add('GET', $pattern, $handler); }
    public function post(string $pattern, callable|array $handler): void { $this->add('POST', $pattern, $handler); }
    private function add(string $method, string $pattern, callable|array $handler): void { $this->routes[] = compact('method','pattern','handler'); }

    public function dispatch(string $method, string $uri): mixed
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['pattern']);
            if (preg_match('#^'.$regex.'$#', $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                if (is_array($route['handler'])) {
                    [$class, $action] = $route['handler'];
                    return (new $class())->$action(...array_values($params));
                }
                return $route['handler'](...array_values($params));
            }
        }
        http_response_code(404);
        return view('partials.error', ['title' => 'Page not found', 'message' => 'The requested page does not exist.']);
    }
}
