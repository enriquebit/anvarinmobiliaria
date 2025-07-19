<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <?php foreach ($breadcrumb as $item): ?>
                        <?php if (!empty($item['url'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= $item['name'] ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Formulario -->
        <form action="<?= site_url('/admin/empresas/store') ?>" method="POST" id="form-empresa">
            <?= csrf_field() ?>
            
            <div class="row">
                <!-- Información Básica -->
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building mr-2"></i>
                                Información Básica de la Empresa
                            </h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Nombre y RFC -->
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="nombre">
                                            Nombre de la Empresa <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>" 
                                               id="nombre" 
                                               name="nombre" 
                                               value="<?= old('nombre') ?>"
                                               placeholder="Ej: ANVAR Inmobiliaria"
                                               required maxlength="255">
                                        <?php if (isset($errors['nombre'])): ?>
                                            <div class="invalid-feedback"><?= $errors['nombre'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="rfc">
                                            RFC <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control <?= isset($errors['rfc']) ? 'is-invalid' : '' ?>" 
                                               id="rfc" 
                                               name="rfc" 
                                               value="<?= old('rfc') ?>"
                                               placeholder="ABC123456789"
                                               required maxlength="13"
                                               style="text-transform: uppercase;">
                                        <?php if (isset($errors['rfc'])): ?>
                                            <div class="invalid-feedback"><?= $errors['rfc'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Razón Social -->
                            <div class="form-group">
                                <label for="razon_social">Razón Social</label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['razon_social']) ? 'is-invalid' : '' ?>" 
                                       id="razon_social" 
                                       name="razon_social" 
                                       value="<?= old('razon_social') ?>"
                                       placeholder="Razón social completa de la empresa"
                                       maxlength="300">
                                <?php if (isset($errors['razon_social'])): ?>
                                    <div class="invalid-feedback"><?= $errors['razon_social'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Domicilio -->
                            <div class="form-group">
                                <label for="domicilio">Domicilio Fiscal</label>
                                <textarea class="form-control <?= isset($errors['domicilio']) ? 'is-invalid' : '' ?>" 
                                          id="domicilio" 
                                          name="domicilio" 
                                          rows="3"
                                          placeholder="Dirección completa del domicilio fiscal"><?= old('domicilio') ?></textarea>
                                <?php if (isset($errors['domicilio'])): ?>
                                    <div class="invalid-feedback"><?= $errors['domicilio'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Contacto -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="text" 
                                               class="form-control <?= isset($errors['telefono']) ? 'is-invalid' : '' ?>" 
                                               id="telefono" 
                                               name="telefono" 
                                               value="<?= old('telefono') ?>"
                                               placeholder="6691234567"
                                               maxlength="15">
                                        <?php if (isset($errors['telefono'])): ?>
                                            <div class="invalid-feedback"><?= $errors['telefono'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Corporativo</label>
                                        <input type="email" 
                                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                               id="email" 
                                               name="email" 
                                               value="<?= old('email') ?>"
                                               placeholder="contacto@empresa.com"
                                               maxlength="100">
                                        <?php if (isset($errors['email'])): ?>
                                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Representante Legal -->
                            <div class="form-group">
                                <label for="representante">Representante Legal</label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['representante']) ? 'is-invalid' : '' ?>" 
                                       id="representante" 
                                       name="representante" 
                                       value="<?= old('representante') ?>"
                                       placeholder="Nombre completo del representante legal"
                                       maxlength="200">
                                <?php if (isset($errors['representante'])): ?>
                                    <div class="invalid-feedback"><?= $errors['representante'] ?></div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Botones de Acción -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-6">
                                    <a href="<?= site_url('/admin/empresas') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Cancelar
                                    </a>
                                </div>
                                <div class="col-sm-6 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i>
                                        Guardar Empresa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>

    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- jquery-validation -->
<script src="<?= base_url('assets/plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-validation/additional-methods.min.js') ?>"></script>

<!-- Toastr -->
<script src="<?= base_url('assets/plugins/toastr/toastr.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // =====================================================================
    // VALIDACIÓN DEL FORMULARIO
    // =====================================================================
    
    $('#form-empresa').validate({
        rules: {
            nombre: {
                required: true,
                minlength: 2,
                maxlength: 255
            },
            rfc: {
                required: true,
                minlength: 12,
                maxlength: 13
            },
            razon_social: {
                maxlength: 300
            },
            telefono: {
                maxlength: 15,
                digits: true
            },
            email: {
                email: true,
                maxlength: 100
            },
            representante: {
                maxlength: 200
            },
            porcentaje_anticipo: {
                min: 0,
                max: 100,
                number: true
            },
            anticipo_fijo: {
                min: 0,
                number: true
            },
            apartado_minimo: {
                min: 0,
                number: true
            },
            porcentaje_comision: {
                min: 0,
                max: 100,
                number: true
            },
            comision_fija: {
                min: 0,
                number: true
            },
            meses_sin_intereses: {
                min: 0,
                digits: true
            },
            meses_con_intereses: {
                min: 0,
                digits: true
            },
            porcentaje_interes_anual: {
                min: 0,
                max: 100,
                number: true
            },
            dias_anticipo: {
                min: 0,
                max: 365,
                digits: true
            },
            porcentaje_cancelacion: {
                min: 0,
                max: 100,
                number: true
            }
        },
        messages: {
            nombre: {
                required: "El nombre de la empresa es obligatorio",
                minlength: "El nombre debe tener al menos 2 caracteres",
                maxlength: "El nombre no puede exceder 255 caracteres"
            },
            rfc: {
                required: "El RFC es obligatorio",
                minlength: "El RFC debe tener al menos 12 caracteres",
                maxlength: "El RFC no puede exceder 13 caracteres"
            },
            telefono: {
                digits: "El teléfono solo debe contener números"
            },
            email: {
                email: "Por favor ingresa un email válido"
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });

    // =====================================================================
    // FORMATEO AUTOMÁTICO
    // =====================================================================
    
    // RFC en mayúsculas automáticamente
    $('#rfc').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Formateo de teléfono (solo números)
    $('#telefono').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // =====================================================================
    // MOSTRAR TOASTS SI HAY MENSAJES DE SESIÓN
    // =====================================================================
    
    <?php if (session('error')): ?>
        toastr.error('<?= session('error') ?>', 'Error', {
            timeOut: 5000,
            progressBar: true
        });
    <?php endif; ?>

    <?php if (session('errors')): ?>
        toastr.error('Por favor corrige los errores marcados en rojo', 'Errores de Validación', {
            timeOut: 8000,
            progressBar: true
        });
    <?php endif; ?>

    // =====================================================================
    // CONFIGURACIÓN DE TOASTR
    // =====================================================================
    
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
});
</script>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Toastr -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/toastr/toastr.min.css') ?>">

<style>
/* Estilos personalizados para el formulario */
.card-header .card-title {
    font-weight: 600;
}

.form-group label {
    font-weight: 500;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

/* Mejoras responsive */
@media (max-width: 768px) {
    .col-md-4 .card {
        margin-top: 1rem;
    }
    
    .card-footer .text-right {
        text-align: left !important;
        margin-top: 0.5rem;
    }
}

/* Estados de validación mejorados */
.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.invalid-feedback {
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}
</style>
<?= $this->endSection() ?>