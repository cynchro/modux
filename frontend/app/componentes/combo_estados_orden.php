<?php
require_once __DIR__.'/../config/AuthRouter.php';

$router = new AuthRouter();

$token = "";
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}
$data = $router->getRequest('estados_orden_trabajo?paginate=false', $token);

if ($data['success'] && isset($data['response'])) {
    $results = $data['response'];
    $seleccion = isset($idEstados) ? $idEstados : 0;
    echo '<option value="">Seleccione una opción</option>'; // Opción inicial
    foreach ($results as $item) {

        if (isset($item['id']) && isset($item['nombre'])) {
            echo '<option value="' . htmlspecialchars($item['id']) . '"' .  ($seleccion == $item['id'] ? 'selected' : '') . '>' . htmlspecialchars($item['nombre']) . '</option>';
        }
    }
}
