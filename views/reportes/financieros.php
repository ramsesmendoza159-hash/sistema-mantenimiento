<?php
// views/reportes/financieros.php
// Ubicación: C:\xampp\htdocs\proyecto\views\reportes\financieros.php

$titulo = "Reportes Financieros";
$seccion = "financieros";  // 👈 CAMBIADO de "reportes" a "financieros"
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-money-bill-wave"></i> Reportes Financieros</h1>
                <div>
                    <button onclick="exportarReporte('detallado')" class="btn btn-success me-2">
                        <i class="fas fa-file-excel"></i> Exportar Detallado
                    </button>
                    <button onclick="exportarReporte('resumen')" class="btn btn-primary">
                        <i class="fas fa-file-excel"></i> Exportar Resumen
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/proyecto/reportes/financieros" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                   value="<?php echo $fechaInicio ?? date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                   value="<?php echo $fechaFin ?? date('Y-m-t'); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Actualizar
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="/proyecto/reportes/financieros" class="btn btn-secondary w-100">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cards de resumen financiero -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-clipboard-list"></i> Total Órdenes</h6>
                            <p class="card-text display-6"><?php echo number_format($stats['total_ordenes'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-dollar-sign"></i> Costo Total</h6>
                            <p class="card-text display-6">S/ <?php echo number_format($stats['total_costos'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-tools"></i> Repuestos</h6>
                            <p class="card-text display-6">S/ <?php echo number_format($stats['total_repuestos'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-user-cog"></i> Mano de Obra</h6>
                            <p class="card-text display-6">S/ <?php echo number_format($stats['total_mano_obra'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segundo grupo de cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-chart-line"></i> Promedio por Orden</h6>
                            <p class="card-text display-6">S/ <?php echo number_format($stats['promedio_costo'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-clock"></i> Total Horas</h6>
                            <p class="card-text display-6"><?php echo number_format($stats['total_horas'] ?? 0, 1); ?> h</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-clock"></i> Promedio Horas</h6>
                            <p class="card-text display-6"><?php echo number_format($stats['promedio_horas'] ?? 0, 1); ?> h</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Costos por Planta</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="costosPlantaChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-doughnut"></i> Distribución de Costos</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="distribucionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Evolución Mensual</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="mensualChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-medal"></i> Top Repuestos</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="repuestosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de costos por técnico -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Costos por Técnico</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Técnico</th>
                                    <th>Órdenes</th>
                                    <th>Total Horas</th>
                                    <th>Prom. Horas</th>
                                    <th>Costo Mano Obra</th>
                                    <th>Costo Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($costos_por_tecnico)): ?>
                                    <?php foreach ($costos_por_tecnico as $tecnico): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($tecnico['tecnico']); ?></strong></td>
                                            <td><?php echo $tecnico['total_ordenes']; ?></td>
                                            <td><?php echo number_format($tecnico['total_horas'] ?? 0, 1); ?></td>
                                            <td><?php echo number_format($tecnico['promedio_horas'] ?? 0, 1); ?></td>
                                            <td>S/ <?php echo number_format($tecnico['total_mano_obra'] ?? 0, 2); ?></td>
                                            <td>S/ <?php echo number_format($tecnico['total_costos'] ?? 0, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay datos disponibles</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Costos por Planta
    <?php if (!empty($costos_por_planta)): ?>
    const ctxPlanta = document.getElementById('costosPlantaChart').getContext('2d');
    new Chart(ctxPlanta, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($costos_por_planta, 'nombre_planta')); ?>,
            datasets: [
                {
                    label: 'Costo Total',
                    data: <?php echo json_encode(array_column($costos_por_planta, 'total_costos')); ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: '#0d6efd',
                    borderWidth: 1
                },
                {
                    label: 'Repuestos',
                    data: <?php echo json_encode(array_column($costos_por_planta, 'total_repuestos')); ?>,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: '#ffc107',
                    borderWidth: 1
                },
                {
                    label: 'Mano de Obra',
                    data: <?php echo json_encode(array_column($costos_por_planta, 'total_mano_obra')); ?>,
                    backgroundColor: 'rgba(23, 162, 184, 0.7)',
                    borderColor: '#17a2b8',
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
                        callback: function(value) {
                            return 'S/ ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Distribución de Costos
    <?php if (isset($stats['total_costos']) && $stats['total_costos'] > 0): ?>
    const ctxDist = document.getElementById('distribucionChart').getContext('2d');
    new Chart(ctxDist, {
        type: 'doughnut',
        data: {
            labels: ['Repuestos', 'Mano de Obra'],
            datasets: [{
                data: [
                    <?php echo $stats['total_repuestos'] ?? 0; ?>,
                    <?php echo $stats['total_mano_obra'] ?? 0; ?>
                ],
                backgroundColor: ['#ffc107', '#17a2b8'],
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
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((context.raw / total) * 100).toFixed(1);
                            return 'S/ ' + context.raw.toFixed(2) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Evolución Mensual
    <?php if (!empty($costos_por_mes)): ?>
    const ctxMensual = document.getElementById('mensualChart').getContext('2d');
    new Chart(ctxMensual, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($costos_por_mes, 'mes')); ?>,
            datasets: [
                {
                    label: 'Costo Total',
                    data: <?php echo json_encode(array_column($costos_por_mes, 'total_costos')); ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Repuestos',
                    data: <?php echo json_encode(array_column($costos_por_mes, 'total_repuestos')); ?>,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Mano de Obra',
                    data: <?php echo json_encode(array_column($costos_por_mes, 'total_mano_obra')); ?>,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    fill: true,
                    tension: 0.3
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
                        callback: function(value) {
                            return 'S/ ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Top Repuestos
    <?php if (!empty($top_repuestos)): ?>
    const ctxRepuestos = document.getElementById('repuestosChart').getContext('2d');
    new Chart(ctxRepuestos, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($top_repuestos, 'nombre')); ?>,
            datasets: [
                {
                    label: 'Cantidad Utilizada',
                    data: <?php echo json_encode(array_column($top_repuestos, 'total_utilizados')); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    <?php endif; ?>

    function exportarReporte(tipo) {
        const params = new URLSearchParams(window.location.search);
        params.set('tipo', tipo);
        window.location.href = '/proyecto/reportes/financieros/exportar?' + params.toString();
    }
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>