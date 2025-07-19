<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
Ver Proyecto: <?= esc($proyecto->nombre) ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/proyectos') ?>">Proyectos</a></li>
<li class="breadcrumb-item active">Ver</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building"></i> 
                                <?= esc($proyecto->nombre) ?>
                            </h3>
                            <div class="card-tools">
                                <span class="badge" style="background-color: <?= $proyecto->getColorHex() ?>; color: white;">
                                    <?= $proyecto->getColorHex() ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Clave:</strong>
                                    <p class="text-muted"><code><?= esc($proyecto->clave) ?></code></p>
                                    
                                    <strong>Empresa:</strong>
                                    <p class="text-muted"><?= esc($proyecto->nombre_empresa) ?></p>
                                    
                                    <strong>Fecha de Creación:</strong>
                                    <p class="text-muted"><?= $proyecto->created_at->format('d/m/Y H:i') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($proyecto->direccion)): ?>
                                    <strong>Dirección:</strong>
                                    <p class="text-muted"><?= esc($proyecto->direccion) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($proyecto->hasCoordinates()): ?>
                                    <strong>Coordenadas:</strong>
                                    <p class="text-muted">
                                        Lat: <?= esc($proyecto->latitud) ?><br>
                                        Lng: <?= esc($proyecto->longitud) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($proyecto->descripcion)): ?>
                            <strong>Descripción:</strong>
                            <p class="text-muted"><?= nl2br(esc($proyecto->descripcion)) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer">
                            <a href="<?= site_url('/admin/proyectos/edit/'. $proyecto->id) ?>" 
                               class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="<?= site_url('/admin/proyectos') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-alt"></i> 
                                Documentos
                            </h3>
                        </div>
                        
                        <div class="card-body">
                            <?php if (empty($documentos)): ?>
                                <p class="text-muted text-center">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay documentos adjuntos
                                </p>
                            <?php else: ?>
                                <?php foreach ($documentos as $documento): ?>
                                <div class="document-item mb-3 p-2 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-start">
                                            <div class="mr-3">
                                                <?php
                                                $extension = strtolower(pathinfo($documento->nombre_archivo, PATHINFO_EXTENSION));
                                                ?>
                                                <?php if ($extension === 'pdf'): ?>
                                                    <i class="fas fa-file-pdf text-danger fa-2x"></i>
                                                <?php elseif (in_array($extension, ['jpg', 'jpeg', 'png'])): ?>
                                                    <i class="fas fa-file-image text-success fa-2x"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-file text-secondary fa-2x"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= esc($documento->nombre_archivo) ?></h6>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($documento->created_at)) ?><br>
                                                    Tamaño: <?= number_format($documento->tamaño_archivo / 1024, 2) ?> KB
                                                </small>
                                            </div>
                                        </div>
                                        <div class="btn-group-sm">
                                            <a href="<?= site_url('/admin/proyectos/documentos/' . $documento->id . '/descargar') ?>" 
                                               class="btn btn-outline-primary btn-sm" title="Descargar">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
<?= $this->endSection() ?>