<?php

namespace App\Helpers;

use App\Modules\Auth\Repositories\AuthRepository;

class UserDataHelper
{

    public static function getUserData()
    {
        // Obtener todos los encabezados HTTP
        $headers = getallheaders();

        // Verificar si el encabezado Authorization está presente
        if (isset($headers['Authorization'])) {
            // Extraer el token Bearer
            $authHeader = $headers['Authorization'];

            // Asegurarse de que el encabezado comience con "Bearer "
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $bearerToken = $matches[1];
                return AuthRepository::findUserByToken($bearerToken);
            } else {
                return "No se encontró un token válido en el encabezado Authorization.";
            }
        } else {
            return "Encabezado Authorization no presente.";
        }
    }
}
