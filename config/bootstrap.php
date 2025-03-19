<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use Dotenv\Dotenv;
use App\Support\Facade;
use App\Support\Container;
use App\Support\JWTConfig;

// Cargar las variables de entorno
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Configuración de errores
if ($_ENV['DISPLAY_ERRORS'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

\App\Config\Database::getInstance();

// Crear el contenedor de dependencias
$container = new Container();
Facade::setContainer($container);

class Router
{
    private $routes = [];
    private $protectedRoutes = [];
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get($uri, $action, $protected = false)
    {
        $this->addRoute('GET', $uri, $action, $protected);
    }

    public function post($uri, $action, $protected = false)
    {
        $this->addRoute('POST', $uri, $action, $protected);
    }

    public function put($uri, $action, $protected = false)
    {
        $this->addRoute('PUT', $uri, $action, $protected);
    }

    public function patch($uri, $action, $protected = false)
    {
        $this->addRoute('PATCH', $uri, $action, $protected);
    }

    public function delete($uri, $action, $protected = false)
    {
        $this->addRoute('DELETE', $uri, $action, $protected);
    }

    private function addRoute($method, $uri, $action, $protected)
    {
        $formattedUri = $this->formatUri($uri);
        $this->routes[$method][$formattedUri] = $action;
        if ($protected) {
            $this->protectedRoutes[$method][$formattedUri] = $action;
        }
    }

    public function dispatch($uri, $method)
    {
        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'];

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $action) {
                // Crear un patrón regex para capturar los parámetros de la URL
                $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // Remover el match completo

                    // Verificar si la ruta es protegida
                    if (isset($this->protectedRoutes[$method][$route])) {
                        if (!$this->checkAuth()) {
                            http_response_code(401);
                            echo json_encode(['error' => 'Unauthorized']);
                            return;
                        }
                    }

                    // Controlador y método a invocar
                    [$controllerClass, $controllerMethod] = $action;
                    $controllerInstance = $this->container->get($controllerClass);

                    // Parseo de los parámetros necesarios
                    $reflectionMethod = new \ReflectionMethod($controllerInstance, $controllerMethod);
                    $parameters = $reflectionMethod->getParameters();
                    $dependencies = [];

                    // Cargar el cuerpo de la solicitud si es necesario
                    $inputData = [];
                    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                        $inputData = json_decode(file_get_contents("php://input"), true) ?? [];
                    }

                    foreach ($parameters as $parameter) {
                        $parameterName = $parameter->getName();
                        $parameterType = $parameter->getType() ? $parameter->getType()->getName() : null;

                        if ($parameterType && class_exists($parameterType)) {
                            // Para requests tipados (como SucursalShowRequest), inicializar el request con los parámetros URL y cuerpo
                            $requestData = array_merge(['id' => $matches[0] ?? null], $inputData);
                            $dependencies[] = new $parameterType($requestData);
                        } elseif (!empty($matches)) {
                            // Para parámetros de URL no tipados
                            $dependencies[] = array_shift($matches);
                        } else {
                            $dependencies[] = null;
                        }
                    }

                    // Invocar al controlador con los parámetros resueltos
                    return $reflectionMethod->invokeArgs($controllerInstance, $dependencies);
                }
            }
        }

        // Respuesta para ruta no encontrada
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    private function formatUri($uri)
    {
        return rtrim($uri, '/');
    }

    private function checkAuth()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            try {
                return JWTConfig::decodeToken($token) !== null;
            } catch (\Exception $e) {
                error_log("Error de JWT: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}

// Instanciar y cargar las rutas
$router = new Router($container);

foreach (glob(__DIR__ . '/../app/Modules/*/routes.php') as $routeFile) {
    require $routeFile;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);
