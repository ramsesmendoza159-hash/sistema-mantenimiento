<?php
// views/supervisor/revisar.php
// Revisar Orden - VERSIÓN COMPLETA

if (!isset($seccion)) {
    $seccion = 'supervisor';
}
if (!isset($titulo)) {
    $titulo = 'Revisar Orden de Trabajo';
}
if (!isset($orden) || !$orden) {
    header('Location: /proyecto/supervisor/ordenes');
    exit();
}

include_once __DIR__ . '/../layouts/header.php';
// ❌ NO incluir sidebar aquí (ya está en header)
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-clipboard-check text-primary me-2"></i>Revisar Orden #<?php echo $orden['id']; ?>
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Revisa y evalúa el trabajo realizado
            </p>
        </div>
        <a href="/proyecto/supervisor/ordenes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
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
                                <label class="text-muted small fw-semibold text-uppercase">Técnico</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['tecnico'] ?? 'Sin asignar'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Fecha creación</label>
                                <p class="fw-semibold mb-0"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_creacion'])); ?></p>
                            </div>
                            <?php if (!empty($orden['fecha_cierre'])): ?>
                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold text-uppercase">Fecha cierre</label>
                                    <p class="fw-semibold mb-0"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_cierre'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Tiempo invertido</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['tiempo_invertido'] ?? 'N/A'; ?> horas</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold text-uppercase">Repuestos</label>
                                <p class="fw-semibold mb-0"><?php echo $orden['repuestos_utilizados'] ?? 'Ninguno'; ?></p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <label class="text-muted small fw-semibold text-uppercase">Descripción</label>
                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion'])); ?></p>
                    </div>
                    <?php if (!empty($orden['descripcion_cierre'])): ?>
                        <hr>
                        <div>
                            <label class="text-muted small fw-semibold text-uppercase">Trabajo Realizado</label>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Evidencias -->
            <?php if (!empty($orden['evidencias'])): ?>
                <div class="card border-0 mt-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-images text-primary me-2"></i> Evidencias
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php 
                            $evidencias = is_array($orden['evidencias']) ? $orden['evidencias'] : explode(',', $orden['evidencias']);
                            foreach ($evidencias as $evidencia): 
                                $evidencia = trim($evidencia);
                                if (empty($evidencia)) continue;
                            ?>
                                <div class="col-md-3 col-6">
                                    <a href="/proyecto/uploads/evidencias/<?php echo $evidencia; ?>" target="_blank" class="d-block">
                                        <img src="/proyecto/uploads/evidencias/<?php echo $evidencia; ?>" 
                                             alt="Evidencia" class="img-fluid rounded" 
                                             style="height: 150px; width: 100%; object-fit: cover;">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna lateral - Formulario de supervisión -->
        <div class="col-lg-4">
            <div class="card border-0">
                <div class="card-header bg-primary text-white border-0">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-clipboard-check me-2"></i> Revisión de Supervisión
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/proyecto/supervisor/guardar_revision" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCSRFToken(); ?>">
                        <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="calificacion" class="form-label fw-semibold">Calificación <span class="text-danger">*</span></label>
                            <select class="form-select" id="calificacion" name="calificacion" required>
                                <option value="">Seleccionar...</option>
                                <option value="1">⭐ 1 - Muy malo</option>
                                <option value="2">⭐⭐ 2 - Malo</option>
                                <option value="3">⭐⭐⭐ 3 - Regular</option>
                                <option value="4">⭐⭐⭐⭐ 4 - Bueno</option>
                                <option value="5">⭐⭐⭐⭐⭐ 5 - Excelente</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label fw-semibold">Decisión <span class="text-danger">*</span></label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="">Seleccionar...</option>
                                <option value="APROBADA">✅ Aprobar</option>
                                <option value="RECHAZADA">❌ Rechazar</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Si rechazas, explica el motivo..."></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="cumple" name="cumple" value="1" checked>
                            <label class="form-check-label" for="cumple">
                                Cumple con los estándares de calidad
                            </label>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Guardar Revisión
                        </button>
                    </form>
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
</style>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>