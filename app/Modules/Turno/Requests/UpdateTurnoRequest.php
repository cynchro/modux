<?php

namespace App\Modules\Turno\Requests;

use App\Support\FormRequest;

class UpdateTurnoRequest extends FormRequest
{
    /** @return array<string, string> */
    protected function rules(): array
    {
        return [
            'servicio'     => 'required|string|min:2|max:255',
            'fecha_hora'   => 'required|date',
            'duracion_min' => 'required|integer',
            'estado'       => 'required|in:pendiente,confirmado,cancelado',
        ];
    }
}
