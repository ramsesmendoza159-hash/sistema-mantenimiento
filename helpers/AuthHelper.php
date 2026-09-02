<?php
// helpers/AuthHelper.php
// VERSIÓN COMPLETA CON TODOS LOS ROLES

class AuthHelper {
    
    /**
     * Verificar si el usuario está logueado
     */
    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }

    /**
     * Obtener el rol del usuario actual
     */
    public function getRole() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
    }

    /**
     * Obtener el ID del usuario actual
     */
    public function getUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    }

    /**
     * Obtener el nombre del usuario actual
     */
    public function getUsername() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
    }

    /**
     * Obtener el email del usuario actual
     */
    public function getUserEmail() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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

    // ✅ VERIFICACIONES POR ROL
    public function isAdmin() {
        return $this->hasRole('admin');
    }

    public function isSupervisor() {
        return $this->hasRole('supervisor');
    }

    public function isTecnico() {
        return $this->hasRole('tecnico');
    }

    public function isAlmacen() {
        return $this->hasRole('almacen');
    }

    public function isOperador() {
        return $this->hasRole('operador');
    }

    public function isConsultor() {
        return $this->hasRole('consultor');
    }

    // ✅ PERMISOS ESPECÍFICOS
    public function puedeCrearOrden() {
        return $this->isAdmin() || $this->isOperador();
    }

    public function puedeEditarOrdenEnProceso() {
        return $this->isAdmin();
    }

    public function puedeAprobarOrden() {
        return $this->isAdmin() || $this->isSupervisor();
    }

    public function puedeVerTodasOrdenes() {
        return $this->isAdmin() || $this->isConsultor();
    }

    public function puedeGestionarInventario() {
        return $this->isAdmin() || $this->isAlmacen();
    }

    /**
     * Redirigir según el rol del usuario
     */
    public function redirectByRole() {
        if (!$this->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit();
        }

        $role = $this->getRole();
        
        switch ($role) {
            case 'admin':
                header('Location: /proyecto/dashboard');
                break;
            case 'supervisor':
                header('Location: /proyecto/supervisor');
                break;
            case 'tecnico':
                header('Location: /proyecto/tecnico');
                break;
            case 'almacen':
                header('Location: /proyecto/almacen');
                break;
            case 'operador':
                header('Location: /proyecto/operador');
                break;
            case 'consultor':
                header('Location: /proyecto/consultor');
                break;
            default:
                header('Location: /proyecto/dashboard');
                break;
        }
        exit();
    }

    /**
     * Verificar si el usuario tiene acceso a una sección
     */
    public function checkAccess($allowedRoles = []) {
        if (!$this->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit();
        }

        if (!empty($allowedRoles) && !$this->hasRole($allowedRoles)) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            $this->redirectByRole();
            exit();
        }

        return true;
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: /proyecto/auth/login');
        exit();
    }
}
?>