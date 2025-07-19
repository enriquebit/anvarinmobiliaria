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
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-check-circle"></i>
                            Liquidar Enganche
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Información:</strong> Esta funcionalidad está en desarrollo. 
                            Aquí se procesará la liquidación del enganche.
                        </div>
                        
                        <p><strong>Venta:</strong> <?= $venta->folio_venta ?? 'N/A' ?></p>
                        <p><strong>Cliente:</strong> <?= $cliente->nombres ?? 'N/A' ?></p>
                        <p><strong>Lote:</strong> <?= $lote->clave ?? 'N/A' ?></p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <a href="<?= site_url('admin/pagos') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Regresar
                                </a>
                            </div>
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
// Llenar monto completo para liquidar enganche
function llenarMontoCompleto() {
    const montoCompleto = <?= $resumen_anticipos['saldo_pendiente'] ?? 0 ?>;
    document.getElementById('monto').value = montoCompleto.toFixed(2);
}

// Validar formulario antes de enviar
document.getElementById('formLiquidacionEnganche')?.addEventListener('submit', function(e) {
    const monto = parseFloat(document.getElementById('monto').value);
    const saldoPendiente = <?= $resumen_anticipos['saldo_pendiente'] ?? 0 ?>;
    
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
    
    // Confirmar la liquidación
    if (!confirm('¿Está seguro de procesar esta liquidación de enganche por $' + monto.toFixed(2) + '?\n\nEsto abrirá la cuenta de financiamiento si se completa el enganche.')) {
        e.preventDefault();
        return false;
    }
});

// Actualizar progreso en tiempo real
document.getElementById('monto')?.addEventListener('input', function() {
    const monto = parseFloat(this.value) || 0;
    const engancheRequerido = <?= $resumen_anticipos['enganche_requerido'] ?? 0 ?>;
    const anticiposAplicados = <?= $resumen_anticipos['total_anticipos'] ?? 0 ?>;
    
    const nuevoTotal = anticiposAplicados + monto;
    const nuevoPorcentaje = engancheRequerido > 0 ? (nuevoTotal / engancheRequerido) * 100 : 0;
    
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