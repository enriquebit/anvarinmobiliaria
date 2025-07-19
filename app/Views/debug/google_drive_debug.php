<!DOCTYPE html>
<html>
<head>
    <title>Google Drive Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .debug-section { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .debug-output { background: #f8f9fa; padding: 10px; border-radius: 4px; white-space: pre-wrap; font-family: monospace; }
        .auth-link { display: inline-block; margin: 10px 0; padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; }
        .auth-link:hover { background: #c82333; color: white; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Google Drive Debug</h1>
        
        <?php if (session('success')): ?>
            <div class="debug-section success">
                <strong>✅ Éxito:</strong> <?= session('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session('error')): ?>
            <div class="debug-section error">
                <strong>❌ Error:</strong> <?= session('error') ?>
            </div>
        <?php endif; ?>
        
        <!-- Sección de autenticación -->
        <div class="debug-section">
            <h2>🔑 Test de Autenticación</h2>
            <form method="get" action="<?= base_url('debug/google-drive/test-auth') ?>">
                <button type="submit" class="btn btn-warning">Verificar Autenticación</button>
            </form>
        </div>
        
        <!-- Formulario para crear carpeta -->
        <div class="debug-section">
            <h2>📁 Crear Carpeta en Google Drive</h2>
            <form method="post" action="<?= base_url('debug/google-drive/crear-carpeta') ?>">
                <div class="form-group">
                    <label for="nombre_carpeta">Nombre de la Carpeta:</label>
                    <input type="text" id="nombre_carpeta" name="nombre_carpeta" 
                           placeholder="Ej: JUAN PEREZ GARCIA" required>
                </div>
                <button type="submit" class="btn">Crear Carpeta</button>
            </form>
        </div>
        
        <!-- Resultados -->
        <?php if (isset($resultados)): ?>
            <div class="debug-section">
                <h2>📊 Resultados del Debug</h2>
                
                <?php if (isset($resultados['success']) && $resultados['success']): ?>
                    <div class="success">
                        <strong>✅ Éxito:</strong> <?= $resultados['mensaje'] ?? 'Operación exitosa' ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($resultados['error'])): ?>
                    <div class="error">
                        <strong>❌ Error:</strong> <?= $resultados['error'] ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($resultados['auth_url'])): ?>
                    <div class="warning">
                        <strong>🔐 Autorización Requerida:</strong><br>
                        <a href="<?= $resultados['auth_url'] ?>" class="auth-link" target="_blank">
                            Autorizar Aplicación en Google Drive
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="debug-output">
                    <strong>🔍 Debug Output:</strong>
                    <?php 
                    echo "\n";
                    foreach ($resultados as $key => $value) {
                        echo "[$key] => ";
                        if (is_array($value)) {
                            var_dump($value);
                        } else {
                            echo $value . "\n";
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Información del sistema -->
        <div class="debug-section">
            <h2>⚙️ Información del Sistema</h2>
            <div class="debug-output">
                <strong>Rutas importantes:</strong>
                Cliente Secret: <?= file_exists(WRITEPATH . '../writable/client_secret_2_105460097414-smklrm0r5f2d1j88f39gg9kpc55i0ai9.apps.googleusercontent.com.json') ? '✅ Existe' : '❌ No existe' ?>
                Tokens: <?= file_exists(WRITEPATH . 'google_drive_tokens.json') ? '✅ Existe' : '❌ No existe' ?>
                Log Debug: <?= file_exists(WRITEPATH . 'logs/google_drive_debug.log') ? '✅ Existe' : '❌ No existe' ?>
                
                <strong>URLs importantes:</strong>
                Base URL: <?= base_url() ?>
                Callback URL: <?= base_url('debug/google-drive/callback') ?>
                
                <strong>Configuración:</strong>
                WRITEPATH: <?= WRITEPATH ?>
                Carpeta Raíz: ANVAR_Clientes
            </div>
        </div>
        
        <!-- Logs recientes -->
        <div class="debug-section">
            <h2>📋 Logs Recientes</h2>
            <div class="debug-output">
                <?php
                $logFile = WRITEPATH . 'logs/google_drive_debug.log';
                if (file_exists($logFile)) {
                    $logs = file_get_contents($logFile);
                    echo htmlspecialchars($logs);
                } else {
                    echo "No hay logs disponibles";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>