<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
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
            
            <!-- Resumen de errores -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= count($registros) ?></h3>
                            <p>Registros con Errores</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="errores-hubspot">-</h3>
                            <p>Errores HubSpot</p>
                        </div>
                        <div class="icon">
                            <i class="fab fa-hubspot"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3 id="errores-drive">-</h3>
                            <p>Errores Google Drive</p>
                        </div>
                        <div class="icon">
                            <i class="fab fa-google-drive"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="pendientes">-</h3>
                            <p>Pendientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($registros)): ?>
            <!-- Sin errores -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="py-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h3>¡Excelente!</h3>
                        <p class="text-muted">No hay registros con errores en este momento.</p>
                        <a href="<?= site_url('/admin/leads') ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Acciones rápidas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools mr-2"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="btn-group">
                        <a href="<?= site_url('/admin/leads') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                        </a>
                        <button type="button" class="btn btn-warning" id="reenviarTodosHubSpot">
                            <i class="fab fa-hubspot mr-2"></i>Reenviar Todos a HubSpot
                        </button>
                        <button type="button" class="btn btn-success" id="reenviarTodosDrive">
                            <i class="fab fa-google-drive mr-2"></i>Reenviar Todos a Google Drive
                        </button>
                        <a href="<?= site_url('/admin/leads/logs?errores=1') ?>" class="btn btn-info">
                            <i class="fas fa-list-alt mr-2"></i>Ver Logs de Errores
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabla de registros con errores -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Registros con Errores
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger"><?= count($registros) ?> registros</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablaErrores">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="seleccionarTodos">
                                    </th>
                                    <th>Folio</th>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Desarrollo</th>
                                    <th>Error HubSpot</th>
                                    <th>Error Google Drive</th>
                                    <th>Último Intento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros as $registro): ?>
                                <tr data-id="<?= $registro->id ?>">
                                    <td>
                                        <input type="checkbox" class="seleccionar-registro" value="<?= $registro->id ?>">
                                    </td>
                                    <td>
                                        <a href="<?= site_url('/admin/leads/show/' . $registro->id) ?>" class="badge badge-info">
                                            <?= $registro->folio ?? 'REG-' . date('Y') . '-' . str_pad($registro->id, 6, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?= $registro->getNombreCompleto() ?></strong>
                                        <?php if (!empty($registro->agente_referido)): ?>
                                            <br><small class="text-muted">Agente: <?= $registro->agente_referido ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= $registro->email ?>"><?= $registro->email ?></a>
                                        <br><a href="tel:<?= $registro->telefono ?>"><?= $registro->getTelefonoFormateado() ?></a>
                                    </td>
                                    <td>
                                        <span class="badge <?= $registro->desarrollo === 'valle_natura' ? 'badge-primary' : 'badge-success' ?>">
                                            <?= $registro->getDesarrolloTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($registro->falloSincronizacionHubSpot()): ?>
                                            <span class="badge badge-danger">Error</span>
                                            <?php if (!empty($registro->hubspot_sync_error)): ?>
                                                <br><small class="text-danger"><?= substr($registro->hubspot_sync_error, 0, 50) ?>...</small>
                                            <?php endif; ?>
                                        <?php elseif ($registro->hubspot_sync_status === 'pending'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">OK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($registro->falloSincronizacionGoogleDrive()): ?>
                                            <span class="badge badge-danger">Error</span>
                                            <?php if (!empty($registro->google_drive_sync_error)): ?>
                                                <br><small class="text-danger"><?= substr($registro->google_drive_sync_error, 0, 50) ?>...</small>
                                            <?php endif; ?>
                                        <?php elseif ($registro->google_drive_sync_status === 'pending'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">OK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $ultimoIntento = max(
                                            strtotime($registro->hubspot_last_sync ?? '1970-01-01'),
                                            strtotime($registro->created_at ?? '1970-01-01')
                                        );
                                        ?>
                                        <?= date('d/m/Y H:i', $ultimoIntento) ?>
                                        <br><small class="text-muted"><?= \Carbon\Carbon::createFromTimestamp($ultimoIntento)->diffForHumans() ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <a href="<?= site_url('/admin/leads/show/' . $registro->id) ?>" 
                                               class="btn btn-info btn-sm" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($registro->falloSincronizacionHubSpot()): ?>
                                            <button class="btn btn-warning btn-sm reenviar-hubspot" 
                                                    data-id="<?= $registro->id ?>" title="Reenviar a HubSpot">
                                                <i class="fab fa-hubspot"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($registro->falloSincronizacionGoogleDrive()): ?>
                                            <button class="btn btn-success btn-sm reenviar-drive" 
                                                    data-id="<?= $registro->id ?>" title="Reenviar a Google Drive">
                                                <i class="fab fa-google-drive"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning" id="reenviarSeleccionadosHubSpot" disabled>
                                    <i class="fab fa-hubspot mr-2"></i>Reenviar Seleccionados a HubSpot
                                </button>
                                <button type="button" class="btn btn-success" id="reenviarSeleccionadosDrive" disabled>
                                    <i class="fab fa-google-drive mr-2"></i>Reenviar Seleccionados a Drive
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <span id="contadorSeleccionados" class="text-muted">0 seleccionados</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>


<!-- Modal de progreso para reenvíos masivos -->
<div class="modal fade" id="modalProgresoReenvio" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reenvío Masivo en Progreso</h4>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="barraProgreso" role="progressbar" style="width: 0%">
                        </div>
                    </div>
                </div>
                <div id="estadoProgreso">
                    <p>Iniciando reenvío...</p>
                </div>
                <div id="resultadosProgreso" style="display: none;">
                    <h5>Resultados:</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                    <br><strong id="exitosos">0</strong>
                                    <br><small>Exitosos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-danger">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                    <br><strong id="fallidos">0</strong>
                                    <br><small>Fallidos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="text-info">
                                    <i class="fas fa-list fa-2x"></i>
                                    <br><strong id="procesados">0</strong>
                                    <br><small>Procesados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cerrarProgreso" disabled>Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

<script>
$(document).ready(function() {
    
    // Inicializar DataTable
    $('#tablaErrores').DataTable({
        order: [[7, 'desc']], // Ordenar por último intento desc
        language: {
            processing: 'Procesando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)',
            loadingRecords: 'Cargando...',
            zeroRecords: 'No se encontraron registros',
            emptyTable: 'No hay registros con errores',
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Último'
            }
        }
    });

    // Calcular estadísticas
    calcularEstadisticas();

    // ===============================================
    // SELECCIÓN MÚLTIPLE
    // ===============================================

    // Seleccionar todos
    $('#seleccionarTodos').on('change', function() {
        const checked = $(this).prop('checked');
        $('.seleccionar-registro').prop('checked', checked);
        actualizarContadorSeleccionados();
    });

    // Selección individual
    $(document).on('change', '.seleccionar-registro', function() {
        actualizarContadorSeleccionados();
        
        // Actualizar "seleccionar todos"
        const total = $('.seleccionar-registro').length;
        const seleccionados = $('.seleccionar-registro:checked').length;
        
        $('#seleccionarTodos').prop('checked', seleccionados === total);
        $('#seleccionarTodos').prop('indeterminate', seleccionados > 0 && seleccionados < total);
    });

    function actualizarContadorSeleccionados() {
        const seleccionados = $('.seleccionar-registro:checked').length;
        $('#contadorSeleccionados').text(seleccionados + ' seleccionados');
        
        // Habilitar/deshabilitar botones
        $('#reenviarSeleccionadosHubSpot, #reenviarSeleccionadosDrive').prop('disabled', seleccionados === 0);
    }

    // ===============================================
    // REENVÍOS INDIVIDUALES
    // ===============================================

    // Reenviar a HubSpot individual
    $(document).on('click', '.reenviar-hubspot', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post('/admin/leads/reenviar-hubspot/' + id)
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito');
                    // Remover de la tabla si se corrigió
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error || 'Error desconocido', 'Error');
                    btn.prop('disabled', false).html('<i class="fab fa-hubspot"></i>');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[ERRORES] Error reenviando a HubSpot:', error);
                toastr.error('Error de conexión', 'Error');
                btn.prop('disabled', false).html('<i class="fab fa-hubspot"></i>');
            });
    });

    // Reenviar a Google Drive individual
    $(document).on('click', '.reenviar-drive', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post('/admin/leads/reenviar-google-drive/' + id)
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error || 'Error desconocido', 'Error');
                    btn.prop('disabled', false).html('<i class="fab fa-google-drive"></i>');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[ERRORES] Error reenviando a Google Drive:', error);
                toastr.error('Error de conexión', 'Error');
                btn.prop('disabled', false).html('<i class="fab fa-google-drive"></i>');
            });
    });

    // ===============================================
    // REENVÍOS MASIVOS
    // ===============================================

    // Reenviar seleccionados a HubSpot
    $('#reenviarSeleccionadosHubSpot').on('click', function() {
        const ids = obtenerIdsSeleccionados();
        if (ids.length === 0) return;
        
        if (!confirm(`¿Reenviar ${ids.length} registros a HubSpot?`)) return;
        
        procesarReenvioMasivo(ids, 'hubspot');
    });

    // Reenviar seleccionados a Google Drive
    $('#reenviarSeleccionadosDrive').on('click', function() {
        const ids = obtenerIdsSeleccionados();
        if (ids.length === 0) return;
        
        if (!confirm(`¿Reenviar ${ids.length} registros a Google Drive?`)) return;
        
        procesarReenvioMasivo(ids, 'google_drive');
    });

    // Reenviar todos a HubSpot
    $('#reenviarTodosHubSpot').on('click', function() {
        const ids = obtenerTodosLosIds('hubspot');
        if (ids.length === 0) {
            toastr.info('No hay registros con errores de HubSpot para reenviar', 'Sin errores');
            return;
        }
        
        if (!confirm(`¿Reenviar TODOS los ${ids.length} registros con errores de HubSpot?`)) return;
        
        procesarReenvioMasivo(ids, 'hubspot');
    });

    // Reenviar todos a Google Drive
    $('#reenviarTodosDrive').on('click', function() {
        const ids = obtenerTodosLosIds('google_drive');
        if (ids.length === 0) {
            toastr.info('No hay registros con errores de Google Drive para reenviar', 'Sin errores');
            return;
        }
        
        if (!confirm(`¿Reenviar TODOS los ${ids.length} registros con errores de Google Drive?`)) return;
        
        procesarReenvioMasivo(ids, 'google_drive');
    });

    // ===============================================
    // FUNCIONES AUXILIARES
    // ===============================================

    function obtenerIdsSeleccionados() {
        const ids = [];
        $('.seleccionar-registro:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }

    function obtenerTodosLosIds(servicio) {
        const ids = [];
        $('#tablaErrores tbody tr').each(function() {
            const id = $(this).data('id');
            const tieneError = servicio === 'hubspot' 
                ? $(this).find('td:eq(5) .badge-danger').length > 0
                : $(this).find('td:eq(6) .badge-danger').length > 0;
            
            if (tieneError) {
                ids.push(id);
            }
        });
        return ids;
    }

    function procesarReenvioMasivo(ids, servicio) {
        // Mostrar modal de progreso
        $('#modalProgresoReenvio').modal('show');
        $('#estadoProgreso').show();
        $('#resultadosProgreso').hide();
        $('#cerrarProgreso').prop('disabled', true);
        
        let procesados = 0;
        let exitosos = 0;
        let fallidos = 0;
        const total = ids.length;
        
        $('#barraProgreso').css('width', '0%');
        $('#estadoProgreso p').text(`Procesando ${total} registros...`);
        
        // Procesar uno por uno
        procesarSiguienteRegistro(0);
        
        function procesarSiguienteRegistro(index) {
            if (index >= ids.length) {
                // Terminado
                $('#estadoProgreso').hide();
                $('#resultadosProgreso').show();
                $('#exitosos').text(exitosos);
                $('#fallidos').text(fallidos);
                $('#procesados').text(procesados);
                $('#cerrarProgreso').prop('disabled', false);
                
                if (exitosos > 0) {
                    toastr.success(`${exitosos} registros reenviados exitosamente`, 'Reenvío completado');
                    setTimeout(() => location.reload(), 3000);
                }
                return;
            }
            
            const id = ids[index];
            const porcentaje = Math.round((index / total) * 100);
            
            $('#barraProgreso').css('width', porcentaje + '%');
            $('#estadoProgreso p').text(`Procesando registro ${index + 1} de ${total}...`);
            
            const endpoint = servicio === 'hubspot' 
                ? '/admin/leads/reenviar-hubspot/' + id
                : '/admin/leads/reenviar-google-drive/' + id;
            
            $.post(endpoint)
                .done(function(response) {
                    if (response.success) {
                        exitosos++;
                    } else {
                        fallidos++;
                    }
                })
                .fail(function() {
                    fallidos++;
                })
                .always(function() {
                    procesados++;
                    // Continuar con el siguiente
                    setTimeout(() => procesarSiguienteRegistro(index + 1), 500);
                });
        }
    }

    function calcularEstadisticas() {
        let erroresHubSpot = 0;
        let erroresDrive = 0;
        let pendientes = 0;
        
        $('#tablaErrores tbody tr').each(function() {
            const hubspotError = $(this).find('td:eq(5) .badge-danger').length > 0;
            const driveError = $(this).find('td:eq(6) .badge-danger').length > 0;
            const hubspotPendiente = $(this).find('td:eq(5) .badge-warning').length > 0;
            const drivePendiente = $(this).find('td:eq(6) .badge-warning').length > 0;
            
            if (hubspotError) erroresHubSpot++;
            if (driveError) erroresDrive++;
            if (hubspotPendiente || drivePendiente) pendientes++;
        });
        
        $('#errores-hubspot').text(erroresHubSpot);
        $('#errores-drive').text(erroresDrive);
        $('#pendientes').text(pendientes);
        
    }

    // Cerrar modal de progreso
    $('#cerrarProgreso').on('click', function() {
        $('#modalProgresoReenvio').modal('hide');
    });

});
</script>
<?= $this->endSection() ?>