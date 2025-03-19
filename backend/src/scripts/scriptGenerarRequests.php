<?php

$host = 'agartedb'; // Cambia según tu configuración
$db = 'agarte'; // Cambia por el nombre de tu base de datos
$user = 'root'; // Cambia por tu usuario
$password = 'root'; // Cambia por tu contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener todas las tablas de la base de datos
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Obtener columnas de la tabla
        $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);

        // Generar archivos para cada tipo de Request
        generateRequestFile($table, $columns, 'Create');
        generateRequestFile($table, $columns, 'Update');
        generateRequestFile($table, ['id'], 'Delete');
        generateRequestFile($table, ['id'], 'Show');
    }

    echo "Archivos generados exitosamente.";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

/**
 * Genera un archivo de Request para una tabla.
 *
 * @param string $table
 * @param array $columns
 * @param string $type
 */
function generateRequestFile($table, $columns, $type)
{
    $className = convertToCamelCase($table) . ucfirst($type) . 'Request';
    $namespace = "App\\Modules\\" . convertToCamelCase($table) . "\\Requests";
    $fileName = __DIR__ . "/$className.php";

    $methods = array_map(function ($column) {
        $camelCase = convertToCamelCase($column);
        return "    public function get" . ucfirst($camelCase) . "()\n    {\n        return \$this->data['$column'] ?? null;\n    }\n";
    }, $columns);

    $content = <<<PHP
<?php

namespace $namespace;

use App\Helpers\ValidatorHelper;

class $className
{
    protected \$data;

    public function __construct(\$data = [])
    {
        \$this->data = \$data;
        \$this->validate();
    }

    // Métodos para obtener los campos
PHP;

    $content .= "\n" . implode("\n", $methods);

    // Agregar el método validate al final
    $content .= <<<PHP

    protected function validate()
    {
        \$rules = [
            // Reglas de validación
        ];

        // Validar los datos
        \$errors = ValidatorHelper::validate(\$this->data, \$rules);

        if (!empty(\$errors)) {
            echo json_encode(\$errors, true);
            exit;
        }
    }
}
PHP;

    // Escribir el archivo
    file_put_contents($fileName, $content);
}

/**
 * Convierte un string con guiones bajos a camelCase.
 *
 * @param string $string
 * @return string
 */
function convertToCamelCase($string)
{
    return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $string)));
}
