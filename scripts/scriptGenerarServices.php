<?php

// Lista de repositorios
$repositories = [
    'RecibosRepository',
    'SucursalesRepository',
    'OrdenDeTrabajoDetallesRepository',
    'RolesRepository',
    'SeccionesRepository',
    'UsuariosRepository',
    'AccesosRepository',
    'OrdenDeTrabajoRepository',
    'ClientesRepository',
    'PresupuestosRepository',
    'PresupuestosDetalleRepository',
    'ProductosRepository',
    'EmpleadosRepository',
    'EstadosOrdenTrabajoRepository',
];

// Directorio de destino para los servicios
$outputDir = __DIR__ . '/Services/';

// Crear el directorio si no existe
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// Plantilla base del servicio
$template = <<<PHP
<?php

namespace App\Modules\{{MODULE}}\Services;

use PDOException;
use App\Modules\{{MODULE}}\Repositories\{{REPOSITORY}};

class {{MODULE}}Service
{
    public function create(\$request): array
    {
        try {
            \$item = {{REPOSITORY}}::create(\$request);
            return ["datos" => \$item];
        } catch (PDOException \$e) {
            throw new \Exception('Error al crear un{{ARTICLE}} {{MODULE_LOWER}}. Inténtalo más tarde.');
        }
    }

    public function getAll(): array
    {
        \$items = {{REPOSITORY}}::find();

        if (!\$items) {
            throw new \Exception('No se encuentran {{MODULE_LOWER_PLURAL}}.');
        }
        return \$items;
    }

    public function get(\$request): array
    {
        \$item = {{REPOSITORY}}::findById(\$request->getId());

        if (!\$item) {
            throw new \Exception('No se encuentra {{MODULE_LOWER}}.');
        }
        return \$item;
    }

    public function update(\$request): array
    {
        try {
            \$item = {{REPOSITORY}}::update(\$request);
            if (!\$item) {
                throw new \Exception('{{MODULE}} inexistente.');
            }
            return \$item;
        } catch (PDOException \$e) {
            throw new \Exception('Error al modificar un{{ARTICLE}} {{MODULE_LOWER}}. Inténtalo más tarde.');
        }
    }

    public function delete(\$request): bool
    {
        try {
            \$item = {{REPOSITORY}}::delete(\$request);
            if (!\$item) {
                throw new \Exception('{{MODULE}} inexistente.');
            }
            return \$item;
        } catch (PDOException \$e) {
            throw new \Exception('Error al eliminar un{{ARTICLE}} {{MODULE_LOWER}}. Inténtalo más tarde.');
        }
    }
}
PHP;

// Generar los servicios
foreach ($repositories as $repository) {
    $module = str_replace('Repository', '', $repository);
    $article = in_array(strtolower(substr($module, 0, 1)), ['a', 'e', 'i', 'o', 'u']) ? 'n' : '';
    $moduleLower = strtolower($module);
    $moduleLowerPlural = $moduleLower . 'es'; // Aproximación básica al plural
    if (substr($moduleLower, -1) === 's') {
        $moduleLowerPlural = $moduleLower; // Para palabras que ya terminan en "s"
    }

    // Reemplazar las variables en la plantilla
    $content = str_replace(
        ['{{MODULE}}', '{{REPOSITORY}}', '{{ARTICLE}}', '{{MODULE_LOWER}}', '{{MODULE_LOWER_PLURAL}}'],
        [$module, $repository, $article, $moduleLower, $moduleLowerPlural],
        $template
    );

    // Guardar el archivo
    $filename = $outputDir . $module . 'Service.php';
    file_put_contents($filename, $content);

    echo "Servicio generado: $filename\n";
}
