<?php
// views/supervision/ver.php
// Detalle de Supervisión - VERSIÓN CORREGIDA

// Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /proyecto/login');
    exit();
}

$titulo = "Detalle de Supervisión";
$seccion = "supervision";
include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';

$supervision = $supervision ?? null;
if (!$supervision) {
    header('Location: /proyecto/supervision');
    exit();
}

$estado = $supervision['estado'] ?? 'PENDIENTE';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-clipboard-check text-primary me-2"></i>Detalle de Supervisión #<?php echo $supervision['id']; ?>
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Información detallada de la supervisión
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/proyecto/supervision" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="/proyecto/supervision/editar/<?php echo $supervision['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Editar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información de la Supervisión
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">ID</label>
                                <p class="fw-semibold mb-0"><?php echo $supervision['id']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Orden de Trabajo</label>
                                <p class="fw-semibold mb-0">#<?php echo $supervision['orden_id']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Supervisor</label>
                                <p class="fw-semibold mb-0"><?php echo $supervision['supervisor'] ?? 'N/A'; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Estado</label>
                                <p class="mb-0">
                                    <?php 
                                    $estadoColor = match(strtolower($estado)) {
                                        'aprobada' => 'success',
                                        'rechazada' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $estadoColor; ?> bg-opacity-10 text-<?php echo $estadoColor; ?>">
                                        <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                        <?php echo ucfirst($estado); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Fecha de supervisión</label>
                                <p class="fw-semibold mb-0"><?php echo $supervision['fecha_supervision'] ?? 'Pendiente'; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Calificación</label>
                                <p class="fw-semibold mb-0">
                                    <?php if (!empty($supervision['calificacion'])): ?>
                                        <span class="fw-bold"><?php echo $supervision['calificacion']; ?>/5</span>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $supervision['calificacion'] ? '' : '-o'; ?> text-warning" style="font-size:0.85rem;"></i>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Cumple estándares</label>
                                <p class="fw-semibold mb-0"><?php echo ($supervision['cumple'] ?? false) ? '✅ Sí' : '❌ No'; ?></p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <label class="text-muted small fw-semibold text-uppercase">Observaciones</label>
                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($supervision['observaciones'] ?? 'Sin observaciones')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
            <!-- Detalle de la orden -->
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-clipboard-list text-primary me-2"></i> Orden Relacionada
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($supervision['orden'])): ?>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Título</label>
                            <p class="fw-semibold mb-0"><?php echo htmlspecialchars($supervision['orden']['titulo']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Área</label>
                            <p class="fw-semibold mb-0"><?php echo $supervision['orden']['area'] ?? 'N/A'; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Prioridad</label>
                            <p class="mb-0">
                                <?php 
                                $prioridad = $supervision['orden']['prioridad'] ?? 'Media';
                                $prioridadColor = match(strtolower($prioridad)) {
                                    'urgente' => 'danger',
                                    'alta' => 'warning',
                                    'media' => 'info',
                                    'baja' => 'success',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $prioridadColor; ?> bg-opacity-10 text-<?php echo $prioridadColor; ?>">
                                    <?php echo ucfirst($prioridad); ?>
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Estado</label>
                            <p class="mb-0">
                                <?php 
                                $estadoOrden = $supervision['orden']['estado'] ?? 'PENDIENTE';
                                $estadoOrdenColor = match(strtolower($estadoOrden)) {
                                    'completada', 'cerrada', 'aprobada' => 'success',
                                    'en_progreso', 'en proceso' => 'info',
                                    'cancelada', 'rechazada' => 'danger',
                                    default => 'warning'
                                };
                                ?>
                                <span class="badge bg-<?php echo $estadoOrdenColor; ?> bg-opacity-10 text-<?php echo $estadoOrdenColor; ?>">
                                    <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                    <?php echo ucfirst($estadoOrden); ?>
                                </span>
                            </p>
                        </div>
                        <a href="/proyecto/ordenes/ver/<?php echo $supervision['orden_id']; ?>" class="btn btn-info w-100">
                            <i class="fas fa-eye me-1"></i> Ver Orden
                        </a>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Orden no disponible</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ✅ Estilos -->
<style>
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.badge-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
}
</style>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>