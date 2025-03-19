<?php

namespace App\Modules\Admin\Middleware;

use App\Support\JWTConfig;

class AdminMiddleware
{
    public function handle($request, $next)
    {
        // Obtener el token del encabezado o de la sesión
        $token = $request->getHeader('Authorization')[0] ?? $_SESSION['token'] ?? null;

        if (!$token) {
            header('HTTP/1.0 401 Unauthorized');
            echo 'Token no proporcionado';
            exit();
        }

        // Decodificar el token
        $decoded = JWTConfig::decodeToken($token);

        if (!$decoded) {
            header('HTTP/1.0 401 Unauthorized');
            echo 'Token inválido';
            exit();
        }

        // Verificar el rol
        if ($decoded['role'] !== 'admin') {
            header('HTTP/1.0 403 Forbidden');
            echo 'Acceso denegado: Se requiere rol de administrador';
            exit();
        }

        // Si el rol es "admin", continuar con la solicitud
        return $next($request);
    }
}