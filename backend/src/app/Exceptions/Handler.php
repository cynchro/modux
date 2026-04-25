<?php

namespace App\Exceptions;

use App\Support\Logger;
use App\Support\Config;

class Handler
{
    public function __construct(private Logger $logger)
    {
    }

    public function handle(\Throwable $e): void
    {
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
        ]);

        $status = $this->resolveStatus($e);
        $body   = $this->resolveBody($e);

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function resolveStatus(\Throwable $e): int
    {
        if ($e instanceof AppException) {
            return $e->getHttpStatusCode();
        }
        return 500;
    }

    /** @return array<string, mixed> */
    private function resolveBody(\Throwable $e): array
    {
        if ($e instanceof AppException) {
            return $e->toArray();
        }

        $debug = Config::get('app.debug', false);

        return [
            'success' => false,
            'message' => $debug ? $e->getMessage() : 'An internal server error occurred.',
            'debug'   => $debug ? [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ] : null,
        ];
    }

    public static function register(Logger $logger): void
    {
        $instance = new self($logger);

        set_exception_handler([$instance, 'handle']);

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
