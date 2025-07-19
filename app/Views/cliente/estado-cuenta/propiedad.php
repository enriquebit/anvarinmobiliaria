<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Detalle de Propiedad<?= $this->endSection() ?>

<?= $this->section('page_title') ?>
Detalle de Propiedad <?= !empty($venta->lote_clave) ? '- ' . esc($venta->lote_clave) : '' ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/estado-cuenta') ?>">Estado de Cuenta</a></li>
<li class="breadcrumb-item active">Detalle Propiedad</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Información principal de la propiedad -->
<div class="row">
    <!-- Información del inmueble -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-home mr-2"></i>
                    Información del Inmueble
                </h3>
                <div class="card-tools">
                    <?php if ($venta->tipo_venta === 'financiado' && !empty($tabla_amortizacion)): ?>
                        <a href="<?= site_url('/cliente/estado-cuenta/descargarPDF/' . $venta->id) ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i>
                            Descargar Estado
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="140">Folio Venta:</th>
                                <td><span class="badge badge-primary"><?= esc($venta->folio_venta) ?></span></td>
                            </tr>
                            <tr>
                                <th>Lote:</th>
                                <td><strong><?= esc($venta->lote_clave ?? 'N/A') ?></strong></td>
                            </tr>
                            <tr>
                                <th>Proyecto:</th>
                                <td><?= esc($venta->proyecto_nombre ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Área:</th>
                                <td><?= number_format($venta->lote_area ?? 0, 2) ?> m²</td>
                            </tr>
                            <tr>
                                <th>Fecha Venta:</th>
                                <td><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="140">Tipo Venta:</th>
                                <td>
                                    <?php if ($venta->tipo_venta === 'contado'): ?>
                                        <span class="badge badge-success">Contado</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Financiado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Estatus:</th>
                                <td>
                                    <?php
                                    $badgeClass = match($venta->estatus_venta) {
                                        'activa' => 'badge-success',
                                        'liquidada' => 'badge-primary',
                                        'cancelada' => 'badge-danger',
                                        'juridico' => 'badge-warning',
                                        default => 'badge-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($venta->estatus_venta) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Precio Lista:</th>
                                <td>$<?= number_format($venta->precio_lista, 2) ?></td>
                            </tr>
                            <?php if ($venta->descuento_aplicado > 0): ?>
                            <tr>
                                <th>Descuento:</th>
                                <td class="text-warning">-$<?= number_format($venta->descuento_aplicado, 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th><strong>Precio Final:</strong></th>
                                <td><strong>$<?= number_format($venta->precio_venta_final, 2) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel lateral con resumen financiero -->
    <div class="col-md-4">
        <?php if ($venta->tipo_venta === 'financiado' && !empty($resumen_financiero)): ?>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Resumen Financiero
                </h3>
            </div>
            <div class="card-body">
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: <?= $resumen_financiero['porcentaje_liquidacion'] ?? 0 ?>%">
                        <?= number_format($resumen_financiero['porcentaje_liquidacion'] ?? 0, 1) ?>% Liquidado
                    </div>
                </div>
                
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>Total Pagado:</th>
                        <td class="text-success">
                            <strong>$<?= number_format($resumen_financiero['total_pagado'], 0) ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Saldo Pendiente:</th>
                        <td class="text-warning">
                            <strong>$<?= number_format($resumen_financiero['saldo_pendiente'], 0) ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Mensualidades:</th>
                        <td>
                            <span class="badge badge-success"><?= $resumen_financiero['pagadas'] ?? 0 ?></span> /
                            <span class="badge badge-secondary"><?= $resumen_financiero['total'] ?? 0 ?></span>
                        </td>
                    </tr>
                </table>
                
                <?php if (!empty($proxima_mensualidad)): ?>
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-calendar-alt mr-1"></i>Próximo Pago</h6>
                    <p class="mb-1">
                        <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($proxima_mensualidad->fecha_vencimiento)) ?>
                    </p>
                    <p class="mb-2">
                        <strong>Monto:</strong> $<?= number_format($proxima_mensualidad->monto_total, 0) ?>
                    </p>
                    <a href="<?= site_url('/cliente/pagos/mensualidad/' . $proxima_mensualidad->id) ?>" 
                       class="btn btn-success btn-sm btn-block">
                        <i class="fas fa-dollar-sign"></i> Realizar Pago
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Información de contacto -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-tie mr-2"></i>
                    Mi Vendedor
                </h3>
            </div>
            <div class="card-body">
                <p><strong><?= esc($vendedor->username ?? 'No asignado') ?></strong></p>
                <?php if (!empty($vendedor->email)): ?>
                    <p><i class="fas fa-envelope mr-2"></i><?= esc($vendedor->email) ?></p>
                <?php endif; ?>
                <?php if (!empty($vendedor->telefono)): ?>
                    <p><i class="fas fa-phone mr-2"></i><?= esc($vendedor->telefono) ?></p>
                <?php endif; ?>
                
                <div class="btn-group btn-group-sm w-100">
                    <?php if (!empty($vendedor->email)): ?>
                    <a href="mailto:<?= esc($vendedor->email) ?>" class="btn btn-primary">
                        <i class="fas fa-envelope"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($vendedor->telefono)): ?>
                    <a href="tel:<?= esc($vendedor->telefono) ?>" class="btn btn-success">
                        <i class="fas fa-phone"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Amortización / Historial de Pagos -->
<?php if ($venta->tipo_venta === 'financiado' && !empty($tabla_amortizacion)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="propiedadTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tabla-amortizacion-tab" data-toggle="tab" href="#tabla-amortizacion" role="tab">
                            <i class="fas fa-table mr-1"></i>
                            Tabla de Amortización
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="historial-pagos-tab" data-toggle="tab" href="#historial-pagos" role="tab">
                            <i class="fas fa-history mr-1"></i>
                            Historial de Pagos
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="propiedadTabsContent">
                    <!-- Tabla de Amortización -->
                    <div class="tab-pane fade show active" id="tabla-amortizacion" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tabla-amortizacion-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Monto</th>
                                        <th>Capital</th>
                                        <th>Interés</th>
                                        <th>Saldo</th>
                                        <th>Estado</th>
                                        <th>Días Atraso</th>
                                        <th>Mora</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tabla_amortizacion as $mensualidad): ?>
                                    <tr class="<?= $mensualidad->estatus === 'vencida' ? 'table-danger' : ($mensualidad->estatus === 'pagada' ? 'table-success' : '') ?>">
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= $mensualidad->numero_pago ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($mensualidad->monto_total, 0) ?></strong>
                                        </td>
                                        <td>
                                            $<?= number_format($mensualidad->monto_capital, 0) ?>
                                        </td>
                                        <td>
                                            $<?= number_format($mensualidad->monto_interes, 0) ?>
                                        </td>
                                        <td>
                                            $<?= number_format($mensualidad->saldo_pendiente_total, 0) ?>
                                        </td>
                                        <td>
                                            <?php if ($mensualidad->estatus === 'pagada'): ?>
                                                <span class="badge badge-success">Pagada</span>
                                            <?php elseif ($mensualidad->estatus === 'vencida'): ?>
                                                <span class="badge badge-danger">Vencida</span>
                                            <?php elseif ($mensualidad->estatus === 'parcial'): ?>
                                                <span class="badge badge-warning">Parcial</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($mensualidad->dias_atraso > 0): ?>
                                                <span class="text-danger">
                                                    <?= $mensualidad->dias_atraso ?> días
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($mensualidad->interes_moratorio > 0): ?>
                                                <span class="text-warning">
                                                    $<?= number_format($mensualidad->interes_moratorio, 0) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">$0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($mensualidad->estatus === 'pagada'): ?>
                                                <button class="btn btn-info btn-xs" 
                                                        onclick="verDetallePago(<?= $mensualidad->id ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php elseif (in_array($mensualidad->estatus, ['pendiente', 'vencida', 'parcial'])): ?>
                                                <a href="<?= site_url('/cliente/pagos/mensualidad/' . $mensualidad->id) ?>" 
                                                   class="btn btn-success btn-xs">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Historial de Pagos -->
                    <div class="tab-pane fade" id="historial-pagos" role="tabpanel">
                        <?php if (!empty($historial_pagos)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm" id="historial-pagos-datatable">
                                <thead>
                                    <tr>
                                        <th>Fecha Pago</th>
                                        <th>Folio</th>
                                        <th>Mensualidad</th>
                                        <th>Monto</th>
                                        <th>Forma Pago</th>
                                        <th>Referencia</th>
                                        <th>Estado</th>
                                        <th>Comprobante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_pagos as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($pago->fecha_pago)) ?></td>
                                        <td><strong><?= esc($pago->folio_pago) ?></strong></td>
                                        <td>
                                            <span class="badge badge-info">
                                                #<?= $pago->numero_mensualidad ?? 'N/A' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                $<?= number_format($pago->monto_pago, 0) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= ucfirst($pago->forma_pago) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= esc($pago->referencia_pago) ?: '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">Aplicado</span>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('/cliente/pagos/comprobante/' . $pago->id) ?>" 
                                               class="btn btn-info btn-xs">
                                                <i class="fas fa-file-alt"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Aún no se han registrado pagos para esta propiedad.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Acciones Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>
                    Acciones Disponibles
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($venta->tipo_venta === 'financiado' && !empty($proxima_mensualidad)): ?>
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/pagos/mensualidad/' . $proxima_mensualidad->id) ?>" 
                           class="btn btn-success btn-block">
                            <i class="fas fa-dollar-sign"></i><br>
                            Realizar Pago
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/estado-cuenta/historialPagos') ?>" 
                           class="btn btn-info btn-block">
                            <i class="fas fa-history"></i><br>
                            Ver Historial
                        </a>
                    </div>
                    
                    <?php if ($venta->tipo_venta === 'financiado'): ?>
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/estado-cuenta/descargarPDF/' . $venta->id) ?>" 
                           class="btn btn-primary btn-block">
                            <i class="fas fa-file-pdf"></i><br>
                            Descargar PDF
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-3 col-6">
                        <a href="<?= site_url('/cliente/pagos/reportarPago') ?>" 
                           class="btn btn-warning btn-block">
                            <i class="fas fa-bell"></i><br>
                            Reportar Pago
                        </a>
                    </div>
                </div>
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
    // Configurar DataTable para tabla de amortización
    $('#tabla-amortizacion-datatable').DataTable({
        "language": {
            "url": "<?= base_url('assets/plugins/datatables/i18n/Spanish.json') ?>"
        },
        "pageLength": 12,
        "order": [[0, 'asc']],
        "columnDefs": [
            { "orderable": false, "targets": [9] }
        ]
    });
    
    // Configurar DataTable para historial de pagos
    $('#historial-pagos-datatable').DataTable({
        "language": {
            "url": "<?= base_url('assets/plugins/datatables/i18n/Spanish.json') ?>"
        },
        "pageLength": 10,
        "order": [[0, 'desc']],
        "columnDefs": [
            { "orderable": false, "targets": [7] }
        ]
    });
    
    // Manejar cambio de tabs
    $('#propiedadTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
});

// Función para ver detalle de pago
function verDetallePago(mensualidadId) {
    // Buscar en el historial de pagos el último pago para esta mensualidad
    var ultimoPagoId = null;
    
    // Buscar en la tabla de historial
    $('#historial-pagos-datatable tbody tr').each(function() {
        var numeroMensualidad = $(this).find('td:eq(2) .badge').text().replace('#', '');
        if (numeroMensualidad === mensualidadId.toString()) {
            var linkComprobante = $(this).find('td:eq(7) a').attr('href');
            if (linkComprobante) {
                window.open(linkComprobante, '_blank');
                return false;
            }
        }
    });
    
    if (!ultimoPagoId) {
        alert('No se encontró el comprobante de pago para esta mensualidad.');
    }
}
</script>
<?= $this->endSection() ?>