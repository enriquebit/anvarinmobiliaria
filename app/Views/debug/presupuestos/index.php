<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('debug') ?>">Debug</a></li>
<li class="breadcrumb-item active">Presupuestos</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bug mr-2"></i>
                    Debug - Módulo de Presupuestos
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Modo Debug:</strong> Esta información solo está disponible en desarrollo.
                </div>
                
                <h5>Información del Sistema</h5>
                <table class="table table-striped">
                    <tr>
                        <th width="25%">Timestamp</th>
                        <td><?= $info['timestamp'] ?></td>
                    </tr>
                    <tr>
                        <th>Environment</th>
                        <td><span class="badge badge-<?= $info['environment'] === 'development' ? 'warning' : 'success' ?>"><?= $info['environment'] ?></span></td>
                    </tr>
                    <tr>
                        <th>Base URL</th>
                        <td><?= $info['base_url'] ?></td>
                    </tr>
                    <tr>
                        <th>Dompdf Disponible</th>
                        <td>
                            <?php if ($info['dompdf_available']): ?>
                                <span class="badge badge-success">✓ Sí</span>
                            <?php else: ?>
                                <span class="badge badge-danger">✗ No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>PdfService Disponible</th>
                        <td>
                            <?php if ($info['pdf_service_available']): ?>
                                <span class="badge badge-success">✓ Sí</span>
                            <?php else: ?>
                                <span class="badge badge-danger">✗ No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Configuraciones Financieras</th>
                        <td><?= $info['config_financiera_count'] ?> registros</td>
                    </tr>
                </table>
                
                <h5>Tests Disponibles</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6>Test PdfService</h6>
                                <p class="text-muted">Genera un PDF simple para verificar que Dompdf funciona</p>
                                <a href="<?= base_url('debug/presupuestos/test-pdf-service') ?>" class="btn btn-primary">
                                    <i class="fas fa-download mr-1"></i>
                                    Test PDF Simple
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6>Test Presupuesto</h6>
                                <p class="text-muted">Genera un PDF de presupuesto con datos simulados</p>
                                <a href="<?= base_url('debug/presupuestos/test-presupuesto-simulado') ?>" class="btn btn-success">
                                    <i class="fas fa-calculator mr-1"></i>
                                    Test Presupuesto
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6>Test Envío Email</h6>
                                <p class="text-muted">Prueba el envío de email con template HTML</p>
                                <a href="<?= base_url('debug/presupuestos/test-enviar-email') ?>" class="btn btn-warning">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Test Email
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6>Debug Exportar PDF</h6>
                                <p class="text-muted">Muestra parámetros recibidos por la ruta de exportación</p>
                                <a href="<?= base_url('debug/presupuestos/debug-exportar-pdf') ?>" class="btn btn-info">
                                    <i class="fas fa-search mr-1"></i>
                                    Debug Exportar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5>Rutas Registradas (muestra)</h5>
                <div class="accordion" id="routesAccordion">
                    <div class="card">
                        <div class="card-header">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#routesCollapse">
                                <i class="fas fa-route mr-2"></i>
                                Ver Rutas del Sistema
                            </button>
                        </div>
                        <div id="routesCollapse" class="collapse">
                            <div class="card-body">
                                <pre style="max-height: 300px; overflow-y: auto; font-size: 12px;">
<?php
$routes = $info['routes_loaded'];
foreach ($routes as $route => $handler) {
    if (strpos($route, 'presupuestos') !== false || strpos($route, 'debug') !== false) {
        echo $route . ' => ' . $handler . "\n";
    }
}
?>
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>