<?php
// views/ordenes/index.php
// Gestión de órdenes - VERSIÓN CORREGIDA CON ASTEROADMIN

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
    exit;
}

$titulo = 'Gestión de Órdenes';
$seccion = 'ordenes';

include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header con saludo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-clipboard-list text-primary me-2"></i>Gestión de Órdenes</h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y H:i'); ?>
                <span class="mx-2">|</span>
                <i class="fas fa-info-circle me-1"></i> 
                <?php echo $estadisticas['total'] ?? 0; ?> órdenes en total
            </p>
        </div>
        <div>
            <a href="/proyecto/ordenes/crear" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Nueva Orden
            </a>
        </div>
    </div>

    <!-- ✅ Tarjetas de Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-mini">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-mini" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total</div>
                        <div class="stat-number-mini"><?php echo $estadisticas['total'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-mini">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-mini" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stat-label">Pendientes</div>
                        <div class="stat-number-mini"><?php echo $estadisticas['pendientes'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-mini">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-mini" style="background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div>
                        <div class="stat-label">En Proceso</div>
                        <div class="stat-number-mini"><?php echo $estadisticas['en_proceso'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-mini">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-mini" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Cerradas</div>
                        <div class="stat-number-mini"><?php echo $estadisticas['cerradas'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-mini">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-mini" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Canceladas</div>
                        <div class="stat-number-mini"><?php echo $estadisticas['canceladas'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-mini">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-mini" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Ejecutadas</div>
                        <div class="stat-number-mini"><?php echo $estadisticas['ejecutadas'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Filtros -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="buscar" class="form-control border-start-0" 
                               placeholder="Buscar orden..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="PENDIENTE" <?php echo ($_GET['status'] ?? '') === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="EN_PROCESO" <?php echo ($_GET['status'] ?? '') === 'EN_PROCESO' ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="EJECUTADA" <?php echo ($_GET['status'] ?? '') === 'EJECUTADA' ? 'selected' : ''; ?>>Ejecutada</option>
                        <option value="CERRADA" <?php echo ($_GET['status'] ?? '') === 'CERRADA' ? 'selected' : ''; ?>>Cerrada</option>
                        <option value="CANCELADA" <?php echo ($_GET['status'] ?? '') === 'CANCELADA' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="prioridad" class="form-select">
                        <option value="">Todas las prioridades</option>
                        <option value="Alta" <?php echo ($_GET['prioridad'] ?? '') === 'Alta' ? 'selected' : ''; ?>>Alta</option>
                        <option value="Media" <?php echo ($_GET['prioridad'] ?? '') === 'Media' ? 'selected' : ''; ?>>Media</option>
                        <option value="Baja" <?php echo ($_GET['prioridad'] ?? '') === 'Baja' ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                        <input type="date" name="fecha_desde" class="form-control border-start-0" 
                               value="<?php echo htmlspecialchars($_GET['fecha_desde'] ?? ''); ?>" placeholder="Desde">
                        <span class="input-group-text bg-transparent border-start-0 border-end-0">-</span>
                        <input type="date" name="fecha_hasta" class="form-control border-start-0" 
                               value="<?php echo htmlspecialchars($_GET['fecha_hasta'] ?? ''); ?>" placeholder="Hasta">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Tabla de Órdenes -->
    <div class="card border-0">
        <div class="card-body p-0">
            <?php if (!empty($ordenes)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Descripción</th>
                            <th>Técnico</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes as $orden): ?>
                        <tr>
                            <td><span class="fw-semibold">#<?php echo $orden['id']; ?></span></td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars(substr($orden['descripcion'] ?? '', 0, 50)); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($orden['nombre_planta'] ?? 'Sin planta'); ?></small>
                            </td>
                            <td>
                                <?php if (!empty($orden['tecnico_nombre'])): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-user-circle me-1"></i>
                                        <?php echo htmlspecialchars($orden['tecnico_nombre']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $prioridad = $orden['prioridad'] ?? 'Media';
                                $color = match($prioridad) {
                                    'Urgente' => 'danger',
                                    'Alta' => 'warning',
                                    'Media' => 'info',
                                    'Baja' => 'success',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?> px-3 py-2">
                                    <?php echo $prioridad; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $estado = $orden['status'] ?? $orden['estado'] ?? 'PENDIENTE';
                                $badge = match(strtoupper($estado)) {
                                    'PENDIENTE' => 'warning',
                                    'EN_PROCESO' => 'info',
                                    'EJECUTADA' => 'primary',
                                    'CERRADA' => 'success',
                                    'CANCELADA' => 'danger',
                                    'APROBADA' => 'success',
                                    'RECHAZADA' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $badge; ?> bg-opacity-10 text-<?php echo $badge; ?> px-3 py-2">
                                    <i class="fas fa-circle me-1" style="font-size: 6px;"></i>
                                    <?php echo str_replace('_', ' ', $estado); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo date('d/m/Y', strtotime($orden['fecha_creacion'] ?? 'now')); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/proyecto/ordenes/ver/<?php echo $orden['id']; ?>" 
                                       class="btn btn-outline-primary" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_array($orden['status'] ?? '', ['PENDIENTE', 'EN_PROCESO'])): ?>
                                    <a href="/proyecto/ordenes/editar/<?php echo $orden['id']; ?>" 
                                       class="btn btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (($orden['status'] ?? '') === 'EN_PROCESO'): ?>
                                    <a href="/proyecto/ordenes/cerrar/<?php echo $orden['id']; ?>" 
                                       class="btn btn-outline-success" title="Cerrar">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5>No hay órdenes registradas</h5>
                <p class="text-muted">Haz clic en "Nueva Orden" para crear la primera.</p>
                <a href="/proyecto/ordenes/crear" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Crear Primera Orden
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ✅ Estilos personalizados -->
<style>
.stat-card-mini {
    background: #fff;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.3s ease;
}
.stat-card-mini:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
.stat-card-mini .stat-icon-mini {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.stat-card-mini .stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    font-weight: 600;
}
.stat-card-mini .stat-number-mini {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a2e;
    line-height: 1.2;
}
.table th {
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}
.table td {
    font-size: 0.9rem;
}
.btn-group .btn {
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>