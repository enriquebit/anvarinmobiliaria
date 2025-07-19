<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Ventas Registradas<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Ventas Registradas<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas') ?>">Ventas</a></li>
<li class="breadcrumb-item active">Ventas Registradas</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Contenido principal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Lista de Ventas Registradas
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/ventas') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>
                        Nueva Venta
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" action="<?= current_url() ?>">
                            <div class="input-group">
                                <select name="estatus" class="form-control form-control-sm">
                                    <option value="">Todos los estatus</option>
                                    <option value="activa" <?= $filtros['estatus'] === 'activa' ? 'selected' : '' ?>>Activa</option>
                                    <option value="liquidada" <?= $filtros['estatus'] === 'liquidada' ? 'selected' : '' ?>>Liquidada</option>
                                    <option value="cancelada" <?= $filtros['estatus'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    <option value="juridico" <?= $filtros['estatus'] === 'juridico' ? 'selected' : '' ?>>Jurídico</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de ventas -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Lote</th>
                                <th>Vendedor</th>
                                <th>Fecha Venta</th>
                                <th>Precio Final</th>
                                <th>Tipo</th>
                                <th>Estatus</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ventas)): ?>
                                <?php foreach ($ventas as $venta): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?= esc($venta->folio_venta) ?></strong>
                                        </td>
                                        <td>
                                            <?= esc($venta->cliente_nombres ?? 'N/A') ?> 
                                            <?= esc($venta->cliente_apellido_paterno ?? '') ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= esc($venta->lote_clave ?? 'N/A') ?></span>
                                            <br><small class="text-muted"><?= esc($venta->proyecto_nombre ?? 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <?= esc($venta->vendedor_nombres ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($venta->fecha_venta)) ?>
                                        </td>
                                        <td>
                                            <strong class="text-success">$<?= number_format($venta->precio_venta_final, 2) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $venta->tipo_venta === 'contado' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($venta->tipo_venta) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = 'secondary';
                                            switch ($venta->estatus_venta) {
                                                case 'activa': $badgeClass = 'primary'; break;
                                                case 'liquidada': $badgeClass = 'success'; break;
                                                case 'cancelada': $badgeClass = 'danger'; break;
                                                case 'juridico': $badgeClass = 'warning'; break;
                                            }
                                            ?>
                                            <span class="badge badge-<?= $badgeClass ?>">
                                                <?= ucfirst($venta->estatus_venta) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= site_url('/admin/ventas/show/' . $venta->id) ?>" 
                                                   class="btn btn-info btn-sm" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($venta->estatus_venta === 'activa'): ?>
                                                    <a href="<?= site_url('/admin/ventas/edit/' . $venta->id) ?>" 
                                                       class="btn btn-warning btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        No hay ventas registradas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable si hay datos
    <?php if (!empty($ventas)): ?>
    $('.table').DataTable({
        order: [[4, 'desc']], // Ordenar por fecha de venta descendente
        columnDefs: [
            { targets: [8], orderable: false } // Deshabilitar ordenación en columna de acciones
        ]
    });
    <?php endif; ?>
    
    <?php if (session('open_recibo') && session('recibo_url')): ?>
        // Abrir recibo en nueva tab después de crear venta
        setTimeout(function() {
            window.open('<?= session('recibo_url') ?>', '_blank');
        }, 500);
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>