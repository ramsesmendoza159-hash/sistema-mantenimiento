<?php
// views/tecnicos/editar.php

$titulo = "Editar Técnico";
$seccion = "tecnicos";
include_once __DIR__ . '/../layouts/header.php';

$tecnico = $tecnico ?? null;
if (!$tecnico) {
    header('Location: /produmar/tecnicos');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-user-edit"></i> Editar Técnico</h1>
                <a href="/produmar/tecnicos" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Mensajes de error -->
            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="/produmar/tecnicos/actualizar/<?php echo $tecnico['id']; ?>" method="POST">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($tecnico['nombre']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($tecnico['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" 
                                           value="<?php echo htmlspecialchars($tecnico['telefono'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="especialidad" class="form-label">Especialidad <span class="text-danger">*</span></label>
                                    <select class="form-select" id="especialidad" name="especialidad" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Mecánica" <?php echo ($tecnico['especialidad'] ?? '') == 'Mecánica' ? 'selected' : ''; ?>>Mecánica</option>
                                        <option value="Eléctrica" <?php echo ($tecnico['especialidad'] ?? '') == 'Eléctrica' ? 'selected' : ''; ?>>Eléctrica</option>
                                        <option value="Electrónica" <?php echo ($tecnico['especialidad'] ?? '') == 'Electrónica' ? 'selected' : ''; ?>>Electrónica</option>
                                        <option value="Hidráulica" <?php echo ($tecnico['especialidad'] ?? '') == 'Hidráulica' ? 'selected' : ''; ?>>Hidráulica</option>
                                        <option value="Refrigeración" <?php echo ($tecnico['especialidad'] ?? '') == 'Refrigeración' ? 'selected' : ''; ?>>Refrigeración</option>
                                        <option value="Metalmecánica" <?php echo ($tecnico['especialidad'] ?? '') == 'Metalmecánica' ? 'selected' : ''; ?>>Metalmecánica</option>
                                        <option value="General" <?php echo ($tecnico['especialidad'] ?? '') == 'General' ? 'selected' : ''; ?>>General</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="form-text">Dejar en blanco para mantener la contraseña actual</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="activo" <?php echo ($tecnico['estado'] ?? 'activo') == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?php echo ($tecnico['estado'] ?? '') == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="/produmar/tecnicos" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
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