<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('debug') ?>">Debug</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('debug/presupuestos') ?>">Presupuestos</a></li>
<li class="breadcrumb-item active">Exportar PDF</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-search mr-2"></i>
                    Debug - Exportar PDF
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Información:</strong> Esta página muestra todos los parámetros recibidos por la ruta de exportación PDF.
                </div>
                
                <p><strong>Timestamp:</strong> <?= $timestamp ?></p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Parámetros GET</h5>
                        <div class="card">
                            <div class="card-body">
                                <pre><?= json_encode($parametros['GET'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Parámetros POST</h5>
                        <div class="card">
                            <div class="card-body">
                                <pre><?= json_encode($parametros['POST'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5>Información de la Petición</h5>
                <table class="table table-striped">
                    <tr>
                        <th width="25%">URI</th>
                        <td><?= $parametros['REQUEST_URI'] ?></td>
                    </tr>
                    <tr>
                        <th>Método</th>
                        <td><span class="badge badge-<?= $parametros['REQUEST_METHOD'] === 'GET' ? 'success' : 'primary' ?>"><?= $parametros['REQUEST_METHOD'] ?></span></td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td><?= $parametros['USER_AGENT'] ?></td>
                    </tr>
                </table>
                
                <h5>Headers</h5>
                <div class="card">
                    <div class="card-body">
                        <pre style="max-height: 200px; overflow-y: auto;"><?= json_encode($parametros['HEADERS'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="<?= base_url('debug/presupuestos') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Volver a Debug
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>