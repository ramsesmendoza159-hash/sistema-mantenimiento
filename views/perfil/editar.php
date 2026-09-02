<!-- views/perfil/editar.php -->
<!-- Editar perfil - VERSIÓN CORREGIDA -->

<?php
// Definir variables para el header
$titulo = 'Editar Perfil';
$seccion = 'perfil';

include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-user-edit text-primary me-2"></i>Editar Perfil
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Actualiza tu información personal
            </p>
        </div>
        <a href="/proyecto/perfil" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <?php if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['errores']); ?>
        </div>
    <?php endif; ?>

    <?php 
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);
    ?>

    <div class="card border-0">
        <div class="card-body">
            <!-- ✅ CORREGIDO: action apunta a /perfil/actualizar -->
            <form action="/proyecto/perfil/actualizar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCSRFToken() ?>">

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" 
                                   value="<?= htmlspecialchars($old['nombre'] ?? $usuario['nombre'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Apellido</label>
                            <input type="text" class="form-control" name="apellido" 
                                   value="<?= htmlspecialchars($old['apellido'] ?? $usuario['apellido'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($old['email'] ?? $usuario['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" 
                                   value="<?= htmlspecialchars($old['telefono'] ?? $usuario['telefono'] ?? '') ?>"
                                   placeholder="999 999 999">
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
                    </button>
                    <a href="/proyecto/perfil" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<style>
.form-group {
    margin-bottom: 0;
}
.form-label {
    font-size: 0.85rem;
    margin-bottom: 0.4rem;
}
.form-control {
    border-radius: 10px;
    padding: 10px 14px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>