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
                            <i class="fas fa-sync-alt"></i>
                            Refactorizaciones
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Información:</strong> Esta funcionalidad está en desarrollo. 
                            Aquí se mostrarán las refactorizaciones de tabla de amortización.
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