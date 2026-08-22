<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'tecnico') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Mis Órdenes de Trabajo";
$seccion = "tecnico";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Mis Órdenes de Trabajo</h1>
                <a href="/produmar/tecnico" class="btn btn-secondary">
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
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="prioridad" class="form-label">Prioridad</label>
                            <select class="form-select" id="prioridad" name="prioridad">
                                <option value="">Todas</option>
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
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

            <!-- Tabla de órdenes -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Área</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="ordenesBody">
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

    function cargarOrdenes(page = 1) {
        paginaActual = page;
        const formData = new FormData(document.getElementById('filtrosForm'));
        const params = new URLSearchParams(formData);
        params.append('page', page);
        params.append('limit', porPagina);

        fetch(`/produmar/tecnico/mis_ordenes_list?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('ordenesBody');
                tbody.innerHTML = '';

                if (data.ordenes && data.ordenes.length > 0) {
                    data.ordenes.forEach(orden => {
                        const tr = document.createElement('tr');
                        const prioridadClass = {
                            'baja': 'info',
                            'media': 'warning',
                            'alta': 'danger',
                            'urgente': 'danger'
                        }[orden.prioridad] || 'secondary';

                        const estadoClass = {
                            'pendiente': 'warning',
                            'en_progreso': 'info',
                            'completada': 'success',
                            'cancelada': 'danger'
                        }[orden.estado] || 'secondary';

                        tr.innerHTML = `
                            <td>${orden.id}</td>
                            <td>${orden.titulo}</td>
                            <td>${orden.area || 'N/A'}</td>
                            <td><span class="badge bg-${prioridadClass}">${orden.prioridad}</span></td>
                            <td><span class="badge bg-${estadoClass}">${orden.estado}</span></td>
                            <td>${orden.fecha_creacion}</td>
                            <td>
                                <a href="/produmar/tecnico/detalle_orden/${orden.id}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                ${orden.estado !== 'completada' && orden.estado !== 'cancelada' ? 
                                    `<a href="/produmar/tecnico/cerrar_orden/${orden.id}" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i>
                                    </a>` : ''}
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    actualizarPaginacion(data.total, data.paginas);
                    document.getElementById('totalRegistros').textContent = 
                        `Mostrando ${data.ordenes.length} de ${data.total} registros`;
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center">No hay órdenes asignadas</td></tr>`;
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
                cargarOrdenes(i);
            };
            li.appendChild(a);
            ul.appendChild(li);
        }
        
        div.appendChild(ul);
    }

    document.getElementById('filtrosForm').addEventListener('submit', function(e) {
        e.preventDefault();
        cargarOrdenes(1);
    });

    document.getElementById('filtrosForm').addEventListener('reset', function(e) {
        e.preventDefault();
        this.querySelectorAll('select, input').forEach(el => el.value = '');
        cargarOrdenes(1);
    });

    document.addEventListener('DOMContentLoaded', function() {
        cargarOrdenes(1);
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>