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
            
            <!-- Información base -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user"></i>
                                Información del Cliente
                            </h3>
                        </div>
                        <div class="card-body">
                            <strong>Nombre:</strong> <?= $historial['info_base']['cliente']['nombre_completo'] ?><br>
                            <strong>Email:</strong> <?= $historial['info_base']['cliente']['email'] ?><br>
                            <strong>Teléfono:</strong> <?= $historial['info_base']['cliente']['telefono'] ?><br>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Información del Lote
                            </h3>
                        </div>
                        <div class="card-body">
                            <strong>Clave:</strong> <?= $historial['info_base']['lote']['clave'] ?><br>
                            <strong>Precio:</strong> $<?= number_format($historial['info_base']['lote']['precio_venta'], 2) ?><br>
                            <strong>Superficie:</strong> <?= $historial['info_base']['lote']['superficie'] ?> m²<br>
                            <strong>Proyecto:</strong> <?= $historial['info_base']['lote']['proyecto'] ?><br>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-shopping-cart"></i>
                                Información de Venta
                            </h3>
                        </div>
                        <div class="card-body">
                            <strong>Folio:</strong> <?= $historial['info_base']['venta']['folio'] ?><br>
                            <strong>Estado:</strong> <span class="badge badge-<?= getEstadoColor($historial['info_base']['venta']['estatus']) ?>"><?= $historial['info_base']['venta']['estatus'] ?></span><br>
                            <strong>Tipo:</strong> <?= $historial['info_base']['venta']['tipo'] ?><br>
                            <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($historial['info_base']['venta']['fecha_venta'])) ?><br>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen financiero -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i>
                                Resumen Financiero
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-dollar-sign"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Precio Total</span>
                                            <span class="info-box-number">$<?= number_format($historial['resumen_financiero']['precio_venta'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success">
                                            <i class="fas fa-percent"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Avance Total</span>
                                            <span class="info-box-number"><?= number_format($historial['resumen_financiero']['porcentaje_avance'], 1) ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Saldo Pendiente</span>
                                            <span class="info-box-number">$<?= number_format($historial['resumen_financiero']['saldo_total_pendiente'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-<?= $historial['resumen_financiero']['al_corriente'] ? 'success' : 'danger' ?>">
                                            <i class="fas fa-<?= $historial['resumen_financiero']['al_corriente'] ? 'check' : 'exclamation-triangle' ?>"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Estado</span>
                                            <span class="info-box-number"><?= $historial['resumen_financiero']['al_corriente'] ? 'Al Corriente' : 'Con Atraso' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones disponibles -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tools"></i>
                                Acciones Disponibles
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach($historial['acciones_disponibles'] as $accion): ?>
                                <div class="col-md-3">
                                    <a href="<?= $accion['url'] ?>" class="btn <?= $accion['clase'] ?> btn-block">
                                        <i class="<?= $accion['icono'] ?>"></i>
                                        <?= $accion['texto'] ?>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas -->
            <?php if (!empty($historial['alertas'])): ?>
            <div class="row">
                <div class="col-md-12">
                    <?php foreach($historial['alertas'] as $alerta): ?>
                    <div class="alert alert-<?= $alerta['tipo'] ?> alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon <?= $alerta['icono'] ?>"></i> <?= $alerta['titulo'] ?></h5>
                        <?= $alerta['mensaje'] ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Timeline de pagos -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history"></i>
                                Historial de Pagos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php foreach($historial['timeline_pagos'] as $pago): ?>
                                <div class="time-label">
                                    <span class="bg-<?= $pago['color'] ?>">
                                        <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                                    </span>
                                </div>
                                <div>
                                    <i class="<?= $pago['icono'] ?> bg-<?= $pago['color'] ?>"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">
                                            <?= $pago['concepto'] ?>
                                            <small class="text-muted float-right">
                                                $<?= number_format($pago['monto'], 2) ?>
                                            </small>
                                        </h3>
                                        <div class="timeline-body">
                                            <span class="badge badge-<?= getEstadoPagoColor($pago['estado']) ?>">
                                                <?= $pago['estado'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <div>
                                    <i class="fas fa-clock bg-gray"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i>
                                Avance de Pagos
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chart-avance" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i>
                                Enganche
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chart-enganche" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<script>
$(document).ready(function() {
    // Gráfico de avance de pagos
    var ctx1 = document.getElementById('chart-avance').getContext('2d');
    var chartAvance = new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Pagado', 'Pendiente'],
            datasets: [{
                data: [
                    <?= $historial['graficos']['avance_pago']['pagado'] ?>,
                    <?= $historial['graficos']['avance_pago']['pendiente'] ?>
                ],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfico de enganche
    var ctx2 = document.getElementById('chart-enganche').getContext('2d');
    var chartEnganche = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Pagado', 'Pendiente'],
            datasets: [{
                data: [
                    <?= $historial['graficos']['distribucion_enganche']['pagado'] ?>,
                    <?= $historial['graficos']['distribucion_enganche']['pendiente'] ?>
                ],
                backgroundColor: ['#007bff', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

<?php
// Funciones auxiliares PHP para colores
function getEstadoColor($estado) {
    switch($estado) {
        case 'activa': return 'success';
        case 'completada': return 'primary';
        case 'cancelada': return 'danger';
        case 'suspendida': return 'warning';
        default: return 'secondary';
    }
}

function getEstadoPagoColor($estado) {
    switch($estado) {
        case 'aplicado': return 'success';
        case 'pendiente': return 'warning';
        case 'vencido': return 'danger';
        case 'cancelado': return 'secondary';
        default: return 'info';
    }
}
?>
</script>
<?= $this->endSection() ?>