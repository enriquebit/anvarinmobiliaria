<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
    <?php if (!empty($item['url'])): ?>
        <li class="breadcrumb-item">
            <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
        </li>
    <?php else: ?>
        <li class="breadcrumb-item active"><?= $item['name'] ?></li>
    <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">
        
        <!-- INFORMACIÓN SUPERIOR -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Nuevo Cliente:</strong> Complete la información básica obligatoria. 
                    La información adicional se puede completar después.
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h6 class="text-primary">Estado del Formulario</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-primary" 
                                 role="progressbar" 
                                 style="width: 20%" 
                                 id="progreso-formulario"></div>
                        </div>
                        <small><strong><span id="porcentaje-progreso">20</span>%</strong> completado</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FORMULARIO DE CREACIÓN -->
        <?= form_open_multipart('/admin/clientes/store', [
            'id' => 'form-crear-cliente',
            'class' => 'needs-validation',
            'novalidate' => true,
            'method' => 'POST'
        ]) ?>
        
        <?= csrf_field() ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">
                    <i class="fas fa-user-plus mr-2"></i>
                    Crear Nuevo Cliente
                </h4>
            </div>
            
            <div class="card-body">
                
                <!-- ✅ MOSTRAR ERRORES -->
                <?php if (session('errors')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>Por favor, corrige los siguientes errores:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach (session('errors') as $field => $error): ?>
                                <li><strong><?= ucfirst($field) ?>:</strong> <?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- PESTAÑAS PRINCIPALES -->
                <ul class="nav nav-tabs" id="create-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="datos-basicos-tab" data-toggle="tab" 
                           href="#datos-basicos" role="tab" aria-controls="datos-basicos" aria-selected="true">
                            <i class="fas fa-user mr-1"></i>
                            Datos Básicos *
                        </a>
                    </li>
                    <li class="nav-item" id="datos-empresa-tab-li" style="display: none;">
                        <a class="nav-link" id="datos-empresa-tab" data-toggle="tab" 
                           href="#datos-empresa" role="tab" aria-controls="datos-empresa" aria-selected="false">
                            <i class="fas fa-building mr-1"></i>
                            Datos Empresa *
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="direccion-tab" data-toggle="tab" 
                           href="#direccion" role="tab" aria-controls="direccion" aria-selected="false">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dirección
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="laboral-tab" data-toggle="tab" 
                           href="#laboral" role="tab" aria-controls="laboral" aria-selected="false">
                            <i class="fas fa-briefcase mr-1"></i>
                            Info. Laboral
                        </a>
                    </li>
                    <li class="nav-item" id="conyuge-tab-li" style="display: none;">
                        <a class="nav-link" id="conyuge-tab" data-toggle="tab" 
                           href="#conyuge" role="tab" aria-controls="conyuge" aria-selected="false">
                            <i class="fas fa-heart mr-1"></i>
                            Cónyuge
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="referencias-tab" data-toggle="tab" 
                           href="#referencias" role="tab" aria-controls="referencias" aria-selected="false">
                            <i class="fas fa-users mr-1"></i>
                            Referencias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="seguimiento-tab" data-toggle="tab" 
                           href="#seguimiento" role="tab" aria-controls="seguimiento" aria-selected="false">
                            <i class="fas fa-chart-line mr-1"></i>
                            Seguimiento
                        </a>
                    </li>
                    <li class="nav-item" id="documentos-tab-li">
                        <a class="nav-link" id="documentos-tab" data-toggle="tab" 
                        href="#documentos" role="tab" aria-controls="documentos" aria-selected="false">
                            <i class="fas fa-file-upload mr-1"></i>
                            Documentos
                            <span class="badge badge-secondary ml-1" id="badge-documentos">0/4</span>
                        </a>
                    </li>
                </ul>
                
                <!-- CONTENIDO DE LAS PESTAÑAS -->
                <div class="tab-content mt-3" id="create-tabs-content">
                    
<!-- =====================================================================
                         PESTAÑA 1: DATOS BÁSICOS
                         ===================================================================== -->
                    <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel" aria-labelledby="datos-basicos-tab">
                        
                        <!-- TIPO DE PERSONA -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-user-tag mr-2"></i>
                                    Tipo de Cliente
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required">Tipo de Persona *</label>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" 
                                                       id="persona_fisica" 
                                                       name="persona_moral" 
                                                       value="0" 
                                                       class="custom-control-input" 
                                                       <?= old('persona_moral', '0') == '0' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="persona_fisica">
                                                    <i class="fas fa-user mr-2 text-primary"></i>
                                                    <strong>Persona Física</strong>
                                                    <br><small class="text-muted">Cliente individual</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" 
                                                       id="persona_moral" 
                                                       name="persona_moral" 
                                                       value="1" 
                                                       class="custom-control-input" 
                                                       <?= old('persona_moral') == '1' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="persona_moral">
                                                    <i class="fas fa-building mr-2 text-success"></i>
                                                    <strong>Persona Moral</strong>
                                                    <br><small class="text-muted">Empresa/Organización</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- INFORMACIÓN PERSONAL -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-user mr-2"></i>
                                    <span id="titulo-info-personal">Información del Representante</span>
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombres" class="required">Nombres *</label>
                                    <input type="text" 
                                           class="form-control <?= session('errors.nombres') ? 'is-invalid' : '' ?>" 
                                           id="nombres" 
                                           name="nombres" 
                                           value="<?= old('nombres') ?>" 
                                           required 
                                           maxlength="100"
                                           placeholder="Ej: José Manuel">
                                    <?php if (session('errors.nombres')): ?>
                                        <div class="invalid-feedback"><?= session('errors.nombres') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apellido_paterno" class="required">Apellido Paterno *</label>
                                    <input type="text" 
                                           class="form-control <?= session('errors.apellido_paterno') ? 'is-invalid' : '' ?>" 
                                           id="apellido_paterno" 
                                           name="apellido_paterno" 
                                           value="<?= old('apellido_paterno') ?>" 
                                           required 
                                           maxlength="50"
                                           placeholder="Ej: García">
                                    <?php if (session('errors.apellido_paterno')): ?>
                                        <div class="invalid-feedback"><?= session('errors.apellido_paterno') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apellido_materno" class="required">Apellido Materno *</label>
                                    <input type="text" 
                                           class="form-control <?= session('errors.apellido_materno') ? 'is-invalid' : '' ?>" 
                                           id="apellido_materno" 
                                           name="apellido_materno" 
                                           value="<?= old('apellido_materno') ?>" 
                                           required 
                                           maxlength="50"
                                           placeholder="Ej: López">
                                    <?php if (session('errors.apellido_materno')): ?>
                                        <div class="invalid-feedback"><?= session('errors.apellido_materno') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="required">Email *</label>
                                    <input type="email" 
                                           class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" 
                                           id="email" 
                                           name="email" 
                                           value="<?= old('email') ?>" 
                                           required 
                                           maxlength="255"
                                           placeholder="ejemplo@correo.com">
                                    <?php if (session('errors.email')): ?>
                                        <div class="invalid-feedback"><?= session('errors.email') ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Este será el email de acceso al sistema</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono" class="required">Teléfono *</label>
                                    <input type="text" 
                                           class="form-control <?= session('errors.telefono') ? 'is-invalid' : '' ?>" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="<?= old('telefono') ?>" 
                                           required 
                                           maxlength="13"
                                           placeholder="5551234567">
                                    <?php if (session('errors.telefono')): ?>
                                        <div class="invalid-feedback"><?= session('errors.telefono') ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Solo números, 10-13 dígitos</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- INFORMACIÓN COMPLEMENTARIA -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-success border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Información Complementaria
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="genero">Género</label>
                                    <select class="form-control" id="genero" name="genero">
                                        <option value="M" <?= old('genero') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                        <option value="F" <?= old('genero') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_nacimiento" 
                                           name="fecha_nacimiento" 
                                           value="<?= old('fecha_nacimiento') ?>"
                                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado_civil">Estado Civil</label>
                                    <select class="form-control" id="estado_civil" name="estado_civil">
                                        <option value="soltero" <?= old('estado_civil') === 'soltero' ? 'selected' : '' ?>>Soltero(a)</option>
                                        <option value="casado" <?= old('estado_civil') === 'casado' ? 'selected' : '' ?>>Casado(a)</option>
                                        <option value="union_libre" <?= old('estado_civil') === 'union_libre' ? 'selected' : '' ?>>Unión Libre</option>
                                        <option value="viudo" <?= old('estado_civil') === 'viudo' ? 'selected' : '' ?>>Viudo(a)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nacionalidad">Nacionalidad</label>
                                    <select class="form-control" id="nacionalidad" name="nacionalidad">
                                        <option value="mexicana" <?= old('nacionalidad') === 'mexicana' ? 'selected' : '' ?>>Mexicana</option>
                                        <option value="estadounidense" <?= old('nacionalidad') === 'estadounidense' ? 'selected' : '' ?>>Estadounidense</option>
                                        <option value="canadiense" <?= old('nacionalidad') === 'canadiense' ? 'selected' : '' ?>>Canadiense</option>
                                        <option value="otra" <?= old('nacionalidad') === 'otra' ? 'selected' : '' ?>>Otra</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lugar_nacimiento">Lugar de Nacimiento</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="lugar_nacimiento" 
                                           name="lugar_nacimiento" 
                                           value="<?= old('lugar_nacimiento') ?>" 
                                           maxlength="200"
                                           placeholder="Ej: Ciudad de México, México">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profesion">Profesión</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="profesion" 
                                           name="profesion" 
                                           value="<?= old('profesion') ?>" 
                                           maxlength="150"
                                           placeholder="Ej: Ingeniero">
                                </div>
                            </div>
                        </div>
                        
                        <!-- INFORMACIÓN FISCAL E IDENTIFICACIÓN -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-warning border-bottom pb-2 mb-3">
                                    <i class="fas fa-file-invoice mr-2"></i>
                                    Información Fiscal e Identificación
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rfc">RFC</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="rfc" 
                                           name="rfc" 
                                           value="<?= old('rfc') ?>" 
                                           maxlength="13"
                                           placeholder="ABCD123456XYZ">
                                    <small class="form-text text-muted">12 o 13 caracteres</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="curp">CURP</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="curp" 
                                           name="curp" 
                                           value="<?= old('curp') ?>" 
                                           maxlength="18"
                                           placeholder="ABCD123456HDFXYZ12">
                                    <small class="form-text text-muted">18 caracteres</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="identificacion">Tipo de Identificación</label>
                                    <select class="form-control" id="identificacion" name="identificacion">
                                        <option value="ine" <?= old('identificacion') === 'ine' ? 'selected' : '' ?>>INE</option>
                                        <option value="pasaporte" <?= old('identificacion') === 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                        <option value="cedula" <?= old('identificacion') === 'cedula' ? 'selected' : '' ?>>Cédula Profesional</option>
                                        <option value="licencia" <?= old('identificacion') === 'licencia' ? 'selected' : '' ?>>Licencia de Conducir</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_identificacion">Número de Identificación</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="numero_identificacion" 
                                           name="numero_identificacion" 
                                           value="<?= old('numero_identificacion') ?>" 
                                           maxlength="100"
                                           placeholder="Número de la identificación">
                                </div>
                            </div>
                        </div>
                        
                        <!-- FUENTE DE INFORMACIÓN -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-info border-bottom pb-2 mb-3">
                                    <i class="fas fa-bullhorn mr-2"></i>
                                    Fuente de Información
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fuente_informacion">¿Cómo se enteró de nosotros?</label>
                                    <select class="form-control" id="fuente_informacion" name="fuente_informacion">
                                        <option value="">Seleccionar fuente...</option>
                                        <?php foreach ($fuentes_informacion as $fuente): ?>
                                            <option value="<?= $fuente['valor'] ?>" 
                                                    <?= old('fuente_informacion') === $fuente['valor'] ? 'selected' : '' ?>>
                                                <?= $fuente['nombre'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="otro_origen">Especificar si es "Otro"</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="otro_origen" 
                                           name="otro_origen" 
                                           value="<?= old('otro_origen') ?>" 
                                           maxlength="200"
                                           placeholder="Especifique si seleccionó 'Otro'"
                                           style="display: none;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- NOTAS INTERNAS -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notas_internas">Notas Internas</label>
                                    <textarea class="form-control" 
                                              id="notas_internas" 
                                              name="notas_internas" 
                                              rows="3"
                                              maxlength="1000"
                                              placeholder="Comentarios internos sobre el cliente..."><?= old('notas_internas') ?></textarea>
                                    <small class="form-text text-muted">Máximo 1000 caracteres</small>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
<!-- =====================================================================
                         PESTAÑA 2: DATOS EMPRESA (DINÁMICO)
                         ===================================================================== -->
                    <div class="tab-pane fade" id="datos-empresa" role="tabpanel" aria-labelledby="datos-empresa-tab">
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-success border-bottom pb-2 mb-3">
                                    <i class="fas fa-building mr-2"></i>
                                    Información de la Empresa
                                </h5>
                                <div class="alert alert-success">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Complete los datos de la empresa. Esta información es necesaria para personas morales.
                                </div>
                            </div>
                        </div>
                        
                        <!-- DATOS BÁSICOS DE LA EMPRESA -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-building mr-2"></i>
                                    Datos Básicos de la Empresa
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="empresa_razon_social" class="required">Razón Social *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="empresa_razon_social" 
                                           name="empresa_razon_social" 
                                           value="<?= old('empresa_razon_social') ?>" 
                                           maxlength="500"
                                           placeholder="Ej: CONSTRUCTORA XYZ S.A. DE C.V.">
                                    <small class="form-text text-muted">Razón social completa de la empresa</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="empresa_rfc">RFC de la Empresa</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="empresa_rfc" 
                                           name="empresa_rfc" 
                                           value="<?= old('empresa_rfc') ?>" 
                                           maxlength="13"
                                           placeholder="ABC123456XYZ">
                                    <small class="form-text text-muted">RFC empresarial (12-13 caracteres)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="empresa_direccion_fiscal">Dirección Fiscal</label>
                                    <textarea class="form-control" 
                                              id="empresa_direccion_fiscal" 
                                              name="empresa_direccion_fiscal" 
                                              rows="2"
                                              placeholder="Dirección fiscal completa de la empresa"><?= old('empresa_direccion_fiscal') ?></textarea>
                                    <small class="form-text text-muted">Dirección que aparece en la constancia de situación fiscal</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CONTACTO EMPRESARIAL -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-info border-bottom pb-2 mb-3">
                                    <i class="fas fa-phone mr-2"></i>
                                    Contacto Empresarial
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="empresa_telefono">Teléfono de la Empresa</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="empresa_telefono" 
                                           name="empresa_telefono" 
                                           value="<?= old('empresa_telefono') ?>" 
                                           maxlength="20"
                                           placeholder="5551234567">
                                    <small class="form-text text-muted">Teléfono principal de la empresa</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="empresa_email">Email de la Empresa</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="empresa_email" 
                                           name="empresa_email" 
                                           value="<?= old('empresa_email') ?>" 
                                           maxlength="255"
                                           placeholder="contacto@empresa.com">
                                    <small class="form-text text-muted">Email corporativo de la empresa</small>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
<!-- =====================================================================
                         PESTAÑA 3: DIRECCIÓN
                         ===================================================================== -->
                    <div class="tab-pane fade" id="direccion" role="tabpanel" aria-labelledby="direccion-tab">
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-info border-bottom pb-2 mb-3">
                                    <i class="fas fa-home mr-2"></i>
                                    Dirección del Domicilio
                                </h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    La información de dirección es opcional y se puede completar después.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="direccion_domicilio">Calle y Número</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="direccion_domicilio" 
                                           name="direccion_domicilio" 
                                           value="<?= old('direccion_domicilio') ?>"
                                           placeholder="Ej: Av. Insurgentes Sur 123">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="direccion_numero">Número Interior</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="direccion_numero" 
                                           name="direccion_numero" 
                                           value="<?= old('direccion_numero') ?>"
                                           placeholder="Ej: Depto 4A">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="direccion_colonia">Colonia</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="direccion_colonia" 
                                           name="direccion_colonia" 
                                           value="<?= old('direccion_colonia') ?>"
                                           placeholder="Ej: Del Valle">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="direccion_cp">C.P.</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="direccion_cp" 
                                           name="direccion_cp" 
                                           value="<?= old('direccion_cp') ?>"
                                           placeholder="03100"
                                           maxlength="5">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="direccion_ciudad">Ciudad</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="direccion_ciudad" 
                                           name="direccion_ciudad" 
                                           value="<?= old('direccion_ciudad') ?>"
                                           placeholder="Ciudad de México">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="direccion_estado">Estado</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="direccion_estado" 
                                           name="direccion_estado" 
                                           value="<?= old('direccion_estado') ?>"
                                           placeholder="CDMX">
                                </div>
                            </div>
                        </div>
                        
                        <!-- INFORMACIÓN ADICIONAL DE RESIDENCIA -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-secondary border-bottom pb-2 mb-3">
                                    <i class="fas fa-house-user mr-2"></i>
                                    Información de Residencia
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tiempo_radicando">Tiempo Radicando</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tiempo_radicando" 
                                           name="tiempo_radicando" 
                                           value="<?= old('tiempo_radicando') ?>"
                                           placeholder="Ej: 2 años, 6 meses">
                                    <small class="form-text text-muted">Tiempo viviendo en esta dirección</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_residencia">Tipo de Residencia</label>
                                    <select class="form-control" id="tipo_residencia" name="tipo_residencia">
                                        <option value="propia" <?= old('tipo_residencia') === 'propia' ? 'selected' : '' ?>>Propia</option>
                                        <option value="renta" <?= old('tipo_residencia') === 'renta' ? 'selected' : '' ?>>Renta</option>
                                        <option value="hipoteca" <?= old('tipo_residencia') === 'hipoteca' ? 'selected' : '' ?>>Hipoteca</option>
                                        <option value="padres" <?= old('tipo_residencia') === 'padres' ? 'selected' : '' ?>>Casa de Padres</option>
                                        <option value="familiar" <?= old('tipo_residencia') === 'familiar' ? 'selected' : '' ?>>Casa de Familiar</option>
                                        <option value="otro" <?= old('tipo_residencia') === 'otro' ? 'selected' : '' ?>>Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="residente">Tipo de Residente</label>
                                    <select class="form-control" id="residente" name="residente">
                                        <option value="permanente" <?= old('residente') === 'permanente' ? 'selected' : '' ?>>Permanente</option>
                                        <option value="temporal" <?= old('residente') === 'temporal' ? 'selected' : '' ?>>Temporal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- =====================================================================
                         PESTAÑA 3: INFORMACIÓN LABORAL
                         ===================================================================== -->
                    <div class="tab-pane fade" id="laboral" role="tabpanel" aria-labelledby="laboral-tab">
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-warning border-bottom pb-2 mb-3">
                                    <i class="fas fa-briefcase mr-2"></i>
                                    Información Laboral
                                </h5>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    La información laboral es opcional y ayuda a completar el perfil del cliente.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="laboral_empresa">Empresa donde trabaja</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="laboral_empresa" 
                                           name="laboral_empresa" 
                                           value="<?= old('laboral_empresa') ?>"
                                           placeholder="Nombre de la empresa">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="laboral_puesto">Puesto o Cargo</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="laboral_puesto" 
                                           name="laboral_puesto" 
                                           value="<?= old('laboral_puesto') ?>"
                                           placeholder="Ej: Gerente de Ventas">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="laboral_telefono">Teléfono de Trabajo</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="laboral_telefono" 
                                           name="laboral_telefono" 
                                           value="<?= old('laboral_telefono') ?>"
                                           maxlength="13"
                                           placeholder="5551234567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="laboral_antiguedad">Antigüedad</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="laboral_antiguedad" 
                                           name="laboral_antiguedad" 
                                           value="<?= old('laboral_antiguedad') ?>"
                                           placeholder="Ej: 2 años, 6 meses">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="laboral_direccion">Dirección de Trabajo</label>
                                    <textarea class="form-control" 
                                              id="laboral_direccion" 
                                              name="laboral_direccion" 
                                              rows="2"
                                              placeholder="Dirección completa del lugar de trabajo"><?= old('laboral_direccion') ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                    </div>
<!-- =====================================================================
                         PESTAÑA 4: INFORMACIÓN DEL CÓNYUGE
                         ===================================================================== -->
                    <div class="tab-pane fade" id="conyuge" role="tabpanel" aria-labelledby="conyuge-tab">
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-danger border-bottom pb-2 mb-3">
                                    <i class="fas fa-heart mr-2"></i>
                                    Información del Cónyuge
                                </h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Esta información aparece cuando selecciona "Casado" o "Unión Libre".
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="conyuge_nombre">Nombre Completo del Cónyuge</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="conyuge_nombre" 
                                           name="conyuge_nombre" 
                                           value="<?= old('conyuge_nombre') ?>" 
                                           maxlength="300"
                                           placeholder="Ej: María Elena García López">
                                    <small class="form-text text-muted">Nombre completo de la pareja</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="conyuge_telefono">Teléfono del Cónyuge</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="conyuge_telefono" 
                                           name="conyuge_telefono" 
                                           value="<?= old('conyuge_telefono') ?>" 
                                           maxlength="13"
                                           placeholder="5551234567">
                                    <small class="form-text text-muted">Solo números, 10-13 dígitos</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="conyuge_profesion">Profesión del Cónyuge</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="conyuge_profesion" 
                                           name="conyuge_profesion" 
                                           value="<?= old('conyuge_profesion') ?>" 
                                           maxlength="150"
                                           placeholder="Ej: Doctora, Ingeniero">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="conyuge_email">Email del Cónyuge</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="conyuge_email" 
                                           name="conyuge_email" 
                                           value="<?= old('conyuge_email') ?>" 
                                           maxlength="255"
                                           placeholder="conyuge@correo.com">
                                    <small class="form-text text-muted">Email de contacto de la pareja</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- INFORMACIÓN ADICIONAL DEL ESTADO CIVIL -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="leyenda_civil">Leyenda del Estado Civil</label>
                                    <textarea class="form-control" 
                                              id="leyenda_civil" 
                                              name="leyenda_civil" 
                                              rows="2"
                                              maxlength="500"
                                              placeholder="Información adicional sobre el estado civil o situación familiar..."><?= old('leyenda_civil') ?></textarea>
                                    <small class="form-text text-muted">Detalles adicionales sobre la situación civil (máximo 500 caracteres)</small>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- =====================================================================
                         PESTAÑA 5: REFERENCIAS PERSONALES
                         ===================================================================== -->
                    <div class="tab-pane fade" id="referencias" role="tabpanel" aria-labelledby="referencias-tab">
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-success border-bottom pb-2 mb-3">
                                    <i class="fas fa-users mr-2"></i>
                                    Referencias Personales
                                </h5>
                                <div class="alert alert-success">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Las referencias personales son opcionales pero ayudan a completar el perfil del cliente.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Referencia 1 -->
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user mr-2"></i>
                                            Referencia Personal #1
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="referencia1_nombre">Nombre Completo</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="referencia1_nombre" 
                                                   name="referencia1_nombre" 
                                                   value="<?= old('referencia1_nombre') ?>"
                                                   placeholder="Nombre completo de la referencia">
                                        </div>
                                        <div class="form-group">
                                            <label for="referencia1_parentesco">Parentesco</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="referencia1_parentesco" 
                                                   name="referencia1_parentesco" 
                                                   value="<?= old('referencia1_parentesco') ?>"
                                                   placeholder="Ej: Hermano, Amigo, Compañero">
                                        </div>
                                        <div class="form-group">
                                            <label for="referencia1_telefono">Teléfono</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="referencia1_telefono" 
                                                   name="referencia1_telefono" 
                                                   value="<?= old('referencia1_telefono') ?>"
                                                   maxlength="13"
                                                   placeholder="5551234567">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Referencia 2 -->
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user mr-2"></i>
                                            Referencia Personal #2
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="referencia2_nombre">Nombre Completo</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="referencia2_nombre" 
                                                   name="referencia2_nombre" 
                                                   value="<?= old('referencia2_nombre') ?>"
                                                   placeholder="Nombre completo de la referencia">
                                        </div>
                                        <div class="form-group">
                                            <label for="referencia2_parentesco">Parentesco</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="referencia2_parentesco" 
                                                   name="referencia2_parentesco" 
                                                   value="<?= old('referencia2_parentesco') ?>"
                                                   placeholder="Ej: Prima, Compañero, Vecino">
                                        </div>
                                        <div class="form-group">
                                            <label for="referencia2_telefono">Teléfono</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="referencia2_telefono" 
                                                   name="referencia2_telefono" 
                                                   value="<?= old('referencia2_telefono') ?>"
                                                   maxlength="13"
                                                   placeholder="5551234567">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- =====================================================================
                         PESTAÑA 6: SEGUIMIENTO Y GESTIÓN
                         ===================================================================== -->
                    <div class="tab-pane fade" id="seguimiento" role="tabpanel" aria-labelledby="seguimiento-tab">
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-purple border-bottom pb-2 mb-3">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    Información de Seguimiento y Gestión
                                </h5>
                                <div class="alert alert-secondary">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Esta información ayuda en el seguimiento y gestión del cliente.
                                </div>
                            </div>
                        </div>
                        
                        <!-- INFORMACIÓN DE GESTIÓN -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    Asignación y Gestión
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="etapa_proceso">Etapa del Proceso</label>
                                    <select class="form-control" id="etapa_proceso" name="etapa_proceso">
                                        <option value="interesado" <?= old('etapa_proceso') === 'interesado' ? 'selected' : '' ?>>Interesado</option>
                                        <option value="calificado" <?= old('etapa_proceso') === 'calificado' ? 'selected' : '' ?>>Calificado</option>
                                        <option value="documentacion" <?= old('etapa_proceso') === 'documentacion' ? 'selected' : '' ?>>En Documentación</option>
                                        <option value="contrato" <?= old('etapa_proceso') === 'contrato' ? 'selected' : '' ?>>En Contrato</option>
                                        <option value="cerrado" <?= old('etapa_proceso') === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                    </select>
                                    <small class="form-text text-muted">Etapa actual en el proceso de venta</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asesor_asignado">Asesor Asignado</label>
                                    <select class="form-control" id="asesor_asignado" name="asesor_asignado">
                                        <option value="">Seleccionar asesor...</option>
                                        <?php if (isset($asesores) && !empty($asesores)): ?>
                                            <?php foreach ($asesores as $asesor): ?>
                                                <option value="<?= $asesor['user_id'] ?>" <?= old('asesor_asignado') == $asesor['user_id'] ? 'selected' : '' ?>>
                                                    <?= $asesor['nombres'] ?> - <?= ucfirst($asesor['tipo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="<?= auth()->user()->id ?>" selected>
                                                <?= $usuario_actual ?? auth()->user()->getEmail() ?> - Admin
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Asesor responsable del cliente</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="activado_por">Activado Por</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="activado_por" 
                                           name="activado_por_display" 
                                           value="<?= old('activado_por_display', $usuario_actual ?? auth()->user()->getEmail()) ?>"
                                           readonly>
                                    <input type="hidden" name="activado_por_id" value="<?= auth()->user()->id ?>">
                                    <small class="form-text text-muted">Usuario que está creando al cliente</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- FECHAS DE SEGUIMIENTO -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-info border-bottom pb-2 mb-3">
                                    <i class="fas fa-calendar mr-2"></i>
                                    Fechas de Seguimiento
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_primer_contacto">Fecha de Primer Contacto</label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="fecha_primer_contacto" 
                                           name="fecha_primer_contacto" 
                                           value="<?= old('fecha_primer_contacto', date('Y-m-d\TH:i')) ?>">
                                    <small class="form-text text-muted">Fecha y hora del primer contacto</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_ultima_actividad">Fecha de Última Actividad</label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="fecha_ultima_actividad" 
                                           name="fecha_ultima_actividad" 
                                           value="<?= old('fecha_ultima_actividad') ?>">
                                    <small class="form-text text-muted">Fecha de la última interacción o actividad</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ESTADO Y ACTIVACIÓN -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-warning border-bottom pb-2 mb-3">
                                    <i class="fas fa-toggle-on mr-2"></i>
                                    Estado y Activación
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado_cliente_info">Estado del Cliente</label>
                                    <div class="alert alert-info mb-2">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Automático:</strong> El cliente se crea activo. El estado se sincroniza con la tabla users.active de Shield.
                                    </div>
                                    <input type="text" 
                                           class="form-control" 
                                           id="estado_cliente_info" 
                                           value="Activo (automático al crear)" 
                                           readonly>
                                    <small class="form-text text-muted">El estado se puede cambiar después desde la gestión de usuarios</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_activacion_display">Fecha de Activación</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="fecha_activacion_display" 
                                           value="<?= date('d/m/Y H:i:s') ?> (automático)" 
                                           readonly>
                                    <small class="form-text text-muted">Se establece automáticamente al crear el cliente</small>
                                </div>
                            </div>
                        </div>
                        
                    </div>
<!-- ✅ PESTAÑA DE DOCUMENTOS -->
<div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab">
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Documentos Opcionales:</strong> Puedes subir los documentos ahora o después desde la sección de editar cliente.
        <br><small>Formatos permitidos: PDF, JPG, PNG (máximo 5MB cada uno)</small>
    </div>
    
    <div class="row">
        <!-- INE/Pasaporte -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-id-card mr-2"></i>
                        INE / Pasaporte
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <input type="file" 
                               class="form-control-file documento-input" 
                               id="doc_identificacion" 
                               name="documentos[identificacion_oficial]"
                               accept=".pdf,.jpg,.jpeg,.png"
                               data-tipo="identificacion_oficial"
                               data-nombre="INE/Pasaporte">
                    </div>
                    <div class="documento-preview" id="preview_identificacion"></div>
                    <small class="text-muted">Identificación oficial vigente</small>
                </div>
            </div>
        </div>
        
        <!-- Comprobante de Domicilio -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-home mr-2"></i>
                        Comprobante de Domicilio
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <input type="file" 
                               class="form-control-file documento-input" 
                               id="doc_domicilio" 
                               name="documentos[comprobante_domicilio]"
                               accept=".pdf,.jpg,.jpeg,.png"
                               data-tipo="comprobante_domicilio"
                               data-nombre="Comprobante de Domicilio">
                    </div>
                    <div class="documento-preview" id="preview_domicilio"></div>
                    <small class="text-muted">No mayor a 90 días (luz, agua, gas, teléfono)</small>
                </div>
            </div>
        </div>
        
        <!-- CSF -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Constancia Situación Fiscal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <input type="file" 
                               class="form-control-file documento-input" 
                               id="doc_rfc" 
                               name="documentos[rfc]"
                               accept=".pdf"
                               data-tipo="rfc"
                               data-nombre="CSF">
                    </div>
                    <div class="documento-preview" id="preview_rfc"></div>
                    <small class="text-muted">Descarga desde el portal del SAT (solo PDF)</small>
                </div>
            </div>
        </div>
        
        <!-- CURP -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning   text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-certificate mr-2"></i>
                        CURP
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <input type="file" 
                               class="form-control-file documento-input" 
                               id="doc_curp" 
                               name="documentos[curp]"
                               accept=".pdf,.jpg,.jpeg,.png"
                               data-tipo="curp"
                               data-nombre="CURP">
                    </div>
                    <div class="documento-preview" id="preview_curp"></div>
                    <small class="text-muted">Clave Única de Registro de Población</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resumen de archivos -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-list-check mr-2"></i>
                        Resumen de Documentos
                    </h6>
                    <div id="resumen-documentos">
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los documentos seleccionados aparecerán aquí
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
</div> <!-- /tab-content -->
                
            </div> <!-- /card-body -->
            
            <!-- BOTONES DE ACCIÓN -->
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los campos marcados con (*) son obligatorios
                        </small>
                        <br>
                        <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-secondary mt-2">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-lg" id="btn-guardar">
                            <i class="fas fa-save mr-1"></i>
                            <span id="btn-text">Crear Cliente</span>
                            <span id="btn-spinner" class="spinner-border spinner-border-sm ml-2" style="display: none;"></span>
                        </button>
                    </div>
                </div>
            </div>
            
        </div> <!-- /card -->
        
        <?= form_close() ?>
        
    </div> <!-- /col -->
</div> <!-- /row -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // ✅ DEPURACIÓN - VERIFICAR CONFIGURACIÓN
    
    // =====================================================================
    // ✅ FUNCIONALIDAD PRINCIPAL: MOSTRAR/OCULTAR PESTAÑA DEL CÓNYUGE
    // =====================================================================
    
    function toggleConyugeTab() {
        const estadoCivil = $('#estado_civil').val();
        const tabConyugeLi = $('#conyuge-tab-li');
        
        
        if (estadoCivil === 'casado' || estadoCivil === 'union_libre') {
            tabConyugeLi.show();
        } else {
            tabConyugeLi.hide();
            
            // Si estaba activa la pestaña del cónyuge, cambiar a datos básicos
            if ($('#conyuge-tab').hasClass('active')) {
                $('#datos-basicos-tab').tab('show');
            }
            
            // Limpiar campos del cónyuge (opcional)
            $('#conyuge input, #conyuge textarea').val('');
        }
        
        // Actualizar progreso del formulario
        actualizarProgreso();
    }
    
    // Ejecutar al cambiar estado civil
    $('#estado_civil').on('change', function() {
        toggleConyugeTab();
    });
    
    // Ejecutar al cargar la página
    toggleConyugeTab();
    
    // =====================================================================
    // ✅ FUNCIONALIDAD: MOSTRAR/OCULTAR PESTAÑA DATOS EMPRESA
    // =====================================================================
    
    function toggleEmpresaTab() {
        const esPersonaMoral = $('input[name="persona_moral"]:checked').val();
        const tabEmpresaLi = $('#datos-empresa-tab-li');
        const tituloInfoPersonal = $('#titulo-info-personal');
        
        
        if (esPersonaMoral === '1') {
            tabEmpresaLi.show();
            tituloInfoPersonal.text('Información del Representante Legal');
            
            // Hacer obligatorios los campos de empresa
            $('#empresa_razon_social').prop('required', true);
            
        } else {
            tabEmpresaLi.hide();
            tituloInfoPersonal.text('Información Personal');
            
            // Si estaba activa la pestaña de empresa, cambiar a datos básicos
            if ($('#datos-empresa-tab').hasClass('active')) {
                $('#datos-basicos-tab').tab('show');
            }
            
            // Limpiar campos de empresa y quitar obligatorios
            $('#datos-empresa input, #datos-empresa textarea').val('').prop('required', false);
        }
        
        // Actualizar progreso del formulario
        actualizarProgreso();
    }
    
    // Ejecutar al cambiar tipo de persona
    $('input[name="persona_moral"]').on('change', function() {
        toggleEmpresaTab();
    });
    
    // Ejecutar al cargar la página
    toggleEmpresaTab();
    
    // =====================================================================
    // SISTEMA DE PROGRESO DEL FORMULARIO
    // =====================================================================
    
    function actualizarProgreso() {
        let camposCompletos = 0;
        let totalCampos = 0;
        
        // Campos obligatorios (peso mayor)
        const camposObligatorios = ['#nombres', '#apellido_paterno', '#apellido_materno', '#email', '#telefono'];
        camposObligatorios.forEach(function(campo) {
            totalCampos += 2; // Peso doble para obligatorios
            if ($(campo).val().trim()) {
                camposCompletos += 2;
            }
        });
        
        // Campos opcionales importantes
        const camposOpcionales = [
            '#genero', '#fecha_nacimiento', '#estado_civil', '#nacionalidad',
            '#lugar_nacimiento', '#profesion', '#fuente_informacion', '#rfc', '#curp'
        ];
        
        camposOpcionales.forEach(function(campo) {
            totalCampos += 1;
            if ($(campo).val().trim()) {
                camposCompletos += 1;
            }
        });
        
        // Campos de seguimiento
        const camposSeguimiento = ['#etapa_proceso', '#asesor_asignado', '#fecha_primer_contacto'];
        camposSeguimiento.forEach(function(campo) {
            totalCampos += 0.5;
            if ($(campo).val().trim()) {
                camposCompletos += 0.5;
            }
        });
        
        // Calcular porcentaje
        const porcentaje = Math.round((camposCompletos / totalCampos) * 100);
        
        // Actualizar barra de progreso
        $('#progreso-formulario').css('width', porcentaje + '%');
        $('#porcentaje-progreso').text(porcentaje);
        
        // Cambiar color según progreso
        const barraProgreso = $('#progreso-formulario');
        barraProgreso.removeClass('bg-danger bg-warning bg-primary bg-success');
        
        if (porcentaje < 30) {
            barraProgreso.addClass('bg-danger');
        } else if (porcentaje < 60) {
            barraProgreso.addClass('bg-warning');
        } else if (porcentaje < 85) {
            barraProgreso.addClass('bg-primary');
        } else {
            barraProgreso.addClass('bg-success');
        }
    }
    
    // Actualizar progreso al cambiar cualquier campo
    $('input, select, textarea').on('input change', function() {
        actualizarProgreso();
    });
    
    // =====================================================================
    // FORMATEO AUTOMÁTICO DE CAMPOS
    // =====================================================================
    
    // Formatear teléfonos (solo números)
    $('#telefono, #conyuge_telefono, #laboral_telefono, #referencia1_telefono, #referencia2_telefono').on('input', function() {
        var valor = $(this).val().replace(/\D/g, '');
        if (valor.length > 13) {
            valor = valor.substring(0, 13);
        }
        $(this).val(valor);
    });
    
    // Formatear RFC y CURP (mayúsculas)
    $('#rfc, #curp').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
    
    // Formatear nombres y campos de texto importantes (Title Case)
    $('#nombres, #apellido_paterno, #apellido_materno, #conyuge_nombre, #lugar_nacimiento, #profesion, #conyuge_profesion, #razon_social').on('blur', function() {
        var valor = $(this).val();
        if (valor) {
            $(this).val(toTitleCase(valor));
        }
    });
    
    function toTitleCase(str) {
        return str.replace(/\w\S*/g, function(txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }
    
    // =====================================================================
    // VALIDACIÓN DEL FORMULARIO
    // =====================================================================
    
    $('#form-crear-cliente').on('submit', function(e) {
        
        var formularioValido = true;
        var errores = [];
        
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar campos obligatorios
        var camposObligatorios = [
            { id: '#nombres', name: 'Nombres' },
            { id: '#apellido_paterno', name: 'Apellido Paterno' },
            { id: '#apellido_materno', name: 'Apellido Materno' },
            { id: '#email', name: 'Email' },
            { id: '#telefono', name: 'Teléfono' }
        ];
        
        camposObligatorios.forEach(function(campo) {
            var valor = $(campo.id).val().trim();
            if (!valor) {
                $(campo.id).addClass('is-invalid');
                $(campo.id).after('<div class="invalid-feedback">' + campo.name + ' es obligatorio</div>');
                errores.push(campo.name + ' es obligatorio');
                formularioValido = false;
            }
        });
        
        // Validar email
        var email = $('#email').val().trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('#email').addClass('is-invalid');
            $('#email').after('<div class="invalid-feedback">Formato de email inválido</div>');
            errores.push('Formato de email inválido');
            formularioValido = false;
        }
        
        // Validar teléfono
        var telefono = $('#telefono').val().replace(/\D/g, '');
        if (telefono && (telefono.length < 10 || telefono.length > 13)) {
            $('#telefono').addClass('is-invalid');
            $('#telefono').after('<div class="invalid-feedback">El teléfono debe tener entre 10 y 13 dígitos</div>');
            errores.push('Teléfono debe tener entre 10 y 13 dígitos');
            formularioValido = false;
        }
        
        if (!formularioValido) {
            e.preventDefault();
            
            // Mostrar notificación de errores
            toastr.error('Por favor, corrige los errores en el formulario', 'Errores de Validación');
            
            // Ir a la pestaña de datos básicos donde están los errores
            $('#datos-basicos-tab').tab('show');
            
            // Hacer scroll al primer error
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
            }, 300);
            
            return false;
        }
        
        // ✅ Si llegamos aquí, el formulario es válido
        
        // Mostrar indicador de carga
        $('#btn-guardar').prop('disabled', true);
        $('#btn-text').text('Guardando...');
        $('#btn-spinner').show();
        
        return true;
    });
    
    // =====================================================================
    // NAVEGACIÓN ENTRE PESTAÑAS
    // =====================================================================
    
    // Guardar pestaña activa en localStorage
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        localStorage.setItem('cliente_create_active_tab', $(e.target).attr('href'));
    });
    
    // Restaurar pestaña activa al recargar (útil si hay errores de validación)
    var activeTab = localStorage.getItem('cliente_create_active_tab');
    if (activeTab && $(activeTab).length) {
        $('a[href="' + activeTab + '"]').tab('show');
    }
    
    // =====================================================================
    // COMPORTAMIENTO DE CAMPOS CONDICIONALES
    // =====================================================================
    
    // Mostrar/ocultar campo "Otro origen"
    $('#fuente_informacion').on('change', function() {
        var otroOrigenGroup = $('#otro_origen').closest('.form-group');
        
        if ($(this).val() === 'otro') {
            otroOrigenGroup.show();
            $('#otro_origen').focus();
        } else {
            otroOrigenGroup.hide();
            $('#otro_origen').val('');
        }
    });
    
    // Inicializar estado del campo "otro origen"
    $('#fuente_informacion').trigger('change');
    
    // Auto-completar fecha de primer contacto si está vacía
    if (!$('#fecha_primer_contacto').val()) {
        $('#fecha_primer_contacto').val(new Date().toISOString().slice(0, 16));
    }
    
    // Auto-asignar usuario actual como asesor si no hay otros
    if ($('#asesor_asignado option').length <= 1) {
        $('#asesor_asignado').val($('#asesor_asignado option:last').val());
    }
    
    // =====================================================================
    // FUNCIONES AUXILIARES Y MEJORAS DE UX
    // =====================================================================
    
    // Contador de caracteres para textareas
    $('textarea[maxlength]').on('input', function() {
        var maxLength = $(this).attr('maxlength');
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        var counter = $(this).siblings('.form-text');
        var originalText = counter.text().split('(')[0];
        counter.text(originalText + '(' + remaining + ' restantes)');
        
        if (remaining < 50) {
            counter.removeClass('text-muted').addClass('text-warning');
        } else if (remaining < 0) {
            counter.removeClass('text-warning').addClass('text-danger');
            $(this).val($(this).val().substring(0, maxLength));
        } else {
            counter.removeClass('text-warning text-danger').addClass('text-muted');
        }
    });
    
    // Validación en tiempo real para emails
    $('#email, #conyuge_email').on('blur', function() {
        var email = $(this).val().trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Formato de email inválido</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
    
    // Validación en tiempo real para teléfonos
    $('#telefono, #conyuge_telefono, #laboral_telefono, #referencia1_telefono, #referencia2_telefono').on('blur', function() {
        var telefono = $(this).val().replace(/\D/g, '');
        
        if (telefono && (telefono.length < 10 || telefono.length > 13)) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">El teléfono debe tener entre 10 y 13 dígitos</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
    
    // =====================================================================
    // INDICADORES VISUALES
    // =====================================================================
    
    // Resaltar campos modificados
    $('input, select, textarea').on('change input', function() {
        $(this).addClass('border-primary');
    });
    
    // =====================================================================
    // NOTIFICACIONES Y MENSAJES
    // =====================================================================
    
    // Mostrar mensajes de sesión si existen
    <?php if (session('success')): ?>
        toastr.success('<?= esc(session('success')) ?>', 'Éxito');
    <?php endif; ?>
    
    <?php if (session('error')): ?>
        toastr.error('<?= esc(session('error')) ?>', 'Error');
    <?php endif; ?>
    
    // Inicializar progreso del formulario
    actualizarProgreso();
    

// =====================================================================
// MANEJO DE DOCUMENTOS
// =====================================================================

// Manejar selección de documentos
$('.documento-input').on('change', function() {
    const file = this.files[0];
    const tipo = $(this).data('tipo');
    const nombre = $(this).data('nombre');
    const previewId = 'preview_' + tipo.replace('_', '');
    
    if (file) {
        // Validar tamaño (5MB)
        if (file.size > 5 * 1024 * 1024) {
            toastr.error('Archivo muy grande. Máximo 5MB permitido.');
            $(this).val('');
            return;
        }
        
        // Validar tipo
        const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            toastr.error('Tipo no válido. Solo PDF, JPG y PNG.');
            $(this).val('');
            return;
        }
        
        // Mostrar preview
        $(`#${previewId}`).html(`
            <div class="alert alert-success alert-sm p-2 mb-0">
                <i class="fas fa-check-circle mr-1"></i>
                <strong>${file.name}</strong>
                <br><small>${formatFileSize(file.size)} - ${file.type}</small>
                <button type="button" class="btn btn-sm btn-outline-danger float-right remove-doc" 
                        data-tipo="${tipo}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
        
        actualizarContadorDocs();
        toastr.success(`${nombre} seleccionado correctamente`);
    } else {
        $(`#${previewId}`).empty();
        actualizarContadorDocs();
    }
});

// Remover documento
$(document).on('click', '.remove-doc', function() {
    const tipo = $(this).data('tipo');
    $(`input[data-tipo="${tipo}"]`).val('');
    $(`#preview_${tipo.replace('_', '')}`).empty();
    actualizarContadorDocs();
    toastr.info('Documento removido');
});

// Actualizar contador
function actualizarContadorDocs() {
    let count = 0;
    $('.documento-input').each(function() {
        if ($(this).val()) count++;
    });
    
    $('#badge-documentos').text(`${count}/4`);
    
    // Cambiar color según cantidad
    const badge = $('#badge-documentos');
    badge.removeClass('badge-secondary badge-warning badge-success');
    if (count === 0) badge.addClass('badge-secondary');
    else if (count < 4) badge.addClass('badge-warning');
    else badge.addClass('badge-success');
    
    actualizarResumenDocs();
}

// Actualizar resumen
function actualizarResumenDocs() {
    const resumen = $('#resumen-documentos');
    let html = '';
    
    $('.documento-input').each(function() {
        if ($(this).val()) {
            const file = this.files[0];
            const nombre = $(this).data('nombre');
            html += `
                <div class="mb-1">
                    <i class="fas fa-file text-success mr-2"></i>
                    <strong>${nombre}:</strong> ${file.name} (${formatFileSize(file.size)})
                </div>
            `;
        }
    });
    
    if (html === '') {
        html = '<p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i>Los documentos seleccionados aparecerán aquí</p>';
    }
    
    resumen.html(html);
}

// Formatear tamaño de archivo
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Inicializar
actualizarContadorDocs();
});
</script>
<?= $this->endSection() ?>          