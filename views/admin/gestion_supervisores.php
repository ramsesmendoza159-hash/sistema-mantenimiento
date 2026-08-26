<?php
// views/admin/gestion_supervisores.php
// Ubicación: C:\xampp\htdocs\proyecto\views\admin\gestion_supervisores.php

// ✅ Asegurar que las variables existan
$supervisores = $supervisores ?? [];
$estadisticas = $estadisticas ?? ['total' => 0, 'activos' => 0, 'inactivos' => 0];

$titulo = "Gestión de Supervisores";
$seccion = "supervisores";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-user-tie"></i> Gestión de Supervisores</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/proyecto/supervisores/crear" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nuevo Supervisor
                    </a>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title">Total Supervisores</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['total'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title">Activos</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['activos'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6 class="card-title">Inactivos</h6>
                            <p class="card-text display-6"><?php echo $estadisticas['inactivos'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Área</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($supervisores)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-info-circle"></i> No hay supervisores registrados
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($supervisores as $supervisor): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($supervisor['nombre']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($supervisor['email']); ?></td>
                                            <td><?php echo htmlspecialchars($supervisor['area'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($supervisor['estado'] ?? 'activo') === 'activo' ? 'success' : 'danger'; ?>">
                                                    <?php echo $supervisor['estado'] ?? 'activo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/proyecto/supervisores/editar/<?php echo $supervisor['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="/proyecto/supervisores/eliminar/<?php echo $supervisor['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este supervisor?')">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-list"></i> Mostrando <?= count($supervisores) ?> supervisor(es)
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
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