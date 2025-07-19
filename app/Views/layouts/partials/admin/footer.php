<!-- Main Footer -->
<footer class="main-footer">
  <div class="float-right d-none d-sm-block">
    <strong>Versión:</strong> 1.0.0 | 
    <strong>Usuario:</strong> <?= userName() ?> (<?= userRole() ?>)
  </div>
  
  <strong>
    &copy; <?= date('Y') ?> 
    <a href="<?= site_url('/') ?>" class="text-primary">Anvar Inmobiliaria</a>
  </strong>
  
  Todos los derechos reservados.
  
  <span class="ml-3">
    <i class="fas fa-shield-alt text-success"></i>
    Sistema seguro
  </span>
</footer>

<!-- ===== NORMALIZACIÓN DE INPUTS A MAYÚSCULAS ===== -->
<script>
$(document).ready(function() {
    // Función para convertir texto a mayúsculas preservando acentos
    function toUpperCaseWithAccents(text) {
        return text.toUpperCase();
    }
    
    // Aplicar normalización a todos los inputs de texto, textarea y selects
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
        
        // Normalizar inputs que se cargan dinámicamente usando MutationObserver
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                var $newElement = $(node);
                                if ($newElement.is('input, textarea') || $newElement.find('input, textarea').length > 0) {
                                    setTimeout(function() {
                                        $newElement.find(inputSelectors).each(function() {
                                            var $input = $(this);
                                            var normalizedValue = toUpperCaseWithAccents($input.val());
                                            $input.val(normalizedValue);
                                        });
                                    }, 100);
                                }
                            }
                        });
                    }
                });
            });
            
            // Observar cambios en todo el documento
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
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
    
    //console.log('✅ Normalización de inputs a mayúsculas activada');
});
</script>