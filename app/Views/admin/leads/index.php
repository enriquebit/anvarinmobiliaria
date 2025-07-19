<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
<!-- DateRange Picker -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css') ?>">
<!-- Select2 -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
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
            
            <!-- Filtros y acciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros y Acciones
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="<?= base_url('admin/leads/conversiones') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Conversiones
                            </a>
                            <a href="<?= base_url('admin/leads/metricas') ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-chart-line mr-2"></i>
                                Ver Métricas
                            </a>
                            <a href="<?= base_url('admin/leads/logs') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-list-alt mr-2"></i>
                                Ver Logs
                            </a>
                            <button type="button" class="btn btn-warning btn-sm" id="exportarRegistros">
                                <i class="fas fa-download mr-2"></i>
                                Exportar
                            </button>
                        </div>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filtrosRegistros">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_periodo">Período</label>
                                    <input type="text" class="form-control" id="filtro_periodo" name="periodo">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="filtro_agente">Agente</label>
                                    <input type="text" class="form-control" id="filtro_agente" name="agente_referido" 
                                           placeholder="ID del agente">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="filtro_desarrollo">Desarrollo</label>
                                    <select class="form-control" id="filtro_desarrollo" name="desarrollo">
                                        <option value="">Todos</option>
                                        <option value="valle_natura">Valle Natura</option>
                                        <option value="cordelia">Cordelia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="filtro_hubspot">Estado HubSpot</label>
                                    <select class="form-control" id="filtro_hubspot" name="hubspot_sync_status">
                                        <option value="">Todos</option>
                                        <option value="success">Exitoso</option>
                                        <option value="error">Error</option>
                                        <option value="pending">Pendiente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="filtro_drive">Estado G.Drive</label>
                                    <select class="form-control" id="filtro_drive" name="google_drive_sync_status">
                                        <option value="">Todos</option>
                                        <option value="success">Exitoso</option>
                                        <option value="error">Error</option>
                                        <option value="pending">Pendiente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary ml-1" id="limpiarFiltros">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row" id="estadisticasRapidas">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total-registros">-</h3>
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
                            <h3 id="hubspot-exitosos">-</h3>
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
                            <h3 id="drive-exitosos">-</h3>
                            <p>Google Drive Exitosos</p>
                        </div>
                        <div class="icon">
                            <i class="fab fa-google-drive"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="con-errores">-</h3>
                            <p>Con Errores</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de registros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Lista de Registros
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary" id="contador-registros">0 registros</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablaRegistros">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Desarrollo</th>
                                    <th>Agente</th>
                                    <th>HubSpot</th>
                                    <th>G. Drive</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables carga los datos -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


<!-- Modal para ver detalle rápido -->
<div class="modal fade" id="modalDetalleRapido" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalle del Registro</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="contenidoDetalleRapido">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="#" class="btn btn-primary" id="verDetalleCompleto">Ver Detalle Completo</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<!-- Moment.js -->
<script src="<?= base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<!-- DateRange Picker -->
<script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>
<!-- Select2 -->
<script src="<?= base_url('assets/plugins/select2/js/select2.full.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // Variables globales
    let tablaRegistros = null;
    let estadisticasTimer = null;

    // Inicializar componentes
    inicializarDateRangePicker();
    inicializarTablaRegistros();
    cargarEstadisticasRapidas();

    // Actualizar estadísticas cada 30 segundos
    estadisticasTimer = setInterval(cargarEstadisticasRapidas, 30000);

    // ===============================================
    // INICIALIZACIÓN
    // ===============================================

    function inicializarDateRangePicker() {
        $('#filtro_periodo').daterangepicker({
            startDate: moment().subtract(7, 'days'),
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

    function inicializarTablaRegistros() {
        tablaRegistros = $('#tablaRegistros').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('admin/leads/obtener-registros') ?>',
                type: 'GET',
                data: function(d) {
                    // Agregar filtros personalizados
                    const periodo = $('#filtro_periodo').val().split(' - ');
                    d.fecha_desde = periodo[0];
                    d.fecha_hasta = periodo[1];
                    d.agente_referido = $('#filtro_agente').val();
                    d.desarrollo = $('#filtro_desarrollo').val();
                    d.hubspot_sync_status = $('#filtro_hubspot').val();
                    d.google_drive_sync_status = $('#filtro_drive').val();
                },
                dataSrc: function(json) {
                    // Actualizar contador
                    $('#contador-registros').text(json.recordsFiltered + ' registros');
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.error('[REGISTRO-CLIENTES] Error cargando datos:', error);
                    toastr.error('Error al cargar los registros', 'Error');
                }
            },
            columns: [
                { data: 'folio', name: 'folio' },
                { data: 'nombre_completo', name: 'nombre_completo' },
                { 
                    data: null,
                    render: function(data) {
                        return data.email + '<br><small class="text-muted">' + data.telefono + '</small>';
                    },
                    orderable: false
                },
                { data: 'desarrollo', name: 'desarrollo' },
                { data: 'agente_referido', name: 'agente_referido' },
                { 
                    data: 'hubspot_sync',
                    name: 'hubspot_sync_status',
                    orderable: false
                },
                { 
                    data: 'google_drive_sync',
                    name: 'google_drive_sync_status', 
                    orderable: false
                },
                { 
                    data: null,
                    render: function(data) {
                        return data.fecha_registro + '<br><small class="text-muted">' + data.tiempo_transcurrido + '</small>';
                    },
                    orderable: false
                },
                { 
                    data: 'acciones',
                    name: 'acciones',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[7, 'desc']], // Ordenar por fecha de registro desc
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: 'Procesando...',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                loadingRecords: 'Cargando...',
                zeroRecords: 'No se encontraron registros coincidentes',
                emptyTable: 'No hay datos disponibles en la tabla',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último'
                }
            }
        });

    }

    // ===============================================
    // CARGA DE DATOS
    // ===============================================

    function cargarEstadisticasRapidas() {
        $.get('<?= base_url('admin/leads/obtener-estadisticas') ?>')
            .done(function(response) {
                if (response.success) {
                    const stats = response.estadisticas_registros;
                    
                    $('#total-registros').text(stats.total_registros || 0);
                    $('#hubspot-exitosos').text(stats.hubspot_exitosos || 0);
                    $('#drive-exitosos').text(stats.google_drive_exitosos || 0);
                    $('#con-errores').text(response.registros_con_errores || 0);
                    
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[REGISTRO-CLIENTES] Error cargando estadísticas:', error);
            });
    }

    // ===============================================
    // EVENTOS
    // ===============================================

    // Aplicar filtros
    $('#filtrosRegistros').on('submit', function(e) {
        e.preventDefault();
        tablaRegistros.ajax.reload();
    });

    // Limpiar filtros
    $('#limpiarFiltros').on('click', function() {
        
        $('#filtro_periodo').data('daterangepicker').setStartDate(moment().subtract(7, 'days'));
        $('#filtro_periodo').data('daterangepicker').setEndDate(moment());
        $('#filtro_agente').val('');
        $('#filtro_desarrollo').val('');
        $('#filtro_hubspot').val('');
        $('#filtro_drive').val('');
        
        tablaRegistros.ajax.reload();
    });

    // Exportar registros
    $('#exportarRegistros').on('click', function() {
        
        const periodo = $('#filtro_periodo').val().split(' - ');
        let url = '<?= base_url('admin/leads/exportar-registros') ?>?fecha_desde=' + periodo[0] + '&fecha_hasta=' + periodo[1];
        
        const agente = $('#filtro_agente').val();
        const desarrollo = $('#filtro_desarrollo').val();
        
        if (agente) url += '&agente_referido=' + agente;
        if (desarrollo) url += '&desarrollo=' + desarrollo;
        
        window.open(url, '_blank');
    });

    // Reenviar a HubSpot
    $(document).on('click', '.reenviar-hubspot', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post('<?= base_url('admin/leads/reenviar-hubspot') ?>/' + id)
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito');
                    tablaRegistros.ajax.reload(null, false);
                } else {
                    toastr.error(response.error || 'Error desconocido', 'Error');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[REGISTRO-CLIENTES] Error reenviando a HubSpot:', error);
                toastr.error('Error de conexión', 'Error');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fab fa-hubspot"></i>');
            });
    });

    // Reenviar a Google Drive
    $(document).on('click', '.reenviar-drive', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post('<?= base_url('admin/leads/reenviar-google-drive') ?>/' + id)
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito');
                    tablaRegistros.ajax.reload(null, false);
                } else {
                    toastr.error(response.error || 'Error desconocido', 'Error');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[REGISTRO-CLIENTES] Error reenviando a Google Drive:', error);
                toastr.error('Error de conexión', 'Error');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fab fa-google-drive"></i>');
            });
    });

    // Limpiar timer al salir
    $(window).on('beforeunload', function() {
        if (estadisticasTimer) {
            clearInterval(estadisticasTimer);
        }
    });

});
</script>
<?= $this->endSection() ?>