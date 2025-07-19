<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/divisiones') ?>">Divisiones</a></li>
<li class="breadcrumb-item active"><?= isset($division) ? 'Editar' : 'Crear' ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-<?= isset($division) ? 'edit' : 'plus' ?>"></i> 
                                <?= isset($division) ? 'Editar División' : 'Nueva División' ?>
                            </h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/divisiones') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>

                        <form id="form-division" method="post">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Información Básica -->
                                    <div class="col-md-6">
                                        <div class="card card-primary">
                                            <div class="card-header">
                                                <h3 class="card-title">Información Básica</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                                           value="<?= isset($division) ? $division->nombre : '' ?>" 
                                                           placeholder="Nombre de la división" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="clave">Clave <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="clave" name="clave" 
                                                           value="<?= isset($division) ? $division->clave : '' ?>" 
                                                           placeholder="Clave única (ej: E1, D1, etc.)" required>
                                                    <small class="form-text text-muted">Clave única para identificar la división en nomenclatura de lotes</small>
                                                </div>

                                                <div class="form-group">
                                                    <label for="empresas_id">Empresa <span class="text-danger">*</span></label>
                                                    <?= form_dropdown('empresas_id', $empresas, isset($division) ? $division->empresas_id : '', [
                                                        'class' => 'form-control',
                                                        'id' => 'empresas_id',
                                                        'required' => true
                                                    ]) ?>
                                                </div>

                                                <div class="form-group">
                                                    <label for="proyectos_id">Proyecto <span class="text-danger">*</span></label>
                                                    <?= form_dropdown('proyectos_id', $proyectos, isset($division) ? $division->proyectos_id : '', [
                                                        'class' => 'form-control',
                                                        'id' => 'proyectos_id',
                                                        'required' => true
                                                    ]) ?>
                                                </div>

                                                <div class="form-group">
                                                    <label for="descripcion">Descripción</label>
                                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                                              placeholder="Descripción de la división..."><?= isset($division) ? $division->descripcion : '' ?></textarea>
                                                </div>
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
                                                <div class="form-group">
                                                    <label for="orden">Orden de Visualización</label>
                                                    <input type="number" class="form-control" id="orden" name="orden" 
                                                           value="<?= isset($division) ? $division->orden : '1' ?>" 
                                                           min="1" max="100" placeholder="1">
                                                    <small class="form-text text-muted">Orden en que se mostrará la división en listas</small>
                                                </div>

                                                <div class="form-group">
                                                    <label for="color">Color para Identificación</label>
                                                    <input type="color" class="form-control" id="color" name="color" 
                                                           value="<?= isset($division) ? $division->color : '#3498db' ?>">
                                                    <small class="form-text text-muted">Color para identificar la división en mapas y reportes</small>
                                                </div>

                                                <div class="form-group">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                                                               <?= !isset($division) || $division->activo ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="activo">
                                                            División activa
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Solo las divisiones activas aparecerán en formularios</small>
                                                </div>

                                                <?php if (isset($division)): ?>
                                                <div class="form-group">
                                                    <label>Información del Registro</label>
                                                    <div class="alert alert-info">
                                                        <strong>Creado:</strong> <?= $division->created_at ?><br>
                                                        <strong>Actualizado:</strong> <?= $division->updated_at ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?= isset($division) ? 'Actualizar División' : 'Crear División' ?>
                                </button>
                                <a href="<?= base_url('admin/divisiones') ?>" class="btn btn-secondary">
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
    
    // En modo edición, habilitar el select de proyectos
    <?php if (isset($division)): ?>
    $('#proyectos_id').prop('disabled', false);
    <?php endif; ?>
    
    // Filtros en cascada
    $('#empresas_id').change(function() {
        const empresaId = $(this).val();
        $('#proyectos_id').prop('disabled', !empresaId).html('<option value="">Seleccionar proyecto...</option>');
        
        if (empresaId) {
            cargarProyectos(empresaId);
        }
    });

    function cargarProyectos(empresaId) {
        $.get('<?= base_url('admin/divisiones/obtener-proyectos-por-empresa') ?>/' + empresaId)
            .done(function(response) {
                if (response.success) {
                    $.each(response.proyectos, function(id, nombre) {
                        $('#proyectos_id').append(new Option(nombre, id));
                    });
                    $('#proyectos_id').prop('disabled', false);
                } else {
                    toastr.error('Error al cargar proyectos: ' + response.message);
                }
            })
            .fail(function() {
                toastr.error('Error de conexión al cargar proyectos');
            });
    }

    // Validación en tiempo real de clave
    $('#clave').on('input', function() {
        const clave = $(this).val().toUpperCase();
        $(this).val(clave);
        
        if (clave.length >= 2) {
            validarClaveUnica(clave);
        }
    });

    function validarClaveUnica(clave) {
        const divisionId = <?= isset($division) ? $division->id : 'null' ?>;
        
        $.post('<?= base_url('admin/divisiones/validar-clave') ?>', {
            clave: clave,
            division_id: divisionId
        })
        .done(function(response) {
            if (response.success) {
                $('#clave').removeClass('is-invalid').addClass('is-valid');
                $('.clave-feedback').remove();
            } else {
                $('#clave').removeClass('is-valid').addClass('is-invalid');
                $('.clave-feedback').remove();
                $('#clave').after('<div class="invalid-feedback clave-feedback">' + response.message + '</div>');
            }
        });
    }

    // Envío del formulario
    $('#form-division').submit(function(e) {
        e.preventDefault();
        
        if ($('#clave').hasClass('is-invalid')) {
            toastr.error('Por favor corrija los errores antes de continuar');
            return;
        }
        
        const url = <?= isset($division) ? "'" . base_url('admin/divisiones/update/' . $division->id) . "'" : "'" . base_url('admin/divisiones/store') . "'" ?>;
        
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
                        window.location.href = '<?= base_url('admin/divisiones') ?>';
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error de conexión');
            },
            complete: function() {
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> <?= isset($division) ? 'Actualizar División' : 'Crear División' ?>');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>