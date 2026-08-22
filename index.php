<?php
// index.php - Raíz del proyecto
// Ubicación: C:\xampp\htdocs\produmar\index.php

// ==========================================
// INICIAR SESIÓN
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// CARGAR ARCHIVOS NECESARIOS
// ==========================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/AuthHelper.php';
require_once __DIR__ . '/helpers/Controller.php';

// ==========================================
// OBTENER PARÁMETROS DE LA URL
// ==========================================

// Si la URL está vacía, redirigir a login
if (empty($_GET) || !isset($_GET['controller'])) {
    header('Location: /produmar/auth/login');
    exit();
}

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'auth';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// ==========================================
// MAPEO DE CONTROLADORES
// ==========================================

$controllerMap = [
    // Autenticación
    'auth' => 'AuthController',
    
    // Admin
    'admin' => 'AdminController',
    'dashboard' => 'DashboardController',
    
    // Supervisores (gestión)
    'supervisores' => 'SupervisoresController',
    'supervisor' => 'SupervisorController',
    
    // Técnicos (gestión)
    'tecnicos' => 'TecnicosController',
    'tecnico' => 'TecnicoController',
    
    // Órdenes
    'orden' => 'OrdenController',
    'ordenes' => 'OrdenController',
    
    // Inventario
    'inventario' => 'InventarioController',
    
    // Reportes
    'reportes' => 'ReporteController',
    'reporteFinanciero' => 'ReporteFinancieroController',
    
    // Supervisión
    'supervision' => 'SupervisionController',
];

// Determinar el nombre de la clase del controlador
$controllerClass = isset($controllerMap[$controller]) ? $controllerMap[$controller] : ucfirst($controller) . 'Controller';

// ==========================================
// VERIFICAR Y CARGAR EL CONTROLADOR
// ==========================================

$controllerFile = __DIR__ . '/controller/' . $controllerClass . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "<h1>Error 404: Controlador no encontrado</h1>";
    echo "<p><strong>Controlador solicitado:</strong> " . htmlspecialchars($controller) . "</p>";
    echo "<p><strong>Clase buscada:</strong> " . htmlspecialchars($controllerClass) . "</p>";
    echo "<p><strong>Archivo buscado:</strong> " . htmlspecialchars($controllerFile) . "</p>";
    echo "<p><strong>Acción:</strong> " . htmlspecialchars($action) . "</p>";
    echo "<p><strong>ID:</strong> " . htmlspecialchars($id) . "</p>";
    exit();
}

require_once $controllerFile;

if (!class_exists($controllerClass)) {
    http_response_code(404);
    echo "<h1>Error 404: Clase no encontrada</h1>";
    echo "<p><strong>Clase:</strong> " . htmlspecialchars($controllerClass) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($controllerFile) . "</p>";
    exit();
}

// ==========================================
// INSTANCIAR Y EJECUTAR EL CONTROLADOR
// ==========================================

$controllerInstance = new $controllerClass();

if (!method_exists($controllerInstance, $action)) {
    http_response_code(404);
    echo "<h1>Error 404: Método no encontrado</h1>";
    echo "<p><strong>Controlador:</strong> " . htmlspecialchars($controllerClass) . "</p>";
    echo "<p><strong>Método:</strong> " . htmlspecialchars($action) . "</p>";
    echo "<p><strong>Métodos disponibles:</strong></p>";
    echo "<ul>";
    foreach (get_class_methods($controllerInstance) as $method) {
        if (strpos($method, '__') !== 0) {
            echo "<li>" . htmlspecialchars($method) . "</li>";
        }
    }
    echo "</ul>";
    exit();
}

// Ejecutar el método
if ($id !== null) {
    $controllerInstance->$action($id);
} else {
    $controllerInstance->$action();
}