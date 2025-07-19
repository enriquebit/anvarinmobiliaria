<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Recuperar Contraseña<?= $this->endSection() ?>

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
            <p class="text-muted">Recuperar Contraseña</p>
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
            <?php if (session('error') !== null): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <?= session('error') ?>
                </div>
            <?php endif ?>
            
            <?php if (session('errors') !== null): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Errores de validación:</strong>
                    <ul class="mb-0 mt-2">
                        <?php if (is_array(session('errors'))): ?>
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        <?php else: ?>
                            <li><?= esc(session('errors')) ?></li>
                        <?php endif ?>
                    </ul>
                </div>
            <?php endif ?>

            <!-- Información sobre el proceso -->
            <div class="alert alert-info">
                <i class="icon fas fa-info-circle"></i>
                <strong>Instrucciones:</strong><br>
                Ingresa tu correo electrónico y te enviaremos un enlace mágico para que puedas acceder a tu cuenta sin contraseña.
            </div>

            <!-- FORMULARIO DE RECUPERACIÓN -->
            <?= form_open(url_to('magic-link'), ['id' => 'magicLinkForm']) ?>
                <?= csrf_field() ?>
                
                <!-- Campo Email -->
                <div class="input-group mb-3">
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="Correo electrónico"
                           value="<?= old('email', auth()->user()->email ?? '') ?>"
                           required 
                           autocomplete="email"
                           autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <!-- Botón de envío -->
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-magic mr-2"></i>
                            Enviar Enlace Mágico
                        </button>
                    </div>
                </div>

            <?= form_close() ?>

            <!-- Links adicionales -->
            <div class="text-center mt-4">
                <p class="mb-1">
                    <a href="<?= base_url('login') ?>">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Volver al inicio de sesión
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
    // Auto-hide alerts después de 8 segundos
    setTimeout(function() {
        $('.alert').slideUp('slow');
    }, 8000);
    
    // Validación del formulario
    $('#magicLinkForm').on('submit', function(e) {
        var email = $('input[name="email"]').val();
        
        if (!email) {
            e.preventDefault();
            alert('Por favor, ingresa tu correo electrónico.');
            return false;
        }
        
        // Validar formato de email básico
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Por favor, ingresa un correo electrónico válido.');
            return false;
        }
        
        // Mostrar loading en el botón
        var $btn = $(this).find('button[type="submit"]');
        var originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Enviando enlace...');
        $btn.prop('disabled', true);
        
        // Si hay error, restaurar botón después de 5 segundos
        setTimeout(function() {
            $btn.html(originalText);
            $btn.prop('disabled', false);
        }, 5000);
    });
});
</script>
<?= $this->endSection() ?>