<?php
// views/layouts/sidebar.php
// VERSIÓN COMPLETA CON TODOS LOS ROLES

$rol = $_SESSION['rol'] ?? 'usuario';
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$seccion = $seccion ?? 'dashboard';
$iniciales = strtoupper(substr($nombre, 0, 1));
?>

<!-- Overlay para móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-tools"></i></div>
        <h4>PROYECTO</h4>
        <small>Sistema de Mantenimiento</small>
    </div>
    
    <ul class="nav flex-column">
        
        <?php if ($rol === 'admin'): ?>
            
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'dashboard' ? 'active' : ''; ?>" href="/proyecto/dashboard">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            
            <div class="nav-section-title">GESTIÓN</div>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'ordenes' ? 'active' : ''; ?>" href="/proyecto/ordenes">
                    <i class="fas fa-clipboard-list"></i> Órdenes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'tecnicos' ? 'active' : ''; ?>" href="/proyecto/tecnicos">
                    <i class="fas fa-users-cog"></i> Técnicos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervisores' ? 'active' : ''; ?>" href="/proyecto/supervisores">
                    <i class="fas fa-user-tie"></i> Supervisores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'inventario' ? 'active' : ''; ?>" href="/proyecto/inventario">
                    <i class="fas fa-boxes"></i> Inventario
                </a>
            </li>
            
            <div class="nav-section-title">REPORTES</div>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'reportes' ? 'active' : ''; ?>" href="/proyecto/reportes">
                    <i class="fas fa-file-alt"></i> Reportes Generales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'financieros' ? 'active' : ''; ?>" href="/proyecto/reportes/financieros">
                    <i class="fas fa-money-bill-wave"></i> Reportes Financieros
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervision' ? 'active' : ''; ?>" href="/proyecto/supervision">
                    <i class="fas fa-clipboard-check"></i> Supervisión
                </a>
            </li>
            
            <!-- PERFIL -->
            <div class="nav-section-title">CUENTA</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'perfil' ? 'active' : ''; ?>" href="/proyecto/perfil">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
            </li>
            
        <?php elseif ($rol === 'supervisor'): ?>
            
            <!-- Dashboard Supervisor -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervisor' ? 'active' : ''; ?>" href="/proyecto/supervisor">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'ordenes' ? 'active' : ''; ?>" href="/proyecto/supervisor/ordenes">
                    <i class="fas fa-list"></i> Órdenes para Revisar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'supervision' ? 'active' : ''; ?>" href="/proyecto/supervision">
                    <i class="fas fa-clipboard-check"></i> Supervisiones
                </a>
            </li>
            
            <div class="nav-section-title">REPORTES</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'financieros' ? 'active' : ''; ?>" href="/proyecto/reportes/financieros">
                    <i class="fas fa-money-bill-wave"></i> Financieros
                </a>
            </li>
            
            <!-- PERFIL -->
            <div class="nav-section-title">CUENTA</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'perfil' ? 'active' : ''; ?>" href="/proyecto/perfil">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
            </li>
            
        <?php elseif ($rol === 'tecnico'): ?>
            
            <!-- Dashboard Técnico -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'tecnico' ? 'active' : ''; ?>" href="/proyecto/tecnico">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'mis_ordenes' ? 'active' : ''; ?>" href="/proyecto/tecnico/mis_ordenes">
                    <i class="fas fa-clipboard-list"></i> Mis Órdenes
                </a>
            </li>
            
            <!-- PERFIL -->
            <div class="nav-section-title">CUENTA</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'perfil' ? 'active' : ''; ?>" href="/proyecto/perfil">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
            </li>
            
        <?php elseif ($rol === 'almacen'): ?>
            
            <!-- ✅ Dashboard Almacén -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'almacen' ? 'active' : ''; ?>" href="/proyecto/almacen">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'inventario' ? 'active' : ''; ?>" href="/proyecto/inventario">
                    <i class="fas fa-boxes"></i> Inventario
                </a>
            </li>
            
            <!-- PERFIL -->
            <div class="nav-section-title">CUENTA</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'perfil' ? 'active' : ''; ?>" href="/proyecto/perfil">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
            </li>
            
        <?php elseif ($rol === 'operador'): ?>
            
            <!-- ✅ Dashboard Operador -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'operador' ? 'active' : ''; ?>" href="/proyecto/operador">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'ordenes' ? 'active' : ''; ?>" href="/proyecto/operador/ordenes">
                    <i class="fas fa-clipboard-list"></i> Mis Órdenes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'crear_orden' ? 'active' : ''; ?>" href="/proyecto/ordenes/crear">
                    <i class="fas fa-plus-circle"></i> Nueva Orden
                </a>
            </li>
            
            <!-- PERFIL -->
            <div class="nav-section-title">CUENTA</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'perfil' ? 'active' : ''; ?>" href="/proyecto/perfil">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
            </li>
            
        <?php elseif ($rol === 'consultor'): ?>
            
            <!-- ✅ Dashboard Consultor -->
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'consultor' ? 'active' : ''; ?>" href="/proyecto/consultor">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'ordenes' ? 'active' : ''; ?>" href="/proyecto/consultor/ordenes">
                    <i class="fas fa-clipboard-list"></i> Ver Órdenes
                </a>
            </li>
            
            <!-- PERFIL -->
            <div class="nav-section-title">CUENTA</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion === 'perfil' ? 'active' : ''; ?>" href="/proyecto/perfil">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
            </li>
            
        <?php endif; ?>
        
    </ul>
    
    <div class="user-info">
        <div class="user-details">
            <span class="user-avatar"><?= $iniciales ?></span>
            <div>
                <span class="user-name"><?= htmlspecialchars($nombre) ?></span>
                <div class="user-role"><?= ucfirst($rol) ?></div>
            </div>
        </div>
        <a href="/proyecto/auth/logout" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</nav>