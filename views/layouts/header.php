<!-- views/layouts/header.php -->
<!-- ✅ VERSIÓN CORREGIDA Y UNIFICADA -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Sistema de Mantenimiento' ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- ✅ ESTILOS PERSONALIZADOS -->
    <style>
        /* ========================================== */
        /* RESET Y BASE */
        /* ========================================== */
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
            font-family: 'Inter', sans-serif;
        }
        
        /* ========================================== */
        /* WRAPPER */
        /* ========================================== */
        
        .wrapper {
            display: flex !important;
            min-height: 100vh !important;
            width: 100% !important;
        }
        
        /* ========================================== */
        /* SIDEBAR - ESTILOS FUERTES */
        /* ========================================== */
        
        .sidebar {
            min-height: 100vh !important;
            height: 100% !important;
            width: 260px !important;
            flex-shrink: 0 !important;
            background: #1a1a2e !important;
            color: #ecf0f1 !important;
            position: sticky !important;
            top: 0 !important;
            overflow-y: auto !important;
            padding: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            z-index: 1000 !important;
            border-right: none !important;
        }
        
        /* Brand */
        .sidebar .brand {
            padding: 24px 20px 20px !important;
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
            text-align: center !important;
        }
        
        .sidebar .brand .brand-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 48px !important;
            height: 48px !important;
            background: #3498db !important;
            border-radius: 12px !important;
            font-size: 1.5rem !important;
            color: #fff !important;
            margin-bottom: 12px !important;
        }
        
        .sidebar .brand h4 {
            color: #fff !important;
            margin: 0 !important;
            font-weight: 700 !important;
            font-size: 1.1rem !important;
        }
        
        .sidebar .brand small {
            color: rgba(255,255,255,0.35) !important;
            font-size: 0.7rem !important;
            display: block !important;
            margin-top: 2px !important;
        }
        
        /* Sección título */
        .sidebar .nav-section-title {
            padding: 20px 20px 8px !important;
            font-size: 0.6rem !important;
            text-transform: uppercase !important;
            letter-spacing: 1.5px !important;
            color: rgba(255,255,255,0.2) !important;
            font-weight: 600 !important;
        }
        
        /* Navegación */
        .sidebar .nav {
            flex: 1 !important;
            padding: 8px 0 !important;
            list-style: none !important;
        }
        
        .sidebar .nav-item {
            margin: 2px 12px !important;
            list-style: none !important;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.5) !important;
            padding: 10px 16px !important;
            border-radius: 10px !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            font-size: 0.85rem !important;
            background: transparent !important;
            border: none !important;
        }
        
        .sidebar .nav-link i {
            width: 20px !important;
            text-align: center !important;
            font-size: 1rem !important;
            color: rgba(255,255,255,0.3) !important;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.06) !important;
            color: #fff !important;
        }
        
        .sidebar .nav-link:hover i {
            color: rgba(255,255,255,0.7) !important;
        }
        
        .sidebar .nav-link.active {
            background: #3498db !important;
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.25) !important;
        }
        
        .sidebar .nav-link.active i {
            color: #fff !important;
        }
        
        /* Usuario info */
        .sidebar .user-info {
            padding: 16px 20px !important;
            border-top: 1px solid rgba(255,255,255,0.06) !important;
            margin-top: auto !important;
        }
        
        .sidebar .user-info .user-details {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin-bottom: 10px !important;
        }
        
        .sidebar .user-info .user-avatar {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 36px !important;
            height: 36px !important;
            background: #3498db !important;
            border-radius: 50% !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            color: #fff !important;
            flex-shrink: 0 !important;
        }
        
        .sidebar .user-name {
            color: #fff !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
        }
        
        .sidebar .user-role {
            color: rgba(255,255,255,0.35) !important;
            font-size: 0.7rem !important;
        }
        
        .sidebar .btn-logout {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            width: 100% !important;
            padding: 10px !important;
            background: rgba(220, 53, 69, 0.12) !important;
            color: #dc3545 !important;
            border: none !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            font-size: 0.85rem !important;
            transition: all 0.3s ease !important;
        }
        
        .sidebar .btn-logout:hover {
            background: rgba(220, 53, 69, 0.2) !important;
            color: #dc3545 !important;
        }
        
        /* ========================================== */
        /* MAIN CONTENT */
        /* ========================================== */
        
        .main-content {
            flex: 1 !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            background: #f0f2f5 !important;
        }
        
        .main-content .content {
            flex: 1 !important;
            padding: 24px 30px !important;
        }
        
        /* Topbar */
        .topbar {
            background: #fff !important;
            padding: 14px 30px !important;
            border-bottom: 1px solid #e9ecef !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 999 !important;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04) !important;
        }
        
        .topbar .page-title h4 {
            margin: 0 !important;
            font-weight: 700 !important;
            color: #1a1a2e !important;
            font-size: 1.2rem !important;
        }
        
        .topbar .page-title small {
            color: #6c757d !important;
            font-size: 0.75rem !important;
        }
        
        .topbar .user-avatar {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 36px !important;
            height: 36px !important;
            background: #3498db !important;
            border-radius: 50% !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            color: #fff !important;
        }
        
        .menu-toggle {
            display: none !important;
            background: transparent !important;
            border: none !important;
            font-size: 1.5rem !important;
            color: #1a1a2e !important;
            padding: 0 !important;
            cursor: pointer !important;
        }
        
        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        
        @media (max-width: 992px) {
            .sidebar {
                position: fixed !important;
                left: -280px !important;
                top: 0 !important;
                height: 100vh !important;
                z-index: 1050 !important;
                transition: left 0.3s ease !important;
            }
            
            .sidebar.show {
                left: 0 !important;
            }
            
            .sidebar-overlay {
                display: none !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background: rgba(0,0,0,0.5) !important;
                z-index: 1040 !important;
            }
            
            .sidebar-overlay.show {
                display: block !important;
            }
            
            .menu-toggle {
                display: block !important;
            }
        }
        
        @media (min-width: 993px) {
            .menu-toggle {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .main-content .content {
                padding: 16px !important;
            }
            .topbar {
                padding: 12px 16px !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        
        <!-- ✅ INCLUIR SIDEBAR -->
        <?php 
        // Solo incluir sidebar si NO estamos en páginas de autenticación
        $excludeSidebar = ['login', 'auth/login', 'auth/authenticate'];
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        $showSidebar = true;
        foreach ($excludeSidebar as $path) {
            if (strpos($currentPath, $path) !== false) {
                $showSidebar = false;
                break;
            }
        }
        
        if ($showSidebar && isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
            include_once __DIR__ . '/sidebar.php';
        }
        ?>
        
        <!-- Contenido principal -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-title">
                        <h4><?= $titulo ?? 'Dashboard' ?></h4>
                        <small><?= date('d/m/Y H:i') ?></small>
                    </div>
                </div>
                <div>
                    <?php if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])): ?>
                        <a href="/proyecto/perfil" class="text-decoration-none" title="Mi Perfil">
                            <span class="user-avatar">
                                <?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1)) ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Contenido dinámico -->
            <div class="content">