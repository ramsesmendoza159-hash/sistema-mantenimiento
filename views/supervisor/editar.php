<?php
// views/supervisor/editar_supervision.php
// Ubicación: C:\xampp\htdocs\proyecto\views\supervisor\editar_supervision.php

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'supervisor') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Editar Supervisión";
$seccion = "supervisor";
include_once __DIR__ . '/../layouts/header.php';

$supervision = $supervision ?? null;
if (!$supervision) {
    header('Location: /proyecto/supervisor/supervisiones');
    exit();
}

// Verificar que la supervisión pertenezca al supervisor actual
if ($supervision['supervisor_id'] != $_SESSION['usuario_id']) {
    header('Location: /proyecto/supervisor/supervisiones');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Editar Supervisión #<?php echo $supervision['id']; ?></h1>
                <a href="/proyecto/supervisor/supervisiones" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Editar Revisión</h5>
                        </div>
                        <div class="card-body">
                            <form action="/proyecto/supervisor/actualizar_supervision/<?php echo $supervision['id']; ?>" method="POST">
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" name="orden_id" value="<?php echo $supervision['orden_id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="calificacion" class="form-label">Calificación *</label>
                                    <select class="form-select" id="calificacion" name="calificacion" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="1" <?php echo $supervision['calificacion'] == 1 ? 'selected' : ''; ?>>1 - Muy malo</option>
                                        <option value="2" <?php echo $supervision['calificacion'] == 2 ? 'selected' : ''; ?>>2 - Malo</option>
                                        <option value="3" <?php echo $supervision['calificacion'] == 3 ? 'selected' : ''; ?>>3 - Regular</option>
                                        <option value="4" <?php echo $supervision['calificacion'] == 4 ? 'selected' : ''; ?>>4 - Bueno</option>
                                        <option value="5" <?php echo $supervision['calificacion'] == 5 ? 'selected' : ''; ?>>5 - Excelente</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="estado" class="form-label">Decisión *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="APROBADA" <?php echo $supervision['estado'] === 'APROBADA' ? 'selected' : ''; ?>>✅ Aprobar</option>
                                        <option value="RECHAZADA" <?php echo $supervision['estado'] === 'RECHAZADA' ? 'selected' : ''; ?>>❌ Rechazar</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?php echo htmlspecialchars($supervision['observaciones'] ?? ''); ?></textarea>
                                    <div class="form-text">Si rechazas, explica el motivo</div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="cumple" name="cumple" value="1" 
                                           <?php echo ($supervision['cumple'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cumple">
                                        Cumple con los estándares de calidad
                                    </label>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-end">
                                    <a href="/proyecto/supervisor/supervisiones" class="btn btn-secondary me-2">
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Actualizar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información de la Orden</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($supervision['orden'])): ?>
                                <p><strong>Orden:</strong> #<?php echo $supervision['orden']['id']; ?></p>
                                <p><strong>Título:</strong> <?php echo htmlspecialchars($supervision['orden']['titulo']); ?></p>
                                <p><strong>Técnico:</strong> <?php echo $supervision['orden']['tecnico'] ?? 'N/A'; ?></p>
                                <p><strong>Prioridad:</strong> 
                                    <span class="badge bg-<?php 
                                        $prioridad = $supervision['orden']['prioridad'] ?? 'Media';
                                        echo $prioridad === 'Urgente' ? 'danger' : 
                                             ($prioridad === 'Alta' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo $prioridad; ?>
                                    </span>
                                </p>
                                <a href="/proyecto/supervisor/ver_orden/<?php echo $supervision['orden_id']; ?>" class="btn btn-sm btn-info w-100">
                                    <i class="bi bi-eye"></i> Ver Orden
                                </a>
                            <?php else: ?>
                                <p class="text-muted">Orden no disponible</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Información de la Supervisión</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Fecha creación:</strong> <?php echo $supervision['fecha_creacion']; ?></p>
                            <p><strong>Estado actual:</strong> 
                                <span class="badge bg-<?php 
                                    $estado = $supervision['estado'] ?? 'PENDIENTE';
                                    echo $estado === 'APROBADA' ? 'success' : 
                                         ($estado === 'RECHAZADA' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo $estado; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>