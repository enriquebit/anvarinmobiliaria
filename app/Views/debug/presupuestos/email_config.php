<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('debug') ?>">Debug</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('debug/presupuestos') ?>">Presupuestos</a></li>
<li class="breadcrumb-item active">Configuración Email</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-envelope mr-2"></i>
                    Debug - Configuración de Email
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Información:</strong> Esta página muestra la configuración de email cargada desde el archivo .env
                </div>
                
                <p><strong>Timestamp:</strong> <?= $timestamp ?></p>
                
                <h5>Configuración SMTP</h5>
                <table class="table table-striped table-bordered">
                    <tr>
                        <th width="25%">Parámetro</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td><strong>Protocol</strong></td>
                        <td><span class="badge badge-primary"><?= esc($config['protocol']) ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>SMTP Host</strong></td>
                        <td><?= esc($config['SMTPHost']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>SMTP User</strong></td>
                        <td><?= esc($config['SMTPUser']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>SMTP Port</strong></td>
                        <td><span class="badge badge-success"><?= esc($config['SMTPPort']) ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>SMTP Timeout</strong></td>
                        <td><?= esc($config['SMTPTimeout']) ?> segundos</td>
                    </tr>
                    <tr>
                        <td><strong>SMTP Crypto</strong></td>
                        <td><span class="badge badge-warning"><?= esc($config['SMTPCrypto']) ?></span></td>
                    </tr>
                </table>
                
                <h5>Configuración de Email</h5>
                <table class="table table-striped table-bordered">
                    <tr>
                        <th width="25%">Parámetro</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td><strong>From Email</strong></td>
                        <td><code><?= esc($config['fromEmail']) ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>From Name</strong></td>
                        <td><?= esc($config['fromName']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Mail Type</strong></td>
                        <td><span class="badge badge-info"><?= esc($config['mailType']) ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Charset</strong></td>
                        <td><?= esc($config['charset']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Validate</strong></td>
                        <td>
                            <?php if ($config['validate'] === 'true'): ?>
                                <span class="badge badge-success">Activado</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Desactivado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Priority</strong></td>
                        <td><?= esc($config['priority']) ?></td>
                    </tr>
                </table>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card card-success">
                            <div class="card-header">
                                <h5 class="card-title">Test Email Template</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Prueba únicamente la vista HTML del email sin enviar</p>
                                <a href="<?= base_url('debug/presupuestos/test-email-template') ?>" class="btn btn-success">
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver Template
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h5 class="card-title">Test Envío Email</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Intenta enviar un email real usando la configuración</p>
                                <a href="<?= base_url('debug/presupuestos/test-enviar-email') ?>" class="btn btn-warning">
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Enviar Test
                                </a>
                            </div>
                        </div>
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