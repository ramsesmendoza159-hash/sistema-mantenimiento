<?php
// views/ordenes/estadisticas.php
// Ubicación: C:\xampp\htdocs\proyecto\views\ordenes\estadisticas.php

// ✅ ELIMINAR session_start() - ya está iniciada en el router
// session_start(); // ❌ ELIMINAR ESTA LÍNEA

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
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
                <h1 class="h2"><i class="fas fa-chart-bar"></i> Estadísticas de Órdenes</h1>
                <div>
                    <a href="/proyecto/ordenes" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/proyecto/ordenes/estadisticas" class="row g-3 align-items-end">
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
                            <a href="/proyecto/ordenes/estadisticas" class="btn btn-secondary w-100">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-clipboard-list"></i> Total Órdenes</h6>
                            <p class="card-text display-6"><?php echo number_format($estadisticas['total'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-clock"></i> Pendientes</h6>
                            <p class="card-text display-6"><?php echo number_format($estadisticas['pendientes'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-spinner"></i> En Proceso</h6>
                            <p class="card-text display-6"><?php echo number_format($estadisticas['en_proceso'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-check-circle"></i> Cerradas</h6>
                            <p class="card-text display-6"><?php echo number_format($estadisticas['cerradas'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-doughnut"></i> Distribución por Estado</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="estadoChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Órdenes por Prioridad</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="prioridadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Evolución Mensual</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="evolucionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de estadísticas por técnico -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Rendimiento por Técnico</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Técnico</th>
                                    <th>Órdenes</th>
                                    <th>Completadas</th>
                                    <th>Pendientes</th>
                                    <th>Eficiencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rendimiento_tecnicos)): ?>
                                    <?php foreach ($rendimiento_tecnicos as $tecnico): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($tecnico['nombre'] ?? 'N/A'); ?></strong></td>
                                            <td><?php echo $tecnico['total'] ?? 0; ?></td>
                                            <td><?php echo $tecnico['completadas'] ?? 0; ?></td>
                                            <td><?php echo ($tecnico['total'] ?? 0) - ($tecnico['completadas'] ?? 0); ?></td>
                                            <td>
                                                <?php 
                                                $eficiencia = ($tecnico['total'] ?? 0) > 0 
                                                    ? round((($tecnico['completadas'] ?? 0) / ($tecnico['total'] ?? 0)) * 100, 1) 
                                                    : 0;
                                                ?>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?php echo $eficiencia; ?>%;" 
                                                         aria-valuenow="<?php echo $eficiencia; ?>" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $eficiencia; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No hay datos disponibles</td>
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
    // Gráfico de Distribución por Estado
    <?php if (isset($estadisticas)): ?>
    const ctxEstado = document.getElementById('estadoChart').getContext('2d');
    new Chart(ctxEstado, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Cerradas', 'Canceladas', 'Aprobadas', 'Rechazadas'],
            datasets: [{
                data: [
                    <?php echo $estadisticas['pendientes'] ?? 0; ?>,
                    <?php echo $estadisticas['en_proceso'] ?? 0; ?>,
                    <?php echo $estadisticas['cerradas'] ?? 0; ?>,
                    <?php echo $estadisticas['canceladas'] ?? 0; ?>,
                    <?php echo $estadisticas['aprobadas'] ?? 0; ?>,
                    <?php echo $estadisticas['rechazadas'] ?? 0; ?>
                ],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545', '#0d6efd', '#6c757d'],
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

    // Gráfico de Prioridad
    const ctxPrioridad = document.getElementById('prioridadChart').getContext('2d');
    new Chart(ctxPrioridad, {
        type: 'bar',
        data: {
            labels: ['Alta', 'Media', 'Baja'],
            datasets: [{
                label: 'Órdenes por Prioridad',
                data: [
                    <?php echo $prioridades['alta'] ?? 0; ?>,
                    <?php echo $prioridades['media'] ?? 0; ?>,
                    <?php echo $prioridades['baja'] ?? 0; ?>
                ],
                backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                borderColor: ['#dc3545', '#ffc107', '#28a745'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
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

    // Gráfico de Evolución Mensual
    <?php if (!empty($evolucion_mensual)): ?>
    const ctxEvolucion = document.getElementById('evolucionChart').getContext('2d');
    new Chart(ctxEvolucion, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($evolucion_mensual, 'mes')); ?>,
            datasets: [{
                label: 'Órdenes por Mes',
                data: <?php echo json_encode(array_column($evolucion_mensual, 'total')); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
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
    <?php endif; ?>
    <?php endif; ?>
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>