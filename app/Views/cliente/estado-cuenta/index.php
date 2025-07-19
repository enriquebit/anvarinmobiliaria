<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Mi Estado de Cuenta<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Mi Estado de Cuenta<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Estado de Cuenta</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Alertas de información importante -->
<?php if (!empty($alertas_criticas)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Atención!</h5>
            <ul class="mb-0">
                <?php foreach ($alertas_criticas as $alerta): ?>
                <li><?= esc($alerta['mensaje']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Cards de resumen financiero -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= count($propiedades) ?></h3>
                <p>Mis Propiedades</p>
            </div>
            <div class="icon">
                <i class="fas fa-home"></i>
            </div>
            <a href="#seccion-propiedades" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>$<?= number_format($resumen_financiero['total_pagado'] ?? 0, 0) ?></h3>
                <p>Total Pagado</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <a href="<?= site_url('/cliente/estado-cuenta/historialPagos') ?>" class="small-box-footer">Ver historial <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>$<?= number_format($resumen_financiero['saldo_pendiente'] ?? 0, 0) ?></h3>
                <p>Saldo Pendiente</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="<?= site_url('/cliente/estado-cuenta/proximosVencimientos') ?>" class="small-box-footer">Ver vencimientos <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= count($mensualidades_vencidas ?? []) ?></h3>
                <p>Pagos Vencidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="<?= site_url('/cliente/pagos') ?>" class="small-box-footer">Realizar pago <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Mis Propiedades -->
<div class="row" id="seccion-propiedades">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-home mr-2"></i>
                    Mis Propiedades
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($propiedades)): ?>
                <div class="row">
                    <?php foreach ($propiedades as $venta): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= esc($venta->lote_clave ?? 'Lote N/A') ?>
                                </h5>
                                <div class="card-tools">
                                    <?php if ($venta->tipo_venta === 'financiado'): ?>
                                        <span class="badge badge-info">Financiado</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Contado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><strong>Folio:</strong> <?= esc($venta->folio_venta) ?></p>
                                <p><strong>Proyecto:</strong> <?= esc($venta->proyecto_nombre ?? 'N/A') ?></p>
                                <p><strong>Precio:</strong> $<?= number_format($venta->precio_venta_final, 0) ?></p>
                                
                                <?php if ($venta->tipo_venta === 'financiado' && !empty($venta->resumen_estado)): ?>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $venta->resumen_estado['porcentaje_liquidacion'] ?? 0 ?>%">
                                        <?= number_format($venta->resumen_estado['porcentaje_liquidacion'] ?? 0, 1) ?>%
                                    </div>
                                </div>
                                
                                <small class="text-muted">
                                    Saldo: $<?= number_format($venta->resumen_estado['saldo_pendiente'] ?? 0, 0) ?>
                                </small>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <a href="<?= site_url('/cliente/estado-cuenta/propiedad/' . $venta->id) ?>" 
                                       class="btn btn-info">
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    </a>
                                    
                                    <?php if ($venta->tipo_venta === 'financiado' && !empty($venta->proxima_mensualidad)): ?>
                                    <a href="<?= site_url('/cliente/pagos/mensualidad/' . $venta->proxima_mensualidad->id) ?>" 
                                       class="btn btn-success">
                                        <i class="fas fa-dollar-sign"></i> Pagar
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle mr-2"></i>Sin Propiedades</h5>
                    <p>Aún no tienes propiedades registradas en tu cuenta.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Próximos Vencimientos -->
<?php if (!empty($proximos_vencimientos)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Próximos Vencimientos
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/cliente/estado-cuenta/proximosVencimientos') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-calendar"></i> Ver Calendario Completo
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Mensualidad</th>
                                <th>Fecha Vencimiento</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($proximos_vencimientos, 0, 5) as $mensualidad): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($mensualidad->lote_clave ?? 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= esc($mensualidad->folio_venta) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        #<?= $mensualidad->numero_pago ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                    <?php 
                                    $dias_para_vencer = (strtotime($mensualidad->fecha_vencimiento) - time()) / (60 * 60 * 24);
                                    if ($dias_para_vencer < 0): ?>
                                        <br><small class="text-danger">
                                            <?= abs(floor($dias_para_vencer)) ?> días vencida
                                        </small>
                                    <?php elseif ($dias_para_vencer <= 7): ?>
                                        <br><small class="text-warning">
                                            <?= floor($dias_para_vencer) ?> días restantes
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>$<?= number_format($mensualidad->monto_total, 0) ?></strong>
                                    <?php if ($mensualidad->interes_moratorio > 0): ?>
                                        <br><small class="text-warning">
                                            + $<?= number_format($mensualidad->interes_moratorio, 0) ?> mora
                                        </small>
                                    <?php endif; ?>
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
                                    <?php if ($mensualidad->estatus !== 'pagada'): ?>
                                    <a href="<?= site_url('/cliente/pagos/mensualidad/' . $mensualidad->id) ?>" 
                                       class="btn btn-success btn-xs">
                                        <i class="fas fa-dollar-sign"></i> Pagar
                                    </a>
                                    <?php else: ?>
                                    <a href="<?= site_url('/cliente/pagos/comprobante/' . $mensualidad->ultimo_pago_id) ?>" 
                                       class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <?php endif; ?>
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

<!-- Últimos Pagos Realizados -->
<?php if (!empty($ultimos_pagos)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Últimos Pagos Realizados
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/cliente/estado-cuenta/historialPagos') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-list"></i> Ver Historial Completo
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Folio</th>
                                <th>Propiedad</th>
                                <th>Mensualidad</th>
                                <th>Monto</th>
                                <th>Forma Pago</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($ultimos_pagos, 0, 5) as $pago): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></td>
                                <td><strong><?= esc($pago->folio_pago) ?></strong></td>
                                <td>
                                    <strong><?= esc($pago->lote_clave ?? 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= esc($pago->folio_venta) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        #<?= $pago->numero_mensualidad ?? 'N/A' ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        $<?= number_format($pago->monto_pago, 0) ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?= ucfirst($pago->forma_pago) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('/cliente/pagos/comprobante/' . $pago->id) ?>" 
                                       class="btn btn-info btn-xs">
                                        <i class="fas fa-file-alt"></i> Ver
                                    </a>
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

<!-- Acciones Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/pagos') ?>" class="btn btn-success btn-block">
                            <i class="fas fa-dollar-sign"></i><br>
                            Realizar Pago
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/estado-cuenta/descargarPDF') ?>" class="btn btn-info btn-block">
                            <i class="fas fa-file-pdf"></i><br>
                            Descargar Estado
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/estado-cuenta/proximosVencimientos') ?>" class="btn btn-warning btn-block">
                            <i class="fas fa-calendar-alt"></i><br>
                            Ver Calendario
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/pagos/reportarPago') ?>" class="btn btn-secondary btn-block">
                            <i class="fas fa-bell"></i><br>
                            Reportar Pago
                        </a>
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
    // Auto-actualizar datos cada 5 minutos
    setInterval(function() {
        // Recargar widgets de resumen si existen
        if (typeof actualizarWidgetResumen === 'function') {
            actualizarWidgetResumen();
        }
    }, 300000); // 5 minutos
    
    // Configuración de tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Efecto hover en cards de propiedades
    $('.card-outline').hover(
        function() {
            $(this).addClass('card-primary').removeClass('card-info');
        },
        function() {
            $(this).addClass('card-info').removeClass('card-primary');
        }
    );
});

// Función para actualizar widget de resumen (AJAX)
function actualizarWidgetResumen() {
    $.get('<?= site_url('/cliente/estado-cuenta/widgetResumenFinanciero') ?>')
        .done(function(data) {
            // Actualizar valores en los small-boxes
            if (data.success) {
                $('#total-pagado').text('$' + Number(data.total_pagado).toLocaleString());
                $('#saldo-pendiente').text('$' + Number(data.saldo_pendiente).toLocaleString());
                $('#pagos-vencidos').text(data.pagos_vencidos);
            }
        })
        .fail(function() {
            console.log('Error al actualizar resumen financiero');
        });
}
</script>
<?= $this->endSection() ?>