<?php

namespace App\Http\Middleware;

use App\Support\Config;
use App\Support\Request;
use App\Support\Response;
use App\Support\Contracts\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $origins     = Config::get('cors.allowed_origins', ['*']);
        $credentials = Config::get('cors.allow_credentials', false);
        $origin      = $request->header('Origin') ?? '';

        // Wildcard + credentials is forbidden by the CORS spec; browsers reject it.
        // When credentials are required, always reflect the specific allowed origin.
        if (in_array('*', $origins, true) && !$credentials) {
            header('Access-Control-Allow-Origin: *');
        } elseif ($origin !== '' && in_array($origin, $origins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', Config::get('cors.allowed_methods', [])));
        header('Access-Control-Allow-Headers: ' . implode(', ', Config::get('cors.allowed_headers', [])));

        if ($credentials) {
            header('Access-Control-Allow-Credentials: true');
        }

        if ($request->method() === 'OPTIONS') {
            return (new Response())->withStatus(204);
        }

        return $next($request);
    }
}
