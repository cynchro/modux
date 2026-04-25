<?php

namespace App\Support;

class Response
{
    private int     $status  = 200;
    private array   $headers = ['Content-Type' => 'application/json; charset=utf-8'];
    private ?array  $body    = null;
    private ?string $rawBody = null;

    public function withStatus(int $status): static
    {
        $clone         = clone $this;
        $clone->status = $status;
        return $clone;
    }

    public function withHeader(string $name, string $value): static
    {
        $clone                 = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function json(array $data, int $status = 200): static
    {
        $clone         = clone $this;
        $clone->status = $status;
        $clone->body   = $data;
        return $clone;
    }

    public static function success(array $data = [], int $status = 200): static
    {
        return (new static())->json([
            'success'  => true,
            'response' => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 422): static
    {
        return (new static())->json([
            'success' => false,
            'error'   => $message,
        ], $status);
    }

    public static function html(string $content, int $status = 200): static
    {
        $clone                          = new static();
        $clone->status                  = $status;
        $clone->headers['Content-Type'] = 'text/html; charset=utf-8';
        $clone->rawBody                 = $content;
        return $clone;
    }

    public static function redirect(string $url, int $status = 302): static
    {
        $clone                      = new static();
        $clone->status              = $status;
        $clone->headers['Location'] = $url;
        unset($clone->headers['Content-Type']);
        return $clone;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($this->rawBody !== null) {
            echo $this->rawBody;
        } elseif ($this->body !== null) {
            echo json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
}
