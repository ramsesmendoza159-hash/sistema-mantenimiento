<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Detalle de Supervisión";
$seccion = "supervision";
include_once __DIR__ . '/../layouts/header.php';

$supervision = $supervision ?? null;
if (!$supervision) {
    header('Location: /produmar/supervision');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detalle de Supervisión #<?php echo $supervision['id']; ?></h1>
                <div>
                    <a href="/produmar/supervision" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <a href="/produmar/supervision/editar/<?php echo $supervision['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
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
                                    <p><strong>Orden de Trabajo:</strong> #<?php echo $supervision['orden_id']; ?></p>
                                    <p><strong>Supervisor:</strong> <?php echo $supervision['supervisor']; ?></p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-<?php echo $supervision['estado'] === 'aprobada' ? 'success' : 
                                                                 ($supervision['estado'] === 'rechazada' ? 'danger' : 'warning'); ?>">
                                            <?php echo $supervision['estado']; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha de supervisión:</strong> <?php echo $supervision['fecha_supervision'] ?? 'Pendiente'; ?></p>
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
                                    <span class="badge bg-<?php echo $supervision['orden']['prioridad'] === 'urgente' ? 'danger' : 'info'; ?>">
                                        <?php echo $supervision['orden']['prioridad']; ?>
                                    </span>
                                </p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge bg-<?php echo $supervision['orden']['estado'] === 'completada' ? 'success' : 'warning'; ?>">
                                        <?php echo $supervision['orden']['estado']; ?>
                                    </span>
                                </p>
                                <a href="/produmar/ordenes/ver/<?php echo $supervision['orden_id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Ver Orden
                                </a>
                            <?php else: ?>
                                <p class="text-muted">Orden no disponible</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>