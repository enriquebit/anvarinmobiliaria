/**
 * ANVAR Utilities - Archivo Principal
 * Sistema de gestión inmobiliaria ANVAR
 * 
 * Este archivo carga todas las utilidades organizadas por categoría.
 * Mantiene compatibilidad con el código existente.
 * 
 * Estructura:
 * - utils/format.js: Formateo de datos (monedas, números, fechas)
 * - utils/validation.js: Validaciones de formularios
 * - utils/ui.js: Funciones de interfaz de usuario
 * - utils/data.js: Manipulación de datos y arrays
 * 
 * @version 2.0.0
 * @author Sistema ANVAR
 */

(function() {
    'use strict';
    
    // Configuración base
    const ANVAR_UTILS_VERSION = '2.0.0';
    const BASE_URL = window.location.origin;
    const DEBUG_MODE = false; // Cambiar a true para habilitar logs de debug
    
    // Debug helper
    function log(mensaje) {
        // Debug logging disabled
    }
    
    // Función para cargar script dinámicamente
    function cargarScript(url) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }
    
    // Lista de utilidades a cargar
    const utilidades = [
        '/assets/js/utils/format.js',
        '/assets/js/utils/validation.js', 
        '/assets/js/utils/ui.js',
        '/assets/js/utils/data.js'
    ];
    
    // Cargar todas las utilidades
    async function inicializarUtilidades() {
        try {
            log('Iniciando carga de utilidades...');
            
            for (const utilidad of utilidades) {
                await cargarScript(BASE_URL + utilidad);
                log(`Cargado: ${utilidad}`);
            }
            
            log('✅ Todas las utilidades cargadas correctamente');
            
            // Ejecutar callback de inicialización si existe
            if (typeof window.onAnvarUtilsLoaded === 'function') {
                window.onAnvarUtilsLoaded();
            }
            
            // Emitir evento personalizado
            window.dispatchEvent(new CustomEvent('anvarUtilsLoaded', {
                detail: { version: ANVAR_UTILS_VERSION }
            }));
            
        } catch (error) {
            // Cargar funciones básicas de emergencia
            cargarFuncionesEmergencia();
        }
    }
    
    // Funciones básicas de emergencia en caso de fallo
    function cargarFuncionesEmergencia() {
        log('⚠️ Cargando funciones de emergencia...');
        
        // Función básica de formateo de moneda
        if (typeof window.formatearMoneda === 'undefined') {
            window.formatearMoneda = function(numero) {
                if (!numero && numero !== 0) return '$0.00';
                const num = parseFloat(numero);
                if (isNaN(num)) return '$0.00';
                const parts = num.toFixed(2).split('.');
                const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                return `$${integerPart}.${parts[1]}`;
            };
        }
        
        // Toast básico
        if (typeof window.mostrarToast === 'undefined') {
            window.mostrarToast = function(mensaje, tipo = 'info') {
                alert(`${tipo.toUpperCase()}: ${mensaje}`);
            };
        }
        
        // Aliases básicos
        window.formatPrecio = window.formatearMoneda;
        window.formatCurrency = window.formatearMoneda;
        
        log('⚠️ Funciones de emergencia cargadas');
    }
    
    // Información del sistema
    window.ANVAR_UTILS = {
        version: ANVAR_UTILS_VERSION,
        loaded: false,
        modules: ['format', 'validation', 'ui', 'data']
    };
    
    // Iniciar carga cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarUtilidades);
    } else {
        inicializarUtilidades();
    }
    
})();