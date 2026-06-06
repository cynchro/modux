<?php

namespace App\Modules\IA\Controllers;

use App\Modules\IA\Services\IAService;
use App\Modules\IA\Requests\ChatRequest;
use App\Modules\IA\Requests\RagAskRequest;
use App\Modules\IA\Requests\IngestRequest;
use App\Support\Response;

class IAController
{
    public function __construct(private readonly IAService $service) {}

    public function chat(ChatRequest $request): Response
    {
        $data   = $request->validated();
        $answer = $this->service->chat($data['messages'], (float) ($data['temperature'] ?? 0.7));

        return Response::success(['answer' => $answer]);
    }

    public function ask(RagAskRequest $request): Response
    {
        $data   = $request->validated();
        $answer = $this->service->ask($data['question'], [], $data['system_prompt'] ?? null);

        return Response::success(['answer' => $answer]);
    }

    public function ingest(IngestRequest $request): Response
    {
        $data   = $request->validated();
        $chunks = $this->service->ingest($data['content'], $data['id'], $data['metadata'] ?? []);

        return Response::success(['chunks_ingested' => count($chunks)], 201);
    }

    public function retrieve(RagAskRequest $request): Response
    {
        $data    = $request->validated();
        $results = $this->service->retrieve($data['question']);

        return Response::success(['results' => $results]);
    }
}
