/**
 * ANVAR Utilities - UI y Interacción
 * Funciones para mejorar la experiencia de usuario
 */

/**
 * Mostrar toast de notificación
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} tipo - Tipo: success, error, warning, info
 * @param {number} duracion - Duración en ms (default: 5000)
 */
function mostrarToast(mensaje, tipo = 'info', duracion = 5000) {
    const alertClass = {
        success: 'alert-success',
        error: 'alert-danger',
        warning: 'alert-warning',
        info: 'alert-info'
    };
    
    const iconos = {
        success: '<i class="fas fa-check-circle mr-2"></i>',
        error: '<i class="fas fa-exclamation-circle mr-2"></i>',
        warning: '<i class="fas fa-exclamation-triangle mr-2"></i>',
        info: '<i class="fas fa-info-circle mr-2"></i>'
    };
    
    const toastId = 'toast-' + Date.now();
    const toast = $(`
        <div id="${toastId}" class="alert ${alertClass[tipo]} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
            ${iconos[tipo]}${mensaje}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(toast);
    
    // Auto-ocultar después de la duración especificada
    setTimeout(() => {
        $(`#${toastId}`).alert('close');
    }, duracion);
}

/**
 * Mostrar modal de confirmación
 * @param {string} titulo - Título del modal
 * @param {string} mensaje - Mensaje del modal
 * @param {function} callback - Función a ejecutar si confirma
 * @param {string} textoConfirmar - Texto del botón confirmar (default: "Confirmar")
 * @param {string} textoCancelar - Texto del botón cancelar (default: "Cancelar")
 */
function mostrarConfirmacion(titulo, mensaje, callback, textoConfirmar = 'Confirmar', textoCancelar = 'Cancelar') {
    if (typeof Swal !== 'undefined') {
        // Usar SweetAlert si está disponible
        Swal.fire({
            title: titulo,
            text: mensaje,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: textoConfirmar,
            cancelButtonText: textoCancelar
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    } else {
        // Fallback a confirm nativo
        if (confirm(`${titulo}\n\n${mensaje}`)) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    }
}

/**
 * Mostrar loading spinner en elemento
 * @param {string|jQuery} elemento - Selector o elemento jQuery
 * @param {string} mensaje - Mensaje de loading (opcional)
 */
function mostrarLoading(elemento, mensaje = 'Cargando...') {
    const $elemento = $(elemento);
    if ($elemento.length === 0) return;
    
    const loadingHtml = `
        <div class="anvar-loading text-center p-3">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">${mensaje}</span>
            </div>
            <div class="mt-2">${mensaje}</div>
        </div>
    `;
    
    $elemento.data('contenido-original', $elemento.html());
    $elemento.html(loadingHtml);
}

/**
 * Ocultar loading spinner
 * @param {string|jQuery} elemento - Selector o elemento jQuery
 */
function ocultarLoading(elemento) {
    const $elemento = $(elemento);
    if ($elemento.length === 0) return;
    
    const contenidoOriginal = $elemento.data('contenido-original');
    if (contenidoOriginal) {
        $elemento.html(contenidoOriginal);
        $elemento.removeData('contenido-original');
    } else {
        $elemento.find('.anvar-loading').remove();
    }
}

/**
 * Copiar texto al portapapeles
 * @param {string} texto - Texto a copiar
 * @param {function} callback - Función a ejecutar después de copiar
 */
function copiarAlPortapapeles(texto, callback) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(texto).then(() => {
            if (typeof callback === 'function') callback();
        });
    } else {
        // Fallback para navegadores antiguos
        const textarea = document.createElement('textarea');
        textarea.value = texto;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        if (typeof callback === 'function') callback();
    }
}

/**
 * Capitalizar primera letra de cada palabra
 * @param {string} texto - Texto a capitalizar
 * @returns {string} Texto capitalizado
 */
function capitalizarTexto(texto) {
    if (!texto) return '';
    return texto.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Truncar texto con puntos suspensivos
 * @param {string} texto - Texto a truncar
 * @param {number} longitud - Longitud máxima
 * @returns {string} Texto truncado
 */
function truncarTexto(texto, longitud) {
    if (!texto || texto.length <= longitud) return texto;
    return texto.substring(0, longitud) + '...';
}

/**
 * Debounce para limitar ejecución de funciones
 * @param {function} func - Función a ejecutar
 * @param {number} delay - Delay en ms
 * @returns {function} Función con debounce
 */
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Exportar funciones como propiedades globales
window.mostrarToast = mostrarToast;
window.mostrarConfirmacion = mostrarConfirmacion;
window.mostrarLoading = mostrarLoading;
window.ocultarLoading = ocultarLoading;
window.copiarAlPortapapeles = copiarAlPortapapeles;
window.capitalizarTexto = capitalizarTexto;
window.truncarTexto = truncarTexto;
window.debounce = debounce;