<?php
// views/supervisor/index.php
// Ubicación: C:\xampp\htdocs\proyecto\views\supervisor\index.php

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'supervisor') {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Panel de Supervisor";
$seccion = "supervisor";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Panel de Supervisor</h1>
                <div>
                    <a href="/proyecto/supervisor/ordenes" class="btn btn-primary me-2">
                        <i class="bi bi-list-task"></i> Ver Órdenes
                    </a>
                    <a href="/proyecto/supervisor/supervisiones" class="btn btn-info">
                        <i class="bi bi-clipboard-check"></i> Supervisiones
                    </a>
                </div>
            </div>

            <!-- Cards de resumen -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Órdenes</h5>
                            <p class="card-text display-6" id="total_ordenes">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pendientes Revisión</h5>
                            <p class="card-text display-6" id="pendientes_revision">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Aprobadas</h5>
                            <p class="card-text display-6" id="aprobadas">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">Rechazadas</h5>
                            <p class="card-text display-6" id="rechazadas">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimas órdenes para revisar -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Órdenes Pendientes de Revisión</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Técnico</th>
                                    <th>Prioridad</th>
                                    <th>Fecha Cierre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="ordenesBody">
                                <!-- Cargado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    function cargarDashboard() {
        fetch('/proyecto/supervisor/dashboardData')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total_ordenes').textContent = data.total_ordenes || 0;
                document.getElementById('pendientes_revision').textContent = data.pendientes_revision || 0;
                document.getElementById('aprobadas').textContent = data.aprobadas || 0;
                document.getElementById('rechazadas').textContent = data.rechazadas || 0;

                const tbody = document.getElementById('ordenesBody');
                tbody.innerHTML = '';
                
                if (data.ordenes && data.ordenes.length > 0) {
                    data.ordenes.forEach(orden => {
                        const tr = document.createElement('tr');
                        const prioridadClass = {
                            'Baja': 'info',
                            'Media': 'warning',
                            'Alta': 'danger',
                            'Urgente': 'danger'
                        }[orden.prioridad] || 'secondary';

                        tr.innerHTML = `
                            <td>${orden.id}</td>
                            <td>${orden.titulo}</td>
                            <td>${orden.tecnico || 'Sin asignar'}</td>
                            <td><span class="badge bg-${prioridadClass}">${orden.prioridad}</span></td>
                            <td>${orden.fecha_cierre || 'N/A'}</td>
                            <td>
                                <a href="/proyecto/supervisor/revisar/${orden.id}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Revisar
                                </a>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center">No hay órdenes pendientes de revisión</td></tr>`;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    document.addEventListener('DOMContentLoaded', cargarDashboard);
    
    // Recargar cada 30 segundos
    setInterval(cargarDashboard, 30000);
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>