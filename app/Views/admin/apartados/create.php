<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Nuevo Apartado<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Nuevo Apartado<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
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

<!-- LAYOUT PRINCIPAL: Columna Izquierda + Columna Derecha -->
<div class="row">
    <!-- COLUMNA IZQUIERDA: Información del Lote + Configuración del Apartado -->
    <div class="col-lg-4">
        <!-- Información del Lote -->
        <div class="card border-primary mb-3" id="info-lote-card" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-home mr-2"></i>
                    Información del Lote
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="text-primary" id="info-lote-clave">-</h2>
                    <p class="text-muted mb-0">Clave del Lote</p>
                </div>
                
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="40%">Proyecto:</th>
                        <td id="info-proyecto">-</td>
                    </tr>
                    <tr>
                        <th>Manzana:</th>
                        <td id="info-manzana">-</td>
                    </tr>
                    <tr>
                        <th>Tipo:</th>
                        <td>
                            <span class="badge badge-info" id="info-tipo">-</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Superficie:</th>
                        <td id="info-area">- m²</td>
                    </tr>
                    <tr>
                        <th>Precio/m²:</th>
                        <td id="info-precio-m2">$-</td>
                    </tr>
                    <tr class="table-success">
                        <th><strong>Precio Total:</strong></th>
                        <td><strong id="info-precio-total">$-</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Configuración del Apartado -->
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title mb-0">
                    <i class="fas fa-handshake mr-2"></i>
                    Configuración del Apartado
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Las fechas del apartado se configuran en el formulario principal de la derecha.
                </p>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Formulario Principal -->
    <div class="col-lg-8">
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
                                <div class="input-group">
                                    <input type="hidden" id="lote_id" name="lote_id" value="<?= old('lote_id') ?>" required>
                                    <input type="text" class="form-control <?= session('errors.lote_id') ? 'is-invalid' : '' ?>" 
                                           id="lote_display" placeholder="Hacer clic para seleccionar lote..." readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" id="btn-seleccionar-lote">
                                            <i class="fas fa-search"></i> Seleccionar
                                        </button>
                                    </div>
                                </div>
                                <?php if (session('errors.lote_id')): ?>
                                    <div class="invalid-feedback"><?= session('errors.lote_id') ?></div>
                                <?php endif; ?>
                                <small class="form-text text-muted">
                                    Haga clic en "Seleccionar" para ver todas las características del lote
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas del Apartado -->
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
                                <small class="form-text text-muted">
                                    Se calcula automáticamente según el plan seleccionado
                                </small>
                                <?php if (session('errors.fecha_limite_enganche')): ?>
                                    <div class="invalid-feedback"><?= session('errors.fecha_limite_enganche') ?></div>
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
                                <label for="perfil_financiamiento_id">Plan de Financiamiento <span class="text-danger">*</span></label>
                                <select class="form-control select2 <?= session('errors.perfil_financiamiento_id') ? 'is-invalid' : '' ?>" 
                                        id="perfil_financiamiento_id" name="perfil_financiamiento_id" required>
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
                                                    <?= old('perfil_financiamiento_id') == $plan->id ? 'selected' : '' ?>>
                                                <?= $planLabel ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (session('errors.perfil_financiamiento_id')): ?>
                                    <div class="invalid-feedback"><?= session('errors.perfil_financiamiento_id') ?></div>
                                <?php endif; ?>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    MSI = Meses Sin Intereses | MCI = Meses Con Intereses
                                </small>
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
                                    <input type="text" class="form-control input-moneda <?= session('errors.monto_apartado') ? 'is-invalid' : '' ?>" 
                                           id="monto_apartado" name="monto_apartado" value="<?= old('monto_apartado') ?>" 
                                           placeholder="0.00" required>
                                    <input type="hidden" id="monto_apartado_raw" name="monto_apartado_raw">
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
                                    <input type="text" class="form-control input-moneda <?= session('errors.monto_enganche_requerido') ? 'is-invalid' : '' ?>" 
                                           id="monto_enganche_requerido" name="monto_enganche_requerido" value="<?= old('monto_enganche_requerido') ?>" 
                                           placeholder="0.00" required>
                                    <input type="hidden" id="monto_enganche_requerido_raw" name="monto_enganche_requerido_raw">
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

<!-- Modal para selección de lotes -->
<div class="modal fade" id="modalSeleccionarLote" tabindex="-1" role="dialog" aria-labelledby="modalSeleccionarLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalSeleccionarLoteLabel">
                    <i class="fas fa-home mr-2"></i>
                    Seleccionar Lote para Apartado
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="filtro-buscar">Buscar:</label>
                        <input type="text" class="form-control form-control-sm" id="filtro-buscar" 
                               placeholder="Clave, número, proyecto, manzana...">
                    </div>
                    <div class="col-md-4">
                        <label for="filtro-proyecto">Proyecto:</label>
                        <select class="form-control form-control-sm" id="filtro-proyecto">
                            <option value="">Todos los proyectos</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtro-estado">Estado:</label>
                        <select class="form-control form-control-sm" id="filtro-estado">
                            <option value="0">Solo disponibles</option>
                            <option value="">Todos los estados</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de lotes -->
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered" id="tabla-lotes-modal">
                        <thead class="thead-dark">
                            <tr>
                                <th>Clave</th>
                                <th>Proyecto</th>
                                <th>Manzana</th>
                                <th>Área</th>
                                <th>Precio/m²</th>
                                <th>Precio Total</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llena dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('/assets/js/anvar-utils.js') ?>?v=<?= time() ?>"></script>
<script>
$(document).ready(function() {
    // Funciones básicas inmediatas antes de cargar utilidades
    if (typeof limpiarMoneda === 'undefined') {
        window.limpiarMoneda = function(moneda) {
            if (!moneda) return 0;
            return parseFloat(moneda.toString().replace(/[$,\s]/g, '')) || 0;
        };
    }
    
    if (typeof formatearMoneda === 'undefined') {
        window.formatearMoneda = function(numero) {
            if (!numero && numero !== 0) return '$0.00';
            const num = parseFloat(numero);
            if (isNaN(num)) return '$0.00';
            const parts = num.toFixed(2).split('.');
            const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return `$${integerPart}.${parts[1]}`;
        };
    }
    
    
    // Configurar inputs de moneda para formateo automático
    function configurarInputsMoneda() {
        $('.input-moneda').on('input blur', function() {
            const valor = limpiarMoneda($(this).val());
            if (!isNaN(valor) && valor > 0) {
                $(this).val(formatearMoneda(valor).replace('$', ''));
                // Actualizar campo hidden con valor numérico
                const fieldName = $(this).attr('id') + '_raw';
                $('#' + fieldName).val(valor);
            }
        });
        
        $('.input-moneda').on('focus', function() {
            const valor = limpiarMoneda($(this).val());
            if (valor > 0) {
                $(this).val(valor.toFixed(2));
            }
        });
    }
    
    // Función para establecer valor en input de moneda
    function setMonedaValue(selector, valor) {
        const input = $(selector);
        const valorNumerico = parseFloat(valor) || 0;
        
        if (valorNumerico > 0) {
            input.val(formatearMoneda(valorNumerico).replace('$', ''));
            // Actualizar campo hidden
            const fieldName = input.attr('id') + '_raw';
            $('#' + fieldName).val(valorNumerico);
        } else {
            input.val('');
            const fieldName = input.attr('id') + '_raw';
            $('#' + fieldName).val('');
        }
    }
    
    // Configurar inputs después de que las utilidades estén listas
    setTimeout(configurarInputsMoneda, 200);
    
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
        if (datos.perfil_financiamiento_id) {
            $('#perfil_financiamiento_id').val(datos.perfil_financiamiento_id).trigger('change');
        }
        if (datos.monto_apartado) {
            setMonedaValue('#monto_apartado', datos.monto_apartado);
        }
        
        // Limpiar sessionStorage
        sessionStorage.removeItem('datosApartado');
        
        // Después de cargar todos los datos, activar la configuración
        setTimeout(function() {
            $('#perfil_financiamiento_id').trigger('change');
        }, 100);
    }
    <?php endif; ?>
    
    // Auto-cargar valores cuando la página ya tiene una configuración seleccionada
    setTimeout(function() {
        if ($('#perfil_financiamiento_id').val()) {
            $('#perfil_financiamiento_id').trigger('change');
        }
    }, 300);


    // Cuando cambia la configuración financiera
    $('#perfil_financiamiento_id').change(function() {
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
        
        // Obtener precio del lote del campo info-precio-total (más confiable)
        let precioLote = 0;
        const precioTexto = $('#info-precio-total').text().replace(/[^0-9.]/g, '');
        if (precioTexto) {
            precioLote = parseFloat(precioTexto);
        }
        
        
        // Actualizar monto de apartado mínimo
        if (apartadoMinimo > 0) {
            setMonedaValue('#monto_apartado', apartadoMinimo);
            $('#label-apartado-info').text(`(Mínimo: ${formatearMoneda(apartadoMinimo)})`);
            $('#help-monto-apartado').text(`Monto mínimo requerido: ${formatearMoneda(apartadoMinimo)}`);
        } else {
            setMonedaValue('#monto_apartado', 0);
            $('#label-apartado-info').text('');
            $('#help-monto-apartado').text('Ingrese el monto del apartado');
        }
        
        // Todos los planes disponibles en apartados requieren enganche (filtrados en el modelo)
        $('#monto_enganche_requerido').prop('readonly', false);
        $('#label-enganche').html('Monto Enganche Requerido <span class="text-danger">*</span>');
        
        let montoEngancheCalculado = 0;
        
        if (precioLote > 0) {
            if (tipoAnticipo === 'porcentaje') {
                montoEngancheCalculado = precioLote * (porcentajeAnticipo / 100);
                const calculoDetalle = `${formatearMoneda(precioLote)} × ${porcentajeAnticipo}% = ${formatearMoneda(montoEngancheCalculado)}`;
                $('#label-enganche-info').text(`(${porcentajeAnticipo}% del precio: ${calculoDetalle})`);
            } else if (tipoAnticipo === 'fijo') {
                montoEngancheCalculado = anticipoFijo;
                $('#label-enganche-info').text(`(Monto fijo: ${formatearMoneda(anticipoFijo)})`);
            }
        }
        
        // Validar enganche mínimo
        if (engancheMinimo > 0 && montoEngancheCalculado < engancheMinimo) {
            montoEngancheCalculado = engancheMinimo;
            $('#label-enganche-info').text(`(Mínimo requerido: ${formatearMoneda(engancheMinimo)})`);
        }
        
        if (montoEngancheCalculado > 0) {
            setMonedaValue('#monto_enganche_requerido', montoEngancheCalculado);
            $('#help-monto-enganche').text(`Monto requerido para aperturar el plan de pagos`);
        } else {
            setMonedaValue('#monto_enganche_requerido', 0);
            $('#label-enganche-info').text(engancheMinimo > 0 ? `(Mínimo: ${formatearMoneda(engancheMinimo)})` : '');
        }
        
        // Calcular fecha límite de enganche
        const fechaApartado = new Date($('#fecha_apartado').val() || new Date());
        fechaApartado.setDate(fechaApartado.getDate() + plazoEnganche);
        $('#fecha_limite_enganche').val(fechaApartado.toISOString().split('T')[0]);
        
        
    });

    // Función para calcular fecha límite
    function calcularFechaLimite() {
        const fechaApartado = new Date($('#fecha_apartado').val());
        const selectedConfig = $('#perfil_financiamiento_id').find('option:selected');
        const plazoEnganche = parseInt(selectedConfig.data('plazo-liquidar-enganche')) || 30;
        
        if (fechaApartado && !isNaN(fechaApartado.getTime())) {
            fechaApartado.setDate(fechaApartado.getDate() + plazoEnganche);
            const fechaLimite = fechaApartado.toISOString().split('T')[0];
            $('#fecha_limite_enganche').val(fechaLimite);
        }
    }

    // Calcular fecha límite basada en fecha de apartado y configuración
    $('#fecha_apartado').change(calcularFechaLimite);
    $('#perfil_financiamiento_id').change(calcularFechaLimite);

    // Calcular fecha límite inicial si ya hay datos
    if ($('#fecha_apartado').val() && $('#perfil_financiamiento_id').val()) {
        calcularFechaLimite();
    }

    // Variable global para controlar la tabla
    let tableLotesModal = null;

    // Manejar clic en botón seleccionar lote
    $('#btn-seleccionar-lote').click(function() {
        // Mostrar modal primero
        $('#modalSeleccionarLote').modal('show');
    });

    // Inicializar tabla cuando el modal se muestra completamente
    $('#modalSeleccionarLote').on('shown.bs.modal', function() {
        cargarLotesModal();
    });

    // Limpiar tabla cuando se oculta el modal
    $('#modalSeleccionarLote').on('hidden.bs.modal', function() {
        if (tableLotesModal) {
            tableLotesModal.destroy();
            tableLotesModal = null;
        }
        $('#tabla-lotes-modal tbody').empty();
    });

    // Función para cargar lotes en el modal
    function cargarLotesModal() {
        // Si ya existe una tabla, destruirla
        if (tableLotesModal) {
            tableLotesModal.destroy();
            tableLotesModal = null;
        }
        
        // Limpiar tabla completamente
        $('#tabla-lotes-modal').empty();
        $('#tabla-lotes-modal').html(`
            <thead class="thead-dark">
                <tr>
                    <th>Clave</th>
                    <th>Proyecto</th>
                    <th>Manzana</th>
                    <th>Área</th>
                    <th>Precio/m²</th>
                    <th>Precio Total</th>
                    <th>Estado</th>
                    <th>Tipo</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
        `);
        
        // Cargar datos primero con AJAX simple
        $.ajax({
            url: '<?= site_url('/admin/apartados/obtener-lotes-modal') ?>',
            type: 'POST',
            data: {
                proyecto_id: $('#filtro-proyecto').val(),
                estado_id: $('#filtro-estado').val() || '0',
                search: $('#filtro-buscar').val(),
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response && response.data && Array.isArray(response.data)) {
                    // Inicializar DataTable con los datos
                    tableLotesModal = $('#tabla-lotes-modal').DataTable({
                        data: response.data,
                        destroy: true,
                        processing: false,
                        serverSide: false,
                        columns: [
                            { data: 'clave' },
                            { data: 'proyecto' },
                            { data: 'manzana' },
                            { data: 'area' },
                            { data: 'precio_m2' },
                            { data: 'precio_total' },
                            { 
                                data: 'estado',
                                render: function(data, type, row) {
                                    return data; // Ya viene formateado
                                }
                            },
                            { data: 'tipo' },
                            { 
                                data: 'accion',
                                orderable: false,
                                render: function(data, type, row) {
                                    return data; // Ya viene formateado
                                }
                            }
                        ],
                        language: {
                            search: "Buscar:",
                            lengthMenu: "Mostrar _MENU_ registros",
                            info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                            infoEmpty: "No hay registros para mostrar",
                            zeroRecords: "No se encontraron resultados",
                            emptyTable: "No hay datos disponibles",
                            paginate: {
                                first: "Primero",
                                previous: "Anterior",
                                next: "Siguiente",
                                last: "Último"
                            }
                        },
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                        order: [[0, 'asc']]});
                } else {
                    $('#tabla-lotes-modal tbody').html(`
                        <tr>
                            <td colspan="9" class="text-center text-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No se encontraron lotes disponibles
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr, error, code) {
                console.error('❌ Error loading lots data:', {xhr, error, code});
                $('#tabla-lotes-modal tbody').html(`
                    <tr>
                        <td colspan="9" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Error al cargar los lotes: ${xhr.responseText || error}
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Manejar filtros del modal
    $(document).on('change keyup', '#filtro-buscar, #filtro-proyecto, #filtro-estado', function() {
        // Recargar completamente los datos
        if ($('#modalSeleccionarLote').hasClass('show')) {
            cargarLotesModal();
        }
    });

    // Manejar selección de lote desde el modal
    $(document).on('click', '.btn-seleccionar-lote', function() {
        const loteId = $(this).data('id');
        const clave = $(this).data('clave');
        const precio = parseFloat($(this).data('precio'));
        const area = parseFloat($(this).data('area'));
        const precioM2 = parseFloat($(this).data('precio-m2'));
        const proyecto = $(this).data('proyecto');
        const manzana = $(this).data('manzana');
        const tipo = $(this).data('tipo');
        const categoria = $(this).data('categoria');

        // Actualizar campos del formulario
        $('#lote_id').val(loteId);
        $('#lote_display').val(`${clave} - ${formatearMoneda(precio)}`);

        // Mostrar información del lote
        mostrarInfoLote({
            clave: clave,
            precio: precio,
            area: area,
            precioM2: precioM2,
            proyecto: proyecto,
            manzana: manzana,
            tipo: tipo,
            categoria: categoria
        });

        // Cerrar modal
        $('#modalSeleccionarLote').modal('hide');

        // Actualizar las financiamientos disponibles para este lote
        actualizarFinanciamientos(loteId);
    });

    // Detectar cambios en lote_id para casos de pre-carga
    $('#lote_id').change(function() {
        const loteId = $(this).val();
        if (loteId && !$('#lote_display').val()) {
            // Buscar información del lote si viene pre-cargado
            buscarInfoLote(loteId);
        }
    });
    
    // Función para actualizar las financiamientos disponibles según el lote
    function actualizarFinanciamientos(loteId) {
        if (!loteId) return;
        
        // Guardar configuración actual
        const configuracionActual = $('#perfil_financiamiento_id').val();
        
        $.ajax({
            url: '<?= site_url('/admin/apartados/perfiles-por-lote') ?>',
            method: 'GET',
            data: { lote_id: loteId },
            success: function(response) {
                if (response.success && response.configuraciones) {
                    // Limpiar y repoblar el select
                    $('#perfil_financiamiento_id').empty();
                    $('#perfil_financiamiento_id').append('<option value="">Seleccionar plan...</option>');
                    
                    response.configuraciones.forEach(function(config) {
                        // Determinar tipo de financiamiento y crear descripción detallada
                        let tipoFinanciamiento = '';
                        let meses = 0;
                        let tasa = '';
                        
                        if (config.meses_sin_intereses > 0) {
                            tipoFinanciamiento = 'MSI';
                            meses = config.meses_sin_intereses;
                            tasa = '0% Interés';
                        } else if (config.meses_con_intereses > 0) {
                            tipoFinanciamiento = 'MCI';
                            meses = config.meses_con_intereses;
                            tasa = config.porcentaje_interes_anual > 0 ? config.porcentaje_interes_anual + '% Anual' : '0% Interés';
                        }
                        
                        // Verificar si es plan cero enganche
                        const ceroEnganche = (config.tipo_anticipo === 'fijo' && config.anticipo_fijo == 0) || 
                                           (config.tipo_anticipo === 'porcentaje' && config.porcentaje_anticipo == 0) ||
                                           config.promocion_cero_enganche == 1;
                        
                        const labelEnganche = ceroEnganche ? ' • CERO ENGANCHE' : '';
                        
                        // Crear label descriptivo
                        let planLabel = config.nombre + ' - ' + (config.empresa_nombre || 'Global');
                        if (meses > 0) {
                            planLabel += ` • ${meses} ${tipoFinanciamiento} • ${tasa}`;
                        }
                        planLabel += labelEnganche;
                        
                        const option = $('<option></option>')
                            .attr('value', config.id)
                            .attr('data-apartado-minimo', config.apartado_minimo)
                            .attr('data-enganche-minimo', config.enganche_minimo)
                            .attr('data-plazo-liquidar-enganche', config.plazo_liquidar_enganche)
                            .attr('data-tipo-anticipo', config.tipo_anticipo)
                            .attr('data-porcentaje-anticipo', config.porcentaje_anticipo)
                            .attr('data-anticipo-fijo', config.anticipo_fijo)
                            .attr('data-promocion-cero-enganche', config.promocion_cero_enganche)
                            .attr('data-meses-sin-intereses', config.meses_sin_intereses)
                            .attr('data-meses-con-intereses', config.meses_con_intereses)
                            .attr('data-porcentaje-interes-anual', config.porcentaje_interes_anual)
                            .attr('data-cero-enganche', ceroEnganche ? 'true' : 'false')
                            .attr('data-accion-anticipo-incompleto', config.accion_anticipo_incompleto)
                            .attr('data-penalizacion-apartado', config.penalizacion_apartado)
                            .attr('data-penalizacion-enganche-tardio', config.penalizacion_enganche_tardio)
                            .text(planLabel);
                        
                        $('#perfil_financiamiento_id').append(option);
                    });
                    
                    // Si había una configuración seleccionada y aún está disponible, reseleccionarla
                    if (configuracionActual) {
                        $('#perfil_financiamiento_id').val(configuracionActual);
                    }
                    
                    // Trigger change para recalcular montos
                    $('#perfil_financiamiento_id').trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener configuraciones:', error);
                Swal.fire('Error', 'No se pudieron cargar las financiamientos', 'error');
            }
        });
    }

    // Función para buscar información de lote pre-cargado
    function buscarInfoLote(loteId) {
        $.ajax({
            url: '<?= site_url('/admin/apartados/obtener-lotes-modal') ?>',
            method: 'POST',
            data: {
                lote_id: loteId,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    const lote = response.data[0];
                    $('#lote_display').val(`${lote.clave} - ${formatearMoneda(lote.data_precio)}`);
                    
                    mostrarInfoLote({
                        clave: lote.clave,
                        precio: parseFloat(lote.data_precio),
                        area: parseFloat(lote.data_area),
                        precioM2: parseFloat(lote.data_precio_m2),
                        proyecto: lote.proyecto,
                        manzana: lote.manzana,
                        tipo: lote.tipo,
                        categoria: lote.categoria || ''
                    });
                }
            }
        });
    }

    // Función para mostrar información detallada del lote
    function mostrarInfoLote(loteData) {
        // Actualizar información del lote
        $('#info-lote-clave').text(loteData.clave || '-');
        $('#info-precio-total').text(formatearMoneda(loteData.precio || 0));
        $('#info-area').text((loteData.area || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' m²');
        $('#info-precio-m2').text(formatearMoneda(loteData.precioM2 || 0));
        $('#info-proyecto').text(loteData.proyecto || 'N/A');
        $('#info-manzana').text(loteData.manzana || 'N/A');
        
        // Mostrar tipo y categoría
        let tipoHtml = `<span class="badge badge-info">${loteData.tipo || 'Lote'}</span>`;
        if (loteData.categoria) {
            tipoHtml += ` <span class="badge badge-secondary ml-1">${loteData.categoria}</span>`;
        }
        $('#info-tipo').html(tipoHtml);
        
        $('#info-lote-card').show();
    }



    // Validación del monto de apartado
    $('#monto_apartado').on('input', function() {
        const montoMinimo = parseFloat($(this).attr('min')) || 0;
        const montoIngresado = parseFloat($(this).val()) || 0;
        
        if (montoIngresado < montoMinimo && montoMinimo > 0) {
            $(this).addClass('is-invalid');
            if (!$('#error-monto-apartado-custom').length) {
                $(this).after(`<div class="invalid-feedback" id="error-monto-apartado-custom">
                    El monto mínimo de apartado es ${formatearMoneda(montoMinimo)}
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
                    El monto mínimo de enganche es ${formatearMoneda(montoMinimo)}
                </div>`);
            }
        } else {
            $(this).removeClass('is-invalid');
            $('#error-monto-enganche-custom').remove();
        }
    });

    // Validación antes de enviar el formulario
    $('form').on('submit', function(e) {
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
        const configuracionId = $('#perfil_financiamiento_id').val();
        
        if (loteId && configuracionId) {
            $('#btnSimularAmortizacion').prop('disabled', false);
        } else {
            $('#btnSimularAmortizacion').prop('disabled', true);
        }
    }

    $('#lote_id, #perfil_financiamiento_id').change(verificarSimulacion);

    // Simulación de Tabla de Amortización
    $('#btnSimularAmortizacion').click(function() {
        const loteId = $('#lote_id').val();
        const configuracionId = $('#perfil_financiamiento_id').val();
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
                perfil_financiamiento_id: configuracionId,
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
        
        
        // Calcular totales - los datos vienen como números del servidor
        let totalPagos = 0;
        let totalIntereses = 0;
        let totalCapital = 0;
        
        tabla.forEach(pago => {
            // Asegurar que obtenemos valores numéricos, independientemente del formato
            const pagoNumerico = typeof pago.pago === 'string' ? limpiarMoneda(pago.pago) : parseFloat(pago.pago) || 0;
            const interesNumerico = typeof pago.interes === 'string' ? limpiarMoneda(pago.interes) : parseFloat(pago.interes) || 0;
            const capitalNumerico = typeof pago.capital === 'string' ? limpiarMoneda(pago.capital) : parseFloat(pago.capital) || 0;
            
            totalPagos += pagoNumerico;
            totalIntereses += interesNumerico;
            totalCapital += capitalNumerico;
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
                                <strong>después de liquidar el enganche de ${formatearMoneda(datos.monto_enganche)}</strong>.
                                El cliente tiene hasta el <strong>${new Date(datos.fecha_limite_enganche).toLocaleDateString('es-MX')}</strong> para completar el enganche.
                            </div>

                            <!-- Resumen Ejecutivo -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-home"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Precio Total</span>
                                            <span class="info-box-number">${formatearMoneda(datos.precio_total)}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-hand-holding-usd"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Enganche Requerido</span>
                                            <span class="info-box-number">${formatearMoneda(datos.monto_enganche)}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-money-check-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">A Financiar</span>
                                            <span class="info-box-number">${formatearMoneda(datos.monto_financiar)}</span>
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
                                    <li><strong>Monto mínimo de apartado:</strong> ${formatearMoneda(datos.monto_apartado)}</li>
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
                                        ${tabla.map(pago => {
                                            // Asegurar formato numérico correcto para cada celda
                                            const saldoInicial = typeof pago.saldo_inicial === 'string' ? limpiarMoneda(pago.saldo_inicial) : parseFloat(pago.saldo_inicial) || 0;
                                            const pagoMensual = typeof pago.pago === 'string' ? limpiarMoneda(pago.pago) : parseFloat(pago.pago) || 0;
                                            const interes = typeof pago.interes === 'string' ? limpiarMoneda(pago.interes) : parseFloat(pago.interes) || 0;
                                            const capital = typeof pago.capital === 'string' ? limpiarMoneda(pago.capital) : parseFloat(pago.capital) || 0;
                                            const saldoFinal = typeof pago.saldo_final === 'string' ? limpiarMoneda(pago.saldo_final) : parseFloat(pago.saldo_final) || 0;
                                            
                                            return `
                                            <tr>
                                                <td class="text-center">${pago.periodo}</td>
                                                <td class="text-center">${pago.fecha}</td>
                                                <td class="text-right">${formatearMoneda(saldoInicial)}</td>
                                                <td class="text-right font-weight-bold">${formatearMoneda(pagoMensual)}</td>
                                                <td class="text-right">${formatearMoneda(interes)}</td>
                                                <td class="text-right">${formatearMoneda(capital)}</td>
                                                <td class="text-right">${formatearMoneda(saldoFinal)}</td>
                                            </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="3" class="text-right">TOTALES:</th>
                                            <th class="text-right">${formatearMoneda(totalPagos)}</th>
                                            <th class="text-right">${formatearMoneda(totalIntereses)}</th>
                                            <th class="text-right">${formatearMoneda(totalCapital)}</th>
                                            <th class="text-right">$0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-info" onclick="imprimirSimulacion()">
                                <i class="fas fa-print mr-1"></i>Imprimir
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Mostrar modal
        $('body').append(modalHtml);
        $('#modalSimulacion').modal('show');
        
        // Limpiar modal cuando se cierre
        $('#modalSimulacion').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    // Función para imprimir simulación
    window.imprimirSimulacion = function() {
        const modalContent = $('#modalSimulacion .modal-body').html();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Simulación de Amortización - Apartado</title>
                <link rel="stylesheet" href="<?= base_url('/assets/css/bootstrap.min.css') ?>">
                <style>
                    body { font-family: Arial, sans-serif; }
                    .info-box { border: 1px solid #ddd; padding: 10px; margin: 5px; }
                    .info-box-icon { display: inline-block; width: 50px; text-align: center; }
                    .table { width: 100%; border-collapse: collapse; }
                    .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
                    .table th { background-color: #f8f9fa; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body>
                <h1>Simulación de Tabla de Amortización - Apartado</h1>
                ${modalContent}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    };

    // DEBUG: Limpiar valores de moneda antes de enviar el formulario
    $('form').on('submit', function(e) {
        // Debug temporal - remover en producción
        
        // Verificar si los campos tienen name attribute
        $('input[required], select[required]').each(function() {
        });
        
        // NO modificar fechas en JavaScript - dejar que el servidor las procese
        
        // Limpiar formato de moneda para enviar números puros
        const montoApartado = $('#monto_apartado').val();
        if (montoApartado) {
            $('#monto_apartado').val(limpiarMoneda(montoApartado));
        }
        
        const montoEnganche = $('#monto_enganche_requerido').val();
        if (montoEnganche) {
            $('#monto_enganche_requerido').val(limpiarMoneda(montoEnganche));
        }
        
        // Calcular fecha límite si está vacía
        if (!$('#fecha_limite_enganche').val()) {
            calcularFechaLimite();
        }
        
    });
});
</script>
<?= $this->endSection() ?>