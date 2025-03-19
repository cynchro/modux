<?php

namespace App\Modules\Auth\Repositories;

use PDOException;
use App\Config\Database;
use App\Helpers\LogHelper;

class AuthRepository
{

    public static function create($username, $hashedPassword, $rol)
    {
        try {
            $connection = Database::getConnection();

            $SQL = "SELECT 
                COUNT(*) 
            FROM 
                usuarios
            WHERE 
                usuario = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$username]);

            if ($stmt->fetchColumn() > 0) {
                throw new \Exception('El usuario ya existe en el sistema.');
            }

            $SQL = "INSERT INTO 
                    usuarios 
                    (usuario, clave, rol) 
                    VALUES 
                    (?, ?, ?)";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$username, $hashedPassword, $rol]);
            return ["id" => $connection->lastInsertId()];
        } catch (PDOException $e) {
            throw new \Exception('Error en la base de datos: ' . $e->getMessage());
        }
    }

    public static function update($username, $hashedPassword, $id, $rol)
    {

        try {
            $connection = Database::getConnection();
            $SQL = "UPDATE usuarios
                    SET 
                    usuario = ?,
                    clave = ?,
                    rol = ?
                    WHERE 
                    id = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$username, $hashedPassword, $rol, $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
    }


    public static function updateUser($username, $id, $rol)
    {

        try {
            $connection = Database::getConnection();
            $SQL = "UPDATE usuarios
                    SET 
                    usuario = ?,
                    rol = ?
                    WHERE 
                    id = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$username, $rol, $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
    }

    public static function updateToken($userId, $token)
    {
        try {
            $connection = Database::getConnection();
            $SQL = "UPDATE 
                    usuarios 
                    SET 
                    token = ? 
                    WHERE 
                    id = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$token, $userId]);
        } catch (PDOException $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
    }

    public static function ClearToken($token)
    {
        try {
            $connection = Database::getConnection();
            $SQL = "UPDATE 
                    usuarios 
                    SET 
                    token = NULL 
                    WHERE 
                    token = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$token]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
    }


    public static function findUserByToken($token)
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT 
                    usuarios.id,
                    usuarios.usuario,
                    usuarios.rol,
                    roles.nombre as nombre_rol
                    FROM 
                    usuarios
                    LEFT JOIN
                    roles
                    ON
                    usuarios.rol=roles.id
                    WHERE 
                    token = ?;";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$token]);

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $user;
            }

            return null;
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function findUserPermissions($token, $key)
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT 
                        roles_permisos.estado AS permiso
                    FROM 
                        usuarios 
                        LEFT JOIN roles_permisos ON roles_permisos.rol=usuarios.rol
                        LEFT JOIN permisos ON roles_permisos.permiso=permisos.id
                    WHERE 
                        usuarios.token = ?
                        AND
                        permisos.key = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$token, $key]);

            if ($stmt->rowCount() > 0) {
                $permiso = $stmt->fetch(\PDO::FETCH_ASSOC);
                return ["permisos" => $permiso];
            }

            return ["permisos" => ["permiso" => 0]];
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function findUserByName($username)
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT 
                    usuarios.id, 
                    usuarios.clave, 
                    usuarios.rol
                    FROM 
                    usuarios 
                    WHERE 
                    usuarios.usuario = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                //$accesos = self::findAccesosByRol($user['rol']);
                return ["user" => $user];
            }

            return null;
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function findAccesosByRol($rol)
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT 
                    secciones.nombre,
                    secciones.tag
                    FROM 
                    accesos
                    INNER JOIN
                    secciones
                    ON
                    accesos.seccion = secciones.id
                    WHERE
                    accesos.rol= ? ";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$rol]);

            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            return null;
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }

    public static function findUserById($userId)
    {
        try {
            $connection = Database::getConnection();
            $SQL = "SELECT 
                    usuarios.id, 
                    usuarios.usuario, 
                    usuarios.rol,
                    roles.nombre
                    FROM 
                        usuarios 
                    LEFT JOIN
                        roles
                    ON
                        usuarios.rol=roles.id
                    WHERE 
                    usuarios.id = ?";
            $stmt = $connection->prepare($SQL);
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            return null;
        } catch (PDOException $e) {
            LogHelper::error($e);
            throw new PDOException('Error: ' . $e->getMessage());
        }
    }
}
