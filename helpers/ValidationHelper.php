<?php
// helpers/ValidationHelper.php
// VERSIÓN COMPLETA

class ValidationHelper {
    
    public static function sanitize($input) {
        if (is_array($input)) return array_map([self::class, 'sanitize'], $input);
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    public static function validateNumber($value, $min = null, $max = null) {
        if (!is_numeric($value)) return false;
        if ($min !== null && $value < $min) return false;
        if ($max !== null && $value > $max) return false;
        return true;
    }
    
    public static function validateLength($input, $min, $max) {
        $length = strlen(trim($input));
        return $length >= $min && $length <= $max;
    }
    
    public static function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field]) && $data[$field] !== '0') {
                $errors[$field] = "El campo '$field' es requerido";
            }
        }
        return $errors;
    }
}