<?php
// test_curl_login.php
// Probar login con curl - VERSIÓN CORREGIDA

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión para guardar el token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers/SecurityHelper.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Test Login con Curl</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; overflow: auto; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🧪 Test de Login con Curl</h1>";

// Generar token
$token = SecurityHelper::generateCSRFToken();
echo "<p>Token generado: <code>$token</code></p>";

// Datos del formulario
$data = [
    'csrf_token' => $token,
    'email' => 'admin@proyecto.com',
    'password' => 'admin123'
];

echo "<h2>Datos enviados:</h2>";
echo "<div class='box'>";
echo "<pre>";
print_r($data);
echo "</pre>";
echo "</div>";

// Enviar petición con curl
$ch = curl_init('http://localhost/proyecto/auth/authenticate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "<h2>Resultado:</h2>";
echo "<p><strong>Código HTTP:</strong> " . $info['http_code'] . "</p>";
echo "<p><strong>URL final:</strong> " . $info['url'] . "</p>";

// Verificar si hubo redirección
if ($info['http_code'] == 302 || $info['http_code'] == 303) {
    // Buscar Location en los headers
    if (preg_match('/Location: ([^\r\n]+)/', $response, $matches)) {
        $location = trim($matches[1]);
        echo "<p><strong>Redirección a:</strong> <code>$location</code></p>";
        
        if (strpos($location, 'dashboard') !== false || strpos($location, 'admin') !== false) {
            echo "<p class='success'>✅ ¡Login EXITOSO! Redirige al dashboard</p>";
        } else {
            echo "<p class='error'>❌ Redirige a: $location (no es dashboard)</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Login FALLIDO (código: " . $info['http_code'] . ")</p>";
}

// Mostrar respuesta
echo "<h2>Headers de respuesta:</h2>";
echo "<div class='box'>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "</div>";

echo "<br>";
echo "<a href='/proyecto/login' class='btn' style='display:inline-block;padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:5px;'>🔐 Ir al Login</a>";
echo " ";
echo "<a href='/proyecto/dashboard' class='btn' style='display:inline-block;padding:10px 20px;background:#2196F3;color:white;text-decoration:none;border-radius:5px;'>📊 Ir al Dashboard</a>";

echo "</div></body></html>";
?>