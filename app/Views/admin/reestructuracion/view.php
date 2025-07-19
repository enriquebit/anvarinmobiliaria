<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
                <p class="text-muted">Gestión de todas las reestructuraciones de cartera</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin/reestructuracion') ?>">Reestructuración</a></li>
                    <li class="breadcrumb-item active">Ver Todas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Estadísticas de búsqueda -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $estadisticas_busqueda['total_reestructuraciones'] ?></h3>
                        <p>Total Encontradas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$<?= number_format($estadisticas_busqueda['total_saldo_original'], 2) ?></h3>
                        <p>Saldo Original</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>$<?= number_format($estadisticas_busqueda['total_nuevo_saldo'], 2) ?></h3>
                        <p>Nuevo Saldo</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$<?= number_format($estadisticas_busqueda['total_quitas'], 2) ?></h3>
                        <p>Total Quitas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cut"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filtros de Búsqueda</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('/admin/reestructuracion/view') ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estatus">Estado</label>
                                <select class="form-control" id="estatus" name="estatus">
                                    <option value="">Todos los estados</option>
                                    <?php foreach ($opciones_filtros['estados'] as $valor => $texto): ?>
                                    <option value="<?= $valor ?>" <?= ($filtros_aplicados['estatus'] ?? '') == $valor ? 'selected' : '' ?>>
                                        <?= $texto ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_desde">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                       value="<?= $filtros_aplicados['fecha_desde'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_hasta">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                       value="<?= $filtros_aplicados['fecha_hasta'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cliente_nombre">Cliente</label>
                                <input type="text" class="form-control" id="cliente_nombre" name="cliente_nombre" 
                                       value="<?= $filtros_aplicados['cliente_nombre'] ?? '' ?>" 
                                       placeholder="Nombre del cliente">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="folio_venta">Folio Venta</label>
                                <input type="text" class="form-control" id="folio_venta" name="folio_venta" 
                                       value="<?= $filtros_aplicados['folio_venta'] ?? '' ?>" 
                                       placeholder="Folio de venta">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="orden_por">Ordenar Por</label>
                                <select class="form-control" id="orden_por" name="orden_por">
                                    <option value="r.fecha_reestructuracion" <?= ($filtros_aplicados['orden_por'] ?? '') == 'r.fecha_reestructuracion' ? 'selected' : '' ?>>
                                        Fecha Reestructuración
                                    </option>
                                    <option value="r.folio_reestructuracion" <?= ($filtros_aplicados['orden_por'] ?? '') == 'r.folio_reestructuracion' ? 'selected' : '' ?>>
                                        Folio
                                    </option>
                                    <option value="r.estatus" <?= ($filtros_aplicados['orden_por'] ?? '') == 'r.estatus' ? 'selected' : '' ?>>
                                        Estado
                                    </option>
                                    <option value="r.saldo_pendiente_original" <?= ($filtros_aplicados['orden_por'] ?? '') == 'r.saldo_pendiente_original' ? 'selected' : '' ?>>
                                        Saldo Original
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <select class="form-control" id="direccion" name="direccion">
                                    <option value="DESC" <?= ($filtros_aplicados['direccion'] ?? '') == 'DESC' ? 'selected' : '' ?>>
                                        Descendente
                                    </option>
                                    <option value="ASC" <?= ($filtros_aplicados['direccion'] ?? '') == 'ASC' ? 'selected' : '' ?>>
                                        Ascendente
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="btn-group d-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <a href="<?= base_url('/admin/reestructuracion/view') ?>" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Distribución por estado -->
        <?php if (!empty($estadisticas_busqueda['por_estatus'])): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Distribución por Estado</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($estadisticas_busqueda['por_estatus'] as $estatus => $cantidad): ?>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-<?= $estatus == 'activa' ? 'success' : ($estatus == 'propuesta' ? 'warning' : 'info') ?>">
                                <i class="fas fa-<?= $estatus == 'activa' ? 'check-circle' : ($estatus == 'propuesta' ? 'clock' : 'handshake') ?>"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= ucfirst($estatus) ?></span>
                                <span class="info-box-number"><?= $cantidad ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla de reestructuraciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reestructuraciones</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" name="table_search" class="form-control float-right" 
                               placeholder="Buscar..." id="tableSearch">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap" id="reestructuracionesTable">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Folio Venta</th>
                            <th>Lote</th>
                            <th>Proyecto</th>
                            <th>Fecha</th>
                            <th>Saldo Original</th>
                            <th>Nuevo Saldo</th>
                            <th>Quita</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reestructuraciones as $reestructuracion): ?>
                        <tr>
                            <td>
                                <strong><?= $reestructuracion->folio_reestructuracion ?></strong>
                            </td>
                            <td><?= $reestructuracion->nombre_cliente ?></td>
                            <td>
                                <a href="<?= base_url('/admin/ventas/show/' . $reestructuracion->venta_id) ?>" 
                                   class="btn btn-link btn-sm p-0">
                                    <?= $reestructuracion->folio_venta ?>
                                </a>
                            </td>
                            <td><?= $reestructuracion->clave_lote ?></td>
                            <td><?= $reestructuracion->nombre_proyecto ?></td>
                            <td><?= date('d/m/Y', strtotime($reestructuracion->fecha_reestructuracion)) ?></td>
                            <td class="text-right">
                                <span class="text-danger">$<?= number_format($reestructuracion->saldo_pendiente_original, 2) ?></span>
                            </td>
                            <td class="text-right">
                                <span class="text-success">$<?= number_format($reestructuracion->nuevo_saldo_capital, 2) ?></span>
                            </td>
                            <td class="text-right">
                                <span class="text-info">$<?= number_format($reestructuracion->quita_aplicada, 2) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $reestructuracion->estatus == 'activa' ? 'success' : ($reestructuracion->estatus == 'propuesta' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($reestructuracion->estatus) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= base_url('/admin/reestructuracion/show/' . $reestructuracion->id) ?>" 
                                       class="btn btn-info btn-sm" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($reestructuracion->estatus == 'propuesta'): ?>
                                        <a href="<?= base_url('/admin/reestructuracion/autorizar/' . $reestructuracion->id) ?>" 
                                           class="btn btn-success btn-sm" 
                                           title="Autorizar"
                                           onclick="return confirm('¿Autorizar esta reestructuración?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php elseif ($reestructuracion->estatus == 'autorizada'): ?>
                                        <a href="<?= base_url('/admin/reestructuracion/activar/' . $reestructuracion->id) ?>" 
                                           class="btn btn-primary btn-sm" 
                                           title="Activar"
                                           onclick="return confirm('¿Activar esta reestructuración?')">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" 
                                            type="button" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="<?= base_url('/admin/reestructuracion/show/' . $reestructuracion->id) ?>">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </a>
                                        <?php if ($reestructuracion->estatus == 'propuesta' || $reestructuracion->estatus == 'autorizada'): ?>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" 
                                           href="<?= base_url('/admin/reestructuracion/cancelar/' . $reestructuracion->id) ?>"
                                           onclick="return confirm('¿Cancelar esta reestructuración?')">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="card">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="<?= base_url('/admin/reestructuracion/create') ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Reestructuración
                    </a>
                    <a href="<?= base_url('/admin/reestructuracion') ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-dashboard"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Búsqueda en tiempo real
    $('#tableSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#reestructuracionesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // Resaltar filas según estado
    $('#reestructuracionesTable tbody tr').each(function() {
        var estado = $(this).find('.badge').text().toLowerCase();
        if (estado === 'propuesta') {
            $(this).addClass('table-warning');
        } else if (estado === 'activa') {
            $(this).addClass('table-success');
        }
    });
});
</script>
<?= $this->endSection() ?>