<?php

require_once dirname(__DIR__, 1) . '/config/database.php';
use App\Config\Database;

try {
    $pdo = Database::getInstance()->getConnection();

    $tables = [
        'tenants' => [
            "id CHAR(36) PRIMARY KEY DEFAULT (UUID())",
            "nombre VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL"
        ],
        'usuarios' => [
            "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
            "usuario VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            "clave VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL",
            "rol VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            "token VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            "desarrollador INT DEFAULT NULL",
            "tenant_id CHAR(36) NOT NULL",
            "FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE"
        ],
        'roles' => [
            "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
            "nombre VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            "estado VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL"
        ],
        'permisos' => [
            "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
            "rol INT DEFAULT NULL",
            "permiso VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            "estado INT DEFAULT NULL COMMENT '0: sin permiso, 1: lectura, 2: lectura-escritura'"
        ],
        'roles_permisos' => [
            "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
            "rol INT DEFAULT NULL",
            "permiso VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            "estado INT DEFAULT NULL COMMENT '0: sin permiso, 1: lectura, 2: lectura-escritura'"
        ]
    ];

    foreach ($tables as $name => $columns) {
        $query = "CREATE TABLE IF NOT EXISTS $name (" . implode(",", $columns) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($query);
        echo "Tabla '{$name}' creada o actualizada correctamente.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
