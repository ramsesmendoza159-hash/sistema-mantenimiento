<?php
// helpers/ValidationHelper.php
// Ubicación: C:\xampp\htdocs\proyecto\helpers\ValidationHelper.php

class ValidationHelper
{
    /**
     * Validar que un campo no esté vacío
     */
    public static function required($value): bool
    {
        if (is_array($value)) {
            return !empty($value);
        }
        return trim((string)$value) !== '';
    }

    /**
     * Validar que un campo tenga una longitud mínima
     */
    public static function minLength($value, int $min): bool
    {
        return strlen((string)$value) >= $min;
    }

    /**
     * Validar que un campo tenga una longitud máxima
     */
    public static function maxLength($value, int $max): bool
    {
        return strlen((string)$value) <= $max;
    }

    /**
     * Validar que un campo sea un email válido
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar que un campo sea un número entero
     */
    public static function integer($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validar que un campo sea un número decimal
     */
    public static function float($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * Validar que un campo sea una fecha válida
     */
    public static function date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validar que un campo esté en un arreglo de valores permitidos
     */
    public static function inArray($value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    /**
     * Validar que un campo sea un número entre un rango
     */
    public static function between($value, int $min, int $max): bool
    {
        $value = (int)$value;
        return $value >= $min && $value <= $max;
    }

    /**
     * Validar que un campo sea un teléfono válido
     */
    public static function phone(string $phone): bool
    {
        return preg_match('/^[0-9+\-\s()]{7,20}$/', $phone) === 1;
    }

    /**
     * Validar que un campo sea una URL válida
     */
    public static function url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validar todos los campos de un arreglo
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;

            foreach ($ruleSet as $rule) {
                if (is_string($rule)) {
                    // Regla simple: 'required', 'email', etc.
                    $method = $rule;
                    if (method_exists(self::class, $method)) {
                        if (!self::$method($value)) {
                            $errors[$field][] = "El campo {$field} no es válido para la regla {$rule}";
                            break;
                        }
                    }
                } elseif (is_array($rule)) {
                    // Regla con parámetros: ['minLength', 6]
                    $method = $rule[0];
                    $params = array_slice($rule, 1);
                    if (method_exists(self::class, $method)) {
                        if (!self::$method($value, ...$params)) {
                            $errors[$field][] = "El campo {$field} no cumple con la regla {$method}";
                            break;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Sanitizar una cadena
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar un arreglo completo
     */
    public static function sanitizeArray(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::sanitizeArray($value);
            } else {
                $result[$key] = self::sanitize((string)$value);
            }
        }
        return $result;
    }
}