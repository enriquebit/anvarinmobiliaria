<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Gestión de Apartados<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Apartados<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Apartados</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Alertas de apartados vencidos -->
<?php if (!empty($apartados_vencidos)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Apartados Vencidos</h5>
            Hay <?= count($apartados_vencidos) ?> apartado(s) vencido(s) que requieren procesamiento.
            <form action="<?= site_url('/admin/apartados/procesar-vencidos') ?>" method="post" class="d-inline ml-3">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning btn-sm" 
                        onclick="return confirm('¿Procesar todos los apartados vencidos?')">
                    <i class="fas fa-cog mr-1"></i>
                    Procesar Vencidos
                </button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Contenido principal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Lista de Apartados
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/apartados/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>
                        Nuevo Apartado
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-control" id="filtroEstatus">
                            <option value="">Todos los estatus</option>
                            <option value="vigente">Vigente</option>
                            <option value="completado">Completado</option>
                            <option value="vencido">Vencido</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="penalizado">Penalizado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filtroVendedor">
                            <option value="">Todos los vendedores</option>
                            <?php foreach ($vendedores as $vendedor): ?>
                                <option value="<?= $vendedor->id ?>"><?= esc($vendedor->username) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-secondary" id="limpiarFiltros">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="apartadosTable">
                        <thead>
                            <tr>
                                <th width="80">Folio</th>
                                <th>Cliente</th>
                                <th>Lote</th>
                                <th>Vendedor</th>
                                <th>Fecha Apartado</th>
                                <th>Límite Enganche</th>
                                <th>Monto</th>
                                <th>Estatus</th>
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($apartados)): ?>
                                <?php foreach ($apartados as $apartado): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-info">AP-<?= $apartado->id ?></span>
                                        </td>
                                        <td>
                                            <strong><?= esc($apartado->cliente_nombre) ?></strong><br>
                                            <small class="text-muted">
                                                <?= esc($apartado->apellido_paterno) ?> <?= esc($apartado->apellido_materno) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?= esc($apartado->lote_clave) ?></strong><br>
                                            <small class="text-muted"><?= number_format($apartado->area, 2) ?> m²</small>
                                        </td>
                                        <td><?= esc($apartado->vendedor_nombre) ?></td>
                                        <td><?= date('d/m/Y', strtotime($apartado->fecha_apartado)) ?></td>
                                        <td>
                                            <?php 
                                            $fechaLimite = strtotime($apartado->fecha_limite_enganche);
                                            $hoy = strtotime('today');
                                            $esVencido = $fechaLimite < $hoy;
                                            ?>
                                            <span class="<?= $esVencido ? 'text-danger' : 'text-success' ?>">
                                                <?= date('d/m/Y', $fechaLimite) ?>
                                            </span>
                                            <?php if ($esVencido && $apartado->estatus_apartado === 'vigente'): ?>
                                                <br><small class="badge badge-danger">VENCIDO</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($apartado->monto_apartado, 2) ?></strong><br>
                                            <small class="text-muted">
                                                Enganche: $<?= number_format($apartado->monto_enganche_requerido, 2) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = match($apartado->estatus_apartado) {
                                                'vigente' => 'badge-success',
                                                'completado' => 'badge-primary',
                                                'vencido' => 'badge-danger',
                                                'cancelado' => 'badge-secondary',
                                                'penalizado' => 'badge-warning',
                                                default => 'badge-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= ucfirst($apartado->estatus_apartado) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= site_url('/admin/apartados/' . $apartado->id) ?>" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($apartado->estatus_apartado === 'vigente'): ?>
                                                    <a href="<?= site_url('/admin/apartados/edit/' . $apartado->id) ?>" 
                                                       class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= site_url('/admin/apartados/cancelar/' . $apartado->id) ?>" 
                                                       class="btn btn-sm btn-danger" title="Cancelar"
                                                       onclick="return confirm('¿Estás seguro de cancelar este apartado?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                    <a href="<?= site_url('/admin/apartados/comprobante/' . $apartado->id) ?>" 
                                                       class="btn btn-sm btn-secondary" title="Subir comprobante">
                                                        <i class="fas fa-upload"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                        No hay apartados registrados
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Verificar si debe abrir recibo automáticamente
    <?php if (session('open_recibo') && session('recibo_url')): ?>
        // Abrir recibo en nueva pestaña después de crear apartado
        setTimeout(function() {
            window.open('<?= session('recibo_url') ?>', '_blank');
        }, 500);
    <?php endif; ?>
    
    // Solo inicializar DataTable si hay filas de datos
    if ($('#apartadosTable tbody tr').length > 0 && !$('#apartadosTable tbody tr').first().find('td[colspan]').length) {
                // Configuración global de DataTables aplicada desde datatables-config.js

        var table = $('#apartadosTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "pageLength": 25,
            "order": [[4, "desc"]],
            "columnDefs": [
                { "orderable": false, "targets": 8 }
            ]
        });

        // Filtros solo si DataTable está inicializado
        $('#filtroEstatus').on('change', function() {
            table.column(7).search(this.value).draw();
        });

        $('#filtroVendedor').on('change', function() {
            const valor = this.value;
            if (valor) {
                const textoVendedor = $(this).find('option:selected').text();
                table.column(3).search(textoVendedor).draw();
            } else {
                table.column(3).search('').draw();
            }
        });

        $('#limpiarFiltros').on('click', function() {
            $('#filtroEstatus, #filtroVendedor').val('');
            table.search('').columns().search('').draw();
        });
    }
});
</script>
<?= $this->endSection() ?>