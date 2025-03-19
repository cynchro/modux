<?php

namespace App\Helpers;

class RenderHelper
{
    public static function render($view, $data = [])
    {
        // Extraer las variables del arreglo asociativo
        extract($data);

        // Dividir el nombre de la vista en partes usando el punto como separador
        $parts = explode('.', $view);

        // Asegurar que las partes estén en mayúsculas
        $parts = array_map('ucfirst', $parts);

        // Reconstruir la ruta con las partes modificadas
        $viewPath = implode('/', $parts) . '.view.php';

        // Construir la ruta completa de la vista, empezando desde la carpeta base del proyecto
        $viewFile = dirname(__DIR__, 1) . '/Modules/' . $viewPath;

        // Verificar que el archivo de vista existe
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile");
        }

        // Leer el contenido del archivo de vista
        $viewContent = file_get_contents($viewFile);

        // Procesar el contenido para transformar las expresiones personalizadas en PHP
        $viewContent = self::processTemplate($viewContent);

        // Evaluar el contenido transformado con las variables extraídas
        ob_start();
        eval('?>' . $viewContent);
        echo ob_get_clean();
    }

    private static function processTemplate($content)
    {
        // Reemplazar {{ variable }} con <?= htmlspecialchars($variable); 
        $content = preg_replace('/{{\s*(\$\w+)\s*}}/', '<?= htmlspecialchars($1); ?>', $content);

        // Reemplazar {{ foreach (...) }} con <?php foreach (...) { 
        $content = preg_replace('/{{\s*foreach\s*\((.+)\)\s*}}/', '<?php foreach ($1) { ?>', $content);

        // Reemplazar {{ endforeach }} con <?php } 
        $content = preg_replace('/{{\s*endforeach\s*}}/', '<?php } ?>', $content);

        // Reemplazar {{ if (...) }} con <?php if (...) { 
        $content = preg_replace('/{{\s*if\s*\((.+)\)\s*}}/', '<?php if ($1) { ?>', $content);

        // Reemplazar {{ else }} con <?php } else { 
        $content = preg_replace('/{{\s*else\s*}}/', '<?php } else { ?>', $content);

        // Reemplazar {{ endif }} con <?php } 
        $content = preg_replace('/{{\s*endif\s*}}/', '<?php } ?>', $content);

        // Agregar más patrones si es necesario

        return $content;
    }

    public static function pdf($ruta, $variables = [])
    {
        if (!file_exists($ruta)) {
            die("Error: No se encontró la plantilla HTML en $ruta.");
        }

        $html = file_get_contents($ruta);

        foreach ($variables as $key => $value) {
            $html = str_replace("{{{$key}}}", $value ?? '', $html); // Convertir null en cadena vacía
        }

        return $html;
    }
}
