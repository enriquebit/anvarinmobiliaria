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
                <p class="text-muted">Mensualidades categorizadas por urgencia</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/mensualidades') ?>">Mensualidades</a></li>
                    <li class="breadcrumb-item active">Pendientes</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Resumen de Categorías -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= count($mensualidades_categorizadas['criticas'] ?? []) ?></h3>
                        <p>Críticas (>30 días)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= count($mensualidades_categorizadas['urgentes'] ?? []) ?></h3>
                        <p>Urgentes (8-30 días)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= count($mensualidades_categorizadas['proximas'] ?? []) ?></h3>
                        <p>Próximas (1-7 días)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?= count($mensualidades_categorizadas['futuras'] ?? []) ?></h3>
                        <p>Futuras (>7 días)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Monto Total por Categoría -->
        <?php if (!empty($resumen_montos)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info-circle"></i> Resumen de Montos</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Críticas:</strong> <span class="text-danger">$<?= number_format($resumen_montos['criticas'], 0) ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Urgentes:</strong> <span class="text-warning">$<?= number_format($resumen_montos['urgentes'], 0) ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Próximas:</strong> <span class="text-info">$<?= number_format($resumen_montos['proximas'], 0) ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Total:</strong> <span class="text-primary">$<?= number_format($resumen_montos['total'], 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades Críticas (Más de 30 días vencidas) -->
        <?php if (!empty($mensualidades_categorizadas['criticas'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Mensualidades CRÍTICAS - Más de 30 días vencidas (<?= count($mensualidades_categorizadas['criticas']) ?>)
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm" onclick="pagoMasivoCriticas()">
                                    <i class="fas fa-dollar-sign"></i> Pago Masivo
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="exportarCriticas()">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaCriticas">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAllCriticas" onchange="toggleSelectCriticas()">
                                        </th>
                                        <th>Cliente</th>
                                        <th>Folio Venta</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Atraso</th>
                                        <th>Monto Original</th>
                                        <th>Moratorios</th>
                                        <th>Total a Pagar</th>
                                        <th>Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensualidades_categorizadas['criticas'] as $critica): ?>
                                    <tr class="table-danger">
                                        <td>
                                            <input type="checkbox" class="select-critica" value="<?= $critica->id ?>">
                                        </td>
                                        <td>
                                            <strong><?= esc($critica->nombre_cliente) ?></strong><br>
                                            <small class="text-muted"><?= esc($critica->email ?? 'N/A') ?></small>
                                        </td>
                                        <td><strong><?= $critica->folio_venta ?></strong></td>
                                        <td>
                                            <span class="badge badge-dark">
                                                #<?= $critica->numero_pago ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($critica->fecha_vencimiento)) ?></td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <?= $critica->dias_atraso ?> días
                                            </span>
                                        </td>
                                        <td>$<?= number_format($critica->monto_total, 0) ?></td>
                                        <td>
                                            <span class="text-warning">
                                                $<?= number_format($critica->interes_moratorio, 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">
                                                $<?= number_format($critica->monto_total + $critica->interes_moratorio, 0) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if (!empty($critica->telefono)): ?>
                                                <a href="tel:<?= $critica->telefono ?>" class="btn btn-info btn-xs">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $critica->id) ?>" 
                                                   class="btn btn-success btn-xs" title="Aplicar Pago">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                                <a href="<?= site_url('/admin/estado-cuenta/cliente/' . $critica->cliente_id) ?>" 
                                                   class="btn btn-info btn-xs" title="Ver Cliente">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                <a href="<?= site_url('/admin/mensualidades/detalle/' . $critica->id) ?>" 
                                                   class="btn btn-warning btn-xs" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades Urgentes (8-30 días vencidas) -->
        <?php if (!empty($mensualidades_categorizadas['urgentes'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i>
                            Mensualidades URGENTES - 8 a 30 días vencidas (<?= count($mensualidades_categorizadas['urgentes']) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaUrgentes">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Folio</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Atraso</th>
                                        <th>Monto + Mora</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensualidades_categorizadas['urgentes'] as $urgente): ?>
                                    <tr class="table-warning">
                                        <td>
                                            <strong><?= esc($urgente->nombre_cliente) ?></strong><br>
                                            <?php if (!empty($urgente->telefono)): ?>
                                                <small><i class="fas fa-phone"></i> <?= esc($urgente->telefono) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $urgente->folio_venta ?></td>
                                        <td>
                                            <span class="badge badge-warning">
                                                #<?= $urgente->numero_pago ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($urgente->fecha_vencimiento)) ?></td>
                                        <td>
                                            <span class="badge badge-warning">
                                                <?= $urgente->dias_atraso ?> días
                                            </span>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($urgente->monto_total + $urgente->interes_moratorio, 0) ?></strong>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $urgente->id) ?>" 
                                                   class="btn btn-success btn-xs">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                                <a href="<?= site_url('/admin/mensualidades/detalle/' . $urgente->id) ?>" 
                                                   class="btn btn-info btn-xs">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensualidades Próximas a Vencer (1-7 días) -->
        <?php if (!empty($mensualidades_categorizadas['proximas'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-info collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day"></i>
                            Mensualidades PRÓXIMAS a Vencer - 1 a 7 días (<?= count($mensualidades_categorizadas['proximas']) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($mensualidades_categorizadas['proximas'] as $proxima): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <strong><?= esc($proxima->nombre_cliente) ?></strong>
                                        </h5>
                                        <div class="card-tools">
                                            <span class="badge badge-info">
                                                <?= abs($proxima->dias_atraso) ?> días
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-1">
                                            <?= $proxima->folio_venta ?> - Mensualidad #<?= $proxima->numero_pago ?>
                                        </p>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-calendar"></i> 
                                            <?= date('d/m/Y', strtotime($proxima->fecha_vencimiento)) ?>
                                        </p>
                                        <p class="text-success mb-2">
                                            <strong>$<?= number_format($proxima->monto_total, 0) ?></strong>
                                        </p>
                                        <div class="btn-group btn-group-sm btn-block">
                                            <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $proxima->id) ?>" 
                                               class="btn btn-success">
                                                <i class="fas fa-dollar-sign"></i> Pagar
                                            </a>
                                            <?php if (!empty($proxima->telefono)): ?>
                                            <a href="tel:<?= $proxima->telefono ?>" 
                                               class="btn btn-info">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
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
        
        <!-- Mensualidades Futuras (Más de 7 días) -->
        <?php if (!empty($mensualidades_categorizadas['futuras'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt"></i>
                            Mensualidades FUTURAS - Más de 7 días (<?= count($mensualidades_categorizadas['futuras']) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaFuturas">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Folio</th>
                                        <th>Mensualidad #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Restantes</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensualidades_categorizadas['futuras'] as $futura): ?>
                                    <tr>
                                        <td><?= esc($futura->nombre_cliente) ?></td>
                                        <td><?= $futura->folio_venta ?></td>
                                        <td>
                                            <span class="badge badge-light">
                                                #<?= $futura->numero_pago ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($futura->fecha_vencimiento)) ?></td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?= abs($futura->dias_atraso) ?> días
                                            </span>
                                        </td>
                                        <td>$<?= number_format($futura->monto_total, 0) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- Modal de Pago Masivo -->
<div class="modal fade" id="modalPagoMasivo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pago Masivo - Mensualidades Críticas</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formPagoMasivo" method="POST" action="<?= site_url('/admin/mensualidades/procesarPagoMasivo') ?>">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><strong>Mensualidades Seleccionadas:</strong></h6>
                        <div id="resumenSeleccionadas"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Forma de Pago <span class="text-danger">*</span></label>
                                <select name="forma_pago" class="form-control" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="deposito">Depósito</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Pago</label>
                                <input type="date" name="fecha_pago" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Referencia General</label>
                        <input type="text" name="referencia_pago" class="form-control" placeholder="Opcional">
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3" placeholder="Detalles del pago masivo..."></textarea>
                    </div>
                    
                    <input type="hidden" name="mensualidades_ids" id="mensualidadesIds">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-dollar-sign"></i> Procesar Pago Masivo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTables para las tablas grandes
    $('#tablaCriticas').DataTable({
        "responsive": true,
        "pageLength": 25,
        "order": [[ 5, "desc" ]], // Ordenar por días de atraso
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": [0, 10] } // No ordenar checkbox y acciones
        ]
    });
    
    $('#tablaUrgentes').DataTable({
        "responsive": true,
        "pageLength": 25,
        "order": [[ 4, "desc" ]], // Ordenar por días de atraso
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
    
    $('#tablaFuturas').DataTable({
        "responsive": true,
        "pageLength": 25,
        "order": [[ 4, "asc" ]], // Ordenar por días restantes
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
});

// Toggle selección todas las críticas
function toggleSelectCriticas() {
    var selectAll = document.getElementById('selectAllCriticas');
    var checkboxes = document.querySelectorAll('.select-critica');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = selectAll.checked;
    });
}

// Pago masivo de críticas
function pagoMasivoCriticas() {
    var seleccionadas = [];
    var totalMonto = 0;
    
    document.querySelectorAll('.select-critica:checked').forEach(function(checkbox) {
        seleccionadas.push(checkbox.value);
        // Obtener monto de la fila
        var fila = checkbox.closest('tr');
        var montoTexto = fila.cells[8].textContent.replace(/[$,]/g, '');
        totalMonto += parseFloat(montoTexto);
    });
    
    if (seleccionadas.length === 0) {
        alert('Seleccione al menos una mensualidad crítica');
        return;
    }
    
    // Mostrar resumen
    document.getElementById('resumenSeleccionadas').innerHTML = `
        <strong>${seleccionadas.length}</strong> mensualidades seleccionadas<br>
        <strong>Monto total:</strong> $${totalMonto.toLocaleString()}
    `;
    
    document.getElementById('mensualidadesIds').value = seleccionadas.join(',');
    $('#modalPagoMasivo').modal('show');
}

// Exportar críticas
function exportarCriticas() {
    var url = '<?= site_url("/admin/mensualidades/exportarCriticas") ?>?formato=excel';
    window.open(url, '_blank');
}

// Auto-refresh cada 5 minutos
setTimeout(function() {
    location.reload();
}, 300000);
</script>
<?= $this->endSection() ?>