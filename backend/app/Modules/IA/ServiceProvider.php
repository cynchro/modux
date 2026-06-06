<?php

namespace App\Modules\IA;

use PhpAI\Bootstrap;
use PhpAI\DriverFactory;
use PhpAI\RAG\RAGEngine;

class ServiceProvider extends \App\Support\ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(DriverFactory::class, fn() => Bootstrap::fromEnv());

        $this->container->singleton(RAGEngine::class, fn() =>
            $this->container->get(DriverFactory::class)->rag()
        );
    }
}
