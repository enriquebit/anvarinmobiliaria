<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Mis Pagos<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Centro de Pagos<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Pagos</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Alertas importantes -->
<?php if (!empty($alertas_pagos)): ?>
<div class="row">
    <div class="col-12">
        <?php foreach ($alertas_pagos as $alerta): ?>
        <div class="alert alert-<?= $alerta['tipo'] ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-<?= $alerta['icono'] ?>"></i> <?= $alerta['titulo'] ?></h5>
            <?= $alerta['mensaje'] ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Cards de resumen -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= $resumen_pagos['mensualidades_vencidas'] ?? 0 ?></h3>
                <p>Pagos Vencidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="#vencidos" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>$<?= number_format($resumen_pagos['monto_total_vencido'] ?? 0, 0) ?></h3>
                <p>Monto Vencido</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <a href="#vencidos" class="small-box-footer">Pagar ahora <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $resumen_pagos['mensualidades_proximas'] ?? 0 ?></h3>
                <p>Próximos 30 días</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="#proximos" class="small-box-footer">Ver calendario <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $resumen_pagos['pagos_realizados_mes'] ?? 0 ?></h3>
                <p>Pagos este mes</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="<?= site_url('/cliente/estado-cuenta/historialPagos') ?>" class="small-box-footer">Ver historial <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Opciones de pago rápido -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>
                    Opciones de Pago Rápido
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-success">
                            <span class="info-box-icon">
                                <i class="fas fa-credit-card"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pago en Línea</span>
                                <span class="info-box-number">Tarjeta / Transferencia</span>
                                <button class="btn btn-outline-light btn-sm mt-2" onclick="iniciarPagoLinea()">
                                    <i class="fas fa-arrow-right"></i> Pagar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box bg-info">
                            <span class="info-box-icon">
                                <i class="fas fa-university"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Depósito Bancario</span>
                                <span class="info-box-number">Ver Cuentas</span>
                                <button class="btn btn-outline-light btn-sm mt-2" data-toggle="modal" data-target="#modal-cuentas-bancarias">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon">
                                <i class="fas fa-bell"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Reportar Pago</span>
                                <span class="info-box-number">Offline</span>
                                <a href="<?= site_url('/cliente/pagos/reportarPago') ?>" class="btn btn-outline-light btn-sm mt-2">
                                    <i class="fas fa-plus"></i> Reportar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mensualidades Vencidas -->
<?php if (!empty($mensualidades_vencidas)): ?>
<div class="row" id="vencidos">
    <div class="col-12">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Pagos Vencidos - Requieren Atención Inmediata
                </h3>
                <div class="card-tools">
                    <?php if (count($mensualidades_vencidas) > 1): ?>
                    <button class="btn btn-tool btn-danger" onclick="seleccionarTodosVencidos()">
                        <i class="fas fa-check-square"></i> Seleccionar Todos
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <form id="form-pago-multiple" method="post" action="<?= site_url('/cliente/pagos/procesarMultiple') ?>">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <?php if (count($mensualidades_vencidas) > 1): ?>
                                    <th width="40">
                                        <input type="checkbox" id="check-all-vencidos">
                                    </th>
                                    <?php endif; ?>
                                    <th>Propiedad</th>
                                    <th>Mensualidad</th>
                                    <th>Vencimiento</th>
                                    <th>Días Atraso</th>
                                    <th>Monto</th>
                                    <th>Mora</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mensualidades_vencidas as $mensualidad): ?>
                                <tr>
                                    <?php if (count($mensualidades_vencidas) > 1): ?>
                                    <td>
                                        <input type="checkbox" name="mensualidades[]" 
                                               value="<?= $mensualidad->id ?>" 
                                               class="check-mensualidad"
                                               data-monto="<?= $mensualidad->monto_total + $mensualidad->interes_moratorio ?>">
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <strong><?= esc($mensualidad->lote_clave ?? 'N/A') ?></strong><br>
                                        <small class="text-muted"><?= esc($mensualidad->folio_venta) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">
                                            #<?= $mensualidad->numero_pago ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                    </td>
                                    <td>
                                        <span class="text-danger font-weight-bold">
                                            <?= $mensualidad->dias_atraso ?> días
                                        </span>
                                    </td>
                                    <td>$<?= number_format($mensualidad->monto_total, 0) ?></td>
                                    <td>
                                        <span class="text-warning">
                                            $<?= number_format($mensualidad->interes_moratorio, 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-danger">
                                            $<?= number_format($mensualidad->monto_total + $mensualidad->interes_moratorio, 0) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('/cliente/pagos/mensualidad/' . $mensualidad->id) ?>" 
                                           class="btn btn-danger btn-sm">
                                            <i class="fas fa-dollar-sign"></i> Pagar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (count($mensualidades_vencidas) > 1): ?>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-right">Total Seleccionado:</th>
                                    <th colspan="2">
                                        <span id="total-seleccionado" class="text-danger font-weight-bold">$0</span>
                                    </th>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <?php if (count($mensualidades_vencidas) > 1): ?>
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-danger btn-lg" id="btn-pagar-seleccionados" style="display: none;">
                                <i class="fas fa-credit-card"></i> Pagar Seleccionados
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Próximas Mensualidades -->
<div class="row" id="proximos">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Próximas Mensualidades
                </h3>
                <div class="card-tools">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-primary active" onclick="filtrarProximas('todas')">Todas</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarProximas('7dias')">7 días</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarProximas('15dias')">15 días</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarProximas('30dias')">30 días</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($mensualidades_proximas)): ?>
                <div class="table-responsive">
                    <table class="table table-sm" id="tabla-proximas-mensualidades">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Mensualidad</th>
                                <th>Vencimiento</th>
                                <th>Días</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mensualidades_proximas as $mensualidad): ?>
                            <?php 
                            $dias_para_vencer = (strtotime($mensualidad->fecha_vencimiento) - time()) / (60 * 60 * 24);
                            ?>
                            <tr data-dias="<?= floor($dias_para_vencer) ?>">
                                <td>
                                    <strong><?= esc($mensualidad->lote_clave ?? 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= esc($mensualidad->folio_venta) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        #<?= $mensualidad->numero_pago ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                </td>
                                <td>
                                    <?php if ($dias_para_vencer <= 7): ?>
                                        <span class="badge badge-warning">
                                            <?= floor($dias_para_vencer) ?> días
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info">
                                            <?= floor($dias_para_vencer) ?> días
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>$<?= number_format($mensualidad->monto_total, 0) ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">Pendiente</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('/cliente/pagos/mensualidad/' . $mensualidad->id) ?>" 
                                           class="btn btn-success">
                                            <i class="fas fa-dollar-sign"></i> Pagar
                                        </a>
                                        <button type="button" class="btn btn-info" 
                                                onclick="verDetalleMensualidad(<?= $mensualidad->id ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    No tienes mensualidades próximas a vencer.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Métodos de Pago Disponibles -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-credit-card mr-2"></i>
                    Métodos de Pago Disponibles
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Pago en línea -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success">
                                <h5 class="mb-0">
                                    <i class="fas fa-globe mr-2"></i>
                                    Pago en Línea (Recomendado)
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success mr-2"></i>Proceso inmediato</li>
                                    <li><i class="fas fa-check text-success mr-2"></i>Comprobante instantáneo</li>
                                    <li><i class="fas fa-check text-success mr-2"></i>Seguro y confiable</li>
                                    <li><i class="fas fa-check text-success mr-2"></i>Disponible 24/7</li>
                                </ul>
                                <h6>Métodos aceptados:</h6>
                                <div class="row mb-3">
                                    <div class="col-3"><i class="fab fa-cc-visa fa-2x text-primary"></i></div>
                                    <div class="col-3"><i class="fab fa-cc-mastercard fa-2x text-danger"></i></div>
                                    <div class="col-3"><i class="fab fa-cc-amex fa-2x text-info"></i></div>
                                    <div class="col-3"><i class="fas fa-university fa-2x text-secondary"></i></div>
                                </div>
                                <button class="btn btn-success btn-block" onclick="iniciarPagoLinea()">
                                    <i class="fas fa-lock mr-2"></i>Pagar de Forma Segura
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Depósito bancario -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info">
                                <h5 class="mb-0">
                                    <i class="fas fa-university mr-2"></i>
                                    Depósito o Transferencia Bancaria
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-info mr-2"></i>Sin comisiones bancarias</li>
                                    <li><i class="fas fa-check text-info mr-2"></i>Desde tu banco de preferencia</li>
                                    <li><i class="fas fa-check text-info mr-2"></i>SPEI disponible</li>
                                    <li><i class="fas fa-check text-info mr-2"></i>Confirmación en 24-48 hrs</li>
                                </ul>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Importante:</strong> Después de realizar tu depósito, repórtalo para agilizar la aplicación.
                                </div>
                                <div class="btn-group btn-group-justified w-100">
                                    <button class="btn btn-info" data-toggle="modal" data-target="#modal-cuentas-bancarias">
                                        <i class="fas fa-list mr-2"></i>Ver Cuentas
                                    </button>
                                    <a href="<?= site_url('/cliente/pagos/reportarPago') ?>" class="btn btn-warning">
                                        <i class="fas fa-bell mr-2"></i>Reportar Pago
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cuentas Bancarias -->
<div class="modal fade" id="modal-cuentas-bancarias" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-university mr-2"></i>
                    Cuentas Bancarias para Depósito
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!empty($cuentas_bancarias)): ?>
                    <?php foreach ($cuentas_bancarias as $cuenta): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5><?= esc($cuenta->banco) ?></h5>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="120">Titular:</th>
                                            <td><?= esc($cuenta->titular) ?></td>
                                        </tr>
                                        <tr>
                                            <th>No. Cuenta:</th>
                                            <td>
                                                <code class="cuenta-numero"><?= esc($cuenta->numero_cuenta) ?></code>
                                                <button class="btn btn-xs btn-secondary ml-2" 
                                                        onclick="copiarAlPortapapeles('<?= esc($cuenta->numero_cuenta) ?>')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>CLABE:</th>
                                            <td>
                                                <code class="cuenta-clabe"><?= esc($cuenta->clabe) ?></code>
                                                <button class="btn btn-xs btn-secondary ml-2" 
                                                        onclick="copiarAlPortapapeles('<?= esc($cuenta->clabe) ?>')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Sucursal:</th>
                                            <td><?= esc($cuenta->sucursal) ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4 text-center">
                                    <img src="<?= base_url('assets/img/bancos/' . strtolower(str_replace(' ', '', $cuenta->banco)) . '.png') ?>" 
                                         alt="<?= esc($cuenta->banco) ?>" 
                                         class="img-fluid"
                                         onerror="this.src='<?= base_url('assets/img/bancos/default.png') ?>'">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Instrucciones:</h6>
                        <ol class="mb-0">
                            <li>Realiza tu depósito o transferencia a cualquiera de las cuentas mostradas</li>
                            <li>Guarda tu comprobante de pago</li>
                            <li>Reporta tu pago usando el botón "Reportar Pago" para agilizar la aplicación</li>
                            <li>Tu pago será aplicado en un máximo de 24-48 horas hábiles</li>
                        </ol>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No hay cuentas bancarias disponibles en este momento. Por favor contacta a soporte.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="<?= site_url('/cliente/pagos/reportarPago') ?>" class="btn btn-warning">
                    <i class="fas fa-bell mr-2"></i>Reportar Pago Realizado
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Mensualidad -->
<div class="modal fade" id="modal-detalle-mensualidad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                    Detalle de Mensualidad
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contenido-detalle-mensualidad">
                <!-- Contenido cargado por AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="#" id="btn-pagar-mensualidad" class="btn btn-success">
                    <i class="fas fa-dollar-sign mr-2"></i>Realizar Pago
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // Configurar DataTable
    $('#tabla-proximas-mensualidades').DataTable({
        "language": {
            "url": "<?= base_url('assets/plugins/datatables/i18n/Spanish.json') ?>"
        },
        "pageLength": 10,
        "order": [[2, 'asc']], // Ordenar por fecha de vencimiento
        "columnDefs": [
            { "orderable": false, "targets": [6] }
        ]
    });
    
    // Manejo de checkboxes para pago múltiple
    $('#check-all-vencidos').on('change', function() {
        $('.check-mensualidad').prop('checked', this.checked);
        calcularTotalSeleccionado();
    });
    
    $('.check-mensualidad').on('change', function() {
        calcularTotalSeleccionado();
    });
});

// Calcular total seleccionado
function calcularTotalSeleccionado() {
    var total = 0;
    $('.check-mensualidad:checked').each(function() {
        total += parseFloat($(this).data('monto'));
    });
    
    $('#total-seleccionado').text('$' + total.toLocaleString('es-MX', { minimumFractionDigits: 0 }));
    
    if (total > 0) {
        $('#btn-pagar-seleccionados').show();
    } else {
        $('#btn-pagar-seleccionados').hide();
    }
}

// Seleccionar todos los vencidos
function seleccionarTodosVencidos() {
    $('#check-all-vencidos').prop('checked', true).trigger('change');
}

// Filtrar próximas mensualidades
function filtrarProximas(filtro) {
    // Cambiar botón activo
    $('.card-tools .btn-group .btn').removeClass('btn-primary active').addClass('btn-outline-primary');
    event.target.classList.remove('btn-outline-primary');
    event.target.classList.add('btn-primary', 'active');
    
    // Obtener DataTable
    var table = $('#tabla-proximas-mensualidades').DataTable();
    
    // Aplicar filtro
    if (filtro === 'todas') {
        table.search('').draw();
    } else {
        var dias = parseInt(filtro);
        // Aquí implementarías el filtro personalizado basado en días
        table.draw();
    }
}

// Ver detalle de mensualidad
function verDetalleMensualidad(mensualidadId) {
    $('#modal-detalle-mensualidad').modal('show');
    $('#contenido-detalle-mensualidad').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    
    $.get('<?= site_url('/cliente/pagos/mensualidad/') ?>' + mensualidadId + '?ajax=1')
        .done(function(data) {
            $('#contenido-detalle-mensualidad').html(data);
            $('#btn-pagar-mensualidad').attr('href', '<?= site_url('/cliente/pagos/mensualidad/') ?>' + mensualidadId);
        })
        .fail(function() {
            $('#contenido-detalle-mensualidad').html(
                '<div class="alert alert-danger">' +
                '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                'Error al cargar los detalles.' +
                '</div>'
            );
        });
}

// Iniciar pago en línea
function iniciarPagoLinea() {
    // Verificar si hay mensualidades seleccionadas
    var mensualidadesSeleccionadas = $('.check-mensualidad:checked').length;
    
    if (mensualidadesSeleccionadas > 0) {
        // Pago múltiple
        $('#form-pago-multiple').attr('action', '<?= site_url('/cliente/pagos/procesarLineaMultiple') ?>');
        $('#form-pago-multiple').submit();
    } else {
        // Redirigir a selección de mensualidad
        window.location.href = '<?= site_url('/cliente/pagos/seleccionarMensualidad') ?>';
    }
}

// Copiar al portapapeles
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(function() {
        // Mostrar toast de éxito
        toastr.success('Copiado al portapapeles');
    }, function(err) {
        // Fallback para navegadores antiguos
        var textArea = document.createElement("textarea");
        textArea.value = texto;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        toastr.success('Copiado al portapapeles');
    });
}

// Auto-refresh cada 5 minutos
setInterval(function() {
    location.reload();
}, 300000); // 5 minutos
</script>
<?= $this->endSection() ?>