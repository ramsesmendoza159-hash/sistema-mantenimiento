<?php
// views/tecnico/detalle_orden.php
// Ubicación: C:\xampp\htdocs\proyecto\views\tecnico\detalle_orden.php

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'tecnico') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Detalle de Orden";
$seccion = "tecnico";
include_once __DIR__ . '/../layouts/header.php';

$orden = $orden ?? null;
if (!$orden) {
    header('Location: /proyecto/tecnico/mis_ordenes');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detalle de Orden #<?php echo $orden['id']; ?></h1>
                <div>
                    <a href="/proyecto/tecnico/mis_ordenes" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <?php 
                    $estado = $orden['estado'] ?? 'PENDIENTE';
                    if ($estado === 'PENDIENTE' || $estado === 'EN_PROCESO'): 
                    ?>
                        <a href="/proyecto/tecnico/cerrar_orden/<?php echo $orden['id']; ?>" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Cerrar Orden
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información de la Orden</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Título:</strong> <?php echo htmlspecialchars($orden['titulo'] ?? ''); ?></p>
                                    <p><strong>Área:</strong> <?php echo $orden['area'] ?? 'N/A'; ?></p>
                                    <p><strong>Prioridad:</strong> 
                                        <span class="badge bg-<?php 
                                            $prioridad = $orden['prioridad'] ?? 'Media';
                                            echo $prioridad === 'Urgente' ? 'danger' : 
                                                 ($prioridad === 'Alta' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo $prioridad; ?>
                                        </span>
                                    </p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-<?php 
                                            $estado = $orden['estado'] ?? 'PENDIENTE';
                                            echo $estado === 'CERRADA' || $estado === 'APROBADA' ? 'success' : 
                                                 ($estado === 'EN_PROCESO' ? 'info' : 
                                                 ($estado === 'CANCELADA' || $estado === 'RECHAZADA' ? 'danger' : 'warning')); 
                                        ?>">
                                            <?php echo $estado; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha creación:</strong> <?php echo $orden['fecha_creacion'] ?? ''; ?></p>
                                    <?php if (!empty($orden['fecha_limite'])): ?>
                                        <p><strong>Fecha límite:</strong> <?php echo $orden['fecha_limite']; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($orden['fecha_cierre'])): ?>
                                        <p><strong>Fecha cierre:</strong> <?php echo $orden['fecha_cierre']; ?></p>
                                    <?php endif; ?>
                                    <p><strong>Técnico asignado:</strong> <?php echo $orden['tecnico'] ?? 'Sin asignar'; ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Descripción</h6>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion'] ?? 'Sin descripción')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php 
                    $estado = $orden['estado'] ?? 'PENDIENTE';
                    if ($estado === 'CERRADA' || $estado === 'APROBADA' || $estado === 'CANCELADA'): 
                    ?>
                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Trabajo Realizado</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($orden['descripcion_cierre'])): ?>
                                <p><strong>Descripción del trabajo:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if (!empty($orden['tiempo_invertido'])): ?>
                                        <p><strong>Tiempo invertido:</strong> <?php echo $orden['tiempo_invertido']; ?> horas</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($orden['repuestos_utilizados'])): ?>
                                        <p><strong>Repuestos utilizados:</strong> <?php echo htmlspecialchars($orden['repuestos_utilizados']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Evidencias</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($orden['evidencias'])): ?>
                                <div class="row g-2">
                                    <?php foreach (explode(',', $orden['evidencias']) as $evidencia): ?>
                                        <div class="col-6">
                                            <a href="/proyecto/uploads/evidencias/<?php echo trim($evidencia); ?>" target="_blank">
                                                <img src="/proyecto/uploads/evidencias/<?php echo trim($evidencia); ?>" 
                                                     alt="Evidencia" class="img-fluid rounded" style="max-height: 100px; width: 100%; object-fit: cover;">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay evidencias registradas</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php 
                    $estado = $orden['estado'] ?? 'PENDIENTE';
                    if ($estado === 'PENDIENTE' || $estado === 'EN_PROCESO'): 
                    ?>
                    <div class="card mt-3 border-warning">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Acciones</h5>
                        </div>
                        <div class="card-body">
                            <p class="small">¿Ya completaste el trabajo?</p>
                            <a href="/proyecto/tecnico/cerrar_orden/<?php echo $orden['id']; ?>" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Cerrar Orden
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>