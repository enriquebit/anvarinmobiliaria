<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/divisiones') ?>">Divisiones</a></li>
<li class="breadcrumb-item active">Ver División</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-eye"></i> Detalles de la División
                            </h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/divisiones/edit/' . $division->id) ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="<?= base_url('admin/divisiones') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <!-- Información Básica -->
                                <div class="col-md-6">
                                    <div class="card card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">Información Básica</h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th width="30%">ID:</th>
                                                    <td><?= $division->id ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Nombre:</th>
                                                    <td><strong><?= $division->nombre ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Clave:</th>
                                                    <td>
                                                        <span class="badge badge-primary badge-lg">
                                                            <?= $division->clave ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Empresa:</th>
                                                    <td><?= $empresa->nombre ?? 'No definida' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Proyecto:</th>
                                                    <td><?= $proyecto->nombre ?? 'No definido' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Descripción:</th>
                                                    <td><?= $division->descripcion ?: '<em>Sin descripción</em>' ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuración -->
                                <div class="col-md-6">
                                    <div class="card card-success">
                                        <div class="card-header">
                                            <h3 class="card-title">Configuración</h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th width="30%">Orden:</th>
                                                    <td><?= $division->orden ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Color:</th>
                                                    <td>
                                                        <span class="badge badge-secondary" style="background-color: <?= $division->color ?>;">
                                                            <?= $division->color ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Estado:</th>
                                                    <td>
                                                        <?php if ($division->activo): ?>
                                                            <span class="badge badge-success">Activa</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Inactiva</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Creado:</th>
                                                    <td><?= $division->created_at ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Actualizado:</th>
                                                    <td><?= $division->updated_at ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas de Lotes -->
                            <?php if (isset($estadisticas) && !empty($estadisticas)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">Estadísticas de Lotes</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-info">
                                                            <i class="fas fa-th"></i>
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Lotes</span>
                                                            <span class="info-box-number"><?= $estadisticas['total_lotes'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-success">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Disponibles</span>
                                                            <span class="info-box-number"><?= $estadisticas['disponibles'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-warning">
                                                            <i class="fas fa-clock"></i>
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Apartados</span>
                                                            <span class="info-box-number"><?= $estadisticas['apartados'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-primary">
                                                            <i class="fas fa-handshake"></i>
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Vendidos</span>
                                                            <span class="info-box-number"><?= $estadisticas['vendidos'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Área Total:</label>
                                                        <p class="form-control-static"><?= number_format($estadisticas['area_total'], 2) ?> m²</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Precio Promedio:</label>
                                                        <p class="form-control-static">$<?= number_format($estadisticas['precio_promedio'], 2) ?></p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Valor Total Inventario:</label>
                                                        <p class="form-control-static"><strong>$<?= number_format($estadisticas['valor_total'], 2) ?></strong></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Manzanas Relacionadas -->
                            <?php if (isset($manzanas) && !empty($manzanas)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-warning">
                                        <div class="card-header">
                                            <h3 class="card-title">Manzanas en esta División</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Nombre</th>
                                                            <th>Clave</th>
                                                            <th>Lotes</th>
                                                            <th>Estado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($manzanas as $manzana): ?>
                                                        <tr>
                                                            <td><?= $manzana->id ?></td>
                                                            <td><?= $manzana->nombre ?></td>
                                                            <td><span class="badge badge-secondary"><?= $manzana->clave ?></span></td>
                                                            <td><?= $manzana->total_lotes ?? '0' ?></td>
                                                            <td>
                                                                <?php if ($manzana->activo): ?>
                                                                    <span class="badge badge-success">Activa</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger">Inactiva</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <a href="<?= base_url('admin/manzanas/ver/' . $manzana->id) ?>" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
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
                        </div>

                        <div class="card-footer">
                            <div class="btn-group" role="group">
                                <a href="<?= base_url('admin/divisiones/edit/' . $division->id) ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar División
                                </a>
                                <a href="<?= base_url('admin/lotes/crear?division_id=' . $division->id) ?>" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Agregar Lote
                                </a>
                                <a href="<?= base_url('admin/manzanas/crear?division_id=' . $division->id) ?>" class="btn btn-info">
                                    <i class="fas fa-plus"></i> Agregar Manzana
                                </a>
                            </div>
                            
                            <div class="float-right">
                                <a href="<?= base_url('admin/divisiones') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver a Lista
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Aquí se pueden agregar scripts específicos para la vista de detalles
});
</script>
<?= $this->endSection() ?>