<?php
// views/supervisor/ordenes.php
// Órdenes para Revisar - VERSIÓN COMPLETA

// ✅ Verificar que las variables existan
if (!isset($seccion)) {
    $seccion = 'supervisor';
}
if (!isset($titulo)) {
    $titulo = 'Órdenes para Revisar';
}

include_once __DIR__ . '/../layouts/header.php';
// ❌ NO incluir sidebar aquí (ya está en header)
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-clipboard-list text-primary me-2"></i>Órdenes para Revisar
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Revisa y evalúa las órdenes de trabajo
            </p>
        </div>
        <a href="/proyecto/supervisor" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver al Panel
        </a>
    </div>

    <!-- ✅ Filtros -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form id="filtrosForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="estado" class="form-label fw-semibold small">Estado</label>
                    <select class="form-select form-select-sm" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="EN_PROCESO">En Progreso</option>
                        <option value="CERRADA">Completada</option>
                        <option value="CANCELADA">Cancelada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="prioridad" class="form-label fw-semibold small">Prioridad</label>
                    <select class="form-select form-select-sm" id="prioridad" name="prioridad">
                        <option value="">Todas</option>
                        <option value="Baja">Baja</option>
                        <option value="Media">Media</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">Urgente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tecnico" class="form-label fw-semibold small">Técnico</label>
                    <select class="form-select form-select-sm" id="tecnico" name="tecnico">
                        <option value="">Todos</option>
                        <!-- Cargado por AJAX -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha" class="form-label fw-semibold small">Fecha</label>
                    <input type="date" class="form-control form-control-sm" id="fecha" name="fecha">
                </div>
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                        <button type="reset" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo me-1"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Tabla de órdenes -->
    <div class="card border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Técnico</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Supervisión</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ordenesBody">
                        <!-- Cargado por AJAX -->
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div id="paginacion"></div>
                <div class="text-muted small">
                    <i class="fas fa-list me-1"></i> <span id="totalRegistros">Mostrando 0 registros</span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ✅ Estilos -->
<style>
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.badge-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
}
</style>

<script>
let paginaActual = 1;
const porPagina = 10;

function cargarTecnicos() {
    fetch('/proyecto/supervisor/tecnicosList')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('tecnico');
            data.forEach(tecnico => {
                const option = document.createElement('option');
                option.value = tecnico.id;
                option.textContent = tecnico.nombre;
                select.appendChild(option);
            });
        });
}

function cargarOrdenes(page = 1) {
    paginaActual = page;
    const formData = new FormData(document.getElementById('filtrosForm'));
    const params = new URLSearchParams(formData);
    params.append('page', page);
    params.append('limit', porPagina);

    fetch(`/proyecto/supervisor/ordenesList?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('ordenesBody');
            tbody.innerHTML = '';

            if (data.ordenes && data.ordenes.length > 0) {
                data.ordenes.forEach(orden => {
                    const prioridadColor = {
                        'Baja': 'success',
                        'Media': 'info',
                        'Alta': 'warning',
                        'Urgente': 'danger'
                    }[orden.prioridad] || 'secondary';

                    const estadoColor = {
                        'PENDIENTE': 'warning',
                        'EN_PROCESO': 'info',
                        'EJECUTADA': 'primary',
                        'CERRADA': 'success',
                        'APROBADA': 'success',
                        'CANCELADA': 'danger',
                        'RECHAZADA': 'danger'
                    }[orden.estado] || 'secondary';

                    const supervisionColor = {
                        'APROBADA': 'success',
                        'RECHAZADA': 'danger',
                        'PENDIENTE': 'warning'
                    }[orden.supervision_estado] || 'secondary';
                    
                    const supervisionBadge = orden.supervision_estado ? 
                        `<span class="badge-status bg-${supervisionColor} bg-opacity-10 text-${supervisionColor}">
                            <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                            ${orden.supervision_estado}
                        </span>` : 
                        `<span class="badge-status bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                            Sin revisar
                        </span>`;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><span class="fw-semibold">#${orden.id}</span></td>
                        <td>${orden.titulo || 'Sin título'}</td>
                        <td>${orden.tecnico || 'Sin asignar'}</td>
                        <td>
                            <span class="badge bg-${prioridadColor} bg-opacity-10 text-${prioridadColor}">
                                ${orden.prioridad || 'Media'}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status bg-${estadoColor} bg-opacity-10 text-${estadoColor}">
                                <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                ${orden.estado || 'PENDIENTE'}
                            </span>
                        </td>
                        <td><small>${orden.fecha_creacion}</small></td>
                        <td>${supervisionBadge}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <a href="/proyecto/supervisor/ver_orden/${orden.id}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                ${orden.estado === 'CERRADA' && orden.supervision_estado !== 'APROBADA' ? 
                                    `<a href="/proyecto/supervisor/revisar/${orden.id}" class="btn btn-sm btn-primary" title="Revisar">
                                        <i class="fas fa-clipboard-check"></i>
                                    </a>` : ''}
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                actualizarPaginacion(data.total, data.paginas);
                document.getElementById('totalRegistros').textContent = 
                    `Mostrando ${data.ordenes.length} de ${data.total} registros`;
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                    No hay órdenes registradas
                </td></tr>`;
                document.getElementById('totalRegistros').textContent = 'Mostrando 0 registros';
            }
        })
        .catch(error => console.error('Error:', error));
}

function actualizarPaginacion(total, paginas) {
    const div = document.getElementById('paginacion');
    div.innerHTML = '';
    
    if (paginas <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0';
    
    if (paginaActual > 1) {
        const li = document.createElement('li');
        li.className = 'page-item';
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.innerHTML = '&laquo;';
        a.onclick = (e) => {
            e.preventDefault();
            cargarOrdenes(paginaActual - 1);
        };
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    for (let i = 1; i <= paginas; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.onclick = (e) => {
            e.preventDefault();
            cargarOrdenes(i);
        };
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    if (paginaActual < paginas) {
        const li = document.createElement('li');
        li.className = 'page-item';
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.innerHTML = '&raquo;';
        a.onclick = (e) => {
            e.preventDefault();
            cargarOrdenes(paginaActual + 1);
        };
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    div.appendChild(ul);
}

document.getElementById('filtrosForm').addEventListener('submit', function(e) {
    e.preventDefault();
    cargarOrdenes(1);
});

document.getElementById('filtrosForm').addEventListener('reset', function(e) {
    e.preventDefault();
    this.querySelectorAll('select, input').forEach(el => el.value = '');
    cargarOrdenes(1);
});

document.addEventListener('DOMContentLoaded', function() {
    cargarTecnicos();
    cargarOrdenes(1);
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>