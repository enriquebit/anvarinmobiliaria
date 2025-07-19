<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="#">Catálogos</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/catalogos/fuentes-informacion') ?>">Fuentes de Información</a></li>
<li class="breadcrumb-item active">Estadísticas</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar"></i> Estadísticas de Fuentes de Información
                            </h3>
                            <div class="card-tools">
                                <a href="<?= base_url('admin/catalogos/fuentes-informacion') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Información:</strong> Estas estadísticas muestran cuántos clientes registrados provienen de cada fuente de información, lo que ayuda a evaluar la efectividad de los diferentes canales de marketing.
                            </div>

                            <div class="row">
                                <?php 
                                $totalClientes = array_sum(array_column($estadisticas, 'total_clientes'));
                                $totalFuentes = count($estadisticas);
                                $fuentesPorcentaje = [];
                                
                                foreach ($estadisticas as $stat) {
                                    $porcentaje = $totalClientes > 0 ? round(($stat['total_clientes'] / $totalClientes) * 100, 1) : 0;
                                    $fuentesPorcentaje[] = array_merge($stat, ['porcentaje' => $porcentaje]);
                                }
                                ?>
                                
                                <!-- Resumen general -->
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon">
                                            <i class="fas fa-bullhorn"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Fuentes Activas</span>
                                            <span class="info-box-number"><?= $totalFuentes ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon">
                                            <i class="fas fa-users"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Clientes</span>
                                            <span class="info-box-number"><?= $totalClientes ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon">
                                            <i class="fas fa-trophy"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fuente Principal</span>
                                            <span class="info-box-number small">
                                                <?= !empty($fuentesPorcentaje) ? $fuentesPorcentaje[0]['nombre'] : 'N/A' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de estadísticas -->
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-table"></i> Detalle por Fuente
                                            </h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Fuente</th>
                                                        <th>Valor</th>
                                                        <th class="text-center">Clientes</th>
                                                        <th class="text-center">Porcentaje</th>
                                                        <th>Barra de Progreso</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($fuentesPorcentaje as $index => $fuente): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= $fuente['nombre'] ?></strong>
                                                        </td>
                                                        <td>
                                                            <code><?= $fuente['valor'] ?></code>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-<?= $fuente['total_clientes'] > 0 ? 'success' : 'secondary' ?>">
                                                                <?= $fuente['total_clientes'] ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <strong><?= $fuente['porcentaje'] ?>%</strong>
                                                        </td>
                                                        <td>
                                                            <div class="progress progress-sm">
                                                                <div class="progress-bar bg-<?= $index < 3 ? ['primary', 'success', 'info'][$index] : 'secondary' ?>" 
                                                                     style="width: <?= $fuente['porcentaje'] ?>%">
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    
                                                    <?php if (empty($fuentesPorcentaje)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            <i class="fas fa-info-circle"></i> No hay datos disponibles
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card card-outline card-success">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-chart-pie"></i> Top 5 Fuentes
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <?php 
                                            $topFuentes = array_slice($fuentesPorcentaje, 0, 5);
                                            $colores = ['primary', 'success', 'info', 'warning', 'secondary'];
                                            ?>
                                            
                                            <?php foreach ($topFuentes as $index => $fuente): ?>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-sm">
                                                        <i class="fas fa-circle text-<?= $colores[$index] ?> mr-1"></i>
                                                        <?= $fuente['nombre'] ?>
                                                    </span>
                                                    <strong><?= $fuente['total_clientes'] ?></strong>
                                                </div>
                                                <div class="progress progress-xs">
                                                    <div class="progress-bar bg-<?= $colores[$index] ?>" 
                                                         style="width: <?= $fuente['porcentaje'] ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>

                                            <?php if (empty($topFuentes)): ?>
                                            <div class="text-center text-muted">
                                                <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                                <p>No hay datos para mostrar</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Recomendaciones -->
                                    <div class="card card-outline card-warning">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-lightbulb"></i> Recomendaciones
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled small">
                                                <?php if (!empty($fuentesPorcentaje)): ?>
                                                    <?php if ($fuentesPorcentaje[0]['porcentaje'] > 50): ?>
                                                    <li class="mb-2">
                                                        <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                                                        <strong>Diversificar:</strong> Una sola fuente representa más del 50% de clientes.
                                                    </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php 
                                                    $fuentesSinClientes = array_filter($fuentesPorcentaje, function($f) { return $f['total_clientes'] == 0; });
                                                    if (count($fuentesSinClientes) > 0): 
                                                    ?>
                                                    <li class="mb-2">
                                                        <i class="fas fa-chart-line text-info mr-2"></i>
                                                        <strong>Optimizar:</strong> <?= count($fuentesSinClientes) ?> fuentes sin resultados.
                                                    </li>
                                                    <?php endif; ?>
                                                    
                                                    <li class="mb-2">
                                                        <i class="fas fa-target text-success mr-2"></i>
                                                        <strong>Invertir:</strong> Enfocar recursos en las fuentes más efectivas.
                                                    </li>
                                                <?php else: ?>
                                                    <li class="mb-2">
                                                        <i class="fas fa-info-circle text-info mr-2"></i>
                                                        Registra más clientes para obtener análisis detallados.
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Aquí se podría agregar JavaScript para gráficos más avanzados con Chart.js
});
</script>
<?= $this->endSection() ?>