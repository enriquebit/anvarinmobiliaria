<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('page_title') ?: $this->renderSection('title') ?: 'Autenticación' ?> | Anvar Inmobiliaria</title>

    <!-- ===== FAVICON ===== -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏠</text></svg>">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome (CDN más confiable) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    
    <!-- AdminLTE Theme (solo base) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- ANVAR AUTH STYLES - ESTILOS DORADOS PARA AUTENTICACIÓN -->
    <link rel="stylesheet" href="<?= base_url('assets/css/anvar-auth.css') ?>">
</head>
<body class="hold-transition login-page">

    <!-- Contenido principal -->
    <?= $this->renderSection('content') ?>

    <!-- jQuery (CDN confiable) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 4 (CDN confiable) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AdminLTE App (CDN confiable) -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- Scripts adicionales -->
    <?= $this->renderSection('scripts') ?>

    <!-- ===== NORMALIZACIÓN DE INPUTS A MAYÚSCULAS ===== -->
    <script>
    $(document).ready(function() {
        // Función para convertir texto a mayúsculas preservando acentos
        function toUpperCaseWithAccents(text) {
            return text.toUpperCase();
        }
        
        // Aplicar normalización a todos los inputs de texto, textarea (EXCLUYE email y password)
        function normalizeInputs() {
            // Selectores de elementos que necesitan normalización (EXCLUYE email y password)
            var inputSelectors = [
                'input[type="text"]',
                'input[type="search"]',
                'textarea',
                'input:not([type="password"]):not([type="email"]):not([type="number"]):not([type="date"]):not([type="time"]):not([type="datetime-local"]):not([type="hidden"]):not([type="file"]):not([type="checkbox"]):not([type="radio"]):not([type="url"])'
            ].join(',');
            
            // Aplicar normalización en tiempo real (mientras el usuario escribe)
            $(document).on('input keyup paste', inputSelectors, function() {
                var $this = $(this);
                var cursorPosition = this.selectionStart;
                var originalValue = $this.val();
                var normalizedValue = toUpperCaseWithAccents(originalValue);
                
                // Solo actualizar si hay cambios para evitar bucles infinitos
                if (originalValue !== normalizedValue) {
                    $this.val(normalizedValue);
                    // Restaurar posición del cursor
                    this.setSelectionRange(cursorPosition, cursorPosition);
                }
            });
            
            // Aplicar normalización también al perder el foco
            $(document).on('blur', inputSelectors, function() {
                var $this = $(this);
                var normalizedValue = toUpperCaseWithAccents($this.val());
                $this.val(normalizedValue);
            });
        }
        
        // Inicializar normalización
        normalizeInputs();
        
        // Normalizar inputs existentes al cargar la página (EXCLUYE email y password)
        $('input[type="text"], input[type="search"], textarea').each(function() {
            var $this = $(this);
            var normalizedValue = toUpperCaseWithAccents($this.val());
            $this.val(normalizedValue);
        });
        
        // Excluir ciertos campos que no deben ser normalizados
        var excludedFields = [
            'input[name*="password"]',
            'input[name*="email"]',
            'input[name*="url"]',
            'input[type="password"]',
            'input[type="email"]',
            'input[type="url"]',
            '.no-normalize'
        ];
        
        // Remover normalización de campos excluidos
        $(document).off('input keyup paste blur', excludedFields.join(','));
        
    });
    </script>

</body>
</html>