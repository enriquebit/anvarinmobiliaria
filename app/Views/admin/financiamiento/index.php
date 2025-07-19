<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item active">Financiamiento</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Estadísticas rápidas -->
<div class="row mb-3">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $estadisticas['total_financiamientos'] ?></h3>
                <p>Total Financiamientos</p>
            </div>
            <div class="icon">
                <i class="fas fa-calculator"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $estadisticas['financiamientos_activos'] ?></h3>
                <p>Financiamientos Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= $estadisticas['financiamientos_default'] ?></h3>
                <p>Financiamientos por Defecto</p>
            </div>
            <div class="icon">
                <i class="fas fa-star"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= $estadisticas['financiamientos_cero_enganche'] ?></h3>
                <p>Cero Enganche</p>
            </div>
            <div class="icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros y acciones -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-sliders-h mr-2"></i>
            Financiamientos
        </h3>
        <div class="card-tools">
            <a href="<?= site_url('admin/financiamiento/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Financiamiento
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filtros simples -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filtro_empresa">Filtrar por Empresa:</label>
                <select id="filtro_empresa" class="form-control">
                    <option value="">Todas las empresas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa->id ?>"><?= $empresa->nombre ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <div>
                    <button id="btn_aplicar_filtros" class="btn btn-info">
                        <i class="fas fa-filter"></i> Aplicar Filtros
                    </button>
                    <button id="btn_limpiar_filtros" class="btn btn-secondary">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <div class="text-right">
                    <button id="btn_simulador" class="btn btn-warning" data-toggle="modal" data-target="#modal_simulador">
                        <i class="fas fa-calculator"></i> Simulador
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de financiamientos -->
        <div class="table-responsive">
            <table id="tabla_financiamientos" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empresa</th>
                        <th>Proyecto</th>
                        <th>Nombre</th>
                        <th>Anticipo</th>
                        <th>Comisión</th>
                        <th>Plazo</th>
                        <th>Estado</th>
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

<!-- Modal Simulador -->
<div class="modal fade" id="modal_simulador" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-calculator"></i> Simulador de Financiamiento
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form_simulador">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sim_empresa_id">Empresa:</label>
                                <select id="sim_empresa_id" class="form-control" required>
                                    <option value="">Seleccionar empresa...</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?= $empresa->id ?>"><?= $empresa->nombre ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sim_financiamiento_id">Financiamiento:</label>
                                <select id="sim_financiamiento_id" class="form-control" required disabled>
                                    <option value="">Seleccionar financiamiento...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sim_precio_total">Precio Total:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" id="sim_precio_total" class="form-control" 
                                           step="0.01" min="1" value="500000" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-play"></i> Simular
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Resultados de la simulación -->
                <div id="resultados_simulacion" style="display: none;">
                    <hr>
                    <h5><i class="fas fa-chart-pie"></i> Resultados de la Simulación</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Anticipo</span>
                                    <span class="info-box-number" id="resultado_anticipo">$0.00</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Comisión</span>
                                    <span class="info-box-number" id="resultado_comision">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-credit-card"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Mensualidad</span>
                                    <span class="info-box-number" id="resultado_mensualidad">$0.00</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Plazo Total</span>
                                    <span class="info-box-number" id="resultado_plazo">0 meses</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Monto a Financiar:</strong> $<span id="resultado_monto_financiar">0.00</span><br>
                        <strong>Total de Pagos:</strong> $<span id="resultado_total_pagos">0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // DataTable
    var tabla = $('#tabla_financiamientos').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= site_url("admin/financiamiento/obtenerFinanciamientos") ?>',
            data: function(d) {
                d.empresa_id = $('#filtro_empresa').val();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'empresa' },
            { data: 'proyecto' },
            { data: 'nombre' },
            { data: 'anticipo' },
            { data: 'comision' },
            { data: 'plazo' },
            { data: 'estado', orderable: false },
            { data: 'acciones', orderable: false }
        ]});

    // Aplicar filtros
    $('#btn_aplicar_filtros').click(function() {
        tabla.ajax.reload();
    });

    // Limpiar filtros
    $('#btn_limpiar_filtros').click(function() {
        $('#filtro_empresa').val('');
        tabla.ajax.reload();
    });

    // Cargar financiamientos al cambiar empresa en simulador
    $('#sim_empresa_id').change(function() {
        var empresaId = $(this).val();
        var configSelect = $('#sim_financiamiento_id');
        
        configSelect.prop('disabled', true).html('<option value="">Cargando...</option>');
        
        if (empresaId) {
            $.get('<?= site_url("admin/financiamiento/getByEmpresa") ?>/' + empresaId)
            .done(function(response) {
                if (response.success) {
                    var options = '<option value="">Seleccionar financiamiento...</option>';
                    $.each(response.financiamientos, function(i, config) {
                        var badge = config.es_default ? ' (Predeterminada)' : '';
                        options += '<option value="' + config.id + '">' + config.nombre + badge + '</option>';
                    });
                    configSelect.html(options).prop('disabled', false);
                }
            })
            .fail(function() {
                configSelect.html('<option value="">Error al cargar</option>');
            });
        } else {
            configSelect.html('<option value="">Seleccionar financiamiento...</option>');
        }
    });

    // Simulador
    $('#form_simulador').submit(function(e) {
        e.preventDefault();
        
        var formData = {
            financiamiento_id: $('#sim_financiamiento_id').val(),
            precio_total: $('#sim_precio_total').val()
        };
        
        $.post('<?= site_url("admin/financiamiento/simular") ?>', formData)
        .done(function(response) {
            if (response.success) {
                var sim = response.simulacion;
                
                $('#resultado_anticipo').text('$' + numberFormat(sim.anticipo));
                $('#resultado_comision').text('$' + numberFormat(sim.comision));
                $('#resultado_mensualidad').text('$' + numberFormat(sim.mensualidad));
                $('#resultado_plazo').text(sim.total_meses + ' meses');
                $('#resultado_monto_financiar').text(numberFormat(sim.monto_financiar));
                $('#resultado_total_pagos').text(numberFormat(sim.total_pagos));
                
                $('#resultados_simulacion').show();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .fail(function() {
            Swal.fire('Error', 'Error al procesar la simulación', 'error');
        });
    });

    // Establecer como predeterminado
    $(document).on('click', '.btn-set-default', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: '¿Establecer como predeterminado?',
            text: 'Esta configuración se establecerá como la predeterminada para la empresa',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, establecer',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url("admin/financiamiento/setDefault") ?>', {id: id})
                .done(function(response) {
                    if (response.success) {
                        Swal.fire('¡Éxito!', response.message, 'success');
                        tabla.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }
        });
    });

    // Eliminar configuración
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar configuración?',
            text: 'Esta acción desactivará la configuración',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url("admin/financiamiento/delete") ?>/' + id)
                .done(function(response) {
                    if (response.success) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tabla.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }
        });
    });
});

// Función auxiliar para formatear números
function numberFormat(num) {
    return parseFloat(num).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>
<?= $this->endSection() ?>