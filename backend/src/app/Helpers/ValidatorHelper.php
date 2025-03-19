<?php

namespace App\Helpers;

class ValidatorHelper
{

   /*
    * Tipos de filtros
    *
    * required - si es requerido
    * min:5 - si es campo tiene minimo 5 caracteres
    * max:5 - si el campo tiene maximo 5 caracteres
    * email - si es un email valido 
    * integer - si es un entero
    * trim - quita los espacios
    */

    private static $errors = [];

    public static function validate($data, $rules)
    {
        self::$errors = []; // Reiniciar los errores cada vez que se llame a validate

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            foreach ($rulesArray as $rule) {
                self::applyRule($data, $field, $rule);
            }
        }

        return self::$errors;
    }

    private static function applyRule($data, $field, $rule)
    {
        if (strpos($rule, ':')) {
            [$ruleName, $ruleValue] = explode(':', $rule);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($data[$field])) {
                    self::addError($field, "$field is required.");
                }
                break;

            case 'email':
                if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    self::addError($field, "$field must be a valid email address.");
                }
                break;

            case 'min':
                if (strlen($data[$field]) < $ruleValue) {
                    self::addError($field, "$field must be at least $ruleValue characters.");
                }
                break;

            case 'max':
                if (strlen($data[$field]) > $ruleValue) {
                    self::addError($field, "$field must be no more than $ruleValue characters.");
                }
                break;

            case 'integer':
                if (!filter_var($data[$field], FILTER_VALIDATE_INT)) {
                    self::addError($field, "$field must be an integer.");
                }
                break;

            case 'trim':
                $data[$field] = trim($data[$field]);
                break;

                // Agrega más reglas según tus necesidades

            default:
                break;
        }
    }

    private static function addError($field, $message)
    {

        self::$errors['success'] = true; 
        self::$errors['response']['required'][$field][] = $message;
        //self::$errors[$field][] = $message;
    }
}
