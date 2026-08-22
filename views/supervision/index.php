<?php
// views/supervision/index.php
// Ubicación: C:\xampp\htdocs\produmar\views\supervision\index.php

// ==========================================
// NO INICIAR SESIÓN AQUÍ - YA ESTÁ INICIADA EN index.php
// ==========================================
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// ==========================================
// ASEGURAR QUE LAS VARIABLES EXISTAN
// ==========================================
$ordenes = $ordenes ?? [];
$estadisticas = $estadisticas ?? [
    'total' => 0,
    'pendientes' => 0,
    'en_proceso' => 0,
    'ejecutadas' => 0,
    'cerradas' => 0,
    'aprobadas' => 0,
    'rechazadas' => 0,
    'canceladas' => 0
];
$tecnicos = $tecnicos ?? [];
$filtros = $filtros ?? [];

$titulo = "Supervisión de Órdenes";
$seccion = "supervision";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-clipboard-check"></i> Supervisión de Órdenes</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/produmar/supervision/reporte" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Reporte
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

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title">Total</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['total'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6 class="card-title">Pendientes</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['pendientes'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6 class="card-title">En Proceso</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['en_proceso'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title">Aprobadas</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['aprobadas'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6 class="card-title">Rechazadas</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['rechazadas'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-secondary">
                        <div class="card-body">
                            <h6 class="card-title">Canceladas</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['canceladas'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/produmar/supervision" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="estado" class="form-label"><i class="fas fa-filter"></i> Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="PENDIENTE" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'PENDIENTE') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="EN_PROCESO" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'EN_PROCESO') ? 'selected' : ''; ?>>En Proceso</option>
                                <option value="EJECUTADA" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'EJECUTADA') ? 'selected' : ''; ?>>Ejecutada</option>
                                <option value="CERRADA" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'CERRADA') ? 'selected' : ''; ?>>Cerrada</option>
                                <option value="APROBADA" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'APROBADA') ? 'selected' : ''; ?>>Aprobada</option>
                                <option value="RECHAZADA" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'RECHAZADA') ? 'selected' : ''; ?>>Rechazada</option>
                                <option value="CANCELADA" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'CANCELADA') ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tecnico_id" class="form-label"><i class="fas fa-user-cog"></i> Técnico</label>
                            <select name="tecnico_id" id="tecnico_id" class="form-select">
                                <option value="">Todos</option>
                                <?php if (!empty($tecnicos)): ?>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?php echo $tecnico['id']; ?>" <?php echo (isset($_GET['tecnico_id']) && $_GET['tecnico_id'] == $tecnico['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tecnico['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="buscar" class="form-label"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" name="buscar" id="buscar" class="form-control" 
                                   placeholder="N° Orden, título..." 
                                   value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
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

            <!-- Tabla de supervisiones -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Orden</th>
                                    <th>Supervisor</th>
                                    <th>Calificación</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Cumple</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($supervisiones)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-info-circle"></i> No hay supervisiones registradas
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($supervisiones as $supervision): ?>
                                        <tr>
                                            <td><?php echo $supervision['id']; ?></td>
                                            <td>#<?php echo $supervision['orden_id']; ?></td>
                                            <td><?php echo htmlspecialchars($supervision['supervisor'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($supervision['calificacion']): ?>
                                                    <?php echo $supervision['calificacion']; ?>/5
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?php echo $i <= $supervision['calificacion'] ? '' : '-o'; ?> text-warning"></i>
                                                    <?php endfor; ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $estadoClases = [
                                                    'pendiente' => 'warning',
                                                    'aprobada' => 'success',
                                                    'rechazada' => 'danger'
                                                ];
                                                $estadoClase = $estadoClases[$supervision['estado'] ?? 'pendiente'] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $estadoClase; ?>">
                                                    <?php echo $supervision['estado'] ?? 'pendiente'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo isset($supervision['fecha_creacion']) ? date('d/m/Y', strtotime($supervision['fecha_creacion'])) : '-'; ?></td>
                                            <td><?php echo ($supervision['cumple'] ?? 0) ? '✅ Sí' : '❌ No'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/produmar/supervision/ver/<?php echo $supervision['id']; ?>" class="btn btn-sm btn-info" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/produmar/supervision/editar/<?php echo $supervision['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-list"></i> Mostrando <?php echo count($supervisiones); ?> registro(s)
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

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