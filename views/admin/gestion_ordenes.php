<?php
// views/admin/gestion_ordenes.php
// Ubicación: C:\xampp\htdocs\produmar\views\admin\gestion_ordenes.php

// Asegurar que las variables existan
$ordenes = $ordenes ?? [];
$tecnicos = $tecnicos ?? [];
$totalPages = $totalPages ?? 0;
$page = $page ?? 1;
$total = $total ?? 0;

$titulo = "Gestión de Órdenes";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-clipboard-list"></i> Gestión de Órdenes</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/produmar/ordenes/crear" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nueva Orden
                    </a>
                </div>
            </div>

            <!-- Mensajes de alerta -->
            <?php if (isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/produmar/admin/gestion_ordenes" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="buscar" class="form-label"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" name="buscar" id="buscar" class="form-control" 
                                   placeholder="N° Orden, título..." 
                                   value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label"><i class="fas fa-filter"></i> Estado</label>
                            <select name="estado" id="estado" class="form-select">
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
                            <label for="prioridad" class="form-label"><i class="fas fa-flag"></i> Prioridad</label>
                            <select name="prioridad" id="prioridad" class="form-select">
                                <option value="">Todas</option>
                                <option value="Baja" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Baja') ? 'selected' : '' ?>>Baja</option>
                                <option value="Media" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Media') ? 'selected' : '' ?>>Media</option>
                                <option value="Alta" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Alta') ? 'selected' : '' ?>>Alta</option>
                                <option value="Urgente" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Urgente') ? 'selected' : '' ?>>Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tecnico" class="form-label"><i class="fas fa-user-cog"></i> Técnico</label>
                            <select name="tecnico" id="tecnico" class="form-select">
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
                            <label for="fecha" class="form-label"><i class="fas fa-calendar"></i> Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" 
                                   value="<?= isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '' ?>">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filtrar</button>
                            <a href="/produmar/admin/gestion_ordenes" class="btn btn-secondary w-100"><i class="fas fa-undo"></i> Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de órdenes -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>N° Orden</th>
                                    <th>Título</th>
                                    <th>Planta / Área</th>
                                    <th>Técnico</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($ordenes)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-info-circle"></i> No hay órdenes registradas
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($ordenes as $orden): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($orden['num_om'] ?? '-') ?></strong></td>
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
                                                <span class="badge bg-<?= $estadoClase ?>">
                                                    <?= $orden['status'] ?? 'PENDIENTE' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $prioridadClases = [
                                                    'Urgente' => 'danger',
                                                    'Alta' => 'danger',
                                                    'Media' => 'warning',
                                                    'Baja' => 'secondary'
                                                ];
                                                $prioridadClase = $prioridadClases[$orden['prioridad'] ?? 'Media'] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $prioridadClase ?>">
                                                    <?= $orden['prioridad'] ?? 'Media' ?>
                                                </span>
                                            </td>
                                            <td><?= isset($orden['fecha_creacion']) ? date('d/m/Y', strtotime($orden['fecha_creacion'])) : '-' ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/produmar/ordenes/ver/<?= $orden['id'] ?>" class="btn btn-sm btn-primary" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/produmar/ordenes/editar/<?= $orden['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if (($orden['status'] ?? '') == 'PENDIENTE' || ($orden['status'] ?? '') == 'EN_PROCESO'): ?>
                                                        <a href="/produmar/ordenes/cerrar/<?= $orden['id'] ?>" class="btn btn-sm btn-success" title="Cerrar">
                                                            <i class="fas fa-check-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (($orden['status'] ?? '') == 'PENDIENTE'): ?>
                                                        <button class="btn btn-sm btn-danger" title="Eliminar" 
                                                                data-bs-toggle="modal" data-bs-target="#modalEliminar<?= $orden['id'] ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
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
                    
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-list"></i> Mostrando <?= count($ordenes) ?> orden(es)
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modales Eliminar -->
<?php if (!empty($ordenes)): ?>
    <?php foreach ($ordenes as $orden): ?>
        <?php if (($orden['status'] ?? '') == 'PENDIENTE'): ?>
            <div class="modal fade" id="modalEliminar<?= $orden['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="/produmar/ordenes/eliminar/<?= $orden['id'] ?>" method="POST">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="fas fa-trash"></i> Eliminar Orden</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>¿Estás seguro de eliminar la orden <strong><?= htmlspecialchars($orden['num_om'] ?? '') ?></strong>?</p>
                                <p class="text-danger"><small>⚠️ Esta acción no se puede deshacer.</small></p>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-ocultar alertas después de 5 segundos
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>