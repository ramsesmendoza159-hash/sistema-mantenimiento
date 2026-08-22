<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Crear Orden de Trabajo";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Nueva Orden de Trabajo</h1>
                <a href="/produmar/ordenes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="/produmar/ordenes/guardar" method="POST" id="ordenForm">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título de la orden *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="prioridad" class="form-label">Prioridad *</label>
                                    <select class="form-select" id="prioridad" name="prioridad" required>
                                        <option value="baja">Baja</option>
                                        <option value="media" selected>Media</option>
                                        <option value="alta">Alta</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="area_id" class="form-label">Área *</label>
                                    <select class="form-select" id="area_id" name="area_id" required>
                                        <option value="">Seleccionar área...</option>
                                        <!-- Cargado por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tecnico_id" class="form-label">Técnico asignado</label>
                                    <select class="form-select" id="tecnico_id" name="tecnico_id">
                                        <option value="">Sin asignar</option>
                                        <!-- Cargado por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción detallada *</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_limite" class="form-label">Fecha límite</label>
                                    <input type="date" class="form-control" id="fecha_limite" name="fecha_limite">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipo_id" class="form-label">Equipo relacionado</label>
                                    <select class="form-select" id="equipo_id" name="equipo_id">
                                        <option value="">Ninguno</option>
                                        <!-- Cargado por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="urgente" name="urgente">
                                    <label class="form-check-label" for="urgente">
                                        Marcar como urgente
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="/produmar/ordenes" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear Orden
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
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

        // Si el checkbox urgente se activa, cambiar prioridad a urgente
        document.getElementById('urgente').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('prioridad').value = 'urgente';
                document.getElementById('prioridad').disabled = true;
            } else {
                document.getElementById('prioridad').value = 'media';
                document.getElementById('prioridad').disabled = false;
            }
        });
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>