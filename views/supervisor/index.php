<?php
// views/supervisor/index.php
// Panel de Supervisor - VERSIÓN COMPLETA

// ✅ Verificar que las variables existan
if (!isset($seccion)) {
    $seccion = 'supervisor';
}
if (!isset($titulo)) {
    $titulo = 'Panel de Supervisor';
}

include_once __DIR__ . '/../layouts/header.php';
// ❌ NO incluir sidebar aquí (ya está en header)
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-user-tie text-primary me-2"></i>Panel de Supervisor
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/proyecto/supervisor/ordenes" class="btn btn-primary">
                <i class="fas fa-list me-1"></i> Ver Órdenes
            </a>
            <a href="/proyecto/supervisor/supervisiones" class="btn btn-info">
                <i class="fas fa-clipboard-check me-1"></i> Supervisiones
            </a>
        </div>
    </div>

    <!-- ✅ Tarjetas de estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Total Órdenes</div>
                        <div class="stat-number fw-bold" id="total_ordenes">0</div>
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
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Pendientes Revisión</div>
                        <div class="stat-number fw-bold" id="pendientes_revision">0</div>
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
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Aprobadas</div>
                        <div class="stat-number fw-bold" id="aprobadas">0</div>
                    </div>
                    <div class="stat-icon" style="background:rgba(25,135,84,0.1);color:#198754;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Rechazadas</div>
                        <div class="stat-number fw-bold" id="rechazadas">0</div>
                    </div>
                    <div class="stat-icon" style="background:rgba(220,53,69,0.1);color:#dc3545;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Últimas órdenes para revisar -->
    <div class="card border-0">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-clock text-info me-2"></i> Órdenes Pendientes de Revisión
            </h5>
            <a href="/proyecto/supervisor/ordenes" class="btn btn-sm btn-primary">
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
                            <th>Técnico</th>
                            <th>Prioridad</th>
                            <th>Fecha Cierre</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ordenesBody">
                        <!-- Cargado por AJAX -->
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function cargarDashboard() {
        fetch('/proyecto/supervisor/dashboardData')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total_ordenes').textContent = data.total_ordenes || 0;
                document.getElementById('pendientes_revision').textContent = data.pendientes_revision || 0;
                document.getElementById('aprobadas').textContent = data.aprobadas || 0;
                document.getElementById('rechazadas').textContent = data.rechazadas || 0;

                const tbody = document.getElementById('ordenesBody');
                tbody.innerHTML = '';
                
                if (data.ordenes && data.ordenes.length > 0) {
                    data.ordenes.forEach(orden => {
                        const prioridadColor = {
                            'Baja': 'success',
                            'Media': 'info',
                            'Alta': 'warning',
                            'Urgente': 'danger'
                        }[orden.prioridad] || 'secondary';

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><span class="fw-semibold">#${orden.id}</span></td>
                            <td>${orden.titulo || 'Sin título'}</td>
                            <td>${orden.tecnico || 'Sin asignar'}</td>
                            <td>
                                <span class="badge bg-${prioridadColor} bg-opacity-10 text-${prioridadColor}">
                                    ${orden.prioridad || 'Media'}
                                </span>
                            </td>
                            <td><small>${orden.fecha_cierre || 'N/A'}</small></td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <a href="/proyecto/supervisor/revisar/${orden.id}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i> Revisar
                                    </a>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                        No hay órdenes pendientes de revisión
                    </td></tr>`;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    cargarDashboard();
    setInterval(cargarDashboard, 30000);
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>