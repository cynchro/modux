<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Support\Container;
use App\Support\Config;
use App\Support\Router;
use App\Support\Kernel;
use App\Support\Pipeline;
use App\Support\Request;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\RequestLoggerMiddleware;
use App\Support\Request as AppRequest;

abstract class FeatureTestCase extends TestCase
{
    protected Container $app;

    protected function setUp(): void
    {
        parent::setUp();

        Config::setPath(dirname(__DIR__, 2) . '/config');

        $this->app = new Container();

        // Minimal services for feature tests
        $this->app->singleton(\App\Support\Logger::class, fn () =>
            new \App\Support\Logger(Config::all('logging')));

        \App\Exceptions\Handler::register($this->app->get(\App\Support\Logger::class));

        $this->app->singleton(\App\Support\Router::class, fn ($c) =>
            new Router($c));

        // Load module routes
        $router = $this->app->get(Router::class);
        foreach (glob(dirname(__DIR__, 2) . '/app/Modules/*/routes.php') as $f) {
            require $f;
        }
    }

    protected function request(string $method, string $uri, array $body = [], array $headers = []): array
    {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI']    = $uri;
        $_SERVER['CONTENT_TYPE']   = 'application/json';

        foreach ($headers as $k => $v) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $k))] = $v;
        }

        // Feed body through php://input simulation via a stream wrapper
        $this->setInputStream(json_encode($body));

        $request  = new Request();
        $router   = $this->app->get(Router::class);
        $pipeline = new Pipeline();

        ob_start();
        try {
            $response = $router->dispatch($request, $pipeline);
            $response->send();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $output = ob_get_clean();
        return json_decode((string) $output, true) ?? [];
    }

    protected function get(string $uri, array $headers = []): array
    {
        return $this->request('GET', $uri, [], $headers);
    }

    protected function post(string $uri, array $body = [], array $headers = []): array
    {
        return $this->request('POST', $uri, $body, $headers);
    }

    protected function authHeader(string $token): array
    {
        return ['Authorization' => "Bearer {$token}"];
    }

    protected function tearDown(): void
    {
        AppRequest::setTestInputStream(null);
        $_SERVER = [];
        $_POST   = [];
        $_GET    = [];
        parent::tearDown();
    }

    private function setInputStream(string $content): void
    {
        AppRequest::setTestInputStream($content);
        $_POST = [];
    }
}
