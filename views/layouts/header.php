<?php
// views/layouts/header.php
// Ubicación: C:\xampp\htdocs\produmar\views\layouts\header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'PRODUMAR - Sistema de Mantenimiento'; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar .brand {
            padding: 25px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-align: center;
        }
        .sidebar .brand h4 {
            color: white;
            font-weight: 700;
            margin: 0;
        }
        .sidebar .brand h4 i {
            color: #667eea;
        }
        .sidebar .brand small {
            color: rgba(255,255,255,0.4);
            font-size: 12px;
        }
        .sidebar .nav {
            flex: 1;
            padding: 15px 10px;
        }
        .sidebar .nav-item {
            margin-bottom: 2px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.6);
            padding: 12px 16px;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.08);
        }
        .sidebar .nav-link.active {
            color: white;
            background: rgba(102, 126, 234, 0.25);
        }
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        .sidebar .user-info {
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: auto;
        }
        .sidebar .user-info .user-name {
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        .sidebar .user-info .user-role {
            color: rgba(255,255,255,0.4);
            font-size: 12px;
        }
        .sidebar .user-info .btn-logout {
            color: rgba(255,255,255,0.6);
            border-color: rgba(255,255,255,0.15);
            margin-top: 8px;
            width: 100%;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
        }
        .sidebar .user-info .btn-logout:hover {
            color: white;
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.15);
        }
        
        /* Main content */
        .main-content {
            padding: 20px 30px;
            background: #f0f2f5;
            min-height: 100vh;
        }
        
        /* Cards */
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: none;
            background: white;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            padding: 16px 20px;
        }
        .card-body {
            padding: 20px;
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        /* Tables */
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
            font-size: 14px;
        }
        
        /* Buttons */
        .btn-group .btn {
            padding: 4px 10px;
            font-size: 13px;
            border-radius: 6px;
        }
        
        /* Alerts */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 14px 18px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                width: 280px;
                z-index: 1050;
                transition: left 0.3s ease;
                overflow-y: auto;
                height: 100vh;
            }
            .sidebar.show {
                left: 0;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            .sidebar-overlay.show {
                display: block;
            }
            .sidebar-toggle {
                display: block !important;
                position: fixed;
                top: 12px;
                left: 12px;
                z-index: 1060;
                background: #1a1a2e;
                color: white;
                border: none;
                padding: 10px 14px;
                border-radius: 10px;
                font-size: 18px;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }
            .sidebar-toggle:hover {
                background: #2a2a4e;
            }
            .main-content {
                padding: 70px 15px 20px;
            }
            .display-6 {
                font-size: 1.8rem;
            }
        }
        @media (min-width: 769px) {
            .sidebar-toggle {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- Overlay para móvil -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Botón toggle para móvil -->
<button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>