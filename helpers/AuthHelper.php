<?php
// helpers/AuthHelper.php

class AuthHelper {
    
    /**
     * Verificar si el usuario está logueado
     */
    public function isLoggedIn() {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }

    /**
     * Obtener el rol del usuario actual
     */
    public function getRole() {
        return isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
    }

    /**
     * Obtener el ID del usuario actual
     */
    public function getUserId() {
        return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    }

    /**
     * Obtener el nombre del usuario actual
     */
    public function getUsername() {
        return isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
    }

    /**
     * Obtener el email del usuario actual
     */
    public function getUserEmail() {
        return isset($_SESSION['email']) ? $_SESSION['email'] : null;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (is_array($role)) {
            return in_array($this->getRole(), $role);
        }
        
        return $this->getRole() === $role;
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }

    /**
     * Verificar si el usuario es supervisor
     */
    public function isSupervisor() {
        return $this->hasRole('supervisor');
    }

    /**
     * Verificar si el usuario es técnico
     */
    public function isTecnico() {
        return $this->hasRole('tecnico');
    }

    /**
     * Redirigir según el rol del usuario
     */
    public function redirectByRole() {
        if (!$this->isLoggedIn()) {
            header('Location: /produmar/auth/login');
            exit();
        }

        $role = $this->getRole();
        
        switch ($role) {
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
                header('Location: /produmar/dashboard');
                break;
        }
        exit();
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: /produmar/auth/login');
        exit();
    }

    /**
     * Verificar si el usuario tiene acceso a una sección
     */
    public function checkAccess($allowedRoles = []) {
        if (!$this->isLoggedIn()) {
            header('Location: /produmar/auth/login');
            exit();
        }

        if (!empty($allowedRoles) && !$this->hasRole($allowedRoles)) {
            $this->redirectByRole();
            exit();
        }

        return true;
    }

    /**
     * Obtener el tipo de usuario (admin, supervisor, tecnico)
     */
    public function getUserType() {
        if ($this->isAdmin()) return 'admin';
        if ($this->isSupervisor()) return 'supervisor';
        if ($this->isTecnico()) return 'tecnico';
        return 'usuario';
    }
}