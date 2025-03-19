<?php

namespace App\Modules\Admin\Repositories;

use App\Config\Database;
use App\Helpers\PaginatorHelper;
use App\Modules\Permisos\Filters\FindFilter;
use App\Helpers\LogHelper;
use PDOException;

class PermisosRepository
{

    public static function find(): array
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT * FROM permisos";
            $stmt = $connection->prepare($SQL);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function findById($id): array
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT * FROM permisos WHERE id = {$id}";
            $stmt = $connection->prepare($SQL);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function findAvailable($id): array
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT 
                    permisos.id AS id_permiso, 
                    permisos.key
                    FROM permisos
                    LEFT JOIN roles_permisos ON permisos.id = roles_permisos.permiso 
                    AND roles_permisos.rol = ?
                    WHERE roles_permisos.permiso IS NULL";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$id]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }


    public static function findInUse(int $id): array
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT permisos.id AS id_permiso, permisos.key
                    FROM permisos
                    INNER JOIN roles_permisos ON permisos.id = roles_permisos.permiso
                    WHERE roles_permisos.rol = ?;";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$id]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function asignar($rol,$permiso)
    {
        try{
        $connection = Database::getConnection();
        $SQL = "INSERT INTO 
                roles_permisos 
                (rol, permiso, estado) 
                VALUES 
                (?, ?, ?)";
        $stmt = $connection->prepare($SQL);
        $stmt->execute([
            $rol,
            $permiso,
            2
        ]);

    } catch (PDOException $e) {
        LogHelper::error($e);
        throw new PDOException('Error en la base de datos: ' . $e->getMessage());
    }

    }

    public static function desasignar($rol,$permiso)
    {
        try{
        $connection = Database::getConnection();
        $SQL = "DELETE FROM  
                roles_permisos
                WHERE 
                rol = ?
                AND
                permiso = ?";
        $stmt = $connection->prepare($SQL);
        $stmt->execute([
            $rol,
            $permiso
        ]);

    } catch (PDOException $e) {
        LogHelper::error($e);
        throw new PDOException('Error en la base de datos: ' . $e->getMessage());
    }

    }

    public static function create(object $request): int
    {
        try {
            $connection = Database::getConnection();
            $SQL = "INSERT INTO 
                    roles 
                    (nombre, estado) 
                    VALUES 
                    (?, ?)";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([
                $request->nombre,
                0
            ]);

             return $connection->lastInsertId();

        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error en la base de datos: ' . $e->getMessage());
        }
    }



    public static function update(object $request): bool
    {
        try {
            $connection = Database::getConnection();
            $SQL = "UPDATE roles 
                    SET 
                    nombre = ?, 
                    estado = ?
                    WHERE 
                    id = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([
                $request->nombre,
                $request->estado,
                $request->rol
            ]);

            return true;

        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function createPermiso(object $request): int
    {
        try {
            $connection = Database::getConnection();
            $SQL = "INSERT INTO 
                    permisos 
                    (`key`, descripcion, estado) 
                    VALUES 
                    (?, ?, ?)";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([
                $request->nombre,
                $request->descripcion,
                0
            ]);

             return $connection->lastInsertId();

        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error en la base de datos: ' . $e->getMessage());
        }
    }



    public static function updatePermiso(object $request): bool
    {
        try {
            $connection = Database::getConnection();
            $SQL = "UPDATE permisos 
                    SET 
                    `key` = ?, 
                    descripcion = ?,
                    estado = ?
                    WHERE 
                    id = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([
                $request->nombre,
                $request->descripcion,
                $request->estado,
                $request->id
            ]);

            return true;

        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }


    public static function delete(object $datos): bool
    {
        try {
            $connection = Database::getConnection();
            $SQL = "UPDATE                      
                    roles 
                    SET estado=0
                    WHERE 
                    id = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$datos->getId()]);
            if (!$stmt->rowCount() > 0) {
                return false;
            }
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }
}
