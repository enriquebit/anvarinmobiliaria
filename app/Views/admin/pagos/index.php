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
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= $title ?></li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
            
            <!-- Estadísticas principales -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $estadisticas['total_ventas_activas'] ?></h3>
                            <p>Ventas Activas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $estadisticas['total_cuentas_activas'] ?></h3>
                            <p>Cuentas Activas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>$<?= number_format($estadisticas['monto_pendiente_total'], 2) ?></h3>
                            <p>Monto Pendiente</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $estadisticas['pagos_mes_actual'] ?></h3>
                            <p>Pagos Este Mes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buscador de ventas -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search"></i>
                                Buscar Venta para Procesar Pagos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Buscar por Folio, Cliente o Lote</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="buscar-venta" 
                                               placeholder="Ingrese folio de venta, nombre de cliente o clave de lote">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" 
                                                class="btn btn-primary btn-block" 
                                                onclick="buscarVentas()">
                                            <i class="fas fa-search"></i>
                                            Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resultados de búsqueda -->
                            <div id="resultados-busqueda" class="mt-3" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Cliente</th>
                                                <th>Lote</th>
                                                <th>Proyecto</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-resultados">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ventas activas recientes -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i>
                                Ventas Activas Recientes
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Cliente</th>
                                            <th>Lote</th>
                                            <th>Proyecto</th>
                                            <th>Fecha Venta</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($ventas_activas as $venta): ?>
                                        <tr>
                                            <td><?= $venta->folio_venta ?></td>
                                            <td><?= $venta->nombres . ' ' . $venta->apellido_paterno . ' ' . $venta->apellido_materno ?></td>
                                            <td><?= $venta->lote_clave ?></td>
                                            <td><?= $venta->proyecto_nombre ?></td>
                                            <td><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?= site_url("admin/pagos/detalle/{$venta->cliente_id}/{$venta->lote_id}") ?>" 
                                                       class="btn btn-sm btn-info"
                                                       title="Ver Detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= site_url("admin/pagos/procesar-apartado/{$venta->id}") ?>" 
                                                       class="btn btn-sm btn-primary"
                                                       title="Procesar Apartado">
                                                        <i class="fas fa-hand-holding-usd"></i>
                                                    </a>
                                                    <a href="<?= site_url("admin/pagos/liquidar-enganche/{$venta->id}") ?>" 
                                                       class="btn btn-sm btn-success"
                                                       title="Liquidar Enganche">
                                                        <i class="fas fa-check-circle"></i>
                                                    </a>
                                                    <a href="<?= site_url("admin/pagos/procesar-mensualidad/{$venta->id}") ?>" 
                                                       class="btn btn-sm btn-warning"
                                                       title="Procesar Mensualidad">
                                                        <i class="fas fa-calendar-check"></i>
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
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let busquedaTimeout;

function buscarVentas() {
    const termino = $('#buscar-venta').val().trim();
    
    if (termino.length < 2) {
        alert('Ingrese al menos 2 caracteres para buscar');
        return;
    }
    
    // Mostrar loading
    $('#resultados-busqueda').show();
    $('#tbody-resultados').html(`
        <tr>
            <td colspan="6" class="text-center">
                <i class="fas fa-spinner fa-spin"></i> Buscando...
            </td>
        </tr>
    `);
    
    // Realizar búsqueda AJAX
    $.ajax({
        url: '<?= site_url("admin/pagos/buscar-ventas") ?>',
        method: 'GET',
        data: { q: termino },
        dataType: 'json',
        success: function(response) {
            mostrarResultados(response);
        },
        error: function() {
            $('#tbody-resultados').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al buscar
                    </td>
                </tr>
            `);
        }
    });
}

function mostrarResultados(ventas) {
    let html = '';
    
    if (ventas.length === 0) {
        html = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-search"></i> No se encontraron ventas
                </td>
            </tr>
        `;
    } else {
        ventas.forEach(function(venta) {
            const nombreCompleto = `${venta.nombres} ${venta.apellido_paterno} ${venta.apellido_materno}`;
            html += `
                <tr>
                    <td>${venta.folio_venta}</td>
                    <td>${nombreCompleto}</td>
                    <td>${venta.lote_clave}</td>
                    <td>${venta.proyecto_nombre}</td>
                    <td><span class="badge badge-${getStatusColor(venta.estatus_venta)}">${venta.estatus_venta}</span></td>
                    <td>
                        <div class="btn-group">
                            <a href="<?= site_url("admin/pagos/detalle/") ?>${venta.cliente_id}/${venta.lote_id}" 
                               class="btn btn-sm btn-info"
                               title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= site_url("admin/pagos/procesar-apartado/") ?>${venta.id}" 
                               class="btn btn-sm btn-primary"
                               title="Procesar Apartado">
                                <i class="fas fa-hand-holding-usd"></i>
                            </a>
                            <a href="<?= site_url("admin/pagos/liquidar-enganche/") ?>${venta.id}" 
                               class="btn btn-sm btn-success"
                               title="Liquidar Enganche">
                                <i class="fas fa-check-circle"></i>
                            </a>
                            <a href="<?= site_url("admin/pagos/procesar-mensualidad/") ?>${venta.id}" 
                               class="btn btn-sm btn-warning"
                               title="Procesar Mensualidad">
                                <i class="fas fa-calendar-check"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#tbody-resultados').html(html);
}

function getStatusColor(status) {
    switch(status) {
        case 'activa': return 'success';
        case 'completada': return 'primary';
        case 'cancelada': return 'danger';
        default: return 'secondary';
    }
}

// Búsqueda en tiempo real
$('#buscar-venta').on('input', function() {
    clearTimeout(busquedaTimeout);
    
    busquedaTimeout = setTimeout(function() {
        if ($('#buscar-venta').val().trim().length >= 2) {
            buscarVentas();
        } else {
            $('#resultados-busqueda').hide();
        }
    }, 500);
});

// Actualizar estadísticas cada 30 segundos
setInterval(function() {
    $.ajax({
        url: '<?= site_url("admin/pagos/dashboard-pagos") ?>',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            // Actualizar estadísticas sin recargar la página
            $('.small-box.bg-info .inner h3').text(response.total_ventas_activas);
            $('.small-box.bg-success .inner h3').text(response.total_cuentas_activas);
            $('.small-box.bg-warning .inner h3').text('$' + new Intl.NumberFormat('es-MX').format(response.monto_pendiente_total));
            $('.small-box.bg-danger .inner h3').text(response.pagos_mes_actual);
        }
    });
}, 30000);
</script>
<?= $this->endSection() ?>