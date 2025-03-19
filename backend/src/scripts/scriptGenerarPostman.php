<?php

// Configuración de la conexión a MySQL
$dbConfig = [
    'host' => 'agartedb',
    'user' => 'root',
    'password' => 'root',
    'database' => 'agarte',
];

// Crear conexión
$conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database']);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consultar todas las tablas y columnas
$sql = "
    SELECT TABLE_NAME, COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = '{$dbConfig['database']}' 
    ORDER BY TABLE_NAME, ORDINAL_POSITION;
";

$result = $conn->query($sql);

if (!$result) {
    die("Error al ejecutar la consulta: " . $conn->error);
}

// Procesar datos
$dbStructure = [];
while ($row = $result->fetch_assoc()) {
    $tableName = $row['TABLE_NAME'];
    $columnName = $row['COLUMN_NAME'];

    if (!isset($dbStructure[$tableName])) {
        $dbStructure[$tableName] = [];
    }
    $dbStructure[$tableName][] = $columnName;
}

// Generar colección Postman
$collection = [
    'info' => [
        'name' => 'API Collection',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'item' => []
];

foreach ($dbStructure as $table => $columns) {
    // Base URL para las solicitudes
    $baseUrl = "http://localhost/api/$table";

    // Solicitud GET: Obtener todos los registros de la tabla
    $collection['item'][] = [
        'name' => "GET $table",
        'request' => [
            'method' => 'GET',
            'url' => [
                'raw' => "$baseUrl",
                'host' => ['localhost'],
                'path' => ['api', $table],
            ],
        ],
    ];

    // Solicitud POST: Insertar un registro en la tabla
    $postBody = [];
    foreach ($columns as $column) {
        $postBody[$column] = "{{{$column}}}";
    }
    $collection['item'][] = [
        'name' => "POST $table",
        'request' => [
            'method' => 'POST',
            'url' => [
                'raw' => "$baseUrl",
                'host' => ['localhost'],
                'path' => ['api', $table],
            ],
            'header' => [
                [
                    'key' => 'Content-Type',
                    'value' => 'application/json',
                ],
            ],
            'body' => [
                'mode' => 'raw',
                'raw' => json_encode($postBody, JSON_PRETTY_PRINT),
            ],
        ],
    ];

    // Solicitud PUT: Actualizar un registro por ID
    $putBody = [];
    foreach ($columns as $column) {
        $putBody[$column] = "{{{$column}}}";
    }
    $collection['item'][] = [
        'name' => "PUT $table",
        'request' => [
            'method' => 'PUT',
            'url' => [
                'raw' => "$baseUrl/:id",
                'host' => ['localhost'],
                'path' => ['api', $table, ':id'],
            ],
            'header' => [
                [
                    'key' => 'Content-Type',
                    'value' => 'application/json',
                ],
            ],
            'body' => [
                'mode' => 'raw',
                'raw' => json_encode($putBody, JSON_PRETTY_PRINT),
            ],
        ],
    ];

    // Solicitud DELETE: Eliminar un registro por ID
    $collection['item'][] = [
        'name' => "DELETE $table",
        'request' => [
            'method' => 'DELETE',
            'url' => [
                'raw' => "$baseUrl/:id",
                'host' => ['localhost'],
                'path' => ['api', $table, ':id'],
            ],
        ],
    ];
}

// Guardar el archivo JSON
$fileName = 'postman_collection.json';
file_put_contents($fileName, json_encode($collection, JSON_PRETTY_PRINT));

// Cerrar conexión
$conn->close();

echo "Archivo '$fileName' generado con éxito.\n";

