<?php
require_once __DIR__.'/../config/AuthRouter.php';

$router = new AuthRouter();

$token = "";
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}


if(isset($idSucursal)){ $url= "tipo_enmarcacion?paginate=false&id_sucursal=".$idSucursal; }else{ $url= "tipo_enmarcacion?paginate=false"; }
    

$data = $router->getRequest($url, $token);

if ($data['success'] && isset($data['response'])) {
    $results = $data['response'];
    $seleccion = isset($idTipoEnmarcacion) ? $idTipoEnmarcacion : 0;

    echo '<option value="">Seleccione una opción</option>';

    foreach ($results as $item) {
        if (isset($item['id']) && isset($item['nombre'])) {
            echo '<option value="' . htmlspecialchars($item['id']) . '"' .  ($seleccion == $item['id'] ? 'selected' : '') . '>' . htmlspecialchars($item['nombre']) . '</option>';
        }
    }
}
