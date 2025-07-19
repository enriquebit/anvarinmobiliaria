<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Historial de Ventas<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Historial de Ventas<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas') ?>">Ventas</a></li>
<li class="breadcrumb-item active">Historial</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Estadísticas principales -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $estadisticas->total_ventas ?? 0 ?></h3>
                <p>Total Ventas</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $estadisticas->ventas_activas ?? 0 ?></h3>
                <p>Ventas Activas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>$<?= number_format($estadisticas->monto_total ?? 0, 2) ?></h3>
                <p>Monto Total</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>$<?= number_format($estadisticas->ticket_promedio ?? 0, 2) ?></h3>
                <p>Ticket Promedio</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Lista de Ventas Realizadas
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/ventas') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-home mr-1"></i>
                        Ver Lotes Disponibles
                    </a>
                    <a href="<?= site_url('/admin/ventas/reportes') ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Reportes
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="ventasTable">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Lote</th>
                                <th>Vendedor</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Precio Final</th>
                                <th>Estatus</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ventas)): ?>
                                <?php foreach ($ventas as $venta): ?>
                                    <tr>
                                        <td><span class="badge badge-secondary"><?= esc($venta->folio_venta) ?></span></td>
                                        <td>
                                            <strong><?= esc($venta->cliente_nombre) ?></strong><br>
                                            <small class="text-muted"><?= esc($venta->apellido_paterno) ?> <?= esc($venta->apellido_materno) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= esc($venta->lote_clave) ?></strong><br>
                                            <small class="text-muted"><?= esc($venta->proyecto_nombre) ?></small>
                                        </td>
                                        <td><?= esc($venta->vendedor_nombre) ?></td>
                                        <td><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></td>
                                        <td>
                                            <?= $venta->tipo_venta === 'contado' ? '<span class="badge badge-success">Contado</span>' : '<span class="badge badge-info">Financiado</span>' ?>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($venta->precio_venta_final, 2) ?></strong>
                                            <?php if ($venta->descuento_aplicado > 0): ?>
                                                <br><small class="text-warning">Desc: $<?= number_format($venta->descuento_aplicado, 2) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = match($venta->estatus_venta) {
                                                'activa' => 'badge-success',
                                                'liquidada' => 'badge-primary', 
                                                'cancelada' => 'badge-danger',
                                                'juridico' => 'badge-warning',
                                                default => 'badge-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($venta->estatus_venta) ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= site_url('/admin/ventas/' . $venta->id) ?>" class="btn btn-sm btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                                                <?php if ($venta->estatus_venta === 'activa'): ?>
                                                    <a href="<?= site_url('/admin/ventas/edit/' . $venta->id) ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                                    <a href="<?= site_url('/admin/ventas/cancelar/' . $venta->id) ?>" class="btn btn-sm btn-danger" title="Cancelar" onclick="return confirm('¿Cancelar venta?')"><i class="fas fa-times"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
    // Solo inicializar DataTable si hay filas de datos
    if ($('#ventasTable tbody tr').length > 0 && !$('#ventasTable tbody tr').first().find('td[colspan]').length) {
                // Configuración global de DataTables aplicada desde datatables-config.js

        $('#ventasTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "pageLength": 25,
            "order": [[4, "desc"]],
            "columnDefs": [
                { "orderable": false, "targets": 8 }
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>