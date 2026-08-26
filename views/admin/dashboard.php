<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) : 'Dashboard - PROYECTO' ?></title>
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
        .stats-card.pendientes { border-left-color: #ffc107; }
        .stats-card.pendientes .number { color: #ffc107; }
        .stats-card.en_proceso { border-left-color: #17a2b8; }
        .stats-card.en_proceso .number { color: #17a2b8; }
        .stats-card.cerradas { border-left-color: #28a745; }
        .stats-card.cerradas .number { color: #28a745; }
        .stats-card.eficiencia { border-left-color: #6f42c1; }
        .stats-card.eficiencia .number { color: #6f42c1; }
        .card { border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); border: none; }
        .card-header { background: #f8f9fa; border-bottom: 1px solid #e9ecef; font-weight: 600; }
        .table th { background: #f8f9fa; }
        .badge { padding: 5px 10px; border-radius: 20px; }
        @media (max-width: 768px) {
            .stats-card { margin-bottom: 15px; }
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .menu-item {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: #2c3e50;
            border: 1px solid #e9ecef;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
            text-decoration: none;
            color: #2c3e50;
        }
        .menu-item i {
            font-size: 36px;
            display: block;
            margin-bottom: 10px;
        }
        .menu-item .title {
            font-weight: 600;
            font-size: 16px;
        }
        .menu-item .desc {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .menu-item.primary i { color: #007bff; }
        .menu-item.success i { color: #28a745; }
        .menu-item.warning i { color: #ffc107; }
        .menu-item.danger i { color: #dc3545; }
        .menu-item.info i { color: #17a2b8; }
        .menu-item.purple i { color: #6f42c1; }
    </style>
</head>
<body>
    <?php include_once 'views/layouts/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'views/layouts/sidebar.php'; ?>
            
            <div class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-tachometer-alt"></i> Dashboard Administrativo</h1>
                    <span>Bienvenido, <?= $_SESSION['nombre'] ?? 'Administrador' ?></span>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card total">
                            <span class="icon"><i class="fas fa-clipboard-list"></i></span>
                            <div class="number"><?= $total_ordenes ?? 0 ?></div>
                            <div class="label">Total Órdenes</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card pendientes">
                            <span class="icon"><i class="fas fa-clock"></i></span>
                            <div class="number"><?= $pendientes ?? 0 ?></div>
                            <div class="label">Pendientes</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card en_proceso">
                            <span class="icon"><i class="fas fa-spinner"></i></span>
                            <div class="number"><?= $en_proceso ?? 0 ?></div>
                            <div class="label">En Proceso</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card cerradas">
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div class="number"><?= $cerradas ?? 0 ?></div>
                            <div class="label">Cerradas</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <span class="icon"><i class="fas fa-users"></i></span>
                            <div class="number" style="color: #17a2b8;"><?= $total_tecnicos ?? 0 ?></div>
                            <div class="label">Técnicos Activos</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <span class="icon"><i class="fas fa-user-tie"></i></span>
                            <div class="number" style="color: #6f42c1;"><?= $total_supervisores ?? 0 ?></div>
                            <div class="label">Supervisores Activos</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card eficiencia">
                            <span class="icon"><i class="fas fa-chart-line"></i></span>
                            <div class="number"><?= $eficiencia ?? 0 ?>%</div>
                            <div class="label">Eficiencia</div>
                        </div>
                    </div>
                </div>

                <!-- Menú rápido -->
                <div class="menu-grid">
                    <a href="/proyecto/ordenes" class="menu-item primary">
                        <i class="fas fa-clipboard-list"></i>
                        <div class="title">Órdenes</div>
                        <div class="desc">Gestionar órdenes de mantenimiento</div>
                    </a>
                    <a href="/proyecto/tecnicos" class="menu-item success">
                        <i class="fas fa-users"></i>
                        <div class="title">Técnicos</div>
                        <div class="desc">Gestionar técnicos</div>
                    </a>
                    <a href="/proyecto/supervisores" class="menu-item warning">
                        <i class="fas fa-user-tie"></i>
                        <div class="title">Supervisores</div>
                        <div class="desc">Gestionar supervisores</div>
                    </a>
                    <a href="/proyecto/inventario" class="menu-item info">
                        <i class="fas fa-boxes"></i>
                        <div class="title">Inventario</div>
                        <div class="desc">Gestionar repuestos</div>
                    </a>
                    <a href="/proyecto/reportes" class="menu-item purple">
                        <i class="fas fa-chart-bar"></i>
                        <div class="title">Reportes</div>
                        <div class="desc">Ver reportes y estadísticas</div>
                    </a>
                    <a href="/proyecto/supervision" class="menu-item danger">
                        <i class="fas fa-eye"></i>
                        <div class="title">Supervisión</div>
                        <div class="desc">Supervisar órdenes</div>
                    </a>
                </div>

                <!-- Órdenes recientes -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header"><i class="fas fa-list"></i> Órdenes Recientes</div>
                            <div class="card-body">
                                <?php if (!empty($ordenes_recientes)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>N° Orden</th>
                                                    <th>Título</th>
                                                    <th>Estado</th>
                                                    <th>Fecha</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ordenes_recientes as $orden): ?>
                                                    <tr>
                                                        <td><?= $orden['num_om'] ?? 'N/A' ?></td>
                                                        <td><?= htmlspecialchars($orden['titulo'] ?? '') ?></td>
                                                        <td>
                                                            <span class="badge badge-<?= $orden['status'] == 'CERRADA' ? 'success' : ($orden['status'] == 'EN_PROCESO' ? 'info' : 'warning') ?>">
                                                                <?= $orden['status'] ?? 'PENDIENTE' ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('d/m/Y', strtotime($orden['fecha_creacion'])) ?></td>
                                                        <td>
                                                            <a href="/proyecto/ordenes/ver/<?= $orden['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No hay órdenes recientes</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>