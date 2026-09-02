<?php
// model/Tecnico.php
// Modelo de Técnicos - VERSIÓN ACTUALIZADA CON TARIFA

require_once __DIR__ . '/../config/database.php';

class Tecnico {
    private $db;
    private $lastError;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Error al conectar a la base de datos: " . $e->getMessage());
            throw $e;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * Obtener todos los técnicos con filtros - ✅ CON TARIFA
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT id, nombre, email, telefono, especialidad, tarifa, estado, fecha_creacion 
                    FROM tecnicos WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = ?";
                $params[] = $filtros['estado'];
            }
            
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (nombre LIKE ? OR email LIKE ? OR especialidad LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
                $params[] = $buscar;
                $params[] = $buscar;
                $params[] = $buscar;
            }
            
            if (!empty($filtros['especialidad'])) {
                $sql .= " AND especialidad = ?";
                $params[] = $filtros['especialidad'];
            }
            
            $sql .= " ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en Tecnico::obtenerTodos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener técnico por ID - ✅ CON TARIFA
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT id, nombre, email, telefono, especialidad, tarifa, estado, fecha_creacion 
                    FROM tecnicos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Tecnico::obtenerPorId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo técnico - ✅ CON TARIFA
     */
    public function crear($datos) {
        try {
            // Validar datos requeridos
            if (empty($datos['nombre'])) {
                $this->lastError = 'El nombre es obligatorio';
                return false;
            }
            if (empty($datos['email'])) {
                $this->lastError = 'El email es obligatorio';
                return false;
            }
            if (empty($datos['password'])) {
                $this->lastError = 'La contraseña es obligatoria';
                return false;
            }
            
            // ✅ CORREGIDO: Incluir tarifa en el INSERT
            $sql = "INSERT INTO tecnicos (nombre, email, telefono, especialidad, tarifa, password_hash, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $datos['nombre'],
                $datos['email'],
                $datos['telefono'] ?? '',
                $datos['especialidad'] ?? '',
                $datos['tarifa'] ?? 0,  // ✅ TARIFA INCLUIDA
                $datos['password'],
                $datos['estado'] ?? 'activo'
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            } else {
                $this->lastError = 'Error al ejecutar la inserción';
                error_log("Error en crear: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Error en Tecnico::crear: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Actualizar técnico - ✅ CON TARIFA
     */
    public function actualizar($id, $datos) {
        try {
            // ✅ CORREGIDO: Incluir tarifa en el UPDATE
            $sql = "UPDATE tecnicos SET 
                        nombre = ?,
                        email = ?,
                        telefono = ?,
                        especialidad = ?,
                        tarifa = ?,
                        estado = ?";
            $params = [
                $datos['nombre'],
                $datos['email'],
                $datos['telefono'] ?? '',
                $datos['especialidad'] ?? '',
                $datos['tarifa'] ?? 0,  // ✅ TARIFA INCLUIDA
                $datos['estado'] ?? 'activo'
            ];
            
            // Si se proporcionó nueva contraseña
            if (isset($datos['password']) && !empty($datos['password'])) {
                $sql .= ", password_hash = ?";
                $params[] = $datos['password'];
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error en Tecnico::actualizar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar técnico
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM tecnicos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en Tecnico::eliminar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si el email ya existe
     */
    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT id FROM tecnicos WHERE email = ?";
            $params = [$email];
            
            if ($excluirId) {
                $sql .= " AND id != ?";
                $params[] = $excluirId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() !== false;
            
        } catch (PDOException $e) {
            error_log("Error en Tecnico::emailExiste: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado del técnico
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE tecnicos SET estado = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $id]);
        } catch (PDOException $e) {
            error_log("Error en Tecnico::cambiarEstado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de técnicos
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos
                    FROM tecnicos";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'activos' => (int)($result['activos'] ?? 0),
                'inactivos' => (int)($result['inactivos'] ?? 0)
            ];
            
        } catch (PDOException $e) {
            error_log("Error en Tecnico::obtenerEstadisticas: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }
    
    /**
     * Obtener lista de especialidades únicas
     */
    public function obtenerEspecialidades() {
        try {
            $sql = "SELECT DISTINCT especialidad 
                    FROM tecnicos 
                    WHERE especialidad != '' AND especialidad IS NOT NULL
                    ORDER BY especialidad ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $result ?: [];
            
        } catch (PDOException $e) {
            error_log("Error en Tecnico::obtenerEspecialidades: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener técnicos activos - ✅ CON TARIFA
     */
    public function obtenerActivos() {
        try {
            $sql = "SELECT id, nombre, email, telefono, especialidad, tarifa 
                    FROM tecnicos WHERE estado = 'activo' ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Tecnico::obtenerActivos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener técnicos por especialidad - ✅ CON TARIFA
     */
    public function obtenerPorEspecialidad($especialidad) {
        try {
            $sql = "SELECT id, nombre, email, telefono, especialidad, tarifa 
                    FROM tecnicos WHERE especialidad = ? AND estado = 'activo' ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$especialidad]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Tecnico::obtenerPorEspecialidad: " . $e->getMessage());
            return [];
        }
    }
}
?>