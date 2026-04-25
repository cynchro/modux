<?php

namespace Tests\Unit\Support;

use App\Support\Router;
use App\Support\Container;
use App\Support\Request;
use App\Support\Response;
use App\Support\Pipeline;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\AdminMiddleware;
use Tests\Unit\UnitTestCase;

class RouterGroupTest extends UnitTestCase
{
    private Router    $router;
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->instance(self::class, $this);
        $this->router = new Router($this->container);
    }

    public function test_group_applies_middleware_to_all_routes(): void
    {
        $this->router->group([AuthMiddleware::class], function ($router) {
            $router->get('/a', [self::class, 'pingAction']);
            $router->get('/b', [self::class, 'pingAction']);
        });

        $routes = $this->router->getRegisteredRoutes();

        $this->assertCount(2, $routes);
        $this->assertContains(AuthMiddleware::class, $routes[0]['middlewares']);
        $this->assertContains(AuthMiddleware::class, $routes[1]['middlewares']);
    }

    public function test_nested_groups_merge_middlewares(): void
    {
        $this->router->group([AuthMiddleware::class], function ($router) {
            $router->group([AdminMiddleware::class], function ($router) {
                $router->get('/admin', [self::class, 'pingAction']);
            });
        });

        $routes = $this->router->getRegisteredRoutes();

        $this->assertContains(AuthMiddleware::class, $routes[0]['middlewares']);
        $this->assertContains(AdminMiddleware::class, $routes[0]['middlewares']);
    }

    public function test_routes_outside_group_have_no_middleware(): void
    {
        $this->router->get('/public', [self::class, 'pingAction']);

        $this->router->group([AuthMiddleware::class], function ($router) {
            $router->get('/private', [self::class, 'pingAction']);
        });

        $routes = $this->router->getRegisteredRoutes();

        $this->assertEmpty($routes[0]['middlewares']);
        $this->assertNotEmpty($routes[1]['middlewares']);
    }

    public function test_group_does_not_leak_middleware_after_callback(): void
    {
        $this->router->group([AuthMiddleware::class], function ($router) {
            $router->get('/inside', [self::class, 'pingAction']);
        });

        $this->router->get('/outside', [self::class, 'pingAction']);

        $routes = $this->router->getRegisteredRoutes();

        $this->assertEmpty($routes[1]['middlewares']);
    }

    public function pingAction(Request $request): Response
    {
        return Response::success([]);
    }
}
