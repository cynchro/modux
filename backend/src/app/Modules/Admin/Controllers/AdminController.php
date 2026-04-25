<?php

namespace App\Modules\Admin\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Helpers\RenderHelper;
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

    public function home(Request $request): Response
    {
        return RenderHelper::render('Admin.views.home');
    }

    public function roles(Request $request): Response
    {
        $roles = $this->rolService->getAll();
        return RenderHelper::render('Admin.views.roles', ['roles' => $roles]);
    }

    public function rolesABM(Request $request): Response
    {
        $id = (int) $request->route('id');

        $rol                = $this->rolService->get($id);
        $nombre             = $rol[0]['nombre'] ?? '';
        $estado             = $rol[0]['estado'] ?? '';
        $permisosDisponibles = $this->permisosService->getAvailable($id);
        $permisosAsignados   = $this->permisosService->getOnUse($id);

        return RenderHelper::render('Admin.views.rolesABM', [
            'nombre'              => $nombre,
            'estado'              => $estado,
            'permisosDisponibles' => $permisosDisponibles,
            'permisosAsignados'   => $permisosAsignados,
            'rol'                 => $id,
        ]);
    }

    public function gestionarRoles(Request $request): Response
    {
        $accion  = $request->input('accion');
        $rolId   = (int) $request->input('rol', 0);
        $nombre  = (string) $request->input('nombre', '');
        $estado  = (int) $request->input('estado', 1);

        match ($accion) {
            'asignar'       => $this->permisosService->asignar($rolId, (array) $request->input('permisos_disponibles', [])),
            'desasignar'    => $this->permisosService->desasignar($rolId, (array) $request->input('permisos_asignados', [])),
            'actualizarDatos' => $rolId === 0
                ? ($rolId = $this->rolService->create($nombre))
                : $this->rolService->update($rolId, $nombre, $estado),
            default         => null,
        };

        return Response::redirect("/admin/roles/abm/{$rolId}");
    }

    public function permisos(Request $request): Response
    {
        $permisos = $this->permisosService->getAll();
        return RenderHelper::render('Admin.views.permisos', ['permisos' => $permisos]);
    }

    public function permisosABM(Request $request): Response
    {
        $id      = (int) $request->route('id');
        $permiso = $this->permisosService->get($id);

        return RenderHelper::render('Admin.views.permisosABM', [
            'nombre'      => $permiso[0]['key'] ?? '',
            'descripcion' => $permiso[0]['descripcion'] ?? '',
            'estado'      => $permiso[0]['estado'] ?? '',
            'id'          => $id,
        ]);
    }

    public function gestionarPermisos(Request $request): Response
    {
        $accion      = $request->input('accion');
        $id          = (int) $request->input('id', 0);
        $key         = (string) $request->input('nombre', '');
        $descripcion = (string) $request->input('descripcion', '');
        $estado      = (int) $request->input('estado', 0);

        if ($accion === 'actualizarDatos') {
            $id === 0
                ? $this->permisosService->createPermiso($key, $descripcion)
                : $this->permisosService->updatePermiso($id, $key, $descripcion, $estado);
        }

        return Response::redirect('/admin/permisos');
    }

    public function impersonalizar(Request $request): Response
    {
        $data = $this->usuariosService->getAll();
        return RenderHelper::render('Admin.views.impersonalizar', ['usuarios' => $data['results'] ?? []]);
    }

    public function loguear(Request $request): Response
    {
        $adminId  = (int) $request->input('adminUserId');
        $targetId = (int) $request->input('targetUserId');
        $token    = $this->authService->impersonate($adminId, $targetId);

        return Response::success([
            'token'       => $token,
            'redirectUrl' => $_ENV['IMPERSONALIZE_URL'] ?? '',
        ]);
    }
}
