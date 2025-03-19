<?php

namespace App\Support;

class Container
{
    protected $instances = [];

    public function set($key, $instance)
    {
        $this->instances[$key] = $instance;
    }

    public function get($key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        return $this->resolve($key);
    }

    // Método para registrar una clase en el contenedor
    public function bind($key, $callback)
    {
        $this->instances[$key] = $callback;
    }

    protected function resolve($key)
    {
        if (!class_exists($key)) {
            throw new \Exception("Class $key does not exist");
        }

        $reflector = new \ReflectionClass($key);
        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $key;
        }

        $dependencies = $this->getDependencies($constructor);

        return $reflector->newInstanceArgs($dependencies);
    }

    protected function getDependencies($constructor)
{
    $dependencies = [];

    foreach ($constructor->getParameters() as $parameter) {
        $type = $parameter->getType();

        if ($type !== null && !$type->isBuiltin()) {
            $className = $type->getName();
            $namespaceParts = explode('\\', $className);
            $moduleName = $namespaceParts[2] ?? null;

            // Si el parámetro es un Request, pasa los datos necesarios
            if (strpos($className, 'Request') !== false && $moduleName) {
                $inputData = json_decode(file_get_contents("php://input"), true);
                $dependencies[] = new $className($inputData);
            } else {
                $dependencies[] = $this->get($className);
            }
        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        } else {
            throw new \Exception("Unable to resolve dependency for parameter: " . $parameter->getName());
        }
    }

    return $dependencies;
}
}

