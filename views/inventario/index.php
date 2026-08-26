<?php
// views/inventario/index.php
// Ubicación: C:\xampp\htdocs\proyecto\views\inventario\index.php

// ✅ Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/auth/login');
    exit();
}

$titulo = "Inventario";
$seccion = "inventario";
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Inventario General</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/proyecto/inventario/crear" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Agregar
                    </a>
                </div>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="filtrosForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Nombre, código, categoría...">
                        </div>
                        <div class="col-md-2">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Todos</option>
                                <option value="repuesto">Repuesto</option>
                                <option value="equipo">Equipo</option>
                                <option value="herramienta">Herramienta</option>
                                <option value="insumo">Insumo</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Todas</option>
                                <option value="mecanica">Mecánica</option>
                                <option value="electrica">Eléctrica</option>
                                <option value="electronica">Electrónica</option>
                                <option value="hidraulica">Hidráulica</option>
                                <option value="seguridad">Seguridad</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="stock" class="form-label">Stock</label>
                            <select class="form-select" id="stock" name="stock">
                                <option value="">Todos</option>
                                <option value="bajo">Bajo (<=5)</option>
                                <option value="medio">Medio (6-20)</option>
                                <option value="alto">Alto (>20)</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de inventario -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaInventario">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="inventarioBody">
                                <!-- Cargado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="paginacion"></div>
                        <div>
                            <span id="totalRegistros">Mostrando 0 registros</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    let paginaActual = 1;
    const porPagina = 15;

    function cargarInventario(page = 1) {
        paginaActual = page;
        const formData = new FormData(document.getElementById('filtrosForm'));
        const params = new URLSearchParams(formData);
        params.append('page', page);
        params.append('limit', porPagina);

        fetch(`/proyecto/inventario/list?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('inventarioBody');
                tbody.innerHTML = '';

                if (data.items && data.items.length > 0) {
                    data.items.forEach((item, index) => {
                        const tr = document.createElement('tr');
                        const stockClass = item.cantidad <= 5 ? 'text-danger' : 
                                          item.cantidad <= 20 ? 'text-warning' : 'text-success';
                        
                        tr.innerHTML = `
                            <td>${((page - 1) * porPagina) + index + 1}</td>
                            <td>
                                ${item.imagen ? 
                                    `<img src="/proyecto/uploads/inventario/${item.imagen}" 
                                         alt="${item.nombre}" style="height: 40px; width: 40px; object-fit: cover;" class="rounded">` : 
                                    `<div class="bg-secondary rounded" style="height: 40px; width: 40px;"></div>`
                                }
                            </td>
                            <td>${item.nombre}</td>
                            <td><span class="badge bg-info">${item.tipo}</span></td>
                            <td>${item.categoria}</td>
                            <td class="${stockClass} fw-bold">${item.cantidad}</td>
                            <td>${item.precio_unitario ? '$' + parseFloat(item.precio_unitario).toFixed(2) : 'N/A'}</td>
                            <td>${item.ubicacion || 'N/A'}</td>
                            <td>
                                <span class="badge ${item.activo ? 'bg-success' : 'bg-danger'}">
                                    ${item.activo ? 'Activo' : 'Inactivo'}
                                </span>
                            </td>
                            <td>
                                <a href="/proyecto/inventario/editar/${item.id}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="eliminarItem(${item.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    actualizarPaginacion(data.total, data.paginas);
                    document.getElementById('totalRegistros').textContent = 
                        `Mostrando ${data.items.length} de ${data.total} registros`;
                } else {
                    tbody.innerHTML = `<tr><td colspan="10" class="text-center">No hay elementos en el inventario</td></tr>`;
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
        ul.className = 'pagination pagination-sm';
        
        for (let i = 1; i <= paginas; i++) {
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
        
        div.appendChild(ul);
    }

    function eliminarItem(id) {
        if (confirm('¿Estás seguro de eliminar este elemento del inventario?')) {
            fetch(`/proyecto/inventario/eliminar/${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarInventario(paginaActual);
                } else {
                    alert('Error al eliminar el elemento');
                }
            });
        }
    }

    document.getElementById('filtrosForm').addEventListener('submit', function(e) {
        e.preventDefault();
        cargarInventario(1);
    });

    document.getElementById('filtrosForm').addEventListener('reset', function(e) {
        e.preventDefault();
        this.querySelectorAll('select, input').forEach(el => el.value = '');
        cargarInventario(1);
    });

    document.addEventListener('DOMContentLoaded', function() {
        cargarInventario(1);
    });
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>