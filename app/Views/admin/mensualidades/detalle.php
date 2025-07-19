<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= $title ?></h1>
                <p class="text-muted">
                    Cliente: <strong><?= esc($mensualidad->nombre_cliente) ?></strong>
                </p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/mensualidades') ?>">Mensualidades</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Información Principal -->
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Información General</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-6">Folio Venta:</dt>
                            <dd class="col-sm-6"><strong><?= $mensualidad->folio_venta ?></strong></dd>
                            
                            <dt class="col-sm-6">Mensualidad:</dt>
                            <dd class="col-sm-6">
                                <span class="badge badge-info">
                                    #<?= $mensualidad->numero_pago ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-6">Fecha Venc.:</dt>
                            <dd class="col-sm-6">
                                <?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?>
                            </dd>
                            
                            <dt class="col-sm-6">Estado:</dt>
                            <dd class="col-sm-6">
                                <?php if ($mensualidad->estatus === 'pagada'): ?>
                                    <span class="badge badge-success">Pagada</span>
                                <?php elseif ($mensualidad->estatus === 'vencida'): ?>
                                    <span class="badge badge-danger">Vencida</span>
                                <?php elseif ($mensualidad->estatus === 'parcial'): ?>
                                    <span class="badge badge-warning">Parcial</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Pendiente</span>
                                <?php endif; ?>
                            </dd>
                            
                            <?php if ($mensualidad->dias_atraso != 0): ?>
                            <dt class="col-sm-6">Días Atraso:</dt>
                            <dd class="col-sm-6">
                                <?php if ($mensualidad->dias_atraso > 0): ?>
                                    <span class="badge badge-danger">
                                        <?= $mensualidad->dias_atraso ?> días
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success">
                                        Vence en <?= abs($mensualidad->dias_atraso) ?> días
                                    </span>
                                <?php endif; ?>
                            </dd>
                            <?php endif; ?>
                        </dl>
                        
                        <div class="text-center mt-3">
                            <a href="<?= site_url('/admin/estado-cuenta/cliente/' . $mensualidad->cliente_id) ?>" 
                               class="btn btn-info btn-sm">
                                <i class="fas fa-user"></i> Ver Cliente
                            </a>
                            <a href="<?= site_url('/admin/estado-cuenta/venta/' . $mensualidad->venta_id) ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-home"></i> Ver Venta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Resumen Financiero -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumen Financiero de la Mensualidad</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header">
                                        $<?= number_format($detalle_financiero['capital'], 2) ?>
                                    </h5>
                                    <span class="description-text">CAPITAL</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header">
                                        $<?= number_format($detalle_financiero['interes'], 2) ?>
                                    </h5>
                                    <span class="description-text">INTERÉS</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-warning">
                                        $<?= number_format($detalle_financiero['interes_moratorio'], 2) ?>
                                    </h5>
                                    <span class="description-text">MORATORIOS</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="description-block">
                                    <h5 class="description-header text-primary">
                                        $<?= number_format($detalle_financiero['monto_total'], 2) ?>
                                    </h5>
                                    <span class="description-text">TOTAL</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-success">
                                        $<?= number_format($detalle_financiero['monto_pagado'], 2) ?>
                                    </h5>
                                    <span class="description-text">PAGADO</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="description-block border-right">
                                    <h5 class="description-header text-danger">
                                        $<?= number_format($detalle_financiero['saldo_pendiente'], 2) ?>
                                    </h5>
                                    <span class="description-text">PENDIENTE</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="description-block">
                                    <h5 class="description-header text-info">
                                        <?= number_format($detalle_financiero['porcentaje_pagado'], 1) ?>%
                                    </h5>
                                    <span class="description-text">COMPLETADO</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Barra de Progreso -->
                        <div class="progress mt-3">
                            <?php 
                            $porcentaje = $detalle_financiero['porcentaje_pagado'];
                            $colorBarra = $porcentaje >= 100 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                            ?>
                            <div class="progress-bar bg-<?= $colorBarra ?>" style="width: <?= min($porcentaje, 100) ?>%">
                                <?= number_format($porcentaje, 1) ?>%
                            </div>
                        </div>
                        
                        <?php if ($detalle_financiero['saldo_pendiente'] > 0): ?>
                        <div class="text-center mt-3">
                            <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $mensualidad->id) ?>" 
                               class="btn btn-success">
                                <i class="fas fa-dollar-sign"></i> Aplicar Pago
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información del Cliente -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Información del Cliente</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8">
                                <strong><?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) ?></strong>
                            </dd>
                            
                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">
                                <?php if (!empty($cliente->email)): ?>
                                    <a href="mailto:<?= esc($cliente->email) ?>">
                                        <i class="fas fa-envelope"></i> <?= esc($cliente->email) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No registrado</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">
                                <?php if (!empty($cliente->telefono)): ?>
                                    <a href="tel:<?= esc($cliente->telefono) ?>">
                                        <i class="fas fa-phone"></i> <?= esc($cliente->telefono) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No registrado</span>
                                <?php endif; ?>
                            </dd>
                            
                            <?php if (!empty($indicadores_cliente)): ?>
                            <dt class="col-sm-4">Comportamiento:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-<?= $indicadores_cliente['comportamiento'] === 'Bueno' ? 'success' : ($indicadores_cliente['comportamiento'] === 'Regular' ? 'warning' : 'danger') ?>">
                                    <?= $indicadores_cliente['comportamiento'] ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4">Atraso Promedio:</dt>
                            <dd class="col-sm-8">
                                <span class="text-muted">
                                    <?= $indicadores_cliente['atraso_promedio'] ?> días
                                </span>
                            </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Información de la Venta</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Fecha Venta:</dt>
                            <dd class="col-sm-7">
                                <?= date('d/m/Y', strtotime($venta->fecha_venta)) ?>
                            </dd>
                            
                            <dt class="col-sm-5">Tipo:</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-<?= $venta->tipo_venta === 'contado' ? 'success' : 'info' ?>">
                                    <?= ucfirst($venta->tipo_venta) ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Precio Total:</dt>
                            <dd class="col-sm-7">
                                <strong class="text-success">
                                    $<?= number_format($venta->precio_venta_final, 2) ?>
                                </strong>
                            </dd>
                            
                            <dt class="col-sm-5">Vendedor:</dt>
                            <dd class="col-sm-7">
                                <?= esc($venta->nombre_vendedor ?? 'N/A') ?>
                            </dd>
                            
                            <?php if (!empty($venta->proyecto_nombre)): ?>
                            <dt class="col-sm-5">Proyecto:</dt>
                            <dd class="col-sm-7">
                                <small class="text-muted">
                                    <?= esc($venta->proyecto_nombre) ?>
                                </small>
                            </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial de Pagos de esta Mensualidad -->
        <?php if (!empty($historial_pagos)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Historial de Pagos (<?= count($historial_pagos) ?>)
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-sm" onclick="exportarHistorial()">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaHistorial">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Folio</th>
                                        <th>Forma de Pago</th>
                                        <th>Referencia</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_pagos as $pago): ?>
                                    <tr class="<?= $pago->estatus_pago === 'cancelado' ? 'table-secondary' : '' ?>">
                                        <td>
                                            <?= date('d/m/Y', strtotime($pago->fecha_pago)) ?><br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($pago->fecha_pago)) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?= $pago->folio_pago ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= ucfirst(str_replace('_', ' ', $pago->forma_pago)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= esc($pago->referencia_pago ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <?= esc($pago->concepto_pago) ?>
                                            <?php if (!empty($pago->descripcion_concepto)): ?>
                                                <br><small class="text-muted">
                                                    <?= esc($pago->descripcion_concepto) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="<?= $pago->estatus_pago === 'cancelado' ? 'text-muted' : 'text-success' ?>">
                                                $<?= number_format($pago->monto_pago, 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($pago->estatus_pago === 'aplicado'): ?>
                                                <span class="badge badge-success">Aplicado</span>
                                            <?php elseif ($pago->estatus_pago === 'cancelado'): ?>
                                                <span class="badge badge-danger">Cancelado</span>
                                                <?php if (!empty($pago->motivo_cancelacion)): ?>
                                                    <br><small class="text-muted">
                                                        <?= esc($pago->motivo_cancelacion) ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= esc($pago->nombre_usuario ?? 'Sistema') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('/admin/mensualidades/comprobante/' . $pago->id) ?>" 
                                                   class="btn btn-info btn-xs" title="Ver Comprobante">
                                                    <i class="fas fa-receipt"></i>
                                                </a>
                                                
                                                <?php if ($pago->estatus_pago === 'aplicado'): ?>
                                                <button type="button" class="btn btn-danger btn-xs" 
                                                        onclick="confirmarCancelacion(<?= $pago->id ?>)" 
                                                        title="Cancelar Pago">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pago->comprobante_url)): ?>
                                                <a href="<?= $pago->comprobante_url ?>" target="_blank"
                                                   class="btn btn-secondary btn-xs" title="Ver Archivo">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="5" class="text-right">TOTAL PAGADO:</td>
                                        <td>
                                            <strong class="text-success">
                                                $<?= number_format($detalle_financiero['monto_pagado'], 2) ?>
                                            </strong>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Sin Pagos Registrados</h5>
                    Esta mensualidad aún no tiene pagos aplicados.
                    <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $mensualidad->id) ?>" 
                       class="btn btn-primary btn-sm ml-2">
                        <i class="fas fa-dollar-sign"></i> Aplicar Primer Pago
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Bitácora de Cambios -->
        <?php if (!empty($bitacora_cambios)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Bitácora de Cambios
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($bitacora_cambios as $cambio): ?>
                            <div class="time-label">
                                <span class="bg-info">
                                    <?= date('d/m/Y H:i', strtotime($cambio->fecha_cambio)) ?>
                                </span>
                            </div>
                            <div>
                                <i class="fas fa-edit bg-blue"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">
                                        <?= esc($cambio->tipo_cambio) ?>
                                        <small>por <?= esc($cambio->nombre_usuario) ?></small>
                                    </h3>
                                    <div class="timeline-body">
                                        <?= esc($cambio->descripcion_cambio) ?>
                                        <?php if (!empty($cambio->valores_anteriores)): ?>
                                            <br><small class="text-muted">
                                                Valores anteriores: <?= esc($cambio->valores_anteriores) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- Modal para Cancelar Pago -->
<div class="modal fade" id="modalCancelarPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cancelar Pago</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formCancelarPago" method="POST">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>¿Está seguro de cancelar este pago?</strong><br>
                        Esta acción afectará el estado de la mensualidad y no se puede deshacer.
                    </div>
                    <div class="form-group">
                        <label>Motivo de cancelación <span class="text-danger">*</span></label>
                        <textarea name="motivo_cancelacion" class="form-control" rows="3" required 
                                  placeholder="Describe el motivo de la cancelación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable para historial
    $('#tablaHistorial').DataTable({
        "responsive": true,
        "pageLength": 25,
        "order": [[ 0, "desc" ]], // Ordenar por fecha
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": -1 } // No ordenar columna de acciones
        ]
    });
});

// Confirmar cancelación de pago
function confirmarCancelacion(pagoId) {
    $('#formCancelarPago').attr('action', '<?= site_url("/admin/mensualidades/cancelarPago/") ?>' + pagoId);
    $('#modalCancelarPago').modal('show');
}

// Manejar envío del formulario de cancelación
$('#formCancelarPago').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this);
    var motivo = form.find('textarea[name="motivo_cancelacion"]').val().trim();
    
    if (motivo === '') {
        alert('El motivo de cancelación es requerido');
        return;
    }
    
    if (!confirm('¿Confirma la cancelación de este pago?')) {
        return;
    }
    
    // Enviar formulario
    form.off('submit').submit();
});

// Exportar historial
function exportarHistorial() {
    var url = '<?= site_url("/admin/mensualidades/exportarHistorial/" . $mensualidad->id) ?>?formato=excel';
    window.open(url, '_blank');
}

// Imprimir detalle
function imprimirDetalle() {
    window.print();
}
</script>
<?= $this->endSection() ?>