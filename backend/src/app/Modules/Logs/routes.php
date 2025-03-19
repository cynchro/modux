<?php

use App\Modules\Logs\Controllers\LogsController;

$router->get('/logs', [LogsController::class, 'index']);
$router->get('/logs/show/{id}', [LogsController::class, 'show']);
$router->post('/logs', [LogsController::class, 'delete']);
// $router->post('/logs', [LogsController::class, 'deleteAll']);

