<?php
header("Access-Control-Allow-Origin: *"); // Puedes cambiar '*' por el dominio de tu frontend si prefieres restringirlo.
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true"); // Si necesitas enviar cookies o autenticación

// Si es una solicitud OPTIONS (preflight request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(); // Termina aquí el script para que no procese nada más
}
require '../config/bootstrap.php';
