<?php

namespace App\Modules\Admin\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Modules\Admin\Services\RolService;
use App\Modules\Admin\Services\PermisosService;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Usuario\Services\UsuariosService;

class AdminController
{
    public function __construct(
        private RolService      $rolService,
        private PermisosService $permisosService,
        private AuthService     $authService,
        private UsuariosService $usuariosService
    ) {
    }

    // ── Roles ─────────────────────────────────────────────────────────────────

    public function indexRoles(Request $request): Response
    {
        return Response::success($this->rolService->getAll());
    }

    public function showRole(Request $request): Response
    {
        $id = (int) $request->route('id');

        return Response::success([
            'rol'                  => $this->rolService->get($id),
            'permisosDisponibles'  => $this->permisosService->getAvailable($id),
            'permisosAsignados'    => $this->permisosService->getOnUse($id),
        ]);
    }

    public function storeRole(Request $request): Response
    {
        $nombre = (string) $request->input('nombre', '');
        $id     = $this->rolService->create($nombre);

        return Response::success(['id' => $id], 201);
    }

    public function updateRole(Request $request): Response
    {
        $id     = (int) $request->route('id');
        $nombre = (string) $request->input('nombre', '');
        $estado = (int) $request->input('estado', 1);

        $this->rolService->update($id, $nombre, $estado);

        return Response::success(['updated' => true]);
    }

    public function assignPermisos(Request $request): Response
    {
        $id       = (int) $request->route('id');
        $permisos = (array) $request->input('permisos', []);

        $this->permisosService->asignar($id, $permisos);

        return Response::success(['assigned' => true]);
    }

    public function unassignPermisos(Request $request): Response
    {
        $id       = (int) $request->route('id');
        $permisos = (array) $request->input('permisos', []);

        $this->permisosService->desasignar($id, $permisos);

        return Response::success(['unassigned' => true]);
    }

    // ── Permisos ──────────────────────────────────────────────────────────────

    public function indexPermisos(Request $request): Response
    {
        return Response::success($this->permisosService->getAll());
    }

    public function showPermiso(Request $request): Response
    {
        $id = (int) $request->route('id');

        return Response::success($this->permisosService->get($id));
    }

    public function storePermiso(Request $request): Response
    {
        $key         = (string) $request->input('key', '');
        $descripcion = (string) $request->input('descripcion', '');

        $this->permisosService->createPermiso($key, $descripcion);

        return Response::success(['created' => true], 201);
    }

    public function updatePermiso(Request $request): Response
    {
        $id          = (int) $request->route('id');
        $key         = (string) $request->input('key', '');
        $descripcion = (string) $request->input('descripcion', '');
        $estado      = (int) $request->input('estado', 0);

        $this->permisosService->updatePermiso($id, $key, $descripcion, $estado);

        return Response::success(['updated' => true]);
    }

    // ── Users & Impersonation ─────────────────────────────────────────────────

    public function users(Request $request): Response
    {
        $data = $this->usuariosService->getAll();

        return Response::success($data['results'] ?? []);
    }

    public function impersonate(Request $request): Response
    {
        $adminId  = (int) $request->user()['id'];
        $targetId = (int) $request->input('userId');
        $token    = $this->authService->impersonate($adminId, $targetId);

        return Response::success(['token' => $token]);
    }
}
