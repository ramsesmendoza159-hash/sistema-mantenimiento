SecurityHelper<?php
// helpers/SecurityHelper.php
// Helper para seguridad

class SecurityHelper {
    
    /**
     * Prevenir XSS
     */
    public static function preventXSS($data) {
        if (is_array($data)) {
            return array_map([self::class, 'preventXSS'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verificar token CSRF
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generar token de recuperación
     */
    public static function generateRecoveryToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Sanitizar entrada para base de datos
     */
    public static function sanitizeForDB($data) {
        // Para usar con prepared statements, solo validar tipo
        if (is_string($data)) {
            return substr(trim($data), 0, 65535);
        }
        return $data;
    }
}
?>