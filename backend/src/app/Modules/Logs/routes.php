<?php

use App\Modules\Logs\Controllers\LogsController;
use App\Http\Middleware\AuthMiddleware;

$router->group([AuthMiddleware::class], function ($router) {
    $router->get('/logs',             [LogsController::class, 'index']);
    $router->get('/logs/{id}',        [LogsController::class, 'show']);
    $router->post('/logs/delete-all', [LogsController::class, 'deleteAll']);
});
