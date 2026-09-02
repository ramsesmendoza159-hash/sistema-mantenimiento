<?php
// views/tecnico/index.php
// Panel de Técnico - VERSIÓN CORREGIDA

// ✅ Verificar que las variables existan
if (!isset($seccion)) {
    $seccion = 'tecnico';
}
if (!isset($titulo)) {
    $titulo = 'Panel de Técnico';
}
if (!isset($estadisticas)) {
    $estadisticas = ['total' => 0, 'pendientes' => 0, 'en_progreso' => 0, 'completadas' => 0];
}
if (!isset($ordenes_recientes)) {
    $ordenes_recientes = [];
}

include_once __DIR__ . '/../layouts/header.php';
// ❌ NO incluir sidebar aquí (ya está en header)
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-user-cog text-primary me-2"></i>Panel de Técnico
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>
            </p>
        </div>
        <a href="/proyecto/tecnico/mis_ordenes" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> Mis Órdenes
        </a>
    </div>

    <!-- ✅ Tarjetas de estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Total Asignadas</div>
                        <div class="stat-number fw-bold" id="total"><?php echo $estadisticas['total'] ?? 0; ?></div>
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
                        <div class="stat-number fw-bold" id="pendientes"><?php echo $estadisticas['pendientes'] ?? 0; ?></div>
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
                        <div class="stat-label text-muted text-uppercase small fw-semibold">En Progreso</div>
                        <div class="stat-number fw-bold" id="en_progreso"><?php echo $estadisticas['en_progreso'] ?? 0; ?></div>
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
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Completadas</div>
                        <div class="stat-number fw-bold" id="completadas"><?php echo $estadisticas['completadas'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(25,135,84,0.1);color:#198754;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Últimas órdenes asignadas -->
    <div class="card border-0">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-clock text-info me-2"></i> Últimas Órdenes Asignadas
            </h5>
            <a href="/proyecto/tecnico/mis_ordenes" class="btn btn-sm btn-primary">
                Ver todas <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ordenesBody">
                        <?php if (empty($ordenes_recientes)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay órdenes asignadas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ordenes_recientes as $orden): ?>
                                <tr>
                                    <td><span class="fw-semibold">#<?php echo $orden['id']; ?></span></td>
                                    <td><?php echo htmlspecialchars($orden['titulo'] ?? 'Sin título'); ?></td>
                                    <td>
                                        <?php
                                        $prioridadColor = match($orden['prioridad'] ?? 'Media') {
                                            'Baja' => 'success',
                                            'Media' => 'info',
                                            'Alta' => 'warning',
                                            'Urgente' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $prioridadColor; ?> bg-opacity-10 text-<?php echo $prioridadColor; ?>">
                                            <?php echo $orden['prioridad'] ?? 'Media'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $estadoColor = match($orden['status'] ?? 'PENDIENTE') {
                                            'PENDIENTE' => 'warning',
                                            'EN_PROCESO' => 'info',
                                            'EJECUTADA' => 'primary',
                                            'CERRADA' => 'success',
                                            'APROBADA' => 'success',
                                            'CANCELADA' => 'danger',
                                            'RECHAZADA' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge-status bg-<?php echo $estadoColor; ?> bg-opacity-10 text-<?php echo $estadoColor; ?>">
                                            <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                            <?php echo $orden['status'] ?? 'PENDIENTE'; ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('d/m/Y', strtotime($orden['fecha_creacion'] ?? 'now')); ?></small></td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="/proyecto/tecnico/detalle_orden/<?php echo $orden['id']; ?>" class="btn btn-sm btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!in_array($orden['status'] ?? '', ['CERRADA', 'APROBADA', 'CANCELADA'])): ?>
                                                <a href="/proyecto/tecnico/cerrar_orden/<?php echo $orden['id']; ?>" class="btn btn-sm btn-success" title="Cerrar">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar datos del dashboard vía AJAX
    fetch('/proyecto/tecnico/dashboardData')
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('total').textContent = data.total || 0;
                document.getElementById('pendientes').textContent = data.pendientes || 0;
                document.getElementById('en_progreso').textContent = data.en_progreso || 0;
                document.getElementById('completadas').textContent = data.completadas || 0;
            }
        })
        .catch(error => console.error('Error:', error));
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>