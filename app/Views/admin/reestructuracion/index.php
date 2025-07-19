<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
                <small class="text-muted"><?= $subtitle ?></small>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reestructuración de Cartera</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Estadísticas generales -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $estadisticas['total_reestructuraciones'] ?? 0 ?></h3>
                        <p>Total Reestructuraciones</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <a href="<?= base_url('/admin/reestructuracion/view') ?>" class="small-box-footer">
                        Ver todas <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $estadisticas['pendientes_autorizacion'] ?? 0 ?></h3>
                        <p>Pendientes Autorización</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="<?= base_url('/admin/reestructuracion/view?estatus=propuesta') ?>" class="small-box-footer">
                        Ver pendientes <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $estadisticas['activas'] ?? 0 ?></h3>
                        <p>Activas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="<?= base_url('/admin/reestructuracion/view?estatus=activa') ?>" class="small-box-footer">
                        Ver activas <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $estadisticas['canceladas'] ?? 0 ?></h3>
                        <p>Canceladas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="<?= base_url('/admin/reestructuracion/view?estatus=cancelada') ?>" class="small-box-footer">
                        Ver canceladas <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Resumen financiero -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumen Financiero</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="description-block border-right">
                                    <h5 class="description-header">$<?= number_format($estadisticas['total_saldo_original'] ?? 0, 2) ?></h5>
                                    <span class="description-text">Saldo Original</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <h5 class="description-header">$<?= number_format($estadisticas['total_nuevo_saldo'] ?? 0, 2) ?></h5>
                                    <span class="description-text">Nuevo Saldo</span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-success">$<?= number_format($estadisticas['total_quitas_aplicadas'] ?? 0, 2) ?></h5>
                                    <span class="description-text">Quitas Aplicadas</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <h5 class="description-header text-info">$<?= number_format($estadisticas['total_descuentos'] ?? 0, 2) ?></h5>
                                    <span class="description-text">Descuentos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actividad Reciente</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="description-block">
                                    <h5 class="description-header"><?= $estadisticas_actividad['actividades_hoy'] ?? 0 ?></h5>
                                    <span class="description-text">Hoy</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="description-block border-right border-left">
                                    <h5 class="description-header"><?= $estadisticas_actividad['actividades_semana'] ?? 0 ?></h5>
                                    <span class="description-text">Esta Semana</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="description-block">
                                    <h5 class="description-header"><?= $estadisticas_actividad['actividades_mes'] ?? 0 ?></h5>
                                    <span class="description-text">Este Mes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reestructuraciones pendientes de autorización -->
        <?php if (!empty($pendientes_autorizacion)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reestructuraciones Pendientes de Autorización</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Venta</th>
                                <th>Fecha</th>
                                <th>Saldo Original</th>
                                <th>Nuevo Saldo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientes_autorizacion as $reestructuracion): ?>
                            <tr>
                                <td>
                                    <strong><?= $reestructuracion->folio_reestructuracion ?></strong>
                                </td>
                                <td><?= $reestructuracion->nombre_cliente ?></td>
                                <td><?= $reestructuracion->folio_venta ?></td>
                                <td><?= date('d/m/Y', strtotime($reestructuracion->fecha_reestructuracion)) ?></td>
                                <td>$<?= number_format($reestructuracion->saldo_pendiente_original, 2) ?></td>
                                <td>$<?= number_format($reestructuracion->nuevo_saldo_capital, 2) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= base_url('/admin/reestructuracion/show/' . $reestructuracion->id) ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('/admin/reestructuracion/autorizar/' . $reestructuracion->id) ?>" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('¿Está seguro de autorizar esta reestructuración?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reestructuraciones activas -->
        <?php if (!empty($reestructuraciones_activas)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reestructuraciones Activas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Venta</th>
                                <th>Fecha Activación</th>
                                <th>Nuevo Pago</th>
                                <th>Plazo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reestructuraciones_activas as $reestructuracion): ?>
                            <tr>
                                <td>
                                    <strong><?= $reestructuracion->folio_reestructuracion ?></strong>
                                </td>
                                <td><?= $reestructuracion->nombre_cliente ?></td>
                                <td><?= $reestructuracion->folio_venta ?></td>
                                <td><?= date('d/m/Y', strtotime($reestructuracion->fecha_reestructuracion)) ?></td>
                                <td>$<?= number_format($reestructuracion->nuevo_pago_mensual, 2) ?></td>
                                <td><?= $reestructuracion->nuevo_plazo_meses ?> meses</td>
                                <td>
                                    <a href="<?= base_url('/admin/reestructuracion/show/' . $reestructuracion->id) ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actividad reciente -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actividad Reciente</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($actividad_reciente as $actividad): ?>
                    <div class="time-label">
                        <span class="bg-primary"><?= date('d/m/Y', strtotime($actividad->created_at)) ?></span>
                    </div>
                    <div>
                        <i class="fas fa-handshake bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> <?= date('H:i', strtotime($actividad->created_at)) ?>
                            </span>
                            <h3 class="timeline-header">
                                <strong><?= $actividad->folio_reestructuracion ?></strong> - <?= $actividad->nombre_cliente ?>
                            </h3>
                            <div class="timeline-body">
                                <?= $actividad->descripcion ?>
                                <br>
                                <small class="text-muted">Por: <?= $actividad->nombre_usuario ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Acciones Rápidas</h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="<?= base_url('/admin/reestructuracion/ventas-elegibles') ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nueva Reestructuración
                            </a>
                            <a href="<?= base_url('/admin/reestructuracion/view') ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-list"></i> Ver Todas
                            </a>
                            <a href="<?= base_url('/admin/reestructuracion/view?estatus=propuesta') ?>" 
                               class="btn btn-warning">
                                <i class="fas fa-clock"></i> Pendientes Autorización
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de actualización -->
        <div class="row">
            <div class="col-12">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Última actualización: <?= $fecha_actualizacion ?>
                </small>
            </div>
        </div>

    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Auto-refresh cada 5 minutos
    setTimeout(function() {
        location.reload();
    }, 300000);
});
</script>
<?= $this->endSection() ?>