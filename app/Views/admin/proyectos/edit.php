<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
Editar Proyecto: <?= esc($proyecto->nombre) ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/proyectos') ?>">Proyectos</a></li>
<li class="breadcrumb-item active">Editar</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Proyecto</h3>
                </div>
                
                <form action="<?= site_url('/admin/proyectos/update/' . $proyecto->id) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre del Proyecto *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= old('nombre', $proyecto->nombre) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="clave">Clave *</label>
                                    <input type="text" class="form-control" id="clave" name="clave" 
                                           value="<?= old('clave', $proyecto->clave) ?>" required>
                                    <small class="form-text text-muted">Código interno del proyecto</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="empresas_id">Empresa *</label>
                                    <select class="form-control select2" id="empresas_id" name="empresas_id" required>
                                        <option value="">Seleccionar empresa...</option>
                                        <?php foreach ($empresas as $empresa): ?>
                                            <option value="<?= $empresa->id ?>" 
                                                    <?= old('empresas_id', $proyecto->empresas_id) == $empresa->id ? 'selected' : '' ?>>
                                                <?= esc($empresa->nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Color</label>
                                    <input type="color" class="form-control" id="color" name="color" 
                                           value="<?= old('color', $proyecto->color ?: '#007bff') ?>" style="height: 38px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3"><?= old('descripcion', $proyecto->descripcion) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?= old('direccion', $proyecto->direccion) ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitud">Longitud</label>
                                    <input type="text" class="form-control" id="longitud" name="longitud" 
                                           value="<?= old('longitud', $proyecto->longitud) ?>" placeholder="-99.1234567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitud">Latitud</label>
                                    <input type="text" class="form-control" id="latitud" name="latitud" 
                                           value="<?= old('latitud', $proyecto->latitud) ?>" placeholder="19.1234567">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="archivo">Agregar Nuevo Documento (Opcional)</label>
                            <input type="file" class="form-control-file" id="archivo" name="archivo" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                Archivos permitidos: PDF, JPG, JPEG, PNG. Tamaño máximo: 20MB.
                            </small>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar Proyecto
                        </button>
                        <a href="<?= site_url('/admin/proyectos') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
    </div>

    <!-- Panel lateral para documentos existentes -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Documentos del Proyecto
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($documentos)): ?>
                    <?php foreach ($documentos as $documento): ?>
                        <div class="document-item mb-3 p-2 border rounded" id="documento-<?= $documento->id ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= esc($documento->nombre_archivo) ?></h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($documento->created_at)) ?><br>
                                        Tamaño: <?= number_format($documento->tamaño_archivo / 1024, 2) ?> KB
                                    </small>
                                </div>
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="<?= site_url('/admin/proyectos/documentos/' . $documento->id . '/descargar') ?>" 
                                       class="btn btn-outline-primary btn-sm" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm btn-eliminar-documento" 
                                            data-id="<?= $documento->id ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No hay documentos subidos para este proyecto.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Eliminar documento
    $('.btn-eliminar-documento').click(function() {
        const documentoId = $(this).data('id');
        const documentoElement = $('#documento-' + documentoId);
        
        Swal.fire({
            title: '¿Confirmar eliminación?',
            text: 'Este documento será eliminado permanentemente',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('/admin/proyectos/documentos') ?>/' + documentoId,
                    type: 'DELETE',
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            documentoElement.fadeOut(400, function() {
                                $(this).remove();
                                
                                // Si no quedan documentos, mostrar mensaje
                                if ($('.document-item').length === 0) {
                                    $('.card-body').html(`
                                        <p class="text-muted text-center">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            No hay documentos subidos para este proyecto.
                                        </p>
                                    `);
                                }
                            });
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Error de conexión al eliminar documento');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>