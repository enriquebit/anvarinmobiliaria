<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= $titulo ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <?php foreach ($breadcrumb as $item): ?>
                        <?php if (!empty($item['url'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= $item['name'] ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <?php 
        $esEdicion = !empty($cliente);
        $action = $esEdicion ? "/admin/clientes/update/{$cliente->id}" : '/admin/clientes/store';
        ?>
        
        <!-- Mostrar errores -->
        <?php if (session('errors')): ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Errores en el formulario:</h5>
                <ul class="mb-0">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Mostrar mensajes -->
        <?php if (session('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> <?= session('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= session('success') ?>
            </div>
        <?php endif; ?>
        
        <?= form_open(site_url($action), ['id' => 'form-cliente']) ?>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-edit mr-2"></i>
                        <?= $esEdicion ? 'Editar Cliente' : 'Nuevo Cliente' ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Volver al Listado
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="cliente-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="basicos-tab" data-toggle="tab" href="#basicos">
                                <i class="fas fa-user mr-1"></i> Datos Básicos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="direccion-tab" data-toggle="tab" href="#direccion">
                                <i class="fas fa-home mr-1"></i> Dirección
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="laboral-tab" data-toggle="tab" href="#laboral">
                                <i class="fas fa-briefcase mr-1"></i> Información Laboral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="referencias-tab" data-toggle="tab" href="#referencias">
                                <i class="fas fa-users mr-1"></i> Referencias y Cónyuge
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="adicional-tab" data-toggle="tab" href="#adicional">
                                <i class="fas fa-file-alt mr-1"></i> Información Adicional
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content mt-3">
                        
                        <!-- TAB 1: DATOS BÁSICOS -->
                        <div class="tab-pane fade show active" id="basicos">
                            <div class="row">
                                
                                <!-- Información Personal -->
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-id-card mr-2"></i>Información Personal
                                    </h5>
                                    
                                    <div class="form-group">
                                        <label for="nombres" class="required">Nombres</label>
                                        <?= form_input([
                                            'name' => 'nombres',
                                            'id' => 'nombres',
                                            'class' => 'form-control',
                                            'value' => old('nombres', $cliente->nombres ?? ''),
                                            'required' => true,
                                            'placeholder' => 'Ingresa los nombres'
                                        ]) ?>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="apellido_paterno" class="required">Apellido Paterno</label>
                                                <?= form_input([
                                                    'name' => 'apellido_paterno',
                                                    'id' => 'apellido_paterno',
                                                    'class' => 'form-control',
                                                    'value' => old('apellido_paterno', $cliente->apellido_paterno ?? ''),
                                                    'required' => true
                                                ]) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="apellido_materno" class="required">Apellido Materno</label>
                                                <?= form_input([
                                                    'name' => 'apellido_materno',
                                                    'id' => 'apellido_materno',
                                                    'class' => 'form-control',
                                                    'value' => old('apellido_materno', $cliente->apellido_materno ?? ''),
                                                    'required' => true
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="genero">Género</label>
                                                <?= form_dropdown('genero', [
                                                    'M' => 'Masculino',
                                                    'F' => 'Femenino'
                                                ], old('genero', $cliente->genero ?? 'M'), [
                                                    'id' => 'genero',
                                                    'class' => 'form-control'
                                                ]) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                                <?= form_input([
                                                    'name' => 'fecha_nacimiento',
                                                    'id' => 'fecha_nacimiento',
                                                    'type' => 'date',
                                                    'class' => 'form-control',
                                                    'value' => old('fecha_nacimiento', $cliente && $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('Y-m-d') : '')
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="lugar_nacimiento">Lugar de Nacimiento</label>
                                        <?= form_input([
                                            'name' => 'lugar_nacimiento',
                                            'id' => 'lugar_nacimiento',
                                            'class' => 'form-control',
                                            'value' => old('lugar_nacimiento', $cliente->lugar_nacimiento ?? ''),
                                            'placeholder' => 'Ej: Ciudad de México, México'
                                        ]) ?>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nacionalidad">Nacionalidad</label>
                                                <?= form_dropdown('nacionalidad', [
                                                    'mexicana' => 'Mexicana',
                                                    'estadounidense' => 'Estadounidense',
                                                    'canadiense' => 'Canadiense',
                                                    'otra' => 'Otra'
                                                ], old('nacionalidad', $cliente->nacionalidad ?? 'mexicana'), [
                                                    'id' => 'nacionalidad',
                                                    'class' => 'form-control'
                                                ]) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="profesion">Profesión</label>
                                                <?= form_input([
                                                    'name' => 'profesion',
                                                    'id' => 'profesion',
                                                    'class' => 'form-control',
                                                    'value' => old('profesion', $cliente->profesion ?? ''),
                                                    'placeholder' => 'Ej: Ingeniero, Contador'
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="razon_social">Razón Social (Solo personas morales)</label>
                                        <?= form_input([
                                            'name' => 'razon_social',
                                            'id' => 'razon_social',
                                            'class' => 'form-control',
                                            'value' => old('razon_social', $cliente->razon_social ?? ''),
                                            'placeholder' => 'Opcional - Solo para empresas'
                                        ]) ?>
                                    </div>
                                </div>
                                
                                <!-- Información de Contacto -->
                                <div class="col-md-6">
                                    <h5 class="text-success mb-3">
                                        <i class="fas fa-phone mr-2"></i>Información de Contacto
                                    </h5>
                                    
                                    <div class="form-group">
                                        <label for="email" class="required">Email</label>
                                        <?php if ($esEdicion): ?>
                                            <div class="alert alert-info py-2">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                <strong>Email actual:</strong> <?= $cliente->email ?>
                                                <small class="d-block">El email no se puede modificar en modo edición</small>
                                            </div>
                                        <?php else: ?>
                                            <?= form_input([
                                                'name' => 'email',
                                                'id' => 'email',
                                                'type' => 'email',
                                                'class' => 'form-control',
                                                'value' => old('email'),
                                                'required' => true,
                                                'placeholder' => 'ejemplo@correo.com'
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="telefono" class="required">Teléfono</label>
                                        <?= form_input([
                                            'name' => 'telefono',
                                            'id' => 'telefono',
                                            'class' => 'form-control',
                                            'value' => old('telefono', $cliente->telefono ?? ''),
                                            'required' => true,
                                            'placeholder' => '5551234567',
                                            'maxlength' => '10'
                                        ]) ?>
                                        <small class="form-text text-muted">Solo números, 10 dígitos</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="estado_civil">Estado Civil</label>
                                        <?= form_dropdown('estado_civil', $estados_civiles, old('estado_civil', $cliente->estado_civil ?? 'soltero'), [
                                            'id' => 'estado_civil',
                                            'class' => 'form-control'
                                        ]) ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="fuente_informacion">¿Cómo se enteró de nosotros?</label>
                                        <?= form_dropdown('fuente_informacion', $fuentes_informacion, old('fuente_informacion', $cliente->fuente_informacion ?? 'referido'), [
                                            'id' => 'fuente_informacion',
                                            'class' => 'form-control'
                                        ]) ?>            'otro' => 'Otro'
                                        ], old('fuente_informacion', $cliente->fuente_informacion ?? 'referido'), [
                                            'id' => 'fuente_informacion',
                                            'class' => 'form-control'
                                        ]) ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="otro_origen">Especificar si es "Otro"</label>
                                        <?= form_input([
                                            'name' => 'otro_origen',
                                            'id' => 'otro_origen',
                                            'class' => 'form-control',
                                            'value' => old('otro_origen', $cliente->otro_origen ?? ''),
                                            'placeholder' => 'Especifique si seleccionó "Otro"'
                                        ]) ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="residente">Tipo de Residencia</label>
                                        <?= form_dropdown('residente', [
                                            'permanente' => 'Permanente',
                                            'temporal' => 'Temporal'
                                        ], old('residente', $cliente->residente ?? 'permanente'), [
                                            'id' => 'residente',
                                            'class' => 'form-control'
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 2: DIRECCIÓN -->
                        <div class="tab-pane fade" id="direccion">
                            <h5 class="text-warning mb-3">
                                <i class="fas fa-home mr-2"></i>Dirección del Cliente
                            </h5>
                            
                            <?php 
                            $direccion = $cliente ? $cliente->getDireccion() : [];
                            ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="direccion_domicilio">Calle y Número</label>
                                        <?= form_input([
                                            'name' => 'direccion_domicilio',
                                            'id' => 'direccion_domicilio',
                                            'class' => 'form-control',
                                            'value' => old('direccion_domicilio', $direccion['domicilio'] ?? ''),
                                            'placeholder' => 'Ej: Av. Insurgentes Sur 123'
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="direccion_numero">Número Interior</label>
                                        <?= form_input([
                                            'name' => 'direccion_numero',
                                            'id' => 'direccion_numero',
                                            'class' => 'form-control',
                                            'value' => old('direccion_numero', $direccion['numero'] ?? ''),
                                            'placeholder' => 'Ej: Depto 4A'
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="direccion_colonia">Colonia</label>
                                        <?= form_input([
                                            'name' => 'direccion_colonia',
                                            'id' => 'direccion_colonia',
                                            'class' => 'form-control',
                                            'value' => old('direccion_colonia', $direccion['colonia'] ?? ''),
                                            'placeholder' => 'Ej: Del Valle'
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="direccion_cp">Código Postal</label>
                                        <?= form_input([
                                            'name' => 'direccion_cp',
                                            'id' => 'direccion_cp',
                                            'class' => 'form-control',
                                            'value' => old('direccion_cp', $direccion['codigo_postal'] ?? ''),
                                            'placeholder' => '03100',
                                            'maxlength' => '5'
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="direccion_ciudad">Ciudad</label>
                                        <?= form_input([
                                            'name' => 'direccion_ciudad',
                                            'id' => 'direccion_ciudad',
                                            'class' => 'form-control',
                                            'value' => old('direccion_ciudad', $direccion['ciudad'] ?? ''),
                                            'placeholder' => 'Ej: Ciudad de México'
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="direccion_estado">Estado</label>
                                        <?= form_input([
                                            'name' => 'direccion_estado',
                                            'id' => 'direccion_estado',
                                            'class' => 'form-control',
                                            'value' => old('direccion_estado', $direccion['estado'] ?? ''),
                                            'placeholder' => 'Ej: CDMX'
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="direccion_tipo_residencia">Tipo de Residencia</label>
                                        <?= form_dropdown('direccion_tipo_residencia', [
                                            'propia' => 'Casa Propia',
                                            'renta' => 'Casa en Renta',
                                            'hipoteca' => 'Casa con Hipoteca',
                                            'padres' => 'Casa de Padres',
                                            'otro' => 'Otro'
                                        ], old('direccion_tipo_residencia', $direccion['tipo_residencia'] ?? 'propia'), [
                                            'id' => 'direccion_tipo_residencia',
                                            'class' => 'form-control'
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="direccion_tiempo_radicando">Tiempo Radicando</label>
                                <?= form_input([
                                    'name' => 'direccion_tiempo_radicando',
                                    'id' => 'direccion_tiempo_radicando',
                                    'class' => 'form-control',
                                    'value' => old('direccion_tiempo_radicando', $direccion['tiempo_radicando'] ?? ''),
                                    'placeholder' => 'Ej: 5 años, 2 meses'
                                ]) ?>
                            </div>
                        </div>
                        
                        <!-- TAB 3: INFORMACIÓN ADICIONAL -->
                        <div class="tab-pane fade" id="adicional">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-file-alt mr-2"></i>Información Adicional
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rfc">RFC</label>
                                        <?= form_input([
                                            'name' => 'rfc',
                                            'id' => 'rfc',
                                            'class' => 'form-control',
                                            'value' => old('rfc', $cliente->rfc ?? ''),
                                            'placeholder' => 'ABCD123456XYZ',
                                            'maxlength' => '13'
                                        ]) ?>
                                        <small class="form-text text-muted">12 o 13 caracteres</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="curp">CURP</label>
                                        <?= form_input([
                                            'name' => 'curp',
                                            'id' => 'curp',
                                            'class' => 'form-control',
                                            'value' => old('curp', $cliente->curp ?? ''),
                                            'placeholder' => 'ABCD123456HDFXYZ12',
                                            'maxlength' => '18'
                                        ]) ?>
                                        <small class="form-text text-muted">18 caracteres</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="identificacion">Tipo de Identificación</label>
                                        <?= form_dropdown('identificacion', [
                                            'ine' => 'INE',
                                            'pasaporte' => 'Pasaporte',
                                            'cedula' => 'Cédula Profesional',
                                            'licencia' => 'Licencia de Conducir'
                                        ], old('identificacion', $cliente->identificacion ?? 'ine'), [
                                            'id' => 'identificacion',
                                            'class' => 'form-control'
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="numero_identificacion">Número de Identificación</label>
                                        <?= form_input([
                                            'name' => 'numero_identificacion',
                                            'id' => 'numero_identificacion',
                                            'class' => 'form-control',
                                            'value' => old('numero_identificacion', $cliente->numero_identificacion ?? ''),
                                            'placeholder' => 'Número de la identificación'
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notas_internas">Notas Internas</label>
                                <?= form_textarea([
                                    'name' => 'notas_internas',
                                    'id' => 'notas_internas',
                                    'class' => 'form-control',
                                    'rows' => '4',
                                    'value' => old('notas_internas', $cliente->notas_internas ?? ''),
                                    'placeholder' => 'Comentarios internos sobre el cliente...'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Footer con botones -->
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-default">
                                <i class="fas fa-times mr-1"></i>
                                Cancelar
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i>
                                <?= $esEdicion ? 'Actualizar Cliente' : 'Crear Cliente' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
        </form>
        
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.required:after {
    content: " *";
    color: #e74c3c;
    font-weight: bold;
}

.nav-tabs .nav-link {
    border-bottom: 3px solid transparent;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #007bff;
    font-weight: bold;
}

.alert {
    border-radius: 0.5rem;
}

.form-group label {
    font-weight: 500;
    color: #495057;
}

h5 {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // Formatear teléfono (solo números)
    $('#telefono').on('input', function() {
        var valor = $(this).val().replace(/\D/g, '');
        $(this).val(valor);
    });
    
    // Formatear código postal (solo números)
    $('#direccion_cp').on('input', function() {
        var valor = $(this).val().replace(/\D/g, '');
        $(this).val(valor);
    });
    
    // Formatear RFC y CURP (mayúsculas)
    $('#rfc, #curp').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
    
    // Mostrar/ocultar campo "otro_origen" según selección
    $('#fuente_informacion').on('change', function() {
        if ($(this).val() === 'otro') {
            $('#otro_origen').closest('.form-group').show();
            $('#otro_origen').attr('required', true);
        } else {
            $('#otro_origen').closest('.form-group').hide();
            $('#otro_origen').attr('required', false);
        }
    });
    
    // Activar al cargar la página
    $('#fuente_informacion').trigger('change');
    
    // Auto-focus en el primer campo
    setTimeout(function() {
        $('#nombres').focus();
    }, 100);
    
    // Confirmación antes de enviar
    $('#form-cliente').on('submit', function(e) {
        var nombres = $('#nombres').val().trim();
        var apellidoP = $('#apellido_paterno').val().trim();
        var email = $('#email').val().trim();
        var telefono = $('#telefono').val().trim();
        
        if (!nombres || !apellidoP || (!email && !<?= $esEdicion ? 'true' : 'false' ?>) || !telefono) {
            e.preventDefault();
            alert('Por favor, completa todos los campos obligatorios marcados con *');
            return false;
        }
        
        return true;
    });
    
});
</script>
<?= $this->endSection() ?>