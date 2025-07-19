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
                <p class="text-muted">Registro de pago para mensualidad</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/mensualidades') ?>">Mensualidades</a></li>
                    <li class="breadcrumb-item active">Aplicar Pago</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Información de la Mensualidad -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Información de la Mensualidad
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Cliente:</dt>
                            <dd class="col-sm-7">
                                <strong><?= esc($mensualidad->nombre_cliente) ?></strong><br>
                                <small class="text-muted"><?= esc($mensualidad->email ?? 'N/A') ?></small>
                            </dd>
                            
                            <dt class="col-sm-5">Folio Venta:</dt>
                            <dd class="col-sm-7"><strong><?= $mensualidad->folio_venta ?></strong></dd>
                            
                            <dt class="col-sm-5">Mensualidad #:</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-info">
                                    #<?= $mensualidad->numero_pago ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Fecha Vencimiento:</dt>
                            <dd class="col-sm-7">
                                <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                <?php if ($mensualidad->dias_atraso > 0): ?>
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <?= $mensualidad->dias_atraso ?> días vencida
                                    </small>
                                <?php elseif ($mensualidad->dias_atraso < 0): ?>
                                    <br><small class="text-success">
                                        <i class="fas fa-clock"></i>
                                        Vence en <?= abs($mensualidad->dias_atraso) ?> días
                                    </small>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-5">Estado Actual:</dt>
                            <dd class="col-sm-7">
                                <?php if ($mensualidad->estatus === 'pagada'): ?>
                                    <span class="badge badge-success">Pagada</span>
                                <?php elseif ($mensualidad->estatus === 'vencida'): ?>
                                    <span class="badge badge-danger">Vencida</span>
                                <?php elseif ($mensualidad->estatus === 'parcial'): ?>
                                    <span class="badge badge-warning">Pago Parcial</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Pendiente</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calculator"></i>
                            Resumen Financiero
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="description-block border-right">
                                    <h5 class="description-header">
                                        $<?= number_format($calculos_pago['monto_original'], 2) ?>
                                    </h5>
                                    <span class="description-text">MONTO ORIGINAL</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <h5 class="description-header text-warning">
                                        $<?= number_format($calculos_pago['interes_moratorio'], 2) ?>
                                    </h5>
                                    <span class="description-text">INTERÉS MORATORIO</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-success">
                                        $<?= number_format($calculos_pago['monto_pagado'], 2) ?>
                                    </h5>
                                    <span class="description-text">YA PAGADO</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <h5 class="description-header text-danger">
                                        $<?= number_format($calculos_pago['saldo_pendiente'], 2) ?>
                                    </h5>
                                    <span class="description-text">SALDO PENDIENTE</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <h4 class="text-primary">
                                <strong>Total a Pagar: $<?= number_format($calculos_pago['total_a_pagar'], 2) ?></strong>
                            </h4>
                            <?php if ($calculos_pago['descuento_pronto_pago'] > 0): ?>
                                <small class="text-success">
                                    <i class="fas fa-tag"></i> Descuento pronto pago: 
                                    $<?= number_format($calculos_pago['descuento_pronto_pago'], 2) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulario de Pago -->
        <div class="row">
            <div class="col-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-dollar-sign"></i>
                            Registro de Pago
                        </h3>
                    </div>
                    <form id="formAplicarPago" method="POST" action="<?= site_url('/admin/mensualidades/procesarPago') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="mensualidad_id" value="<?= $mensualidad->id ?>">
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Monto a Pagar <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" name="monto_pago" class="form-control" 
                                                   step="0.01" min="0.01" 
                                                   max="<?= $calculos_pago['total_a_pagar'] ?>"
                                                   value="<?= $calculos_pago['total_a_pagar'] ?>" 
                                                   required>
                                        </div>
                                        <small class="form-text text-muted">
                                            Máximo: $<?= number_format($calculos_pago['total_a_pagar'], 2) ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Forma de Pago <span class="text-danger">*</span></label>
                                        <select name="forma_pago" class="form-control" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="transferencia">Transferencia Bancaria</option>
                                            <option value="deposito">Depósito Bancario</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                                            <option value="spei">SPEI</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha de Pago <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha_pago" class="form-control" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Referencia/Folio</label>
                                        <input type="text" name="referencia_pago" class="form-control" 
                                               placeholder="Número de referencia, folio, autorización...">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cuenta/Banco (si aplica)</label>
                                        <select name="cuenta_bancaria_id" class="form-control">
                                            <option value="">Seleccionar cuenta...</option>
                                            <?php if (!empty($cuentas_bancarias)): ?>
                                                <?php foreach ($cuentas_bancarias as $cuenta): ?>
                                                <option value="<?= $cuenta->id ?>">
                                                    <?= esc($cuenta->banco) ?> - <?= esc($cuenta->numero_cuenta_formato) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Concepto del Pago <span class="text-danger">*</span></label>
                                        <input type="text" name="concepto_pago" class="form-control" 
                                               value="Pago de mensualidad #<?= $mensualidad->numero_pago ?> - <?= $mensualidad->folio_venta ?>" 
                                               required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Observaciones/Notas</label>
                                        <textarea name="descripcion_concepto" class="form-control" rows="3" 
                                                  placeholder="Detalles adicionales del pago..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Comprobante (Opcional)</label>
                                        <input type="file" name="comprobante_pago" class="form-control-file" 
                                               accept=".jpg,.jpeg,.png,.pdf">
                                        <small class="form-text text-muted">
                                            Formatos: JPG, PNG, PDF. Máximo 5MB
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Opciones Adicionales -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-outline card-secondary">
                                        <div class="card-header">
                                            <h5 class="card-title">Opciones Adicionales</h5>
                                            <div class="card-tools">
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               name="aplicar_a_capital" id="aplicarCapital">
                                                        <label class="custom-control-label" for="aplicarCapital">
                                                            Aplicar excedente a capital
                                                        </label>
                                                        <small class="form-text text-muted">
                                                            Si el pago es mayor al adeudo, aplicar la diferencia a capital
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               name="generar_recibo_automatico" id="generarRecibo" checked>
                                                        <label class="custom-control-label" for="generarRecibo">
                                                            Generar recibo automáticamente
                                                        </label>
                                                        <small class="form-text text-muted">
                                                            Se generará un recibo PDF del pago
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               name="enviar_notificacion_cliente" id="notificarCliente" checked>
                                                        <label class="custom-control-label" for="notificarCliente">
                                                            Notificar al cliente por email
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               name="actualizar_comisiones" id="actualizarComisiones" checked>
                                                        <label class="custom-control-label" for="actualizarComisiones">
                                                            Actualizar cálculo de comisiones
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?= site_url('/admin/mensualidades') ?>" class="btn btn-default">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <a href="<?= site_url('/admin/mensualidades/detalle/' . $mensualidad->id) ?>" 
                                       class="btn btn-info">
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" class="btn btn-warning" onclick="calcularPago()">
                                        <i class="fas fa-calculator"></i> Recalcular
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-dollar-sign"></i> Aplicar Pago
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Historial de Pagos de esta Mensualidad -->
        <?php if (!empty($historial_pagos)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Historial de Pagos de esta Mensualidad
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Folio</th>
                                        <th>Forma de Pago</th>
                                        <th>Referencia</th>
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
                                            <span class="badge badge-info">
                                                <?= ucfirst($pago->forma_pago) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($pago->referencia_pago ?? 'N/A') ?></td>
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
                                            <a href="<?= site_url('/admin/mensualidades/comprobante/' . $pago->id) ?>" 
                                               class="btn btn-info btn-xs">
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
        
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Validación del formulario
    $('#formAplicarPago').on('submit', function(e) {
        var montoPago = parseFloat($('input[name="monto_pago"]').val());
        var montoMaximo = <?= $calculos_pago['total_a_pagar'] ?>;
        
        if (montoPago <= 0) {
            alert('El monto del pago debe ser mayor a cero');
            e.preventDefault();
            return false;
        }
        
        if (montoPago > montoMaximo) {
            if (!confirm('El monto excede el saldo pendiente. ¿Desea continuar?')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Validar forma de pago
        var formaPago = $('select[name="forma_pago"]').val();
        if (!formaPago) {
            alert('Seleccione una forma de pago');
            e.preventDefault();
            return false;
        }
        
        // Confirmar el pago
        if (!confirm('¿Confirma la aplicación de este pago por $' + montoPago.toLocaleString() + '?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Cambio en la forma de pago
    $('select[name="forma_pago"]').change(function() {
        var formaPago = $(this).val();
        var campoReferencia = $('input[name="referencia_pago"]');
        var campoCuenta = $('select[name="cuenta_bancaria_id"]');
        
        // Resetear campos
        campoReferencia.attr('placeholder', 'Número de referencia, folio, autorización...');
        campoReferencia.removeClass('required');
        campoCuenta.prop('required', false);
        
        // Configurar según forma de pago
        switch(formaPago) {
            case 'transferencia':
            case 'spei':
                campoReferencia.attr('placeholder', 'Número de referencia bancaria');
                campoReferencia.addClass('required');
                campoCuenta.prop('required', true);
                break;
            case 'cheque':
                campoReferencia.attr('placeholder', 'Número de cheque');
                campoReferencia.addClass('required');
                break;
            case 'tarjeta_credito':
            case 'tarjeta_debito':
                campoReferencia.attr('placeholder', 'Número de autorización');
                break;
        }
    });
});

// Función para recalcular el pago
function calcularPago() {
    var montoPago = parseFloat($('input[name="monto_pago"]').val());
    var montoOriginal = <?= $calculos_pago['monto_original'] ?>;
    var interesMoratorio = <?= $calculos_pago['interes_moratorio'] ?>;
    var montoPagado = <?= $calculos_pago['monto_pagado'] ?>;
    
    if (isNaN(montoPago) || montoPago <= 0) {
        alert('Ingrese un monto válido');
        return;
    }
    
    // Aquí podrías hacer una llamada AJAX para recalcular
    // Por ahora solo mostramos información
    var totalAdeudo = montoOriginal + interesMoratorio - montoPagado;
    
    if (montoPago > totalAdeudo) {
        var excedente = montoPago - totalAdeudo;
        alert('El pago excede el adeudo en $' + excedente.toLocaleString() + '\n' +
              'El excedente puede aplicarse a capital si marca la opción correspondiente.');
    }
}

// Auto-llenar monto completo
$('.btn-complete-payment').click(function() {
    $('input[name="monto_pago"]').val(<?= $calculos_pago['total_a_pagar'] ?>);
});
</script>
<?= $this->endSection() ?>