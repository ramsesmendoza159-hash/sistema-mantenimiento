<?php
// controller/ErrorController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\ErrorController.php

require_once __DIR__ . '/../helpers/Controller.php';

class ErrorController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Mostrar página de error 404
     * URL: /error/404
     */
    public function error404() {
        http_response_code(404);
        
        $titulo = "Error 404 - Página no encontrada";
        $seccion = "error";
        
        // Cargar la vista de error
        $this->view('error/404');
    }

    /**
     * Mostrar página de error 403 (acceso denegado)
     * URL: /error/403
     */
    public function error403() {
        http_response_code(403);
        
        $titulo = "Error 403 - Acceso denegado";
        $seccion = "error";
        
        $this->view('error/403');
    }

    /**
     * Mostrar página de error 500 (error interno)
     * URL: /error/500
     */
    public function error500() {
        http_response_code(500);
        
        $titulo = "Error 500 - Error interno del servidor";
        $seccion = "error";
        
        $this->view('error/500');
    }
}