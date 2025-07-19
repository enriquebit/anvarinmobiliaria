<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fab fa-google-drive mr-2"></i>
                        Estado de Google Drive OAuth2
                    </h3>
                </div>
                <div class="card-body">
                    
                    <?php if ($has_tokens): ?>
                        <!-- Tokens existentes -->
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle mr-2"></i>Autorización Activa</h5>
                            <p class="mb-0">Google Drive está autorizado y listo para usar.</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información de Tokens:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Creado:</strong> <?= $tokens['created_at'] ?></li>
                                    <li><strong>Expira:</strong> <?= $tokens['expires_at'] ?></li>
                                    <li><strong>Estado:</strong> 
                                        <?php if ($tokens['is_expired']): ?>
                                            <span class="badge badge-warning">Expirado</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Acciones:</h6>
                                <a href="<?= base_url('admin/google-drive/test') ?>" class="btn btn-info btn-sm mb-2">
                                    <i class="fas fa-vial mr-2"></i>Probar Conexión
                                </a><br>
                                
                                <form method="post" action="<?= base_url('admin/google-drive/revoke') ?>" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de revocar la autorización?')">
                                        <i class="fas fa-times mr-2"></i>Revocar Autorización
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Sin tokens -->
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Autorización Requerida</h5>
                            <p>Google Drive no está autorizado. Es necesario completar el proceso OAuth2.</p>
                        </div>
                        
                        <div class="text-center">
                            <a href="<?= base_url('admin/google-drive/authorize') ?>" class="btn btn-primary btn-lg">
                                <i class="fab fa-google mr-2"></i>Autorizar Google Drive
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <h6>Instrucciones:</h6>
                            <ol>
                                <li>Haz clic en "Autorizar Google Drive"</li>
                                <li>Serás redirigido a Google para autorizar la aplicación</li>
                                <li>Acepta los permisos solicitados</li>
                                <li>Serás redirigido de vuelta con la autorización completa</li>
                            </ol>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Información Técnica:</h6>
                            <small class="text-muted">
                                <strong>Archivo de tokens:</strong> <?= $token_file ?><br>
                                <strong>Scopes requeridos:</strong> https://www.googleapis.com/auth/drive.file<br>
                                <strong>Redirect URI:</strong> <?= base_url('admin/google-drive/callback') ?>
                            </small>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>