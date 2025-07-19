<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title') ?> | ANVAR Inmobiliaria</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/anvar-override.css') ?>">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') ?>">
    
    <!-- Custom CSS for public forms -->
    <style>
        body {
            background: #f5f7fa;
            min-height: 100vh;
        }
        .registration-container {
            margin: 2rem auto;
            max-width: 900px;
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-logo img {
            max-height: 80px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        .step.active .step-number {
            background: #28a745;
            color: white;
        }
        .step.completed .step-number {
            background: #17a2b8;
            color: white;
        }
        .step-line {
            width: 50px;
            height: 2px;
            background: #e9ecef;
            margin: 0 1rem;
        }
        .step.completed + .step-line {
            background: #17a2b8;
        }
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background-color: #f8f9fa;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            user-select: none;
        }
        .file-upload-area:hover {
            border-color: #1a1360;
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 19, 96, 0.1);
        }
        .file-upload-area:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(26, 19, 96, 0.2);
        }
        .file-upload-area.dragover {
            border-color: #07c15b;
            background-color: #e8f5e9;
            transform: scale(1.02);
        }
        .file-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            margin: 1rem auto;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
        }
        .loading-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>
<body class="hold-transition">
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-border text-light" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-3">Procesando información...</p>
        </div>
    </div>

    <!-- Brand Logo -->
    <div class="brand-logo">
        <img src="<?= base_url('assets/img/logo_admin.png') ?>" alt="ANVAR Inmobiliaria" class="img-fluid">
    </div>

    <!-- Main Content -->
    <div class="registration-container">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Footer -->
    <footer class="text-center text-white mt-5">
        <p>&copy; <?= date('Y') ?> ANVAR Inmobiliaria. Todos los derechos reservados.</p>
    </footer>

    <!-- jQuery -->
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <!-- Bootstrap 4 -->
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?= base_url('assets/adminlte/js/adminlte.min.js') ?>"></script>
    <!-- SweetAlert2 -->
    <script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>
    <!-- bs-custom-file-input -->
    <script src="<?= base_url('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') ?>"></script>

    <script>
        // Utility functions for debugging
        function debugLog(message, data = null) {
            // Send to backend for logging
            if (typeof data === 'object') {
                $.post('/api/debug-log', {
                    message: message,
                    data: JSON.stringify(data),
                    timestamp: new Date().toISOString(),
                    page: window.location.pathname
                }).catch(function(error) {
                });
            }
        }

        // Global error handler
        window.addEventListener('error', function(e) {
            debugLog('JavaScript Error', {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                stack: e.error ? e.error.stack : null
            });
        });

        // Loading overlay functions
        function showLoading(message = 'Procesando información...') {
            $('#loadingOverlay .loading-content p').text(message);
            $('#loadingOverlay').fadeIn();
            debugLog('Loading overlay shown', { message: message });
        }

        function hideLoading() {
            $('#loadingOverlay').fadeOut();
            debugLog('Loading overlay hidden');
        }

        // Initialize on page load
        $(document).ready(function() {
            debugLog('Page loaded', { 
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString()
            });
            
            // Initialize file input
            bsCustomFileInput.init();
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>