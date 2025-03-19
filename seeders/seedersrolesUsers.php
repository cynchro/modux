<?php

require_once dirname(__DIR__, 1) . '/config/database.php';
use App\Config\Database;

try {
    $pdo = Database::getInstance()->getConnection();

    // Crear el tenant principal si no existe
    $tenantName = "Main Tenant";
    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE nombre = :nombre");
    $stmt->execute(['nombre' => $tenantName]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tenant) {
        $stmt = $pdo->prepare("INSERT INTO tenants (id, nombre) VALUES (UUID(), :nombre)");
        $stmt->execute(['nombre' => $tenantName]);
        $tenantId = $pdo->lastInsertId();
    } else {
        $tenantId = $tenant['id'];
    }

    // Crear rol Administrador si no existe
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE nombre = 'Administrador'");
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$role) {
        $stmt = $pdo->prepare("INSERT INTO roles (nombre, estado) VALUES ('Administrador', 'activo')");
        $stmt->execute();
        $roleId = $pdo->lastInsertId();
    } else {
        $roleId = $role['id'];
    }

    // Crear permiso si no existe
    $stmt = $pdo->prepare("SELECT id FROM permisos WHERE permiso = 'Acceso Total'");
    $stmt->execute();
    $permiso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$permiso) {
        $stmt = $pdo->prepare("INSERT INTO permisos (permiso, estado) VALUES ('Acceso Total', 1)");
        $stmt->execute();
        $permisoId = $pdo->lastInsertId();
    } else {
        $permisoId = $permiso['id'];
    }

    // Crear usuario admin si no existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = 'admin@admin.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $password = password_hash('admin123', PASSWORD_BCRYPT); // Genera el hash seguro de la contraseña
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, clave, rol, tenant_id) VALUES (:usuario, :clave, :rol, :tenant_id)");
        $stmt->execute([
            'usuario'   => 'admin@admin.com',
            'clave'     => $password,
            'rol'       => 'Administrador',
            'tenant_id' => $tenantId
        ]);
        echo "✅ Super usuario 'admin' creado correctamente.\n";
    } else {
        echo "⚠️ El usuario 'admin' ya existe.\n";
    }

    // Asignar permisos al rol Administrador en roles_permisos
    $stmt = $pdo->prepare("SELECT id FROM roles_permisos WHERE rol = :rol AND permiso = :permiso");
    $stmt->execute(['rol' => $roleId, 'permiso' => $permisoId]);
    $rolPermiso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rolPermiso) {
        $stmt = $pdo->prepare("INSERT INTO roles_permisos (rol, permiso, estado) VALUES (:rol, :permiso, 1)");
        $stmt->execute([
            'rol'     => $roleId,
            'permiso' => $permisoId
        ]);
        echo "✅ Permiso asignado al rol Administrador correctamente.\n";
    } else {
        echo "⚠️ El rol Administrador ya tiene el permiso asignado.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
