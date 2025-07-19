<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/manzanas') ?>">Manzanas</a></li>
<li class="breadcrumb-item active"><?= $manzana ? 'Editar' : 'Agregar' ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-th-large"></i>
                                <?= $manzana ? 'Editar Manzana' : 'Nueva Manzana' ?>
                            </h3>
                        </div>
                        
                        <?= form_open('', ['id' => 'form-manzana', 'class' => 'needs-validation', 'novalidate' => true]) ?>
                        <div class="card-body">
                            
                            <!-- Información básica -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre" class="required">Nombre de la Manzana:</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nombre" 
                                               name="nombre" 
                                               value="<?= $manzana ? $manzana->nombre : '' ?>"
                                               placeholder="Ej: 1, 2, A, B, etc."
                                               required>
                                        <div class="invalid-feedback">
                                            El nombre de la manzana es obligatorio.
                                        </div>
                                        <small class="form-text text-muted">
                                            Identificador único de la manzana dentro del proyecto.
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="proyectos_id" class="required">Proyecto:</label>
                                        <select class="form-control" id="proyectos_id" name="proyectos_id" required>
                                            <option value="">Seleccione un proyecto</option>
                                            <?php foreach ($proyectos as $proyecto): ?>
                                                <option value="<?= $proyecto->id ?>" 
                                                        data-clave="<?= $proyecto->clave ?>"
                                                        data-color="<?= $proyecto->color ?>"
                                                        <?= ($manzana && $manzana->proyectos_id == $proyecto->id) ? 'selected' : '' ?>>
                                                    <?= $proyecto->nombre ?> (<?= $proyecto->clave ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Debe seleccionar un proyecto.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Clave y color -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="clave">Clave (Auto-generada):</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="clave" 
                                               name="clave"
                                               value="<?= $manzana ? $manzana->clave : '' ?>"
                                               readonly 
                                               placeholder="Se genera automáticamente">
                                        <small class="form-text text-muted">
                                            Formato: {Clave Proyecto}-{Nombre Manzana}
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="color">Color de Identificación:</label>
                                        <div class="input-group">
                                            <input type="color" 
                                                   class="form-control form-control-color" 
                                                   id="color" 
                                                   name="color"
                                                   value="<?= $manzana ? $manzana->color : '#3498db' ?>"
                                                   style="width: 60px;">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="color-text"
                                                   value="<?= $manzana ? $manzana->color : '#3498db' ?>"
                                                   readonly>
                                        </div>
                                        <small class="form-text text-muted">
                                            Color para identificación visual en mapas y planos.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="form-group">
                                <label for="descripcion">Descripción:</label>
                                <textarea class="form-control" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="3"
                                          placeholder="Descripción opcional de la manzana..."><?= $manzana ? $manzana->descripcion : '' ?></textarea>
                                <small class="form-text text-muted">
                                    Información adicional sobre la manzana (opcional).
                                </small>
                            </div>

                            <!-- Coordenadas GPS -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Ubicación GPS (Opcional)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="latitud">Latitud:</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="latitud" 
                                                       name="latitud"
                                                       value="<?= $manzana ? $manzana->latitud : '' ?>"
                                                       step="any"
                                                       min="-90"
                                                       max="90"
                                                       placeholder="Ej: 23.2494140">
                                                <small class="form-text text-muted">
                                                    Rango válido: -90 a 90 grados
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="longitud">Longitud:</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="longitud" 
                                                       name="longitud"
                                                       value="<?= $manzana ? $manzana->longitud : '' ?>"
                                                       step="any"
                                                       min="-180"
                                                       max="180"
                                                       placeholder="Ej: -106.4056920">
                                                <small class="form-text text-muted">
                                                    Rango válido: -180 a 180 grados
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Tip:</strong> Puede obtener las coordenadas desde Google Maps haciendo clic derecho en el mapa y seleccionando las coordenadas que aparecen.
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?= base_url('admin/manzanas') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Cancelar
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary" id="btn-guardar">
                                        <i class="fas fa-save"></i>
                                        <?= $manzana ? 'Actualizar Manzana' : 'Guardar Manzana' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Event listeners
    $('#nombre, #proyectos_id').on('change keyup', generarClave);
    $('#color').on('change', actualizarColorTexto);
    $('#proyectos_id').on('change', actualizarColorProyecto);
    $('#form-manzana').on('submit', guardarManzana);
    
    // Generar clave inicial si estamos editando
    <?php if ($manzana): ?>
    generarClave();
    <?php endif; ?>
});

/**
 * Generar clave automáticamente
 */
function generarClave() {
    const nombre = $('#nombre').val().trim().toUpperCase();
    const proyectoSelect = $('#proyectos_id');
    const claveProyecto = proyectoSelect.find('option:selected').data('clave');
    
    if (nombre && claveProyecto) {
        const clave = claveProyecto + '-' + nombre;
        $('#clave').val(clave);
    } else {
        $('#clave').val('');
    }
}

/**
 * Actualizar texto del color
 */
function actualizarColorTexto() {
    const color = $('#color').val();
    $('#color-text').val(color);
}

/**
 * Actualizar color basado en el proyecto seleccionado
 */
function actualizarColorProyecto() {
    const proyectoSelect = $('#proyectos_id');
    const colorProyecto = proyectoSelect.find('option:selected').data('color');
    
    if (colorProyecto && colorProyecto !== '') {
        $('#color').val(colorProyecto);
        $('#color-text').val(colorProyecto);
    }
    
    generarClave();
}

/**
 * Guardar manzana
 */
function guardarManzana(e) {
    e.preventDefault();
    
    // Validar formulario
    if (!e.target.checkValidity()) {
        e.stopPropagation();
        $(e.target).addClass('was-validated');
        return;
    }
    
    const formData = new FormData($('#form-manzana')[0]);
    const esEdicion = <?= $manzana ? 'true' : 'false' ?>;
    const url = esEdicion ? 
        '<?= base_url('admin/manzanas/update') ?>/<?= $manzana ? $manzana->id : '' ?>' :
        '<?= base_url('admin/manzanas/store') ?>';
    
    // Deshabilitar botón y mostrar loading
    const btnGuardar = $('#btn-guardar');
    const textoOriginal = btnGuardar.html();
    btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Redirigir al listado
                    window.location.href = '<?= base_url('admin/manzanas') ?>';
                });
            } else {
                // Mostrar errores de validación
                mostrarErrores(response.errors || {});
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: response.message || 'Por favor corrija los errores en el formulario'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Intente nuevamente.'
            });
        },
        complete: function() {
            // Restaurar botón
            btnGuardar.prop('disabled', false).html(textoOriginal);
        }
    });
}

/**
 * Mostrar errores de validación en el formulario
 */
function mostrarErrores(errores) {
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback.custom').remove();
    
    // Mostrar nuevos errores
    for (const campo in errores) {
        const input = $('#' + campo);
        const mensaje = errores[campo];
        
        input.addClass('is-invalid');
        
        // Agregar mensaje de error personalizado
        const feedback = $('<div class="invalid-feedback custom">' + mensaje + '</div>');
        input.after(feedback);
    }
}

/**
 * Validar coordenadas en tiempo real
 */
$('#latitud').on('input', function() {
    const valor = parseFloat($(this).val());
    const input = $(this);
    
    if (isNaN(valor) || valor < -90 || valor > 90) {
        input.addClass('is-invalid');
    } else {
        input.removeClass('is-invalid');
    }
});

$('#longitud').on('input', function() {
    const valor = parseFloat($(this).val());
    const input = $(this);
    
    if (isNaN(valor) || valor < -180 || valor > 180) {
        input.addClass('is-invalid');
    } else {
        input.removeClass('is-invalid');
    }
});
</script>
<?= $this->endSection() ?>