<?php

namespace App\Http\Middleware;

use App\Support\Request;
use App\Support\Response;
use App\Support\Contracts\MiddlewareInterface;
use App\Exceptions\ForbiddenException;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $user = $request->user();

        if (!$user || ((int) ($user['rol'] ?? 0)) !== 1) {
            throw new ForbiddenException('Admin access required.');
        }

        return $next($request);
    }
}
