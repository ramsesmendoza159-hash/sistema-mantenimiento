<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /produmar/auth/login');
    exit();
}

// Este archivo es similar a ver.php pero más detallado para técnicos
// Se puede usar como una vista más completa con todos los detalles

$titulo = "Detalle Completo de Orden";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';

$orden = $orden ?? null;
if (!$orden) {
    header('Location: /produmar/ordenes');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detalle de Orden #<?php echo $orden['id']; ?></h1>
                <a href="/produmar/ordenes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Detalle completo similar a ver.php pero con más información -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Información General</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>ID</th>
                                    <td><?php echo $orden['id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Título</th>
                                    <td><?php echo htmlspecialchars($orden['titulo']); ?></td>
                                </tr>
                                <tr>
                                    <th>Área</th>
                                    <td><?php echo $orden['area'] ?? 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Prioridad</th>
                                    <td>
                                        <span class="badge bg-<?php echo $orden['prioridad'] === 'urgente' ? 'danger' : 
                                                                 ($orden['prioridad'] === 'alta' ? 'warning' : 'info'); ?>">
                                            <?php echo $orden['prioridad']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estado</th>
                                    <td>
                                        <span class="badge bg-<?php echo $orden['estado'] === 'completada' ? 'success' : 
                                                                 ($orden['estado'] === 'en_progreso' ? 'info' : 
                                                                 ($orden['estado'] === 'cancelada' ? 'danger' : 'warning')); ?>">
                                            <?php echo $orden['estado']; ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Fechas y Asignación</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Fecha Creación</th>
                                    <td><?php echo $orden['fecha_creacion']; ?></td>
                                </tr>
                                <?php if ($orden['fecha_limite']): ?>
                                <tr>
                                    <th>Fecha Límite</th>
                                    <td><?php echo $orden['fecha_limite']; ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($orden['fecha_cierre']): ?>
                                <tr>
                                    <th>Fecha Cierre</th>
                                    <td><?php echo $orden['fecha_cierre']; ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Técnico</th>
                                    <td><?php echo $orden['tecnico'] ?? 'Sin asignar'; ?></td>
                                </tr>
                                <?php if ($orden['equipo']): ?>
                                <tr>
                                    <th>Equipo</th>
                                    <td><?php echo $orden['equipo']; ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <hr>
                    <h5>Descripción</h5>
                    <p><?php echo nl2br(htmlspecialchars($orden['descripcion'])); ?></p>

                    <?php if ($orden['estado'] === 'completada' || $orden['estado'] === 'cancelada'): ?>
                        <hr>
                        <h5>Detalles de Cierre</h5>
                        <?php if ($orden['descripcion_cierre']): ?>
                            <p><strong>Descripción del trabajo:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Tiempo invertido:</strong> 
                                <?php echo $orden['tiempo_invertido'] ?? 'N/A'; ?> horas
                            </div>
                            <div class="col-md-4">
                                <strong>Repuestos:</strong> 
                                <?php echo $orden['repuestos_utilizados'] ?? 'Ninguno'; ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Satisfactorio:</strong> 
                                <?php echo $orden['satisfactorio'] ? '✅ Sí' : '❌ No'; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>