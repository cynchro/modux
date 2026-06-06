<?php

namespace App\Modules\IA\Requests;

use App\Support\FormRequest;

class RagAskRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'question'      => 'required|string|min:1',
            'system_prompt' => 'string',
        ];
    }
}
