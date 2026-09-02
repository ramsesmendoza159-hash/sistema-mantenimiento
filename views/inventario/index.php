<?php
// views/inventario/index.php
// Inventario - VERSIÓN MEJORADA CON DISEÑO ESTÉTICO

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../helpers/SecurityHelper.php';

if (!SecurityHelper::verificarSesion()) {
    header('Location: /proyecto/auth/login');
    exit;
}

if (!SecurityHelper::verificarRol('admin')) {
    $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
    header('Location: /proyecto/dashboard');
    exit;
}

$titulo = "Inventario";
$seccion = "inventario";

$items = $items ?? [];
$estadisticas = $estadisticas ?? [
    'total' => 0,
    'total_stock' => 0,
    'precio_promedio' => 0,
    'valor_total' => 0,
    'agotados' => 0,
    'stock_bajo' => 0
];
$filtros = $filtros ?? [];

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid px-0">

    <!-- ✅ Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-boxes text-primary me-2"></i>Inventario General
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y H:i') ?>
                <span class="mx-2">|</span>
                <i class="fas fa-list me-1"></i> <?= $estadisticas['total'] ?? 0 ?> ítems registrados
            </p>
        </div>
        <a href="/proyecto/inventario/crear" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Agregar
        </a>
    </div>

    <!-- ✅ Tarjetas de Estadísticas - Diseño mejorado -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-label-modern">Total Items</div>
                        <div class="stat-number-modern"><?= number_format($estadisticas['total'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #198754, #20c997);">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-label-modern">Stock Total</div>
                        <div class="stat-number-modern"><?= number_format($estadisticas['total_stock'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-label-modern">Valor Total</div>
                        <div class="stat-number-modern">S/ <?= number_format($estadisticas['valor_total'] ?? 0, 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-label-modern">Precio Promedio</div>
                        <div class="stat-number-modern">S/ <?= number_format($estadisticas['precio_promedio'] ?? 0, 2) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Segunda fila de tarjetas -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #dc3545, #ff6b6b);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-label-modern">Stock Bajo</div>
                        <div class="stat-number-modern"><?= number_format($estadisticas['stock_bajo'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #6c757d, #adb5bd);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-label-modern">Agotados</div>
                        <div class="stat-number-modern"><?= number_format($estadisticas['agotados'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Filtros -->
    <div class="card border-0 mb-4 shadow-sm">
        <div class="card-body">
            <form id="filtrosForm" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="search" name="search" 
                               placeholder="Buscar por nombre, código...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Tipo</option>
                        <option value="repuesto">Repuesto</option>
                        <option value="equipo">Equipo</option>
                        <option value="herramienta">Herramienta</option>
                        <option value="insumo">Insumo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Categoría</option>
                        <option value="mecanica">Mecánica</option>
                        <option value="electrica">Eléctrica</option>
                        <option value="electronica">Electrónica</option>
                        <option value="hidraulica">Hidráulica</option>
                        <option value="seguridad">Seguridad</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="stock" name="stock">
                        <option value="">Stock</option>
                        <option value="bajo">Bajo (≤5)</option>
                        <option value="medio">Medio (6-20)</option>
                        <option value="alto">Alto (>20)</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-50">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <button type="reset" class="btn btn-secondary w-50" id="btnLimpiar">
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Tabla de inventario -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaInventario">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;">#</th>
                            <th style="width:60px;">Imagen</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Categoría</th>
                            <th style="width:80px;">Stock</th>
                            <th style="width:100px;">Precio</th>
                            <th>Ubicación</th>
                            <th style="width:100px;">Estado</th>
                            <th style="width:110px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="inventarioBody">
                        <!-- Cargado por AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
            <div id="paginacion"></div>
            <div>
                <span class="text-muted small" id="totalRegistros">Cargando...</span>
            </div>
        </div>
    </div>

</div>

<!-- ✅ Estilos mejorados -->
<style>
.stat-card-modern {
    background: #fff;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid rgba(0,0,0,0.04);
}
.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}
.stat-card-modern .stat-icon-modern {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: #fff;
    flex-shrink: 0;
}
.stat-card-modern .stat-label-modern {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    font-weight: 600;
}
.stat-card-modern .stat-number-modern {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a1a2e;
    line-height: 1.2;
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.table th {
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    background: #f8f9fa;
}
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
}
.badge-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.7rem;
    display: inline-flex;
    align-items: center;
}
</style>

<!-- ✅ Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarInventario(1);
});

let paginaActual = 1;
const porPagina = 100;

function cargarInventario(page = 1) {
    paginaActual = page;
    const form = document.getElementById('filtrosForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('page', page);
    params.append('limit', porPagina);

    document.getElementById('inventarioBody').innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted small">Cargando inventario...</p>
            </td>
        </tr>
    `;

    fetch(`/proyecto/inventario/list?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('inventarioBody');
            tbody.innerHTML = '';

            if (data.items && data.items.length > 0) {
                data.items.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    const stockClass = item.cantidad <= 5 ? 'text-danger' : 
                                      item.cantidad <= 20 ? 'text-warning' : 'text-success';
                    
                    tr.innerHTML = `
                        <td><span class="fw-semibold">${((page - 1) * porPagina) + index + 1}</span></td>
                        <td>
                            ${item.imagen ? 
                                `<img src="/proyecto/uploads/inventario/${item.imagen}" 
                                     alt="${item.nombre}" style="height: 40px; width: 40px; object-fit: cover;" class="rounded">` : 
                                `<div class="bg-secondary bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="height: 40px; width: 40px;">
                                    <i class="fas fa-box text-secondary"></i>
                                </div>`
                            }
                        </td>
                        <td><strong>${item.nombre || 'N/A'}</strong></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info">${item.tipo || 'N/A'}</span></td>
                        <td>${item.categoria || 'N/A'}</td>
                        <td class="fw-bold ${stockClass}">${item.cantidad}</td>
                        <td>S/ ${parseFloat(item.precio_unitario || 0).toFixed(2)}</td>
                        <td>${item.ubicacion || 'N/A'}</td>
                        <td>
                            <span class="badge-status ${item.activo ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'}">
                                <i class="fas fa-circle me-1" style="font-size:6px;"></i>
                                ${item.activo ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/proyecto/inventario/editar/${item.id}" class="btn btn-outline-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger" onclick="eliminarItem(${item.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                actualizarPaginacion(data.total, data.paginas);
                document.getElementById('totalRegistros').textContent = 
                    `Mostrando ${data.items.length} de ${data.total} registros`;
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <h6>No hay elementos en el inventario</h6>
                            <p class="text-muted small">Agrega tu primer ítem haciendo clic en "Agregar"</p>
                            <a href="/proyecto/inventario/crear" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus-circle me-1"></i> Agregar Ítem
                            </a>
                        </td>
                    </tr>
                `;
                document.getElementById('totalRegistros').textContent = 'Mostrando 0 registros';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('inventarioBody').innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Error al cargar los datos. Recarga la página.</p>
                        <button class="btn btn-primary btn-sm mt-2" onclick="cargarInventario(1)">
                            <i class="fas fa-sync me-1"></i> Reintentar
                        </button>
                    </td>
                </tr>
            `;
            document.getElementById('totalRegistros').textContent = 'Error al cargar';
        });
}

function actualizarPaginacion(total, paginas) {
    const div = document.getElementById('paginacion');
    div.innerHTML = '';
    
    if (paginas <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0';
    
    const liPrev = document.createElement('li');
    liPrev.className = `page-item ${paginaActual <= 1 ? 'disabled' : ''}`;
    const aPrev = document.createElement('a');
    aPrev.className = 'page-link';
    aPrev.href = '#';
    aPrev.textContent = '‹';
    aPrev.onclick = (e) => {
        e.preventDefault();
        if (paginaActual > 1) cargarInventario(paginaActual - 1);
    };
    liPrev.appendChild(aPrev);
    ul.appendChild(liPrev);
    
    let startPage = Math.max(1, paginaActual - 2);
    let endPage = Math.min(paginas, paginaActual + 2);
    
    if (startPage > 1) {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        const a = document.createElement('a');
        a.className = 'page-link';
        a.textContent = '…';
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.onclick = (e) => {
            e.preventDefault();
            cargarInventario(i);
        };
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    if (endPage < paginas) {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        const a = document.createElement('a');
        a.className = 'page-link';
        a.textContent = '…';
        li.appendChild(a);
        ul.appendChild(li);
    }
    
    const liNext = document.createElement('li');
    liNext.className = `page-item ${paginaActual >= paginas ? 'disabled' : ''}`;
    const aNext = document.createElement('a');
    aNext.className = 'page-link';
    aNext.href = '#';
    aNext.textContent = '›';
    aNext.onclick = (e) => {
        e.preventDefault();
        if (paginaActual < paginas) cargarInventario(paginaActual + 1);
    };
    liNext.appendChild(aNext);
    ul.appendChild(liNext);
    
    div.appendChild(ul);
}

function eliminarItem(id) {
    if (confirm('¿Estás seguro de eliminar este elemento del inventario?')) {
        fetch(`/proyecto/inventario/eliminar/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=<?= SecurityHelper::generateCSRFToken() ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarInventario(paginaActual);
            } else {
                alert('Error al eliminar el elemento: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            alert('Error al eliminar el elemento');
            console.error('Error:', error);
        });
    }
}

document.getElementById('filtrosForm').addEventListener('submit', function(e) {
    e.preventDefault();
    cargarInventario(1);
});

document.getElementById('btnLimpiar').addEventListener('click', function(e) {
    e.preventDefault();
    const form = this.closest('form');
    form.querySelectorAll('select, input').forEach(el => el.value = '');
    cargarInventario(1);
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>