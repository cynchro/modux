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
        $origins    = Config::get('cors.allowed_origins', ['*']);
        $origin     = $request->header('Origin') ?? '';
        $allowed    = in_array('*', $origins, true) ? '*' : (in_array($origin, $origins, true) ? $origin : '');

        header("Access-Control-Allow-Origin: {$allowed}");
        header('Access-Control-Allow-Methods: ' . implode(', ', Config::get('cors.allowed_methods', [])));
        header('Access-Control-Allow-Headers: ' . implode(', ', Config::get('cors.allowed_headers', [])));

        if (Config::get('cors.allow_credentials', false)) {
            header('Access-Control-Allow-Credentials: true');
        }

        if ($request->method() === 'OPTIONS') {
            return (new Response())->withStatus(204);
        }

        return $next($request);
    }
}
