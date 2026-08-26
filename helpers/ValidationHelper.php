<?php
// helpers/ValidationHelper.php
// Helper para validación de datos

class ValidationHelper {
    
    /**
     * Sanitizar entrada
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim(strip_tags($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar fecha
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validar campos requeridos
     */
    public static function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[] = "El campo '$field' es obligatorio";
            }
        }
        return $errors;
    }
    
    /**
     * Validar longitud
     */
    public static function validateLength($value, $min, $max) {
        $len = strlen(trim($value));
        return $len >= $min && $len <= $max;
    }
    
    /**
     * Validar número
     */
    public static function validateNumber($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        if ($min !== null && $value < $min) {
            return false;
        }
        if ($max !== null && $value > $max) {
            return false;
        }
        return true;
    }
    
    /**
     * Validar URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
?>