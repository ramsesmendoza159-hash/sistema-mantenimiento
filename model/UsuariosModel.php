<?php
// model/UsuariosModel.php
// Ubicación: C:\xampp\htdocs\produmar\model\UsuariosModel.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class UsuariosModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener usuario por email (para login)
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEmail (Usuarios): " . $e->getMessage());
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
     * Crear nuevo usuario
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO usuarios (nombre, email, password_hash, rol, estado) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                trim($datos['nombre']),
                trim($datos['email']),
                $datos['password_hash'] ?? '',
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
     * Actualizar usuario
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
     * Actualizar contraseña
     */
    public function actualizarPassword($id, $passwordHash) {
        try {
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
                        SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as admins
                    FROM usuarios";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'admins' => 0];
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
}