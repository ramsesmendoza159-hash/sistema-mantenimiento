<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'tecnico') {
    header('Location: /produmar/auth/login');
    exit();
}

$titulo = "Panel de Técnico";
$seccion = "tecnico";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Panel de Técnico</h1>
                <a href="/produmar/tecnico/mis_ordenes" class="btn btn-primary">
                    <i class="bi bi-list-task"></i> Mis Órdenes
                </a>
            </div>

            <!-- Cards de resumen -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Asignadas</h5>
                            <p class="card-text display-6" id="total">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pendientes</h5>
                            <p class="card-text display-6" id="pendientes">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">En Progreso</h5>
                            <p class="card-text display-6" id="en_progreso">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Completadas</h5>
                            <p class="card-text display-6" id="completadas">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimas órdenes asignadas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Últimas Órdenes Asignadas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
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
        fetch('/produmar/tecnico/dashboardData')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total').textContent = data.total || 0;
                document.getElementById('pendientes').textContent = data.pendientes || 0;
                document.getElementById('en_progreso').textContent = data.en_progreso || 0;
                document.getElementById('completadas').textContent = data.completadas || 0;

                const tbody = document.getElementById('ordenesBody');
                tbody.innerHTML = '';
                
                if (data.ordenes && data.ordenes.length > 0) {
                    data.ordenes.forEach(orden => {
                        const tr = document.createElement('tr');
                        const prioridadClass = {
                            'baja': 'info',
                            'media': 'warning',
                            'alta': 'danger',
                            'urgente': 'danger'
                        }[orden.prioridad] || 'secondary';

                        const estadoClass = {
                            'pendiente': 'warning',
                            'en_progreso': 'info',
                            'completada': 'success',
                            'cancelada': 'danger'
                        }[orden.estado] || 'secondary';

                        tr.innerHTML = `
                            <td>${orden.id}</td>
                            <td>${orden.titulo}</td>
                            <td><span class="badge bg-${prioridadClass}">${orden.prioridad}</span></td>
                            <td><span class="badge bg-${estadoClass}">${orden.estado}</span></td>
                            <td>${orden.fecha_creacion}</td>
                            <td>
                                <a href="/produmar/tecnico/detalle_orden/${orden.id}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                ${orden.estado !== 'completada' && orden.estado !== 'cancelada' ? 
                                    `<a href="/produmar/tecnico/cerrar_orden/${orden.id}" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i>
                                    </a>` : ''}
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center">No hay órdenes asignadas</td></tr>`;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    document.addEventListener('DOMContentLoaded', cargarDashboard);
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>