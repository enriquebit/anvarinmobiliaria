<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
  <?php if (isset($item['url']) && !empty($item['url'])): ?>
    <li class="breadcrumb-item"><a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a></li>
  <?php else: ?>
    <li class="breadcrumb-item active"><?= $item['name'] ?></li>
  <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
  <div class="col-12">
    
    <!-- 🎯 Mensaje informativo para usuarios sin información de staff -->
    <?php if ($es_nuevo_staff): ?>
      <div class="alert alert-info">
        <h6><i class="fas fa-info-circle mr-2"></i>Completar Información</h6>
        <p class="mb-0">
          Este usuario existe en el sistema pero <strong>no tiene información adicional</strong>. 
          Completa los campos para agregar detalles como nombre, teléfono y agencia.
        </p>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-edit mr-2"></i>
          <?= $es_nuevo_staff ? 'Completar Información de Usuario' : 'Editar Usuario Administrativo' ?>
        </h3>
        <div class="card-tools">
          <a href="<?= site_url('/admin/usuarios') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
          </a>
        </div>
      </div>
      
      <!-- 🎯 MVP: Formulario idéntico a create pero con datos precargados -->
      <form action="<?= site_url("/admin/usuarios/update/{$user->id}") ?>" method="POST" id="form-editar-usuario">
        <?= csrf_field() ?>
        
        <div class="card-body">
          
          <!-- Mostrar errores de validación -->
          <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
              <h6><i class="fas fa-exclamation-triangle"></i> Errores de Validación:</h6>
              <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                  <li><?= esc($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          
          <!-- 🎯 INFORMACIÓN BÁSICA -->
          <div class="row">
            <div class="col-12">
              <h5 class="text-primary mb-3">
                <i class="fas fa-user mr-2"></i>
                Información Básica
              </h5>
            </div>
          </div>
          
          <div class="row">
            <!-- Nombres -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="nombres">
                  Nombres <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control <?= (session()->has('errors') && isset(session('errors')['nombres'])) ? 'is-invalid' : '' ?>" 
                       id="nombres" 
                       name="nombres" 
                       value="<?= esc(old('nombres', $staffInfo ? $staffInfo->nombres : '')) ?>" 
                       placeholder="Juan Carlos"
                       required>
                <small class="form-text text-muted">
                  Nombre(s) de pila
                </small>
              </div>
            </div>
            
            <!-- Apellido Paterno -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="apellido_paterno">
                  Apellido Paterno <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control <?= (session()->has('errors') && isset(session('errors')['apellido_paterno'])) ? 'is-invalid' : '' ?>" 
                       id="apellido_paterno" 
                       name="apellido_paterno" 
                       value="<?= esc(old('apellido_paterno', $staffInfo ? $staffInfo->apellido_paterno : '')) ?>" 
                       placeholder="García"
                       required>
              </div>
            </div>
            
            <!-- Apellido Materno -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="apellido_materno">
                  Apellido Materno
                </label>
                <input type="text" 
                       class="form-control <?= (session()->has('errors') && isset(session('errors')['apellido_materno'])) ? 'is-invalid' : '' ?>" 
                       id="apellido_materno" 
                       name="apellido_materno" 
                       value="<?= esc(old('apellido_materno', $staffInfo ? $staffInfo->apellido_materno : '')) ?>" 
                       placeholder="López">
              </div>
            </div>
          </div>
          
          <div class="row">
            <!-- Fecha de Nacimiento -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="fecha_nacimiento">
                  Fecha de Nacimiento
                </label>
                <input type="date" 
                       class="form-control <?= (session()->has('errors') && isset(session('errors')['fecha_nacimiento'])) ? 'is-invalid' : '' ?>" 
                       id="fecha_nacimiento" 
                       name="fecha_nacimiento" 
                       value="<?= esc(old('fecha_nacimiento', $staffInfo && $staffInfo->fecha_nacimiento ? $staffInfo->fecha_nacimiento->format('Y-m-d') : '')) ?>">
                <small class="form-text text-muted">
                  Opcional - para cálculo de edad
                </small>
              </div>
            </div>
            
            <!-- Teléfono -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" 
                       class="form-control <?= (session()->has('errors') && isset(session('errors')['telefono'])) ? 'is-invalid' : '' ?>" 
                       id="telefono" 
                       name="telefono" 
                       value="<?= esc(old('telefono', $staffInfo ? $staffInfo->telefono : '')) ?>" 
                       placeholder="5551234567"
                       maxlength="15">
                <small class="form-text text-muted">
                  Solo números, mínimo 10 dígitos
                </small>
              </div>
            </div>
          </div>
          
          <!-- 🎯 CREDENCIALES DE ACCESO -->
          <div class="row">
            <div class="col-12">
              <h5 class="text-primary mb-3 mt-4">
                <i class="fas fa-key mr-2"></i>
                Credenciales de Acceso
              </h5>
            </div>
          </div>
          
          <div class="row">
            <!-- Email -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="email">
                  Email <span class="text-danger">*</span>
                </label>
                <input type="email" 
                       class="form-control <?= (session()->has('errors') && isset(session('errors')['email'])) ? 'is-invalid' : '' ?>" 
                       id="email" 
                       name="email" 
                       value="<?= esc(old('email', $email ?? '')) ?>" 
                       placeholder="usuario@empresa.com"
                       required>
                <small class="form-text text-muted">
                  Usuario para iniciar sesión
                </small>
              </div>
            </div>
            
            <!-- Contraseña (Opcional en edición) -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="password">
                  Nueva Contraseña <span class="text-muted">(Opcional)</span>
                </label>
                <div class="input-group">
                  <input type="password" 
                         class="form-control <?= (session()->has('errors') && isset(session('errors')['password'])) ? 'is-invalid' : '' ?>" 
                         id="password" 
                         name="password" 
                         placeholder="Dejar vacío para mantener actual">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                      <i class="fas fa-eye" id="toggle-password-icon"></i>
                    </button>
                  </div>
                </div>
                <small class="form-text text-muted">
                  Solo llenar si deseas cambiar la contraseña
                </small>
              </div>
            </div>
            
            <!-- Confirmar Contraseña -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="password_confirm">
                  Confirmar Nueva Contraseña
                </label>
                <div class="input-group">
                  <input type="password" 
                         class="form-control <?= (session()->has('errors') && isset(session('errors')['password_confirm'])) ? 'is-invalid' : '' ?>" 
                         id="password_confirm" 
                         name="password_confirm" 
                         placeholder="Repetir nueva contraseña">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirm')">
                      <i class="fas fa-eye" id="toggle-password-confirm-icon"></i>
                    </button>
                  </div>
                </div>
                <small class="form-text text-muted">
                  <span id="password-match-message"></span>
                </small>
              </div>
            </div>
          </div>
          
          <!-- 🎯 INFORMACIÓN LABORAL -->
          <div class="row">
            <div class="col-12">
              <h5 class="text-primary mb-3 mt-4">
                <i class="fas fa-briefcase mr-2"></i>
                Información Laboral
              </h5>
            </div>
          </div>
          
          <div class="row">
            <!-- Grupo/Rol -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="grupo">
                  Grupo/Rol <span class="text-danger">*</span>
                </label>
                <select class="form-control <?= (session()->has('errors') && isset(session('errors')['grupo'])) ? 'is-invalid' : '' ?>" 
                        id="grupo" 
                        name="grupo" 
                        required>
                  <option value="">Seleccionar grupo...</option>
                  <?php foreach ($grupos_disponibles as $key => $nombre): ?>
                    <option value="<?= $key ?>" <?= (old('grupo', $grupo) == $key) ? 'selected' : '' ?>>
                      <?= $nombre ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                  Define los permisos del usuario en el sistema
                </small>
              </div>
            </div>
            
            <!-- Agencia/Sucursal -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="agencia">Agencia/Sucursal</label>
                <select class="form-control" id="agencia" name="agencia">
                  <option value="">Seleccionar agencia...</option>
                  <?php foreach ($agencias_disponibles as $key => $nombre): ?>
                    <option value="<?= $key ?>" <?= (old('agencia', $staffInfo ? $staffInfo->agencia : '') == $key) ? 'selected' : '' ?>>
                      <?= $nombre ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                  Ubicación o sucursal asignada (opcional)
                </small>
              </div>
            </div>
          </div>
          
          <!-- Notas -->
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label for="notas">Notas Internas</label>
                <textarea class="form-control" 
                          id="notas" 
                          name="notas" 
                          rows="3" 
                          placeholder="Información adicional, observaciones, etc."><?= esc(old('notas', $staffInfo ? $staffInfo->notas : '')) ?></textarea>
                <small class="form-text text-muted">
                  Información visible solo para administradores
                </small>
              </div>
            </div>
          </div>
          
          <!-- 🎯 INFORMACIÓN ADICIONAL -->
          <div class="row">
            <div class="col-12">
              <div class="card card-outline card-info">
                <div class="card-header">
                  <h5 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Información del Usuario
                  </h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <h6><i class="fas fa-user-shield text-primary"></i> Estado Actual:</h6>
                      <ul class="list-unstyled">
                        <li><strong>ID Usuario:</strong> <?= $user->id ?></li>
                        <li><strong>Estado:</strong> 
                          <?php if ($user->active): ?>
                            <span class="badge badge-success">Activo</span>
                          <?php else: ?>
                            <span class="badge badge-secondary">Inactivo</span>
                          <?php endif; ?>
                        </li>
                        <li><strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($user->created_at)) ?></li>
                        <?php if ($staffInfo): ?>
                          <li><strong>Info Staff:</strong> <span class="badge badge-success">Completa</span></li>
                        <?php else: ?>
                          <li><strong>Info Staff:</strong> <span class="badge badge-warning">Incompleta</span></li>
                        <?php endif; ?>
                      </ul>
                    </div>
                    <div class="col-md-6">
                      <h6><i class="fas fa-lightbulb text-warning"></i> Recomendaciones:</h6>
                      <ul class="list-unstyled">
                        <li>• Solo cambiar contraseña si es necesario</li>
                        <li>• Verificar que el email sea correcto</li>
                        <li>• Asignar el rol adecuado según funciones</li>
                        <li>• Documentar cambios importantes en notas</li>
                        <li>• El estado activo/inactivo lo maneja Shield</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        
        <div class="card-footer">
          <div class="row">
            <div class="col-md-6">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> <?= $es_nuevo_staff ? 'Completar Información' : 'Actualizar Usuario' ?>
              </button>
              <button type="reset" class="btn btn-secondary ml-2">
                <i class="fas fa-undo"></i> Restaurar Valores
              </button>
            </div>
            <div class="col-md-6 text-right">
              <a href="<?= site_url('/admin/usuarios') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Cancelar
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// 🎯 MVP: JavaScript idéntico a create pero adaptado para edición
function togglePassword(fieldId) {
  const passwordInput = document.getElementById(fieldId);
  const icon = document.getElementById('toggle-' + fieldId + '-icon');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    passwordInput.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}

// 🎯 Validación de confirmación de contraseña (solo si se llena)
function validarConfirmacionPassword() {
  const password = $('#password').val();
  const passwordConfirm = $('#password_confirm').val();
  const messageDiv = $('#password-match-message');
  
  // Si no se llena ninguna, está bien
  if (password.length === 0 && passwordConfirm.length === 0) {
    messageDiv.html('<i class="fas fa-info text-info"></i> No se cambiará la contraseña');
    $('#password_confirm').removeClass('is-valid is-invalid');
    return true;
  }
  
  // Si se llena una pero no la otra
  if (password.length > 0 && passwordConfirm.length === 0) {
    messageDiv.html('<i class="fas fa-exclamation text-warning"></i> Confirma la nueva contraseña');
    $('#password_confirm').removeClass('is-valid is-invalid');
    return false;
  }
  
  // Si se llenan ambas, validar que coincidan
  if (password === passwordConfirm) {
    messageDiv.html('<i class="fas fa-check text-success"></i> Las contraseñas coinciden');
    $('#password_confirm').removeClass('is-invalid').addClass('is-valid');
    return true;
  } else {
    messageDiv.html('<i class="fas fa-times text-danger"></i> Las contraseñas no coinciden');
    $('#password_confirm').removeClass('is-valid').addClass('is-invalid');
    return false;
  }
}

// 🎯 Limpiar teléfono (el mutator de Entity se encarga del resto)
function limpiarTelefono() {
  const telefono = $('#telefono');
  let valor = telefono.val().replace(/[^0-9]/g, '');
  
  // Limitar a 15 dígitos
  if (valor.length > 15) {
    valor = valor.substring(0, 15);
  }
  
  telefono.val(valor);
}

// Validación del formulario
$(document).ready(function() {
  // Validar confirmación de contraseña en tiempo real
  $('#password, #password_confirm').on('keyup blur', validarConfirmacionPassword);
  
  // 🎯 Entity-First: El mutator se encarga de la limpieza, solo limitamos entrada
  $('#telefono').on('input', limpiarTelefono);
  
  // 🎯 Entity-First: El mutator se encarga de convertir a mayúsculas
  $('#nombres').on('blur', function() {
    const valor = $(this).val().trim();
    if (valor) {
      $(this).val(valor.toUpperCase());
    }
  });
  
  // Validación antes del envío
  $('#form-editar-usuario').on('submit', function(e) {
    const nombres = $('#nombres').val().trim();
    const email = $('#email').val().trim();
    const password = $('#password').val();
    const passwordConfirm = $('#password_confirm').val();
    const grupo = $('#grupo').val();
    
    // Validaciones básicas
    if (!nombres || !email || !grupo) {
      e.preventDefault();
      toastr.error('Por favor completa todos los campos obligatorios');
      return false;
    }
    
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      e.preventDefault();
      toastr.error('El email no es válido');
      return false;
    }
    
    // Validar contraseña solo si se proporcionó
    if (password.length > 0) {
      if (password.length < 8) {
        e.preventDefault();
        toastr.error('La nueva contraseña debe tener al menos 8 caracteres');
        return false;
      }
      
      if (password !== passwordConfirm) {
        e.preventDefault();
        toastr.error('Las contraseñas no coinciden');
        return false;
      }
    }
    
    // Confirmar actualización
    e.preventDefault();
    const esNuevoStaff = <?= $es_nuevo_staff ? 'true' : 'false' ?>;
    const accion = esNuevoStaff ? 'completar información' : 'actualizar';
    
    Swal.fire({
      title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} del usuario?`,
      html: `
        <div class="text-left">
          <strong>Nombre:</strong> ${nombres}<br>
          <strong>Email:</strong> ${email}<br>
          <strong>Grupo:</strong> ${$('#grupo option:selected').text()}<br>
          ${$('#agencia').val() ? '<strong>Agencia:</strong> ' + $('#agencia option:selected').text() + '<br>' : ''}
          ${password.length > 0 ? '<strong>Contraseña:</strong> Se cambiará<br>' : '<strong>Contraseña:</strong> Se mantendrá actual<br>'}
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: `Sí, ${accion}`,
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        // Mostrar loading
        Swal.fire({
          title: `${accion.charAt(0).toUpperCase() + accion.slice(1)}ando usuario...`,
          html: 'Por favor espera mientras se procesa la información',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Enviar formulario
        this.submit();
      }
    });
  });
  
  // Auto-focus en el primer campo
  $('#nombres').focus();
  
  // Mostrar mensaje inicial según el contexto
  const esNuevoStaff = <?= $es_nuevo_staff ? 'true' : 'false' ?>;
  if (esNuevoStaff) {
    toastr.info('Completa la información adicional para este usuario');
  }
});
</script>
<?= $this->endSection() ?>