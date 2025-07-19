<?= $this->extend('layouts/auth') ?>

<?= $this->section('page_title') ?>Iniciar Sesión<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <div class="login-logo">
                <img src="<?= base_url('assets/img/logo_admin.png') ?>" 
                     alt="Anvar Inmobiliaria" 
                     class="img-fluid">
            </div>
            <h4 class="mb-0">
                <strong>ANVAR INMOBILIARIA</strong>
            </h4>
            <p class="text-muted">Acceso a la Plataforma</p>
        </div>
        
        <div class="card-body">
            
            <!-- ===== NOTIFICACIONES ===== -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('message')): ?>
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-info-circle"></i>
                    <?= session()->getFlashdata('message') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-check-circle"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <!-- Errores de validación de Shield -->
            <?php if (session('errors') !== null): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Errores de validación:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <!-- FORMULARIO DE LOGIN -->
            <?= form_open(base_url('login'), ['id' => 'loginForm']) ?>
                
                <!-- Campo Email -->
                <div class="input-group mb-3">
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="Correo electrónico"
                           value="<?= old('email') ?>"
                           required 
                           autocomplete="email"
                           autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <!-- Campo Password -->
                <div class="input-group mb-3">
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Contraseña"
                           required 
                           autocomplete="current-password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <!-- Recordarme -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <label for="remember">
                                Recordarme en este dispositivo
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botón de Login -->
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Iniciar Sesión
                        </button>
                    </div>
                </div>

            <?= form_close() ?>

            <!-- Links adicionales -->
            <div class="text-center mt-4">
                <p class="mb-1">
                    <a href="<?= url_to('magic-link') ?>">
                        <i class="fas fa-magic mr-1"></i>
                        ¿Olvidaste tu contraseña?
                    </a>
                </p>
                <p class="mb-0">
                    <a href="<?= base_url('register') ?>">
                        <i class="fas fa-user-plus mr-1"></i>
                        Crear nueva cuenta
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Auto-hide alerts después de 5 segundos
    setTimeout(function() {
        $('.alert').slideUp('slow');
    }, 5000);
    
    // Validación básica del formulario
    $('#loginForm').on('submit', function(e) {
        var email = $('input[name="email"]').val();
        var password = $('input[name="password"]').val();
        
        if (!email || !password) {
            e.preventDefault();
            alert('Por favor, completa todos los campos.');
            return false;
        }
        
        // Mostrar loading en el botón
        var $btn = $(this).find('button[type="submit"]');
        var originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Iniciando sesión...');
        $btn.prop('disabled', true);
        
        // Si hay error, restaurar botón después de 3 segundos
        setTimeout(function() {
            $btn.html(originalText);
            $btn.prop('disabled', false);
        }, 3000);
    });
});
</script>
<?= $this->endSection() ?>