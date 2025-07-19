<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
Categorías de Lotes
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/catalogos') ?>">Catálogos</a></li>
<li class="breadcrumb-item active">Categorías de Lotes</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= $titulo ?></h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/catalogos/categorias-lotes/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaCategorias" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Lotes Asignados</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?= $categoria['id'] ?></td>
                                <td><?= esc($categoria['nombre']) ?></td>
                                <td><?= esc($categoria['descripcion'] ?: 'Sin descripción') ?></td>
                                <td>
                                    <span class="badge badge-info"><?= $categoria['lotes_count'] ?? 0 ?></span>
                                </td>
                                <td>
                                    <?php if ($categoria['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($categoria['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($categoria['activo']): ?>
                                            <a href="<?= site_url('/admin/catalogos/categorias-lotes/edit/' . $categoria['id']) ?>" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (isSuperAdmin()): ?>
                                            <button class="btn btn-sm <?= $categoria['activo'] ? 'btn-danger' : 'btn-success' ?> btn-cambiar-estado" 
                                                    data-id="<?= $categoria['id'] ?>" 
                                                    title="<?= $categoria['activo'] ? 'Desactivar' : 'Activar' ?>">
                                                <i class="fas <?= $categoria['activo'] ? 'fa-ban' : 'fa-check' ?>"></i>
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
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmar Acción</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="mensajeConfirmacion"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmar">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaCategorias').DataTable({
        columnDefs: [
            { orderable: false, targets: 6 }
        ]
    });
    
    // Cambiar estado de categoría
    let categoriaId = null;
    
    $(document).on('click', '.btn-cambiar-estado', function() {
        categoriaId = $(this).data('id');
        const esActivo = $(this).hasClass('btn-danger');
        const accion = esActivo ? 'desactivar' : 'activar';
        
        $('#mensajeConfirmacion').text(`¿Está seguro que desea ${accion} esta categoría?`);
        $('#modalConfirmacion').modal('show');
    });
    
    $('#btnConfirmar').click(function() {
        if (categoriaId) {
            $.ajax({
                url: `<?= site_url('/admin/catalogos/categorias-lotes/cambiarEstado/') ?>${categoriaId}`,
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#modalConfirmacion').modal('hide');
                    
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
                    }
                },
                error: function() {
                    $('#modalConfirmacion').modal('hide');
                    Toast.fire({
                        icon: 'error',
                        title: 'Error al cambiar el estado'
                    });
                }
            });
        }
    });
});

// Toast configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});
</script>
<?= $this->endSection() ?>