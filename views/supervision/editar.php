<?php
// views/supervision/editar.php
// Editar Supervisión - VERSIÓN CORREGIDA

// Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Editar Supervisión";
$seccion = "supervision";
include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';

$supervision = $supervision ?? null;
if (!$supervision) {
    header('Location: /proyecto/supervision');
    exit();
}
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-edit text-warning me-2"></i>Editar Supervisión #<?php echo $supervision['id']; ?>
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Modifica los datos de la supervisión
            </p>
        </div>
        <a href="/proyecto/supervision" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- ✅ Formulario -->
    <div class="card border-0">
        <div class="card-body">
            <form action="/proyecto/supervision/actualizar/<?php echo $supervision['id']; ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCSRFToken(); ?>">
                <input type="hidden" name="_method" value="PUT">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="orden_id" class="form-label fw-semibold">Orden de Trabajo <span class="text-danger">*</span></label>
                            <select class="form-select" id="orden_id" name="orden_id" required>
                                <option value="">Seleccionar orden...</option>
                                <!-- Cargado por AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supervisor_id" class="form-label fw-semibold">Supervisor <span class="text-danger">*</span></label>
                            <select class="form-select" id="supervisor_id" name="supervisor_id" required>
                                <option value="">Seleccionar supervisor...</option>
                                <!-- Cargado por AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Observaciones de la supervisión..."><?php echo htmlspecialchars($supervision['observaciones'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="calificacion" class="form-label fw-semibold">Calificación</label>
                            <select class="form-select" id="calificacion" name="calificacion">
                                <option value="">Sin calificar</option>
                                <option value="1" <?php echo $supervision['calificacion'] == 1 ? 'selected' : ''; ?>>⭐ 1 - Muy malo</option>
                                <option value="2" <?php echo $supervision['calificacion'] == 2 ? 'selected' : ''; ?>>⭐⭐ 2 - Malo</option>
                                <option value="3" <?php echo $supervision['calificacion'] == 3 ? 'selected' : ''; ?>>⭐⭐⭐ 3 - Regular</option>
                                <option value="4" <?php echo $supervision['calificacion'] == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ 4 - Bueno</option>
                                <option value="5" <?php echo $supervision['calificacion'] == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ 5 - Excelente</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="estado" class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="PENDIENTE" <?php echo $supervision['estado'] === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="APROBADA" <?php echo $supervision['estado'] === 'APROBADA' ? 'selected' : ''; ?>>✅ Aprobada</option>
                                <option value="RECHAZADA" <?php echo $supervision['estado'] === 'RECHAZADA' ? 'selected' : ''; ?>>❌ Rechazada</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_supervision" class="form-label fw-semibold">Fecha de Supervisión</label>
                            <input type="datetime-local" class="form-control" id="fecha_supervision" 
                                   name="fecha_supervision" 
                                   value="<?php echo $supervision['fecha_supervision'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="cumple" name="cumple" 
                                   <?php echo ($supervision['cumple'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="cumple">
                                Cumple con los estándares de calidad
                            </label>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex gap-2">
                    <a href="/proyecto/supervision" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- ✅ Estilos -->
<style>
.form-group {
    margin-bottom: 0;
}
.form-label {
    font-size: 0.85rem;
    margin-bottom: 0.4rem;
}
.form-control, .form-select {
    border-radius: 10px;
    padding: 10px 14px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<script>
    const ordenActual = <?php echo json_encode($supervision['orden_id'] ?? null); ?>;
    const supervisorActual = <?php echo json_encode($supervision['supervisor_id'] ?? null); ?>;

    function cargarOrdenes() {
        fetch('/proyecto/supervision/ordenes')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('orden_id');
                data.forEach(orden => {
                    const option = document.createElement('option');
                    option.value = orden.id;
                    option.textContent = `${orden.id} - ${orden.titulo}`;
                    if (orden.id == ordenActual) option.selected = true;
                    select.appendChild(option);
                });
            });
    }

    function cargarSupervisores() {
        fetch('/proyecto/supervision/supervisores')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('supervisor_id');
                data.forEach(supervisor => {
                    const option = document.createElement('option');
                    option.value = supervisor.id;
                    option.textContent = supervisor.nombre;
                    if (supervisor.id == supervisorActual) option.selected = true;
                    select.appendChild(option);
                });
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        cargarOrdenes();
        cargarSupervisores();
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>