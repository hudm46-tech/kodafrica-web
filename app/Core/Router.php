<?php
declare(strict_types=1);
namespace App\Core;

final class Router
{
    /** @var array<int, array{pattern:string, handler:callable|array, methods:array}> */
    private array $routes = [];

    public function get(string $path, $handler): void   { $this->add('GET', $path, $handler); }
    public function post(string $path, $handler): void  { $this->add('POST', $path, $handler); }
    public function any(string $path, $handler): void   { $this->add('*', $path, $handler); }

    private function add(string $method, string $path, $handler): void
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . rtrim($pattern, '/') . '/?$#';

        $this->routes[] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'methods' => $method === '*' ? ['GET','POST','PUT','DELETE','PATCH'] : [$method],
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = '/' . ltrim($path, '/');

        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'], true)) continue;

            if (preg_match($route['pattern'], $path, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (is_string($k)) {
                        // Auto-cast numeric params to int
                        $params[$k] = ctype_digit($v) ? (int)$v : $v;
                    }
                }

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $instance = new $class();
                    $instance->$methodName(...array_values($params));
                } else {
                    $handler(...array_values($params));
                }
                return;
            }
        }

        http_response_code(404);
        echo "<!doctype html><meta charset='utf-8'><title>404</title>"
            ."<style>body{background:#0A1929;color:#fff;font-family:system-ui;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}"
            ."h1{font-size:80px;margin:0;color:#00B4D8}p{color:#8892B0}</style>"
            ."<div style='text-align:center'><h1>404</h1><p>Page not found</p>"
            ."<a href='".url('/')."' style='color:#00B4D8;text-decoration:none;font-weight:600'>← Back to home</a></div>";
    }
}
