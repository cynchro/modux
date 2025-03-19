<?php

namespace App\Modules\Admin\Services;

use PDOException;

class AdminService
{
    public function create($request): void
    {
        // try {
        //     $item = UsuariosRepository::create($request);
        //     return ["datos" => $item];
        // } catch (PDOException $e) {
        //     throw new \Exception('Error al crear un usuarios. Inténtalo más tarde.');
        // }
    }

    public function getAll(): void
    {
        // $items = UsuariosRepository::find();

        // if (!$items) {
        //     throw new \Exception('No se encuentran usuarios.');
        // }
        // return $items;
    }

    public function get($request): void
    {
        // $item = UsuariosRepository::findById($request->getId());

        // if (!$item) {
        //     throw new \Exception('No se encuentra usuarios.');
        // }
        // return $item;
    }

    public function update($request): void
    {
        // try {
        //     $item = UsuariosRepository::update($request);
        //     if (!$item) {
        //         throw new \Exception('Usuarios inexistente.');
        //     }
        //     return $item;
        // } catch (PDOException $e) {
        //     throw new \Exception('Error al modificar unn usuarios. Inténtalo más tarde.');
        // }
    }

    public function updateSucursal($request): void
    {
        // try {
        //     $item = UsuariosRepository::updateSucursal($request);
        //     if (!$item) {
        //         throw new \Exception('Usuarios inexistente.');
        //     }
        //     return $item;
        // } catch (PDOException $e) {
        //     throw new \Exception('Error al modificar unn usuarios. Inténtalo más tarde.');
        // }
    }

    public function delete($request): void
    {
        // try {
        //     $item = UsuariosRepository::delete($request);
        //     if (!$item) {
        //         throw new \Exception('Usuarios inexistente.');
        //     }
        //     return $item;
        // } catch (PDOException $e) {
        //     throw new \Exception('Error al eliminar unn usuarios. Inténtalo más tarde.');
        // }
    }
}