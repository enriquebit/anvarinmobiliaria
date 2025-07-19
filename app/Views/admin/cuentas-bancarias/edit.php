<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>


<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
    <?php if (!empty($item['url'])): ?>
        <li class="breadcrumb-item">
            <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
        </li>
    <?php else: ?>
        <li class="breadcrumb-item active"><?= $item['name'] ?></li>
    <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php 
// Acceder directamente a los attributes para evitar problemas con getters
$cuentaData = [];
if (isset($cuenta)) {
    // Acceso directo a los attributes para evitar interferencia de métodos getter
    $reflection = new ReflectionClass($cuenta);
    $attributesProperty = $reflection->getProperty('attributes');
    $attributesProperty->setAccessible(true);
    $cuentaData = $attributesProperty->getValue($cuenta);
}
?>



<div class="row justify-content-center">
    <div class="col-md-10">
        
        <!-- INFORMACIÓN SUPERIOR -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="alert alert-warning">
                    <i class="fas fa-edit mr-2"></i>
                    <strong>Editando Cuenta:</strong> <?= $cuentaData['descripcion'] ?? '' ?> (<?= $cuentaData['banco'] ?? '' ?>)
                    <br><small>Modifica los datos según sea necesario. Los campos marcados con * son obligatorios.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h6 class="text-info">Saldo Actual</h6>
                        <?php 
                            $saldo = $cuentaData['saldo_actual'] ?? 0;
                            $colorSaldo = $saldo > 1000000 ? 'success' : ($saldo > 100000 ? 'info' : ($saldo > 0 ? 'warning' : 'danger'));
                            $simboloMoneda = ['MXN' => '$', 'USD' => '$', 'EUR' => '€'][$cuentaData['moneda'] ?? 'MXN'] ?? '$';
                        ?>
                        <h4 class="text-<?= $colorSaldo ?>"><?= $simboloMoneda ?> <?= number_format($saldo, 2, '.', ',') ?></h4>
                        <small>Tipo: <strong><?= ucfirst($cuentaData['tipo_cuenta'] ?? 'corriente') ?></strong></small>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="<?= base_url('admin/cuentas-bancarias/update/' . ($cuentaData['id'] ?? '')) ?>" method="POST" id="form-cuenta">
            <?= csrf_field() ?>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-university mr-2"></i>
                        Editar Cuenta Bancaria
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- INFORMACIÓN BÁSICA -->
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-info-circle"></i> Información Básica
                            </h5>
                            
                            <div class="form-group">
                                <label for="descripcion">Descripción de la Cuenta *</label>
                                <input type="text" class="form-control <?= isset($errors['descripcion']) ? 'is-invalid' : '' ?>" 
                                       id="descripcion" name="descripcion" value="<?= old('descripcion', $cuentaData['descripcion'] ?? '') ?>" 
                                       placeholder="Ej: Cuenta Principal Valle Natura" required>
                                <?php if (isset($errors['descripcion'])): ?>
                                    <div class="invalid-feedback"><?= $errors['descripcion'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="banco">Banco *</label>
                                <select class="form-control <?= isset($errors['banco']) ? 'is-invalid' : '' ?>" 
                                        id="banco" name="banco" required>
                                    <option value="">Seleccionar banco...</option>
                                    <?php foreach ($bancos as $codigo => $nombre): ?>
                                        <option value="<?= $codigo ?>" <?= old('banco', $cuentaData['banco'] ?? '') === $codigo ? 'selected' : '' ?>>
                                            <?= $nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['banco'])): ?>
                                    <div class="invalid-feedback"><?= $errors['banco'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="numero_cuenta">Número de Cuenta *</label>
                                <input type="text" class="form-control <?= isset($errors['numero_cuenta']) ? 'is-invalid' : '' ?>" 
                                       id="numero_cuenta" name="numero_cuenta" value="<?= old('numero_cuenta', $cuentaData['numero_cuenta'] ?? '') ?>" 
                                       placeholder="Ej: 0123456789" maxlength="20" required>
                                <?php if (isset($errors['numero_cuenta'])): ?>
                                    <div class="invalid-feedback"><?= $errors['numero_cuenta'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="clabe">CLABE Interbancaria</label>
                                <input type="text" class="form-control <?= isset($errors['clabe']) ? 'is-invalid' : '' ?>" 
                                       id="clabe" name="clabe" value="<?= old('clabe', $cuentaData['clabe'] ?? '') ?>" 
                                       placeholder="18 dígitos" maxlength="18" pattern="[0-9]{18}">
                                <small class="form-text text-muted">18 dígitos para transferencias SPEI</small>
                                <?php if (isset($errors['clabe'])): ?>
                                    <div class="invalid-feedback"><?= $errors['clabe'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="swift">Código SWIFT</label>
                                <input type="text" class="form-control <?= isset($errors['swift']) ? 'is-invalid' : '' ?>" 
                                       id="swift" name="swift" value="<?= old('swift', $cuentaData['swift'] ?? '') ?>" 
                                       placeholder="Ej: BBVAMXMM" maxlength="11">
                                <small class="form-text text-muted">Para transferencias internacionales</small>
                                <?php if (isset($errors['swift'])): ?>
                                    <div class="invalid-feedback"><?= $errors['swift'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="titular">Titular de la Cuenta *</label>
                                <input type="text" class="form-control <?= isset($errors['titular']) ? 'is-invalid' : '' ?>" 
                                       id="titular" name="titular" value="<?= old('titular', $cuentaData['titular'] ?? '') ?>" 
                                       placeholder="Nombre completo del titular" required>
                                <?php if (isset($errors['titular'])): ?>
                                    <div class="invalid-feedback"><?= $errors['titular'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- CONFIGURACIÓN Y ASOCIACIONES -->
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-cogs"></i> Configuración
                            </h5>

                            <div class="form-group">
                                <label for="tipo_cuenta">Tipo de Cuenta *</label>
                                <select class="form-control <?= isset($errors['tipo_cuenta']) ? 'is-invalid' : '' ?>" 
                                        id="tipo_cuenta" name="tipo_cuenta" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <?php foreach ($tipos_cuenta as $codigo => $nombre): ?>
                                        <option value="<?= $codigo ?>" <?= old('tipo_cuenta', $cuentaData['tipo_cuenta'] ?? '') === $codigo ? 'selected' : '' ?>>
                                            <?= $nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['tipo_cuenta'])): ?>
                                    <div class="invalid-feedback"><?= $errors['tipo_cuenta'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="moneda">Moneda *</label>
                                <select class="form-control <?= isset($errors['moneda']) ? 'is-invalid' : '' ?>" 
                                        id="moneda" name="moneda" required>
                                    <?php foreach ($monedas as $codigo => $nombre): ?>
                                        <option value="<?= $codigo ?>" <?= old('moneda', $cuentaData['moneda'] ?? '') === $codigo ? 'selected' : '' ?>>
                                            <?= $nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['moneda'])): ?>
                                    <div class="invalid-feedback"><?= $errors['moneda'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="saldo_actual_display">Saldo Actual</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="saldo_actual_display" 
                                           value="<?= number_format($cuentaData['saldo_actual'] ?? 0, 2) ?>" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text text-info">
                                            <i class="fas fa-info-circle" title="El saldo se actualiza automáticamente con las transacciones"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">El saldo se actualiza automáticamente con las transacciones</small>
                            </div>

                            <div class="form-group">
                                <label for="empresa_id">Empresa Propietaria *</label>
                                <select class="form-control <?= isset($errors['empresa_id']) ? 'is-invalid' : '' ?>" 
                                        id="empresa_id" name="empresa_id" required>
                                    <option value="">Seleccionar empresa...</option>
                                    <?php foreach ($empresas as $id => $nombre): ?>
                                        <option value="<?= $id ?>" <?= old('empresa_id', $cuentaData['empresa_id'] ?? '') == $id ? 'selected' : '' ?>>
                                            <?= $nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['empresa_id'])): ?>
                                    <div class="invalid-feedback"><?= $errors['empresa_id'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="proyecto_id">Proyecto Asociado</label>
                                <select class="form-control <?= isset($errors['proyecto_id']) ? 'is-invalid' : '' ?>" 
                                        id="proyecto_id" name="proyecto_id">
                                    <?php foreach ($proyectos as $id => $nombre): ?>
                                        <option value="<?= $id ?>" <?= old('proyecto_id', $cuentaData['proyecto_id'] ?? '') == $id ? 'selected' : '' ?>>
                                            <?= $nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Opcional: Asociar a un proyecto específico</small>
                                <?php if (isset($errors['proyecto_id'])): ?>
                                    <div class="invalid-feedback"><?= $errors['proyecto_id'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="color_identificacion">Color de Identificación</label>
                                <input type="color" class="form-control <?= isset($errors['color_identificacion']) ? 'is-invalid' : '' ?>" 
                                       id="color_identificacion" name="color_identificacion" value="<?= old('color_identificacion', $cuentaData['color_identificacion'] ?? '#007bff') ?>">
                                <small class="form-text text-muted">Color para identificar visualmente la cuenta</small>
                                <?php if (isset($errors['color_identificacion'])): ?>
                                    <div class="invalid-feedback"><?= $errors['color_identificacion'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- PERMISOS Y CONFIGURACIÓN ADICIONAL -->
                    <hr>
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-shield-alt"></i> Permisos y Configuración Adicional
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="permite_depositos" 
                                           name="permite_depositos" value="1" <?= old('permite_depositos', $cuentaData['permite_depositos'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="permite_depositos">
                                        Permite Depósitos/Ingresos
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="permite_retiros" 
                                           name="permite_retiros" value="1" <?= old('permite_retiros', $cuentaData['permite_retiros'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="permite_retiros">
                                        Permite Retiros/Egresos
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="convenio">Número de Convenio</label>
                                <input type="text" class="form-control <?= isset($errors['convenio']) ? 'is-invalid' : '' ?>" 
                                       id="convenio" name="convenio" value="<?= old('convenio', $cuentaData['convenio'] ?? '') ?>" 
                                       placeholder="Convenio bancario" maxlength="50">
                                <?php if (isset($errors['convenio'])): ?>
                                    <div class="invalid-feedback"><?= $errors['convenio'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_apertura">Fecha de Apertura</label>
                                <input type="date" class="form-control <?= isset($errors['fecha_apertura']) ? 'is-invalid' : '' ?>" 
                                       id="fecha_apertura" name="fecha_apertura" value="<?= old('fecha_apertura', $cuentaData['fecha_apertura'] ?? '') ?>">
                                <?php if (isset($errors['fecha_apertura'])): ?>
                                    <div class="invalid-feedback"><?= $errors['fecha_apertura'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="notas">Notas y Observaciones</label>
                                <textarea class="form-control <?= isset($errors['notas']) ? 'is-invalid' : '' ?>" 
                                          id="notas" name="notas" rows="3" 
                                          placeholder="Observaciones adicionales sobre la cuenta"><?= old('notas', $cuentaData['notas'] ?? '') ?></textarea>
                                <?php if (isset($errors['notas'])): ?>
                                    <div class="invalid-feedback"><?= $errors['notas'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= base_url('admin/cuentas-bancarias') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Validación del formulario (misma que en create.php)
    $('#form-cuenta').validate({
        rules: {
            descripcion: {
                required: true,
                maxlength: 255
            },
            banco: {
                required: true
            },
            numero_cuenta: {
                required: true,
                maxlength: 20
            },
            clabe: {
                minlength: 18,
                maxlength: 18,
                digits: true
            },
            swift: {
                minlength: 8,
                maxlength: 11
            },
            titular: {
                required: true,
                maxlength: 255
            },
            tipo_cuenta: {
                required: true
            },
            moneda: {
                required: true
            },
            empresa_id: {
                required: true
            }
        },
        messages: {
            descripcion: {
                required: "La descripción es obligatoria",
                maxlength: "Máximo 255 caracteres"
            },
            banco: {
                required: "Seleccione un banco"
            },
            numero_cuenta: {
                required: "El número de cuenta es obligatorio",
                maxlength: "Máximo 20 caracteres"
            },
            clabe: {
                minlength: "La CLABE debe tener 18 dígitos",
                maxlength: "La CLABE debe tener 18 dígitos",
                digits: "Solo se permiten números"
            },
            titular: {
                required: "El titular es obligatorio",
                maxlength: "Máximo 255 caracteres"
            },
            tipo_cuenta: {
                required: "Seleccione el tipo de cuenta"
            },
            moneda: {
                required: "Seleccione la moneda"
            },
            empresa_id: {
                required: "Seleccione la empresa propietaria"
            }
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        }
    });

    // Formatear campos
    $('#numero_cuenta').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $('#clabe').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

// Mostrar errores si existen
<?php if (session('error')): ?>
    toastr.error('<?= session('error') ?>');
<?php endif; ?>

<?php if (isset($errors) && !empty($errors)): ?>
    toastr.error('Por favor corrige los errores en el formulario');
<?php endif; ?>
</script>
<?= $this->endSection() ?>