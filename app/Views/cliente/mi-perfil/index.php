<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Mi Perfil<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Mi Perfil</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Dashboard</a></li>
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
                        <?php if ($debeCambiarPassword): ?>
                        <span class="btn btn-sm btn-outline-secondary disabled" title="Debe cambiar su contraseña primero">
                            <i class="fas fa-user"></i> Perfil
                        </span>
                        <?php else: ?>
                        <a href="#perfil" class="btn btn-sm btn-outline-primary active" data-toggle="tab">
                            <i class="fas fa-user"></i> Perfil
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-4 text-center">
                        <a href="#config" class="btn btn-sm btn-outline-primary <?= $debeCambiarPassword ? 'active' : '' ?>" data-toggle="tab">
                            <i class="fas fa-cog"></i> Config
                        </a>
                    </div>
                    <div class="col-4 text-center">
                        <?php if ($debeCambiarPassword): ?>
                        <span class="btn btn-sm btn-outline-secondary disabled" title="Debe cambiar su contraseña primero">
                            <i class="fas fa-file-alt"></i> Documentos
                        </span>
                        <?php else: ?>
                        <a href="#documentos" class="btn btn-sm btn-outline-primary" data-toggle="tab">
                            <i class="fas fa-file-alt"></i> Documentos
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- TAB PERFIL -->
                    <div class="<?= $debeCambiarPassword ? '' : 'active' ?> tab-pane" id="perfil">
                        <div class="row">
                            <div class="col-md-3">
                                <!-- Foto de perfil -->
                                <div class="card card-primary card-outline">
                                    <div class="card-body box-profile">
                                        <div class="text-center">
                                            <img class="profile-user-img img-fluid img-circle" 
                                                 id="fotoPerfil"
                                                 src="<?= !empty($cliente->foto_perfil) ? site_url($cliente->foto_perfil) : site_url('assets/img/default-avatar.png') ?>" 
                                                 alt="Foto de perfil">
                                        </div>
                                        <h3 class="profile-username text-center"><?= esc($cliente->nombres ?: 'Sin nombre') ?></h3>
                                        <p class="text-muted text-center"><?= userRole() ?></p>
                                        
                                        <!-- Upload foto de perfil -->
                                        <form id="formFotoPerfil" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="inputFotoPerfil">Cambiar foto de perfil</label>
                                                <input type="file" class="form-control-file" id="inputFotoPerfil" name="foto_perfil" accept="image/jpeg,image/jpg,image/png">
                                                <small class="form-text text-muted">Tamaño máximo: 2MB. Formato: JPG, PNG</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block">
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
                                                        <input type="text" class="form-control" id="nombres" name="nombres" value="<?= esc($cliente->nombres) ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="apellido_paterno">Apellido Paterno</label>
                                                        <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?= esc($cliente->apellido_paterno) ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="apellido_materno">Apellido Materno</label>
                                                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?= esc($cliente->apellido_materno) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="email">Email</label>
                                                        <input type="email" class="form-control" id="email" name="email" value="<?= esc($cliente->email) ?>" readonly>
                                                        <small class="form-text text-muted">El email no se puede cambiar</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="telefono">Teléfono</label>
                                                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= esc($cliente->telefono) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="rfc">RFC</label>
                                                        <input type="text" class="form-control" id="rfc" name="rfc" value="<?= esc($cliente->rfc) ?>" maxlength="13">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="curp">CURP</label>
                                                        <input type="text" class="form-control" id="curp" name="curp" value="<?= esc($cliente->curp) ?>" maxlength="18">
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
                    <div class="<?= $debeCambiarPassword ? 'active' : '' ?> tab-pane" id="config">
                        <?php if ($debeCambiarPassword): ?>
                        <div class="alert alert-info">
                            <h4><i class="icon fas fa-hand-paper"></i> ¡Bienvenido!</h4>
                            Para garantizar la seguridad de su cuenta, es necesario que establezca una nueva contraseña. 
                            Esta será su contraseña personal para futuras sesiones.
                        </div>
                        <?php endif; ?>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><?= $debeCambiarPassword ? 'Establecer Nueva Contraseña' : 'Cambiar Contraseña' ?></h3>
                            </div>
                            <div class="card-body">
                                <form id="formCambiarPassword">
                                    <?php if (!$debeCambiarPassword): ?>
                                    <div class="form-group">
                                        <label for="password_actual">Contraseña Actual</label>
                                        <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                    </div>
                                    <?php endif; ?>
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
                                        <form id="formSubirDocumento" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="tipo_documento">Tipo de Documento</label>
                                                <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                                    <option value="">Seleccionar tipo</option>
                                                    <option value="ine_frente">INE Frente</option>
                                                    <option value="ine_reverso">INE Reverso</option>
                                                    <option value="pasaporte">Pasaporte (alternativa al INE)</option>
                                                    <?php foreach ($tiposDocumento as $key => $value): ?>
                                                        <?php if (!in_array($key, ['ine_frente', 'ine_reverso', 'pasaporte'])): ?>
                                                        <option value="<?= $key ?>"><?= esc($value) ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="documento">Archivo</label>
                                                <input type="file" class="form-control-file" id="documento" name="documento" accept=".jpg,.jpeg,.png,.pdf" required>
                                                <small class="form-text text-muted">Tamaño máximo: 10MB. Formatos: JPG, PNG, PDF</small>
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
                                                        <th>Estado</th>
                                                        <th>Fecha</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="listaDocumentos">
                                                    <?php if (empty($documentos)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">No hay documentos subidos</td>
                                                    </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($documentos as $doc): ?>
                                                        <tr>
                                                            <td>
                                                                <small><?= esc($tiposDocumento[$doc['tipo_documento']] ?? $doc['tipo_documento']) ?></small>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-<?= $doc['estado'] === 'aprobado' ? 'success' : ($doc['estado'] === 'rechazado' ? 'danger' : 'warning') ?>">
                                                                    <?= ucfirst($doc['estado']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small><?= date('d/m/Y', strtotime($doc['created_at'])) ?></small>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarDocumento(<?= $doc['id'] ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
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
    // Si debe cambiar contraseña, activar automáticamente la pestaña de configuración
    <?php if ($debeCambiarPassword): ?>
    // Activar la pestaña de configuración
    $('[href="#config"]').tab('show');
    $('.btn-outline-primary').removeClass('active');
    $('[href="#config"]').addClass('active');
    
    // Enfocar en el campo de nueva contraseña
    setTimeout(function() {
        $('#password_nuevo').focus();
    }, 500);
    
    // Mostrar alerta con SweetAlert2
    Swal.fire({
        icon: 'info',
        title: '¡Bienvenido al Sistema ANVAR!',
        html: 'Para garantizar la seguridad de su cuenta, es necesario que establezca una nueva contraseña personal.<br><br><strong>Esta será su contraseña para futuras sesiones.</strong>',
        confirmButtonText: 'Establecer Contraseña',
        allowOutsideClick: false,
        allowEscapeKey: false
    });
    <?php endif; ?>
    
    // Tab navigation
    $('[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('.btn-outline-primary').removeClass('active');
        $(this).addClass('active');
    });
    
    // Bloquear navegación si debe cambiar contraseña
    <?php if ($debeCambiarPassword): ?>
    $('[data-toggle="tab"]').on('show.bs.tab', function (e) {
        // Solo permitir la pestaña de config
        if ($(e.target).attr('href') !== '#config') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Acción bloqueada',
                text: 'Debe establecer su nueva contraseña antes de acceder a otras secciones.',
                confirmButtonText: 'Entendido'
            });
        }
    });
    <?php endif; ?>

    // Form información personal
    $('#formInfoPersonal').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= site_url('/cliente/mi-perfil/actualizar-info') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success');
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
            url: '<?= site_url('/cliente/mi-perfil/cambiar-password') ?>',
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

    // Form foto de perfil
    $('#formFotoPerfil').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= site_url('/cliente/mi-perfil/subir-foto-perfil') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#fotoPerfil').attr('src', '<?= site_url() ?>' + response.ruta + '?t=' + Date.now());
                    $('#inputFotoPerfil').val('');
                    Swal.fire('¡Éxito!', response.message, 'success');
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });

    // Form subir documento
    $('#formSubirDocumento').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= site_url('/cliente/mi-perfil/subir-documento') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
});

function eliminarDocumento(documentoId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "El documento será eliminado permanentemente",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url('/cliente/mi-perfil/eliminar-documento/') ?>' + documentoId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Eliminado!', response.message, 'success').then(function() {
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
        }
    });
}
</script>
<?= $this->endSection() ?>