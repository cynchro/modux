<?php

namespace App\Modules\Admin;

use App\Support\ServiceProvider;
use App\Modules\Admin\Repositories\RolRepository;
use App\Modules\Admin\Repositories\PermisosRepository;
use App\Modules\Admin\Services\RolService;
use App\Modules\Admin\Services\PermisosService;
use App\Modules\Admin\Services\LogService;
use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Admin\Controllers\LogsController;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Usuario\Services\UsuariosService;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(RolRepository::class, fn ($c) =>
            new RolRepository($c->get(\PDO::class)));

        $this->container->singleton(PermisosRepository::class, fn ($c) =>
            new PermisosRepository($c->get(\PDO::class)));

        $this->container->singleton(RolService::class, fn ($c) =>
            new RolService($c->get(RolRepository::class)));

        $this->container->singleton(PermisosService::class, fn ($c) =>
            new PermisosService($c->get(PermisosRepository::class)));

        $this->container->singleton(LogService::class, fn () =>
            new LogService());

        $this->container->singleton(AdminController::class, fn ($c) =>
            new AdminController(
                $c->get(RolService::class),
                $c->get(PermisosService::class),
                $c->get(AuthService::class),
                $c->get(UsuariosService::class)
            ));

        $this->container->singleton(LogsController::class, fn ($c) =>
            new LogsController($c->get(LogService::class)));
    }

    public function boot(): void
    {
        $router = $this->container->get(\App\Support\Router::class);
        require __DIR__ . '/routes.php';
    }
}
