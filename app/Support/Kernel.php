<?php

namespace App\Support;

use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\RequestLoggerMiddleware;

class Kernel
{
    private Pipeline $globalPipeline;

    public function __construct(private Container $container)
    {
        $this->globalPipeline = (new Pipeline())
            ->pipe($container->get(CorsMiddleware::class))
            ->pipe($container->get(SecurityHeadersMiddleware::class))
            ->pipe($container->get(RequestLoggerMiddleware::class));
    }

    public function handle(): void
    {
        $request = new Request();
        $router  = $this->container->get(Router::class);

        foreach (glob(dirname(__DIR__) . '/Modules/*/routes.php') as $routeFile) {
            require $routeFile;
        }

        $response = $router->dispatch($request, $this->globalPipeline);
        $response->send();
    }
}
