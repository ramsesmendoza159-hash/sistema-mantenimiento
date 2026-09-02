<?php
// views/inventario/crear.php
// Crear ítem en inventario - VERSIÓN CORREGIDA CON ASTEROADMIN

// Verificar sesión y rol
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit;
}

$titulo = "Agregar al Inventario";
$seccion = "inventario";

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-boxes text-primary me-2"></i>Agregar al Inventario
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Ingresa los datos del nuevo ítem
            </p>
        </div>
        <a href="/proyecto/inventario" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- ✅ Mensajes de error -->
    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-2">
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

    <!-- ✅ Formulario -->
    <div class="card border-0">
        <div class="card-body">
            <form action="/proyecto/inventario/guardar" method="POST" enctype="multipart/form-data" id="formInventario">
                <!-- ✅ CSRF TOKEN -->
                <?php if (method_exists('SecurityHelper', 'generateCSRFToken')): ?>
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCSRFToken() ?>">
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Nombre -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Nombre del repuesto/equipo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" 
                                   value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                                   required placeholder="Ej: Filtro de aceite">
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo" required>
                                <option value="">Seleccionar...</option>
                                <option value="repuesto" <?= ($old['tipo'] ?? '') === 'repuesto' ? 'selected' : '' ?>>Repuesto</option>
                                <option value="equipo" <?= ($old['tipo'] ?? '') === 'equipo' ? 'selected' : '' ?>>Equipo</option>
                                <option value="herramienta" <?= ($old['tipo'] ?? '') === 'herramienta' ? 'selected' : '' ?>>Herramienta</option>
                                <option value="insumo" <?= ($old['tipo'] ?? '') === 'insumo' ? 'selected' : '' ?>>Insumo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" name="categoria" required>
                                <option value="">Seleccionar...</option>
                                <option value="mecanica" <?= ($old['categoria'] ?? '') === 'mecanica' ? 'selected' : '' ?>>Mecánica</option>
                                <option value="electrica" <?= ($old['categoria'] ?? '') === 'electrica' ? 'selected' : '' ?>>Eléctrica</option>
                                <option value="electronica" <?= ($old['categoria'] ?? '') === 'electronica' ? 'selected' : '' ?>>Electrónica</option>
                                <option value="hidraulica" <?= ($old['categoria'] ?? '') === 'hidraulica' ? 'selected' : '' ?>>Hidráulica</option>
                                <option value="seguridad" <?= ($old['categoria'] ?? '') === 'seguridad' ? 'selected' : '' ?>>Seguridad</option>
                                <option value="otros" <?= ($old['categoria'] ?? '') === 'otros' ? 'selected' : '' ?>>Otros</option>
                            </select>
                        </div>
                    </div>

                    <!-- Código SKU -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Código SKU</label>
                            <input type="text" class="form-control" name="codigo" 
                                   value="<?= htmlspecialchars($old['codigo'] ?? '') ?>"
                                   placeholder="Ej: SKU-001">
                        </div>
                    </div>

                    <!-- Cantidad -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="cantidad" 
                                   value="<?= htmlspecialchars($old['cantidad'] ?? 0) ?>"
                                   min="0" step="1" required>
                        </div>
                    </div>

                    <!-- Precio Unitario -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Precio Unitario (S/)</label>
                            <input type="number" class="form-control" name="precio_unitario" 
                                   value="<?= htmlspecialchars($old['precio_unitario'] ?? 0) ?>"
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Ubicación</label>
                            <input type="text" class="form-control" name="ubicacion" 
                                   value="<?= htmlspecialchars($old['ubicacion'] ?? '') ?>"
                                   placeholder="Ej: Estante A-1">
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" name="descripcion" 
                                      rows="3" placeholder="Descripción del ítem"><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Imagen del producto</label>
                            <input type="file" class="form-control" name="imagen" 
                                   id="imagen" accept="image/*">
                            <small class="text-muted">Formatos: JPG, PNG, WEBP. Máx 5MB</small>
                            <div id="preview-container" class="mt-2"></div>
                        </div>
                    </div>

                    <!-- Activo -->
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="activo" name="activo" 
                                   <?= isset($old['activo']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="activo">Activo</label>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                    <a href="/proyecto/inventario" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- ✅ Estilos -->
<style>
.form-group {
    margin-bottom: 0;
}
.form-label {
    font-size: 0.85rem;
    margin-bottom: 0.4rem;
}
.form-control, .form-select {
    border-radius: 10px;
    padding: 10px 14px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<!-- ✅ Validación JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formInventario');
    const btnGuardar = document.getElementById('btnGuardar');
    const imagenInput = document.getElementById('imagen');
    const previewContainer = document.getElementById('preview-container');
    
    // ✅ Previsualización de imagen
    imagenInput.addEventListener('change', function(e) {
        const file = this.files[0];
        previewContainer.innerHTML = '';
        
        if (file) {
            // Validar tipo
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('❌ Formato no permitido. Usa JPG, PNG o WEBP.');
                this.value = '';
                return;
            }
            
            // Validar tamaño (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('❌ El archivo no debe superar los 5MB.');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.maxHeight = '150px';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // ✅ Validación antes de enviar
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar nombre
        const nombre = document.querySelector('[name="nombre"]');
        if (!nombre.value.trim()) {
            nombre.classList.add('is-invalid');
            isValid = false;
        } else {
            nombre.classList.remove('is-invalid');
        }
        
        // Validar tipo
        const tipo = document.querySelector('[name="tipo"]');
        if (!tipo.value) {
            tipo.classList.add('is-invalid');
            isValid = false;
        } else {
            tipo.classList.remove('is-invalid');
        }
        
        // Validar categoría
        const categoria = document.querySelector('[name="categoria"]');
        if (!categoria.value) {
            categoria.classList.add('is-invalid');
            isValid = false;
        } else {
            categoria.classList.remove('is-invalid');
        }
        
        // Validar cantidad
        const cantidad = document.querySelector('[name="cantidad"]');
        if (isNaN(cantidad.value) || parseInt(cantidad.value) < 0) {
            cantidad.classList.add('is-invalid');
            isValid = false;
        } else {
            cantidad.classList.remove('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';
        }
    });
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>