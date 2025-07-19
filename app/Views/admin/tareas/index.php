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
                    <i class="fas fa-tasks"></i>
                    Gestión de Tareas
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tareas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $estadisticas['total'] ?></h3>
                        <p>Total de Tareas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $estadisticas['pendiente'] ?></h3>
                        <p>Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $estadisticas['en_proceso'] ?></h3>
                        <p>En Proceso</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $estadisticas['completada'] ?></h3>
                        <p>Completadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (!empty($tareasVencidas)): ?>
        <div class="alert alert-danger">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Tareas Vencidas</h5>
            Tienes <?= count($tareasVencidas) ?> tarea(s) vencida(s) que requieren atención.
        </div>
        <?php endif; ?>

        <?php if (!empty($tareasProximasVencer)): ?>
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-clock"></i> Tareas Próximas a Vencer</h5>
            Tienes <?= count($tareasProximasVencer) ?> tarea(s) que vencen en los próximos 3 días.
        </div>
        <?php endif; ?>

        <!-- Filtros y Controles -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i>
                    Filtros y Controles
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/tareas/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Tarea
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= site_url('/admin/tareas') ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select name="estado" id="estado" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <?php foreach ($estados as $valor => $texto): ?>
                                        <option value="<?= $valor ?>" <?= ($filtros['estado'] ?? '') === $valor ? 'selected' : '' ?>>
                                            <?= $texto ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="prioridad">Prioridad</label>
                                <select name="prioridad" id="prioridad" class="form-control">
                                    <option value="">Todas las prioridades</option>
                                    <?php foreach ($prioridades as $valor => $texto): ?>
                                        <option value="<?= $valor ?>" <?= ($filtros['prioridad'] ?? '') === $valor ? 'selected' : '' ?>>
                                            <?= $texto ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="asignado_a">Asignado a</label>
                                <select name="asignado_a" id="asignado_a" class="form-control">
                                    <option value="">Todos los usuarios</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario->id ?>" <?= ($filtros['asignado_a'] ?? '') == $usuario->id ? 'selected' : '' ?>>
                                            <?= $usuario->username ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="periodo">Período</label>
                                <select name="periodo" id="periodo" class="form-control">
                                    <option value="este_mes" <?= ($filtros['periodo'] ?? 'este_mes') === 'este_mes' ? 'selected' : '' ?>>Este mes</option>
                                    <option value="esta_semana" <?= ($filtros['periodo'] ?? '') === 'esta_semana' ? 'selected' : '' ?>>Esta semana</option>
                                    <option value="hoy" <?= ($filtros['periodo'] ?? '') === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                                    <option value="" <?= ($filtros['periodo'] ?? '') === '' ? 'selected' : '' ?>>Todas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="<?= site_url('/admin/tareas') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Tareas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Lista de Tareas Creadas
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tareasTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Asignado a</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Progreso</th>
                                <th>Vencimiento</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tareas)): ?>
                                <?php foreach ($tareas as $tarea): ?>
                                <tr data-tarea-id="<?= $tarea->id ?>">
                                    <td><?= $tarea->id ?></td>
                                    <td>
                                        <a href="<?= site_url('/admin/tareas/show/' . $tarea->id) ?>">
                                            <?= esc($tarea->titulo) ?>
                                        </a>
                                        <?php if ($tarea->estaVencida()): ?>
                                            <span class="badge badge-danger ml-1">VENCIDA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($tarea->asignado_a_nombre ?? 'Usuario no encontrado') ?></td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorEstado() ?>">
                                            <?= $tarea->getEstadoTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorPrioridad() ?>">
                                            <?= $tarea->getPrioridadTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Barra de progreso solo visualización -->
                                        <div class="progress-container-readonly" data-tarea-id="<?= $tarea->id ?>">
                                            <div class="progress" style="height: 25px;" 
                                                 title="Progreso: <?= $tarea->progreso ?>%">
                                                <div class="progress-bar progress-bar-striped" 
                                                     id="progress-bar-<?= $tarea->id ?>"
                                                     role="progressbar" 
                                                     style="width: <?= $tarea->progreso ?>%"
                                                     aria-valuenow="<?= $tarea->progreso ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <span class="progress-text font-weight-bold"><?= $tarea->progreso ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tarea->fecha_vencimiento): ?>
                                            <?= $tarea->fecha_vencimiento->format('d/m/Y') ?>
                                            <br>
                                            <small class="text-muted"><?= $tarea->getDiasRestantesTexto() ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin fecha límite</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $tarea->created_at->format('d/m/Y H:i') ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= site_url('/admin/tareas/show/' . $tarea->id) ?>" 
                                               class="btn btn-sm btn-info" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('/admin/tareas/edit/' . $tarea->id) ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success btn-cambiar-estado" 
                                                    data-id="<?= $tarea->id ?>" 
                                                    data-estado="completada"
                                                    title="Marcar como completada"
                                                    <?= $tarea->estado === 'completada' ? 'disabled' : '' ?>>
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <?php if ($tarea->asignado_por == $currentUserId): ?>
                                                <button class="btn btn-sm btn-danger btn-eliminar" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        title="Eliminar (solo propietario)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" 
                                                        disabled
                                                        title="Solo el propietario puede eliminar">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
    // Inicializar DataTable
            // Configuración global de DataTables aplicada desde datatables-config.js

    $('#tareasTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25
    });

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
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#estadoModal').modal('hide');
    });

    // Manejar eliminación (usando delegación de eventos)
    $(document).on('click', '.btn-eliminar', function() {
        const tareaId = $(this).data('id');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('/admin/tareas/delete/') ?>' + tareaId,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('¡Eliminada!', response.message, 'success')
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

    // ===== VISUALIZACIÓN DE PROGRESO (SOLO LECTURA) =====
    
    // Función para actualizar colores según progreso
    function updateProgressColors(tareaId, progreso, estado = null) {
        const progressBar = $(`#progress-bar-${tareaId}`);
        
        // Remover clases existentes
        progressBar.removeClass('bg-danger bg-warning bg-info bg-success');
        
        // Si está cancelada, siempre rojo independientemente del progreso
        if (estado === 'cancelada') {
            progressBar.addClass('bg-danger');
            return;
        }
        
        // Aplicar colores según progreso
        if (progreso === 0) {
            progressBar.addClass('bg-danger');
        } else if (progreso === 25) {
            progressBar.addClass('bg-warning');
        } else if (progreso === 50) {
            progressBar.addClass('bg-info');
        } else if (progreso === 75) {
            progressBar.addClass('bg-warning');
        } else if (progreso === 100) {
            progressBar.addClass('bg-success');
        }
    }

    // Función para actualizar el progreso vía AJAX
    function actualizarProgreso(tareaId, progreso) {
        if (!tareaId || progreso === undefined || progreso === null) {
            console.error('❌ Datos inválidos para actualizar progreso:', { tareaId, progreso });
            return;
        }

        
        // Mostrar loading en la barra de progreso
        const progressContainer = $(`.progress-container[data-tarea-id="${tareaId}"]`);
        if (progressContainer.length === 0) {
            console.error('❌ No se encontró contenedor de progreso para tarea:', tareaId);
            return;
        }
        
        const originalContent = progressContainer.html();
        progressContainer.html('<div class="text-center p-2"><i class="fas fa-spinner fa-spin"></i> Actualizando...</div>');

        $.ajax({
            url: '<?= site_url('/admin/tareas/actualizar-progreso') ?>',
            method: 'POST',
            data: {
                tarea_id: tareaId,
                progreso: progreso,
                comentario: `Progreso actualizado a ${progreso}%`,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            timeout: 10000,
            success: function(response) {
                try {
                    if (response && response.success) {
                        // Actualizar la interfaz
                        actualizarInterfazProgreso(tareaId, progreso, response.nuevo_estado);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Progreso actualizado',
                                text: `Progreso actualizado a ${progreso}%`,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                        
                        // Actualizar estado en la tabla si cambió
                        if (response.nuevo_estado) {
                            const estadoBadge = $(`tr[data-tarea-id="${tareaId}"] .badge`).first();
                            if (estadoBadge.length) {
                                // Actualizar el badge de estado
                                estadoBadge.removeClass().addClass('badge');
                                if (response.nuevo_estado === 'completada') {
                                    estadoBadge.addClass('badge-success').text('Completada');
                                } else if (response.nuevo_estado === 'en_proceso') {
                                    estadoBadge.addClass('badge-warning').text('En Proceso');
                                }
                            }
                        }
                    } else {
                        progressContainer.html(originalContent);
                        const errorMsg = response.error || 'Error al actualizar el progreso';
                        console.error('❌ Error del servidor:', errorMsg);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    }
                } catch (error) {
                    console.error('❌ Error procesando respuesta:', error);
                    progressContainer.html(originalContent);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Error procesando la respuesta del servidor', 'error');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error AJAX:', { xhr, status, error });
                progressContainer.html(originalContent);
                let errorMessage = 'Error de conexión';
                if (status === 'timeout') {
                    errorMessage = 'Tiempo de espera agotado';
                } else if (xhr.status === 404) {
                    errorMessage = 'Ruta no encontrada';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor';
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMessage, 'error');
                }
            }
        });
    }

    // Función para actualizar la interfaz después de cambio de progreso (desde servidor)
    function actualizarInterfazProgreso(tareaId, progreso, nuevoEstado) {
        // Solo actualizar visualmente sin interacción
        $(`#progress-bar-${tareaId} .progress-text`).text(`${progreso}%`);
        $(`#progress-bar-${tareaId}`).css('width', `${progreso}%`).attr('aria-valuenow', progreso);
        
        // Obtener estado de la fila
        const row = $(`tr[data-tarea-id="${tareaId}"]`);
        const estadoBadge = row.find('.badge').first();
        let estado = 'pendiente';
        if (estadoBadge.text().toLowerCase().includes('completada')) estado = 'completada';
        else if (estadoBadge.text().toLowerCase().includes('cancelada')) estado = 'cancelada';
        else if (estadoBadge.text().toLowerCase().includes('proceso')) estado = 'en_proceso';
        
        updateProgressColors(tareaId, progreso, estado);
    }
    
    // Inicializar colores y estado al cargar la página
    $('.progress-container-readonly').each(function() {
        const tareaId = $(this).data('tarea-id');
        const progreso = parseInt($(`#progress-bar-${tareaId}`).attr('aria-valuenow'));
        
        // Obtener estado del badge
        const row = $(`tr[data-tarea-id="${tareaId}"]`);
        const estadoBadge = row.find('.badge').first();
        let estado = 'pendiente';
        if (estadoBadge.text().toLowerCase().includes('completada')) estado = 'completada';
        else if (estadoBadge.text().toLowerCase().includes('cancelada')) estado = 'cancelada';
        else if (estadoBadge.text().toLowerCase().includes('proceso')) estado = 'en_proceso';
        
        updateProgressColors(tareaId, progreso, estado);
    });
});
</script>

<style>
/* Estilos para barra de progreso solo lectura en index */
.progress-container-readonly {
    min-width: 180px;
    max-width: 220px;
    position: relative;
}

.progress-text {
    font-weight: bold;
    font-size: 12px;
    line-height: 1.2;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    color: white;
}

/* Transiciones suaves para cambios de color */
.progress-bar {
    transition: all 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .progress-container-readonly {
        min-width: 150px;
        max-width: 180px;
    }
    
    .progress-text {
        font-size: 10px;
    }
}
</style>
<?= $this->endSection() ?>