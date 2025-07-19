<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Historial de Pagos<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Mi Historial de Pagos<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/estado-cuenta') ?>">Estado de Cuenta</a></li>
<li class="breadcrumb-item active">Historial de Pagos</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Resumen estadístico -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= count($pagos_realizados ?? []) ?></h3>
                <p>Pagos Realizados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>$<?= number_format($total_pagado ?? 0, 0) ?></h3>
                <p>Total Pagado</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>$<?= number_format($promedio_pago ?? 0, 0) ?></h3>
                <p>Promedio por Pago</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?= date('m/Y', strtotime($ultimo_pago_fecha ?? 'now')) ?></h3>
                <p>Último Pago</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary collapsed-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filtros de Búsqueda
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="form-filtros" method="get">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_desde">Fecha Desde:</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                       value="<?= $filtros['fecha_desde'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_hasta">Fecha Hasta:</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                       value="<?= $filtros['fecha_hasta'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="propiedad">Propiedad:</label>
                                <select class="form-control" id="propiedad" name="propiedad">
                                    <option value="">Todas las propiedades</option>
                                    <?php if (!empty($propiedades)): ?>
                                        <?php foreach ($propiedades as $propiedad): ?>
                                        <option value="<?= $propiedad->id ?>" 
                                                <?= ($filtros['propiedad'] ?? '') == $propiedad->id ? 'selected' : '' ?>>
                                            <?= esc($propiedad->lote_clave) ?> - <?= esc($propiedad->folio_venta) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="forma_pago">Forma de Pago:</label>
                                <select class="form-control" id="forma_pago" name="forma_pago">
                                    <option value="">Todas las formas</option>
                                    <option value="efectivo" <?= ($filtros['forma_pago'] ?? '') == 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                    <option value="transferencia" <?= ($filtros['forma_pago'] ?? '') == 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                    <option value="cheque" <?= ($filtros['forma_pago'] ?? '') == 'cheque' ? 'selected' : '' ?>>Cheque</option>
                                    <option value="tarjeta" <?= ($filtros['forma_pago'] ?? '') == 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="<?= site_url('/cliente/estado-cuenta/historialPagos') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                            <div class="float-right">
                                <button type="button" class="btn btn-success" onclick="exportarExcel()">
                                    <i class="fas fa-file-excel"></i> Exportar Excel
                                </button>
                                <button type="button" class="btn btn-danger" onclick="exportarPDF()">
                                    <i class="fas fa-file-pdf"></i> Exportar PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de historial de pagos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Historial Completo de Pagos
                </h3>
                <div class="card-tools">
                    <span class="badge badge-secondary">
                        <?= count($historial_pagos ?? []) ?> registros
                    </span>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($historial_pagos)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="tabla-historial-pagos">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Folio Pago</th>
                                <th>Propiedad</th>
                                <th>Mensualidad</th>
                                <th>Monto</th>
                                <th>Forma Pago</th>
                                <th>Referencia</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial_pagos as $pago): ?>
                            <tr>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></strong><br>
                                    <small class="text-muted"><?= date('H:i', strtotime($pago->fecha_pago)) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?= esc($pago->folio_pago) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= esc($pago->lote_clave ?? 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= esc($pago->folio_venta) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        #<?= $pago->numero_mensualidad ?? 'N/A' ?>
                                    </span>
                                    <?php if (!empty($pago->fecha_vencimiento_original)): ?>
                                    <br><small class="text-muted">
                                        Venc: <?= date('d/m/Y', strtotime($pago->fecha_vencimiento_original)) ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        $<?= number_format($pago->monto_pago, 0) ?>
                                    </strong>
                                    <?php if (!empty($pago->monto_capital) && !empty($pago->monto_intereses)): ?>
                                    <br>
                                    <small class="text-muted">
                                        Cap: $<?= number_format($pago->monto_capital, 0) ?> |
                                        Int: $<?= number_format($pago->monto_intereses, 0) ?>
                                    </small>
                                    <?php endif; ?>
                                    <?php if (!empty($pago->monto_mora) && $pago->monto_mora > 0): ?>
                                    <br>
                                    <small class="text-warning">
                                        Mora: $<?= number_format($pago->monto_mora, 0) ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?= ucfirst($pago->forma_pago) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($pago->referencia_pago)): ?>
                                        <code><?= esc($pago->referencia_pago) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">Aplicado</span>
                                    <?php if (!empty($pago->fecha_aplicacion)): ?>
                                    <br><small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($pago->fecha_aplicacion)) ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('/cliente/pagos/comprobante/' . $pago->id) ?>" 
                                           class="btn btn-info btn-xs" target="_blank">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        <button type="button" class="btn btn-secondary btn-xs" 
                                                onclick="verDetallePago(<?= $pago->id ?>)" 
                                                data-toggle="tooltip" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle mr-2"></i>Sin Pagos Registrados</h5>
                    <p>Aún no se han registrado pagos en tu cuenta.</p>
                    <a href="<?= site_url('/cliente/pagos') ?>" class="btn btn-primary">
                        <i class="fas fa-dollar-sign"></i> Realizar mi Primer Pago
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de pagos por mes (opcional) -->
<?php if (!empty($estadisticas_mensuales)): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Historial de Pagos por Mes
                </h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="grafico-pagos-mes" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Formas de Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="grafico-formas-pago" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal para detalles de pago -->
<div class="modal fade" id="modal-detalle-pago" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                    Detalle del Pago
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contenido-detalle-pago">
                <!-- Contenido cargado vía AJAX -->
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando información...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetalle()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

<!-- Chart.js -->
<script src="<?= base_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // Configurar DataTable
    $('#tabla-historial-pagos').DataTable({
        "language": {
            "url": "<?= base_url('assets/plugins/datatables/i18n/Spanish.json') ?>"
        },
        "pageLength": 25,
        "order": [[0, 'desc']],
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ],
        "dom": 'Bfrtip',
        "buttons": [
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
        ]
    });
    
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Gráficos si hay datos
    <?php if (!empty($estadisticas_mensuales)): ?>
    inicializarGraficos();
    <?php endif; ?>
});

// Función para ver detalle del pago
function verDetallePago(pagoId) {
    $('#modal-detalle-pago').modal('show');
    
    // Cargar contenido vía AJAX
    $.get('<?= site_url('/cliente/pagos/comprobante/') ?>' + pagoId + '?ajax=1')
        .done(function(data) {
            $('#contenido-detalle-pago').html(data);
        })
        .fail(function() {
            $('#contenido-detalle-pago').html(
                '<div class="alert alert-danger">' +
                '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                'Error al cargar los detalles del pago.' +
                '</div>'
            );
        });
}

// Función para exportar a Excel
function exportarExcel() {
    var params = $('#form-filtros').serialize();
    window.open('<?= site_url('/cliente/estado-cuenta/exportarHistorial') ?>?' + params + '&formato=excel', '_blank');
}

// Función para exportar a PDF
function exportarPDF() {
    var params = $('#form-filtros').serialize();
    window.open('<?= site_url('/cliente/estado-cuenta/exportarHistorial') ?>?' + params + '&formato=pdf', '_blank');
}

// Función para imprimir detalle
function imprimirDetalle() {
    var contenido = $('#contenido-detalle-pago').html();
    var ventanaImpresion = window.open('', '_blank');
    ventanaImpresion.document.write(`
        <html>
        <head>
            <title>Comprobante de Pago</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .table { width: 100%; border-collapse: collapse; }
                .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .table th { background-color: #f2f2f2; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .badge { padding: 3px 6px; border-radius: 3px; }
                .badge-success { background-color: #28a745; color: white; }
                .badge-info { background-color: #17a2b8; color: white; }
            </style>
        </head>
        <body>
            ${contenido}
        </body>
        </html>
    `);
    ventanaImpresion.document.close();
    ventanaImpresion.print();
}

<?php if (!empty($estadisticas_mensuales)): ?>
// Inicializar gráficos
function inicializarGraficos() {
    // Gráfico de pagos por mes
    var ctx1 = document.getElementById('grafico-pagos-mes').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($estadisticas_mensuales, 'mes')) ?>,
            datasets: [{
                label: 'Monto ($)',
                data: <?= json_encode(array_column($estadisticas_mensuales, 'total')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + Number(value).toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de formas de pago
    var ctx2 = document.getElementById('grafico-formas-pago').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($estadisticas_formas_pago ?? [], 'forma_pago')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($estadisticas_formas_pago ?? [], 'total')) ?>,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}
<?php endif; ?>
</script>
<?= $this->endSection() ?>