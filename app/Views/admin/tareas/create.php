<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus"></i>
                    Crear Nueva Tarea
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/tareas') ?>">Tareas</a></li>
                    <li class="breadcrumb-item active">Crear</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i>
                    Formulario de Nueva Tarea
                </h3>
            </div>
            <form action="<?= site_url('/admin/tareas/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="titulo">Título de la Tarea <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['titulo']) ? 'is-invalid' : '' ?>" 
                                       id="titulo" 
                                       name="titulo" 
                                       value="<?= old('titulo') ?>" 
                                       required>
                                <?php if (isset($errors['titulo'])): ?>
                                    <div class="invalid-feedback"><?= $errors['titulo'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="prioridad">Prioridad <span class="text-danger">*</span></label>
                                <select class="form-control <?= isset($errors['prioridad']) ? 'is-invalid' : '' ?>" 
                                        id="prioridad" 
                                        name="prioridad" 
                                        required>
                                    <option value="">Seleccionar prioridad</option>
                                    <?php foreach ($prioridades as $valor => $texto): ?>
                                        <option value="<?= $valor ?>" <?= old('prioridad') === $valor ? 'selected' : '' ?>>
                                            <?= $texto ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['prioridad'])): ?>
                                    <div class="invalid-feedback"><?= $errors['prioridad'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="asignado_a">Asignar a <span class="text-danger">*</span></label>
                                <?php if (empty($usuarios)): ?>
                                    <select class="form-control" id="asignado_a" name="asignado_a" disabled>
                                        <option value="">No hay usuarios disponibles</option>
                                    </select>
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        No hay usuarios disponibles para asignar tareas. 
                                        <a href="<?= site_url('/admin/tareas/mis-tareas') ?>" class="text-primary">Crear tarea personal</a>
                                    </small>
                                <?php else: ?>
                                    <select class="form-control select2 <?= isset($errors['asignado_a']) ? 'is-invalid' : '' ?>" 
                                            id="asignado_a" 
                                            name="asignado_a" 
                                            required>
                                        <option value="">Seleccionar usuario</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?= $usuario->id ?>" <?= old('asignado_a') == $usuario->id ? 'selected' : '' ?>>
                                                <?= esc($usuario->nombre_completo) ?><?php if (!empty($usuario->username)): ?> (<?= esc($usuario->username) ?>)<?php elseif (!empty($usuario->email)): ?> (<?= esc($usuario->email) ?>)<?php endif; ?><?= $usuario->id == $currentUserId ? ' - (yo)' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['asignado_a'])): ?>
                                        <div class="invalid-feedback"><?= $errors['asignado_a'] ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="descripcion">Descripción de la Tarea</label>
                                <textarea class="form-control <?= isset($errors['descripcion']) ? 'is-invalid' : '' ?>" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="4" 
                                          placeholder="Describe detalladamente lo que debe hacer el usuario..."><?= old('descripcion') ?></textarea>
                                <?php if (isset($errors['descripcion'])): ?>
                                    <div class="invalid-feedback"><?= $errors['descripcion'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_vencimiento">Fecha Límite</label>
                                <input type="date" 
                                       class="form-control <?= isset($errors['fecha_vencimiento']) ? 'is-invalid' : '' ?>" 
                                       id="fecha_vencimiento" 
                                       name="fecha_vencimiento" 
                                       value="<?= old('fecha_vencimiento') ?>"
                                       min="<?= date('Y-m-d') ?>">
                                <small class="form-text text-muted">
                                    Opcional. Si no se especifica, la tarea no tendrá fecha límite.
                                </small>
                                <?php if (isset($errors['fecha_vencimiento'])): ?>
                                    <div class="invalid-feedback"><?= $errors['fecha_vencimiento'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comentarios_admin">Comentarios Adicionales</label>
                                <textarea class="form-control" 
                                          id="comentarios_admin" 
                                          name="comentarios_admin" 
                                          rows="3" 
                                          placeholder="Comentarios adicionales para el usuario asignado..."><?= old('comentarios_admin') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Plantillas predefinidas -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-secondary collapsed-card">
                                <div class="card-header">
                                    <h3 class="card-title">Plantillas Predefinidas</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-outline-primary btn-block btn-plantilla" 
                                                    data-titulo="Revisar documentación"
                                                    data-descripcion="Revisar y actualizar la documentación del módulo correspondiente"
                                                    data-prioridad="media">
                                                <i class="fas fa-book"></i> Revisar Documentación
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-outline-warning btn-block btn-plantilla"
                                                    data-titulo="Corregir errores reportados"
                                                    data-descripcion="Identificar y corregir los errores reportados en el sistema"
                                                    data-prioridad="alta">
                                                <i class="fas fa-bug"></i> Corregir Errores
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-outline-success btn-block btn-plantilla"
                                                    data-titulo="Implementar nueva funcionalidad"
                                                    data-descripcion="Desarrollar e implementar nueva funcionalidad según especificaciones"
                                                    data-prioridad="alta">
                                                <i class="fas fa-code"></i> Nueva Funcionalidad
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?php if (empty($usuarios)): ?>
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="fas fa-ban"></i> No hay usuarios disponibles
                                </button>
                                <a href="<?= site_url('/admin/tareas/mis-tareas') ?>" class="btn btn-primary">
                                    <i class="fas fa-user-check"></i> Crear Tarea Personal
                                </a>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Crear Tarea
                                </button>
                            <?php endif; ?>
                            <a href="<?= site_url('/admin/tareas') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                Los campos marcados con <span class="text-danger">*</span> son obligatorios
                            </small>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Manejar plantillas predefinidas
    $('.btn-plantilla').on('click', function() {
        const titulo = $(this).data('titulo');
        const descripcion = $(this).data('descripcion');
        const prioridad = $(this).data('prioridad');
        
        $('#titulo').val(titulo);
        $('#descripcion').val(descripcion);
        $('#prioridad').val(prioridad);
        
        Swal.fire({
            icon: 'success',
            title: 'Plantilla aplicada',
            text: 'Los campos se han rellenado con la plantilla seleccionada',
            showConfirmButton: false,
            timer: 1500
        });
    });

    // Debug: Log datos del formulario antes de enviar
    $('form').on('submit', function(e) {
        
        let valid = true;
        
        // Validar título
        if ($('#titulo').val().trim() === '') {
            $('#titulo').addClass('is-invalid');
            valid = false;
        } else {
            $('#titulo').removeClass('is-invalid');
        }
        
        // Validar prioridad
        if ($('#prioridad').val() === '') {
            $('#prioridad').addClass('is-invalid');
            valid = false;
        } else {
            $('#prioridad').removeClass('is-invalid');
        }
        
        // Validar usuario asignado
        if ($('#asignado_a').val() === '') {
            $('#asignado_a').addClass('is-invalid');
            valid = false;
        } else {
            $('#asignado_a').removeClass('is-invalid');
        }
        
        if (!valid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Campos requeridos',
                text: 'Por favor complete todos los campos obligatorios'
            });
        } else {
        }
    });
});
</script>
<?= $this->endSection() ?>