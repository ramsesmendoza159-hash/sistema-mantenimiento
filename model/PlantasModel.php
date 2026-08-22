<?php
// model/PlantasModel.php
// Ubicación: C:\xampp\htdocs\produmar\model\PlantasModel.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class PlantasModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las plantas
     */
    public function obtenerTodos()
    {
        try {
            $sql = "SELECT * FROM plantas ORDER BY nombre_planta ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (Plantas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener planta por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT * FROM plantas WHERE id_planta = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId (Plantas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener planta por nombre
     */
    public function obtenerPorNombre($nombre)
    {
        try {
            $sql = "SELECT * FROM plantas WHERE nombre_planta = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorNombre (Plantas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener ID de planta por nombre
     */
    public function obtenerIdPorNombre($nombre)
    {
        try {
            $sql = "SELECT id_planta FROM plantas WHERE nombre_planta = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['id_planta'] : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerIdPorNombre (Plantas): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener plantas con conteo de áreas
     */
    public function obtenerConConteo()
    {
        try {
            $sql = "SELECT p.*, 
                           COUNT(a.id_area) as total_areas
                    FROM plantas p
                    LEFT JOIN areas a ON p.id_planta = a.id_planta
                    GROUP BY p.id_planta
                    ORDER BY p.nombre_planta ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerConConteo (Plantas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar plantas por nombre
     */
    public function buscar($termino)
    {
        try {
            $sql = "SELECT * FROM plantas 
                    WHERE nombre_planta LIKE ? 
                    ORDER BY nombre_planta ASC
                    LIMIT 10";
            $buscar = '%' . $termino . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$buscar]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscar (Plantas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear nueva planta
     */
    public function crear($datos)
    {
        try {
            $sql = "INSERT INTO plantas (nombre_planta, descripcion) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                trim($datos['nombre']),
                $datos['descripcion'] ?? ''
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear (Plantas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar planta
     */
    public function actualizar($id, $datos)
    {
        try {
            $sql = "UPDATE plantas SET nombre_planta = ?, descripcion = ? WHERE id_planta = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                trim($datos['nombre']),
                $datos['descripcion'] ?? '',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar (Plantas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar planta
     */
    public function eliminar($id)
    {
        try {
            // Verificar si tiene áreas asociadas
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM areas WHERE id_planta = ?");
            $stmt->execute([$id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && $resultado['total'] > 0) {
                return ['error' => true, 'message' => 'No se puede eliminar la planta porque tiene áreas asociadas'];
            }

            $sql = "DELETE FROM plantas WHERE id_planta = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return ['error' => false, 'message' => 'Planta eliminada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en eliminar (Plantas): " . $e->getMessage());
            return ['error' => true, 'message' => 'Error al eliminar la planta: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener estadísticas de plantas
     */
    public function obtenerEstadisticas()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_plantas,
                        SUM(CASE WHEN (SELECT COUNT(*) FROM areas WHERE id_planta = plantas.id_planta) > 0 THEN 1 ELSE 0 END) as plantas_con_areas,
                        SUM(CASE WHEN (SELECT COUNT(*) FROM areas WHERE id_planta = plantas.id_planta) = 0 THEN 1 ELSE 0 END) as plantas_sin_areas
                    FROM plantas";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas (Plantas): " . $e->getMessage());
            return [
                'total_plantas' => 0,
                'plantas_con_areas' => 0,
                'plantas_sin_areas' => 0
            ];
        }
    }

    /**
     * Verificar si una planta ya existe por nombre
     */
    public function existe($nombre, $excluirId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM plantas WHERE nombre_planta = ?";
            $params = [$nombre];
            
            if ($excluirId !== null) {
                $sql .= " AND id_planta != ?";
                $params[] = $excluirId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($resultado['total'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en existe (Plantas): " . $e->getMessage());
            return false;
        }
    }
}