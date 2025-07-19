<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a ANVAR Inmobiliaria</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-message {
            background-color: #ecf0f1;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }
        .welcome-message h2 {
            color: #2c3e50;
            margin-top: 0;
            font-size: 24px;
        }
        .magic-link-section {
            background-color: #e8f5e8;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
            border: 2px solid #27ae60;
        }
        .magic-link-button {
            display: inline-block;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            transition: all 0.3s ease;
        }
        .magic-link-button:hover {
            background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }
        .info-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-box .icon {
            color: #f39c12;
            font-size: 20px;
            margin-right: 10px;
        }
        .steps {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .steps h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .steps ol {
            padding-left: 20px;
        }
        .steps li {
            margin-bottom: 10px;
            color: #555;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        .footer p {
            margin: 5px 0;
        }
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #34495e;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
        }
        .expiry-notice {
            background-color: #ffeaa7;
            border: 1px solid #f39c12;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            color: #856404;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }
            .content {
                padding: 20px !important;
            }
            .header {
                padding: 20px !important;
            }
            .welcome-message, .magic-link-section, .steps, .info-box {
                padding: 15px !important;
                margin: 15px 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="<?= base_url('assets/img/logo_admin.png') ?>" alt="ANVAR Inmobiliaria" class="logo">
            <h1>¡Bienvenido a ANVAR!</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h2>🎉 ¡Felicidades!</h2>
                <p>Nos complace informarte que tu proceso de registro ha sido completado exitosamente. Ahora eres oficialmente parte de la familia ANVAR Inmobiliaria.</p>
                <p><strong><?= esc($nombre_cliente) ?></strong>, tu expediente ha sido procesado y tu cuenta de cliente ha sido creada con el email: <strong><?= esc($email_cliente) ?></strong></p>
            </div>

            <!-- Magic Link Section -->
            <div class="magic-link-section">
                <h3>🔐 Accede a tu cuenta</h3>
                <p>Para completar la configuración de tu cuenta y establecer tu contraseña personalizada, haz clic en el siguiente enlace:</p>
                
                <a href="<?= esc($magic_link) ?>" class="magic-link-button">
                    Activar mi cuenta
                </a>
                
                <div class="expiry-notice">
                    ⚠️ Este enlace expira en 24 horas
                </div>
            </div>

            <!-- Next Steps -->
            <div class="steps">
                <h3>📋 Próximos pasos</h3>
                <ol>
                    <li><strong>Activa tu cuenta:</strong> Haz clic en el enlace de arriba para acceder por primera vez</li>
                    <li><strong>Establece tu contraseña:</strong> Crea una contraseña segura para futuras sesiones</li>
                    <li><strong>Completa tu perfil:</strong> Revisa y actualiza tu información personal</li>
                    <li><strong>Explora tu portal:</strong> Conoce todas las funcionalidades disponibles</li>
                    <li><strong>Mantén tus documentos actualizados:</strong> Sube cualquier documentación adicional necesaria</li>
                </ol>
            </div>

            <!-- Important Information -->
            <div class="info-box">
                <span class="icon">🔒</span>
                <strong>Información importante sobre tu cuenta:</strong>
                <ul style="margin: 10px 0; padding-left: 30px;">
                    <li>Tu cuenta está protegida con las mejores medidas de seguridad</li>
                    <li>Todos tus documentos están resguardados de forma segura</li>
                    <li>Puedes actualizar tu información personal en cualquier momento</li>
                    <li>Recibirás notificaciones importantes sobre tu expediente</li>
                </ul>
            </div>

            <!-- What's Available -->
            <div class="info-box" style="background-color: #e8f5e8; border-color: #27ae60;">
                <span class="icon" style="color: #27ae60;">🏠</span>
                <strong>¿Qué puedes hacer en tu portal?</strong>
                <ul style="margin: 10px 0; padding-left: 30px;">
                    <li>Ver el estado de tu expediente en tiempo real</li>
                    <li>Descargar y revisar tus documentos</li>
                    <li>Actualizar tu información de contacto</li>
                    <li>Comunicarte directamente con tu agente asignado</li>
                    <li>Recibir actualizaciones sobre tu proceso</li>
                </ul>
            </div>

            <!-- Support Information -->
            <div class="info-box" style="background-color: #e3f2fd; border-color: #2196f3;">
                <span class="icon" style="color: #2196f3;">📞</span>
                <strong>¿Necesitas ayuda?</strong>
                <p style="margin: 10px 0;">Nuestro equipo de soporte está disponible para ayudarte:</p>
                <ul style="margin: 10px 0; padding-left: 30px;">
                    <li><strong>Email:</strong> soporte@anvarinmobiliaria.com</li>
                    <li><strong>Teléfono:</strong> (55) 1234-5678</li>
                    <li><strong>WhatsApp:</strong> +52 55 1234-5678</li>
                    <li><strong>Horario:</strong> Lunes a Viernes, 9:00 AM - 6:00 PM</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>ANVAR Inmobiliaria</strong></p>
            <p>Tu socio de confianza en bienes raíces</p>
            
            <div class="contact-info">
                <p>📧 contacto@anvarinmobiliaria.com</p>
                <p>📞 (55) 1234-5678</p>
                <p>📍 Ciudad de México, México</p>
            </div>
            
            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Instagram</a> |
                <a href="#">LinkedIn</a> |
                <a href="#">YouTube</a>
            </div>
            
            <p style="font-size: 12px; margin-top: 20px; color: #bdc3c7;">
                Este correo fue enviado automáticamente. Si no solicitaste esta cuenta, por favor contacta a nuestro equipo de soporte.
            </p>
            
            <p style="font-size: 12px; color: #bdc3c7;">
                © <?= date('Y') ?> ANVAR Inmobiliaria. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>