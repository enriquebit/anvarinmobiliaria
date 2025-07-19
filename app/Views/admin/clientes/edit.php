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
    <div class="col-md-12">
        
        <!-- INFORMACIÓN SUPERIOR -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="alert alert-warning">
                    <i class="fas fa-edit mr-2"></i>
                    <strong>Editando Cliente:</strong> <?= $cliente->getNombreCompleto() ?>
                    <br><small>Modifica la información según sea necesario. Los campos marcados con * son obligatorios.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h6 class="text-primary">Completitud del Cliente</h6>
                        <?php 
                        $completitud = 70; // Simplificado por ahora
                        if (!empty($cliente->rfc)) $completitud += 10;
                        if (!empty($cliente->curp)) $completitud += 10;
                        if (!empty($cliente->fecha_nacimiento)) $completitud += 10;
                        ?>
                        <div class="progress mb-2">
                            <div class="progress-bar <?= $completitud >= 80 ? 'bg-success' : ($completitud >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                 role="progressbar" 
                                 style="width: <?= $completitud ?>%"></div>
                        </div>
                        <small><strong><?= $completitud ?>%</strong> completado</small>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="<?= site_url('/admin/clientes/update/' . $cliente->id) ?>" method="POST" enctype="multipart/form-data" id="editClienteForm">
            <?= csrf_field() ?>
            
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="clienteTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="basicos-tab" data-toggle="tab" 
                               href="#basicos" role="tab" aria-controls="basicos" aria-selected="true">
                                <i class="fas fa-user mr-1"></i>Datos Básicos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="direccion-tab" data-toggle="tab" 
                               href="#direccion" role="tab" aria-controls="direccion" aria-selected="false">
                                <i class="fas fa-map-marker-alt mr-1"></i>Dirección
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="laboral-tab" data-toggle="tab" 
                               href="#laboral" role="tab" aria-controls="laboral" aria-selected="false">
                                <i class="fas fa-briefcase mr-1"></i>Info. Laboral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="referencias-tab" data-toggle="tab" 
                               href="#referencias" role="tab" aria-controls="referencias" aria-selected="false">
                                <i class="fas fa-users mr-1"></i>Referencias
                            </a>
                        </li>
                        <li class="nav-item" id="conyuge-tab-li" style="<?= !in_array($cliente->estado_civil, ['casado', 'union_libre']) ? 'display: none;' : '' ?>">
                            <a class="nav-link" id="conyuge-tab" data-toggle="tab" 
                               href="#conyuge" role="tab" aria-controls="conyuge" aria-selected="false">
                                <i class="fas fa-heart mr-1"></i>Cónyuge
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="seguimiento-tab" data-toggle="tab" 
                               href="#seguimiento" role="tab" aria-controls="seguimiento" aria-selected="false">
                                <i class="fas fa-chart-line mr-1"></i>Seguimiento
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="documentos-tab" data-toggle="tab" 
                               href="#documentos" role="tab" aria-controls="documentos" aria-selected="false">
                                <i class="fas fa-file-upload mr-1"></i>Documentos
                                <span class="badge badge-primary ml-1"><?= $estadisticas_documentos['subidos'] ?>/<?= $estadisticas_documentos['total_esenciales'] ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="clienteTabsContent">
                        
                        <!-- TAB: DATOS BÁSICOS -->
                        <div class="tab-pane fade show active" id="basicos" role="tabpanel" aria-labelledby="basicos-tab">
                            
                            <!-- Tipo de Persona -->
                            <div class="form-group">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="persona_fisica" name="persona_moral" value="0" 
                                           class="custom-control-input" <?= old('persona_moral', $cliente->persona_moral) == 0 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="persona_fisica">Persona Física</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="persona_moral" name="persona_moral" value="1" 
                                           class="custom-control-input" <?= old('persona_moral', $cliente->persona_moral) == 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="persona_moral">Persona Moral</label>
                                </div>
                            </div>
                            
                            <!-- PERSONA FÍSICA -->
                            <div id="datos_persona_fisica" style="<?= $cliente->persona_moral == 1 ? 'display: none;' : '' ?>">
                                <h5 class="mb-3">
                                    <i class="fas fa-user text-primary mr-2"></i>
                                    Información Personal
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nombres" class="required">Nombres *</label>
                                            <input type="text" class="form-control" id="nombres" name="nombres" 
                                                   value="<?= old('nombres', $cliente->nombres) ?>" required maxlength="100"
                                                   placeholder="Ej: José Manuel">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="apellido_paterno" class="required">Apellido Paterno *</label>
                                            <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" 
                                                   value="<?= old('apellido_paterno', $cliente->apellido_paterno) ?>" required maxlength="50"
                                                   placeholder="Ej: García">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="apellido_materno" class="required">Apellido Materno *</label>
                                            <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" 
                                                   value="<?= old('apellido_materno', $cliente->apellido_materno) ?>" required maxlength="50"
                                                   placeholder="Ej: López">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="genero">Género</label>
                                            <select class="form-control" id="genero" name="genero">
                                                <option value="M" <?= old('genero', $cliente->genero) === 'M' ? 'selected' : '' ?>>Masculino</option>
                                                <option value="F" <?= old('genero', $cliente->genero) === 'F' ? 'selected' : '' ?>>Femenino</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                                   value="<?= old('fecha_nacimiento', $cliente->fecha_nacimiento && $cliente->fecha_nacimiento instanceof \CodeIgniter\I18n\Time ? $cliente->fecha_nacimiento->format('Y-m-d') : '') ?>"
                                                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nacionalidad">Nacionalidad</label>
                                            <select class="form-control" id="nacionalidad" name="nacionalidad">
                                                <option value="mexicana" <?= old('nacionalidad', $cliente->nacionalidad ?? 'mexicana') === 'mexicana' ? 'selected' : '' ?>>Mexicana</option>
                                                <option value="estadounidense" <?= old('nacionalidad', $cliente->nacionalidad) === 'estadounidense' ? 'selected' : '' ?>>Estadounidense</option>
                                                <option value="canadiense" <?= old('nacionalidad', $cliente->nacionalidad) === 'canadiense' ? 'selected' : '' ?>>Canadiense</option>
                                                <option value="otra" <?= old('nacionalidad', $cliente->nacionalidad) === 'otra' ? 'selected' : '' ?>>Otra</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="profesion">Profesión</label>
                                            <input type="text" class="form-control" id="profesion" name="profesion" 
                                                   value="<?= old('profesion', $cliente->profesion) ?>" maxlength="150"
                                                   placeholder="Ej: Ingeniero">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- LUGAR DE NACIMIENTO -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lugar_nacimiento">Lugar de Nacimiento</label>
                                            <input type="text" class="form-control" id="lugar_nacimiento" name="lugar_nacimiento" 
                                                   value="<?= old('lugar_nacimiento', $cliente->lugar_nacimiento) ?>" maxlength="200"
                                                   placeholder="Ej: Ciudad de México, México">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PERSONA MORAL -->
                            <div id="datos_persona_moral" style="<?= $cliente->persona_moral == 0 ? 'display: none;' : '' ?>">
                                <h5 class="mb-3">
                                    <i class="fas fa-building text-primary mr-2"></i>
                                    Información de la Empresa
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="empresa_razon_social" class="required">Razón Social *</label>
                                            <input type="text" class="form-control" id="empresa_razon_social" name="empresa_razon_social" 
                                                   value="<?= old('empresa_razon_social', $cliente->razon_social) ?>" maxlength="500"
                                                   placeholder="Ej: CONSTRUCTORA XYZ S.A. DE C.V.">
                                            <small class="form-text text-muted">Razón social completa de la empresa</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="empresa_rfc">RFC de la Empresa</label>
                                            <input type="text" class="form-control" id="empresa_rfc" name="empresa_rfc" 
                                                   value="<?= old('empresa_rfc', $persona_moral['rfc_empresa'] ?? '') ?>" maxlength="13"
                                                   placeholder="ABC123456XYZ">
                                            <small class="form-text text-muted">RFC empresarial (12-13 caracteres)</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="empresa_direccion_fiscal">Dirección Fiscal</label>
                                            <textarea class="form-control" id="empresa_direccion_fiscal" name="empresa_direccion_fiscal" 
                                                      rows="2" placeholder="Dirección fiscal completa de la empresa"><?= old('empresa_direccion_fiscal', $persona_moral['direccion_fiscal'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">Dirección que aparece en la constancia de situación fiscal</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="empresa_telefono">Teléfono de la Empresa</label>
                                            <input type="text" class="form-control" id="empresa_telefono" name="empresa_telefono" 
                                                   value="<?= old('empresa_telefono', $persona_moral['telefono_empresa'] ?? '') ?>" maxlength="20"
                                                   placeholder="5551234567">
                                            <small class="form-text text-muted">Teléfono principal de la empresa</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="empresa_email">Email de la Empresa</label>
                                            <input type="email" class="form-control" id="empresa_email" name="empresa_email" 
                                                   value="<?= old('empresa_email', $persona_moral['email_empresa'] ?? '') ?>" maxlength="255"
                                                   placeholder="contacto@empresa.com">
                                            <small class="form-text text-muted">Email corporativo de la empresa</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- DATOS COMUNES -->
                            <h5 class="mb-3 mt-4">
                                <i class="fas fa-envelope text-primary mr-2"></i>
                                Información de Contacto
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="required">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= old('email', $cliente->email) ?>" required maxlength="255"
                                               placeholder="ejemplo@correo.com">
                                        <small class="form-text text-muted">Este será el email de acceso al sistema</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telefono" class="required">Teléfono *</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                                               value="<?= old('telefono', $cliente->telefono) ?>" required maxlength="15"
                                               placeholder="5551234567">
                                        <small class="form-text text-muted">Solo números, 10-13 dígitos</small>
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
                                        <input type="text" class="form-control" id="rfc" name="rfc" 
                                               value="<?= old('rfc', $cliente->rfc) ?>" maxlength="13"
                                               placeholder="ABCD123456XYZ">
                                        <small class="form-text text-muted">12 o 13 caracteres</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="curp">CURP</label>
                                        <input type="text" class="form-control" id="curp" name="curp" 
                                               value="<?= old('curp', $cliente->curp) ?>" maxlength="18"
                                               placeholder="ABCD123456HDFXYZ12">
                                        <small class="form-text text-muted">18 caracteres</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="identificacion">Tipo de Identificación</label>
                                        <select class="form-control" id="identificacion" name="identificacion">
                                            <option value="ine" <?= old('identificacion', $cliente->identificacion) === 'ine' ? 'selected' : '' ?>>INE</option>
                                            <option value="pasaporte" <?= old('identificacion', $cliente->identificacion) === 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                            <option value="cedula" <?= old('identificacion', $cliente->identificacion) === 'cedula' ? 'selected' : '' ?>>Cédula Profesional</option>
                                            <option value="licencia" <?= old('identificacion', $cliente->identificacion) === 'licencia' ? 'selected' : '' ?>>Licencia de Conducir</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="numero_identificacion">Número de Identificación</label>
                                        <input type="text" class="form-control" id="numero_identificacion" name="numero_identificacion" 
                                               value="<?= old('numero_identificacion', $cliente->numero_identificacion) ?>" maxlength="100"
                                               placeholder="Número de la identificación">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="estado_civil">Estado Civil</label>
                                        <select class="form-control" id="estado_civil" name="estado_civil">
                                            <?php foreach ($estados_civiles as $estado): ?>
                                                <option value="<?= $estado['valor'] ?>" 
                                                        <?= old('estado_civil', $cliente->estado_civil) === $estado['valor'] ? 'selected' : '' ?>>
                                                    <?= $estado['nombre'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
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
                                            <?php foreach ($fuentes_informacion as $fuente): ?>
                                                <option value="<?= $fuente['valor'] ?>" 
                                                        <?= old('fuente_informacion', $cliente->fuente_informacion) === $fuente['valor'] ? 'selected' : '' ?>>
                                                    <?= $fuente['nombre'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="otro_origen">Especificar si es "Otro"</label>
                                        <input type="text" class="form-control" id="otro_origen" name="otro_origen" 
                                               value="<?= old('otro_origen', $cliente->otro_origen) ?>" maxlength="200"
                                               placeholder="Especifique si seleccionó 'Otro'"
                                               style="<?= $cliente->fuente_informacion !== 'otro' ? 'display: none;' : '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB: DIRECCIÓN -->
                        <div class="tab-pane fade" id="direccion" role="tabpanel" aria-labelledby="direccion-tab">
                            <?php $direccion = $cliente->getDireccion(); ?>
                            
                            <h5 class="mb-3">
                                <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                                Dirección Principal
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="calle">Calle y Número</label>
                                        <input type="text" class="form-control" id="calle" name="calle" 
                                               value="<?= old('calle', $direccion->domicilio) ?>" maxlength="200">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="numero_interior">Número Interior</label>
                                        <input type="text" class="form-control" id="numero_interior" name="numero_interior" 
                                               value="<?= old('numero_interior', $direccion->numero) ?>" maxlength="10">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="colonia">Colonia/Fraccionamiento</label>
                                        <input type="text" class="form-control" id="colonia" name="colonia" 
                                               value="<?= old('colonia', $direccion->colonia) ?>" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codigo_postal">Código Postal</label>
                                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                               value="<?= old('codigo_postal', $direccion->codigo_postal) ?>" maxlength="5" pattern="[0-9]{5}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="ciudad">Ciudad</label>
                                        <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                               value="<?= old('ciudad', $direccion->ciudad) ?>" maxlength="100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="estado">Estado</label>
                                        <input type="text" class="form-control" id="estado" name="estado" 
                                               value="<?= old('estado', $direccion->estado) ?>" maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tiempo_residencia">Tiempo de Residencia</label>
                                        <input type="text" class="form-control" id="tiempo_residencia" name="tiempo_residencia" 
                                               value="<?= old('tiempo_residencia', $direccion->tiempo_radicando) ?>" placeholder="Ej: 5 años">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tipo_vivienda">Tipo de Vivienda</label>
                                        <select class="form-control" id="tipo_vivienda" name="tipo_vivienda">
                                            <option value="">Seleccionar...</option>
                                            <option value="propia" <?= old('tipo_vivienda', $direccion->tipo_residencia) === 'propia' ? 'selected' : '' ?>>Propia</option>
                                            <option value="rentada" <?= old('tipo_vivienda', $direccion->tipo_residencia) === 'rentada' ? 'selected' : '' ?>>Rentada</option>
                                            <option value="familiar" <?= old('tipo_vivienda', $direccion->tipo_residencia) === 'familiar' ? 'selected' : '' ?>>Familiar</option>
                                            <option value="hipoteca" <?= old('tipo_vivienda', $direccion->tipo_residencia) === 'hipoteca' ? 'selected' : '' ?>>Hipoteca</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB: INFORMACIÓN LABORAL -->
                        <div class="tab-pane fade" id="laboral" role="tabpanel" aria-labelledby="laboral-tab">
                            <?php $laboral = $cliente->getInformacionLaboral(); ?>
                            
                            <h5 class="mb-3">
                                <i class="fas fa-briefcase text-primary mr-2"></i>
                                Información Laboral
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="empresa">Nombre de la Empresa</label>
                                        <input type="text" class="form-control" id="empresa" name="empresa" 
                                               value="<?= old('empresa', $laboral->nombre_empresa) ?>" maxlength="300">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="puesto">Puesto/Cargo</label>
                                        <input type="text" class="form-control" id="puesto" name="puesto" 
                                               value="<?= old('puesto', $laboral->puesto_cargo) ?>" maxlength="200">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="antiguedad">Antigüedad</label>
                                        <input type="text" class="form-control" id="antiguedad" name="antiguedad" 
                                               value="<?= old('antiguedad', $laboral->antiguedad) ?>" placeholder="Ej: 2 años">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="telefono_empresa">Teléfono de Trabajo</label>
                                        <input type="tel" class="form-control" id="telefono_empresa" name="telefono_empresa" 
                                               value="<?= old('telefono_empresa', $laboral->telefono_trabajo) ?>" maxlength="15">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="salario_mensual">Salario Mensual</label>
                                        <input type="number" class="form-control" id="salario_mensual" name="salario_mensual" 
                                               value="<?= old('salario_mensual', $laboral->salario) ?>" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="direccion_empresa">Dirección de la Empresa</label>
                                <textarea class="form-control" id="direccion_empresa" name="direccion_empresa" 
                                          rows="2" maxlength="500"><?= old('direccion_empresa', $laboral->direccion_trabajo) ?></textarea>
                            </div>
                        </div>
                        
                        <!-- TAB: REFERENCIAS -->
                        <div class="tab-pane fade" id="referencias" role="tabpanel" aria-labelledby="referencias-tab">
                            <?php 
                            $referencias = $cliente->getReferencias();
                            $ref1 = $referencias[0];
                            $ref2 = $referencias[1];
                            ?>
                            
                            <h5 class="mb-3">
                                <i class="fas fa-users text-primary mr-2"></i>
                                Referencias Personales
                            </h5>
                            
                            <!-- Referencia 1 -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Referencia #1</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="referencia1_nombre">Nombre Completo</label>
                                                <input type="text" class="form-control" id="referencia1_nombre" name="referencia1_nombre" 
                                                       value="<?= old('referencia1_nombre', $ref1->nombre_completo) ?>" maxlength="200">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="referencia1_telefono">Teléfono</label>
                                                <input type="tel" class="form-control" id="referencia1_telefono" name="referencia1_telefono" 
                                                       value="<?= old('referencia1_telefono', $ref1->telefono) ?>" maxlength="15">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="referencia1_parentesco">Parentesco</label>
                                                <input type="text" class="form-control" id="referencia1_parentesco" name="referencia1_parentesco" 
                                                       value="<?= old('referencia1_parentesco', $ref1->parentesco) ?>" placeholder="Ej: Amigo, Hermano, Compañero" maxlength="50">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Referencia 2 -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Referencia #2</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="referencia2_nombre">Nombre Completo</label>
                                                <input type="text" class="form-control" id="referencia2_nombre" name="referencia2_nombre" 
                                                       value="<?= old('referencia2_nombre', $ref2->nombre_completo) ?>" maxlength="200">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="referencia2_telefono">Teléfono</label>
                                                <input type="tel" class="form-control" id="referencia2_telefono" name="referencia2_telefono" 
                                                       value="<?= old('referencia2_telefono', $ref2->telefono) ?>" maxlength="15">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="referencia2_parentesco">Parentesco</label>
                                                <input type="text" class="form-control" id="referencia2_parentesco" name="referencia2_parentesco" 
                                                       value="<?= old('referencia2_parentesco', $ref2->parentesco) ?>" placeholder="Ej: Amigo, Hermano, Compañero" maxlength="50">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB: CÓNYUGE -->
                        <div class="tab-pane fade" id="conyuge" role="tabpanel" aria-labelledby="conyuge-tab">
                            <?php 
                            // Obtener información del cónyuge usando el método de la entidad
                            $conyuge = $cliente->getInformacionConyuge();
                            ?>
                            <h5 class="mb-3">
                                <i class="fas fa-heart text-danger mr-2"></i>
                                Información del Cónyuge
                            </h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                Esta información aparece cuando selecciona "Casado" o "Unión Libre".
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="conyuge_nombre">Nombre Completo del Cónyuge</label>
                                        <input type="text" class="form-control" id="conyuge_nombre" name="conyuge_nombre" 
                                               value="<?= old('conyuge_nombre', $conyuge->nombre_completo ?? '') ?>" maxlength="300"
                                               placeholder="Ej: María Elena García López">
                                        <small class="form-text text-muted">Nombre completo de la pareja</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="conyuge_telefono">Teléfono del Cónyuge</label>
                                        <input type="text" class="form-control" id="conyuge_telefono" name="conyuge_telefono" 
                                               value="<?= old('conyuge_telefono', $conyuge->telefono ?? '') ?>" maxlength="13"
                                               placeholder="5551234567">
                                        <small class="form-text text-muted">Solo números, 10-13 dígitos</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="conyuge_profesion">Profesión del Cónyuge</label>
                                        <input type="text" class="form-control" id="conyuge_profesion" name="conyuge_profesion" 
                                               value="<?= old('conyuge_profesion', $conyuge->profesion ?? '') ?>" maxlength="150"
                                               placeholder="Ej: Doctora, Ingeniero">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="conyuge_email">Email del Cónyuge</label>
                                        <input type="email" class="form-control" id="conyuge_email" name="conyuge_email" 
                                               value="<?= old('conyuge_email', $conyuge->email ?? '') ?>" maxlength="255"
                                               placeholder="conyuge@correo.com">
                                        <small class="form-text text-muted">Email de contacto de la pareja</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB: SEGUIMIENTO -->
                        <div class="tab-pane fade" id="seguimiento" role="tabpanel" aria-labelledby="seguimiento-tab">
                            <h5 class="mb-3">
                                <i class="fas fa-chart-line text-primary mr-2"></i>
                                Seguimiento del Cliente
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="etapa_proceso">Etapa del Proceso</label>
                                        <select class="form-control" id="etapa_proceso" name="etapa_proceso">
                                            <option value="interesado" <?= old('etapa_proceso', $cliente->etapa_proceso) === 'interesado' ? 'selected' : '' ?>>Interesado</option>
                                            <option value="calificado" <?= old('etapa_proceso', $cliente->etapa_proceso) === 'calificado' ? 'selected' : '' ?>>Calificado</option>
                                            <option value="documentacion" <?= old('etapa_proceso', $cliente->etapa_proceso) === 'documentacion' ? 'selected' : '' ?>>En Documentación</option>
                                            <option value="contrato" <?= old('etapa_proceso', $cliente->etapa_proceso) === 'contrato' ? 'selected' : '' ?>>En Contrato</option>
                                            <option value="cerrado" <?= old('etapa_proceso', $cliente->etapa_proceso) === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fuente_informacion">Fuente de Información</label>
                                        <select class="form-control" id="fuente_informacion" name="fuente_informacion">
                                            <?php foreach ($fuentes_informacion as $fuente): ?>
                                                <option value="<?= $fuente['valor'] ?>" 
                                                        <?= old('fuente_informacion', $cliente->fuente_informacion) === $fuente['valor'] ? 'selected' : '' ?>>
                                                    <?= $fuente['nombre'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notas_internas">Notas Internas</label>
                                <textarea class="form-control" id="notas_internas" name="notas_internas" 
                                          rows="4" placeholder="Notas internas para seguimiento del cliente..."><?= old('notas_internas', $cliente->notas_internas) ?></textarea>
                            </div>
                        </div>
                        
                        <!-- TAB: DOCUMENTOS (FUNCIONALIDAD NO IMPLEMENTADA) -->
                        <div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab">
                            <h5 class="mb-3">
                                <i class="fas fa-file-upload text-primary mr-2"></i>
                                Gestión de Documentos
                                <small class="text-muted ml-2">
                                    (Módulo en desarrollo)
                                </small>
                            </h5>
                            
                            <!-- MENSAJE TEMPORAL -->
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle mr-2"></i>Módulo de Documentos</h5>
                                <p>La funcionalidad de gestión de documentos se encuentra en desarrollo.</p>
                                <p class="mb-0">Próximamente podrás:</p>
                                <ul class="mb-0">
                                    <li>Subir documentos del cliente (INE, Comprobante de domicilio, etc.)</li>
                                    <li>Reemplazar documentos existentes</li>
                                    <li>Ver el progreso de documentación</li>
                                    <li>Descargar documentos subidos</li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- BOTONES DE ACCIÓN -->
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // ✅ RESTAURAR PESTAÑA ACTIVA DESPUÉS DE RELOAD
    const activeTab = sessionStorage.getItem('activeTab');
    if (activeTab) {
        // Remover active de todas las pestañas
        $('.nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        // Activar la pestaña guardada
        $(`#${activeTab}-tab`).addClass('active');
        $(`#${activeTab}`).addClass('show active');
        
        // Limpiar el storage
        sessionStorage.removeItem('activeTab');
    }

    // Manejar cambio de tipo de persona
    $('input[name="persona_moral"]').change(function() {
        var esMoral = $(this).val() == '1';
        
        if (esMoral) {
            $('#datos_persona_fisica').hide();
            $('#datos_persona_moral').show();
        } else {
            $('#datos_persona_moral').hide();
            $('#datos_persona_fisica').show();
        }
    });

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
                $('#basicos-tab').tab('show');
            }
            
            // Limpiar campos del cónyuge (opcional)
            $('#conyuge input, #conyuge textarea').val('');
        }
    }
    
    // Ejecutar al cambiar estado civil
    $('#estado_civil').on('change', function() {
        toggleConyugeTab();
    });
    
    // Ejecutar al cargar la página
    toggleConyugeTab();
    
    // Validación de formulario
    $('#editClienteForm').submit(function(e) {
        var tipoPersona = $('input[name="persona_moral"]:checked').val();
        var valid = true;
        
        // Validaciones básicas
        if (!$('#nombres').val() && tipoPersona == '0') {
            valid = false;
            $('#nombres').focus();
            toastr.error('Los nombres son requeridos para persona física');
        }
        
        if (!$('#empresa_razon_social').val() && tipoPersona == '1') {
            valid = false;
            $('#empresa_razon_social').focus();
            toastr.error('La razón social es requerida para persona moral');
        }
        
        if (!$('#email').val()) {
            valid = false;
            $('#email').focus();
            toastr.error('El email es requerido');
        }
        
        if (!$('#telefono').val()) {
            valid = false;
            $('#telefono').focus();
            toastr.error('El teléfono es requerido');
        }
        
        if (!valid) {
            e.preventDefault();
            return false;
        }
    });

    // ========================================================================
    // FUNCIONES PARA GESTIÓN DE DOCUMENTOS
    // ========================================================================
});

/**
 * Subir documento
 */
function subirDocumento(tipoDocumento) {
    // Crear input file dinámico
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.pdf,.jpg,.jpeg,.png,.gif';
    input.style.display = 'none';
    
    input.addEventListener('change', function(e) {
        const archivo = e.target.files[0];
        if (!archivo) return;
        
        // Validar archivo
        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!tiposPermitidos.includes(archivo.type)) {
            toastr.error('Tipo de archivo no permitido. Use: PDF, JPG, PNG, GIF');
            return;
        }
        
        if (archivo.size > 5 * 1024 * 1024) { // 5MB
            toastr.error('Archivo muy grande. Máximo 5MB permitido');
            return;
        }
        
        // Preparar FormData
        const formData = new FormData();
        formData.append('archivo', archivo);
        formData.append('tipo_documento', tipoDocumento);
        
        // Mostrar loading
        Swal.fire({
            title: 'Subiendo documento...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Enviar archivo
        fetch(`<?= site_url('/admin/clientes/subir-documento/' . $cliente->id) ?>`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                toastr.success(data.message);
                // ✅ MANTENER EN PESTAÑA DOCUMENTOS - NO RECARGAR PÁGINA
                setTimeout(() => {
                    // Actualizar solo la sección de documentos
                    actualizarSeccionDocumentos();
                }, 1500);
            } else {
                toastr.error(data.error || 'Error subiendo documento');
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            toastr.error('Error de conexión');
        });
    });
    
    // Simular click en input
    document.body.appendChild(input);
    input.click();
    document.body.removeChild(input);
}

/**
 * Eliminar documento
 */
function eliminarDocumento(documentoId) {
    Swal.fire({
        title: '¿Eliminar documento?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando documento...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Eliminar documento
            fetch(`<?= site_url('/admin/clientes/eliminar-documento/') ?>${documentoId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    toastr.success(data.message);
                    // ✅ MANTENER EN PESTAÑA DOCUMENTOS - NO RECARGAR PÁGINA
                    setTimeout(() => {
                        // Actualizar solo la sección de documentos
                        actualizarSeccionDocumentos();
                    }, 1500);
                } else {
                    toastr.error(data.error || 'Error eliminando documento');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                toastr.error('Error de conexión');
            });
        }
    });
}

/**
 * Actualizar solo la sección de documentos sin recargar página completa
 */
function actualizarSeccionDocumentos() {
    // ✅ SOLUCIÓN SIMPLE: RECARGAR PÁGINA PERO MANTENER EN PESTAÑA DOCUMENTOS
    // Guardar que estamos en la pestaña documentos
    sessionStorage.setItem('activeTab', 'documentos');
    
    // Recargar página
    location.reload();
}
</script>
<?= $this->endSection() ?>