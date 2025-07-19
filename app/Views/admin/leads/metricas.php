<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Chart.js -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/chart.js/Chart.min.css') ?>">
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<!-- DateRange Picker -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
    <?php if (empty($item['url'])): ?>
        <li class="breadcrumb-item active"><?= $item['name'] ?></li>
    <?php else: ?>
        <li class="breadcrumb-item"><a href="<?= $item['url'] ?>"><?= $item['name'] ?></a></li>
    <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            
            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros de Análisis
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filtrosMetricas">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="filtro_periodo">Período</label>
                                    <input type="text" class="form-control" id="filtro_periodo" name="periodo" 
                                           value="<?= date('Y-m-01') ?> - <?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="filtro_agente">Agente Específico</label>
                                    <input type="text" class="form-control" id="filtro_agente" name="agente_id" 
                                           placeholder="ID del agente (opcional)">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-2"></i>
                                        Actualizar Métricas
                                    </button>
                                    <button type="button" class="btn btn-success ml-2" id="exportarMetricas">
                                        <i class="fas fa-download mr-2"></i>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas Generales -->
            <div class="row" id="estadisticasGenerales">
                <!-- Se cargan dinámicamente -->
            </div>

            <!-- Gráficos -->
            <div class="row">
                <!-- Gráfico de Registros por Desarrollo -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-2"></i>
                                Registros por Desarrollo
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartDesarrollos" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Estados de Sincronización -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-doughnut mr-2"></i>
                                Estados de Sincronización
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartSincronizacion" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Agentes -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-2"></i>
                        Top 10 Agentes Referidores
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablaTopAgentes">
                            <thead>
                                <tr>
                                    <th>Posición</th>
                                    <th>ID Agente</th>
                                    <th>Total Registros</th>
                                    <th>Valle Natura</th>
                                    <th>Cordelia</th>
                                    <th>HubSpot Exitosos</th>
                                    <th>Último Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se carga dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Métricas de Agente Específico -->
            <div class="card" id="metricasAgenteEspecifico" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-tie mr-2"></i>
                        Métricas Detalladas del Agente
                    </h3>
                </div>
                <div class="card-body">
                    <div id="contenidoMetricasAgente">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Actividad en Tiempo Real -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Registros con Errores
                            </h3>
                        </div>
                        <div class="card-body">
                            <div id="registrosErrores">
                                <!-- Se carga dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-2"></i>
                                Actividad Reciente
                            </h3>
                        </div>
                        <div class="card-body">
                            <div id="actividadReciente">
                                <!-- Se carga dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>


<!-- Modal para Detalles de Agente -->
<div class="modal fade" id="modalDetalleAgente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalle del Agente</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="contenidoModalAgente">
                    <!-- Se carga dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js -->
<script src="<?= base_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<!-- Moment.js -->
<script src="<?= base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<!-- DateRange Picker -->
<script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>

<script>
$(document).ready(function() {
    // Variables globales
    let chartDesarrollos = null;
    let chartSincronizacion = null;
    let tablaTopAgentes = null;

    // Inicializar componentes
    inicializarDateRangePicker();
    inicializarTablaTopAgentes();
    cargarMetricasIniciales();

    // Configurar actualización automática cada 5 minutos
    setInterval(function() {
        actualizarEstadisticasGenerales();
        actualizarActividadReciente();
    }, 300000);

    // ===============================================
    // INICIALIZACIÓN DE COMPONENTES
    // ===============================================

    function inicializarDateRangePicker() {
        $('#filtro_periodo').daterangepicker({
            startDate: moment().startOf('month'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                weekLabel: 'Sem',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

    }

    function inicializarTablaTopAgentes() {
        tablaTopAgentes = $('#tablaTopAgentes').DataTable({
            processing: true,
            searching: false,
            paging: false,
            info: false,
            order: [[2, 'desc']], // Ordenar por total registros
            language: {
                processing: 'Cargando...',
                emptyTable: 'No hay datos disponibles'
            },
            columnDefs: [
                {
                    targets: [3, 4, 5], // Columnas numéricas
                    className: 'text-center'
                },
                {
                    targets: 7, // Columna de acciones
                    orderable: false,
                    className: 'text-center'
                }
            ]
        });

    }

    // ===============================================
    // CARGA DE DATOS
    // ===============================================

    function cargarMetricasIniciales() {
        
        mostrarCargando();
        
        const periodo = $('#filtro_periodo').val().split(' - ');
        const agenteId = $('#filtro_agente').val();
        
        const params = {
            fecha_inicio: periodo[0],
            fecha_fin: periodo[1]
        };
        
        if (agenteId) {
            params.agente_id = agenteId;
        }

        $.get('/admin/leads/obtener-metricas', params)
            .done(function(response) {
                if (response.success) {
                    
                    mostrarEstadisticasGenerales(response.estadisticas_generales);
                    mostrarTopAgentes(response.top_agentes);
                    crearGraficos(response.estadisticas_generales);
                    
                    if (response.metricas_agente) {
                        mostrarMetricasAgenteEspecifico(response.metricas_agente, agenteId);
                    }
                } else {
                    mostrarError('Error al cargar métricas: ' + response.error);
                }
            })
            .fail(function(xhr, status, error) {
                mostrarError('Error de conexión al cargar métricas');
            })
            .always(function() {
                ocultarCargando();
            });

        // Cargar estadísticas adicionales
        actualizarEstadisticasGenerales();
        actualizarActividadReciente();
    }

    function actualizarEstadisticasGenerales() {
        $.get('/admin/leads/obtener-estadisticas')
            .done(function(response) {
                if (response.success) {
                    // Actualizar badges en tiempo real
                    actualizarBadgesEstadisticas(response);
                }
            })
            .fail(function(xhr, status, error) {
            });
    }

    function actualizarActividadReciente() {
        // TODO: Implementar carga de actividad reciente
    }

    // ===============================================
    // VISUALIZACIÓN DE DATOS
    // ===============================================

    function mostrarEstadisticasGenerales(stats) {
        const html = `
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>${stats.total_registros || 0}</h3>
                        <p>Total Registros</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>${stats.total_agentes_activos || 0}</h3>
                        <p>Agentes Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>${stats.hubspot_exitosos || 0}</h3>
                        <p>HubSpot Exitosos</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-hubspot"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>${stats.google_drive_exitosos || 0}</h3>
                        <p>Google Drive Exitosos</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-google-drive"></i>
                    </div>
                </div>
            </div>
        `;
        
        $('#estadisticasGenerales').html(html);
    }

    function mostrarTopAgentes(agentes) {
        tablaTopAgentes.clear();
        
        agentes.forEach(function(agente, index) {
            const posicion = index + 1;
            const badge = posicion <= 3 ? `<span class="badge badge-warning">#${posicion}</span>` : posicion;
            
            tablaTopAgentes.row.add([
                badge,
                agente.agente_referido,
                agente.total_registros,
                agente.valle_natura || 0,
                agente.cordelia || 0,
                agente.hubspot_exitosos || 0,
                moment(agente.ultimo_registro).format('DD/MM/YYYY HH:mm'),
                `<button class="btn btn-sm btn-info ver-detalle-agente" data-agente="${agente.agente_referido}">
                    <i class="fas fa-eye"></i> Ver Detalle
                </button>`
            ]);
        });
        
        tablaTopAgentes.draw();
    }

    function crearGraficos(stats) {
        // Gráfico de Desarrollos
        crearGraficoDesarrollos(stats);
        
        // Gráfico de Sincronización
        crearGraficoSincronizacion(stats);
    }

    function crearGraficoDesarrollos(stats) {
        const ctx = document.getElementById('chartDesarrollos').getContext('2d');
        
        if (chartDesarrollos) {
            chartDesarrollos.destroy();
        }
        
        chartDesarrollos = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Valle Natura', 'Cordelia'],
                datasets: [{
                    data: [stats.total_valle_natura || 0, stats.total_cordelia || 0],
                    backgroundColor: ['#007bff', '#28a745'],
                    hoverBackgroundColor: ['#0056b3', '#1e7e34']
                }]
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });
        
    }

    function crearGraficoSincronizacion(stats) {
        const ctx = document.getElementById('chartSincronizacion').getContext('2d');
        
        if (chartSincronizacion) {
            chartSincronizacion.destroy();
        }
        
        chartSincronizacion = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['HubSpot Exitosos', 'HubSpot Fallidos', 'Google Drive Exitosos', 'Google Drive Fallidos'],
                datasets: [{
                    data: [
                        stats.hubspot_exitosos || 0,
                        stats.hubspot_fallidos || 0,
                        stats.google_drive_exitosos || 0,
                        stats.google_drive_fallidos || 0
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#17a2b8', '#ffc107'],
                    hoverBackgroundColor: ['#1e7e34', '#bd2130', '#0f6674', '#d39e00']
                }]
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });
        
    }

    function mostrarMetricasAgenteEspecifico(metricas, agenteId) {
        const html = `
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Registros</span>
                            <span class="info-box-number">${metricas.total_registros || 0}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Éxito HubSpot</span>
                            <span class="info-box-number">${metricas.tasa_exito_hubspot || 0}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-percentage"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Éxito G.Drive</span>
                            <span class="info-box-number">${metricas.tasa_exito_google_drive || 0}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Con Co-propietario</span>
                            <span class="info-box-number">${metricas.porcentaje_con_copropietario || 0}%</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#contenidoMetricasAgente').html(html);
        $('#metricasAgenteEspecifico').show();
        
    }

    // ===============================================
    // EVENTOS
    // ===============================================

    // Formulario de filtros
    $('#filtrosMetricas').on('submit', function(e) {
        e.preventDefault();
        cargarMetricasIniciales();
    });

    // Ver detalle de agente
    $(document).on('click', '.ver-detalle-agente', function() {
        const agenteId = $(this).data('agente');
        
        $('#filtro_agente').val(agenteId);
        cargarMetricasIniciales();
    });

    // Exportar métricas
    $('#exportarMetricas').on('click', function() {
        
        const periodo = $('#filtro_periodo').val().split(' - ');
        const agenteId = $('#filtro_agente').val();
        
        let url = '/admin/leads/exportar-metricas?fecha_desde=' + periodo[0] + '&fecha_hasta=' + periodo[1];
        
        if (agenteId) {
            url += '&agente_referido=' + agenteId;
        }
        
        window.open(url, '_blank');
    });

    // ===============================================
    // UTILIDADES
    // ===============================================

    function mostrarCargando() {
        // TODO: Implementar overlay de carga
    }

    function ocultarCargando() {
        // TODO: Ocultar overlay de carga
    }

    function mostrarError(mensaje) {
        toastr.error(mensaje, 'Error');
    }

    function actualizarBadgesEstadisticas(response) {
        // TODO: Actualizar badges en tiempo real
    }

});
</script>
<?= $this->endSection() ?>