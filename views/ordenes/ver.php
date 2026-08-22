<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Detalle de Orden de Trabajo";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';

// El controlador debe pasar la variable $orden
$orden = $orden ?? null;
if (!$orden) {
    header('Location: /produmar/ordenes');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Orden #<?php echo $orden['id']; ?></h1>
                <div>
                    <a href="/produmar/ordenes" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <?php if ($orden['estado'] !== 'completada' && $orden['estado'] !== 'cancelada'): ?>
                        <a href="/produmar/ordenes/editar/<?php echo $orden['id']; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a href="/produmar/ordenes/cerrar/<?php echo $orden['id']; ?>" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Cerrar
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <!-- Información principal -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información de la Orden</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Título:</strong> <?php echo htmlspecialchars($orden['titulo']); ?></p>
                                    <p><strong>Área:</strong> <?php echo $orden['area'] ?? 'N/A'; ?></p>
                                    <p><strong>Prioridad:</strong> 
                                        <span class="badge bg-<?php echo $orden['prioridad'] === 'urgente' ? 'danger' : 
                                                                 ($orden['prioridad'] === 'alta' ? 'warning' : 'info'); ?>">
                                            <?php echo $orden['prioridad']; ?>
                                        </span>
                                    </p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-<?php echo $orden['estado'] === 'completada' ? 'success' : 
                                                                 ($orden['estado'] === 'en_progreso' ? 'info' : 
                                                                 ($orden['estado'] === 'cancelada' ? 'danger' : 'warning')); ?>">
                                            <?php echo $orden['estado']; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha creación:</strong> <?php echo $orden['fecha_creacion']; ?></p>
                                    <?php if ($orden['fecha_limite']): ?>
                                        <p><strong>Fecha límite:</strong> <?php echo $orden['fecha_limite']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($orden['fecha_cierre']): ?>
                                        <p><strong>Fecha cierre:</strong> <?php echo $orden['fecha_cierre']; ?></p>
                                    <?php endif; ?>
                                    <p><strong>Técnico asignado:</strong> <?php echo $orden['tecnico'] ?? 'Sin asignar'; ?></p>
                                    <?php if ($orden['equipo']): ?>
                                        <p><strong>Equipo:</strong> <?php echo $orden['equipo']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Descripción</h6>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de cierre -->
                    <?php if ($orden['estado'] === 'completada' || $orden['estado'] === 'cancelada'): ?>
                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Información de Cierre</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($orden['descripcion_cierre']): ?>
                                <p><strong>Descripción del trabajo:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if ($orden['tiempo_invertido']): ?>
                                        <p><strong>Tiempo invertido:</strong> <?php echo $orden['tiempo_invertido']; ?> horas</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($orden['repuestos_utilizados']): ?>
                                        <p><strong>Repuestos utilizados:</strong> <?php echo htmlspecialchars($orden['repuestos_utilizados']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($orden['satisfactorio'] !== null): ?>
                                <p><strong>Trabajo satisfactorio:</strong> 
                                    <?php echo $orden['satisfactorio'] ? '✅ Sí' : '❌ No'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <!-- Evidencias -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Evidencias</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($orden['evidencias'])): ?>
                                <div class="row g-2">
                                    <?php foreach (explode(',', $orden['evidencias']) as $evidencia): ?>
                                        <div class="col-6">
                                            <a href="/produmar/uploads/evidencias/<?php echo trim($evidencia); ?>" target="_blank">
                                                <img src="/produmar/uploads/evidencias/<?php echo trim($evidencia); ?>" 
                                                     alt="Evidencia" class="img-fluid rounded">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay evidencias registradas</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Historial de cambios -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Historial</h5>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <?php if (!empty($orden['historial'])): ?>
                                <?php foreach ($orden['historial'] as $registro): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <small class="text-muted"><?php echo $registro['fecha']; ?></small>
                                        <p class="mb-0"><?php echo htmlspecialchars($registro['descripcion']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No hay historial disponible</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>