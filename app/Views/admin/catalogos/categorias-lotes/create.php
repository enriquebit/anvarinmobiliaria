<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
Nueva Categoría de Lote
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/catalogos') ?>">Catálogos</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/catalogos/categorias-lotes') ?>">Categorías de Lotes</a></li>
<li class="breadcrumb-item active">Crear</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= $titulo ?></h3>
            </div>
            
            <form id="formCategoria" action="<?= site_url('/admin/catalogos/categorias-lotes') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="nombre">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?= old('nombre') ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="3" placeholder="Descripción opcional de la categoría"><?= old('descripcion') ?></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Categoría
                    </button>
                    <a href="<?= site_url('/admin/catalogos/categorias-lotes') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formCategoria').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Deshabilitar botón
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    
                    setTimeout(() => {
                        window.location.href = '<?= site_url('/admin/catalogos/categorias-lotes') ?>';
                    }, 1500);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    });
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    
                    Object.keys(errors).forEach(field => {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field]);
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error al guardar la categoría'
                    });
                }
                
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});

// Toast configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});
</script>
<?= $this->endSection() ?>