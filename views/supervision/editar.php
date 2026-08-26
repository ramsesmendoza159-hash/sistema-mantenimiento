<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Editar Supervisión";
$seccion = "supervision";
include_once __DIR__ . '/../layouts/header.php';

$supervision = $supervision ?? null;
if (!$supervision) {
    header('Location: /proyecto/supervision');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Editar Supervisión #<?php echo $supervision['id']; ?></h1>
                <a href="/proyecto/supervision" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="/proyecto/supervision/actualizar/<?php echo $supervision['id']; ?>" method="POST">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orden_id" class="form-label">Orden de Trabajo *</label>
                                    <select class="form-select" id="orden_id" name="orden_id" required>
                                        <option value="">Seleccionar orden...</option>
                                        <!-- Cargado por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supervisor_id" class="form-label">Supervisor *</label>
                                    <select class="form-select" id="supervisor_id" name="supervisor_id" required>
                                        <option value="">Seleccionar supervisor...</option>
                                        <!-- Cargado por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?php echo htmlspecialchars($supervision['observaciones'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="calificacion" class="form-label">Calificación</label>
                                    <select class="form-select" id="calificacion" name="calificacion">
                                        <option value="">Sin calificar</option>
                                        <option value="1" <?php echo $supervision['calificacion'] == 1 ? 'selected' : ''; ?>>1 - Muy malo</option>
                                        <option value="2" <?php echo $supervision['calificacion'] == 2 ? 'selected' : ''; ?>>2 - Malo</option>
                                        <option value="3" <?php echo $supervision['calificacion'] == 3 ? 'selected' : ''; ?>>3 - Regular</option>
                                        <option value="4" <?php echo $supervision['calificacion'] == 4 ? 'selected' : ''; ?>>4 - Bueno</option>
                                        <option value="5" <?php echo $supervision['calificacion'] == 5 ? 'selected' : ''; ?>>5 - Excelente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="PENDIENTE" <?php echo $supervision['estado'] === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="APROBADA" <?php echo $supervision['estado'] === 'APROBADA' ? 'selected' : ''; ?>>Aprobada</option>
                                        <option value="RECHAZADA" <?php echo $supervision['estado'] === 'RECHAZADA' ? 'selected' : ''; ?>>Rechazada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha_supervision" class="form-label">Fecha de Supervisión</label>
                                    <input type="datetime-local" class="form-control" id="fecha_supervision" 
                                           name="fecha_supervision" 
                                           value="<?php echo $supervision['fecha_supervision'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="cumple" name="cumple" 
                                           <?php echo ($supervision['cumple'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cumple">
                                        Cumple con los estándares de calidad
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="/proyecto/supervision" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

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