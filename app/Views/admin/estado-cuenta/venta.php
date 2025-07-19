<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= $title ?></h1>
                <p class="text-muted">
                    Cliente: <strong><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno) ?></strong>
                </p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/estado-cuenta') ?>">Estado de Cuenta</a></li>
                    <li class="breadcrumb-item active">Detalle Venta</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Información General de la Venta -->
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Información de la Venta</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Folio:</dt>
                            <dd class="col-sm-7"><strong><?= $venta->folio_venta ?></strong></dd>
                            
                            <dt class="col-sm-5">Fecha Venta:</dt>
                            <dd class="col-sm-7"><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></dd>
                            
                            <dt class="col-sm-5">Tipo:</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-<?= $venta->tipo_venta === 'contado' ? 'success' : 'info' ?>">
                                    <?= ucfirst($venta->tipo_venta) ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Estado:</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-<?= $estado_visual['color_badge'] ?>">
                                    <?= $estado_visual['estatus_formateado'] ?>
                                </span>
                            </dd>
                        </dl>
                        
                        <div class="text-center mt-3">
                            <a href="<?= site_url('/admin/ventas/show/' . $venta->id) ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Ver Venta Completa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Resumen Financiero -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumen Financiero</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-success">
                                        <?= $montos_formateados['precio_venta_final'] ?>
                                    </h5>
                                    <span class="description-text">PRECIO TOTAL</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-info">
                                        <?= $montos_formateados['total_pagado'] ?>
                                    </h5>
                                    <span class="description-text">TOTAL PAGADO</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-warning">
                                        <?= $montos_formateados['saldo_pendiente'] ?>
                                    </h5>
                                    <span class="description-text">SALDO PENDIENTE</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block">
                                    <h5 class="description-header text-primary">
                                        <?= number_format($resumen_financiero['porcentaje_liquidacion'], 1) ?>%
                                    </h5>
                                    <span class="description-text">LIQUIDACIÓN</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Barra de Progreso -->
                        <div class="progress mt-3">
                            <?php 
                            $porcentaje = $resumen_financiero['porcentaje_liquidacion'];
                            $colorBarra = $porcentaje >= 80 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                            ?>
                            <div class="progress-bar bg-<?= $colorBarra ?>" style="width: <?= $porcentaje ?>%">
                                <?= number_format($porcentaje, 1) ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Proyección de Liquidación -->
        <?php if ($proyeccion_liquidacion['success']): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Proyección de Liquidación
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success"><i class="fas fa-money-bill"></i> Liquidación Anticipada (Hoy)</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Monto a pagar:</strong> 
                                        <span class="text-success">
                                            $<?= number_format($proyeccion_liquidacion['escenarios']['liquidacion_hoy']['monto_pagar'], 2) ?>
                                        </span>
                                    </li>
                                    <li><strong>Ahorro en intereses:</strong> 
                                        <span class="text-info">
                                            $<?= number_format($proyeccion_liquidacion['escenarios']['liquidacion_hoy']['ahorro'], 2) ?>
                                        </span>
                                    </li>
                                    <li><strong>Porcentaje de ahorro:</strong> 
                                        <span class="badge badge-success">
                                            <?= $proyeccion_liquidacion['escenarios']['liquidacion_hoy']['porcentaje_ahorro'] ?>%
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-warning"><i class="fas fa-calendar-alt"></i> Pago Puntual (Mensualidades)</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Fecha de término:</strong> 
                                        <?= $proyeccion_liquidacion['escenarios']['pago_puntual']['fecha_termino'] ? 
                                            date('d/m/Y', strtotime($proyeccion_liquidacion['escenarios']['pago_puntual']['fecha_termino'])) : 'N/A' ?>
                                    </li>
                                    <li><strong>Total a pagar:</strong> 
                                        <span class="text-warning">
                                            $<?= number_format($proyeccion_liquidacion['escenarios']['pago_puntual']['total_pagar'], 2) ?>
                                        </span>
                                    </li>
                                    <li><strong>Mensualidades restantes:</strong> 
                                        <span class="badge badge-warning">
                                            <?= $proyeccion_liquidacion['escenarios']['pago_puntual']['mensualidades_restantes'] ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Tabla de Amortización -->
        <?php if (!empty($mensualidades)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table"></i>
                            Tabla de Amortización (<?= count($mensualidades) ?> mensualidades)
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-sm" onclick="filtrarMensualidades('todas')">
                                    Todas
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" onclick="filtrarMensualidades('pendientes')">
                                    Pendientes
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="filtrarMensualidades('vencidas')">
                                    Vencidas
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="filtrarMensualidades('pagadas')">
                                    Pagadas
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaMensualidades">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Capital</th>
                                        <th>Interés</th>
                                        <th>Monto Total</th>
                                        <th>Pagado</th>
                                        <th>Pendiente</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensualidades as $mensualidad): ?>
                                    <tr data-estado="<?= $mensualidad->estatus ?>" 
                                        class="<?= $mensualidad->estatus === 'vencida' ? 'table-danger' : 
                                                  ($mensualidad->estatus === 'pagada' ? 'table-success' : '') ?>">
                                        <td>
                                            <span class="badge badge-secondary">
                                                #<?= $mensualidad->numero_pago ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                            <?php if ($mensualidad->estatus === 'vencida'): ?>
                                                <br><small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <?= $mensualidad->dias_atraso ?> días atraso
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?= number_format($mensualidad->capital, 2) ?></td>
                                        <td>
                                            $<?= number_format($mensualidad->interes, 2) ?>
                                            <?php if ($mensualidad->interes_moratorio > 0): ?>
                                                <br><small class="text-warning">
                                                    + $<?= number_format($mensualidad->interes_moratorio, 2) ?> mora
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($mensualidad->monto_total + $mensualidad->interes_moratorio, 2) ?></strong>
                                        </td>
                                        <td>
                                            <span class="text-success">
                                                $<?= number_format($mensualidad->monto_pagado, 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-warning">
                                                $<?= number_format($mensualidad->saldo_pendiente, 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($mensualidad->estatus === 'pagada'): ?>
                                                <span class="badge badge-success">Pagada</span>
                                            <?php elseif ($mensualidad->estatus === 'vencida'): ?>
                                                <span class="badge badge-danger">Vencida</span>
                                            <?php elseif ($mensualidad->estatus === 'parcial'): ?>
                                                <span class="badge badge-warning">Parcial</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($mensualidad->estatus !== 'pagada'): ?>
                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $mensualidad->id) ?>" 
                                                   class="btn btn-success btn-xs" title="Aplicar Pago">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="<?= site_url('/admin/mensualidades/detalle/' . $mensualidad->id) ?>" 
                                                   class="btn btn-info btn-xs" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Historial de Pagos -->
        <?php if (!empty($historial_pagos)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Historial de Pagos (<?= count($historial_pagos) ?>)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Folio</th>
                                        <th>Mensualidad #</th>
                                        <th>Concepto</th>
                                        <th>Forma de Pago</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_pagos as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($pago->fecha_pago)) ?></td>
                                        <td><strong><?= $pago->folio_pago ?></strong></td>
                                        <td>
                                            <?php if ($pago->numero_mensualidad): ?>
                                                <span class="badge badge-info">
                                                    #<?= $pago->numero_mensualidad ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($pago->concepto_pago) ?></td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= ucfirst($pago->forma_pago) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                $<?= number_format($pago->monto_pago, 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($pago->estatus_pago === 'aplicado'): ?>
                                                <span class="badge badge-success">Aplicado</span>
                                            <?php elseif ($pago->estatus_pago === 'cancelado'): ?>
                                                <span class="badge badge-danger">Cancelado</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('/admin/mensualidades/comprobante/' . $pago->id) ?>" 
                                                   class="btn btn-info btn-xs" title="Ver Comprobante">
                                                    <i class="fas fa-receipt"></i>
                                                </a>
                                                <?php if ($pago->estatus_pago === 'aplicado'): ?>
                                                <button type="button" class="btn btn-danger btn-xs" 
                                                        onclick="confirmarCancelacion(<?= $pago->id ?>)" 
                                                        title="Cancelar Pago">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- Modal para Cancelar Pago -->
<div class="modal fade" id="modalCancelarPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cancelar Pago</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formCancelarPago" method="POST">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>¿Está seguro de cancelar este pago?</strong><br>
                        Esta acción afectará el estado de la mensualidad correspondiente.
                    </div>
                    <div class="form-group">
                        <label>Motivo de cancelación <span class="text-danger">*</span></label>
                        <textarea name="motivo_cancelacion" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable para mensualidades
    $('#tablaMensualidades').DataTable({
        "responsive": true,
        "pageLength": 25,
        "order": [[ 0, "asc" ]], // Ordenar por número de mensualidad
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
});

// Filtrar mensualidades por estado
function filtrarMensualidades(estado) {
    var tabla = $('#tablaMensualidades').DataTable();
    
    if (estado === 'todas') {
        tabla.column(7).search('').draw(); // Columna de estado
    } else {
        var termino = '';
        switch(estado) {
            case 'pendientes':
                termino = 'Pendiente';
                break;
            case 'vencidas':
                termino = 'Vencida';
                break;
            case 'pagadas':
                termino = 'Pagada';
                break;
        }
        tabla.column(7).search(termino).draw();
    }
}

// Confirmar cancelación de pago
function confirmarCancelacion(pagoId) {
    $('#formCancelarPago').attr('action', '<?= site_url("/admin/mensualidades/cancelarPago/") ?>' + pagoId);
    $('#modalCancelarPago').modal('show');
}

// Manejar envío del formulario de cancelación
$('#formCancelarPago').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this);
    var motivo = form.find('textarea[name="motivo_cancelacion"]').val().trim();
    
    if (motivo === '') {
        alert('El motivo de cancelación es requerido');
        return;
    }
    
    // Enviar formulario
    form.off('submit').submit();
});
</script>
<?= $this->endSection() ?>