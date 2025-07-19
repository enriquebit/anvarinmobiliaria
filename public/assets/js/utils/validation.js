/**
 * ANVAR Utilities - Validaciones
 * Funciones de validación para formularios y datos
 */

/**
 * Validar si un string es un número válido
 * @param {string} valor - Valor a validar
 * @returns {boolean} True si es número válido
 */
function esNumeroValido(valor) {
    return !isNaN(parseFloat(valor)) && isFinite(valor);
}

/**
 * Validar email
 * @param {string} email - Email a validar
 * @returns {boolean} True si email es válido
 */
function esEmailValido(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validar teléfono mexicano
 * @param {string} telefono - Teléfono a validar
 * @returns {boolean} True si teléfono es válido
 */
function esTelefonoValido(telefono) {
    // Remover caracteres no numéricos
    const numeros = telefono.replace(/\D/g, '');
    // Validar 10 dígitos para México
    return numeros.length === 10;
}

/**
 * Validar CURP mexicano
 * @param {string} curp - CURP a validar
 * @returns {boolean} True si CURP es válido
 */
function esCurpValido(curp) {
    const regex = /^[A-Z]{1}[AEIOU]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}$/;
    return regex.test(curp.toUpperCase());
}

/**
 * Validar RFC mexicano
 * @param {string} rfc - RFC a validar
 * @returns {boolean} True si RFC es válido
 */
function esRfcValido(rfc) {
    // RFC persona física: 4 letras + 6 dígitos + 3 caracteres
    const regexFisica = /^[A-Z]{4}[0-9]{6}[A-Z0-9]{3}$/;
    // RFC persona moral: 3 letras + 6 dígitos + 3 caracteres
    const regexMoral = /^[A-Z]{3}[0-9]{6}[A-Z0-9]{3}$/;
    
    const rfcUpper = rfc.toUpperCase();
    return regexFisica.test(rfcUpper) || regexMoral.test(rfcUpper);
}

/**
 * Validar campo requerido
 * @param {any} valor - Valor a validar
 * @returns {boolean} True si no está vacío
 */
function esRequerido(valor) {
    if (valor === null || valor === undefined) return false;
    if (typeof valor === 'string') return valor.trim().length > 0;
    if (Array.isArray(valor)) return valor.length > 0;
    return true;
}

/**
 * Validar longitud mínima
 * @param {string} valor - Valor a validar
 * @param {number} min - Longitud mínima
 * @returns {boolean} True si cumple longitud mínima
 */
function longitudMinima(valor, min) {
    return valor && valor.length >= min;
}

/**
 * Validar longitud máxima
 * @param {string} valor - Valor a validar
 * @param {number} max - Longitud máxima
 * @returns {boolean} True si cumple longitud máxima
 */
function longitudMaxima(valor, max) {
    return !valor || valor.length <= max;
}

/**
 * Validar rango numérico
 * @param {number} valor - Valor a validar
 * @param {number} min - Valor mínimo
 * @param {number} max - Valor máximo
 * @returns {boolean} True si está en rango
 */
function enRango(valor, min, max) {
    const num = parseFloat(valor);
    return !isNaN(num) && num >= min && num <= max;
}

// Exportar funciones como propiedades globales
window.esNumeroValido = esNumeroValido;
window.esEmailValido = esEmailValido;
window.esTelefonoValido = esTelefonoValido;
window.esCurpValido = esCurpValido;
window.esRfcValido = esRfcValido;
window.esRequerido = esRequerido;
window.longitudMinima = longitudMinima;
window.longitudMaxima = longitudMaxima;
window.enRango = enRango;