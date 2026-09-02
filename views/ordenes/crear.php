<?php
// views/ordenes/crear.php
// Crear orden de trabajo - VERSIÓN CORREGIDA PARA ASTEROADMIN

// Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
    exit;
}

$seccion = 'ordenes';
$titulo = 'Crear Orden de Trabajo';

// Asegurar que las variables existan
$tecnicos = $tecnicos ?? [];
$supervisores = $supervisores ?? [];
$plantas = $plantas ?? [];
$areas = $areas ?? [];
$equipos = $equipos ?? [];
$componentes = $componentes ?? [];

include_once __DIR__ . '/../layouts/header.php';
// ❌ ELIMINAR: include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-plus-circle text-primary me-2"></i>Crear Orden de Trabajo
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Complete los campos para crear una nueva orden
            </p>
        </div>
        <a href="/proyecto/ordenes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- ✅ Mensajes de error -->
    <?php if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errores']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ✅ Formulario -->
    <div class="card border-0">
        <div class="card-body">
            <form method="POST" action="/proyecto/ordenes/guardar">
                <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCSRFToken(); ?>">
                
                <div class="row g-4">
                    <!-- N° OM -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">N° OM</label>
                            <input type="text" class="form-control" name="num_om" 
                                   placeholder="OT-2026-08-0001" 
                                   value="<?php echo htmlspecialchars($_POST['num_om'] ?? ''); ?>">
                            <small class="text-muted">Dejar en blanco para generar automático</small>
                        </div>
                    </div>
                    
                    <!-- Título -->
                    <div class="col-md-9">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titulo" 
                                   placeholder="Título de la orden" required
                                   value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Planta</label>
                            <select class="form-select" name="id_planta">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($plantas as $planta): ?>
                                    <option value="<?php echo $planta['id_planta']; ?>" 
                                            <?php echo (isset($_POST['id_planta']) && $_POST['id_planta'] == $planta['id_planta']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($planta['nombre_planta']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Área</label>
                            <select class="form-select" name="id_area">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id_area']; ?>" 
                                            <?php echo (isset($_POST['id_area']) && $_POST['id_area'] == $area['id_area']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($area['nombre_area']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Equipo</label>
                            <select class="form-select" name="id_equipo">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($equipos as $equipo): ?>
                                    <option value="<?php echo $equipo['id_equipo']; ?>" 
                                            <?php echo (isset($_POST['id_equipo']) && $_POST['id_equipo'] == $equipo['id_equipo']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Componente</label>
                            <select class="form-select" name="id_componente">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($componentes as $componente): ?>
                                    <option value="<?php echo $componente['id_componente']; ?>" 
                                            <?php echo (isset($_POST['id_componente']) && $_POST['id_componente'] == $componente['id_componente']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($componente['nombre_componente']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Personal -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Técnico</label>
                            <select class="form-select" name="tecnico_id">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['id']; ?>" 
                                            <?php echo (isset($_POST['tecnico_id']) && $_POST['tecnico_id'] == $tecnico['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tecnico['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Supervisor</label>
                            <select class="form-select" name="id_supervisor">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($supervisores as $supervisor): ?>
                                    <option value="<?php echo $supervisor['id']; ?>" 
                                            <?php echo (isset($_POST['id_supervisor']) && $_POST['id_supervisor'] == $supervisor['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($supervisor['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Prioridad <span class="text-danger">*</span></label>
                            <select class="form-select" name="prioridad" required>
                                <option value="Baja" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] === 'Baja') ? 'selected' : ''; ?>>Baja</option>
                                <option value="Media" <?php echo (!isset($_POST['prioridad']) || $_POST['prioridad'] === 'Media') ? 'selected' : ''; ?>>Media</option>
                                <option value="Alta" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] === 'Alta') ? 'selected' : ''; ?>>Alta</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tipos -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Tipo Mantenimiento</label>
                            <select class="form-select" name="tipo_mantenimiento">
                                <option value="CORRECTIVO" <?php echo (isset($_POST['tipo_mantenimiento']) && $_POST['tipo_mantenimiento'] === 'CORRECTIVO') ? 'selected' : ''; ?>>Correctivo</option>
                                <option value="PREVENTIVO" <?php echo (isset($_POST['tipo_mantenimiento']) && $_POST['tipo_mantenimiento'] === 'PREVENTIVO') ? 'selected' : ''; ?>>Preventivo</option>
                                <option value="PREDICTIVO" <?php echo (isset($_POST['tipo_mantenimiento']) && $_POST['tipo_mantenimiento'] === 'PREDICTIVO') ? 'selected' : ''; ?>>Predictivo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Tipo Actividad</label>
                            <select class="form-select" name="tipo_actividad">
                                <option value="">Seleccionar...</option>
                                <option value="MECANICA" <?php echo (isset($_POST['tipo_actividad']) && $_POST['tipo_actividad'] === 'MECANICA') ? 'selected' : ''; ?>>Mecánica</option>
                                <option value="ELECTRICA" <?php echo (isset($_POST['tipo_actividad']) && $_POST['tipo_actividad'] === 'ELECTRICA') ? 'selected' : ''; ?>>Eléctrica</option>
                                <option value="REFRIGERACION" <?php echo (isset($_POST['tipo_actividad']) && $_POST['tipo_actividad'] === 'REFRIGERACION') ? 'selected' : ''; ?>>Refrigeración</option>
                                <option value="GENERAL" <?php echo (isset($_POST['tipo_actividad']) && $_POST['tipo_actividad'] === 'GENERAL') ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Solicitante</label>
                            <input type="text" class="form-control" name="solicitante" 
                                   placeholder="Nombre del solicitante"
                                   value="<?php echo htmlspecialchars($_POST['solicitante'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" 
                                   value="<?php echo htmlspecialchars($_POST['fecha_inicio'] ?? date('Y-m-d')); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Fecha Estimada</label>
                            <input type="date" class="form-control" name="fecha_estimada" 
                                   value="<?php echo htmlspecialchars($_POST['fecha_estimada'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Duración (horas)</label>
                            <input type="number" step="0.5" class="form-control" name="horas_duracion" 
                                   placeholder="0" min="0" max="24"
                                   value="<?php echo htmlspecialchars($_POST['horas_duracion'] ?? 0); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Supervisor Solicitante</label>
                            <input type="text" class="form-control" name="supervisor_solicitante" 
                                   placeholder="Nombre del supervisor"
                                   value="<?php echo htmlspecialchars($_POST['supervisor_solicitante'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Descripción del Mantenimiento <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="descripcion_mantenimiento" 
                                      rows="5" placeholder="Describir el mantenimiento a realizar..." required><?php echo htmlspecialchars($_POST['descripcion_mantenimiento'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Costos -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Tarifa Técnico (S/)</label>
                            <input type="number" step="0.01" class="form-control" name="tarifa_tecnico" 
                                   placeholder="0.00" min="0"
                                   value="<?php echo htmlspecialchars($_POST['tarifa_tecnico'] ?? 0); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Horas Trabajadas</label>
                            <input type="number" step="0.5" class="form-control" name="horas_trabajadas" 
                                   placeholder="0" min="0" max="24"
                                   value="<?php echo htmlspecialchars($_POST['horas_trabajadas'] ?? 0); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Guardar Orden
                        </button>
                    </div>
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

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>