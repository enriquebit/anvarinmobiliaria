<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Gestión de Lotes<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Lotes<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Lotes</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Tarjetas de estadísticas -->
<div class="row mb-3">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="total-disponibles">0</h3>
                <p>Lotes Disponibles</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="total-apartados">0</h3>
                <p>Lotes Apartados</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 id="total-vendidos">0</h3>
                <p>Lotes Vendidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-home"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="valor-inventario">$0</h3>
                <p>Valor Inventario</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Lista de Lotes
                </h3>
                <div class="card-tools">
                    <?php if (isAdmin()): ?>
                    <a href="<?= site_url('/admin/lotes/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>
                        Nuevo Lote
                    </a>
                    <?php endif; ?>
                    <?php if (isSuperAdmin()): ?>
                    <button type="button" class="btn btn-warning btn-sm" onclick="regenerarClaves()" title="Regenerar claves con nueva nomenclatura">
                        <i class="fas fa-sync mr-1"></i>
                        Regenerar Claves
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control form-control-sm" id="filtroEmpresa">
                            <option value="">Todas las empresas</option>
                            <!-- Las opciones se cargarán vía AJAX -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control form-control-sm" id="filtroProyecto" disabled>
                            <option value="">Todos los proyectos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control form-control-sm" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="Disponible">Disponible</option>
                            <option value="Apartado">Apartado</option>
                            <option value="Vendido">Vendido</option>
                            <option value="Suspendido">Suspendido</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control form-control-sm" id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                            <!-- Las opciones se cargarán vía AJAX -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-secondary btn-sm" id="limpiarFiltros">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Tabla de lotes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="lotesTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>No.</th>
                                <th>Clave</th>
                                <th>Emp.</th>
                                <th>Proy.</th>
                                <th>Tipo</th>
                                <th>Div.</th>
                                <th>Cat.</th>
                                <th>Fte.</th>
                                <th>Fdo.</th>
                                <th>L.I</th>
                                <th>L.D</th>
                                <th>m²</th>
                                <th>Const.</th>
                                <th>$/m²</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acc.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables cargará el contenido aquí -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cambiar Estado del Lote</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCambiarEstado">
                    <input type="hidden" id="loteIdEstado">
                    <div class="form-group">
                        <label>Nuevo Estado:</label>
                        <select class="form-control" id="nuevoEstado">
                            <option value="1">Disponible</option>
                            <option value="2">Apartado</option>
                            <option value="3">Vendido</option>
                            <option value="4">Suspendido</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Motivo (opcional):</label>
                        <textarea class="form-control" id="motivoCambio" rows="3" placeholder="Describe el motivo del cambio de estado..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEstado">Cambiar Estado</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* Estilos compactos para tabla de lotes */
#lotesTable {
    font-size: 0.85rem;
}

#lotesTable th,
#lotesTable td {
    padding: 0.4rem 0.25rem;
    vertical-align: middle;
    white-space: nowrap;
}

#lotesTable th {
    font-size: 0.6rem;
    text-align: center;
}

#lotesTable .btn {
    font-size: 0.7rem;
    padding: 0.25rem 0.4rem;
    margin: 1px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Función global de formateo de moneda
<?= formatPrecioJs() ?>

$(document).ready(function() {
    let tabla;

    // Inicializar DataTable
    function inicializarTabla() {
        tabla = $('#lotesTable').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            searching: false,
            ajax: {
                url: '<?= site_url('admin/lotes/obtener-lotes') ?>',
                type: 'POST',
                data: function(d) {
                    d.empresas_id = $('#filtroEmpresa').val();
                    d.proyectos_id = $('#filtroProyecto').val();
                    d.estado_codigo = $('#filtroEstado').val();
                    d.categorias_lotes_id = $('#filtroCategoria').val();
                },
                dataSrc: function(json) {
                    if (json.success) {
                        actualizarEstadisticas(json.data);
                        return json.data;
                    } else {
                        toastr.error(json.message || 'Error al cargar los datos');
                        return [];
                    }
                }
            },
            columns: [
                { data: 'numero', width: "40px" },
                { data: 'clave', width: "90px" },
                { data: 'empresa', width: "60px" },
                { data: 'proyecto', width: "70px" },
                { data: 'tipo', width: "50px" },
                { data: 'division', width: "40px" },
                { data: 'categoria', width: "50px" },
                { data: 'frente', width: "45px", className: "text-right" },
                { data: 'fondo', width: "45px", className: "text-right" },
                { data: 'lateral_izq', width: "40px", className: "text-right" },
                { data: 'lateral_der', width: "40px", className: "text-right" },
                { 
                    data: 'area', 
                    width: "55px",
                    className: "text-right",
                    render: function(data) {
                        return parseFloat(data).toFixed(0);
                    }
                },
                { data: 'construccion', width: "55px", className: "text-right" },
                { 
                    data: 'precio_m2', 
                    width: "70px",
                    className: "text-right"
                },
                { 
                    data: 'precio_total', 
                    width: "85px",
                    className: "text-right font-weight-bold"
                },
                { 
                    data: 'estado', 
                    width: "70px",
                    className: "text-center"
                },
                { 
                    data: 'acciones', 
                    width: "80px",
                    orderable: false, 
                    searchable: false,
                    className: "text-center"
                }
            ],
            language: {
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "No hay datos disponibles en esta tabla",
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
            scrollX: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm'
                }
            ],
            order: [[0, 'asc']]
        });
    }

    // Actualizar estadísticas
    function actualizarEstadisticas(data) {
        let disponibles = 0, apartados = 0, vendidos = 0, valorTotal = 0;
        
        data.forEach(function(lote) {
            const estadoTexto = lote.estado.replace(/<[^>]*>/g, '').trim();
            
            if (estadoTexto.includes('Disponible')) disponibles++;
            else if (estadoTexto.includes('Apartado')) apartados++;
            else if (estadoTexto.includes('Vendido')) vendidos++;
            
            let precio = parseFloat(lote.precio_total.replace(/[$,]/g, ''));
            if (!isNaN(precio)) valorTotal += precio;
        });

        $('#total-disponibles').text(disponibles);
        $('#total-apartados').text(apartados);
        $('#total-vendidos').text(vendidos);
        $('#valor-inventario').text('$' + valorTotal.toLocaleString());
    }

    // Inicializar tabla
    inicializarTabla();
    
    // Cargar opciones de filtros
    cargarFiltros();

    // Filtros
    $('#filtroEmpresa').change(function() {
        const empresaId = $(this).val();
        $('#filtroProyecto').prop('disabled', !empresaId).val('');
        
        if (empresaId) {
            cargarProyectos(empresaId);
        }
        tabla.ajax.reload();
    });

    $('#filtroProyecto, #filtroEstado, #filtroCategoria').change(function() {
        tabla.ajax.reload();
    });

    $('#limpiarFiltros').click(function() {
        $('#filtroEmpresa, #filtroProyecto, #filtroEstado, #filtroCategoria').val('');
        $('#filtroProyecto').prop('disabled', true);
        tabla.ajax.reload();
    });

    // Cargar filtros iniciales
    function cargarFiltros() {
        // Cargar empresas
        $.get('<?= site_url('admin/lotes/obtener-empresas') ?>')
            .done(function(response) {
                if (response.success) {
                    $('#filtroEmpresa').empty().append('<option value="">Todas las empresas</option>');
                    $.each(response.empresas, function(index, empresa) {
                        $('#filtroEmpresa').append(new Option(empresa.nombre, empresa.id));
                    });
                }
            });
        
        // Cargar categorías
        $.get('<?= site_url('admin/lotes/obtener-categorias') ?>')
            .done(function(response) {
                if (response.success) {
                    $('#filtroCategoria').empty().append('<option value="">Todas las categorías</option>');
                    $.each(response.categorias, function(index, categoria) {
                        $('#filtroCategoria').append(new Option(categoria.nombre, categoria.nombre));
                    });
                }
            });
    }

    // Cargar proyectos por empresa
    function cargarProyectos(empresaId) {
        $.get('<?= site_url('admin/lotes/obtener-proyectos-por-empresa') ?>/' + empresaId)
            .done(function(response) {
                if (response.success) {
                    $('#filtroProyecto').empty().append('<option value="">Todos los proyectos</option>');
                    $.each(response.proyectos, function(id, nombre) {
                        $('#filtroProyecto').append(new Option(nombre, id));
                    });
                    $('#filtroProyecto').prop('disabled', false);
                }
            });
    }

    // Cambiar estado de lote
    $(document).on('click', '.btn-cambiar-estado', function() {
        const id = $(this).data('id');
        const estadoActual = $(this).data('estado');
        
        $('#loteIdEstado').val(id);
        $('#nuevoEstado').val(estadoActual);
        $('#modalCambiarEstado').modal('show');
    });

    $('#btnConfirmarEstado').click(function() {
        const id = $('#loteIdEstado').val();
        const nuevoEstado = $('#nuevoEstado').val();
        const motivo = $('#motivoCambio').val();
        
        $.post('<?= site_url('admin/lotes/cambiar-estado-ajax') ?>', {
            id: id,
            estado: nuevoEstado,
            motivo: motivo
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                tabla.ajax.reload();
                $('#modalCambiarEstado').modal('hide');
            } else {
                toastr.error(response.message);
            }
        })
        .fail(function() {
            toastr.error('Error de conexión');
        });
    });

    // Eliminar lote
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: '⚠️ ¿Confirmar eliminación del lote?',
            html: `
                <div class="text-left">
                    <p><strong>Esta acción eliminará permanentemente el lote.</strong></p>
                    <br>
                    <p>📋 <strong>Verificaciones automáticas:</strong></p>
                    <ul style="text-align: left; margin-left: 20px;">
                        <li>• Ventas activas asociadas</li>
                        <li>• Apartados vigentes</li>
                        <li>• Amenidades configuradas</li>
                        <li>• Bloqueos administrativos</li>
                    </ul>
                    <br>
                    <p class="text-warning">⚠️ <strong>Si el lote tiene transacciones, no se podrá eliminar por integridad de datos.</strong></p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, proceder con eliminación',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            width: '500px'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url('admin/lotes/destroy') ?>/' + id)
                    .done(function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            tabla.ajax.reload();
                        } else {
                            // Manejar mensajes de restricciones de integridad con más detalle
                            if (response.tipo === 'restricciones_integridad' || response.tipo === 'foreign_key_error') {
                                // Mostrar alerta detallada para errores de integridad
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No se puede eliminar el lote',
                                    html: response.message.replace(/\n/g, '<br>'),
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#d33',
                                    customClass: {
                                        content: 'text-left'
                                    },
                                    width: '600px'
                                });
                            } else {
                                // Mensaje de error estándar
                                toastr.error(response.message);
                            }
                        }
                    })
                    .fail(function() {
                        toastr.error('Error de conexión al servidor');
                    });
            }
        });
    });
});

// Función para regenerar claves de lotes
function regenerarClaves() {
    Swal.fire({
        title: '¿Regenerar todas las claves?',
        html: `
            <p>Esta acción regenerará las claves de TODOS los lotes existentes con la nueva nomenclatura:</p>
            <code>PROYECTO-DIVISION-MANZANA-NUMERO</code>
            <br><br>
            <p class="text-warning"><strong>¿Estás seguro de continuar?</strong></p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, regenerar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Regenerando claves...',
                text: 'Por favor espera mientras se procesan los lotes',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.post('<?= site_url('admin/lotes/regenerar-claves') ?>')
                .done(function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Regeneración Completada',
                            html: `
                                <p><strong>Total procesados:</strong> ${response.data.total}</p>
                                <p><strong>Actualizados:</strong> ${response.data.actualizados}</p>
                                <p><strong>Errores:</strong> ${response.data.errores}</p>
                            `,
                            icon: 'success'
                        }).then(() => {
                                    // Configuración global de DataTables aplicada desde datatables-config.js

                            $('#lotesTable').DataTable().ajax.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Error de conexión al regenerar claves', 'error');
                });
        }
    });
}
</script>
<?= $this->endSection() ?>