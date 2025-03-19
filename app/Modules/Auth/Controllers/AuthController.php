<?php

namespace App\Modules\Auth\Controllers;

use App\Support\Request;
use App\Helpers\ResponseHelper;
use App\Modules\Auth\Requests\AuthRequest;
use App\Modules\Auth\Services\AuthService;

class AuthController
{

    public function register(AuthRequest $request)
    {
        $service = new AuthService;

        try {
            $service->register($request);
            ResponseHelper::success(['Usuario registrado con éxito']);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function update(AuthRequest $request)
    {
        $service = new AuthService;

        try {
            $service->update($request);
            ResponseHelper::success(['Usuario actualizado con éxito']);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }


    public function login(AuthRequest $request)
    {
        $service = new AuthService;

        try {
            $response = $service->login($request);
            ResponseHelper::success(['token' => $response['token']]);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }


    public function logout()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        $service = new AuthService;

        try {
            $service->logout($authHeader);
            ResponseHelper::success(['Logged out successfully']);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function me()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        $service = new AuthService;

        try {
            $user = $service->me($authHeader);
            ResponseHelper::success(['user' => $user]);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function permisos($key)
    {

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        $service = new AuthService;

        try {
            $permisos = $service->permisos($authHeader, $key);
            ResponseHelper::success($permisos['permisos']);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function impersonate(Request $request)
    {
        $service = new AuthService;

        try {
            $response = $service->impersonate($request);
            ResponseHelper::success(['token' => $response['token']]);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }
}
