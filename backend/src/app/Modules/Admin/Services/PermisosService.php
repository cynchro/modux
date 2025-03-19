<?php

namespace App\Modules\Admin\Services;

use PDOException;
use App\Modules\Admin\Repositories\PermisosRepository;

class PermisosService
{

    public function getAll(): array
    {

        $Permisos = PermisosRepository::find();

        if (!$Permisos) {
            return [];
        }
        return $Permisos;
    }

    public function get($id): array
    {
        $Permisos = PermisosRepository::findById($id);

        if (!$Permisos) {
            return [0];
        }
        return $Permisos;
    }

    public function getAvailable($id): array
    {

        return PermisosRepository::findAvailable($id);
    }

    public function getOnUse($id): array
    {
        return PermisosRepository::findInUse($id);
    }

    public function asignaciones($request)
    {   
        $permisosDisponibles = $request->input('permisos_disponibles', []);
        $permisosAsignados = $request->input('permisos_asignados', []);

        if ($request->accion === 'asignar') {
            $this->asignar($request, $permisosDisponibles);
        }

        if ($request->accion === 'desasignar') {
            $this->desasignar($request, $permisosAsignados);
        }

        if ($request->accion === 'actualizarDatos') {

            if ($request->rol == 0) {
                $this->create($request);
            } else {
                $this->update($request);
            }
        }
    }

    public function asignar($request, $permisosDisponibles)
    {

        foreach ($permisosDisponibles as $permiso) {
            PermisosRepository::asignar($request->rol, $permiso);
        }
        $route = str_replace(" ","", "/admin/roles/abm/".$request->rol);
        header("Location: ".$route);
        exit;
    }

    public function desasignar($request, $permisosAsignados)
    {

        foreach ($permisosAsignados as $permiso) {
            PermisosRepository::desasignar($request->rol, $permiso);
        }
        $route = str_replace(" ","", "/admin/roles/abm/".$request->rol);
        header("Location: ".$route);
        exit;
    }

    public function update($request)
    {

        PermisosRepository::update($request);

        header("Location: /admin/roles");
        exit;
    }

    public function create($request)
    {
        $rol = PermisosRepository::create($request);
        $route = str_replace(" ","", "/admin/roles/abm/".$rol);
        header("Location: ".$route);

        exit;
    }

    public function updatePermiso($request)
    {

        PermisosRepository::updatePermiso($request);

        header("Location: /admin/permisos");
        exit;
    }

    public function createPermiso($request)
    {
        $id = PermisosRepository::createPermiso($request);
        $route = str_replace(" ","", "/admin/permisos");
        header("Location: ".$route);

        exit;
    }


    public function acciones($request)
    {   
        if ($request->accion === 'actualizarDatos') {
            if ($request->id == 0) {
                $this->createPermiso($request);
            } else {
                $this->updatePermiso($request);
            }
        }
    }
}
