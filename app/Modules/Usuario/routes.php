<?php

use App\Modules\Usuario\Controllers\UsuarioController;

$router->get('/usuarios', [UsuarioController::class, 'index'], true);
$router->get('/usuarios/{id}', [UsuarioController::class, 'show'], true);
$router->post('/usuarios', [UsuarioController::class, 'create'], true); 
$router->put('/usuarios/{id}', [UsuarioController::class, 'update'], true);
$router->put('/usuarios/sucursal/{id}', [UsuarioController::class, 'updateSucursal'], true);
$router->delete('/usuarios/{id}', [UsuarioController::class, 'delete'], true); 

