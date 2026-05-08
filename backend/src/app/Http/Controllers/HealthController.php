<?php

namespace App\Http\Controllers;

use App\Support\Request;
use App\Support\Response;

class HealthController
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function check(Request $request): Response
    {
        $db = $this->pingDatabase();

        return Response::success([
            'status' => $db ? 'ok' : 'degraded',
            'php'    => PHP_VERSION,
            'db'     => $db ? 'ok' : 'unreachable',
        ], $db ? 200 : 503);
    }

    private function pingDatabase(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
