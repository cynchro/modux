<?php

namespace App\Modules\Tenant;

use App\Support\ServiceProvider;
use App\Support\Router;
use App\Modules\Tenant\Repositories\TenantRepository;
use App\Modules\Tenant\Services\TenantService;
use App\Modules\Tenant\Controllers\TenantController;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(TenantRepository::class, fn ($c) =>
            new TenantRepository($c->get(\PDO::class)));

        $this->container->singleton(TenantService::class, fn ($c) =>
            new TenantService($c->get(TenantRepository::class)));

        $this->container->singleton(TenantController::class, fn ($c) =>
            new TenantController($c->get(TenantService::class)));
    }

    public function boot(): void
    {
        $router = $this->container->get(Router::class);
        require __DIR__ . '/routes.php';
    }
}
