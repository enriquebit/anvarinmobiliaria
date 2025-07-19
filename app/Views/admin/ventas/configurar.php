<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Configurar Venta<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Configurar Venta - Lote <?= esc($lote->clave) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ventas') ?>">Ventas</a></li>
<li class="breadcrumb-item active">Configurar Venta</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Alerta cuando se viene desde apartados -->
<div id="alerta-desde-apartado" class="alert alert-info alert-dismissible" style="display: none;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <h5><i class="fas fa-info-circle mr-2"></i>Venta desde Apartado - Plan Cero Enganche</h5>
    <p class="mb-1">
        Ha sido redirigido desde el módulo de apartados porque seleccionó un plan de <strong>cero enganche</strong>.
    </p>
    <p class="mb-0">
        <small class="text-muted">
            Los datos del cliente, lote y configuración financiera han sido precargados automáticamente.
        </small>
    </p>
</div>

<!-- LAYOUT PRINCIPAL: Columna Izquierda + Columna Derecha -->
<div class="row">
    <!-- COLUMNA IZQUIERDA: Información del Lote + Configuración de Comisiones -->
    <div class="col-lg-4">
        <!-- Información del Lote -->
        <div class="card border-primary mb-3">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-home mr-2"></i>
                    Información del Lote
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="text-primary"><?= esc($lote->clave) ?></h2>
                    <p class="text-muted mb-0">Clave del Lote</p>
                </div>
                
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="40%">Proyecto:</th>
                        <td><?= esc($lote->proyecto_nombre ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Manzana:</th>
                        <td><?= esc($lote->manzana_nombre ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Tipo:</th>
                        <td>
                            <span class="badge badge-info">
                                <?= esc($lote->tipo_nombre ?? 'Lote') ?>
                            </span>
                            <?php if (!empty($lote->categoria_nombre)): ?>
                                <span class="badge badge-secondary ml-1">
                                    <?= esc($lote->categoria_nombre) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Superficie:</th>
                        <td><?= number_format($lote->area, 2) ?> m²</td>
                    </tr>
                    <tr>
                        <th>Precio/m²:</th>
                        <td>$<?= number_format($lote->precio_m2, 2) ?></td>
                    </tr>
                    <tr class="table-success">
                        <th><strong>Precio Total:</strong></th>
                        <td><strong>$<?= number_format($lote->precio_total, 2) ?></strong></td>
                    </tr>
                </table>

                <?php if (!empty($lote->descripcion)): ?>
                <hr>
                <h6>Descripción:</h6>
                <p class="text-muted small"><?= esc($lote->descripcion) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Configuración de Comisiones -->
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-percentage mr-2"></i>
                    Configuración de Comisiones
                </h3>
            </div>
            <div class="card-body">
                <!-- Información del tipo de comisión -->
                <div class="alert alert-info mb-3" id="info_tipo_comision">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Tipo:</strong> <span id="tipo_comision_texto">Seleccione configuración financiera</span>
                </div>
                
                <div class="form-group">
                    <label class="small font-weight-bold">Tipo de Comisión</label>
                    <input type="text" class="form-control form-control-sm" id="tipo_comision_display" readonly>
                    <input type="hidden" id="tipo_comision" name="tipo_comision">
                </div>
                
                <!-- Campo para porcentaje de comisión -->
                <div class="form-group" id="campo_porcentaje_comision">
                    <label class="small font-weight-bold">Porcentaje (%)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="porcentaje_comision_display" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <input type="hidden" id="porcentaje_comision" name="porcentaje_comision">
                </div>
                
                <!-- Campo para comisión fija -->
                <div class="form-group d-none" id="campo_comision_fija">
                    <label class="small font-weight-bold">Comisión Fija</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="text" class="form-control" id="comision_fija_display" readonly>
                    </div>
                    <input type="hidden" id="comision_fija" name="comision_fija">
                </div>
                
                <div class="form-group">
                    <label class="small font-weight-bold">Comisión Calculada</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="text" class="form-control font-weight-bold text-success" id="monto_comision_calculada_display" readonly>
                        <input type="hidden" id="monto_comision_calculada" name="monto_comision_calculada">
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="promocion_cero_enganche_aplicada" 
                           name="promocion_cero_enganche_aplicada" disabled>
                    <label class="form-check-label small" for="promocion_cero_enganche_aplicada">
                        Promoción Cero Enganche
                    </label>
                </div>
                
                <div class="form-group mt-2 d-none" id="grupo_mensualidades_comision_venta">
                    <label class="small font-weight-bold">Mensualidades para Comisión</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="mensualidades_comision_venta" 
                               name="mensualidades_comision_venta" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">pagos</span>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-secondary small mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    El vendedor recibirá: <span id="comision_resumen" class="font-weight-bold">---</span>
                </div>
                
                <!-- Mostrar monto total de comisión calculada -->
                <div class="alert alert-success small mt-2">
                    <i class="fas fa-dollar-sign mr-1"></i>
                    <strong>Total Comisión:</strong> <span id="comision_total_display" class="font-weight-bold">$0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Configuración de la Venta -->
    <div class="col-lg-8">
        <form action="<?= site_url('/admin/ventas/store') ?>" method="post" id="ventaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="lote_id" value="<?= $lote->id ?>">
            <input type="hidden" name="folio_venta" id="folio_venta">
            <input type="hidden" name="perfil_financiamiento_id" id="perfil_financiamiento_id_hidden">
            <input type="hidden" name="precio_lista" id="precio_lista" value="<?= $lote->precio_total ?>">
            <input type="hidden" name="promocion_cero_enganche_aplicada" id="promocion_cero_enganche_aplicada_hidden" value="0">
            
            <!-- Selección de Cliente -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Selección de Cliente
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="cliente_id">Cliente <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="cliente_id" name="cliente_id" required>
                                    <option value="">Seleccionar cliente...</option>
                                    <?php if (!empty($clientes)): ?>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente->id ?>">
                                                <?= esc($cliente->nombres) ?> <?= esc($cliente->apellido_paterno) ?> <?= esc($cliente->apellido_materno) ?>
                                                - <?= esc($cliente->email) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <a href="<?= site_url('/admin/clientes/create') ?>" class="btn btn-success btn-block" target="_blank">
                                    <i class="fas fa-plus mr-1"></i>
                                    Nuevo Cliente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración Financiera -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator mr-2"></i>
                        Configuración Financiera
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="perfil_financiamiento_id">Plan Financiero <span class="text-danger">*</span></label>
                                
                                <?php
                                // DEBUG VISTA: Verificar datos recibidos del controller
                                echo "<!-- DEBUG VISTA configurar.php -->\n";
                                echo "<!-- Total planes recibidos: " . count($planes_financiamiento ?? []) . " -->\n";
                                if (!empty($planes_financiamiento)) {
                                    foreach ($planes_financiamiento as $i => $plan) {
                                        echo "<!-- Plan[$i] en vista: ID={$plan->id}, Nombre={$plan->nombre} -->\n";
                                    }
                                }
                                echo "<!-- FIN DEBUG VISTA -->\n";
                                ?>
                                
                                <select class="form-control" id="perfil_financiamiento_id" name="perfil_financiamiento_id" required>
                                    <option value="">Seleccionar plan...</option>
                                    <?php if (!empty($planes_financiamiento)): ?>
                                        <?php foreach ($planes_financiamiento as $config): ?>
                                            <option value="<?= $config->id ?>"
                                                    data-permite-apartado="<?= $config->permite_apartado ? '1' : '0' ?>"
                                                    data-apartado-minimo="<?= $config->apartado_minimo ?>"
                                                    data-tipo-anticipo="<?= $config->tipo_anticipo ?>"
                                                    data-porcentaje-anticipo="<?= $config->porcentaje_anticipo ?>"
                                                    data-anticipo-fijo="<?= $config->anticipo_fijo ?>"
                                                    data-enganche-minimo="<?= $config->enganche_minimo ?>"
                                                    data-plazo-liquidar-enganche="<?= $config->plazo_liquidar_enganche ?>"
                                                    data-meses-sin-intereses="<?= $config->meses_sin_intereses ?>"
                                                    data-meses-con-intereses="<?= $config->meses_con_intereses ?>"
                                                    data-porcentaje-interes-anual="<?= $config->porcentaje_interes_anual ?>"
                                                    data-promocion-cero-enganche="<?= $config->promocion_cero_enganche ? '1' : '0' ?>"
                                                    data-tipo-comision="<?= $config->tipo_comision ?>"
                                                    data-porcentaje-comision="<?= $config->porcentaje_comision ?>"
                                                    data-comision-fija="<?= $config->comision_fija ?>"
                                                    data-mensualidades-comision="<?= $config->mensualidades_comision ?? 2 ?>"
                                                    data-dias-anticipo="<?= $config->dias_anticipo ?? 30 ?>"
                                                    data-tipo-financiamiento="<?= $config->tipo_financiamiento ?? 'msi' ?>">
                                                <?= esc($config->nombre) ?>
                                                - <?= $config->meses_sin_intereses ?> MSI + <?= $config->meses_con_intereses ?> MCI
                                                (<?= $config->porcentaje_interes_anual > 0 ? $config->porcentaje_interes_anual . '% anual' : 'Sin interés' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_venta">Tipo de Venta <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipo_venta" name="tipo_venta" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="contado">Contado</option>
                                    <option value="financiado">Financiado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Apartado -->
            <div class="card mb-3 d-none" id="apartadoConfig">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-home mr-2"></i>
                        Configuración de Apartado
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="crear_apartado" name="crear_apartado">
                                <label for="crear_apartado" class="form-check-label">
                                    Crear apartado primero
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto_apartado">Monto del Apartado</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="monto_apartado_display" readonly>
                                    <input type="hidden" id="monto_apartado" name="monto_apartado">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Enganche -->
            <div class="card mb-3 d-none" id="engancheConfig">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-handshake mr-2"></i>
                        Configuración de Enganche
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Información del tipo de anticipo -->
                    <div class="alert alert-info mb-3" id="info_tipo_anticipo">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Tipo de Anticipo:</strong> <span id="tipo_anticipo_texto">---</span>
                    </div>
                    
                    <div class="row">
                        <!-- Campo para porcentaje -->
                        <div class="col-md-4" id="campo_porcentaje_enganche">
                            <div class="form-group">
                                <label for="porcentaje_enganche">Porcentaje de Enganche (%)</label>
                                <input type="number" class="form-control" id="porcentaje_enganche" name="porcentaje_enganche" 
                                       min="0" max="100" step="0.01">
                                <small class="form-text text-muted" id="rangoEnganche"></small>
                            </div>
                        </div>
                        
                        <!-- Campo para monto fijo (eliminado para evitar duplicación) -->
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="monto_enganche">Monto del Enganche</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="monto_enganche_display" readonly>
                                    <input type="hidden" id="monto_enganche" name="monto_enganche">
                                </div>
                                <small class="form-text text-muted" id="enganche_info">Calculado automáticamente</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="monto_financiar">Monto a Financiar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="monto_financiar_display" readonly>
                                    <input type="hidden" id="monto_financiar" name="monto_financiar">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="monto_mensualidad_comision">Monto a Pagar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control bg-warning" id="monto_mensualidad_comision_display" readonly>
                                    <input type="hidden" id="monto_mensualidad_comision" name="monto_mensualidad_comision">
                                </div>
                                <small class="form-text text-muted" id="pago_info">Para cero enganche: monto a financiar | Con enganche: monto del enganche</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Plazos -->
            <div class="card mb-3 d-none" id="plazosConfig">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Plazos y Financiamiento
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Información de financiamiento -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="alert alert-warning" id="info_financiamiento">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Financiamiento:</strong> <span id="financiamiento_texto">---</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-primary" id="info_interes_aplicado">
                                <i class="fas fa-percentage mr-2"></i>
                                <strong>Interés:</strong> <span id="interes_aplicado_texto">---</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="plazo_meses">Plazo en Meses <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="plazo_meses" name="plazo_meses" 
                                       min="1" step="1" required placeholder="Ej: 12">
                                <small class="form-text text-muted" id="rangoPlazos">Seleccione configuración</small>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-success" id="progress_sin_interes" style="width: 0%"></div>
                                    <div class="progress-bar bg-warning" id="progress_con_interes" style="width: 0%"></div>
                                </div>
                                <small class="form-text" id="status_plazo"></small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tasa_interes_anual">Tasa Anual (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="tasa_interes_anual" name="tasa_interes_anual" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="pago_mensual">Pago Mensual</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control font-weight-bold" id="pago_mensual_display" readonly>
                                    <input type="hidden" id="pago_mensual" name="pago_mensual">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="dia_pago_mensual">Día de Pago</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="dia_pago_mensual" name="dia_pago_mensual" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">del mes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información Adicional
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendedor_id">Vendedor <span class="text-danger">*</span></label>
                                <select class="form-control" id="vendedor_id" name="vendedor_id" required>
                                    <option value="">Seleccionar vendedor...</option>
                                    <?php if (!empty($vendedores)): ?>
                                        <?php foreach ($vendedores as $vendedor): ?>
                                            <option value="<?= $vendedor->id ?>" <?= auth()->id() == $vendedor->id ? 'selected' : '' ?>>
                                                <?= esc($vendedor->nombres ?? $vendedor->username) ?> 
                                                <?= esc($vendedor->apellido_paterno ?? '') ?> 
                                                <?= esc($vendedor->apellido_materno ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_venta">Fecha de Venta <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_venta" name="fecha_venta" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="descuento_aplicado">Descuento</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="descuento_aplicado" name="descuento_aplicado" 
                                           min="0" step="0.01" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_a_pagar">Total a Pagar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control font-weight-bold" id="total_a_pagar_display" readonly>
                                    <input type="hidden" id="total_a_pagar" name="total_a_pagar">
                                </div>
                                <small class="text-muted" id="total_a_pagar_descripcion">Seleccione plan financiero</small>
                                <!-- Campo hidden para compatibilidad con backend -->
                                <input type="hidden" id="precio_venta_final" name="precio_venta_final">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-info btn-block" id="generarSimulacion">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    Generar Simulación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Pago -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i>
                        Información del Pago
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="forma_pago">Método de Pago</label>
                                <select class="form-control" id="forma_pago" name="forma_pago" required>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="deposito">Depósito</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="referencia_pago">Referencia del Pago</label>
                                <input type="text" class="form-control" id="referencia_pago" name="referencia_pago" 
                                       placeholder="No. operación, folio, referencia...">
                                <small class="form-text text-muted">
                                    Ingrese número de operación, folio o referencia para ubicar la transacción
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-warning btn-block" id="crearApartadoBtn">
                                <i class="fas fa-home mr-1"></i>
                                Crear Solo Apartado
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-handshake mr-1"></i>
                                Procesar Venta Completa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- INCLUIR MODAL DE TABLA DE AMORTIZACIÓN -->
<?= $this->include('admin/ventas/partials/modal_amortizacion') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- 🔄 JAVASCRIPT REGENERADO DESDE CERO -->
<script src="<?= base_url('/assets/js/anvar-utils.js') ?>?v=<?= time() ?>"></script>

<script>
$(document).ready(function() {
    'use strict';
    

    
    // DEBUG VISTA -> JS: Verificar handoff de datos PHP a JavaScript



    
    <?php if (!empty($planes_financiamiento)): ?>

    <?php foreach ($planes_financiamiento as $i => $plan): ?>

    <?php endforeach; ?>
    <?php else: ?>

    <?php endif; ?>
    
    // VARIABLES GLOBALES
    let planActual = null;
    
    // INICIALIZACIÓN
    inicializarComponentes();
    configurarEventos();
    inicializarValoresIniciales();
    
    // FIX: Forzar carga del plan si hay uno preseleccionado
    setTimeout(() => {
        const planPreseleccionado = $('#perfil_financiamiento_id').val();
        if (planPreseleccionado && planPreseleccionado !== '') {

            cargarPlanFinanciero(planPreseleccionado);
            
            setTimeout(() => {



            }, 200);
        }
    }, 500);
    
    // DEBUG AJAX: Interceptar todas las peticiones AJAX
    $(document).ajaxSend(function(event, jqxhr, settings) {
        // Debug AJAX requests removed
    });
    
    $(document).ajaxComplete(function(event, jqxhr, settings) {
        // Debug AJAX responses removed
    });
    
    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        // Debug AJAX errors removed
    });
    
    function inicializarComponentes() {
        // LOGGING DETALLADO - Verificar estado inicial del select






        
        // Listar todas las opciones disponibles
        $('#perfil_financiamiento_id option').each(function(index) {

        });
        
        // Inicializar Select2 solo para clientes
        $('#cliente_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Seleccionar cliente...'
        });
        

    }
    
    function inicializarValoresIniciales() {

        
        // Establecer precio inicial del lote
        const precioLote = loteData.precio_total;
        $('#precio_lista').val(precioLote);
        $('#precio_venta_final').val(precioLote);
        
        // Total a pagar se actualiza cuando se seleccione plan y tipo de venta
        $('#total_a_pagar_display').val('$0.00');
        $('#total_a_pagar_descripcion').text('Seleccione plan financiero');
    }
    
    function configurarEventos() {

        
        // DIAGNÓSTICO: Verificar si podemos acceder al select antes de asignar eventos
        const selectPlanes = $('#perfil_financiamiento_id');




        
        // EVENTO: Cambio de plan financiero CON LOGGING EXTENSIVO Y FORZADO
        selectPlanes.on('change', function() {
            const planId = $(this).val();




            
            // SINCRONIZAR CON HIDDEN INPUT
            $('#perfil_financiamiento_id_hidden').val(planId);

            
            // FORZAR ACTUALIZACIÓN DEL VALOR (fix para sincronización)
            if (planId && planId !== '') {

                cargarPlanFinanciero(planId);
                
                // VERIFICACIÓN POST-CARGA
                setTimeout(() => {




                }, 100);
            } else {
                planActual = null;

            }
        });
        
        // AGREGAR EVENTOS ADICIONALES PARA DEPURACIÓN
        selectPlanes.on('click', function() {

        });
        
        selectPlanes.on('focus', function() {

        });
        
        selectPlanes.on('input', function() {

        });
        
        // EVENTO: Cambio de tipo de venta CON VERIFICACIÓN MEJORADA
        $('#tipo_venta').on('change', function() {
            const tipo = $(this).val();



            
            if (tipo === 'financiado' && !planActual) {

                
                // DIAGNÓSTICO COMPLETO DEL PROBLEMA
                diagnosticarProblemaSeleccionPlan();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Plan Requerido',
                        text: 'Debe seleccionar un plan financiero primero',
                        confirmButtonColor: '#007bff'
                    });
                } else {
                    alert('Debe seleccionar un plan financiero primero');
                }
                
                $(this).val('');
                return;
            }
            
            procesarTipoVenta(tipo);
        });
        
        // VERIFICAR QUE LOS EVENTOS SE REGISTRARON
        setTimeout(function() {

            const eventos = $._data(selectPlanes[0], 'events');


        }, 100);
        
        // Evento para generar simulación
        $('#generarSimulacion').on('click', function() {
            mostrarSimulacionAmortizacion();
        });
        

    }
    
    function cargarPlanFinanciero(planId) {

        
        const option = $(`#perfil_financiamiento_id option[value="${planId}"]`);



        
        if (option.length === 0) {

            return;
        }
        
        // Crear objeto del plan desde los data attributes
        planActual = {
            id: planId,
            nombre: option.text(),
            permite_apartado: option.data('permite-apartado') === 1,
            tipo_anticipo: option.data('tipo-anticipo') || 'porcentaje',
            porcentaje_anticipo: parseFloat(option.data('porcentaje-anticipo')) || 0,
            anticipo_fijo: parseFloat(option.data('anticipo-fijo')) || 0,
            enganche_minimo: parseFloat(option.data('enganche-minimo')) || 0,
            apartado_minimo: parseFloat(option.data('apartado-minimo')) || 0,
            plazo_liquidar_enganche: parseInt(option.data('plazo-liquidar-enganche')) || 10,
            meses_sin_intereses: parseInt(option.data('meses-sin-intereses')) || 0,
            meses_con_intereses: parseInt(option.data('meses-con-intereses')) || 0,
            porcentaje_interes_anual: parseFloat(option.data('porcentaje-interes-anual')) || 0,
            promocion_cero_enganche: option.data('promocion-cero-enganche') === 1,
            tipo_comision: option.data('tipo-comision') || 'porcentaje',
            porcentaje_comision: parseFloat(option.data('porcentaje-comision')) || 0,
            comision_fija: parseFloat(option.data('comision-fija')) || 0,
            mensualidades_comision: parseInt(option.data('mensualidades-comision')) || 2,
            dias_anticipo: parseInt(option.data('dias-anticipo')) || 30,
            tipo_financiamiento: option.data('tipo-financiamiento') || 'msi'
        };
        


        
        // Actualizar UI
        actualizarUIDelPlan();
    }
    
    function actualizarUIDelPlan() {
        if (!planActual) return;
        

        
        // 1. CALCULAR MONTOS BASICOS
        const precioLote = loteData.precio_total;
        const descuento = parseFloat($('#descuento_aplicado').val()) || 0;
        const precioFinal = precioLote - descuento;
        
        // 2. CALCULAR ENGANCHE/ANTICIPO
        let montoEnganche = 0;
        
        // Para planes de cero enganche, el enganche es siempre 0
        if (planActual.promocion_cero_enganche) {
            montoEnganche = 0;
        } else {
            if (planActual.tipo_anticipo === 'fijo') {
                montoEnganche = planActual.anticipo_fijo;
            } else {
                montoEnganche = precioFinal * (planActual.porcentaje_anticipo / 100);
            }
            
            // Aplicar mínimo si existe
            if (planActual.enganche_minimo > 0 && montoEnganche < planActual.enganche_minimo) {
                montoEnganche = planActual.enganche_minimo;
            }
        }
        
        // 3. CALCULAR MONTO A FINANCIAR
        const montoFinanciar = precioFinal - montoEnganche;
        
        // 4. ELIMINAR LÓGICA DE APARTADOS (vista dedicada separada)
        
        // 5. ACTUALIZAR CAMPOS DE LA UI
        $('#precio_venta_final').val(precioFinal); // Para compatibilidad backend
        
        // El "Total a Pagar" se actualiza después según el tipo de venta seleccionado
        actualizarTotalAPagar();
        
        // 6. MOSTRAR INFORMACIÓN DEL FINANCIAMIENTO
        actualizarInfoFinanciamiento(montoEnganche, montoFinanciar);
        
        // 7. CALCULAR COMISIONES DEL VENDEDOR
        const datosComision = calcularComisionVendedor(precioFinal);
        
        // 8. GENERAR TABLA DE AMORTIZACIÓN
        if (planActual.meses_sin_intereses > 0 || planActual.meses_con_intereses > 0) {
            generarTablaAmortizacion(montoFinanciar, montoEnganche);
        }
        
        // 9. MOSTRAR/OCULTAR SECCIONES RELEVANTES
        // Apartados se manejan en vista separada
        $('#apartadoConfig').addClass('d-none');
        $('#plazosConfig').removeClass('d-none');
    }
    
    function procesarTipoVenta(tipo) {

        
        if (tipo === 'contado') {
            // Lógica para venta de contado

            $('#plazosConfig').addClass('d-none');
        } else if (tipo === 'financiado') {
            // Lógica para venta financiada

            if (planActual) {
                $('#plazosConfig').removeClass('d-none');
                actualizarUIDelPlan(); // Recalcular con el tipo de venta
            }
        }
        
        // Actualizar "Total a Pagar" según el tipo de venta
        actualizarTotalAPagar();
    }
    
    // =====================================
    // FUNCIONES DE CÁLCULOS FINANCIEROS
    // =====================================
    
    function calcularComisionVendedor(precioVenta) {
        if (!planActual) return { total: 0, tipo: 'ninguna' };
        

        
        let montoComision = 0;
        let tipoCalculo = '';
        let detalles = {};
        
        // 1. DETERMINAR TIPO DE COMISIÓN
        if (planActual.tipo_comision === 'fijo') {
            // Comisión fija
            montoComision = planActual.comision_fija;
            tipoCalculo = 'fijo';
            detalles = {
                monto_fijo: planActual.comision_fija,
                descripcion: `Comisión fija de ${formatCurrency(planActual.comision_fija)}`
            };
        } else {
            // Comisión por porcentaje
            montoComision = precioVenta * (planActual.porcentaje_comision / 100);
            tipoCalculo = 'porcentaje';
            detalles = {
                porcentaje: planActual.porcentaje_comision,
                base_calculo: precioVenta,
                descripcion: `${planActual.porcentaje_comision}% sobre ${formatCurrency(precioVenta)}`
            };
        }
        
        // 2. MANEJAR CASO ESPECIAL: CERO ENGANCHE (COMISIÓN POR MENSUALIDADES)
        if (planActual.promocion_cero_enganche) {

            
            // Para cero enganche, la comisión se calcula sobre la mensualidad
            const pagoMensual = window.datosAmortizacion ? 
                window.datosAmortizacion.resumen.pago_promedio : 
                (precioVenta / (planActual.meses_sin_intereses + planActual.meses_con_intereses));
            
            const mesesComision = planActual.mensualidades_comision || 2;
            
            if (planActual.tipo_comision === 'fijo') {
                // Comisión fija multiplicada por los meses
                montoComision = planActual.comision_fija * mesesComision;
                detalles = {
                    ...detalles,
                    meses_comision: mesesComision,
                    descripcion: `Comisión fija de ${formatCurrency(planActual.comision_fija)} x ${mesesComision} meses`
                };
            } else {
                // Para cero enganche: 100% del pago mensual durante n mensualidades
                const comisionPorMes = pagoMensual; // 100% del pago mensual
                montoComision = comisionPorMes * mesesComision;
                detalles = {
                    ...detalles,
                    pago_mensual: pagoMensual,
                    comision_por_mes: comisionPorMes,
                    meses_comision: mesesComision,
                    descripcion: `100% del pago mensual (${formatCurrency(pagoMensual)}) x ${mesesComision} meses consecutivos`
                };
            }
            
            tipoCalculo = 'cero_enganche';
        }
        
        const resultadoComision = {
            total: montoComision,
            tipo: tipoCalculo,
            detalles: detalles,
            formateado: formatCurrency(montoComision)
        };
        
        // 3. MOSTRAR INFORMACIÓN DE COMISIÓN EN LA UI
        actualizarInfoComision(resultadoComision);
        

        
        return resultadoComision;
    }
    
    function actualizarInfoComision(comision) {
        // Usar el card existente de "Configuración de Comisiones"
        
        // 1. Actualizar tipo de comisión
        $('#tipo_comision').val(comision.tipo);
        $('#tipo_comision_display').val(comision.tipo.charAt(0).toUpperCase() + comision.tipo.slice(1));
        $('#tipo_comision_texto').text(comision.detalles.descripcion);
        
        // 2. Mostrar/ocultar campos según el tipo
        if (comision.tipo === 'fijo' || comision.tipo === 'cero_enganche') {
            $('#campo_porcentaje_comision').addClass('d-none');
            $('#campo_comision_fija').removeClass('d-none');
            $('#comision_fija').val(planActual.comision_fija);
            $('#comision_fija_display').val(formatCurrency(planActual.comision_fija));
        } else {
            $('#campo_porcentaje_comision').removeClass('d-none');
            $('#campo_comision_fija').addClass('d-none');
            $('#porcentaje_comision').val(planActual.porcentaje_comision);
            $('#porcentaje_comision_display').val(planActual.porcentaje_comision);
        }
        
        // 3. Actualizar comisión calculada
        $('#monto_comision_calculada').val(comision.total);
        $('#monto_comision_calculada_display').val(comision.formateado);
        
        // 4. Manejar promoción cero enganche
        if (planActual.promocion_cero_enganche) {
            $('#promocion_cero_enganche_aplicada').prop('checked', true);
            $('#promocion_cero_enganche_aplicada_hidden').val('1');
            $('#grupo_mensualidades_comision_venta').removeClass('d-none');
            $('#mensualidades_comision_venta').val(planActual.mensualidades_comision || 2);
            
            // Actualizar el monto de mensualidad para cero enganche
            const pagoMensual = parseFloat($('#pago_mensual').val()) || 0;
            $('#monto_mensualidad_comision').val(pagoMensual);
            $('#monto_mensualidad_comision_display').val(formatCurrency(pagoMensual));
        } else {
            $('#promocion_cero_enganche_aplicada').prop('checked', false);
            $('#promocion_cero_enganche_aplicada_hidden').val('0');
            $('#grupo_mensualidades_comision_venta').addClass('d-none');
            $('#monto_mensualidad_comision').val(0);
            $('#monto_mensualidad_comision_display').val('$0.00');
        }
        
        // 5. Actualizar resúmenes
        $('#comision_resumen').text(comision.detalles.descripcion);
        $('#comision_total_display').text(comision.formateado);
        

    }
    
    function actualizarTotalAPagar() {
        const tipoVenta = $('#tipo_venta').val();
        const precioFinal = parseFloat($('#precio_venta_final').val()) || 0;
        
        let totalAPagar = 0;
        let descripcion = '';
        
        if (!planActual) {
            $('#total_a_pagar_display').val('$0.00');
            $('#total_a_pagar_descripcion').text('Seleccione plan financiero');
            return;
        }
        
        if (tipoVenta === 'contado') {
            // Pago de contado: precio completo
            totalAPagar = precioFinal;
            descripcion = 'Pago único de contado';
        } else if (tipoVenta === 'financiado') {
            if (planActual.promocion_cero_enganche) {
                // Cero enganche: primera mensualidad
                const pagoMensual = window.datosAmortizacion ? 
                    window.datosAmortizacion.resumen.pago_promedio : 
                    (precioFinal / (planActual.meses_sin_intereses + planActual.meses_con_intereses));
                totalAPagar = pagoMensual;
                descripcion = 'Primera mensualidad (cero enganche)';
            } else {
                // Financiado normal: enganche
                if (planActual.tipo_anticipo === 'fijo') {
                    totalAPagar = planActual.anticipo_fijo;
                } else {
                    totalAPagar = precioFinal * (planActual.porcentaje_anticipo / 100);
                }
                // Aplicar mínimo si existe
                if (planActual.enganche_minimo > 0 && totalAPagar < planActual.enganche_minimo) {
                    totalAPagar = planActual.enganche_minimo;
                }
                descripcion = 'Enganche inicial';
            }
        } else {
            // Sin tipo de venta seleccionado
            totalAPagar = 0;
            descripcion = 'Seleccione tipo de venta';
        }
        
        // Actualizar UI
        $('#total_a_pagar').val(totalAPagar);
        $('#total_a_pagar_display').val(formatCurrency(totalAPagar));
        $('#total_a_pagar_descripcion').text(descripcion);
    }
    
    function actualizarInfoFinanciamiento(montoEnganche, montoFinanciar) {
        const totalMeses = planActual.meses_sin_intereses + planActual.meses_con_intereses;
        
        // Actualizar información del financiamiento
        let textoFinanciamiento = '';
        if (planActual.meses_sin_intereses > 0) {
            textoFinanciamiento += `${planActual.meses_sin_intereses} meses sin interés`;
        }
        if (planActual.meses_con_intereses > 0) {
            if (textoFinanciamiento) textoFinanciamiento += ' + ';
            textoFinanciamiento += `${planActual.meses_con_intereses} meses con interés`;
        }
        
        $('#financiamiento_texto').text(textoFinanciamiento);
        
        // Actualizar información del interés
        let textoInteres = 'Sin interés';
        if (planActual.porcentaje_interes_anual > 0) {
            textoInteres = `${planActual.porcentaje_interes_anual}% anual`;
        }
        $('#interes_aplicado_texto').text(textoInteres);
        
        // Actualizar rango de plazos
        $('#rangoPlazos').text(`Rango: 1 - ${totalMeses} meses`);
        
        // Actualizar campo de plazo
        $('#plazo_meses').attr('max', totalMeses);
        if (totalMeses > 0) {
            $('#plazo_meses').val(totalMeses);
        }
        
        // Actualizar tasa de interés
        $('#tasa_interes_anual').val(planActual.porcentaje_interes_anual);
        
        // Calcular día de pago basado en la fecha actual
        const fechaVenta = new Date($('#fecha_venta').val() || Date.now());
        $('#dia_pago_mensual').val(fechaVenta.getDate());
    }
    
    function generarTablaAmortizacion(montoFinanciar, montoEnganche) {
        if (!planActual || montoFinanciar <= 0) return;
        

        
        const mesesSinInteres = planActual.meses_sin_intereses;
        const mesesConInteres = planActual.meses_con_intereses;
        const tasaAnual = planActual.porcentaje_interes_anual;
        const tasaMensual = tasaAnual / 100 / 12;
        
        let saldoPendiente = montoFinanciar;
        let pagos = [];
        let totalMeses = mesesSinInteres + mesesConInteres;
        
        // FASE 1: MESES SIN INTERÉS
        let cuotaMensualSinInteres = 0;
        if (mesesSinInteres > 0) {
            cuotaMensualSinInteres = montoFinanciar / mesesSinInteres; // Distribuir solo en los meses sin interés
        }
        
        for (let mes = 1; mes <= mesesSinInteres; mes++) {
            const pagoCapital = cuotaMensualSinInteres;
            const pagoInteres = 0;
            const pagoTotal = pagoCapital + pagoInteres;
            
            saldoPendiente -= pagoCapital;
            
            pagos.push({
                numero: mes,
                saldo_inicial: saldoPendiente + pagoCapital,
                pago_capital: pagoCapital,
                pago_interes: pagoInteres,
                pago_total: pagoTotal,
                saldo_final: saldoPendiente,
                tipo: 'sin_interes'
            });
        }
        
        // FASE 2: MESES CON INTERÉS
        if (mesesConInteres > 0 && saldoPendiente > 0) {
            // Calcular cuota fija con interés para el saldo restante
            let cuotaConInteres = 0;
            if (tasaMensual > 0) {
                cuotaConInteres = saldoPendiente * (tasaMensual * Math.pow(1 + tasaMensual, mesesConInteres)) / 
                                 (Math.pow(1 + tasaMensual, mesesConInteres) - 1);
            } else {
                cuotaConInteres = saldoPendiente / mesesConInteres;
            }
            
            for (let mes = 1; mes <= mesesConInteres; mes++) {
                const numeroMes = mesesSinInteres + mes;
                const saldoInicial = saldoPendiente;
                const pagoInteres = saldoPendiente * tasaMensual;
                const pagoCapital = cuotaConInteres - pagoInteres;
                const pagoTotal = cuotaConInteres;
                
                saldoPendiente -= pagoCapital;
                
                // Ajuste para último pago
                if (mes === mesesConInteres && saldoPendiente < 1) {
                    saldoPendiente = 0;
                }
                
                pagos.push({
                    numero: numeroMes,
                    saldo_inicial: saldoInicial,
                    pago_capital: pagoCapital,
                    pago_interes: pagoInteres,
                    pago_total: pagoTotal,
                    saldo_final: saldoPendiente,
                    tipo: 'con_interes'
                });
            }
        }
        
        // Calcular pago mensual correcto para mostrar en UI
        let pagoMensualReal = 0;
        
        if (pagos.length > 0) {
            // Para planes de cero enganche sin interés, usar el pago de los primeros meses
            if (mesesSinInteres > 0 && mesesConInteres === 0) {
                // Plan completamente sin interés: usar pago constante de meses sin interés
                pagoMensualReal = cuotaMensualSinInteres;
            } else if (mesesSinInteres > 0 && mesesConInteres > 0) {
                // Plan mixto: mostrar el pago más común (generalmente de la fase con interés)
                pagoMensualReal = pagos.find(p => p.tipo === 'con_interes')?.pago_total || cuotaMensualSinInteres;
            } else {
                // Solo meses con interés: usar el pago fijo
                pagoMensualReal = pagos[0].pago_total;
            }
        }
        
        $('#pago_mensual').val(pagoMensualReal);
        $('#pago_mensual_display').val(formatCurrency(pagoMensualReal));
        
        // Actualizar monto mensualidad comisión si es cero enganche
        if (planActual && planActual.promocion_cero_enganche) {
            $('#monto_mensualidad_comision').val(pagoMensualReal);
            $('#monto_mensualidad_comision_display').val(formatCurrency(pagoMensualReal));
        }
        
        // Actualizar progreso visual
        actualizarProgressoPlazos(mesesSinInteres, mesesConInteres);
        
        // Guardar datos para el modal
        window.datosAmortizacion = {
            pagos: pagos,
            resumen: {
                monto_financiar: montoFinanciar,
                monto_enganche: montoEnganche,
                total_meses: totalMeses,
                tasa_anual: tasaAnual,
                pago_promedio: pagoMensualReal
            },
            plan: planActual
        };
        

    }
    
    function actualizarProgressoPlazos(mesesSinInteres, mesesConInteres) {
        const totalMeses = mesesSinInteres + mesesConInteres;
        
        if (totalMeses > 0) {
            const porcentajeSinInteres = (mesesSinInteres / totalMeses) * 100;
            const porcentajeConInteres = (mesesConInteres / totalMeses) * 100;
            
            $('#progress_sin_interes').css('width', porcentajeSinInteres + '%');
            $('#progress_con_interes').css('width', porcentajeConInteres + '%');
            
            $('#status_plazo').html(`
                <span class="badge badge-success">${mesesSinInteres} MSI</span>
                <span class="badge badge-warning">${mesesConInteres} MCI</span>
            `);
        }
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        }).format(amount || 0);
    }
    
    // =====================================
    // EVENTOS ADICIONALES
    // =====================================
    
    // Recalcular cuando cambie el descuento
    $('#descuento_aplicado').on('input change', function() {
        if (planActual) {
            actualizarUIDelPlan();
        }
    });
    
    // Recalcular cuando cambie el plazo
    $('#plazo_meses').on('input change', function() {
        if (planActual) {
            const plazoSeleccionado = parseInt($(this).val());
            actualizarCalculosConPlazo(plazoSeleccionado);
        }
    });
    
    function actualizarCalculosConPlazo(plazoMeses) {
        // Actualizar cálculos basado en el plazo seleccionado específico
        const precioFinal = loteData.precio_total - (parseFloat($('#descuento_aplicado').val()) || 0);
        let montoEnganche = 0;
        
        if (planActual.tipo_anticipo === 'fijo') {
            montoEnganche = planActual.anticipo_fijo;
        } else {
            montoEnganche = precioFinal * (planActual.porcentaje_anticipo / 100);
        }
        
        const montoFinanciar = precioFinal - montoEnganche;
        
        // Generar tabla con el plazo específico
        generarTablaAmortizacionPersonalizada(montoFinanciar, plazoMeses);
    }
    
    function generarTablaAmortizacionPersonalizada(montoFinanciar, plazoMeses) {
        // Similar a generarTablaAmortizacion pero con plazo personalizado
        // Esta función permite al usuario cambiar el plazo dentro del rango permitido
        
        if (!planActual || montoFinanciar <= 0) return;
        
        // Para planes de cero enganche, distribuir el monto total en el plazo seleccionado
        const mesesSinInteres = Math.min(plazoMeses, planActual.meses_sin_intereses);
        const mesesConInteres = Math.max(0, plazoMeses - planActual.meses_sin_intereses);
        
        // Calcular pago mensual real
        let pagoMensualReal = 0;
        if (mesesSinInteres > 0 && mesesConInteres === 0) {
            // Plan completamente sin interés
            pagoMensualReal = montoFinanciar / mesesSinInteres;
        } else {
            // Plan mixto o con interés: usar función completa
            generarTablaAmortizacion(montoFinanciar, 0);
            return;
        }
        
        // Actualizar UI con el pago mensual correcto
        $('#pago_mensual').val(pagoMensualReal);
        $('#pago_mensual_display').val(formatCurrency(pagoMensualReal));
        
        // Actualizar monto mensualidad comisión si es cero enganche
        if (planActual && planActual.promocion_cero_enganche) {
            $('#monto_mensualidad_comision').val(pagoMensualReal);
            $('#monto_mensualidad_comision_display').val(formatCurrency(pagoMensualReal));
        }
        
        // Actualizar progreso visual
        actualizarProgressoPlazos(mesesSinInteres, mesesConInteres);
    }
    
    function mostrarSimulacionAmortizacion() {
        if (!window.datosAmortizacion) {
            Swal.fire({
                icon: 'warning',
                title: 'Simulación no disponible',
                text: 'Primero seleccione un plan financiero'
            });
            return;
        }
        

        
        // Mostrar modal (implementar según el modal disponible)
        if (typeof $('#modalSimulacion').modal === 'function') {
            // Llenar el modal con los datos
            llenarModalSimulacion();
            $('#modalSimulacion').modal('show');
        } else {
            // Alternativa: mostrar en consola o nueva ventana

            
            // Crear resumen para mostrar
            const resumen = window.datosAmortizacion.resumen;
            const alertContent = `
                <div class="text-left">
                    <h5>📊 Resumen Financiero</h5>
                    <table class="table table-sm">
                        <tr><td><strong>Monto a Financiar:</strong></td><td>${formatCurrency(resumen.monto_financiar)}</td></tr>
                        <tr><td><strong>Enganche:</strong></td><td>${formatCurrency(resumen.monto_enganche)}</td></tr>
                        <tr><td><strong>Total de Meses:</strong></td><td>${resumen.total_meses}</td></tr>
                        <tr><td><strong>Tasa Anual:</strong></td><td>${resumen.tasa_anual}%</td></tr>
                        <tr><td><strong>Pago Promedio:</strong></td><td>${formatCurrency(resumen.pago_promedio)}</td></tr>
                    </table>
                    <small class="text-muted">Consulte la consola del navegador para ver la tabla completa de pagos.</small>
                </div>
            `;
            
            Swal.fire({
                title: 'Simulación de Financiamiento',
                html: alertContent,
                width: '600px',
                showCloseButton: true,
                confirmButtonText: 'Entendido'
            });
        }
    }
    
    function llenarModalSimulacion() {
        if (!window.datosAmortizacion) return;
        

        
        const datos = window.datosAmortizacion;
        const resumen = datos.resumen;
        const pagos = datos.pagos;
        
        // Llenar encabezado del modal
        $('#modalLoteClave').text(loteData.clave);
        
        // Llenar resumen ejecutivo
        $('#resumen-monto-financiar').text(formatCurrency(resumen.monto_financiar));
        $('#resumen-pago-mensual').text(formatCurrency(resumen.pago_promedio));
        $('#resumen-plazo-meses').text(resumen.total_meses + ' meses');
        $('#resumen-tasa-anual').text(resumen.tasa_anual + '%');
        
        // Generar encabezado corporativo
        $('#encabezadoCorporativo').html(`
            <div class="mb-3 p-3 bg-light border">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-1 text-primary">${loteData.empresa_nombre}</h6>
                        <small class="text-muted">Simulación de Financiamiento</small>
                    </div>
                    <div class="col-md-6 text-right">
                        <small class="text-muted">Lote: ${loteData.clave}</small><br>
                        <small class="text-muted">Fecha: ${new Date().toLocaleDateString('es-MX')}</small>
                    </div>
                </div>
            </div>
        `);
        
        // Llenar tabla de amortización
        let tbody = '';
        let totalIntereses = 0;
        let totalCapital = 0;
        let totalPagos = 0;
        
        pagos.forEach((pago, index) => {
            const fechaPago = new Date();
            fechaPago.setMonth(fechaPago.getMonth() + pago.numero);
            
            const tipoClass = pago.tipo === 'sin_interes' ? 'table-success' : 'table-warning';
            const tipoBadge = pago.tipo === 'sin_interes' ? 
                '<span class="badge badge-success badge-sm">MSI</span>' : 
                '<span class="badge badge-warning badge-sm">MCI</span>';
            
            tbody += `
                <tr class="${tipoClass}">
                    <td class="text-center">${pago.numero} ${tipoBadge}</td>
                    <td class="text-center">${fechaPago.toLocaleDateString('es-MX')}</td>
                    <td class="text-right">${formatCurrency(pago.saldo_inicial)}</td>
                    <td class="text-right"><strong>${formatCurrency(pago.pago_total)}</strong></td>
                    <td class="text-right">${formatCurrency(pago.pago_interes)}</td>
                    <td class="text-right">${formatCurrency(pago.pago_capital)}</td>
                    <td class="text-right">${formatCurrency(pago.saldo_final)}</td>
                </tr>
            `;
            
            totalIntereses += pago.pago_interes;
            totalCapital += pago.pago_capital;
            totalPagos += pago.pago_total;
        });
        
        $('#cuerpoTablaAmortizacion').html(tbody);
        
        // Llenar totales
        $('#totalIntereses').text(formatCurrency(totalIntereses));
        $('#totalCapital').text(formatCurrency(totalCapital));
        $('#totalPagado').text(formatCurrency(totalPagos));
        

    }
    
    // =====================================
    // FUNCIÓN DE IMPRESIÓN
    // =====================================
    
    window.imprimirSimulacion = function() {
        if (!window.datosAmortizacion) {
            Swal.fire({
                icon: 'warning',
                title: 'No hay datos',
                text: 'No se pueden imprimir datos que no existen'
            });
            return;
        }
        

        
        // Preparar datos para el template de impresión
        const datos = window.datosAmortizacion;
        const pagos = datos.pagos;
        const resumen = datos.resumen;
        
        // Crear formulario para enviar datos al controlador de impresión
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= site_url('/admin/ventas/imprimir-amortizacion') ?>';
        form.target = '_blank';
        
        // Preparar datos para el template
        const datosImpresion = {
            // Datos de la empresa
            empresa_nombre: loteData.empresa_nombre || 'Anvar Inmobiliaria',
            empresa_telefono: '(555) 123-4567', // TODO: Obtener de configuración
            empresa_email: 'ventas@anvar.com', // TODO: Obtener de configuración
            
            // Datos del inmueble
            proyecto_nombre: loteData.proyecto_nombre,
            lote_clave: loteData.clave,
            lote_numero: '', // Si existe
            manzana_nombre: loteData.manzana_nombre,
            lote_area: loteData.area,
            vendedor_nombre: $('#vendedor_id option:selected').text() || 'Por asignar',
            
            // Datos financieros
            cliente_nombre: $('#cliente_id option:selected').text() || 'Por seleccionar',
            precio_formateado: formatCurrency(loteData.precio_total),
            enganche_formateado: formatCurrency(resumen.monto_enganche),
            financiar_formateado: formatCurrency(resumen.monto_financiar),
            plazo_meses: resumen.total_meses,
            pago_formateado: formatCurrency(resumen.pago_promedio),
            
            // Totales
            total_pagos: formatCurrency(pagos.reduce((sum, p) => sum + p.pago_total, 0)),
            total_intereses: formatCurrency(pagos.reduce((sum, p) => sum + p.pago_interes, 0)),
            total_capital: formatCurrency(pagos.reduce((sum, p) => sum + p.pago_capital, 0))
        };
        
        // Crear campos hidden con los datos
        const inputDatos = document.createElement('input');
        inputDatos.type = 'hidden';
        inputDatos.name = 'datos';
        inputDatos.value = JSON.stringify(datosImpresion);
        form.appendChild(inputDatos);
        
        // Preparar pagos para la tabla
        const pagosParaImpresion = pagos.map((pago, index) => {
            const fechaPago = new Date();
            fechaPago.setMonth(fechaPago.getMonth() + pago.numero);
            
            return {
                periodo: pago.numero,
                fecha: fechaPago.toLocaleDateString('es-MX'),
                saldo_inicial: formatCurrency(pago.saldo_inicial),
                pago: formatCurrency(pago.pago_total),
                interes: formatCurrency(pago.pago_interes),
                capital: formatCurrency(pago.pago_capital),
                saldo_final: formatCurrency(pago.saldo_final)
            };
        });
        
        const inputPagos = document.createElement('input');
        inputPagos.type = 'hidden';
        inputPagos.name = 'pagos';
        inputPagos.value = JSON.stringify(pagosParaImpresion);
        form.appendChild(inputPagos);
        
        // Agregar al DOM y enviar
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        

    };
    
    // FUNCIONES GLOBALES PARA TEST MANUAL DESDE CONSOLA
    window.testPlanesDisponibles = function() {

        $('#perfil_financiamiento_id option').each(function(index) {

        });
    };
    
    window.testSeleccionarPlan = function(planId) {

        $('#perfil_financiamiento_id').val(planId);

        $('#perfil_financiamiento_id').trigger('change');

    };
    
    window.testEstadoPlanActual = function() {



    };
    
    window.testEventosRegistrados = function() {

        const eventos = $._data($('#perfil_financiamiento_id')[0], 'events');


    };
    
    window.testCalculosFinancieros = function() {

        if (!planActual) {

            return;
        }
        
        actualizarUIDelPlan();
        actualizarTotalAPagar();
        
        if (window.datosAmortizacion) {

 // Mostrar primeros 5 pagos

        }
    };
    
    window.testSimulacion = function() {

        mostrarSimulacionAmortizacion();
    };
    
    window.testCambiarDescuento = function(descuento) {

        $('#descuento_aplicado').val(descuento).trigger('change');
    };
    
    window.testComisiones = function() {

        if (!planActual) {

            return;
        }
        
        const precioFinal = loteData.precio_total - (parseFloat($('#descuento_aplicado').val()) || 0);
        const comision = calcularComisionVendedor(precioFinal);
    };
    











    
    // FUNCIÓN DE DIAGNÓSTICO COMPLETO
    function diagnosticarProblemaSeleccionPlan() {


        
        // 1. Estado del DOM

        const selectElement = document.getElementById('perfil_financiamiento_id');




        
        // 2. Estado de jQuery

        const $select = $('#perfil_financiamiento_id');




        
        // 3. Opciones disponibles

        $select.find('option').each(function(index) {
            const isSelected = $(this).is(':selected');

        });
        
        // 4. Variable planActual





        
        // 5. Eventos registrados

        const eventos = $._data($select[0], 'events');

        if (eventos && eventos.change) {

            eventos.change.forEach((handler, index) => {

            });
        }
        
        // 6. Intentar cargar manualmente

        const valorActual = $select.val();
        if (valorActual && valorActual !== '') {

            cargarPlanFinanciero(valorActual);
            
            setTimeout(() => {

            }, 100);
        } else {

        }
        
        // 7. Estado del formulario





        


    }
    
    // Exponer función de diagnóstico globalmente
    window.diagnosticarProblemaSeleccionPlan = diagnosticarProblemaSeleccionPlan;
});

// Datos del lote disponibles globalmente
const loteData = {
    id: <?= $lote->id ?>,
    precio_total: <?= $lote->precio_total ?>,
    area: <?= $lote->area ?>,
    clave: '<?= esc($lote->clave) ?>',
    tipo_nombre: '<?= esc($lote->tipo_nombre ?? '') ?>',
    empresa_nombre: '<?= esc($lote->empresa_nombre ?? '') ?>',
    proyecto_nombre: '<?= esc($lote->proyecto_nombre ?? 'N/A') ?>',
    manzana_nombre: '<?= esc($lote->manzana_nombre ?? 'N/A') ?>'
};
</script>
<?= $this->endSection() ?>
