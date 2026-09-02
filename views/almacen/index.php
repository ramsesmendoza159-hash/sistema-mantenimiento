<?php
// views/almacen/index.php
// Panel de Almacén - VERSIÓN COMPLETA

if (!isset($seccion)) {
    $seccion = 'almacen';
}
if (!isset($titulo)) {
    $titulo = 'Panel de Almacén';
}
if (!isset($estadisticas)) {
    $estadisticas = ['total' => 0, 'valor_total' => 0];
}
if (!isset($stock_bajo)) {
    $stock_bajo = [];
}
if (!isset($ultimos_movimientos)) {
    $ultimos_movimientos = [];
}

include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-boxes text-primary me-2"></i>Panel de Almacén
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle me-1"></i> Gestión de inventario y repuestos
            </p>
        </div>
        <a href="/proyecto/inventario/crear" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Item
        </a>
    </div>

    <!-- ✅ Tarjetas de estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Total Items</div>
                        <div class="stat-number fw-bold" id="total_items"><?php echo $estadisticas['total'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(13,110,253,0.1);color:#0d6efd;">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Stock Bajo</div>
                        <div class="stat-number fw-bold" id="stock_bajo"><?php echo count($stock_bajo); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(220,53,69,0.1);color:#dc3545;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Valor Total</div>
                        <div class="stat-number fw-bold">S/ <?php echo number_format($estadisticas['valor_total'] ?? 0, 2); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(25,135,84,0.1);color:#198754;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card border-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label text-muted text-uppercase small fw-semibold">Movimientos</div>
                        <div class="stat-number fw-bold" id="movimientos"><?php echo count($ultimos_movimientos); ?></div>
                    </div>
                    <div class="stat-icon" style="background:rgba(13,202,240,0.1);color:#0dcaf0;">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Alertas de stock bajo -->
    <?php if (!empty($stock_bajo)): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡Atención!</strong> Los siguientes productos tienen stock bajo:
            <ul class="mb-0 mt-2">
                <?php foreach ($stock_bajo as $item): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($item['nombre']); ?></strong>
                        - Stock actual: <?php echo $item['cantidad']; ?> unidades
                        (Mínimo: <?php echo $item['stock_minimo']; ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- ✅ Últimos movimientos -->
    <div class="card border-0">
        <div class="card-header bg-transparent border-0 pt-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-clock text-info me-2"></i> Últimos Movimientos
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimos_movimientos)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay movimientos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimos_movimientos as $movimiento): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($movimiento['nombre_producto']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $movimiento['tipo'] === 'entrada' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($movimiento['tipo']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $movimiento['cantidad']; ?></td>
                                    <td><?php echo htmlspecialchars($movimiento['usuario']); ?></td>
                                    <td><small><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ✅ Estilos -->
<style>
.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.stat-card .stat-number {
    font-size: 2rem;
    margin: 4px 0 2px;
    color: #1a1a2e;
}
.card {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/proyecto/almacen/dashboardData')
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('total_items').textContent = data.total_items || 0;
                document.getElementById('stock_bajo').textContent = data.stock_bajo || 0;
            }
        })
        .catch(error => console.error('Error:', error));
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>