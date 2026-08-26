<?php
// helpers/HashHelper.php
// Helper unificado para manejar hashes en toda la aplicación

class HashHelper {
    
    /**
     * Generar hash de una contraseña usando bcrypt
     */
    public static function encrypt($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verificar una contraseña contra un hash
     */
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Verificar si un hash necesita ser rehasheado
     */
    public static function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
?>