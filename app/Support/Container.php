<?php

namespace App\Support;

use Psr\Container\ContainerInterface;
use App\Exceptions\ContainerException;
use App\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    private array $bindings  = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $abstract, callable $factory, bool $singleton = false): void
    {
        $this->bindings[$abstract]   = $factory;
        $this->singletons[$abstract] = $singleton;
        unset($this->instances[$abstract]);
    }

    public function singleton(string $abstract, callable $factory): void
    {
        $this->bind($abstract, $factory, true);
    }

    public function instance(string $abstract, mixed $concrete): void
    {
        $this->instances[$abstract] = $concrete;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $resolved = ($this->bindings[$id])($this);

            if ($this->singletons[$id] ?? false) {
                $this->instances[$id] = $resolved;
            }

            return $resolved;
        }

        return $this->autowire($id);
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->bindings[$id])
            || class_exists($id);
    }

    public function make(string $class): object
    {
        return $this->autowire($class);
    }

    private function autowire(string $class): object
    {
        if (!class_exists($class)) {
            throw new NotFoundException($class);
        }

        $reflector   = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
                continue;
            }

            throw new ContainerException(
                "Cannot resolve parameter [{$param->getName()}] in [{$class}]."
            );
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
