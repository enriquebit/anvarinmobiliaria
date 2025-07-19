<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="#">Catálogos</a></li>
<li class="breadcrumb-item active">Amenidades</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-swimming-pool"></i> Gestión de Amenidades
                    </h3>
                    <div class="card-tools">
                        <?php if (isAdmin()): ?>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-amenidad">
                            <i class="fas fa-plus"></i> Nueva Amenidad
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-amenidades" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Icono</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Lotes Asociados</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

<!-- Modal para crear/editar amenidad -->
<div class="modal fade" id="modal-amenidad" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="titulo-modal">Nueva Amenidad</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="form-amenidad">
                <div class="modal-body">
                    <input type="hidden" id="amenidad-id" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required 
                                       placeholder="Ej: Alberca, Gimnasio, Seguridad...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="activo">Estado</label>
                                <select class="form-control" id="activo" name="activo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="icono">Clase de Icono FontAwesome <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i id="preview-icono" class="fas fa-swimming-pool"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" id="icono" name="icono" required 
                                   placeholder="Ej: fas fa-swimming-pool, fas fa-car, fas fa-leaf..."
                                   value="fas fa-swimming-pool">
                        </div>
                        <small class="form-text text-muted">
                            Usa clases de <a href="https://fontawesome.com/icons" target="_blank">FontAwesome</a>. 
                            El icono se actualiza automáticamente mientras escribes.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                  placeholder="Descripción detallada de la amenidad..."></textarea>
                    </div>

                    <!-- Iconos comunes para selección rápida -->
                    <div class="form-group">
                        <label>Iconos Comunes (Click para seleccionar)</label>
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-swimming-pool">
                                <i class="fas fa-swimming-pool"></i> Alberca
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-car">
                                <i class="fas fa-car"></i> Estacionamiento
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-leaf">
                                <i class="fas fa-leaf"></i> Jardín
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-shield-alt">
                                <i class="fas fa-shield-alt"></i> Seguridad
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-dumbbell">
                                <i class="fas fa-dumbbell"></i> Gimnasio
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-child">
                                <i class="fas fa-child"></i> Juegos
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-building">
                                <i class="fas fa-building"></i> Casa Club
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-bed">
                                <i class="fas fa-bed"></i> Recámaras
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-bath">
                                <i class="fas fa-bath"></i> Baños
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm m-1 btn-icono-rapido" data-icono="fas fa-home">
                                <i class="fas fa-home"></i> Terraza
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let tabla;

    // Inicializar DataTable
    function inicializarTabla() {
        tabla = $('#tabla-amenidades').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= base_url('admin/catalogos/amenidades/obtener-amenidades') ?>',
                type: 'POST',
                dataSrc: function(json) {
                    if (json.success) {
                        return json.data;
                    } else {
                        toastr.error(json.message);
                        return [];
                    }
                }
            },
            columns: [
                { data: 'id', width: '50px' },
                { 
                    data: 'icono', 
                    width: '60px',
                    orderable: false,
                    render: function(data, type, row) {
                        return '<i class="' + data + '" style="font-size: 1.2em; color: #007bff;"></i>';
                    }
                },
                { data: 'nombre' },
                { 
                    data: 'descripcion',
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">Sin descripción</span>';
                        return data.length > 50 ? data.substring(0, 50) + '...' : data;
                    }
                },
                { 
                    data: 'total_lotes',
                    width: '100px',
                    className: 'text-center',
                    render: function(data, type, row) {
                        const total = data || 0;
                        const color = total > 0 ? 'success' : 'secondary';
                        return '<span class="badge badge-' + color + '">' + total + ' lotes</span>';
                    }
                },
                { 
                    data: 'activo',
                    width: '80px',
                    className: 'text-center',
                    render: function(data, type, row) {
                        const estado = data ? 'Activo' : 'Inactivo';
                        const color = data ? 'success' : 'secondary';
                        return '<span class="badge badge-' + color + '">' + estado + '</span>';
                    }
                },
                { 
                    data: 'id',
                    width: '120px',
                    orderable: false,
                    render: function(data, type, row) {
                        return generarBotonesAccion(row);
                    }
                }
            ],
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar:",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                }
            },
            order: [[2, 'asc']], // Ordenar por nombre
            });
    }

    function generarBotonesAccion(amenidad) {
        let botones = '<div class="btn-group" role="group">';
        
        // Editar
        if (amenidad.activo) {
            botones += '<button class="btn btn-sm btn-warning btn-editar" data-id="' + amenidad.id + '" title="Editar">' +
                       '<i class="fas fa-edit"></i></button>';
        }
        
        // Cambiar estado (solo superadmin puede eliminar)
        if (amenidad.activo) {
            if (amenidad.total_lotes == 0 && <?= isSuperAdmin() ? 'true' : 'false' ?>) {
                botones += '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' + amenidad.id + '" title="Desactivar">' +
                           '<i class="fas fa-trash"></i></button>';
            } else {
                botones += '<button class="btn btn-sm btn-secondary" title="No se puede eliminar (tiene lotes asociados)" disabled>' +
                           '<i class="fas fa-ban"></i></button>';
            }
        } else {
            botones += '<button class="btn btn-sm btn-success btn-activar" data-id="' + amenidad.id + '" title="Activar">' +
                       '<i class="fas fa-check"></i></button>';
        }
        
        botones += '</div>';
        return botones;
    }

    // Inicializar tabla
    inicializarTabla();

    // Preview del icono mientras se escribe
    $('#icono').on('input', function() {
        const iconClass = $(this).val() || 'fas fa-question';
        $('#preview-icono').attr('class', iconClass);
    });

    // Selección rápida de iconos
    $(document).on('click', '.btn-icono-rapido', function() {
        const icono = $(this).data('icono');
        $('#icono').val(icono);
        $('#preview-icono').attr('class', icono);
        
        // Agregar efecto visual
        $('.btn-icono-rapido').removeClass('btn-secondary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');
    });

    // Abrir modal para nueva amenidad
    $('#modal-amenidad').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btn-editar')) {
            $('#form-amenidad')[0].reset();
            $('#amenidad-id').val('');
            $('#titulo-modal').text('Nueva Amenidad');
            $('#icono').val('fas fa-swimming-pool');
            $('#preview-icono').attr('class', 'fas fa-swimming-pool');
            
            // Resetear selección de iconos
            $('.btn-icono-rapido').removeClass('btn-secondary').addClass('btn-outline-secondary');
            $('.btn-icono-rapido[data-icono="fas fa-swimming-pool"]').removeClass('btn-outline-secondary').addClass('btn-secondary');
        }
    });

    // Editar amenidad
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        
        $.get('<?= base_url('admin/catalogos/amenidades/obtener-amenidades') ?>/' + id)
            .done(function(response) {
                if (response.success) {
                    const amenidad = response.data;
                    $('#amenidad-id').val(amenidad.id);
                    $('#nombre').val(amenidad.nombre);
                    $('#icono').val(amenidad.icono);
                    $('#descripcion').val(amenidad.descripcion);
                    $('#activo').val(amenidad.activo ? '1' : '0');
                    $('#preview-icono').attr('class', amenidad.icono);
                    $('#titulo-modal').text('Editar Amenidad');
                    $('#modal-amenidad').modal('show');
                }
            });
    });

    // Guardar amenidad
    $('#form-amenidad').submit(function(e) {
        e.preventDefault();
        
        const id = $('#amenidad-id').val();
        const url = id ? 
            '<?= base_url('admin/catalogos/amenidades/actualizar') ?>/' + id : 
            '<?= base_url('admin/catalogos/amenidades/guardar') ?>';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#modal-amenidad').modal('hide');
                    tabla.ajax.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error de conexión');
            },
            complete: function() {
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    // Eliminar/Desactivar amenidad
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: '¿Confirmar desactivación?',
            text: 'Esta amenidad se marcará como inactiva',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                cambiarEstado(id, false);
            }
        });
    });

    // Activar amenidad
    $(document).on('click', '.btn-activar', function() {
        const id = $(this).data('id');
        cambiarEstado(id, true);
    });

    function cambiarEstado(id, activo) {
        $.post('<?= base_url('admin/catalogos/amenidades/cambiar-estado') ?>/' + id, {
            activo: activo ? 1 : 0
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                tabla.ajax.reload();
            } else {
                toastr.error(response.message);
            }
        })
        .fail(function() {
            toastr.error('Error de conexión');
        });
    }
});
</script>
<?= $this->endSection() ?>