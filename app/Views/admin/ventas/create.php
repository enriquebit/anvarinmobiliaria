<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Nueva Venta<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Nueva Venta<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas') ?>">Ventas</a></li>
<li class="breadcrumb-item active">Nueva Venta</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus mr-2"></i>
                    Registrar Nueva Venta
                </h3>
            </div>
            <form action="<?= site_url('/admin/ventas/store') ?>" method="post" id="ventaForm">
                <?= csrf_field() ?>
                <div class="card-body">
                    
                    <!-- Información básica -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="folio_venta">Folio de Venta <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="folio_venta" name="folio_venta" 
                                           value="<?= old('folio_venta') ?>" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="generateFolio">
                                            <i class="fas fa-sync"></i> Generar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_venta">Fecha de Venta <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_venta" name="fecha_venta" 
                                       value="<?= old('fecha_venta', date('Y-m-d')) ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Cliente y Lote -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="cliente_id" name="cliente_id" required>
                                    <option value="">Seleccionar cliente...</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente->id ?>" <?= old('cliente_id') == $cliente->id ? 'selected' : '' ?>>
                                            <?= esc($cliente->nombres) ?> <?= esc($cliente->apellido_paterno) ?> <?= esc($cliente->apellido_materno) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lote_id">Lote <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="lote_id" name="lote_id" required>
                                    <option value="">Seleccionar lote...</option>
                                    <?php foreach ($lotes as $lote): ?>
                                        <option value="<?= $lote->id ?>" 
                                                data-precio="<?= $lote->precio_total ?>"
                                                data-superficie="<?= $lote->area ?>"
                                                <?= old('lote_id') == $lote->id ? 'selected' : '' ?>>
                                            <?= esc($lote->clave) ?> - $<?= number_format($lote->precio_total, 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Vendedor y Plan -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendedor_id">Vendedor <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="vendedor_id" name="vendedor_id" required>
                                    <option value="">Seleccionar vendedor...</option>
                                    <?php foreach ($vendedores as $vendedor): ?>
                                        <option value="<?= $vendedor->id ?>" <?= old('vendedor_id') == $vendedor->id ? 'selected' : '' ?>>
                                            <?= esc($vendedor->username) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="perfil_financiamiento_id">Plan de Financiamiento <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="perfil_financiamiento_id" name="perfil_financiamiento_id" required>
                                    <option value="">Seleccionar plan...</option>
                                    <?php foreach ($planes as $plan): ?>
                                        <option value="<?= $plan->id ?>" <?= old('perfil_financiamiento_id') == $plan->id ? 'selected' : '' ?>>
                                            <?= esc($plan->nombre_plan) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Apartado (opcional) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apartado_id">Apartado (opcional)</label>
                                <select class="form-control select2" id="apartado_id" name="apartado_id">
                                    <option value="">Sin apartado...</option>
                                    <?php foreach ($apartados as $apartado): ?>
                                        <option value="<?= $apartado->id ?>" 
                                                data-monto="<?= $apartado->monto_apartado ?>"
                                                <?= old('apartado_id') == $apartado->id ? 'selected' : '' ?>>
                                            <?= esc($apartado->folio_apartado) ?> - $<?= number_format($apartado->monto_apartado, 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Si seleccionas un apartado, se convertirá automáticamente en venta.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_venta">Tipo de Venta <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipo_venta" name="tipo_venta" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="contado" <?= old('tipo_venta') == 'contado' ? 'selected' : '' ?>>Contado</option>
                                    <option value="financiado" <?= old('tipo_venta') == 'financiado' ? 'selected' : '' ?>>Financiado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="precio_lista">Precio de Lista <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="precio_lista" name="precio_lista" 
                                           step="0.01" min="0" value="<?= old('precio_lista') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="descuento_aplicado">Descuento</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="descuento_aplicado" name="descuento_aplicado" 
                                           step="0.01" min="0" value="<?= old('descuento_aplicado', 0) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="precio_venta_final">Precio Final <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="precio_venta_final" name="precio_venta_final" 
                                           step="0.01" min="0" value="<?= old('precio_venta_final') ?>" required readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Motivo del descuento -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="motivo_descuento">Motivo del Descuento</label>
                                <input type="text" class="form-control" id="motivo_descuento" name="motivo_descuento" 
                                       value="<?= old('motivo_descuento') ?>" placeholder="Especificar motivo del descuento...">
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Observaciones adicionales..."><?= old('observaciones') ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Guardar Venta
                    </button>
                    <a href="<?= site_url('/admin/ventas') ?>" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Generar folio automáticamente al cargar
    generateFolio();

    // Botón para generar folio
    $('#generateFolio').click(function() {
        generateFolio();
    });

    // Calcular precio final cuando cambie el precio lista o descuento
    $('#precio_lista, #descuento_aplicado').on('input', function() {
        calcularPrecioFinal();
    });

    // Cuando se selecciona un lote, cargar su precio
    $('#lote_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const precio = selectedOption.data('precio');
        
        if (precio) {
            $('#precio_lista').val(precio);
            calcularPrecioFinal();
        }
    });

    function generateFolio() {
        $.get('<?= site_url('/admin/ventas/generar-folio') ?>', function(response) {
            if (response.folio) {
                $('#folio_venta').val(response.folio);
            }
        });
    }

    function calcularPrecioFinal() {
        const precioLista = parseFloat($('#precio_lista').val()) || 0;
        const descuento = parseFloat($('#descuento_aplicado').val()) || 0;
        const precioFinal = precioLista - descuento;
        
        $('#precio_venta_final').val(precioFinal.toFixed(2));
    }

    // Validación del formulario
    $('#ventaForm').submit(function(e) {
        const precioFinal = parseFloat($('#precio_venta_final').val());
        
        if (precioFinal <= 0) {
            e.preventDefault();
            alert('El precio final debe ser mayor a cero.');
            return false;
        }
    });
});
</script>
<?= $this->endSection() ?>