<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Gestión de Ingresos<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Ingresos<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Ingresos</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Estadísticas de Ingresos -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>$<?= number_format($estadisticas['total_general'], 2) ?></h3>
                <p>Total Ingresos</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>$<?= number_format($estadisticas['ultimos_30_dias'], 2) ?></h3>
                <p>Últimos 30 días</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= number_format($estadisticas['total_operaciones']) ?></h3>
                <p>Total Operaciones</p>
            </div>
            <div class="icon">
                <i class="fas fa-receipt"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= count($estadisticas['por_tipo']) ?></h3>
                <p>Tipos de Ingreso</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico por tipo de ingreso -->
<?php if (!empty($estadisticas['por_tipo'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Ingresos por Tipo
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($estadisticas['por_tipo'] as $tipo): ?>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-<?= match($tipo->tipo_ingreso) {
                                        'apartado' => 'home',
                                        'enganche' => 'handshake',
                                        'mensualidad' => 'calendar',
                                        'abono_enganche' => 'plus',
                                        default => 'money-bill'
                                    } ?>"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text"><?= ucfirst(str_replace('_', ' ', $tipo->tipo_ingreso)) ?></span>
                                    <span class="info-box-number">$<?= number_format($tipo->total, 2) ?></span>
                                    <span class="progress-description"><?= $tipo->cantidad ?> operaciones</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filtros y Lista de Ingresos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Lista de Ingresos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#filtrosModal">
                        <i class="fas fa-filter mr-1"></i>
                        Filtros Avanzados
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtros rápidos -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroTipoIngreso">
                            <option value="">Todos los tipos</option>
                            <option value="apartado" <?= $filtros['tipo_ingreso'] === 'apartado' ? 'selected' : '' ?>>Apartado</option>
                            <option value="enganche" <?= $filtros['tipo_ingreso'] === 'enganche' ? 'selected' : '' ?>>Enganche</option>
                            <option value="mensualidad" <?= $filtros['tipo_ingreso'] === 'mensualidad' ? 'selected' : '' ?>>Mensualidad</option>
                            <option value="abono_enganche" <?= $filtros['tipo_ingreso'] === 'abono_enganche' ? 'selected' : '' ?>>Abono Enganche</option>
                            <option value="otros" <?= $filtros['tipo_ingreso'] === 'otros' ? 'selected' : '' ?>>Otros</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFechaInicio" 
                               value="<?= $filtros['fecha_inicio'] ?>" placeholder="Fecha inicio">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFechaFin" 
                               value="<?= $filtros['fecha_fin'] ?>" placeholder="Fecha fin">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-secondary" id="limpiarFiltros">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="ingresosTable">
                        <thead>
                            <tr>
                                <th width="100">Folio</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Método Pago</th>
                                <th>Referencia</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables cargará los datos vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Filtros Avanzados -->
<div class="modal fade" id="filtrosModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filtros Avanzados
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="<?= site_url('/admin/ingresos') ?>" method="get">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_ingreso">Tipo de Ingreso</label>
                                <select class="form-control" name="tipo_ingreso">
                                    <option value="">Todos</option>
                                    <option value="apartado" <?= $filtros['tipo_ingreso'] === 'apartado' ? 'selected' : '' ?>>Apartado</option>
                                    <option value="enganche" <?= $filtros['tipo_ingreso'] === 'enganche' ? 'selected' : '' ?>>Enganche</option>
                                    <option value="mensualidad" <?= $filtros['tipo_ingreso'] === 'mensualidad' ? 'selected' : '' ?>>Mensualidad</option>
                                    <option value="abono_enganche" <?= $filtros['tipo_ingreso'] === 'abono_enganche' ? 'selected' : '' ?>>Abono Enganche</option>
                                    <option value="otros" <?= $filtros['tipo_ingreso'] === 'otros' ? 'selected' : '' ?>>Otros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cliente_id">Cliente</label>
                                <select class="form-control select2" name="cliente_id">
                                    <option value="">Todos los clientes</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente->id ?>" <?= $filtros['cliente_id'] == $cliente->id ? 'selected' : '' ?>>
                                            <?= esc($cliente->nombres) ?> <?= esc($cliente->apellido_paterno) ?> <?= esc($cliente->apellido_materno) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" value="<?= $filtros['fecha_inicio'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_fin">Fecha Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" value="<?= $filtros['fecha_fin'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Inicializar DataTable
            // Configuración global de DataTables aplicada desde datatables-config.js

    var table = $('#ingresosTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= site_url('/admin/ingresos/getData') ?>",
            "type": "POST",
            "data": function(d) {
                d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
            }
        },
        "columns": [
            { "data": "folio" },
            { "data": "cliente_completo" },
            { 
                "data": "tipo_ingreso",
                "render": function(data) {
                    const badges = {
                        'apartado': 'badge-info',
                        'enganche': 'badge-success', 
                        'mensualidad': 'badge-primary',
                        'abono_enganche': 'badge-warning',
                        'otros': 'badge-secondary'
                    };
                    const label = data.replace('_', ' ').toUpperCase();
                    const badgeClass = badges[data] || 'badge-secondary';
                    return `<span class="badge ${badgeClass}">${label}</span>`;
                }
            },
            { 
                "data": "monto",
                "render": function(data) {
                    return '$' + parseFloat(data).toLocaleString('es-MX', {minimumFractionDigits: 2});
                }
            },
            { "data": "metodo_pago" },
            { "data": "referencia" },
            { 
                "data": "fecha_ingreso",
                "render": function(data) {
                    return new Date(data).toLocaleDateString('es-MX');
                }
            },
            { "data": "usuario_nombre" },
            {
                "data": "id",
                "render": function(data) {
                    return `
                        <div class="btn-group" role="group">
                            <a href="<?= site_url('/admin/ingresos/') ?>${data}" class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= site_url('/admin/ingresos/recibo/') ?>${data}" class="btn btn-sm btn-secondary" title="Ver recibo" target="_blank">
                                <i class="fas fa-receipt"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[6, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": 8 }
        ]
    });

    // Filtros rápidos
    $('#filtroTipoIngreso, #filtroFechaInicio, #filtroFechaFin').on('change', function() {
        aplicarFiltrosRapidos();
    });

    $('#limpiarFiltros').on('click', function() {
        $('#filtroTipoIngreso, #filtroFechaInicio, #filtroFechaFin').val('');
        window.location.href = '<?= site_url('/admin/ingresos') ?>';
    });

    function aplicarFiltrosRapidos() {
        const tipoIngreso = $('#filtroTipoIngreso').val();
        const fechaInicio = $('#filtroFechaInicio').val();
        const fechaFin = $('#filtroFechaFin').val();
        
        let url = '<?= site_url('/admin/ingresos') ?>?';
        const params = [];
        
        if (tipoIngreso) params.push('tipo_ingreso=' + tipoIngreso);
        if (fechaInicio) params.push('fecha_inicio=' + fechaInicio);
        if (fechaFin) params.push('fecha_fin=' + fechaFin);
        
        if (params.length > 0) {
            url += params.join('&');
        }
        
        window.location.href = url;
    }
});
</script>
<?= $this->endSection() ?>