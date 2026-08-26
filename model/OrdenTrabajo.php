<?php
// model/OrdenTrabajo.php
// Ubicación: C:\xampp\htdocs\proyecto\model\OrdenTrabajo.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';

class OrdenTrabajo {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las órdenes con filtros y datos de costos
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT o.*, 
                           t.nombre as tecnico_nombre,
                           s.nombre as supervisor_nombre,
                           p.nombre_planta,
                           a.nombre_area,
                           e.nombre_equipo,
                           c.nombre_componente,
                           (o.costo_repuestos + o.costo_mano_obra) as costo_total_calculado
                    FROM ordenes_mantenimiento o
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    LEFT JOIN supervisores s ON o.id_supervisor = s.id
                    LEFT JOIN plantas p ON o.id_planta = p.id_planta
                    LEFT JOIN areas a ON o.id_area = a.id_area
                    LEFT JOIN equipos e ON o.id_equipo = e.id_equipo
                    LEFT JOIN componentes c ON o.id_componente = c.id_componente
                    WHERE 1=1";
            $params = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND o.status = ?";
                $params[] = $filtros['status'];
            }

            if (!empty($filtros['buscar'])) {
                $sql .= " AND (o.num_om LIKE ? OR o.titulo LIKE ? OR o.descripcion_mantenimiento LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
                $params[] = $buscar;
                $params[] = $buscar;
                $params[] = $buscar;
            }

            if (!empty($filtros['tecnico_id'])) {
                $sql .= " AND o.tecnico_id = ?";
                $params[] = $filtros['tecnico_id'];
            }

            if (!empty($filtros['prioridad'])) {
                $sql .= " AND o.prioridad = ?";
                $params[] = $filtros['prioridad'];
            }

            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $sql .= " AND o.fecha_creacion BETWEEN ? AND ?";
                $params[] = $filtros['fecha_desde'] . ' 00:00:00';
                $params[] = $filtros['fecha_hasta'] . ' 23:59:59';
            }

            $sql .= " ORDER BY o.fecha_creacion DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (OrdenTrabajo): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener orden por ID - VERSIÓN CORREGIDA CON MANEJO DE ERRORES ROBUSTO
     * Esta versión maneja todos los casos de datos faltantes y errores de consulta
     */
    public function obtenerPorId($id) {
        try {
            // ✅ Primero obtener la orden básica
            $sql = "SELECT * FROM ordenes_mantenimiento WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                error_log("obtenerPorId: Orden con ID $id no encontrada");
                return false;
            }
            
            // ✅ Obtener datos relacionados por separado con manejo de errores
            
            // Técnico
            if (!empty($orden['tecnico_id'])) {
                try {
                    $sql = "SELECT nombre, tarifa FROM tecnicos WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$orden['tecnico_id']]);
                    $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($tecnico) {
                        $orden['tecnico_nombre'] = $tecnico['nombre'];
                        $orden['tarifa_tecnico'] = $tecnico['tarifa'] ?? 0;
                    } else {
                        // Técnico no encontrado - usar valores por defecto
                        $orden['tecnico_nombre'] = 'Técnico ID: ' . $orden['tecnico_id'] . ' (no encontrado)';
                        $orden['tarifa_tecnico'] = 0;
                        error_log("obtenerPorId: Técnico ID " . $orden['tecnico_id'] . " no encontrado para orden $id");
                    }
                } catch (PDOException $e) {
                    error_log("obtenerPorId: Error al obtener técnico para orden $id: " . $e->getMessage());
                    $orden['tecnico_nombre'] = 'Error al cargar técnico';
                    $orden['tarifa_tecnico'] = 0;
                }
            } else {
                $orden['tecnico_nombre'] = 'Sin asignar';
                $orden['tarifa_tecnico'] = 0;
            }
            
            // Supervisor
            if (!empty($orden['id_supervisor'])) {
                try {
                    $sql = "SELECT nombre FROM supervisores WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$orden['id_supervisor']]);
                    $supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($supervisor) {
                        $orden['supervisor_nombre'] = $supervisor['nombre'];
                    } else {
                        $orden['supervisor_nombre'] = 'Supervisor ID: ' . $orden['id_supervisor'] . ' (no encontrado)';
                        error_log("obtenerPorId: Supervisor ID " . $orden['id_supervisor'] . " no encontrado para orden $id");
                    }
                } catch (PDOException $e) {
                    error_log("obtenerPorId: Error al obtener supervisor para orden $id: " . $e->getMessage());
                    $orden['supervisor_nombre'] = 'Error al cargar supervisor';
                }
            } else {
                $orden['supervisor_nombre'] = 'Sin asignar';
            }
            
            // Planta
            if (!empty($orden['id_planta'])) {
                try {
                    $sql = "SELECT nombre_planta FROM plantas WHERE id_planta = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$orden['id_planta']]);
                    $planta = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($planta) {
                        $orden['nombre_planta'] = $planta['nombre_planta'];
                    } else {
                        $orden['nombre_planta'] = 'Planta ID: ' . $orden['id_planta'] . ' (no encontrada)';
                        error_log("obtenerPorId: Planta ID " . $orden['id_planta'] . " no encontrada para orden $id");
                    }
                } catch (PDOException $e) {
                    error_log("obtenerPorId: Error al obtener planta para orden $id: " . $e->getMessage());
                    $orden['nombre_planta'] = 'Error al cargar planta';
                }
            } else {
                $orden['nombre_planta'] = 'Sin asignar';
            }
            
            // Área
            if (!empty($orden['id_area'])) {
                try {
                    $sql = "SELECT nombre_area FROM areas WHERE id_area = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$orden['id_area']]);
                    $area = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($area) {
                        $orden['nombre_area'] = $area['nombre_area'];
                    } else {
                        $orden['nombre_area'] = 'Área ID: ' . $orden['id_area'] . ' (no encontrada)';
                        error_log("obtenerPorId: Área ID " . $orden['id_area'] . " no encontrada para orden $id");
                    }
                } catch (PDOException $e) {
                    error_log("obtenerPorId: Error al obtener área para orden $id: " . $e->getMessage());
                    $orden['nombre_area'] = 'Error al cargar área';
                }
            } else {
                $orden['nombre_area'] = 'Sin asignar';
            }
            
            // Equipo
            if (!empty($orden['id_equipo'])) {
                try {
                    $sql = "SELECT nombre_equipo FROM equipos WHERE id_equipo = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$orden['id_equipo']]);
                    $equipo = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($equipo) {
                        $orden['nombre_equipo'] = $equipo['nombre_equipo'];
                    } else {
                        $orden['nombre_equipo'] = 'Equipo ID: ' . $orden['id_equipo'] . ' (no encontrado)';
                        error_log("obtenerPorId: Equipo ID " . $orden['id_equipo'] . " no encontrado para orden $id");
                    }
                } catch (PDOException $e) {
                    error_log("obtenerPorId: Error al obtener equipo para orden $id: " . $e->getMessage());
                    $orden['nombre_equipo'] = 'Error al cargar equipo';
                }
            } else {
                $orden['nombre_equipo'] = 'Sin asignar';
            }
            
            // Componente
            if (!empty($orden['id_componente'])) {
                try {
                    $sql = "SELECT nombre_componente FROM componentes WHERE id_componente = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$orden['id_componente']]);
                    $componente = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($componente) {
                        $orden['nombre_componente'] = $componente['nombre_componente'];
                    } else {
                        $orden['nombre_componente'] = 'Componente ID: ' . $orden['id_componente'] . ' (no encontrado)';
                        error_log("obtenerPorId: Componente ID " . $orden['id_componente'] . " no encontrado para orden $id");
                    }
                } catch (PDOException $e) {
                    error_log("obtenerPorId: Error al obtener componente para orden $id: " . $e->getMessage());
                    $orden['nombre_componente'] = 'Error al cargar componente';
                }
            } else {
                $orden['nombre_componente'] = 'Sin asignar';
            }
            
            // Calcular costo total
            $orden['costo_total_calculado'] = ($orden['costo_repuestos'] ?? 0) + ($orden['costo_mano_obra'] ?? 0);
            
            error_log("obtenerPorId: Orden con ID $id encontrada correctamente");
            return $orden;
            
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener orden por número de OM
     */
    public function obtenerPorNumOM($num_om) {
        try {
            $sql = "SELECT * FROM ordenes_mantenimiento WHERE num_om = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$num_om]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorNumOM: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nueva orden de trabajo con costos
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO ordenes_mantenimiento (
                        num_om, cantidad, mes, semana, fecha_emision, fecha_inicio, 
                        fecha_estimada, nombre_planta, nombre_area, nombre_equipo, 
                        nombre_componente, id_planta, id_area, id_equipo, id_componente,
                        titulo, descripcion_mantenimiento, tipo_actividad, 
                        tipo_mantenimiento, prioridad, solicitante, 
                        supervisor_solicitante, id_supervisor, tecnico_id,
                        horas_duracion, horas_trabajadas, tarifa_tecnico,
                        costo_total, costo_repuestos, costo_mano_obra,
                        status, creado_por
                    ) VALUES (
                        :num_om, :cantidad, :mes, :semana, :fecha_emision, :fecha_inicio, 
                        :fecha_estimada, :nombre_planta, :nombre_area, :nombre_equipo, 
                        :nombre_componente, :id_planta, :id_area, :id_equipo, :id_componente,
                        :titulo, :descripcion_mantenimiento, :tipo_actividad, 
                        :tipo_mantenimiento, :prioridad, :solicitante, 
                        :supervisor_solicitante, :id_supervisor, :tecnico_id,
                        :horas_duracion, :horas_trabajadas, :tarifa_tecnico,
                        :costo_total, :costo_repuestos, :costo_mano_obra,
                        :status, :creado_por
                    )";
            
            // Calcular costos si no vienen
            $horas = $datos['horas_trabajadas'] ?? 0;
            $tarifa = $datos['tarifa_tecnico'] ?? 0;
            $costo_repuestos = $datos['costo_repuestos'] ?? 0;
            $costo_mano_obra = $horas * $tarifa;
            $costo_total = $costo_repuestos + $costo_mano_obra;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'num_om' => $datos['num_om'],
                'cantidad' => $datos['cantidad'] ?? 1,
                'mes' => $datos['mes'] ?? '',
                'semana' => $datos['semana'] ?? '',
                'fecha_emision' => $datos['fecha_emision'] ?? date('Y-m-d'),
                'fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                'fecha_estimada' => $datos['fecha_estimada'] ?? null,
                'nombre_planta' => $datos['nombre_planta'] ?? '',
                'nombre_area' => $datos['nombre_area'] ?? '',
                'nombre_equipo' => $datos['nombre_equipo'] ?? '',
                'nombre_componente' => $datos['nombre_componente'] ?? '',
                'id_planta' => $datos['id_planta'] ?? null,
                'id_area' => $datos['id_area'] ?? null,
                'id_equipo' => $datos['id_equipo'] ?? null,
                'id_componente' => $datos['id_componente'] ?? null,
                'titulo' => $datos['titulo'],
                'descripcion_mantenimiento' => $datos['descripcion_mantenimiento'] ?? '',
                'tipo_actividad' => $datos['tipo_actividad'] ?? '',
                'tipo_mantenimiento' => $datos['tipo_mantenimiento'] ?? '',
                'prioridad' => $datos['prioridad'] ?? 'Media',
                'solicitante' => $datos['solicitante'] ?? '',
                'supervisor_solicitante' => $datos['supervisor_solicitante'] ?? '',
                'id_supervisor' => $datos['id_supervisor'] ?? null,
                'tecnico_id' => $datos['tecnico_id'] ?? null,
                'horas_duracion' => $datos['horas_duracion'] ?? 0,
                'horas_trabajadas' => $horas,
                'tarifa_tecnico' => $tarifa,
                'costo_total' => $costo_total,
                'costo_repuestos' => $costo_repuestos,
                'costo_mano_obra' => $costo_mano_obra,
                'status' => $datos['status'] ?? 'PENDIENTE',
                'creado_por' => $datos['creado_por'] ?? $_SESSION['usuario_id'] ?? 1
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear (OrdenTrabajo): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar orden de trabajo con costos
     */
    public function actualizar($id, $datos) {
        try {
            // Calcular costos
            $horas = $datos['horas_trabajadas'] ?? 0;
            $tarifa = $datos['tarifa_tecnico'] ?? 0;
            $costo_repuestos = $datos['costo_repuestos'] ?? 0;
            $costo_mano_obra = $horas * $tarifa;
            $costo_total = $costo_repuestos + $costo_mano_obra;

            $sql = "UPDATE ordenes_mantenimiento SET 
                        cantidad = ?,
                        mes = ?,
                        semana = ?,
                        fecha_emision = ?,
                        fecha_inicio = ?,
                        fecha_estimada = ?,
                        nombre_planta = ?,
                        nombre_area = ?,
                        nombre_equipo = ?,
                        nombre_componente = ?,
                        id_planta = ?,
                        id_area = ?,
                        id_equipo = ?,
                        id_componente = ?,
                        titulo = ?,
                        descripcion_mantenimiento = ?,
                        descripcion_realizada = ?,
                        tipo_actividad = ?,
                        tipo_mantenimiento = ?,
                        prioridad = ?,
                        solicitante = ?,
                        supervisor_solicitante = ?,
                        id_supervisor = ?,
                        tecnico_id = ?,
                        horas_duracion = ?,
                        horas_trabajadas = ?,
                        tarifa_tecnico = ?,
                        costo_total = ?,
                        costo_repuestos = ?,
                        costo_mano_obra = ?,
                        status = ?,
                        observaciones_tecnico = ?,
                        observaciones_cierre = ?,
                        actualizado_por = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['cantidad'] ?? 1,
                $datos['mes'] ?? '',
                $datos['semana'] ?? '',
                $datos['fecha_emision'] ?? date('Y-m-d'),
                $datos['fecha_inicio'] ?? date('Y-m-d'),
                $datos['fecha_estimada'] ?? null,
                $datos['nombre_planta'] ?? '',
                $datos['nombre_area'] ?? '',
                $datos['nombre_equipo'] ?? '',
                $datos['nombre_componente'] ?? '',
                $datos['id_planta'] ?? null,
                $datos['id_area'] ?? null,
                $datos['id_equipo'] ?? null,
                $datos['id_componente'] ?? null,
                $datos['titulo'] ?? '',
                $datos['descripcion_mantenimiento'] ?? '',
                $datos['descripcion_realizada'] ?? '',
                $datos['tipo_actividad'] ?? '',
                $datos['tipo_mantenimiento'] ?? '',
                $datos['prioridad'] ?? 'Media',
                $datos['solicitante'] ?? '',
                $datos['supervisor_solicitante'] ?? '',
                $datos['id_supervisor'] ?? null,
                $datos['tecnico_id'] ?? null,
                $datos['horas_duracion'] ?? 0,
                $horas,
                $tarifa,
                $costo_total,
                $costo_repuestos,
                $costo_mano_obra,
                $datos['status'] ?? 'PENDIENTE',
                $datos['observaciones_tecnico'] ?? '',
                $datos['observaciones_cierre'] ?? '',
                $datos['actualizado_por'] ?? $_SESSION['usuario_id'] ?? 1,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar (OrdenTrabajo): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cerrar orden de trabajo con costos
     */
    public function cerrar($id, $datos) {
        try {
            // Calcular costos
            $horas = $datos['horas_trabajadas'] ?? 0;
            $tarifa = $datos['tarifa_tecnico'] ?? 0;
            $costo_repuestos = $datos['costo_repuestos'] ?? 0;
            $costo_mano_obra = $horas * $tarifa;
            $costo_total = $costo_repuestos + $costo_mano_obra;

            $sql = "UPDATE ordenes_mantenimiento SET 
                        descripcion_realizada = ?,
                        pasos_ejecutados = ?,
                        horas_trabajadas = ?,
                        tarifa_tecnico = ?,
                        costo_total = ?,
                        costo_repuestos = ?,
                        costo_mano_obra = ?,
                        foto_evidencia = ?,
                        firma_tecnico = ?,
                        observaciones_tecnico = ?,
                        observaciones_cierre = ?,
                        status = 'CERRADA',
                        fecha_finalizacion = NOW(),
                        actualizado_por = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['descripcion_realizada'] ?? '',
                $datos['pasos_ejecutados'] ?? '',
                $horas,
                $tarifa,
                $costo_total,
                $costo_repuestos,
                $costo_mano_obra,
                $datos['foto_evidencia'] ?? '',
                $datos['firma_tecnico'] ?? '',
                $datos['observaciones_tecnico'] ?? '',
                $datos['observaciones_cierre'] ?? '',
                $datos['actualizado_por'] ?? $_SESSION['usuario_id'] ?? 1,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en cerrar (OrdenTrabajo): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado($id, $estado, $observaciones = null) {
        try {
            $sql = "UPDATE ordenes_mantenimiento SET 
                        status = ?,
                        observaciones_cierre = ?,
                        actualizado_por = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $estado,
                $observaciones,
                $_SESSION['usuario_id'] ?? 1,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asignar técnico a la orden
     */
    public function asignarTecnico($orden_id, $tecnico_id) {
        try {
            $sql = "UPDATE ordenes_mantenimiento SET 
                        tecnico_id = ?, 
                        status = 'EN_PROCESO', 
                        fecha_actualizacion = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$tecnico_id, $orden_id]);
        } catch (PDOException $e) {
            error_log("Error en asignarTecnico: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asignar supervisor a la orden
     */
    public function asignarSupervisor($orden_id, $supervisor_id) {
        try {
            $sql = "UPDATE ordenes_mantenimiento SET 
                        id_supervisor = ?, 
                        fecha_actualizacion = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$supervisor_id, $orden_id]);
        } catch (PDOException $e) {
            error_log("Error en asignarSupervisor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar orden
     */
    public function eliminar($id) {
        try {
            // Eliminar técnicos asociados
            $stmt = $this->db->prepare("DELETE FROM ordenes_tecnicos WHERE orden_id = ?");
            $stmt->execute([$id]);
            
            // Eliminar repuestos asociados
            $stmt = $this->db->prepare("DELETE FROM ordenes_repuestos WHERE orden_id = ?");
            $stmt->execute([$id]);
            
            // Eliminar historial
            $stmt = $this->db->prepare("DELETE FROM ordenes_historial WHERE orden_id = ?");
            $stmt->execute([$id]);
            
            // Eliminar la orden
            $sql = "DELETE FROM ordenes_mantenimiento WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de órdenes
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN status = 'EN_PROCESO' THEN 1 ELSE 0 END) as en_proceso,
                        SUM(CASE WHEN status = 'EJECUTADA' THEN 1 ELSE 0 END) as ejecutadas,
                        SUM(CASE WHEN status = 'CERRADA' THEN 1 ELSE 0 END) as cerradas,
                        SUM(CASE WHEN status = 'CANCELADA' THEN 1 ELSE 0 END) as canceladas,
                        SUM(CASE WHEN status = 'APROBADA' THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN status = 'RECHAZADA' THEN 1 ELSE 0 END) as rechazadas,
                        AVG(horas_trabajadas) as promedio_horas,
                        AVG(costo_total) as promedio_costo,
                        SUM(costo_total) as total_costos
                    FROM ordenes_mantenimiento";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'pendientes' => 0,
                'en_proceso' => 0,
                'ejecutadas' => 0,
                'cerradas' => 0,
                'canceladas' => 0,
                'aprobadas' => 0,
                'rechazadas' => 0,
                'promedio_horas' => 0,
                'promedio_costo' => 0,
                'total_costos' => 0
            ];
        }
    }

    /**
     * Obtener estadísticas financieras
     */
    public function obtenerEstadisticasFinancieras($fechaInicio = null, $fechaFin = null) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_ordenes,
                        COALESCE(SUM(costo_total), 0) as total_costos,
                        COALESCE(SUM(costo_repuestos), 0) as total_repuestos,
                        COALESCE(SUM(costo_mano_obra), 0) as total_mano_obra,
                        COALESCE(AVG(costo_total), 0) as promedio_costo,
                        COALESCE(AVG(horas_trabajadas), 0) as promedio_horas,
                        COALESCE(SUM(horas_trabajadas), 0) as total_horas
                    FROM ordenes_mantenimiento
                    WHERE status IN ('CERRADA', 'APROBADA', 'EJECUTADA')";
            $params = [];

            if ($fechaInicio && $fechaFin) {
                $sql .= " AND fecha_creacion BETWEEN ? AND ?";
                $params[] = $fechaInicio . ' 00:00:00';
                $params[] = $fechaFin . ' 23:59:59';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_ordenes' => (int)($result['total_ordenes'] ?? 0),
                'total_costos' => (float)($result['total_costos'] ?? 0),
                'total_repuestos' => (float)($result['total_repuestos'] ?? 0),
                'total_mano_obra' => (float)($result['total_mano_obra'] ?? 0),
                'promedio_costo' => round((float)($result['promedio_costo'] ?? 0), 2),
                'promedio_horas' => round((float)($result['promedio_horas'] ?? 0), 1),
                'total_horas' => round((float)($result['total_horas'] ?? 0), 1)
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasFinancieras: " . $e->getMessage());
            return [
                'total_ordenes' => 0,
                'total_costos' => 0,
                'total_repuestos' => 0,
                'total_mano_obra' => 0,
                'promedio_costo' => 0,
                'promedio_horas' => 0,
                'total_horas' => 0
            ];
        }
    }

    /**
     * Obtener costos por planta
     */
    public function obtenerCostosPorPlanta($fechaInicio = null, $fechaFin = null) {
        try {
            $sql = "SELECT 
                        nombre_planta,
                        COUNT(*) as total_ordenes,
                        SUM(costo_total) as total_costos,
                        SUM(costo_repuestos) as total_repuestos,
                        SUM(costo_mano_obra) as total_mano_obra
                    FROM ordenes_mantenimiento
                    WHERE status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND nombre_planta IS NOT NULL AND nombre_planta != ''";
            $params = [];

            if ($fechaInicio && $fechaFin) {
                $sql .= " AND fecha_creacion BETWEEN ? AND ?";
                $params[] = $fechaInicio . ' 00:00:00';
                $params[] = $fechaFin . ' 23:59:59';
            }

            $sql .= " GROUP BY nombre_planta ORDER BY total_costos DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCostosPorPlanta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener costos por técnico
     */
    public function obtenerCostosPorTecnico($fechaInicio = null, $fechaFin = null) {
        try {
            $sql = "SELECT 
                        t.nombre as tecnico,
                        COUNT(o.id) as total_ordenes,
                        SUM(o.costo_total) as total_costos,
                        SUM(o.horas_trabajadas) as total_horas,
                        AVG(o.horas_trabajadas) as promedio_horas,
                        SUM(o.costo_mano_obra) as total_mano_obra
                    FROM ordenes_mantenimiento o
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    WHERE o.status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND o.tecnico_id IS NOT NULL";
            $params = [];

            if ($fechaInicio && $fechaFin) {
                $sql .= " AND o.fecha_creacion BETWEEN ? AND ?";
                $params[] = $fechaInicio . ' 00:00:00';
                $params[] = $fechaFin . ' 23:59:59';
            }

            $sql .= " GROUP BY o.tecnico_id ORDER BY total_costos DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCostosPorTecnico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener costos por mes
     */
    public function obtenerCostosPorMes($anio = null) {
        try {
            if (!$anio) {
                $anio = date('Y');
            }
            
            $sql = "SELECT 
                        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                        COUNT(*) as total_ordenes,
                        SUM(costo_total) as total_costos,
                        SUM(costo_repuestos) as total_repuestos,
                        SUM(costo_mano_obra) as total_mano_obra
                    FROM ordenes_mantenimiento
                    WHERE status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND YEAR(fecha_creacion) = ?
                    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                    ORDER BY mes ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$anio]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCostosPorMes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener top de repuestos más utilizados
     */
    public function obtenerTopRepuestos($limite = 10) {
        try {
            $sql = "SELECT 
                        r.nombre,
                        r.codigo,
                        SUM(orp.cantidad) as total_utilizados,
                        SUM(orp.costo_total) as total_costos
                    FROM ordenes_repuestos orp
                    JOIN inventario r ON orp.repuesto_id = r.id
                    GROUP BY orp.repuesto_id
                    ORDER BY total_utilizados DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTopRepuestos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener órdenes por técnico
     */
    public function obtenerPorTecnico($tecnico_id, $limit = null, $offset = null) {
        try {
            $sql = "SELECT o.*, 
                           p.nombre_planta,
                           a.nombre_area,
                           e.nombre_equipo,
                           c.nombre_componente
                    FROM ordenes_mantenimiento o
                    LEFT JOIN plantas p ON o.id_planta = p.id_planta
                    LEFT JOIN areas a ON o.id_area = a.id_area
                    LEFT JOIN equipos e ON o.id_equipo = e.id_equipo
                    LEFT JOIN componentes c ON o.id_componente = c.id_componente
                    WHERE o.tecnico_id = ? 
                    ORDER BY o.fecha_creacion DESC";
            
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$tecnico_id, $limit, $offset]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$tecnico_id]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorTecnico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener órdenes para reporte
     */
    public function obtenerParaReporte($fecha_desde, $fecha_hasta, $estado = null) {
        try {
            $sql = "SELECT o.*, 
                           t.nombre as tecnico_nombre,
                           s.nombre as supervisor_nombre,
                           p.nombre_planta,
                           a.nombre_area,
                           e.nombre_equipo
                    FROM ordenes_mantenimiento o
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    LEFT JOIN supervisores s ON o.id_supervisor = s.id
                    LEFT JOIN plantas p ON o.id_planta = p.id_planta
                    LEFT JOIN areas a ON o.id_area = a.id_area
                    LEFT JOIN equipos e ON o.id_equipo = e.id_equipo
                    WHERE o.fecha_creacion BETWEEN ? AND ?";
            $params = [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59'];

            if ($estado) {
                $sql .= " AND o.status = ?";
                $params[] = $estado;
            }

            $sql .= " ORDER BY o.fecha_creacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerParaReporte: " . $e->getMessage());
            return [];
        }
    }
} // Fin de la clase OrdenTrabajo
?>