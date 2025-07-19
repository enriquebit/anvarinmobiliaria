<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="#">Catálogos</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/catalogos/fuentes-informacion') ?>">Fuentes de Información</a></li>
<li class="breadcrumb-item active"><?= isset($fuente) ? 'Editar' : 'Crear' ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-<?= isset($fuente) ? 'edit' : 'plus' ?>"></i> 
                                <?= isset($fuente) ? 'Editar Fuente de Información' : 'Nueva Fuente de Información' ?>
                            </h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/catalogos/fuentes-informacion') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>

                        <form id="form-fuente" method="post">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 offset-md-2">
                                        
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Información:</strong> Las fuentes de información se utilizan para conocer cómo los clientes se enteraron de nuestros servicios. Aparecerán como opciones en el formulario de registro de clientes.
                                        </div>

                                        <div class="form-group">
                                            <label for="nombre">Nombre de la Fuente <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   value="<?= isset($fuente) ? $fuente->nombre : '' ?>" required
                                                   placeholder="Ej: Facebook, Referido, Señalítica, Agencia Inmobiliaria...">
                                            <small class="form-text text-muted">
                                                Nombre descriptivo que aparecerá en los formularios y reportes
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="valor">Valor/Clave <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="valor" name="valor" 
                                                   value="<?= isset($fuente) ? $fuente->valor : '' ?>" required
                                                   placeholder="Ej: facebook, referido, senalitica, agencia_inmobiliaria...">
                                            <small class="form-text text-muted">
                                                Valor único que se guarda en la base de datos. Se recomienda usar solo letras minúsculas, números y guiones bajos. Se genera automáticamente desde el nombre.
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="activo">Estado</label>
                                            <select class="form-control" id="activo" name="activo">
                                                <option value="1" <?= isset($fuente) && $fuente->activo ? 'selected' : '' ?>>Activo</option>
                                                <option value="0" <?= isset($fuente) && !$fuente->activo ? 'selected' : '' ?>>Inactivo</option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Las fuentes inactivas no aparecerán en los formularios de registro
                                            </small>
                                        </div>

                                        <!-- Ejemplos de fuentes comunes -->
                                        <div class="card card-outline card-info">
                                            <div class="card-header">
                                                <h6 class="card-title"><i class="fas fa-lightbulb"></i> Ejemplos de Fuentes Comunes</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary">Medios Digitales:</h6>
                                                        <ul class="list-unstyled small">
                                                            <li><i class="fab fa-facebook text-primary mr-2"></i> <strong>Facebook</strong> → facebook</li>
                                                            <li><i class="fab fa-instagram text-danger mr-2"></i> <strong>Instagram</strong> → instagram</li>
                                                            <li><i class="fas fa-search text-info mr-2"></i> <strong>Navegador</strong> → navegador</li>
                                                            <li><i class="fab fa-google text-warning mr-2"></i> <strong>Google Ads</strong> → google_ads</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-success">Medios Tradicionales:</h6>
                                                        <ul class="list-unstyled small">
                                                            <li><i class="fas fa-users text-success mr-2"></i> <strong>Referido</strong> → referido</li>
                                                            <li><i class="fas fa-sign text-warning mr-2"></i> <strong>Señalítica</strong> → senalitica</li>
                                                            <li><i class="fas fa-building text-info mr-2"></i> <strong>Agencia Inmobiliaria</strong> → agencia_inmobiliaria</li>
                                                            <li><i class="fas fa-tv text-secondary mr-2"></i> <strong>Espectacular</strong> → espectacular</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="alert alert-light mt-2 mb-0">
                                                    <small class="text-muted">
                                                        <i class="fas fa-tip text-info"></i>
                                                        <strong>Tip:</strong> Al escribir el nombre, el valor se generará automáticamente. Puedes modificarlo si es necesario.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?= isset($fuente) ? 'Actualizar Fuente' : 'Crear Fuente' ?>
                                </button>
                                <a href="<?= base_url('admin/catalogos/fuentes-informacion') ?>" class="btn btn-secondary">
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
    
    // Auto-generar valor desde nombre (solo para nuevos registros)
    <?php if (!isset($fuente)): ?>
    $('#nombre').on('input', function() {
        const nombre = $(this).val();
        const valor = nombre.toLowerCase()
                          .replace(/ñ/g, 'n')
                          .replace(/[áàäâ]/g, 'a')
                          .replace(/[éèëê]/g, 'e')
                          .replace(/[íìïî]/g, 'i')
                          .replace(/[óòöô]/g, 'o')
                          .replace(/[úùüû]/g, 'u')
                          .replace(/[^a-z0-9]/g, '_')
                          .replace(/_+/g, '_')
                          .replace(/^_|_$/g, '');
        $('#valor').val(valor);
    });
    <?php endif; ?>

    // Envío del formulario
    $('#form-fuente').submit(function(e) {
        e.preventDefault();
        
        const isEdit = <?= isset($fuente) ? 'true' : 'false' ?>;
        const url = isEdit ? 
            '<?= base_url('admin/catalogos/fuentes-informacion/update/' . (isset($fuente) ? $fuente->id : '')) ?>' : 
            '<?= base_url('admin/catalogos/fuentes-informacion/store') ?>';
        
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
                        window.location.href = '<?= base_url('admin/catalogos/fuentes-informacion') ?>';
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
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> <?= isset($fuente) ? 'Actualizar Fuente' : 'Crear Fuente' ?>');
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

    $('#valor').on('blur', function() {
        const valor = $(this).val().trim();
        if (valor.length > 50) {
            toastr.warning('El valor no debe exceder 50 caracteres');
            $(this).focus();
        }
    });

    // Auto-focus en el campo nombre
    $('#nombre').focus();
});
</script>
<?= $this->endSection() ?>