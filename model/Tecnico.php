<?php
// model/Tecnico.php
// Modelo de técnicos - CORREGIDO

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/HashHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';

class Tecnico {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los técnicos con filtros
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT * FROM tecnicos WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = ?";
                $params[] = SecurityHelper::sanitizeForDB($filtros['estado']);
            }
            
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (nombre LIKE ? OR email LIKE ? OR especialidad LIKE ?)";
                $buscar = '%' . SecurityHelper::sanitizeForDB($filtros['buscar']) . '%';
                $params[] = $buscar;
                $params[] = $buscar;
                $params[] = $buscar;
            }
            
            if (!empty($filtros['especialidad'])) {
                $sql .= " AND especialidad = ?";
                $params[] = SecurityHelper::sanitizeForDB($filtros['especialidad']);
            }
            
            $sql .= " ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (Tecnico): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener técnico por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM tecnicos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId (Tecnico): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener técnico por email
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM tecnicos WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([SecurityHelper::sanitizeForDB($email)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEmail (Tecnico): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si email existe
     */
    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM tecnicos WHERE email = ?";
            $params = [SecurityHelper::sanitizeForDB($email)];
            
            if ($excluirId !== null) {
                $sql .= " AND id != ?";
                $params[] = (int)$excluirId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['total'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en emailExiste (Tecnico): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                        COUNT(DISTINCT especialidad) as especialidades
                    FROM tecnicos";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas (Tecnico): " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'especialidades' => 0];
        }
    }
    
    /**
     * Crear técnico
     */
    public function crear($datos) {
        try {
            // Validar datos
            if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
                return false;
            }
            
            $hash = HashHelper::encrypt($datos['password']);
            
            $sql = "INSERT INTO tecnicos (nombre, email, password_hash, especialidad, tarifa, telefono, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                SecurityHelper::sanitizeForDB($datos['nombre']),
                SecurityHelper::sanitizeForDB($datos['email']),
                $hash,
                SecurityHelper::sanitizeForDB($datos['especialidad'] ?? ''),
                (float)($datos['tarifa'] ?? 0),
                SecurityHelper::sanitizeForDB($datos['telefono'] ?? ''),
                SecurityHelper::sanitizeForDB($datos['estado'] ?? 'activo')
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear (Tecnico): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar técnico
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE tecnicos SET 
                        nombre = ?,
                        email = ?,
                        especialidad = ?,
                        tarifa = ?,
                        telefono = ?,
                        estado = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            $params = [
                SecurityHelper::sanitizeForDB($datos['nombre']),
                SecurityHelper::sanitizeForDB($datos['email']),
                SecurityHelper::sanitizeForDB($datos['especialidad'] ?? ''),
                (float)($datos['tarifa'] ?? 0),
                SecurityHelper::sanitizeForDB($datos['telefono'] ?? ''),
                SecurityHelper::sanitizeForDB($datos['estado'] ?? 'activo'),
                (int)$id
            ];
            
            // Si se proporcionó contraseña
            if (!empty($datos['password'])) {
                $hash = HashHelper::encrypt($datos['password']);
                $sql = str_replace('WHERE id = ?', 'password_hash = ?, WHERE id = ?', $sql);
                array_splice($params, 0, 0, [$hash]);
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en actualizar (Tecnico): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE tecnicos SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                SecurityHelper::sanitizeForDB($estado),
                (int)$id
            ]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado (Tecnico): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar técnico
     */
    public function eliminar($id) {
        try {
            // Verificar si tiene órdenes asignadas
            $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE tecnico_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (($result['total'] ?? 0) > 0) {
                $_SESSION['error'] = 'No se puede eliminar: tiene órdenes asignadas';
                return false;
            }
            
            $sql = "DELETE FROM tecnicos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$id]);
        } catch (PDOException $e) {
            error_log("Error en eliminar (Tecnico): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar contraseña
     */
    public function actualizarPassword($id, $password) {
        try {
            $hash = HashHelper::encrypt($password);
            $sql = "UPDATE tecnicos SET password_hash = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hash, (int)$id]);
        } catch (PDOException $e) {
            error_log("Error en actualizarPassword (Tecnico): " . $e->getMessage());
            return false;
        }
    }
}
?>