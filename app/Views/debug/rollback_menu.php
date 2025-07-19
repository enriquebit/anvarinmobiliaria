<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔄 Sistema de Rollback - Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .action-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .action-card h3 {
            margin-top: 0;
            color: #495057;
        }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            font-weight: bold;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Sistema de Rollback - Debug</h1>
            <p>Herramientas para limpiar y resetear el sistema durante desarrollo</p>
        </div>

        <div class="danger">
            <h4>⚠️ ADVERTENCIA CRÍTICA</h4>
            <p><strong>Estas herramientas están diseñadas SOLO para desarrollo.</strong></p>
            <p>Eliminan permanentemente todos los datos de ventas, pagos, comisiones y liberan todos los lotes.</p>
            <p><strong>NO usar en producción.</strong></p>
        </div>

        <div class="warning">
            <h4>🔧 Entorno: <?= ENVIRONMENT ?></h4>
            <p>Las operaciones destructivas solo funcionan en entorno de desarrollo.</p>
        </div>

        <div class="actions">
            <div class="action-card">
                <h3>📊 Estado del Sistema</h3>
                <p>Verificar el estado actual de las tablas del sistema.</p>
                <a href="<?= site_url('debug/rollback/estado-sistema') ?>" class="btn btn-success">
                    📊 Ver Estado Actual
                </a>
            </div>

            <div class="action-card">
                <h3>🔄 Rollback Completo</h3>
                <p>Elimina TODOS los datos de ventas, pagos, comisiones y libera todos los lotes.</p>
                <div class="danger">
                    <strong>OPERACIÓN DESTRUCTIVA</strong>
                </div>
                <a href="<?= site_url('debug/rollback/rollback-completo') ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('¿Estás seguro de que deseas eliminar TODOS los datos? Esta acción no se puede deshacer.')">
                    🗑️ Rollback Completo
                </a>
            </div>

            <div class="action-card">
                <h3>🎯 Rollback Venta Específica</h3>
                <p>Elimina una venta específica y todos sus datos relacionados.</p>
                <form method="get" action="<?= site_url('debug/rollback/rollback-venta') ?>" style="display: inline;">
                    <input type="number" name="venta_id" placeholder="ID de Venta" required style="padding: 8px; margin: 5px;">
                    <button type="submit" class="btn btn-warning" 
                            onclick="return confirm('¿Eliminar esta venta y todos sus datos relacionados?')">
                        🎯 Eliminar Venta
                    </button>
                </form>
                <p><small>También puedes acceder directamente: /debug/rollback/rollback-venta/123</small></p>
            </div>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <h3>🔗 Enlaces Útiles</h3>
            <ul>
                <li><a href="<?= site_url('admin/ventas') ?>">📋 Gestión de Ventas</a></li>
                <li><a href="<?= site_url('admin/estado-cuenta') ?>">💰 Estado de Cuenta</a></li>
                <li><a href="<?= site_url('admin/pagos') ?>">💳 Gestión de Pagos</a></li>
                <li><a href="<?= site_url('admin/dashboard') ?>">🏠 Dashboard Admin</a></li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-refresh estado cada 30 segundos si estamos en la página de estado
        if (window.location.href.includes('estado-sistema')) {
            setTimeout(() => {
                location.reload();
            }, 30000);
        }
    </script>
</body>
</html>