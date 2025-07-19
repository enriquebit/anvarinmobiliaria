<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Lightbox para imágenes -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/ekko-lightbox/ekko-lightbox.css') ?>">
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
            
            <!-- Información general -->
            <div class="row">
                <!-- Datos del cliente -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                Información del Cliente
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-info"><?= $registro->folio ?? 'REG-' . date('Y') . '-' . str_pad($registro->id, 6, '0', STR_PAD_LEFT) ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Nombre completo:</th>
                                            <td><?= $registro->getNombreCompleto() ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td>
                                                <a href="mailto:<?= $registro->email ?>"><?= $registro->email ?></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td>
                                                <a href="tel:<?= $registro->telefono ?>"><?= $registro->getTelefonoFormateado() ?></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Medio de contacto:</th>
                                            <td>
                                                <?php if ($registro->medio_contacto === 'whatsapp'): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fab fa-whatsapp mr-1"></i>WhatsApp
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-phone mr-1"></i>Teléfono
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Desarrollo:</th>
                                            <td><?= $registro->getDesarrolloTexto() ?></td>
                                        </tr>
                                        <?php if (!empty($registro->manzana)): ?>
                                        <tr>
                                            <th>Manzana:</th>
                                            <td><?= $registro->manzana ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if (!empty($registro->lote)): ?>
                                        <tr>
                                            <th>Lote:</th>
                                            <td><?= $registro->lote ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>Fecha de registro:</th>
                                            <td>
                                                <?= $registro->getFechaRegistroFormateada() ?>
                                                <br><small class="text-muted"><?= $registro->getTiempoTranscurrido() ?></small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <?php if (!empty($registro->nombre_copropietario)): ?>
                            <hr>
                            <h5><i class="fas fa-users mr-2"></i>Co-propietario</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Nombre:</th>
                                            <td><?= $registro->nombre_copropietario ?></td>
                                        </tr>
                                        <tr>
                                            <th>Parentesco:</th>
                                            <td><?= $registro->parentesco_copropietario ?? 'No especificado' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Estados de sincronización -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sync mr-2"></i>
                                Estados de Sincronización
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- HubSpot -->
                            <div class="mb-3">
                                <strong>HubSpot CRM</strong>
                                <div class="mt-1">
                                    <?= $registro->getBadgeEstadoSincronizacion() ?>
                                    <?php if ($registro->falloSincronizacionHubSpot()): ?>
                                        <button class="btn btn-sm btn-warning mt-2 reenviar-hubspot" data-id="<?= $registro->id ?>">
                                            <i class="fab fa-hubspot mr-1"></i>Reenviar
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($registro->hubspot_contact_id)): ?>
                                    <small class="text-muted d-block">ID Contacto: <?= $registro->hubspot_contact_id ?></small>
                                <?php endif; ?>
                                <?php if (!empty($registro->hubspot_ticket_id)): ?>
                                    <small class="text-muted d-block">ID Ticket: <?= $registro->hubspot_ticket_id ?></small>
                                <?php endif; ?>
                                <?php if (!empty($registro->hubspot_sync_error)): ?>
                                    <small class="text-danger d-block">Error: <?= $registro->hubspot_sync_error ?></small>
                                <?php endif; ?>
                            </div>

                            <!-- Google Drive -->
                            <div class="mb-3">
                                <strong>Google Drive</strong>
                                <div class="mt-1">
                                    <?php if ($registro->estaSincronizadoGoogleDrive()): ?>
                                        <span class="badge badge-success">Exitoso</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pendiente/Error</span>
                                        <button class="btn btn-sm btn-success mt-2 reenviar-drive" data-id="<?= $registro->id ?>">
                                            <i class="fab fa-google-drive mr-1"></i>Reenviar
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($registro->google_drive_folder_id)): ?>
                                    <small class="text-muted d-block">Folder ID: <?= $registro->google_drive_folder_id ?></small>
                                <?php endif; ?>
                                <?php if (!empty($registro->google_drive_sync_error)): ?>
                                    <small class="text-danger d-block">Error: <?= $registro->google_drive_sync_error ?></small>
                                <?php endif; ?>
                            </div>

                            <!-- Agente referido -->
                            <?php if (!empty($registro->agente_referido)): ?>
                            <div class="mb-3">
                                <strong>Agente Referido</strong>
                                <div class="mt-1">
                                    <span class="badge badge-info"><?= $registro->agente_referido ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos -->
            <?php if (!empty($documentos)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt mr-2"></i>
                        Documentos Cargados
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary"><?= count($documentos) ?> documentos</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($documentos as $documento): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-header p-2">
                                    <strong><?= ucfirst(str_replace('_', ' ', $documento['tipo_documento'])) ?></strong>
                                    <?php 
                                    // Estados: uploading, success, failed - O manual: aceptado, rechazado, pendiente
                                    $status = $documento['upload_status'] ?? 'pendiente';
                                    $manualStatus = $documento['estado_revision'] ?? null; // Campo para estado manual
                                    
                                    if (!empty($manualStatus)) {
                                        // Estado manual tiene prioridad
                                        switch($manualStatus) {
                                            case 'aceptado':
                                                echo '<span class="badge badge-success float-right">Aceptado</span>';
                                                break;
                                            case 'rechazado':
                                                echo '<span class="badge badge-danger float-right">Rechazado</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-warning float-right">Pendiente</span>';
                                        }
                                    } else {
                                        // Estado automático del upload
                                        switch($status) {
                                            case 'success':
                                                echo '<span class="badge badge-success float-right">Subido</span>';
                                                break;
                                            case 'failed':
                                                echo '<span class="badge badge-danger float-right">Error</span>';
                                                break;
                                            case 'uploading':
                                                echo '<span class="badge badge-info float-right">Subiendo...</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-warning float-right">Pendiente</span>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="card-body p-2">
                                    <?php if (!empty($documento['google_drive_url'])): ?>
                                        <a href="<?= $documento['google_drive_url'] ?>" target="_blank" class="btn btn-sm btn-primary btn-block mb-2">
                                            <i class="fab fa-google-drive mr-1"></i>Ver en Drive
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Botones de revisión manual -->
                                    <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                                        <button type="button" class="btn btn-outline-success cambiar-estado-doc" 
                                                data-id="<?= $documento['id'] ?>" data-estado="aceptado">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning cambiar-estado-doc" 
                                                data-id="<?= $documento['id'] ?>" data-estado="pendiente">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger cambiar-estado-doc" 
                                                data-id="<?= $documento['id'] ?>" data-estado="rechazado">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <small class="text-muted d-block mt-2">
                                        Archivo: <?= $documento['nombre_archivo_original'] ?? $documento['nombre_archivo_final'] ?? 'N/A' ?><br>
                                        Tamaño: <?= number_format(($documento['tamano_bytes'] ?? 0) / 1024, 1) ?> KB<br>
                                        Subido: <?= isset($documento['created_at']) ? date('d/m/Y H:i', strtotime($documento['created_at'])) : 'N/A' ?>
                                    </small>
                                    
                                    <?php if (!empty($documento['upload_error'])): ?>
                                        <small class="text-danger d-block mt-1">
                                            Error: <?= $documento['upload_error'] ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Logs de API -->
            <?php if (!empty($logs)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-alt mr-2"></i>
                        Logs de API
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary"><?= count($logs) ?> entradas</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Operación</th>
                                    <th>Estado</th>
                                    <th>Respuesta</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <?php if ($log->servicio === 'hubspot'): ?>
                                            <i class="fab fa-hubspot text-orange"></i> HubSpot
                                        <?php elseif ($log->servicio === 'google_drive'): ?>
                                            <i class="fab fa-google-drive text-blue"></i> Google Drive
                                        <?php else: ?>
                                            <?= ucfirst($log->servicio) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $log->operacion ?></td>
                                    <td>
                                        <?php if ($log->exitoso): ?>
                                            <span class="badge badge-success">Exitoso</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Error</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($log->respuesta)): ?>
                                            <button class="btn btn-sm btn-outline-secondary ver-respuesta" 
                                                    data-respuesta="<?= htmlspecialchars($log->respuesta) ?>">
                                                Ver Detalle
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Sin respuesta</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i:s', strtotime($log->fecha_evento)) ?>
                                        <br><small class="text-muted"><?= $log->duracion_ms ?>ms</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Acciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools mr-2"></i>
                        Acciones
                    </h3>
                </div>
                <div class="card-body">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/leads') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                        </a>
                        <?php if ($registro->falloSincronizacionHubSpot()): ?>
                        <button class="btn btn-warning reenviar-hubspot" data-id="<?= $registro->id ?>">
                            <i class="fab fa-hubspot mr-2"></i>Reenviar a HubSpot
                        </button>
                        <?php endif; ?>
                        <?php if ($registro->falloSincronizacionGoogleDrive()): ?>
                        <button class="btn btn-success reenviar-drive" data-id="<?= $registro->id ?>">
                            <i class="fab fa-google-drive mr-2"></i>Reenviar a Google Drive
                        </button>
                        <?php endif; ?>
                        <a href="<?= base_url('admin/leads/logs?registro_id=' . $registro->id) ?>" class="btn btn-info">
                            <i class="fas fa-list-alt mr-2"></i>Ver Todos los Logs
                        </a>
                    </div>
                </div>
            </div>


<!-- Modal para ver respuesta de API -->
<div class="modal fade" id="modalRespuestaAPI" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Respuesta de API</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="contenidoRespuestaAPI" class="bg-light p-3" style="max-height: 400px; overflow-y: auto;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Lightbox -->
<script src="<?= base_url('assets/plugins/ekko-lightbox/ekko-lightbox.min.js') ?>"></script>

<script>
$(document).ready(function() {
    
    // ===============================================
    // EVENTOS
    // ===============================================

    // Ver respuesta de API
    $(document).on('click', '.ver-respuesta', function() {
        const respuesta = $(this).data('respuesta');
        
        try {
            // Intentar formatear como JSON si es posible
            const jsonData = JSON.parse(respuesta);
            $('#contenidoRespuestaAPI').text(JSON.stringify(jsonData, null, 2));
        } catch (e) {
            // Si no es JSON válido, mostrar como texto plano
            $('#contenidoRespuestaAPI').text(respuesta);
        }
        
        $('#modalRespuestaAPI').modal('show');
    });

    // Reenviar a HubSpot
    $(document).on('click', '.reenviar-hubspot', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        
        if (!confirm('¿Estás seguro de que quieres reenviar este registro a HubSpot?')) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...');
        
        $.post('<?= base_url('admin/leads/reenviar-hubspot') ?>/' + id)
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito');
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error || 'Error desconocido', 'Error');
                    btn.prop('disabled', false).html('<i class="fab fa-hubspot mr-2"></i>Reenviar a HubSpot');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[DETALLE-REGISTRO] Error reenviando a HubSpot:', error);
                toastr.error('Error de conexión', 'Error');
                btn.prop('disabled', false).html('<i class="fab fa-hubspot mr-2"></i>Reenviar a HubSpot');
            });
    });

    // Reenviar a Google Drive
    $(document).on('click', '.reenviar-drive', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        
        if (!confirm('¿Estás seguro de que quieres reenviar los documentos a Google Drive?')) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...');
        
        $.post('<?= base_url('admin/leads/reenviar-google-drive') ?>/' + id)
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito');
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error || 'Error desconocido', 'Error');
                    btn.prop('disabled', false).html('<i class="fab fa-google-drive mr-2"></i>Reenviar a Google Drive');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[DETALLE-REGISTRO] Error reenviando a Google Drive:', error);
                toastr.error('Error de conexión', 'Error');
                btn.prop('disabled', false).html('<i class="fab fa-google-drive mr-2"></i>Reenviar a Google Drive');
            });
    });

    // Cambiar estado de revisión de documento
    $(document).on('click', '.cambiar-estado-doc', function() {
        const btn = $(this);
        const documentoId = btn.data('id');
        const nuevoEstado = btn.data('estado');
        
        
        $.post('<?= base_url('admin/leads/cambiar-estado-documento') ?>', {
            documento_id: documentoId,
            estado: nuevoEstado
        })
        .done(function(response) {
            if (response.success) {
                toastr.success('Estado del documento actualizado', 'Éxito');
                // Recargar la página para mostrar los cambios
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(response.error || 'Error al actualizar estado', 'Error');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('[DETALLE-REGISTRO] Error cambiando estado documento:', error);
            toastr.error('Error de conexión', 'Error');
        });
    });

    // Lightbox para imágenes
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });

});
</script>
<?= $this->endSection() ?>