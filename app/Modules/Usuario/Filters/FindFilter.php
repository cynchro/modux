<?php

namespace App\Modules\Cliente\Filters;

class FindFilter
{
    public static function getFilters(): array
    {
        $filters = [];

        /* Ejemplo */
        
        // if (isset($_GET['campo'])) {
        //     $filters[] = "campo = " . self::SQLformat($_GET['campo']);
        // }

        return $filters;
    }
    

    /* 
     *Esta funcion es para cuando viene un string 
     *lo envie con comillas dobles y no de un error en la consulta
     */

    private static function SQLformat($value): string
    {
        // Verifica si el valor es un string
        if (is_string($value)) {
            return '"' . $value . '"';
        }
        // Si no es un string, lo retorna tal cual
        return $value;
    }
}
