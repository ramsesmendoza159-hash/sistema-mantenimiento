<?php
// views/ordenes/estadisticas.php
// Estadísticas de Órdenes - VERSIÓN CORREGIDA

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Estadísticas de Órdenes";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-chart-bar text-primary me-2"></i>Estadísticas de Órdenes
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Análisis detallado de las órdenes de trabajo
            </p>
        </div>
        <a href="/proyecto/ordenes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- ✅ Filtros -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="/proyecto/ordenes/estadisticas" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label fw-semibold small">Fecha Inicio</label>
                    <input type="date" class="form-control form-control-sm" id="fecha_inicio" name="fecha_inicio" 
                           value="<?php echo $fechaInicio ?? date('Y-m-01'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label fw-semibold small">Fecha Fin</label>
                    <input type="date" class="form-control form-control-sm" id="fecha_fin" name="fecha_fin" 
                           value="<?php echo $fechaFin ?? date('Y-m-t'); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i> Actualizar
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="/proyecto/ordenes/estadisticas" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Tarjetas de estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Total Órdenes</div>
                        <div class="stat-number fw-bold"><?php echo number_format($estadisticas['total'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(13,110,253,0.1);color:#0d6efd;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Pendientes</div>
                        <div class="stat-number fw-bold"><?php echo number_format($estadisticas['pendientes'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(255,193,7,0.1);color:#ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">En Proceso</div>
                        <div class="stat-number fw-bold"><?php echo number_format($estadisticas['en_proceso'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(13,202,240,0.1);color:#0dcaf0;">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Cerradas</div>
                        <div class="stat-number fw-bold"><?php echo number_format($estadisticas['cerradas'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(25,135,84,0.1);color:#198754;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Gráficos -->
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-doughnut text-primary me-2"></i> Distribución por Estado
                    </h5>
                </div>
                <div class="card-body" style="height:300px;">
                    <canvas id="estadoChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-bar text-primary me-2"></i> Órdenes por Prioridad
                    </h5>
                </div>
                <div class="card-body" style="height:300px;">
                    <canvas id="prioridadChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Evolución Mensual -->
    <div class="card border-0 mt-4">
        <div class="card-header bg-transparent border-0 pt-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-chart-line text-primary me-2"></i> Evolución Mensual
            </h5>
        </div>
        <div class="card-body" style="height:300px;">
            <canvas id="evolucionChart"></canvas>
        </div>
    </div>

    <!-- ✅ Tabla de rendimiento por técnico -->
    <div class="card border-0 mt-4">
        <div class="card-header bg-transparent border-0 pt-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-users text-primary me-2"></i> Rendimiento por Técnico
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Técnico</th>
                            <th class="text-center">Órdenes</th>
                            <th class="text-center">Completadas</th>
                            <th class="text-center">Pendientes</th>
                            <th class="text-center">Eficiencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rendimiento_tecnicos)): ?>
                            <?php foreach ($rendimiento_tecnicos as $tecnico): ?>
                                <tr>
                                    <td><span class="fw-semibold"><?php echo htmlspecialchars($tecnico['nombre'] ?? 'N/A'); ?></span></td>
                                    <td class="text-center"><?php echo $tecnico['total'] ?? 0; ?></td>
                                    <td class="text-center"><?php echo $tecnico['completadas'] ?? 0; ?></td>
                                    <td class="text-center"><?php echo ($tecnico['total'] ?? 0) - ($tecnico['completadas'] ?? 0); ?></td>
                                    <td>
                                        <?php 
                                        $eficiencia = ($tecnico['total'] ?? 0) > 0 
                                            ? round((($tecnico['completadas'] ?? 0) / ($tecnico['total'] ?? 0)) * 100, 1) 
                                            : 0;
                                        ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px;">
                                                <div class="progress-bar bg-<?php echo $eficiencia >= 80 ? 'success' : ($eficiencia >= 50 ? 'warning' : 'danger'); ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $eficiencia; ?>%;" 
                                                     aria-valuenow="<?php echo $eficiencia; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="fw-semibold small"><?php echo $eficiencia; ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay datos disponibles
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ✅ Estilos -->
<style>
.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.stat-card .stat-number {
    font-size: 2rem;
    margin: 4px 0 2px;
    color: #1a1a2e;
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.progress {
    border-radius: 20px;
    background-color: #e9ecef;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 11 },
                        padding: 15
                    }
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
                backgroundColor: ['rgba(220,53,69,0.7)', 'rgba(255,193,7,0.7)', 'rgba(40,167,69,0.7)'],
                borderColor: ['#dc3545', '#ffc107', '#28a745'],
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
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
                tension: 0.3,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    <?php endif; ?>
    <?php endif; ?>
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>