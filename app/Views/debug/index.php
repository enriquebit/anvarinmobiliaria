<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛠️ Panel de Debug - ANVAR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .section {
            margin: 30px 0;
        }
        .section h2 {
            color: #555;
            margin-bottom: 15px;
        }
        .tool-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .tool-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .tool-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .tool-card h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .tool-card p {
            color: #6c757d;
            font-size: 14px;
            margin: 10px 0;
        }
        .tool-card a {
            display: inline-block;
            margin: 5px 5px 5px 0;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .tool-card a:hover {
            background-color: #0056b3;
        }
        .tool-card a.danger {
            background-color: #dc3545;
        }
        .tool-card a.danger:hover {
            background-color: #c82333;
        }
        .tool-card a.warning {
            background-color: #ffc107;
            color: #333;
        }
        .tool-card a.warning:hover {
            background-color: #e0a800;
        }
        .tool-card a.success {
            background-color: #28a745;
        }
        .tool-card a.success:hover {
            background-color: #218838;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .emoji {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ Panel de Herramientas de Debug</h1>
        
        <div class="warning-box">
            <span class="emoji">⚠️</span>
            <strong>ADVERTENCIA:</strong> Estas herramientas son solo para desarrollo. 
            Úsalas con precaución ya que pueden modificar o eliminar datos.
        </div>

        <!-- Sección de Rollback -->
        <div class="section">
            <h2>🔄 Herramientas de Rollback</h2>
            <div class="tool-grid">
                <div class="tool-card">
                    <h3>📊 Estado del Sistema</h3>
                    <p>Ver el estado actual de todas las tablas del sistema</p>
                    <a href="<?= site_url('debug/rollback/estado-sistema') ?>">Ver Estado</a>
                </div>
                
                <div class="tool-card">
                    <h3>🔄 Rollback de Último Pago</h3>
                    <p>Revertir el último pago de una venta específica sin eliminar la venta</p>
                    <a href="<?= site_url('debug/rollback/ultimo-pago/40') ?>" class="warning">Rollback Venta 40</a>
                    <a href="<?= site_url('debug/rollback/ultimo-pago/41') ?>" class="warning">Rollback Venta 41</a>
                    <p><small>🎯 Para debugging de pagos</small></p>
                </div>
                
                <div class="tool-card">
                    <h3>🗑️ Rollback de Venta Completa</h3>
                    <p>Eliminar completamente una venta y todos sus registros relacionados</p>
                    <a href="<?= site_url('debug/rollback/rollback-venta/40') ?>" class="danger">Eliminar Venta 40</a>
                    <a href="<?= site_url('debug/rollback/rollback-venta/41') ?>" class="danger">Eliminar Venta 41</a>
                    <a href="<?= site_url('debug/rollback/rollback-venta') ?>?venta_id=42" class="danger">Por Query</a>
                </div>
                
                <div class="tool-card">
                    <h3>💣 Rollback Completo del Sistema</h3>
                    <p>⚠️ ELIMINAR TODOS los datos de ventas, pagos, comisiones, etc.</p>
                    <a href="<?= site_url('debug/rollback/rollback-completo') ?>" class="danger">💀 ROLLBACK TOTAL</a>
                    <p><small>⚠️ Solo usar para reset completo</small></p>
                </div>
            </div>
        </div>

        <!-- Sección de Autenticación -->
        <div class="section">
            <h2>🔐 Herramientas de Autenticación</h2>
            <div class="tool-grid">
                <div class="tool-card">
                    <h3>👤 Debug de Usuarios</h3>
                    <p>Ver información de usuarios y sus permisos</p>
                    <a href="<?= site_url('debug/auth') ?>">Ver Usuarios</a>
                </div>
                
                <div class="tool-card">
                    <h3>🔑 Login Rápido</h3>
                    <p>Iniciar sesión rápidamente como diferentes usuarios</p>
                    <a href="<?= site_url('debug/login') ?>">Login Debug</a>
                </div>
                
                <div class="tool-card">
                    <h3>📧 Bienvenida Cliente</h3>
                    <p>Probar flujo de bienvenida y cambio de contraseña</p>
                    <a href="<?= site_url('debug/bienvenida') ?>">Test Bienvenida</a>
                </div>
                
                <div class="tool-card">
                    <h3>🔄 Debug de Sesiones</h3>
                    <p>Ver y gestionar sesiones activas</p>
                    <a href="<?= site_url('debug/session') ?>">Ver Sesiones</a>
                </div>
            </div>
        </div>

        <!-- Sección de Datos -->
        <div class="section">
            <h2>📊 Herramientas de Datos</h2>
            <div class="tool-grid">
                <div class="tool-card">
                    <h3>📈 Tabla de Amortización</h3>
                    <p>Debug de cálculos y generación de tablas</p>
                    <a href="<?= site_url('debug/amortizacion') ?>">Ver Amortización</a>
                </div>
                
                <div class="tool-card">
                    <h3>👥 Debug de Clientes</h3>
                    <p>Ver información detallada de clientes</p>
                    <a href="<?= site_url('debug/clientes') ?>">Ver Clientes</a>
                </div>
                
                <div class="tool-card">
                    <h3>💰 Configuración Financiera</h3>
                    <p>Debug de configuraciones y cálculos financieros</p>
                    <a href="<?= site_url('debug/configuracion-financiera') ?>">Ver Config</a>
                </div>
                
                <div class="tool-card">
                    <h3>📄 Presupuestos</h3>
                    <p>Debug de generación de presupuestos</p>
                    <a href="<?= site_url('debug/presupuestos') ?>">Ver Presupuestos</a>
                </div>
            </div>
        </div>

        <!-- Sección de Integraciones -->
        <div class="section">
            <h2>🔗 Herramientas de Integración</h2>
            <div class="tool-grid">
                <div class="tool-card">
                    <h3>📁 Google Drive</h3>
                    <p>Debug de integración con Google Drive</p>
                    <a href="<?= site_url('debug/google-drive') ?>">Test Google Drive</a>
                </div>
                
                <div class="tool-card">
                    <h3>⏱️ Timing Debug</h3>
                    <p>Analizar tiempos de ejecución y rendimiento</p>
                    <a href="<?= site_url('debug/timing') ?>">Ver Timing</a>
                </div>
            </div>
        </div>

        <!-- Sección de Testing -->
        <div class="section">
            <h2>🧪 Herramientas de Testing</h2>
            <div class="tool-grid">
                <div class="tool-card">
                    <h3>🎯 Controller General</h3>
                    <p>Debug general del sistema</p>
                    <a href="<?= site_url('debug/controller') ?>">Debug General</a>
                </div>
                
                <div class="tool-card">
                    <h3>🚀 Simple Debug</h3>
                    <p>Herramientas simples de debug</p>
                    <a href="<?= site_url('debug/simple') ?>">Simple Debug</a>
                </div>
                
                <div class="tool-card">
                    <h3>🔬 Test Controller</h3>
                    <p>Pruebas específicas del sistema</p>
                    <a href="<?= site_url('debug/test') ?>">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Enlaces rápidos -->
        <div class="section">
            <h2>⚡ Enlaces Rápidos</h2>
            <div class="tool-grid">
                <div class="tool-card">
                    <h3>🏠 Paneles Principales</h3>
                    <p>Acceso rápido a los diferentes paneles</p>
                    <a href="<?= site_url('admin/dashboard') ?>" class="success">Panel Admin</a>
                    <a href="<?= site_url('cliente/dashboard') ?>" class="success">Panel Cliente</a>
                    <a href="<?= site_url('/') ?>" class="success">Inicio</a>
                </div>
                
                <div class="tool-card">
                    <h3>📝 Logs del Sistema</h3>
                    <p>Ver logs de errores y debug</p>
                    <a href="<?= site_url('debug/logs') ?>">Ver Logs</a>
                    <a href="<?= site_url('writable/logs') ?>" target="_blank">Carpeta Logs</a>
                </div>
            </div>
        </div>

        <div style="margin-top: 50px; text-align: center; color: #999;">
            <p>ANVAR Debug Panel v1.0 | Environment: <?= ENVIRONMENT ?></p>
        </div>
    </div>
</body>
</html>