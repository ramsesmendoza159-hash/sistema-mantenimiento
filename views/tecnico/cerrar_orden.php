<?php
// views/tecnico/cerrar_orden.php
// Ubicación: C:\xampp\htdocs\proyecto\views\tecnico\cerrar_orden.php

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'tecnico') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Cerrar Orden de Trabajo";
$seccion = "tecnico";
include_once __DIR__ . '/../layouts/header.php';

$orden = $orden ?? null;
if (!$orden) {
    header('Location: /proyecto/tecnico');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Cerrar Orden #<?php echo $orden['id']; ?></h1>
                <a href="/proyecto/tecnico/mis_ordenes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Completar Trabajo</h5>
                        </div>
                        <div class="card-body">
                            <form action="/proyecto/tecnico/cerrar/<?php echo $orden['id']; ?>" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="descripcion_cierre" class="form-label">Descripción detallada del trabajo *</label>
                                    <textarea class="form-control" id="descripcion_cierre" name="descripcion_cierre" 
                                              rows="5" required></textarea>
                                    <div class="form-text">Explica qué se hizo, qué se reparó, qué se reemplazó, etc.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="evidencias" class="form-label">Evidencias (Fotos del trabajo)</label>
                                    <input type="file" class="form-control" id="evidencias" name="evidencias[]" 
                                           accept="image/*" multiple>
                                    <div class="form-text">Sube fotos del antes y después, o del trabajo en proceso</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tiempo_invertido" class="form-label">Tiempo invertido (horas) *</label>
                                            <input type="number" class="form-control" id="tiempo_invertido" 
                                                   name="tiempo_invertido" step="0.5" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="repuestos_utilizados" class="form-label">Repuestos utilizados</label>
                                            <input type="text" class="form-control" id="repuestos_utilizados" 
                                                   name="repuestos_utilizados" 
                                                   placeholder="Ej: Filtro de aceite x1, Correa de distribución x1">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="satisfactorio" 
                                           name="satisfactorio" checked>
                                    <label class="form-check-label" for="satisfactorio">
                                        El trabajo se realizó de manera satisfactoria
                                    </label>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-end">
                                    <a href="/proyecto/tecnico/mis_ordenes" class="btn btn-secondary me-2">
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Cerrar Orden
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información de la Orden</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Título:</strong> <?php echo htmlspecialchars($orden['titulo'] ?? ''); ?></p>
                            <p><strong>Descripción:</strong></p>
                            <p class="small"><?php echo nl2br(htmlspecialchars(substr($orden['descripcion'] ?? 'Sin descripción', 0, 150))); ?></p>
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
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>