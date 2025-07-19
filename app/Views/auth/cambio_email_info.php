<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Cambio de Email - Información<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-box">
    <div class="card card-outline card-info">
        <div class="card-header text-center">
            <div class="login-logo">
                <img src="<?= base_url('assets/img/logo_admin.png') ?>" 
                     alt="Anvar Inmobiliaria" 
                     class="img-fluid">
            </div>
            <h4 class="mb-0">
                <strong>ANVAR INMOBILIARIA</strong>
            </h4>
            <p class="text-muted">Cambio de Email de Acceso</p>
        </div>
        
        <div class="card-body">
            <!-- Información principal -->
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <h5>¿Necesitas cambiar tu email de acceso?</h5>
                <p class="mb-0">Esta funcionalidad te permite actualizar tu correo electrónico para iniciar sesión en el sistema ANVAR.</p>
            </div>

            <!-- Proceso paso a paso -->
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-list-ol mr-2"></i>¿Cómo funciona?</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-blue">Proceso de Cambio</span>
                        </div>
                        
                        <div>
                            <i class="fas fa-user bg-primary"></i>
                            <div class="timeline-item">
                                <h6 class="timeline-header">
                                    <strong>Paso 1:</strong> Inicias sesión con tu email actual
                                </h6>
                                <div class="timeline-body">
                                    Accede a tu perfil desde el panel de administración o cliente.
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <i class="fas fa-envelope bg-info"></i>
                            <div class="timeline-item">
                                <h6 class="timeline-header">
                                    <strong>Paso 2:</strong> Solicitas el cambio
                                </h6>
                                <div class="timeline-body">
                                    Ingresas tu nuevo email en la sección "Cambiar Email" de tu perfil.
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <i class="fas fa-paper-plane bg-warning"></i>
                            <div class="timeline-item">
                                <h6 class="timeline-header">
                                    <strong>Paso 3:</strong> Recibes email de verificación
                                </h6>
                                <div class="timeline-body">
                                    Se envía un enlace de confirmación a tu nuevo correo electrónico.
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <i class="fas fa-check-circle bg-success"></i>
                            <div class="timeline-item">
                                <h6 class="timeline-header">
                                    <strong>Paso 4:</strong> Confirmas el cambio
                                </h6>
                                <div class="timeline-body">
                                    Haces clic en el enlace del email para completar el cambio.
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <i class="fas fa-star bg-green"></i>
                            <div class="timeline-item">
                                <h6 class="timeline-header">
                                    <strong>¡Listo!</strong> Ya puedes usar tu nuevo email
                                </h6>
                                <div class="timeline-body">
                                    Desde ese momento usarás tu nuevo email para iniciar sesión.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información importante -->
            <div class="alert alert-warning">
                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Información importante:</h6>
                <ul class="mb-0">
                    <li><strong>Seguridad:</strong> El enlace de verificación expira en 2 horas</li>
                    <li><strong>Único uso:</strong> Cada enlace solo puede usarse una vez</li>
                    <li><strong>Contraseña:</strong> Tu contraseña permanece igual</li>
                    <li><strong>Acceso:</strong> Debes tener acceso al nuevo email para confirmar</li>
                </ul>
            </div>

            <!-- Acciones disponibles -->
            <div class="text-center mt-4">
                <a href="<?= base_url('login') ?>" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Iniciar Sesión
                </a>
                
                <a href="<?= base_url('magic-link') ?>" class="btn btn-secondary ml-2">
                    <i class="fas fa-magic mr-2"></i>
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <!-- Ayuda adicional -->
            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-1">
                    <a href="<?= base_url('/') ?>">
                        <i class="fas fa-home mr-1"></i>
                        Volver al inicio
                    </a>
                </p>
                <p class="mb-0">
                    <small class="text-muted">
                        Si tienes problemas, contacta al administrador del sistema
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
});
</script>
<?= $this->endSection() ?>