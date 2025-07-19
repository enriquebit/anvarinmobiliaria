<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
Gestión de Proyectos
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item active">Proyectos</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Proyectos</h3>
                    <div class="card-tools">
                        <a href="<?= site_url('/admin/proyectos/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Proyecto
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros de búsqueda -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select id="filtro_empresa" class="form-control">
                                <option value="">Todas las empresas</option>
                                <?php foreach ($empresas as $empresa): ?>
                                    <option value="<?= $empresa->id ?>"><?= esc($empresa->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="filtro_busqueda" class="form-control" placeholder="Buscar proyecto...">
                        </div>
                        <div class="col-md-4">
                            <select id="filtro_estatus" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="pausado">Pausado</option>
                                <option value="terminado">Terminado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>

                    <table id="proyectosTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Clave</th>
                                <th>Empresa</th>
                                <th>Manzanas</th>
                                <th>Lotes</th>
                                <th>Disponibles</th>
                                <th>Vendidos</th>
                                <th>Avance</th>
                                <th>Documentos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proyectos as $proyecto): ?>
                            <tr>
                                <td><?= $proyecto->id ?></td>
                                <td>
                                    <strong><?= esc($proyecto->nombre) ?></strong>
                                    <?php if ($proyecto->color): ?>
                                        <span class="badge ml-1" style="background-color: <?= $proyecto->color ?>; color: white;">
                                            <?= strtoupper($proyecto->clave) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= esc($proyecto->clave) ?></code></td>
                                <td><?= esc($proyecto->nombre_empresa) ?></td>
                                <td>
                                    <span class="badge badge-info"><?= $proyecto->total_manzanas ?? 0 ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= $proyecto->total_lotes ?? 0 ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-success"><?= $proyecto->lotes_disponibles ?? 0 ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= $proyecto->lotes_vendidos ?? 0 ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $avance = 0;
                                    if (($proyecto->total_lotes ?? 0) > 0) {
                                        $avance = round((($proyecto->lotes_vendidos ?? 0) / $proyecto->total_lotes) * 100, 1);
                                    }
                                    $colorBarra = $avance < 30 ? 'danger' : ($avance < 70 ? 'warning' : 'success');
                                    ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?= $colorBarra ?>" 
                                             style="width: <?= $avance ?>%"
                                             title="<?= $avance ?>% vendido">
                                            <?= $avance ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= $proyecto->total_documentos ?? 0 ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $estatus = $proyecto->estatus ?? 'activo';
                                    $badgeColor = match($estatus) {
                                        'activo' => 'success',
                                        'pausado' => 'warning', 
                                        'terminado' => 'info',
                                        'cancelado' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $badgeColor ?>"><?= ucfirst($estatus) ?></span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= site_url('/admin/proyectos/show/' . $proyecto->id) ?>" 
                                           class="btn btn-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('/admin/proyectos/edit/' . $proyecto->id) ?>" 
                                           class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('/admin/proyectos/delete/' . $proyecto->id) ?>" 
                                           class="btn btn-danger btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<script>
$(document).ready(function() {
            // Configuración global de DataTables aplicada desde datatables-config.js

    $('#proyectosTable').DataTable({
        });
});
</script>
<?= $this->endSection() ?>