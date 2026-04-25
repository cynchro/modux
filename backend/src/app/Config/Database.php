<?php

namespace App\Config;

class Database
{
    private static ?\PDO $connection = null;

    private function __construct()
    {
    }

    public static function setConnection(\PDO $pdo): void
    {
        self::$connection = $pdo;
    }

    public static function getConnection(): \PDO
    {
        if (self::$connection === null) {
            throw new \RuntimeException(
                'Database connection not initialized. Call Database::setConnection() first.'
            );
        }

        return self::$connection;
    }

    public static function getInstance(): static
    {
        return new self();
    }

    private function __clone()
    {
    }
}
