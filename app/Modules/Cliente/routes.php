<?php

use App\Modules\Cliente\Controllers\ClienteController;
use App\Http\Middleware\AuthMiddleware;

$router->get('/clientes',       [ClienteController::class, 'index'],  [AuthMiddleware::class]);
$router->get('/clientes/{id}',  [ClienteController::class, 'show'],   [AuthMiddleware::class]);
$router->post('/clientes',      [ClienteController::class, 'create'], [AuthMiddleware::class]);
$router->put('/clientes/{id}',  [ClienteController::class, 'update'], [AuthMiddleware::class]);
$router->delete('/clientes/{id}', [ClienteController::class, 'delete'], [AuthMiddleware::class]);