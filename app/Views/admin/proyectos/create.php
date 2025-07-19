<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
Crear Proyecto
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/proyectos') ?>">Proyectos</a></li>
<li class="breadcrumb-item active">Crear</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Proyecto</h3>
                </div>
                
                <form action="<?= site_url('/admin/proyectos/store') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre del Proyecto *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= old('nombre') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="clave">Clave *</label>
                                    <input type="text" class="form-control" id="clave" name="clave" 
                                           value="<?= old('clave') ?>" required>
                                    <small class="form-text text-muted">Código interno del proyecto</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="empresas_id">Empresa *</label>
                                    <select class="form-control select2" id="empresas_id" name="empresas_id" required>
                                        <option value="">Seleccionar empresa...</option>
                                        <?php foreach ($empresas as $empresa): ?>
                                            <option value="<?= $empresa->id ?>" 
                                                    <?= old('empresas_id') == $empresa->id ? 'selected' : '' ?>>
                                                <?= esc($empresa->nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Color</label>
                                    <input type="color" class="form-control" id="color" name="color" 
                                           value="<?= old('color', '#007bff') ?>" style="height: 38px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3"><?= old('descripcion') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?= old('direccion') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitud">Longitud</label>
                                    <input type="text" class="form-control" id="longitud" name="longitud" 
                                           value="<?= old('longitud') ?>" placeholder="-99.1234567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitud">Latitud</label>
                                    <input type="text" class="form-control" id="latitud" name="latitud" 
                                           value="<?= old('latitud') ?>" placeholder="19.1234567">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="archivo">Documento (Opcional)</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="archivo" name="archivo" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <label class="custom-file-label" for="archivo">Seleccionar archivo...</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Tipos permitidos: PDF, JPG, PNG. Tamaño máximo: 20MB
                            </small>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Proyecto
                        </button>
                        <a href="<?= site_url('/admin/proyectos') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>

<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });
    
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
<?= $this->endSection() ?>