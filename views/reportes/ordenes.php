<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Reporte de Órdenes";
$seccion = "reportes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Reporte de Órdenes de Trabajo</h1>
                <div>
                    <button class="btn btn-success me-2" onclick="exportarExcel()">
                        <i class="bi bi-file-excel"></i> Exportar Excel
                    </button>
                    <button class="btn btn-danger" onclick="exportarPDF()">
                        <i class="bi bi-file-pdf"></i> Exportar PDF
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="filtrosForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
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
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Generar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row mb-4" id="resumenCards">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title">Total Órdenes</h6>
                            <p class="card-text display-6" id="total">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title">Completadas</h6>
                            <p class="card-text display-6" id="completadas">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6 class="card-title">Pendientes</h6>
                            <p class="card-text display-6" id="pendientes">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6 class="card-title">Canceladas</h6>
                            <p class="card-text display-6" id="canceladas">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de reporte -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="reporteTabla">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Área</th>
                                    <th>Técnico</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Fecha Cierre</th>
                                    <th>Tiempo (horas)</th>
                                </tr>
                            </thead>
                            <tbody id="reporteBody">
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
    const porPagina = 20;

    function cargarReporte(page = 1) {
        paginaActual = page;
        const formData = new FormData(document.getElementById('filtrosForm'));
        const params = new URLSearchParams(formData);
        params.append('page', page);
        params.append('limit', porPagina);

        fetch(`/produmar/reportes/ordenesData?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                // Actualizar resumen
                document.getElementById('total').textContent = data.resumen?.total || 0;
                document.getElementById('completadas').textContent = data.resumen?.completadas || 0;
                document.getElementById('pendientes').textContent = data.resumen?.pendientes || 0;
                document.getElementById('canceladas').textContent = data.resumen?.canceladas || 0;

                // Actualizar tabla
                const tbody = document.getElementById('reporteBody');
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
                            <td>${orden.tecnico || 'Sin asignar'}</td>
                            <td><span class="badge bg-${prioridadClass}">${orden.prioridad}</span></td>
                            <td><span class="badge bg-${estadoClass}">${orden.estado}</span></td>
                            <td>${orden.fecha_creacion}</td>
                            <td>${orden.fecha_cierre || '-'}</td>
                            <td>${orden.tiempo_invertido || '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });

                    actualizarPaginacion(data.total, data.paginas);
                    document.getElementById('totalRegistros').textContent = 
                        `Mostrando ${data.ordenes.length} de ${data.total} registros`;
                } else {
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center">No hay datos para mostrar</td></tr>`;
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
                cargarReporte(i);
            };
            li.appendChild(a);
            ul.appendChild(li);
        }
        
        div.appendChild(ul);
    }

    function exportarExcel() {
        const params = new URLSearchParams(new FormData(document.getElementById('filtrosForm')));
        window.location.href = `/produmar/reportes/ordenesExcel?${params.toString()}`;
    }

    function exportarPDF() {
        const params = new URLSearchParams(new FormData(document.getElementById('filtrosForm')));
        window.location.href = `/produmar/reportes/ordenesPDF?${params.toString()}`;
    }

    document.getElementById('filtrosForm').addEventListener('submit', function(e) {
        e.preventDefault();
        cargarReporte(1);
    });

    document.getElementById('filtrosForm').addEventListener('reset', function(e) {
        e.preventDefault();
        this.querySelectorAll('input, select').forEach(el => el.value = '');
        cargarReporte(1);
    });

    document.addEventListener('DOMContentLoaded', function() {
        cargarReporte(1);
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>