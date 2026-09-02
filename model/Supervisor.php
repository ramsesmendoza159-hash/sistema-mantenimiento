<?php
// model/Supervisor.php
// Modelo de supervisores - VERSIÓN CORREGIDA

require_once __DIR__ . '/../config/database.php';

class Supervisor {
    private $db;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Error al conectar a la base de datos (Supervisor): " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener todos los supervisores con filtros
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT * FROM supervisores WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = ?";
                $params[] = $filtros['estado'];
            }
            
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (nombre LIKE ? OR email LIKE ? OR area LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
                $params[] = $buscar;
                $params[] = $buscar;
                $params[] = $buscar;
            }
            
            if (!empty($filtros['area'])) {
                $sql .= " AND area = ?";
                $params[] = $filtros['area'];
            }
            
            $sql .= " ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (Supervisor): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener supervisor por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM supervisores WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId (Supervisor): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener supervisor por email
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM supervisores WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEmail (Supervisor): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verificar si email existe
     */
    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM supervisores WHERE email = ?";
            $params = [$email];
            
            if ($excluirId !== null) {
                $sql .= " AND id != ?";
                $params[] = (int)$excluirId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['total'] ?? 0) > 0;
            
        } catch (PDOException $e) {
            error_log("Error en emailExiste (Supervisor): " . $e->getMessage());
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
                        COUNT(DISTINCT area) as areas
                    FROM supervisores";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas (Supervisor): " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'areas' => 0];
        }
    }
    
    /**
     * Obtener áreas únicas
     */
    public function obtenerAreas() {
        try {
            $sql = "SELECT DISTINCT area FROM supervisores WHERE area != '' AND area IS NOT NULL ORDER BY area ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerAreas (Supervisor): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear supervisor
     */
    public function crear($datos) {
        try {
            // Validar datos
            if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
                error_log("Error en crear (Supervisor): Datos incompletos");
                return false;
            }
            
            // Hashear contraseña
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO supervisores (nombre, email, password_hash, area, telefono, estado) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $datos['nombre'],
                $datos['email'],
                $passwordHash,
                $datos['area'] ?? '',
                $datos['telefono'] ?? '',
                $datos['estado'] ?? 'activo'
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            error_log("Error al ejecutar insert en crear (Supervisor)");
            return false;
            
        } catch (PDOException $e) {
            error_log("Error en crear (Supervisor): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar supervisor
     */
    public function actualizar($id, $datos) {
        try {
            // Construir consulta SQL dinámicamente
            $sql = "UPDATE supervisores SET 
                        nombre = :nombre,
                        email = :email,
                        area = :area,
                        telefono = :telefono,
                        estado = :estado,
                        fecha_actualizacion = NOW()";
            
            $params = [
                ':nombre' => $datos['nombre'],
                ':email' => $datos['email'],
                ':area' => $datos['area'] ?? '',
                ':telefono' => $datos['telefono'] ?? '',
                ':estado' => $datos['estado'] ?? 'activo',
                ':id' => (int)$id
            ];
            
            // Si se proporcionó contraseña
            if (!empty($datos['password'])) {
                $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
                $sql .= ", password_hash = :password_hash";
                $params[':password_hash'] = $passwordHash;
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error en actualizar (Supervisor): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE supervisores SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, (int)$id]);
            
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado (Supervisor): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar supervisor
     */
    public function eliminar($id) {
        try {
            // Verificar si tiene órdenes asignadas
            $sql = "SELECT COUNT(*) as total FROM ordenes_trabajo WHERE supervisor_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (($result['total'] ?? 0) > 0) {
                error_log("No se puede eliminar supervisor ID {$id}: tiene órdenes asignadas");
                return false;
            }
            
            $sql = "DELETE FROM supervisores WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$id]);
            
        } catch (PDOException $e) {
            error_log("Error en eliminar (Supervisor): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar contraseña
     */
    public function actualizarPassword($id, $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE supervisores SET password_hash = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hash, (int)$id]);
            
        } catch (PDOException $e) {
            error_log("Error en actualizarPassword (Supervisor): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar credenciales de login
     */
    public function verificarCredenciales($email, $password) {
        try {
            $sql = "SELECT * FROM supervisores WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$supervisor) {
                return null;
            }
            
            // Verificar contraseña
            if (!password_verify($password, $supervisor['password_hash'])) {
                return null;
            }
            
            return $supervisor;
            
        } catch (PDOException $e) {
            error_log("Error en verificarCredenciales (Supervisor): " . $e->getMessage());
            return null;
        }
    }
}
?>