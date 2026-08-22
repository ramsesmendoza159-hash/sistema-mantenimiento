<?php
// views/tecnicos/index.php

$titulo = "Gestión de Técnicos";
$seccion = "tecnicos";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users-cog"></i> Gestión de Técnicos</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/produmar/tecnicos/crear" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nuevo Técnico
                    </a>
                </div>
            </div>

            <!-- Mensajes -->
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

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6 class="card-title">Total Técnicos</h6>
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
                                    <th>Teléfono</th>
                                    <th>Especialidad</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tecnicos)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-info-circle"></i> No hay técnicos registrados
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($tecnico['nombre']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($tecnico['email']); ?></td>
                                            <td><?php echo htmlspecialchars($tecnico['telefono'] ?? '-'); ?></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($tecnico['especialidad'] ?? '-'); ?></span></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($tecnico['estado'] ?? 'activo') === 'activo' ? 'success' : 'danger'; ?>">
                                                    <?php echo $tecnico['estado'] ?? 'activo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/produmar/tecnicos/editar/<?php echo $tecnico['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="/produmar/tecnicos/eliminar/<?php echo $tecnico['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este técnico?')">
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
                        <i class="fas fa-list"></i> Mostrando <?= count($tecnicos) ?> técnico(s)
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

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