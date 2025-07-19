<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('admin/financiamiento') ?>">Financiamiento</a></li>
<li class="breadcrumb-item active"><?= $financiamiento->nombre ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- Información General -->
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Información General
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('admin/financiamiento/edit/' . $financiamiento->id) ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nombre:</strong>
                        <p><?= esc($financiamiento->nombre) ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Estado:</strong>
                        <p><?= $financiamiento->getBadgeEstado() ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Empresa:</strong>
                        <p><?= esc($financiamiento->empresa_nombre ?? 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Proyecto:</strong>
                        <p><?= esc($financiamiento->proyecto_nombre ?? 'Global') ?></p>
                    </div>
                </div>
                
                <?php if ($financiamiento->descripcion): ?>
                <div class="row">
                    <div class="col-12">
                        <strong>Descripción:</strong>
                        <p><?= esc($financiamiento->descripcion) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Creado:</strong>
                        <p><?= $financiamiento->created_at ? $financiamiento->created_at->format('d/m/Y H:i:s') : 'N/A' ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Última actualización:</strong>
                        <p><?= $financiamiento->updated_at ? $financiamiento->updated_at->format('d/m/Y H:i:s') : 'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resumen Rápido -->
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Resumen Rápido
                </h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-hand-holding-usd"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Anticipo</span>
                        <span class="info-box-number"><?= $financiamiento->getAnticipoFormateado() ?></span>
                    </div>
                </div>
                
                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-percentage"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Comisión</span>
                        <span class="info-box-number"><?= $financiamiento->getComisionFormateada() ?></span>
                    </div>
                </div>
                
                <div class="info-box">
                    <span class="info-box-icon bg-warning">
                        <i class="fas fa-calendar"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Plazo Total</span>
                        <span class="info-box-number"><?= $financiamiento->getTotalMeses() ?> meses</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Configuración de Anticipo -->
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-hand-holding-usd mr-2"></i>
                    Configuración de Anticipo
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td><?= $financiamiento->getBadgeTipoAnticipo() ?></td>
                    </tr>
                    <tr>
                        <td><strong>Porcentaje:</strong></td>
                        <td><?= $financiamiento->porcentaje_anticipo ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Anticipo Fijo:</strong></td>
                        <td>$<?= number_format($financiamiento->anticipo_fijo, 2) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Apartado Mínimo:</strong></td>
                        <td>$<?= number_format($financiamiento->apartado_minimo, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Configuración de Comisión -->
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-percentage mr-2"></i>
                    Configuración de Comisión
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td>
                            <span class="badge <?= $financiamiento->tipo_comision === 'porcentaje' ? 'badge-info' : 'badge-warning' ?>">
                                <?= ucfirst($financiamiento->tipo_comision) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Porcentaje:</strong></td>
                        <td><?= $financiamiento->porcentaje_comision ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Comisión Fija:</strong></td>
                        <td>$<?= number_format($financiamiento->comision_fija, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Configuración de Financiamiento -->
    <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-credit-card mr-2"></i>
                    Financiamiento y Políticas
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Meses Sin Intereses:</strong></td>
                        <td><?= $financiamiento->meses_sin_intereses ?></td>
                    </tr>
                    <tr>
                        <td><strong>Meses Con Intereses:</strong></td>
                        <td><?= $financiamiento->meses_con_intereses ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tasa Anual:</strong></td>
                        <td><?= $financiamiento->porcentaje_interes_anual ?>%</td>
                    </tr>
                    <tr>
                        <td><strong>Días Anticipo:</strong></td>
                        <td><?= $financiamiento->dias_anticipo ?></td>
                    </tr>
                    <tr>
                        <td><strong>% Cancelación:</strong></td>
                        <td><?= $financiamiento->porcentaje_cancelacion ?>%</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Simulación de Ejemplo -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calculator mr-2"></i>
                    Simulación de Ejemplo ($500,000)
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-dollar-sign"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Anticipo</span>
                                <span class="info-box-number">$<?= number_format($simulacion['anticipo'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-percentage"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Comisión</span>
                                <span class="info-box-number">$<?= number_format($simulacion['comision'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-credit-card"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Mensualidad</span>
                                <span class="info-box-number">$<?= number_format($simulacion['mensualidad'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-coins"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Pagos</span>
                                <span class="info-box-number">$<?= number_format($simulacion['total_pagos'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Detalles de la Simulación</h5>
                    <ul class="mb-0">
                        <li><strong>Precio Total:</strong> $<?= number_format($simulacion['precio_total'], 2) ?></li>
                        <li><strong>Monto a Financiar:</strong> $<?= number_format($simulacion['monto_financiar'], 2) ?></li>
                        <li><strong>Plazo Total:</strong> <?= $simulacion['total_meses'] ?> meses</li>
                        <li><strong>Meses Sin Intereses:</strong> <?= $financiamiento->meses_sin_intereses ?></li>
                        <li><strong>Meses Con Intereses:</strong> <?= $financiamiento->meses_con_intereses ?></li>
                        <li><strong>Tasa Anual:</strong> <?= $financiamiento->porcentaje_interes_anual ?>%</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botones de acción -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-footer">
                <a href="<?= site_url('admin/financiamiento-financiera/edit/' . $financiamiento->id) ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="<?= site_url('admin/financiamiento-financiera') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="button" class="btn btn-info" id="btn_duplicar">
                    <i class="fas fa-copy"></i> Duplicar
                </button>
                <?php if (!$financiamiento->es_default): ?>
                    <button type="button" class="btn btn-success" id="btn_set_default">
                        <i class="fas fa-star"></i> Establecer como Predeterminado
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // Duplicar configuración
    $('#btn_duplicar').click(function() {
        Swal.fire({
            title: '¿Duplicar configuración?',
            text: 'Se creará una copia de esta configuración',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, duplicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url("admin/financiamiento-financiera/duplicate") ?>', {
                    id: <?= $financiamiento->id ?>
                })
                .done(function(response) {
                    if (response.success) {
                        Swal.fire('¡Duplicado!', response.message, 'success')
                        .then(() => {
                            window.location.href = '<?= site_url("admin/financiamiento-financiera/edit") ?>/' + response.new_id;
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }
        });
    });

    // Establecer como predeterminado
    $('#btn_set_default').click(function() {
        Swal.fire({
            title: '¿Establecer como predeterminado?',
            text: 'Esta configuración se establecerá como la predeterminada para la empresa',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, establecer',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url("admin/financiamiento-financiera/setDefault") ?>', {
                    id: <?= $financiamiento->id ?>
                })
                .done(function(response) {
                    if (response.success) {
                        Swal.fire('¡Éxito!', response.message, 'success')
                        .then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>