<?php
// views/inventario/editar.php
// Ubicación: C:\xampp\htdocs\proyecto\views\inventario\editar.php

// ✅ ELIMINAR session_start() - ya está iniciada en el router
// session_start(); // ❌ ELIMINAR ESTA LÍNEA

// Verificar autenticación y rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit();
}

// Incluir header ANTES de cualquier salida HTML
$titulo = "Editar Ítem - Inventario";
$seccion = "inventario";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-edit"></i> Editar Ítem - Inventario</h1>
                <a href="/proyecto/inventario" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/proyecto/inventario/actualizar/<?php echo $item['id'] ?? 0; ?>" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($item['nombre'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="codigo" name="codigo" 
                                       value="<?php echo htmlspecialchars($item['codigo'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria">
                                    <option value="">Seleccionar</option>
                                    <option value="Herramientas" <?php echo (isset($item['categoria']) && $item['categoria'] == 'Herramientas') ? 'selected' : ''; ?>>Herramientas</option>
                                    <option value="Repuestos" <?php echo (isset($item['categoria']) && $item['categoria'] == 'Repuestos') ? 'selected' : ''; ?>>Repuestos</option>
                                    <option value="Equipos" <?php echo (isset($item['categoria']) && $item['categoria'] == 'Equipos') ? 'selected' : ''; ?>>Equipos</option>
                                    <option value="Insumos" <?php echo (isset($item['categoria']) && $item['categoria'] == 'Insumos') ? 'selected' : ''; ?>>Insumos</option>
                                    <option value="Seguridad" <?php echo (isset($item['categoria']) && $item['categoria'] == 'Seguridad') ? 'selected' : ''; ?>>Seguridad</option>
                                    <option value="Otros" <?php echo (isset($item['categoria']) && $item['categoria'] == 'Otros') ? 'selected' : ''; ?>>Otros</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                       value="<?php echo $item['stock'] ?? 0; ?>" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                                       value="<?php echo $item['stock_minimo'] ?? 0; ?>" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio_compra" class="form-label">Precio de Compra (S/)</label>
                                <input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra" 
                                       value="<?php echo $item['precio_compra'] ?? 0; ?>" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="precio_venta" class="form-label">Precio de Venta (S/)</label>
                                <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" 
                                       value="<?php echo $item['precio_venta'] ?? 0; ?>" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="imagen" class="form-label">Imagen</label>
                                <?php if (!empty($item['imagen'])): ?>
                                    <div class="mb-2">
                                        <img src="/proyecto/uploads/inventario/<?php echo $item['imagen']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['nombre'] ?? ''); ?>" 
                                             style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                <small class="text-muted">Dejar vacío para mantener la imagen actual</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="activo" <?php echo (isset($item['estado']) && $item['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" <?php echo (isset($item['estado']) && $item['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Actualizar Ítem
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>