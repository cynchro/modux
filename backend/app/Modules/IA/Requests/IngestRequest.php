<?php

namespace App\Modules\IA\Requests;

use App\Support\FormRequest;

class IngestRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'content'  => 'required|string|min:1',
            'id'       => 'required|string|min:1',
            'metadata' => 'array',
        ];
    }
}
