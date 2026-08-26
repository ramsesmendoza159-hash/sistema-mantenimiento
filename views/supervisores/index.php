<?php
// views/supervisores/index.php
$seccion = 'supervisores';  // 👈 ESTO ES LO QUE FALTABA
$titulo = 'Gestión de Supervisores';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) : 'Gestión de Supervisores' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        .stats-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px 15px;
            text-align: center;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }
        .stats-card .number { font-size: 32px; font-weight: bold; color: #007bff; }
        .stats-card .label { color: #6c757d; font-size: 14px; }
        .stats-card .icon { font-size: 24px; display: block; margin-bottom: 5px; }
        .stats-card.total { border-left-color: #007bff; }
        .stats-card.total .number { color: #007bff; }
        .stats-card.activos { border-left-color: #28a745; }
        .stats-card.activos .number { color: #28a745; }
        .stats-card.inactivos { border-left-color: #dc3545; }
        .stats-card.inactivos .number { color: #dc3545; }
        .stats-card.areas { border-left-color: #ffc107; }
        .stats-card.areas .number { color: #ffc107; }
        
        .badge-estado {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-inactivo { background: #f8d7da; color: #721c24; }
        
        .filtros-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        .filtros-card label { font-weight: 600; font-size: 14px; color: #495057; }
        .table-container { background: #ffffff; border-radius: 10px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        .table thead th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057; font-size: 13px; text-transform: uppercase; }
        .table tbody td { vertical-align: middle; font-size: 14px; }
        .btn-group .btn { padding: 4px 8px; font-size: 13px; border-radius: 4px; }
        .btn-group .btn:not(:last-child) { margin-right: 3px; }
        .modal-content { border-radius: 10px; }
        .modal-header { border-bottom: 1px solid #dee2e6; padding: 15px 20px; }
        .modal-footer { border-top: 1px solid #dee2e6; padding: 15px 20px; }
        @media (max-width: 768px) {
            .stats-card { margin-bottom: 15px; }
            .table-responsive { font-size: 13px; }
            .btn-group .btn { padding: 3px 6px; font-size: 11px; }
        }
    </style>
</head>
<body>
    <?php include_once 'views/layouts/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'views/layouts/sidebar.php'; ?>
            
            <div class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-user-tie"></i> <?= isset($titulo) ? htmlspecialchars($titulo) : 'Gestión de Supervisores' ?></h1>
                    <a href="/proyecto/supervisores/crear" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nuevo Supervisor
                    </a>
                </div>

                <?php if (isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
                    </div>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php
                if (!isset($estadisticas) || !is_array($estadisticas)) {
                    $estadisticas = ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'total_areas' => 0];
                }
                ?>
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card total">
                            <span class="icon"><i class="fas fa-users"></i></span>
                            <div class="number"><?= isset($estadisticas['total']) ? number_format((int)$estadisticas['total']) : 0 ?></div>
                            <div class="label">Total Supervisores</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card activos">
                            <span class="icon"><i class="fas fa-user-check"></i></span>
                            <div class="number"><?= isset($estadisticas['activos']) ? number_format((int)$estadisticas['activos']) : 0 ?></div>
                            <div class="label">Activos</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card inactivos">
                            <span class="icon"><i class="fas fa-user-times"></i></span>
                            <div class="number"><?= isset($estadisticas['inactivos']) ? number_format((int)$estadisticas['inactivos']) : 0 ?></div>
                            <div class="label">Inactivos</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card areas">
                            <span class="icon"><i class="fas fa-building"></i></span>
                            <div class="number"><?= isset($estadisticas['total_areas']) ? number_format((int)$estadisticas['total_areas']) : 0 ?></div>
                            <div class="label">Áreas</div>
                        </div>
                    </div>
                </div>

                <div class="filtros-card">
                    <form method="GET" action="/proyecto/supervisores" class="row align-items-end">
                        <div class="col-md-4">
                            <label for="buscar"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" name="buscar" id="buscar" class="form-control" 
                                   placeholder="Nombre o email..." 
                                   value="<?= isset($filtros['buscar']) ? htmlspecialchars($filtros['buscar']) : '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="estado"><i class="fas fa-filter"></i> Estado</label>
                            <select name="estado" id="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="activo" <?= (isset($filtros['estado']) && $filtros['estado'] == 'activo') ? 'selected' : '' ?>>Activos</option>
                                <option value="inactivo" <?= (isset($filtros['estado']) && $filtros['estado'] == 'inactivo') ? 'selected' : '' ?>>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                        <div class="col-md-2">
                            <a href="/proyecto/supervisores" class="btn btn-secondary w-100"><i class="fas fa-undo"></i> Limpiar</a>
                        </div>
                    </form>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Área</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!isset($supervisores) || !is_array($supervisores)) {
                                    $supervisores = [];
                                }
                                if (empty($supervisores)): 
                                ?>
                                    <tr><td colspan="7" class="text-center py-4"><i class="fas fa-info-circle"></i> No hay supervisores registrados</td></tr>
                                <?php else: ?>
                                    <?php foreach ($supervisores as $supervisor): ?>
                                        <tr>
                                            <td><strong>#<?= isset($supervisor['id']) ? (int)$supervisor['id'] : '' ?></strong></td>
                                            <td><?= isset($supervisor['nombre']) ? htmlspecialchars($supervisor['nombre']) : '' ?></td>
                                            <td><?= isset($supervisor['email']) ? htmlspecialchars($supervisor['email']) : '' ?></td>
                                            <td><?= isset($supervisor['area']) ? htmlspecialchars($supervisor['area']) : 'Sin área' ?></td>
                                            <td>
                                                <span class="badge-estado badge-<?= isset($supervisor['estado']) && $supervisor['estado'] == 'activo' ? 'activo' : 'inactivo' ?>">
                                                    <?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? '✅ Activo' : '⛔ Inactivo' ?>
                                                </span>
                                            </td>
                                            <td><?= isset($supervisor['fecha_creacion']) ? date('d/m/Y', strtotime($supervisor['fecha_creacion'])) : '' ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/proyecto/supervisores/editar/<?= isset($supervisor['id']) ? (int)$supervisor['id'] : 0 ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalPassword<?= isset($supervisor['id']) ? (int)$supervisor['id'] : 0 ?>"><i class="fas fa-key"></i></button>
                                                    <button class="btn btn-sm <?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? 'btn-secondary' : 'btn-success' ?>" data-toggle="modal" data-target="#modalEstado<?= isset($supervisor['id']) ? (int)$supervisor['id'] : 0 ?>">
                                                        <i class="fas fa-<?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? 'pause' : 'play' ?>"></i>
                                                    </button>
                                                    <?php if (isset($supervisor['id']) && $supervisor['id'] != 1): ?>
                                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalEliminar<?= isset($supervisor['id']) ? (int)$supervisor['id'] : 0 ?>"><i class="fas fa-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php $total = (isset($supervisores) && is_array($supervisores)) ? count($supervisores) : 0; ?>
                    <div class="mt-2 text-muted small"><i class="fas fa-list"></i> Mostrando <?= $total ?> supervisor(es)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    <?php if (isset($supervisores) && is_array($supervisores) && !empty($supervisores)): ?>
        <?php foreach ($supervisores as $supervisor): ?>
            <?php if (!isset($supervisor['id'])) continue; ?>
            
            <div class="modal fade" id="modalPassword<?= (int)$supervisor['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="/proyecto/supervisores/cambiarPassword/<?= (int)$supervisor['id'] ?>" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-key text-primary"></i> Cambiar Contraseña</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Nueva Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required minlength="6">
                                </div>
                                <div class="form-group">
                                    <label>Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" name="confirmar_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalEstado<?= (int)$supervisor['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="/proyecto/supervisores/cambiarEstado/<?= (int)$supervisor['id'] ?>" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? '⛔ Inactivar' : '✅ Activar' ?></h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p>¿Estás seguro de <?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? 'inactivar' : 'activar' ?> a <strong><?= isset($supervisor['nombre']) ? htmlspecialchars($supervisor['nombre']) : '' ?></strong>?</p>
                                <input type="hidden" name="estado" value="<?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? 'inactivo' : 'activo' ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-<?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? 'danger' : 'success' ?>">
                                    <?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? '⛔ Inactivar' : '✅ Activar' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (isset($supervisor['id']) && $supervisor['id'] != 1): ?>
                <div class="modal fade" id="modalEliminar<?= (int)$supervisor['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="/proyecto/supervisores/eliminar/<?= (int)$supervisor['id'] ?>" method="POST">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title"><i class="fas fa-trash"></i> Eliminar Supervisor</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Eliminar a <strong><?= isset($supervisor['nombre']) ? htmlspecialchars($supervisor['nombre']) : '' ?></strong>?</p>
                                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <strong>¡Advertencia!</strong> Esta acción no se puede deshacer.</div>
                                    <div class="alert alert-warning"><i class="fas fa-info-circle"></i> Solo se puede eliminar si no tiene órdenes asignadas.</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(function(alert) {
                    var closeBtn = alert.querySelector('.close');
                    if (closeBtn) closeBtn.click();
                });
            }, 5000);
        });

        <?php if (isset($supervisores) && is_array($supervisores) && !empty($supervisores)): ?>
            <?php foreach ($supervisores as $supervisor): ?>
                <?php if (!isset($supervisor['id'])) continue; ?>
                document.getElementById('formPassword<?= (int)$supervisor['id'] ?>')?.addEventListener('submit', function(e) {
                    var password = document.getElementById('password_<?= (int)$supervisor['id'] ?>').value;
                    var confirmar = document.getElementById('confirmar_password_<?= (int)$supervisor['id'] ?>').value;
                    if (password !== confirmar) {
                        e.preventDefault();
                        alert('Las contraseñas no coinciden.');
                        return false;
                    }
                    if (password.length < 6) {
                        e.preventDefault();
                        alert('La contraseña debe tener al menos 6 caracteres.');
                        return false;
                    }
                });
            <?php endforeach; ?>
        <?php endif; ?>
    </script>
</body>
</html>