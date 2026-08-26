<?php
// views/reportes/index.php
// Ubicación: C:\xampp\htdocs\proyecto\views\reportes\index.php

// ✅ ELIMINAR session_start() - ya está iniciada en el router principal
// session_start(); // ❌ ELIMINAR ESTA LÍNEA

// Verificar autenticación y rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Reportes";
$seccion = "reportes";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Panel de Reportes</h1>
            </div>

            <div class="row">
                <!-- Reporte de Órdenes -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-list-task"></i> Reporte de Órdenes</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Genera reportes detallados de todas las órdenes de trabajo con filtros por fecha, estado, prioridad y técnico.</p>
                            <ul class="list-unstyled">
                                <li>✅ Resumen de órdenes</li>
                                <li>✅ Detalle por estado</li>
                                <li>✅ Análisis de tiempos</li>
                                <li>✅ Productividad por técnico</li>
                            </ul>
                            <a href="/proyecto/reportes/ordenes" class="btn btn-primary">
                                <i class="bi bi-file-earmark-text"></i> Generar Reporte
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Técnicos -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-people"></i> Reporte de Técnicos</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Analiza el rendimiento y productividad de los técnicos, con métricas de órdenes completadas y tiempos promedio.</p>
                            <ul class="list-unstyled">
                                <li>✅ Órdenes por técnico</li>
                                <li>✅ Tiempo promedio por orden</li>
                                <li>✅ Eficiencia y rendimiento</li>
                                <li>✅ Ranking de técnicos</li>
                            </ul>
                            <a href="/proyecto/reportes/tecnicos" class="btn btn-success">
                                <i class="bi bi-file-earmark-text"></i> Generar Reporte
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Inventario -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-boxes"></i> Reporte de Inventario</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Visualiza el estado del inventario, incluyendo niveles de stock, valoración y rotación de productos.</p>
                            <ul class="list-unstyled">
                                <li>✅ Stock actual</li>
                                <li>✅ Valoración de inventario</li>
                                <li>✅ Productos con bajo stock</li>
                                <li>✅ Movimientos de inventario</li>
                            </ul>
                            <a href="/proyecto/reportes/inventario" class="btn btn-warning">
                                <i class="bi bi-file-earmark-text"></i> Generar Reporte
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Supervisión -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Reporte de Supervisión</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Monitorea la supervisión de órdenes, incluyendo cumplimiento, calidad y observaciones de los supervisores.</p>
                            <ul class="list-unstyled">
                                <li>✅ Órdenes supervisadas</li>
                                <li>✅ Calidad del trabajo</li>
                                <li>✅ Observaciones</li>
                                <li>✅ Cumplimiento de estándares</li>
                            </ul>
                            <a href="/proyecto/reportes/supervision" class="btn btn-info">
                                <i class="bi bi-file-earmark-text"></i> Generar Reporte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>