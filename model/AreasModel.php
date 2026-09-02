<?php
// model/AreasModel.php
// Ubicación: C:\xampp\htdocs\proyecto\model\AreasModel.php
// VERSIÓN CORREGIDA CON FILTRO DE ESTADO

require_once __DIR__ . '/../config/database.php';

class AreasModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las áreas, opcionalmente filtradas por planta y estado
     */
    public function obtenerTodos($filtros = [])
    {
        try {
            $sql = "SELECT a.*, p.nombre_planta 
                    FROM areas a
                    JOIN plantas p ON a.id_planta = p.id_planta
                    WHERE 1=1";
            $params = [];

            if (!empty($filtros['id_planta'])) {
                $sql .= " AND a.id_planta = ?";
                $params[] = $filtros['id_planta'];
            }

            if (!empty($filtros['estado'])) {
                $sql .= " AND a.estado = ?";
                $params[] = $filtros['estado'];
            }

            if (!empty($filtros['buscar'])) {
                $sql .= " AND (a.nombre_area LIKE ? OR a.descripcion LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
                $params[] = $buscar;
                $params[] = $buscar;
            }

            $sql .= " ORDER BY p.nombre_planta, a.nombre_area ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (Areas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener áreas activas
     */
    public function obtenerActivos()
    {
        return $this->obtenerTodos(['estado' => 'activo']);
    }

    /**
     * Obtener área por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT a.*, p.nombre_planta 
                    FROM areas a
                    JOIN plantas p ON a.id_planta = p.id_planta
                    WHERE a.id_area = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId (Areas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener ID de área por nombre
     */
    public function obtenerIdPorNombre($nombre, $id_planta = null)
    {
        try {
            $sql = "SELECT id_area FROM areas WHERE nombre_area = ?";
            $params = [$nombre];

            if ($id_planta) {
                $sql .= " AND id_planta = ?";
                $params[] = $id_planta;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['id_area'] : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerIdPorNombre (Areas): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener áreas por planta
     */
    public function obtenerPorPlanta($id_planta)
    {
        try {
            $sql = "SELECT * FROM areas WHERE id_planta = ? ORDER BY nombre_area ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_planta]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorPlanta (Areas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener áreas con conteo de equipos
     */
    public function obtenerConConteo($filtros = [])
    {
        try {
            $sql = "SELECT a.*, 
                           COUNT(e.id_equipo) as total_equipos,
                           p.nombre_planta
                    FROM areas a
                    JOIN plantas p ON a.id_planta = p.id_planta
                    LEFT JOIN equipos e ON a.id_area = e.id_area
                    WHERE 1=1";
            $params = [];

            if (!empty($filtros['id_planta'])) {
                $sql .= " AND a.id_planta = ?";
                $params[] = $filtros['id_planta'];
            }

            if (!empty($filtros['estado'])) {
                $sql .= " AND a.estado = ?";
                $params[] = $filtros['estado'];
            }

            $sql .= " GROUP BY a.id_area 
                      ORDER BY p.nombre_planta, a.nombre_area ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerConConteo (Areas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear nueva área
     */
    public function crear($datos)
    {
        try {
            $sql = "INSERT INTO areas (id_planta, nombre_area, descripcion, estado) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['id_planta'],
                trim($datos['nombre']),
                $datos['descripcion'] ?? '',
                $datos['estado'] ?? 'activo'
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear (Areas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar área
     */
    public function actualizar($id, $datos)
    {
        try {
            $sql = "UPDATE areas SET 
                        id_planta = ?,
                        nombre_area = ?,
                        descripcion = ?,
                        estado = ?
                    WHERE id_area = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['id_planta'],
                trim($datos['nombre']),
                $datos['descripcion'] ?? '',
                $datos['estado'] ?? 'activo',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar (Areas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado del área
     */
    public function cambiarEstado($id, $estado)
    {
        try {
            $sql = "UPDATE areas SET estado = ? WHERE id_area = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $id]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado (Areas): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar área
     */
    public function eliminar($id)
    {
        try {
            // Verificar si tiene equipos asociados
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM equipos WHERE id_area = ?");
            $stmt->execute([$id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && $resultado['total'] > 0) {
                return ['error' => true, 'message' => 'No se puede eliminar el área porque tiene equipos asociados'];
            }

            $sql = "DELETE FROM areas WHERE id_area = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return ['error' => false, 'message' => 'Área eliminada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en eliminar (Areas): " . $e->getMessage());
            return ['error' => true, 'message' => 'Error al eliminar el área: ' . $e->getMessage()];
        }
    }

    /**
     * Buscar áreas por nombre
     */
    public function buscar($termino)
    {
        try {
            $sql = "SELECT a.*, p.nombre_planta 
                    FROM areas a
                    JOIN plantas p ON a.id_planta = p.id_planta
                    WHERE a.nombre_area LIKE ? 
                    ORDER BY a.nombre_area ASC
                    LIMIT 10";
            $buscar = '%' . $termino . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$buscar]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscar (Areas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de áreas
     */
    public function obtenerEstadisticas()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_areas,
                        COUNT(DISTINCT id_planta) as total_plantas,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activas,
                        SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivas,
                        SUM(CASE WHEN (SELECT COUNT(*) FROM equipos WHERE id_area = areas.id_area) > 0 THEN 1 ELSE 0 END) as areas_con_equipos,
                        SUM(CASE WHEN (SELECT COUNT(*) FROM equipos WHERE id_area = areas.id_area) = 0 THEN 1 ELSE 0 END) as areas_sin_equipos
                    FROM areas";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas (Areas): " . $e->getMessage());
            return [
                'total_areas' => 0,
                'total_plantas' => 0,
                'activas' => 0,
                'inactivas' => 0,
                'areas_con_equipos' => 0,
                'areas_sin_equipos' => 0
            ];
        }
    }
}
?>