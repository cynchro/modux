<?php

use App\Modules\Usuario\Controllers\UsuarioController;
use App\Http\Middleware\AuthMiddleware;

$router->group([AuthMiddleware::class], function ($router) {
    $router->get('/usuarios',               [UsuarioController::class, 'index']);
    $router->get('/usuarios/{id}',          [UsuarioController::class, 'show']);
    $router->post('/usuarios',              [UsuarioController::class, 'create']);
    $router->put('/usuarios/{id}',          [UsuarioController::class, 'update']);
    $router->put('/usuarios/sucursal/{id}', [UsuarioController::class, 'updateSucursal']);
    $router->delete('/usuarios/{id}',       [UsuarioController::class, 'delete']);
});
