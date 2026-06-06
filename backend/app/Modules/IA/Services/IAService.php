<?php

namespace App\Modules\IA\Services;

use PhpAI\DriverFactory;
use PhpAI\RAG\RAGEngine;
use PhpAI\Contracts\CompletionRequest;

class IAService
{
    public function __construct(
        private readonly DriverFactory $factory,
        private readonly RAGEngine $rag,
    ) {}

    /** @param list<array{role: string, content: string}> $messages */
    public function chat(array $messages, float $temperature = 0.7): string
    {
        $response = $this->factory->llm()->complete(new CompletionRequest(
            messages: $messages,
            temperature: $temperature,
        ));

        return $response->content;
    }

    /** @param array<string,mixed> $filter */
    public function ask(string $question, array $filter = [], ?string $systemPrompt = null): string
    {
        return $this->rag->ask($question, $filter, $systemPrompt);
    }

    /** @param array<string,mixed> $metadata @return list<string> */
    public function ingest(string $content, string $id, array $metadata = []): array
    {
        return $this->rag->ingest($content, $id, $metadata);
    }

    /** @return list<array{score: float, metadata: array<string,mixed>}> */
    public function retrieve(string $question): array
    {
        return $this->rag->retrieve($question);
    }
}
