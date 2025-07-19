<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Pagar Mensualidad #<?= $mensualidad->numero_pago ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
Pagar Mensualidad #<?= $mensualidad->numero_pago ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/pagos') ?>">Pagos</a></li>
<li class="breadcrumb-item active">Mensualidad #<?= $mensualidad->numero_pago ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- Información de la mensualidad -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                    Detalle de la Mensualidad
                </h3>
            </div>
            <div class="card-body">
                <!-- Información de la propiedad -->
                <div class="callout callout-info">
                    <h5><i class="fas fa-home mr-2"></i>Información de la Propiedad</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Lote:</strong> <?= esc($venta->lote_clave ?? 'N/A') ?></p>
                            <p><strong>Proyecto:</strong> <?= esc($venta->proyecto_nombre ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Folio Venta:</strong> <?= esc($venta->folio_venta) ?></p>
                            <p><strong>Cliente:</strong> <?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Detalle financiero -->
                <h5 class="mt-3"><i class="fas fa-calculator mr-2"></i>Detalle Financiero</h5>
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th width="200">Mensualidad:</th>
                            <td>
                                <span class="badge badge-<?= $mensualidad->estatus === 'vencida' ? 'danger' : 'secondary' ?>">
                                    #<?= $mensualidad->numero_pago ?> de <?= $total_mensualidades ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha de Vencimiento:</th>
                            <td>
                                <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                <?php if ($mensualidad->dias_atraso > 0): ?>
                                    <span class="badge badge-danger ml-2">
                                        <?= $mensualidad->dias_atraso ?> días de atraso
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Monto Capital:</th>
                            <td>$<?= number_format($mensualidad->monto_capital, 2) ?></td>
                        </tr>
                        <tr>
                            <th>Monto Interés:</th>
                            <td>$<?= number_format($mensualidad->monto_interes, 2) ?></td>
                        </tr>
                        <tr class="table-active">
                            <th>Subtotal Mensualidad:</th>
                            <td><strong>$<?= number_format($mensualidad->monto_total, 2) ?></strong></td>
                        </tr>
                        <?php if ($mensualidad->interes_moratorio > 0): ?>
                        <tr>
                            <th>Interés Moratorio:</th>
                            <td class="text-warning">
                                <strong>$<?= number_format($mensualidad->interes_moratorio, 2) ?></strong>
                                <small class="text-muted ml-2">
                                    (<?= $mensualidad->dias_atraso ?> días × $<?= number_format($mensualidad->interes_moratorio / $mensualidad->dias_atraso, 2) ?>/día)
                                </small>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($mensualidad->monto_pagado > 0): ?>
                        <tr>
                            <th>Monto ya Pagado:</th>
                            <td class="text-success">
                                -$<?= number_format($mensualidad->monto_pagado, 2) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr class="table-primary">
                            <th><h5 class="mb-0">Total a Pagar:</h5></th>
                            <td>
                                <h4 class="mb-0 text-primary">
                                    $<?= number_format($total_a_pagar, 2) ?>
                                </h4>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Historial de pagos parciales (si existen) -->
                <?php if (!empty($pagos_parciales)): ?>
                <div class="mt-4">
                    <h5><i class="fas fa-history mr-2"></i>Pagos Parciales Realizados</h5>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Folio</th>
                                <th>Monto</th>
                                <th>Forma de Pago</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos_parciales as $pago): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></td>
                                <td><?= esc($pago->folio_pago) ?></td>
                                <td>$<?= number_format($pago->monto_pago, 2) ?></td>
                                <td><?= ucfirst($pago->forma_pago) ?></td>
                                <td>
                                    <a href="<?= site_url('/cliente/pagos/comprobante/' . $pago->id) ?>" 
                                       class="btn btn-info btn-xs" target="_blank">
                                        <i class="fas fa-file-alt"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Panel de opciones de pago -->
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-credit-card mr-2"></i>
                    Opciones de Pago
                </h3>
            </div>
            <div class="card-body">
                <!-- Resumen del pago -->
                <div class="info-box bg-primary">
                    <span class="info-box-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total a Pagar</span>
                        <span class="info-box-number">
                            $<?= number_format($total_a_pagar, 2) ?>
                        </span>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg btn-block mb-2" onclick="pagarEnLinea()">
                        <i class="fas fa-lock mr-2"></i>
                        Pagar en Línea
                        <br>
                        <small>Tarjeta / Transferencia</small>
                    </button>
                    
                    <button class="btn btn-info btn-block mb-2" data-toggle="modal" data-target="#modal-deposito-bancario">
                        <i class="fas fa-university mr-2"></i>
                        Ver Cuentas Bancarias
                    </button>
                    
                    <a href="<?= site_url('/cliente/pagos/reportarPago?mensualidad=' . $mensualidad->id) ?>" 
                       class="btn btn-warning btn-block">
                        <i class="fas fa-bell mr-2"></i>
                        Reportar Pago Realizado
                    </a>
                </div>
                
                <!-- Información adicional -->
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle mr-1"></i>Información Importante</h6>
                    <ul class="mb-0 pl-3">
                        <li>Los pagos en línea se aplican inmediatamente</li>
                        <li>Los depósitos bancarios se verifican en 24-48 hrs</li>
                        <li>Conserva tu comprobante de pago</li>
                    </ul>
                </div>
                
                <!-- Ayuda -->
                <div class="text-center mt-3">
                    <p class="text-muted mb-1">¿Necesitas ayuda?</p>
                    <div class="btn-group btn-group-sm">
                        <a href="tel:<?= $config_empresa->telefono ?? '' ?>" class="btn btn-outline-primary">
                            <i class="fas fa-phone"></i> Llamar
                        </a>
                        <a href="https://wa.me/<?= $config_empresa->whatsapp ?? '' ?>?text=Hola,%20necesito%20ayuda%20con%20mi%20pago" 
                           class="btn btn-outline-success" target="_blank">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Advertencia si está vencida -->
        <?php if ($mensualidad->estatus === 'vencida'): ?>
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle mr-1"></i>Mensualidad Vencida</h6>
            <p class="mb-0">Esta mensualidad tiene <?= $mensualidad->dias_atraso ?> días de atraso. 
            Se han generado $<?= number_format($mensualidad->interes_moratorio, 2) ?> de intereses moratorios.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Depósito Bancario -->
<div class="modal fade" id="modal-deposito-bancario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-university mr-2"></i>
                    Cuentas Bancarias para Depósito
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!empty($cuentas_bancarias)): ?>
                    <div class="alert alert-warning mb-3">
                        <strong>Referencia de Pago:</strong> <?= $referencia_pago ?>
                        <button class="btn btn-sm btn-secondary float-right" 
                                onclick="copiarAlPortapapeles('<?= $referencia_pago ?>')">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                    
                    <?php foreach ($cuentas_bancarias as $cuenta): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5><?= esc($cuenta->banco) ?></h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="120">Titular:</th>
                                            <td><?= esc($cuenta->titular) ?></td>
                                        </tr>
                                        <tr>
                                            <th>No. Cuenta:</th>
                                            <td>
                                                <code><?= esc($cuenta->numero_cuenta) ?></code>
                                                <button class="btn btn-xs btn-secondary ml-2" 
                                                        onclick="copiarAlPortapapeles('<?= esc($cuenta->numero_cuenta) ?>')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>CLABE:</th>
                                            <td>
                                                <code><?= esc($cuenta->clabe) ?></code>
                                                <button class="btn btn-xs btn-secondary ml-2" 
                                                        onclick="copiarAlPortapapeles('<?= esc($cuenta->clabe) ?>')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h6>Monto a Depositar:</h6>
                                    <h3 class="text-primary">$<?= number_format($total_a_pagar, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Instrucciones:</h6>
                        <ol class="mb-0">
                            <li>Realiza tu depósito por <strong>$<?= number_format($total_a_pagar, 2) ?></strong></li>
                            <li>Incluye la referencia: <strong><?= $referencia_pago ?></strong></li>
                            <li>Guarda tu comprobante</li>
                            <li>Reporta tu pago para agilizar la aplicación</li>
                        </ol>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="<?= site_url('/cliente/pagos/reportarPago?mensualidad=' . $mensualidad->id) ?>" 
                   class="btn btn-warning">
                    <i class="fas fa-bell mr-2"></i>Reportar Pago
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para pago en línea -->
<form id="form-pago-linea" method="post" action="<?= site_url('/cliente/pagos/procesarPagoLinea') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="mensualidad_id" value="<?= $mensualidad->id ?>">
    <input type="hidden" name="monto" value="<?= $total_a_pagar ?>">
    <input type="hidden" name="concepto" value="Mensualidad #<?= $mensualidad->numero_pago ?> - <?= esc($venta->lote_clave) ?>">
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Función para pagar en línea
function pagarEnLinea() {
    // Mostrar confirmación
    Swal.fire({
        title: 'Confirmar Pago',
        html: `
            <p>Estás a punto de realizar un pago por:</p>
            <h3 class="text-primary">$<?= number_format($total_a_pagar, 2) ?></h3>
            <p>Mensualidad #<?= $mensualidad->numero_pago ?></p>
            <p class="text-muted">Serás redirigido a la pasarela de pago segura</p>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Continuar al Pago',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                text: 'Preparando tu pago seguro',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Enviar formulario
            $('#form-pago-linea').submit();
        }
    });
}

// Función para copiar al portapapeles
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(function() {
        toastr.success('Copiado al portapapeles');
    }, function(err) {
        // Fallback
        var textArea = document.createElement("textarea");
        textArea.value = texto;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        toastr.success('Copiado al portapapeles');
    });
}

// Si es una vista AJAX, no cargar el layout completo
<?php if ($this->request->getGet('ajax')): ?>
$(document).ready(function() {
    // Remover elementos del layout para vista modal
    $('.content-wrapper').removeClass('content-wrapper');
    $('.content-header').remove();
});
<?php endif; ?>
</script>
<?= $this->endSection() ?>