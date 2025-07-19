<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="#">Catálogos</a></li>
<li class="breadcrumb-item active">Tipos de Lotes</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i> Gestión de Tipos de Lotes
                    </h3>
                    <div class="card-tools">
                        <?php if (isAdmin()): ?>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tipo">
                            <i class="fas fa-plus"></i> Nuevo Tipo
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-tipos" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Lotes Asociados</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

<!-- Modal para crear/editar tipo -->
<div class="modal fade" id="modal-tipo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="titulo-modal">Nuevo Tipo de Lote</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="form-tipo">
                <div class="modal-body">
                    <input type="hidden" id="tipo-id" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required 
                                       placeholder="Ej: Lote, Casa, Departamento, Local Comercial...">
                                <small class="form-text text-muted">
                                    Tipo de lote que se oferta (Lote, Casa, Departamento, etc.)
                                </small>
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
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                  placeholder="Descripción detallada del tipo de lote..."></textarea>
                        <small class="form-text text-muted">
                            Describe las características principales de este tipo de lote
                        </small>
                    </div>

                    <!-- Tipos comunes para referencia -->
                    <div class="form-group">
                        <label>Tipos Comunes (Referencia)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled text-muted small">
                                    <li><i class="fas fa-square mr-2"></i> <strong>Lote:</strong> Terreno sin construcción</li>
                                    <li><i class="fas fa-home mr-2"></i> <strong>Casa:</strong> Lote con casa construida</li>
                                    <li><i class="fas fa-building mr-2"></i> <strong>Departamento:</strong> Unidad en edificio</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled text-muted small">
                                    <li><i class="fas fa-store mr-2"></i> <strong>Local Comercial:</strong> Espacio para negocio</li>
                                    <li><i class="fas fa-home mr-2"></i> <strong>Townhouse:</strong> Casa adosada</li>
                                    <li><i class="fas fa-industry mr-2"></i> <strong>Industrial:</strong> Uso industrial</li>
                                </ul>
                            </div>
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
        tabla = $('#tabla-tipos').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= base_url('admin/catalogos/tipos-lotes/obtener-tipos') ?>',
                type: 'POST',
                dataSrc: function(json) {
                    if (json.success) {
                        return json.data;
                    } else {
                        toastr.error(json.message || 'Error al cargar tipos');
                        return [];
                    }
                }
            },
            columns: [
                { data: 'id', width: '50px' },
                { 
                    data: 'nombre',
                    render: function(data, type, row) {
                        return '<strong>' + data + '</strong>';
                    }
                },
                { 
                    data: 'descripcion',
                    render: function(data, type, row) {
                        if (!data || data === 'Sin descripción') {
                            return '<span class="text-muted">Sin descripción</span>';
                        }
                        return data.length > 80 ? data.substring(0, 80) + '...' : data;
                    }
                },
                { 
                    data: 'lotes_count',
                    width: '120px',
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
                    className: 'text-center'
                },
                { 
                    data: 'created_at',
                    width: '100px',
                    className: 'text-center'
                },
                { 
                    data: 'acciones',
                    width: '120px',
                    orderable: false,
                    className: 'text-center'
                }
            ],
            language: {
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            order: [[1, 'asc']], // Ordenar por nombre
            });
    }

    // Inicializar tabla
    inicializarTabla();

    // Abrir modal para nuevo tipo
    $('#modal-tipo').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btn-editar')) {
            $('#form-tipo')[0].reset();
            $('#tipo-id').val('');
            $('#titulo-modal').text('Nuevo Tipo de Lote');
            $('#activo').val('1');
        }
    });

    // Editar tipo - desde los botones generados por el controlador
    $(document).on('click', '.btn-editar', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        const id = href.split('/').pop();
        
        // Obtener datos del tipo desde la tabla
        const fila = tabla.row($(this).closest('tr')).data();
        
        $('#tipo-id').val(fila.id);
        $('#nombre').val(fila.nombre);
        $('#descripcion').val(fila.descripcion === 'Sin descripción' ? '' : fila.descripcion);
        $('#activo').val(fila.activo.includes('Activo') ? '1' : '0');
        $('#titulo-modal').text('Editar Tipo: ' + fila.nombre);
        $('#modal-tipo').modal('show');
    });

    // Guardar tipo
    $('#form-tipo').submit(function(e) {
        e.preventDefault();
        
        const id = $('#tipo-id').val();
        const url = id ? 
            '<?= base_url('admin/catalogos/tipos-lotes/update') ?>/' + id : 
            '<?= base_url('admin/catalogos/tipos-lotes/store') ?>';
        
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
                    $('#modal-tipo').modal('hide');
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

    // Cambiar estado - desde los botones generados por el controlador
    $(document).on('click', '.btn-cambiar-estado', function() {
        const id = $(this).data('id');
        const isActive = $(this).hasClass('btn-danger'); // Si es rojo, está activo y se va a desactivar
        const accion = isActive ? 'desactivar' : 'activar';
        const titulo = isActive ? '¿Confirmar desactivación?' : '¿Confirmar activación?';
        const texto = isActive ? 'Este tipo se marcará como inactivo' : 'Este tipo se marcará como activo';
        
        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ' + accion,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                cambiarEstado(id);
            }
        });
    });

    function cambiarEstado(id) {
        $.post('<?= base_url('admin/catalogos/tipos-lotes/cambiar-estado') ?>/' + id)
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