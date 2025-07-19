<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Detalle de Venta<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Detalle de Venta <?= esc($venta->folio_venta) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas') ?>">Ventas</a></li>
<li class="breadcrumb-item active">Detalle</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- Información principal -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Información de la Venta
                </h3>
                <div class="card-tools">
                    <?php if ($venta->tipo_venta === 'financiado'): ?>
                        <a href="<?= site_url('/admin/estado-cuenta/venta/' . $venta->id) ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-chart-line mr-1"></i>
                            Estado de Cuenta
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($venta->estaActiva()): ?>
                        <a href="<?= site_url('/admin/ventas/edit/' . $venta->id) ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit mr-1"></i>
                            Editar
                        </a>
                        <a href="<?= site_url('/admin/ventas/cancelar/' . $venta->id) ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Estás seguro de cancelar esta venta?')">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="140">Folio:</th>
                                <td><span class="badge badge-secondary"><?= esc($venta->folio_venta) ?></span></td>
                            </tr>
                            <tr>
                                <th>Fecha:</th>
                                <td><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></td>
                            </tr>
                            <tr>
                                <th>Tipo:</th>
                                <td>
                                    <?php if ($venta->tipo_venta === 'contado'): ?>
                                        <span class="badge badge-success">Contado</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Financiado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Estatus:</th>
                                <td>
                                    <?php
                                    $badgeClass = match($venta->estatus_venta) {
                                        'activa' => 'badge-success',
                                        'liquidada' => 'badge-primary',
                                        'cancelada' => 'badge-danger',
                                        'juridico' => 'badge-warning',
                                        default => 'badge-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($venta->estatus_venta) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Plan:</th>
                                <td><?= esc($plan->nombre_plan) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="140">Precio Lista:</th>
                                <td class="text-right">$<?= number_format($venta->precio_lista, 2) ?></td>
                            </tr>
                            <?php if ($venta->descuento_aplicado > 0): ?>
                                <tr>
                                    <th>Descuento:</th>
                                    <td class="text-right text-warning">-$<?= number_format($venta->descuento_aplicado, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th><strong>Precio Final:</strong></th>
                                <td class="text-right"><strong>$<?= number_format($venta->precio_venta_final, 2) ?></strong></td>
                            </tr>
                            <?php if (!empty($venta->motivo_descuento)): ?>
                                <tr>
                                    <th>Motivo Descuento:</th>
                                    <td class="text-muted"><?= esc($venta->motivo_descuento) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($venta->contrato_generado): ?>
                                <tr>
                                    <th>Contrato:</th>
                                    <td>
                                        <span class="badge badge-success">Generado</span><br>
                                        <small><?= esc($venta->numero_contrato) ?></small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php if (!empty($venta->observaciones)): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Observaciones:</h6>
                            <p class="text-muted"><?= nl2br(esc($venta->observaciones)) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Panel lateral -->
    <div class="col-md-4">
        <!-- Información del cliente -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user mr-2"></i>
                    Cliente
                </h3>
            </div>
            <div class="card-body">
                <p><strong><?= esc($cliente->nombres) ?> <?= esc($cliente->apellido_paterno) ?> <?= esc($cliente->apellido_materno) ?></strong></p>
                <?php if (!empty($cliente->email)): ?>
                    <p><i class="fas fa-envelope mr-2"></i><?= esc($cliente->email) ?></p>
                <?php endif; ?>
                <?php if (!empty($cliente->telefono)): ?>
                    <p><i class="fas fa-phone mr-2"></i><?= esc($cliente->telefono) ?></p>
                <?php endif; ?>
                <a href="<?= site_url('/admin/clientes/' . $cliente->id) ?>" class="btn btn-sm btn-outline-primary">
                    Ver Perfil Completo
                </a>
            </div>
        </div>

        <!-- Información del lote -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    Lote
                </h3>
            </div>
            <div class="card-body">
                <p><strong><?= esc($lote->clave) ?></strong></p>
                <p><i class="fas fa-expand-arrows-alt mr-2"></i><?= number_format($lote->area, 2) ?> m²</p>
                <p><i class="fas fa-dollar-sign mr-2"></i>$<?= number_format($lote->precio_total, 2) ?></p>
                <a href="<?= site_url('/admin/lotes/' . $lote->id) ?>" class="btn btn-sm btn-outline-primary">
                    Ver Detalles del Lote
                </a>
            </div>
        </div>

        <!-- Información del vendedor -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-tie mr-2"></i>
                    Vendedor
                </h3>
            </div>
            <div class="card-body">
                <p><strong><?= esc($vendedor->username) ?></strong></p>
                <?php if (!empty($vendedor->email)): ?>
                    <p><i class="fas fa-envelope mr-2"></i><?= esc($vendedor->email) ?></p>
                <?php endif; ?>
                <a href="<?= site_url('/admin/usuarios/' . $vendedor->id) ?>" class="btn btn-sm btn-outline-primary">
                    Ver Perfil
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Historial y documentos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="ventaTabs" role="tablist">
                    <?php if ($venta->tipo_venta === 'financiado'): ?>
                        <li class="nav-item">
                            <a class="nav-link active" id="financiamiento-tab" data-toggle="tab" href="#financiamiento" role="tab">
                                <i class="fas fa-calculator mr-1"></i>
                                Plan de Financiamiento
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="estado-cuenta-tab" data-toggle="tab" href="#estado-cuenta" role="tab">
                                <i class="fas fa-chart-line mr-1"></i>
                                Estado de Cuenta
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $venta->tipo_venta === 'contado' ? 'active' : '' ?>" id="documentos-tab" data-toggle="tab" href="#documentos" role="tab">
                            <i class="fas fa-file-alt mr-1"></i>
                            Documentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="historial-tab" data-toggle="tab" href="#historial" role="tab">
                            <i class="fas fa-history mr-1"></i>
                            Historial
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="ventaTabsContent">
                    <?php if ($venta->tipo_venta === 'financiado'): ?>
                        <div class="tab-pane fade show active" id="financiamiento" role="tabpanel">
                            <!-- Información del Plan -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h5 class="card-title">Información del Plan</h5>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Plan Seleccionado:</strong> <?= esc($plan->nombre_plan) ?></p>
                                            <p><strong>Descripción:</strong> <?= esc($plan->descripcion) ?></p>
                                            <?php if (!empty($configuracion_financiera)): ?>
                                                <?php if (!isset($plan->promocion_cero_enganche) || !$plan->promocion_cero_enganche): ?>
                                                <p><strong>Enganche:</strong> <?= $configuracion_financiera->porcentaje_enganche ?>%</p>
                                                <?php endif; ?>
                                                <p><strong>Plazo:</strong> <?= $configuracion_financiera->plazo_meses ?> meses</p>
                                                <p><strong>Tasa Anual:</strong> <?= $configuracion_financiera->tasa_interes_anual ?>%</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card card-outline card-success">
                                        <div class="card-header">
                                            <h5 class="card-title">Resumen Financiero</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!empty($resumen_financiero)): ?>
                                                <p><strong>Precio Total:</strong> $<?= number_format($venta->precio_venta_final, 2) ?></p>
                                                <?php if (!isset($plan->promocion_cero_enganche) || !$plan->promocion_cero_enganche): ?>
                                                <p><strong>Enganche:</strong> $<?= number_format($resumen_financiero['monto_enganche'], 2) ?></p>
                                                <?php endif; ?>
                                                <p><strong>Monto a Financiar:</strong> $<?= number_format($resumen_financiero['monto_financiar'], 2) ?></p>
                                                <p><strong>Pago Mensual:</strong> $<?= number_format($resumen_financiero['pago_mensual'], 2) ?></p>
                                                <p><strong>Total a Pagar:</strong> $<?= number_format($resumen_financiero['total_pagar'], 2) ?></p>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    La tabla de amortización aún no ha sido generada.
                                                    <a href="<?= site_url('/admin/ventas/generarAmortizacion/' . $venta->id) ?>" 
                                                       class="btn btn-primary btn-sm ml-2">
                                                        <i class="fas fa-calculator"></i> Generar Ahora
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Acciones Rápidas -->
                            <?php if (!empty($resumen_financiero)): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-light">
                                        <h6><i class="fas fa-bolt mr-2"></i>Acciones Rápidas</h6>
                                        <div class="btn-group" role="group">
                                            <a href="<?= site_url('/admin/estado-cuenta/venta/' . $venta->id) ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-chart-line"></i> Ver Estado Completo
                                            </a>
                                            
                                            <?php if (!empty($proxima_mensualidad)): ?>
                                            <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $proxima_mensualidad->id) ?>" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-dollar-sign"></i> Aplicar Pago
                                            </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?= site_url('/admin/mensualidades?folio_venta=' . $venta->folio_venta) ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-list"></i> Ver Mensualidades
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Nueva Pestaña: Estado de Cuenta -->
                        <div class="tab-pane fade" id="estado-cuenta" role="tabpanel">
                            <?php if (!empty($resumen_financiero)): ?>
                            <!-- Indicadores Financieros -->
                            <div class="row">
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>$<?= number_format($resumen_estado_cuenta['total_pagado'] ?? 0, 0) ?></h3>
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
                                            <h3>$<?= number_format($resumen_estado_cuenta['saldo_pendiente'] ?? 0, 0) ?></h3>
                                            <p>Saldo Pendiente</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3><?= number_format($resumen_estado_cuenta['porcentaje_liquidacion'] ?? 0, 1) ?>%</h3>
                                            <p>% Liquidación</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-chart-pie"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3><?= count($mensualidades_vencidas ?? []) ?></h3>
                                            <p>Vencidas</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Próximas Mensualidades -->
                            <?php if (!empty($proximas_mensualidades)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <i class="fas fa-calendar-alt mr-2"></i>
                                                Próximas Mensualidades
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Fecha Vencimiento</th>
                                                            <th>Monto</th>
                                                            <th>Estado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (array_slice($proximas_mensualidades, 0, 5) as $mensualidad): ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-secondary">
                                                                    #<?= $mensualidad->numero_pago ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                                                <?php if ($mensualidad->dias_atraso > 0): ?>
                                                                    <br><small class="text-danger">
                                                                        <?= $mensualidad->dias_atraso ?> días vencida
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
                                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $mensualidad->id) ?>" 
                                                                   class="btn btn-success btn-xs">
                                                                    <i class="fas fa-dollar-sign"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                                <a href="<?= site_url('/admin/mensualidades/detalle/' . $mensualidad->id) ?>" 
                                                                   class="btn btn-info btn-xs">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div class="text-center mt-3">
                                                <a href="<?= site_url('/admin/estado-cuenta/venta/' . $venta->id) ?>" 
                                                   class="btn btn-primary">
                                                    <i class="fas fa-chart-line"></i> Ver Estado de Cuenta Completo
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Últimos Pagos -->
                            <?php if (!empty($ultimos_pagos)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <i class="fas fa-history mr-2"></i>
                                                Últimos Pagos Registrados
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Folio</th>
                                                            <th>Mensualidad</th>
                                                            <th>Forma Pago</th>
                                                            <th>Monto</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (array_slice($ultimos_pagos, 0, 5) as $pago): ?>
                                                        <tr>
                                                            <td><?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></td>
                                                            <td><strong><?= $pago->folio_pago ?></strong></td>
                                                            <td>
                                                                <span class="badge badge-info">
                                                                    #<?= $pago->numero_mensualidad ?? 'N/A' ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-secondary">
                                                                    <?= ucfirst($pago->forma_pago) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <strong class="text-success">
                                                                    $<?= number_format($pago->monto_pago, 0) ?>
                                                                </strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-success">Aplicado</span>
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
                            
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle mr-2"></i>Tabla de Amortización No Generada</h5>
                                <p>Para ver el estado de cuenta, primero debe generar la tabla de amortización de esta venta financiada.</p>
                                <a href="<?= site_url('/admin/ventas/generarAmortizacion/' . $venta->id) ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-calculator"></i> Generar Tabla de Amortización
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="tab-pane fade <?= $venta->tipo_venta === 'contado' ? 'show active' : '' ?>" id="documentos" role="tabpanel">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            La gestión de documentos será implementada en una fase posterior.
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="historial" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            El historial de cambios será implementado en una fase posterior.
                        </div>
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
    // Manejar tabs
    $('#ventaTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Auto-refresh para la pestaña de estado de cuenta cada 2 minutos
    setInterval(function() {
        if ($('#estado-cuenta-tab').hasClass('active')) {
            // Solo refrescar si está en la pestaña de estado de cuenta
            location.reload();
        }
    }, 120000); // 2 minutos
    
    // Cargar datos de estado de cuenta dinámicamente cuando se activa la pestaña
    $('#estado-cuenta-tab').on('shown.bs.tab', function (e) {
        // Opcional: Cargar datos vía AJAX si es necesario
        console.log('Tab Estado de Cuenta activada');
    });
});
</script>
<?= $this->endSection() ?>