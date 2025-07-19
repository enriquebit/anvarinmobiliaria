<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye"></i>
                    Detalle de Tarea
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/tareas') ?>">Tareas</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Información de la Tarea -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    <?= esc($tarea->titulo) ?>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-<?= $tarea->getColorEstado() ?> badge-lg">
                        <?= $tarea->getEstadoTexto() ?>
                    </span>
                    <span class="badge badge-<?= $tarea->getColorPrioridad() ?> badge-lg ml-2">
                        <?= $tarea->getPrioridadTexto() ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5>Descripción</h5>
                        <p class="text-muted">
                            <?= $tarea->descripcion ? nl2br(esc($tarea->descripcion)) : '<em>Sin descripción</em>' ?>
                        </p>
                        
                        <?php if ($tarea->comentarios_admin): ?>
                        <h5>Comentarios del Administrador</h5>
                        <div class="alert alert-info">
                            <?= nl2br(esc($tarea->comentarios_admin)) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($tarea->comentarios_usuario): ?>
                        <h5>Comentarios del Usuario</h5>
                        <div class="alert alert-success">
                            <?= nl2br(esc($tarea->comentarios_usuario)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <!-- Progreso -->
                        <h5>Progreso</h5>
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped" 
                                 role="progressbar" 
                                 style="width: <?= $tarea->progreso ?>%"
                                 aria-valuenow="<?= $tarea->progreso ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= $tarea->progreso ?>%
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Asignado por</span>
                                <span class="info-box-number"><?= esc($asignado_por->email ?? 'Usuario no encontrado') ?></span>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Asignado a</span>
                                <span class="info-box-number"><?= esc($asignado_a->email ?? 'Usuario no encontrado') ?></span>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fecha límite</span>
                                <span class="info-box-number">
                                    <?php if ($tarea->fecha_vencimiento): ?>
                                        <?= $tarea->fecha_vencimiento->format('d/m/Y') ?>
                                        <br><small><?= $tarea->getDiasRestantesTexto() ?></small>
                                    <?php else: ?>
                                        <small>Sin fecha límite</small>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Creada</span>
                                <span class="info-box-number">
                                    <?= $tarea->created_at->format('d/m/Y H:i') ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($tarea->fecha_completada): ?>
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completada</span>
                                <span class="info-box-number">
                                    <?= $tarea->fecha_completada->format('d/m/Y H:i') ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="<?= site_url('/admin/tareas') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la Lista
                </a>
                <a href="<?= site_url('/admin/tareas/' . $tarea->id . '/edit') ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <?php if ($tarea->estado !== 'completada'): ?>
                <button class="btn btn-success btn-cambiar-estado" 
                        data-id="<?= $tarea->id ?>" 
                        data-estado="completada">
                    <i class="fas fa-check"></i> Marcar como Completada
                </button>
                <?php endif; ?>
                <?php if ($tarea->estado !== 'cancelada'): ?>
                <button class="btn btn-secondary btn-cambiar-estado" 
                        data-id="<?= $tarea->id ?>" 
                        data-estado="cancelada">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Historial de la Tarea -->
        <?php if (!empty($historial)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Historial de Cambios
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($historial as $item): ?>
                    <div class="time-label">
                        <span class="bg-info">
                            <?php if (is_object($item) && isset($item->created_at)): ?>
                                <?= $item->created_at->format('d/m/Y') ?>
                            <?php elseif (is_array($item) && isset($item['created_at'])): ?>
                                <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                            <?php else: ?>
                                <?= date('d/m/Y') ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-edit bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> 
                                <?php if (is_object($item) && isset($item->created_at)): ?>
                                    <?= $item->created_at->format('H:i') ?>
                                <?php elseif (is_array($item) && isset($item['created_at'])): ?>
                                    <?= date('H:i', strtotime($item['created_at'])) ?>
                                <?php else: ?>
                                    <?= date('H:i') ?>
                                <?php endif; ?>
                            </span>
                            <h3 class="timeline-header">
                                <?php if (is_object($item)): ?>
                                    <?= esc($item->accion ?? 'Acción desconocida') ?>
                                <?php else: ?>
                                    <?= esc($item['accion'] ?? 'Acción desconocida') ?>
                                <?php endif; ?>
                            </h3>
                            <div class="timeline-body">
                                <?php if (is_object($item)): ?>
                                    <?= nl2br(esc($item->detalles ?? 'Sin detalles')) ?>
                                <?php else: ?>
                                    <?= nl2br(esc($item['detalles'] ?? 'Sin detalles')) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="estadoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado de Tarea</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEstado">
                    <input type="hidden" id="tarea_id" name="tarea_id">
                    <input type="hidden" id="estado" name="estado">
                    
                    <div class="form-group">
                        <label for="comentario">Comentario (opcional)</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEstado">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Manejar cambio de estado
    $('.btn-cambiar-estado').on('click', function() {
        const tareaId = $(this).data('id');
        const estado = $(this).data('estado');
        
        $('#tarea_id').val(tareaId);
        $('#estado').val(estado);
        $('#estadoModal').modal('show');
    });

    // Confirmar cambio de estado
    $('#btnConfirmarEstado').on('click', function() {
        const formData = $('#formEstado').serialize();
        
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/cambiar-estado') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ [ESTADO_DEBUG] Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'Error de conexión: ' + error, 'error');
            }
        });
        
        $('#estadoModal').modal('hide');
    });
});
</script>
<?= $this->endSection() ?>