<?php

namespace App\Support;

use App\Http\Middleware\AuthMiddleware;
use App\Exceptions\MethodNotAllowedException;
use App\Exceptions\NotFoundException;
use App\Support\Contracts\MiddlewareInterface;

class Router
{
    private array $routes = [];

    /** @var list<array<string, mixed>> */
    private array $registeredRoutes = [];

    /** @var list<class-string> */
    private array $groupMiddlewares = [];

    public function __construct(private Container $container)
    {
    }

    public function get(string $uri, array $action, bool|array $protected = false): void
    {
        $this->addRoute('GET', $uri, $action, $this->resolveMiddlewares($protected));
    }

    public function post(string $uri, array $action, bool|array $protected = false): void
    {
        $this->addRoute('POST', $uri, $action, $this->resolveMiddlewares($protected));
    }

    public function put(string $uri, array $action, bool|array $protected = false): void
    {
        $this->addRoute('PUT', $uri, $action, $this->resolveMiddlewares($protected));
    }

    public function patch(string $uri, array $action, bool|array $protected = false): void
    {
        $this->addRoute('PATCH', $uri, $action, $this->resolveMiddlewares($protected));
    }

    public function delete(string $uri, array $action, bool|array $protected = false): void
    {
        $this->addRoute('DELETE', $uri, $action, $this->resolveMiddlewares($protected));
    }

    /**
     * Group routes under shared middlewares.
     *
     * @param list<class-string> $middlewares
     */
    public function group(array $middlewares, callable $callback): void
    {
        $previous               = $this->groupMiddlewares;
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);
        $callback($this);
        $this->groupMiddlewares = $previous;
    }

    /** @return list<array<string, mixed>> */
    public function getRegisteredRoutes(): array
    {
        return $this->registeredRoutes;
    }

    /** @param bool|list<class-string> $protected
     *  @return list<class-string>
     */
    private function resolveMiddlewares(bool|array $protected): array
    {
        if ($protected === false) {
            return $this->groupMiddlewares;
        }

        if ($protected === true) {
            return array_unique(array_merge($this->groupMiddlewares, [AuthMiddleware::class]));
        }

        return array_unique(array_merge($this->groupMiddlewares, $protected));
    }

    /** @param array{0: class-string, 1: string} $action
     *  @param list<class-string>               $middlewares
     */
    private function addRoute(string $method, string $uri, array $action, array $middlewares): void
    {
        $normalized                          = rtrim($uri, '/');
        $pattern                             = $this->compilePattern($normalized);
        $this->routes[$method][$pattern]     = [
            'action'      => $action,
            'middlewares' => $middlewares,
        ];
        $this->registeredRoutes[] = [
            'method'      => $method,
            'uri'         => $uri ?: '/',
            'action'      => $action,
            'middlewares' => $middlewares,
        ];
    }

    private function compilePattern(string $uri): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request, Pipeline $globalPipeline): Response
    {
        $method = $request->method();
        $path   = rtrim($request->uri(), '/') ?: '/';

        foreach (($this->routes[$method] ?? []) as $pattern => $route) {
            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $request->setRouteParams($params);

            $pipeline = $globalPipeline;

            foreach ($route['middlewares'] as $middlewareClass) {
                /** @var MiddlewareInterface $middleware */
                $middleware = $this->container->get($middlewareClass);
                $pipeline   = $pipeline->pipe($middleware);
            }

            return $pipeline->run($request, function (Request $req) use ($route): Response {
                return $this->callAction($req, $route['action']);
            });
        }

        // Path exists for another method → 405 instead of 404
        $allowedMethods = [];
        foreach ($this->routes as $registeredMethod => $routes) {
            if ($registeredMethod === $method) {
                continue;
            }
            foreach (array_keys($routes) as $pattern) {
                if (preg_match($pattern, $path)) {
                    $allowedMethods[] = $registeredMethod;
                    break;
                }
            }
        }

        if (!empty($allowedMethods)) {
            throw new MethodNotAllowedException($allowedMethods);
        }

        throw new NotFoundException('Route', $method . ' ' . $path);
    }

    /** @param array{0: class-string, 1: string} $action */
    private function callAction(Request $request, array $action): Response
    {
        [$controllerClass, $method] = $action;

        $controller = $this->container->get($controllerClass);
        $reflector  = new \ReflectionMethod($controller, $method);
        $params     = [];

        foreach ($reflector->getParameters() as $param) {
            $type = $param->getType();

            if (!$type || !($type instanceof \ReflectionNamedType) || $type->isBuiltin()) {
                $params[] = $request->route($param->getName());
                continue;
            }

            $typeName = $type->getName();

            if ($typeName === Request::class || is_subclass_of($typeName, Request::class)) {
                $params[] = $request;
                continue;
            }

            if (is_subclass_of($typeName, FormRequest::class)) {
                $data     = array_merge($request->all(), $request->routeParams());
                $params[] = new $typeName($data);
                continue;
            }

            $params[] = $this->container->get($typeName);
        }

        return $reflector->invokeArgs($controller, $params);
    }
}
