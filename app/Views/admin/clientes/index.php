<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
    <?php if (!empty($item['url'])): ?>
        <li class="breadcrumb-item">
            <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
        </li>
    <?php else: ?>
        <li class="breadcrumb-item active"><?= $item['name'] ?></li>
    <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
        
        <!-- Tarjetas de estadísticas -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="total-clientes"><?= $estadisticas['total'] ?></h3>
                        <p>Total Clientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="clientes-activos"><?= $estadisticas['activos'] ?></h3>
                        <p>Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="clientes-pendientes"><?= $estadisticas['pendientes'] ?></h3>
                        <p>Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="clientes-inactivos"><?= $estadisticas['inactivos'] ?></h3>
                        <p>Inactivos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y acciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filtros y Acciones
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/clientes/create') ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i>
                        Nuevo Cliente
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="form-filtros" class="row">
                    <div class="col-md-3">
                        <label for="filtro-etapa">Etapa del Proceso</label>
                        <select id="filtro-etapa" name="etapa" class="form-control">
                            <option value="">Todas las etapas</option>
                            <?php foreach ($etapas as $valor => $nombre): ?>
                                <option value="<?= $valor ?>"><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filtro-activo">Estado</label>
                        <select id="filtro-activo" name="activo" class="form-control">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="filtro-busqueda">Búsqueda General</label>
                        <input type="text" id="filtro-busqueda" name="busqueda" 
                               class="form-control" 
                               placeholder="Nombre, email, teléfono...">
                    </div>
                    
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-block">
                            <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-eraser mr-1"></i>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de clientes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>
                    Lista de Clientes
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-clientes" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Etapa</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargan via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


<!-- Modal para cambiar etapa -->
<div class="modal fade" id="modal-cambiar-etapa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cambiar Etapa del Cliente</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-cambiar-etapa">
                    <input type="hidden" id="modal-cliente-id" name="cliente_id">
                    
                    <div class="form-group">
                        <label for="modal-nueva-etapa">Nueva Etapa</label>
                        <select id="modal-nueva-etapa" name="etapa" class="form-control" required>
                            <?php foreach ($etapas as $valor => $nombre): ?>
                                <option value="<?= $valor ?>"><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-guardar-etapa" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // =====================================================================
    // INICIALIZAR DATATABLE
    // =====================================================================
    
    var tablaClientes = $('#tabla-clientes').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url("/admin/clientes/datatable") ?>',
            type: 'POST',
            data: function(d) {
                // Capturar datos de filtros personalizados
                d.etapa = $('#filtro-etapa').val();
                d.activo = $('#filtro-activo').val();
                d.busqueda = $('#filtro-busqueda').val();
            },
            error: function(xhr, error, code) {
                console.error('Error en DataTable:', error);
                toastr.error('Error al cargar los datos de clientes');
            }
        },
        columns: [
            { data: 'id', width: '60px', className: 'text-center' },
            { data: 'nombre_completo' },
            { data: 'email' },
            { data: 'telefono', width: '120px' },
            { 
                data: 'etapa_proceso',
                width: '120px',
                render: function(data, type, row) {
                    var badges = {
                        'interesado': 'badge-info',
                        'calificado': 'badge-warning', 
                        'documentacion': 'badge-primary',
                        'contrato': 'badge-success',
                        'cerrado': 'badge-dark'
                    };
                    var badgeClass = badges[row.etapa_proceso] || 'badge-secondary';
                    return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                }
            },
            { 
                data: 'activo',
                width: '80px',
                className: 'text-center',
                render: function(data, type, row) {
                    if (data == 1) {
                        return '<span class="badge badge-success">Activo</span>';
                    } else {
                        return '<span class="badge badge-danger">Inactivo</span>';
                    }
                }
            },
            { data: 'fecha_registro', width: '140px', className: 'text-center' },
            { 
                data: 'acciones', 
                width: '120px', 
                className: 'text-center',
                orderable: false,
                searchable: false
            }
        ],
        order: [[0, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        drawCallback: function(settings) {
            // Actualizar estadísticas después de cada carga
            actualizarEstadisticas();
            // Reinicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
    
    // =====================================================================
    // FILTROS
    // =====================================================================
    
    // Aplicar filtros cuando cambien
    $('#filtro-etapa, #filtro-activo').on('change', function() {
        tablaClientes.draw();
    });
    
    // Búsqueda con delay para evitar muchas peticiones
    var searchTimer;
    $('#filtro-busqueda').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            tablaClientes.draw();
        }, 500);
    });
    
    // Limpiar filtros
    $('#btn-limpiar-filtros').on('click', function() {
        $('#form-filtros')[0].reset();
        tablaClientes.draw();
    });
    
    // =====================================================================
    // ACCIONES DE LA TABLA
    // =====================================================================
    
    // Cambiar estado activo/inactivo
    $(document).on('click', '.btn-cambiar-estado', function() {
        var clienteId = $(this).data('cliente-id');
        var activo = $(this).data('activo');
        var boton = $(this);
        var accion = activo === 'true' ? 'activar' : 'desactivar';
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas ' + accion + ' este cliente?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ' + accion,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                cambiarEstadoCliente(clienteId, activo, boton);
            }
        });
    });
    
    // Eliminar cliente (soft delete)
    $(document).on('click', '.btn-eliminar-cliente', function() {
        var clienteId = $(this).data('cliente-id');
        var boton = $(this);
        
        Swal.fire({
            title: '¿Eliminar Cliente?',
            text: 'Esta acción realizará un soft delete. El cliente y todos sus datos relacionados serán marcados como eliminados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarCliente(clienteId, boton);
            }
        });
    });
    
    // Cambiar etapa (botón en acciones)
    $(document).on('click', '.btn-cambiar-etapa', function() {
        var clienteId = $(this).data('cliente-id');
        var etapaActual = $(this).data('etapa-actual');
        
        $('#modal-cliente-id').val(clienteId);
        $('#modal-nueva-etapa').val(etapaActual);
        $('#modal-cambiar-etapa').modal('show');
    });
    
    // Guardar nueva etapa
    $('#btn-guardar-etapa').on('click', function() {
        var clienteId = $('#modal-cliente-id').val();
        var nuevaEtapa = $('#modal-nueva-etapa').val();
        
        if (!nuevaEtapa) {
            toastr.error('Selecciona una etapa válida');
            return;
        }
        
        cambiarEtapaCliente(clienteId, nuevaEtapa);
    });
    
    // =====================================================================
    // FUNCIONES AJAX
    // =====================================================================
    
    function cambiarEstadoCliente(clienteId, activo, boton) {
        $.ajax({
            url: '<?= site_url("/admin/clientes/cambiarEstado") ?>',
            type: 'POST',
            data: {
                cliente_id: clienteId,
                activo: activo,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            beforeSend: function() {
                boton.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    tablaClientes.draw(false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error al cambiar el estado del cliente');
            },
            complete: function() {
                boton.prop('disabled', false);
            }
        });
    }
    
    function cambiarEtapaCliente(clienteId, nuevaEtapa) {
        $.ajax({
            url: '<?= site_url("/admin/clientes/cambiarEtapa") ?>',
            type: 'POST',
            data: {
                cliente_id: clienteId,
                etapa: nuevaEtapa,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            beforeSend: function() {
                $('#btn-guardar-etapa').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#modal-cambiar-etapa').modal('hide');
                    tablaClientes.draw(false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error al cambiar la etapa del cliente');
            },
            complete: function() {
                $('#btn-guardar-etapa').prop('disabled', false);
            }
        });
    }
    
    function eliminarCliente(clienteId, boton) {
        $.ajax({
            url: '<?= site_url("/admin/clientes/eliminar/") ?>' + clienteId,
            type: 'POST',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            beforeSend: function() {
                boton.prop('disabled', true);
                boton.html('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    tablaClientes.draw(false);
                    actualizarEstadisticas();
                } else {
                    Swal.fire(
                        'Error',
                        response.message,
                        'error'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Error eliminando cliente:', error);
                Swal.fire(
                    'Error',
                    'Error al eliminar el cliente',
                    'error'
                );
            },
            complete: function() {
                boton.prop('disabled', false);
                boton.html('<i class="fas fa-trash-alt"></i>');
            }
        });
    }
    
    function actualizarEstadisticas() {
        $.ajax({
            url: '<?= site_url("/admin/clientes/estadisticas") ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#total-clientes').text(response.data.total);
                    $('#clientes-activos').text(response.data.activos);
                    $('#clientes-pendientes').text(response.data.pendientes);
                    $('#clientes-inactivos').text(response.data.inactivos);
                }
            },
            error: function(xhr, status, error) {
            }
        });
    }
    
    // =====================================================================
    // TOOLTIPS
    // =====================================================================
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Reinicializar tooltips después de cada redraw de la tabla
    tablaClientes.on('draw.dt', function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
    
});
</script>
<?= $this->endSection() ?>