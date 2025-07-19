<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Editar Venta <?= esc($venta->folio_venta) ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>Editar Venta <?= esc($venta->folio_venta) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas') ?>">Ventas</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas/' . $venta->id) ?>">Venta <?= esc($venta->folio_venta) ?></a></li>
<li class="breadcrumb-item active">Editar</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Venta <?= esc($venta->folio_venta) ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/ventas/' . $venta->id) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al Detalle
                    </a>
                </div>
            </div>
            <form action="<?= site_url('/admin/ventas/update/' . $venta->id) ?>" method="post" id="ventaEditForm">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">
                
                <div class="card-body">
                    
                    <!-- Alerta de información -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Importante:</strong> Algunos campos pueden afectar los cálculos financieros de esta venta. 
                        Revise cuidadosamente antes de guardar los cambios.
                    </div>
                    
                    <!-- Información básica de solo lectura -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Folio de Venta</label>
                                <input type="text" class="form-control" value="<?= esc($venta->folio_venta) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Venta</label>
                                <input type="date" class="form-control" name="fecha_venta" 
                                       value="<?= date('Y-m-d', strtotime($venta->fecha_venta)) ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Cliente y Lote (solo lectura por seguridad) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cliente</label>
                                <input type="text" class="form-control" 
                                       value="<?= isset($cliente) ? esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) : 'Cliente no encontrado' ?>" 
                                       readonly>
                                <small class="text-muted">No se puede cambiar el cliente en ventas existentes</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lote</label>
                                <input type="text" class="form-control" 
                                       value="<?= isset($lote) ? esc($lote->clave) : 'Lote no encontrado' ?>" 
                                       readonly>
                                <small class="text-muted">No se puede cambiar el lote en ventas existentes</small>
                            </div>
                        </div>
                    </div>

                    <!-- Vendedor -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendedor_id">Vendedor <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="vendedor_id" name="vendedor_id" required>
                                    <option value="">Seleccionar vendedor...</option>
                                    <?php if (!empty($vendedores)): ?>
                                        <?php foreach ($vendedores as $vendedor): ?>
                                            <option value="<?= $vendedor->id ?>" 
                                                    <?= $venta->vendedor_id == $vendedor->id ? 'selected' : '' ?>>
                                                <?= esc($vendedor->username) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_venta">Tipo de Venta</label>
                                <input type="text" class="form-control" 
                                       value="<?= ucfirst($venta->tipo_venta) ?>" readonly>
                                <small class="text-muted">No se puede cambiar el tipo de venta</small>
                            </div>
                        </div>
                    </div>

                    <!-- Precios y descuentos -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="precio_lista">Precio de Lista <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="precio_lista" name="precio_lista" 
                                           value="<?= old('precio_lista', $venta->precio_lista) ?>" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="descuento_aplicado">Descuento Aplicado</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="descuento_aplicado" name="descuento_aplicado" 
                                           value="<?= old('descuento_aplicado', $venta->descuento_aplicado) ?>" 
                                           step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Precio Final</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="precio_final_display" readonly>
                                </div>
                                <input type="hidden" id="precio_venta_final" name="precio_venta_final" 
                                       value="<?= old('precio_venta_final', $venta->precio_venta_final) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Motivo del descuento -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="motivo_descuento">Motivo del Descuento</label>
                                <textarea class="form-control" id="motivo_descuento" name="motivo_descuento" 
                                          rows="2" placeholder="Especifique el motivo del descuento..."><?= old('motivo_descuento', $venta->motivo_descuento) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Plan de financiamiento (solo para ventas financiadas) -->
                    <?php if ($venta->tipo_venta === 'financiado'): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="perfil_financiamiento_id">Plan de Financiamiento <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="perfil_financiamiento_id" name="perfil_financiamiento_id" required>
                                    <option value="">Seleccionar plan...</option>
                                    <?php if (!empty($planes_financiamiento)): ?>
                                        <?php foreach ($planes_financiamiento as $plan): ?>
                                            <option value="<?= $plan->id ?>" 
                                                    <?= $venta->perfil_financiamiento_id == $plan->id ? 'selected' : '' ?>>
                                                <?= esc($plan->nombre) ?> - <?= $plan->meses_con_intereses ?> meses
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estatus_venta">Estado de la Venta</label>
                                <select class="form-control" id="estatus_venta" name="estatus_venta">
                                    <option value="activa" <?= $venta->estatus_venta === 'activa' ? 'selected' : '' ?>>Activa</option>
                                    <option value="liquidada" <?= $venta->estatus_venta === 'liquidada' ? 'selected' : '' ?>>Liquidada</option>
                                    <option value="juridico" <?= $venta->estatus_venta === 'juridico' ? 'selected' : '' ?>>En Jurídico</option>
                                    <option value="cancelada" <?= $venta->estatus_venta === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Observaciones -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" 
                                          rows="3" placeholder="Observaciones adicionales..."><?= old('observaciones', $venta->observaciones) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Información de contrato -->
                    <?php if ($venta->contrato_generado): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="numero_contrato">Número de Contrato</label>
                                <input type="text" class="form-control" id="numero_contrato" name="numero_contrato" 
                                       value="<?= old('numero_contrato', $venta->numero_contrato) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_contrato">Fecha del Contrato</label>
                                <input type="date" class="form-control" id="fecha_contrato" name="fecha_contrato" 
                                       value="<?= old('fecha_contrato', $venta->fecha_contrato ? date('Y-m-d', strtotime($venta->fecha_contrato)) : '') ?>">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                            <a href="<?= site_url('/admin/ventas/' . $venta->id) ?>" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Los cambios en precios pueden afectar la tabla de amortización
                            </small>
                        </div>
                    </div>
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
        theme: 'bootstrap4'
    });
    
    // Calcular precio final automáticamente
    function calcularPrecioFinal() {
        var precioLista = parseFloat($('#precio_lista').val()) || 0;
        var descuento = parseFloat($('#descuento_aplicado').val()) || 0;
        var precioFinal = precioLista - descuento;
        
        $('#precio_final_display').val(precioFinal.toFixed(2));
        $('#precio_venta_final').val(precioFinal.toFixed(2));
    }
    
    // Calcular al cargar la página
    calcularPrecioFinal();
    
    // Recalcular cuando cambien los valores
    $('#precio_lista, #descuento_aplicado').on('input', calcularPrecioFinal);
    
    // Validación del formulario
    $('#ventaEditForm').on('submit', function(e) {
        var precioLista = parseFloat($('#precio_lista').val()) || 0;
        var descuento = parseFloat($('#descuento_aplicado').val()) || 0;
        
        if (descuento > precioLista) {
            e.preventDefault();
            alert('El descuento no puede ser mayor al precio de lista');
            return false;
        }
        
        if (precioLista <= 0) {
            e.preventDefault();
            alert('El precio de lista debe ser mayor a cero');
            return false;
        }
        
        // Confirmar cambios
        if (!confirm('¿Está seguro de guardar los cambios en esta venta?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Mostrar/ocultar campos según el tipo de venta
    function toggleFieldsByType() {
        var tipoVenta = '<?= $venta->tipo_venta ?>';
        
        if (tipoVenta === 'contado') {
            $('#perfil_financiamiento_id').closest('.form-group').hide();
        }
    }
    
    toggleFieldsByType();
});
</script>
<?= $this->endSection() ?>