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
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/tareas/mis-tareas') ?>">Mis Tareas</a></li>
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
                    <?php if ($esPropia): ?>
                        <span class="badge badge-info badge-lg ml-2">Propia</span>
                    <?php elseif ($esAsignada): ?>
                        <span class="badge badge-warning badge-lg ml-2">Asignada a mí</span>
                    <?php endif; ?>
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
                        <h5>Mis Comentarios</h5>
                        <div class="alert alert-success">
                            <?= nl2br(esc($tarea->comentarios_usuario)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <!-- Progreso Interactivo -->
                        <h5>Progreso de la Tarea</h5>
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?= $tarea->progreso ?>%"
                                 aria-valuenow="<?= $tarea->progreso ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= $tarea->progreso ?>%
                            </div>
                        </div>
                        
                        <!-- Botones de Progreso Rápido -->
                        <?php if (!$tarea->estaCompletada() && !$tarea->estaCancelada()): ?>
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6>Actualización Rápida:</h6>
                                <div class="btn-group btn-group-sm d-flex flex-wrap" role="group">
                                    <button class="btn btn-outline-secondary btn-progreso-rapido" data-progreso="0">
                                        😴 0%
                                    </button>
                                    <button class="btn btn-outline-primary btn-progreso-rapido" data-progreso="25">
                                        🚀 25%
                                    </button>
                                    <button class="btn btn-outline-info btn-progreso-rapido" data-progreso="50">
                                        ⚡ 50%
                                    </button>
                                    <button class="btn btn-outline-warning btn-progreso-rapido" data-progreso="75">
                                        🔥 75%
                                    </button>
                                    <button class="btn btn-outline-success btn-progreso-rapido" data-progreso="100">
                                        ✅ 100%
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Información adicional -->
                        <div class="info-box mb-3">
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
                        
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Creada</span>
                                <span class="info-box-number">
                                    <?= $tarea->created_at->format('d/m/Y H:i') ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($tarea->fecha_completada): ?>
                        <div class="info-box mb-3">
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
                <a href="<?= site_url('/admin/tareas/mis-tareas') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Mis Tareas
                </a>
                
                <?php if (!$tarea->estaCompletada() && !$tarea->estaCancelada()): ?>
                    <?php if ($tarea->estado === 'pendiente'): ?>
                    <button class="btn btn-warning btn-iniciar" data-id="<?= $tarea->id ?>">
                        <i class="fas fa-play"></i> Iniciar Tarea
                    </button>
                    <?php endif; ?>
                    
                    <button class="btn btn-info btn-actualizar-progreso" 
                            data-id="<?= $tarea->id ?>"
                            data-progreso="<?= $tarea->progreso ?>">
                        <i class="fas fa-edit"></i> Actualizar Progreso
                    </button>
                    
                    <button class="btn btn-success btn-completar" data-id="<?= $tarea->id ?>">
                        <i class="fas fa-check"></i> Marcar como Completada
                    </button>
                <?php endif; ?>
                
                <button class="btn btn-secondary btn-comentario" data-id="<?= $tarea->id ?>">
                    <i class="fas fa-comment"></i> Agregar Comentario
                </button>
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
                            <?php if (is_object($item) && isset($item['created_at'])): ?>
                                <?= date('d/m/Y', strtotime($item['created_at'])) ?>
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
                                <?php if (is_object($item) && isset($item['created_at'])): ?>
                                    <?= date('H:i', strtotime($item['created_at'])) ?>
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
                                    <?= nl2br(esc($item->comentario ?? 'Sin detalles')) ?>
                                <?php else: ?>
                                    <?= nl2br(esc($item['comentario'] ?? 'Sin detalles')) ?>
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

<!-- Modales incluidos desde la vista principal -->
<?= $this->include('admin/tareas/mis-tareas/_modals') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Progreso rápido con emojis
    $('.btn-progreso-rapido').on('click', function() {
        const progreso = $(this).data('progreso');
        const tareaId = <?= $tarea->id ?>;
        
        actualizarProgresoRapido(tareaId, progreso);
    });
    
    function actualizarProgresoRapido(tareaId, progreso) {
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/actualizar-progreso') ?>',
            type: 'POST',
            data: {
                tarea_id: tareaId,
                progreso: progreso,
                comentario: 'Actualización rápida desde vista de detalle'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Progreso actualizado!',
                        text: `Progreso: ${progreso}%`,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    }
});
</script>
<?= $this->endSection() ?>