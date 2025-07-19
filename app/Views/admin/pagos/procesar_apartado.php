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
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-hand-holding-usd"></i>
                            Procesar Pago de Apartado
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Información de la venta -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Venta</span>
                                        <span class="info-box-number"><?= $venta->folio_venta ?? 'N/A' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cliente</span>
                                        <span class="info-box-number"><?= $cliente->nombres ?? 'N/A' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Lote</span>
                                        <span class="info-box-number"><?= $lote->clave ?? 'N/A' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de pago -->
                        <form action="<?= site_url('admin/pagos/guardar-apartado') ?>" method="POST">
                            <input type="hidden" name="venta_id" value="<?= $venta->id ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="monto">Monto del Apartado *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control" id="monto" name="monto" 
                                                   step="0.01" min="0" required>
                                        </div>
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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Procesar Pago de Apartado
                                    </button>
                                    <a href="<?= site_url('admin/pagos') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Llenar monto sugerido para liquidar apartado
function llenarMontoSugerido() {
    const montoSugerido = <?= $info_anticipo['saldo_pendiente'] ?? 0 ?>;
    document.getElementById('monto').value = montoSugerido.toFixed(2);
}

// Validar formulario antes de enviar
document.getElementById('formPagoApartado').addEventListener('submit', function(e) {
    const monto = parseFloat(document.getElementById('monto').value);
    const saldoPendiente = <?= $info_anticipo['saldo_pendiente'] ?? 0 ?>;
    
    if (monto > saldoPendiente) {
        e.preventDefault();
        alert('El monto no puede ser mayor al saldo pendiente ($' + saldoPendiente.toFixed(2) + ')');
        return false;
    }
    
    if (monto <= 0) {
        e.preventDefault();
        alert('El monto debe ser mayor a cero');
        return false;
    }
    
    // Confirmar el pago
    if (!confirm('¿Está seguro de procesar este pago de apartado por $' + monto.toFixed(2) + '?')) {
        e.preventDefault();
        return false;
    }
});

// Actualizar progreso en tiempo real
document.getElementById('monto').addEventListener('input', function() {
    const monto = parseFloat(this.value) || 0;
    const montoRequerido = <?= $info_anticipo['monto_requerido'] ?? 0 ?>;
    const montoAplicado = <?= $info_anticipo['monto_aplicado'] ?? 0 ?>;
    
    const nuevoTotal = montoAplicado + monto;
    const nuevoPorcentaje = montoRequerido > 0 ? (nuevoTotal / montoRequerido) * 100 : 0;
    
    // Actualizar barra de progreso visualmente
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = Math.min(nuevoPorcentaje, 100) + '%';
        progressBar.textContent = Math.min(nuevoPorcentaje, 100).toFixed(1) + '% Completado';
        
        // Cambiar color según porcentaje
        progressBar.className = 'progress-bar ' + 
            (nuevoPorcentaje >= 100 ? 'bg-success' : (nuevoPorcentaje >= 50 ? 'bg-warning' : 'bg-info'));
    }
});
</script>
<?= $this->endSection() ?>