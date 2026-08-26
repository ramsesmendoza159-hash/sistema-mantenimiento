<?php
// views/reportes/supervision.php
// Ubicación: C:\xampp\htdocs\proyecto\views\reportes\supervision.php

// Asegurar que las variables existan
$supervisiones = $supervisiones ?? [];
$estadisticas = $estadisticas ?? [
    'total' => 0,
    'aprobadas' => 0,
    'rechazadas' => 0,
    'pendientes' => 0,
    'calificacion_promedio' => 0
];

$titulo = "Reporte de Supervisión";
$seccion = "reportes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-clipboard-check"></i> Reporte de Supervisión</h1>
                <div>
                    <button class="btn btn-success me-2" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </button>
                    <button class="btn btn-danger" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="filtrosForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="APROBADA">Aprobada</option>
                                <option value="RECHAZADA">Rechazada</option>
                                <option value="PENDIENTE">Pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title">Total Supervisiones</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['total'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title">Aprobadas</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['aprobadas'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6 class="card-title">Rechazadas</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['rechazadas'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6 class="card-title">Calif. Promedio</h6>
                            <p class="card-text display-6"><?php echo number_format($estadisticas['calificacion_promedio'] ?? 0, 1); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de supervisiones -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Orden</th>
                                    <th>Supervisor</th>
                                    <th>Técnico</th>
                                    <th>Calificación</th>
                                    <th>Estado</th>
                                    <th>Cumple</th>
                                    <th>Fecha</th>
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
                                    <?php foreach ($supervisiones as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td>#<?php echo $item['orden_id']; ?></td>
                                            <td><?php echo htmlspecialchars($item['supervisor'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($item['tecnico'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($item['calificacion']): ?>
                                                    <?php echo $item['calificacion']; ?>/5
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?php echo $i <= $item['calificacion'] ? '' : '-o'; ?> text-warning"></i>
                                                    <?php endfor; ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $estadoClases = [
                                                    'APROBADA' => 'success',
                                                    'RECHAZADA' => 'danger',
                                                    'PENDIENTE' => 'warning'
                                                ];
                                                $estadoClase = $estadoClases[$item['estado'] ?? 'PENDIENTE'] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $estadoClase; ?>">
                                                    <?php echo $item['estado'] ?? 'PENDIENTE'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo ($item['cumple'] ?? 0) ? '✅ Sí' : '❌ No'; ?></td>
                                            <td><?php echo isset($item['fecha_creacion']) ? date('d/m/Y', strtotime($item['fecha_creacion'])) : '-'; ?></td>
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

<script>
    function exportarExcel() {
        const params = new URLSearchParams(new FormData(document.getElementById('filtrosForm')));
        window.location.href = '/proyecto/reportes/supervisionExcel?' + params.toString();
    }

    function exportarPDF() {
        const params = new URLSearchParams(new FormData(document.getElementById('filtrosForm')));
        window.location.href = '/proyecto/reportes/supervisionPDF?' + params.toString();
    }

    document.getElementById('filtrosForm').addEventListener('submit', function(e) {
        e.preventDefault();
        window.location.href = '/proyecto/reportes/supervision?' + new URLSearchParams(new FormData(this)).toString();
    });

    document.getElementById('filtrosForm').addEventListener('reset', function(e) {
        e.preventDefault();
        window.location.href = '/proyecto/reportes/supervision';
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>