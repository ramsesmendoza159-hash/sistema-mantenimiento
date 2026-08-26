<?php
// controller/DashboardController.php

require_once __DIR__ . '/../helpers/Controller.php';

class DashboardController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Dashboard principal según rol
     * URL: /dashboard
     */
    public function index() {
        $rol = $this->authHelper->getRole();
        
        switch ($rol) {
            case 'admin':
                header('Location: /proyecto/admin/dashboard');
                break;
            case 'supervisor':
                header('Location: /proyecto/supervisor');
                break;
            case 'tecnico':
                header('Location: /proyecto/tecnico');
                break;
            default:
                header('Location: /proyecto/auth/login');
                break;
        }
        exit();
    }
}