<?php
// crear_usuarios_faltantes.php - Crear usuarios que faltan

require_once 'config/database.php';
require_once 'model/UsuariosModel.php';

echo "========================================\n";
echo "  CREAR USUARIOS FALTANTES\n";
echo "========================================\n\n";

try {
    $usuarioModel = new UsuariosModel();
    
    // Lista de usuarios a crear
    $usuarios = [
        ['nombre' => 'William Gomez', 'email' => 'william@proyecto.com', 'rol' => 'admin'],
        ['nombre' => 'Harold Garcia', 'email' => 'harold@proyecto.com', 'rol' => 'tecnico'],
        ['nombre' => 'Santiago Lopez', 'email' => 'santiago@proyecto.com', 'rol' => 'tecnico'],
        ['nombre' => 'Luis Fiestas', 'email' => 'lfiestas@proyecto.com', 'rol' => 'tecnico'],
        ['nombre' => 'Melvin Lazaro', 'email' => 'melvin@proyecto.com', 'rol' => 'tecnico'],
        ['nombre' => 'Leyla Guzman', 'email' => 'leyla@proyecto.com', 'rol' => 'supervisor'],
        ['nombre' => 'Carlos Ruiz', 'email' => 'carlos.ruiz@proyecto.com', 'rol' => 'tecnico'],
        ['nombre' => 'Maria Torres', 'email' => 'maria.torres@proyecto.com', 'rol' => 'tecnico'],
    ];
    
    $creados = 0;
    $existentes = 0;
    
    foreach ($usuarios as $data) {
        // Verificar si el usuario ya existe
        $existe = $usuarioModel->emailExiste($data['email']);
        
        if ($existe) {
            echo "⚠️ Usuario ya existe: {$data['email']}\n";
            $existentes++;
            continue;
        }
        
        // Crear el usuario
        $datos = [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => '123456',
            'rol' => $data['rol'],
            'estado' => 'activo'
        ];
        
        $id = $usuarioModel->crear($datos);
        
        if ($id) {
            echo "✅ Usuario creado: {$data['nombre']} ({$data['email']}) - Rol: {$data['rol']}\n";
            $creados++;
        } else {
            echo "❌ Error al crear: {$data['email']}\n";
        }
    }
    
    echo "\n========================================\n";
    echo "  RESUMEN\n";
    echo "========================================\n";
    echo "✅ Usuarios creados: $creados\n";
    echo "⚠️ Usuarios existentes: $existentes\n";
    
    // Mostrar todos los usuarios ahora
    echo "\n========================================\n";
    echo "  USUARIOS EN EL SISTEMA\n";
    echo "========================================\n";
    $todos = $usuarioModel->obtenerTodos();
    foreach ($todos as $usuario) {
        $emoji = $usuario['rol'] == 'admin' ? '👑' : ($usuario['rol'] == 'supervisor' ? '👔' : '🔧');
        echo "  $emoji {$usuario['nombre']} ({$usuario['email']}) - Rol: {$usuario['rol']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>