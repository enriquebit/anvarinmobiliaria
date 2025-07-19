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
                    <?= $subtitle ?>
                </p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/estado-cuenta') ?>">Estado de Cuenta</a></li>
                    <li class="breadcrumb-item active">Venta Completa</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Selector de Otras Ventas (si las hay) -->
        <?php if (!empty($otras_ventas) && count($otras_ventas) > 0): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-home"></i>
                            Cliente con Múltiples Propiedades
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Este cliente tiene otras propiedades. Seleccione para ver su estado de cuenta:</p>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success active">
                                <i class="fas fa-check"></i> 
                                Actual: <?= $venta['clave_lote'] ?> - <?= $venta['nombre_proyecto'] ?>
                            </button>
                            <?php foreach($otras_ventas as $otra): ?>
                            <a href="<?= site_url("/admin/estado-cuenta/venta/{$otra['id']}") ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-arrow-right"></i>
                                <?= $otra['clave_lote'] ?> - <?= $otra['nombre_proyecto'] ?>
                                <span class="badge badge-<?= $otra['estatus_venta'] === 'liquidada' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($otra['estatus_venta']) ?>
                                </span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Información General de la Venta -->
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-contract"></i>
                            Información del Contrato
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Folio:</dt>
                            <dd class="col-sm-7"><strong><?= $venta['folio_venta'] ?></strong></dd>
                            
                            <dt class="col-sm-5">Cliente:</dt>
                            <dd class="col-sm-7"><?= esc($venta['nombre_cliente']) ?></dd>
                            
                            <dt class="col-sm-5">Fecha Venta:</dt>
                            <dd class="col-sm-7"><?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></dd>
                            
                            <dt class="col-sm-5">Lote:</dt>
                            <dd class="col-sm-7">
                                <strong><?= $venta['clave_lote'] ?></strong>
                                <br><small class="text-muted"><?= $venta['area_lote'] ?>m² - <?= $venta['nombre_proyecto'] ?></small>
                            </dd>
                            
                            <dt class="col-sm-5">Vendedor:</dt>
                            <dd class="col-sm-7"><?= esc($venta['nombre_vendedor']) ?></dd>
                            
                            <dt class="col-sm-5">Plan:</dt>
                            <dd class="col-sm-7">
                                <?= $venta['nombre_plan'] ?? 'Plan Personalizado' ?>
                                <?php if($venta['plazo_meses']): ?>
                                <br><small class="text-info"><?= $venta['plazo_meses'] ?> meses</small>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-5">Estado:</dt>
                            <dd class="col-sm-7">
                                <?php 
                                $badgeColor = match($venta['estatus_venta']) {
                                    'apartado' => 'warning',
                                    'pagando' => 'info', 
                                    'liquidada' => 'success',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge badge-<?= $badgeColor ?>">
                                    <?= ucfirst($venta['estatus_venta']) ?>
                                </span>
                            </dd>
                        </dl>
                        
                        <!-- Botones de Acción -->
                        <div class="text-center mt-3">
                            <a href="<?= site_url('/admin/estado-cuenta/generar/' . $venta['id']) ?>" 
                               class="btn btn-outline-secondary btn-sm" 
                               target="_blank"
                               title="Generar estado de cuenta imprimible">
                                <i class="fas fa-file-pdf mr-1"></i>
                                Generar PDF
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Resumen Financiero -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calculator"></i>
                            Resumen Financiero
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($historial_completo['resumen_financiero'])): ?>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-success">
                                        $<?= number_format($historial_completo['resumen_financiero']['precio_venta'], 2) ?>
                                    </h5>
                                    <span class="description-text">VALOR CONTRATO</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-<?= $historial_completo['resumen_financiero']['es_cero_enganche'] ? 'success' : 'warning' ?>">
                                        <?php if($historial_completo['resumen_financiero']['es_cero_enganche']): ?>
                                            CERO ENGANCHE
                                        <?php else: ?>
                                            $<?= number_format($historial_completo['resumen_financiero']['enganche_requerido'], 2) ?>
                                        <?php endif; ?>
                                    </h5>
                                    <span class="description-text">
                                        <?php if($historial_completo['resumen_financiero']['es_cero_enganche']): ?>
                                            PROMOCIÓN ESPECIAL
                                        <?php else: ?>
                                            ENGANCHE (<?= $historial_completo['resumen_financiero']['porcentaje_anticipo'] ?>%)
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-info">
                                        $<?= number_format($historial_completo['resumen_financiero']['total_pagado'], 2) ?>
                                    </h5>
                                    <span class="description-text">TOTAL PAGADO</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-warning">
                                        $<?= number_format($historial_completo['resumen_financiero']['saldo_total_pendiente'], 2) ?>
                                    </h5>
                                    <span class="description-text">SALDO PENDIENTE</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block">
                                    <h5 class="description-header text-primary">
                                        <?= number_format($historial_completo['resumen_financiero']['porcentaje_avance'], 1) ?>%
                                    </h5>
                                    <span class="description-text">LIQUIDADO</span>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Cargando información financiera...
                        </div>
                        <?php endif; ?>
                        
                        <!-- Barra de Progreso -->
                        <?php if(isset($historial_completo['resumen_financiero'])): ?>
                        <div class="progress mt-3">
                            <?php 
                            $porcentaje = $historial_completo['resumen_financiero']['porcentaje_avance'];
                            $colorBarra = $porcentaje >= 80 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                            ?>
                            <div class="progress-bar bg-<?= $colorBarra ?>" style="width: <?= $porcentaje ?>%">
                                <?= number_format($porcentaje, 1) ?>% Liquidado
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Estado de Pagos -->
                        <?php if(isset($historial_completo['resumen_financiero'])): ?>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if (!isset($venta['promocion_cero_enganche']) || !$venta['promocion_cero_enganche']): ?>
                                    <p class="mb-1">
                                        <strong>Enganche:</strong> 
                                        <span class="badge badge-<?= $historial_completo['resumen_financiero']['saldo_enganche'] > 0 ? 'warning' : 'success' ?>">
                                            <?= $historial_completo['resumen_financiero']['saldo_enganche'] > 0 ? 'Pendiente' : 'Completado' ?>
                                        </span>
                                    </p>
                                    <?php endif; ?>
                                    <p class="mb-1">
                                        <strong>Estatus Financiero:</strong> 
                                        <span class="badge badge-<?= $historial_completo['resumen_financiero']['al_corriente'] ? 'success' : 'danger' ?>">
                                            <?= $historial_completo['resumen_financiero']['estatus_financiero'] ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Próxima Acción:</strong><br>
                                        <small class="text-muted">
                                            <?= $historial_completo['resumen_financiero']['proxima_accion'] ?>
                                        </small>
                                    </p>
                                    <?php if($historial_completo['resumen_financiero']['dias_atraso'] > 0): ?>
                                    <p class="mb-1">
                                        <strong>Días de Atraso:</strong> 
                                        <span class="badge badge-danger"><?= $historial_completo['resumen_financiero']['dias_atraso'] ?> días</span>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Card de Próxima Fecha de Pago -->
                <?php if(!empty($tabla_amortizacion)): ?>
                <?php
                    // Buscar la próxima mensualidad pendiente
                    $proximoPago = null;
                    foreach($tabla_amortizacion as $pago) {
                        if($pago['estatus'] === 'pendiente') {
                            $proximoPago = $pago;
                            break;
                        }
                    }
                ?>
                <?php if($proximoPago): ?>
                <div class="card card-info mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Próxima Fecha de Pago
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h5 class="mb-1">Mensualidad #<?= $proximoPago['numero_pago'] ?></h5>
                                <p class="text-muted mb-0">Vence: <?= date('d/m/Y', strtotime($proximoPago['fecha_vencimiento'])) ?></p>
                            </div>
                            <div class="col-md-4">
                                <h4 class="text-primary mb-0">$<?= number_format($proximoPago['monto_total'], 2) ?></h4>
                                <small class="text-muted">Monto a pagar</small>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= site_url('/admin/pagos/procesar-mensualidad/' . $venta['id']) ?>" class="btn btn-primary mr-2">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    Pagar Mensualidad
                                </a>
                                <a href="<?= site_url('/admin/estado-cuenta/generar/' . $venta['id']) ?>" 
                                   class="btn btn-secondary" 
                                   target="_blank"
                                   title="Generar estado de cuenta imprimible">
                                    <i class="fas fa-file-pdf mr-2"></i>
                                    Generar Estado de Cuenta
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tracking de Pagos y Timeline -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="apartado-tab" data-toggle="pill" href="#apartado" role="tab">
                                    <i class="fas fa-handshake mr-2"></i>
                                    Apartado / Enganche
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="financiamiento-tab" data-toggle="pill" href="#financiamiento" role="tab">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    Financiamiento
                                </a>
                            </li>
                            <?php if($mostrar_toggle_amortizacion && !empty($tabla_amortizacion)): ?>
                            <li class="nav-item">
                                <a class="nav-link" id="amortizacion-tab" data-toggle="pill" href="#amortizacion" role="tab">
                                    <i class="fas fa-table mr-2"></i>
                                    Tabla de Amortización
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" id="timeline-tab" data-toggle="pill" href="#timeline" role="tab">
                                    <i class="fas fa-history mr-2"></i>
                                    Timeline
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            
                            <!-- Tab: Apartado / Enganche -->
                            <div class="tab-pane fade show active" id="apartado" role="tabpanel">
                                <h5>Tracking de Apartado y Enganche</h5>
                                
                                <?php if(isset($historial_completo['historial_apartado'])): ?>
                                <!-- Información de Anticipos -->
                                <?php if(!empty($historial_completo['historial_apartado']['anticipos'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-handshake"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Anticipos</span>
                                                <span class="info-box-number">$<?= number_format($historial_completo['historial_apartado']['total_anticipos'], 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-<?= $historial_completo['historial_apartado']['enganche_completado'] ? 'success' : 'warning' ?>">
                                                <i class="fas fa-<?= $historial_completo['historial_apartado']['enganche_completado'] ? 'check' : 'clock' ?>"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Estado Enganche</span>
                                                <span class="info-box-number">
                                                    <?= $historial_completo['historial_apartado']['enganche_completado'] ? 'Completado' : 'Pendiente' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lista de Anticipos -->
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($historial_completo['historial_apartado']['anticipos'] as $anticipo): ?>
                                            <tr>
                                                <td><span class="badge badge-secondary"><?= $anticipo->folio_apartado ?></span></td>
                                                <td><?= date('d/m/Y', strtotime($anticipo->fecha_apartado)) ?></td>
                                                <td><strong>$<?= number_format($anticipo->monto_apartado, 2) ?></strong></td>
                                                <td>
                                                    <span class="badge badge-<?= $anticipo->estatus_apartado === 'aplicado' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($anticipo->estatus_apartado) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-info btn-sm" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No se han registrado anticipos para esta venta.
                                </div>
                                <?php endif; ?>

                                <!-- Todos los Pagos Registrados -->
                                <?php if(!empty($historial_completo['historial_apartado']['todos_pagos'])): ?>
                                <div class="mt-4">
                                    <h6>Historial de Pagos</h6>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Folio</th>
                                                    <th>Fecha</th>
                                                    <th>Concepto</th>
                                                    <th>Forma Pago</th>
                                                    <th>Monto</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($historial_completo['historial_apartado']['todos_pagos'] as $pago): ?>
                                                <tr>
                                                    <td><span class="badge badge-info"><?= $pago->folio ?></span></td>
                                                    <td><?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></td>
                                                    <td><?= $pago->concepto ?></td>
                                                    <td><?= $pago->forma_pago ?? 'N/A' ?></td>
                                                    <td><strong>$<?= number_format($pago->monto, 2) ?></strong></td>
                                                    <td>
                                                        <span class="badge badge-<?= $pago->estado === 'APLICADO' ? 'success' : 'warning' ?>">
                                                            <?= $pago->estado ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?= site_url('/admin/ingresos/comprobante/' . $pago->id) ?>" 
                                                           class="btn btn-info btn-sm" 
                                                           target="_blank"
                                                           title="Ver comprobante">
                                                            <i class="fas fa-receipt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Liquidación de Enganche -->
                                <?php if($historial_completo['historial_apartado']['liquidacion']): ?>
                                <div class="mt-4">
                                    <h6>Liquidación de Enganche</h6>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>Enganche liquidado el <?= date('d/m/Y', strtotime($historial_completo['historial_apartado']['liquidacion']->fecha_aplicacion)) ?></strong>
                                        <br>
                                        Monto: $<?= number_format($historial_completo['historial_apartado']['liquidacion']->monto, 2) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No hay información de apartado disponible.
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Financiamiento -->
                            <div class="tab-pane fade" id="financiamiento" role="tabpanel">
                                <h5>Plan de Financiamiento</h5>
                                
                                <!-- Detalles del Plan -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-file-contract mr-2"></i>
                                                    Detalles del Plan
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <dl class="row">
                                                    <dt class="col-sm-5">Plan:</dt>
                                                    <dd class="col-sm-7"><?= $venta['nombre_plan'] ?? 'No especificado' ?></dd>
                                                    
                                                    <dt class="col-sm-5">Tipo:</dt>
                                                    <dd class="col-sm-7">
                                                        <span class="badge badge-<?= $venta['promocion_cero_enganche'] ? 'success' : 'primary' ?>">
                                                            <?= $venta['promocion_cero_enganche'] ? 'Cero Enganche' : 'Tradicional' ?>
                                                            <?= strtoupper($venta['tipo_financiamiento'] ?? 'MCI') ?>
                                                        </span>
                                                    </dd>
                                                    
                                                    <dt class="col-sm-5">Anticipo:</dt>
                                                    <dd class="col-sm-7"><?= number_format($venta['porcentaje_anticipo'] ?? 0, 1) ?>%</dd>
                                                    
                                                    <dt class="col-sm-5">Tasa Interés:</dt>
                                                    <dd class="col-sm-7"><?= number_format($venta['tasa_interes_anual'] ?? 0, 2) ?>% anual</dd>
                                                    
                                                    <dt class="col-sm-5">Plazo:</dt>
                                                    <dd class="col-sm-7">
                                                        <?php if($venta['tipo_financiamiento'] === 'msi'): ?>
                                                            <?= $venta['meses_sin_intereses'] ?? 0 ?> meses sin intereses
                                                        <?php else: ?>
                                                            <?= $venta['plazo_meses'] ?? 0 ?> meses
                                                        <?php endif; ?>
                                                    </dd>
                                                    
                                                    <dt class="col-sm-5">Tasa Mora:</dt>
                                                    <dd class="col-sm-7"><?= number_format($venta['tasa_mora_mensual'] ?? 0, 2) ?>% mensual</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-percentage mr-2"></i>
                                                    Comisiones y Cargos
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <dl class="row">
                                                    <dt class="col-sm-6">Comisión Apertura:</dt>
                                                    <dd class="col-sm-6"><?= number_format($venta['comision_apertura'] ?? 0, 2) ?>%</dd>
                                                    
                                                    <dt class="col-sm-6">Comisión Cobranza:</dt>
                                                    <dd class="col-sm-6"><?= number_format($venta['comision_cobranza'] ?? 0, 2) ?>%</dd>
                                                </dl>
                                                
                                                <?php if(!empty($venta['descripcion_plan'])): ?>
                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <strong>Descripción:</strong><br>
                                                        <?= $venta['descripcion_plan'] ?>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if(isset($historial_completo['historial_financiamiento']) && $historial_completo['historial_financiamiento']['cuenta_activa']): ?>
                                <!-- Información de la Cuenta -->
                                <h6>Resumen Financiero</h6>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-primary"><i class="fas fa-credit-card"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Saldo Inicial</span>
                                                <span class="info-box-number">$<?= number_format($historial_completo['historial_financiamiento']['cuenta']->saldo_inicial ?? 0, 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-minus-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Saldo Actual</span>
                                                <span class="info-box-number">$<?= number_format($historial_completo['historial_financiamiento']['cuenta']->saldo_actual ?? 0, 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Pagado</span>
                                                <span class="info-box-number">$<?= number_format(($historial_completo['historial_financiamiento']['cuenta']->saldo_inicial ?? 0) - ($historial_completo['historial_financiamiento']['cuenta']->saldo_actual ?? 0), 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información de Progreso de Pagos -->
                                <?php 
                                // Calcular progreso de pagos
                                $totalMensualidades = count($tabla_amortizacion);
                                $mensualidadesPagadas = 0;
                                $proximaMensualidad = null;
                                
                                foreach($tabla_amortizacion as $pago) {
                                    if ($pago['estado_tracking'] === 'Aplicado') {
                                        $mensualidadesPagadas++;
                                    } elseif ($pago['estado_tracking'] !== 'Aplicado' && !$proximaMensualidad) {
                                        $proximaMensualidad = $pago;
                                    }
                                }
                                
                                $porcentajeProgreso = $totalMensualidades > 0 ? round(($mensualidadesPagadas / $totalMensualidades) * 100, 1) : 0;
                                ?>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-calendar-check"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Progreso</span>
                                                <span class="info-box-number"><?= $mensualidadesPagadas ?> de <?= $totalMensualidades ?></span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-info" style="width: <?= $porcentajeProgreso ?>%"></div>
                                                </div>
                                                <span class="progress-description"><?= $porcentajeProgreso ?>% completado</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-<?= $proximaMensualidad ? ($proximaMensualidad['estado_tracking'] === 'Vencido' ? 'danger' : ($proximaMensualidad['estado_tracking'] === 'Próximo vencimiento' ? 'warning' : 'primary')) : 'success' ?>">
                                                <i class="fas fa-<?= $proximaMensualidad ? 'clock' : 'trophy' ?>"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Próxima Mensualidad</span>
                                                <?php if ($proximaMensualidad): ?>
                                                    <span class="info-box-number">#<?= $proximaMensualidad['numero_pago'] ?></span>
                                                    <span class="info-box-description">
                                                        <?= date('d/m/Y', strtotime($proximaMensualidad['fecha_vencimiento'])) ?><br>
                                                        <strong>$<?= number_format($proximaMensualidad['monto_total'], 0) ?></strong>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="info-box-number">¡Completado!</span>
                                                    <span class="info-box-description">Cuenta liquidada</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">% Liquidación</span>
                                                <span class="info-box-number"><?= $porcentajeProgreso ?>%</span>
                                                <span class="info-box-description">
                                                    Quedan <?= $totalMensualidades - $mensualidadesPagadas ?> pagos
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Movimientos de la Cuenta -->
                                <?php if(!empty($historial_completo['historial_financiamiento']['movimientos'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Concepto</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($historial_completo['historial_financiamiento']['movimientos'] as $movimiento): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($movimiento->fecha_aplicacion)) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $movimiento->tipo_concepto === 'mensualidad' ? 'success' : 'info' ?>">
                                                        <?= ucfirst($movimiento->tipo_concepto) ?>
                                                    </span>
                                                </td>
                                                <td><strong>$<?= number_format($movimiento->monto, 2) ?></strong></td>
                                                <td>
                                                    <span class="badge badge-success">Aplicado</span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No se han registrado movimientos en la cuenta de financiamiento.
                                </div>
                                <?php endif; ?>

                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    No hay cuenta de financiamiento activa. El enganche debe estar completado para activar el financiamiento.
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Tabla de Amortización -->
                            <?php if($mostrar_toggle_amortizacion && !empty($tabla_amortizacion)): ?>
                            <div class="tab-pane fade" id="amortizacion" role="tabpanel">
                                <h5>Tabla de Amortización</h5>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Fecha Venc.</th>
                                                <th>Capital</th>
                                                <th>Interés</th>
                                                <th>Pago Total</th>
                                                <th>Pagado</th>
                                                <th>Pendiente</th>
                                                <th>Estado</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($tabla_amortizacion as $pago): ?>
                                            <tr class="<?= $pago['estado_tracking'] === 'Vencido' ? 'table-danger' : ($pago['estado_tracking'] === 'Próximo vencimiento' ? 'table-warning' : ($pago['estado_tracking'] === 'Aplicado' ? 'table-success' : '')) ?>">
                                                <td><?= $pago['numero_pago'] ?></td>
                                                <td><?= date('d/m/Y', strtotime($pago['fecha_vencimiento'])) ?></td>
                                                <td>$<?= number_format($pago['capital'], 0) ?></td>
                                                <td>$<?= number_format($pago['interes'], 0) ?></td>
                                                <td><strong>$<?= number_format($pago['monto_total'], 0) ?></strong></td>
                                                <td class="text-success">$<?= number_format($pago['monto_pagado'], 0) ?></td>
                                                <td class="text-danger">$<?= number_format($pago['saldo_pendiente'], 0) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $pago['estado_tracking'] === 'Aplicado' ? 'success' : ($pago['estado_tracking'] === 'Vencido' ? 'danger' : 'warning') ?>">
                                                        <?= $pago['estado_tracking'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?= $pago['porcentaje_pagado'] >= 100 ? 'success' : 'warning' ?>" 
                                                             style="width: <?= min($pago['porcentaje_pagado'], 100) ?>%">
                                                            <?= $pago['porcentaje_pagado'] ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Tab: Timeline -->
                            <div class="tab-pane fade" id="timeline" role="tabpanel">
                                <h5>Timeline de Pagos</h5>
                                
                                <?php if(isset($historial_completo['timeline_pagos']) && !empty($historial_completo['timeline_pagos'])): ?>
                                <div class="timeline">
                                    <?php foreach($historial_completo['timeline_pagos'] as $evento): ?>
                                    <div class="time-label">
                                        <span class="bg-<?= $evento['color'] ?>">
                                            <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <i class="<?= $evento['icono'] ?> bg-<?= $evento['color'] ?>"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header"><?= $evento['concepto'] ?></h3>
                                            <div class="timeline-body">
                                                <strong>$<?= number_format($evento['monto'], 2) ?></strong>
                                                <br>
                                                <span class="badge badge-<?= $evento['color'] ?>"><?= ucfirst($evento['estado']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No hay eventos en el timeline de pagos.
                                </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial de Pagos -->
        <?php if(!empty($historial_pagos)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Historial de Pagos (<?= count($historial_pagos) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" onclick="window.location.href='<?= site_url('/admin/pagos/venta/' . $venta['id']) ?>'">
                                <i class="fas fa-plus"></i>
                                Nuevo Pago
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaHistorialPagos">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Folio</th>
                                        <th>Concepto</th>
                                        <th>Forma de Pago</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($historial_pagos as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                                        <td><strong><?= $pago['folio_pago'] ?? 'N/A' ?></strong></td>
                                        <td>
                                            <?= esc($pago['concepto_nombre'] ?? 'Sin concepto') ?>
                                            <?php if($pago['numero_mensualidad'] ?? false): ?>
                                            <br><small class="text-muted">Mensualidad #<?= $pago['numero_mensualidad'] ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= ucfirst($pago['forma_pago_nombre'] ?? 'Efectivo') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                $<?= number_format($pago['total_ingreso'], 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">Aplicado</span>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('/admin/ingresos/comprobante/' . $pago['id']) ?>" 
                                               class="btn btn-info btn-xs" title="Ver Comprobante">
                                                <i class="fas fa-receipt"></i>
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
        
        <!-- Acciones Disponibles del Helper -->
        <?php if(isset($historial_completo['acciones_disponibles']) && !empty($historial_completo['acciones_disponibles'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs mr-2"></i>
                            Acciones Disponibles
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <?php foreach($historial_completo['acciones_disponibles'] as $accion): ?>
                            <a href="<?= $accion['url'] ?>" class="btn <?= $accion['clase'] ?>">
                                <i class="<?= $accion['icono'] ?> mr-2"></i>
                                <?= $accion['texto'] ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Acciones Rápidas -->
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tools"></i>
                            Acciones Adicionales
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="<?= site_url('/admin/estado-cuenta/imprimir/' . $venta['id']) ?>" class="btn btn-info">
                                <i class="fas fa-print"></i> Imprimir Estado
                            </a>
                            <a href="<?= site_url('/admin/ventas/show/' . $venta['id']) ?>" class="btn btn-primary">
                                <i class="fas fa-file-contract"></i> Ver Contrato Completo
                            </a>
                            <a href="<?= site_url('/admin/estado-cuenta') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Buscador
                            </a>
                        </div>
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
    // Inicializar DataTables
    $('#tablaAmortizacion').DataTable({
        "responsive": true,
        "pageLength": 25,
        "order": [[ 0, "asc" ]], // Ordenar por número de mensualidad
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
    
    $('#tablaHistorialPagos').DataTable({
        "responsive": true,
        "pageLength": 10,
        "order": [[ 0, "desc" ]], // Más recientes primero
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
    
    // Toggle para mostrar/ocultar tabla de amortización
    $('#toggleAmortizacion').click(function() {
        var container = $('#tablaAmortizacionContainer');
        var icon = $('#iconToggle');
        var text = $('#textToggle');
        
        if (container.is(':visible')) {
            container.slideUp();
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
            text.text('Mostrar Tabla');
        } else {
            container.slideDown();
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
            text.text('Ocultar Tabla');
        }
    });
});
</script>
<?= $this->endSection() ?>