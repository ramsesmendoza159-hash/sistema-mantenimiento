<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Editar Inventario";
$seccion = "inventario";
include_once __DIR__ . '/../layouts/header.php';

// El controlador debe pasar la variable $item
$item = $item ?? null;
if (!$item) {
    header('Location: /produmar/inventario');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Editar: <?php echo htmlspecialchars($item['nombre']); ?></h1>
                <a href="/produmar/inventario" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="/produmar/inventario/actualizar/<?php echo $item['id']; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del repuesto/equipo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($item['nombre']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo *</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="repuesto" <?php echo $item['tipo'] === 'repuesto' ? 'selected' : ''; ?>>Repuesto</option>
                                        <option value="equipo" <?php echo $item['tipo'] === 'equipo' ? 'selected' : ''; ?>>Equipo</option>
                                        <option value="herramienta" <?php echo $item['tipo'] === 'herramienta' ? 'selected' : ''; ?>>Herramienta</option>
                                        <option value="insumo" <?php echo $item['tipo'] === 'insumo' ? 'selected' : ''; ?>>Insumo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoria" class="form-label">Categoría *</label>
                                    <select class="form-select" id="categoria" name="categoria" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="mecanica" <?php echo $item['categoria'] === 'mecanica' ? 'selected' : ''; ?>>Mecánica</option>
                                        <option value="electrica" <?php echo $item['categoria'] === 'electrica' ? 'selected' : ''; ?>>Eléctrica</option>
                                        <option value="electronica" <?php echo $item['categoria'] === 'electronica' ? 'selected' : ''; ?>>Electrónica</option>
                                        <option value="hidraulica" <?php echo $item['categoria'] === 'hidraulica' ? 'selected' : ''; ?>>Hidráulica</option>
                                        <option value="seguridad" <?php echo $item['categoria'] === 'seguridad' ? 'selected' : ''; ?>>Seguridad</option>
                                        <option value="otros" <?php echo $item['categoria'] === 'otros' ? 'selected' : ''; ?>>Otros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código SKU</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" 
                                           value="<?php echo htmlspecialchars($item['codigo'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad *</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                           value="<?php echo $item['cantidad']; ?>" min="0" step="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="precio_unitario" class="form-label">Precio Unitario (USD)</label>
                                    <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" 
                                           value="<?php echo $item['precio_unitario'] ?? ''; ?>" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                           value="<?php echo htmlspecialchars($item['ubicacion'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="imagen" class="form-label">Imagen del producto</label>
                                    <?php if (!empty($item['imagen'])): ?>
                                        <div class="mb-2">
                                            <img src="/produmar/uploads/inventario/<?php echo $item['imagen']; ?>" 
                                                 alt="Imagen actual" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                    <div class="form-text">Dejar en blanco para mantener la imagen actual</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" 
                                           <?php echo ($item['activo'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">Activo</label>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="/produmar/inventario" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Previsualización de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'img-thumbnail mt-2';
                preview.style.maxHeight = '200px';
                
                const oldPreview = document.querySelector('.image-preview');
                if (oldPreview) oldPreview.remove();
                
                preview.classList.add('image-preview');
                this.parentNode.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>