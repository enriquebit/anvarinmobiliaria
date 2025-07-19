<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header text-center">
        <h4>Crear Cuenta - Anvar Inmobiliaria</h4>
    </div>
    <div class="card-body">
        
        <!-- Mostrar errores si existen -->
        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <!-- Mostrar error general -->
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger">
                <?= esc(session('error')) ?>
            </div>
        <?php endif ?>

        <!-- ✅ FORMULARIO CORREGIDO -->
        <?= form_open('register', ['id' => 'registerForm', 'class' => 'needs-validation', 'novalidate' => true]) ?>
            
            <!-- ✅ TOKEN CSRF EXPLÍCITO -->
            <?= csrf_field() ?>
            
            <!-- Nombres -->
            <div class="mb-3">
                <label for="nombres" class="form-label">Nombres *</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="nombres" 
                    name="nombres" 
                    value="<?= old('nombres') ?>"
                    required
                    placeholder="Tus nombres"
                    maxlength="100"
                >
                <div class="invalid-feedback">Los nombres son obligatorios</div>
            </div>

            <!-- Apellido Paterno -->
            <div class="mb-3">
                <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="apellido_paterno" 
                    name="apellido_paterno" 
                    value="<?= old('apellido_paterno') ?>"
                    required
                    placeholder="Tu apellido paterno"
                    maxlength="50"
                >
                <div class="invalid-feedback">El apellido paterno es obligatorio</div>
            </div>

            <!-- Apellido Materno -->
            <div class="mb-3">
                <label for="apellido_materno" class="form-label">Apellido Materno *</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="apellido_materno" 
                    name="apellido_materno" 
                    value="<?= old('apellido_materno') ?>"
                    required
                    placeholder="Tu apellido materno"
                    maxlength="50"
                >
                <div class="invalid-feedback">El apellido materno es obligatorio</div>
            </div>

            <!-- ✅ EMAIL CORREGIDO -->
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    name="email" 
                    value="<?= old('email') ?>"
                    required
                    placeholder="tu@email.com"
                    maxlength="254"
                >
                <div class="invalid-feedback">Ingresa un email válido</div>
                <div id="email-status" class="small"></div>
            </div>

            <!-- Teléfono -->
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono *</label>
                <input 
                    type="tel" 
                    class="form-control" 
                    id="telefono" 
                    name="telefono" 
                    value="<?= old('telefono') ?>"
                    required
                    placeholder="5551234567"
                    pattern="[0-9]{10,15}"
                    maxlength="15"
                >
                <div class="invalid-feedback">Ingresa un teléfono válido (solo números)</div>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña *</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    required
                    minlength="8"
                    maxlength="255"
                    placeholder="Mínimo 8 caracteres"
                >
                <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres</div>
            </div>

            <!-- Confirmar Contraseña -->
            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirmar Contraseña *</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password_confirm" 
                    name="password_confirm" 
                    required
                    minlength="8"
                    maxlength="255"
                    placeholder="Repite tu contraseña"
                >
                <div class="invalid-feedback">Las contraseñas deben coincidir</div>
            </div>

            <!-- Términos y Condiciones -->
            <div class="mb-3 form-check">
                <input 
                    type="checkbox" 
                    class="form-check-input" 
                    id="terms" 
                    name="terms" 
                    value="1"
                    required
                    <?= old('terms') ? 'checked' : '' ?>
                >
                <label class="form-check-label" for="terms">
                    Acepto los <a href="#" target="_blank">términos y condiciones</a> *
                </label>
                <div class="invalid-feedback">Debes aceptar los términos y condiciones</div>
            </div>

            <!-- Botón de envío -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="btn-text">Crear Cuenta</span>
                    <span id="btn-loading" class="d-none">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Procesando...
                    </span>
                </button>
            </div>
            
        <?= form_close() ?>

        <!-- Link al login -->
        <div class="text-center mt-3">
            <p>¿Ya tienes cuenta? <a href="<?= site_url('/login') ?>">Inicia sesión aquí</a></p>
        </div>
        
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');
    
    // ✅ VERIFICAR EMAIL DISPONIBLE - RUTA CORREGIDA
    const emailInput = document.getElementById('email');
    const emailStatus = document.getElementById('email-status');
    
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && email.includes('@')) {
                
                // Obtener token CSRF
                const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]').value;
                
                fetch('<?= site_url("/register/check-email") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `email=${encodeURIComponent(email)}&<?= csrf_token() ?>=${encodeURIComponent(csrfToken)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        emailInput.classList.remove('is-invalid');
                        emailInput.classList.add('is-valid');
                        emailStatus.innerHTML = '<span class="text-success">✅ Email disponible</span>';
                        emailInput.setCustomValidity('');
                    } else {
                        emailInput.classList.remove('is-valid');
                        emailInput.classList.add('is-invalid');
                        emailStatus.innerHTML = '<span class="text-danger">❌ ' + data.message + '</span>';
                        emailInput.setCustomValidity(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error verificando email:', error);
                    emailStatus.innerHTML = '<span class="text-warning">⚠️ Error al verificar email</span>';
                });
            }
        });
    }

    // ✅ CONFIRMAR CONTRASEÑAS
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirm');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            confirmPassword.classList.add('is-invalid');
        } else {
            confirmPassword.setCustomValidity('');
            confirmPassword.classList.remove('is-invalid');
            if (confirmPassword.value.length >= 8) {
                confirmPassword.classList.add('is-valid');
            }
        }
    }
    
    if (password && confirmPassword) {
        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePassword);
    }

    // ✅ MANEJAR ENVÍO DEL FORMULARIO
    if (form) {
        form.addEventListener('submit', function(e) {
            
            // Mostrar estado de carga
            if (submitBtn && btnText && btnLoading) {
                submitBtn.disabled = true;
                btnText.classList.add('d-none');
                btnLoading.classList.remove('d-none');
            }
            
            // Validar formulario
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                
                // Restaurar botón
                if (submitBtn && btnText && btnLoading) {
                    submitBtn.disabled = false;
                    btnText.classList.remove('d-none');
                    btnLoading.classList.add('d-none');
                }
            } else {
            }
            
            form.classList.add('was-validated');
        });
    }

    // ✅ VALIDACIÓN EN TIEMPO REAL
    const inputs = form.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });

    // ✅ LIMPIAR SOLO NÚMEROS EN TELÉFONO
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            // Permitir solo números
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>

<?= $this->endSection() ?>