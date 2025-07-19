/**
 * ANVAR Utilities - Formateo de Datos
 * Funciones de formateo para monedas, números, fechas, etc.
 */

/**
 * Formatear moneda en formato mexicano - FUNCIÓN ÚNICA DEL PROYECTO
 * Usa formato manual para evitar problemas de localización del navegador
 * @param {number|string} numero - Número a formatear
 * @returns {string} Moneda formateada (ej: $1,234.56)
 */
function formatearMoneda(numero) {
    if (!numero && numero !== 0) return '$0.00';
    const num = parseFloat(numero);
    if (isNaN(num)) return '$0.00';
    
    // Formato manual para garantizar consistencia: $1,234.56
    const parts = num.toFixed(2).split('.');
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return `$${integerPart}.${parts[1]}`;
}

/**
 * Formatear número con separadores de miles
 * @param {number|string} numero - Número a formatear
 * @param {number} decimales - Número de decimales (default: 2)
 * @returns {string} Número formateado (ej: 1,234.56)
 */
function formatearNumero(numero, decimales = 2) {
    if (!numero && numero !== 0) return '0';
    const num = parseFloat(numero);
    if (isNaN(num)) return '0';
    
    const parts = num.toFixed(decimales).split('.');
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return decimales > 0 ? `${integerPart}.${parts[1]}` : integerPart;
}

/**
 * Formatear porcentaje
 * @param {number|string} numero - Número a formatear
 * @param {number} decimales - Número de decimales (default: 2)
 * @returns {string} Porcentaje formateado (ej: 15.50%)
 */
function formatearPorcentaje(numero, decimales = 2) {
    if (!numero && numero !== 0) return '0%';
    const num = parseFloat(numero);
    if (isNaN(num)) return '0%';
    
    return formatearNumero(num, decimales) + '%';
}

/**
 * Formatear área en metros cuadrados
 * @param {number|string} metros - Metros cuadrados
 * @returns {string} Área formateada (ej: 150.25 m²)
 */
function formatearArea(metros) {
    if (!metros && metros !== 0) return '0 m²';
    const num = parseFloat(metros);
    if (isNaN(num)) return '0 m²';
    
    return formatearNumero(num, 2) + ' m²';
}

/**
 * Formatear fecha en español
 * @param {string|Date} fecha - Fecha a formatear
 * @param {boolean} incluirHora - Si incluir hora (default: false)
 * @returns {string} Fecha formateada
 */
function formatearFecha(fecha, incluirHora = false) {
    if (!fecha) return '';
    
    const date = new Date(fecha);
    if (isNaN(date.getTime())) return '';
    
    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    };
    
    if (incluirHora) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    
    return date.toLocaleDateString('es-MX', options);
}

/**
 * Formatear folio con padding de ceros
 * @param {number|string} numero - Número a formatear
 * @param {number} longitud - Longitud total (default: 6)
 * @param {string} prefijo - Prefijo opcional
 * @returns {string} Folio formateado (ej: APT-000123)
 */
function formatearFolio(numero, longitud = 6, prefijo = '') {
    const num = parseInt(numero) || 0;
    const padded = num.toString().padStart(longitud, '0');
    return prefijo ? `${prefijo}-${padded}` : padded;
}

// Exportar funciones como propiedades globales
window.formatearMoneda = formatearMoneda;
window.formatearNumero = formatearNumero;
window.formatearPorcentaje = formatearPorcentaje;
window.formatearArea = formatearArea;
window.formatearFecha = formatearFecha;
window.formatearFolio = formatearFolio;

// Aliases para compatibilidad con código existente
window.formatPrecio = formatearMoneda;
window.formatCurrency = formatearMoneda;
window.formatearPrecio = formatearMoneda;