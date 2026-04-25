<?php

namespace App\Http\Middleware;

use PDO;
use App\Support\JWTConfig;
use App\Support\Request;
use App\Support\Response;
use App\Support\Contracts\MiddlewareInterface;
use App\Exceptions\AuthException;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new AuthException('Token not provided.');
        }

        $payload = JWTConfig::decodeToken($token);

        if (!$payload) {
            throw new AuthException('Invalid or expired token.');
        }

        $stmt = $this->pdo->prepare('SELECT id FROM usuarios WHERE token = ?');
        $stmt->execute([$token]);

        if (!$stmt->fetch()) {
            throw new AuthException('Token has been revoked.');
        }

        $request->setUser($payload);

        return $next($request);
    }
}
