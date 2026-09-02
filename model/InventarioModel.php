<?php
// model/InventarioModel.php
// Modelo de Inventario - VERSIÓN DEFINITIVA COMPLETA

require_once __DIR__ . '/../config/database.php';

class InventarioModel {
    private $db;
    private $lastError;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Error al conectar a la base de datos (Inventario): " . $e->getMessage());
            throw $e;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Obtener todos los ítems del inventario con filtros
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT id, codigo, nombre, descripcion, categoria, cantidad, 
                           precio_unitario, unidad_medida, stock_minimo, stock_maximo,
                           ubicacion, estado, proveedor, fecha_creacion 
                    FROM inventario WHERE 1=1";
            $params = [];

            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = ?";
                $params[] = $filtros['estado'];
            }

            if (!empty($filtros['categoria'])) {
                $sql .= " AND categoria = ?";
                $params[] = $filtros['categoria'];
            }

            if (!empty($filtros['buscar'])) {
                $sql .= " AND (nombre LIKE ? OR codigo LIKE ? OR descripcion LIKE ?)";
                $buscar = '%' . $filtros['buscar'] . '%';
                $params[] = $buscar;
                $params[] = $buscar;
                $params[] = $buscar;
            }

            if (!empty($filtros['stock']) && $filtros['stock'] === 'bajo') {
                $sql .= " AND cantidad <= 5 AND cantidad > 0";
            }

            if (!empty($filtros['stock']) && $filtros['stock'] === 'agotado') {
                $sql .= " AND cantidad = 0";
            }

            $sql .= " ORDER BY nombre ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mapear campos para compatibilidad con la vista
            foreach ($result as &$item) {
                $item['tipo'] = $item['categoria'] ?? 'N/A';
                $item['activo'] = ($item['estado'] ?? 'inactivo') === 'activo' ? 1 : 0;
                $item['imagen'] = null;
            }
            
            return $result;

        } catch (PDOException $e) {
            error_log("Error en Inventario::obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener ítem por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM inventario WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['tipo'] = $result['categoria'] ?? 'N/A';
                $result['activo'] = ($result['estado'] ?? 'inactivo') === 'activo' ? 1 : 0;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en Inventario::obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ CREAR NUEVO ÍTEM - CON DEBUG
     */
    public function crear($datos) {
        try {
            // ✅ Validar datos requeridos
            if (empty($datos['nombre'])) {
                $this->lastError = 'El nombre es obligatorio';
                error_log("Inventario::crear - Error: " . $this->lastError);
                return false;
            }

            // ✅ Verificar que la categoría no esté vacía
            if (empty($datos['categoria'])) {
                $this->lastError = 'La categoría es obligatoria';
                error_log("Inventario::crear - Error: " . $this->lastError);
                return false;
            }

            // ✅ Log de los datos a insertar
            error_log("Inventario::crear - Datos a insertar: " . print_r($datos, true));

            $sql = "INSERT INTO inventario (codigo, nombre, descripcion, categoria, cantidad, 
                                            precio_unitario, unidad_medida, stock_minimo, ubicacion, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $datos['codigo'] ?? '',
                $datos['nombre'],
                $datos['descripcion'] ?? '',
                $datos['categoria'],
                (int)($datos['cantidad'] ?? 0),
                (float)($datos['precio_unitario'] ?? 0),
                $datos['unidad_medida'] ?? 'Unidad',
                (int)($datos['stock_minimo'] ?? 0),
                $datos['ubicacion'] ?? '',
                isset($datos['estado']) ? $datos['estado'] : 'activo'
            ]);

            if ($result) {
                $lastId = $this->db->lastInsertId();
                error_log("Inventario::crear - Ítem creado con ID: " . $lastId);
                return $lastId;
            } else {
                $errorInfo = $stmt->errorInfo();
                $this->lastError = 'Error al ejecutar la inserción: ' . $errorInfo[2];
                error_log("Inventario::crear - Error: " . $this->lastError);
                error_log("Inventario::crear - ErrorInfo: " . print_r($errorInfo, true));
                return false;
            }

        } catch (PDOException $e) {
            error_log("Inventario::crear - Excepción: " . $e->getMessage());
            error_log("Inventario::crear - Trace: " . $e->getTraceAsString());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Actualizar ítem del inventario
     */
    public function actualizar($id, $datos) {
        try {
            if (empty($datos['nombre'])) {
                $this->lastError = 'El nombre es obligatorio';
                return false;
            }

            $sql = "UPDATE inventario SET 
                        codigo = ?,
                        nombre = ?,
                        descripcion = ?,
                        categoria = ?,
                        cantidad = ?,
                        precio_unitario = ?,
                        unidad_medida = ?,
                        stock_minimo = ?,
                        ubicacion = ?,
                        estado = ?
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['codigo'] ?? '',
                $datos['nombre'],
                $datos['descripcion'] ?? '',
                $datos['categoria'] ?? '',
                (int)($datos['cantidad'] ?? 0),
                (float)($datos['precio_unitario'] ?? 0),
                $datos['unidad_medida'] ?? 'Unidad',
                (int)($datos['stock_minimo'] ?? 0),
                $datos['ubicacion'] ?? '',
                isset($datos['estado']) ? $datos['estado'] : 'activo',
                $id
            ]);

        } catch (PDOException $e) {
            error_log("Error en Inventario::actualizar: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Eliminar ítem del inventario
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM inventario WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en Inventario::eliminar: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Buscar ítems para autocomplete
     */
    public function buscar($termino) {
        try {
            $sql = "SELECT id, codigo, nombre, cantidad, precio_unitario, unidad_medida 
                    FROM inventario 
                    WHERE (nombre LIKE ? OR codigo LIKE ?)
                    AND cantidad > 0
                    AND estado = 'activo'
                    ORDER BY nombre ASC
                    LIMIT 15";
            $buscar = '%' . $termino . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$buscar, $buscar]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Inventario::buscar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si el código SKU ya existe
     */
    public function codigoExiste($codigo, $excluirId = null) {
        try {
            $sql = "SELECT id FROM inventario WHERE codigo = ? AND codigo != ''";
            $params = [$codigo];

            if ($excluirId) {
                $sql .= " AND id != ?";
                $params[] = $excluirId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() !== false;

        } catch (PDOException $e) {
            error_log("Error en Inventario::codigoExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas del inventario
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(cantidad) as total_stock,
                        AVG(precio_unitario) as precio_promedio,
                        SUM(cantidad * precio_unitario) as valor_total,
                        SUM(CASE WHEN cantidad = 0 THEN 1 ELSE 0 END) as agotados,
                        SUM(CASE WHEN cantidad <= 5 AND cantidad > 0 THEN 1 ELSE 0 END) as stock_bajo
                    FROM inventario
                    WHERE estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'total_stock' => (int)($result['total_stock'] ?? 0),
                'precio_promedio' => (float)($result['precio_promedio'] ?? 0),
                'valor_total' => (float)($result['valor_total'] ?? 0),
                'agotados' => (int)($result['agotados'] ?? 0),
                'stock_bajo' => (int)($result['stock_bajo'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error en Inventario::obtenerEstadisticas: " . $e->getMessage());
            return [
                'total' => 0, 
                'total_stock' => 0, 
                'precio_promedio' => 0, 
                'valor_total' => 0,
                'agotados' => 0,
                'stock_bajo' => 0
            ];
        }
    }

    /**
     * Obtener categorías únicas
     */
    public function obtenerCategorias() {
        try {
            $sql = "SELECT DISTINCT categoria FROM inventario WHERE categoria != '' ORDER BY categoria ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error en Inventario::obtenerCategorias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener tipos únicos
     */
    public function obtenerTipos() {
        try {
            $sql = "SELECT DISTINCT categoria FROM inventario WHERE categoria != '' ORDER BY categoria ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error en Inventario::obtenerTipos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar stock de un ítem
     */
    public function actualizarStock($id, $cantidad) {
        try {
            $sql = "UPDATE inventario SET cantidad = cantidad + ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cantidad, $id]);
        } catch (PDOException $e) {
            error_log("Error en Inventario::actualizarStock: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si hay stock suficiente
     */
    public function verificarStock($id, $cantidadRequerida) {
        try {
            $sql = "SELECT cantidad FROM inventario WHERE id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false;
            }
            
            return $result['cantidad'] >= $cantidadRequerida;
        } catch (PDOException $e) {
            error_log("Error en Inventario::verificarStock: " . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // ✅ MÉTODOS PARA EL PANEL DE ALMACÉN (AGREGADOS)
    // ============================================================

    /**
     * ✅ OBTENER PRODUCTOS CON STOCK BAJO
     * Para el panel de Almacén
     */
    public function obtenerStockBajo() {
        try {
            $sql = "SELECT id, codigo, nombre, descripcion, categoria, cantidad, 
                           stock_minimo, precio_unitario, unidad_medida, ubicacion
                    FROM inventario 
                    WHERE estado = 'activo' 
                    AND cantidad <= stock_minimo 
                    AND cantidad > 0
                    ORDER BY (stock_minimo - cantidad) DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mapear para compatibilidad
            foreach ($result as &$item) {
                $item['nombre'] = $item['nombre'] ?? 'Sin nombre';
                $item['stock_minimo'] = $item['stock_minimo'] ?? 5;
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error en Inventario::obtenerStockBajo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ OBTENER ÚLTIMOS MOVIMIENTOS
     * Para el panel de Almacén
     */
    public function obtenerUltimosMovimientos($limite = 10) {
        try {
            // Verificar si la tabla inventario_movimientos existe
            $sqlCheck = "SHOW TABLES LIKE 'inventario_movimientos'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute();
            
            if ($stmtCheck->rowCount() === 0) {
                // Si no existe la tabla, retornar array vacío
                error_log("Tabla inventario_movimientos no existe");
                return [];
            }
            
            $sql = "SELECT 
                        im.id,
                        im.inventario_id,
                        im.tipo,
                        im.cantidad,
                        im.fecha,
                        i.nombre as nombre_producto,
                        i.codigo as codigo_producto,
                        u.nombre as usuario
                    FROM inventario_movimientos im
                    LEFT JOIN inventario i ON im.inventario_id = i.id
                    LEFT JOIN usuarios u ON im.usuario_id = u.id
                    ORDER BY im.fecha DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$limite]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mapear para compatibilidad con la vista
            foreach ($result as &$item) {
                $item['nombre_producto'] = $item['nombre_producto'] ?? 'Producto eliminado';
                $item['usuario'] = $item['usuario'] ?? 'Sistema';
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error en Inventario::obtenerUltimosMovimientos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ AGREGAR MOVIMIENTO DE INVENTARIO
     */
    public function agregarMovimiento($inventario_id, $tipo, $cantidad, $usuario_id = null) {
        try {
            // Verificar si la tabla existe
            $sqlCheck = "SHOW TABLES LIKE 'inventario_movimientos'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute();
            
            if ($stmtCheck->rowCount() === 0) {
                // Crear la tabla si no existe
                $this->crearTablaMovimientos();
            }
            
            if (!$usuario_id) {
                $usuario_id = $_SESSION['usuario_id'] ?? 1;
            }
            
            $sql = "INSERT INTO inventario_movimientos (inventario_id, tipo, cantidad, usuario_id, fecha) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$inventario_id, $tipo, (int)$cantidad, (int)$usuario_id]);
            
        } catch (PDOException $e) {
            error_log("Error en Inventario::agregarMovimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ CREAR TABLA DE MOVIMIENTOS SI NO EXISTE
     */
    private function crearTablaMovimientos() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS inventario_movimientos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        inventario_id INT NOT NULL,
                        tipo ENUM('entrada', 'salida') NOT NULL,
                        cantidad INT NOT NULL,
                        usuario_id INT NULL,
                        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE,
                        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            error_log("Tabla inventario_movimientos creada correctamente");
            return true;
        } catch (PDOException $e) {
            error_log("Error al crear tabla inventario_movimientos: " . $e->getMessage());
            return false;
        }
    }
}
?>