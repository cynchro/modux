<?php

namespace App\Modules\Usuario\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Modules\Usuario\Services\UsuariosService;

class UsuarioController
{
    public function __construct(private UsuariosService $service)
    {
    }

    public function index(Request $request): Response
    {
        $data = $this->service->getAll();
        return Response::success($data);
    }

    public function show(Request $request): Response
    {
        $id   = (int) $request->route('id');
        $user = $this->service->get($id);
        return Response::success($user);
    }

    public function create(Request $request): Response
    {
        $result = $this->service->create($request->all());
        return Response::success(['created' => $result], 201);
    }

    public function update(Request $request): Response
    {
        $data   = array_merge($request->all(), ['id' => $request->route('id')]);
        $result = $this->service->update($data);
        return Response::success(['updated' => $result]);
    }

    public function updateSucursal(Request $request): Response
    {
        $userId     = (int) $request->user()['sub'];
        $sucursalId = (int) $request->route('id');
        $this->service->updateSucursal($userId, $sucursalId);
        return Response::success(['message' => 'Sucursal actualizada.']);
    }

    public function delete(Request $request): Response
    {
        $id = (int) $request->route('id');
        $this->service->delete($id);
        return Response::success(['message' => 'Usuario eliminado.']);
    }
}
