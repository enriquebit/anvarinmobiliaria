<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Gestión de Ventas<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Catálogo de Lotes Disponibles<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Ventas</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Estadísticas principales -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $estadisticas_lotes->total_lotes ?? 0 ?></h3>
                <p>Total Lotes</p>
            </div>
            <div class="icon">
                <i class="fas fa-map"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $estadisticas_lotes->lotes_disponibles ?? 0 ?></h3>
                <p>Lotes Disponibles</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= $estadisticas_lotes->lotes_vendidos ?? 0 ?></h3>
                <p>Lotes Vendidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>$<?= number_format($estadisticas_lotes->precio_promedio ?? 0, 2) ?></h3>
                <p>Precio Promedio</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros Rápidos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filtros Rápidos
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/ventas/historial') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-history mr-1"></i>
                        Ver Historial de Ventas
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row" id="filtros-rapidos">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtro-empresa">Empresa</label>
                            <select class="form-control form-control-sm" id="filtro-empresa">
                                <option value="">Todas las empresas</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-proyecto">Proyecto</label>
                            <select class="form-control form-control-sm" id="filtro-proyecto">
                                <option value="">Todos los proyectos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtro-tipo">Tipo</label>
                            <select class="form-control form-control-sm" id="filtro-tipo">
                                <option value="">Todos los tipos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtro-division">División</label>
                            <select class="form-control form-control-sm" id="filtro-division">
                                <option value="">Todas las divisiones</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-categoria">Categoría</label>
                            <select class="form-control form-control-sm" id="filtro-categoria">
                                <option value="">Todas las categorías</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los filtros se aplican automáticamente al seleccionar una opción. También puedes usar la búsqueda general de la tabla.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Catálogo de lotes disponibles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Vista de cuadrícula -->
                <div id="gridView" class="row d-none">
                    <?php if (!empty($lotes_disponibles)): ?>
                        <?php foreach ($lotes_disponibles as $lote): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                            Lote <?= esc($lote->clave) ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Proyecto:</strong></p>
                                                <p class="text-muted"><?= esc($lote->proyecto_nombre ?? 'N/A') ?></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Manzana:</strong></p>
                                                <p class="text-muted"><?= esc($lote->manzana_nombre ?? 'N/A') ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Superficie:</strong></p>
                                                <p class="text-info"><i class="fas fa-expand-arrows-alt mr-1"></i><?= number_format($lote->area, 2) ?> m²</p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Precio/m²:</strong></p>
                                                <p class="text-warning">$<?= number_format($lote->precio_m2, 2) ?></p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="text-center">
                                            <h4 class="text-success mb-3">
                                                <strong>$<?= number_format($lote->precio_total, 2) ?></strong>
                                            </h4>
                                            <p class="text-muted mb-3">Precio total del lote</p>
                                            <a href="<?= site_url('/admin/ventas/configurar/' . $lote->id) ?>" 
                                               class="btn btn-success btn-lg btn-block">
                                                <i class="fas fa-shopping-cart mr-2"></i>
                                                Comprar Este Lote
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <small>
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Estado: Disponible
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-home fa-5x text-muted mb-3"></i>
                                <h3 class="text-muted">No hay lotes disponibles</h3>
                                <p class="text-muted">En este momento no hay lotes disponibles para venta con los filtros seleccionados.</p>
                                <a href="<?= site_url('/admin/lotes') ?>" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i>
                                    Gestionar Lotes
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Vista de lista -->
                <div id="listView" class="">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="lotesTable" style="font-size: 0.9rem;">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="3%">#</th>
                                    <th width="7%">Clave</th>
                                    <th width="7%">Empresa</th>
                                    <th width="9%">Proyecto</th>
                                    <th width="5%">Tipo</th>
                                    <th width="7%">División</th>
                                    <th width="6%">Manzana</th>
                                    <th width="8%">Categoría</th>
                                    <th width="5%">N° Lote</th>
                                    <th width="12%">Dimensiones</th>
                                    <th width="7%">$ m²</th>
                                    <th width="7%">Total</th>
                                    <th width="11%">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargan via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* Tipografía mejorada */
#lotesTable {
    font-size: 0.9rem !important;
}

#lotesTable td {
    padding: 0.6rem 0.4rem !important;
    vertical-align: middle !important;
    line-height: 1.3 !important;
}

#lotesTable th {
    padding: 0.7rem 0.4rem !important;
    font-size: 0.85rem !important;
    font-weight: bold !important;
    background-color: #343a40 !important;
    color: white !important;
    text-align: center !important;
}

/* Estilos para dimensiones y botón desplegable */
.dimension-main strong {
    font-size: 0.95rem !important;
    color: #007bff !important;
}

.btn-toggle-details {
    padding: 0.2rem 0.4rem !important;
    font-size: 0.7rem !important;
    border-radius: 50% !important;
    width: 24px !important;
    height: 24px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.btn-toggle-details:hover {
    background-color: #17a2b8 !important;
    color: white !important;
}

/* Child row styling - Más compacto */
.child-row-details {
    background-color: #f8f9fa !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 3px !important;
    padding: 8px 12px !important;
    margin: 2px 0 !important;
}

.detail-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) !important;
    gap: 6px !important;
}

.detail-item {
    background: white !important;
    padding: 4px 8px !important;
    border-radius: 3px !important;
    border-left: 2px solid #007bff !important;
    min-height: auto !important;
}

.detail-item .label {
    font-weight: 600 !important;
    color: #6c757d !important;
    font-size: 0.7rem !important;
    margin-bottom: 1px !important;
    line-height: 1.1 !important;
}

.detail-item .value {
    color: #007bff !important;
    font-size: 0.75rem !important;
    line-height: 1.1 !important;
}

/* Botones */
#lotesTable .btn {
    font-size: 0.8rem !important;
    padding: 0.5rem 0.8rem !important;
    line-height: 1.2 !important;
}

/* DataTables controls */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    height: 32px !important;
    font-size: 0.9rem !important;
}

.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    font-size: 0.9rem !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #lotesTable {
        font-size: 0.8rem !important;
    }
    
    .detail-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    let table =         // Configuración global de DataTables aplicada desde datatables-config.js
 $('#lotesTable').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "<?= site_url('/admin/ventas/datatables') ?>",
            "type": "GET",
            "data": function(d) {
                d.proyecto_id = $('#proyecto_id').val();
            }
        },
        "columns": [
            { "data": "indice", "orderable": false, "className": "text-center" },
            { "data": "clave", "orderable": true, "className": "text-center" },
            { "data": "empresa", "orderable": true, "className": "text-left" },
            { "data": "proyecto", "orderable": true, "className": "text-left" },
            { "data": "tipo", "orderable": true, "className": "text-center" },
            { "data": "division", "orderable": true, "className": "text-left" },
            { "data": "manzana", "orderable": true, "className": "text-center" },
            { "data": "categoria", "orderable": true, "className": "text-left" },
            { "data": "numero_lote", "orderable": true, "className": "text-center" },
            { "data": "dimensiones", "orderable": false, "className": "text-left" },
            { "data": "precio_m2", "orderable": true, "className": "text-right" },
            { "data": "total", "orderable": true, "className": "text-right" },
            { "data": "accion", "orderable": false, "className": "text-center" }
        ],
        "order": [[1, "asc"]],
        "pageLength": 50,
        "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "Todos"]],
        "responsive": true,
        "autoWidth": false,
        "dom": 'Brtip',
        "buttons": [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-info btn-sm'
            }
        ],
        "initComplete": function () {
            var api = this.api();
            
            // Configurar filtros independientes
            var filtros = [
                { columna: 2, selector: '#filtro-empresa' },
                { columna: 3, selector: '#filtro-proyecto' },
                { columna: 4, selector: '#filtro-tipo' },
                { columna: 5, selector: '#filtro-division' },
                { columna: 7, selector: '#filtro-categoria' }
            ];
            
            filtros.forEach(function(filtro) {
                var column = api.column(filtro.columna);
                var select = $(filtro.selector);
                
                // Llenar opciones del select
                column.data().unique().sort().each(function (d, j) {
                    if (d && d.trim() !== '' && d !== 'N/A') {
                        select.append('<option value="' + d + '">' + d + '</option>');
                    }
                });
                
                // Evento de cambio
                select.on('change', function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                });
            });
        }
    });

    // Función para generar el contenido de child row - Versión compacta
    function formatChildRow(detalles) {
        return '<div class="child-row-details">' +
            '<div class="detail-grid">' +
                '<div class="detail-item">' +
                    '<div class="label">Área</div>' +
                    '<div class="value">' + detalles.area_total + '</div>' +
                '</div>' +
                '<div class="detail-item">' +
                    '<div class="label">Frente</div>' +
                    '<div class="value">' + detalles.frente + '</div>' +
                '</div>' +
                '<div class="detail-item">' +
                    '<div class="label">Fondo</div>' +
                    '<div class="value">' + detalles.fondo + '</div>' +
                '</div>' +
                '<div class="detail-item">' +
                    '<div class="label">Lat. Izq</div>' +
                    '<div class="value">' + detalles.lateral_izquierdo + '</div>' +
                '</div>' +
                '<div class="detail-item">' +
                    '<div class="label">Lat. Der</div>' +
                    '<div class="value">' + detalles.lateral_derecho + '</div>' +
                '</div>' +
                '<div class="detail-item">' +
                    '<div class="label">Construc.</div>' +
                    '<div class="value">' + detalles.construccion + '</div>' +
                '</div>' +
                (detalles.descripcion !== 'Sin descripción adicional' ? 
                    '<div class="detail-item" style="grid-column: 1 / -1;">' +
                        '<div class="label">Notas</div>' +
                        '<div class="value">' + detalles.descripcion + '</div>' +
                    '</div>' : '') +
            '</div>' +
        '</div>';
    }

    // Manejar clicks en botón de detalles
    $('#lotesTable tbody').on('click', '.btn-toggle-details', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var icon = $(this).find('i');
        
        if (row.child.isShown()) {
            // Cerrar child row
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('fa-minus').addClass('fa-plus');
            $(this).removeClass('btn-info').addClass('btn-outline-info');
        } else {
            // Abrir child row
            var rowData = row.data();
            if (rowData && rowData.detalles_terreno) {
                row.child(formatChildRow(rowData.detalles_terreno)).show();
                tr.addClass('shown');
                icon.removeClass('fa-plus').addClass('fa-minus');
                $(this).removeClass('btn-outline-info').addClass('btn-info');
            }
        }
    });

    // Los filtros automáticos ya están configurados en initComplete
});
</script>
<?= $this->endSection() ?>