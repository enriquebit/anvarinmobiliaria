<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
    <li class="breadcrumb-item <?= empty($item['url']) ? 'active' : '' ?>">
        <?php if (!empty($item['url'])): ?>
            <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
        <?php else: ?>
            <?= $item['name'] ?>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">
      <?= form_open("/admin/clientes/update/{$cliente->id}", ['id' => 'form-editar-cliente', 'class' => 'needs-validation', 'novalidate' => true, 'method' => 'POST']) ?>

<?= csrf_field() ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="card-title mb-0">
            <i class="fas fa-user-edit mr-2"></i>
            Editar Cliente
        </h4>
    </div>

    <div class="card-body">
        <!-- Mostrar Errores de Validación -->
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Por favor, corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Campo: Nombres -->
        <div class="form-group">
            <label for="nombres" class="required">Nombres *</label>
            <input type="text" class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>"
                   id="nombres" name="nombres" value="<?= old('nombres', $cliente->nombres) ?>" required maxlength="100"
                   placeholder="Ej: José Manuel">
            <?php if (isset($errors['nombres'])): ?>
                <div class="invalid-feedback"><?= $errors['nombres'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Campo: Apellido Paterno -->
        <div class="form-group">
            <label for="apellido_paterno" class="required">Apellido Paterno *</label>
            <input type="text" class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
                   id="apellido_paterno" name="apellido_paterno" value="<?= old('apellido_paterno', $cliente->apellido_paterno) ?>"
                   required maxlength="50" placeholder="Ej: García">
            <?php if (isset($errors['apellido_paterno'])): ?>
                <div class="invalid-feedback"><?= $errors['apellido_paterno'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Campo: Apellido Materno -->
        <div class="form-group">
            <label for="apellido_materno" class="required">Apellido Materno *</label>
            <input type="text" class="form-control <?= isset($errors['apellido_materno']) ? 'is-invalid' : '' ?>"
                   id="apellido_materno" name="apellido_materno" value="<?= old('apellido_materno', $cliente->apellido_materno) ?>"
                   required maxlength="50" placeholder="Ej: López">
            <?php if (isset($errors['apellido_materno'])): ?>
                <div class="invalid-feedback"><?= $errors['apellido_materno'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Campo: Email -->
        <div class="form-group">
            <label for="email" class="required">Email *</label>
            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   id="email" name="email" value="<?= old('email', $cliente->email) ?>" required maxlength="255"
                   placeholder="ejemplo@correo.com">
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= $errors['email'] ?></div>
            <?php endif; ?>
            <small class="form-text text-muted">Este será el email de acceso al sistema</small>
        </div>

        <!-- Campo: Teléfono -->
        <div class="form-group">
            <label for="telefono" class="required">Teléfono *</label>
            <input type="text" class="form-control <?= isset($errors['telefono']) ? 'is-invalid' : '' ?>"
                   id="telefono" name="telefono" value="<?= old('telefono', $cliente->telefono) ?>" required maxlength="15"
                   placeholder="5551234567">
            <?php if (isset($errors['telefono'])): ?>
                <div class="invalid-feedback"><?= $errors['telefono'] ?></div>
            <?php endif; ?>
            <small class="form-text text-muted">Solo números, 10 dígitos</small>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="card-footer bg-light">
        <div class="row">
            <div class="col-md-6">
                <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </a>
            </div>
            <div class="col-md-6 text-right">
                <button type="submit" class="btn btn-primary btn-lg" id="btn-guardar">
                    <i class="fas fa-save mr-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<?= form_close() ?>

    </div>
</div>
<?= $this->endSection() ?>
