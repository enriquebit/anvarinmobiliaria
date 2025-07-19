<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="#">Catálogos</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/catalogos/tipos-lotes') ?>">Tipos de Lotes</a></li>
<li class="breadcrumb-item active"><?= isset($tipo) ? 'Editar' : 'Crear' ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-<?= isset($tipo) ? 'edit' : 'plus' ?>"></i> 
                                <?= isset($tipo) ? 'Editar Tipo de Lote' : 'Nuevo Tipo de Lote' ?>
                            </h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/catalogos/tipos-lotes') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>

                        <form id="form-tipo" method="post">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 offset-md-2">
                                        <div class="form-group">
                                            <label for="nombre">Nombre del Tipo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   value="<?= isset($tipo) ? $tipo->nombre : '' ?>" required
                                                   placeholder="Ej: Lote, Casa, Departamento, Local Comercial...">
                                            <small class="form-text text-muted">
                                                Tipo de propiedad que se oferta (debe ser único)
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="descripcion">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                                      placeholder="Descripción detallada del tipo de lote..."><?= isset($tipo) ? $tipo->descripcion : '' ?></textarea>
                                            <small class="form-text text-muted">
                                                Describe las características principales de este tipo de propiedad
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="activo">Estado</label>
                                            <select class="form-control" id="activo" name="activo">
                                                <option value="1" <?= isset($tipo) && $tipo->activo ? 'selected' : '' ?>>Activo</option>
                                                <option value="0" <?= isset($tipo) && !$tipo->activo ? 'selected' : '' ?>>Inactivo</option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Los tipos inactivos no aparecerán en los formularios de lotes
                                            </small>
                                        </div>

                                        <!-- Ejemplos de tipos comunes -->
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle"></i> Tipos Comunes de Propiedades:</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled small mb-0">
                                                        <li><i class="fas fa-square text-primary mr-2"></i> <strong>Lote:</strong> Terreno sin construcción</li>
                                                        <li><i class="fas fa-home text-success mr-2"></i> <strong>Casa:</strong> Vivienda unifamiliar</li>
                                                        <li><i class="fas fa-building text-info mr-2"></i> <strong>Departamento:</strong> Unidad en edificio</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled small mb-0">
                                                        <li><i class="fas fa-store text-warning mr-2"></i> <strong>Local Comercial:</strong> Espacio para negocio</li>
                                                        <li><i class="fas fa-home text-secondary mr-2"></i> <strong>Townhouse:</strong> Casa adosada</li>
                                                        <li><i class="fas fa-industry text-dark mr-2"></i> <strong>Industrial:</strong> Uso industrial o bodega</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?= isset($tipo) ? 'Actualizar Tipo' : 'Crear Tipo' ?>
                                </button>
                                <a href="<?= base_url('admin/catalogos/tipos-lotes') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // Envío del formulario
    $('#form-tipo').submit(function(e) {
        e.preventDefault();
        
        const isEdit = <?= isset($tipo) ? 'true' : 'false' ?>;
        const url = isEdit ? 
            '<?= base_url('admin/catalogos/tipos-lotes/actualizar/' . (isset($tipo) ? $tipo->id : '')) ?>' : 
            '<?= base_url('admin/catalogos/tipos-lotes/guardar') ?>';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        window.location.href = '<?= base_url('admin/catalogos/tipos-lotes') ?>';
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr.responseText);
                toastr.error('Error de conexión: ' + error);
            },
            complete: function() {
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> <?= isset($tipo) ? 'Actualizar Tipo' : 'Crear Tipo' ?>');
            }
        });
    });

    // Validación en tiempo real
    $('#nombre').on('blur', function() {
        const nombre = $(this).val().trim();
        if (nombre.length > 100) {
            toastr.warning('El nombre no debe exceder 100 caracteres');
            $(this).focus();
        }
    });

    // Auto-focus en el campo nombre
    $('#nombre').focus();
});
</script>
<?= $this->endSection() ?>