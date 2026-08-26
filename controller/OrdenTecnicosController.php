<?php
// model/OrdenTecnicosModel.php
// Ubicación: C:\xampp\htdocs\proyecto\model\OrdenTecnicosModel.php

class OrdenTecnicosModel {
    private $db;

    public function __construct() {
        // Usar la misma conexión que el resto del proyecto
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Guardar técnico en una orden
     */
    public function guardar($datos) {
        $sql = "INSERT INTO ordenes_tecnicos (orden_id, tecnico_id, horas_trabajadas, tarifa_hora, costo_total) 
                VALUES (:orden_id, :tecnico_id, :horas_trabajadas, :tarifa_hora, :costo_total)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($datos);
    }

    /**
     * Obtener técnicos por orden
     */
    public function obtenerPorOrden($ordenId) {
        $sql = "SELECT ot.*, t.nombre as tecnico_nombre, t.especialidad, t.telefono 
                FROM ordenes_tecnicos ot 
                JOIN tecnicos t ON ot.tecnico_id = t.id 
                WHERE ot.orden_id = :orden_id
                ORDER BY ot.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['orden_id' => $ordenId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Eliminar técnicos por orden
     */
    public function eliminarPorOrden($ordenId) {
        $sql = "DELETE FROM ordenes_tecnicos WHERE orden_id = :orden_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['orden_id' => $ordenId]);
    }

    /**
     * Sumar costo total de mano de obra por orden
     */
    public function sumarManoObra($ordenId) {
        $sql = "SELECT SUM(costo_total) as total 
                FROM ordenes_tecnicos 
                WHERE orden_id = :orden_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['orden_id' => $ordenId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Actualizar técnico de una orden
     */
    public function actualizar($id, $datos) {
        $sql = "UPDATE ordenes_tecnicos 
                SET horas_trabajadas = :horas_trabajadas, 
                    tarifa_hora = :tarifa_hora,
                    costo_total = horas_trabajadas * tarifa_hora
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $datos['id'] = $id;
        return $stmt->execute($datos);
    }

    /**
     * Eliminar un técnico específico de una orden
     */
    public function eliminar($id) {
        $sql = "DELETE FROM ordenes_tecnicos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Guardar múltiples técnicos para una orden
     */
    public function guardarMultiples($ordenId, $tecnicos) {
        try {
            $sql = "INSERT INTO ordenes_tecnicos (orden_id, tecnico_id, horas_trabajadas, tarifa_hora, costo_total) 
                    VALUES (:orden_id, :tecnico_id, :horas_trabajadas, :tarifa_hora, :costo_total)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($tecnicos as $tecnico) {
                $costo_total = $tecnico['horas_trabajadas'] * $tecnico['tarifa_hora'];
                $stmt->execute([
                    'orden_id' => $ordenId,
                    'tecnico_id' => $tecnico['tecnico_id'],
                    'horas_trabajadas' => $tecnico['horas_trabajadas'],
                    'tarifa_hora' => $tecnico['tarifa_hora'],
                    'costo_total' => $costo_total
                ]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en guardarMultiples: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el técnico principal de una orden
     */
    public function obtenerTecnicoPrincipal($ordenId) {
        $sql = "SELECT ot.*, t.nombre as tecnico_nombre, t.especialidad, t.telefono 
                FROM ordenes_tecnicos ot 
                JOIN tecnicos t ON ot.tecnico_id = t.id 
                WHERE ot.orden_id = :orden_id 
                ORDER BY ot.id ASC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['orden_id' => $ordenId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si un técnico ya está asignado a una orden
     */
    public function existeEnOrden($ordenId, $tecnicoId) {
        $sql = "SELECT COUNT(*) as total FROM ordenes_tecnicos 
                WHERE orden_id = :orden_id AND tecnico_id = :tecnico_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'orden_id' => $ordenId,
            'tecnico_id' => $tecnicoId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Obtener total de horas trabajadas por un técnico en todas sus órdenes
     */
    public function obtenerTotalHorasTecnico($tecnicoId) {
        $sql = "SELECT SUM(horas_trabajadas) as total_horas 
                FROM ordenes_tecnicos 
                WHERE tecnico_id = :tecnico_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tecnico_id' => $tecnicoId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_horas'] ?? 0;
    }

    /**
     * Obtener todas las órdenes de un técnico
     */
    public function obtenerOrdenesPorTecnico($tecnicoId, $limit = null) {
        $sql = "SELECT ot.*, o.num_om, o.titulo, o.status, o.fecha_creacion 
                FROM ordenes_tecnicos ot 
                JOIN ordenes_mantenimiento o ON ot.orden_id = o.id 
                WHERE ot.tecnico_id = :tecnico_id 
                ORDER BY o.fecha_creacion DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tecnico_id', $tecnicoId, PDO::PARAM_INT);
        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}