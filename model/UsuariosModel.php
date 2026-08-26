<?php
// model/Usuario.php
// Modelo de usuarios - CORREGIDO

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/HashHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Autenticar usuario - MÉTODO CORREGIDO
     */
    public function autenticar($email, $password) {
        try {
            // Buscar usuario por email
            $sql = "SELECT id, nombre, email, password_hash, rol, estado 
                    FROM usuarios 
                    WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([SecurityHelper::sanitizeForDB($email)]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                error_log("Autenticación fallida: Usuario no encontrado - $email");
                return false;
            }
            
            // Verificar contraseña usando HashHelper
            if (HashHelper::verify($password, $usuario['password_hash'])) {
                // Si el hash necesita ser rehasheado
                if (HashHelper::needsRehash($usuario['password_hash'])) {
                    $this->actualizarHash($usuario['id'], $password);
                }
                return $usuario;
            }
            
            // COMPATIBILIDAD: MD5 (versiones anteriores)
            if (md5($password) === $usuario['password_hash']) {
                $nuevoHash = HashHelper::encrypt($password);
                $this->actualizarHash($usuario['id'], $password);
                error_log("Usuario migrado de MD5 a bcrypt: $email");
                return $usuario;
            }
            
            error_log("Autenticación fallida: Contraseña incorrecta - $email");
            return false;
            
        } catch (PDOException $e) {
            error_log("Error en autenticar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar hash de usuario
     */
    public function actualizarHash($usuario_id, $password) {
        try {
            $nuevoHash = HashHelper::encrypt($password);
            $sql = "UPDATE usuarios SET password_hash = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$nuevoHash, (int)$usuario_id]);
        } catch (PDOException $e) {
            error_log("Error en actualizarHash: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener usuario por email
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([SecurityHelper::sanitizeForDB($email)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEmail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM usuarios WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT * FROM usuarios WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = ?";
                $params[] = SecurityHelper::sanitizeForDB($filtros['estado']);
            }
            
            if (!empty($filtros['rol'])) {
                $sql .= " AND rol = ?";
                $params[] = SecurityHelper::sanitizeForDB($filtros['rol']);
            }
            
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
                $buscar = '%' . SecurityHelper::sanitizeForDB($filtros['buscar']) . '%';
                $params[] = $buscar;
                $params[] = $buscar;
            }
            
            $sql .= " ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear usuario con hash seguro
     */
    public function crear($datos) {
        try {
            // Validar datos
            $errores = $this->validarDatos($datos, true);
            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                return false;
            }
            
            $hash = HashHelper::encrypt($datos['password']);
            
            $sql = "INSERT INTO usuarios (nombre, email, password_hash, rol, estado) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                SecurityHelper::sanitizeForDB($datos['nombre']),
                SecurityHelper::sanitizeForDB($datos['email']),
                $hash,
                SecurityHelper::sanitizeForDB($datos['rol'] ?? 'usuario'),
                SecurityHelper::sanitizeForDB($datos['estado'] ?? 'activo')
            ]);
        } catch (PDOException $e) {
            error_log("Error en crear: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar($id, $datos) {
        try {
            $errores = $this->validarDatos($datos, false);
            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                return false;
            }
            
            $sql = "UPDATE usuarios SET 
                        nombre = ?,
                        email = ?,
                        rol = ?,
                        estado = ?,
                        updated_at = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                SecurityHelper::sanitizeForDB($datos['nombre']),
                SecurityHelper::sanitizeForDB($datos['email']),
                SecurityHelper::sanitizeForDB($datos['rol'] ?? 'usuario'),
                SecurityHelper::sanitizeForDB($datos['estado'] ?? 'activo'),
                (int)$id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar datos
     */
    private function validarDatos($datos, $esCreacion = true) {
        $errores = [];
        
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es obligatorio';
        }
        
        if (empty($datos['email']) || !ValidationHelper::validateEmail($datos['email'])) {
            $errores[] = 'El email no es válido';
        }
        
        if ($esCreacion && empty($datos['password'])) {
            $errores[] = 'La contraseña es obligatoria';
        }
        
        if ($esCreacion && strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        return $errores;
    }
}
?>