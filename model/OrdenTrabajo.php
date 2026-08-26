<?php
// model/OrdenTrabajo.php
// Ubicación: C:\xampp\htdocs\proyecto\model\OrdenTrabajo.php

// Incluir la base de datos
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class OrdenTrabajo {
    private $db;
    
    // Constantes de estado
    const ESTADO_PENDIENTE = 'PENDIENTE';
    const ESTADO_EN_PROCESO = 'EN_PROCESO';
    const ESTADO_EJECUTADA = 'EJECUTADA';
    const ESTADO_CERRADA = 'CERRADA';
    const ESTADO_CANCELADA = 'CANCELADA';
    const ESTADO_APROBADA = 'APROBADA';
    const ESTADO_RECHAZADA = 'RECHAZADA';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las órdenes con filtros y datos de costos
     */
    public function obtenerTodos($filtros = [], $limit = null, $offset = null) {
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

            // Aplicar filtros de forma segura
            if (!empty($filtros['status'])) {
                $sql .= " AND o.status = ?";
                $params[] = SecurityHelper::sanitizeForDB($filtros['status']);
            }

            if (!empty($filtros['buscar'])) {
                $sql .= " AND (o.num_om LIKE ? OR o.titulo LIKE ? OR o.descripcion_mantenimiento LIKE ?)";
                $buscar = '%' . SecurityHelper::sanitizeForDB($filtros['buscar']) . '%';
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
                $params[] = SecurityHelper::sanitizeForDB($filtros['prioridad']);
            }

            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $sql .= " AND o.fecha_creacion BETWEEN ? AND ?";
                $params[] = SecurityHelper::sanitizeForDB($filtros['fecha_desde'] . ' 00:00:00');
                $params[] = SecurityHelper::sanitizeForDB($filtros['fecha_hasta'] . ' 23:59:59');
            }

            $sql .= " ORDER BY o.fecha_creacion DESC";
            
            // Paginación
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = (int)$limit;
                $params[] = (int)$offset;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos (OrdenTrabajo): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener orden por ID - VERSIÓN CORREGIDA CON MANEJO DE ERRORES
     */
    public function obtenerPorId($id) {
        try {
            // Validar ID
            if (!is_numeric($id) || (int)$id <= 0) {
                error_log("obtenerPorId: ID inválido - $id");
                return false;
            }
            
            // Obtener la orden básica
            $sql = "SELECT * FROM ordenes_mantenimiento WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                error_log("obtenerPorId: Orden con ID $id no encontrada");
                return false;
            }
            
            // Obtener datos relacionados por separado con manejo de errores
            $this->cargarDatosRelacionados($orden);
            
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
     * Cargar datos relacionados de una orden
     */
    private function cargarDatosRelacionados(&$orden) {
        // Técnico
        if (!empty($orden['tecnico_id'])) {
            try {
                $sql = "SELECT nombre, tarifa FROM tecnicos WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([(int)$orden['tecnico_id']]);
                $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($tecnico) {
                    $orden['tecnico_nombre'] = SecurityHelper::preventXSS($tecnico['nombre']);
                    $orden['tarifa_tecnico'] = (float)($tecnico['tarifa'] ?? 0);
                } else {
                    $orden['tecnico_nombre'] = 'Técnico ID: ' . (int)$orden['tecnico_id'] . ' (no encontrado)';
                    $orden['tarifa_tecnico'] = 0;
                    error_log("obtenerPorId: Técnico ID " . $orden['tecnico_id'] . " no encontrado para orden {$orden['id']}");
                }
            } catch (PDOException $e) {
                error_log("obtenerPorId: Error al obtener técnico: " . $e->getMessage());
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
                $stmt->execute([(int)$orden['id_supervisor']]);
                $supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
                $orden['supervisor_nombre'] = $supervisor ? SecurityHelper::preventXSS($supervisor['nombre']) : 'Supervisor ID: ' . (int)$orden['id_supervisor'] . ' (no encontrado)';
            } catch (PDOException $e) {
                error_log("obtenerPorId: Error al obtener supervisor: " . $e->getMessage());
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
                $stmt->execute([(int)$orden['id_planta']]);
                $planta = $stmt->fetch(PDO::FETCH_ASSOC);
                $orden['nombre_planta'] = $planta ? SecurityHelper::preventXSS($planta['nombre_planta']) : 'Planta ID: ' . (int)$orden['id_planta'] . ' (no encontrada)';
            } catch (PDOException $e) {
                error_log("obtenerPorId: Error al obtener planta: " . $e->getMessage());
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
                $stmt->execute([(int)$orden['id_area']]);
                $area = $stmt->fetch(PDO::FETCH_ASSOC);
                $orden['nombre_area'] = $area ? SecurityHelper::preventXSS($area['nombre_area']) : 'Área ID: ' . (int)$orden['id_area'] . ' (no encontrada)';
            } catch (PDOException $e) {
                error_log("obtenerPorId: Error al obtener área: " . $e->getMessage());
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
                $stmt->execute([(int)$orden['id_equipo']]);
                $equipo = $stmt->fetch(PDO::FETCH_ASSOC);
                $orden['nombre_equipo'] = $equipo ? SecurityHelper::preventXSS($equipo['nombre_equipo']) : 'Equipo ID: ' . (int)$orden['id_equipo'] . ' (no encontrado)';
            } catch (PDOException $e) {
                error_log("obtenerPorId: Error al obtener equipo: " . $e->getMessage());
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
                $stmt->execute([(int)$orden['id_componente']]);
                $componente = $stmt->fetch(PDO::FETCH_ASSOC);
                $orden['nombre_componente'] = $componente ? SecurityHelper::preventXSS($componente['nombre_componente']) : 'Componente ID: ' . (int)$orden['id_componente'] . ' (no encontrado)';
            } catch (PDOException $e) {
                error_log("obtenerPorId: Error al obtener componente: " . $e->getMessage());
                $orden['nombre_componente'] = 'Error al cargar componente';
            }
        } else {
            $orden['nombre_componente'] = 'Sin asignar';
        }
    }

    /**
     * Obtener orden por número de OM
     */
    public function obtenerPorNumOM($num_om) {
        try {
            $num_om = SecurityHelper::sanitizeForDB($num_om);
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
     * Crear nueva orden de trabajo
     */
    public function crear($datos) {
        try {
            // Validar datos
            $errores = $this->validarDatos($datos, true);
            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                return false;
            }
            
            // Sanitizar datos
            $datos = $this->sanitizarDatos($datos);
            
            // Calcular costos
            $horas = (float)($datos['horas_trabajadas'] ?? 0);
            $tarifa = (float)($datos['tarifa_tecnico'] ?? 0);
            $costo_repuestos = (float)($datos['costo_repuestos'] ?? 0);
            $costo_mano_obra = $horas * $tarifa;
            $costo_total = $costo_repuestos + $costo_mano_obra;

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
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'num_om' => $datos['num_om'],
                'cantidad' => (int)($datos['cantidad'] ?? 1),
                'mes' => $datos['mes'] ?? '',
                'semana' => $datos['semana'] ?? '',
                'fecha_emision' => $datos['fecha_emision'] ?? date('Y-m-d'),
                'fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                'fecha_estimada' => $datos['fecha_estimada'] ?? null,
                'nombre_planta' => $datos['nombre_planta'] ?? '',
                'nombre_area' => $datos['nombre_area'] ?? '',
                'nombre_equipo' => $datos['nombre_equipo'] ?? '',
                'nombre_componente' => $datos['nombre_componente'] ?? '',
                'id_planta' => !empty($datos['id_planta']) ? (int)$datos['id_planta'] : null,
                'id_area' => !empty($datos['id_area']) ? (int)$datos['id_area'] : null,
                'id_equipo' => !empty($datos['id_equipo']) ? (int)$datos['id_equipo'] : null,
                'id_componente' => !empty($datos['id_componente']) ? (int)$datos['id_componente'] : null,
                'titulo' => $datos['titulo'] ?? '',
                'descripcion_mantenimiento' => $datos['descripcion_mantenimiento'] ?? '',
                'tipo_actividad' => $datos['tipo_actividad'] ?? '',
                'tipo_mantenimiento' => $datos['tipo_mantenimiento'] ?? '',
                'prioridad' => $datos['prioridad'] ?? 'Media',
                'solicitante' => $datos['solicitante'] ?? '',
                'supervisor_solicitante' => $datos['supervisor_solicitante'] ?? '',
                'id_supervisor' => !empty($datos['id_supervisor']) ? (int)$datos['id_supervisor'] : null,
                'tecnico_id' => !empty($datos['tecnico_id']) ? (int)$datos['tecnico_id'] : null,
                'horas_duracion' => (float)($datos['horas_duracion'] ?? 0),
                'horas_trabajadas' => $horas,
                'tarifa_tecnico' => $tarifa,
                'costo_total' => $costo_total,
                'costo_repuestos' => $costo_repuestos,
                'costo_mano_obra' => $costo_mano_obra,
                'status' => $datos['status'] ?? self::ESTADO_PENDIENTE,
                'creado_por' => (int)($datos['creado_por'] ?? $_SESSION['usuario_id'] ?? 1)
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear (OrdenTrabajo): " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear la orden: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Actualizar orden de trabajo
     */
    public function actualizar($id, $datos) {
        try {
            // Validar datos
            $errores = $this->validarDatos($datos, false);
            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                return false;
            }
            
            // Sanitizar datos
            $datos = $this->sanitizarDatos($datos);
            
            // Calcular costos
            $horas = (float)($datos['horas_trabajadas'] ?? 0);
            $tarifa = (float)($datos['tarifa_tecnico'] ?? 0);
            $costo_repuestos = (float)($datos['costo_repuestos'] ?? 0);
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
                (int)($datos['cantidad'] ?? 1),
                $datos['mes'] ?? '',
                $datos['semana'] ?? '',
                $datos['fecha_emision'] ?? date('Y-m-d'),
                $datos['fecha_inicio'] ?? date('Y-m-d'),
                $datos['fecha_estimada'] ?? null,
                $datos['nombre_planta'] ?? '',
                $datos['nombre_area'] ?? '',
                $datos['nombre_equipo'] ?? '',
                $datos['nombre_componente'] ?? '',
                !empty($datos['id_planta']) ? (int)$datos['id_planta'] : null,
                !empty($datos['id_area']) ? (int)$datos['id_area'] : null,
                !empty($datos['id_equipo']) ? (int)$datos['id_equipo'] : null,
                !empty($datos['id_componente']) ? (int)$datos['id_componente'] : null,
                $datos['titulo'] ?? '',
                $datos['descripcion_mantenimiento'] ?? '',
                $datos['descripcion_realizada'] ?? '',
                $datos['tipo_actividad'] ?? '',
                $datos['tipo_mantenimiento'] ?? '',
                $datos['prioridad'] ?? 'Media',
                $datos['solicitante'] ?? '',
                $datos['supervisor_solicitante'] ?? '',
                !empty($datos['id_supervisor']) ? (int)$datos['id_supervisor'] : null,
                !empty($datos['tecnico_id']) ? (int)$datos['tecnico_id'] : null,
                (float)($datos['horas_duracion'] ?? 0),
                $horas,
                $tarifa,
                $costo_total,
                $costo_repuestos,
                $costo_mano_obra,
                $datos['status'] ?? self::ESTADO_PENDIENTE,
                $datos['observaciones_tecnico'] ?? '',
                $datos['observaciones_cierre'] ?? '',
                (int)($datos['actualizado_por'] ?? $_SESSION['usuario_id'] ?? 1),
                (int)$id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizar (OrdenTrabajo): " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar la orden: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Validar datos de orden
     */
    private function validarDatos($datos, $esCreacion = true) {
        $errores = [];
        
        // Validar título
        if (empty($datos['titulo'])) {
            $errores[] = 'El título es obligatorio';
        } elseif (strlen($datos['titulo']) > 200) {
            $errores[] = 'El título no puede tener más de 200 caracteres';
        }
        
        // Validar número de OM (solo en creación)
        if ($esCreacion && empty($datos['num_om'])) {
            $errores[] = 'El número de OM es obligatorio';
        }
        
        // Validar fechas
        if (!empty($datos['fecha_inicio']) && !ValidationHelper::validateDate($datos['fecha_inicio'])) {
            $errores[] = 'La fecha de inicio no es válida';
        }
        
        if (!empty($datos['fecha_estimada']) && !ValidationHelper::validateDate($datos['fecha_estimada'])) {
            $errores[] = 'La fecha estimada no es válida';
        }
        
        // Validar horas
        if (isset($datos['horas_trabajadas']) && !ValidationHelper::validateNumber($datos['horas_trabajadas'], 0, 24)) {
            $errores[] = 'Las horas trabajadas deben ser entre 0 y 24';
        }
        
        // Validar costos
        if (isset($datos['costo_repuestos']) && !ValidationHelper::validateNumber($datos['costo_repuestos'], 0)) {
            $errores[] = 'El costo de repuestos debe ser un número positivo';
        }
        
        return $errores;
    }

    /**
     * Sanitizar datos
     */
    private function sanitizarDatos($datos) {
        $sanitizados = [];
        foreach ($datos as $key => $value) {
            if (is_string($value)) {
                $sanitizados[$key] = SecurityHelper::sanitizeForDB($value);
            } else {
                $sanitizados[$key] = $value;
            }
        }
        return $sanitizados;
    }

    /**
     * Cerrar orden de trabajo
     */
    public function cerrar($id, $datos) {
        try {
            // Validar ID
            if (!is_numeric($id) || (int)$id <= 0) {
                $_SESSION['error'] = 'ID de orden inválido';
                return false;
            }
            
            // Validar datos
            if (isset($datos['horas_trabajadas']) && !ValidationHelper::validateNumber($datos['horas_trabajadas'], 0, 24)) {
                $_SESSION['error'] = 'Las horas trabajadas deben ser entre 0 y 24';
                return false;
            }
            
            // Calcular costos
            $horas = (float)($datos['horas_trabajadas'] ?? 0);
            $tarifa = (float)($datos['tarifa_tecnico'] ?? 0);
            $costo_repuestos = (float)($datos['costo_repuestos'] ?? 0);
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
                        status = ?,
                        fecha_finalizacion = NOW(),
                        actualizado_por = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                SecurityHelper::sanitizeForDB($datos['descripcion_realizada'] ?? ''),
                SecurityHelper::sanitizeForDB($datos['pasos_ejecutados'] ?? ''),
                $horas,
                $tarifa,
                $costo_total,
                $costo_repuestos,
                $costo_mano_obra,
                SecurityHelper::sanitizeForDB($datos['foto_evidencia'] ?? ''),
                SecurityHelper::sanitizeForDB($datos['firma_tecnico'] ?? ''),
                SecurityHelper::sanitizeForDB($datos['observaciones_tecnico'] ?? ''),
                SecurityHelper::sanitizeForDB($datos['observaciones_cierre'] ?? ''),
                self::ESTADO_CERRADA,
                (int)($datos['actualizado_por'] ?? $_SESSION['usuario_id'] ?? 1),
                (int)$id
            ]);
        } catch (PDOException $e) {
            error_log("Error en cerrar (OrdenTrabajo): " . $e->getMessage());
            $_SESSION['error'] = 'Error al cerrar la orden: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado($id, $estado, $observaciones = null) {
        try {
            // Validar estado
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
                        status = ?,
                        observaciones_cierre = ?,
                        actualizado_por = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $estado,
                SecurityHelper::sanitizeForDB($observaciones ?? ''),
                (int)($_SESSION['usuario_id'] ?? 1),
                (int)$id
            ]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cambiar el estado: ' . $e->getMessage();
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
                        status = ?,
                        fecha_actualizacion = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                (int)$tecnico_id,
                self::ESTADO_EN_PROCESO,
                (int)$orden_id
            ]);
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
            return $stmt->execute([
                (int)$supervisor_id,
                (int)$orden_id
            ]);
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
            $stmt->execute([(int)$id]);
            
            // Eliminar repuestos asociados
            $stmt = $this->db->prepare("DELETE FROM ordenes_repuestos WHERE orden_id = ?");
            $stmt->execute([(int)$id]);
            
            // Eliminar historial
            $stmt = $this->db->prepare("DELETE FROM ordenes_historial WHERE orden_id = ?");
            $stmt->execute([(int)$id]);
            
            // Eliminar la orden
            $sql = "DELETE FROM ordenes_mantenimiento WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$id]);
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
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as en_proceso,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ejecutadas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cerradas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as canceladas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rechazadas,
                        AVG(horas_trabajadas) as promedio_horas,
                        AVG(costo_total) as promedio_costo,
                        SUM(costo_total) as total_costos
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
                'rechazadas' => (int)($result['rechazadas'] ?? 0),
                'promedio_horas' => round((float)($result['promedio_horas'] ?? 0), 1),
                'promedio_costo' => round((float)($result['promedio_costo'] ?? 0), 2),
                'total_costos' => round((float)($result['total_costos'] ?? 0), 2)
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'total' => 0, 'pendientes' => 0, 'en_proceso' => 0,
                'ejecutadas' => 0, 'cerradas' => 0, 'canceladas' => 0,
                'aprobadas' => 0, 'rechazadas' => 0,
                'promedio_horas' => 0, 'promedio_costo' => 0, 'total_costos' => 0
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
                    WHERE status IN (?, ?, ?)";
            $params = [self::ESTADO_CERRADA, self::ESTADO_APROBADA, self::ESTADO_EJECUTADA];

            if ($fechaInicio && $fechaFin) {
                $sql .= " AND fecha_creacion BETWEEN ? AND ?";
                $params[] = SecurityHelper::sanitizeForDB($fechaInicio . ' 00:00:00');
                $params[] = SecurityHelper::sanitizeForDB($fechaFin . ' 23:59:59');
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
                'total_ordenes' => 0, 'total_costos' => 0,
                'total_repuestos' => 0, 'total_mano_obra' => 0,
                'promedio_costo' => 0, 'promedio_horas' => 0, 'total_horas' => 0
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
                    WHERE status IN (?, ?, ?)
                    AND nombre_planta IS NOT NULL AND nombre_planta != ''";
            $params = [self::ESTADO_CERRADA, self::ESTADO_APROBADA, self::ESTADO_EJECUTADA];

            if ($fechaInicio && $fechaFin) {
                $sql .= " AND fecha_creacion BETWEEN ? AND ?";
                $params[] = SecurityHelper::sanitizeForDB($fechaInicio . ' 00:00:00');
                $params[] = SecurityHelper::sanitizeForDB($fechaFin . ' 23:59:59');
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
                    WHERE o.status IN (?, ?, ?)
                    AND o.tecnico_id IS NOT NULL";
            $params = [self::ESTADO_CERRADA, self::ESTADO_APROBADA, self::ESTADO_EJECUTADA];

            if ($fechaInicio && $fechaFin) {
                $sql .= " AND o.fecha_creacion BETWEEN ? AND ?";
                $params[] = SecurityHelper::sanitizeForDB($fechaInicio . ' 00:00:00');
                $params[] = SecurityHelper::sanitizeForDB($fechaFin . ' 23:59:59');
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
                    WHERE status IN (?, ?, ?)
                    AND YEAR(fecha_creacion) = ?
                    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                    ORDER BY mes ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                self::ESTADO_CERRADA,
                self::ESTADO_APROBADA,
                self::ESTADO_EJECUTADA,
                $anio
            ]);
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
     * Obtener órdenes para reporte (con filtros de fecha)
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
            $params = [
                SecurityHelper::sanitizeForDB($fecha_desde . ' 00:00:00'),
                SecurityHelper::sanitizeForDB($fecha_hasta . ' 23:59:59')
            ];

            if ($estado) {
                $sql .= " AND o.status = ?";
                $params[] = SecurityHelper::sanitizeForDB($estado);
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