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
                <p class="text-muted">Gestión centralizada de mensualidades</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Mensualidades</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Estadísticas Generales -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($estadisticas_generales['total_mensualidades']) ?></h3>
                        <p>Total Mensualidades</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($estadisticas_generales['pagadas']) ?></h3>
                        <p>Mensualidades Pagadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($estadisticas_generales['pendientes']) ?></h3>
                        <p>Mensualidades Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($estadisticas_generales['vencidas']) ?></h3>
                        <p>Mensualidades Vencidas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros de Búsqueda -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i>
                            Filtros de Búsqueda
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= current_url() ?>" id="formFiltros">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estatus" class="form-control">
                                            <option value="">Todos los estados</option>
                                            <?php foreach ($estados_disponibles as $estado): ?>
                                            <option value="<?= $estado ?>" 
                                                    <?= ($filtros_aplicados['estatus'] ?? '') === $estado ? 'selected' : '' ?>>
                                                <?= ucfirst($estado) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Vendedor</label>
                                        <select name="vendedor_id" class="form-control">
                                            <option value="">Todos los vendedores</option>
                                            <?php foreach ($opciones_vendedores as $vendedor): ?>
                                            <option value="<?= $vendedor->vendedor_id ?>" 
                                                    <?= ($filtros_aplicados['vendedor_id'] ?? '') == $vendedor->vendedor_id ? 'selected' : '' ?>>
                                                <?= esc($vendedor->nombre_vendedor) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha Desde</label>
                                        <input type="date" name="fecha_desde" class="form-control" 
                                               value="<?= $filtros_aplicados['fecha_desde'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha Hasta</label>
                                        <input type="date" name="fecha_hasta" class="form-control" 
                                               value="<?= $filtros_aplicados['fecha_hasta'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Cliente</label>
                                        <input type="text" name="nombre_cliente" class="form-control" 
                                               placeholder="Nombre del cliente..."
                                               value="<?= $filtros_aplicados['nombre_cliente'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Días Vencido (mínimo)</label>
                                        <input type="number" name="dias_vencido_min" class="form-control" 
                                               placeholder="0"
                                               value="<?= $filtros_aplicados['dias_vencido_min'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Ordenar por</label>
                                        <select name="orden_por" class="form-control">
                                            <option value="ta.fecha_vencimiento" 
                                                    <?= ($filtros_aplicados['orden_por'] ?? '') === 'ta.fecha_vencimiento' ? 'selected' : '' ?>>
                                                Fecha Vencimiento
                                            </option>
                                            <option value="ta.dias_atraso" 
                                                    <?= ($filtros_aplicados['orden_por'] ?? '') === 'ta.dias_atraso' ? 'selected' : '' ?>>
                                                Días Atraso
                                            </option>
                                            <option value="ta.monto_total" 
                                                    <?= ($filtros_aplicados['orden_por'] ?? '') === 'ta.monto_total' ? 'selected' : '' ?>>
                                                Monto
                                            </option>
                                            <option value="c.nombres" 
                                                    <?= ($filtros_aplicados['orden_por'] ?? '') === 'c.nombres' ? 'selected' : '' ?>>
                                                Cliente
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Dirección</label>
                                        <select name="direccion" class="form-control">
                                            <option value="ASC" 
                                                    <?= ($filtros_aplicados['direccion'] ?? '') === 'ASC' ? 'selected' : '' ?>>
                                                Ascendente
                                            </option>
                                            <option value="DESC" 
                                                    <?= ($filtros_aplicados['direccion'] ?? '') === 'DESC' ? 'selected' : '' ?>>
                                                Descendente
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <a href="<?= current_url() ?>" class="btn btn-secondary">
                                        <i class="fas fa-eraser"></i> Limpiar Filtros
                                    </a>
                                    <a href="<?= site_url('/admin/mensualidades/pendientes') ?>" class="btn btn-warning">
                                        <i class="fas fa-exclamation-circle"></i> Ver Críticas
                                    </a>
                                    <a href="<?= site_url('/admin/mensualidades/reporteMensual') ?>" class="btn btn-info">
                                        <i class="fas fa-chart-bar"></i> Reportes
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resultados de Búsqueda -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Mensualidades Encontradas (<?= count($mensualidades) ?>)
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" onclick="exportarMensualidades('excel')">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="exportarMensualidades('pdf')">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mensualidades)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaMensualidades">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Folio Venta</th>
                                        <th>Mens. #</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Monto Total</th>
                                        <th>Pagado</th>
                                        <th>Pendiente</th>
                                        <th>Estado</th>
                                        <th>Días Atraso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensualidades as $mensualidad): ?>
                                    <tr class="<?= $mensualidad->estatus === 'vencida' ? 'table-danger' : 
                                                  ($mensualidad->estatus === 'pagada' ? 'table-success' : '') ?>">
                                        <td>
                                            <strong><?= esc($mensualidad->nombre_cliente ?? 'N/A') ?></strong>
                                            <?php if (!empty($mensualidad->telefono)): ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-phone"></i> <?= esc($mensualidad->telefono) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= $mensualidad->folio_venta ?? 'N/A' ?></strong>
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
                                            <strong>$<?= number_format($mensualidad->monto_total, 0) ?></strong>
                                            <?php if ($mensualidad->interes_moratorio > 0): ?>
                                            <br><small class="text-warning">
                                                + $<?= number_format($mensualidad->interes_moratorio, 0) ?> mora
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-success">
                                                $<?= number_format($mensualidad->monto_pagado, 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-warning">
                                                $<?= number_format($mensualidad->saldo_pendiente, 0) ?>
                                            </span>
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
                                                <span class="badge badge-danger">
                                                    <?= $mensualidad->dias_atraso ?> días
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($mensualidad->estatus !== 'pagada'): ?>
                                                <a href="<?= site_url('/admin/mensualidades/aplicarPago/' . $mensualidad->id) ?>" 
                                                   class="btn btn-success btn-xs" title="Aplicar Pago">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <a href="<?= site_url('/admin/mensualidades/detalle/' . $mensualidad->id) ?>" 
                                                   class="btn btn-info btn-xs" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if (!empty($mensualidad->cliente_id)): ?>
                                                <a href="<?= site_url('/admin/estado-cuenta/cliente/' . $mensualidad->cliente_id) ?>" 
                                                   class="btn btn-primary btn-xs" title="Estado Cuenta Cliente">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($mensualidad->telefono)): ?>
                                                <a href="tel:<?= $mensualidad->telefono ?>" 
                                                   class="btn btn-secondary btn-xs" title="Llamar Cliente">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <?php if ($paginacion['total_elementos'] > $paginacion['elementos_por_pagina']): ?>
                        <div class="row mt-3">
                            <div class="col-sm-5">
                                <div class="dataTables_info">
                                    Mostrando <?= min($paginacion['elementos_por_pagina'], $paginacion['total_elementos']) ?> 
                                    de <?= $paginacion['total_elementos'] ?> mensualidades
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    <?php 
                                    $totalPaginas = ceil($paginacion['total_elementos'] / $paginacion['elementos_por_pagina']);
                                    $paginaActual = $paginacion['pagina_actual'];
                                    ?>
                                    
                                    <?php if ($paginaActual > 1): ?>
                                    <a href="?<?= http_build_query(array_merge($filtros_aplicados, ['page' => $paginaActual - 1])) ?>" 
                                       class="paginate_button previous">Anterior</a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $paginaActual - 2); $i <= min($totalPaginas, $paginaActual + 2); $i++): ?>
                                    <a href="?<?= http_build_query(array_merge($filtros_aplicados, ['page' => $i])) ?>" 
                                       class="paginate_button <?= $i === $paginaActual ? 'current' : '' ?>"><?= $i ?></a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($paginaActual < $totalPaginas): ?>
                                    <a href="?<?= http_build_query(array_merge($filtros_aplicados, ['page' => $paginaActual + 1])) ?>" 
                                       class="paginate_button next">Siguiente</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>No se encontraron mensualidades con los filtros aplicados.</p>
                            <a href="<?= current_url() ?>" class="btn btn-primary">
                                <i class="fas fa-eraser"></i> Limpiar Filtros
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- Modal de Aplicar Pago Rápido -->
<div class="modal fade" id="modalPagoRapido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Aplicar Pago Rápido</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formPagoRapido" method="POST">
                <div class="modal-body">
                    <div id="infoPagoRapido"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Monto a Pagar <span class="text-danger">*</span></label>
                                <input type="number" name="monto_pago" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Forma de Pago <span class="text-danger">*</span></label>
                                <select name="forma_pago" class="form-control" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="deposito">Depósito</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Pago</label>
                                <input type="date" name="fecha_pago" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Referencia/Folio</label>
                                <input type="text" name="referencia_pago" class="form-control" placeholder="Opcional">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Concepto</label>
                        <input type="text" name="concepto_pago" class="form-control" value="Pago de mensualidad" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="descripcion_concepto" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-dollar-sign"></i> Aplicar Pago
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
    // Inicializar DataTable si hay muchos registros
    if ($('#tablaMensualidades tbody tr').length > 25) {
        $('#tablaMensualidades').DataTable({
            "responsive": true,
            "pageLength": 50,
            "order": [[ 3, "asc" ]], // Ordenar por fecha vencimiento
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "columnDefs": [
                { "orderable": false, "targets": -1 } // No ordenar columna de acciones
            ]
        });
    }
});

// Aplicar pago rápido
function aplicarPagoRapido(mensualidadId, nombreCliente, numeroMensualidad, monto) {
    $('#infoPagoRapido').html(`
        <div class="alert alert-info">
            <strong>Cliente:</strong> ${nombreCliente}<br>
            <strong>Mensualidad:</strong> #${numeroMensualidad}<br>
            <strong>Monto Total:</strong> $${parseFloat(monto).toLocaleString()}
        </div>
    `);
    
    $('input[name="monto_pago"]').val(monto);
    $('#formPagoRapido').attr('action', '<?= site_url("/admin/mensualidades/procesarPago") ?>');
    $('#formPagoRapido').append(`<input type="hidden" name="mensualidad_id" value="${mensualidadId}">`);
    
    $('#modalPagoRapido').modal('show');
}

// Exportar mensualidades
function exportarMensualidades(formato) {
    var filtros = $('#formFiltros').serialize();
    var url = '<?= site_url("/admin/estado-cuenta/exportarMensualidades") ?>?' + filtros + '&formato=' + formato;
    window.open(url, '_blank');
}

// Auto-refrescar cada 5 minutos
setTimeout(function() {
    location.reload();
}, 300000);
</script>
<?= $this->endSection() ?>