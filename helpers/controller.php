<?php
// helpers/Controller.php
// Ubicación: C:\xampp\htdocs\proyecto\helpers\Controller.php

// Incluir AuthHelper
require_once __DIR__ . '/AuthHelper.php';

class Controller {
    
    protected $authHelper;

    public function __construct() {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->authHelper = new AuthHelper();
    }

    /**
     * Cargar una vista
     */
    protected function view($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: {$view}");
        }
    }

    /**
     * Redirigir a una URL
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Verificar que la solicitud sea POST
     */
    protected function requirePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.0 405 Method Not Allowed');
            die('Método no permitido');
        }
    }

    /**
     * Obtener un valor POST
     */
    protected function post($key, $default = null) {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Obtener un valor GET
     */
    protected function get($key, $default = null) {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Responder con JSON
     */
    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }
}