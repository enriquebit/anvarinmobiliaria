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
                <p class="text-muted">Anticipación: <?= $dias_anticipacion ?> días</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/estado-cuenta') ?>">Estado de Cuenta</a></li>
                    <li class="breadcrumb-item active">Alertas</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Filtros de Anticipación -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="form-inline">
                            <label class="mr-2">Mostrar alertas para los próximos:</label>
                            <select name="dias" class="form-control mr-2" onchange="this.form.submit()">
                                <option value="7" <?= $dias_anticipacion == 7 ? 'selected' : '' ?>>7 días</option>
                                <option value="15" <?= $dias_anticipacion == 15 ? 'selected' : '' ?>>15 días</option>
                                <option value="30" <?= $dias_anticipacion == 30 ? 'selected' : '' ?>>30 días</option>
                                <option value="60" <?= $dias_anticipacion == 60 ? 'selected' : '' ?>>60 días</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Actualizar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Alertas -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $estadisticas['total_criticas'] ?></h3>
                        <p>Alertas Críticas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $estadisticas['total_hoy'] ?></h3>
                        <p>Vencen Hoy</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $estadisticas['total_mañana'] ?></h3>
                        <p>Vencen Mañana</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?= $estadisticas['total_semana'] ?></h3>
                        <p>Esta Semana</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alerta de Montos -->
        <?php if ($estadisticas['monto_vencido'] > 0 || $estadisticas['monto_proximo'] > 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Resumen Financiero de Alertas</h5>
                    
                    <?php if ($estadisticas['monto_vencido'] > 0): ?>
                        <strong>Monto Vencido:</strong> $<?= number_format($estadisticas['monto_vencido'], 2) ?> &nbsp;
                    <?php endif; ?>
                    
                    <?php if ($estadisticas['monto_proximo'] > 0): ?>
                        <strong>Monto Próximo a Vencer:</strong> $<?= number_format($estadisticas['monto_proximo'], 2) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades Vencidas (Críticas) -->
        <?php if (!empty($alertas_categorizadas['vencidas'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Mensualidades Vencidas - CRÍTICAS (<?= count($alertas_categorizadas['vencidas']) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaVencidas">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Folio Venta</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Atraso</th>
                                        <th>Monto Total</th>
                                        <th>Moratorios</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alertas_categorizadas['vencidas'] as $vencida): ?>
                                    <tr class="table-danger">
                                        <td>
                                            <strong><?= esc($vencida->nombre_cliente) ?></strong><br>
                                            <small class="text-muted"><?= esc($vencida->email) ?></small>
                                        </td>
                                        <td><strong><?= $vencida->folio_venta ?></strong></td>
                                        <td>
                                            <span class="badge badge-dark">
                                                #<?= $vencida->numero_pago ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($vencida->fecha_vencimiento)) ?></td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <?= $vencida->dias_atraso ?> días
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">
                                                $<?= number_format($vencida->monto_total, 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="text-warning">
                                                $<?= number_format($vencida->interes_moratorio, 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $vencida->id) ?>" 
                                                   class="btn btn-success btn-xs" title="Aplicar Pago">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                                <a href="<?= site_url('/admin/estado-cuenta/cliente/' . $vencida->cliente_id) ?>" 
                                                   class="btn btn-info btn-xs" title="Ver Estado Cliente">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                <a href="<?= site_url('/admin/mensualidades/detalle/' . $vencida->id) ?>" 
                                                   class="btn btn-warning btn-xs" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
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
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades que Vencen Hoy -->
        <?php if (!empty($alertas_categorizadas['hoy'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day"></i>
                            Mensualidades que Vencen HOY (<?= count($alertas_categorizadas['hoy']) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Folio Venta</th>
                                        <th>Mensualidad #</th>
                                        <th>Monto</th>
                                        <th>Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alertas_categorizadas['hoy'] as $hoy): ?>
                                    <tr class="table-warning">
                                        <td><strong><?= esc($hoy->nombre_cliente) ?></strong></td>
                                        <td><?= $hoy->folio_venta ?></td>
                                        <td>
                                            <span class="badge badge-warning">
                                                #<?= $hoy->numero_pago ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($hoy->monto_total, 2) ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-phone"></i> <?= esc($hoy->telefono) ?><br>
                                            <small><i class="fas fa-envelope"></i> <?= esc($hoy->email) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $hoy->id) ?>" 
                                                   class="btn btn-success btn-xs">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                                <a href="tel:<?= $hoy->telefono ?>" 
                                                   class="btn btn-info btn-xs">
                                                    <i class="fas fa-phone"></i>
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
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades que Vencen Mañana -->
        <?php if (!empty($alertas_categorizadas['mañana'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-plus"></i>
                            Mensualidades que Vencen MAÑANA (<?= count($alertas_categorizadas['mañana']) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($alertas_categorizadas['mañana'] as $mañana): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-outline card-info">
                                    <div class="card-body">
                                        <h6><strong><?= esc($mañana->nombre_cliente) ?></strong></h6>
                                        <p class="text-muted mb-1">
                                            <?= $mañana->folio_venta ?> - Mensualidad #<?= $mañana->numero_pago ?>
                                        </p>
                                        <p class="text-success mb-2">
                                            <strong>$<?= number_format($mañana->monto_total, 2) ?></strong>
                                        </p>
                                        <div class="btn-group btn-group-sm btn-block">
                                            <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $mañana->id) ?>" 
                                               class="btn btn-success">
                                                <i class="fas fa-dollar-sign"></i> Pagar
                                            </a>
                                            <a href="tel:<?= $mañana->telefono ?>" 
                                               class="btn btn-info">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades que Vencen Esta Semana -->
        <?php if (!empty($alertas_categorizadas['esta_semana'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-week"></i>
                            Mensualidades que Vencen Esta Semana (<?= count($alertas_categorizadas['esta_semana']) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Folio</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Restantes</th>
                                        <th>Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alertas_categorizadas['esta_semana'] as $semana): ?>
                                    <tr>
                                        <td><?= esc($semana->nombre_cliente) ?></td>
                                        <td><?= $semana->folio_venta ?></td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                #<?= $semana->numero_pago ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($semana->fecha_vencimiento)) ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= $semana->dias_hasta_vencimiento ?> días
                                            </span>
                                        </td>
                                        <td><strong>$<?= number_format($semana->monto_total, 2) ?></strong></td>
                                        <td>
                                            <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $semana->id) ?>" 
                                               class="btn btn-success btn-xs">
                                                <i class="fas fa-dollar-sign"></i>
                                            </a>
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
        <?php endif; ?>
        
        <!-- Próximas Mensualidades (Futuras) -->
        <?php if (!empty($proximas_mensualidades)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt"></i>
                            Próximas Mensualidades (<?= count($proximas_mensualidades) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaProximas">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Folio</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Restantes</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximas_mensualidades as $proxima): ?>
                                    <tr>
                                        <td><?= esc($proxima->nombre_cliente) ?></td>
                                        <td><?= $proxima->folio_venta ?></td>
                                        <td>
                                            <span class="badge badge-light">
                                                #<?= $proxima->numero_pago ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($proxima->fecha_vencimiento)) ?></td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?= $proxima->dias_hasta_vencimiento ?> días
                                            </span>
                                        </td>
                                        <td>$<?= number_format($proxima->monto_total, 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTables para las tablas grandes
    if ($('#tablaVencidas tbody tr').length > 10) {
        $('#tablaVencidas').DataTable({
            "responsive": true,
            "pageLength": 25,
            "order": [[ 4, "desc" ]], // Ordenar por días de atraso
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });
    }
    
    if ($('#tablaProximas tbody tr').length > 10) {
        $('#tablaProximas').DataTable({
            "responsive": true,
            "pageLength": 25,
            "order": [[ 4, "asc" ]], // Ordenar por días restantes
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });
    }
    
    // Auto-refresh cada 5 minutos
    setTimeout(function() {
        location.reload();
    }, 300000);
    
    // Mostrar hora de actualización
    $('#horaActualizacion').text('Última actualización: <?= $fecha_actualizacion ?>');
});
</script>
<?= $this->endSection() ?>