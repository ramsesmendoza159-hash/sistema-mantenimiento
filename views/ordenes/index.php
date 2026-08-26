<?php
// views/ordenes/index.php
// Ubicación: C:\xampp\htdocs\proyecto\views\ordenes\index.php

// Asegurar que las variables existan
$ordenes = $ordenes ?? [];
$tecnicos = $tecnicos ?? [];
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
$total = $total ?? 0;
$estados = $estados ?? [];
$prioridades = $prioridades ?? [];
$rol = $rol ?? 'usuario';

$titulo = "Órdenes de Trabajo";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-clipboard-list"></i> Órdenes de Trabajo</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if ($rol === 'admin' || $rol === 'supervisor'): ?>
                        <a href="/proyecto/ordenes/crear" class="btn btn-primary me-2">
                            <i class="fas fa-plus-circle"></i> Nueva Orden
                        </a>
                    <?php endif; ?>
                    <a href="/proyecto/ordenes/estadisticas" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/proyecto/ordenes" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="buscar" class="form-label"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" name="buscar" id="buscar" class="form-control" 
                                   placeholder="N° Orden, título..." 
                                   value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label"><i class="fas fa-filter"></i> Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                <?php if (!empty($estados)): ?>
                                    <?php foreach ($estados as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($_GET['estado']) && $_GET['estado'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="prioridad" class="form-label"><i class="fas fa-flag"></i> Prioridad</label>
                            <select name="prioridad" id="prioridad" class="form-select">
                                <option value="">Todas</option>
                                <?php if (!empty($prioridades)): ?>
                                    <?php foreach ($prioridades as $prioridad): ?>
                                        <option value="<?php echo $prioridad; ?>" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == $prioridad) ? 'selected' : ''; ?>>
                                            <?php echo $prioridad; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_desde" class="form-label"><i class="fas fa-calendar"></i> Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" 
                                   value="<?php echo isset($_GET['fecha_desde']) ? htmlspecialchars($_GET['fecha_desde']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_hasta" class="form-label"><i class="fas fa-calendar"></i> Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" 
                                   value="<?php echo isset($_GET['fecha_hasta']) ? htmlspecialchars($_GET['fecha_hasta']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filtrar</button>
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
                                    <th>Prioridad</th>
                                    <th>Estado</th>
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
                                            <td><strong><?php echo htmlspecialchars($orden['num_om'] ?? '-'); ?></strong></td>
                                            <td><?php echo htmlspecialchars($orden['titulo'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($orden['nombre_planta'])): ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($orden['nombre_planta']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($orden['nombre_area'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($orden['nombre_area']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($orden['tecnico_nombre'] ?? 'Sin asignar'); ?></td>
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
                                                <span class="badge bg-<?php echo $prioridadClase; ?>">
                                                    <?php echo $orden['prioridad'] ?? 'Media'; ?>
                                                </span>
                                            </td>
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
                                                <span class="badge bg-<?php echo $estadoClase; ?>">
                                                    <?php echo $orden['status'] ?? 'PENDIENTE'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo isset($orden['fecha_creacion']) ? date('d/m/Y', strtotime($orden['fecha_creacion'])) : '-'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/proyecto/ordenes/ver/<?php echo $orden['id']; ?>" class="btn btn-sm btn-primary" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($rol === 'admin' || $rol === 'supervisor'): ?>
                                                        <?php if ($orden['status'] !== 'CERRADA' && $orden['status'] !== 'CANCELADA' && $orden['status'] !== 'APROBADA'): ?>
                                                            <a href="/proyecto/ordenes/editar/<?php echo $orden['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($orden['status'] === 'PENDIENTE' || $orden['status'] === 'EN_PROCESO'): ?>
                                                            <a href="/proyecto/ordenes/cerrar/<?php echo $orden['id']; ?>" class="btn btn-sm btn-success" title="Cerrar">
                                                                <i class="fas fa-check-circle"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($orden['status'] === 'PENDIENTE' && $rol === 'admin'): ?>
                                                            <button class="btn btn-sm btn-danger" title="Eliminar" 
                                                                    onclick="confirmarEliminar(<?php echo $orden['id']; ?>, '<?php echo htmlspecialchars($orden['num_om']); ?>')">
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
                    
                    <!-- Paginación -->
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-list"></i> Mostrando <?php echo count($ordenes); ?> orden(es) de <?php echo $total ?? 0; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash"></i> Eliminar Orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar la orden <strong id="ordenEliminarNombre"></strong>?</p>
                <p class="text-danger"><small>⚠️ Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formEliminar" method="POST">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmarEliminar(id, num_om) {
        document.getElementById('ordenEliminarNombre').textContent = num_om;
        document.getElementById('formEliminar').action = '/proyecto/ordenes/eliminar/' + id;
        var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
        modal.show();
    }

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