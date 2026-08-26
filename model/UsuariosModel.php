<?php
// model/UsuariosModel.php
// Ubicación: C:\xampp\htdocs\proyecto\model\UsuariosModel.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class UsuariosModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Autenticar usuario por email y password
     * Este es el método principal para el login
     */
    public function autenticar($email, $password) {
        try {
            // Buscar usuario por email
            $sql = "SELECT * FROM usuarios WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                error_log("Autenticación fallida: Usuario no encontrado - $email");
                return false;
            }
            
            // Verificar contraseña usando password_verify nativo
            if (password_verify($password, $usuario['password_hash'])) {
                // Si el hash necesita ser rehasheado (actualizar a bcrypt más fuerte)
                if (password_needs_rehash($usuario['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
                    $nuevoHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $this->actualizarPassword($usuario['id'], $nuevoHash);
                }
                return $usuario;
            }
            
            // COMPATIBILIDAD: Si falla con bcrypt, probar con MD5 (versiones anteriores)
            if (md5($password) === $usuario['password_hash']) {
                // Migrar a bcrypt
                $nuevoHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $this->actualizarPassword($usuario['id'], $nuevoHash);
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
     * Obtener usuario por email
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
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
            $stmt->execute([$id]);
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
                $params[] = $filtros['estado'];
            }

            if (!empty($filtros['rol'])) {
                $sql .= " AND rol = ?";
                $params[] = $filtros['rol'];
            }

            if (!empty($filtros['buscar'])) {
                $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
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
     * Obtener total de usuarios
     */
    public function obtenerTotal() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en obtenerTotal: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crear nuevo usuario con hash de contraseña seguro
     */
    public function crear($datos) {
        try {
            // Generar hash con password_hash nativo
            $passwordHash = '';
            if (!empty($datos['password'])) {
                $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            } elseif (!empty($datos['password_hash'])) {
                $passwordHash = $datos['password_hash'];
            }

            $sql = "INSERT INTO usuarios (nombre, email, password_hash, rol, estado) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                trim($datos['nombre']),
                trim($datos['email']),
                $passwordHash,
                $datos['rol'] ?? 'usuario',
                $datos['estado'] ?? 'activo'
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario (sin cambiar contraseña)
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE usuarios SET 
                        nombre = ?,
                        email = ?,
                        rol = ?,
                        estado = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                trim($datos['nombre']),
                trim($datos['email']),
                $datos['rol'] ?? 'usuario',
                $datos['estado'] ?? 'activo',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar contraseña con hash seguro usando password_hash
     */
    public function actualizarPassword($id, $password) {
        try {
            // Si es texto plano (menos de 60 chars o no es bcrypt), hashearlo
            if (strlen($password) < 60 || strpos($password, '$2y$') !== 0) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            } else {
                $passwordHash = $password;
            }
            
            $sql = "UPDATE usuarios SET password_hash = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$passwordHash, $id]);
        } catch (PDOException $e) {
            error_log("Error en actualizarPassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado del usuario
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE usuarios SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $id]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM usuarios WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                        SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN rol = 'supervisor' THEN 1 ELSE 0 END) as supervisores,
                        SUM(CASE WHEN rol = 'tecnico' THEN 1 ELSE 0 END) as tecnicos
                    FROM usuarios";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'admins' => 0, 'supervisores' => 0, 'tecnicos' => 0];
        }
    }

    /**
     * Verificar si un email ya está registrado
     */
    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
            $params = [$email];
            
            if ($excluirId !== null) {
                $sql .= " AND id != ?";
                $params[] = $excluirId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['total'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en emailExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear usuario admin si no existe
     */
    public function crearAdminSiNoExiste() {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = 'admin@localhost'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (($result['total'] ?? 0) == 0) {
                $hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
                $sql = "INSERT INTO usuarios (nombre, email, password_hash, rol, estado) 
                        VALUES ('Administrador', 'admin@localhost', ?, 'admin', 'activo')";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$hash]);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en crearAdminSiNoExiste: " . $e->getMessage());
            return false;
        }
    }
}
?>