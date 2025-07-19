<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>Mi Perfil<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Mi Perfil</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Mi Perfil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Alert para cambio de contraseña forzoso -->
        <?php if ($debeCambiarPassword): ?>
        <div class="alert alert-warning alert-dismissible">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Cambio de contraseña requerido</h5>
            Debe cambiar su contraseña antes de continuar usando el sistema.
        </div>
        <?php endif; ?>

        <!-- Pestañas del perfil -->
        <div class="card">
            <div class="card-header p-2">
                <div class="row">
                    <div class="col-4 text-center">
                        <a href="#perfil" class="btn btn-sm btn-outline-primary active" data-toggle="tab">
                            <i class="fas fa-user"></i> Perfil
                        </a>
                    </div>
                    <div class="col-4 text-center">
                        <a href="#config" class="btn btn-sm btn-outline-primary" data-toggle="tab">
                            <i class="fas fa-cog"></i> Config
                        </a>
                    </div>
                    <div class="col-4 text-center">
                        <a href="#documentos" class="btn btn-sm btn-outline-primary" data-toggle="tab">
                            <i class="fas fa-file-alt"></i> Documentos
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- TAB PERFIL -->
                    <div class="active tab-pane" id="perfil">
                        <div class="row">
                            <div class="col-md-3">
                                <!-- Foto de perfil -->
                                <div class="card card-primary card-outline">
                                    <div class="card-body box-profile">
                                        <div class="text-center">
                                            <img class="profile-user-img img-fluid img-circle" 
                                                 id="fotoPerfil"
                                                 src="<?= getUserAvatarUrl() ?>" 
                                                 alt="Foto de perfil">
                                        </div>
                                        <h3 class="profile-username text-center"><?= $staff->getNombreFormateado() ?></h3>
                                        <p class="text-muted text-center"><?= userRole() ?></p>
                                        
                                        <!-- Upload foto de perfil -->
                                        <form id="formFotoPerfil" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="inputFotoPerfil">Cambiar foto de perfil</label>
                                                <input type="file" class="form-control-file" id="inputFotoPerfil" name="foto_perfil" accept="image/jpeg,image/jpg,image/png">
                                                <small class="form-text text-muted">Tamaño máximo: 2MB. Formato: JPG, PNG<br><small class="text-success">Vista previa se mostrará en la foto de perfil</small></small>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block" id="btnSubirFoto">
                                                <i class="fas fa-upload"></i> Subir Foto
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-9">
                                <!-- Información personal -->
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Información Personal</h3>
                                    </div>
                                    <div class="card-body">
                                        <form id="formInfoPersonal">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="nombres">Nombres</label>
                                                        <input type="text" class="form-control" id="nombres" name="nombres" value="<?= esc($staff->nombres) ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="apellido_paterno">Apellido Paterno</label>
                                                        <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?= esc($staff->apellido_paterno) ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="apellido_materno">Apellido Materno</label>
                                                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?= esc($staff->apellido_materno) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                                               value="<?= $staff->fecha_nacimiento ? $staff->fecha_nacimiento->format('Y-m-d') : '' ?>">
                                                        <small class="form-text text-muted">
                                                            <?php if ($staff->getEdad()): ?>
                                                                Edad: <?= $staff->getEdad() ?> años
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="email">Email de Contacto</label>
                                                        <input type="email" class="form-control" id="email" name="email" value="<?= esc($staff->email) ?>" readonly>
                                                        <small class="form-text text-muted">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            Para cambiar tu email de acceso, ve a la pestaña 
                                                            <a href="#config" data-toggle="tab" class="text-primary">
                                                                <strong>Config</strong>
                                                            </a>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="telefono">Teléfono</label>
                                                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= esc($staff->telefono) ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="agencia">Agencia</label>
                                                        <input type="text" class="form-control" id="agencia" name="agencia" value="<?= esc($staff->agencia) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="rfc">RFC</label>
                                                        <input type="text" class="form-control" id="rfc" name="rfc" value="<?= esc($staff->rfc) ?>" maxlength="13">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> Guardar Cambios
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB CONFIG -->
                    <div class="tab-pane" id="config">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Cambiar Contraseña</h3>
                            </div>
                            <div class="card-body">
                                <form id="formCambiarPassword">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Modo desarrollo:</strong> No se requiere contraseña actual para facilitar pruebas.
                                    </div>
                                    <div class="form-group">
                                        <label for="password_nuevo">Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="password_nuevo" name="password_nuevo" required minlength="6">
                                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required minlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Cambiar Contraseña
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- CAMBIO DE EMAIL -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-envelope mr-2"></i>
                                    Cambiar Email de Acceso
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Email actual -->
                                <div class="alert alert-info" id="email-actual-container">
                                    <strong>Email actual:</strong> 
                                    <span id="email-actual-display">Cargando...</span>
                                </div>

                                <!-- Solicitudes pendientes -->
                                <div id="solicitudes-pendientes-container" style="display: none;">
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-clock mr-2"></i>Solicitud Pendiente</h6>
                                        <p id="solicitud-pendiente-info">Tienes una solicitud de cambio pendiente.</p>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-cancelar-solicitud">
                                            <i class="fas fa-times mr-1"></i>
                                            Cancelar Solicitud
                                        </button>
                                    </div>
                                </div>

                                <!-- Formulario de cambio -->
                                <form id="formCambiarEmail">
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle mr-2"></i>Importante:</h6>
                                        <ul class="mb-0">
                                            <li>Se enviará un email de verificación al nuevo correo</li>
                                            <li>El enlace expira en 2 horas</li>
                                            <li>Tu contraseña permanece igual</li>
                                            <li>Debes tener acceso al nuevo email para confirmar</li>
                                        </ul>
                                    </div>

                                    <div class="form-group">
                                        <label for="nuevo_email">Nuevo Email de Acceso</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="nuevo_email" 
                                               name="nuevo_email" 
                                               placeholder="ejemplo@nuevoanvar.test"
                                               required>
                                        <small class="form-text text-muted">
                                            Ingresa el email que quieres usar para iniciar sesión
                                        </small>
                                    </div>

                                    <button type="submit" class="btn btn-warning" id="btn-solicitar-cambio">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Solicitar Cambio de Email
                                    </button>
                                </form>

                                <!-- Información adicional -->
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Si tienes problemas, puedes usar 
                                        <a href="<?= base_url('magic-link') ?>" target="_blank">recuperación por Magic Link</a>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB DOCUMENTOS -->
                    <div class="tab-pane" id="documentos">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Subir documento -->
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Subir Documento</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($staff->rfc)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>RFC requerido:</strong> Debe registrar su RFC en la pestaña de perfil antes de subir documentos.
                                        </div>
                                        <?php endif; ?>
                                        
                                        <form id="formSubirDocumento" enctype="multipart/form-data" <?= empty($staff->rfc) ? 'style="display:none;"' : '' ?>>
                                            <div class="form-group">
                                                <label for="tipo_documento">Tipo de Documento</label>
                                                <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                                    <option value="">Seleccionar tipo</option>
                                                    <?php foreach ($tiposDocumento as $key => $value): ?>
                                                    <option value="<?= $key ?>"><?= esc($value) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="documento">Archivo</label>
                                                <input type="file" class="form-control-file" id="documento" name="documento">
                                                <small class="form-text text-muted">Tamaño máximo: <strong>10MB</strong>. Formatos: JPG, PNG, PDF, DOC, DOCX, TXT<br><small class="text-info">Se guardará en: uploads/staff/<?= esc($staff->rfc) ?>/</small><br><small class="text-success">Límite aumentado a 10MB</small></small>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Subir Documento
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Lista de documentos -->
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Mis Documentos</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Documento</th>
                                                        <th>Archivo</th>
                                                        <th>Estado</th>
                                                        <th>Fecha</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="listaDocumentos">
                                                    <?php if (empty($documentos)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No hay documentos subidos</td>
                                                    </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($documentos as $doc): ?>
                                                        <tr>
                                                            <td>
                                                                <div>
                                                                    <strong><?= esc($tiposDocumento[$doc['tipo_documento']] ?? $doc['tipo_documento']) ?></strong>
                                                                    <br><small class="text-muted"><?= esc($doc['nombre_archivo']) ?></small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <?php
                                                                    $extension = strtolower($doc['extension'] ?? '');
                                                                    $iconos = [
                                                                        'pdf' => ['fas fa-file-pdf', 'text-danger'],
                                                                        'jpg' => ['fas fa-file-image', 'text-info'],
                                                                        'jpeg' => ['fas fa-file-image', 'text-info'],
                                                                        'png' => ['fas fa-file-image', 'text-info'],
                                                                        'doc' => ['fas fa-file-word', 'text-primary'],
                                                                        'docx' => ['fas fa-file-word', 'text-primary'],
                                                                        'txt' => ['fas fa-file-alt', 'text-secondary']
                                                                    ];
                                                                    $icono = $iconos[$extension] ?? ['fas fa-file', 'text-muted'];
                                                                    ?>
                                                                    <i class="<?= $icono[0] ?> <?= $icono[1] ?> mr-2"></i>
                                                                    <small>
                                                                        <?= strtoupper($extension) ?><br>
                                                                        <span class="text-muted"><?= $doc['tamano_formateado'] ?></span>
                                                                    </small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-<?= $doc['estado'] === 'aprobado' ? 'success' : ($doc['estado'] === 'rechazado' ? 'danger' : 'warning') ?>">
                                                                    <i class="fas fa-<?= $doc['estado'] === 'aprobado' ? 'check' : ($doc['estado'] === 'rechazado' ? 'times' : 'clock') ?>"></i>
                                                                    <?= ucfirst($doc['estado']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small><?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?></small>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <!-- Botón Ver/Descargar -->
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="verDocumento(<?= $doc['id'] ?>, '<?= esc($doc['nombre_archivo']) ?>', '<?= $extension ?>')" title="Ver documento">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                    
                                                                    <!-- Botón Descargar -->
                                                                    <button class="btn btn-sm btn-outline-success" onclick="descargarDocumento(<?= $doc['id'] ?>, '<?= esc($doc['nombre_archivo']) ?>')" title="Descargar">
                                                                        <i class="fas fa-download"></i>
                                                                    </button>
                                                                    
                                                                    <!-- Botón Eliminar -->
                                                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarDocumento(<?= $doc['id'] ?>)" title="Eliminar">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Tab navigation
    $('[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('.btn-outline-primary').removeClass('active');
        $(this).addClass('active');
    });

    // Preview de imagen - En foto de perfil actual
    $('#inputFotoPerfil').on('change', function(e) {
        const file = e.target.files[0];
        const fotoPerfil = $('#fotoPerfil');
        const originalSrc = fotoPerfil.attr('src');
        
        if (file) {
            // Validar tamaño (2MB)
            if (file.size > 2048000) {
                Swal.fire('Error', 'Archivo muy grande (máximo 2MB)', 'error');
                this.value = '';
                fotoPerfil.attr('src', originalSrc);
                return;
            }
            
            // Validar tipo
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire('Error', 'Formato no válido (JPG, PNG)', 'error');
                this.value = '';
                fotoPerfil.attr('src', originalSrc);
                return;
            }
            
            // Mostrar preview en la foto de perfil actual
            const reader = new FileReader();
            reader.onload = function(e) {
                fotoPerfil.attr('src', e.target.result);
                // Agregar borde verde para indicar que es preview
                fotoPerfil.css('border', '3px solid #28a745');
            };
            reader.readAsDataURL(file);
        } else {
            // Restaurar imagen original
            fotoPerfil.attr('src', originalSrc).css('border', '');
        }
    });

    // Form información personal
    $('#formInfoPersonal').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= site_url('/admin/mi-perfil/actualizar-info') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success');
                    
                    // Si se registró RFC, mostrar formulario de documentos
                    const rfcValue = $('#rfc').val();
                    if (rfcValue && rfcValue.trim() !== '') {
                        $('.alert-warning').hide();
                        $('#formSubirDocumento').show();
                        // Actualizar la ruta en el texto de ayuda
                        $('#formSubirDocumento .text-info').text('Se guardará en: uploads/staff/' + rfcValue + '/');
                    }
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });

    // Form cambiar contraseña
    $('#formCambiarPassword').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#password_nuevo').val() !== $('#password_confirmar').val()) {
            Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
            return;
        }
        
        $.ajax({
            url: '<?= site_url('/admin/mi-perfil/cambiar-password') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success').then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });

    // Form foto de perfil - Optimizado Entity-First
    $('#formFotoPerfil').on('submit', function(e) {
        e.preventDefault();
        
        const btnSubir = $('#btnSubirFoto');
        const originalText = btnSubir.html();
        
        // Validar que hay archivo
        if (!$('#inputFotoPerfil')[0].files.length) {
            Swal.fire('Error', 'Seleccione una imagen', 'error');
            return;
        }
        
        // UI feedback
        btnSubir.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= site_url('/admin/mi-perfil/subir-foto-perfil') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Usar la URL segura devuelta por el controlador
                    const avatarUrl = response.avatar_url + '?t=' + Date.now();
                    
                    // Actualizar todas las imágenes de avatar en la página
                    $('#fotoPerfil').attr('src', avatarUrl).css('border', '');
                    $('.user-image').attr('src', avatarUrl); // Header navbar
                    $('.img-circle').attr('src', avatarUrl); // Dropdown avatar
                    
                    $('#inputFotoPerfil').val('');
                    Swal.fire('¡Éxito!', response.message, 'success');
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            },
            complete: function() {
                // Restaurar botón
                btnSubir.prop('disabled', false).html(originalText);
            }
        });
    });

    // Form subir documento - Validaciones relajadas para desarrollo
    $('#formSubirDocumento').on('submit', function(e) {
        e.preventDefault();
        
        // Validaciones básicas en frontend
        const archivo = $('#documento')[0].files[0];
        const tipoDocumento = $('#tipo_documento').val();
        
        
        if (!archivo) {
            Swal.fire('Error', 'Seleccione un archivo', 'error');
            return;
        }
        
        if (!tipoDocumento) {
            Swal.fire('Error', 'Seleccione el tipo de documento', 'error');
            return;
        }
        
        // Mostrar información del archivo
        const tamanoMB = (archivo.size / 1024 / 1024).toFixed(2);
        
        // Validación de tamaño aumentada a 10MB
        const maxSizeMB = 10; // 10MB permitidos
        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        
        if (archivo.size > maxSizeBytes) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo muy grande',
                text: `El archivo pesa ${tamanoMB} MB. El máximo permitido es ${maxSizeMB} MB.`,
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Validación de tipo de archivo - Manejo robusto de múltiples puntos
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt'];
        const fileName = archivo.name.toLowerCase();
        const fileExtension = fileName.split('.').pop();
        
        
        if (!allowedExtensions.includes(fileExtension)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no válido',
                text: `La extensión ".${fileExtension}" no está permitida.\n\nFormatos válidos: ${allowedExtensions.join(', ').toUpperCase()}`,
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= site_url('admin/mi-perfil/subir-documento') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    title: 'Subiendo...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success').then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'Error de conexión: ' + error, 'error');
            }
        });
    });
});

// Función para ver documento
function verDocumento(documentoId, nombreArchivo, extension) {
    
    // Mostrar mensaje informativo sobre funcionalidad no implementada
    Swal.fire({
        title: 'Funcionalidad en desarrollo',
        html: `La visualización de documentos no está implementada aún.<br><br>` +
              `<strong>Documento:</strong> ${nombreArchivo}<br>` +
              `<strong>Tipo:</strong> ${extension.toUpperCase()}`,
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

// Función para descargar documento
function descargarDocumento(documentoId, nombreArchivo) {
    
    // Mostrar mensaje informativo sobre funcionalidad no implementada
    Swal.fire({
        title: 'Funcionalidad en desarrollo',
        html: `La descarga de documentos no está implementada aún.<br><br>` +
              `<strong>Documento:</strong> ${nombreArchivo}`,
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

function eliminarDocumento(documentoId) {
    
    // Mostrar mensaje informativo sobre funcionalidad no implementada
    Swal.fire({
        title: 'Funcionalidad en desarrollo',
        text: 'La eliminación de documentos no está implementada aún.',
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

// =====================================================================
// GESTIÓN DE CAMBIO DE EMAIL
// =====================================================================

// Cargar email actual y solicitudes pendientes al cargar la página
function cargarInformacionEmail() {
    // Obtener email actual
    $.get('<?= site_url('admin/mi-perfil/obtener-email-actual') ?>')
        .done(function(response) {
            if (response.success) {
                $('#email-actual-display').text(response.email_actual);
            } else {
                $('#email-actual-display').text('Error al cargar');
            }
        })
        .fail(function() {
            $('#email-actual-display').text('Error al cargar');
        });

    // Verificar solicitudes pendientes
    $.get('<?= site_url('admin/mi-perfil/solicitudes-pendientes') ?>')
        .done(function(response) {
            if (response.success && response.solicitudes.length > 0) {
                const solicitud = response.solicitudes[0];
                $('#solicitud-pendiente-info').html(
                    `Solicitud para cambiar a: <strong>${solicitud.nuevo_email}</strong><br>` +
                    `<small>Expira: ${solicitud.tiempo_restante}</small>`
                );
                $('#solicitudes-pendientes-container').show();
                $('#formCambiarEmail').hide();
            } else {
                $('#solicitudes-pendientes-container').hide();
                $('#formCambiarEmail').show();
            }
        });
}

// Manejar formulario de cambio de email
$('#formCambiarEmail').on('submit', function(e) {
    e.preventDefault();
    
    const nuevoEmail = $('#nuevo_email').val().trim();
    
    if (!nuevoEmail) {
        Swal.fire('Error', 'Por favor ingresa un email válido', 'error');
        return;
    }

    // Confirmar acción
    Swal.fire({
        title: '¿Confirmar cambio de email?',
        html: `Se enviará un enlace de verificación a:<br><strong>${nuevoEmail}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar enlace',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarSolicitudCambioEmail(nuevoEmail);
        }
    });
});

// Enviar solicitud de cambio de email
function enviarSolicitudCambioEmail(nuevoEmail) {
    $.ajax({
        url: '<?= site_url('admin/mi-perfil/solicitar-cambio-email') ?>',
        type: 'POST',
        data: { nuevo_email: nuevoEmail },
        dataType: 'json',
        beforeSend: function() {
            $('#btn-solicitar-cambio').prop('disabled', true).html(
                '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...'
            );
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Enlace enviado',
                    html: `Se ha enviado un enlace de verificación a:<br><strong>${nuevoEmail}</strong><br><br>` +
                          'Revisa tu bandeja de entrada y haz clic en el enlace para completar el cambio.',
                    icon: 'success',
                    confirmButtonText: 'Entendido'
                });
                
                // Limpiar formulario y recargar información
                $('#nuevo_email').val('');
                cargarInformacionEmail();
            } else {
                Swal.fire('Error', response.message || response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de conexión. Inténtalo de nuevo.', 'error');
        },
        complete: function() {
            $('#btn-solicitar-cambio').prop('disabled', false).html(
                '<i class="fas fa-paper-plane mr-2"></i>Solicitar Cambio de Email'
            );
        }
    });
}

// Cancelar solicitud pendiente
$('#btn-cancelar-solicitud').on('click', function() {
    Swal.fire({
        title: '¿Cancelar solicitud?',
        text: 'Se cancelará la solicitud de cambio de email pendiente',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= site_url('admin/mi-perfil/cancelar-cambio-email') ?>')
                .done(function(response) {
                    if (response.success) {
                        Swal.fire('Cancelado', response.message, 'success');
                        cargarInformacionEmail();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
        }
    });
});

// Cargar información al inicializar
cargarInformacionEmail();

</script>
<?= $this->endSection() ?>