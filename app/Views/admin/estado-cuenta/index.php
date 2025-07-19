<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= $title ?></h1>
                <?php if(isset($subtitle)): ?>
                    <p class="text-muted"><?= $subtitle ?></p>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Estado de Cuenta</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

            <!-- Buscador Principal -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search mr-2"></i>
                                Buscar Estado de Cuenta
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="formBusqueda">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="termino_busqueda">Buscar por cliente, folio o lote</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="termino_busqueda" 
                                                   name="termino_busqueda"
                                                   placeholder="Ingrese nombre del cliente, folio de venta o clave de lote"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search mr-2"></i>
                                                Buscar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados de Búsqueda -->
            <div class="row" id="resultados-busqueda" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                Resultados de Búsqueda
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tabla-resultados">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Folio</th>
                                            <th>Lote</th>
                                            <th>Proyecto</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-resultados">
                                        <!-- Resultados dinámicos -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimas 20 Ventas -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-2"></i>
                                Últimas 20 Ventas Financiadas
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if(empty($ultimas_ventas)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No hay ventas financiadas registradas en el sistema.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Folio</th>
                                                <th>Lote</th>
                                                <th>Proyecto</th>
                                                <th>Plan</th>
                                                <th>Fecha</th>
                                                <th>Precio</th>
                                                <th>Avance</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($ultimas_ventas as $venta): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= esc($venta->nombre_cliente) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= esc($venta->email) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary"><?= esc($venta->folio_venta) ?></span>
                                                </td>
                                                <td>
                                                    <strong><?= esc($venta->clave_lote) ?></strong>
                                                    <br>
                                                    <small class="text-muted">#<?= esc($venta->numero_lote) ?></small>
                                                </td>
                                                <td>
                                                    <span class="text-muted"><?= esc($venta->proyecto_nombre ?: 'Sin proyecto') ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $venta->promocion_cero_enganche ? 'success' : 'info' ?>">
                                                        <?= esc($venta->modalidad) ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted"><?= $venta->meses_financiamiento ?> meses</small>
                                                </td>
                                                <td>
                                                    <span class="text-muted"><?= date('d/m/Y', strtotime($venta->fecha_venta)) ?></span>
                                                </td>
                                                <td>
                                                    <strong>$<?= number_format($venta->precio_venta_final, 0) ?></strong>
                                                </td>
                                                <td>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar bg-<?= $venta->porcentaje_avance >= 80 ? 'success' : ($venta->porcentaje_avance >= 50 ? 'warning' : 'danger') ?>" 
                                                             style="width: <?= $venta->porcentaje_avance ?>%">
                                                        </div>
                                                    </div>
                                                    <span class="text-muted"><?= number_format($venta->porcentaje_avance, 1) ?>%</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $venta->estatus_venta === 'activa' ? 'success' : 'info' ?>">
                                                        <?= ucfirst($venta->estatus_venta) ?>
                                                    </span>
                                                    <?php if(!$venta->tiene_cuenta): ?>
                                                        <br>
                                                        <small class="text-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> Sin cuenta
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="<?= site_url('admin/estado-cuenta/venta/' . $venta->id) ?>" 
                                                           class="btn btn-sm btn-primary"
                                                           title="Ver estado de cuenta">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if($venta->tiene_cuenta): ?>
                                                            <a href="<?= site_url('admin/pagos/procesar-mensualidad/' . $venta->id) ?>" 
                                                               class="btn btn-sm btn-success"
                                                               title="Procesar mensualidad">
                                                                <i class="fas fa-credit-card"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="<?= site_url('debug/amortizacion/debug-venta/' . $venta->id) ?>" 
                                                               class="btn btn-sm btn-warning"
                                                               title="Debug - Generar cuenta"
                                                               target="_blank">
                                                                <i class="fas fa-tools"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                Por Cliente
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Consulte el estado de cuenta ingresando directamente el ID del cliente:</p>
                            <form action="<?= site_url('admin/estado-cuenta/cliente') ?>" method="GET">
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           name="cliente_id" 
                                           placeholder="ID del cliente" 
                                           required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-contract mr-2"></i>
                                Por Venta
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Consulte el estado de cuenta ingresando directamente el ID de la venta:</p>
                            <form action="<?= site_url('admin/estado-cuenta/venta') ?>" method="GET">
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           name="venta_id" 
                                           placeholder="ID de la venta" 
                                           required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</section>

<script>
$(document).ready(function() {
    // Configurar búsqueda con autocompletado
    $('#termino_busqueda').on('input', function() {
        const termino = $(this).val();
        
        if (termino.length >= 3) {
            buscarVentas(termino);
        } else {
            $('#resultados-busqueda').hide();
        }
    });

    // Manejar envío del formulario
    $('#formBusqueda').on('submit', function(e) {
        e.preventDefault();
        const termino = $('#termino_busqueda').val();
        if (termino.length >= 3) {
            buscarVentas(termino);
        }
    });

    function buscarVentas(termino) {
        $.ajax({
            url: '<?= site_url('admin/estado-cuenta/buscar') ?>',
            method: 'GET',
            data: { q: termino },
            success: function(response) {
                mostrarResultados(response);
            },
            error: function() {
                toastr.error('Error al realizar la búsqueda');
            }
        });
    }

    function mostrarResultados(ventas) {
        const tbody = $('#tbody-resultados');
        tbody.empty();

        if (ventas.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        No se encontraron resultados
                    </td>
                </tr>
            `);
        } else {
            ventas.forEach(function(venta) {
                tbody.append(`
                    <tr>
                        <td>${venta.nombre_cliente}</td>
                        <td>${venta.folio_venta}</td>
                        <td>${venta.clave_lote}</td>
                        <td>${venta.proyecto_nombre || 'Sin proyecto'}</td>
                        <td>
                            <span class="badge badge-${venta.estatus_venta === 'activa' ? 'success' : 'info'}">
                                ${venta.estatus_venta}
                            </span>
                        </td>
                        <td>
                            <a href="<?= site_url('admin/estado-cuenta/venta') ?>/${venta.id}" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye mr-1"></i>
                                Ver Estado
                            </a>
                        </td>
                    </tr>
                `);
            });
        }

        $('#resultados-busqueda').show();
    }
});
</script>
<?= $this->endSection() ?>