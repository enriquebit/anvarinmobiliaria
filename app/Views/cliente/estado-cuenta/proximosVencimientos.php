<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Próximos Vencimientos<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Mis Próximos Vencimientos<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/estado-cuenta') ?>">Estado de Cuenta</a></li>
<li class="breadcrumb-item active">Próximos Vencimientos</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Alertas críticas -->
<?php if (!empty($vencimientos_criticos)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Pagos Vencidos!</h5>
            <p>Tienes <strong><?= count($vencimientos_criticos) ?></strong> mensualidades vencidas que requieren atención inmediata.</p>
            <a href="#vencidos" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-down"></i> Ver Detalles
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Resumen de vencimientos -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= count($vencimientos_criticos ?? []) ?></h3>
                <p>Vencidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="#vencidos" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-down"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= count($vencimientos_hoy ?? []) ?></h3>
                <p>Vencen Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="#hoy" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-down"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= count($vencimientos_semana ?? []) ?></h3>
                <p>Esta Semana</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-week"></i>
            </div>
            <a href="#semana" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-down"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= count($vencimientos_mes ?? []) ?></h3>
                <p>Este Mes</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="#mes" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-down"></i></a>
        </div>
    </div>
</div>

<!-- Vista de Calendario -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar mr-2"></i>
                    Calendario de Vencimientos
                </h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" onclick="cambiarVista('mes')">
                            <i class="fas fa-calendar-alt"></i> Mes
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="cambiarVista('semana')">
                            <i class="fas fa-calendar-week"></i> Semana
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="cambiarVista('lista')">
                            <i class="fas fa-list"></i> Lista
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="calendario-vencimientos"></div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Pagos Vencidos -->
<?php if (!empty($vencimientos_criticos)): ?>
<div class="row" id="vencidos">
    <div class="col-12">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Pagos Vencidos - Acción Requerida
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Mensualidad</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Vencida</th>
                                <th>Monto Original</th>
                                <th>Mora Acumulada</th>
                                <th>Total a Pagar</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vencimientos_criticos as $mensualidad): ?>
                            <tr>
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
                                    <strong><?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-danger">
                                        <?= $mensualidad->dias_atraso ?> días
                                    </span>
                                </td>
                                <td>
                                    $<?= number_format($mensualidad->monto_total, 0) ?>
                                </td>
                                <td>
                                    <span class="text-warning">
                                        <strong>$<?= number_format($mensualidad->interes_moratorio, 0) ?></strong>
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
                                        <i class="fas fa-dollar-sign"></i> Pagar Ahora
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-8">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle mr-1"></i>Importante:</h6>
                            <p class="mb-0">Los pagos vencidos generan intereses moratorios diarios. Te recomendamos ponerte al corriente lo antes posible para evitar cargos adicionales.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <h5>Total a Regularizar:</h5>
                        <h3 class="text-danger">
                            $<?= number_format(array_sum(array_map(function($m) { return $m->monto_total + $m->interes_moratorio; }, $vencimientos_criticos)), 0) ?>
                        </h3>
                        <a href="<?= site_url('/cliente/pagos') ?>" class="btn btn-danger btn-lg">
                            <i class="fas fa-credit-card"></i> Realizar Pago Múltiple
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Vencimientos de Hoy -->
<?php if (!empty($vencimientos_hoy)): ?>
<div class="row" id="hoy">
    <div class="col-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-2"></i>
                    Vencimientos de Hoy - <?= date('d/m/Y') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($vencimientos_hoy as $mensualidad): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <?= esc($mensualidad->lote_clave ?? 'N/A') ?>
                                </h5>
                                <div class="card-tools">
                                    <span class="badge badge-warning">Vence Hoy</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><strong>Mensualidad:</strong> #<?= $mensualidad->numero_pago ?></p>
                                <p><strong>Monto:</strong> $<?= number_format($mensualidad->monto_total, 0) ?></p>
                                <p><small class="text-muted">Folio: <?= esc($mensualidad->folio_venta) ?></small></p>
                            </div>
                            <div class="card-footer">
                                <a href="<?= site_url('/cliente/pagos/mensualidad/' . $mensualidad->id) ?>" 
                                   class="btn btn-warning btn-block">
                                    <i class="fas fa-dollar-sign"></i> Pagar Hoy
                                </a>
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

<!-- Vencimientos de Esta Semana -->
<?php if (!empty($vencimientos_semana)): ?>
<div class="row" id="semana">
    <div class="col-12">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-week mr-2"></i>
                    Vencimientos de Esta Semana
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Mensualidad</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Restantes</th>
                                <th>Monto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vencimientos_semana as $mensualidad): ?>
                            <?php 
                            $dias_restantes = max(0, (strtotime($mensualidad->fecha_vencimiento) - time()) / (60 * 60 * 24));
                            ?>
                            <tr>
                                <td>
                                    <strong><?= esc($mensualidad->lote_clave ?? 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= esc($mensualidad->folio_venta) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        #<?= $mensualidad->numero_pago ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?></strong><br>
                                    <small class="text-muted"><?= ucfirst(strftime('%A', strtotime($mensualidad->fecha_vencimiento))) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $dias_restantes <= 2 ? 'warning' : 'info' ?>">
                                        <?= floor($dias_restantes) ?> días
                                    </span>
                                </td>
                                <td>
                                    <strong>$<?= number_format($mensualidad->monto_total, 0) ?></strong>
                                </td>
                                <td>
                                    <a href="<?= site_url('/cliente/pagos/mensualidad/' . $mensualidad->id) ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-dollar-sign"></i> Pagar
                                    </a>
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

<!-- Vencimientos de Este Mes -->
<?php if (!empty($vencimientos_mes)): ?>
<div class="row" id="mes">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Vencimientos de Este Mes - <?= ucfirst(strftime('%B %Y')) ?>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="tabla-vencimientos-mes">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Propiedad</th>
                                <th>Mensualidad</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vencimientos_mes as $mensualidad): ?>
                            <tr>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) ?></strong><br>
                                    <small class="text-muted"><?= ucfirst(strftime('%A', strtotime($mensualidad->fecha_vencimiento))) ?></small>
                                </td>
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
                                    <strong>$<?= number_format($mensualidad->monto_total, 0) ?></strong>
                                    <?php if ($mensualidad->interes_moratorio > 0): ?>
                                    <br><small class="text-warning">
                                        + $<?= number_format($mensualidad->interes_moratorio, 0) ?> mora
                                    </small>
                                    <?php endif; ?>
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
                                    <?php if ($mensualidad->estatus === 'pagada'): ?>
                                        <a href="<?= site_url('/cliente/pagos/comprobante/' . $mensualidad->ultimo_pago_id) ?>" 
                                           class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= site_url('/cliente/pagos/mensualidad/' . $mensualidad->id) ?>" 
                                           class="btn btn-success btn-xs">
                                            <i class="fas fa-dollar-sign"></i> Pagar
                                        </a>
                                    <?php endif; ?>
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

<!-- Recordatorios y Tips -->
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Consejos para Mantener tu Cuenta al Día
                </h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i>
                        <strong>Configura recordatorios:</strong> Programa alertas en tu calendario para no olvidar las fechas de vencimiento.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i>
                        <strong>Paga con anticipación:</strong> Realiza tus pagos unos días antes del vencimiento para evitar cualquier inconveniente.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i>
                        <strong>Guarda tus comprobantes:</strong> Mantén un registro de todos tus pagos y comprobantes.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success mr-2"></i>
                        <strong>Reporta tus pagos:</strong> Si realizas un pago y no se refleja inmediatamente, repórtalo usando nuestro sistema.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-headset mr-2"></i>
                    ¿Necesitas Ayuda?
                </h3>
            </div>
            <div class="card-body text-center">
                <p>Nuestro equipo está listo para ayudarte con cualquier duda sobre tus pagos.</p>
                
                <div class="btn-group-vertical w-100">
                    <a href="<?= site_url('/cliente/soporte') ?>" class="btn btn-success mb-2">
                        <i class="fas fa-headset"></i> Contactar Soporte
                    </a>
                    <a href="<?= site_url('/cliente/ayuda/faq') ?>" class="btn btn-outline-success mb-2">
                        <i class="fas fa-question-circle"></i> Preguntas Frecuentes
                    </a>
                    <a href="<?= site_url('/cliente/pagos/reportarPago') ?>" class="btn btn-outline-success">
                        <i class="fas fa-bell"></i> Reportar Pago
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- FullCalendar -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/fullcalendar/main.css') ?>">
<script src="<?= base_url('assets/plugins/fullcalendar/main.js') ?>"></script>
<script src="<?= base_url('assets/plugins/fullcalendar/locales/es.js') ?>"></script>

<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // Inicializar calendario
    inicializarCalendario();
    
    // Configurar DataTable para vencimientos del mes
    $('#tabla-vencimientos-mes').DataTable({
        "language": {
            "url": "<?= base_url('assets/plugins/datatables/i18n/Spanish.json') ?>"
        },
        "pageLength": 15,
        "order": [[0, 'asc']],
        "columnDefs": [
            { "orderable": false, "targets": [5] }
        ]
    });
    
    // Scroll suave para las secciones
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
});

// Variable global para el calendario
var calendar;

// Inicializar calendario
function inicializarCalendario() {
    var calendarEl = document.getElementById('calendario-vencimientos');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        events: [
            <?php if (!empty($eventos_calendario)): ?>
                <?php foreach ($eventos_calendario as $evento): ?>
                {
                    title: '<?= esc($evento['titulo']) ?>',
                    start: '<?= $evento['fecha'] ?>',
                    className: '<?= $evento['clase_css'] ?>',
                    url: '<?= $evento['url'] ?>',
                    extendedProps: {
                        monto: '<?= $evento['monto'] ?>',
                        estado: '<?= $evento['estado'] ?>'
                    }
                },
                <?php endforeach; ?>
            <?php endif; ?>
        ],
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.url) {
                window.open(info.event.url, '_blank');
            }
        },
        eventDidMount: function(info) {
            // Agregar tooltip con información del evento
            $(info.el).tooltip({
                title: info.event.title + ' - ' + info.event.extendedProps.monto,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });
    
    calendar.render();
}

// Cambiar vista del calendario
function cambiarVista(vista) {
    // Remover active de todos los botones
    $('.card-tools .btn').removeClass('btn-primary').addClass('btn-outline-primary');
    
    if (vista === 'mes') {
        calendar.changeView('dayGridMonth');
        $('button[onclick="cambiarVista(\'mes\')"]').removeClass('btn-outline-primary').addClass('btn-primary');
    } else if (vista === 'semana') {
        calendar.changeView('listWeek');
        $('button[onclick="cambiarVista(\'semana\')"]').removeClass('btn-outline-primary').addClass('btn-primary');
    } else if (vista === 'lista') {
        calendar.changeView('listMonth');
        $('button[onclick="cambiarVista(\'lista\')"]').removeClass('btn-outline-primary').addClass('btn-primary');
    }
}
</script>
<?= $this->endSection() ?>