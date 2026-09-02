<?php
// views/error/404.php
// Página de error 404 - VERSIÓN CORREGIDA
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: #fff;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .error-number {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }
        .error-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 16px;
        }
        .btn-primary {
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>
<body>
    <div class="error-container text-center">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="error-number">404</div>
        <h2 class="fw-bold mt-3 mb-2">Página no encontrada</h2>
        <p class="text-muted mb-4">
            Lo sentimos, la página que buscas no existe o ha sido movida.
        </p>
        <a href="/proyecto/dashboard" class="btn btn-primary">
            <i class="fas fa-home me-2"></i> Volver al Dashboard
        </a>
        <div class="mt-4">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i> 
                Si crees que esto es un error, contacta al administrador.
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>