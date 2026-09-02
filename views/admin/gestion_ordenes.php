<?php
// views/admin/gestion_ordenes.php
// Gestión de Órdenes - VERSIÓN CORREGIDA

// ✅ Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

// ✅ Verificar sesión
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit;
}

// Asegurar que las variables existan
$ordenes = $ordenes ?? [];
$tecnicos = $tecnicos ?? [];
$totalPages = $totalPages ?? 0;
$page = $page ?? 1;
$total = $total ?? 0;
$rol = $rol ?? 'usuario';

$titulo = "Gestión de Órdenes";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-clipboard-list text-primary me-2"></i>Gestión de Órdenes
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Administra todas las órdenes de trabajo
            </p>
        </div>
        <a href="/proyecto/ordenes/crear" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Nueva Orden
        </a>
    </div>

    <!-- ✅ Mensajes de alerta -->
    <?php if (isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['mensaje']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Filtros -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="/proyecto/admin/gestion_ordenes" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">
                        <i class="fas fa-search me-1"></i> Buscar
                    </label>
                    <input type="text" name="buscar" class="form-control form-control-sm" 
                           placeholder="N° Orden, título..." 
                           value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">
                        <i class="fas fa-filter me-1"></i> Estado
                    </label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="PENDIENTE" <?= (isset($_GET['estado']) && $_GET['estado'] == 'PENDIENTE') ? 'selected' : '' ?>>Pendiente</option>
                        <option value="EN_PROCESO" <?= (isset($_GET['estado']) && $_GET['estado'] == 'EN_PROCESO') ? 'selected' : '' ?>>En Proceso</option>
                        <option value="EJECUTADA" <?= (isset($_GET['estado']) && $_GET['estado'] == 'EJECUTADA') ? 'selected' : '' ?>>Ejecutada</option>
                        <option value="CERRADA" <?= (isset($_GET['estado']) && $_GET['estado'] == 'CERRADA') ? 'selected' : '' ?>>Cerrada</option>
                        <option value="APROBADA" <?= (isset($_GET['estado']) && $_GET['estado'] == 'APROBADA') ? 'selected' : '' ?>>Aprobada</option>
                        <option value="RECHAZADA" <?= (isset($_GET['estado']) && $_GET['estado'] == 'RECHAZADA') ? 'selected' : '' ?>>Rechazada</option>
                        <option value="CANCELADA" <?= (isset($_GET['estado']) && $_GET['estado'] == 'CANCELADA') ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">
                        <i class="fas fa-flag me-1"></i> Prioridad
                    </label>
                    <select name="prioridad" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="Baja" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Baja') ? 'selected' : '' ?>>Baja</option>
                        <option value="Media" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Media') ? 'selected' : '' ?>>Media</option>
                        <option value="Alta" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Alta') ? 'selected' : '' ?>>Alta</option>
                        <option value="Urgente" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Urgente') ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">
                        <i class="fas fa-user-cog me-1"></i> Técnico
                    </label>
                    <select name="tecnico" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php if (!empty($tecnicos)): ?>
                            <?php foreach ($tecnicos as $tecnico): ?>
                                <option value="<?= $tecnico['id'] ?>" <?= (isset($_GET['tecnico']) && $_GET['tecnico'] == $tecnico['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tecnico['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">
                        <i class="fas fa-calendar me-1"></i> Fecha
                    </label>
                    <input type="date" name="fecha" class="form-control form-control-sm" 
                           value="<?= isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '' ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                    <a href="/proyecto/admin/gestion_ordenes" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Tabla de órdenes -->
    <div class="card border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Orden</th>
                            <th>Título</th>
                            <th>Planta / Área</th>
                            <th>Técnico</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordenes)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay órdenes registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ordenes as $orden): ?>
                                <tr>
                                    <td><span class="fw-semibold"><?= htmlspecialchars($orden['num_om'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($orden['titulo'] ?? '-') ?></td>
                                    <td>
                                        <?= htmlspecialchars($orden['nombre_planta'] ?? '') ?>
                                        <?php if (!empty($orden['nombre_area'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($orden['nombre_area']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($orden['tecnico_nombre'] ?? 'Sin asignar') ?></td>
                                    <td>
                                        <?php
                                        $estadoClases = [
                                            'PENDIENTE' => 'warning',
                                            'EN_PROCESO' => 'info',
                                            'EJECUTADA' => 'primary',
                                            'CERRADA' => 'success',
                                            'APROBADA' => 'success',
                                            'RECHAZADA' => 'danger',
                                            'CANCELADA' => 'secondary'
                                        ];
                                        $estadoClase = $estadoClases[$orden['status'] ?? 'PENDIENTE'] ?? 'secondary';
                                        ?>
                                        <span class="badge-status bg-<?= $estadoClase ?> bg-opacity-10 text-<?= $estadoClase ?>">
                                            <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                            <?= $orden['status'] ?? 'PENDIENTE' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $prioridadClases = [
                                            'Urgente' => 'danger',
                                            'Alta' => 'warning',
                                            'Media' => 'info',
                                            'Baja' => 'success'
                                        ];
                                        $prioridadClase = $prioridadClases[$orden['prioridad'] ?? 'Media'] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $prioridadClase ?> bg-opacity-10 text-<?= $prioridadClase ?>">
                                            <?= $orden['prioridad'] ?? 'Media' ?>
                                        </span>
                                    </td>
                                    <td><small><?= isset($orden['fecha_creacion']) ? date('d/m/Y', strtotime($orden['fecha_creacion'])) : '-' ?></small></td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php $ordenId = $orden['id'] ?? 0; ?>
                                            <?php if ($ordenId > 0): ?>
                                                <a href="/proyecto/ordenes/ver/<?= $ordenId ?>" class="btn btn-sm btn-outline-primary" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($rol === 'admin'): ?>
                                                    <a href="/proyecto/ordenes/editar/<?= $ordenId ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php elseif ($rol === 'supervisor' && $orden['status'] !== 'CERRADA' && $orden['status'] !== 'CANCELADA' && $orden['status'] !== 'APROBADA'): ?>
                                                    <a href="/proyecto/ordenes/editar/<?= $ordenId ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if (($orden['status'] ?? '') == 'PENDIENTE' || ($orden['status'] ?? '') == 'EN_PROCESO'): ?>
                                                    <?php if ($rol === 'admin' || $rol === 'supervisor'): ?>
                                                        <a href="/proyecto/ordenes/cerrar/<?= $ordenId ?>" class="btn btn-sm btn-outline-success" title="Cerrar">
                                                            <i class="fas fa-check-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php if (($orden['status'] ?? '') == 'PENDIENTE' && $rol === 'admin'): ?>
                                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" 
                                                            data-bs-toggle="modal" data-bs-target="#modalEliminar<?= $ordenId ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- ✅ Paginación -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == ($page ?? 1) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="mt-3 text-muted small">
                <i class="fas fa-list me-1"></i> Mostrando <?= count($ordenes) ?> orden(es)
            </div>
        </div>
    </div>

</div>

<!-- ✅ Estilos -->
<style>
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

<!-- ✅ Modales Eliminar -->
<?php if (!empty($ordenes)): ?>
    <?php foreach ($ordenes as $orden): ?>
        <?php $ordenId = $orden['id'] ?? 0; ?>
        <?php if (($orden['status'] ?? '') == 'PENDIENTE' && $ordenId > 0): ?>
            <div class="modal fade" id="modalEliminar<?= $ordenId ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="/proyecto/ordenes/eliminar/<?= $ordenId ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCSRFToken() ?>">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="fas fa-trash me-2"></i> Eliminar Orden</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>¿Estás seguro de eliminar la orden <strong><?= htmlspecialchars($orden['num_om'] ?? '') ?></strong>?</p>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Esta acción no se puede deshacer.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

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