<?php
// views/admin/dashboard.php
// Dashboard de administrador - VERSIÓN COMPLETA CON ESTILO MODERNO

// Verificar sesión
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit;
}

$seccion = 'dashboard';
$titulo = 'Dashboard - Administrador';

// ==========================================
// IMPORTAR MODELOS
// ==========================================
require_once __DIR__ . '/../../model/OrdenTrabajo.php';
require_once __DIR__ . '/../../model/Tecnico.php';
require_once __DIR__ . '/../../model/Supervisor.php';
require_once __DIR__ . '/../../model/PlantasModel.php';
require_once __DIR__ . '/../../config/database.php';

$ordenModel = new OrdenTrabajo();
$tecnicoModel = new Tecnico();
$supervisorModel = new Supervisor();
$plantasModel = new PlantasModel();

// ==========================================
// ESTADÍSTICAS
// ==========================================
$estadisticas_ordenes = $ordenModel->obtenerEstadisticas();
$estadisticas_tecnicos = $tecnicoModel->obtenerEstadisticas();
$estadisticas_supervisores = $supervisorModel->obtenerEstadisticas();
$total_gastado = $ordenModel->obtenerTotalGastado();
$total_plantas = $plantasModel->obtenerTotal();

// Estadísticas de usuarios
try {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos
            FROM usuarios";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $estadisticas_usuarios = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'activos' => 0, 'inactivos' => 0];
} catch (Exception $e) {
    error_log("Error al obtener estadísticas de usuarios: " . $e->getMessage());
    $estadisticas_usuarios = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
}

// Órdenes recientes
$ordenes_recientes = $ordenModel->obtenerTodos([], 5, 0);

// ==========================================
// INCLUIR HEADER
// ==========================================
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Top Bar con saludo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">👋 ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>!</h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('l, d \d\e F \d\e Y'); ?>
                <span class="mx-2">|</span>
                <i class="fas fa-clock me-1"></i> <?php echo date('H:i'); ?>
            </p>
        </div>
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary p-2">
                <i class="fas fa-user-shield me-1"></i> Administrador
            </span>
        </div>
    </div>

    <!-- ✅ Tarjetas de Estadísticas - MODERNAS -->
    <div class="row g-3 mb-4">

        <!-- 1. Total Órdenes -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-modern border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-modern" style="background: rgba(102, 126, 234, 0.15); color: #667eea;">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                    <div>
                        <span class="stat-label-modern">Total Órdenes</span>
                        <span class="stat-number-modern"><?php echo number_format($estadisticas_ordenes['total'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-footer-modern mt-2">
                    <span class="badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock me-1"></i> <?php echo $estadisticas_ordenes['pendientes'] ?? 0; ?> pend.
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle me-1"></i> <?php echo $estadisticas_ordenes['cerradas'] ?? 0; ?> cerr.
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Total Gastado -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-modern border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-modern" style="background: rgba(46, 213, 115, 0.15); color: #2ed573;">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <div>
                        <span class="stat-label-modern">Total Gastado</span>
                        <span class="stat-number-modern">S/ <?php echo number_format($total_gastado ?? 0, 2); ?></span>
                    </div>
                </div>
                <div class="stat-footer-modern mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> Costo total
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Técnicos -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-modern border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-modern" style="background: rgba(255, 107, 107, 0.15); color: #ff6b6b;">
                        <i class="fas fa-users-cog fa-2x"></i>
                    </div>
                    <div>
                        <span class="stat-label-modern">Técnicos</span>
                        <span class="stat-number-modern"><?php echo number_format($estadisticas_tecnicos['total'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-footer-modern mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> <?php echo $estadisticas_tecnicos['activos'] ?? 0; ?> activos
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> <?php echo $estadisticas_tecnicos['inactivos'] ?? 0; ?> inact.
                    </span>
                </div>
            </div>
        </div>

        <!-- 4. Supervisores -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-modern border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-modern" style="background: rgba(72, 126, 176, 0.15); color: #487eb0;">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                    <div>
                        <span class="stat-label-modern">Supervisores</span>
                        <span class="stat-number-modern"><?php echo number_format($estadisticas_supervisores['total'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-footer-modern mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> <?php echo $estadisticas_supervisores['activos'] ?? 0; ?> activos
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> <?php echo $estadisticas_supervisores['inactivos'] ?? 0; ?> inact.
                    </span>
                </div>
            </div>
        </div>

        <!-- 5. Usuarios -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-modern border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-modern" style="background: rgba(253, 203, 110, 0.15); color: #fdcb6e;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div>
                        <span class="stat-label-modern">Usuarios</span>
                        <span class="stat-number-modern"><?php echo number_format($estadisticas_usuarios['total'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-footer-modern mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> <?php echo $estadisticas_usuarios['activos'] ?? 0; ?> activos
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> <?php echo $estadisticas_usuarios['inactivos'] ?? 0; ?> inact.
                    </span>
                </div>
            </div>
        </div>

        <!-- 6. Plantas -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stat-card-modern border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-modern" style="background: rgba(161, 140, 209, 0.15); color: #a18cd1;">
                        <i class="fas fa-industry fa-2x"></i>
                    </div>
                    <div>
                        <span class="stat-label-modern">Plantas</span>
                        <span class="stat-number-modern"><?php echo number_format($total_plantas ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-footer-modern mt-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-circle me-1" style="font-size:6px;"></i> Total plantas
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- ✅ Gráficos -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-doughnut text-primary me-2"></i> Distribución de Órdenes
                    </h5>
                </div>
                <div class="card-body" style="height: 280px;">
                    <canvas id="ordenesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-bar text-success me-2"></i> Resumen por Tipo
                    </h5>
                </div>
                <div class="card-body" style="height: 280px;">
                    <canvas id="resumenChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Accesos Rápidos y Últimas Órdenes -->
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-rocket text-warning me-2"></i> Accesos Rápidos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="/proyecto/ordenes/crear" class="text-decoration-none">
                                <div class="quick-action p-3 text-center rounded-3 border">
                                    <i class="fas fa-plus-circle fa-2x text-primary"></i>
                                    <h6 class="mt-2 mb-0 small">Nueva Orden</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="/proyecto/tecnicos/crear" class="text-decoration-none">
                                <div class="quick-action p-3 text-center rounded-3 border">
                                    <i class="fas fa-user-plus fa-2x text-success"></i>
                                    <h6 class="mt-2 mb-0 small">Nuevo Técnico</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="/proyecto/supervisores/crear" class="text-decoration-none">
                                <div class="quick-action p-3 text-center rounded-3 border">
                                    <i class="fas fa-user-tie fa-2x text-info"></i>
                                    <h6 class="mt-2 mb-0 small">Nuevo Supervisor</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="/proyecto/reportes/financieros" class="text-decoration-none">
                                <div class="quick-action p-3 text-center rounded-3 border">
                                    <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                                    <h6 class="mt-2 mb-0 small">Financieros</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-clock text-info me-2"></i> Últimas Órdenes
                    </h5>
                    <a href="/proyecto/ordenes" class="btn btn-sm btn-primary">Ver todas <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="card-body">
                    <?php if (count($ordenes_recientes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Descripción</th>
                                    <th>Técnico</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ordenes_recientes as $orden): ?>
                                <tr>
                                    <td><span class="fw-semibold">#<?php echo $orden['id']; ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($orden['descripcion'] ?? '', 0, 35)); ?>...</td>
                                    <td><?php echo htmlspecialchars($orden['tecnico_nombre'] ?? 'Sin asignar'); ?></td>
                                    <td>
                                        <?php
                                        $estado = $orden['status'] ?? $orden['estado'] ?? 'PENDIENTE';
                                        $badge = match(strtoupper($estado)) {
                                            'PENDIENTE' => 'warning',
                                            'EN_PROCESO' => 'info',
                                            'EJECUTADA' => 'primary',
                                            'CERRADA' => 'success',
                                            'CANCELADA' => 'danger',
                                            'APROBADA' => 'success',
                                            'RECHAZADA' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge-status bg-<?php echo $badge; ?> bg-opacity-10 text-<?php echo $badge; ?>">
                                            <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                            <?php echo str_replace('_', ' ', $estado); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $prioridad = $orden['prioridad'] ?? 'Media';
                                        $color = match($prioridad) {
                                            'Urgente' => 'danger',
                                            'Alta' => 'warning',
                                            'Media' => 'info',
                                            'Baja' => 'success',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?>">
                                            <?php echo $prioridad; ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('d/m/Y', strtotime($orden['fecha_creacion'] ?? 'now')); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No hay órdenes recientes</h6>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ✅ Estilos Modernos -->
<style>
.stat-card-modern {
    background: #fff;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}
.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}
.stat-icon-modern {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-label-modern {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    font-weight: 600;
}
.stat-number-modern {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a1a2e;
    display: block;
    line-height: 1.2;
}
.stat-footer-modern .badge {
    font-size: 0.65rem;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
}
.quick-action {
    transition: all 0.3s ease;
    background: #fff;
}
.quick-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
.badge-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<!-- ✅ Gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de distribución de órdenes
    <?php if (isset($estadisticas_ordenes)): ?>
    const ctxOrdenes = document.getElementById('ordenesChart').getContext('2d');
    new Chart(ctxOrdenes, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Ejecutadas', 'Cerradas', 'Canceladas', 'Aprobadas', 'Rechazadas'],
            datasets: [{
                data: [
                    <?php echo $estadisticas_ordenes['pendientes'] ?? 0; ?>,
                    <?php echo $estadisticas_ordenes['en_proceso'] ?? 0; ?>,
                    <?php echo $estadisticas_ordenes['ejecutadas'] ?? 0; ?>,
                    <?php echo $estadisticas_ordenes['cerradas'] ?? 0; ?>,
                    <?php echo $estadisticas_ordenes['canceladas'] ?? 0; ?>,
                    <?php echo $estadisticas_ordenes['aprobadas'] ?? 0; ?>,
                    <?php echo $estadisticas_ordenes['rechazadas'] ?? 0; ?>
                ],
                backgroundColor: ['#ffc107', '#17a2b8', '#0d6efd', '#28a745', '#dc3545', '#198754', '#6c757d'],
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
                    labels: { font: { size: 10 }, padding: 10 }
                }
            }
        }
    });
    <?php endif; ?>

    // Gráfico de resumen por tipo
    const ctxResumen = document.getElementById('resumenChart').getContext('2d');
    new Chart(ctxResumen, {
        type: 'bar',
        data: {
            labels: ['Técnicos', 'Supervisores', 'Usuarios', 'Plantas'],
            datasets: [
                {
                    label: 'Total',
                    data: [
                        <?php echo $estadisticas_tecnicos['total'] ?? 0; ?>,
                        <?php echo $estadisticas_supervisores['total'] ?? 0; ?>,
                        <?php echo $estadisticas_usuarios['total'] ?? 0; ?>,
                        <?php echo $total_plantas ?? 0; ?>
                    ],
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Activos',
                    data: [
                        <?php echo $estadisticas_tecnicos['activos'] ?? 0; ?>,
                        <?php echo $estadisticas_supervisores['activos'] ?? 0; ?>,
                        <?php echo $estadisticas_usuarios['activos'] ?? 0; ?>,
                        0
                    ],
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 11 } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>