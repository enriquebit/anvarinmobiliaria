<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="#">Catálogos</a></li>
<li class="breadcrumb-item active">Fuentes de Información</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bullhorn"></i> Gestión de Fuentes de Información
                    </h3>
                    <div class="card-tools">
                        <?php if (isAdmin()): ?>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-fuente">
                            <i class="fas fa-plus"></i> Nueva Fuente
                        </button>
                        <a href="<?= base_url('admin/catalogos/fuentes-informacion/estadisticas') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar"></i> Estadísticas
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información:</strong> Las fuentes de información se utilizan en el formulario de registro de clientes para conocer cómo se enteraron de nuestros servicios.
                    </div>
                    
                    <div class="table-responsive">
                        <table id="tabla-fuentes" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Valor</th>
                                    <th>Clientes Asociados</th>
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

<!-- Modal para crear/editar fuente -->
<div class="modal fade" id="modal-fuente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="titulo-modal">Nueva Fuente de Información</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="form-fuente">
                <div class="modal-body">
                    <input type="hidden" id="fuente-id" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nombre">Nombre de la Fuente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required 
                                       placeholder="Ej: Facebook, Referido, Señalítica...">
                                <small class="form-text text-muted">
                                    Nombre descriptivo que aparecerá en los formularios
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
                        <label for="valor">Valor/Clave <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="valor" name="valor" required 
                               placeholder="Ej: facebook, referido, senalitica...">
                        <small class="form-text text-muted">
                            Valor único que se guarda en la base de datos (se convierte automáticamente a minúsculas y guiones bajos)
                        </small>
                    </div>

                    <!-- Ejemplos de fuentes comunes -->
                    <div class="alert alert-secondary">
                        <h6><i class="fas fa-lightbulb"></i> Fuentes Comunes de Información:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled small mb-0">
                                    <li><i class="fab fa-facebook text-primary mr-2"></i> <strong>Facebook:</strong> Redes sociales</li>
                                    <li><i class="fab fa-instagram text-danger mr-2"></i> <strong>Instagram:</strong> Marketing digital</li>
                                    <li><i class="fas fa-users text-success mr-2"></i> <strong>Referido:</strong> Recomendación personal</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled small mb-0">
                                    <li><i class="fas fa-sign text-warning mr-2"></i> <strong>Señalítica:</strong> Anuncios físicos</li>
                                    <li><i class="fas fa-building text-info mr-2"></i> <strong>Agencia:</strong> Agencia inmobiliaria</li>
                                    <li><i class="fas fa-search text-secondary mr-2"></i> <strong>Navegador:</strong> Búsqueda en internet</li>
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
        tabla = $('#tabla-fuentes').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= base_url('admin/catalogos/fuentes-informacion/obtener-fuentes') ?>',
                type: 'POST',
                dataSrc: function(json) {
                    if (json.success) {
                        return json.data;
                    } else {
                        toastr.error(json.message || 'Error al cargar fuentes');
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
                    data: 'valor',
                    render: function(data, type, row) {
                        return '<code>' + data + '</code>';
                    }
                },
                { 
                    data: 'clientes_count',
                    width: '140px',
                    className: 'text-center',
                    render: function(data, type, row) {
                        const total = data || 0;
                        const color = total > 0 ? 'success' : 'secondary';
                        return '<span class="badge badge-' + color + '">' + total + ' clientes</span>';
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

    // Auto-generar valor desde nombre
    $('#nombre').on('input', function() {
        if (!$('#fuente-id').val()) { // Solo para registros nuevos
            const nombre = $(this).val();
            const valor = nombre.toLowerCase()
                              .replace(/ñ/g, 'n')
                              .replace(/[áàäâ]/g, 'a')
                              .replace(/[éèëê]/g, 'e')
                              .replace(/[íìïî]/g, 'i')
                              .replace(/[óòöô]/g, 'o')
                              .replace(/[úùüû]/g, 'u')
                              .replace(/[^a-z0-9]/g, '_')
                              .replace(/_+/g, '_')
                              .replace(/^_|_$/g, '');
            $('#valor').val(valor);
        }
    });

    // Abrir modal para nueva fuente
    $('#modal-fuente').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btn-editar')) {
            $('#form-fuente')[0].reset();
            $('#fuente-id').val('');
            $('#titulo-modal').text('Nueva Fuente de Información');
            $('#activo').val('1');
        }
    });

    // Editar fuente - desde los botones generados por el controlador
    $(document).on('click', '.btn-editar', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        const id = href.split('/').pop();
        
        // Obtener datos de la fuente desde la tabla
        const fila = tabla.row($(this).closest('tr')).data();
        
        $('#fuente-id').val(fila.id);
        $('#nombre').val(fila.nombre);
        $('#valor').val(fila.valor);
        $('#activo').val(fila.activo.includes('Activo') ? '1' : '0');
        $('#titulo-modal').text('Editar Fuente: ' + fila.nombre);
        $('#modal-fuente').modal('show');
    });

    // Guardar fuente
    $('#form-fuente').submit(function(e) {
        e.preventDefault();
        
        const id = $('#fuente-id').val();
        const url = id ? 
            '<?= base_url('admin/catalogos/fuentes-informacion/update') ?>/' + id : 
            '<?= base_url('admin/catalogos/fuentes-informacion/store') ?>';
        
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
                    $('#modal-fuente').modal('hide');
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
        const texto = isActive ? 'Esta fuente se marcará como inactiva' : 'Esta fuente se marcará como activa';
        
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
        $.post('<?= base_url('admin/catalogos/fuentes-informacion/cambiar-estado') ?>/' + id)
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