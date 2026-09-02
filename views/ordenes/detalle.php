<?php
// views/ordenes/detalle.php
// Detalle Completo de Orden - VERSIÓN CORREGIDA

// Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Detalle Completo de Orden";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';

$orden = $orden ?? null;
if (!$orden) {
    header('Location: /proyecto/ordenes');
    exit();
}

$estado = $orden['estado'] ?? 'PENDIENTE';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                Detalle de Orden #<?php echo $orden['id']; ?>
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Información completa de la orden de trabajo
            </p>
        </div>
        <a href="/proyecto/ordenes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- ✅ Detalle completo -->
    <div class="card border-0">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="fw-semibold">
                        <i class="fas fa-info-circle text-primary me-2"></i>Información General
                    </h5>
                    <div class="mt-3">
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">ID</label>
                            <p class="fw-semibold mb-0"><?php echo $orden['id']; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Título</label>
                            <p class="fw-semibold mb-0"><?php echo htmlspecialchars($orden['titulo']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Área</label>
                            <p class="fw-semibold mb-0"><?php echo $orden['area'] ?? 'N/A'; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Prioridad</label>
                            <p class="mb-0">
                                <?php 
                                $prioridad = $orden['prioridad'] ?? 'Media';
                                $prioridadColor = match($prioridad) {
                                    'Urgente' => 'danger',
                                    'Alta' => 'warning',
                                    'Media' => 'info',
                                    'Baja' => 'success',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $prioridadColor; ?> bg-opacity-10 text-<?php echo $prioridadColor; ?>">
                                    <?php echo $prioridad; ?>
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Estado</label>
                            <p class="mb-0">
                                <?php 
                                $estadoColor = match($estado) {
                                    'CERRADA', 'APROBADA' => 'success',
                                    'EN_PROCESO' => 'info',
                                    'EJECUTADA' => 'primary',
                                    'CANCELADA', 'RECHAZADA' => 'danger',
                                    default => 'warning'
                                };
                                ?>
                                <span class="badge bg-<?php echo $estadoColor; ?> bg-opacity-10 text-<?php echo $estadoColor; ?>">
                                    <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                    <?php echo $estado; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-semibold">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>Fechas y Asignación
                    </h5>
                    <div class="mt-3">
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Fecha Creación</label>
                            <p class="fw-semibold mb-0"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_creacion'] ?? 'now')); ?></p>
                        </div>
                        <?php if (!empty($orden['fecha_limite'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Fecha Límite</label>
                                <p class="fw-semibold mb-0"><?php echo date('d/m/Y', strtotime($orden['fecha_limite'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($orden['fecha_cierre'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Fecha Cierre</label>
                                <p class="fw-semibold mb-0"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_cierre'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Técnico</label>
                            <p class="fw-semibold mb-0"><?php echo $orden['tecnico'] ?? 'Sin asignar'; ?></p>
                        </div>
                        <?php if (!empty($orden['equipo'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Equipo</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['equipo']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mt-3">
                <h5 class="fw-semibold">
                    <i class="fas fa-align-left text-primary me-2"></i>Descripción
                </h5>
                <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion'])); ?></p>
            </div>

            <?php if ($estado === 'CERRADA' || $estado === 'APROBADA' || $estado === 'EJECUTADA'): ?>
                <hr>
                <div class="mt-3">
                    <h5 class="fw-semibold text-success">
                        <i class="fas fa-check-circle text-success me-2"></i>Detalles de Cierre
                    </h5>
                    <?php if (!empty($orden['descripcion_cierre'])): ?>
                        <div class="mt-3 mb-3">
                            <label class="text-muted small fw-semibold text-uppercase">Descripción del trabajo</label>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                        </div>
                    <?php endif; ?>
                    <div class="row g-3">
                        <?php if (isset($orden['tiempo_invertido'])): ?>
                            <div class="col-md-4">
                                <label class="text-muted small fw-semibold text-uppercase">Tiempo invertido</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['tiempo_invertido'] ?? 'N/A'; ?> horas</p>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($orden['repuestos_utilizados'])): ?>
                            <div class="col-md-4">
                                <label class="text-muted small fw-semibold text-uppercase">Repuestos</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['repuestos_utilizados'] ?? 'Ninguno'; ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($orden['satisfactorio'])): ?>
                            <div class="col-md-4">
                                <label class="text-muted small fw-semibold text-uppercase">Satisfactorio</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['satisfactorio'] ? '✅ Sí' : '❌ No'; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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