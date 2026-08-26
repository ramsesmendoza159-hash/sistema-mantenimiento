<?php
// model/TecnicosModel.php
// Ubicación: C:\xampp\htdocs\proyecto\model\TecnicosModel.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class TecnicosModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener técnico por email (para login)
     */
    public function obtenerPorEmail($email) {
        try {
            $sql = "SELECT * FROM tecnicos WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEmail (Tecnicos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener técnico por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM tecnicos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
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

            if (!empty($filtros['activo'])) {
                $sql .= " AND estado = 'activo'";
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
     * Obtener técnicos con conteo de órdenes
     */
    public function obtenerTodosConOrdenes($limit = null, $offset = null) {
        try {
            $sql = "SELECT t.*, COUNT(om.id) as total_ordenes 
                    FROM tecnicos t 
                    LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id 
                    GROUP BY t.id 
                    ORDER BY t.nombre ASC";
            
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
     * Obtener total de técnicos
     */
    public function obtenerTotal() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM tecnicos");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en obtenerTotal: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crear nuevo técnico
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO tecnicos (nombre, email, telefono, especialidad, password_hash, estado) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                trim($datos['nombre']),
                trim($datos['email']),
                $datos['telefono'] ?? '',
                $datos['especialidad'] ?? '',
                $datos['password_hash'] ?? '',
                $datos['estado'] ?? 'activo'
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear: " . $e->getMessage());
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
                        telefono = ?,
                        especialidad = ?,
                        estado = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                trim($datos['nombre']),
                trim($datos['email']),
                $datos['telefono'] ?? '',
                $datos['especialidad'] ?? '',
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
            $sql = "UPDATE tecnicos SET password_hash = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$passwordHash, $id]);
        } catch (PDOException $e) {
            error_log("Error en actualizarPassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado del técnico
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE tecnicos SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $id]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
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
            error_log("Error en eliminar: " . $e->getMessage());
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
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }

    /**
     * Obtener técnicos con más órdenes completadas
     */
    public function obtenerTopTecnicos($limite = 5) {
        try {
            $sql = "SELECT 
                        t.id,
                        t.nombre,
                        t.especialidad,
                        COUNT(om.id) as total_ordenes,
                        SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) as completadas,
                        ROUND(SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(om.id), 0), 1) as eficiencia
                    FROM tecnicos t
                    LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id
                    WHERE t.estado = 'activo'
                    GROUP BY t.id
                    HAVING total_ordenes > 0
                    ORDER BY completadas DESC, eficiencia DESC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTopTecnicos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener órdenes asignadas a un técnico
     */
    public function obtenerOrdenesPorTecnico($tecnicoId, $limit = null, $offset = null) {
        try {
            $sql = "SELECT om.*, 
                           p.nombre_planta,
                           a.nombre_area,
                           e.nombre_equipo,
                           c.nombre_componente
                    FROM ordenes_mantenimiento om
                    LEFT JOIN plantas p ON om.id_planta = p.id_planta
                    LEFT JOIN areas a ON om.id_area = a.id_area
                    LEFT JOIN equipos e ON om.id_equipo = e.id_equipo
                    LEFT JOIN componentes c ON om.id_componente = c.id_componente
                    WHERE om.tecnico_id = ?
                    ORDER BY om.fecha_creacion DESC";
            
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$tecnicoId, $limit, $offset]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$tecnicoId]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerOrdenesPorTecnico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar técnicos por nombre o email
     */
    public function buscar($termino) {
        try {
            $sql = "SELECT * FROM tecnicos 
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
     * Obtener técnicos por especialidad
     */
    public function obtenerPorEspecialidad($especialidad) {
        try {
            $sql = "SELECT * FROM tecnicos WHERE especialidad = ? AND estado = 'activo' ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$especialidad]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEspecialidad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las especialidades disponibles
     */
    public function obtenerEspecialidades() {
        try {
            $sql = "SELECT DISTINCT especialidad FROM tecnicos WHERE especialidad IS NOT NULL AND especialidad != '' ORDER BY especialidad";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error en obtenerEspecialidades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un email ya existe
     */
    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM tecnicos WHERE email = ?";
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