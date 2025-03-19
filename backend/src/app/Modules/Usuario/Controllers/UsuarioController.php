<?php

namespace App\Modules\Usuario\Controllers;

use App\Helpers\ResponseHelper;
use App\Modules\Usuario\Services\UsuariosService;
use App\Modules\Usuario\Requests\UsuariosShowRequest;
use App\Modules\Usuario\Requests\UsuariosCreateRequest;
use App\Modules\Usuario\Requests\UsuariosDeleteRequest;
use App\Modules\Usuario\Requests\UsuariosUpdateRequest;
use App\Modules\Usuario\Requests\UsuariosSucursalUpdateRequest;

class UsuarioController
{

    public function index()
    {
        $service = new UsuariosService;

        try {
            $response = $service->getAll();
            ResponseHelper::success($response);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function show(UsuariosShowRequest $request)
    {
        $service = new UsuariosService;

        try {
            $response = $service->get($request);
            ResponseHelper::success($response);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }


    public function create(UsuariosCreateRequest $request)
    {
        $service = new UsuariosService;

        try {
            $response = $service->create($request);
            ResponseHelper::success($response);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function update(UsuariosUpdateRequest $request)
    {
        $service = new UsuariosService;

        try {
            $response = $service->update($request);
            ResponseHelper::success($response);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function updateSucursal(UsuariosSucursalUpdateRequest $request)
    {
        try {
            $service = new UsuariosService;
            $response = $service->updateSucursal($request);
            ResponseHelper::success($response);
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }

    public function delete(UsuariosDeleteRequest $request)
    {
        $service = new UsuariosService;

        try {
            $service->delete($request);
            ResponseHelper::success('Usuario borrado con éxito');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage());
        }
    }
}