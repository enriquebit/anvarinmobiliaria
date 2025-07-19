/**
 * ANVAR Utilities - Manipulación de Datos
 * Funciones para trabajar con datos, arrays, objetos, etc.
 */

/**
 * Limpiar formato de moneda para obtener número puro
 * @param {string} moneda - Moneda formateada (ej: $1,234.56)
 * @returns {number} Número limpio
 */
function limpiarMoneda(moneda) {
    if (!moneda) return 0;
    return parseFloat(moneda.toString().replace(/[$,\s]/g, '')) || 0;
}

/**
 * Limpiar formato de número
 * @param {string} numero - Número formateado (ej: 1,234.56)
 * @returns {number} Número limpio
 */
function limpiarNumero(numero) {
    if (!numero) return 0;
    return parseFloat(numero.toString().replace(/[,\s]/g, '')) || 0;
}

/**
 * Buscar en array de objetos
 * @param {Array} array - Array a buscar
 * @param {string} campo - Campo a buscar
 * @param {any} valor - Valor a buscar
 * @returns {Object|null} Objeto encontrado o null
 */
function buscarEnArray(array, campo, valor) {
    if (!Array.isArray(array)) return null;
    return array.find(item => item[campo] === valor) || null;
}

/**
 * Filtrar array de objetos
 * @param {Array} array - Array a filtrar
 * @param {string} campo - Campo a filtrar
 * @param {any} valor - Valor a filtrar
 * @returns {Array} Array filtrado
 */
function filtrarArray(array, campo, valor) {
    if (!Array.isArray(array)) return [];
    return array.filter(item => item[campo] === valor);
}

/**
 * Ordenar array de objetos
 * @param {Array} array - Array a ordenar
 * @param {string} campo - Campo por el cual ordenar
 * @param {string} direccion - 'asc' o 'desc'
 * @returns {Array} Array ordenado
 */
function ordenarArray(array, campo, direccion = 'asc') {
    if (!Array.isArray(array)) return [];
    
    return [...array].sort((a, b) => {
        const valorA = a[campo];
        const valorB = b[campo];
        
        if (valorA < valorB) return direccion === 'asc' ? -1 : 1;
        if (valorA > valorB) return direccion === 'asc' ? 1 : -1;
        return 0;
    });
}

/**
 * Agrupar array por campo
 * @param {Array} array - Array a agrupar
 * @param {string} campo - Campo por el cual agrupar
 * @returns {Object} Objeto con grupos
 */
function agruparPor(array, campo) {
    if (!Array.isArray(array)) return {};
    
    return array.reduce((grupos, item) => {
        const clave = item[campo];
        if (!grupos[clave]) {
            grupos[clave] = [];
        }
        grupos[clave].push(item);
        return grupos;
    }, {});
}

/**
 * Calcular suma de campo en array
 * @param {Array} array - Array de objetos
 * @param {string} campo - Campo numérico a sumar
 * @returns {number} Suma total
 */
function sumarCampo(array, campo) {
    if (!Array.isArray(array)) return 0;
    
    return array.reduce((suma, item) => {
        const valor = parseFloat(item[campo]) || 0;
        return suma + valor;
    }, 0);
}

/**
 * Calcular promedio de campo en array
 * @param {Array} array - Array de objetos
 * @param {string} campo - Campo numérico
 * @returns {number} Promedio
 */
function promediarCampo(array, campo) {
    if (!Array.isArray(array) || array.length === 0) return 0;
    
    const suma = sumarCampo(array, campo);
    return suma / array.length;
}

/**
 * Encontrar valor máximo en campo
 * @param {Array} array - Array de objetos
 * @param {string} campo - Campo numérico
 * @returns {number} Valor máximo
 */
function maximoCampo(array, campo) {
    if (!Array.isArray(array) || array.length === 0) return 0;
    
    return Math.max(...array.map(item => parseFloat(item[campo]) || 0));
}

/**
 * Encontrar valor mínimo en campo
 * @param {Array} array - Array de objetos
 * @param {string} campo - Campo numérico
 * @returns {number} Valor mínimo
 */
function minimoCampo(array, campo) {
    if (!Array.isArray(array) || array.length === 0) return 0;
    
    return Math.min(...array.map(item => parseFloat(item[campo]) || 0));
}

/**
 * Remover duplicados de array basado en campo
 * @param {Array} array - Array de objetos
 * @param {string} campo - Campo único
 * @returns {Array} Array sin duplicados
 */
function removerDuplicados(array, campo) {
    if (!Array.isArray(array)) return [];
    
    const vistos = new Set();
    return array.filter(item => {
        const valor = item[campo];
        if (vistos.has(valor)) {
            return false;
        }
        vistos.add(valor);
        return true;
    });
}

/**
 * Generar UUID simple
 * @returns {string} UUID
 */
function generarUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

// Exportar funciones como propiedades globales
window.limpiarMoneda = limpiarMoneda;
window.limpiarNumero = limpiarNumero;
window.buscarEnArray = buscarEnArray;
window.filtrarArray = filtrarArray;
window.ordenarArray = ordenarArray;
window.agruparPor = agruparPor;
window.sumarCampo = sumarCampo;
window.promediarCampo = promediarCampo;
window.maximoCampo = maximoCampo;
window.minimoCampo = minimoCampo;
window.removerDuplicados = removerDuplicados;
window.generarUUID = generarUUID;