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
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-plus mr-2"></i>
          Formulario de Nuevo Usuario Administrativo
        </h3>
        <div class="card-tools">
          <a href="<?= site_url('/admin/usuarios') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
          </a>
        </div>
      </div>
      
      <!-- 🎯 MVP: Formulario completo usando helper de CodeIgniter -->
      <form action="<?= site_url('/admin/usuarios/store') ?>" method="POST" id="form-crear-usuario">
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
                       value="<?= esc(old('nombres')) ?>" 
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
                       value="<?= esc(old('apellido_paterno')) ?>" 
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
                       value="<?= esc(old('apellido_materno')) ?>" 
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
                       value="<?= esc(old('fecha_nacimiento')) ?>">
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
                       value="<?= esc(old('telefono')) ?>" 
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
                       value="<?= esc(old('email')) ?>" 
                       placeholder="usuario@empresa.com"
                       required>
                <small class="form-text text-muted">
                  Este será el usuario para iniciar sesión
                </small>
              </div>
            </div>
            
            <!-- Contraseña -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="password">
                  Contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input type="password" 
                         class="form-control <?= (session()->has('errors') && isset(session('errors')['password'])) ? 'is-invalid' : '' ?>" 
                         id="password" 
                         name="password" 
                         placeholder="Mínimo 8 caracteres"
                         required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                      <i class="fas fa-eye" id="toggle-password-icon"></i>
                    </button>
                  </div>
                </div>
                <small class="form-text text-muted">
                  Mínimo 8 caracteres, combina letras y números
                </small>
              </div>
            </div>
            
            <!-- Confirmar Contraseña -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="password_confirm">
                  Confirmar Contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input type="password" 
                         class="form-control <?= (session()->has('errors') && isset(session('errors')['password_confirm'])) ? 'is-invalid' : '' ?>" 
                         id="password_confirm" 
                         name="password_confirm" 
                         placeholder="Repetir contraseña"
                         required>
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
                    <option value="<?= $key ?>" <?= (old('grupo') == $key) ? 'selected' : '' ?>>
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
                    <option value="<?= $key ?>" <?= (old('agencia') == $key) ? 'selected' : '' ?>>
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
                          placeholder="Información adicional, observaciones, etc."><?= esc(old('notas')) ?></textarea>
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
                    Información Importante
                  </h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <h6><i class="fas fa-shield-alt text-primary"></i> Permisos por Grupo:</h6>
                      <ul class="list-unstyled">
                        <li><strong>Super Admin:</strong> Acceso total al sistema</li>
                        <li><strong>Admin:</strong> Gestión completa excepto configuración</li>
                        <li><strong>Super Vendedor:</strong> Ventas + supervisión</li>
                        <li><strong>Vendedor:</strong> Ventas y clientes</li>
                        <li><strong>Sub-Vendedor:</strong> Ventas limitadas</li>
                        <li><strong>Visor:</strong> Solo lectura</li>
                      </ul>
                    </div>
                    <div class="col-md-6">
                      <h6><i class="fas fa-lightbulb text-warning"></i> Recomendaciones:</h6>
                      <ul class="list-unstyled">
                        <li>• Usa emails corporativos</li>
                        <li>• Contraseñas seguras (8+ caracteres)</li>
                        <li>• Asigna el rol mínimo necesario</li>
                        <li>• Documenta cambios en notas</li>
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
                <i class="fas fa-save"></i> Crear Usuario
              </button>
              <button type="reset" class="btn btn-secondary ml-2">
                <i class="fas fa-undo"></i> Limpiar Formulario
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
// 🎯 MVP: JavaScript para mejorar UX + Validaciones (simplificado con Entity Mutators)
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

// 🎯 Validación de confirmación de contraseña en tiempo real
function validarConfirmacionPassword() {
  const password = $('#password').val();
  const passwordConfirm = $('#password_confirm').val();
  const messageDiv = $('#password-match-message');
  
  if (passwordConfirm.length === 0) {
    messageDiv.html('');
    $('#password_confirm').removeClass('is-valid is-invalid');
    return true;
  }
  
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

// 🎯 Limpiar teléfono (simple, el mutator de Entity se encarga del resto)
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
  // Solo agregamos indicación visual al usuario
  $('#nombres').on('blur', function() {
    const valor = $(this).val().trim();
    if (valor) {
      $(this).val(valor.toUpperCase());
    }
  });
  
  // Validación antes del envío
  $('#form-crear-usuario').on('submit', function(e) {
    const nombres = $('#nombres').val().trim();
    const email = $('#email').val().trim();
    const password = $('#password').val();
    const passwordConfirm = $('#password_confirm').val();
    const grupo = $('#grupo').val();
    
    // Validaciones básicas
    if (!nombres || !email || !password || !passwordConfirm || !grupo) {
      e.preventDefault();
      toastr.error('Por favor completa todos los campos obligatorios');
      return false;
    }
    
    if (password.length < 8) {
      e.preventDefault();
      toastr.error('La contraseña debe tener al menos 8 caracteres');
      return false;
    }
    
    if (password !== passwordConfirm) {
      e.preventDefault();
      toastr.error('Las contraseñas no coinciden');
      return false;
    }
    
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      e.preventDefault();
      toastr.error('El email no es válido');
      return false;
    }
    
    // 🎯 Entity-First: No validamos teléfono aquí, el mutator se encarga
    
    // Confirmar creación
    e.preventDefault();
    Swal.fire({
      title: '¿Crear nuevo usuario?',
      html: `
        <div class="text-left">
          <strong>Nombre:</strong> ${nombres}<br>
          <strong>Email:</strong> ${email}<br>
          <strong>Grupo:</strong> ${$('#grupo option:selected').text()}<br>
          ${$('#agencia').val() ? '<strong>Agencia:</strong> ' + $('#agencia option:selected').text() + '<br>' : ''}
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, crear usuario',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        // Mostrar loading
        Swal.fire({
          title: 'Creando usuario...',
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
});
</script>
<?= $this->endSection() ?>