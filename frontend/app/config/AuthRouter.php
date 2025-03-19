<?php

require dirname(__DIR__, 1) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

if ($_ENV['APP_ERRORS']) {
    error_reporting(-1);
    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL);
} else {
    error_reporting(0);
}

class AuthRouter
{
    public function login($usuario, $clave)
    {
        $response = $this->postRequest('auth/login', ['usuario' => $usuario, 'clave' => $clave]);

        if ($response['success']) {
            setcookie('auth_token', $response['response']['token'], time() + 3600, '/');

            return $response;
        }
        return $response;
    }

    public function logout()
    {
        if (isset($_COOKIE['auth_token'])) {
            $token = $_COOKIE['auth_token'];
            $this->postRequest('auth/logout', [], $token);
            setcookie('auth_token', '', time() - 3600, '/');
        }
        header("Location: login.php");
        exit();
    }

    private function handleAccess($permiso, $redirect = true)
    {
        if (!isset($_COOKIE['auth_token'])) {
            if ($redirect) {
                header("Location: login.php");
                exit();
            }
            return false;
        }

        $token = $_COOKIE['auth_token'];
        $response = $this->getRequest('auth/permisos/' . $permiso, $token);

        if (!$response['success']) {
            $this->logout();
        }
        
        /*
        * Nomenclatura de permisos
        * 0: sin permiso, 1: lectura, 2: lectura-escritura
        */

        if (isset($response["response"]["permiso"])) {
            $permisoValue = $response["response"]["permiso"];
            if ($permisoValue != 2) { 
                if ($redirect) {
                    header("Location: forbidden.php");
                    exit();
                }
                return false;
            }
            return true;
        } else {
            echo "El permiso no está definido.";
            exit();
        }


    }
    
    public function checkAccess($permiso)
    {
        $this->handleAccess($permiso, true);
    }
    
    public function checkComponentAccess($permiso)
    {
        return $this->handleAccess($permiso, false);
    }
    

    private function postRequest($endpoint, $data = [], $token = null)
    {
        $url = $_ENV['API_URL'] . $endpoint;
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n" .
                    ($token ? "Authorization: Bearer $token\r\n" : ""),
                'method' => 'POST',
                'content' => json_encode($data),
            ]
        ];
        $context = stream_context_create($options);

        // Capturar errores y warnings
        $result = @file_get_contents($url, false, $context);

        // Manejar caso en que la solicitud falla
        if ($result === false) {
            $error = error_get_last();
            $response = [
                'success' => false,
                'error' => 'Request failed: ' . ($error['message'] ?? 'Unknown error')
            ];
            return $response;
        }

        // Intentar decodificar la respuesta
        $decodedResult = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response: ' . json_last_error_msg()
            ];
        }

        return $decodedResult;
    }

    public function getRequest($endpoint, $token = null)
    {
        $url = $_ENV['API_URL'] . $endpoint;

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n" .
                    ($token ? "Authorization: Bearer $token\r\n" : ""),
                'method' => 'GET'
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        return json_decode($result, true);
    }

   public function session()
    {
        if (!isset($_COOKIE['auth_token'])) {
                header("Location: login.php");
                exit;
        }
    
        $token = $_COOKIE['auth_token'];
        $response = $this->postRequest('auth/me',[] , $token);
         if (!$response['success']) {   
            $this->logout();
         }
    
        return $response;
    } 
}