<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">
        <?= form_open('/admin/clientes/alt_store', [
            'id' => 'form-crear-cliente',
            'class' => 'needs-validation',
            'novalidate' => true,
            'method' => 'POST'
        ]) ?>
        <?= csrf_field() ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">Formulario Alterno: Crear Cliente</h4>
            </div>
            <div class="card-body">
                <!-- Aquí agregamos los campos de entrada del formulario -->
                <div class="form-group">
                    <label for="nombres">Nombres *</label>
                    <input type="text" class="form-control" name="nombres" required>
                </div>
                <div class="form-group">
                    <label for="apellido_paterno">Apellido Paterno *</label>
                    <input type="text" class="form-control" name="apellido_paterno" required>
                </div>
                <div class="form-group">
                    <label for="apellido_materno">Apellido Materno *</label>
                    <input type="text" class="form-control" name="apellido_materno" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input type="text" class="form-control" name="telefono" required>
                </div>
                <!-- Botones de acción -->
                <button type="submit" class="btn btn-primary">Crear Cliente</button>
            </div>
        </div>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>
