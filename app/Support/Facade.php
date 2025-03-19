<?php

namespace App\Support;

class Facade
{
    protected static $container;

    // Asignar el contenedor de servicios
    public static function setContainer($container)
    {
        static::$container = $container;
    }

    // Resolver la clase o servicio correspondiente
    protected static function resolveFacadeInstance($class)
    {
        return static::$container->get($class);
    }

    // Llamar a métodos estáticos de la clase subyacente
    public static function __callStatic($method, $arguments)
    {
        $instance = static::resolveFacadeInstance(static::getFacadeAccessor());

        return $instance->$method(...$arguments);
    }
}

//How To 

/*
<?php

namespace App\Modules\Product\Facades;

use App\Support\Facade;
use App\Modules\Product\Services\ProductService;

 class ProductFacade extends Facade
 {
     protected static function getFacadeAccessor()
     {
         return ProductService::class;
     }
 }
*/ 
