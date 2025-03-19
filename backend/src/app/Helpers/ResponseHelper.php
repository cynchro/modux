<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success(array $data = [], int $statusCode = 200): void
    {
        self::sendResponse([
            'success' => true,
            'response' => $data,
        ], $statusCode);
    }

    public static function error(string $message = 'An error occurred', int $statusCode = 422): void
    {
        self::sendResponse([
            'success' => false,
            'error' => $message,
        ], $statusCode);
    }

    private static function sendResponse(array $body, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8'); // Especificar codificación UTF-8
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit; // Termina el script para evitar ejecución adicional
    }
}
