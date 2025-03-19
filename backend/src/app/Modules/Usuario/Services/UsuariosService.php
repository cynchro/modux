<?php

namespace App\Modules\Usuario\Services;

use PDOException;
use App\Modules\Usuario\Repositories\UsuariosRepository;

class UsuariosService
{
    public function create($request): array
    {
        try {
            $item = UsuariosRepository::create($request);
            return ["datos" => $item];
        } catch (PDOException $e) {
            throw new \Exception('Error al crear un usuarios. Inténtalo más tarde.');
        }
    }

    public function getAll(): array
    {
        $items = UsuariosRepository::find();

        if (!$items) {
            throw new \Exception('No se encuentran usuarios.');
        }
        return $items;
    }

    public function get($request): array
    {
        $item = UsuariosRepository::findById($request->getId());

        if (!$item) {
            throw new \Exception('No se encuentra usuarios.');
        }
        return $item;
    }

    public function update($request): array
    {
        try {
            $item = UsuariosRepository::update($request);
            if (!$item) {
                throw new \Exception('Usuarios inexistente.');
            }
            return $item;
        } catch (PDOException $e) {
            throw new \Exception('Error al modificar unn usuarios. Inténtalo más tarde.');
        }
    }

    public function updateSucursal($request): array
    {
        try {
            $item = UsuariosRepository::updateSucursal($request);
            if (!$item) {
                throw new \Exception('Usuarios inexistente.');
            }
            return $item;
        } catch (PDOException $e) {
            throw new \Exception('Error al modificar unn usuarios. Inténtalo más tarde.');
        }
    }

    public function delete($request): bool
    {
        try {
            $item = UsuariosRepository::delete($request);
            if (!$item) {
                throw new \Exception('Usuarios inexistente.');
            }
            return $item;
        } catch (PDOException $e) {
            throw new \Exception('Error al eliminar unn usuarios. Inténtalo más tarde.');
        }
    }
}