<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/lotes') ?>">Lotes</a></li>
<li class="breadcrumb-item active">Crear</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plus"></i> 
                                Nuevo Lote
                            </h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/lotes') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>

                        <form id="form-lote" method="post">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Información Básica -->
                                    <div class="col-md-6">
                                        <div class="card card-primary">
                                            <div class="card-header">
                                                <h3 class="card-title">Información Básica</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="numero">Número del Lote <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="numero" name="numero" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="clave">Clave (Auto-generada)</label>
                                                            <input type="text" class="form-control" id="clave" readonly 
                                                                   placeholder="Se genera automáticamente">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="empresas_id">Empresa <span class="text-danger">*</span></label>
                                                    <?= form_dropdown('empresas_id', $empresas, '', [
                                                        'class' => 'form-control',
                                                        'id' => 'empresas_id',
                                                        'required' => true
                                                    ]) ?>
                                                </div>

                                                <div class="form-group">
                                                    <label for="proyectos_id">Proyecto <span class="text-danger">*</span></label>
                                                    <?= form_dropdown('proyectos_id', $proyectos, '', [
                                                        'class' => 'form-control',
                                                        'id' => 'proyectos_id',
                                                        'required' => true,
                                                        'disabled' => empty($proyectos)
                                                    ]) ?>
                                                </div>

                                                <div class="form-group">
                                                    <label for="divisiones_id">División <span class="text-danger">*</span></label>
                                                    <?= form_dropdown('divisiones_id', $divisiones, '', [
                                                        'class' => 'form-control',
                                                        'id' => 'divisiones_id',
                                                        'required' => true,
                                                        'disabled' => empty($divisiones)
                                                    ]) ?>
                                                </div>

                                                <div class="form-group">
                                                    <label for="manzanas_id">Manzana <span class="text-danger">*</span></label>
                                                    <?= form_dropdown('manzanas_id', $manzanas, '', [
                                                        'class' => 'form-control',
                                                        'id' => 'manzanas_id',
                                                        'required' => true,
                                                        'disabled' => empty($manzanas)
                                                    ]) ?>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="categorias_lotes_id">Categoría <span class="text-danger">*</span></label>
                                                            <?= form_dropdown('categorias_lotes_id', $categorias, '', [
                                                                'class' => 'form-control',
                                                                'id' => 'categorias_lotes_id',
                                                                'required' => true
                                                            ]) ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="tipos_lotes_id">Tipo <span class="text-danger">*</span></label>
                                                            <?= form_dropdown('tipos_lotes_id', $tipos, '', [
                                                                'class' => 'form-control',
                                                                'id' => 'tipos_lotes_id',
                                                                'required' => true
                                                            ]) ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="estados_lotes_id">Estado <span class="text-danger">*</span></label>
                                                    <?= form_dropdown('estados_lotes_id', $estados, '1', [
                                                        'class' => 'form-control',
                                                        'id' => 'estados_lotes_id',
                                                        'required' => true
                                                    ]) ?>
                                                </div>

                                                <div class="form-group">
                                                    <label for="descripcion">Descripción</label>
                                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                                              placeholder="Descripción del lote..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dimensiones y Precios -->
                                    <div class="col-md-6">
                                        <div class="card card-success">
                                            <div class="card-header">
                                                <h3 class="card-title">Dimensiones y Precios</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="area">Área Total (m²) <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="area" name="area" 
                                                                   step="0.01" min="0" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="construccion">Construcción (m²)</label>
                                                            <input type="number" class="form-control" id="construccion" name="construccion" 
                                                                   step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="frente">Frente (m)</label>
                                                            <input type="number" class="form-control" id="frente" name="frente" 
                                                                   step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="fondo">Fondo (m)</label>
                                                            <input type="number" class="form-control" id="fondo" name="fondo" 
                                                                   step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="lateral_izquierdo">Lat. Izq. (m)</label>
                                                            <input type="number" class="form-control" id="lateral_izquierdo" name="lateral_izquierdo" 
                                                                   step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="lateral_derecho">Lat. Der. (m)</label>
                                                            <input type="number" class="form-control" id="lateral_derecho" name="lateral_derecho" 
                                                                   step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="precio_m2">Precio por m² <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="number" class="form-control" id="precio_m2" name="precio_m2" 
                                                                       step="0.01" min="0" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="precio_total">Precio Total (Calculado)</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="precio_total" readonly value="0.00">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="color">Color para Mapa</label>
                                                    <input type="color" class="form-control" id="color" name="color" value="#3498db">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Ubicación -->
                                    <div class="col-md-6">
                                        <div class="card card-info">
                                            <div class="card-header">
                                                <h3 class="card-title">Ubicación y Coordenadas</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="coordenadas_poligono">Coordenadas Poligonales <small class="text-muted">(Opcional)</small></label>
                                                    <textarea class="form-control" id="coordenadas_poligono" name="coordenadas_poligono" rows="3"
                                                              placeholder="Coordenadas del polígono para mapas (opcional)..."></textarea>
                                                    <small class="form-text text-muted">Formato: lat1,lng1;lat2,lng2;lat3,lng3... (Campo opcional)</small>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="latitud">Latitud GPS <small class="text-muted">(Opcional)</small></label>
                                                            <input type="text" class="form-control" id="latitud" name="latitud" 
                                                                   placeholder="Ej: 19.432608 (opcional)">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="longitud">Longitud GPS <small class="text-muted">(Opcional)</small></label>
                                                            <input type="text" class="form-control" id="longitud" name="longitud" 
                                                                   placeholder="Ej: -99.133209 (opcional)">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <button type="button" class="btn btn-info btn-sm" id="btn-obtener-ubicacion">
                                                        <i class="fas fa-map-marker-alt"></i> Obtener Ubicación Actual
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-sm" id="btn-limpiar-coordenadas">
                                                        <i class="fas fa-broom"></i> Limpiar Coordenadas
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Amenidades -->
                                    <div class="col-md-6">
                                        <div class="card card-warning">
                                            <div class="card-header">
                                                <h3 class="card-title">Amenidades</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Seleccionar Amenidades</label>
                                                    <div class="row">
                                                        <?php foreach ($amenidades as $id => $nombre): ?>
                                                            <?php if ($id !== ''): ?>
                                                            <div class="col-md-6">
                                                                <div class="icheck-primary">
                                                                    <input type="checkbox" id="amenidad_<?= $id ?>" name="amenidades[]" value="<?= $id ?>">
                                                                    <label for="amenidad_<?= $id ?>"><?= $nombre ?></label>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <button type="button" class="btn btn-secondary btn-sm" id="btn-seleccionar-todas">
                                                        <i class="fas fa-check-square"></i> Seleccionar Todas
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-sm" id="btn-deseleccionar-todas">
                                                        <i class="fas fa-square"></i> Deseleccionar Todas
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    Crear Lote
                                </button>
                                <a href="<?= base_url('admin/lotes') ?>" class="btn btn-secondary">
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
    
    // Calcular precio total automáticamente
    function calcularPrecioTotal() {
        const area = parseFloat($('#area').val()) || 0;
        const precioM2 = parseFloat($('#precio_m2').val()) || 0;
        const total = area * precioM2;
        $('#precio_total').val(total.toFixed(2));
    }

    $('#area, #precio_m2').on('input', calcularPrecioTotal);

    // Filtros en cascada
    $('#empresas_id').change(function() {
        const empresaId = $(this).val();
        $('#proyectos_id').prop('disabled', !empresaId).html('<option value="">Seleccionar proyecto...</option>');
        $('#divisiones_id').prop('disabled', true).html('<option value="">Seleccionar división...</option>');
        $('#manzanas_id').prop('disabled', true).html('<option value="">Seleccionar manzana...</option>');
        
        if (empresaId) {
            cargarProyectos(empresaId);
        }
    });

    $('#proyectos_id').change(function() {
        const proyectoId = $(this).val();
        $('#divisiones_id').prop('disabled', !proyectoId).html('<option value="">Seleccionar división...</option>');
        $('#manzanas_id').prop('disabled', true).html('<option value="">Seleccionar manzana...</option>');
        
        if (proyectoId) {
            cargarDivisiones(proyectoId);
            cargarManzanas(proyectoId);
        }
    });

    $('#proyectos_id, #divisiones_id, #manzanas_id, #numero').change(function() {
        generarClave();
    });

    function cargarProyectos(empresaId) {
        $.get('<?= base_url('admin/lotes/obtener-proyectos-por-empresa') ?>/' + empresaId)
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

    function cargarManzanas(proyectoId) {
        $.get('<?= base_url('admin/lotes/obtener-manzanas-por-proyecto') ?>/' + proyectoId)
            .done(function(response) {
                if (response.success) {
                    $.each(response.manzanas, function(id, nombre) {
                        $('#manzanas_id').append(new Option(nombre, id));
                    });
                    $('#manzanas_id').prop('disabled', false);
                } else {
                    toastr.error('Error al cargar manzanas: ' + response.message);
                }
            })
            .fail(function() {
                toastr.error('Error de conexión al cargar manzanas');
            });
    }

    function cargarDivisiones(proyectoId) {
        $.get('<?= base_url('admin/lotes/obtener-divisiones-por-proyecto') ?>/' + proyectoId)
            .done(function(response) {
                if (response.success) {
                    $.each(response.divisiones, function(id, nombre) {
                        $('#divisiones_id').append(new Option(nombre, id));
                    });
                    $('#divisiones_id').prop('disabled', false);
                } else {
                    toastr.error('Error al cargar divisiones: ' + response.message);
                }
            })
            .fail(function() {
                toastr.error('Error de conexión al cargar divisiones');
            });
    }

    // Generar clave automática completa: PROYECTO-DIVISION-MANZANA-NUMERO
    function generarClave() {
        const proyectoId = $('#proyectos_id').val();
        const divisionId = $('#divisiones_id').val();
        const manzanaId = $('#manzanas_id').val();
        const numero = $('#numero').val();

        if (proyectoId && divisionId && manzanaId && numero) {
            // Obtener texto de los elementos seleccionados
            const proyectoTexto = $('#proyectos_id option:selected').text();
            const divisionTexto = $('#divisiones_id option:selected').text();
            const manzanaTexto = $('#manzanas_id option:selected').text();
            
            // Extraer clave del proyecto (si está entre paréntesis) o usar las primeras 3 letras
            let proyectoClave = proyectoTexto.match(/\(([^)]+)\)$/);
            proyectoClave = proyectoClave ? proyectoClave[1] : proyectoTexto.substring(0, 3).toUpperCase();
            
            // Extraer clave de la división (texto entre paréntesis al final)
            let divisionClave = divisionTexto.match(/\(([^)]+)\)$/);
            divisionClave = divisionClave ? divisionClave[1] : 'DIV' + divisionId;
            
            // Para manzana, usar el nombre completo
            let manzanaNombre = manzanaTexto.replace('Seleccionar manzana...', '').trim();
            if (manzanaNombre === '') {
                manzanaNombre = 'MZ' + manzanaId;
            }
            
            // Generar clave completa: PROYECTO-DIVISION-MANZANA-NUMERO
            const clave = (proyectoClave + '-' + divisionClave + '-' + manzanaNombre + '-' + numero).toUpperCase();
            $('#clave').val(clave);
        }
    }

    // Geolocalización
    $('#btn-obtener-ubicacion').click(function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                $('#latitud').val(position.coords.latitude);
                $('#longitud').val(position.coords.longitude);
                toastr.success('Ubicación obtenida exitosamente');
            }, function(error) {
                // Error silencioso - no mostrar mensaje molesto
            });
        }
        // No mostrar mensaje si no hay geolocalización disponible
    });

    $('#btn-limpiar-coordenadas').click(function() {
        $('#latitud, #longitud, #coordenadas_poligono').val('');
    });

    // Amenidades
    $('#btn-seleccionar-todas').click(function() {
        $('input[name="amenidades[]"]').prop('checked', true);
    });

    $('#btn-deseleccionar-todas').click(function() {
        $('input[name="amenidades[]"]').prop('checked', false);
    });

    // Envío del formulario
    $('#form-lote').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url('admin/lotes/store') ?>',
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
                        window.location.href = '<?= base_url('admin/lotes') ?>';
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error de conexión');
            },
            complete: function() {
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Crear Lote');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>