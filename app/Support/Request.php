<?php

namespace App\Support;

use App\Helpers\ValidatorHelper;

class Request
{
    private array $post;
    private array $get;
    private array $files;
    private array $server;
    private array $jsonData;

    public function __construct()
    {
        $this->post = $_POST;
        $this->get = $_GET;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->jsonData = $this->parseJson();
    }

    private function parseJson(): array
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $jsonInput = file_get_contents("php://input");
            return json_decode($jsonInput, true) ?? [];
        }
        return [];
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $this->jsonData[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->jsonData);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function header(string $key, string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }

    public function isJson(): bool
    {
        return !empty($this->jsonData);
    }

    public function validate(array $rules): array
    {
        return ValidatorHelper::validate($this->all(), $rules);
    }

    public function __get($key)
    {
        return $this->input($key);
    }
}
