<?php
// index.php - Punto de entrada principal

// ==========================================
// CONFIGURACIÓN DE ERRORES PARA DEBUG
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Limpiar caché de archivos (seguro)
clearstatcache();

// Intentar resetear OPcache si está disponible
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// ... resto del código



// Crear directorio de logs si no existe
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/error.log');

// ==========================================
// INICIAR SESIÓN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// AUTOCARGA DE CLASES MEJORADA
// ==========================================
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    
    // MAPEO DE NOMBRES DE CLASE A ARCHIVOS
    $classMap = [
        'UsuariosModel' => 'UsuariosModel.php',
        'Usuario' => 'Usuario.php',
        'Tecnico' => 'Tecnico.php',
        'Supervisor' => 'Supervisor.php',
        'OrdenTrabajo' => 'OrdenTrabajo.php',
        'PlantasModel' => 'PlantasModel.php',
        'AreasModel' => 'AreasModel.php',
        'EquiposModel' => 'EquiposModel.php',
        'ComponentesModel' => 'ComponentesModel.php',
        'SecurityHelper' => 'SecurityHelper.php',
        'ValidationHelper' => 'ValidationHelper.php',
        'HashHelper' => 'HashHelper.php',
        'Database' => 'database.php',
        'InventarioModel' => 'InventarioModel.php',
    ];
    
    // Buscar en el mapa de clases
    if (isset($classMap[$class])) {
        $file = __DIR__ . '/model/' . $classMap[$class];
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        $file = __DIR__ . '/helpers/' . $classMap[$class];
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        $file = __DIR__ . '/config/' . $classMap[$class];
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    // Buscar en los directorios estándar
    $paths = [
        __DIR__ . '/controller/',
        __DIR__ . '/model/',
        __DIR__ . '/helpers/',
        __DIR__ . '/config/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    // Buscar con nombres alternativos
    $alternatives = [
        'UsuariosModel' => ['Usuario', 'Usuarios'],
        'Tecnico' => ['Tecnicos', 'TecnicoModel'],
        'Supervisor' => ['Supervisores', 'SupervisorModel'],
    ];
    
    if (isset($alternatives[$class])) {
        foreach ($alternatives[$class] as $alt) {
            foreach ($paths as $path) {
                $file = $path . $alt . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
        }
    }
    
    error_log("Autoload: No se pudo cargar la clase '{$class}'");
    return false;
});

// ==========================================
// CONFIGURACIÓN DE BASE DE DATOS
// ==========================================
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} else {
    die('Error: Archivo de configuración de base de datos no encontrado.');
}

// ==========================================
// PROCESAMIENTO DE RUTAS
// ==========================================

// Obtener parámetros de la URL
$controller = $_GET['controller'] ?? '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Si no hay controller en GET, procesar la URL
if (empty($controller)) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $base = '/proyecto/';
    
    if (strpos($requestUri, $base) === 0) {
        $path = substr($requestUri, strlen($base));
    } else {
        $path = $requestUri;
    }
    
    if (strpos($path, '?') !== false) {
        $path = substr($path, 0, strpos($path, '?'));
    }
    
    $path = trim($path, '/');
    
    // MAPEO DE RUTAS - CON PERFIL AGREGADO
    $routeMap = [
        // Autenticación
        'login' => ['controller' => 'auth', 'action' => 'login'],
        'auth/login' => ['controller' => 'auth', 'action' => 'login'],
        'auth/authenticate' => ['controller' => 'auth', 'action' => 'authenticate'],
        'logout' => ['controller' => 'auth', 'action' => 'logout'],
        'auth/logout' => ['controller' => 'auth', 'action' => 'logout'],
        
        // Dashboard
        'dashboard' => ['controller' => 'dashboard', 'action' => 'index'],
        'admin/dashboard' => ['controller' => 'admin', 'action' => 'dashboard'],
        
        // Reportes
        'reportes' => ['controller' => 'reporte', 'action' => 'index'],
        'reportes/ordenes' => ['controller' => 'reporte', 'action' => 'ordenes'],
        'reportes/tecnicos' => ['controller' => 'reporte', 'action' => 'tecnicos'],
        'reportes/exportar' => ['controller' => 'reporte', 'action' => 'exportar'],
        'reportes/imprimir' => ['controller' => 'reporte', 'action' => 'imprimir'],
        'reportes/financieros' => ['controller' => 'reporteFinanciero', 'action' => 'index'],
        'reportes/financiero' => ['controller' => 'reporteFinanciero', 'action' => 'index'],
        'reportes/financieros/exportar' => ['controller' => 'reporteFinanciero', 'action' => 'exportar'],
        
        // Órdenes
        'ordenes' => ['controller' => 'orden', 'action' => 'index'],
        'ordenes/crear' => ['controller' => 'orden', 'action' => 'crear'],
        'ordenes/guardar' => ['controller' => 'orden', 'action' => 'guardar'],
        'ordenes/ver' => ['controller' => 'orden', 'action' => 'ver'],
        'ordenes/editar' => ['controller' => 'orden', 'action' => 'editar'],
        'ordenes/actualizar' => ['controller' => 'orden', 'action' => 'actualizar'],
        'ordenes/cerrar' => ['controller' => 'orden', 'action' => 'cerrar'],
        'ordenes/eliminar' => ['controller' => 'orden', 'action' => 'eliminar'],
        'ordenes/estadisticas' => ['controller' => 'orden', 'action' => 'estadisticas'],
        
        // Técnicos
        'tecnicos' => ['controller' => 'tecnicos', 'action' => 'index'],
        'tecnicos/crear' => ['controller' => 'tecnicos', 'action' => 'crear'],
        'tecnicos/guardar' => ['controller' => 'tecnicos', 'action' => 'guardar'],
        'tecnicos/editar' => ['controller' => 'tecnicos', 'action' => 'editar'],
        'tecnicos/actualizar' => ['controller' => 'tecnicos', 'action' => 'actualizar'],
        'tecnicos/eliminar' => ['controller' => 'tecnicos', 'action' => 'eliminar'],
        
        // Supervisores
        'supervisores' => ['controller' => 'supervisores', 'action' => 'index'],
        'supervisores/crear' => ['controller' => 'supervisores', 'action' => 'crear'],
        'supervisores/editar' => ['controller' => 'supervisores', 'action' => 'editar'],
        'supervisores/guardar' => ['controller' => 'supervisores', 'action' => 'guardar'],
        'supervisores/actualizar' => ['controller' => 'supervisores', 'action' => 'actualizar'],
        'supervisores/eliminar' => ['controller' => 'supervisores', 'action' => 'eliminar'],
        
        // Supervisor (panel)
        'supervisor' => ['controller' => 'supervisor', 'action' => 'index'],
        'supervisor/ordenes' => ['controller' => 'supervisor', 'action' => 'ordenes'],
        'supervisor/revisar' => ['controller' => 'supervisor', 'action' => 'revisar'],
        'supervisor/supervisiones' => ['controller' => 'supervisor', 'action' => 'supervisiones'],
        
        // Técnico (panel)
        'tecnico' => ['controller' => 'tecnico', 'action' => 'index'],
        'tecnico/mis_ordenes' => ['controller' => 'tecnico', 'action' => 'mis_ordenes'],
        'tecnico/detalle_orden' => ['controller' => 'tecnico', 'action' => 'detalle_orden'],
        'tecnico/cerrar_orden' => ['controller' => 'tecnico', 'action' => 'cerrar_orden'],
        
        // Supervisión
        'supervision' => ['controller' => 'supervision', 'action' => 'index'],
        'supervision/crear' => ['controller' => 'supervision', 'action' => 'crear'],
        'supervision/ver' => ['controller' => 'supervision', 'action' => 'ver'],
        'supervision/aprobar' => ['controller' => 'supervision', 'action' => 'aprobar'],
        'supervision/rechazar' => ['controller' => 'supervision', 'action' => 'rechazar'],
        'supervision/reporte' => ['controller' => 'supervision', 'action' => 'reporte'],
        
        // Inventario
        'inventario' => ['controller' => 'inventario', 'action' => 'index'],
        'inventario/crear' => ['controller' => 'inventario', 'action' => 'crear'],
        'inventario/editar' => ['controller' => 'inventario', 'action' => 'editar'],
        'inventario/eliminar' => ['controller' => 'inventario', 'action' => 'eliminar'],
        
        // PERFIL DE USUARIO
        'perfil' => ['controller' => 'perfil', 'action' => 'index'],
        'perfil/editar' => ['controller' => 'perfil', 'action' => 'editar'],
        'perfil/actualizar' => ['controller' => 'perfil', 'action' => 'actualizar'],
        'perfil/cambiar_password' => ['controller' => 'perfil', 'action' => 'cambiarPassword'],
        'perfil/guardar_password' => ['controller' => 'perfil', 'action' => 'guardar_password'],
    ];
    
    if (isset($routeMap[$path])) {
        $controller = $routeMap[$path]['controller'];
        $action = $routeMap[$path]['action'];
        $id = null;
    } else {
        $segments = empty($path) ? [] : explode('/', $path);
        
        if (count($segments) >= 2) {
            $controller = $segments[0];
            $action = $segments[1] ?? 'index';
            $id = $segments[2] ?? null;
        } else {
            $controller = $segments[0] ?? 'dashboard';
            $action = 'index';
            $id = null;
        }
    }
}

$controller = !empty($controller) ? $controller : 'dashboard';
$action = !empty($action) ? $action : 'index';

// ==========================================
// VALIDACIÓN DE SEGURIDAD
// ==========================================
if (!preg_match('/^[a-zA-Z0-9_]+$/', $controller)) {
    error_log("Intento de acceso con controlador inválido: {$controller}");
    $controller = 'error';
    $action = 'error404';
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
    error_log("Intento de acceso con acción inválida: {$action}");
    $controller = 'error';
    $action = 'error404';
}

// ==========================================
// EJECUCIÓN DEL CONTROLADOR
// ==========================================
$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = __DIR__ . '/controller/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    error_log("Controlador no encontrado: {$controllerName}");
    $controllerName = 'ErrorController';
    $action = 'error404';
    $controllerFile = __DIR__ . '/controller/' . $controllerName . '.php';
    
    if (!file_exists($controllerFile)) {
        die("Error crítico: Controlador de errores no encontrado.");
    }
}

require_once $controllerFile;

if (!class_exists($controllerName)) {
    error_log("Clase no encontrada: {$controllerName}");
    $controllerName = 'ErrorController';
    $action = 'error404';
    
    if (!class_exists($controllerName)) {
        die("Error crítico: Clase de error no encontrada.");
    }
}

try {
    $controllerObj = new $controllerName();
} catch (Exception $e) {
    error_log("Error al instanciar controlador: " . $e->getMessage());
    $controllerName = 'ErrorController';
    $controllerObj = new $controllerName();
    $action = 'error500';
}

if (!method_exists($controllerObj, $action)) {
    error_log("Método no encontrado: {$action} en {$controllerName}");
    $action = 'error404';
    
    if (!method_exists($controllerObj, $action)) {
        die("Error crítico: Método de error no encontrado.");
    }
}

try {
    if ($id !== null && $id !== '') {
        $controllerObj->$action($id);
    } else {
        $controllerObj->$action();
    }
} catch (Exception $e) {
    error_log("Error en ejecución: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    if (class_exists('ErrorController')) {
        $errorController = new ErrorController();
        if (method_exists($errorController, 'error500')) {
            $errorController->error500();
        } else {
            die("Error 500: " . $e->getMessage());
        }
    } else {
        die("Error 500: " . $e->getMessage());
    }
}