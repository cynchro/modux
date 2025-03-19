<?php

namespace App\Modules\Usuario\Requests;

use App\Helpers\ValidatorHelper;

class UsuariosCreateRequest
{
    protected $data;

    public function __construct($data = [])
    {
        $this->data = $data;
        $this->validate();
    }

    // Métodos para obtener los campos
    public function getId()
    {
        return $this->data['id'] ?? null;
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
        return $this->data['rol'] ?? null;
    }

    public function getToken()
    {
        return $this->data['token'] ?? null;
    }

    protected function validate()
    {
        $rules = [
            // Reglas de validación
        ];

        // Validar los datos
        $errors = ValidatorHelper::validate($this->data, $rules);

        if (!empty($errors)) {
            echo json_encode($errors, true);
            exit;
        }
    }
}