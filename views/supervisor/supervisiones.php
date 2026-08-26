<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'supervisor') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Mis Supervisiones";
$seccion = "supervisor";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Mis Supervisiones</h1>
                <a href="/proyecto/supervisor" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Panel
                </a>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="filtrosForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="APROBADA">Aprobada</option>
                                <option value="RECHAZADA">Rechazada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="calificacion" class="form-label">Calificación</label>
                            <select class="form-select" id="calificacion" name="calificacion">
                                <option value="">Todas</option>
                                <option value="1">1 - Muy malo</option>
                                <option value="2">2 - Malo</option>
                                <option value="3">3 - Regular</option>
                                <option value="4">4 - Bueno</option>
                                <option value="5">5 - Excelente</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de supervisiones -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Orden</th>
                                    <th>Técnico</th>
                                    <th>Calificación</th>
                                    <th>Estado</th>
                                    <th>Cumple</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="supervisionesBody">
                                <!-- Cargado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="paginacion"></div>
                        <div>
                            <span id="totalRegistros">Mostrando 0 registros</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    let paginaActual = 1;
    const porPagina = 10;

    function cargarSupervisiones(page = 1) {
        paginaActual = page;
        const formData = new FormData(document.getElementById('filtrosForm'));
        const params = new URLSearchParams(formData);
        params.append('page', page);
        params.append('limit', porPagina);

        fetch(`/proyecto/supervisor/supervisionesList?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('supervisionesBody');
                tbody.innerHTML = '';

                if (data.supervisiones && data.supervisiones.length > 0) {
                    data.supervisiones.forEach(supervision => {
                        const tr = document.createElement('tr');
                        const estadoClass = supervision.estado === 'APROBADA' ? 'success' : 'danger';
                        const calificacionClass = supervision.calificacion >= 4 ? 'text-success' :
                                                  supervision.calificacion >= 3 ? 'text-warning' :
                                                  'text-danger';

                        tr.innerHTML = `
                            <td>${supervision.id}</td>
                            <td>#${supervision.orden_id}</td>
                            <td>${supervision.tecnico || 'N/A'}</td>
                            <td class="${calificacionClass} fw-bold">
                                ${supervision.calificacion ? supervision.calificacion + '/5' : 'N/A'}
                            </td>
                            <td><span class="badge bg-${estadoClass}">${supervision.estado}</span></td>
                            <td>${supervision.cumple ? '✅ Sí' : '❌ No'}</td>
                            <td>${supervision.fecha_creacion}</td>
                            <td>
                                <a href="/proyecto/supervisor/ver_supervision/${supervision.id}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    actualizarPaginacion(data.total, data.paginas);
                    document.getElementById('totalRegistros').textContent = 
                        `Mostrando ${data.supervisiones.length} de ${data.total} registros`;
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center">No hay supervisiones registradas</td></tr>`;
                    document.getElementById('totalRegistros').textContent = 'Mostrando 0 registros';
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function actualizarPaginacion(total, paginas) {
        const div = document.getElementById('paginacion');
        div.innerHTML = '';
        
        if (paginas <= 1) return;

        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm';
        
        for (let i = 1; i <= paginas; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = i;
            a.onclick = (e) => {
                e.preventDefault();
                cargarSupervisiones(i);
            };
            li.appendChild(a);
            ul.appendChild(li);
        }
        
        div.appendChild(ul);
    }

    document.getElementById('filtrosForm').addEventListener('submit', function(e) {
        e.preventDefault();
        cargarSupervisiones(1);
    });

    document.getElementById('filtrosForm').addEventListener('reset', function(e) {
        e.preventDefault();
        this.querySelectorAll('select, input').forEach(el => el.value = '');
        cargarSupervisiones(1);
    });

    document.addEventListener('DOMContentLoaded', function() {
        cargarSupervisiones(1);
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>