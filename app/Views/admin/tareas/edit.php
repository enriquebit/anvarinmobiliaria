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
                    <i class="fas fa-edit"></i>
                    Editar Tarea
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/tareas') ?>">Tareas</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i>
                    Editar Tarea: <?= esc($tarea->titulo) ?>
                </h3>
            </div>
            <form action="<?= site_url('/admin/tareas/update/' . $tarea->id) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="titulo">Título de la Tarea <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['titulo']) ? 'is-invalid' : '' ?>" 
                                       id="titulo" 
                                       name="titulo" 
                                       value="<?= old('titulo', $tarea->titulo) ?>" 
                                       required>
                                <?php if (isset($errors['titulo'])): ?>
                                    <div class="invalid-feedback"><?= $errors['titulo'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="prioridad">Prioridad <span class="text-danger">*</span></label>
                                <select class="form-control <?= isset($errors['prioridad']) ? 'is-invalid' : '' ?>" 
                                        id="prioridad" 
                                        name="prioridad" 
                                        required>
                                    <option value="">Seleccionar prioridad</option>
                                    <?php foreach ($prioridades as $valor => $texto): ?>
                                        <option value="<?= $valor ?>" <?= old('prioridad', $tarea->prioridad) === $valor ? 'selected' : '' ?>>
                                            <?= $texto ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['prioridad'])): ?>
                                    <div class="invalid-feedback"><?= $errors['prioridad'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estado">Estado <span class="text-danger">*</span></label>
                                <select class="form-control <?= isset($errors['estado']) ? 'is-invalid' : '' ?>" 
                                        id="estado" 
                                        name="estado" 
                                        required>
                                    <option value="">Seleccionar estado</option>
                                    <?php foreach ($estados as $valor => $texto): ?>
                                        <option value="<?= $valor ?>" <?= old('estado', $tarea->estado) === $valor ? 'selected' : '' ?>>
                                            <?= $texto ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['estado'])): ?>
                                    <div class="invalid-feedback"><?= $errors['estado'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asignado_a">Asignar a <span class="text-danger">*</span></label>
                                <select class="form-control select2 <?= isset($errors['asignado_a']) ? 'is-invalid' : '' ?>" 
                                        id="asignado_a" 
                                        name="asignado_a" 
                                        required>
                                    <option value="">Seleccionar usuario</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario->id ?>" <?= old('asignado_a', $tarea->asignado_a) == $usuario->id ? 'selected' : '' ?>>
                                            <?= esc($usuario->email) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['asignado_a'])): ?>
                                    <div class="invalid-feedback"><?= $errors['asignado_a'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_vencimiento">Fecha Límite</label>
                                <input type="date" 
                                       class="form-control <?= isset($errors['fecha_vencimiento']) ? 'is-invalid' : '' ?>" 
                                       id="fecha_vencimiento" 
                                       name="fecha_vencimiento" 
                                       value="<?= old('fecha_vencimiento', $tarea->fecha_vencimiento ? $tarea->fecha_vencimiento->format('Y-m-d') : '') ?>"
                                       min="<?= date('Y-m-d') ?>">
                                <small class="form-text text-muted">
                                    Opcional. Si no se especifica, la tarea no tendrá fecha límite.
                                </small>
                                <?php if (isset($errors['fecha_vencimiento'])): ?>
                                    <div class="invalid-feedback"><?= $errors['fecha_vencimiento'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="descripcion">Descripción de la Tarea</label>
                                <textarea class="form-control <?= isset($errors['descripcion']) ? 'is-invalid' : '' ?>" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="4" 
                                          placeholder="Describe detalladamente lo que debe hacer el usuario..."><?= old('descripcion', $tarea->descripcion) ?></textarea>
                                <?php if (isset($errors['descripcion'])): ?>
                                    <div class="invalid-feedback"><?= $errors['descripcion'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="comentarios_admin">Comentarios Adicionales</label>
                                <textarea class="form-control" 
                                          id="comentarios_admin" 
                                          name="comentarios_admin" 
                                          rows="3" 
                                          placeholder="Comentarios adicionales para el usuario asignado..."><?= old('comentarios_admin', $tarea->comentarios_admin) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Información de la Tarea</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Control de Progreso Interactivo:</strong><br>
                                            <!-- Contenedor de progreso interactivo -->
                                            <div class="progress-container-interactive-edit" data-tarea-id="<?= $tarea->id ?>">
                                                <!-- Etiqueta dinámica del progreso -->
                                                <div class="progress-label mb-3">
                                                    <span class="badge badge-lg progress-badge" id="progress-badge-<?= $tarea->id ?>">
                                                        <span id="progress-text-<?= $tarea->id ?>"><?= $tarea->progreso ?>% completado</span>
                                                    </span>
                                                </div>

                                                <!-- Barra de progreso interactiva principal -->
                                                <div class="progress progress-clickable-edit" style="height: 40px; cursor: pointer;" 
                                                     title="Clic o arrastra para cambiar progreso">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                         id="progress-bar-<?= $tarea->id ?>"
                                                         role="progressbar" 
                                                         style="width: <?= $tarea->progreso ?>%"
                                                         aria-valuenow="<?= $tarea->progreso ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <span class="progress-inner-text font-weight-bold" id="progress-inner-<?= $tarea->id ?>">
                                                            <?= $tarea->progreso ?>%
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Marcadores de referencia -->
                                                <div class="progress-markers d-flex justify-content-between mt-2">
                                                    <small class="text-muted marker" data-value="0">0%</small>
                                                    <small class="text-muted marker" data-value="25">25%</small>
                                                    <small class="text-muted marker" data-value="50">50%</small>
                                                    <small class="text-muted marker" data-value="75">75%</small>
                                                    <small class="text-muted marker" data-value="100">100%</small>
                                                </div>
                                                
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-hand-pointer"></i> Haz clic en cualquier punto de la barra para establecer el progreso
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Fecha de Creación:</strong><br>
                                            <?= $tarea->created_at->format('d/m/Y H:i') ?>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Última Actualización:</strong><br>
                                            <?= $tarea->updated_at->format('d/m/Y H:i') ?>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Estado Actual:</strong><br>
                                            <span class="badge badge-<?= $tarea->getColorEstado() ?>">
                                                <?= $tarea->getEstadoTexto() ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Tarea
                            </button>
                            <a href="<?= site_url('/admin/tareas') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <a href="<?= site_url('/admin/tareas/show/' . $tarea->id) ?>" class="btn btn-info">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                Los campos marcados con <span class="text-danger">*</span> son obligatorios
                            </small>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Validación del formulario
    $('form').on('submit', function(e) {
        let valid = true;
        
        // Validar título
        if ($('#titulo').val().trim() === '') {
            $('#titulo').addClass('is-invalid');
            valid = false;
        } else {
            $('#titulo').removeClass('is-invalid');
        }
        
        // Validar prioridad
        if ($('#prioridad').val() === '') {
            $('#prioridad').addClass('is-invalid');
            valid = false;
        } else {
            $('#prioridad').removeClass('is-invalid');
        }
        
        // Validar estado
        if ($('#estado').val() === '') {
            $('#estado').addClass('is-invalid');
            valid = false;
        } else {
            $('#estado').removeClass('is-invalid');
        }
        
        // Validar usuario asignado
        if ($('#asignado_a').val() === '') {
            $('#asignado_a').addClass('is-invalid');
            valid = false;
        } else {
            $('#asignado_a').removeClass('is-invalid');
        }
        
        if (!valid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Campos requeridos',
                text: 'Por favor complete todos los campos obligatorios'
            });
        }
    });

    // ===== FUNCIONALIDAD DE BARRA DE PROGRESO INTERACTIVA =====
    
    // Función para actualizar colores según progreso y estado
    function updateProgressColors(tareaId, progreso, estado = null) {
        const progressBar = $(`#progress-bar-${tareaId}`);
        const badge = $(`#progress-badge-${tareaId}`);
        
        // Remover clases existentes
        progressBar.removeClass('bg-danger bg-warning bg-info bg-success');
        badge.removeClass('badge-danger badge-warning badge-info badge-success');
        
        // Si está cancelada, siempre rojo independientemente del progreso
        if (estado === 'cancelada') {
            progressBar.addClass('bg-danger');
            badge.addClass('badge-danger');
            return;
        }
        
        // Aplicar colores según progreso
        if (progreso === 0) {
            progressBar.addClass('bg-danger');
            badge.addClass('badge-danger');
        } else if (progreso === 25) {
            progressBar.addClass('bg-warning');
            badge.addClass('badge-warning');
        } else if (progreso === 50) {
            progressBar.addClass('bg-info');
            badge.addClass('badge-info');
        } else if (progreso === 75) {
            progressBar.addClass('bg-warning');
            badge.addClass('badge-warning');
        } else if (progreso === 100) {
            progressBar.addClass('bg-success');
            badge.addClass('badge-success');
        }
    }
    
    // Función para actualizar la interfaz visual
    function updateProgressDisplay(tareaId, progreso, estado = null) {
        $(`#progress-text-${tareaId}`).text(`${progreso}% completado`);
        $(`#progress-inner-${tareaId}`).text(`${progreso}%`);
        $(`#progress-bar-${tareaId}`).css('width', `${progreso}%`).attr('aria-valuenow', progreso);
        
        // Obtener estado actual si no se proporciona
        if (!estado) {
            estado = $('#estado').val();
        }
        
        updateProgressColors(tareaId, progreso, estado);
    }
    
    // Función para sincronizar progreso con estado
    function syncProgressWithState(tareaId, progreso) {
        const estadoSelect = $('#estado');
        const estadoActual = estadoSelect.val();
        
        // Sincronización automática: progreso → estado
        if (progreso === 100 && estadoActual !== 'completada' && estadoActual !== 'cancelada') {
            // 100% → Completada
            estadoSelect.val('completada').trigger('change');
        } else if (progreso >= 25 && progreso <= 75 && estadoActual === 'completada') {
            // Bajar de 100% a 25-75% → En Proceso
            estadoSelect.val('en_proceso').trigger('change');
        } else if (progreso === 0 && (estadoActual === 'completada' || estadoActual === 'en_proceso')) {
            // 0% → Pendiente
            estadoSelect.val('pendiente').trigger('change');
        }
        
        // Los estados cancelada, pendiente y en_proceso mantienen flexibilidad
        // Solo se sincronizan cuando hay cambios lógicos evidentes
    }
    
    // Función para sincronizar estado con progreso
    function syncStateWithProgress(tareaId, nuevoEstado) {
        
        const progresoActual = parseInt($(`#progress-bar-${tareaId}`).attr('aria-valuenow'));
        
        if (nuevoEstado === 'completada') {
            // Completada = 100%
            updateProgressDisplay(tareaId, 100, nuevoEstado);
        } else if (nuevoEstado === 'cancelada') {
            // Cancelada = 0% y color rojo
            updateProgressDisplay(tareaId, 0, nuevoEstado);
        } else {
            // pendiente y en_proceso mantienen su progreso actual
            updateProgressDisplay(tareaId, progresoActual, nuevoEstado);
        }
    }
    
    // Función para calcular progreso basado en posición del clic
    function calculateProgressFromClick(event, progressElement) {
        const rect = progressElement.getBoundingClientRect();
        const clickX = event.clientX - rect.left;
        const width = rect.width;
        const percentage = Math.round((clickX / width) * 100);
        
        // Ajustar al múltiplo de 25 más cercano
        return Math.round(percentage / 25) * 25;
    }
    
    // Manejar clic en la barra de progreso de edición
    $('.progress-clickable-edit').on('click', function(e) {
        const tareaId = $(this).closest('.progress-container-interactive-edit').data('tarea-id');
        const progreso = calculateProgressFromClick(e, this);
        
        updateProgressDisplay(tareaId, progreso);
        syncProgressWithState(tareaId, progreso);
        actualizarProgresoServidor(tareaId, progreso);
    });
    
    // Variables para el arrastre en edición
    let isDraggingEdit = false;
    let currentTaskIdEdit = null;
    
    // Manejar inicio de arrastre en la barra de progreso de edición
    $('.progress-clickable-edit').on('mousedown', function(e) {
        e.preventDefault();
        isDraggingEdit = true;
        currentTaskIdEdit = $(this).closest('.progress-container-interactive-edit').data('tarea-id');
        
        // Cambiar cursor durante el arrastre
        $('body').css('cursor', 'grabbing');
        $(this).addClass('dragging');
        
        const progreso = calculateProgressFromClick(e, this);
        updateProgressDisplay(currentTaskIdEdit, progreso);
    });
    
    // Manejar movimiento durante el arrastre en edición
    $(document).on('mousemove', function(e) {
        if (!isDraggingEdit || !currentTaskIdEdit) return;
        
        const progressElement = $(`.progress-container-interactive-edit[data-tarea-id="${currentTaskIdEdit}"] .progress-clickable-edit`)[0];
        if (!progressElement) return;
        
        const progreso = calculateProgressFromClick(e, progressElement);
        updateProgressDisplay(currentTaskIdEdit, progreso);
    });
    
    // Manejar fin de arrastre en edición
    $(document).on('mouseup', function(e) {
        if (!isDraggingEdit || !currentTaskIdEdit) return;
        
        const progressElement = $(`.progress-container-interactive-edit[data-tarea-id="${currentTaskIdEdit}"] .progress-clickable-edit`)[0];
        if (progressElement) {
            const progreso = calculateProgressFromClick(e, progressElement);
            updateProgressDisplay(currentTaskIdEdit, progreso);
            syncProgressWithState(currentTaskIdEdit, progreso);
            actualizarProgresoServidor(currentTaskIdEdit, progreso);
            
            $(progressElement).removeClass('dragging');
        }
        
        isDraggingEdit = false;
        currentTaskIdEdit = null;
        $('body').css('cursor', 'default');
    });
    
    // Manejar clic en marcadores de porcentaje
    $('.progress-markers .marker').on('click', function() {
        const progreso = parseInt($(this).data('value'));
        const tareaId = $(this).closest('.progress-container-interactive-edit').data('tarea-id');
        
        updateProgressDisplay(tareaId, progreso);
        syncProgressWithState(tareaId, progreso);
        actualizarProgresoServidor(tareaId, progreso);
    });
    
    // Manejar cambios en el select de estado
    $('#estado').on('change', function() {
        const nuevoEstado = $(this).val();
        const tareaId = $('.progress-container-interactive-edit').data('tarea-id');
        
        if (tareaId) {
            syncStateWithProgress(tareaId, nuevoEstado);
        }
    });
    
    // Función para enviar actualización al servidor
    function actualizarProgresoServidor(tareaId, progreso) {
        // Mostrar indicador de carga
        const progressContainer = $(`.progress-container[data-tarea-id="${tareaId}"]`);
        const loadingHtml = `<div class="text-center p-2"><i class="fas fa-spinner fa-spin"></i> Actualizando...</div>`;
        
        const originalHtml = progressContainer.html();
        
        // Mostrar loading solo por un momento
        progressContainer.append('<div class="progress-loading-overlay">' + loadingHtml + '</div>');
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/actualizar-progreso') ?>',
            method: 'POST',
            data: {
                tarea_id: tareaId,
                progreso: progreso,
                comentario: `Progreso actualizado a ${progreso}% desde edición`,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            timeout: 10000,
            success: function(response) {
                // Remover loading
                progressContainer.find('.progress-loading-overlay').remove();
                
                if (response && response.success) {
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Progreso actualizado',
                        text: `Progreso actualizado a ${progreso}%`,
                        showConfirmButton: false,
                        timer: 1500,
                        toast: true,
                        position: 'top-end'
                    });
                    
                    // Actualizar el select de estado si cambió desde el servidor
                    if (response.nuevo_estado) {
                        const estadoSelect = $('#estado');
                        const estadoActual = estadoSelect.val();
                        
                        if (estadoActual !== response.nuevo_estado) {
                            estadoSelect.val(response.nuevo_estado);
                            
                            // Mostrar mensaje de sincronización
                            const estadoTexto = {
                                'pendiente': 'Pendiente',
                                'en_proceso': 'En Proceso',
                                'completada': 'Completada',
                                'cancelada': 'Cancelada'
                            };
                            
                            setTimeout(() => {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Estado sincronizado',
                                    text: `Estado cambiado automáticamente a: ${estadoTexto[response.nuevo_estado]}`,
                                    showConfirmButton: false,
                                    timer: 2000,
                                    toast: true,
                                    position: 'top-end'
                                });
                            }, 1600);
                        }
                    }
                } else {
                    const errorMsg = response.error || 'Error al actualizar el progreso';
                    Swal.fire('Error', errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                // Remover loading
                progressContainer.find('.progress-loading-overlay').remove();
                
                let errorMessage = 'Error de conexión';
                if (status === 'timeout') {
                    errorMessage = 'Tiempo de espera agotado';
                } else if (xhr.status === 404) {
                    errorMessage = 'Ruta no encontrada';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor';
                }
                
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    }
    
    // Esta función ya no es necesaria, la lógica se movió a actualizarProgresoServidor
    
    // Inicializar colores al cargar la página
    $('.progress-container-interactive-edit').each(function() {
        const tareaId = $(this).data('tarea-id');
        const progreso = parseInt($(`#progress-bar-${tareaId}`).attr('aria-valuenow'));
        const estadoActual = $('#estado').val();
        updateProgressColors(tareaId, progreso, estadoActual);
    });
});
</script>

<style>
/* Estilos para barra de progreso interactiva en edición */
.progress-container-interactive-edit {
    position: relative;
}

.progress-clickable-edit {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.progress-clickable-edit:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.progress-clickable-edit:active {
    transform: scale(0.98);
}

.progress-clickable-edit.dragging {
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    transform: scale(1.05);
}

.progress-inner-text {
    font-size: 16px;
    line-height: 1.2;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    color: white;
    font-weight: bold;
}

.progress-badge {
    font-size: 1rem;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

.progress-markers {
    margin-top: 10px;
}

.progress-markers .marker {
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 15px;
    transition: all 0.2s ease;
    font-weight: 500;
}

.progress-markers .marker:hover {
    background-color: #007bff;
    color: white;
    transform: scale(1.1);
}

/* Indicadores visuales en los bordes */
.progress-clickable-edit::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, 
        rgba(220,53,69,0.15) 0%, 
        rgba(255,193,7,0.15) 25%, 
        rgba(23,162,184,0.15) 50%, 
        rgba(255,193,7,0.15) 75%, 
        rgba(40,167,69,0.15) 100%);
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.progress-clickable-edit:hover::before {
    opacity: 1;
}

/* Transiciones suaves para cambios de color */
.progress-bar {
    transition: all 0.4s ease;
}

.progress-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: inherit;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .progress-inner-text {
        font-size: 14px;
    }
    
    .progress-markers .marker {
        padding: 2px 6px;
        font-size: 0.8rem;
    }
}
</style>
<?= $this->endSection() ?>