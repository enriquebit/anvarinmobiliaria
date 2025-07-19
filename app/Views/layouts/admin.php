<?= $this->include('layouts/partials/admin/head') ?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  
  <!-- ===== NAVBAR SUPERIOR ===== -->
  <?= $this->include('layouts/partials/admin/navbar') ?>

  <!-- ===== SIDEBAR PRINCIPAL ===== -->
  <?= $this->include('layouts/partials/admin/sidebar') ?>

  <!-- ===== CONTENT WRAPPER ===== -->
  <div class="content-wrapper">
    
    <!-- ===== HEADER DE CONTENIDO ===== -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?= $this->renderSection('page_title') ?: 'Dashboard' ?></h1>
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
  <?= $this->include('layouts/partials/admin/footer') ?>

  <!-- ===== SIDEBAR DE CONTROL (Opcional) ===== -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Contenido del sidebar de control -->
  </aside>
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

<!-- ===== SELECT2 ===== -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- ===== JQUERY VALIDATION ===== -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>

<script>
$(document).ready(function() {
  // Auto-hide alerts después de 5 segundos
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 10000);

  // Configuración global para DataTables se maneja en datatables-config.js

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

  // Esperar a que AdminLTE esté completamente cargado
  $(window).on('load', function() {
    setTimeout(function() {
      
      // Verificar si AdminLTE y TreeView están disponibles
      if (typeof AdminLTE !== 'undefined' && typeof $.fn.Treeview !== 'undefined') {
        // Destruir cualquier instancia previa
        $('[data-widget="treeview"]').each(function() {
          if ($(this).data('widget-treeview')) {
            $(this).Treeview('destroy');
          }
        });
        
        // Inicializar treeview
        $('[data-widget="treeview"]').Treeview({
          trigger: '.nav-link',
          accordion: false,
          animationSpeed: 300,
          expandSidebar: false
        });
        
        //console.log('✅ AdminLTE Treeview inicializado correctamente');
      } else {
        //console.warn('⚠️ AdminLTE o Treeview no disponible, usando implementación manual');
        
        // Implementación manual más robusta
        $('.nav-sidebar .nav-item.has-treeview > .nav-link').off('click.manual-treeview').on('click.manual-treeview', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const $parent = $(this).parent('.nav-item.has-treeview');
          const $submenu = $parent.find('> .nav-treeview');
          
          
          if ($submenu.length) {
            // Toggle este menú
            if ($parent.hasClass('menu-open')) {
              $parent.removeClass('menu-open menu-is-opening');
              $submenu.slideUp(300);
            } else {
              // Opcional: cerrar otros menús (comentado para permitir múltiples abiertos)
              // $('.nav-sidebar .nav-item.has-treeview.menu-open').not($parent).removeClass('menu-open menu-is-opening');
              // $('.nav-sidebar .nav-treeview').not($submenu).slideUp(300);
              
              $parent.addClass('menu-open menu-is-opening');
              $submenu.slideDown(300);
            }
          }
        });
        
        //console.log('✅ Implementación manual de treeview activada');
      }
    }, 500); // Dar tiempo para que AdminLTE se cargue completamente
  });
});

// Helper function para confirmaciones con SweetAlert
function confirmarAccion(titulo, texto, callback) {
  Swal.fire({
    title: titulo,
    text: texto,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed && typeof callback === 'function') {
      callback();
    }
  });
}
</script>

<!-- ===== SCRIPTS PERSONALIZADOS ===== -->
<?= $this->renderSection('scripts') ?>

</body>
</html>