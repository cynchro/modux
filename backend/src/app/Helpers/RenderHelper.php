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
    
        // Capturar la salida del archivo de vista
        ob_start();
        include $viewFile;
        echo ob_get_clean();
    }

//     public static function pdf($ruta, $variables = [])
// {
//     if (!file_exists($ruta)) {
//         die("Error: No se encontró la plantilla HTML en $ruta.");
//     }

//     $html = file_get_contents($ruta);

//     // Reemplazar las variables
//     foreach ($variables as $key => $value) {
//         $html = str_replace("{{{$key}}}", $value ?? '', $html); // Convertir null en cadena vacía
//     }

//     // Manejar condiciones simples
//     $html = preg_replace_callback('/\{\{ if\((.*?)\) \}\}(.*?)\{\{ else \}\}(.*?)\{\{ endif \}\}/s', function($matches) use ($variables) {
//         $condition = trim($matches[1]);
//         $trueBlock = $matches[2];
//         $falseBlock = $matches[3];

//         // Evaluar la condición
//         if (self::evaluateCondition($condition, $variables)) {
//             return $trueBlock;
//         } else {
//             return $falseBlock;
//         }
//     }, $html);

//     return $html;
// }

public static function pdf($ruta, $variables = [])
{
    if (!file_exists($ruta)) {
        die("Error: No se encontró la plantilla HTML en $ruta.");
    }

    $html = file_get_contents($ruta);

    // Reemplazar las variables
    foreach ($variables as $key => $value) {
        $html = str_replace("{{{$key}}}", $value ?? '', $html); // Convertir null en cadena vacía
    }

    // Manejar condiciones simples
    $html = preg_replace_callback('/\{\{ if\((.*?)\) \}\}(.*?)\{\{ else \}\}(.*?)\{\{ endif \}\}/s', function($matches) use ($variables) {
        $condition = trim($matches[1]);
        $trueBlock = $matches[2];
        $falseBlock = $matches[3];

        // Evaluar la condición
        if (self::evaluateCondition($condition, $variables)) {
            return $trueBlock;
        } else {
            return $falseBlock;
        }
    }, $html);

    return $html;
}

private static function evaluateCondition($condition, $variables)
{

    // Extraer la variable y el valor de la condición
    preg_match('/(\w+)\s*([!=<>]+)\s*(.*)/', $condition, $matches);
    if (count($matches) < 4) {
        return false;
    }

    $var = $matches[1];
    $operator = $matches[2];
    $value = trim($matches[3], "'\""); // Eliminar comillas alrededor del valor

    // Obtener el valor de la variable
    $varValue = $variables[$var] ?? null;

    // Comparar según el operador
    switch ($operator) {
        case '==':
            return $varValue == $value; // Comparación flexible
        case '!=':
            return $varValue != $value; // Comparación flexible
        case '>':
            return $varValue > $value;
        case '<':
            return $varValue < $value;
        case '>=':
            return $varValue >= $value;
        case '<=':
            return $varValue <= $value;
        default:
            return false;
    }
}
}
