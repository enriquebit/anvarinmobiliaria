<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Nuevo Apartado<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Nuevo Apartado<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .swal-wide {
        width: 600px !important;
    }
    
    .alert-info .row .col-md-3,
    .alert-warning .row .col-md-4 {
        margin-bottom: 10px;
    }
    
    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: .25rem;
        background: #fff;
        display: flex;
        margin-bottom: 1rem;
        min-height: 80px;
        padding: .5rem;
        position: relative;
        width: 100%;
    }
    
    .info-box .info-box-icon {
        border-radius: .25rem;
        align-items: center;
        display: flex;
        font-size: 1.875rem;
        justify-content: center;
        text-align: center;
        width: 70px;
    }
    
    .info-box .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.8;
        margin-left: .5rem;
        padding: 0 .5rem;
    }
    
    .info-box .info-box-number {
        display: block;
        font-weight: 700;
    }
    
    .info-box .info-box-text {
        display: block;
        font-size: .875rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/apartados') ?>">Apartados</a></li>
<li class="breadcrumb-item active">Nuevo</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Apartado
                </h3>
            </div>
            <form action="<?= site_url('/admin/apartados/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    
                    <!-- Información del apartado -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_apartado">Fecha del Apartado <span class="text-danger">*</span></label>
                                <input type="date" class="form-control <?= session('errors.fecha_apartado') ? 'is-invalid' : '' ?>" 
                                       id="fecha_apartado" name="fecha_apartado" value="<?= old('fecha_apartado', date('Y-m-d')) ?>" 
                                       required>
                                <?php if (session('errors.fecha_apartado')): ?>
                                    <div class="invalid-feedback"><?= session('errors.fecha_apartado') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_limite_enganche">Fecha Límite Enganche <span class="text-danger">*</span></label>
                                <input type="date" class="form-control <?= session('errors.fecha_limite_enganche') ? 'is-invalid' : '' ?>" 
                                       id="fecha_limite_enganche" name="fecha_limite_enganche" value="<?= old('fecha_limite_enganche') ?>" 
                                       required>
                                <?php if (session('errors.fecha_limite_enganche')): ?>
                                    <div class="invalid-feedback"><?= session('errors.fecha_limite_enganche') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cliente y Lote -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
                                <select class="form-control select2 <?= session('errors.cliente_id') ? 'is-invalid' : '' ?>" 
                                        id="cliente_id" name="cliente_id" required>
                                    <option value="">Seleccionar cliente...</option>
                                    <?php if (!empty($clientes)): ?>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente->id ?>" <?= old('cliente_id') == $cliente->id ? 'selected' : '' ?>>
                                                <?= esc($cliente->nombres) ?> <?= esc($cliente->apellido_paterno) ?> <?= esc($cliente->apellido_materno) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (session('errors.cliente_id')): ?>
                                    <div class="invalid-feedback"><?= session('errors.cliente_id') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lote_id">Lote <span class="text-danger">*</span></label>
                                <select class="form-control select2 <?= session('errors.lote_id') ? 'is-invalid' : '' ?>" 
                                        id="lote_id" name="lote_id" required>
                                    <option value="">Seleccionar lote...</option>
                                    <?php if (!empty($lotes)): ?>
                                        <?php foreach ($lotes as $lote): ?>
                                            <option value="<?= $lote->id ?>" 
                                                    data-precio="<?= $lote->precio_total ?>"
                                                    <?= old('lote_id') == $lote->id ? 'selected' : '' ?>>
                                                <?= esc($lote->clave) ?> - $<?= number_format($lote->precio_total, 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (session('errors.lote_id')): ?>
                                    <div class="invalid-feedback"><?= session('errors.lote_id') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Usuario y Plan -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendedor_id">Vendedor <span class="text-danger">*</span></label>
                                <select class="form-control select2 <?= session('errors.vendedor_id') ? 'is-invalid' : '' ?>" 
                                        id="vendedor_id" name="vendedor_id" required>
                                    <option value="">Seleccionar vendedor...</option>
                                    <?php if (!empty($vendedores)): ?>
                                        <?php foreach ($vendedores as $vendedor): ?>
                                            <option value="<?= $vendedor->id ?>" <?= old('vendedor_id') == $vendedor->id ? 'selected' : '' ?>>
                                                <?= esc($vendedor->nombres) ?> <?= esc($vendedor->apellido_paterno) ?> <?= esc($vendedor->apellido_materno) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (session('errors.vendedor_id')): ?>
                                    <div class="invalid-feedback"><?= session('errors.vendedor_id') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="configuracion_financiera_id">Plan de Financiamiento <span class="text-danger">*</span></label>
                                <select class="form-control select2 <?= session('errors.configuracion_financiera_id') ? 'is-invalid' : '' ?>" 
                                        id="configuracion_financiera_id" name="configuracion_financiera_id" required>
                                    <option value="">Seleccionar plan...</option>
                                    <?php if (!empty($planes)): ?>
                                        <?php foreach ($planes as $plan): ?>
                                            <?php 
                                                // Determinar tipo de financiamiento y crear descripción detallada
                                                $tipoFinanciamiento = '';
                                                $meses = 0;
                                                $tasa = '';
                                                
                                                if ($plan->meses_sin_intereses > 0) {
                                                    $tipoFinanciamiento = 'MSI';
                                                    $meses = $plan->meses_sin_intereses;
                                                    $tasa = '0% Interés';
                                                } elseif ($plan->meses_con_intereses > 0) {
                                                    $tipoFinanciamiento = 'MCI';
                                                    $meses = $plan->meses_con_intereses;
                                                    $tasa = ($plan->porcentaje_interes_anual > 0) ? $plan->porcentaje_interes_anual . '% Anual' : '0% Interés';
                                                }
                                                
                                                // Verificar si es plan cero enganche
                                                $ceroEnganche = ($plan->tipo_anticipo === 'fijo' && $plan->anticipo_fijo == 0) || 
                                                               ($plan->tipo_anticipo === 'porcentaje' && $plan->porcentaje_anticipo == 0) ||
                                                               $plan->promocion_cero_enganche == 1;
                                                
                                                $labelEnganche = $ceroEnganche ? ' • CERO ENGANCHE' : '';
                                                
                                                // Crear label descriptivo
                                                $planLabel = esc($plan->nombre) . ' - ' . esc($plan->empresa_nombre ?? 'Global');
                                                if ($meses > 0) {
                                                    $planLabel .= " • {$meses} {$tipoFinanciamiento} • {$tasa}";
                                                }
                                                $planLabel .= $labelEnganche;
                                            ?>
                                            <option value="<?= $plan->id ?>" 
                                                    data-apartado-minimo="<?= $plan->apartado_minimo ?>"
                                                    data-enganche-minimo="<?= $plan->enganche_minimo ?>"
                                                    data-plazo-liquidar-enganche="<?= $plan->plazo_liquidar_enganche ?>"
                                                    data-tipo-anticipo="<?= $plan->tipo_anticipo ?>"
                                                    data-porcentaje-anticipo="<?= $plan->porcentaje_anticipo ?>"
                                                    data-anticipo-fijo="<?= $plan->anticipo_fijo ?>"
                                                    data-promocion-cero-enganche="<?= $plan->promocion_cero_enganche ?>"
                                                    data-meses-sin-intereses="<?= $plan->meses_sin_intereses ?>"
                                                    data-meses-con-intereses="<?= $plan->meses_con_intereses ?>"
                                                    data-porcentaje-interes-anual="<?= $plan->porcentaje_interes_anual ?>"
                                                    data-cero-enganche="<?= $ceroEnganche ? 'true' : 'false' ?>"
                                                    data-accion-anticipo-incompleto="<?= $plan->accion_anticipo_incompleto ?>"
                                                    data-penalizacion-apartado="<?= $plan->penalizacion_apartado ?>"
                                                    data-penalizacion-enganche-tardio="<?= $plan->penalizacion_enganche_tardio ?>"
                                                    <?= old('configuracion_financiera_id') == $plan->id ? 'selected' : '' ?>>
                                                <?= $planLabel ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (session('errors.configuracion_financiera_id')): ?>
                                    <div class="invalid-feedback"><?= session('errors.configuracion_financiera_id') ?></div>
                                <?php endif; ?>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    MSI = Meses Sin Intereses | MCI = Meses Con Intereses
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Lote Seleccionado -->
                    <div class="row" id="info-lote-seleccionado" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-home mr-2"></i>Información de la Propiedad Seleccionada:</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small><strong>Precio Total:</strong></small><br>
                                        <span id="info-precio-total" class="h6 text-primary">-</span>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Área:</strong></small><br>
                                        <span id="info-area" class="h6">-</span>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Tipo:</strong></small><br>
                                        <span id="info-tipo" class="h6">-</span>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Ubicación:</strong></small><br>
                                        <span id="info-ubicacion" class="h6">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Montos Dinámicos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto_apartado">
                                    Monto del Apartado <span class="text-danger">*</span>
                                    <small id="label-apartado-info" class="text-muted"></small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control <?= session('errors.monto_apartado') ? 'is-invalid' : '' ?>" 
                                           id="monto_apartado" name="monto_apartado" value="<?= old('monto_apartado') ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-text text-muted" id="help-monto-apartado">
                                    Seleccione un plan de financiamiento para ver el monto mínimo requerido
                                </small>
                                <?php if (session('errors.monto_apartado')): ?>
                                    <div class="invalid-feedback"><?= session('errors.monto_apartado') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto_enganche_requerido" id="label-enganche">
                                    Monto Enganche Requerido <span class="text-danger">*</span>
                                    <small id="label-enganche-info" class="text-muted"></small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control <?= session('errors.monto_enganche_requerido') ? 'is-invalid' : '' ?>" 
                                           id="monto_enganche_requerido" name="monto_enganche_requerido" value="<?= old('monto_enganche_requerido') ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <small class="form-text text-muted" id="help-monto-enganche">
                                    El cliente deberá liquidar este monto para aperturar el plan de pagos
                                </small>
                                <?php if (session('errors.monto_enganche_requerido')): ?>
                                    <div class="invalid-feedback"><?= session('errors.monto_enganche_requerido') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Plazos -->
                    <div class="row" id="info-plazos" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h6 class="mb-2"><i class="fas fa-clock mr-2"></i>Información de Plazos:</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small><strong>Días para liquidar enganche:</strong></small><br>
                                        <span id="info-plazo-enganche" class="h6 text-warning">-</span> días
                                    </div>
                                    <div class="col-md-4">
                                        <small><strong>Fecha límite estimada:</strong></small><br>
                                        <span id="info-fecha-limite" class="h6">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small><strong>Consecuencia por incumplimiento:</strong></small><br>
                                        <span id="info-consecuencia" class="h6 text-danger">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Forma de pago -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="forma_pago">Forma de Pago <span class="text-danger">*</span></label>
                                <select class="form-control <?= session('errors.forma_pago') ? 'is-invalid' : '' ?>" 
                                        id="forma_pago" name="forma_pago" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo" <?= old('forma_pago') === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                    <option value="transferencia" <?= old('forma_pago') === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                    <option value="cheque" <?= old('forma_pago') === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                                    <option value="tarjeta" <?= old('forma_pago') === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                    <option value="deposito" <?= old('forma_pago') === 'deposito' ? 'selected' : '' ?>>Depósito</option>
                                </select>
                                <?php if (session('errors.forma_pago')): ?>
                                    <div class="invalid-feedback"><?= session('errors.forma_pago') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="referencia_pago">Referencia de Pago</label>
                                <input type="text" class="form-control" id="referencia_pago" name="referencia_pago" 
                                       value="<?= old('referencia_pago') ?>" maxlength="100">
                                <small class="form-text text-muted">Número de cheque, referencia de transferencia, etc.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="comprobante_url">URL del Comprobante</label>
                                <input type="url" class="form-control" id="comprobante_url" name="comprobante_url" 
                                       value="<?= old('comprobante_url') ?>">
                                <small class="form-text text-muted">Link al archivo de comprobante</small>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de Simulación -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-info" id="btnSimularAmortizacion" disabled>
                                <i class="fas fa-calculator mr-1"></i>
                                Ver Simulación de Tabla de Amortización
                            </button>
                            <small class="form-text text-muted">
                                Seleccione lote y plan de financiamiento para ver cómo quedarían los pagos mensuales después de liquidar el enganche
                            </small>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= old('observaciones') ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Guardar Apartado
                    </button>
                    <a href="<?= site_url('/admin/apartados') ?>" class="btn btn-secondary">
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

    // Verificar si viene desde ventas y cargar datos
    <?php if ($from_venta): ?>
    const datosApartado = sessionStorage.getItem('datosApartado');
    if (datosApartado) {
        const datos = JSON.parse(datosApartado);
        
        // Precargar los campos
        if (datos.cliente_id) {
            $('#cliente_id').val(datos.cliente_id).trigger('change');
        }
        if (datos.lote_id) {
            $('#lote_id').val(datos.lote_id).trigger('change');
        }
        if (datos.configuracion_financiera_id) {
            $('#configuracion_financiera_id').val(datos.configuracion_financiera_id).trigger('change');
        }
        if (datos.monto_apartado) {
            $('#monto_apartado').val(datos.monto_apartado);
        }
        
        // Limpiar sessionStorage
        sessionStorage.removeItem('datosApartado');
        
        // Después de cargar todos los datos, activar la configuración
        setTimeout(function() {
            $('#configuracion_financiera_id').trigger('change');
        }, 100);
    }
    <?php endif; ?>
    
    // Auto-cargar valores cuando la página ya tiene una configuración seleccionada
    setTimeout(function() {
        if ($('#configuracion_financiera_id').val()) {
            $('#configuracion_financiera_id').trigger('change');
        }
    }, 300);

    // Cuando cambia la configuración financiera
    $('#configuracion_financiera_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        
        // Obtener datos de la configuración
        const apartadoMinimo = parseFloat(selectedOption.data('apartado-minimo')) || 0;
        const engancheMinimo = parseFloat(selectedOption.data('enganche-minimo')) || 0;
        const plazoEnganche = parseInt(selectedOption.data('plazo-liquidar-enganche')) || 30;
        const tipoAnticipo = selectedOption.data('tipo-anticipo');
        const porcentajeAnticipo = parseFloat(selectedOption.data('porcentaje-anticipo')) || 0;
        const anticipoFijo = parseFloat(selectedOption.data('anticipo-fijo')) || 0;
        const ceroEnganche = selectedOption.data('cero-enganche') === 'true';
        const promocionCeroEnganche = selectedOption.data('promocion-cero-enganche') == 1;
        
        // Obtener datos del lote seleccionado
        const loteOption = $('#lote_id').find('option:selected');
        const precioLote = parseFloat(loteOption.data('precio')) || 0;
        
            apartadoMinimo: apartadoMinimo,
            engancheMinimo: engancheMinimo,
            plazoEnganche: plazoEnganche,
            ceroEnganche: ceroEnganche,
            tipoAnticipo: tipoAnticipo,
            precioLote: precioLote
        });
        
        // Actualizar monto de apartado mínimo
        $('#monto_apartado').attr('min', apartadoMinimo);
        if (apartadoMinimo > 0) {
            $('#monto_apartado').val(apartadoMinimo.toFixed(2));
            $('#label-apartado-info').text(`(Mínimo: $${apartadoMinimo.toLocaleString()})`);
            $('#help-monto-apartado').text(`Monto mínimo requerido: $${apartadoMinimo.toLocaleString()}`);
        } else {
            $('#label-apartado-info').text('');
            $('#help-monto-apartado').text('Ingrese el monto del apartado');
        }
        
        // Manejar monto de enganche según el tipo de plan
        if (ceroEnganche || promocionCeroEnganche) {
            // Plan cero enganche - NO APLICA APARTADO, redirigir a ventas
            mostrarRedirectCeroEnganche(selectedOption);
            return;
        } else {
            // Plan con enganche
            $('#monto_enganche_requerido').prop('readonly', false);
            $('#label-enganche').html('Monto Enganche Requerido <span class="text-danger">*</span>');
            
            let montoEngancheCalculado = 0;
            
            if (precioLote > 0) {
                if (tipoAnticipo === 'porcentaje') {
                    montoEngancheCalculado = precioLote * (porcentajeAnticipo / 100);
                } else if (tipoAnticipo === 'fijo') {
                    montoEngancheCalculado = anticipoFijo;
                }
            }
            
            // Validar enganche mínimo
            if (engancheMinimo > 0 && montoEngancheCalculado < engancheMinimo) {
                montoEngancheCalculado = engancheMinimo;
            }
            
            if (montoEngancheCalculado > 0) {
                $('#monto_enganche_requerido').val(montoEngancheCalculado.toFixed(2));
                $('#monto_enganche_requerido').attr('min', Math.max(engancheMinimo, 0));
                $('#label-enganche-info').text(`(Calculado: $${montoEngancheCalculado.toLocaleString()})`);
                $('#help-monto-enganche').text(`Monto requerido para aperturar el plan de pagos`);
            } else {
                $('#label-enganche-info').text(engancheMinimo > 0 ? `(Mínimo: $${engancheMinimo.toLocaleString()})` : '');
            }
        }
        
        // Calcular fecha límite de enganche
        const fechaApartado = new Date($('#fecha_apartado').val() || new Date());
        fechaApartado.setDate(fechaApartado.getDate() + plazoEnganche);
        $('#fecha_limite_enganche').val(fechaApartado.toISOString().split('T')[0]);
        
        // Mostrar información de plazos
        actualizarInfoPlazos(selectedOption, fechaApartado);
        
            montoApartado: $('#monto_apartado').val(),
            engancheRequerido: $('#monto_enganche_requerido').val(),
            fechaLimite: $('#fecha_limite_enganche').val(),
            ceroEnganche: ceroEnganche
        });
    });

    // Calcular fecha límite basada en fecha de apartado y configuración
    $('#fecha_apartado').change(function() {
        const fechaApartado = new Date(this.value);
        if (fechaApartado) {
            const selectedConfig = $('#configuracion_financiera_id').find('option:selected');
            const plazoEnganche = parseInt(selectedConfig.data('plazo-liquidar-enganche')) || 30;
            
            fechaApartado.setDate(fechaApartado.getDate() + plazoEnganche);
            const fechaLimite = fechaApartado.toISOString().split('T')[0];
            $('#fecha_limite_enganche').val(fechaLimite);
        }
    });

    // Calcular montos basados en el lote seleccionado
    $('#lote_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const precio = parseFloat(selectedOption.data('precio')) || 0;
        
        if (precio > 0) {
            // Mostrar información del lote
            mostrarInfoLote(selectedOption, precio);
            
            // Recalcular configuración si ya hay una seleccionada
            if ($('#configuracion_financiera_id').val()) {
                $('#configuracion_financiera_id').trigger('change');
            }
        } else {
            // Ocultar información si no hay lote seleccionado
            $('#info-lote-seleccionado').hide();
        }
    });

    // Función para mostrar información detallada del lote
    function mostrarInfoLote(loteOption, precio) {
        // Extraer información del option text (formato: "CLAVE - $PRECIO")
        const loteText = loteOption.text();
        const loteId = loteOption.val();
        
        // Mostrar información básica (se puede expandir cuando tengamos más datos del lote)
        $('#info-precio-total').text('$' + precio.toLocaleString());
        $('#info-area').text('Por definir'); // TODO: Agregar área del lote
        $('#info-tipo').text('Por definir'); // TODO: Agregar tipo del lote
        $('#info-ubicacion').text(loteText.split(' - ')[0] || 'No especificado');
        
        $('#info-lote-seleccionado').show();
    }

    // Función para manejar redirection a ventas cuando es plan cero enganche
    function mostrarRedirectCeroEnganche(configOption) {
        const planNombre = configOption.text();
        const loteId = $('#lote_id').val();
        const clienteId = $('#cliente_id').val();
        const configuracionId = configOption.val();
        
        // Limpiar campos de apartado
        $('#monto_apartado').val('');
        $('#monto_enganche_requerido').val('');
        
        // Ocultar información de apartado
        $('#info-plazos').hide();
        
        // Mostrar modal de confirmación para redirección
        Swal.fire({
            title: '¡Plan de Cero Enganche Detectado!',
            html: `
                <div class="text-left">
                    <p><strong>Plan seleccionado:</strong> ${planNombre}</p>
                    <p class="text-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Los planes de <strong>CERO ENGANCHE</strong> no requieren apartado ya que 
                        no hay enganche que liquidar.
                    </p>
                    <p class="text-success">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Se recomienda proceder directamente con la <strong>VENTA</strong> 
                        utilizando este plan de financiamiento.
                    </p>
                    <hr>
                    <p><strong>¿Desea proceder con la venta directa?</strong></p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-shopping-cart mr-2"></i>Ir a Ventas',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Seleccionar Otro Plan',
            reverseButtons: true,
            customClass: {
                popup: 'swal-wide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Guardar datos en sessionStorage para el módulo de ventas
                const datosVenta = {
                    lote_id: loteId,
                    cliente_id: clienteId,
                    configuracion_financiera_id: configuracionId,
                    vendedor_id: $('#vendedor_id').val(),
                    desde_apartado: true,
                    plan_cero_enganche: true
                };
                
                sessionStorage.setItem('datosVenta', JSON.stringify(datosVenta));
                
                // Mostrar mensaje de transición
                Swal.fire({
                    title: 'Redirigiendo a Ventas...',
                    text: 'Preparando el formulario de venta con los datos seleccionados',
                    icon: 'info',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    willClose: () => {
                        // Redirigir al módulo de ventas
                        window.location.href = '<?= site_url('/admin/ventas/configurar') ?>/' + loteId + '?from_apartado=true';
                    }
                });
            } else {
                // Usuario canceló, limpiar selección de configuración
                $('#configuracion_financiera_id').val('').trigger('change');
                
                Swal.fire({
                    title: 'Seleccione otro plan',
                    text: 'Por favor seleccione un plan que requiera apartado',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }

    // Función para actualizar información de plazos
    function actualizarInfoPlazos(configOption, fechaLimite) {
        const plazoEnganche = parseInt(configOption.data('plazo-liquidar-enganche')) || 30;
        const accionIncompleto = configOption.data('accion-anticipo-incompleto');
        const penalizacionApartado = parseFloat(configOption.data('penalizacion-apartado')) || 0;
        
        // Determinar acción por incumplimiento
        let accionIncumplimiento = '';
        switch (accionIncompleto) {
            case 'liberar_lote':
                accionIncumplimiento = 'El lote se libera y el apartado se pierde';
                break;
            case 'mantener_apartado':
                accionIncumplimiento = 'Se mantiene el apartado activo';
                break;
            case 'aplicar_penalizacion':
                accionIncumplimiento = `Se aplica ${penalizacionApartado}% de penalización`;
                break;
            default:
                accionIncumplimiento = 'Revisar configuración del plan';
        }
        
        $('#info-plazo-enganche').text(plazoEnganche);
        $('#info-fecha-limite').text(fechaLimite.toLocaleDateString('es-MX'));
        $('#info-consecuencia').text(accionIncumplimiento);
        
        $('#info-plazos').show();
    }

    // Validación del monto de apartado
    $('#monto_apartado').on('input', function() {
        const montoMinimo = parseFloat($(this).attr('min')) || 0;
        const montoIngresado = parseFloat($(this).val()) || 0;
        
        if (montoIngresado < montoMinimo && montoMinimo > 0) {
            $(this).addClass('is-invalid');
            if (!$('#error-monto-apartado-custom').length) {
                $(this).after(`<div class="invalid-feedback" id="error-monto-apartado-custom">
                    El monto mínimo de apartado es $${montoMinimo.toLocaleString()}
                </div>`);
            }
        } else {
            $(this).removeClass('is-invalid');
            $('#error-monto-apartado-custom').remove();
        }
    });

    // Validación del monto de enganche (solo para planes que no son cero enganche)
    $('#monto_enganche_requerido').on('input', function() {
        const isReadonly = $(this).prop('readonly');
        if (isReadonly) return; // Skip validation for zero down payment plans
        
        const montoMinimo = parseFloat($(this).attr('min')) || 0;
        const montoIngresado = parseFloat($(this).val()) || 0;
        
        if (montoIngresado < montoMinimo && montoMinimo > 0) {
            $(this).addClass('is-invalid');
            if (!$('#error-monto-enganche-custom').length) {
                $(this).after(`<div class="invalid-feedback" id="error-monto-enganche-custom">
                    El monto mínimo de enganche es $${montoMinimo.toLocaleString()}
                </div>`);
            }
        } else {
            $(this).removeClass('is-invalid');
            $('#error-monto-enganche-custom').remove();
        }
    });

    // Validación antes de enviar el formulario
    $('form').on('submit', function(e) {
        const configOption = $('#configuracion_financiera_id').find('option:selected');
        const ceroEnganche = configOption.data('cero-enganche') === 'true';
        const promocionCeroEnganche = configOption.data('promocion-cero-enganche') == 1;
        
        // Si es plan cero enganche, no permitir envío del formulario - debe ir a ventas
        if (ceroEnganche || promocionCeroEnganche) {
            e.preventDefault();
            Swal.fire({
                title: '¡Error!',
                text: 'Los planes de cero enganche no permiten apartados. Debe proceder directamente con la venta.',
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
        
        // Validar que todos los campos requeridos estén llenos
        let hasErrors = false;
        
        $('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                hasErrors = true;
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            Swal.fire('Error', 'Por favor complete todos los campos requeridos', 'error');
            return false;
        }
    });

    // Habilitar botón de simulación cuando se seleccione lote y configuración
    function verificarSimulacion() {
        const loteId = $('#lote_id').val();
        const configuracionId = $('#configuracion_financiera_id').val();
        
        if (loteId && configuracionId) {
            $('#btnSimularAmortizacion').prop('disabled', false);
        } else {
            $('#btnSimularAmortizacion').prop('disabled', true);
        }
    }

    $('#lote_id, #configuracion_financiera_id').change(verificarSimulacion);

    // Simulación de Tabla de Amortización
    $('#btnSimularAmortizacion').click(function() {
        const loteId = $('#lote_id').val();
        const configuracionId = $('#configuracion_financiera_id').val();
        const fechaApartado = $('#fecha_apartado').val() || new Date().toISOString().split('T')[0];
        
        if (!loteId || !configuracionId) {
            Swal.fire('Error', 'Debe seleccionar un lote y un plan de financiamiento', 'error');
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Generando Simulación',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Llamar al endpoint de simulación
        $.ajax({
            url: '<?= site_url('/admin/apartados/simular-amortizacion') ?>',
            method: 'POST',
            data: {
                lote_id: loteId,
                configuracion_financiera_id: configuracionId,
                fecha_inicio: fechaApartado,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    mostrarTablaAmortizacion(response);
                } else {
                    Swal.fire('Error', response.message || 'Error al generar la simulación', 'error');
                }
            },
            error: function() {
                Swal.close();
                Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
            }
        });
    });

    // Función para mostrar la tabla de amortización
    function mostrarTablaAmortizacion(data) {
        const { datos, tabla, configuracion } = data;
        
        // Calcular totales
        let totalPagos = 0;
        let totalIntereses = 0;
        let totalCapital = 0;
        
        tabla.forEach(pago => {
            totalPagos += parseFloat(pago.pago.replace(/,/g, ''));
            totalIntereses += parseFloat(pago.interes.replace(/,/g, ''));
            totalCapital += parseFloat(pago.capital.replace(/,/g, ''));
        });

        // Generar HTML del modal
        const modalHtml = `
            <div class="modal fade" id="modalSimulacion" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-info">
                            <h5 class="modal-title text-white">
                                <i class="fas fa-calculator mr-2"></i>
                                Simulación de Tabla de Amortización - ${configuracion.nombre}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Alerta informativa -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Nota:</strong> Esta simulación muestra los pagos mensuales que realizará el cliente 
                                <strong>después de liquidar el enganche de $${parseFloat(datos.monto_enganche).toLocaleString('es-MX', {minimumFractionDigits: 2})}</strong>.
                                El cliente tiene hasta el <strong>${new Date(datos.fecha_limite_enganche).toLocaleDateString('es-MX')}</strong> para completar el enganche.
                            </div>

                            <!-- Resumen Ejecutivo -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-home"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Precio Total</span>
                                            <span class="info-box-number">$${parseFloat(datos.precio_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-hand-holding-usd"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Enganche Requerido</span>
                                            <span class="info-box-number">$${parseFloat(datos.monto_enganche).toLocaleString('es-MX', {minimumFractionDigits: 2})}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-money-check-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">A Financiar</span>
                                            <span class="info-box-number">$${parseFloat(datos.monto_financiar).toLocaleString('es-MX', {minimumFractionDigits: 2})}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Plazo</span>
                                            <span class="info-box-number">${datos.plazo_meses} meses</span>
                                            <small>${datos.tipo_financiamiento === 'sin_intereses' ? 'Sin Intereses' : datos.tasa_anual + '% Anual'}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de Políticas -->
                            ${configuracion.accion_incompleto ? `
                            <div class="alert alert-warning">
                                <h6 class="mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Políticas de Apartado:</h6>
                                <ul class="mb-0">
                                    <li><strong>Monto mínimo de apartado:</strong> $${parseFloat(datos.monto_apartado).toLocaleString('es-MX', {minimumFractionDigits: 2})}</li>
                                    <li><strong>Si no completa el enganche a tiempo:</strong> ${
                                        configuracion.accion_incompleto === 'liberar_lote' ? 'El lote se libera y el apartado se pierde' :
                                        configuracion.accion_incompleto === 'mantener_apartado' ? 'Se mantiene el apartado activo' :
                                        `Se aplica ${configuracion.penalizacion}% de penalización sobre el apartado`
                                    }</li>
                                    ${configuracion.penalizacion_tardio > 0 ? 
                                        `<li><strong>Penalización por enganche tardío:</strong> ${configuracion.penalizacion_tardio}%</li>` : 
                                        ''
                                    }
                                </ul>
                            </div>
                            ` : ''}

                            <!-- Tabla de Amortización -->
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center">Período</th>
                                            <th class="text-center">Fecha Pago</th>
                                            <th class="text-right">Saldo Inicial</th>
                                            <th class="text-right">Pago Mensual</th>
                                            <th class="text-right">Interés</th>
                                            <th class="text-right">Capital</th>
                                            <th class="text-right">Saldo Final</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${tabla.map(pago => `
                                            <tr>
                                                <td class="text-center">${pago.periodo}</td>
                                                <td class="text-center">${pago.fecha}</td>
                                                <td class="text-right">$${pago.saldo_inicial}</td>
                                                <td class="text-right font-weight-bold">$${pago.pago}</td>
                                                <td class="text-right">$${pago.interes}</td>
                                                <td class="text-right">$${pago.capital}</td>
                                                <td class="text-right">$${pago.saldo_final}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="3" class="text-right">TOTALES:</th>
                                            <th class="text-right">$${totalPagos.toLocaleString('es-MX', {minimumFractionDigits: 2})}</th>
                                            <th class="text-right">$${totalIntereses.toLocaleString('es-MX', {minimumFractionDigits: 2})}</th>
                                            <th class="text-right">$${totalCapital.toLocaleString('es-MX', {minimumFractionDigits: 2})}</th>
                                            <th class="text-right">$0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remover modal anterior si existe
        $('#modalSimulacion').remove();
        
        // Agregar modal al body
        $('body').append(modalHtml);
        
        // Mostrar modal
        $('#modalSimulacion').modal('show');
        
        // Limpiar al cerrar
        $('#modalSimulacion').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }
});
</script>
<?= $this->endSection() ?>