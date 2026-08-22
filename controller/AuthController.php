<?php
// controller/AuthController.php
// Ubicación: C:\xampp\htdocs\produmar\controller\AuthController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class AuthController extends Controller {
    
    private $usuariosModel;
    private $tecnicosModel;
    private $supervisoresModel;

    public function __construct() {
        parent::__construct();
        
        // Cargar modelos
        require_once __DIR__ . '/../model/UsuariosModel.php';
        require_once __DIR__ . '/../model/TecnicosModel.php';
        require_once __DIR__ . '/../model/SupervisoresModel.php';
        
        $this->usuariosModel = new UsuariosModel();
        $this->tecnicosModel = new TecnicosModel();
        $this->supervisoresModel = new SupervisoresModel();
    }

    /**
     * Mostrar página de login
     * URL: /auth/login
     */
    public function login() {
        // Si ya está logueado, redirigir
        if ($this->authHelper->isLoggedIn()) {
            $this->authHelper->redirectByRole();
            exit();
        }
        
        // Cargar la vista de login
        $this->view('auth/login');
    }

    /**
     * Procesar autenticación
     * URL: /auth/authenticate (POST)
     */
    public function authenticate() {
        $this->requirePost();

        $email = $this->post('email', '');
        $password = $this->post('password', '');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Por favor, ingresa tu email y contraseña.';
            $this->redirect('/produmar/auth/login');
            return;
        }

        // Buscar en usuarios (admin)
        $user = $this->usuariosModel->obtenerPorEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];
            
            $this->authHelper->redirectByRole();
            exit();
        }

        // Buscar en supervisores
        $supervisor = $this->supervisoresModel->obtenerPorEmail($email);
        if ($supervisor && password_verify($password, $supervisor['password_hash'])) {
            $_SESSION['usuario_id'] = $supervisor['id'];
            $_SESSION['nombre'] = $supervisor['nombre'];
            $_SESSION['email'] = $supervisor['email'];
            $_SESSION['rol'] = 'supervisor';
            
            $this->authHelper->redirectByRole();
            exit();
        }

        // Buscar en técnicos
        $tecnico = $this->tecnicosModel->obtenerPorEmail($email);
        if ($tecnico && password_verify($password, $tecnico['password_hash'])) {
            $_SESSION['usuario_id'] = $tecnico['id'];
            $_SESSION['nombre'] = $tecnico['nombre'];
            $_SESSION['email'] = $tecnico['email'];
            $_SESSION['rol'] = 'tecnico';
            
            $this->authHelper->redirectByRole();
            exit();
        }

        // Credenciales incorrectas
        $_SESSION['error'] = 'Email o contraseña incorrectos.';
        $this->redirect('/produmar/auth/login');
    }

    /**
     * Cerrar sesión
     * URL: /auth/logout
     */
    public function logout() {
        $this->authHelper->logout();
    }
}