<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
    <?php if (!empty($item['url'])): ?>
        <li class="breadcrumb-item">
            <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
        </li>
    <?php else: ?>
        <li class="breadcrumb-item active"><?= $item['name'] ?></li>
    <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        
        <!-- TARJETAS DE ESTADÍSTICAS -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $estadisticas['total_cuentas'] ?></h3>
                        <p>Total Cuentas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-university"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $estadisticas['cuentas_activas'] ?></h3>
                        <p>Cuentas Activas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $estadisticas['cuentas_inactivas'] ?></h3>
                        <p>Cuentas Inactivas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>
                            <?php if (isset($estadisticas['saldos_por_moneda']['MXN'])): ?>
                                $<?= number_format($estadisticas['saldos_por_moneda']['MXN'], 0, '.', ',') ?>
                            <?php else: ?>
                                $0
                            <?php endif; ?>
                        </h3>
                        <p>Saldo Total MXN</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA PRINCIPAL -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-university mr-2"></i>
                    Cuentas Bancarias
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('admin/cuentas-bancarias/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Cuenta
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-cuentas" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Descripción</th>
                                <th>Banco</th>
                                <th>No. Cuenta</th>
                                <th>Titular</th>
                                <th>Saldo Actual</th>
                                <th>Tipo</th>
                                <th>Proyecto</th>
                                <th>Empresa</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data cargada via AJAX -->
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
    // Inicializar DataTable
    $('#tabla-cuentas').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= base_url('admin/cuentas-bancarias/obtener-cuentas') ?>',
            type: 'POST',
            error: function(xhr, error, thrown) {
                console.error('Error al cargar las cuentas:', error);
                toastr.error('Error al cargar las cuentas bancarias');
            }
        },
        columns: [
            { data: 'descripcion' },
            { data: 'banco' },
            { data: 'numero_cuenta' },
            { data: 'titular' },
            { 
                data: 'saldo_actual',
                className: 'text-right'
            },
            { data: 'tipo_cuenta' },
            { data: 'proyecto_nombre' },
            { data: 'empresa_nombre' },
            { 
                data: 'estado',
                className: 'text-center'
            },
            { 
                data: 'acciones',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[0, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            }
        ]
    });
});

// Función para cambiar estado
function cambiarEstado(id) {
    Swal.fire({
        title: '¿Confirmar cambio de estado?',
        text: "Esta acción cambiará el estado de la cuenta bancaria",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('admin/cuentas-bancarias/cambiar-estado') ?>/' + id,
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#tabla-cuentas').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Error al cambiar el estado de la cuenta');
                }
            });
        }
    });
}

// Función para eliminar cuenta
function eliminarCuenta(id) {
    Swal.fire({
        title: '¿Eliminar cuenta bancaria?',
        text: 'Esta acción no se puede deshacer. Solo se puede eliminar si no tiene ventas asociadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('admin/cuentas-bancarias/delete/') ?>' + id,
                type: 'POST',
                headers: {
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#tabla-cuentas').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Error al eliminar la cuenta bancaria');
                }
            });
        }
    });
}

// Mostrar notificaciones si existen
<?php if (session('success')): ?>
    toastr.success('<?= session('success') ?>');
<?php endif; ?>

<?php if (session('error')): ?>
    toastr.error('<?= session('error') ?>');
<?php endif; ?>
</script>
<?= $this->endSection() ?>