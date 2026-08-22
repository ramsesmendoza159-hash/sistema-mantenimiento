<?php
// views/layouts/sidebar.php
// Ubicación: C:\xampp\htdocs\produmar\views\layouts\sidebar.php

$rol = $_SESSION['rol'] ?? 'usuario';
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$seccion = $seccion ?? 'dashboard';
?>

<nav class="col-md-2 d-md-block sidebar">
    <div class="brand">
        <h4><i class="fas fa-tools"></i> PRODUMAR</h4>
        <small>Sistema de Mantenimiento</small>
    </div>
    
    <ul class="nav flex-column">
        
        <!-- ========================================== -->
        <!-- ADMIN -->
        <!-- ========================================== -->
        <?php if ($rol === 'admin'): ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'dashboard' ? 'active' : ''; ?>" href="/produmar/admin/dashboard">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'ordenes' ? 'active' : ''; ?>" href="/produmar/admin/gestion_ordenes">
                    <i class="fas fa-clipboard-list"></i> Órdenes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'tecnicos' ? 'active' : ''; ?>" href="/produmar/tecnicos">
                    <i class="fas fa-users-cog"></i> Técnicos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervisores' ? 'active' : ''; ?>" href="/produmar/supervisores">
                    <i class="fas fa-user-tie"></i> Supervisores
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'inventario' ? 'active' : ''; ?>" href="/produmar/inventario">
                    <i class="fas fa-boxes"></i> Inventario
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'reportes' ? 'active' : ''; ?>" href="/produmar/reportes">
                    <i class="fas fa-file-alt"></i> Reportes
                </a>
            </li>
            
            <!-- NUEVO: Reportes Financieros -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'financieros' ? 'active' : ''; ?>" href="/produmar/reportes/financieros">
                    <i class="fas fa-money-bill-wave"></i> Financieros
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervision' ? 'active' : ''; ?>" href="/produmar/supervision">
                    <i class="fas fa-clipboard-check"></i> Supervisión
                </a>
            </li>

        <!-- ========================================== -->
        <!-- SUPERVISOR -->
        <!-- ========================================== -->
        <?php elseif ($rol === 'supervisor'): ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervisor' ? 'active' : ''; ?>" href="/produmar/supervisor">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervision' ? 'active' : ''; ?>" href="/produmar/supervision">
                    <i class="fas fa-clipboard-check"></i> Supervisión
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'ordenes' ? 'active' : ''; ?>" href="/produmar/supervisor/ordenes">
                    <i class="fas fa-list"></i> Mis Órdenes
                </a>
            </li>
            
            <!-- NUEVO: Reportes Financieros -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'financieros' ? 'active' : ''; ?>" href="/produmar/reportes/financieros">
                    <i class="fas fa-money-bill-wave"></i> Financieros
                </a>
            </li>

        <!-- ========================================== -->
        <!-- TÉCNICO -->
        <!-- ========================================== -->
        <?php elseif ($rol === 'tecnico'): ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'tecnico' ? 'active' : ''; ?>" href="/produmar/tecnico">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'mis_ordenes' ? 'active' : ''; ?>" href="/produmar/tecnico/mis_ordenes">
                    <i class="fas fa-clipboard-list"></i> Mis Órdenes
                </a>
            </li>
            
        <?php endif; ?>
        
    </ul>
    
    <!-- Usuario y Cerrar Sesión -->
    <div class="user-info">
        <div class="user-name"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($nombre); ?></div>
        <div class="user-role"><?php echo ucfirst($rol); ?></div>
        <a href="/produmar/auth/logout" class="btn btn-logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</nav>