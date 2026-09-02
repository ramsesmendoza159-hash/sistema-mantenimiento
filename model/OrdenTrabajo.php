<?php
// model/OrdenTrabajo.php
// Modelo de Órdenes de Trabajo - VERSIÓN DEFINITIVA CORREGIDA CON ROLES
// Usa la tabla ordenes_mantenimiento

require_once __DIR__ . '/../config/database.php';

class OrdenTrabajo {
    private $db;
    
    // Constantes de estado (MAYÚSCULAS como en la tabla)
    const ESTADO_PENDIENTE = 'PENDIENTE';
    const ESTADO_EN_PROCESO = 'EN_PROCESO';
    const ESTADO_EJECUTADA = 'EJECUTADA';
    const ESTADO_CERRADA = 'CERRADA';
    const ESTADO_CANCELADA = 'CANCELADA';
    const ESTADO_APROBADA = 'APROBADA';
    const ESTADO_RECHAZADA = 'RECHAZADA';

    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Error al conectar a la base de datos (OrdenTrabajo): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Obtener todas las órdenes con filtros Y FILTRO POR ROL
     */
    public function obtenerTodos($filtros = [], $limit = null, $offset = null) {
        try {
            // ✅ Obtener el rol del usuario actual
            $rol = $_SESSION['rol'] ?? 'usuario';
            $usuarioId = $_SESSION['usuario_id'] ?? 0;
            
            $sql = "SELECT o.*, 
                           t.nombre as tecnico_nombre,
                           s.nombre as supervisor_nombre,
                           p.nombre_planta,
                           a.nombre_area,
                           e.nombre_equipo,
                           c.nombre_componente
                    FROM ordenes_mantenimiento o
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    LEFT JOIN supervisores s ON o.id_supervisor = s.id
                    LEFT JOIN plantas p ON o.id_planta = p.id_planta
                    LEFT JOIN areas a ON o.id_area = a.id_area
                    LEFT JOIN equipos e ON o.id_equipo = e.id_equipo
                    LEFT JOIN componentes c ON o.id_componente = c.id_componente
                    WHERE 1=1";
            $params = [];

            // ✅ FILTRO POR ROL
            if ($rol === 'tecnico') {
                // Técnico solo ve sus órdenes asignadas
                $sql .= " AND o.tecnico_id = ?";
                $params[] = (int)$usuarioId;
            } elseif ($rol === 'supervisor') {
                // Supervisor ve órdenes CERRADAS para revisar
                $sql .= " AND o.status = 'CERRADA'";
            }
            // Admin ve todas, sin filtro adicional

            // Aplicar filtros adicionales
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
                $params[] = (int)$filtros['tecnico_id'];
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
            
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = (int)$limit;
                $params[] = (int)$offset;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener orden por ID
     */
    public function obtenerPorId($id) {
        try {
            if (!is_numeric($id) || (int)$id <= 0) {
                error_log("obtenerPorId: ID inválido - $id");
                return false;
            }
            
            $sql = "SELECT o.*, 
                           t.nombre as tecnico_nombre,
                           s.nombre as supervisor_nombre,
                           p.nombre_planta,
                           a.nombre_area,
                           e.nombre_equipo,
                           c.nombre_componente
                    FROM ordenes_mantenimiento o
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    LEFT JOIN supervisores s ON o.id_supervisor = s.id
                    LEFT JOIN plantas p ON o.id_planta = p.id_planta
                    LEFT JOIN areas a ON o.id_area = a.id_area
                    LEFT JOIN equipos e ON o.id_equipo = e.id_equipo
                    LEFT JOIN componentes c ON o.id_componente = c.id_componente
                    WHERE o.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                error_log("obtenerPorId: Orden con ID $id no encontrada");
                return false;
            }
            
            return $orden;
            
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nueva orden de trabajo
     */
    public function crear($datos) {
        try {
            // Validar datos básicos
            if (empty($datos['descripcion_mantenimiento']) && empty($datos['descripcion'])) {
                $_SESSION['error'] = 'La descripción del mantenimiento es obligatoria';
                return false;
            }

            $descripcion = $datos['descripcion_mantenimiento'] ?? $datos['descripcion'] ?? '';

            $sql = "INSERT INTO ordenes_mantenimiento (
                        num_om,
                        titulo,
                        descripcion_mantenimiento,
                        tipo_mantenimiento,
                        tipo_actividad,
                        prioridad,
                        tecnico_id,
                        id_supervisor,
                        id_planta,
                        id_area,
                        id_equipo,
                        id_componente,
                        solicitante,
                        supervisor_solicitante,
                        fecha_inicio,
                        fecha_estimada,
                        horas_duracion,
                        tarifa_tecnico,
                        costo_repuestos,
                        status,
                        creado_por,
                        fecha_creacion
                    ) VALUES (
                        :num_om,
                        :titulo,
                        :descripcion_mantenimiento,
                        :tipo_mantenimiento,
                        :tipo_actividad,
                        :prioridad,
                        :tecnico_id,
                        :id_supervisor,
                        :id_planta,
                        :id_area,
                        :id_equipo,
                        :id_componente,
                        :solicitante,
                        :supervisor_solicitante,
                        :fecha_inicio,
                        :fecha_estimada,
                        :horas_duracion,
                        :tarifa_tecnico,
                        :costo_repuestos,
                        :status,
                        :creado_por,
                        NOW()
                    )";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'num_om' => $datos['num_om'] ?? $this->generarNumeroOM(),
                'titulo' => $datos['titulo'] ?? '',
                'descripcion_mantenimiento' => $descripcion,
                'tipo_mantenimiento' => $datos['tipo_mantenimiento'] ?? 'CORRECTIVO',
                'tipo_actividad' => $datos['tipo_actividad'] ?? '',
                'prioridad' => $datos['prioridad'] ?? 'Media',
                'tecnico_id' => !empty($datos['tecnico_id']) ? (int)$datos['tecnico_id'] : null,
                'id_supervisor' => !empty($datos['id_supervisor']) ? (int)$datos['id_supervisor'] : null,
                'id_planta' => !empty($datos['id_planta']) ? (int)$datos['id_planta'] : null,
                'id_area' => !empty($datos['id_area']) ? (int)$datos['id_area'] : null,
                'id_equipo' => !empty($datos['id_equipo']) ? (int)$datos['id_equipo'] : null,
                'id_componente' => !empty($datos['id_componente']) ? (int)$datos['id_componente'] : null,
                'solicitante' => $datos['solicitante'] ?? '',
                'supervisor_solicitante' => $datos['supervisor_solicitante'] ?? '',
                'fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                'fecha_estimada' => $datos['fecha_estimada'] ?? null,
                'horas_duracion' => (float)($datos['horas_duracion'] ?? 0),
                'tarifa_tecnico' => (float)($datos['tarifa_tecnico'] ?? 0),
                'costo_repuestos' => (float)($datos['costo_repuestos'] ?? 0),
                'status' => $datos['status'] ?? self::ESTADO_PENDIENTE,
                'creado_por' => (int)($datos['creado_por'] ?? $_SESSION['usuario_id'] ?? 1)
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            error_log("Error al crear orden de trabajo");
            return false;
            
        } catch (PDOException $e) {
            error_log("Error en crear: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear la orden: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Actualizar orden de trabajo
     */
    public function actualizar($id, $datos) {
        try {
            if (!is_numeric($id) || (int)$id <= 0) {
                $_SESSION['error'] = 'ID de orden inválido';
                return false;
            }

            $sql = "UPDATE ordenes_mantenimiento SET 
                        titulo = :titulo,
                        descripcion_mantenimiento = :descripcion_mantenimiento,
                        descripcion_realizada = :descripcion_realizada,
                        tipo_mantenimiento = :tipo_mantenimiento,
                        tipo_actividad = :tipo_actividad,
                        prioridad = :prioridad,
                        tecnico_id = :tecnico_id,
                        id_supervisor = :id_supervisor,
                        id_planta = :id_planta,
                        id_area = :id_area,
                        id_equipo = :id_equipo,
                        id_componente = :id_componente,
                        solicitante = :solicitante,
                        supervisor_solicitante = :supervisor_solicitante,
                        fecha_inicio = :fecha_inicio,
                        fecha_estimada = :fecha_estimada,
                        horas_duracion = :horas_duracion,
                        tarifa_tecnico = :tarifa_tecnico,
                        costo_repuestos = :costo_repuestos,
                        status = :status,
                        observaciones_tecnico = :observaciones_tecnico,
                        observaciones_cierre = :observaciones_cierre,
                        actualizado_por = :actualizado_por,
                        fecha_actualizacion = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => (int)$id,
                'titulo' => $datos['titulo'] ?? '',
                'descripcion_mantenimiento' => $datos['descripcion_mantenimiento'] ?? $datos['descripcion'] ?? '',
                'descripcion_realizada' => $datos['descripcion_realizada'] ?? '',
                'tipo_mantenimiento' => $datos['tipo_mantenimiento'] ?? 'CORRECTIVO',
                'tipo_actividad' => $datos['tipo_actividad'] ?? '',
                'prioridad' => $datos['prioridad'] ?? 'Media',
                'tecnico_id' => !empty($datos['tecnico_id']) ? (int)$datos['tecnico_id'] : null,
                'id_supervisor' => !empty($datos['id_supervisor']) ? (int)$datos['id_supervisor'] : null,
                'id_planta' => !empty($datos['id_planta']) ? (int)$datos['id_planta'] : null,
                'id_area' => !empty($datos['id_area']) ? (int)$datos['id_area'] : null,
                'id_equipo' => !empty($datos['id_equipo']) ? (int)$datos['id_equipo'] : null,
                'id_componente' => !empty($datos['id_componente']) ? (int)$datos['id_componente'] : null,
                'solicitante' => $datos['solicitante'] ?? '',
                'supervisor_solicitante' => $datos['supervisor_solicitante'] ?? '',
                'fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                'fecha_estimada' => $datos['fecha_estimada'] ?? null,
                'horas_duracion' => (float)($datos['horas_duracion'] ?? 0),
                'tarifa_tecnico' => (float)($datos['tarifa_tecnico'] ?? 0),
                'costo_repuestos' => (float)($datos['costo_repuestos'] ?? 0),
                'status' => $datos['status'] ?? self::ESTADO_PENDIENTE,
                'observaciones_tecnico' => $datos['observaciones_tecnico'] ?? '',
                'observaciones_cierre' => $datos['observaciones_cierre'] ?? '',
                'actualizado_por' => (int)($datos['actualizado_por'] ?? $_SESSION['usuario_id'] ?? 1)
            ]);
            
        } catch (PDOException $e) {
            error_log("Error en actualizar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar la orden: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Cerrar una orden de trabajo
     */
    public function cerrar($id, $datos) {
        try {
            if (!is_numeric($id) || (int)$id <= 0) {
                error_log("cerrar: ID inválido - $id");
                return false;
            }
            
            $descripcion_realizada = $datos['descripcion_realizada'] ?? '';
            $pasos_ejecutados = $datos['pasos_ejecutados'] ?? '';
            $horas_trabajadas = (float)($datos['horas_trabajadas'] ?? 0);
            $observaciones = $datos['observaciones_tecnico'] ?? '';
            
            $sqlCheck = "SELECT status FROM ordenes_mantenimiento WHERE id = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([(int)$id]);
            $orden = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                error_log("cerrar: Orden con ID $id no encontrada");
                $_SESSION['error'] = 'Orden no encontrada';
                return false;
            }
            
            $estados_validos = ['PENDIENTE', 'EN_PROCESO', 'EJECUTADA'];
            if (!in_array($orden['status'], $estados_validos)) {
                error_log("cerrar: Orden con ID $id en estado '" . $orden['status'] . "' no se puede cerrar");
                $_SESSION['error'] = 'La orden no se puede cerrar en su estado actual';
                return false;
            }
            
            $tarifa = (float)($datos['tarifa_tecnico'] ?? 0);
            $costo_repuestos = (float)($datos['costo_repuestos'] ?? 0);
            $costo_mano_obra = $horas_trabajadas * $tarifa;
            $costo_total = $costo_repuestos + $costo_mano_obra;

            $sql = "UPDATE ordenes_mantenimiento SET 
                        descripcion_realizada = :descripcion_realizada,
                        pasos_ejecutados = :pasos_ejecutados,
                        horas_trabajadas = :horas_trabajadas,
                        tarifa_tecnico = :tarifa_tecnico,
                        costo_total = :costo_total,
                        costo_repuestos = :costo_repuestos,
                        costo_mano_obra = :costo_mano_obra,
                        foto_evidencia = :foto_evidencia,
                        firma_tecnico = :firma_tecnico,
                        observaciones_tecnico = :observaciones_tecnico,
                        observaciones_cierre = :observaciones_cierre,
                        status = :status,
                        fecha_finalizacion = NOW(),
                        actualizado_por = :actualizado_por
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'descripcion_realizada' => $descripcion_realizada,
                'pasos_ejecutados' => $pasos_ejecutados,
                'horas_trabajadas' => $horas_trabajadas,
                'tarifa_tecnico' => $tarifa,
                'costo_total' => $costo_total,
                'costo_repuestos' => $costo_repuestos,
                'costo_mano_obra' => $costo_mano_obra,
                'foto_evidencia' => $datos['foto_evidencia'] ?? '',
                'firma_tecnico' => $datos['firma_tecnico'] ?? '',
                'observaciones_tecnico' => $observaciones,
                'observaciones_cierre' => $datos['observaciones_cierre'] ?? '',
                'status' => self::ESTADO_CERRADA,
                'actualizado_por' => (int)($datos['actualizado_por'] ?? $_SESSION['usuario_id'] ?? 1),
                'id' => (int)$id
            ]);
            
        } catch (PDOException $e) {
            error_log("Error en cerrar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cerrar la orden: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado($id, $estado, $observaciones = null) {
        try {
            $estados_validos = [
                self::ESTADO_PENDIENTE, self::ESTADO_EN_PROCESO, self::ESTADO_EJECUTADA,
                self::ESTADO_CERRADA, self::ESTADO_CANCELADA, self::ESTADO_APROBADA,
                self::ESTADO_RECHAZADA
            ];
            
            if (!in_array($estado, $estados_validos)) {
                $_SESSION['error'] = 'Estado inválido';
                return false;
            }
            
            $sql = "UPDATE ordenes_mantenimiento SET 
                        status = :status,
                        observaciones_cierre = :observaciones,
                        fecha_actualizacion = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'status' => $estado,
                'observaciones' => $observaciones ?? '',
                'id' => (int)$id
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
                        tecnico_id = :tecnico_id, 
                        status = :status,
                        fecha_actualizacion = NOW() 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'tecnico_id' => (int)$tecnico_id,
                'status' => self::ESTADO_EN_PROCESO,
                'id' => (int)$orden_id
            ]);
            
        } catch (PDOException $e) {
            error_log("Error en asignarTecnico: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar orden
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM ordenes_mantenimiento WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$id]);
            
        } catch (PDOException $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar número de OM automático
     */
    private function generarNumeroOM() {
        $anio = date('Y');
        $mes = date('m');
        $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE YEAR(fecha_creacion) = ? AND MONTH(fecha_creacion) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anio, $mes]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero = ($result['total'] ?? 0) + 1;
        return "OM-" . $anio . "-" . $mes . "-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener estadísticas de órdenes
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as en_proceso,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ejecutadas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cerradas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as canceladas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rechazadas
                    FROM ordenes_mantenimiento";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                self::ESTADO_PENDIENTE,
                self::ESTADO_EN_PROCESO,
                self::ESTADO_EJECUTADA,
                self::ESTADO_CERRADA,
                self::ESTADO_CANCELADA,
                self::ESTADO_APROBADA,
                self::ESTADO_RECHAZADA
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'pendientes' => (int)($result['pendientes'] ?? 0),
                'en_proceso' => (int)($result['en_proceso'] ?? 0),
                'ejecutadas' => (int)($result['ejecutadas'] ?? 0),
                'cerradas' => (int)($result['cerradas'] ?? 0),
                'canceladas' => (int)($result['canceladas'] ?? 0),
                'aprobadas' => (int)($result['aprobadas'] ?? 0),
                'rechazadas' => (int)($result['rechazadas'] ?? 0)
            ];
            
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'total' => 0, 'pendientes' => 0, 'en_proceso' => 0,
                'ejecutadas' => 0, 'cerradas' => 0, 'canceladas' => 0,
                'aprobadas' => 0, 'rechazadas' => 0
            ];
        }
    }

    /**
     * Obtener estadísticas financieras
     */
    public function obtenerEstadisticasFinancieras($fechaInicio, $fechaFin) {
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
                    WHERE status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND fecha_creacion BETWEEN ? AND ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
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
                'total_ordenes' => 0, 'total_costos' => 0,
                'total_repuestos' => 0, 'total_mano_obra' => 0,
                'promedio_costo' => 0, 'promedio_horas' => 0, 'total_horas' => 0
            ];
        }
    }

    /**
     * Obtener costos por planta
     */
    public function obtenerCostosPorPlanta($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        p.nombre_planta,
                        COUNT(o.id) as total_ordenes,
                        COALESCE(SUM(o.costo_total), 0) as total_costos,
                        COALESCE(SUM(o.costo_repuestos), 0) as total_repuestos,
                        COALESCE(SUM(o.costo_mano_obra), 0) as total_mano_obra
                    FROM ordenes_mantenimiento o
                    LEFT JOIN plantas p ON o.id_planta = p.id_planta
                    WHERE o.status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND o.fecha_creacion BETWEEN ? AND ?
                    AND o.id_planta IS NOT NULL
                    GROUP BY o.id_planta
                    ORDER BY total_costos DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerCostosPorPlanta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener costos por técnico
     */
    public function obtenerCostosPorTecnico($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        t.nombre as tecnico,
                        COUNT(o.id) as total_ordenes,
                        COALESCE(SUM(o.costo_total), 0) as total_costos,
                        COALESCE(SUM(o.horas_trabajadas), 0) as total_horas,
                        COALESCE(AVG(o.horas_trabajadas), 0) as promedio_horas,
                        COALESCE(SUM(o.costo_mano_obra), 0) as total_mano_obra
                    FROM ordenes_mantenimiento o
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    WHERE o.status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND o.fecha_creacion BETWEEN ? AND ?
                    AND o.tecnico_id IS NOT NULL
                    GROUP BY o.tecnico_id
                    ORDER BY total_costos DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerCostosPorTecnico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener costos por mes
     */
    public function obtenerCostosPorMes() {
        try {
            $anio = date('Y');
            
            $sql = "SELECT 
                        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                        COUNT(*) as total_ordenes,
                        COALESCE(SUM(costo_total), 0) as total_costos,
                        COALESCE(SUM(costo_repuestos), 0) as total_repuestos,
                        COALESCE(SUM(costo_mano_obra), 0) as total_mano_obra
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
            $stmt->execute([(int)$limite]);
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
                $stmt->execute([(int)$tecnico_id, (int)$limit, (int)$offset]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([(int)$tecnico_id]);
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

    /**
     * Contar órdenes según filtros
     */
    public function contar($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE 1=1";
            $params = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filtros['status'];
            }

            if (!empty($filtros['tecnico_id'])) {
                $sql .= " AND tecnico_id = ?";
                $params[] = (int)$filtros['tecnico_id'];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
            
        } catch (PDOException $e) {
            error_log("Error en contar: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ Obtener evolución mensual para gráficos
     */
    public function obtenerEvolucionMensual() {
        try {
            $anio = date('Y');
            
            $sql = "SELECT 
                        DATE_FORMAT(fecha_creacion, '%b %Y') as mes_label,
                        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                        COUNT(*) as total_ordenes,
                        COALESCE(SUM(costo_total), 0) as total_costos,
                        COALESCE(SUM(costo_repuestos), 0) as total_repuestos,
                        COALESCE(SUM(costo_mano_obra), 0) as total_mano_obra
                    FROM ordenes_mantenimiento
                    WHERE status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND YEAR(fecha_creacion) = ?
                    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                    ORDER BY mes ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$anio]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerEvolucionMensual: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ OBTENER TOTAL GASTADO
     */
    public function obtenerTotalGastado() {
        try {
            $sql = "SELECT COALESCE(SUM(costo_total), 0) as total 
                    FROM ordenes_mantenimiento 
                    WHERE status IN ('CERRADA', 'APROBADA', 'EJECUTADA')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($resultado['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en obtenerTotalGastado: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ Obtener evolución mensual para un año específico
     */
    public function obtenerEvolucionMensualPorAnio($anio = null) {
        try {
            if (!$anio) {
                $anio = date('Y');
            }
            
            $sql = "SELECT 
                        DATE_FORMAT(fecha_creacion, '%b %Y') as mes_label,
                        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                        COUNT(*) as total_ordenes,
                        COALESCE(SUM(costo_total), 0) as total_costos,
                        COALESCE(SUM(costo_repuestos), 0) as total_repuestos,
                        COALESCE(SUM(costo_mano_obra), 0) as total_mano_obra,
                        COALESCE(AVG(costo_total), 0) as promedio_costo
                    FROM ordenes_mantenimiento
                    WHERE status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                    AND YEAR(fecha_creacion) = ?
                    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                    ORDER BY mes ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$anio]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerEvolucionMensualPorAnio: " . $e->getMessage());
            return [];
        }
    }
}
?>