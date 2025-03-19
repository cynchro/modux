<?php

require_once __DIR__.'/../config/AuthRouter.php';

$router = new AuthRouter();

$token = "";
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}
$data = $router->getRequest('sucursales', $token);

if ($data['success'] && isset($data['response']['results'])) {
    $results = $data['response']['results'];
    $seleccion = isset($idSucursal) ? $idSucursal : 0;
    //echo '<option value="">Seleccione una opción</option>'; // Opción inicial

    foreach ($results as $item) {
        if (isset($item['id']) && isset($item['nombre'])) {
            echo '<option value="' . htmlspecialchars($item['id']) . '"' .  ($seleccion == $item['id'] ? 'selected' : '') . '>' . htmlspecialchars($item['nombre']) . '</option>';
        }
    }
}
