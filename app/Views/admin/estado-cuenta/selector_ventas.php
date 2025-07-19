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
                    Cliente: <strong><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) ?></strong>
                </p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/estado-cuenta') ?>">Estado de Cuenta</a></li>
                    <li class="breadcrumb-item active">Selector de Propiedades</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Información del Cliente -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user"></i>
                            Información del Cliente
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Nombre Completo:</dt>
                                    <dd class="col-sm-8"><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) ?></dd>
                                    
                                    <dt class="col-sm-4">Email:</dt>
                                    <dd class="col-sm-8"><?= esc($cliente->email) ?></dd>
                                    
                                    <dt class="col-sm-4">Teléfono:</dt>
                                    <dd class="col-sm-8"><?= esc($cliente->telefono) ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Total Propiedades:</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge badge-info badge-lg">
                                            <?= $resumen_consolidado['total_ventas'] ?>
                                        </span>
                                    </dd>
                                    
                                    <dt class="col-sm-4">Valor Total:</dt>
                                    <dd class="col-sm-8">
                                        <span class="text-success h5">
                                            $<?= number_format($resumen_consolidado['valor_total'], 2) ?>
                                        </span>
                                    </dd>
                                    
                                    <dt class="col-sm-4">Total Pagado:</dt>
                                    <dd class="col-sm-8">
                                        <span class="text-info h5">
                                            $<?= number_format($resumen_consolidado['total_pagado'], 2) ?>
                                        </span>
                                    </dd>
                                    
                                    <dt class="col-sm-4">Saldo Pendiente:</dt>
                                    <dd class="col-sm-8">
                                        <span class="text-warning h5">
                                            $<?= number_format($resumen_consolidado['saldo_pendiente'], 2) ?>
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Selector de Propiedades -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-home"></i>
                            Seleccione la Propiedad para Ver Estado de Cuenta
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach($ventas_cliente as $venta): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100 border-left-<?= $venta['estatus_venta'] === 'liquidada' ? 'success' : ($venta['estatus_venta'] === 'pagando' ? 'info' : 'warning') ?>">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <h5 class="card-title">
                                                <i class="fas fa-map-marker-alt text-primary"></i>
                                                <?= esc($venta['clave_lote']) ?>
                                            </h5>
                                            <p class="text-muted small mb-2">
                                                <?= esc($venta['nombre_proyecto']) ?>
                                            </p>
                                            <span class="badge badge-<?= $venta['estatus_venta'] === 'liquidada' ? 'success' : ($venta['estatus_venta'] === 'pagando' ? 'info' : 'warning') ?> badge-lg">
                                                <?= ucfirst($venta['estatus_venta']) ?>
                                            </span>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="small">
                                            <p class="mb-1">
                                                <strong>Folio:</strong> <?= esc($venta['folio_venta']) ?>
                                            </p>
                                            <p class="mb-1">
                                                <strong>Fecha Venta:</strong> <?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?>
                                            </p>
                                            <p class="mb-1">
                                                <strong>Área:</strong> <?= number_format($venta['area_lote'], 2) ?>m²
                                            </p>
                                            <p class="mb-1">
                                                <strong>Valor:</strong> 
                                                <span class="text-success">$<?= number_format($venta['precio_venta'], 2) ?></span>
                                            </p>
                                            <p class="mb-1">
                                                <strong>Pagado:</strong> 
                                                <span class="text-info">$<?= number_format($venta['total_pagado'], 2) ?></span>
                                            </p>
                                            
                                            <?php 
                                            $porcentajePagado = $venta['precio_venta'] > 0 ? 
                                                round(($venta['total_pagado'] / $venta['precio_venta']) * 100, 1) : 0;
                                            ?>
                                            <div class="mt-2">
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-<?= $porcentajePagado >= 80 ? 'success' : ($porcentajePagado >= 50 ? 'warning' : 'danger') ?>" 
                                                         style="width: <?= $porcentajePagado ?>%">
                                                        <?= $porcentajePagado ?>%
                                                    </div>
                                                </div>
                                                <small class="text-muted">Liquidación: <?= $porcentajePagado ?>%</small>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <a href="<?= site_url('/admin/estado-cuenta/venta/' . $venta['id']) ?>" 
                                               class="btn btn-primary btn-sm btn-block">
                                                <i class="fas fa-chart-line"></i>
                                                Ver Estado de Cuenta
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
        
        <!-- Resumen Consolidado -->
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Resumen Consolidado de Propiedades
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="description-block">
                                    <h5 class="description-header text-primary">
                                        <?= $resumen_consolidado['total_ventas'] ?>
                                    </h5>
                                    <span class="description-text">PROPIEDADES</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-success">
                                        $<?= number_format($resumen_consolidado['valor_total'], 0) ?>
                                    </h5>
                                    <span class="description-text">VALOR TOTAL</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-info">
                                        $<?= number_format($resumen_consolidado['total_pagado'], 0) ?>
                                    </h5>
                                    <span class="description-text">TOTAL PAGADO</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block">
                                    <h5 class="description-header text-warning">
                                        $<?= number_format($resumen_consolidado['saldo_pendiente'], 0) ?>
                                    </h5>
                                    <span class="description-text">SALDO PENDIENTE</span>
                                </div>
                            </div>
                        </div>
                        
                        <?php 
                        $porcentajeConsolidado = $resumen_consolidado['valor_total'] > 0 ? 
                            round(($resumen_consolidado['total_pagado'] / $resumen_consolidado['valor_total']) * 100, 1) : 0;
                        ?>
                        <div class="progress mt-3">
                            <div class="progress-bar bg-<?= $porcentajeConsolidado >= 80 ? 'success' : ($porcentajeConsolidado >= 50 ? 'warning' : 'danger') ?>" 
                                 style="width: <?= $porcentajeConsolidado ?>%">
                                <?= $porcentajeConsolidado ?>% Liquidado (Consolidado)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tools"></i>
                            Acciones del Cliente
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="<?= site_url('/admin/clientes/show/' . $cliente->id) ?>" class="btn btn-primary">
                                <i class="fas fa-user"></i> Ver Perfil del Cliente
                            </a>
                            <a href="<?= site_url('/admin/clientes/edit/' . $cliente->id) ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Cliente
                            </a>
                            <a href="<?= site_url('/admin/ventas?cliente_id=' . $cliente->id) ?>" class="btn btn-info">
                                <i class="fas fa-file-contract"></i> Ver Todas las Ventas
                            </a>
                            <a href="<?= site_url('/admin/ingresos?cliente_id=' . $cliente->id) ?>" class="btn btn-success">
                                <i class="fas fa-dollar-sign"></i> Historial de Pagos
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
    // Efecto hover en las tarjetas de propiedades
    $('.card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
});
</script>
<?= $this->endSection() ?>