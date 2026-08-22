<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'supervisor') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Revisar Orden de Trabajo";
$seccion = "supervisor";
include_once __DIR__ . '/../layouts/header.php';

$orden = $orden ?? null;
if (!$orden) {
    header('Location: /produmar/supervisor/ordenes');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Revisar Orden #<?php echo $orden['id']; ?></h1>
                <a href="/produmar/supervisor/ordenes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <!-- Información de la orden -->
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
                                    <p><strong>Técnico:</strong> <?php echo $orden['tecnico'] ?? 'Sin asignar'; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha creación:</strong> <?php echo $orden['fecha_creacion']; ?></p>
                                    <p><strong>Fecha cierre:</strong> <?php echo $orden['fecha_cierre'] ?? 'N/A'; ?></p>
                                    <p><strong>Tiempo invertido:</strong> <?php echo $orden['tiempo_invertido'] ?? 'N/A'; ?> horas</p>
                                    <p><strong>Repuestos:</strong> <?php echo $orden['repuestos_utilizados'] ?? 'Ninguno'; ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Descripción</h6>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion'])); ?></p>
                                </div>
                            </div>
                            <?php if ($orden['descripcion_cierre']): ?>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Trabajo Realizado</h6>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Evidencias -->
                    <?php if (!empty($orden['evidencias'])): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Evidencias</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <?php foreach (explode(',', $orden['evidencias']) as $evidencia): ?>
                                    <div class="col-md-3 col-6">
                                        <a href="/produmar/uploads/evidencias/<?php echo trim($evidencia); ?>" target="_blank">
                                            <img src="/produmar/uploads/evidencias/<?php echo trim($evidencia); ?>" 
                                                 alt="Evidencia" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <!-- Formulario de supervisión -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Revisión de Supervisión</h5>
                        </div>
                        <div class="card-body">
                            <form action="/produmar/supervisor/guardar_revision" method="POST">
                                <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="calificacion" class="form-label">Calificación *</label>
                                    <select class="form-select" id="calificacion" name="calificacion" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="1">1 - Muy malo</option>
                                        <option value="2">2 - Malo</option>
                                        <option value="3">3 - Regular</option>
                                        <option value="4">4 - Bueno</option>
                                        <option value="5">5 - Excelente</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="estado" class="form-label">Decisión *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="aprobada">✅ Aprobar</option>
                                        <option value="rechazada">❌ Rechazar</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="4"></textarea>
                                    <div class="form-text">Si rechazas, explica el motivo</div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="cumple" name="cumple" value="1" checked>
                                    <label class="form-check-label" for="cumple">
                                        Cumple con los estándares de calidad
                                    </label>
                                </div>

                                <hr>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-save"></i> Guardar Revisión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>