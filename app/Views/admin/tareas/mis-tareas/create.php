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
                    Crear Tarea Personal
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/tareas/mis-tareas') ?>">Mis Tareas</a></li>
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
                    Nueva Tarea Personal
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">Esta tarea será asignada a ti mismo</span>
                </div>
            </div>
            <form action="<?= site_url('/admin/tareas/mis-tareas/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="titulo">Título de la Tarea <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['titulo']) ? 'is-invalid' : '' ?>" 
                                       id="titulo" 
                                       name="titulo" 
                                       value="<?= old('titulo') ?>" 
                                       placeholder="Ej: Revisar documentación del proyecto"
                                       required>
                                <?php if (isset($errors['titulo'])): ?>
                                    <div class="invalid-feedback"><?= $errors['titulo'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="descripcion">Descripción de la Tarea</label>
                                <textarea class="form-control <?= isset($errors['descripcion']) ? 'is-invalid' : '' ?>" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="4" 
                                          placeholder="Describe detalladamente lo que necesitas hacer..."><?= old('descripcion') ?></textarea>
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
                            <!-- Información sobre la tarea personal -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-info-circle text-info"></i> Información:</h6>
                                    <ul class="mb-0">
                                        <li>Esta tarea será asignada a ti mismo</li>
                                        <li>Podrás actualizar el progreso en cualquier momento</li>
                                        <li>Solo tú podrás ver y modificar esta tarea</li>
                                        <li>Se iniciará en estado "Pendiente"</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plantillas predefinidas para tareas personales -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-secondary collapsed-card">
                                <div class="card-header">
                                    <h3 class="card-title">Plantillas de Tareas Personales</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-outline-info btn-block btn-plantilla" 
                                                    data-titulo="Estudiar nueva tecnología"
                                                    data-descripcion="Dedicar tiempo a aprender y practicar con una nueva tecnología"
                                                    data-prioridad="media">
                                                <i class="fas fa-book"></i><br>Aprendizaje
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-outline-success btn-block btn-plantilla"
                                                    data-titulo="Mejorar proceso de trabajo"
                                                    data-descripcion="Identificar y implementar mejoras en mi flujo de trabajo diario"
                                                    data-prioridad="alta">
                                                <i class="fas fa-cogs"></i><br>Optimización
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-outline-warning btn-block btn-plantilla"
                                                    data-titulo="Organizar archivos y documentos"
                                                    data-descripcion="Limpiar y organizar archivos de trabajo y documentos personales"
                                                    data-prioridad="baja">
                                                <i class="fas fa-folder"></i><br>Organización
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-outline-danger btn-block btn-plantilla"
                                                    data-titulo="Completar entrega urgente"
                                                    data-descripcion="Finalizar y entregar trabajo pendiente con fecha límite próxima"
                                                    data-prioridad="urgente">
                                                <i class="fas fa-fire"></i><br>Urgente
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
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Crear Mi Tarea
                            </button>
                            <a href="<?= site_url('/admin/tareas/mis-tareas') ?>" class="btn btn-secondary">
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
    // Manejar plantillas predefinidas
    $('.btn-plantilla').on('click', function() {
        const titulo = $(this).data('titulo');
        const descripcion = $(this).data('descripcion');
        const prioridad = $(this).data('prioridad');
        
        $('#titulo').val(titulo);
        $('#descripcion').val(descripcion);
        $('#prioridad').val(prioridad);
        
        // Feedback visual
        $(this).removeClass('btn-outline-info btn-outline-success btn-outline-warning btn-outline-danger')
               .addClass('btn-primary');
        
        Swal.fire({
            icon: 'success',
            title: 'Plantilla aplicada',
            text: 'Los campos se han rellenado con la plantilla seleccionada',
            showConfirmButton: false,
            timer: 1500
        });
        
        // Restaurar estilo después de 2 segundos
        setTimeout(() => {
            $(this).removeClass('btn-primary')
                   .addClass('btn-outline-info');
        }, 2000);
    });

    // Validación del formulario
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
        
        if (!valid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Campos requeridos',
                text: 'Por favor complete todos los campos obligatorios'
            });
        }
    });
});
</script>
<?= $this->endSection() ?>