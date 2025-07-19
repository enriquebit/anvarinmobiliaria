<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $titulo ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <?php foreach ($breadcrumb as $item): ?>
                        <?php if (!empty($item['url'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= $item['name'] ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Stats Row -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $total_empresas ?></h3>
                        <p>Empresas Activas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>0</h3>
                        <p>Proyectos Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>0</h3>
                        <p>Configuraciones Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>0%</h3>
                        <p>Comisiones Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building mr-2"></i>
                    Listado de Empresas
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/empresas/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>
                        Nueva Empresa
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- DataTables Table -->
                <table id="empresas-table" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">Nombre</th>
                            <th width="12%">RFC</th>
                            <th width="18%">Razón Social</th>
                            <th width="12%">Teléfono</th>
                            <th width="15%">Email</th>
                            <th width="8%">Proyectos</th>
                            <th width="10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables cargará el contenido via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables & plugins -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>

<!-- SweetAlert2 -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<!-- Toastr -->
<script src="<?= base_url('assets/plugins/toastr/toastr.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // =====================================================================
    // CONFIGURACIÓN DE DATATABLES
    // =====================================================================
    
    const table =         // Configuración global de DataTables aplicada desde datatables-config.js
 $('#empresas-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url("/admin/empresas/datatable") ?>',
            type: 'POST',
            data: function(d) {
                // Agregar token CSRF
                d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'rfc', name: 'rfc' },
            { data: 'razon_social', name: 'razon_social' },
            { data: 'telefono', name: 'telefono' },
            { data: 'email', name: 'email' },
            { data: 'proyectos', name: 'proyectos', className: 'text-center' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'asc']], // Ordenar por nombre
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron registros coincidentes",
            emptyTable: "No hay empresas registradas",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
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
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-info btn-sm'
            }
        ]
    });

    // =====================================================================
    // FUNCIÓN PARA ELIMINAR EMPRESA CON SWEETALERT
    // =====================================================================
    
    window.eliminarEmpresa = function(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            html: `Se eliminará la empresa:<br><strong>${nombre}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // AJAX para eliminar
                $.ajax({
                    url: `<?= site_url('/admin/empresas/delete/') ?>${id}`,
                    type: 'POST',
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            toastr.success(response.message, 'Empresa Eliminada', {
                                timeOut: 5000,
                                progressBar: true
                            });
                            
                            // Recargar tabla
                            table.ajax.reload(null, false);
                        } else {
                            // Mostrar error
                            toastr.error(response.message, 'Error', {
                                timeOut: 5000,
                                progressBar: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        toastr.error('Error de conexión. Inténtalo nuevamente.', 'Error de Red', {
                            timeOut: 5000,
                            progressBar: true
                        });
                        console.error('Error AJAX:', error);
                    }
                });
            }
        });
    };

    // =====================================================================
    // MOSTRAR TOASTS SI HAY MENSAJES DE SESIÓN
    // =====================================================================
    
    <?php if (session('success')): ?>
        toastr.success('<?= session('success') ?>', 'Éxito', {
            timeOut: 5000,
            progressBar: true
        });
    <?php endif; ?>

    <?php if (session('error')): ?>
        toastr.error('<?= session('error') ?>', 'Error', {
            timeOut: 5000,
            progressBar: true
        });
    <?php endif; ?>

    <?php if (session('toast_message')): ?>
        toastr.info('<?= session('toast_message') ?>', 'Información', {
            timeOut: 10000,
            progressBar: true
        });
    <?php endif; ?>

    // =====================================================================
    // CONFIGURACIÓN ADICIONAL
    // =====================================================================
    
    // Configurar toastr globalmente
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
});
</script>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">

<!-- SweetAlert2 -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">

<!-- Toastr -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/toastr/toastr.min.css') ?>">

<style>
/* Estilos personalizados para la tabla */
#empresas-table_wrapper .dt-buttons {
    margin-bottom: 1rem;
}

#empresas-table_wrapper .dt-buttons .btn {
    margin-right: 0.5rem;
}

/* Responsive fixes */
@media (max-width: 768px) {
    .card-tools {
        margin-bottom: 1rem;
    }
    
    .small-box .inner h3 {
        font-size: 1.5rem;
    }
}

/* Mejoras visuales para la tabla */
#empresas-table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group-sm .btn {
    margin-right: 2px;
}
</style>
<?= $this->endSection() ?>