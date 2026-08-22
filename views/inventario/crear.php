<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Agregar al Inventario";
$seccion = "inventario";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Agregar al Inventario</h1>
                <a href="/produmar/inventario" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="/produmar/inventario/guardar" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del repuesto/equipo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo *</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="repuesto">Repuesto</option>
                                        <option value="equipo">Equipo</option>
                                        <option value="herramienta">Herramienta</option>
                                        <option value="insumo">Insumo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoria" class="form-label">Categoría *</label>
                                    <select class="form-select" id="categoria" name="categoria" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="mecanica">Mecánica</option>
                                        <option value="electrica">Eléctrica</option>
                                        <option value="electronica">Electrónica</option>
                                        <option value="hidraulica">Hidráulica</option>
                                        <option value="seguridad">Seguridad</option>
                                        <option value="otros">Otros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código SKU</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Ej: SKU-001">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad *</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="0" step="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="precio_unitario" class="form-label">Precio Unitario (USD)</label>
                                    <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" placeholder="Ej: Estante A-1">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="imagen" class="form-label">Imagen del producto</label>
                                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                    <div class="form-text">Formatos permitidos: JPG, PNG, WEBP. Máx 5MB</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
                                    <label class="form-check-label" for="activo">Activo</label>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="/produmar/inventario" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Validación de formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        const tipo = document.getElementById('tipo').value;
        const categoria = document.getElementById('categoria').value;
        const cantidad = parseInt(document.getElementById('cantidad').value);
        
        if (!nombre) {
            e.preventDefault();
            alert('El nombre es obligatorio');
            return;
        }
        
        if (!tipo) {
            e.preventDefault();
            alert('El tipo es obligatorio');
            return;
        }
        
        if (!categoria) {
            e.preventDefault();
            alert('La categoría es obligatoria');
            return;
        }
        
        if (isNaN(cantidad) || cantidad < 0) {
            e.preventDefault();
            alert('La cantidad debe ser un número válido');
            return;
        }
    });

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