<?php

use App\Modules\Turno\Controllers\TurnoController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\TenantMiddleware;
use App\Http\Middleware\PermissionMiddleware;

/** @var \App\Support\Router $router (inyectado por bootstrap/app.php al cargar las rutas) */
$router->group([AuthMiddleware::class, TenantMiddleware::class], function ($router) {
    $read  = PermissionMiddleware::class . ':turnos';
    $write = PermissionMiddleware::class . ':turnos:write';

    $router->get('/turnos', [TurnoController::class, 'index'], [$read]);
    $router->get('/turnos/{id}', [TurnoController::class, 'show'], [$read]);
    $router->post('/turnos', [TurnoController::class, 'create'], [$write]);
    $router->put('/turnos/{id}', [TurnoController::class, 'update'], [$write]);
    $router->delete('/turnos/{id}', [TurnoController::class, 'delete'], [$write]);
});
