<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Gestión de Comisiones<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Comisiones<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Comisiones</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Tarjetas de estadísticas -->
<div class="row mb-3">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="total-comisiones">0</h3>
                <p>Total Comisiones</p>
            </div>
            <div class="icon">
                <i class="fas fa-percentage"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="comisiones-pendientes">0</h3>
                <p>Pendientes de Pago</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="comisiones-pagadas">0</h3>
                <p>Comisiones Pagadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 id="monto-total">$0</h3>
                <p>Monto Total</p>
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
                    Lista de Comisiones
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" onclick="generarComisionesFaltantes()">
                        <i class="fas fa-plus mr-1"></i>
                        Generar Comisiones Faltantes
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="generarReporte()">
                        <i class="fas fa-file-excel mr-1"></i>
                        Exportar Reporte
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control form-control-sm" id="filtroEstatus">
                            <option value="">Todos los estatus</option>
                            <option value="devengada">Devengada</option>
                            <option value="pendiente_aceptacion">Pendiente Aceptación</option>
                            <option value="aceptada">Aceptada</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="realizado">Realizado</option>
                            <option value="parcial">Parcial</option>
                            <option value="pagada">Pagada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control form-control-sm" id="filtroVendedor">
                            <option value="">Todos los vendedores</option>
                            <?php if (!empty($vendedores)): ?>
                                <?php foreach ($vendedores as $vendedor): ?>
                                    <option value="<?= $vendedor->id ?>" data-vendedor-id="<?= $vendedor->id ?>">
                                        <?= esc($vendedor->username) ?> (<?= esc($vendedor->email) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="fechaInicio" placeholder="Fecha inicio">
                            <div class="input-group-append">
                                <span class="input-group-text">hasta</span>
                            </div>
                            <input type="date" class="form-control" id="fechaFin" placeholder="Fecha fin">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="btn-group-vertical btn-group-sm" style="width: 100%;">
                            <button type="button" class="btn btn-secondary btn-sm" id="limpiarFiltros">
                                <i class="fas fa-times mr-1"></i>
                                Limpiar
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="verPorVendedor" disabled>
                                <i class="fas fa-user mr-1"></i>
                                Ver Vendedor
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de comisiones -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="comisionesTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Folio</th>
                                <th>Vendedor</th>
                                <th>Cliente</th>
                                <th>Lote</th>
                                <th>Base Cálculo</th>
                                <th>% Comisión</th>
                                <th>Monto Total</th>
                                <th>Monto Pagado</th>
                                <th>Saldo Pendiente</th>
                                <th>Estatus</th>
                                <th>Fecha Generación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables cargará los datos aquí -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Cargar estadísticas al inicio
    cargarEstadisticas();
    
    // Inicializar DataTable
    let tabla = $('#comisionesTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= site_url('admin/comisiones/obtener-comisiones') ?>',
            type: 'POST',
            data: function(d) {
                d.estatus = $('#filtroEstatus').val();
                d.vendedor_id = $('#filtroVendedor').val();
                d.fecha_inicio = $('#fechaInicio').val();
                d.fecha_fin = $('#fechaFin').val();
            }
        },
        columns: [
            { 
                data: null,
                render: function(data, type, row) {
                    return row.folio_venta || row.folio_apartado || 'N/A';
                }
            },
            { data: 'vendedor' },
            { data: 'cliente' },
            { data: 'lote' },
            { data: 'base_calculo' },
            { data: 'porcentaje_aplicado' },
            { data: 'monto_comision_total' },
            { data: 'monto_pagado_total' },
            { data: 'saldo_pendiente' },
            { 
                data: 'estatus',
                orderable: false
            },
            { data: 'fecha_generacion' },
            { 
                data: 'acciones',
                orderable: false,
                searchable: false
            }
        ],
        responsive: true,
        language: {
            url: '<?= base_url('assets/plugins/datatables/i18n/Spanish.json') ?>'
        },
        order: [[10, 'desc']] // Ordenar por fecha de generación
    });

    // Eventos de filtros
    $('#filtroEstatus, #filtroVendedor, #fechaInicio, #fechaFin').change(function() {
        tabla.ajax.reload();
    });

    // Limpiar filtros
    $('#limpiarFiltros').click(function() {
        $('#filtroEstatus, #filtroVendedor').val('');
        $('#fechaInicio, #fechaFin').val('');
        $('#verPorVendedor').prop('disabled', true);
        tabla.ajax.reload();
        toastr.info('Filtros limpiados');
    });

    // Habilitar/deshabilitar botón "Ver Vendedor"
    $('#filtroVendedor').change(function() {
        const vendedorId = $(this).val();
        $('#verPorVendedor').prop('disabled', !vendedorId);
    });

    // Ver comisiones por vendedor
    $('#verPorVendedor').click(function() {
        const vendedorId = $('#filtroVendedor').val();
        if (vendedorId) {
            window.location.href = '<?= site_url('admin/comisiones/por-vendedor/') ?>' + vendedorId;
        }
    });

    // Aceptar comisión
    $(document).on('click', '.btn-aceptar', function() {
        const comisionId = $(this).data('id');
        
        Swal.fire({
            title: '¿Aceptar comisión?',
            text: 'Esta acción cambiará el estado a "Aceptada"',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aceptar',
            cancelButtonText: 'Cancelar',
            input: 'textarea',
            inputPlaceholder: 'Observaciones (opcional)'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url('admin/comisiones/aceptar-comision') ?>', {
                    comision_id: comisionId,
                    observaciones: result.value
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
    });

    // Procesar comisión
    $(document).on('click', '.btn-procesar', function() {
        const comisionId = $(this).data('id');
        
        Swal.fire({
            title: 'Procesar Comisión',
            html: `
                <div class="form-group">
                    <label>Fecha estimada de pago:</label>
                    <input type="date" id="fechaEstimada" class="form-control" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Email del vendedor:</label>
                    <input type="email" id="emailVendedor" class="form-control" placeholder="Confirmar email">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Procesar y enviar email',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const fecha = document.getElementById('fechaEstimada').value;
                const email = document.getElementById('emailVendedor').value;
                
                if (!fecha) {
                    Swal.showValidationMessage('La fecha es requerida');
                    return false;
                }
                if (!email) {
                    Swal.showValidationMessage('El email es requerido');
                    return false;
                }
                
                return { fecha, email };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url('admin/comisiones/procesar-comision') ?>', {
                    comision_id: comisionId,
                    fecha_estimada_pago: result.value.fecha,
                    email_vendedor: result.value.email
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
    });

    // Pagar comisión
    $(document).on('click', '.btn-pagar', function() {
        const comisionId = $(this).data('id');
        
        Swal.fire({
            title: 'Registrar Pago de Comisión',
            html: `
                <div class="form-group">
                    <label>Monto a pagar:</label>
                    <input type="number" id="montoPago" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Pago',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const monto = document.getElementById('montoPago').value;
                
                if (!monto || monto <= 0) {
                    Swal.showValidationMessage('El monto debe ser mayor a 0');
                    return false;
                }
                
                return monto;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url('admin/comisiones/pagar-comision') ?>', {
                    comision_id: comisionId,
                    monto_pago: result.value
                })
                .done(function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        tabla.ajax.reload();
                        
                        // Mostrar opción de ver recibo
                        if (response.url_recibo) {
                            Swal.fire({
                                title: 'Pago Registrado',
                                text: '¿Desea ver el recibo de pago?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Ver Recibo',
                                cancelButtonText: 'Cerrar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open(response.url_recibo, '_blank');
                                }
                            });
                        }
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail(function() {
                    toastr.error('Error de conexión');
                });
            }
        });
    });

    // Ver detalle de comisión
    $(document).on('click', '.btn-detalle', function() {
        const comisionId = $(this).data('id');
        // TODO: Implementar modal de detalle
        toastr.info('Modal de detalle en desarrollo');
    });

    // Generar comisiones faltantes
    window.generarComisionesFaltantes = function() {
        Swal.fire({
            title: '¿Generar comisiones faltantes?',
            text: 'Esto creará comisiones para ventas que no las tengan',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url('admin/comisiones/generar-comisiones-faltantes') ?>')
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
    };

    // Generar reporte
    window.generarReporte = function() {
        const filtros = {
            estatus: $('#filtroEstatus').val(),
            vendedor_id: $('#filtroVendedor').val(),
            fecha_inicio: $('#fechaInicio').val(),
            fecha_fin: $('#fechaFin').val()
        };
        
        const params = new URLSearchParams(filtros).toString();
        window.open('<?= site_url('admin/comisiones/generar-reporte') ?>?' + params, '_blank');
    };

    // Función para cargar estadísticas
    function cargarEstadisticas() {
        $.post('<?= site_url('admin/comisiones/obtener-estadisticas') ?>')
        .done(function(response) {
            if (response.success) {
                const data = response.data;
                $('#total-comisiones').text(data.total_comisiones);
                $('#comisiones-pendientes').text(data.comisiones_pendientes);
                $('#comisiones-pagadas').text(data.comisiones_pagadas);
                $('#monto-total').text(data.monto_total);
            }
        })
        .fail(function() {
            console.error('Error al cargar estadísticas');
        });
    }

    // Recargar estadísticas cuando se actualice la tabla
    tabla.on('draw', function() {
        cargarEstadisticas();
    });
});
</script>
<?= $this->endSection() ?>