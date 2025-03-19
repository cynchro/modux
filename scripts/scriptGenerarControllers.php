<?php

$modules = [
    "Recibo",
    "Sucursal",
    "Producto",
    "Empleado",
    "Auth",
    "Orden",
    "Cliente",
    "Usuario",
    "Presupuesto"
];

$template = <<<PHP
<?php

namespace App\Modules\{{MODULE}}\Controllers;

use App\Modules\{{MODULE}}\Requests\{{MODULE}}ShowRequest;
use App\Modules\{{MODULE}}\Requests\{{MODULE}}CreateRequest;
use App\Modules\{{MODULE}}\Requests\{{MODULE}}DeleteRequest;
use App\Modules\{{MODULE}}\Requests\{{MODULE}}UpdateRequest;
use App\Helpers\ResponseHelper;
use App\Modules\{{MODULE}}\Services\{{MODULE}}Service;

class {{MODULE}}Controller
{

    public function index()
    {
        \$service = new {{MODULE}}Service;

        try {
            \$response = \$service->getAll();
            ResponseHelper::success(\$response);
        } catch (\\Exception \$e) {
            ResponseHelper::error(\$e->getMessage());
        }
    }

    public function show({{MODULE}}ShowRequest \$request)
    {
        \$service = new {{MODULE}}Service;

        try {
            \$response = \$service->get(\$request);
            ResponseHelper::success(\$response);
        } catch (\\Exception \$e) {
            ResponseHelper::error(\$e->getMessage());
        }
    }


    public function create({{MODULE}}CreateRequest \$request)
    {
        \$service = new {{MODULE}}Service;

        try {
            \$response = \$service->create(\$request);
            ResponseHelper::success(\$response);
        } catch (\\Exception \$e) {
            ResponseHelper::error(\$e->getMessage());
        }
    }

    public function update({{MODULE}}UpdateRequest \$request)
    {
        \$service = new {{MODULE}}Service;

        try {
            \$response = \$service->update(\$request);
            ResponseHelper::success(\$response);
        } catch (\\Exception \$e) {
            ResponseHelper::error(\$e->getMessage());
        }
    }

    public function delete({{MODULE}}DeleteRequest \$request)
    {
        \$service = new {{MODULE}}Service;

        try {
            \$service->delete(\$request);
            ResponseHelper::success('{{MODULE}} borrado con éxito');
        } catch (\\Exception \$e) {
            ResponseHelper::error(\$e->getMessage());
        }
    }
}
PHP;

foreach ($modules as $module) {
    $content = str_replace('{{MODULE}}', $module, $template);
    $directory = __DIR__ . "/App/Modules/{$module}/Controllers";
    $filename = "{$directory}/{$module}Controller.php";

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    file_put_contents($filename, $content);
    echo "Archivo generado: $filename\n";
}
