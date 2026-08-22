<?php
// model/SupervisoresModel.php
// Ubicación: C:\xampp\htdocs\produmar\model\SupervisoresModel.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class SupervisoresModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener supervisor por email (para login)
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM supervisores WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEmail (Supervisores): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener supervisor por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM supervisores WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return false;
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
                $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
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
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener supervisores con conteo de órdenes
     */
    public function obtenerTodosConOrdenes($limit = null, $offset = null) {
        try {
            $sql = "SELECT s.*, COUNT(om.id) as total_ordenes 
                    FROM supervisores s 
                    LEFT JOIN ordenes_mantenimiento om ON s.id = om.id_supervisor 
                    GROUP BY s.id 
                    ORDER BY s.nombre ASC";
            
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$limit, $offset]);
            } else {
                $stmt = $this->db->query($sql);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodosConOrdenes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener total de supervisores
     */
    public function obtenerTotal() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM supervisores");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en obtenerTotal: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crear nuevo supervisor
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO supervisores (nombre, email, password_hash, area, estado) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                trim($datos['nombre']),
                trim($datos['email']),
                $datos['password_hash'] ?? '',
                $datos['area'] ?? null,
                $datos['estado'] ?? 'activo'
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar supervisor
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE supervisores SET 
                        nombre = ?,
                        email = ?,
                        area = ?,
                        estado = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                trim($datos['nombre']),
                trim($datos['email']),
                $datos['area'] ?? null,
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
            $sql = "UPDATE supervisores SET password_hash = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$passwordHash, $id]);
        } catch (PDOException $e) {
            error_log("Error en actualizarPassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado del supervisor
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE supervisores SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $id]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar supervisor
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM supervisores WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de supervisores
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos
                    FROM supervisores";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }

    /**
     * Obtener supervisores con más órdenes supervisadas
     */
    public function obtenerTopSupervisores($limite = 5) {
        try {
            $sql = "SELECT 
                        s.id,
                        s.nombre,
                        s.area,
                        COUNT(om.id) as total_ordenes,
                        SUM(CASE WHEN om.status IN ('APROBADA', 'RECHAZADA') THEN 1 ELSE 0 END) as evaluadas
                    FROM supervisores s
                    LEFT JOIN ordenes_mantenimiento om ON s.id = om.id_supervisor
                    WHERE s.estado = 'activo'
                    GROUP BY s.id
                    HAVING total_ordenes > 0
                    ORDER BY evaluadas DESC, total_ordenes DESC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTopSupervisores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener órdenes supervisadas por un supervisor
     */
    public function obtenerOrdenesPorSupervisor($supervisorId, $limit = null, $offset = null) {
        try {
            $sql = "SELECT om.*, 
                           p.nombre_planta,
                           a.nombre_area,
                           e.nombre_equipo,
                           t.nombre as tecnico_nombre
                    FROM ordenes_mantenimiento om
                    LEFT JOIN plantas p ON om.id_planta = p.id_planta
                    LEFT JOIN areas a ON om.id_area = a.id_area
                    LEFT JOIN equipos e ON om.id_equipo = e.id_equipo
                    LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                    WHERE om.id_supervisor = ?
                    ORDER BY om.fecha_creacion DESC";
            
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$supervisorId, $limit, $offset]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$supervisorId]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerOrdenesPorSupervisor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar supervisores por nombre o email
     */
    public function buscar($termino) {
        try {
            $sql = "SELECT * FROM supervisores 
                    WHERE (nombre LIKE ? OR email LIKE ?) 
                    AND estado = 'activo'
                    ORDER BY nombre ASC
                    LIMIT 10";
            $buscar = '%' . $termino . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$buscar, $buscar]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un email ya está registrado (excluyendo un ID opcional)
     */
    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM supervisores WHERE email = ?";
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