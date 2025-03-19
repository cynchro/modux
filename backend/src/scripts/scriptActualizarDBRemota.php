<?php
// Configuración de las bases de datos
$localDb = [
    'host' => 'agartedb',
    'user' => 'root',
    'pass' => 'root',
    'db'   => 'agarte'
];

$remoteDb = [
    'host' => 'remote_host',
    'user' => 'remote_user',
    'pass' => 'password_remote',
    'db'   => 'database_remote'
];

// Conexión a las bases de datos
function connectDatabase($config) {
    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    if ($mysqli->connect_error) {
        die("Error al conectar: " . $mysqli->connect_error);
    }
    return $mysqli;
}

$local = connectDatabase($localDb);
$remote = connectDatabase($remoteDb);

// Obtener todas las tablas de la base de datos
function getTables($mysqli) {
    $tables = [];
    $result = $mysqli->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    }
    return $tables;
}

// Obtener estructura de la tabla
function getTableStructure($mysqli, $table) {
    $result = $mysqli->query("SHOW CREATE TABLE $table");
    $row = $result->fetch_assoc();
    return $row['Create Table'];
}

// Crear o actualizar tabla en la base remota
function ensureTableStructure($remote, $table, $structure) {
    echo "Sincronizando estructura de la tabla: $table\n";

    // Verificar si la tabla ya existe en la base remota
    $result = $remote->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        // La tabla existe, comprobar diferencias
        $remoteStructure = getTableStructure($remote, $table);
        if ($structure !== $remoteStructure) {
            // Actualizar la tabla eliminándola y recreándola
            echo "Actualizando la estructura de la tabla '$table'.\n";
            $remote->query("DROP TABLE $table");
            if (!$remote->query($structure)) {
                die("Error al actualizar la tabla '$table': " . $remote->error . "\n");
            }
        } else {
            echo "La tabla '$table' ya está sincronizada.\n";
        }
    } else {
        // Crear la tabla si no existe
        echo "Creando la tabla '$table'.\n";
        if (!$remote->query($structure)) {
            die("Error al crear la tabla '$table': " . $remote->error . "\n");
        }
    }
}

// Sincronizar las estructuras de las tablas
function syncTableStructures($local, $remote) {
    $localTables = getTables($local);

    foreach ($localTables as $table) {
        $structure = getTableStructure($local, $table);
        ensureTableStructure($remote, $table, $structure);
    }

    echo "Sincronización de estructura completa.\n";
}

// Ejecutar la sincronización
syncTableStructures($local, $remote);

// Cerrar conexiones
$local->close();
$remote->close();
