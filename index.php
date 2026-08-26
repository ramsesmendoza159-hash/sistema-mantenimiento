<?php
// index.php - Punto de entrada principal
session_start();

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Autocarga de clases
spl_autoload_register(function ($class) {
    $prefix = '';
    $base_dir = __DIR__ . '/';
    
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    
    // Buscar en controller, model, helpers
    $paths = ['controller/', 'model/', 'helpers/'];
    foreach ($paths as $path) {
        $file = __DIR__ . '/' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// Incluir configuración
require_once __DIR__ . '/config/database.php';

// Obtener controlador y acción de la URL
$controller = $_GET['controller'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Si hay parámetros adicionales en la URL (ej: /controller/action/id)
$url = $_SERVER['REQUEST_URI'];
$base = '/proyecto/';
if (strpos($url, $base) === 0) {
    $path = substr($url, strlen($base));
    $segments = explode('/', trim($path, '/'));
    
    if (!empty($segments[0]) && !isset($_GET['controller'])) {
        $controller = $segments[0];
        $action = $segments[1] ?? 'index';
        $id = $segments[2] ?? null;
    }
}

// Validar que el controlador y acción solo contengan caracteres permitidos
if (!preg_match('/^[a-zA-Z0-9_]+$/', $controller)) {
    require_once __DIR__ . '/controller/ErrorController.php';
    $error = new ErrorController();
    $error->error404();
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
    require_once __DIR__ . '/controller/ErrorController.php';
    $error = new ErrorController();
    $error->error404();
    exit;
}

// Construir nombre del controlador
$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = __DIR__ . '/controller/' . $controllerName . '.php';

// Verificar si existe el controlador
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controllerObj = new $controllerName();
        
        // Verificar si existe el método
        if (method_exists($controllerObj, $action)) {
            try {
                if ($id !== null) {
                    $controllerObj->$action($id);
                } else {
                    $controllerObj->$action();
                }
            } catch (Exception $e) {
                error_log("Error en ejecución: " . $e->getMessage());
                require_once __DIR__ . '/controller/ErrorController.php';
                $error = new ErrorController();
                $error->error500();
            }
        } else {
            // Error 404 - Método no existe
            require_once __DIR__ . '/controller/ErrorController.php';
            $error = new ErrorController();
            $error->error404();
        }
    } else {
        // Error 500 - Clase no existe
        require_once __DIR__ . '/controller/ErrorController.php';
        $error = new ErrorController();
        $error->error500();
    }
} else {
    // Error 404 - Controlador no existe
    require_once __DIR__ . '/controller/ErrorController.php';
    $error = new ErrorController();
    $error->error404();
}
?>