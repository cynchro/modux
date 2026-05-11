<?php

namespace App\Modules\Cliente\Requests;

use App\Support\FormRequest;

class CreateClienteRequest extends FormRequest
{
    /** @return array<string, string> */
    protected function rules(): array
    {
        return [
            // 'campo' => 'required|min:2|max:255',
        ];
    }
}
