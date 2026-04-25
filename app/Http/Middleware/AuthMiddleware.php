<?php

namespace App\Http\Middleware;

use App\Support\JWTConfig;
use App\Support\Request;
use App\Support\Response;
use App\Support\Contracts\MiddlewareInterface;
use App\Exceptions\AuthException;

class AuthMiddleware implements MiddlewareInterface
{
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

        $request->setUser($payload);

        return $next($request);
    }
}
