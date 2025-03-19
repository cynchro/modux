<?php
require_once __DIR__.'/../config/AuthRouter.php';

function obtenerlocalidades($idLocalidad = null)
{
    $router = new AuthRouter();

    $token = isset($_COOKIE['auth_token']) ? $_COOKIE['auth_token'] : '';

    $data = $router->getRequest('tipo_enmarcacion', $token);

    if ($data['success'] && isset($data['response']['results'])) {
        $results = $data['response']['results'];
        $seleccion = $idLocalidad ?? 0;

        $opciones = '<option value="">Seleccione una opción</option>';

        foreach ($results as $item) {
            if (isset($item['id']) && isset($item['nombre'])) {
                $opciones .= '<option value="' . htmlspecialchars($item['id']) . '"' . 
                    ($seleccion == $item['id'] ? ' selected' : '') . '>' . 
                    htmlspecialchars($item['nombre']) . '</option>';
            }
        }

        return $opciones;
    }

    return '<option value="">No hay opciones disponibles</option>';
}
