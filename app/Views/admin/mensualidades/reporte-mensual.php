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
                <p class="text-muted">Análisis y reportes de cobranza mensual</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/mensualidades') ?>">Mensualidades</a></li>
                    <li class="breadcrumb-item active">Reportes</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Filtros de Reporte -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i>
                            Filtros de Reporte
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= current_url() ?>" id="formFiltrosReporte">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Mes</label>
                                        <select name="mes" class="form-control">
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($filtros['mes'] == $i) ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                            </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Año</label>
                                        <select name="año" class="form-control">
                                            <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($filtros['año'] == $i) ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Vendedor</label>
                                        <select name="vendedor_id" class="form-control">
                                            <option value="">Todos los vendedores</option>
                                            <?php if (!empty($vendedores_disponibles)): ?>
                                                <?php foreach ($vendedores_disponibles as $vendedor): ?>
                                                <option value="<?= $vendedor->id ?>" 
                                                        <?= ($filtros['vendedor_id'] == $vendedor->id) ? 'selected' : '' ?>>
                                                    <?= esc($vendedor->nombres . ' ' . $vendedor->apellido_paterno) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tipo de Reporte</label>
                                        <select name="tipo_reporte" class="form-control">
                                            <option value="cobranza" <?= ($filtros['tipo_reporte'] === 'cobranza') ? 'selected' : '' ?>>
                                                Cobranza
                                            </option>
                                            <option value="eficiencia" <?= ($filtros['tipo_reporte'] === 'eficiencia') ? 'selected' : '' ?>>
                                                Eficiencia
                                            </option>
                                            <option value="morosidad" <?= ($filtros['tipo_reporte'] === 'morosidad') ? 'selected' : '' ?>>
                                                Morosidad
                                            </option>
                                            <option value="comisiones" <?= ($filtros['tipo_reporte'] === 'comisiones') ? 'selected' : '' ?>>
                                                Comisiones
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Generar Reporte
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="exportarReporte('excel')">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="exportarReporte('pdf')">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="enviarReportePorEmail()">
                                        <i class="fas fa-envelope"></i> Enviar por Email
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen Ejecutivo -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>$<?= number_format($resumen_ejecutivo['total_cobrado'], 0) ?></h3>
                        <p>Total Cobrado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $resumen_ejecutivo['mensualidades_cobradas'] ?></h3>
                        <p>Mensualidades Cobradas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($resumen_ejecutivo['eficiencia_cobranza'], 1) ?>%</h3>
                        <p>Eficiencia de Cobranza</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $resumen_ejecutivo['clientes_morosos'] ?></h3>
                        <p>Clientes Morosos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido Específico del Reporte -->
        <?php if ($filtros['tipo_reporte'] === 'cobranza'): ?>
        <!-- Reporte de Cobranza -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cobranza por Día</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartCobranzaDiaria" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cobranza por Vendedor</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartCobranzaVendedor" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalle de Cobranza</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaCobranza">
                                <thead>
                                    <tr>
                                        <th>Vendedor</th>
                                        <th>Mensualidades Asignadas</th>
                                        <th>Mensualidades Cobradas</th>
                                        <th>Monto Cobrado</th>
                                        <th>Eficiencia (%)</th>
                                        <th>Promedio por Mensualidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($datos_reporte['cobranza_vendedores'])): ?>
                                        <?php foreach ($datos_reporte['cobranza_vendedores'] as $vendedor): ?>
                                        <tr>
                                            <td><strong><?= esc($vendedor->nombre_vendedor) ?></strong></td>
                                            <td><?= $vendedor->mensualidades_asignadas ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?= $vendedor->mensualidades_cobradas ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    $<?= number_format($vendedor->monto_cobrado, 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $vendedor->eficiencia >= 80 ? 'success' : ($vendedor->eficiencia >= 60 ? 'warning' : 'danger') ?>">
                                                    <?= number_format($vendedor->eficiencia, 1) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                $<?= number_format($vendedor->promedio_mensualidad, 0) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($filtros['tipo_reporte'] === 'morosidad'): ?>
        <!-- Reporte de Morosidad -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Distribución de Morosidad</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartMorosidad" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Evolución de Cartera Vencida</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartEvolucionCartera" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Clientes con Mayor Morosidad</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaMorosidad">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Mensualidades Vencidas</th>
                                        <th>Días Promedio Atraso</th>
                                        <th>Monto Vencido</th>
                                        <th>Intereses Moratorios</th>
                                        <th>Total Adeudo</th>
                                        <th>Vendedor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($datos_reporte['clientes_morosos'])): ?>
                                        <?php foreach ($datos_reporte['clientes_morosos'] as $moroso): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($moroso->nombre_cliente) ?></strong><br>
                                                <small class="text-muted"><?= esc($moroso->telefono ?? 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    <?= $moroso->mensualidades_vencidas ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    <?= number_format($moroso->dias_promedio_atraso) ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-danger">
                                                    $<?= number_format($moroso->monto_vencido, 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="text-warning">
                                                    $<?= number_format($moroso->intereses_moratorios, 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-danger">
                                                    $<?= number_format($moroso->total_adeudo, 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?= esc($moroso->nombre_vendedor) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($filtros['tipo_reporte'] === 'eficiencia'): ?>
        <!-- Reporte de Eficiencia -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Eficiencia de Cobranza por Vendedor</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartEficiencia" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ranking de Eficiencia</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaEficiencia">
                                <thead>
                                    <tr>
                                        <th>Posición</th>
                                        <th>Vendedor</th>
                                        <th>Meta Asignada</th>
                                        <th>Cobranza Realizada</th>
                                        <th>% Cumplimiento</th>
                                        <th>Días Promedio Cobranza</th>
                                        <th>Calificación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($datos_reporte['ranking_eficiencia'])): ?>
                                        <?php foreach ($datos_reporte['ranking_eficiencia'] as $index => $vendedor): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-<?= $index < 3 ? 'success' : 'secondary' ?>">
                                                    <?= $index + 1 ?>
                                                </span>
                                            </td>
                                            <td><strong><?= esc($vendedor->nombre_vendedor) ?></strong></td>
                                            <td>$<?= number_format($vendedor->meta_asignada, 0) ?></td>
                                            <td>
                                                <strong class="text-success">
                                                    $<?= number_format($vendedor->cobranza_realizada, 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $vendedor->cumplimiento >= 100 ? 'success' : ($vendedor->cumplimiento >= 80 ? 'warning' : 'danger') ?>">
                                                    <?= number_format($vendedor->cumplimiento, 1) ?>%
                                                </span>
                                            </td>
                                            <td><?= number_format($vendedor->dias_promedio_cobranza, 1) ?> días</td>
                                            <td>
                                                <?php 
                                                $estrellas = min(5, max(1, round($vendedor->calificacion)));
                                                for ($i = 1; $i <= 5; $i++): 
                                                ?>
                                                    <i class="fas fa-star <?= $i <= $estrellas ? 'text-warning' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($filtros['tipo_reporte'] === 'comisiones'): ?>
        <!-- Reporte de Comisiones -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Comisiones Generadas</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartComisiones" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Estado de Comisiones</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartEstadoComisiones" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalle de Comisiones por Vendedor</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tablaComisiones">
                                <thead>
                                    <tr>
                                        <th>Vendedor</th>
                                        <th>Ventas del Período</th>
                                        <th>Monto Cobrado</th>
                                        <th>Comisión Generada</th>
                                        <th>Comisión Pagada</th>
                                        <th>Comisión Pendiente</th>
                                        <th>% Comisión</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($datos_reporte['comisiones_vendedores'])): ?>
                                        <?php foreach ($datos_reporte['comisiones_vendedores'] as $comision): ?>
                                        <tr>
                                            <td><strong><?= esc($comision->nombre_vendedor) ?></strong></td>
                                            <td><?= $comision->ventas_periodo ?></td>
                                            <td>
                                                <strong class="text-success">
                                                    $<?= number_format($comision->monto_cobrado, 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <strong class="text-info">
                                                    $<?= number_format($comision->comision_generada, 0) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="text-success">
                                                    $<?= number_format($comision->comision_pagada, 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-warning">
                                                    $<?= number_format($comision->comision_pendiente, 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= number_format($comision->porcentaje_comision, 2) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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

<!-- Modal para Envío por Email -->
<div class="modal fade" id="modalEnviarEmail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Enviar Reporte por Email</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formEnviarEmail" method="POST" action="<?= site_url('/admin/mensualidades/enviarReportePorEmail') ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Destinatarios <span class="text-danger">*</span></label>
                        <input type="email" name="destinatarios" class="form-control" 
                               placeholder="email1@ejemplo.com, email2@ejemplo.com..." required>
                        <small class="form-text text-muted">
                            Separe múltiples emails con comas
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Asunto</label>
                        <input type="text" name="asunto" class="form-control" 
                               value="Reporte Mensual - <?= date('F Y', mktime(0,0,0,$filtros['mes'],1,$filtros['año'])) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Mensaje</label>
                        <textarea name="mensaje" class="form-control" rows="4" 
                                  placeholder="Mensaje adicional..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" 
                                   name="incluir_excel" id="incluirExcel" checked>
                            <label class="custom-control-label" for="incluirExcel">
                                Incluir archivo Excel
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" 
                                   name="incluir_pdf" id="incluirPdf" checked>
                            <label class="custom-control-label" for="incluirPdf">
                                Incluir archivo PDF
                            </label>
                        </div>
                    </div>
                    
                    <input type="hidden" name="filtros_reporte" value="<?= htmlspecialchars(json_encode($filtros)) ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-envelope"></i> Enviar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('/assets/plugins/chart.js/Chart.bundle.js') ?>"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTables
    $('.table').DataTable({
        "responsive": true,
        "pageLength": 25,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
    
    // Inicializar gráficos según el tipo de reporte
    <?php if ($filtros['tipo_reporte'] === 'cobranza'): ?>
    initChartCobranza();
    <?php elseif ($filtros['tipo_reporte'] === 'morosidad'): ?>
    initChartMorosidad();
    <?php elseif ($filtros['tipo_reporte'] === 'eficiencia'): ?>
    initChartEficiencia();
    <?php elseif ($filtros['tipo_reporte'] === 'comisiones'): ?>
    initChartComisiones();
    <?php endif; ?>
});

// Funciones para gráficos
function initChartCobranza() {
    <?php if (!empty($datos_graficos['cobranza_diaria'])): ?>
    // Gráfico de cobranza diaria
    var ctx = document.getElementById('chartCobranzaDiaria').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($datos_graficos['cobranza_diaria'])) ?>,
            datasets: [{
                label: 'Cobranza Diaria',
                data: <?= json_encode(array_values($datos_graficos['cobranza_diaria'])) ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($datos_graficos['cobranza_vendedores'])): ?>
    // Gráfico de cobranza por vendedor
    var ctx2 = document.getElementById('chartCobranzaVendedor').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($datos_graficos['cobranza_vendedores'], 'nombre')) ?>,
            datasets: [{
                label: 'Monto Cobrado',
                data: <?= json_encode(array_column($datos_graficos['cobranza_vendedores'], 'monto')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
}

function initChartMorosidad() {
    // Implementar gráficos de morosidad
    console.log('Inicializando gráficos de morosidad');
}

function initChartEficiencia() {
    // Implementar gráficos de eficiencia
    console.log('Inicializando gráficos de eficiencia');
}

function initChartComisiones() {
    // Implementar gráficos de comisiones
    console.log('Inicializando gráficos de comisiones');
}

// Funciones de exportación
function exportarReporte(formato) {
    var filtros = $('#formFiltrosReporte').serialize();
    var url = '<?= site_url("/admin/mensualidades/exportarReporte") ?>?' + filtros + '&formato=' + formato;
    window.open(url, '_blank');
}

// Función para enviar por email
function enviarReportePorEmail() {
    $('#modalEnviarEmail').modal('show');
}

// Validación del formulario de email
$('#formEnviarEmail').on('submit', function(e) {
    var destinatarios = $('input[name="destinatarios"]').val().trim();
    if (!destinatarios) {
        alert('Ingrese al menos un destinatario');
        e.preventDefault();
        return false;
    }
    
    // Validar formato de emails
    var emails = destinatarios.split(',');
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    for (var i = 0; i < emails.length; i++) {
        var email = emails[i].trim();
        if (!emailRegex.test(email)) {
            alert('El email "' + email + '" no tiene un formato válido');
            e.preventDefault();
            return false;
        }
    }
});
</script>
<?= $this->endSection() ?>