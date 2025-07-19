<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Configurar Contraseña - ANVAR' ?></title>
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/dist/css/adminlte.min.css') ?>">
    
    <style>
        .welcome-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .welcome-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
        }
        
        .welcome-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .welcome-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        
        .welcome-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .welcome-body {
            padding: 40px 30px;
        }
        
        .cliente-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #3498db;
        }
        
        .cliente-info h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 18px;
        }
        
        .cliente-info p {
            margin: 0;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .password-requirements {
            background-color: #e8f5e8;
            border: 1px solid #27ae60;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .password-requirements h6 {
            margin: 0 0 10px 0;
            color: #27ae60;
            font-weight: 600;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            color: #555;
            margin-bottom: 5px;
        }
        
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-message {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .footer-links {
            text-align: center;
            padding: 20px 30px;
            background-color: #f8f9fa;
            border-top: 1px solid #e0e6ed;
        }
        
        .footer-links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <!-- Header -->
            <div class="welcome-header">
                <i class="fas fa-key fa-3x mb-3" style="opacity: 0.8;"></i>
                <h1>¡Bienvenido a ANVAR!</h1>
                <p>Configura tu contraseña para acceder a tu portal</p>
            </div>
            
            <!-- Body -->
            <div class="welcome-body">
                <!-- Información del Cliente -->
                <?php if (isset($cliente) && $cliente): ?>
                <div class="cliente-info">
                    <h4><i class="fas fa-user mr-2"></i>Hola, <?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno) ?>!</h4>
                    <p><i class="fas fa-envelope mr-2"></i><?= esc($user->email ?? '') ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Mensaje de información -->
                <?php if (session('info')): ?>
                    <div class="info-message">
                        <i class="fas fa-info-circle mr-2"></i>
                        <?= session('info') ?>
                    </div>
                <?php endif; ?>
                
                <!-- Errores -->
                <?php if (session('errors')): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Por favor corrige los siguientes errores:</strong>
                        <ul class="mt-2 mb-0">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (session('error')): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>
                
                <!-- Formulario -->
                <form action="<?= site_url('cliente/guardar-password') ?>" method="POST" id="passwordForm">
                    <?= csrf_field() ?>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock mr-2"></i>Nueva Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Ingresa tu nueva contraseña"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">
                            <i class="fas fa-lock mr-2"></i>Confirmar Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirm" 
                               name="password_confirm" 
                               placeholder="Confirma tu contraseña"
                               required>
                    </div>
                    
                    <!-- Requisitos de contraseña -->
                    <div class="password-requirements">
                        <h6><i class="fas fa-shield-alt mr-2"></i>Requisitos de seguridad:</h6>
                        <ul>
                            <li>Mínimo 8 caracteres</li>
                            <li>Se recomienda usar letras, números y símbolos</li>
                            <li>Evita usar información personal</li>
                        </ul>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check mr-2"></i>
                            Configurar Contraseña y Continuar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="footer-links">
                <small class="text-muted">
                    ¿Necesitas ayuda? 
                    <a href="mailto:soporte@anvar.com.mx">Contacta a soporte</a>
                </small>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    
    <script>
    $(document).ready(function() {
        // Validación básica en tiempo real
        $('#password_confirm').on('keyup', function() {
            const password = $('#password').val();
            const confirmPassword = $(this).val();
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    $(this).css('border-color', '#27ae60');
                } else {
                    $(this).css('border-color', '#e74c3c');
                }
            } else {
                $(this).css('border-color', '#e0e6ed');
            }
        });
        
        // Validación al enviar el formulario
        $('#passwordForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#password_confirm').val();
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres.');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                return false;
            }
        });
    });
    </script>
</body>
</html>