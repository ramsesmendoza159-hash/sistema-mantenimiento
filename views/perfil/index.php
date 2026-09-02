<?php
// views/perfil/index.php
// Ver perfil - VERSIÓN CORREGIDA

// ❌ ELIMINAR ESTA LÍNEA (línea 5)
// require_once __DIR__ . '/../model/UsuariosModel.php';

// ✅ Verificar que la variable $usuario existe
if (!isset($usuario) || empty($usuario)) {
    $_SESSION['error'] = 'No se pudo cargar la información del usuario.';
    header('Location: /proyecto/dashboard');
    exit;
}

$titulo = 'Mi Perfil';
$seccion = 'perfil';

include_once __DIR__ . '/../layouts/header.php';
// ❌ NO incluir sidebar aquí (ya está en header)
?>

<div class="container-fluid px-0">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-user-circle text-primary me-2"></i>Mi Perfil
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Información de tu cuenta
            </p>
        </div>
        <a href="/proyecto/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <?php if (isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'success' ?> alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Contenido -->
    <div class="row g-4">
        <!-- Columna izquierda - Avatar -->
        <div class="col-md-4">
            <div class="card border-0 text-center p-4">
                <div class="avatar-lg mx-auto mb-3" style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#0d6efd,#0dcaf0);color:#fff;display:flex;align-items:center;justify-content:center;font-size:3rem;font-weight:700;">
                    <?= strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <h5 class="fw-bold"><?= htmlspecialchars($usuario['nombre'] ?? '') ?></h5>
                <p class="text-muted small"><?= htmlspecialchars($usuario['rol'] ?? 'usuario') ?></p>
                <div class="d-grid gap-2 mt-3">
                    <a href="/proyecto/perfil/editar" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Editar Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Columna derecha - Información -->
        <div class="col-md-8">
            <div class="card border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Información Personal</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold text-uppercase">Nombre</label>
                            <p class="fw-semibold"><?= htmlspecialchars($usuario['nombre'] ?? '') ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold text-uppercase">Email</label>
                            <p class="fw-semibold"><?= htmlspecialchars($usuario['email'] ?? '') ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold text-uppercase">Rol</label>
                            <p><span class="badge bg-info"><?= htmlspecialchars($usuario['rol'] ?? 'usuario') ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold text-uppercase">Estado</label>
                            <p>
                                <span class="badge <?= ($usuario['estado'] ?? 'activo') === 'activo' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= htmlspecialchars($usuario['estado'] ?? 'activo') ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold text-uppercase">Fecha Registro</label>
                            <p class="fw-semibold"><?= date('d/m/Y', strtotime($usuario['fecha_creacion'] ?? 'now')) ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalPassword">
                            <i class="fas fa-key me-2"></i> Cambiar Contraseña
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="modalPassword" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/proyecto/perfil/cambiar_password" method="POST">
                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCSRFToken() ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key text-primary me-2"></i> Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contraseña Actual</label>
                        <input type="password" class="form-control" name="password_actual" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password_nueva" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password_confirmar" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>