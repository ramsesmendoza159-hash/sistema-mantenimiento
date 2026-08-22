<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Editar Orden de Trabajo";
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
                <h1 class="h2">Editar Orden #<?php echo $orden['id']; ?></h1>
                <a href="/produmar/ordenes/ver/<?php echo $orden['id']; ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="/produmar/ordenes/actualizar/<?php echo $orden['id']; ?>" method="POST" id="ordenForm">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título de la orden *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo htmlspecialchars($orden['titulo']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="prioridad" class="form-label">Prioridad *</label>
                                    <select class="form-select" id="prioridad" name="prioridad" required>
                                        <option value="baja" <?php echo $orden['prioridad'] === 'baja' ? 'selected' : ''; ?>>Baja</option>
                                        <option value="media" <?php echo $orden['prioridad'] === 'media' ? 'selected' : ''; ?>>Media</option>
                                        <option value="alta" <?php echo $orden['prioridad'] === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                        <option value="urgente" <?php echo $orden['prioridad'] === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="area_id" class="form-label">Área *</label>
                                    <select class="form-select" id="area_id" name="area_id" required>
                                        <option value="">Seleccionar área...</option>
                                        <!-- Cargado por AJAX con selección actual -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tecnico_id" class="form-label">Técnico asignado</label>
                                    <select class="form-select" id="tecnico_id" name="tecnico_id">
                                        <option value="">Sin asignar</option>
                                        <!-- Cargado por AJAX con selección actual -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción detallada *</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?php echo htmlspecialchars($orden['descripcion']); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_limite" class="form-label">Fecha límite</label>
                                    <input type="date" class="form-control" id="fecha_limite" name="fecha_limite" 
                                           value="<?php echo $orden['fecha_limite'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipo_id" class="form-label">Equipo relacionado</label>
                                    <select class="form-select" id="equipo_id" name="equipo_id">
                                        <option value="">Ninguno</option>
                                        <!-- Cargado por AJAX con selección actual -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="pendiente" <?php echo $orden['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="en_progreso" <?php echo $orden['estado'] === 'en_progreso' ? 'selected' : ''; ?>>En Progreso</option>
                                        <option value="completada" <?php echo $orden['estado'] === 'completada' ? 'selected' : ''; ?>>Completada</option>
                                        <option value="cancelada" <?php echo $orden['estado'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="/produmar/ordenes/ver/<?php echo $orden['id']; ?>" class="btn btn-secondary me-2">
                                Cancelar
                            </a>
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
    const areaActual = <?php echo json_encode($orden['area_id'] ?? null); ?>;
    const tecnicoActual = <?php echo json_encode($orden['tecnico_id'] ?? null); ?>;
    const equipoActual = <?php echo json_encode($orden['equipo_id'] ?? null); ?>;

    // Cargar áreas
    function cargarAreas() {
        fetch('/produmar/ordenes/areas')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('area_id');
                data.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.id;
                    option.textContent = area.nombre;
                    if (area.id == areaActual) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            });
    }

    // Cargar técnicos
    function cargarTecnicos() {
        fetch('/produmar/ordenes/tecnicos')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('tecnico_id');
                data.forEach(tecnico => {
                    const option = document.createElement('option');
                    option.value = tecnico.id;
                    option.textContent = tecnico.nombre;
                    if (tecnico.id == tecnicoActual) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            });
    }

    // Cargar equipos
    function cargarEquipos() {
        fetch('/produmar/ordenes/equipos')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('equipo_id');
                data.forEach(equipo => {
                    const option = document.createElement('option');
                    option.value = equipo.id;
                    option.textContent = equipo.nombre;
                    if (equipo.id == equipoActual) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            });
    }

    // Validación del formulario
    document.getElementById('ordenForm').addEventListener('submit', function(e) {
        const titulo = document.getElementById('titulo').value.trim();
        const area = document.getElementById('area_id').value;
        const descripcion = document.getElementById('descripcion').value.trim();
        const fechaLimite = document.getElementById('fecha_limite').value;

        if (!titulo) {
            e.preventDefault();
            alert('El título es obligatorio');
            return;
        }

        if (!area) {
            e.preventDefault();
            alert('Debes seleccionar un área');
            return;
        }

        if (!descripcion) {
            e.preventDefault();
            alert('La descripción es obligatoria');
            return;
        }

        if (fechaLimite) {
            const hoy = new Date().toISOString().split('T')[0];
            if (fechaLimite < hoy) {
                e.preventDefault();
                alert('La fecha límite no puede ser anterior a hoy');
                return;
            }
        }
    });

    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        cargarAreas();
        cargarTecnicos();
        cargarEquipos();
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>