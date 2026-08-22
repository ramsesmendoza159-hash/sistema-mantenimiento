<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'supervisor') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Detalle de Supervisión";
$seccion = "supervisor";
include_once __DIR__ . '/../layouts/header.php';

$supervision = $supervision ?? null;
if (!$supervision) {
    header('Location: /produmar/supervisor/supervisiones');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detalle de Supervisión #<?php echo $supervision['id']; ?></h1>
                <a href="/produmar/supervisor/supervisiones" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información de la Supervisión</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> <?php echo $supervision['id']; ?></p>
                                    <p><strong>Orden:</strong> #<?php echo $supervision['orden_id']; ?></p>
                                    <p><strong>Técnico:</strong> <?php echo $supervision['tecnico'] ?? 'N/A'; ?></p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-<?php echo $supervision['estado'] === 'aprobada' ? 'success' : 
                                                                 ($supervision['estado'] === 'rechazada' ? 'danger' : 'warning'); ?>">
                                            <?php echo $supervision['estado']; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha creación:</strong> <?php echo $supervision['fecha_creacion']; ?></p>
                                    <p><strong>Calificación:</strong> 
                                        <?php if ($supervision['calificacion']): ?>
                                            <span class="fw-bold">
                                                <?php echo $supervision['calificacion']; ?>/5
                                            </span>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $supervision['calificacion'] ? '-fill' : ''; ?> 
                                                   text-<?php echo $i <= $supervision['calificacion'] ? 'warning' : 'secondary'; ?>">
                                                </i>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Cumple estándares:</strong> 
                                        <?php echo $supervision['cumple'] ? '✅ Sí' : '❌ No'; ?>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Observaciones</h6>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($supervision['observaciones'] ?? 'Sin observaciones')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Detalle de la orden -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Orden Relacionada</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($supervision['orden'])): ?>
                                <p><strong>Título:</strong> <?php echo htmlspecialchars($supervision['orden']['titulo']); ?></p>
                                <p><strong>Área:</strong> <?php echo $supervision['orden']['area'] ?? 'N/A'; ?></p>
                                <p><strong>Prioridad:</strong> 
                                    <span class="badge bg-<?php echo $supervision['orden']['prioridad'] === 'urgente' ? 'danger' : 
                                                             ($supervision['orden']['prioridad'] === 'alta' ? 'warning' : 'info'); ?>">
                                        <?php echo $supervision['orden']['prioridad']; ?>
                                    </span>
                                </p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge bg-<?php echo $supervision['orden']['estado'] === 'completada' ? 'success' : 'warning'; ?>">
                                        <?php echo $supervision['orden']['estado']; ?>
                                    </span>
                                </p>
                                <a href="/produmar/supervisor/ver_orden/<?php echo $supervision['orden_id']; ?>" class="btn btn-sm btn-info w-100">
                                    <i class="bi bi-eye"></i> Ver Orden
                                </a>
                            <?php else: ?>
                                <p class="text-muted">Orden no disponible</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <?php if ($supervision['estado'] !== 'aprobada' && $supervision['estado'] !== 'rechazada'): ?>
                    <div class="card mt-3 border-warning">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Acciones</h5>
                        </div>
                        <div class="card-body">
                            <a href="/produmar/supervisor/revisar/<?php echo $supervision['orden_id']; ?>" class="btn btn-primary w-100">
                                <i class="bi bi-clipboard-check"></i> Completar Revisión
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>