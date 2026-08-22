<?php
// test_db.php - Prueba de conexión a la base de datos

echo "<h1>Prueba de Conexión a la Base de Datos</h1>";

// Cargar configuración
require_once __DIR__ . '/config/database.php';

try {
    // Probar conexión
    $db = Database::getInstance()->getConnection();
    echo "✅ Conexión exitosa a la base de datos<br><br>";
    
    // Verificar tabla usuarios
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Tabla 'usuarios' tiene " . $result['total'] . " registros<br><br>";
    
    // Verificar usuario admin
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@produmar.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Usuario admin encontrado:<br>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Verificar contraseña
        $password = '123456';
        if (password_verify($password, $user['password_hash'])) {
            echo "✅ Contraseña correcta!<br>";
        } else {
            echo "❌ Contraseña incorrecta<br>";
            echo "Hash actual: " . $user['password_hash'] . "<br>";
        }
    } else {
        echo "❌ Usuario admin NO encontrado<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
}