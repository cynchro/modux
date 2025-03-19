<?php

namespace App\Modules\Admin\Controllers;

use App\Support\Request;
use App\Helpers\RenderHelper;
use App\Modules\Admin\Services\RolService;
use App\Modules\Admin\Services\AdminService;
use App\Modules\Admin\Services\PermisosService;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Usuario\Services\UsuariosService;

class AdminController
{
    public function home()
    {
        return RenderHelper::render('Admin.views.home');
    }

    public function gestionarRoles(Request $request)
    {
        $permisosService = new PermisosService();
        $permisosService->asignaciones($request);
    }

    public function roles()
    {
        
        $rolesService = new RolService();
        $roles = $rolesService->getAll();

        return RenderHelper::render('Admin.views.roles',['roles'=>$roles]);
    }

    public function rolesABM($id)
    {

    $permisosService = new PermisosService();
    $rolesService = new RolService();

    $rol = $rolesService->get($id);
    $nombre = $rol[0]['nombre'] ?? '';
    $estado = $rol[0]['estado'] ?? '';
    
    $permisosDisponibles = $permisosService->getAvailable($id);
    $permisosAsignados = $permisosService->getOnUse($id);
    
        return RenderHelper::render(
            'Admin.views.rolesABM',
            [
                'nombre' => $nombre,
                'estado' => $estado,
                'permisosDisponibles' => $permisosDisponibles,
                'permisosAsignados' => $permisosAsignados,
                'rol' => $id,
            ]
        );
    }
    

    public function gestionarPermisos(Request $request)
    {
        $permisosService = new PermisosService();
        $permisosService->acciones($request);
    }

    public function permisos()
    {

        $permisosService = new PermisosService();
        $permisos = $permisosService->getAll();
        
        return RenderHelper::render('Admin.views.permisos',['permisos'=>$permisos]);
    }

    public function permisosABM($id)
    {
        $permisosService = new PermisosService();
        $permisos = $permisosService->get($id);
        $nombre = $permisos[0]['key'] ?? '';
        $descripcion = $permisos[0]['descripcion'] ?? '';
        $estado = $permisos[0]['estado'] ?? '';
        
        
            return RenderHelper::render(
                'Admin.views.permisosABM',
                [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'estado' => $estado,
                    'id' => $id,
                ]
            );
    }

    public function impersonalizar()
    {        
        $usuariosService = new UsuariosService();
        $usuarios = $usuariosService->getAll();

        return RenderHelper::render('Admin.views.impersonalizar',["usuarios"=>$usuarios['results']]);
    }

    public function loguear(Request $request){
        $service = new AuthService();
        $service->impersonate($request);

    }

}

