<?php
// views/ordenes/cerrar.php
// Cerrar orden de trabajo - VERSIÓN COMPLETA CORREGIDA

// Incluir helpers necesarios
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
    exit;
}

// Verificar que la orden exista
if (!isset($orden) || !$orden) {
    $_SESSION['error'] = 'Orden no encontrada';
    header('Location: /proyecto/ordenes');
    exit;
}

// ✅ CORREGIDO: Permitir PENDIENTE, EN_PROCESO y EJECUTADA
if (!in_array($orden['status'] ?? '', ['PENDIENTE', 'EN_PROCESO', 'EJECUTADA'])) {
    $_SESSION['error'] = 'La orden no se puede cerrar en su estado actual';
    header('Location: /proyecto/ordenes/ver/' . $orden['id']);
    exit;
}

$seccion = 'ordenes';
$titulo = 'Cerrar Orden de Trabajo - ' . htmlspecialchars($orden['num_om'] ?? '');

include_once __DIR__ . '/../layouts/header.php';
// ❌ NO incluir sidebar aquí (ya está en header)
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-check-circle text-success me-2"></i>
                Cerrar Orden: <?php echo htmlspecialchars($orden['num_om'] ?? 'N/A'); ?>
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Complete el cierre de la orden de trabajo
            </p>
        </div>
        <a href="/proyecto/ordenes/ver/<?php echo $orden['id']; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- ✅ Mostrar errores -->
    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- ✅ Información de la orden -->
        <div class="col-lg-4">
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-semibold text-uppercase">N° OM</label>
                        <p class="fw-semibold mb-0"><?php echo htmlspecialchars($orden['num_om'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-semibold text-uppercase">Título</label>
                        <p class="fw-semibold mb-0"><?php echo htmlspecialchars($orden['titulo'] ?? 'Sin título'); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-semibold text-uppercase">Técnico</label>
                        <p class="fw-semibold mb-0"><?php echo htmlspecialchars($orden['tecnico_nombre'] ?? 'Sin asignar'); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-semibold text-uppercase">Prioridad</label>
                        <p class="mb-0">
                            <?php 
                            $prioridad = $orden['prioridad'] ?? 'Media';
                            $prioridadColor = match($prioridad) {
                                'Alta' => 'danger',
                                'Media' => 'warning',
                                'Baja' => 'success',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $prioridadColor; ?> bg-opacity-10 text-<?php echo $prioridadColor; ?>">
                                <?php echo htmlspecialchars($prioridad); ?>
                            </span>
                        </p>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small fw-semibold text-uppercase">Estado</label>
                        <p class="mb-0">
                            <?php 
                            $estado = $orden['status'] ?? 'PENDIENTE';
                            $estadoColor = match($estado) {
                                'PENDIENTE' => 'warning',
                                'EN_PROCESO' => 'info',
                                'EJECUTADA' => 'primary',
                                'CERRADA' => 'success',
                                'APROBADA' => 'success',
                                'CANCELADA' => 'danger',
                                'RECHAZADA' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $estadoColor; ?> bg-opacity-10 text-<?php echo $estadoColor; ?>">
                                <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                <?php echo htmlspecialchars($estado); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Formulario de cierre -->
        <div class="col-lg-8">
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-semibold text-success">
                        <i class="fas fa-clipboard-check text-success me-2"></i> Completar Cierre
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/proyecto/ordenes/procesarCierre/<?php echo $orden['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCSRFToken(); ?>">

                        <!-- Descripción realizada -->
                        <div class="mb-3">
                            <label for="descripcion_realizada" class="form-label fw-semibold">Descripción del Trabajo Realizado <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="descripcion_realizada" name="descripcion_realizada" 
                                      rows="4" placeholder="Describir detalladamente el trabajo realizado..." required><?php echo htmlspecialchars($_POST['descripcion_realizada'] ?? ''); ?></textarea>
                        </div>

                        <!-- Pasos ejecutados -->
                        <div class="mb-3">
                            <label for="pasos_ejecutados" class="form-label fw-semibold">Pasos Ejecutados</label>
                            <textarea class="form-control" id="pasos_ejecutados" name="pasos_ejecutados" 
                                      rows="3" placeholder="Listar los pasos realizados para completar el trabajo..."><?php echo htmlspecialchars($_POST['pasos_ejecutados'] ?? ''); ?></textarea>
                        </div>

                        <div class="row g-3">
                            <!-- Horas trabajadas -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="horas_trabajadas" class="form-label fw-semibold">Horas Trabajadas <span class="text-danger">*</span></label>
                                    <input type="number" step="0.5" class="form-control" id="horas_trabajadas" name="horas_trabajadas" 
                                           placeholder="0" min="0" max="24" required
                                           value="<?php echo htmlspecialchars($_POST['horas_trabajadas'] ?? $orden['horas_trabajadas'] ?? 0); ?>">
                                </div>
                            </div>
                            
                            <!-- Tarifa técnico -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tarifa_tecnico" class="form-label fw-semibold">Tarifa Técnico (S/)</label>
                                    <input type="number" step="0.01" class="form-control" id="tarifa_tecnico" name="tarifa_tecnico" 
                                           placeholder="0.00" min="0"
                                           value="<?php echo htmlspecialchars($_POST['tarifa_tecnico'] ?? $orden['tarifa_tecnico'] ?? 0); ?>">
                                </div>
                            </div>
                            
                            <!-- Costo repuestos -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="costo_repuestos" class="form-label fw-semibold">Costo Repuestos (S/)</label>
                                    <input type="number" step="0.01" class="form-control" id="costo_repuestos" name="costo_repuestos" 
                                           placeholder="0.00" min="0"
                                           value="<?php echo htmlspecialchars($_POST['costo_repuestos'] ?? $orden['costo_repuestos'] ?? 0); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="observaciones_tecnico" class="form-label fw-semibold">Observaciones del Técnico</label>
                                    <textarea class="form-control" id="observaciones_tecnico" name="observaciones_tecnico" 
                                              rows="3" placeholder="Observaciones del técnico..."><?php echo htmlspecialchars($_POST['observaciones_tecnico'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="observaciones_cierre" class="form-label fw-semibold">Observaciones de Cierre</label>
                                    <textarea class="form-control" id="observaciones_cierre" name="observaciones_cierre" 
                                              rows="3" placeholder="Observaciones finales del cierre..."><?php echo htmlspecialchars($_POST['observaciones_cierre'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Evidencia -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="foto_evidencia" class="form-label fw-semibold">Foto Evidencia (URL)</label>
                                    <input type="text" class="form-control" id="foto_evidencia" name="foto_evidencia" 
                                           placeholder="https://ejemplo.com/foto.jpg"
                                           value="<?php echo htmlspecialchars($_POST['foto_evidencia'] ?? ''); ?>">
                                    <small class="text-muted">Opcional - URL de la imagen evidencia</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="firma_tecnico" class="form-label fw-semibold">Firma del Técnico (Base64)</label>
                                    <input type="text" class="form-control" id="firma_tecnico" name="firma_tecnico" 
                                           placeholder="Firma en formato base64"
                                           value="<?php echo htmlspecialchars($_POST['firma_tecnico'] ?? ''); ?>">
                                    <small class="text-muted">Opcional - Firma digital del técnico</small>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <hr>
                        <div class="d-flex gap-2">
                            <a href="/proyecto/ordenes/ver/<?php echo $orden['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i> Cerrar Orden
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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