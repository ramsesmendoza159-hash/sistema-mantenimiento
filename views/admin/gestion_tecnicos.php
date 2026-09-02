<?php
// views/admin/gestion_tecnicos.php
// Gestión de Técnicos - VERSIÓN CORREGIDA

// ✅ Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

// ✅ Verificar sesión
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit;
}

// ✅ Asegurar que las variables existan
$tecnicos = $tecnicos ?? [];
$estadisticas = $estadisticas ?? ['total' => 0, 'activos' => 0, 'inactivos' => 0];

$titulo = "Gestión de Técnicos";
$seccion = "tecnicos";
include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-users-cog text-primary me-2"></i>Gestión de Técnicos
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Administra los técnicos del sistema
            </p>
        </div>
        <a href="/proyecto/tecnicos/crear" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Técnico
        </a>
    </div>

    <!-- ✅ Mensajes -->
    <?php if (isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Tarjetas de Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Total Técnicos</div>
                        <div class="stat-number fw-bold"><?php echo number_format($estadisticas['total'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(13,110,253,0.1);color:#0d6efd;">
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Activos</div>
                        <div class="stat-number fw-bold"><?php echo number_format($estadisticas['activos'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(25,135,84,0.1);color:#198754;">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Inactivos</div>
                        <div class="stat-number fw-bold"><?php echo number_format($estadisticas['inactivos'] ?? 0); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(220,53,69,0.1);color:#dc3545;">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Tabla -->
    <div class="card border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tecnicos)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay técnicos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tecnicos as $tecnico): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm" style="width:36px;height:36px;border-radius:50%;background:#667eea;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:0.85rem;">
                                                <?php echo strtoupper(substr($tecnico['nombre'] ?? 'T', 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($tecnico['nombre']); ?></div>
                                                <small class="text-muted">ID: <?php echo $tecnico['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($tecnico['email']); ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($tecnico['email']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($tecnico['telefono'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?php echo htmlspecialchars($tecnico['especialidad'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($tecnico['estado'] ?? 'activo') === 'activo'): ?>
                                            <span class="badge-status bg-success bg-opacity-10 text-success">
                                                <i class="fas fa-circle me-1" style="font-size:6px;"></i> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status bg-danger bg-opacity-10 text-danger">
                                                <i class="fas fa-circle me-1" style="font-size:6px;"></i> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="/proyecto/tecnicos/editar/<?php echo $tecnico['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="/proyecto/tecnicos/eliminar/<?php echo $tecnico['id']; ?>" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este técnico?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCSRFToken(); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-muted small">
                <i class="fas fa-list me-1"></i> Mostrando <?= count($tecnicos) ?> técnico(s)
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
// Auto-ocultar alertas después de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>