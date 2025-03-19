<?php

namespace App\Helpers;

use App\Helpers\UserDataHelper;

class LogHelper
{
    private static $logFile = __DIR__ . '/../../public/storage/logs/app.log';  // Ruta del archivo de log

    /**
     * Escribe un mensaje de log en el archivo especificado
     *
     * @param string $message  El mensaje de log
     * @param string $level    Nivel del log (INFO, ERROR, WARNING, etc.)
     */
    public static function log(object $e, string $level = 'INFO')
    {
        $user = UserDataHelper::getUserData();
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
        $timestamp = date('Y-m-d H:i:s'); 

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'Desconocido';
        $url = $protocol . $host . ($_SERVER['REQUEST_URI'] ?? '/');

        $message = sprintf(
            "[%s] Error: %s\nCódigo: %d\nArchivo: %s\nLínea: %d\n\nURL: %s\n\nStack Trace:\n%s\n\nInput Data:\n%s\n\nUser ID: %s\nIP Address: %s",
            strtoupper('info'),
            $e->getMessage(),
            $e->getCode(),
            $e->getFile(),
            $e->getLine(),
            $url,
            $e->getTraceAsString(),
            file_get_contents('php://input'),
            $user['usuario_id'] ?? 'N/A',
            $ipAddress
        );

        $formattedMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        // Escribir en el archivo de log
        file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);
    }

    /**
     * Log de nivel INFO
     *
     * @param object $e
     */
    public static function info(object $e)
    {
        self::log($e, 'INFO');
    }

    /**
     * Log de nivel ERROR
     *
     * @param object $e
     */
    public static function error(object $e)
    {
        self::log($e, 'ERROR');
    }

    /**
     * Log de nivel WARNING
     *
     * @param object $e
     */
    public static function warning(object $e)
    {
        self::log($e, 'WARNING');
    }

    /**
     * Log de nivel DEBUG
     *
     * @param object $e
     */
    public static function debug(object $e)
    {
        self::log($e, 'DEBUG');
    }
}

