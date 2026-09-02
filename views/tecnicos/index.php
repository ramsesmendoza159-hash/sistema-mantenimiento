<?php
// views/tecnicos/index.php
// VERSIÓN CORREGIDA CON ASTEROADMIN

// Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit;
}

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

    <!-- ✅ Tarjetas de Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Total Técnicos</div>
                        <div class="stat-number fw-bold"><?= number_format($estadisticas['total'] ?? 0) ?></div>
                    </div>
                    <div class="stat-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-user-check me-1"></i> <?= $estadisticas['activos'] ?? 0 ?> Activos
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-user-slash me-1"></i> <?= $estadisticas['inactivos'] ?? 0 ?> Inactivos
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Especialidades</div>
                        <div class="stat-number fw-bold"><?= count($especialidades ?? []) ?></div>
                    </div>
                    <div class="stat-icon" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Tarifa Promedio</div>
                        <div class="stat-number fw-bold">S/ <?= number_format($tarifa_promedio ?? 0, 2) ?></div>
                    </div>
                    <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Órdenes Asignadas</div>
                        <div class="stat-number fw-bold"><?= $total_ordenes ?? 0 ?></div>
                    </div>
                    <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Filtros -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Buscar</label>
                    <input type="text" name="buscar" class="form-control form-control-sm" 
                           placeholder="Buscar técnico..." value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Especialidad</label>
                    <select name="especialidad" class="form-select form-select-sm">
                        <option value="">Todas las especialidades</option>
                        <?php foreach ($especialidades ?? [] as $esp): ?>
                            <option value="<?= htmlspecialchars($esp) ?>" <?= ($_GET['especialidad'] ?? '') === $esp ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esp) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Tabla de Técnicos -->
    <div class="card border-0">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-list text-primary me-2"></i> Lista de Técnicos
                <span class="badge bg-primary ms-2"><?= count($tecnicos ?? []) ?></span>
            </h5>
            <a href="/proyecto/tecnicos/crear" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle me-1"></i> Nuevo Técnico
            </a>
        </div>
        <div class="card-body">
            <?php if (!empty($tecnicos)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Técnico</th>
                            <th>Email</th>
                            <th>Especialidad</th>
                            <th>Tarifa</th>
                            <th>Estado</th>
                            <th style="width: 120px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tecnicos as $index => $tecnico): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm" style="width:36px;height:36px;border-radius:50%;background:#0d6efd;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.85rem;">
                                        <?= strtoupper(substr($tecnico['nombre'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($tecnico['nombre'] ?? '') ?></div>
                                        <small class="text-muted">ID: <?= $tecnico['id'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($tecnico['email'] ?? '') ?>" class="text-decoration-none">
                                    <i class="fas fa-envelope me-1 text-muted"></i>
                                    <?= htmlspecialchars($tecnico['email'] ?? '') ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <?= htmlspecialchars($tecnico['especialidad'] ?? 'Sin especialidad') ?>
                                </span>
                            </td>
                            <td>
                                <strong>S/ <?= number_format($tecnico['tarifa'] ?? 0, 2) ?></strong>
                            </td>
                            <td>
                                <?php if (($tecnico['estado'] ?? '') === 'activo'): ?>
                                    <span class="badge-status bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge-status bg-secondary bg-opacity-10 text-secondary">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="/proyecto/tecnicos/editar/<?= $tecnico['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmarEliminar(<?= $tecnico['id'] ?>, '<?= htmlspecialchars($tecnico['nombre'] ?? '') ?>')" 
                                            title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>No hay técnicos registrados</h5>
                    <p class="text-muted">Haz clic en "Nuevo Técnico" para agregar uno.</p>
                </div>
            <?php endif; ?>
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
function confirmarEliminar(id, nombre) {
    if (confirm(`¿Estás seguro de eliminar al técnico "${nombre}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/proyecto/tecnicos/eliminar/${id}`;
        form.innerHTML = `<input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCSRFToken() ?>">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>