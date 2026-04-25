<?php

namespace App\Modules\Usuario;

use App\Support\ServiceProvider;
use App\Modules\Usuario\Repositories\UsuariosRepository;
use App\Modules\Usuario\Services\UsuariosService;
use App\Modules\Usuario\Controllers\UsuarioController;

class UsuarioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(UsuariosRepository::class, fn ($c) =>
            new UsuariosRepository($c->get(\PDO::class))
        );

        $this->container->singleton(UsuariosService::class, fn ($c) =>
            new UsuariosService($c->get(UsuariosRepository::class))
        );

        $this->container->singleton(UsuarioController::class, fn ($c) =>
            new UsuarioController($c->get(UsuariosService::class))
        );
    }
}
