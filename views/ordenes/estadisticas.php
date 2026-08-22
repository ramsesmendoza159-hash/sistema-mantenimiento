<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Estadísticas de Órdenes";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Estadísticas de Órdenes</h1>
                <a href="/produmar/ordenes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Filtro de período -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="periodoForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Actualizar</button>
                            <button type="reset" class="btn btn-secondary">Hoy</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cards de resumen -->
            <div class="row" id="cardsResumen">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Órdenes</h5>
                            <p class="card-text display-6" id="total">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Completadas</h5>
                            <p class="card-text display-6" id="completadas">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">En Progreso</h5>
                            <p class="card-text display-6" id="en_progreso">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">Pendientes</h5>
                            <p class="card-text display-6" id="pendientes">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Órdenes por Estado</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="estadoChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Órdenes por Prioridad</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="prioridadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Órdenes por Área</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="areaChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Evolución Mensual</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="mensualChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let estadoChart, prioridadChart, areaChart, mensualChart;

    function cargarEstadisticas(fechaInicio = null, fechaFin = null) {
        let url = '/produmar/ordenes/estadisticasData';
        const params = new URLSearchParams();
        if (fechaInicio) params.append('fecha_inicio', fechaInicio);
        if (fechaFin) params.append('fecha_fin', fechaFin);
        if (params.toString()) url += '?' + params.toString();

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Actualizar cards
                document.getElementById('total').textContent = data.total || 0;
                document.getElementById('completadas').textContent = data.estados?.completada || 0;
                document.getElementById('en_progreso').textContent = data.estados?.en_progreso || 0;
                document.getElementById('pendientes').textContent = data.estados?.pendiente || 0;

                // Gráfico de estados
                if (data.estados) {
                    const ctxEstado = document.getElementById('estadoChart').getContext('2d');
                    const labels = Object.keys(data.estados);
                    const valores = Object.values(data.estados);
                    const colores = {
                        'pendiente': '#ffc107',
                        'en_progreso': '#0dcaf0',
                        'completada': '#198754',
                        'cancelada': '#dc3545'
                    };
                    
                    if (estadoChart) estadoChart.destroy();
                    estadoChart = new Chart(ctxEstado, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: valores,
                                backgroundColor: labels.map(l => colores[l] || '#6c757d'),
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }

                // Gráfico de prioridades
                if (data.prioridades) {
                    const ctxPrioridad = document.getElementById('prioridadChart').getContext('2d');
                    const labels = Object.keys(data.prioridades);
                    const valores = Object.values(data.prioridades);
                    const colores = {
                        'baja': '#0dcaf0',
                        'media': '#ffc107',
                        'alta': '#fd7e14',
                        'urgente': '#dc3545'
                    };
                    
                    if (prioridadChart) prioridadChart.destroy();
                    prioridadChart = new Chart(ctxPrioridad, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: valores,
                                backgroundColor: labels.map(l => colores[l] || '#6c757d'),
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }

                // Gráfico de áreas (top 5)
                if (data.areas) {
                    const ctxArea = document.getElementById('areaChart').getContext('2d');
                    const areasData = data.areas.slice(0, 5);
                    const labels = areasData.map(a => a.area);
                    const valores = areasData.map(a => a.total);
                    
                    if (areaChart) areaChart.destroy();
                    areaChart = new Chart(ctxArea, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Órdenes por Área',
                                data: valores,
                                backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545'],
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
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
                }

                // Gráfico mensual
                if (data.mensual) {
                    const ctxMensual = document.getElementById('mensualChart').getContext('2d');
                    const labels = data.mensual.map(m => m.mes);
                    const valores = data.mensual.map(m => m.total);
                    
                    if (mensualChart) mensualChart.destroy();
                    mensualChart = new Chart(ctxMensual, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Órdenes por Mes',
                                data: valores,
                                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                                borderColor: '#0d6efd',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }]
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
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Event listeners
    document.getElementById('periodoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const inicio = document.getElementById('fecha_inicio').value;
        const fin = document.getElementById('fecha_fin').value;
        cargarEstadisticas(inicio || null, fin || null);
    });

    document.getElementById('periodoForm').addEventListener('reset', function(e) {
        e.preventDefault();
        const hoy = new Date().toISOString().split('T')[0];
        document.getElementById('fecha_inicio').value = hoy;
        document.getElementById('fecha_fin').value = hoy;
        cargarEstadisticas(hoy, hoy);
    });

    // Inicializar con hoy
    document.addEventListener('DOMContentLoaded', function() {
        const hoy = new Date().toISOString().split('T')[0];
        document.getElementById('fecha_inicio').value = hoy;
        document.getElementById('fecha_fin').value = hoy;
        cargarEstadisticas(hoy, hoy);
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>