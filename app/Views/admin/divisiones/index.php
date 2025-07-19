<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item active">Divisiones</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <div class="col-12">
                    <!-- Filtros -->
                    <div class="card collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-filter"></i> Filtros de Búsqueda
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <form id="form-filtros">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Empresa</label>
                                            <?= form_dropdown('empresa_id', $empresas, '', [
                                                'class' => 'form-control',
                                                'id' => 'filtro-empresa'
                                            ]) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Proyecto</label>
                                            <?= form_dropdown('proyecto_id', $proyectos, '', [
                                                'class' => 'form-control',
                                                'id' => 'filtro-proyecto',
                                                'disabled' => true
                                            ]) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Buscar</label>
                                            <input type="text" class="form-control" id="filtro-busqueda" placeholder="Nombre, clave, descripción...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Estado</label>
                                            <select class="form-control" id="filtro-estado">
                                                <option value="activas">Solo Activas</option>
                                                <option value="inactivas">Solo Inactivas</option>
                                                <option value="todas">Todas</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary" id="btn-aplicar-filtros">
                                            <i class="fas fa-search"></i> Aplicar Filtros
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="btn-limpiar-filtros">
                                            <i class="fas fa-broom"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla principal -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group"></i> Lista de Divisiones
                            </h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/divisiones/create') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nueva División
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tabla-divisiones" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Clave</th>
                                        <th>Empresa</th>
                                        <th>Proyecto</th>
                                        <th>Orden</th>
                                        <th>Color</th>
                                        <th>Estado</th>
                                        <th width="120">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Datos cargados via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

<!-- Modal para confirmación de eliminación -->
<div class="modal fade" id="modal-eliminar">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmar Eliminación</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar esta división?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmación de restauración -->
<div class="modal fade" id="modal-restaurar">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmar Restauración</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea restaurar esta división?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-restaurar">Restaurar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let tabla;
    let divisionIdAccion = null;

    function inicializarTabla() {
        tabla = $('#tabla-divisiones').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= base_url('admin/divisiones/obtener-divisiones') ?>',
                type: 'POST',
                data: function(d) {
                    d.empresa_id = $('#filtro-empresa').val();
                    d.proyecto_id = $('#filtro-proyecto').val();
                    d.busqueda = $('#filtro-busqueda').val();
                    d.incluir_inactivas = $('#filtro-estado').val() === 'todas' || $('#filtro-estado').val() === 'inactivas';
                },
                dataSrc: function(json) {
                    if (json.success) {
                        return json.data;
                    } else {
                        toastr.error(json.message || 'Error al cargar divisiones');
                        return [];
                    }
                }
            },
            columns: [
                { data: 'id' },
                { data: 'nombre' },
                { data: 'clave' },
                { data: 'empresa' },
                { data: 'proyecto' },
                { data: 'orden' },
                { data: 'color' },
                { data: 'activo' },
                { data: 'acciones', orderable: false, searchable: false }
            ],
            order: [[4, 'asc'], [5, 'asc']], // Por proyecto y orden
            dom: 'Bfrtip',
            buttons: [
                'excel', 'pdf', 'print'
            ]
        });
    }

    // Inicializar tabla
    inicializarTabla();

    // Filtros en cascada
    $('#filtro-empresa').change(function() {
        const empresaId = $(this).val();
        $('#filtro-proyecto').prop('disabled', !empresaId).html('<option value="">Todos los proyectos...</option>');
        
        if (empresaId) {
            cargarProyectos(empresaId);
        }
    });

    function cargarProyectos(empresaId) {
        $.get('<?= base_url('admin/divisiones/obtener-proyectos-por-empresa') ?>/' + empresaId)
            .done(function(response) {
                if (response.success) {
                    $('#filtro-proyecto').append('<option value="">Todos los proyectos...</option>');
                    $.each(response.proyectos, function(id, nombre) {
                        $('#filtro-proyecto').append(new Option(nombre, id));
                    });
                    $('#filtro-proyecto').prop('disabled', false);
                }
            });
    }

    // Aplicar filtros
    $('#btn-aplicar-filtros').click(function() {
        tabla.ajax.reload();
    });

    // Limpiar filtros
    $('#btn-limpiar-filtros').click(function() {
        $('#form-filtros')[0].reset();
        $('#filtro-proyecto').prop('disabled', true).html('<option value="">Todos los proyectos...</option>');
        tabla.ajax.reload();
    });

    // Eliminar división
    $(document).on('click', '.btn-eliminar', function() {
        divisionIdAccion = $(this).data('id');
        $('#modal-eliminar').modal('show');
    });

    $('#btn-confirmar-eliminar').click(function() {
        if (!divisionIdAccion) return;

        $.post('<?= base_url('admin/divisiones/delete') ?>/' + divisionIdAccion)
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
            })
            .always(function() {
                $('#modal-eliminar').modal('hide');
                divisionIdAccion = null;
            });
    });

    // Restaurar división
    $(document).on('click', '.btn-restaurar', function() {
        divisionIdAccion = $(this).data('id');
        $('#modal-restaurar').modal('show');
    });

    $('#btn-confirmar-restaurar').click(function() {
        if (!divisionIdAccion) return;

        $.post('<?= base_url('admin/divisiones/restaurar') ?>/' + divisionIdAccion)
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
            })
            .always(function() {
                $('#modal-restaurar').modal('hide');
                divisionIdAccion = null;
            });
    });
});
</script>
<?= $this->endSection() ?>