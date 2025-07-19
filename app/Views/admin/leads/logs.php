<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
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
            
            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros de Logs
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="<?= site_url('/admin/leads') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" id="limpiarLogs">
                                <i class="fas fa-trash mr-2"></i>Limpiar Logs Antiguos
                            </button>
                        </div>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filtrosLogs">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_servicio">Servicio</label>
                                    <select class="form-control" id="filtro_servicio" name="servicio">
                                        <option value="">Todos los servicios</option>
                                        <option value="hubspot" <?= ($servicio_filtro === 'hubspot') ? 'selected' : '' ?>>HubSpot</option>
                                        <option value="google_drive" <?= ($servicio_filtro === 'google_drive') ? 'selected' : '' ?>>Google Drive</option>
                                        <option value="webhook" <?= ($servicio_filtro === 'webhook') ? 'selected' : '' ?>>Webhook</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_estado">Estado</label>
                                    <select class="form-control" id="filtro_estado" name="estado">
                                        <option value="">Todos</option>
                                        <option value="1" <?= $solo_errores ? 'selected' : '' ?>>Solo errores</option>
                                        <option value="0">Solo exitosos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_registro">ID Registro</label>
                                    <input type="number" class="form-control" id="filtro_registro" name="registro_id" 
                                           placeholder="ID del registro" min="1">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-2"></i>Filtrar
                                    </button>
                                    <button type="button" class="btn btn-secondary ml-2" id="limpiarFiltros">
                                        <i class="fas fa-times mr-2"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas de logs -->
            <div class="row" id="estadisticasLogs">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total-logs">-</h3>
                            <p>Total Logs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="logs-exitosos">-</h3>
                            <p>Exitosos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="logs-errores">-</h3>
                            <p>Con Errores</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="duracion-promedio">-</h3>
                            <p>Duración Promedio</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de logs -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Logs de API
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary" id="contador-logs"><?= count($logs) ?> entradas</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm" id="tablaLogs">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Registro</th>
                                    <th>Servicio</th>
                                    <th>Operación</th>
                                    <th>Estado</th>
                                    <th>Duración</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log->id ?></td>
                                    <td>
                                        <?php if (!empty($log->registro_cliente_id)): ?>
                                            <a href="<?= site_url('/admin/leads/show/' . $log->registro_cliente_id) ?>" 
                                               class="badge badge-info">
                                                #<?= $log->registro_cliente_id ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log->servicio === 'hubspot'): ?>
                                            <span class="badge badge-warning">
                                                <i class="fab fa-hubspot mr-1"></i>HubSpot
                                            </span>
                                        <?php elseif ($log->servicio === 'google_drive'): ?>
                                            <span class="badge badge-primary">
                                                <i class="fab fa-google-drive mr-1"></i>Google Drive
                                            </span>
                                        <?php elseif ($log->servicio === 'webhook'): ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-paper-plane mr-1"></i>Webhook
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= ucfirst($log->servicio) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $log->operacion ?></td>
                                    <td>
                                        <?php if ($log->exitoso): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check mr-1"></i>Exitoso
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times mr-1"></i>Error
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log->duracion_ms > 5000): ?>
                                            <span class="text-danger"><?= $log->duracion_ms ?>ms</span>
                                        <?php elseif ($log->duracion_ms > 2000): ?>
                                            <span class="text-warning"><?= $log->duracion_ms ?>ms</span>
                                        <?php else: ?>
                                            <span class="text-success"><?= $log->duracion_ms ?>ms</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i:s', strtotime($log->fecha_evento)) ?>
                                        <br><small class="text-muted"><?= \Carbon\Carbon::parse($log->fecha_evento)->diffForHumans() ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($log->respuesta)): ?>
                                            <button class="btn btn-sm btn-outline-secondary ver-respuesta" 
                                                    data-respuesta="<?= htmlspecialchars($log->respuesta) ?>"
                                                    data-servicio="<?= $log->servicio ?>"
                                                    data-operacion="<?= $log->operacion ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!empty($log->datos_enviados)): ?>
                                            <button class="btn btn-sm btn-outline-primary ver-datos-enviados" 
                                                    data-datos="<?= htmlspecialchars($log->datos_enviados) ?>"
                                                    data-servicio="<?= $log->servicio ?>"
                                                    data-operacion="<?= $log->operacion ?>">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


<!-- Modal para ver respuesta de API -->
<div class="modal fade" id="modalRespuestaAPI" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Respuesta de API</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Servicio:</strong> <span id="modal-servicio"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Operación:</strong> <span id="modal-operacion"></span>
                    </div>
                </div>
                <pre id="contenidoRespuestaAPI" class="bg-light p-3" style="max-height: 400px; overflow-y: auto; font-size: 12px;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="copiarRespuesta">Copiar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver datos enviados -->
<div class="modal fade" id="modalDatosEnviados" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Datos Enviados</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Servicio:</strong> <span id="modal-datos-servicio"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Operación:</strong> <span id="modal-datos-operacion"></span>
                    </div>
                </div>
                <pre id="contenidoDatosEnviados" class="bg-light p-3" style="max-height: 400px; overflow-y: auto; font-size: 12px;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="copiarDatos">Copiar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para limpiar logs -->
<div class="modal fade" id="modalLimpiarLogs" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Limpiar Logs Antiguos</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Cuántos días de antigüedad quieres mantener?</p>
                <div class="form-group">
                    <label for="diasAntiguedad">Días (los logs más antiguos se eliminarán):</label>
                    <input type="number" class="form-control" id="diasAntiguedad" value="30" min="1" max="365">
                    <small class="form-text text-muted">Por ejemplo: 30 días mantendrá solo los logs de los últimos 30 días.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarLimpiarLogs">Limpiar Logs</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<!-- Select2 -->
<script src="<?= base_url('assets/plugins/select2/js/select2.full.min.js') ?>"></script>

<script>
$(document).ready(function() {
    
    // Inicializar DataTable
    $('#tablaLogs').DataTable({
        order: [[6, 'desc']], // Ordenar por fecha desc
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
            emptyTable: 'No hay logs disponibles',
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Último'
            }
        }
    });

    // Cargar estadísticas
    cargarEstadisticasLogs();

    // ===============================================
    // EVENTOS
    // ===============================================

    // Aplicar filtros
    $('#filtrosLogs').on('submit', function(e) {
        e.preventDefault();
        
        const servicio = $('#filtro_servicio').val();
        const estado = $('#filtro_estado').val();
        const registroId = $('#filtro_registro').val();
        
        let url = '/admin/leads/logs?';
        if (servicio) url += 'servicio=' + servicio + '&';
        if (estado) url += 'errores=' + estado + '&';
        if (registroId) url += 'registro_id=' + registroId + '&';
        
        window.location.href = url;
    });

    // Limpiar filtros
    $('#limpiarFiltros').on('click', function() {
        window.location.href = '/admin/leads/logs';
    });

    // Ver respuesta de API
    $(document).on('click', '.ver-respuesta', function() {
        const respuesta = $(this).data('respuesta');
        const servicio = $(this).data('servicio');
        const operacion = $(this).data('operacion');
        
        $('#modal-servicio').text(servicio);
        $('#modal-operacion').text(operacion);
        
        try {
            const jsonData = JSON.parse(respuesta);
            $('#contenidoRespuestaAPI').text(JSON.stringify(jsonData, null, 2));
        } catch (e) {
            $('#contenidoRespuestaAPI').text(respuesta);
        }
        
        $('#modalRespuestaAPI').modal('show');
    });

    // Ver datos enviados
    $(document).on('click', '.ver-datos-enviados', function() {
        const datos = $(this).data('datos');
        const servicio = $(this).data('servicio');
        const operacion = $(this).data('operacion');
        
        $('#modal-datos-servicio').text(servicio);
        $('#modal-datos-operacion').text(operacion);
        
        try {
            const jsonData = JSON.parse(datos);
            $('#contenidoDatosEnviados').text(JSON.stringify(jsonData, null, 2));
        } catch (e) {
            $('#contenidoDatosEnviados').text(datos);
        }
        
        $('#modalDatosEnviados').modal('show');
    });

    // Copiar respuesta
    $('#copiarRespuesta').on('click', function() {
        const contenido = $('#contenidoRespuestaAPI').text();
        navigator.clipboard.writeText(contenido).then(function() {
            toastr.success('Respuesta copiada al portapapeles', 'Copiado');
        });
    });

    // Copiar datos
    $('#copiarDatos').on('click', function() {
        const contenido = $('#contenidoDatosEnviados').text();
        navigator.clipboard.writeText(contenido).then(function() {
            toastr.success('Datos copiados al portapapeles', 'Copiado');
        });
    });

    // Mostrar modal limpiar logs
    $('#limpiarLogs').on('click', function() {
        $('#modalLimpiarLogs').modal('show');
    });

    // Confirmar limpiar logs
    $('#confirmarLimpiarLogs').on('click', function() {
        const dias = $('#diasAntiguedad').val();
        
        if (!dias || dias < 1) {
            toastr.error('Ingresa un número válido de días', 'Error');
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Limpiando...');
        
        $.post('/admin/leads/limpiar-logs', { dias: dias })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito');
                    $('#modalLimpiarLogs').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error || 'Error desconocido', 'Error');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[LOGS] Error limpiando logs:', error);
                toastr.error('Error de conexión', 'Error');
            })
            .always(function() {
                btn.prop('disabled', false).html('Limpiar Logs');
            });
    });

    // ===============================================
    // FUNCIONES
    // ===============================================

    function cargarEstadisticasLogs() {
        // Calcular estadísticas de los logs visibles
        const totalLogs = $('#tablaLogs tbody tr').length;
        let exitosos = 0;
        let errores = 0;
        let duracionTotal = 0;
        
        $('#tablaLogs tbody tr').each(function() {
            const estado = $(this).find('td:eq(4) .badge').hasClass('badge-success');
            const duracionText = $(this).find('td:eq(5)').text();
            const duracion = parseInt(duracionText.replace('ms', ''));
            
            if (estado) {
                exitosos++;
            } else {
                errores++;
            }
            
            if (!isNaN(duracion)) {
                duracionTotal += duracion;
            }
        });
        
        const promedioMs = totalLogs > 0 ? Math.round(duracionTotal / totalLogs) : 0;
        
        $('#total-logs').text(totalLogs);
        $('#logs-exitosos').text(exitosos);
        $('#logs-errores').text(errores);
        $('#duracion-promedio').text(promedioMs + 'ms');
        
    }

});
</script>
<?= $this->endSection() ?>