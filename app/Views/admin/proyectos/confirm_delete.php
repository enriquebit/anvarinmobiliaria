<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/proyectos') ?>">Proyectos</a></li>
<li class="breadcrumb-item active">Eliminar</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                </h3>
            </div>
            
            <div class="card-body">
                <?php if (!$puede_eliminar): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-ban"></i> No se puede eliminar</h5>
                        <p>Este proyecto no puede ser eliminado porque tiene <strong><?= $total_manzanas ?> manzana(s)</strong> asociada(s).</p>
                        <p>Primero debe eliminar o reasignar todas las manzanas asociadas a este proyecto.</p>
                    </div>
                    
                    <div class="text-center">
                        <a href="<?= site_url('/admin/proyectos') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Proyectos
                        </a>
                        <a href="<?= site_url('/admin/manzanas') ?>" class="btn btn-info">
                            <i class="fas fa-th-large"></i> Gestionar Manzanas
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-question-circle"></i> ¿Está seguro?</h5>
                        <p>¿Desea eliminar el proyecto <strong>"<?= $proyecto->nombre ?>"</strong>?</p>
                        <p><small class="text-muted">Esta acción no se puede deshacer.</small></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Información del Proyecto:</strong>
                            <ul class="list-unstyled mt-2">
                                <li><strong>Nombre:</strong> <?= $proyecto->nombre ?></li>
                                <li><strong>Clave:</strong> <?= $proyecto->clave ?></li>
                                <li><strong>Estado:</strong> <?= $proyecto->estatus ?></li>
                                <li><strong>Manzanas:</strong> <?= $total_manzanas ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <a href="<?= site_url('/admin/proyectos') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        
                        <form method="POST" action="<?= site_url('/admin/proyectos/delete/' . $proyecto->id) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Confirma la eliminación?')">
                                <i class="fas fa-trash"></i> Eliminar Proyecto
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>