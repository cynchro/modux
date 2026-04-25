<?php

namespace App\Modules\Auth\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Modules\Auth\Requests\AuthRequest;
use App\Modules\Auth\Services\AuthService;

class AuthController
{
    public function __construct(private AuthService $service)
    {
    }

    public function register(AuthRequest $request): Response
    {
        $result = $this->service->register($request->all());
        return Response::success($result, 201);
    }

    public function update(AuthRequest $request): Response
    {
        $this->service->update($request->all());
        return Response::success(['message' => 'Usuario actualizado con éxito.']);
    }

    public function login(AuthRequest $request): Response
    {
        $token = $this->service->login($request->all());
        return Response::success(['token' => $token]);
    }

    public function logout(Request $request): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::error('Token not provided.', 401);
        }

        $this->service->logout($token);
        return Response::success(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::error('Token not provided.', 401);
        }

        $user = $this->service->me($token);
        return Response::success(['user' => $user]);
    }

    public function permisos(Request $request): Response
    {
        $token = $request->bearerToken();
        $key   = $request->route('key', '');

        if (!$token) {
            return Response::error('Token not provided.', 401);
        }

        $permisos = $this->service->permisos($token, $key);
        return Response::success($permisos);
    }

    public function impersonate(Request $request): Response
    {
        $adminId  = (int) $request->input('adminUserId');
        $targetId = (int) $request->input('targetUserId');
        $token    = $this->service->impersonate($adminId, $targetId);

        return Response::success([
            'token'       => $token,
            'redirectUrl' => $_ENV['IMPERSONALIZE_URL'] ?? '',
        ]);
    }
}
