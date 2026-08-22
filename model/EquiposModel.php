<?php
// model/EquiposModel.php
// Ubicación: C:\xampp\htdocs\produmar\model\EquiposModel.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class EquiposModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los equipos, opcionalmente filtrados por área
     */
    public function obtenerTodos($id_area = null)
    {
        try {
            $sql = "SELECT e.*, a.nombre_area, p.nombre_planta 
                    FROM equipos e
                    JOIN areas a ON e.id_area = a.id_area
                    JOIN plantas p ON a.id_planta = p.id_planta";
            $params = [];

            if ($id_area) {
                $sql .= " WHERE e.id_area = ?";
                $params[] = $id_area;
            }

            $sql .= " ORDER BY p.nombre_planta, a.nombre_area, e.nombre_equipo ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (Equipos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener equipo por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT e.*, a.nombre_area, p.nombre_planta 
                    FROM equipos e
                    JOIN areas a ON e.id_area = a.id_area
                    JOIN plantas p ON a.id_planta = p.id_planta
                    WHERE e.id_equipo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId (Equipos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener ID de equipo por nombre
     */
    public function obtenerIdPorNombre($nombre, $id_area = null)
    {
        try {
            $sql = "SELECT id_equipo FROM equipos WHERE nombre_equipo = ?";
            $params = [$nombre];

            if ($id_area) {
                $sql .= " AND id_area = ?";
                $params[] = $id_area;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['id_equipo'] : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerIdPorNombre (Equipos): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener equipos por área
     */
    public function obtenerPorArea($id_area)
    {
        try {
            $sql = "SELECT * FROM equipos WHERE id_area = ? ORDER BY nombre_equipo ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_area]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorArea (Equipos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener equipos con conteo de componentes
     */
    public function obtenerConConteo($id_area = null)
    {
        try {
            $sql = "SELECT e.*, 
                           a.nombre_area,
                           p.nombre_planta,
                           COUNT(c.id_componente) as total_componentes
                    FROM equipos e
                    JOIN areas a ON e.id_area = a.id_area
                    JOIN plantas p ON a.id_planta = p.id_planta
                    LEFT JOIN componentes c ON e.id_equipo = c.id_equipo";
            $params = [];

            if ($id_area) {
                $sql .= " WHERE e.id_area = ?";
                $params[] = $id_area;
            }

            $sql .= " GROUP BY e.id_equipo 
                      ORDER BY p.nombre_planta, a.nombre_area, e.nombre_equipo ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerConConteo (Equipos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar equipos por nombre
     */
    public function buscar($termino)
    {
        try {
            $sql = "SELECT e.*, a.nombre_area, p.nombre_planta 
                    FROM equipos e
                    JOIN areas a ON e.id_area = a.id_area
                    JOIN plantas p ON a.id_planta = p.id_planta
                    WHERE e.nombre_equipo LIKE ? 
                    ORDER BY e.nombre_equipo ASC
                    LIMIT 10";
            $buscar = '%' . $termino . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$buscar]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscar (Equipos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear nuevo equipo
     */
    public function crear($datos)
    {
        try {
            $sql = "INSERT INTO equipos (id_area, nombre_equipo, descripcion, modelo, marca, serie) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['id_area'],
                trim($datos['nombre']),
                $datos['descripcion'] ?? '',
                $datos['modelo'] ?? '',
                $datos['marca'] ?? '',
                $datos['serie'] ?? ''
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear (Equipos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar equipo
     */
    public function actualizar($id, $datos)
    {
        try {
            $sql = "UPDATE equipos SET 
                        id_area = ?,
                        nombre_equipo = ?,
                        descripcion = ?,
                        modelo = ?,
                        marca = ?,
                        serie = ?
                    WHERE id_equipo = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['id_area'],
                trim($datos['nombre']),
                $datos['descripcion'] ?? '',
                $datos['modelo'] ?? '',
                $datos['marca'] ?? '',
                $datos['serie'] ?? '',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar (Equipos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar equipo
     */
    public function eliminar($id)
    {
        try {
            // Verificar si tiene componentes asociados
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM componentes WHERE id_equipo = ?");
            $stmt->execute([$id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && $resultado['total'] > 0) {
                return ['error' => true, 'message' => 'No se puede eliminar el equipo porque tiene componentes asociados'];
            }

            $sql = "DELETE FROM equipos WHERE id_equipo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return ['error' => false, 'message' => 'Equipo eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en eliminar (Equipos): " . $e->getMessage());
            return ['error' => true, 'message' => 'Error al eliminar el equipo: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener estadísticas de equipos
     */
    public function obtenerEstadisticas()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_equipos,
                        COUNT(DISTINCT id_area) as total_areas,
                        SUM(CASE WHEN (SELECT COUNT(*) FROM componentes WHERE id_equipo = equipos.id_equipo) > 0 THEN 1 ELSE 0 END) as equipos_con_componentes,
                        SUM(CASE WHEN (SELECT COUNT(*) FROM componentes WHERE id_equipo = equipos.id_equipo) = 0 THEN 1 ELSE 0 END) as equipos_sin_componentes
                    FROM equipos";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas (Equipos): " . $e->getMessage());
            return [
                'total_equipos' => 0,
                'total_areas' => 0,
                'equipos_con_componentes' => 0,
                'equipos_sin_componentes' => 0
            ];
        }
    }
}