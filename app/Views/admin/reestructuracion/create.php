<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
                <p class="text-muted">Crear convenio de reestructuración de cartera</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin/reestructuracion') ?>">Reestructuración</a></li>
                    <li class="breadcrumb-item active">Crear</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Información de la venta -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información de la Venta</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Folio Venta:</strong></td>
                                <td><?= $venta->folio_venta ?></td>
                            </tr>
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td><?= $cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno ?></td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td><?= $cliente->telefono ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?= $cliente->email ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Fecha Venta:</strong></td>
                                <td><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Precio Final:</strong></td>
                                <td>$<?= number_format($venta->precio_venta_final, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td><span class="badge badge-warning"><?= ucfirst($venta->estatus_venta) ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Tipo:</strong></td>
                                <td><?= ucfirst($venta->tipo_venta) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Situación actual -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Situación Actual de la Deuda</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Saldo Total</span>
                                <span class="info-box-number">$<?= number_format($saldo_pendiente_data['saldo_total'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Capital</span>
                                <span class="info-box-number">$<?= number_format($saldo_pendiente_data['saldo_capital'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Intereses</span>
                                <span class="info-box-number">$<?= number_format($saldo_pendiente_data['saldo_interes'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-secondary">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Moratorios</span>
                                <span class="info-box-number">$<?= number_format($saldo_pendiente_data['saldo_moratorio'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong>Mensualidades Pendientes:</strong> <?= $saldo_pendiente_data['mensualidades_pendientes'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha Vencimiento Original:</strong> <?= date('d/m/Y', strtotime($saldo_pendiente_data['fecha_vencimiento_original'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de reestructuración -->
        <form action="<?= base_url('/admin/reestructuracion/store') ?>" method="POST" id="reestructuracionForm">
            <input type="hidden" name="venta_id" value="<?= $venta->id ?>">
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Reestructuración</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_reestructuracion">Fecha de Reestructuración *</label>
                                <input type="date" class="form-control" id="fecha_reestructuracion" 
                                       name="fecha_reestructuracion" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_primer_pago">Fecha del Primer Pago *</label>
                                <input type="date" class="form-control" id="fecha_primer_pago" 
                                       name="fecha_primer_pago" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo de la Reestructuración *</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required
                                  placeholder="Explique el motivo de la reestructuración..."></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Descuentos y Quitas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quita_aplicada">Quita Aplicada</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="quita_aplicada" 
                                           name="quita_aplicada" step="0.01" min="0" 
                                           max="<?= $saldo_pendiente_data['saldo_total'] ?>" value="0">
                                </div>
                                <small class="form-text text-muted">Cantidad a quitar del saldo total</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="descuento_intereses">Descuento en Intereses</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="descuento_intereses" 
                                           name="descuento_intereses" step="0.01" min="0" 
                                           max="<?= $saldo_pendiente_data['saldo_interes'] ?>" value="0">
                                </div>
                                <small class="form-text text-muted">Descuento en intereses acumulados</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="descuento_moratorios">Descuento en Moratorios</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="descuento_moratorios" 
                                           name="descuento_moratorios" step="0.01" min="0" 
                                           max="<?= $saldo_pendiente_data['saldo_moratorio'] ?>" value="0">
                                </div>
                                <small class="form-text text-muted">Descuento en moratorios acumulados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nuevas Condiciones</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevo_saldo_capital">Nuevo Saldo Capital *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="nuevo_saldo_capital" 
                                           name="nuevo_saldo_capital" step="0.01" min="0" required>
                                </div>
                                <small class="form-text text-muted">Saldo después de aplicar descuentos</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="enganche_convenio">Enganche del Convenio</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="enganche_convenio" 
                                           name="enganche_convenio" step="0.01" min="0" value="0">
                                </div>
                                <small class="form-text text-muted">Pago inicial al firmar el convenio</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nuevo_plazo_meses">Nuevo Plazo (Meses) *</label>
                                <input type="number" class="form-control" id="nuevo_plazo_meses" 
                                       name="nuevo_plazo_meses" min="1" max="360" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nueva_tasa_interes">Nueva Tasa de Interés (%) *</label>
                                <input type="number" class="form-control" id="nueva_tasa_interes" 
                                       name="nueva_tasa_interes" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nuevo_pago_mensual">Nuevo Pago Mensual</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="nuevo_pago_mensual" 
                                           name="nuevo_pago_mensual" step="0.01" readonly>
                                </div>
                                <small class="form-text text-muted">Se calcula automáticamente</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Observaciones</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="observaciones">Observaciones Adicionales</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                  placeholder="Observaciones o notas adicionales..."></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= base_url('/admin/reestructuracion/ventas-elegibles') ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Crear Reestructuración
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Calcular nuevo saldo capital automáticamente
    function calcularNuevoSaldo() {
        var saldoTotal = <?= $saldo_pendiente_data['saldo_total'] ?>;
        var quita = parseFloat($('#quita_aplicada').val()) || 0;
        var descuentoIntereses = parseFloat($('#descuento_intereses').val()) || 0;
        var descuentoMoratorios = parseFloat($('#descuento_moratorios').val()) || 0;
        
        var nuevoSaldo = saldoTotal - quita - descuentoIntereses - descuentoMoratorios;
        nuevoSaldo = Math.max(0, nuevoSaldo);
        
        $('#nuevo_saldo_capital').val(nuevoSaldo.toFixed(2));
        calcularPagoMensual();
    }
    
    // Calcular pago mensual
    function calcularPagoMensual() {
        var capital = parseFloat($('#nuevo_saldo_capital').val()) || 0;
        var enganche = parseFloat($('#enganche_convenio').val()) || 0;
        var tasaAnual = parseFloat($('#nueva_tasa_interes').val()) || 0;
        var plazoMeses = parseInt($('#nuevo_plazo_meses').val()) || 0;
        
        var capitalFinanciar = capital - enganche;
        
        if (capitalFinanciar <= 0 || plazoMeses <= 0) {
            $('#nuevo_pago_mensual').val('0.00');
            return;
        }
        
        if (tasaAnual === 0) {
            var pagoMensual = capitalFinanciar / plazoMeses;
        } else {
            var tasaMensual = (tasaAnual / 100) / 12;
            var pagoMensual = capitalFinanciar * (tasaMensual * Math.pow(1 + tasaMensual, plazoMeses)) / (Math.pow(1 + tasaMensual, plazoMeses) - 1);
        }
        
        $('#nuevo_pago_mensual').val(pagoMensual.toFixed(2));
    }
    
    // Event listeners
    $('#quita_aplicada, #descuento_intereses, #descuento_moratorios').on('input', calcularNuevoSaldo);
    $('#nuevo_saldo_capital, #enganche_convenio, #nueva_tasa_interes, #nuevo_plazo_meses').on('input', calcularPagoMensual);
    
    // Calcular inicial
    calcularNuevoSaldo();
    
    // Validación del formulario
    $('#reestructuracionForm').on('submit', function(e) {
        var nuevoSaldo = parseFloat($('#nuevo_saldo_capital').val()) || 0;
        var pagoMensual = parseFloat($('#nuevo_pago_mensual').val()) || 0;
        
        if (nuevoSaldo <= 0) {
            e.preventDefault();
            Swal.fire('Error', 'El nuevo saldo capital debe ser mayor a cero', 'error');
            return;
        }
        
        if (pagoMensual <= 0) {
            e.preventDefault();
            Swal.fire('Error', 'El pago mensual debe ser mayor a cero', 'error');
            return;
        }
        
        // Confirmar creación
        e.preventDefault();
        Swal.fire({
            title: '¿Crear reestructuración?',
            text: 'Se creará una nueva reestructuración con los datos ingresados.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, crear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).off('submit').submit();
            }
        });
    });
    
    // Fecha primer pago automática (próximo mes)
    var fechaReest = new Date($('#fecha_reestructuracion').val());
    fechaReest.setMonth(fechaReest.getMonth() + 1);
    $('#fecha_primer_pago').val(fechaReest.toISOString().split('T')[0]);
});
</script>
<?= $this->endSection() ?>