<?php

namespace App\Modules\IA\Requests;

use App\Support\FormRequest;

class ChatRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'messages'    => 'required|array',
            'temperature' => 'numeric',
        ];
    }
}
