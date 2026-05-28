<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    private array $routes = [];

    public function get(string $path, Closure|array $handler, array $middleware = []): void
    {
        $this->map('GET', $path, $handler, $middleware);
    }

    public function post(string $path, Closure|array $handler, array $middleware = []): void
    {
        $this->map('POST', $path, $handler, $middleware);
    }

    public function map(string $method, string $path, Closure|array $handler, array $middleware = []): void
    {
        $normalizedPath = '/' . trim($path, '/');
        $paramNames = [];
        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $matches) use (&$paramNames): string {
            $paramNames[] = $matches[1];

            return '([^/]+)';
        }, $normalizedPath);

        $this->routes[$method][] = [
            'path' => $normalizedPath,
            'regex' => '#^' . $pattern . '$#',
            'params' => $paramNames,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();
        $route = null;
        $routeParams = [];

        foreach ($this->routes[$method] ?? [] as $candidate) {
            if (preg_match($candidate['regex'], $path, $matches) !== 1) {
                continue;
            }

            array_shift($matches);
            $route = $candidate;
            $routeParams = array_combine($candidate['params'], $matches) ?: [];
            break;
        }

        if ($route === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Pagina nao encontrada'], 'layouts/public');
            return;
        }

        $request->setRouteParams($routeParams);

        foreach ($route['middleware'] as $middlewareClass) {
            (new $middlewareClass())->handle($request);
        }

        $handler = $route['handler'];

        if ($handler instanceof Closure) {
            $handler($request);
            return;
        }

        [$controllerClass, $methodName] = $handler;
        $controller = new $controllerClass();
        $controller->{$methodName}($request);
    }
}
