<?php
// views/reportes/ordenes.php
// Ubicación: C:\xampp\htdocs\proyecto\views\reportes\ordenes.php

// ✅ ELIMINAR session_start() - ya está iniciada en el router principal
// NO usar session_start() aquí

// Verificar autenticación y rol
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Reporte de Órdenes";
$seccion = "reportes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-file-alt"></i> Reporte de Órdenes de Trabajo</h1>
                <div>
                    <a href="/proyecto/reportes/ordenes/exportar?tipo=excel" class="btn btn-success me-2">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="/proyecto/reportes/ordenes/exportar?tipo=pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/proyecto/reportes/ordenes" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="fecha_desde" class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                   value="<?php echo htmlspecialchars($_GET['fecha_desde'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                   value="<?php echo htmlspecialchars($_GET['fecha_hasta'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="PENDIENTE" <?php echo ($_GET['estado'] ?? '') === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="EN_PROCESO" <?php echo ($_GET['estado'] ?? '') === 'EN_PROCESO' ? 'selected' : ''; ?>>En Progreso</option>
                                <option value="EJECUTADA" <?php echo ($_GET['estado'] ?? '') === 'EJECUTADA' ? 'selected' : ''; ?>>Ejecutada</option>
                                <option value="CERRADA" <?php echo ($_GET['estado'] ?? '') === 'CERRADA' ? 'selected' : ''; ?>>Cerrada</option>
                                <option value="CANCELADA" <?php echo ($_GET['estado'] ?? '') === 'CANCELADA' ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="prioridad" class="form-label">Prioridad</label>
                            <select class="form-select" id="prioridad" name="prioridad">
                                <option value="">Todas</option>
                                <option value="Alta" <?php echo ($_GET['prioridad'] ?? '') === 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                <option value="Media" <?php echo ($_GET['prioridad'] ?? '') === 'Media' ? 'selected' : ''; ?>>Media</option>
                                <option value="Baja" <?php echo ($_GET['prioridad'] ?? '') === 'Baja' ? 'selected' : ''; ?>>Baja</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tecnico_id" class="form-label">Técnico</label>
                            <select class="form-select" id="tecnico_id" name="tecnico_id">
                                <option value="">Todos</option>
                                <?php 
                                require_once __DIR__ . '/../../model/Tecnico.php';
                                $tecnicoModel = new Tecnico();
                                $tecnicos = $tecnicoModel->obtenerTodos();
                                foreach ($tecnicos as $tecnico): 
                                ?>
                                    <option value="<?php echo $tecnico['id']; ?>" 
                                            <?php echo ($_GET['tecnico_id'] ?? '') == $tecnico['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tecnico['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row mb-4">
                <?php
                $estadisticas = $estadisticas ?? [
                    'total' => 0,
                    'completadas' => 0,
                    'pendientes' => 0,
                    'canceladas' => 0,
                    'en_proceso' => 0
                ];
                ?>
                <div class="col-md-2 col-sm-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total</h6>
                            <h3><?php echo number_format($estadisticas['total'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4">
                    <div class="card text-white bg-success">
                        <div class="card-body text-center">
                            <h6 class="card-title">Completadas</h6>
                            <h3><?php echo number_format($estadisticas['completadas'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body text-center">
                            <h6 class="card-title">Pendientes</h6>
                            <h3><?php echo number_format($estadisticas['pendientes'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4">
                    <div class="card text-white bg-info">
                        <div class="card-body text-center">
                            <h6 class="card-title">En Proceso</h6>
                            <h3><?php echo number_format($estadisticas['en_proceso'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4">
                    <div class="card text-white bg-danger">
                        <div class="card-body text-center">
                            <h6 class="card-title">Canceladas</h6>
                            <h3><?php echo number_format($estadisticas['canceladas'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4">
                    <div class="card text-white bg-secondary">
                        <div class="card-body text-center">
                            <h6 class="card-title">Costo Total</h6>
                            <h5>S/ <?php echo number_format($estadisticas['costo_total'] ?? 0, 2); ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de reporte -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>N° OM</th>
                                    <th>Título</th>
                                    <th>Planta</th>
                                    <th>Área</th>
                                    <th>Técnico</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Costo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ordenes)): ?>
                                    <?php foreach ($ordenes as $orden): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($orden['num_om'] ?? 'N/A'); ?></strong></td>
                                            <td><?php echo htmlspecialchars($orden['titulo'] ?? 'Sin título'); ?></td>
                                            <td><?php echo htmlspecialchars($orden['nombre_planta'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($orden['nombre_area'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($orden['tecnico_nombre'] ?? 'Sin asignar'); ?></td>
                                            <td>
                                                <?php 
                                                $prioridad = $orden['prioridad'] ?? 'Media';
                                                $badgeClass = match($prioridad) {
                                                    'Alta' => 'danger',
                                                    'Media' => 'warning',
                                                    'Baja' => 'success',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                                    <?php echo htmlspecialchars($prioridad); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $orden['status'] ?? 'PENDIENTE';
                                                $badgeStatus = match($status) {
                                                    'PENDIENTE' => 'warning',
                                                    'EN_PROCESO' => 'info',
                                                    'EJECUTADA' => 'primary',
                                                    'CERRADA' => 'success',
                                                    'APROBADA' => 'success',
                                                    'RECHAZADA' => 'danger',
                                                    'CANCELADA' => 'dark',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $badgeStatus; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($orden['fecha_creacion'] ?? 'now')); ?></td>
                                            <td>S/ <?php echo number_format($orden['costo_total'] ?? 0, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <i class="fas fa-file-alt fa-3x text-muted d-block mb-3"></i>
                                            <p class="text-muted">No hay órdenes que coincidan con los filtros seleccionados</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="fas fa-list"></i> Mostrando <?php echo count($ordenes ?? []); ?> orden(es)
                        </span>
                        <!-- Paginación si es necesario -->
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>