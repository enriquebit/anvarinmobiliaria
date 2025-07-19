<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('admin/financiamiento') ?>">Financiamiento</a></li>
<li class="breadcrumb-item active"><?= $financiamiento ? 'Editar' : 'Crear' ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php 
$action = $financiamiento ? 'admin/financiamiento/update/' . $financiamiento->id : 'admin/financiamiento/store';
?>

<form action="<?= site_url($action) ?>" method="POST" id="form_financiamiento">
    <?= csrf_field() ?>
    
    <!-- Mensajes de feedback -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Se encontraron errores:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Información General -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información General
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="empresa_id">Empresa *</label>
                                <select name="empresa_id" id="empresa_id" class="form-control" required>
                                    <option value="">Seleccionar empresa...</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?= $empresa->id ?>" 
                                            <?= ($financiamiento && $financiamiento->empresa_id == $empresa->id) ? 'selected' : '' ?>>
                                            <?= $empresa->nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['empresa_id'])): ?>
                                    <div class="text-danger"><?= $errors['empresa_id'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="proyecto_id">Proyecto (Opcional)</label>
                                <select name="proyecto_id" id="proyecto_id" class="form-control">
                                    <option value="0" <?= ($financiamiento && $financiamiento->proyecto_id === null) ? 'selected' : '' ?>>Global (Toda la empresa)</option>
                                    <?php foreach ($proyectos as $proyecto): ?>
                                        <option value="<?= $proyecto->id ?>" 
                                            <?= ($financiamiento && $financiamiento->proyecto_id == $proyecto->id) ? 'selected' : '' ?>>
                                            <?= $proyecto->nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre de la Configuración *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" 
                               value="<?= old('nombre', $financiamiento->nombre ?? '') ?>" required>
                        <?php if (isset($errors['nombre'])): ?>
                            <div class="text-danger"><?= $errors['nombre'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= old('descripcion', $financiamiento->descripcion ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="es_default" id="es_default" class="custom-control-input" 
                                           value="1" <?= old('es_default', $financiamiento->es_default ?? false) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="es_default">
                                        Configuración por defecto
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Solo puede haber una configuración por defecto por empresa
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="activo" id="activo" class="custom-control-input" 
                                           value="1" <?= old('activo', $financiamiento->activo ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="activo">
                                        Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Aplicación por Tipo de Terreno</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="permite_apartado" id="permite_apartado" class="custom-control-input" 
                                           value="1" <?= old('permite_apartado', $financiamiento->permite_apartado ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="permite_apartado">
                                        Permite Apartado
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Si no permite apartado, es venta directa
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prioridad">Prioridad de Aplicación</label>
                                <input type="number" name="prioridad" id="prioridad" class="form-control" 
                                       value="<?= old('prioridad', $financiamiento->prioridad ?? 0) ?>" 
                                       min="0" max="999">
                                <small class="form-text text-muted">
                                    Mayor número = Mayor prioridad
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="aplica_terreno_habitacional" id="aplica_terreno_habitacional" 
                                           class="custom-control-input" value="1" 
                                           <?= old('aplica_terreno_habitacional', $financiamiento->aplica_terreno_habitacional ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="aplica_terreno_habitacional">
                                        Lote Habitacional
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="aplica_terreno_comercial" id="aplica_terreno_comercial" 
                                           class="custom-control-input" value="1" 
                                           <?= old('aplica_terreno_comercial', $financiamiento->aplica_terreno_comercial ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="aplica_terreno_comercial">
                                        Lote Comercial
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="aplica_casa" id="aplica_casa" 
                                           class="custom-control-input" value="1" 
                                           <?= old('aplica_casa', $financiamiento->aplica_casa ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="aplica_casa">
                                        Casa
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="aplica_departamento" id="aplica_departamento" 
                                           class="custom-control-input" value="1" 
                                           <?= old('aplica_departamento', $financiamiento->aplica_departamento ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="aplica_departamento">
                                        Departamento
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Rangos de Aplicación</h5>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="superficie_minima_m2">Superficie Mínima (m²)</label>
                                <input type="number" name="superficie_minima_m2" id="superficie_minima_m2" 
                                       class="form-control" value="<?= old('superficie_minima_m2', $financiamiento->superficie_minima_m2 ?? '') ?>" 
                                       min="0" step="0.01" placeholder="Sin límite mínimo">
                                <small class="form-text text-muted">Área mínima para aplicar esta configuración</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="metros_cuadrados_max">Superficie Máxima (m²)</label>
                                <input type="number" name="metros_cuadrados_max" id="metros_cuadrados_max" 
                                       class="form-control" value="<?= old('metros_cuadrados_max', $financiamiento->metros_cuadrados_max ?? '') ?>" 
                                       min="0" step="0.01" placeholder="Sin límite máximo">
                                <small class="form-text text-muted">Área máxima para aplicar esta configuración</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="monto_minimo">Monto Mínimo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="monto_minimo" id="monto_minimo" 
                                           class="form-control" value="<?= old('monto_minimo', $financiamiento->monto_minimo ?? '') ?>" 
                                           min="0" step="0.01" placeholder="Sin mínimo">
                                </div>
                                <small class="form-text text-muted">Precio mínimo del inmueble</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="monto_maximo">Monto Máximo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="monto_maximo" id="monto_maximo" 
                                           class="form-control" value="<?= old('monto_maximo', $financiamiento->monto_maximo ?? '') ?>" 
                                           min="0" step="0.01" placeholder="Sin máximo">
                                </div>
                                <small class="form-text text-muted">Precio máximo del inmueble</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Vigencia Temporal (Opcional)</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_vigencia_inicio">Fecha de Inicio</label>
                                <input type="date" name="fecha_vigencia_inicio" id="fecha_vigencia_inicio" 
                                       class="form-control" value="<?= old('fecha_vigencia_inicio', $financiamiento->fecha_vigencia_inicio ?? '') ?>">
                                <small class="form-text text-muted">Fecha desde la cual esta configuración es válida</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_vigencia_fin">Fecha de Fin</label>
                                <input type="date" name="fecha_vigencia_fin" id="fecha_vigencia_fin" 
                                       class="form-control" value="<?= old('fecha_vigencia_fin', $financiamiento->fecha_vigencia_fin ?? '') ?>">
                                <small class="form-text text-muted">Fecha hasta la cual esta configuración es válida</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vista Previa -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>
                        Vista Previa
                    </h3>
                </div>
                <div class="card-body">
                    <div id="vista_previa">
                        <div class="text-center text-muted">
                            <i class="fas fa-calculator fa-3x"></i>
                            <p>Complete los campos para ver la vista previa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Configuración de Anticipo -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-hand-holding-usd mr-2"></i>
                        Configuración de Anticipo
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tipo de Anticipo <span class="text-danger">*</span></label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipo_anticipo_porcentaje" name="tipo_anticipo" 
                                   class="custom-control-input" value="porcentaje" 
                                   <?= old('tipo_anticipo', $financiamiento->tipo_anticipo ?? 'porcentaje') == 'porcentaje' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="tipo_anticipo_porcentaje">
                                <i class="fas fa-percentage text-info"></i> Porcentaje sobre Precio
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipo_anticipo_fijo" name="tipo_anticipo" 
                                   class="custom-control-input" value="fijo" 
                                   <?= old('tipo_anticipo', $financiamiento->tipo_anticipo ?? '') == 'fijo' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="tipo_anticipo_fijo">
                                <i class="fas fa-dollar-sign text-success"></i> Monto Fijo Definido
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            El anticipo se calcula según el tipo seleccionado
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group" id="grupo_porcentaje_anticipo">
                                <label for="porcentaje_anticipo">Porcentaje de Anticipo</label>
                                <div class="input-group">
                                    <input type="number" name="porcentaje_anticipo" id="porcentaje_anticipo" 
                                           class="form-control" value="<?= old('porcentaje_anticipo', $financiamiento->porcentaje_anticipo ?? 15) ?>" 
                                           min="0" max="100" step="0.01">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Solo aplica cuando se selecciona Porcentaje
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group" id="grupo_anticipo_fijo">
                                <label for="anticipo_fijo">Monto Fijo de Anticipo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="anticipo_fijo" id="anticipo_fijo" 
                                           class="form-control" value="<?= old('anticipo_fijo', $financiamiento->anticipo_fijo ?? 0) ?>" 
                                           min="0" step="0.01">
                                </div>
                                <small class="form-text text-muted">
                                    Solo aplica cuando se selecciona Monto Fijo
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="apartado_minimo">Apartado Mínimo</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" name="apartado_minimo" id="apartado_minimo" 
                                   class="form-control" value="<?= old('apartado_minimo', $financiamiento->apartado_minimo ?? 5000) ?>" 
                                   min="0" step="0.01">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="enganche_minimo">Enganche Mínimo</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" name="enganche_minimo" id="enganche_minimo" 
                                   class="form-control" value="<?= old('enganche_minimo', $financiamiento->enganche_minimo ?? '') ?>" 
                                   min="0" step="0.01" placeholder="Opcional">
                        </div>
                        <small class="form-text text-muted">
                            Monto mínimo de enganche (aplica cuando tipo es fijo)
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="plazo_liquidar_enganche">Plazo para Liquidar Enganche</label>
                        <div class="input-group">
                            <input type="number" name="plazo_liquidar_enganche" id="plazo_liquidar_enganche" 
                                   class="form-control" value="<?= old('plazo_liquidar_enganche', $financiamiento->plazo_liquidar_enganche ?? 10) ?>" 
                                   min="0" max="365">
                            <div class="input-group-append">
                                <span class="input-group-text">días</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="accion_anticipo_incompleto">¿Qué pasa si no completa el anticipo?</label>
                        <select name="accion_anticipo_incompleto" id="accion_anticipo_incompleto" class="form-control">
                            <option value="liberar_lote" <?= old('accion_anticipo_incompleto', $financiamiento->accion_anticipo_incompleto ?? 'liberar_lote') == 'liberar_lote' ? 'selected' : '' ?>>
                                Lote se libera y apartado se pierde
                            </option>
                            <option value="mantener_apartado" <?= old('accion_anticipo_incompleto', $financiamiento->accion_anticipo_incompleto ?? '') == 'mantener_apartado' ? 'selected' : '' ?>>
                                Mantener apartado activo
                            </option>
                            <option value="aplicar_penalizacion" <?= old('accion_anticipo_incompleto', $financiamiento->accion_anticipo_incompleto ?? '') == 'aplicar_penalizacion' ? 'selected' : '' ?>>
                                Aplicar penalización
                            </option>
                        </select>
                    </div>

                    <div class="form-group" id="grupo_penalizacion" style="display: none;">
                        <label for="penalizacion_apartado">% Penalización sobre Apartado</label>
                        <div class="input-group">
                            <input type="number" name="penalizacion_apartado" id="penalizacion_apartado" 
                                   class="form-control" value="<?= old('penalizacion_apartado', $financiamiento->penalizacion_apartado ?? 0) ?>" 
                                   min="0" max="100" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="penalizacion_enganche_tardio">% Penalización por Enganche Tardío</label>
                        <div class="input-group">
                            <input type="number" name="penalizacion_enganche_tardio" id="penalizacion_enganche_tardio" 
                                   class="form-control" value="<?= old('penalizacion_enganche_tardio', $financiamiento->penalizacion_enganche_tardio ?? 0) ?>" 
                                   min="0" max="100" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Penalización aplicada cuando no se completa el enganche en el plazo establecido
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de Comisión -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-percentage mr-2"></i>
                        Configuración de Comisión
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tipo de Comisión <span class="text-danger">*</span></label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipo_comision_porcentaje" name="tipo_comision" 
                                   class="custom-control-input" value="porcentaje" 
                                   <?= old('tipo_comision', $financiamiento->tipo_comision ?? 'porcentaje') == 'porcentaje' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="tipo_comision_porcentaje">
                                <i class="fas fa-percentage text-warning"></i> Porcentaje sobre Venta
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipo_comision_fijo" name="tipo_comision" 
                                   class="custom-control-input" value="fijo" 
                                   <?= old('tipo_comision', $financiamiento->tipo_comision ?? '') == 'fijo' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="tipo_comision_fijo">
                                <i class="fas fa-dollar-sign text-success"></i> Monto Fijo por Venta
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            La comisión se calcula según el tipo seleccionado
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group" id="grupo_porcentaje_comision">
                                <label for="porcentaje_comision">Porcentaje de Comisión</label>
                                <div class="input-group">
                                    <input type="number" name="porcentaje_comision" id="porcentaje_comision" 
                                           class="form-control" value="<?= old('porcentaje_comision', $financiamiento->porcentaje_comision ?? 7) ?>" 
                                           min="0" max="100" step="0.01">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Solo aplica cuando se selecciona Porcentaje
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group" id="grupo_comision_fija">
                                <label for="comision_fija">Monto Fijo de Comisión</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="comision_fija" id="comision_fija" 
                                           class="form-control" value="<?= old('comision_fija', $financiamiento->comision_fija ?? 0) ?>" 
                                           min="0" step="0.01">
                                </div>
                                <small class="form-text text-muted">
                                    Solo aplica cuando se selecciona Monto Fijo
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="promocion_cero_enganche" id="promocion_cero_enganche" 
                                   class="custom-control-input" value="1" 
                                   <?= old('promocion_cero_enganche', $financiamiento->promocion_cero_enganche ?? false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="promocion_cero_enganche">
                                Promoción Cero Enganche
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Permite ventas sin enganche inicial
                        </small>
                    </div>

                    <div class="form-group" id="grupo_mensualidades_comision" style="display: none;">
                        <label for="mensualidades_comision">Mensualidades para Pago de Comisión</label>
                        <div class="input-group">
                            <input type="number" name="mensualidades_comision" id="mensualidades_comision" 
                                   class="form-control" value="<?= old('mensualidades_comision', $financiamiento->mensualidades_comision ?? 2) ?>" 
                                   min="1" max="12">
                            <div class="input-group-append">
                                <span class="input-group-text">pagos</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Número de mensualidades que se pagan como comisión en promoción cero enganche
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de Financiamiento -->
        <div class="col-md-4">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i>
                        Financiamiento y Políticas
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Selección de tipo de financiamiento -->
                    <div class="form-group">
                        <label>Tipo de Financiamiento <span class="text-danger">*</span></label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipo_financiamiento_msi" name="tipo_financiamiento" 
                                   class="custom-control-input" value="msi" 
                                   <?= old('tipo_financiamiento', $financiamiento->tipo_financiamiento ?? 'msi') == 'msi' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="tipo_financiamiento_msi">
                                <i class="fas fa-smile text-success"></i> Meses Sin Intereses (MSI)
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="tipo_financiamiento_mci" name="tipo_financiamiento" 
                                   class="custom-control-input" value="mci" 
                                   <?= old('tipo_financiamiento', $financiamiento->tipo_financiamiento ?? 'msi') == 'mci' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="tipo_financiamiento_mci">
                                <i class="fas fa-percentage text-warning"></i> Meses Con Intereses (MCI)
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            <strong>Importante:</strong> Solo se usará el tipo seleccionado en los cálculos
                        </small>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group" id="grupo_meses_sin_intereses">
                                <label for="meses_sin_intereses">Meses Sin Intereses</label>
                                <input type="number" name="meses_sin_intereses" id="meses_sin_intereses" 
                                       class="form-control" value="<?= old('meses_sin_intereses', $financiamiento->meses_sin_intereses ?? 0) ?>" 
                                       min="0" max="999">
                                <small class="form-text text-muted">
                                    Solo aplica cuando se selecciona MSI
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group" id="grupo_meses_con_intereses">
                                <label for="meses_con_intereses">Meses Con Intereses</label>
                                <input type="number" name="meses_con_intereses" id="meses_con_intereses" 
                                       class="form-control" value="<?= old('meses_con_intereses', $financiamiento->meses_con_intereses ?? 60) ?>" 
                                       min="0" max="999">
                                <small class="form-text text-muted">
                                    Solo aplica cuando se selecciona MCI
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="porcentaje_interes_anual">Tasa Anual (%) *</label>
                        <div class="input-group">
                            <input type="number" name="porcentaje_interes_anual" id="porcentaje_interes_anual" 
                                   class="form-control" value="<?= old('porcentaje_interes_anual', $financiamiento->porcentaje_interes_anual ?? 0) ?>" 
                                   min="0" max="100" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            La tasa solo aplica cuando se selecciona MCI (Meses Con Intereses)
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="dias_anticipo">
                                    Días de Pago
                                    <i class="fas fa-info-circle text-muted" data-toggle="tooltip" title="Haz clic en el botón del calendario para usar el día actual"></i>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="dias_anticipo" id="dias_anticipo" 
                                           class="form-control" value="<?= old('dias_anticipo', $financiamiento->dias_anticipo ?? 30) ?>" 
                                           min="0" max="365">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="btn_dia_actual" title="Usar día actual">
                                            <i class="fas fa-calendar-day"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Días de gracia para realizar el pago mensual
                                    <span id="dia_actual_info" class="text-info"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="porcentaje_cancelacion">% Cancelación</label>
                                <div class="input-group">
                                    <input type="number" name="porcentaje_cancelacion" id="porcentaje_cancelacion" 
                                           class="form-control" value="<?= old('porcentaje_cancelacion', $financiamiento->porcentaje_cancelacion ?? 100) ?>" 
                                           min="0" max="100" step="0.01">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                    <a href="<?= site_url('admin/financiamiento') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <?php if ($financiamiento): ?>
                        <button type="button" class="btn btn-info" id="btn_duplicar">
                            <i class="fas fa-copy"></i> Duplicar
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // Función para obtener el día actual y configurar el campo
    function configurarDiaActual() {
        var fechaActual = new Date();
        var diaActual = fechaActual.getDate();
        var nombreMes = fechaActual.toLocaleDateString('es-ES', { month: 'long' });
        
        // Mostrar información del día actual
        $('#dia_actual_info').text('(Hoy es día ' + diaActual + ' de ' + nombreMes + ')');
        
        // Si es un registro nuevo (sin configuración previa), usar el día actual como valor predeterminado
        <?php if (!$financiamiento): ?>
        if (!$('#dias_anticipo').val() || $('#dias_anticipo').val() == '30') {
            $('#dias_anticipo').val(diaActual);
        }
        <?php endif; ?>
    }
    
    // Configurar día actual al cargar la página
    configurarDiaActual();
    
    // Botón para usar día actual
    $('#btn_dia_actual').click(function() {
        var fechaActual = new Date();
        var diaActual = fechaActual.getDate();
        $('#dias_anticipo').val(diaActual);
        $('#dias_anticipo').trigger('change'); // Disparar evento para actualizar vista previa
        
        // Mostrar mensaje de confirmación
        $(this).find('i').removeClass('fa-calendar-day').addClass('fa-check text-success');
        setTimeout(function() {
            $('#btn_dia_actual').find('i').removeClass('fa-check text-success').addClass('fa-calendar-day');
        }, 1000);
    });
    
    // Actualizar vista previa en tiempo real
    function actualizarVistaPrevia() {
        var nombre = $('#nombre').val() || 'Sin nombre';
        var tipoAnticipo = $('#tipo_anticipo').val();
        var valorAnticipo = tipoAnticipo === 'porcentaje' ? 
            $('#porcentaje_anticipo').val() + '%' : 
            '$' + parseFloat($('#anticipo_fijo').val() || 0).toLocaleString('es-MX');
        
        var tipoComision = $('#tipo_comision').val();
        var valorComision = tipoComision === 'porcentaje' ? 
            $('#porcentaje_comision').val() + '%' : 
            '$' + parseFloat($('#comision_fija').val() || 0).toLocaleString('es-MX');
        
        var tipoFinanciamiento = $('input[name="tipo_financiamiento"]:checked').val();
        var totalMeses = 0;
        
        if (tipoFinanciamiento === 'msi') {
            totalMeses = parseInt($('#meses_sin_intereses').val() || 0);
        } else {
            totalMeses = parseInt($('#meses_con_intereses').val() || 0);
        }
        
        var apartadoMinimo = parseFloat($('#apartado_minimo').val() || 0).toLocaleString('es-MX');
        var enganchemMinimo = $('#enganche_minimo').val() ? 
            '$' + parseFloat($('#enganche_minimo').val()).toLocaleString('es-MX') : 
            'No especificado';
        var plazoEnganche = $('#plazo_liquidar_enganche').val() || 0;
        var permiteApartado = $('#permite_apartado').is(':checked') ? 'Sí' : 'No';
        var promocionCeroEnganche = $('#promocion_cero_enganche').is(':checked') ? 'Sí' : 'No';
        var mensualidadesComision = $('#mensualidades_comision').val() || 2;
        var penalizacionEngancheTardio = $('#penalizacion_enganche_tardio').val() || 0;
        var diasPago = $('#dias_anticipo').val() || 30;
        
        var html = `
            <div class="info-box">
                <span class="info-box-icon bg-primary">
                    <i class="fas fa-file-alt"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Configuración</span>
                    <span class="info-box-number">${nombre}</span>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <strong>Anticipo:</strong> ${valorAnticipo}<br>
                    <strong>Apartado Mín:</strong> $${apartadoMinimo}<br>
                    <strong>Enganche Mín:</strong> ${enganchemMinimo}<br>
                    <strong>Plazo Enganche:</strong> ${plazoEnganche} días<br>
                    <strong>Comisión:</strong> ${valorComision}<br>
                    <strong>Plazo:</strong> ${totalMeses} meses<br>
                    <strong>Tasa Anual:</strong> ${$('#porcentaje_interes_anual').val() || 0}%<br>
                    <strong>Permite Apartado:</strong> ${permiteApartado}<br>
                    <strong>Promoción Cero Enganche:</strong> ${promocionCeroEnganche}<br>
                    ${promocionCeroEnganche === 'Sí' ? '<strong>Pagos Comisión:</strong> ' + mensualidadesComision + '<br>' : ''}
                    <strong>Días de Pago:</strong> ${diasPago}<br>
                    <strong>Penalización Enganche Tardío:</strong> ${penalizacionEngancheTardio}%
                </div>
            </div>
        `;
        
        $('#vista_previa').html(html);
    }

    // Eventos para actualizar vista previa
    $('#nombre, #tipo_anticipo, #porcentaje_anticipo, #anticipo_fijo, #tipo_comision, #porcentaje_comision, #comision_fija, #meses_sin_intereses, #meses_con_intereses, #porcentaje_interes_anual, #apartado_minimo, #enganche_minimo, #plazo_liquidar_enganche, #permite_apartado, #promocion_cero_enganche, #mensualidades_comision, #penalizacion_enganche_tardio, #dias_anticipo').on('input change', function() {
        actualizarVistaPrevia();
    });
    
    // Agregar estilo para campos deshabilitados
    $('<style>').text('.opacity-50 { opacity: 0.5; }').appendTo('head');

    // Mostrar/ocultar campo de penalización según la acción seleccionada
    $('#accion_anticipo_incompleto').on('change', function() {
        if ($(this).val() === 'aplicar_penalizacion') {
            $('#grupo_penalizacion').slideDown();
        } else {
            $('#grupo_penalizacion').slideUp();
            $('#penalizacion_apartado').val(0);
        }
    }).trigger('change');

    // Mostrar/ocultar campo de mensualidades para comisión según promoción cero enganche
    $('#promocion_cero_enganche').on('change', function() {
        if ($(this).is(':checked')) {
            $('#grupo_mensualidades_comision').slideDown();
        } else {
            $('#grupo_mensualidades_comision').slideUp();
            $('#mensualidades_comision').val(2);
        }
    }).trigger('change');

    // Cargar proyectos al cambiar empresa
    $('#empresa_id').change(function() {
        var empresaId = $(this).val();
        var proyectoSelect = $('#proyecto_id');
        
        if (empresaId) {
            $.get('<?= site_url("admin/financiamiento/getProyectosByEmpresa") ?>/' + empresaId)
            .done(function(response) {
                if (response.success) {
                    var options = '';
                    $.each(response.proyectos, function(i, proyecto) {
                        options += '<option value="' + proyecto.id + '">' + proyecto.nombre + '</option>';
                    });
                    proyectoSelect.html(options);
                }
            });
        } else {
            proyectoSelect.html('<option value="0">Global (Toda la empresa)</option>');
        }
    });

    // Duplicar configuración
    <?php if ($financiamiento): ?>
    $('#btn_duplicar').click(function() {
        Swal.fire({
            title: '¿Duplicar configuración?',
            text: 'Se creará una copia de esta configuración',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, duplicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url("admin/financiamiento/duplicate") ?>', {
                    id: <?= $financiamiento->id ?>
                })
                .done(function(response) {
                    if (response.success) {
                        Swal.fire('¡Duplicado!', response.message, 'success')
                        .then(() => {
                            window.location.href = '<?= site_url("admin/financiamiento/edit") ?>/' + response.new_id;
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }
        });
    });
    <?php endif; ?>

    // Manejar cambio de tipo de financiamiento
    $('input[name="tipo_financiamiento"]').on('change', function() {
        var tipo = $(this).val();
        
        
        if (tipo === 'msi') {
            // MSI: Sin intereses
            $('#grupo_meses_sin_intereses').removeClass('opacity-50');
            $('#meses_sin_intereses').prop('disabled', false);
            $('#grupo_meses_con_intereses').addClass('opacity-50');
            $('#meses_con_intereses').prop('disabled', true); // Deshabilitar MCI
            
            // FORZAR tasa 0% en MSI
            $('#porcentaje_interes_anual').val(0).prop('readonly', true);
            
            if (!window.cargaInicial) {
                showNotification('info', 'MSI: Tasa automáticamente en 0%');
            }
        } else {
            // MCI: Con intereses
            $('#grupo_meses_sin_intereses').addClass('opacity-50');
            $('#meses_sin_intereses').prop('disabled', true); // Deshabilitar MSI
            $('#grupo_meses_con_intereses').removeClass('opacity-50');
            $('#meses_con_intereses').prop('disabled', false);
            
            // Habilitar configuración de tasa
            $('#porcentaje_interes_anual').prop('readonly', false);
            if (!window.cargaInicial && parseFloat($('#porcentaje_interes_anual').val()) === 0) {
                $('#porcentaje_interes_anual').val(1); // Valor por defecto para MCI
            }
            
            if (!window.cargaInicial) {
                showNotification('info', 'MCI: Configure la tasa de interés');
            }
        }
        
        actualizarVistaPrevia();
    });
    
    // Configurar estado inicial sin notificaciones
    window.cargaInicial = true;
    $('input[name="tipo_financiamiento"]:checked').trigger('change');
    
    // Permitir notificaciones después de la carga inicial
    setTimeout(function() {
        window.cargaInicial = false;
    }, 500);
    
    // Manejar cambio de tipo de anticipo
    $('input[name="tipo_anticipo"]').on('change', function() {
        var tipo = $(this).val();
        
        if (tipo === 'porcentaje') {
            // Mostrar porcentaje, ocultar fijo
            $('#grupo_porcentaje_anticipo').removeClass('opacity-50');
            $('#porcentaje_anticipo').prop('disabled', false);
            $('#grupo_anticipo_fijo').addClass('opacity-50');
            $('#anticipo_fijo').prop('disabled', true).val(0);
            showNotification('info', 'Modo Porcentaje: Configure el % de anticipo');
        } else {
            // Mostrar fijo, ocultar porcentaje
            $('#grupo_porcentaje_anticipo').addClass('opacity-50');
            $('#porcentaje_anticipo').prop('disabled', true);
            $('#grupo_anticipo_fijo').removeClass('opacity-50');
            $('#anticipo_fijo').prop('disabled', false);
            showNotification('info', 'Modo Fijo: Configure el monto fijo');
        }
        
        actualizarVistaPrevia();
    });
    
    // Manejar cambio de tipo de comisión
    $('input[name="tipo_comision"]').on('change', function() {
        var tipo = $(this).val();
        
        if (tipo === 'porcentaje') {
            // Mostrar porcentaje, ocultar fijo
            $('#grupo_porcentaje_comision').removeClass('opacity-50');
            $('#porcentaje_comision').prop('disabled', false);
            $('#grupo_comision_fija').addClass('opacity-50');
            $('#comision_fija').prop('disabled', true).val(0);
            showNotification('info', 'Modo Porcentaje: Configure el % de comisión');
        } else {
            // Mostrar fijo, ocultar porcentaje
            $('#grupo_porcentaje_comision').addClass('opacity-50');
            $('#porcentaje_comision').prop('disabled', true);
            $('#grupo_comision_fija').removeClass('opacity-50');
            $('#comision_fija').prop('disabled', false);
            showNotification('info', 'Modo Fijo: Configure el monto fijo');
        }
        
        actualizarVistaPrevia();
    });
    
    // Disparar cambios iniciales
    $('input[name="tipo_anticipo"]:checked').trigger('change');
    $('input[name="tipo_comision"]:checked').trigger('change');
    
    // Validación estricta de financiamiento
    function validarLogicaFinanciamiento() {
        var tipoFinanciamiento = $('input[name="tipo_financiamiento"]:checked').val();
        var mesesSinIntereses = parseInt($('#meses_sin_intereses').val() || 0);
        var mesesConIntereses = parseInt($('#meses_con_intereses').val() || 0);
        var tasaAnual = parseFloat($('#porcentaje_interes_anual').val() || 0);
        
            tipoFinanciamiento,
            mesesSinIntereses,
            mesesConIntereses,
            tasaAnual
        });
        
        // VALIDACIONES ESTRICTAS
        if (tipoFinanciamiento === 'msi') {
            // MSI debe tener tasa 0%
            if (tasaAnual !== 0) {
                $('#porcentaje_interes_anual').val(0);
                showNotification('info', 'MSI: Tasa ajustada automáticamente a 0%');
            }
            if (mesesSinIntereses === 0) {
                showNotification('warning', 'Configure los meses sin interés');
            }
        } else {
            // MCI debe tener tasa > 0%
            if (tasaAnual === 0) {
                showNotification('warning', 'MCI requiere tasa mayor a 0%');
            }
            if (mesesConIntereses === 0) {
                showNotification('warning', 'Configure los meses con interés');
            }
        }
        
        actualizarVistaPrevia();
    }
    
    function showNotification(type, message) {
        var alertClass = type === 'error' ? 'alert-danger' : (type === 'warning' ? 'alert-warning' : 'alert-info');
        var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            '</div>');
        
        $('#vista_previa').prepend(notification);
        setTimeout(function() {
            notification.alert('close');
        }, 4000);
    }
    
    // Eventos para validar lógica financiera
    $('#porcentaje_interes_anual, #meses_sin_intereses, #meses_con_intereses').on('input change', validarLogicaFinanciamiento);
    
    // Validación del formulario
    $('#form_financiamiento').submit(function(e) {
        
        // Log todos los datos del formulario
        var formData = new FormData(this);
        for (let [key, value] of formData.entries()) {
        }
        
        var mesesSinIntereses = parseInt($('#meses_sin_intereses').val() || 0);
        var mesesConIntereses = parseInt($('#meses_con_intereses').val() || 0);
        var totalMeses = mesesSinIntereses + mesesConIntereses;
        var tasaAnual = parseFloat($('#porcentaje_interes_anual').val() || 0);
        
            mesesSinIntereses,
            mesesConIntereses,
            totalMeses,
            tasaAnual
        });
        
        if (totalMeses <= 0) {
            e.preventDefault();
            Swal.fire('Error', 'Debe especificar al menos un mes de financiamiento (con o sin intereses)', 'error');
            return false;
        }
        
        // VALIDACIONES AL GUARDAR
        var tipoFinanciamiento = $('input[name="tipo_financiamiento"]:checked').val();
        
            tipoFinanciamiento,
            mesesSinIntereses,
            mesesConIntereses,
            tasaAnual
        });
        
        if (tipoFinanciamiento === 'msi') {
            // Validaciones para MSI
            if (mesesSinIntereses === 0) {
                e.preventDefault();
                Swal.fire('Error', 'MSI: Debe configurar meses sin interés', 'error');
                return false;
            }
            // Forzar tasa 0% antes de guardar
            $('#porcentaje_interes_anual').val(0);
        } else {
            // Validaciones para MCI
            if (mesesConIntereses === 0) {
                e.preventDefault();
                Swal.fire('Error', 'MCI: Debe configurar meses con interés', 'error');
                return false;
            }
            if (tasaAnual <= 0) {
                e.preventDefault();
                Swal.fire('Error', 'MCI: Debe configurar una tasa mayor a 0%', 'error');
                return false;
            }
        }
        
        if ($('#tipo_anticipo').val() === 'porcentaje' && parseFloat($('#porcentaje_anticipo').val()) > 100) {
            e.preventDefault();
            Swal.fire('Error', 'El porcentaje de anticipo no puede ser mayor a 100%', 'error');
            return false;
        }
        
        if ($('#tipo_comision').val() === 'porcentaje' && parseFloat($('#porcentaje_comision').val()) > 100) {
            e.preventDefault();
            Swal.fire('Error', 'El porcentaje de comisión no puede ser mayor a 100%', 'error');
            return false;
        }
        
        // Mostrar indicador de carga
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        // El formulario continuará con el submit normal
        return true;
    });

    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Actualizar vista previa inicial
    actualizarVistaPrevia();
});
</script>
<?= $this->endSection() ?>