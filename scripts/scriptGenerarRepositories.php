<?php

/**
 * Genera repositorios basados en las tablas y columnas de la base de datos, aplicando CamelCase.
 */
function generateRepositories($dbHost, $dbName, $dbUser, $dbPassword, $outputDir, $namespace = 'App\\Modules')
{
    try {
        // Conexión a la base de datos
        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener las tablas
        $tablesQuery = $pdo->query("SHOW TABLES");
        $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        foreach ($tables as $table) {
            // Obtener las columnas de la tabla
            $columnsQuery = $pdo->query("SHOW COLUMNS FROM $table");
            $columns = $columnsQuery->fetchAll(PDO::FETCH_ASSOC);

            // Convertir el nombre de la tabla a CamelCase
            $camelCaseName = toCamelCase($table);
            $className = "{$camelCaseName}Repository";
            $fileName = "$outputDir/{$className}.php";

            // Namespace ajustado a CamelCase
            $tableNamespace = "{$namespace}\\{$camelCaseName}\\Repositories";

            // Extraer nombres de columnas
            $columnNames = array_column($columns, 'Field');
            $insertPlaceholders = implode(', ', array_fill(0, count($columnNames), '?'));

            // Métodos dinámicos generados
            $methods = generateMethods($table, $columnNames, $insertPlaceholders);

            // Contenido del archivo del repositorio
            $content = <<<EOD
<?php

namespace {$tableNamespace};

use App\Config\Database;
use App\Helpers\PaginatorHelper;
use App\Modules\\{$camelCaseName}\\Filters\\FindFilter;
use PDOException;
use App\Helpers\LogHelper;

class {$className}
{
{$methods['find']}

{$methods['findById']}

{$methods['create']}

{$methods['update']}

{$methods['delete']}
}

EOD;

            file_put_contents($fileName, $content);
            echo "Repositorio {$className} generado en {$fileName}\n";
        }
    } catch (PDOException $e) {
        echo "Error al conectar con la base de datos: " . $e->getMessage();
    }
}

/**
 * Convierte un nombre separado por guiones bajos a CamelCase.
 * 
 * Ejemplo: orden_de_trabajo -> OrdenDeTrabajo
 */
function toCamelCase(string $snakeCase): string
{
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $snakeCase)));
}

/**
 * Genera los métodos básicos del repositorio.
 */
function generateMethods($table, $columns, $placeholders)
{
    $columnsList = implode(', ', $columns);
    $setColumns = implode(', ', array_map(fn($col) => "$col = ?", $columns));

    return [
        'find' => <<<EOD
    public static function find(): array
    {
        try {
            \$connection = Database::getConnection();
            \$SQL = "SELECT * FROM {$table}";

            \$filters = FindFilter::getFilters();
            if (\$filters) {
                \$SQL .= " WHERE " . implode(" AND ", \$filters);
            }

            \$paginator = new PaginatorHelper(\$connection, \$SQL);
            \$resultados = \$paginator->getPaginatedResults();

            if ((isset(\$resultados['results']) && empty(\$resultados['results'])) || empty(\$resultados)) {
                throw new \\PDOException('No se encuentran registros en {$table}');
            }

            return \$resultados;
        } catch (\\PDOException \$e) {
            LogHelper::error(\$e);
            throw new PDOException('Error en la paginación: ' . \$e->getMessage());
        }
    }
EOD,
        'findById' => <<<EOD
    public static function findById(int \$id): array
    {
        try {
            \$connection = Database::getConnection();
            \$SQL = "SELECT * FROM {$table} WHERE id = ?";
            \$stmt = \$connection->prepare(\$SQL);
            \$stmt->execute([\$id]);

            if (\$stmt->rowCount() > 0) {
                return \$stmt->fetch(\\PDO::FETCH_ASSOC);
            }

            return [];
        } catch (\\PDOException \$e) {
            LogHelper::error(\$e);
            throw new PDOException('Error: ' . \$e->getMessage());
        }
    }
EOD,
        'create' => <<<EOD
    public static function create(object \$datos): array
    {
        try {
            \$connection = Database::getConnection();
            \$SQL = "INSERT INTO {$table} ({$columnsList}) VALUES ({$placeholders})";
            \$stmt = \$connection->prepare(\$SQL);
            \$stmt->execute([
                {implode(", ", array_map(fn(\$col) => "\$datos->get" . ucfirst(\$col) . "()", $columns))}
            ]);

            \$id = \$connection->lastInsertId();
            return self::findById(\$id);
        } catch (\\PDOException \$e) {
            LogHelper::error(\$e);
            throw new PDOException('Error al crear en {$table}: ' . \$e->getMessage());
        }
    }
EOD,
        'update' => <<<EOD
    public static function update(object \$datos): array
    {
        try {
            \$connection = Database::getConnection();
            \$SQL = "UPDATE {$table} SET {$setColumns} WHERE id = ?";
            \$stmt = \$connection->prepare(\$SQL);
            \$stmt->execute([
                {implode(", ", array_map(fn(\$col) => "\$datos->get" . ucfirst(\$col) . "()", $columns))},
                \$datos->getId()
            ]);

            return self::findById(\$datos->getId());
        } catch (\\PDOException \$e) {
            LogHelper::error(\$e);
            throw new PDOException('Error al actualizar en {$table}: ' . \$e->getMessage());
        }
    }
EOD,
        'delete' => <<<EOD
    public static function delete(int \$id): bool
    {
        try {
            \$connection = Database::getConnection();
            \$SQL = "DELETE FROM {$table} WHERE id = ?";
            \$stmt = \$connection->prepare(\$SQL);
            \$stmt->execute([\$id]);

            return \$stmt->rowCount() > 0;
        } catch (\\PDOException \$e) {
            LogHelper::error(\$e);
            throw new PDOException('Error al eliminar en {$table}: ' . \$e->getMessage());
        }
    }
EOD,
    ];
}

// Configuración de conexión y ejecución
$dbHost = 'localhost';
$dbName = 'mi_base_de_datos';
$dbUser = 'mi_usuario';
$dbPassword = 'mi_contraseña';
$outputDir = __DIR__ . '/Repositories';

generateRepositories($dbHost, $dbName, $dbUser, $dbPassword, $outputDir);
