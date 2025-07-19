<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
                <p class="text-muted">Folio: <?= $reestructuracion->folio_reestructuracion ?></p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin/reestructuracion') ?>">Reestructuración</a></li>
                    <li class="breadcrumb-item active">Ver</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Estado y acciones -->
        <div class="row">
            <div class="col-md-8">
                <div class="alert alert-<?= $reestructuracion->estatus == 'activa' ? 'success' : ($reestructuracion->estatus == 'propuesta' ? 'warning' : 'info') ?>">
                    <h4><i class="fas fa-info-circle"></i> Estado: <?= ucfirst($reestructuracion->estatus) ?></h4>
                    <?php if ($reestructuracion->estatus == 'propuesta'): ?>
                        <p>Esta reestructuración está pendiente de autorización.</p>
                    <?php elseif ($reestructuracion->estatus == 'autorizada'): ?>
                        <p>Reestructuración autorizada, pendiente de firma y activación.</p>
                    <?php elseif ($reestructuracion->estatus == 'activa'): ?>
                        <p>Reestructuración activa con nuevos términos de pago.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Acciones</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($reestructuracion->estatus == 'propuesta'): ?>
                            <a href="<?= base_url('/admin/reestructuracion/autorizar/' . $reestructuracion->id) ?>" 
                               class="btn btn-success btn-block"
                               onclick="return confirm('¿Autorizar esta reestructuración?')">
                                <i class="fas fa-check"></i> Autorizar
                            </a>
                        <?php elseif ($reestructuracion->estatus == 'autorizada'): ?>
                            <a href="<?= base_url('/admin/reestructuracion/activar/' . $reestructuracion->id) ?>" 
                               class="btn btn-primary btn-block"
                               onclick="return confirm('¿Activar esta reestructuración?')">
                                <i class="fas fa-play"></i> Activar
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('/admin/reestructuracion/listar') ?>" 
                           class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> Ver Todas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información general -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información General</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Folio:</strong></td>
                                <td><?= $reestructuracion->folio_reestructuracion ?></td>
                            </tr>
                            <tr>
                                <td><strong>Fecha:</strong></td>
                                <td><?= date('d/m/Y', strtotime($reestructuracion->fecha_reestructuracion)) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge badge-<?= $reestructuracion->estatus == 'activa' ? 'success' : ($reestructuracion->estatus == 'propuesta' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($reestructuracion->estatus) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Registrado por:</strong></td>
                                <td><?= $reestructuracion->nombre_registrado_por ?></td>
                            </tr>
                            <?php if ($reestructuracion->autorizado_por): ?>
                            <tr>
                                <td><strong>Autorizado por:</strong></td>
                                <td><?= $reestructuracion->autorizado_por_nombre ?></td>
                            </tr>
                            <tr>
                                <td><strong>Fecha autorización:</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($reestructuracion->fecha_autorizacion)) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información del Cliente</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td><?= $reestructuracion->nombre_cliente ?></td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td><?= $reestructuracion->telefono_cliente ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?= $reestructuracion->email_cliente ?></td>
                            </tr>
                            <tr>
                                <td><strong>Folio Venta:</strong></td>
                                <td>
                                    <a href="<?= base_url('/admin/ventas/ver/' . $reestructuracion->venta_id) ?>" 
                                       class="btn btn-link btn-sm p-0">
                                        <?= $reestructuracion->folio_venta ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Lote:</strong></td>
                                <td><?= $reestructuracion->clave_lote ?></td>
                            </tr>
                            <tr>
                                <td><strong>Proyecto:</strong></td>
                                <td><?= $reestructuracion->nombre_proyecto ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparación de condiciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Comparación de Condiciones</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Condiciones Originales</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td><strong>Saldo Pendiente:</strong></td>
                                <td class="text-danger">$<?= number_format($reestructuracion->saldo_pendiente_original, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Capital:</strong></td>
                                <td>$<?= number_format($reestructuracion->saldo_capital_original, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Intereses:</strong></td>
                                <td>$<?= number_format($reestructuracion->saldo_interes_original, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Moratorios:</strong></td>
                                <td>$<?= number_format($reestructuracion->saldo_moratorio_original, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Vencimiento:</strong></td>
                                <td><?= date('d/m/Y', strtotime($reestructuracion->fecha_vencimiento_original)) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Nuevas Condiciones</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td><strong>Nuevo Saldo Capital:</strong></td>
                                <td class="text-success">$<?= number_format($reestructuracion->nuevo_saldo_capital, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Plazo:</strong></td>
                                <td><?= $reestructuracion->nuevo_plazo_meses ?> meses</td>
                            </tr>
                            <tr>
                                <td><strong>Tasa Interés:</strong></td>
                                <td><?= $reestructuracion->nueva_tasa_interes ?>% anual</td>
                            </tr>
                            <tr>
                                <td><strong>Pago Mensual:</strong></td>
                                <td class="text-primary">$<?= number_format($reestructuracion->nuevo_pago_mensual, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Primer Pago:</strong></td>
                                <td><?= date('d/m/Y', strtotime($reestructuracion->fecha_primer_pago)) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Descuentos aplicados -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h5>Descuentos y Quitas Aplicadas</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-cut"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Quita</span>
                                        <span class="info-box-number">$<?= number_format($reestructuracion->quita_aplicada, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Desc. Intereses</span>
                                        <span class="info-box-number">$<?= number_format($reestructuracion->descuento_intereses, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Desc. Moratorios</span>
                                        <span class="info-box-number">$<?= number_format($reestructuracion->descuento_moratorios, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-primary">
                                    <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Enganche</span>
                                        <span class="info-box-number">$<?= number_format($reestructuracion->enganche_convenio, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progreso de pagos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Progreso de Pagos</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Mensualidades</span>
                                <span class="info-box-number"><?= $progreso['total_mensualidades'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pagadas</span>
                                <span class="info-box-number"><?= $progreso['mensualidades_pagadas'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pendientes</span>
                                <span class="info-box-number"><?= $progreso['mensualidades_pendientes'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Vencidas</span>
                                <span class="info-box-number"><?= $progreso['mensualidades_vencidas'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Programado</span>
                                <span class="info-box-number">$<?= number_format($progreso['total_programado'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Pagado</span>
                                <span class="info-box-number">$<?= number_format($progreso['total_pagado'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Saldo Pendiente</span>
                                <span class="info-box-number">$<?= number_format($progreso['saldo_pendiente'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <h5>Porcentaje de Liquidación</h5>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $progreso['porcentaje_liquidacion'] ?>%" 
                                 aria-valuenow="<?= $progreso['porcentaje_liquidacion'] ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                                <?= number_format($progreso['porcentaje_liquidacion'], 2) ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de amortización -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tabla de Amortización</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Pago</th>
                            <th>Fecha Vencimiento</th>
                            <th>Saldo Inicial</th>
                            <th>Capital</th>
                            <th>Interés</th>
                            <th>Pago Total</th>
                            <th>Saldo Final</th>
                            <th>Pagado</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tabla_amortizacion as $pago): ?>
                        <tr class="<?= $pago->estatus == 'vencida' ? 'table-danger' : ($pago->estatus == 'pagada' ? 'table-success' : '') ?>">
                            <td><?= $pago->numero_pago ?></td>
                            <td><?= date('d/m/Y', strtotime($pago->fecha_vencimiento)) ?></td>
                            <td>$<?= number_format($pago->saldo_inicial, 2) ?></td>
                            <td>$<?= number_format($pago->capital, 2) ?></td>
                            <td>$<?= number_format($pago->interes, 2) ?></td>
                            <td>$<?= number_format($pago->monto_total, 2) ?></td>
                            <td>$<?= number_format($pago->saldo_final, 2) ?></td>
                            <td>$<?= number_format($pago->monto_pagado, 2) ?></td>
                            <td>
                                <span class="badge badge-<?= $pago->estatus == 'pagada' ? 'success' : ($pago->estatus == 'vencida' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($pago->estatus) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Historial de cambios -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historial de Cambios</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($historial as $item): ?>
                    <div class="time-label">
                        <span class="bg-primary"><?= date('d/m/Y', strtotime($item->created_at)) ?></span>
                    </div>
                    <div>
                        <i class="fas fa-user bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> <?= date('H:i', strtotime($item->created_at)) ?>
                            </span>
                            <h3 class="timeline-header"><?= ucfirst($item->accion) ?></h3>
                            <div class="timeline-body">
                                <?= $item->descripcion ?>
                                <br>
                                <small class="text-muted">Por: <?= $item->nombre_usuario ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Motivo y observaciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Motivo y Observaciones</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Motivo de la Reestructuración</h5>
                        <p><?= nl2br($reestructuracion->motivo) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Observaciones</h5>
                        <p><?= $reestructuracion->observaciones ? nl2br($reestructuracion->observaciones) : 'Sin observaciones' ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Resaltar pagos vencidos
    $('tr.table-danger').each(function() {
        $(this).find('td').css('font-weight', 'bold');
    });
});
</script>
<?= $this->endSection() ?>