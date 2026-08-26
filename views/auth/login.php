<?php
// views/auth/login.php
// Ubicación: C:\xampp\htdocs\proyecto\views\auth\login.php

// Verificar si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header('Location: /proyecto/dashboard');
    exit();
}

$titulo = "Iniciar Sesión - PROYECTO";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 450px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            background: #ffffff;
            padding: 0;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 30px 20px;
            text-align: center;
            color: white;
        }
        .login-header h3 {
            font-weight: 700;
            margin: 0;
            letter-spacing: 1px;
        }
        .login-header p {
            opacity: 0.8;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .login-body {
            padding: 30px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo img {
            max-height: 60px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .input-group .form-control:focus {
            border-left: none;
        }
        .btn-login {
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
        }
        .credentials-help {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        .credentials-help small {
            color: #6c757d;
        }
        .badge-role {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .toggle-password {
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 10px 10px 0;
            background: #f8f9fa;
        }
        .toggle-password:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <h3><i class="bi bi-tools"></i> PROYECTO</h3>
                        <p>Sistema de Gestión de Mantenimiento</p>
                    </div>
                    <div class="login-body">
                        <div class="login-logo">
                            <img src="/proyecto/assets/img/cropped-produmar-logo.png" alt="PROYECTO Logo" 
                                 onerror="this.style.display='none'">
                        </div>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php 
                                    echo htmlspecialchars($_SESSION['error']); 
                                    unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php 
                                    echo htmlspecialchars($_SESSION['success']); 
                                    unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="/proyecto/auth/authenticate" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="usuario@proyecto.com" required autofocus
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Ingresa tu contraseña" required>
                                    <button class="btn toggle-password" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-login">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                                </button>
                            </div>
                        </form>

                        <div class="credentials-help">
                            <div class="text-center mb-2">
                                <small class="text-muted fw-semibold">🔑 Credenciales de prueba</small>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <span class="badge bg-primary badge-role">Admin</span>
                                    <small class="d-block text-muted mt-1" style="font-size: 11px;">admin@proyecto.com</small>
                                </div>
                                <div class="col-4">
                                    <span class="badge bg-success badge-role">Supervisor</span>
                                    <small class="d-block text-muted mt-1" style="font-size: 11px;">william.gomez@proyecto.com</small>
                                </div>
                                <div class="col-4">
                                    <span class="badge bg-info badge-role">Técnico</span>
                                    <small class="d-block text-muted mt-1" style="font-size: 11px;">juan@proyecto.com</small>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">Contraseña: <strong class="text-dark">123456</strong></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/Ocultar contraseña
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });

        // Auto-enfoque en el campo de email
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>