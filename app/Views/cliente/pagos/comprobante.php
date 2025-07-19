<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Comprobante de Pago - <?= $pago->folio_pago ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
Comprobante de Pago
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/pagos') ?>">Pagos</a></li>
<li class="breadcrumb-item active">Comprobante</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <!-- Botones de acción -->
        <div class="mb-3">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="<?= site_url('/cliente/pagos/comprobante/' . $pago->id . '/pdf') ?>" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
            <button class="btn btn-success" onclick="enviarPorCorreo()">
                <i class="fas fa-envelope"></i> Enviar por Correo
            </button>
            <a href="<?= site_url('/cliente/estado-cuenta/historialPagos') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Historial
            </a>
        </div>
        
        <!-- Comprobante -->
        <div class="invoice p-3 mb-3" id="area-comprobante">
            <!-- Header del comprobante -->
            <div class="row">
                <div class="col-12">
                    <h4>
                        <?php if (!empty($config_empresa->logo)): ?>
                        <img src="<?= base_url($config_empresa->logo) ?>" alt="Logo" style="height: 50px;">
                        <?php else: ?>
                        <i class="fas fa-building"></i>
                        <?php endif; ?>
                        <?= esc($config_empresa->nombre_empresa ?? 'Empresa Inmobiliaria') ?>
                        <small class="float-right">Fecha: <?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></small>
                    </h4>
                </div>
            </div>
            
            <!-- Información del comprobante -->
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    <strong>De:</strong>
                    <address>
                        <?= esc($config_empresa->nombre_empresa ?? 'Empresa Inmobiliaria') ?><br>
                        <?= esc($config_empresa->direccion ?? '') ?><br>
                        <?php if (!empty($config_empresa->telefono)): ?>
                        Tel: <?= esc($config_empresa->telefono) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($config_empresa->email)): ?>
                        Email: <?= esc($config_empresa->email) ?>
                        <?php endif; ?>
                    </address>
                </div>
                
                <div class="col-sm-4 invoice-col">
                    <strong>Para:</strong>
                    <address>
                        <?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) ?><br>
                        <?php if (!empty($cliente->direccion)): ?>
                        <?= esc($cliente->direccion) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($cliente->telefono)): ?>
                        Tel: <?= esc($cliente->telefono) ?><br>
                        <?php endif; ?>
                        Email: <?= esc($cliente->email) ?>
                    </address>
                </div>
                
                <div class="col-sm-4 invoice-col">
                    <b>Folio Pago:</b> <span class="text-danger"><?= esc($pago->folio_pago) ?></span><br>
                    <b>Fecha Pago:</b> <?= date('d/m/Y H:i', strtotime($pago->fecha_pago)) ?><br>
                    <b>Forma de Pago:</b> <?= ucfirst($pago->forma_pago) ?><br>
                    <?php if (!empty($pago->referencia_pago)): ?>
                    <b>Referencia:</b> <?= esc($pago->referencia_pago) ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Detalle del pago -->
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Propiedad</th>
                                <th>Vencimiento</th>
                                <th>Monto Base</th>
                                <th>Interés Moratorio</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    Mensualidad #<?= $mensualidad->numero_pago ?> de <?= $total_mensualidades ?>
                                    <?php if ($mensualidad->estatus_al_pagar === 'vencida'): ?>
                                    <br><small class="text-danger">(Pagada con <?= $mensualidad->dias_atraso_al_pagar ?> días de atraso)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= esc($venta->lote_clave ?? 'N/A') ?></strong><br>
                                    <small><?= esc($venta->proyecto_nombre ?? 'N/A') ?></small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?></td>
                                <td>$<?= number_format($pago->monto_capital + $pago->monto_interes, 2) ?></td>
                                <td>
                                    <?php if ($pago->monto_mora > 0): ?>
                                        <span class="text-warning">$<?= number_format($pago->monto_mora, 2) ?></span>
                                    <?php else: ?>
                                        $0.00
                                    <?php endif; ?>
                                </td>
                                <td><strong>$<?= number_format($pago->monto_pago, 2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Totales -->
            <div class="row">
                <div class="col-6">
                    <?php if (!empty($pago->observaciones)): ?>
                    <p class="lead">Observaciones:</p>
                    <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                        <?= esc($pago->observaciones) ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle mr-2"></i>Pago Aplicado Exitosamente</h6>
                        <p class="mb-0">Este pago ha sido registrado y aplicado a tu cuenta.</p>
                    </div>
                </div>
                
                <div class="col-6">
                    <p class="lead">Resumen del Pago</p>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th style="width:50%">Capital:</th>
                                <td>$<?= number_format($pago->monto_capital, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Interés:</th>
                                <td>$<?= number_format($pago->monto_interes, 2) ?></td>
                            </tr>
                            <?php if ($pago->monto_mora > 0): ?>
                            <tr>
                                <th>Interés Moratorio:</th>
                                <td class="text-warning">$<?= number_format($pago->monto_mora, 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Total Pagado:</th>
                                <td><h4>$<?= number_format($pago->monto_pago, 2) ?></h4></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Saldo después del pago -->
                    <div class="bg-light p-3 rounded">
                        <h6>Saldo después del pago:</h6>
                        <h5 class="text-primary">$<?= number_format($saldo_despues_pago, 2) ?></h5>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $porcentaje_liquidacion ?>%">
                                <?= number_format($porcentaje_liquidacion, 1) ?>% Liquidado
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer del comprobante -->
            <div class="row no-print">
                <div class="col-12">
                    <hr>
                    <p class="text-center text-muted">
                        <small>
                            Este comprobante fue generado electrónicamente el <?= date('d/m/Y H:i:s') ?><br>
                            Para cualquier aclaración, comunícate a nuestros teléfonos de atención al cliente.
                        </small>
                    </p>
                </div>
            </div>
            
            <!-- Firma digital o código QR (opcional) -->
            <?php if (!empty($pago->codigo_verificacion)): ?>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted">
                        <small>Código de verificación: <strong><?= $pago->codigo_verificacion ?></strong></small>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Información adicional (no se imprime) -->
<div class="row no-print">
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Próxima Mensualidad
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($proxima_mensualidad)): ?>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150">Mensualidad:</th>
                            <td>#<?= $proxima_mensualidad->numero_pago ?></td>
                        </tr>
                        <tr>
                            <th>Fecha Vencimiento:</th>
                            <td><?= date('d/m/Y', strtotime($proxima_mensualidad->fecha_vencimiento)) ?></td>
                        </tr>
                        <tr>
                            <th>Monto:</th>
                            <td><strong>$<?= number_format($proxima_mensualidad->monto_total, 2) ?></strong></td>
                        </tr>
                    </table>
                    
                    <a href="<?= site_url('/cliente/pagos/mensualidad/' . $proxima_mensualidad->id) ?>" 
                       class="btn btn-success btn-block">
                        <i class="fas fa-dollar-sign"></i> Pagar Siguiente Mensualidad
                    </a>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle mr-2"></i>
                        ¡Felicidades! No tienes mensualidades pendientes.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Estado de tu Cuenta
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="description-block">
                            <h5 class="description-header text-success">
                                $<?= number_format($total_pagado_acumulado, 0) ?>
                            </h5>
                            <span class="description-text">Total Pagado</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="description-block">
                            <h5 class="description-header text-warning">
                                $<?= number_format($saldo_despues_pago, 0) ?>
                            </h5>
                            <span class="description-text">Saldo Pendiente</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="<?= site_url('/cliente/estado-cuenta/propiedad/' . $venta->id) ?>" 
                       class="btn btn-info btn-block">
                        <i class="fas fa-chart-line"></i> Ver Estado de Cuenta Completo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Función para enviar por correo
function enviarPorCorreo() {
    Swal.fire({
        title: 'Enviar comprobante por correo',
        text: 'Se enviará a: <?= esc($cliente->email) ?>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Enviando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Realizar petición AJAX
            $.post('<?= site_url('/cliente/pagos/enviarComprobante/' . $pago->id) ?>')
                .done(function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: 'El comprobante ha sido enviado a tu correo electrónico.',
                        confirmButtonText: 'OK'
                    });
                })
                .fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo enviar el comprobante. Por favor intenta más tarde.',
                        confirmButtonText: 'OK'
                    });
                });
        }
    });
}

// Configuración para impresión
window.addEventListener('beforeprint', function() {
    // Ocultar elementos no necesarios para impresión
    $('.no-print').hide();
});

window.addEventListener('afterprint', function() {
    // Mostrar elementos nuevamente
    $('.no-print').show();
});
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .invoice {
        margin: 0;
        border: 0;
    }
    
    @page {
        margin: 0.5cm;
    }
}

.invoice {
    background: white;
    border: 1px solid #f4f4f4;
    position: relative;
    padding: 20px;
}

.invoice-title {
    margin-top: 0;
}
</style>
<?= $this->endSection() ?>