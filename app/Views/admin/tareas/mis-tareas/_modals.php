<!-- Modal para actualizar progreso con opciones predefinidas -->
<div class="modal fade" id="progresoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Progreso</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formProgreso">
                    <input type="hidden" id="tarea_id_progreso" name="tarea_id">
                    
                    <!-- Opciones de progreso predefinidas -->
                    <div class="form-group">
                        <label>Seleccionar Progreso:</label>
                        <div class="row">
                            <div class="col-md-2 col-6 mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-block btn-progreso-preset" data-progreso="0">
                                    😴<br>0%<br><small>Sin iniciar</small>
                                </button>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block btn-progreso-preset" data-progreso="25">
                                    🚀<br>25%<br><small>Iniciado</small>
                                </button>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <button type="button" class="btn btn-outline-info btn-block btn-progreso-preset" data-progreso="50">
                                    ⚡<br>50%<br><small>Medio</small>
                                </button>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <button type="button" class="btn btn-outline-warning btn-block btn-progreso-preset" data-progreso="75">
                                    🔥<br>75%<br><small>Avanzado</small>
                                </button>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <button type="button" class="btn btn-outline-success btn-block btn-progreso-preset" data-progreso="100">
                                    ✅<br>100%<br><small>Completado</small>
                                </button>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                <button type="button" class="btn btn-outline-dark btn-block" id="btn-progreso-custom">
                                    🎯<br>Manual<br><small>Personalizar</small>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slider para progreso personalizado -->
                    <div class="form-group" id="progreso-custom-container" style="display: none;">
                        <label for="progreso">Progreso Personalizado (%)</label>
                        <input type="range" 
                               class="form-control-range" 
                               id="progreso" 
                               name="progreso" 
                               min="0" 
                               max="100" 
                               step="5">
                        <div class="text-center">
                            <span id="progreso-value" class="badge badge-primary">0%</span>
                        </div>
                    </div>
                    
                    <!-- Progreso seleccionado -->
                    <div class="form-group">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Progreso Actual:</h6>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         id="preview-progress-bar"
                                         role="progressbar" 
                                         style="width: 0%"
                                         aria-valuenow="0" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <span id="preview-progress-text">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comentario_progreso">Comentario sobre el avance</label>
                        <textarea class="form-control" 
                                  id="comentario_progreso" 
                                  name="comentario" 
                                  rows="3" 
                                  placeholder="Describe el avance realizado..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnActualizarProgreso">
                    <i class="fas fa-save"></i> Actualizar Progreso
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para completar tarea -->
<div class="modal fade" id="completarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Completar Tarea</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCompletar">
                    <input type="hidden" id="tarea_id_completar" name="tarea_id">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        ¡Excelente! Estás por marcar esta tarea como completada.
                    </div>
                    
                    <div class="form-group">
                        <label for="comentario_completar">Comentario final</label>
                        <textarea class="form-control" 
                                  id="comentario_completar" 
                                  name="comentario" 
                                  rows="3" 
                                  placeholder="Describe cómo se completó la tarea..." 
                                  required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnCompletar">
                    <i class="fas fa-check"></i> Marcar como Completada
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar comentario -->
<div class="modal fade" id="comentarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Comentario</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formComentario">
                    <input type="hidden" id="tarea_id_comentario" name="tarea_id">
                    
                    <div class="form-group">
                        <label for="comentario_nuevo">Comentario</label>
                        <textarea class="form-control" 
                                  id="comentario_nuevo" 
                                  name="comentario" 
                                  rows="4" 
                                  placeholder="Escribe tu comentario sobre esta tarea..." 
                                  required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnAgregarComentario">
                    <i class="fas fa-comment"></i> Agregar Comentario
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let progresoSeleccionado = 0;
    
    // Manejar actualización de progreso
    $('.btn-actualizar-progreso').on('click', function() {
        const tareaId = $(this).data('id');
        const progresoActual = $(this).data('progreso');
        
        $('#tarea_id_progreso').val(tareaId);
        progresoSeleccionado = progresoActual;
        actualizarVisualizacionProgreso(progresoActual);
        $('#progresoModal').modal('show');
    });
    
    // Manejar botones de progreso predefinido
    $('.btn-progreso-preset').on('click', function() {
        const progreso = $(this).data('progreso');
        progresoSeleccionado = progreso;
        
        // Actualizar estilos de botones
        $('.btn-progreso-preset').removeClass('btn-primary btn-secondary btn-info btn-warning btn-success')
                                   .addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary btn-outline-primary btn-outline-info btn-outline-warning btn-outline-success')
               .addClass('btn-primary');
        
        // Ocultar slider personalizado
        $('#progreso-custom-container').hide();
        
        actualizarVisualizacionProgreso(progreso);
    });
    
    // Manejar progreso personalizado
    $('#btn-progreso-custom').on('click', function() {
        $('.btn-progreso-preset').removeClass('btn-primary btn-secondary btn-info btn-warning btn-success')
                                   .addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-dark').addClass('btn-dark');
        
        $('#progreso-custom-container').show();
        $('#progreso').val(progresoSeleccionado);
        $('#progreso-value').text(progresoSeleccionado + '%');
    });
    
    // Actualizar progreso desde slider
    $('#progreso').on('input', function() {
        progresoSeleccionado = $(this).val();
        $('#progreso-value').text(progresoSeleccionado + '%');
        actualizarVisualizacionProgreso(progresoSeleccionado);
    });
    
    function actualizarVisualizacionProgreso(progreso) {
        $('#preview-progress-bar').css('width', progreso + '%')
                                  .attr('aria-valuenow', progreso);
        $('#preview-progress-text').text(progreso + '%');
        
        // Cambiar color según progreso
        const colorClass = progreso === 0 ? 'bg-secondary' :
                          progreso <= 25 ? 'bg-danger' :
                          progreso <= 50 ? 'bg-warning' :
                          progreso <= 75 ? 'bg-info' : 'bg-success';
        
        $('#preview-progress-bar').removeClass('bg-secondary bg-danger bg-warning bg-info bg-success')
                                  .addClass(colorClass);
    }
    
    // Confirmar actualización de progreso
    $('#btnActualizarProgreso').on('click', function() {
        const formData = {
            tarea_id: $('#tarea_id_progreso').val(),
            progreso: progresoSeleccionado,
            comentario: $('#comentario_progreso').val()
        };
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/actualizar-progreso') ?>',
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
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#progresoModal').modal('hide');
    });
    
    // Resto de modales (completar, comentario, iniciar)
    $('.btn-completar').on('click', function() {
        const tareaId = $(this).data('id');
        $('#tarea_id_completar').val(tareaId);
        $('#completarModal').modal('show');
    });
    
    $('.btn-comentario').on('click', function() {
        const tareaId = $(this).data('id');
        $('#tarea_id_comentario').val(tareaId);
        $('#comentarioModal').modal('show');
    });
    
    $('#btnCompletar').on('click', function() {
        const formData = $('#formCompletar').serialize();
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/completar') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Completada!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#completarModal').modal('hide');
    });
    
    $('#btnAgregarComentario').on('click', function() {
        const formData = $('#formComentario').serialize();
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/agregar-comentario') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Agregado!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#comentarioModal').modal('hide');
    });
    
    // Manejar iniciar tarea
    $('.btn-iniciar').on('click', function() {
        const tareaId = $(this).data('id');
        
        Swal.fire({
            title: '¿Iniciar esta tarea?',
            text: "Se marcará como 'En Proceso'",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, iniciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('/admin/tareas/mis-tareas/iniciar') ?>',
                    type: 'POST',
                    data: {
                        tarea_id: tareaId,
                        comentario: 'Tarea iniciada por el usuario'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('¡Iniciada!', response.message, 'success')
                                .then(() => location.reload());
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
    });
});
</script>