<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item active">Manzanas</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

            <!-- Estadísticas -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="stat-total"><?= $estadisticas['total'] ?></h3>
                            <p>Total Manzanas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-th-large"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="stat-activas"><?= $estadisticas['activas'] ?></h3>
                            <p>Activas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="stat-coordenadas"><?= $estadisticas['con_coordenadas'] ?></h3>
                            <p>Con Coordenadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="stat-inactivas"><?= $estadisticas['inactivas'] ?></h3>
                            <p>Eliminadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-trash"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros y acciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtros y Acciones</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro-proyecto">Proyecto:</label>
                                <select class="form-control" id="filtro-proyecto">
                                    <option value="">Todos los proyectos</option>
                                    <?php foreach ($proyectos as $proyecto): ?>
                                        <option value="<?= $proyecto->id ?>"><?= $proyecto->nombre ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro-busqueda">Buscar:</label>
                                <input type="text" class="form-control" id="filtro-busqueda" 
                                       placeholder="Nombre, clave o descripción...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="incluir-eliminadas">
                                    <label class="form-check-label" for="incluir-eliminadas">
                                        Incluir eliminadas
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="btn-group d-block">
                                    <button type="button" class="btn btn-primary" onclick="filtrarManzanas()">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                    <a href="<?= base_url('admin/manzanas/create') ?>" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Agregar Manzana
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de manzanas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Manzanas</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-manzanas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Clave</th>
                                    <th>Proyecto</th>
                                    <th>Descripción</th>
                                    <th>Coordenadas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargan vía AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Los datos se actualizan automáticamente. Use los filtros para encontrar manzanas específicas.
                    </small>
                </div>
            </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    initDataTable();
    
    // Cargar datos iniciales
    cargarManzanas();
    
    // Event listeners para filtros
    $('#filtro-proyecto, #filtro-busqueda').on('change keyup', function() {
        cargarManzanas();
    });
    
    $('#incluir-eliminadas').on('change', function() {
        cargarManzanas();
    });
});

// Variables globales
let tablaManzanas;

/**
 * Inicializar DataTable
 */
function initDataTable() {
    tablaManzanas = $('#tabla-manzanas').DataTable({
        processing: true,
        serverSide: false,
        columns: [
            { data: 'id', width: '5%' },
            { data: 'nombre', width: '10%' },
            { data: 'clave', width: '15%' },
            { data: 'proyecto', width: '20%' },
            { data: 'descripcion', width: '25%' },
            { data: 'coordenadas', width: '15%', orderable: false },
            { 
                data: 'activo', 
                width: '5%',
                render: function(data, type, row) {
                    if (data) {
                        return '<span class="badge badge-success">Activa</span>';
                    } else {
                        return '<span class="badge badge-danger">Eliminada</span>';
                    }
                }
            },
            { data: 'acciones', width: '5%', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']], // Ordenar por nombre
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
}

/**
 * Cargar manzanas con filtros
 */
function cargarManzanas() {
    const filtros = {
        proyecto_id: $('#filtro-proyecto').val(),
        busqueda: $('#filtro-busqueda').val(),
        incluir_inactivas: $('#incluir-eliminadas').is(':checked')
    };
    
    $.ajax({
        url: '<?= base_url('admin/manzanas/obtener') ?>',
        method: 'POST',
        data: filtros,
        dataType: 'json',
        beforeSend: function() {
            // Mostrar loading en la tabla
            tablaManzanas.clear().draw();
        },
        success: function(response) {
            if (response.success) {
                tablaManzanas.clear().rows.add(response.data).draw();
                actualizarEstadisticas();
            } else {
                mostrarError('Error al cargar manzanas: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error de conexión al cargar las manzanas');
        }
    });
}

/**
 * Filtrar manzanas (botón filtrar)
 */
function filtrarManzanas() {
    cargarManzanas();
}

/**
 * Limpiar filtros
 */
function limpiarFiltros() {
    $('#filtro-proyecto').val('');
    $('#filtro-busqueda').val('');
    $('#incluir-eliminadas').prop('checked', false);
    cargarManzanas();
}

/**
 * Eliminar manzana
 */
function eliminarManzana(id) {
    Swal.fire({
        title: '¿Eliminar manzana?',
        text: 'Esta acción marcará la manzana como eliminada. Se puede restaurar posteriormente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `<?= base_url('admin/manzanas/delete') ?>/${id}`,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarExito(response.message);
                        cargarManzanas();
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function() {
                    mostrarError('Error de conexión al eliminar la manzana');
                }
            });
        }
    });
}

/**
 * Restaurar manzana
 */
function restaurarManzana(id) {
    Swal.fire({
        title: '¿Restaurar manzana?',
        text: 'La manzana volverá a estar activa y disponible.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `<?= base_url('admin/manzanas/restaurar') ?>/${id}`,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarExito(response.message);
                        cargarManzanas();
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function() {
                    mostrarError('Error de conexión al restaurar la manzana');
                }
            });
        }
    });
}

/**
 * Actualizar estadísticas
 */
function actualizarEstadisticas() {
    $.ajax({
        url: '<?= base_url('admin/manzanas/estadisticas') ?>',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#stat-total').text(stats.total);
                $('#stat-activas').text(stats.activas);
                $('#stat-coordenadas').text(stats.con_coordenadas);
                $('#stat-inactivas').text(stats.inactivas);
            }
        },
        error: function() {
            // Error al actualizar estadísticas
        }
    });
}

/**
 * Funciones de utilidad para mostrar mensajes
 */
function mostrarExito(mensaje) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    
    Toast.fire({
        icon: 'success',
        title: mensaje
    });
}

function mostrarError(mensaje) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true
    });
    
    Toast.fire({
        icon: 'error',
        title: mensaje
    });
}
</script>
<?= $this->endSection() ?>