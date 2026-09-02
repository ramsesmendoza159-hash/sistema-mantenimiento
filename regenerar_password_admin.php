<?php
// test_tecnico_error.php
session_start();

echo "<h2>🔍 Diagnóstico del Panel Técnico</h2>";

// 1. Verificar sesión
echo "<h3>1. Sesión actual:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Verificar rol
echo "<h3>2. Rol del usuario:</h3>";
echo "Rol: " . ($_SESSION['rol'] ?? 'NO DEFINIDO') . "<br>";

// 3. Verificar que el controlador existe
$controllerFile = __DIR__ . '/controller/TecnicoController.php';
echo "<h3>3. TecnicoController.php:</h3>";
echo "Archivo existe: " . (file_exists($controllerFile) ? "✅ SÍ" : "❌ NO") . "<br>";

if (file_exists($controllerFile)) {
    // Buscar el método logout en el controlador
    $content = file_get_contents($controllerFile);
    if (strpos($content, 'logout') !== false) {
        echo "✅ Método 'logout' encontrado en TecnicoController<br>";
    } else {
        echo "❌ Método 'logout' NO encontrado en TecnicoController<br>";
    }
}

// 4. Verificar AuthHelper
$authHelper = __DIR__ . '/helpers/AuthHelper.php';
echo "<h3>4. AuthHelper.php:</h3>";
echo "Archivo existe: " . (file_exists($authHelper) ? "✅ SÍ" : "❌ NO") . "<br>";

// 5. Probar logout
echo "<h3>5. Probar logout:</h3>";
echo "<a href='/proyecto/auth/logout' class='btn btn-danger'>Cerrar Sesión (AuthController)</a><br>";
echo "<a href='/proyecto/tecnico/logout' class='btn btn-warning'>Cerrar Sesión (TecnicoController)</a><br>";

// 6. Verificar si hay algún error en la base de datos
echo "<h3>6. Verificar conexión a BD:</h3>";
try {
    require_once 'config/database.php';
    $db = Database::getInstance()->getConnection();
    echo "✅ Conexión a BD exitosa<br>";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}
?>