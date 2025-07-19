<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
                <p class="text-muted">Ventas en estado jurídico elegibles para reestructuración</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin/reestructuracion') ?>">Reestructuración</a></li>
                    <li class="breadcrumb-item active">Ventas Elegibles</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Estadísticas -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $total_ventas ?></h3>
                        <p>Ventas en Jurídico</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= count(array_filter($ventas_juridico, function($v) { return $v->puede_reestructurar; })) ?></h3>
                        <p>Pueden Reestructurar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= count(array_filter($ventas_juridico, function($v) { return $v->reestructuraciones_existentes > 0; })) ?></h3>
                        <p>Con Reestructuraciones</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>$<?= number_format(array_sum(array_column($ventas_juridico, 'saldo_pendiente')), 2) ?></h3>
                        <p>Saldo Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filtros</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('/admin/reestructuracion/ventas-elegibles') ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cliente_nombre">Cliente</label>
                                <input type="text" class="form-control" id="cliente_nombre" name="cliente_nombre" 
                                       value="<?= esc($_GET['cliente_nombre'] ?? '') ?>" 
                                       placeholder="Nombre del cliente">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="folio_venta">Folio Venta</label>
                                <input type="text" class="form-control" id="folio_venta" name="folio_venta" 
                                       value="<?= esc($_GET['folio_venta'] ?? '') ?>" 
                                       placeholder="Folio de venta">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="puede_reestructurar">Estado</label>
                                <select class="form-control" id="puede_reestructurar" name="puede_reestructurar">
                                    <option value="">Todos</option>
                                    <option value="1" <?= ($_GET['puede_reestructurar'] ?? '') == '1' ? 'selected' : '' ?>>
                                        Puede reestructurar
                                    </option>
                                    <option value="0" <?= ($_GET['puede_reestructurar'] ?? '') == '0' ? 'selected' : '' ?>>
                                        Con reestructuración activa
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="btn-group d-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <a href="<?= base_url('/admin/reestructuracion/ventas-elegibles') ?>" 
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

        <!-- Tabla de ventas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ventas Elegibles para Reestructuración</h3>
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
                <table class="table table-hover text-nowrap" id="ventasTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Folio Venta</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Lote</th>
                            <th>Fecha Venta</th>
                            <th>Saldo Pendiente</th>
                            <th>Reestructuraciones</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas_juridico as $venta): ?>
                        <tr>
                            <td><?= $venta->id ?></td>
                            <td>
                                <strong><?= $venta->folio_venta ?></strong>
                            </td>
                            <td>
                                <?= $venta->nombres . ' ' . $venta->apellido_paterno . ' ' . $venta->apellido_materno ?>
                            </td>
                            <td><?= $venta->telefono ?></td>
                            <td><?= $venta->clave_lote ?></td>
                            <td><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></td>
                            <td class="text-right">
                                <strong class="text-danger">$<?= number_format($venta->saldo_pendiente, 2) ?></strong>
                            </td>
                            <td class="text-center">
                                <?php if ($venta->reestructuraciones_existentes > 0): ?>
                                    <span class="badge badge-info"><?= $venta->reestructuraciones_existentes ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($venta->puede_reestructurar): ?>
                                    <span class="badge badge-success">Disponible</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Con reestructuración activa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= base_url('/admin/ventas/ver/' . $venta->id) ?>" 
                                       class="btn btn-info btn-sm" 
                                       title="Ver venta">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($venta->puede_reestructurar): ?>
                                        <a href="<?= base_url('/admin/reestructuracion/create/' . $venta->id) ?>" 
                                           class="btn btn-primary btn-sm" 
                                           title="Crear reestructuración">
                                            <i class="fas fa-handshake"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" 
                                                disabled 
                                                title="Ya tiene reestructuración activa">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="row">
            <div class="col-12">
                <div class="callout callout-info">
                    <h5>Información importante:</h5>
                    <p>
                        • Solo se muestran ventas en estado <strong>jurídico</strong> y con tipo de venta <strong>financiado</strong>.<br>
                        • Las ventas que ya tienen una reestructuración activa no pueden ser reestructuradas nuevamente.<br>
                        • Una venta puede tener múltiples reestructuraciones si las anteriores han sido canceladas.<br>
                        • El saldo pendiente incluye capital, intereses y moratorios pendientes de pago.
                    </p>
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
    // Búsqueda en tiempo real
    $('#tableSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#ventasTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Confirmar creación de reestructuración
    $('a[href*="/create/"]').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        
        Swal.fire({
            title: '¿Crear reestructuración?',
            text: 'Se creará una nueva reestructuración para esta venta.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, crear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>