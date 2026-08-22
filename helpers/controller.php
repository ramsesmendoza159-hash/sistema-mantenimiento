<?php
// helpers/Controller.php

class Controller {
    protected $authHelper;

    public function __construct() {
        $this->authHelper = new AuthHelper();
    }

    /**
     * Cargar una vista
     */
    protected function view($view, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            // Extraer datos para la vista
            extract($data);
            require_once $viewFile;
        } else {
            die("Error: Vista no encontrada: " . $view);
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
     * Verificar si el usuario está autenticado
     */
    protected function requireAuth() {
        if (!$this->authHelper->isLoggedIn()) {
            $this->redirect('/produmar/auth/login');
        }
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    protected function requireRole($role) {
        $this->requireAuth();
        if (!$this->authHelper->hasRole($role)) {
            $this->redirect('/produmar/dashboard');
        }
    }

    /**
     * Verificar que sea una petición POST
     */
    protected function requirePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/produmar');
        }
    }

    /**
     * Obtener datos JSON de la petición
     */
    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }

    /**
     * Responder con JSON
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Obtener un parámetro GET
     */
    protected function get($key, $default = null) {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Obtener un parámetro POST
     */
    protected function post($key, $default = null) {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Obtener un archivo subido
     */
    protected function file($key) {
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }
}