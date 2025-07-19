/**
 * DataTables Global Configuration
 * Sistema de gestión inmobiliaria ANVAR
 * 
 * Este archivo establece la configuración global para todas las tablas DataTables
 * en el proyecto, incluyendo la traducción al español.
 * 
 * @version 1.0.0
 * @author Sistema ANVAR
 */

(function($) {
    'use strict';
    
    // Esperar a que jQuery y DataTables estén disponibles
    $(document).ready(function() {
        
        // Verificar que DataTables esté cargado
        if (typeof $.fn.DataTable === 'undefined') {
            return;
        }
        
        // Configuración global de DataTables
        $.extend(true, $.fn.dataTable.defaults, {
            // Configuración de idioma - español
            "language": {
                "url": getBaseUrl() + "/assets/plugins/datatables/i18n/Spanish.json"
            },
            
            // Configuración de responsive
            "responsive": true,
            "autoWidth": false,
            
            // Configuración de paginación
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            "pageLength": 25,
            
            // Configuración de ordenamiento
            "order": [[0, 'desc']],
            
            // Configuración de procesamiento del lado del servidor
            "processing": true,
            "serverSide": false, // Por defecto false, se puede cambiar individualmente
            
            // Configuración de DOM - Sin búsqueda por defecto (f), se agrega individualmente si se necesita
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>rtip',
            
            // Configuración de estado - DESHABILITADO en desarrollo para evitar conflictos
            "stateSave": false,
            "stateDuration": 60 * 60 * 24, // 24 horas
            
            // Configuración de scrolling
            "scrollX": true,
            "scrollCollapse": true,
            
            // Configuración de búsqueda
            "search": {
                "smart": true,
                "regex": false,
                "caseInsensitive": true
            },
            
            // Configuración de callbacks
            "initComplete": function(settings, json) {
                // Reinicializar tooltips después de que se cargue la tabla
                setTimeout(function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }, 100);
            },
            
            "drawCallback": function(settings) {
                // Reinicializar tooltips después de cada redraw
                $('[data-toggle="tooltip"]').tooltip();
                
                // Reinicializar popovers si existen
                if (typeof $().popover === 'function') {
                    $('[data-toggle="popover"]').popover();
                }
            },
            
            // Configuración de error handling
            "error": function(xhr, error, thrown) {
                // Mostrar notificación de error si toastr está disponible
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error al cargar los datos de la tabla');
                }
            }
        });
        
        // Configuración específica para tablas con server-side processing
        $.fn.dataTable.ext.errMode = 'throw';
        
        // Configuración global de botones (si DataTables Buttons está disponible)
        if (typeof $.fn.DataTable.Buttons !== 'undefined') {
            $.extend(true, $.fn.dataTable.Buttons.defaults, {
                dom: {
                    button: {
                        className: 'btn btn-sm btn-outline-secondary'
                    }
                }
            });
        }
        
        // Configuración global de responsive (si DataTables Responsive está disponible)
        if (typeof $.fn.DataTable.Responsive !== 'undefined') {
            $.extend(true, $.fn.dataTable.Responsive.defaults, {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Detalles del registro #' + (data[0] || 'N/A');
                        }
                    }),
                    renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                        tableClass: 'table table-sm table-bordered'
                    })
                }
            });
        }
    });
    
    /**
     * Obtener la URL base del sitio
     */
    function getBaseUrl() {
        // Intentar obtener la URL base desde meta tag o variable global
        var baseUrl = '';
        
        // Opción 1: Desde meta tag
        var metaBase = $('meta[name="base-url"]').attr('content');
        if (metaBase) {
            baseUrl = metaBase;
        }
        
        // Opción 2: Desde variable global de CodeIgniter
        else if (typeof window.CI_BASE_URL !== 'undefined') {
            baseUrl = window.CI_BASE_URL;
        }
        
        // Opción 3: Construir desde window.location
        else {
            baseUrl = window.location.protocol + '//' + window.location.hostname + 
                     (window.location.port ? ':' + window.location.port : '');
        }
        
        return baseUrl;
    }
    
    /**
     * Función helper para crear DataTables con configuración estándar
     */
    window.createDataTable = function(selector, options) {
        options = options || {};
        
        // Merge con configuración por defecto
        var config = $.extend(true, {}, $.fn.dataTable.defaults, options);
        
        // Crear la tabla
        var table = $(selector).DataTable(config);
        
        // Agregar eventos personalizados si se especifican
        if (options.customEvents) {
            $.each(options.customEvents, function(event, handler) {
                table.on(event, handler);
            });
        }
        
        return table;
    };
    
    /**
     * Función helper para crear DataTables con server-side processing
     */
    window.createServerSideDataTable = function(selector, ajaxUrl, columns, options) {
        options = options || {};
        
        var config = $.extend(true, {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": ajaxUrl,
                "type": "POST",
                "error": function(xhr, error, thrown) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error al cargar los datos del servidor');
                    }
                }
            },
            "columns": columns
        }, options);
        
        return createDataTable(selector, config);
    };
    
    /**
     * Función helper para refrescar todas las tablas DataTables
     */
    window.refreshAllDataTables = function() {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().draw();
    };
    
    /**
     * Función helper para destruir y recrear una tabla DataTable
     */
    window.recreateDataTable = function(selector, options) {
        var $table = $(selector);
        
        // Destruir tabla existente si existe
        if ($.fn.DataTable.isDataTable(selector)) {
            $table.DataTable().destroy();
        }
        
        // Recrear tabla
        return createDataTable(selector, options);
    };
    
})(jQuery);