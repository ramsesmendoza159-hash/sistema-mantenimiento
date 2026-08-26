<?php
// views/ordenes/ver.php
// Ubicación: C:\xampp\htdocs\proyecto\views\ordenes\ver.php

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Detalle de Orden de Trabajo";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';

// El controlador debe pasar la variable $orden
$orden = $orden ?? null;
if (!$orden) {
    header('Location: /proyecto/ordenes');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Orden #<?php echo $orden['id']; ?></h1>
                <div>
                    <a href="/proyecto/ordenes" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <?php 
                    $estado = $orden['status'] ?? $orden['estado'] ?? 'PENDIENTE';
                    if ($estado !== 'CERRADA' && $estado !== 'CANCELADA' && $estado !== 'APROBADA'): 
                    ?>
                        <a href="/proyecto/ordenes/editar/<?php echo $orden['id']; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a href="/proyecto/ordenes/cerrar/<?php echo $orden['id']; ?>" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Cerrar
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <!-- Información principal -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información de la Orden</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Título:</strong> <?php echo htmlspecialchars($orden['titulo'] ?? ''); ?></p>
                                    <p><strong>Área:</strong> <?php echo $orden['area'] ?? $orden['nombre_area'] ?? 'N/A'; ?></p>
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
                                            $estado = $orden['status'] ?? $orden['estado'] ?? 'PENDIENTE';
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
                                    <p><strong>Técnico asignado:</strong> <?php echo $orden['tecnico'] ?? $orden['tecnico_nombre'] ?? 'Sin asignar'; ?></p>
                                    <?php if (!empty($orden['equipo']) || !empty($orden['nombre_equipo'])): ?>
                                        <p><strong>Equipo:</strong> <?php echo $orden['equipo'] ?? $orden['nombre_equipo']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Descripción</h6>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($orden['descripcion'] ?? $orden['descripcion_mantenimiento'] ?? 'Sin descripción')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de cierre -->
                    <?php 
                    $estado = $orden['status'] ?? $orden['estado'] ?? 'PENDIENTE';
                    if ($estado === 'CERRADA' || $estado === 'APROBADA' || $estado === 'EJECUTADA'): 
                    ?>
                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Información de Cierre</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($orden['descripcion_cierre'])): ?>
                                <p><strong>Descripción del trabajo:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($orden['descripcion_cierre'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if (!empty($orden['tiempo_invertido']) || !empty($orden['horas_trabajadas'])): ?>
                                        <p><strong>Tiempo invertido:</strong> <?php echo $orden['tiempo_invertido'] ?? $orden['horas_trabajadas']; ?> horas</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($orden['repuestos_utilizados'])): ?>
                                        <p><strong>Repuestos utilizados:</strong> <?php echo htmlspecialchars($orden['repuestos_utilizados']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (isset($orden['satisfactorio']) && $orden['satisfactorio'] !== null): ?>
                                <p><strong>Trabajo satisfactorio:</strong> 
                                    <?php echo $orden['satisfactorio'] ? '✅ Sí' : '❌ No'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <!-- Evidencias -->
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
                                                     alt="Evidencia" class="img-fluid rounded">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay evidencias registradas</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Historial de cambios -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Historial</h5>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <?php if (!empty($orden['historial'])): ?>
                                <?php foreach ($orden['historial'] as $registro): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <small class="text-muted"><?php echo $registro['fecha']; ?></small>
                                        <p class="mb-0"><?php echo htmlspecialchars($registro['descripcion']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No hay historial disponible</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>