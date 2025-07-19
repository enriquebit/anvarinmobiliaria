<!-- Main Footer -->
<footer class="main-footer">
  <div class="float-right d-none d-sm-block">
    <strong>Cliente:</strong> <?= userName() ?> | 
    <strong>Último acceso:</strong> <?= date('d/m/Y H:i') ?>
  </div>
  
  <strong>
    &copy; <?= date('Y') ?> 
    <a href="<?= site_url('/') ?>" class="text-success">Anvar Inmobiliaria</a>
  </strong>
  
  - Tu hogar, nuestra prioridad.
  
  <span class="ml-3">
    <i class="fas fa-phone text-success"></i>
    <a href="tel:+525512345678" class="text-success">55 1234 5678</a>
  </span>
  
  <span class="ml-3">
    <i class="fas fa-envelope text-success"></i>
    <a href="mailto:clientes@anvarinmobiliaria.com" class="text-success">clientes@anvarinmobiliaria.com</a>
  </span>
</footer>

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
    
    //console.log('✅ Normalización de inputs a mayúsculas activada (Cliente)');
});
</script>