<?php
/**
 * Partial: Tabla de Tareas para Mis Tareas
 * 
 * Parámetros:
 * - $tareas: Array de tareas
 * - $tipo: 'propias' o 'asignadas'
 */

// Establecer valores por defecto para evitar errores
$tareas = $tareas ?? [];
$tipo = $tipo ?? 'general';

?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="misTareasTable_<?= $tipo ?>">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Progreso</th>
                <th>Vencimiento</th>
                <th>Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tareas)): ?>
                <?php foreach ($tareas as $tarea): ?>
                <tr data-tarea-id="<?= $tarea->id ?>">
                    <td><?= $tarea->id ?></td>
                    <td>
                        <a href="<?= site_url('/admin/tareas/mis-tareas/' . $tarea->id) ?>">
                            <?= esc($tarea->titulo) ?>
                        </a>
                        <?php if ($tarea->estaVencida()): ?>
                            <span class="badge badge-danger ml-1">VENCIDA</span>
                        <?php endif; ?>
                        <?php if (!empty($tarea->descripcion)): ?>
                            <br><small class="text-muted"><?= esc(substr($tarea->descripcion, 0, 50)) ?>...</small>
                        <?php endif; ?>
                    </td>
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
                        <!-- Barra de progreso interactiva directa para mis-tareas -->
                        <div class="progress-container-interactive" data-tarea-id="<?= $tarea->id ?>">
                            <div class="progress progress-clickable" style="height: 25px; cursor: pointer;" 
                                 title="Clic o arrastra para cambiar progreso">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
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
                            <?= date('d/m/Y', strtotime($tarea->fecha_vencimiento)) ?>
                            <br>
                            <small class="text-muted"><?= $tarea->getDiasRestantesTexto() ?></small>
                        <?php else: ?>
                            <span class="text-muted">Sin fecha límite</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $tarea->created_at->format('d/m/Y') ?>
                        <br>
                        <small class="text-muted"><?= $tarea->created_at->format('H:i') ?></small>
                    </td>
                    <td>
                        <div class="btn-group-vertical btn-group-sm" role="group">
                            <!-- Ver detalles -->
                            <a href="<?= site_url('/admin/tareas/mis-tareas/show/' . $tarea->id) ?>" 
                               class="btn btn-sm btn-info mb-1" 
                               title="Ver detalles">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            
                            <?php if ($tarea->estado !== 'completada'): ?>
                                <!-- Acciones según el estado -->
                                <?php if ($tarea->estado === 'pendiente'): ?>
                                    <button class="btn btn-sm btn-success btn-iniciar-tarea mb-1" 
                                            data-id="<?= $tarea->id ?>" 
                                            title="Iniciar tarea">
                                        <i class="fas fa-play"></i> Iniciar
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($tarea->estado === 'en_proceso'): ?>
                                    <button class="btn btn-sm btn-primary btn-completar-tarea mb-1" 
                                            data-id="<?= $tarea->id ?>" 
                                            title="Marcar como completada">
                                        <i class="fas fa-check"></i> Completar
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Agregar comentario -->
                                <button class="btn btn-sm btn-warning btn-agregar-comentario" 
                                        data-id="<?= $tarea->id ?>" 
                                        title="Agregar comentario">
                                    <i class="fas fa-comment"></i> Comentar
                                </button>
                            <?php else: ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Completada
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <br>
                        <?php if ($tipo === 'propias'): ?>
                            No tienes tareas propias. <a href="<?= site_url('/admin/tareas/mis-tareas/create') ?>">Crear una nueva</a>
                        <?php else: ?>
                            No tienes tareas asignadas por otros usuarios.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* Estilos para barra de progreso interactiva en mis-tareas */
.progress-container-interactive {
    min-width: 180px;
    max-width: 220px;
    position: relative;
}

.progress-clickable {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    transition: all 0.2s ease;
}

.progress-clickable:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.progress-clickable:active {
    transform: scale(0.98);
}

.progress-text {
    font-weight: bold;
    font-size: 12px;
    line-height: 1.2;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    color: white;
}

/* Efectos visuales para el arrastre */
.progress-clickable.dragging {
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    transform: scale(1.05);
}

/* Transiciones suaves para cambios de color */
.progress-bar {
    transition: all 0.3s ease;
}

/* Indicadores visuales en los bordes */
.progress-clickable::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: inherit;
    background: linear-gradient(90deg, 
        rgba(220,53,69,0.1) 0%, 
        rgba(255,193,7,0.1) 25%, 
        rgba(23,162,184,0.1) 50%, 
        rgba(255,193,7,0.1) 75%, 
        rgba(40,167,69,0.1) 100%);
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.progress-clickable:hover::before {
    opacity: 1;
}

.btn-group-vertical .btn {
    margin-bottom: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .progress-container-interactive {
        min-width: 150px;
        max-width: 180px;
    }
    
    .progress-text {
        font-size: 10px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Verificar si la tabla tiene datos antes de inicializar DataTables
    var tabla = $('#misTareasTable_<?= $tipo ?>');
    var filas = tabla.find('tbody tr');
    
    if (filas.length > 0 && !filas.first().find('td[colspan]').length) {
        try {
            tabla.DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "columnDefs": [
                    { "orderable": false, "targets": [7] }, // Columna de acciones
                    { "width": "200px", "targets": [4] }   // Columna de progreso
                ],
                "responsive": true,
                "destroy": true // Permitir re-inicialización
            });
        } catch (error) {
            console.error('❌ Error inicializando DataTable:', error);
        }
    } else {
    }

    // ===== FUNCIONALIDAD DE BARRA DE PROGRESO INTERACTIVA =====
    
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
    
    // Función para actualizar la interfaz visual
    function updateProgressDisplay(tareaId, progreso, estado = null) {
        $(`#progress-bar-${tareaId} .progress-text`).text(`${progreso}%`);
        $(`#progress-bar-${tareaId}`).css('width', `${progreso}%`).attr('aria-valuenow', progreso);
        
        // Obtener estado de la fila si no se proporciona
        if (!estado) {
            const row = $(`tr[data-tarea-id="${tareaId}"]`);
            const estadoBadge = row.find('.badge').first();
            if (estadoBadge.text().toLowerCase().includes('completada')) estado = 'completada';
            else if (estadoBadge.text().toLowerCase().includes('cancelada')) estado = 'cancelada';
            else if (estadoBadge.text().toLowerCase().includes('proceso')) estado = 'en_proceso';
            else estado = 'pendiente';
        }
        
        updateProgressColors(tareaId, progreso, estado);
    }
    
    // Función para sincronizar progreso con estado en mis-tareas
    function syncProgressWithState(tareaId, progreso) {
        
        const row = $(`tr[data-tarea-id="${tareaId}"]`);
        const estadoBadge = row.find('.badge').first();
        const estadoActual = estadoBadge.text().toLowerCase();
        
        // Sincronización automática según progreso
        if (progreso === 100 && !estadoActual.includes('completada') && !estadoActual.includes('cancelada')) {
            // 100% → Completada
            estadoBadge.removeClass().addClass('badge badge-success').text('Completada');
            
            // Actualizar botones de acción
            const actionsCol = row.find('td:last');
            actionsCol.html(`
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Completada
                </span>
            `);
        } else if (progreso >= 25 && progreso <= 75 && estadoActual.includes('completada')) {
            // Bajar de 100% a 25-75% → En Proceso
            estadoBadge.removeClass().addClass('badge badge-warning').text('En Proceso');
            
            // Restaurar botones de acción normales
            const actionsCol = row.find('td:last');
            actionsCol.html(`
                <div class="btn-group-vertical btn-group-sm" role="group">
                    <a href="<?= site_url('/admin/tareas/mis-tareas/show/' . "' + tareaId + '") ?>" 
                       class="btn btn-sm btn-info mb-1" title="Ver detalles">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <button class="btn btn-sm btn-primary btn-completar-tarea mb-1" 
                            data-id="' + tareaId + '" title="Marcar como completada">
                        <i class="fas fa-check"></i> Completar
                    </button>
                    <button class="btn btn-sm btn-warning btn-agregar-comentario" 
                            data-id="' + tareaId + '" title="Agregar comentario">
                        <i class="fas fa-comment"></i> Comentar
                    </button>
                </div>
            `);
        } else if (progreso === 0 && (estadoActual.includes('completada') || estadoActual.includes('proceso'))) {
            // 0% → Pendiente
            estadoBadge.removeClass().addClass('badge badge-secondary').text('Pendiente');
            
            // Restaurar botones de acción normales
            const actionsCol = row.find('td:last');
            actionsCol.html(`
                <div class="btn-group-vertical btn-group-sm" role="group">
                    <a href="<?= site_url('/admin/tareas/mis-tareas/show/' . "' + tareaId + '") ?>" 
                       class="btn btn-sm btn-info mb-1" title="Ver detalles">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <button class="btn btn-sm btn-success btn-iniciar-tarea mb-1" 
                            data-id="' + tareaId + '" title="Iniciar tarea">
                        <i class="fas fa-play"></i> Iniciar
                    </button>
                    <button class="btn btn-sm btn-warning btn-agregar-comentario" 
                            data-id="' + tareaId + '" title="Agregar comentario">
                        <i class="fas fa-comment"></i> Comentar
                    </button>
                </div>
            `);
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
    
    // Manejar funcionalidad de progreso (solo si hay tareas)
    if (filas.length > 0 && !filas.first().find('td[colspan]').length) {
        // Manejar clic en la barra de progreso
        $('.progress-clickable').on('click', function(e) {
            const tareaId = $(this).closest('.progress-container-interactive').data('tarea-id');
            const progreso = calculateProgressFromClick(e, this);
            
            updateProgressDisplay(tareaId, progreso);
            syncProgressWithState(tareaId, progreso);
            actualizarProgreso(tareaId, progreso);
        });
        
        // Variables para el arrastre
        let isDragging = false;
        let currentTaskId = null;
        
        // Manejar inicio de arrastre en la barra de progreso
        $('.progress-clickable').on('mousedown', function(e) {
            e.preventDefault();
            isDragging = true;
            currentTaskId = $(this).closest('.progress-container-interactive').data('tarea-id');
            
            // Cambiar cursor durante el arrastre
            $('body').css('cursor', 'grabbing');
            
            const progreso = calculateProgressFromClick(e, this);
            updateProgressDisplay(currentTaskId, progreso);
        });
        
        // Manejar movimiento durante el arrastre
        $(document).on('mousemove', function(e) {
            if (!isDragging || !currentTaskId) return;
            
            const progressElement = $(`.progress-container-interactive[data-tarea-id="${currentTaskId}"] .progress-clickable`)[0];
            if (!progressElement) return;
            
            const progreso = calculateProgressFromClick(e, progressElement);
            updateProgressDisplay(currentTaskId, progreso);
        });
        
        // Manejar fin de arrastre
        $(document).on('mouseup', function(e) {
            if (!isDragging || !currentTaskId) return;
            
            const progressElement = $(`.progress-container-interactive[data-tarea-id="${currentTaskId}"] .progress-clickable`)[0];
            if (progressElement) {
                const progreso = calculateProgressFromClick(e, progressElement);
                updateProgressDisplay(currentTaskId, progreso);
                syncProgressWithState(currentTaskId, progreso);
                actualizarProgreso(currentTaskId, progreso);
            }
            
            isDragging = false;
            currentTaskId = null;
            $('body').css('cursor', 'default');
        });
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

        // Verificar que jQuery y CSRF están disponibles
        if (typeof $ === 'undefined') {
            console.error('❌ jQuery no está disponible');
            progressContainer.html(originalContent);
            return;
        }

        $.ajax({
            url: '<?= site_url('/admin/tareas/actualizar-progreso') ?>',
            method: 'POST',
            data: {
                tarea_id: tareaId,
                progreso: progreso,
                comentario: `Progreso actualizado a ${progreso}% desde mis tareas`,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            timeout: 10000, // 10 segundos
            success: function(response) {
                try {
                    if (response && response.success) {
                        // Actualizar la interfaz
                        actualizarInterfazProgreso(tareaId, progreso, response.nuevo_estado);
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Progreso actualizado exitosamente');
                        }
                    } else {
                        progressContainer.html(originalContent);
                        const errorMsg = response.error || 'Error al actualizar el progreso';
                        console.error('❌ Error del servidor:', errorMsg);
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMsg);
                        }
                    }
                } catch (error) {
                    console.error('❌ Error procesando respuesta:', error);
                    progressContainer.html(originalContent);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error procesando la respuesta del servidor');
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
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                }
            }
        });
    }

    // Función para actualizar la interfaz después de cambio de progreso
    function actualizarInterfazProgreso(tareaId, progreso, nuevoEstado) {
        // Usar la función de actualización directa
        updateProgressDisplay(tareaId, progreso);
        
        // Si está completa, actualizar estado en la fila
        if (progreso >= 100 && nuevoEstado === 'completada') {
            const row = $(`tr[data-tarea-id="${tareaId}"]`);
            row.find('.badge').first().removeClass().addClass('badge badge-success').text('Completada');
            
            // Cambiar botones de acción
            const actionsCol = row.find('td:last');
            actionsCol.html(`
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Completada
                </span>
            `);
        }
    }

    // Manejar iniciar tarea
    $('.btn-iniciar-tarea').on('click', function() {
        const tareaId = $(this).data('id');
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/iniciar') ?>',
            method: 'POST',
            data: {
                tarea_id: tareaId,
                comentario: 'Tarea iniciada desde mis tareas',
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Recargar para mostrar cambios
                } else {
                    toastr.error(response.error || 'Error al iniciar la tarea');
                }
            },
            error: function() {
                toastr.error('Error de conexión');
            }
        });
    });

    // Manejar completar tarea
    $('.btn-completar-tarea').on('click', function() {
        const tareaId = $(this).data('id');
        
        if (confirm('¿Está seguro de marcar esta tarea como completada?')) {
            $.ajax({
                url: '<?= site_url('/admin/tareas/mis-tareas/completar') ?>',
                method: 'POST',
                data: {
                    tarea_id: tareaId,
                    comentario: 'Tarea completada desde mis tareas',
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Recargar para mostrar cambios
                    } else {
                        toastr.error(response.error || 'Error al completar la tarea');
                    }
                },
                error: function() {
                    toastr.error('Error de conexión');
                }
            });
        }
    });
    
    // Inicializar colores y estado al cargar la página
    $('.progress-container-interactive').each(function() {
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