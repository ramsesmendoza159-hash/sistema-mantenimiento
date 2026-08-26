<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) : 'Formulario de Supervisor' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        .form-container h2 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 600;
        }
        .form-container h2 i { color: #007bff; margin-right: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 600; color: #2c3e50; margin-bottom: 5px; display: block; }
        .form-group .required::after { content: " *"; color: #dc3545; font-weight: bold; }
        .form-group .form-control { border-radius: 5px; border: 1px solid #ced4da; padding: 10px 12px; }
        .form-group .form-control:focus { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15); }
        .form-group .form-control.is-invalid { border-color: #dc3545; }
        .form-group .form-control.is-valid { border-color: #28a745; }
        .form-group .help-text { display: block; font-size: 12px; color: #6c757d; margin-top: 4px; }
        .form-group .help-text i { margin-right: 3px; }
        .form-group .error-text { display: block; font-size: 12px; color: #dc3545; margin-top: 4px; }
        .form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; text-align: center; }
        .form-actions .btn { padding: 10px 30px; border-radius: 5px; font-weight: 600; min-width: 120px; }
        .form-actions .btn:not(:last-child) { margin-right: 10px; }
        .alert { border-radius: 8px; padding: 12px 20px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert .close { background: transparent; border: none; font-size: 20px; cursor: pointer; float: right; color: inherit; opacity: 0.6; }
        @media (max-width: 768px) {
            .form-container { padding: 20px; margin: 10px; }
            .form-actions .btn { width: 100%; margin-bottom: 10px; }
            .form-actions .btn:not(:last-child) { margin-right: 0; }
        }
        .password-toggle { position: relative; }
        .password-toggle .toggle-btn { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #6c757d; padding: 5px; }
        .password-toggle .toggle-btn:hover { color: #007bff; }
        .password-strength { height: 4px; margin-top: 5px; border-radius: 2px; transition: all 0.3s; }
        .password-strength.weak { background: #dc3545; width: 25%; }
        .password-strength.medium { background: #ffc107; width: 50%; }
        .password-strength.strong { background: #28a745; width: 75%; }
        .password-strength.very-strong { background: #007bff; width: 100%; }
    </style>
</head>
<body>
    <?php include_once 'views/layouts/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include_once 'views/layouts/sidebar.php'; ?>
            
            <div class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <div class="form-container">
                    <h2>
                        <i class="fas fa-<?= isset($accion) && $accion == 'crear' ? 'user-plus' : 'user-edit' ?>"></i> 
                        <?= isset($titulo) ? htmlspecialchars($titulo) : (isset($accion) && $accion == 'crear' ? 'Crear Supervisor' : 'Editar Supervisor') ?>
                    </h2>

                    <?php if (isset($_SESSION['mensaje'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        </div>
                        <?php unset($_SESSION['mensaje']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- ✅ CORREGIDO: Action del formulario -->
                    <?php
                    if (isset($accion) && $accion == 'crear') {
                        $actionUrl = '/proyecto/supervisores/guardar';
                    } else {
                        $actionUrl = '/proyecto/supervisores/actualizar/' . (isset($supervisor['id']) ? $supervisor['id'] : '');
                    }
                    ?>
                    <form action="<?php echo $actionUrl; ?>" method="POST" id="formSupervisor" novalidate>
                        <?php if (isset($accion) && $accion == 'editar'): ?>
                            <input type="hidden" name="_method" value="PUT">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre" class="required">Nombre Completo</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" 
                                           value="<?= isset($supervisor['nombre']) ? htmlspecialchars($supervisor['nombre']) : '' ?>" 
                                           required maxlength="100" placeholder="Ej: Juan Pérez">
                                    <small class="help-text"><i class="fas fa-info-circle"></i> Ingresa el nombre completo del supervisor</small>
                                    <div class="error-text" id="nombre-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="required">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" 
                                           value="<?= isset($supervisor['email']) ? htmlspecialchars($supervisor['email']) : '' ?>" 
                                           required maxlength="100" placeholder="ejemplo@proyecto.com">
                                    <small class="help-text"><i class="fas fa-info-circle"></i> El email debe ser único en el sistema</small>
                                    <div class="error-text" id="email-error"></div>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($accion) && $accion == 'crear'): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Contraseña</label>
                                        <div class="password-toggle">
                                            <input type="password" name="password" id="password" class="form-control" 
                                                   value="password" minlength="6" placeholder="Mínimo 6 caracteres">
                                            <button type="button" class="toggle-btn" onclick="togglePassword('password')">
                                                <i class="fas fa-eye" id="password-icon"></i>
                                            </button>
                                        </div>
                                        <small class="help-text"><i class="fas fa-info-circle"></i> Por defecto: "password". Mínimo 6 caracteres.</small>
                                        <div class="password-strength" id="password-strength"></div>
                                        <div class="error-text" id="password-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirmar_password">Confirmar Contraseña</label>
                                        <div class="password-toggle">
                                            <input type="password" name="confirmar_password" id="confirmar_password" class="form-control" 
                                                   value="password" placeholder="Repite la contraseña">
                                            <button type="button" class="toggle-btn" onclick="togglePassword('confirmar_password')">
                                                <i class="fas fa-eye" id="confirmar-password-icon"></i>
                                            </button>
                                        </div>
                                        <div class="error-text" id="confirmar-password-error"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area">Área</label>
                                    <select name="area" id="area" class="form-control">
                                        <option value="MANTENIMIENTO" <?= (isset($supervisor['area']) && $supervisor['area'] == 'MANTENIMIENTO') ? 'selected' : '' ?>>MANTENIMIENTO</option>
                                        <option value="PRODUCCION" <?= (isset($supervisor['area']) && $supervisor['area'] == 'PRODUCCION') ? 'selected' : '' ?>>PRODUCCIÓN</option>
                                        <option value="CALIDAD" <?= (isset($supervisor['area']) && $supervisor['area'] == 'CALIDAD') ? 'selected' : '' ?>>CALIDAD</option>
                                        <option value="SEGURIDAD" <?= (isset($supervisor['area']) && $supervisor['area'] == 'SEGURIDAD') ? 'selected' : '' ?>>SEGURIDAD</option>
                                        <option value="LOGISTICA" <?= (isset($supervisor['area']) && $supervisor['area'] == 'LOGISTICA') ? 'selected' : '' ?>>LOGÍSTICA</option>
                                        <option value="ADMINISTRACION" <?= (isset($supervisor['area']) && $supervisor['area'] == 'ADMINISTRACION') ? 'selected' : '' ?>>ADMINISTRACIÓN</option>
                                    </select>
                                    <small class="help-text"><i class="fas fa-info-circle"></i> Área donde desempeña sus funciones</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-control">
                                        <option value="activo" <?= (isset($supervisor['estado']) && $supervisor['estado'] == 'activo') ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactivo" <?= (isset($supervisor['estado']) && $supervisor['estado'] == 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                    <small class="help-text"><i class="fas fa-info-circle"></i> Solo los usuarios activos pueden iniciar sesión</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> 
                                <?= isset($accion) && $accion == 'crear' ? 'Guardar Supervisor' : 'Actualizar Supervisor' ?>
                            </button>
                            <a href="/proyecto/supervisores" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(fieldId) {
            var field = document.getElementById(fieldId);
            var icon = document.getElementById(fieldId + '-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        <?php if (isset($accion) && $accion == 'crear'): ?>
        document.getElementById('password').addEventListener('keyup', function() {
            var password = this.value;
            var strengthBar = document.getElementById('password-strength');
            var strength = getPasswordStrength(password);
            strengthBar.className = 'password-strength ' + strength.class;
            strengthBar.style.display = password.length > 0 ? 'block' : 'none';
        });

        function getPasswordStrength(password) {
            if (password.length < 6) return { class: 'weak', label: 'Débil' };
            var score = 0;
            if (password.length >= 8) score++;
            if (password.match(/[a-z]/)) score++;
            if (password.match(/[A-Z]/)) score++;
            if (password.match(/[0-9]/)) score++;
            if (password.match(/[^a-zA-Z0-9]/)) score++;
            if (score <= 1) return { class: 'weak', label: 'Débil' };
            if (score <= 3) return { class: 'medium', label: 'Media' };
            if (score <= 4) return { class: 'strong', label: 'Fuerte' };
            return { class: 'very-strong', label: 'Muy fuerte' };
        }
        <?php endif; ?>

        document.getElementById('formSupervisor').addEventListener('submit', function(e) {
            var valid = true;
            
            var nombre = document.getElementById('nombre');
            var nombreError = document.getElementById('nombre-error');
            if (!nombre.value.trim()) {
                nombre.classList.add('is-invalid');
                nombreError.textContent = 'El nombre es obligatorio';
                valid = false;
            } else if (nombre.value.trim().length < 3) {
                nombre.classList.add('is-invalid');
                nombreError.textContent = 'El nombre debe tener al menos 3 caracteres';
                valid = false;
            } else {
                nombre.classList.remove('is-invalid');
                nombre.classList.add('is-valid');
                nombreError.textContent = '';
            }

            var email = document.getElementById('email');
            var emailError = document.getElementById('email-error');
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!email.value.trim()) {
                email.classList.add('is-invalid');
                emailError.textContent = 'El email es obligatorio';
                valid = false;
            } else if (!emailPattern.test(email.value.trim())) {
                email.classList.add('is-invalid');
                emailError.textContent = 'Ingresa un email válido (ejemplo@dominio.com)';
                valid = false;
            } else {
                email.classList.remove('is-invalid');
                email.classList.add('is-valid');
                emailError.textContent = '';
            }

            <?php if (isset($accion) && $accion == 'crear'): ?>
            var password = document.getElementById('password');
            var passwordError = document.getElementById('password-error');
            var confirmar = document.getElementById('confirmar_password');
            var confirmarError = document.getElementById('confirmar-password-error');
            
            if (password.value.length > 0 && password.value.length < 6) {
                password.classList.add('is-invalid');
                passwordError.textContent = 'La contraseña debe tener al menos 6 caracteres';
                valid = false;
            } else {
                password.classList.remove('is-invalid');
                password.classList.add('is-valid');
                passwordError.textContent = '';
            }
            
            if (password.value !== confirmar.value) {
                confirmar.classList.add('is-invalid');
                confirmarError.textContent = 'Las contraseñas no coinciden';
                valid = false;
            } else {
                confirmar.classList.remove('is-invalid');
                confirmar.classList.add('is-valid');
                confirmarError.textContent = '';
            }
            <?php endif; ?>

            if (!valid) {
                e.preventDefault();
                var firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        document.querySelectorAll('.form-control').forEach(function(input) {
            input.addEventListener('focus', function() {
                this.classList.remove('is-invalid');
                this.classList.remove('is-valid');
                var errorElement = document.getElementById(this.id + '-error');
                if (errorElement) {
                    errorElement.textContent = '';
                }
            });
        });

        var formModified = false;
        document.querySelectorAll('.form-control').forEach(function(input) {
            input.addEventListener('change', function() { formModified = true; });
            input.addEventListener('keyup', function() { formModified = true; });
        });

        window.addEventListener('beforeunload', function(e) {
            if (formModified) {
                e.preventDefault();
                e.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de salir?';
            }
        });

        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var closeBtn = alert.querySelector('.close');
                if (closeBtn) {
                    closeBtn.click();
                }
            });
        }, 5000);
    </script>
</body>
</html>