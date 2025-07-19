<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Comisiones por Vendedor<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Comisiones por Vendedor<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/comisiones') ?>">Comisiones</a></li>
<li class="breadcrumb-item active">Por Vendedor</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-tie mr-2"></i>
                    Comisiones de <?= esc($vendedor->nombre_completo) ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('admin/comisiones') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Volver a Lista General
                    </a>
                </div>
            </div>
            <div class="card-body">
                
                <!-- Información del vendedor -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-user"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Vendedor</span>
                                <span class="info-box-number"><?= esc($vendedor->nombre_completo) ?></span>
                                <span class="info-box-more">
                                    <i class="fas fa-envelope"></i> <?= esc($vendedor->email) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-calendar"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Período</span>
                                <span class="info-box-number">
                                    <?= date('M Y', strtotime($fecha_inicio)) ?> - <?= date('M Y', strtotime($fecha_fin)) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas del vendedor -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $estadisticas['total_comisiones'] ?></h3>
                                <p>Total Comisiones</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-list"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $estadisticas['comisiones_pendientes'] ?></h3>
                                <p>Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $estadisticas['comisiones_pagadas'] ?></h3>
                                <p>Pagadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>$<?= number_format($estadisticas['monto_total'], 2) ?></h3>
                                <p>Monto Total</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de comisiones por mes -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Comisiones por Mes</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="comisionesPorMes" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de comisiones -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="comisionesVendedorTable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Folio Venta</th>
                                <th>Cliente</th>
                                <th>Lote</th>
                                <th>Base Cálculo</th>
                                <th>% Comisión</th>
                                <th>Monto Total</th>
                                <th>Monto Pagado</th>
                                <th>Saldo</th>
                                <th>Estatus</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comisiones as $comision): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($comision->fecha_generacion)) ?></td>
                                <td>
                                    <strong><?= esc($comision->folio_venta ?? $comision->folio_apartado) ?></strong>
                                </td>
                                <td><?= esc($comision->cliente_nombre) ?></td>
                                <td><?= esc($comision->lote_clave) ?></td>
                                <td class="text-right">$<?= number_format($comision->base_calculo, 2) ?></td>
                                <td class="text-center"><?= number_format($comision->porcentaje_aplicado, 2) ?>%</td>
                                <td class="text-right">
                                    <strong>$<?= number_format($comision->monto_comision_total, 2) ?></strong>
                                </td>
                                <td class="text-right">$<?= number_format($comision->monto_pagado_total, 2) ?></td>
                                <td class="text-right">
                                    <span class="text-danger">
                                        $<?= number_format($comision->monto_comision_total - $comision->monto_pagado_total, 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $badges = [
                                        'devengada' => 'secondary',
                                        'pendiente_aceptacion' => 'warning',
                                        'aceptada' => 'info',
                                        'pendiente' => 'primary',
                                        'en_proceso' => 'warning',
                                        'realizado' => 'success',
                                        'parcial' => 'warning',
                                        'pagada' => 'success',
                                        'cancelada' => 'danger'
                                    ];
                                    $badgeClass = $badges[$comision->estatus] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $badgeClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $comision->estatus)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <?php if ($comision->estatus === 'pagada'): ?>
                                        <a href="<?= site_url('admin/comisiones/recibo/' . $comision->id) ?>" 
                                           class="btn btn-info btn-sm" 
                                           target="_blank" 
                                           title="Ver Recibo">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-secondary btn-sm btn-detalle" 
                                                data-id="<?= $comision->id ?>" 
                                                title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/chart.js/Chart.bundle.js') ?>"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#comisionesVendedorTable').DataTable({
        responsive: true,
        language: {
            url: '<?= base_url('assets/plugins/datatables/i18n/Spanish.json') ?>'
        },
        order: [[0, 'desc']] // Ordenar por fecha descendente
    });

    // Gráfico de comisiones por mes
    const ctx = document.getElementById('comisionesPorMes').getContext('2d');
    const comisionesPorMes = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($grafico_meses) ?>,
            datasets: [{
                label: 'Comisiones Generadas',
                data: <?= json_encode($grafico_montos) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Comisiones: $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Ver detalle de comisión
    $(document).on('click', '.btn-detalle', function() {
        const comisionId = $(this).data('id');
        // TODO: Implementar modal de detalle
        toastr.info('Modal de detalle en desarrollo');
    });
});
</script>
<?= $this->endSection() ?>