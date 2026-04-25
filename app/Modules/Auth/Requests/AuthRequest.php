<?php

namespace App\Modules\Auth\Requests;

use App\Support\FormRequest;

class AuthRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'usuario' => 'required|email',
            'clave'   => 'required|min:6',
        ];
    }

    public function getUsuario(): string
    {
        return $this->data['usuario'] ?? '';
    }

    public function getClave(): string
    {
        return $this->data['clave'] ?? '';
    }

    public function getRol(): int
    {
        return (int) ($this->data['rol'] ?? 0);
    }

    public function getId(): ?int
    {
        return isset($this->data['id']) ? (int) $this->data['id'] : null;
    }
}
