<?php
// helpers/SecurityHelper.php
// VERSIÓN COMPLETA Y CORREGIDA

class SecurityHelper {
    
    /**
     * Verificar si el usuario tiene sesión activa
     */
    public static function verificarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }
    
    /**
     * Verificar el rol del usuario
     */
    public static function verificarRol($rolesPermitidos) {
        if (!self::verificarSesion()) {
            return false;
        }
        
        $rol = $_SESSION['rol'] ?? '';
        
        if (!is_array($rolesPermitidos)) {
            $rolesPermitidos = [$rolesPermitidos];
        }
        
        return in_array($rol, $rolesPermitidos);
    }
    
    /**
     * ✅ GENERAR TOKEN CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * ✅ VERIFICAR TOKEN CSRF
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitizar entrada
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Prevenir XSS
     */
    public static function preventXSS($input) {
        if (is_array($input)) {
            return array_map([self::class, 'preventXSS'], $input);
        }
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitizar para base de datos
     */
    public static function sanitizeForDB($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeForDB'], $input);
        }
        return trim(strip_tags($input));
    }
    
    /**
     * Verificar si el usuario es admin
     */
    public static function isAdmin() {
        if (!self::verificarSesion()) return false;
        return ($_SESSION['rol'] ?? '') === 'admin';
    }
    
    /**
     * Verificar si el usuario es supervisor
     */
    public static function isSupervisor() {
        if (!self::verificarSesion()) return false;
        return ($_SESSION['rol'] ?? '') === 'supervisor';
    }
    
    /**
     * Verificar si el usuario es tecnico
     */
    public static function isTecnico() {
        if (!self::verificarSesion()) return false;
        return ($_SESSION['rol'] ?? '') === 'tecnico';
    }
    
    /**
     * Obtener ID del usuario actual
     */
    public static function getUserId() {
        if (!self::verificarSesion()) return null;
        return $_SESSION['usuario_id'] ?? null;
    }
    
    /**
     * Obtener rol del usuario actual
     */
    public static function getRol() {
        if (!self::verificarSesion()) return null;
        return $_SESSION['rol'] ?? null;
    }
    
    /**
     * Redirigir si no está autenticado
     */
    public static function requireAuth() {
        if (!self::verificarSesion()) {
            $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página';
            header('Location: /proyecto/auth/login');
            exit;
        }
    }
    
    /**
     * Redirigir si no tiene el rol requerido
     */
    public static function requireRole($roles) {
        self::requireAuth();
        
        if (!self::verificarRol($roles)) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta página';
            header('Location: /proyecto/dashboard');
            exit;
        }
    }
}
?>