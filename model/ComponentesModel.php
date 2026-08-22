<?php
// model/ComponentesModel.php
// Ubicación: C:\xampp\htdocs\produmar\model\ComponentesModel.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class ComponentesModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los componentes
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT c.*, e.nombre_equipo, a.nombre_area, p.nombre_planta 
                    FROM componentes c
                    JOIN equipos e ON c.id_equipo = e.id_equipo
                    JOIN areas a ON e.id_area = a.id_area
                    JOIN plantas p ON a.id_planta = p.id_planta
                    WHERE 1=1";
            $params = [];

            if (!empty($filtros['id_equipo'])) {
                $sql .= " AND c.id_equipo = ?";
                $params[] = $filtros['id_equipo'];
            }

            if (!empty($filtros['buscar'])) {
                $sql .= " AND (c.nombre_componente LIKE ? OR c.descripcion LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
                $params[] = $buscar;
                $params[] = $buscar;
            }

            $sql .= " ORDER BY p.nombre_planta, a.nombre_area, e.nombre_equipo, c.nombre_componente ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (Componentes): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener componente por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT c.*, e.nombre_equipo, a.nombre_area, p.nombre_planta 
                    FROM componentes c
                    JOIN equipos e ON c.id_equipo = e.id_equipo
                    JOIN areas a ON e.id_area = a.id_area
                    JOIN plantas p ON a.id_planta = p.id_planta
                    WHERE c.id_componente = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId (Componentes): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener componentes por equipo
     */
    public function obtenerPorEquipo($id_equipo) {
        try {
            $sql = "SELECT * FROM componentes WHERE id_equipo = ? ORDER BY nombre_componente ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_equipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorEquipo (Componentes): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear nuevo componente
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO componentes (id_equipo, nombre_componente, descripcion) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['id_equipo'],
                trim($datos['nombre']),
                $datos['descripcion'] ?? ''
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear (Componentes): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar componente
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE componentes SET id_equipo = ?, nombre_componente = ?, descripcion = ? WHERE id_componente = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['id_equipo'],
                trim($datos['nombre']),
                $datos['descripcion'] ?? '',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar (Componentes): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar componente
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM componentes WHERE id_componente = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en eliminar (Componentes): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar componentes
     */
    public function buscar($termino) {
        try {
            $sql = "SELECT c.*, e.nombre_equipo 
                    FROM componentes c
                    JOIN equipos e ON c.id_equipo = e.id_equipo
                    WHERE c.nombre_componente LIKE ? 
                    ORDER BY c.nombre_componente ASC
                    LIMIT 10";
            $buscar = '%' . $termino . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$buscar]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscar (Componentes): " . $e->getMessage());
            return [];
        }
    }
}