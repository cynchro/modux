<?php

namespace App\Helpers;

use App\Support\Response;

class RenderHelper
{
    public static function render(string $view, array $data = []): Response
    {
        extract($data);

        $parts    = array_map('ucfirst', explode('.', $view));
        $viewPath = implode('/', $parts) . '.view.php';
        $viewFile = dirname(__DIR__) . '/Modules/' . $viewPath;

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        ob_start();
        include $viewFile;
        $html = ob_get_clean();

        return Response::html((string) $html);
    }

    public static function pdf(string $ruta, array $variables = []): string
    {
        if (!file_exists($ruta)) {
            throw new \RuntimeException("Template not found: {$ruta}");
        }

        $html = file_get_contents($ruta);

        foreach ($variables as $key => $value) {
            $html = str_replace("{{{$key}}}", (string) ($value ?? ''), $html);
        }

        return (string) preg_replace_callback(
            '/\{\{ if\((.*?)\) \}\}(.*?)\{\{ else \}\}(.*?)\{\{ endif \}\}/s',
            function (array $matches) use ($variables): string {
                return self::evaluateCondition(trim($matches[1]), $variables)
                    ? $matches[2]
                    : $matches[3];
            },
            $html
        );
    }

    private static function evaluateCondition(string $condition, array $variables): bool
    {
        if (!preg_match('/(\w+)\s*([!=<>]+)\s*(.*)/', $condition, $m) || count($m) < 4) {
            return false;
        }

        $varValue = $variables[$m[1]] ?? null;
        $value    = trim($m[3], "'\"");

        return match ($m[2]) {
            '=='    => $varValue == $value,
            '!='    => $varValue != $value,
            '>'     => $varValue > $value,
            '<'     => $varValue < $value,
            '>='    => $varValue >= $value,
            '<='    => $varValue <= $value,
            default => false,
        };
    }
}
