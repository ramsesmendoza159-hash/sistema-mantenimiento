<?php
// views/tecnico/detalle_orden.php
// Ubicación: C:\xampp\htdocs\proyecto\views\tecnico\detalle_orden.php

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'tecnico') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Detalle de Orden";
$seccion = "tecnico";
include_once __DIR__ . '/../layouts/header.php';

$orden = $orden ?? null;
if (!$orden) {
    header('Location: /proyecto/tecnico/mis_ordenes');
    exit();
}

$estado = $orden['estado'] ?? 'PENDIENTE';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header de página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-clipboard-list text-primary me-2"></i>Detalle de Orden #<?php echo $orden['id']; ?>
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Información completa de la orden de trabajo
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/proyecto/tecnico/mis_ordenes" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <?php if ($estado === 'PENDIENTE' || $estado === 'EN_PROCESO'): ?>
                <a href="/proyecto/tecnico/cerrar_orden/<?php echo $orden['id']; ?>" class="btn btn-success">
                    <i class="fas fa-check-circle me-1"></i> Cerrar Orden
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <!-- Información de la orden -->
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información de la Orden
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Título</label>
                                <p class="fw-semibold mb-0"><?php echo htmlspecialchars($orden['titulo'] ?? ''); ?></p>
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
                                    $color = match($prioridad) {
                                        'Urgente' => 'danger',
                                        'Alta' => 'warning',
                                        'Media' => 'info',
                                        'Baja' => 'success',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?>">
                                        <?php echo $prioridad; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Estado</label>
                                <p class="mb-0">
                                    <?php 
                                    $color = match($estado) {
                                        'CERRADA', 'APROBADA' => 'success',
                                        'EN_PROCESO' => 'info',
                                        'CANCELADA', 'RECHAZADA' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?>">
                                        <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                        <?php echo $estado; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Fecha creación</label>
                                <p class="fw-semibold mb-0"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_creacion'] ?? 'now')); ?></p>
                            </div>
                            <?php if (!empty($orden['fecha_limite'])): ?>
                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold text-uppercase">Fecha límite</label>
                                    <p class="fw-semibold mb-0"><?php echo date('d/m/Y', strtotime($orden['fecha_limite'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($orden['fecha_cierre'])): ?>
                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold text-uppercase">Fecha cierre</label>
                                    <p class="fw-semibold mb-0"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_cierre'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Técnico asignado</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['tecnico'] ?? 'Sin asignar'; ?></p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <label class="text-muted small fw-semibold text-uppercase">Descripción</label>
                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion'] ?? 'Sin descripción')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Trabajo realizado -->
            <?php if ($estado === 'CERRADA' || $estado === 'APROBADA'): ?>
                <div class="card border-0 mt-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-semibold text-success">
                            <i class="fas fa-check-circle text-success me-2"></i> Trabajo Realizado
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($orden['descripcion_cierre'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Descripción del trabajo</label>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <?php if (!empty($orden['tiempo_invertido'])): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small fw-semibold text-uppercase">Tiempo invertido</label>
                                        <p class="fw-semibold mb-0"><?php echo $orden['tiempo_invertido']; ?> horas</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($orden['repuestos_utilizados'])): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted small fw-semibold text-uppercase">Repuestos utilizados</label>
                                        <p class="fw-semibold mb-0"><?php echo htmlspecialchars($orden['repuestos_utilizados']); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
            <!-- Evidencias -->
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-images text-primary me-2"></i> Evidencias
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($orden['evidencias'])): ?>
                        <div class="row g-2">
                            <?php 
                            $evidencias = is_array($orden['evidencias']) ? $orden['evidencias'] : explode(',', $orden['evidencias']);
                            foreach ($evidencias as $evidencia): 
                                $evidencia = trim($evidencia);
                                if (empty($evidencia)) continue;
                            ?>
                                <div class="col-6">
                                    <a href="/proyecto/uploads/evidencias/<?php echo $evidencia; ?>" target="_blank" class="d-block">
                                        <img src="/proyecto/uploads/evidencias/<?php echo $evidencia; ?>" 
                                             alt="Evidencia" class="img-fluid rounded" 
                                             style="max-height: 100px; width: 100%; object-fit: cover;">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-images fa-2x d-block mb-2"></i>
                            <span>No hay evidencias registradas</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones -->
            <?php if ($estado === 'PENDIENTE' || $estado === 'EN_PROCESO'): ?>
                <div class="card border-0 mt-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-actions text-warning me-2"></i> Acciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">¿Ya completaste el trabajo?</p>
                        <a href="/proyecto/tecnico/cerrar_orden/<?php echo $orden['id']; ?>" class="btn btn-success w-100">
                            <i class="fas fa-check-circle me-2"></i> Cerrar Orden
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<style>
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>