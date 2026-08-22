<?php
// controller/DashboardController.php

require_once __DIR__ . '/../helpers/Controller.php';

class DashboardController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /produmar/auth/login');
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
                header('Location: /produmar/admin/dashboard');
                break;
            case 'supervisor':
                header('Location: /produmar/supervisor');
                break;
            case 'tecnico':
                header('Location: /produmar/tecnico');
                break;
            default:
                header('Location: /produmar/auth/login');
                break;
        }
        exit();
    }
}