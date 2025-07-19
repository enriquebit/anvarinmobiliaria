<?= $this->include('layouts/partials/cliente/head') ?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  
  <!-- ===== NAVBAR SUPERIOR ===== -->
  <?= $this->include('layouts/partials/cliente/navbar') ?>

  <!-- ===== SIDEBAR PRINCIPAL ===== -->
  <?= $this->include('layouts/partials/cliente/sidebar') ?>

  <!-- ===== CONTENT WRAPPER ===== -->
  <div class="content-wrapper">
    
    <!-- ===== HEADER DE CONTENIDO ===== -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?= $this->renderSection('page_title') ?: 'Mi Panel' ?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <?= $this->renderSection('breadcrumbs') ?>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <section class="content">
      <div class="container-fluid">
        
        <!-- NOTIFICACIONES FLASH -->
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= session()->getFlashdata('warning') ?>
            <button type="button" class="close" data-dismiss="alert">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
          <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <?= session()->getFlashdata('info') ?>
            <button type="button" class="close" data-dismiss="alert">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <!-- CONTENIDO DE LA PÁGINA -->
        <?= $this->renderSection('content') ?>
        
      </div>
    </section>
  </div>

  <!-- ===== FOOTER ===== -->
  <?= $this->include('layouts/partials/cliente/footer') ?>

</div>

<!-- ===== SCRIPTS DE ADMINLTE ===== -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- ===== TOASTR PARA NOTIFICACIONES ===== -->
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

<!-- ===== SWEETALERT2 ===== -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ===== DATATABLES ===== -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<!-- ===== DATATABLES CONFIGURACIÓN GLOBAL ===== -->
<script src="<?= base_url('assets/js/datatables-config.js') ?>"></script>

<script>
$(document).ready(function() {
  // Auto-hide alerts después de 5 segundos
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);

  // Configuración global para Toastr
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

  // Animación de entrada suave
  $('.content-wrapper').addClass('animated fadeIn');
});

// Helper function para confirmaciones con SweetAlert
function confirmarAccion(titulo, texto, callback) {
  Swal.fire({
    title: titulo,
    text: texto,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed && typeof callback === 'function') {
      callback();
    }
  });
}

// Función para mostrar notificaciones de éxito
function mostrarExito(mensaje) {
  toastr.success(mensaje);
}

// Función para mostrar notificaciones de error
function mostrarError(mensaje) {
  toastr.error(mensaje);
}

// Función para mostrar notificaciones de información
function mostrarInfo(mensaje) {
  toastr.info(mensaje);
}
</script>

<!-- ===== SCRIPTS PERSONALIZADOS ===== -->
<?= $this->renderSection('scripts') ?>

</body>
</html>