<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Reportes de Ventas<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Reportes de Ventas<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas') ?>">Ventas</a></li>
<li class="breadcrumb-item active">Reportes</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Reportes de Ventas
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Próximamente:</strong> Los reportes de ventas serán implementados en una fase posterior del sistema.
                    <br><br>
                    Reportes planificados:
                    <ul class="mb-0">
                        <li>Ventas por período</li>
                        <li>Ventas por vendedor</li>
                        <li>Ventas por proyecto</li>
                        <li>Análisis de conversión</li>
                        <li>Comisiones por vendedor</li>
                        <li>Estado de pagos y cobranza</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>