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
                    <i class="fas fa-envelope"></i> <?= esc($cliente['email']) ?> &nbsp;
                    <i class="fas fa-phone"></i> <?= esc($cliente['telefono']) ?>
                </p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/estado-cuenta') ?>">Estado de Cuenta</a></li>
                    <li class="breadcrumb-item active">Cliente</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Información General del Cliente -->
        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Resumen General</h3>
                    </div>
                    <div class="card-body">
                        <strong><i class="fas fa-home mr-1"></i> Propiedades</strong>
                        <p class="text-muted"><?= $resumen_general['total_propiedades'] ?> propiedades activas</p>
                        <hr>
                        
                        <strong><i class="fas fa-dollar-sign mr-1"></i> Inversión Total</strong>
                        <p class="text-muted"><?= $resumen_general['monto_total_invertido'] ?></p>
                        <hr>
                        
                        <strong><i class="fas fa-chart-pie mr-1"></i> Liquidación</strong>
                        <p class="text-muted"><?= $resumen_general['porcentaje_liquidacion'] ?> completado</p>
                        <hr>
                        
                        <strong><i class="fas fa-credit-card mr-1"></i> Total Pagado</strong>
                        <p class="text-muted text-success"><?= $resumen_general['total_pagado'] ?></p>
                        <hr>
                        
                        <strong><i class="fas fa-clock mr-1"></i> Saldo Pendiente</strong>
                        <p class="text-muted text-warning"><?= $resumen_general['saldo_total_pendiente'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <!-- Indicadores -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon <?= $indicadores['comportamiento_pago'] === 'Bueno' ? 'bg-success' : ($indicadores['comportamiento_pago'] === 'Regular' ? 'bg-warning' : 'bg-danger') ?>">
                                <i class="fas fa-user-check"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Comportamiento</span>
                                <span class="info-box-number"><?= $indicadores['comportamiento_pago'] ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Días Promedio Atraso</span>
                                <span class="info-box-number"><?= $indicadores['dias_atraso_promedio'] ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Moratorios</span>
                                <span class="info-box-number"><?= $indicadores['moratorios_acumulados'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximo Vencimiento -->
                <?php if (!empty($proximo_vencimiento)): ?>
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Próximo Vencimiento</h5>
                    <strong>Mensualidad #<?= $proximo_vencimiento['numero_pago'] ?></strong> - 
                    <?= $proximo_vencimiento['monto'] ?> - 
                    Vence: <?= $proximo_vencimiento['fecha'] ?>
                    
                    <?php if ($proximo_vencimiento['estado'] === 'urgente'): ?>
                        <span class="badge badge-warning ml-2">¡Vence en <?= $proximo_vencimiento['dias_restantes'] ?> días!</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Alertas del Cliente -->
        <?php if (!empty($alertas['criticas']) || !empty($alertas['urgentes'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Alertas Importantes (<?= $alertas['total_alertas'] ?>)
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Alertas Críticas -->
                        <?php if (!empty($alertas['criticas'])): ?>
                        <h6 class="text-danger"><strong>Críticas (<?= count($alertas['criticas']) ?>)</strong></h6>
                        <?php foreach ($alertas['criticas'] as $alerta): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong><?= $alerta['titulo'] ?></strong><br>
                            <?= $alerta['mensaje'] ?>
                            <div class="mt-2">
                                <?php if ($alerta['acciones']['pagar_ahora']): ?>
                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $alerta['mensualidad_id']) ?>" 
                                   class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-dollar-sign"></i> Aplicar Pago
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Alertas Urgentes -->
                        <?php if (!empty($alertas['urgentes'])): ?>
                        <h6 class="text-warning"><strong>Urgentes (<?= count($alertas['urgentes']) ?>)</strong></h6>
                        <?php foreach (array_slice($alertas['urgentes'], 0, 3) as $alerta): ?>
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong><?= $alerta['titulo'] ?></strong><br>
                            <?= $alerta['mensaje'] ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades Vencidas -->
        <?php if (!empty($mensualidades_vencidas)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-circle"></i>
                            Mensualidades Vencidas (<?= count($mensualidades_vencidas) ?>)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Folio Venta</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Atraso</th>
                                        <th>Monto Pendiente</th>
                                        <th>Interés Moratorio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensualidades_vencidas as $vencida): ?>
                                    <tr>
                                        <td><strong><?= $vencida['venta_folio'] ?></strong></td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                #<?= $vencida['numero_pago'] ?>
                                            </span>
                                        </td>
                                        <td><?= $vencida['fecha_vencimiento'] ?></td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <?= $vencida['dias_atraso'] ?> días
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">
                                                <?= $vencida['monto_pendiente'] ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="text-warning">
                                                <?= $vencida['interes_moratorio'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $vencida['mensualidad_id']) ?>" 
                                                   class="btn btn-success btn-xs">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                                <a href="<?= site_url('/admin/mensualidades/detalle/' . $vencida['mensualidad_id']) ?>" 
                                                   class="btn btn-info btn-xs">
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
        
        <!-- Próximas Mensualidades -->
        <?php if (!empty($proximas_mensualidades)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt"></i>
                            Próximas Mensualidades (<?= count($proximas_mensualidades) ?>)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Folio Venta</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Hasta Vencer</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximas_mensualidades as $proxima): ?>
                                    <tr>
                                        <td><strong><?= $proxima['venta_folio'] ?></strong></td>
                                        <td>
                                            <span class="badge badge-info">
                                                #<?= $proxima['numero_pago'] ?>
                                            </span>
                                        </td>
                                        <td><?= $proxima['fecha_vencimiento'] ?></td>
                                        <td>
                                            <?php if ($proxima['estado'] === 'proximo'): ?>
                                                <span class="badge badge-warning">
                                                    <?= $proxima['dias_hasta_vencimiento'] ?> días
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-light">
                                                    <?= $proxima['dias_hasta_vencimiento'] ?> días
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= $proxima['monto'] ?></strong></td>
                                        <td>
                                            <?php if ($proxima['estado'] === 'vencida'): ?>
                                                <span class="badge badge-danger">Vencida</span>
                                            <?php elseif ($proxima['estado'] === 'proximo'): ?>
                                                <span class="badge badge-warning">Próximo</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Normal</span>
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
        
        <!-- Propiedades del Cliente -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-home"></i>
                            Propiedades del Cliente (<?= count($propiedades) ?>)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($propiedades as $propiedad): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-outline <?= $propiedad['estado_visual']['color_clase'] ?>">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <strong><?= $propiedad['folio'] ?></strong>
                                        </h5>
                                        <div class="card-tools">
                                            <span class="badge badge-<?= $propiedad['estado_visual']['color_badge'] ?>">
                                                <?= $propiedad['estado_visual']['estatus_formateado'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= esc($propiedad['descripcion']) ?><br>
                                            <small><?= esc($propiedad['proyecto']) ?></small>
                                        </p>
                                        
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="description-block border-right">
                                                    <h5 class="description-header"><?= $propiedad['precio_venta'] ?></h5>
                                                    <span class="description-text">PRECIO</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="description-block">
                                                    <h5 class="description-header"><?= $propiedad['saldo_pendiente'] ?></h5>
                                                    <span class="description-text">PENDIENTE</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="progress mb-2">
                                            <?php 
                                            $porcentajePagado = (float)str_replace('%', '', $propiedad['porcentaje_pagado']);
                                            $colorBarra = $porcentajePagado >= 80 ? 'success' : ($porcentajePagado >= 50 ? 'warning' : 'danger');
                                            ?>
                                            <div class="progress-bar bg-<?= $colorBarra ?>" style="width: <?= $porcentajePagado ?>%"></div>
                                        </div>
                                        
                                        <p class="text-center mb-2">
                                            <strong><?= $propiedad['porcentaje_pagado'] ?></strong> completado
                                        </p>
                                        
                                        <div class="btn-group btn-group-sm btn-block">
                                            <a href="<?= site_url('/admin/estado-cuenta/venta/' . $propiedad['venta_id']) ?>" 
                                               class="btn btn-info">
                                                <i class="fas fa-eye"></i> Detalle
                                            </a>
                                            <a href="<?= site_url('/admin/ventas/show/' . $propiedad['venta_id']) ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-home"></i> Venta
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial de Pagos Recientes -->
        <?php if (!empty($historial_pagos)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Últimos Pagos Registrados
                        </h3>
                        <div class="card-tools">
                            <a href="<?= site_url('/admin/estado-cuenta/cliente/' . $cliente['id'] . '/historial') ?>" 
                               class="btn btn-primary btn-sm">
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
                                        <th>Concepto</th>
                                        <th>Forma de Pago</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_pagos as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></td>
                                        <td><strong><?= $pago->folio_pago ?></strong></td>
                                        <td><?= esc($pago->concepto_pago) ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= ucfirst($pago->forma_pago) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                $<?= number_format($pago->monto_pago, 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                Aplicado
                                            </span>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Auto-refresh cada 10 minutos
    setTimeout(function() {
        location.reload();
    }, 600000);
});
</script>
<?= $this->endSection() ?>