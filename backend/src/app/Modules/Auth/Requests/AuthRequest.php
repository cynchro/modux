<?php

namespace App\Modules\Auth\Requests;

use App\Helpers\ValidatorHelper;

class AuthRequest
{
    protected $data;

    public function __construct($data = [])
    {
        $this->data = $data;
        $this->validate();
    }

    public function getId()
    {
        return $this->data['id'] ?? null;
    }

    public function setId($id)
    {
        $this->data['id'] = $id;
    }

    public function getUsuario()
    {
        return $this->data['usuario'] ?? null;
    }

    public function getClave()
    {
        return $this->data['clave'] ?? null;
    }

    public function getRol()
    {
        return $this->data['rol'] ?? 0;
    }

    protected function validate()
    {

        $rules = [
            'usuario' => 'required|email',
            'clave' => 'required'
        ];

        // Validar los datos
        $errors = ValidatorHelper::validate($this->data, $rules);

        if (!empty($errors)) {
            echo json_encode($errors, true);
            exit;
        }
    }
}
