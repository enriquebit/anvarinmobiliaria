<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= $titulo ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-box">
    <div class="card card-outline <?= $exito ? 'card-success' : 'card-danger' ?>">
        <div class="card-header text-center">
            <div class="login-logo">
                <img src="<?= base_url('assets/img/logo_admin.png') ?>" 
                     alt="Anvar Inmobiliaria" 
                     class="img-fluid">
            </div>
            <h4 class="mb-0">
                <strong>ANVAR INMOBILIARIA</strong>
            </h4>
            <p class="text-muted">Verificación de Cambio de Email</p>
        </div>
        
        <div class="card-body">
            <!-- Resultado de la verificación -->
            <div class="alert alert-<?= $exito ? 'success' : 'danger' ?> text-center">
                <i class="icon fas fa-<?= $exito ? 'check-circle' : 'exclamation-triangle' ?> fa-2x mb-3"></i>
                <h5><?= $titulo ?></h5>
                <p class="mb-0"><?= esc($mensaje) ?></p>
            </div>

            <?php if ($exito): ?>
                <!-- Información adicional para éxito -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i>Información importante:</h6>
                    <ul class="mb-0">
                        <li>Tu email de acceso ha sido actualizado exitosamente</li>
                        <li>Usa <strong><?= esc($nuevo_email) ?></strong> para iniciar sesión</li>
                        <li>Tu contraseña permanece igual</li>
                        <li>Este cambio es efectivo inmediatamente</li>
                    </ul>
                </div>

                <!-- Botón para iniciar sesión -->
                <div class="text-center mt-4">
                    <a href="<?= base_url('login') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Iniciar Sesión con Nuevo Email
                    </a>
                </div>
            <?php else: ?>
                <!-- Opciones para error -->
                <div class="alert alert-warning">
                    <h6><i class="fas fa-lightbulb mr-2"></i>¿Qué puedes hacer?</h6>
                    <ul class="mb-0">
                        <li>Verificar que el enlace esté completo</li>
                        <li>Solicitar un nuevo cambio de email desde tu perfil</li>
                        <li>Contactar al administrador si el problema persiste</li>
                    </ul>
                </div>

                <!-- Botones de acción -->
                <div class="text-center mt-4">
                    <a href="<?= base_url('login') ?>" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Ir al Login
                    </a>
                    <a href="<?= base_url('magic-link') ?>" class="btn btn-secondary ml-2">
                        <i class="fas fa-magic mr-2"></i>
                        Recuperar Contraseña
                    </a>
                </div>
            <?php endif; ?>

            <!-- Enlaces adicionales -->
            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-1">
                    <a href="<?= base_url('/') ?>">
                        <i class="fas fa-home mr-1"></i>
                        Volver al inicio
                    </a>
                </p>
                
                <?php if (!$exito): ?>
                <p class="mb-0">
                    <small class="text-muted">
                        Si necesitas ayuda, contacta al administrador del sistema
                    </small>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // Auto-redirect después de éxito (opcional)
    <?php if ($exito): ?>
    setTimeout(function() {
        if (confirm('¿Quieres ir automáticamente al login para probar tu nuevo email?')) {
            window.location.href = '<?= base_url('login') ?>';
        }
    }, 10000); // 10 segundos
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>