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
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/pagos') ?>">Pagos</a></li>
                    <li class="breadcrumb-item active"><?= $title ?></li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-check"></i>
                            Procesar Mensualidad
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Información de la venta -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Venta</span>
                                        <span class="info-box-number"><?= $venta->folio_venta ?? 'N/A' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cliente</span>
                                        <span class="info-box-number"><?= $cliente->nombres ?? 'N/A' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Lote</span>
                                        <span class="info-box-number"><?= $lote->clave ?? 'N/A' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Precio Venta</span>
                                        <span class="info-box-number">$<?= number_format($venta->precio_venta_final ?? 0, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado de la Cuenta de Financiamiento -->
                        <?php if (isset($resumen['success']) && $resumen['success']): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card card-outline card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-chart-line"></i> Estado de Cuenta de Financiamiento</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5><i class="fas fa-credit-card"></i> Resumen de la Cuenta</h5>
                                                
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <div class="description-block border-right">
                                                            <span class="description-percentage text-primary">
                                                                <i class="fas fa-wallet"></i>
                                                            </span>
                                                            <h5 class="description-header">$<?= number_format($resumen['saldo_capital'] ?? 0, 2) ?></h5>
                                                            <span class="description-text">SALDO CAPITAL</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="description-block border-right">
                                                            <span class="description-percentage text-warning">
                                                                <i class="fas fa-percentage"></i>
                                                            </span>
                                                            <h5 class="description-header">$<?= number_format($resumen['saldo_interes'] ?? 0, 2) ?></h5>
                                                            <span class="description-text">INTERESES</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="description-block border-right">
                                                            <span class="description-percentage text-danger">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                            </span>
                                                            <h5 class="description-header">$<?= number_format($resumen['saldo_moratorio'] ?? 0, 2) ?></h5>
                                                            <span class="description-text">MORATORIOS</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="description-block">
                                                            <span class="description-percentage text-<?= ($resumen['al_corriente'] ?? true) ? 'success' : 'danger' ?>">
                                                                <i class="fas fa-<?= ($resumen['al_corriente'] ?? true) ? 'check-circle' : 'times-circle' ?>"></i>
                                                            </span>
                                                            <h5 class="description-header">$<?= number_format($resumen['saldo_actual'] ?? 0, 2) ?></h5>
                                                            <span class="description-text">SALDO TOTAL</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!($resumen['al_corriente'] ?? true)): ?>
                                                <div class="alert alert-danger mt-3">
                                                    <h5><i class="icon fas fa-times-circle"></i> Cuenta con Atraso</h5>
                                                    Esta cuenta tiene pagos vencidos. Se recomienda ponerse al corriente lo antes posible.
                                                </div>
                                                <?php else: ?>
                                                <div class="alert alert-success mt-3">
                                                    <h5><i class="icon fas fa-check-circle"></i> Cuenta al Corriente</h5>
                                                    Esta cuenta está al corriente con sus pagos.
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-secondary">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Información Adicional</h3>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Mensualidad:</td>
                                                                        <td class="text-right"><strong>$<?= number_format($resumen['mensualidad'] ?? 0, 2) ?></strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Pagos Realizados:</td>
                                                                        <td class="text-right"><strong><?= $resumen['pagos_realizados'] ?? 0 ?></strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Meses Restantes:</td>
                                                                        <td class="text-right"><strong><?= $resumen['meses_restantes'] ?? 0 ?></strong></td>
                                                                    </tr>
                                                                    <tr class="border-top">
                                                                        <td><strong>Próximo Vencimiento:</strong></td>
                                                                        <td class="text-right"><strong><?= $resumen['proximo_vencimiento'] ?? 'N/A' ?></strong></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de pago de mensualidad -->
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-calendar-check"></i> Procesar Pago de Mensualidad</h3>
                            </div>
                            <div class="card-body">
                                <form action="<?= site_url('admin/pagos/guardar-mensualidad') ?>" method="POST" id="formPagoMensualidad">
                                    <input type="hidden" name="venta_id" value="<?= $venta->id ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="monto">Monto del Pago *</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="monto" name="monto" 
                                                           step="0.01" min="0" required
                                                           placeholder="Ingrese el monto del pago">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="llenarMensualidad()" title="Mensualidad completa">
                                                            <i class="fas fa-magic"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">
                                                    Mensualidad sugerida: $<?= number_format($resumen['mensualidad'] ?? 0, 2) ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="fecha_aplicacion">Fecha de Aplicación *</label>
                                                <input type="date" class="form-control" id="fecha_aplicacion" name="fecha_aplicacion" 
                                                       value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="forma_pago">Forma de Pago *</label>
                                                <select class="form-control" id="forma_pago" name="forma_pago" required>
                                                    <option value="">Seleccionar...</option>
                                                    <option value="efectivo">Efectivo</option>
                                                    <option value="transferencia">Transferencia</option>
                                                    <option value="cheque">Cheque</option>
                                                    <option value="tarjeta">Tarjeta</option>
                                                    <option value="deposito">Depósito</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="referencia">Referencia/Folio</label>
                                                <input type="text" class="form-control" id="referencia" name="referencia" 
                                                       placeholder="Número de transferencia, cheque, etc.">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="observaciones">Observaciones</label>
                                                <textarea class="form-control" id="observaciones" name="observaciones" 
                                                          rows="3" placeholder="Observaciones adicionales..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-warning btn-lg">
                                                <i class="fas fa-calendar-check"></i> Procesar Pago de Mensualidad
                                            </button>
                                            <a href="<?= site_url('admin/pagos') ?>" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Cancelar
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <h4><i class="icon fas fa-exclamation-triangle"></i> Sin Cuenta de Financiamiento</h4>
                            No se encontró una cuenta de financiamiento activa para esta venta. Debe completar el enganche primero.
                            <br><br>
                            <a href="<?= site_url('/admin/pagos/liquidar-enganche/' . $venta->id) ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Ir a Liquidar Enganche
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Llenar mensualidad sugerida
function llenarMensualidad() {
    const mensualidad = <?= $resumen['mensualidad'] ?? 0 ?>;
    document.getElementById('monto').value = mensualidad.toFixed(2);
}

// Validar formulario antes de enviar
document.getElementById('formPagoMensualidad')?.addEventListener('submit', function(e) {
    const monto = parseFloat(document.getElementById('monto').value);
    
    if (monto <= 0) {
        e.preventDefault();
        alert('El monto debe ser mayor a cero');
        return false;
    }
    
    // Confirmar el pago
    if (!confirm('¿Está seguro de procesar este pago de mensualidad por $' + monto.toFixed(2) + '?')) {
        e.preventDefault();
        return false;
    }
});

// Mostrar distribución del pago en tiempo real
document.getElementById('monto')?.addEventListener('input', function() {
    const monto = parseFloat(this.value) || 0;
    const saldoMoratorio = <?= $resumen['saldo_moratorio'] ?? 0 ?>;
    const saldoInteres = <?= $resumen['saldo_interes'] ?? 0 ?>;
    const saldoCapital = <?= $resumen['saldo_capital'] ?? 0 ?>;
    
    // Simular distribución: moratorios -> intereses -> capital
    let montoRestante = monto;
    let aplicadoMoratorio = Math.min(montoRestante, saldoMoratorio);
    montoRestante -= aplicadoMoratorio;
    
    let aplicadoInteres = Math.min(montoRestante, saldoInteres);
    montoRestante -= aplicadoInteres;
    
    let aplicadoCapital = Math.min(montoRestante, saldoCapital);
    
    // Actualizar tooltip o mostrar información
    this.title = `Distribución: Moratorios: $${aplicadoMoratorio.toFixed(2)}, Intereses: $${aplicadoInteres.toFixed(2)}, Capital: $${aplicadoCapital.toFixed(2)}`;
});
</script>
<?= $this->endSection() ?>