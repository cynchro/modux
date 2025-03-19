<?php

namespace App\Modules\Admin\Services;

use PDOException;
use App\Modules\Admin\Repositories\RolRepository;

class RolService
{


    public function create($request): array
    {
        try {
            $Rol = RolRepository::create($request);
            return ["datos" => $Rol];
        } catch (PDOException $e) {
            throw new \Exception('Error al crear un rol. Inténtalo más tarde.');
        }
    }

    public function getAll(): array
    {

        $Rol = RolRepository::find();

        if (!$Rol) {
            return [];
        }
        return $Rol;
    }

    public function get($id): array
    {

        $Rol = RolRepository::findById($id);

        if (!$Rol) {
            return [0];
        }
        return $Rol;
    }

    public function update($request): array
    {
        try {
            $Rol = RolRepository::update($request);
            if (!$Rol) {
                throw new \Exception('rol inexistente');
            }
            return $Rol;
        } catch (PDOException $e) {
            throw new \Exception('Error al modificar una rol. Inténtalo más tarde.');
        }
    }

    public function delete($request): bool
    {
        try {
            $Rol = RolRepository::delete($request);
            if (!$Rol) {
                throw new \Exception('rol inexistente');
            }
            return $Rol;
        } catch (PDOException $e) {
            throw new \Exception('Error al eliminar una Rol. Inténtalo más tarde.');
        }
    }
}
