<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Reporte de Técnicos";
$seccion = "reportes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Reporte de Rendimiento de Técnicos</h1>
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
                            <label for="tecnico_id" class="form-label">Técnico</label>
                            <select class="form-select" id="tecnico_id" name="tecnico_id">
                                <option value="">Todos</option>
                                <!-- Cargado por AJAX -->
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Generar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de técnicos -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="reporteTabla">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Técnico</th>
                                    <th>Total Órdenes</th>
                                    <th>Completadas</th>
                                    <th>Pendientes</th>
                                    <th>Tasa Éxito</th>
                                    <th>Tiempo Promedio</th>
                                    <th>Eficiencia</th>
                                </tr>
                            </thead>
                            <tbody id="reporteBody">
                                <!-- Cargado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div id="paginacion"></div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Órdenes por Técnico</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="tecnicosChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Eficiencia de Técnicos</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="eficienciaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let tecnicosChart, eficienciaChart;

    function cargarTecnicosList() {
        fetch('/proyecto/reportes/tecnicosList')
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

    function cargarReporte() {
        const formData = new FormData(document.getElementById('filtrosForm'));
        const params = new URLSearchParams(formData);

        fetch(`/proyecto/reportes/tecnicosData?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('reporteBody');
                tbody.innerHTML = '';

                if (data.tecnicos && data.tecnicos.length > 0) {
                    data.tecnicos.forEach((tecnico, index) => {
                        const tr = document.createElement('tr');
                        const tasaExito = tecnico.total > 0 ? 
                            Math.round((tecnico.completadas / tecnico.total) * 100) : 0;
                        const eficiencia = tecnico.promedio_horas ? 
                            Math.round((1 / tecnico.promedio_horas) * 100) : 0;

                        tr.innerHTML = `
                            <td>${index + 1}</td>
                            <td><strong>${tecnico.nombre}</strong></td>
                            <td>${tecnico.total}</td>
                            <td class="text-success">${tecnico.completadas}</td>
                            <td class="text-warning">${tecnico.pendientes}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-${tasaExito >= 80 ? 'success' : tasaExito >= 50 ? 'warning' : 'danger'}" 
                                         style="width: ${tasaExito}%">
                                        ${tasaExito}%
                                    </div>
                                </div>
                            </td>
                            <td>${tecnico.promedio_horas ? tecnico.promedio_horas.toFixed(1) : 'N/A'} h</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-${eficiencia >= 70 ? 'success' : eficiencia >= 40 ? 'warning' : 'danger'}" 
                                         style="width: ${Math.min(eficiencia, 100)}%">
                                        ${eficiencia}%
                                    </div>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Gráficos
                    const ctxTecnicos = document.getElementById('tecnicosChart').getContext('2d');
                    const ctxEficiencia = document.getElementById('eficienciaChart').getContext('2d');

                    const labels = data.tecnicos.map(t => t.nombre);
                    const totales = data.tecnicos.map(t => t.total);
                    const completadas = data.tecnicos.map(t => t.completadas);
                    const eficiencias = data.tecnicos.map(t => 
                        t.total > 0 ? Math.round((t.completadas / t.total) * 100) : 0
                    );

                    if (tecnicosChart) tecnicosChart.destroy();
                    tecnicosChart = new Chart(ctxTecnicos, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Total',
                                    data: totales,
                                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                                    borderColor: '#0d6efd',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Completadas',
                                    data: completadas,
                                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                                    borderColor: '#198754',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });

                    if (eficienciaChart) eficienciaChart.destroy();
                    eficienciaChart = new Chart(ctxEficiencia, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: eficiencias,
                                backgroundColor: [
                                    '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545',
                                    '#fd7e14', '#ffc107', '#198754', '#0dcaf0', '#6c757d'
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.label}: ${context.raw}% de éxito`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center">No hay datos para mostrar</td></tr>`;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function exportarExcel() {
        const params = new URLSearchParams(new FormData(document.getElementById('filtrosForm')));
        window.location.href = `/proyecto/reportes/tecnicosExcel?${params.toString()}`;
    }

    function exportarPDF() {
        const params = new URLSearchParams(new FormData(document.getElementById('filtrosForm')));
        window.location.href = `/proyecto/reportes/tecnicosPDF?${params.toString()}`;
    }

    document.getElementById('filtrosForm').addEventListener('submit', function(e) {
        e.preventDefault();
        cargarReporte();
    });

    document.getElementById('filtrosForm').addEventListener('reset', function(e) {
        e.preventDefault();
        this.querySelectorAll('input, select').forEach(el => el.value = '');
        cargarReporte();
    });

    document.addEventListener('DOMContentLoaded', function() {
        cargarTecnicosList();
        cargarReporte();
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>