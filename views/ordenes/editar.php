<?php
// views/ordenes/editar.php
// Ubicación: C:\xampp\htdocs\proyecto\views\ordenes\editar.php

// Asegurar que las variables existan
$orden = $orden ?? null;
$plantas = $plantas ?? [];
$areas = $areas ?? [];
$equipos = $equipos ?? [];
$componentes = $componentes ?? [];
$tecnicos = $tecnicos ?? [];
$supervisores = $supervisores ?? [];
$tecnicos_orden = $tecnicos_orden ?? [];

if (!$orden) {
    header('Location: /proyecto/ordenes');
    exit();
}

$titulo = "Editar Orden de Trabajo";
$seccion = "ordenes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Editar Orden #<?php echo $orden['id']; ?></h1>
                <a href="/proyecto/ordenes/ver/<?php echo $orden['id']; ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="/proyecto/ordenes/actualizar/<?php echo $orden['id']; ?>" method="POST" id="ordenForm">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <!-- ========================================= -->
                        <!-- 1. INFORMACIÓN GENERAL -->
                        <!-- ========================================= -->
                        <h5 class="mb-3"><i class="bi bi-info-circle"></i> Información General</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título de la orden *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo htmlspecialchars($orden['titulo'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="prioridad" class="form-label">Prioridad *</label>
                                    <select class="form-select" id="prioridad" name="prioridad" required>
                                        <option value="Baja" <?php echo ($orden['prioridad'] ?? '') == 'Baja' ? 'selected' : ''; ?>>Baja</option>
                                        <option value="Media" <?php echo ($orden['prioridad'] ?? '') == 'Media' ? 'selected' : ''; ?>>Media</option>
                                        <option value="Alta" <?php echo ($orden['prioridad'] ?? '') == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                        <option value="Urgente" <?php echo ($orden['prioridad'] ?? '') == 'Urgente' ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================= -->
                        <!-- 2. UBICACIÓN -->
                        <!-- ========================================= -->
                        <h5 class="mb-3"><i class="bi bi-geo-alt"></i> Ubicación</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="id_planta" class="form-label">Planta</label>
                                    <select class="form-select" id="id_planta" name="id_planta">
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($plantas as $planta): ?>
                                            <option value="<?php echo $planta['id_planta']; ?>" <?php echo ($orden['id_planta'] ?? '') == $planta['id_planta'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($planta['nombre_planta']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="id_area" class="form-label">Área</label>
                                    <select class="form-select" id="id_area" name="id_area">
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?php echo $area['id_area']; ?>" <?php echo ($orden['id_area'] ?? '') == $area['id_area'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($area['nombre_area']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="id_equipo" class="form-label">Equipo</label>
                                    <select class="form-select" id="id_equipo" name="id_equipo">
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($equipos as $equipo): ?>
                                            <option value="<?php echo $equipo['id_equipo']; ?>" <?php echo ($orden['id_equipo'] ?? '') == $equipo['id_equipo'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================= -->
                        <!-- 3. DESCRIPCIÓN -->
                        <!-- ========================================= -->
                        <h5 class="mb-3"><i class="bi bi-file-text"></i> Descripción</h5>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="descripcion_mantenimiento" class="form-label">Descripción detallada *</label>
                                    <textarea class="form-control" id="descripcion_mantenimiento" name="descripcion_mantenimiento" rows="5" required><?php echo htmlspecialchars($orden['descripcion_mantenimiento'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================= -->
                        <!-- 4. ASIGNACIÓN DE TÉCNICOS (MÚLTIPLES) -->
                        <!-- ========================================= -->
                        <h5 class="mb-3"><i class="bi bi-people"></i> Asignación de Técnicos</h5>
                        
                        <!-- Técnico Principal (el que está en la orden) -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="tecnico_id" class="form-label">Técnico principal</label>
                                    <select class="form-select" id="tecnico_id" name="tecnico_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($tecnicos as $tecnico): ?>
                                            <option value="<?php echo $tecnico['id']; ?>" <?php echo ($orden['tecnico_id'] ?? '') == $tecnico['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tecnico['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Técnico principal responsable de la orden</div>
                                </div>
                            </div>
                        </div>

                        <!-- Técnicos adicionales (tabla dinámica) -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Técnicos adicionales</label>
                                    <div id="tecnicos_adicionales_container">
                                        <?php if (!empty($tecnicos_orden)): ?>
                                            <?php foreach ($tecnicos_orden as $index => $t): ?>
                                                <div class="row mb-2 tecnico-adicional" data-index="<?php echo $index; ?>">
                                                    <div class="col-md-4">
                                                        <select class="form-select" name="tecnicos_adicionales[<?php echo $index; ?>][id]">
                                                            <option value="">Seleccionar...</option>
                                                            <?php foreach ($tecnicos as $tecnico): ?>
                                                                <option value="<?php echo $tecnico['id']; ?>" <?php echo ($t['tecnico_id'] ?? '') == $tecnico['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($tecnico['nombre']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="number" class="form-control" name="tecnicos_adicionales[<?php echo $index; ?>][horas]" 
                                                               value="<?php echo $t['horas_trabajadas'] ?? 0; ?>" step="0.5" min="0" placeholder="Horas">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="number" class="form-control" name="tecnicos_adicionales[<?php echo $index; ?>][tarifa]" 
                                                               value="<?php echo $t['tarifa_hora'] ?? 0; ?>" step="0.5" min="0" placeholder="Tarifa S/">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger btn-sm eliminar-tecnico">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" id="agregar_tecnico">
                                        <i class="bi bi-plus-circle"></i> Agregar técnico
                                    </button>
                                    <div class="form-text">Agrega técnicos adicionales con sus horas y tarifas</div>
                                </div>
                            </div>
                        </div>

                        <!-- Supervisor -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_supervisor" class="form-label">Supervisor</label>
                                    <select class="form-select" id="id_supervisor" name="id_supervisor">
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($supervisores as $supervisor): ?>
                                            <option value="<?php echo $supervisor['id']; ?>" <?php echo ($orden['id_supervisor'] ?? '') == $supervisor['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($supervisor['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Estado</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="PENDIENTE" <?php echo ($orden['status'] ?? '') == 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="EN_PROCESO" <?php echo ($orden['status'] ?? '') == 'EN_PROCESO' ? 'selected' : ''; ?>>En Proceso</option>
                                        <option value="EJECUTADA" <?php echo ($orden['status'] ?? '') == 'EJECUTADA' ? 'selected' : ''; ?>>Ejecutada</option>
                                        <option value="CERRADA" <?php echo ($orden['status'] ?? '') == 'CERRADA' ? 'selected' : ''; ?>>Cerrada</option>
                                        <option value="APROBADA" <?php echo ($orden['status'] ?? '') == 'APROBADA' ? 'selected' : ''; ?>>Aprobada</option>
                                        <option value="RECHAZADA" <?php echo ($orden['status'] ?? '') == 'RECHAZADA' ? 'selected' : ''; ?>>Rechazada</option>
                                        <option value="CANCELADA" <?php echo ($orden['status'] ?? '') == 'CANCELADA' ? 'selected' : ''; ?>>Cancelada</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================= -->
                        <!-- 5. FECHAS -->
                        <!-- ========================================= -->
                        <h5 class="mb-3"><i class="bi bi-calendar3"></i> Fechas</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="fecha_emision" class="form-label">Fecha de emisión</label>
                                    <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" 
                                           value="<?php echo $orden['fecha_emision'] ?? date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?php echo $orden['fecha_inicio'] ?? date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="fecha_estimada" class="form-label">Fecha estimada de cierre</label>
                                    <input type="date" class="form-control" id="fecha_estimada" name="fecha_estimada" 
                                           value="<?php echo $orden['fecha_estimada'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="fecha_cierre" class="form-label">Fecha de cierre (real)</label>
                                    <input type="datetime-local" class="form-control" id="fecha_cierre" name="fecha_cierre" 
                                           value="<?php echo isset($orden['fecha_cierre']) ? date('Y-m-d\TH:i', strtotime($orden['fecha_cierre'])) : ''; ?>">
                                    <div class="form-text">Lo completa el técnico, el admin puede editarlo</div>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================= -->
                        <!-- 6. TIEMPO Y COSTOS -->
                        <!-- ========================================= -->
                        <h5 class="mb-3"><i class="bi bi-cash-coin"></i> Tiempo y Costos</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="horas_duracion" class="form-label">Horas estimadas</label>
                                    <input type="number" class="form-control" id="horas_duracion" name="horas_duracion" 
                                           value="<?php echo $orden['horas_duracion'] ?? 0; ?>" step="0.5" min="0">
                                    <div class="form-text">Estimación inicial</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="total_horas_trabajadas" class="form-label">Total horas trabajadas</label>
                                    <input type="number" class="form-control" id="total_horas_trabajadas" 
                                           value="<?php echo $orden['total_horas_trabajadas'] ?? 0; ?>" step="0.5" min="0" readonly>
                                    <div class="form-text">Suma de horas de todos los técnicos</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="costo_repuestos" class="form-label">Costo de repuestos (S/)</label>
                                    <input type="number" class="form-control" id="costo_repuestos" name="costo_repuestos" 
                                           value="<?php echo $orden['costo_repuestos'] ?? 0; ?>" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="costo_total" class="form-label">Costo total (S/)</label>
                                    <input type="number" class="form-control" id="costo_total" name="costo_total" 
                                           value="<?php echo $orden['costo_total'] ?? 0; ?>" step="0.01" min="0" readonly>
                                    <div class="form-text">Se calcula automáticamente</div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="/proyecto/ordenes/ver/<?php echo $orden['id']; ?>" class="btn btn-secondary me-2">Cancelar</a>
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
document.addEventListener('DOMContentLoaded', function() {
    let contador = <?php echo !empty($tecnicos_orden) ? count($tecnicos_orden) : 0; ?>;
    const container = document.getElementById('tecnicos_adicionales_container');
    const agregarBtn = document.getElementById('agregar_tecnico');
    const tecnicosSelect = document.getElementById('tecnico_id');

    function calcularTotales() {
        let totalHoras = 0;
        let totalCosto = 0;
        let totalManoObra = 0;
        
        // Técnico principal
        const tarifaPrincipal = parseFloat(document.getElementById('tarifa_tecnico')?.value) || 0;
        // No sumamos horas del principal porque se maneja en la orden principal
        
        // Técnicos adicionales
        document.querySelectorAll('.tecnico-adicional').forEach(function(row) {
            const horas = parseFloat(row.querySelector('input[name*="[horas]"]')?.value) || 0;
            const tarifa = parseFloat(row.querySelector('input[name*="[tarifa]"]')?.value) || 0;
            totalHoras += horas;
            totalManoObra += horas * tarifa;
        });
        
        // Total horas trabajadas
        const totalHorasInput = document.getElementById('total_horas_trabajadas');
        if (totalHorasInput) {
            totalHorasInput.value = totalHoras.toFixed(1);
        }
        
        // Costo total
        const repuestos = parseFloat(document.getElementById('costo_repuestos')?.value) || 0;
        const totalCostoInput = document.getElementById('costo_total');
        if (totalCostoInput) {
            totalCostoInput.value = (totalManoObra + repuestos).toFixed(2);
        }
    }

    function agregarTecnico(tecnicoId = '', horas = 0, tarifa = 0) {
        const div = document.createElement('div');
        div.className = 'row mb-2 tecnico-adicional';
        div.dataset.index = contador;
        
        const options = <?php echo json_encode($tecnicos); ?>;
        let optionsHtml = '<option value="">Seleccionar...</option>';
        options.forEach(function(t) {
            const selected = t.id == tecnicoId ? 'selected' : '';
            optionsHtml += `<option value="${t.id}" ${selected}>${t.nombre}</option>`;
        });
        
        div.innerHTML = `
            <div class="col-md-4">
                <select class="form-select" name="tecnicos_adicionales[${contador}][id]">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="tecnicos_adicionales[${contador}][horas]" 
                       value="${horas}" step="0.5" min="0" placeholder="Horas">
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="tecnicos_adicionales[${contador}][tarifa]" 
                       value="${tarifa}" step="0.5" min="0" placeholder="Tarifa S/">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm eliminar-tecnico">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        
        // Event listeners
        div.querySelectorAll('input').forEach(function(input) {
            input.addEventListener('input', calcularTotales);
        });
        div.querySelector('select').addEventListener('change', calcularTotales);
        div.querySelector('.eliminar-tecnico').addEventListener('click', function() {
            div.remove();
            calcularTotales();
            // Reindexar
            document.querySelectorAll('.tecnico-adicional').forEach(function(el, idx) {
                el.dataset.index = idx;
                el.querySelectorAll('input, select').forEach(function(input) {
                    const name = input.name;
                    if (name) {
                        input.name = name.replace(/\[[0-9]+\]/, `[${idx}]`);
                    }
                });
            });
        });
        
        container.appendChild(div);
        contador++;
        calcularTotales();
    }

    // Cargar técnicos adicionales existentes
    <?php if (!empty($tecnicos_orden)): ?>
        <?php foreach ($tecnicos_orden as $t): ?>
            agregarTecnico('<?php echo $t['tecnico_id'] ?? ''; ?>', <?php echo $t['horas_trabajadas'] ?? 0; ?>, <?php echo $t['tarifa_hora'] ?? 0; ?>);
        <?php endforeach; ?>
    <?php endif; ?>

    agregarBtn.addEventListener('click', function() {
        agregarTecnico();
    });

    // Event listeners para calcular costos
    document.getElementById('tarifa_tecnico')?.addEventListener('input', calcularTotales);
    document.getElementById('costo_repuestos')?.addEventListener('input', calcularTotales);
    
    // Calcular al inicio
    setTimeout(calcularTotales, 100);
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>