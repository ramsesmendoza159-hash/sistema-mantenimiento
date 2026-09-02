<?php
// model/UsuariosModel.php
// VERSIÓN DEFINITIVA - FUNCIONANDO

require_once __DIR__ . '/../config/database.php';

class UsuariosModel {
    private $db;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Error al conectar a la base de datos (UsuariosModel): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ AUTENTICAR - VERSIÓN FUNCIONAL
     */
    public function autenticar($email, $password) {
        try {
            $sql = "SELECT id, nombre, email, password_hash, rol, estado 
                    FROM usuarios 
                    WHERE email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                error_log("Usuario no encontrado: " . $email);
                return false;
            }
            
            if ($usuario['estado'] !== 'activo') {
                error_log("Usuario inactivo: " . $email);
                return false;
            }
            
            if (!password_verify($password, $usuario['password_hash'])) {
                error_log("Contraseña incorrecta para: " . $email);
                return false;
            }
            
            unset($usuario['password_hash']);
            error_log("Autenticación exitosa para: " . $email);
            return $usuario;
            
        } catch (PDOException $e) {
            error_log("Error en autenticar: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error general en autenticar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ OBTENER USUARIO POR ID - CORREGIDO (sin apellido ni telefono)
     */
    public function obtenerPorId($id) {
        try {
            if (!is_numeric($id) || (int)$id <= 0) {
                error_log("obtenerPorId: ID inválido - $id");
                return null;
            }
            
            // ✅ SOLO campos que existen en la tabla
            $sql = "SELECT id, nombre, email, rol, estado, fecha_creacion, fecha_actualizacion
                    FROM usuarios 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => (int)$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("obtenerPorId: Usuario con ID $id no encontrado");
                return null;
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por email
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT id, nombre, email, rol, estado, fecha_creacion 
                    FROM usuarios WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEmail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT id FROM usuarios WHERE email = :email";
            $params = [':email' => $email];

            if ($excluirId) {
                $sql .= " AND id != :id";
                $params[':id'] = (int)$excluirId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() !== false;

        } catch (PDOException $e) {
            error_log("Error en emailExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function cambiarPassword($id, $password) {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET password_hash = :password_hash, fecha_actualizacion = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':password_hash' => $passwordHash,
                ':id' => (int)$id
            ]);

        } catch (PDOException $e) {
            error_log("Error en cambiarPassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario
     */
    public function actualizar($id, $datos) {
        try {
            // ✅ SOLO campos que existen en la tabla
            $sql = "UPDATE usuarios SET 
                        nombre = :nombre,
                        email = :email,
                        fecha_actualizacion = NOW()";
            
            $params = [
                ':nombre' => $datos['nombre'] ?? '',
                ':email' => $datos['email'] ?? '',
                ':id' => (int)$id
            ];
            
            if (!empty($datos['password'])) {
                $sql .= ", password_hash = :password_hash";
                $params[':password_hash'] = password_hash($datos['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("Error en actualizar usuario: " . $e->getMessage());
            return false;
        }
    }
}
// ✅ CIERRE DE LA CLASE