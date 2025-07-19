<!-- Main Navigation -->
<nav class="main-header navbar navbar-expand navbar-dark">
  
  <!-- ===== LEFT NAVBAR LINKS ===== -->
  <ul class="navbar-nav">
    <!-- Sidebar Toggle -->
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button" title="Menú">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    
    <!-- Home Link -->
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= site_url('/cliente/dashboard') ?>" class="nav-link">
        <i class="fas fa-home mr-1"></i>
        Mi Panel
      </a>
    </li>
    
    <!-- Mi Perfil Link -->
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= site_url('/cliente/perfil') ?>" class="nav-link">
        <i class="fas fa-user mr-1"></i>
        Mi Perfil
      </a>
    </li>

    <!-- Mis Documentos Link -->
    <li class="nav-item d-none d-md-inline-block">
      <a href="<?= site_url('/cliente/documentos') ?>" class="nav-link">
        <i class="fas fa-file-alt mr-1"></i>
        Documentos
      </a>
    </li>
  </ul>

  <!-- ===== CENTRO - MENSAJE DE BIENVENIDA ===== -->
  <div class="navbar-nav mx-auto d-none d-lg-flex">
    <span class="navbar-text text-white">
      <i class="fas fa-user-circle mr-2"></i>
      ¡Bienvenido, <strong><?= userName() ?></strong>!
    </span>
  </div>

  <!-- ===== RIGHT NAVBAR LINKS ===== -->
  <ul class="navbar-nav ml-auto">
    
    <!-- ===== ESTADO DE CUENTA ===== -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Estado de Cuenta">
        <i class="fas fa-credit-card"></i>
        <span class="badge badge-warning navbar-badge">1</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">Estado de Cuenta</span>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/pagos') ?>" class="dropdown-item">
          <i class="fas fa-dollar-sign mr-2 text-success"></i>
          Saldo actual: $50,000
          <span class="float-right text-muted text-sm">Actualizado</span>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/pagos/pendientes') ?>" class="dropdown-item">
          <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
          Pago pendiente: $25,000
          <span class="float-right text-muted text-sm">Vence: 15 Ene</span>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/pagos') ?>" class="dropdown-item dropdown-footer">
          Ver todos mis pagos
        </a>
      </div>
    </li>

    <!-- ===== MENSAJES/NOTIFICACIONES ===== -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Mensajes">
        <i class="far fa-bell"></i>
        <span class="badge badge-info navbar-badge">2</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">2 Notificaciones</span>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <i class="fas fa-check-circle mr-2 text-success"></i>
          Documentos aprobados
          <span class="float-right text-muted text-sm">Hace 2 horas</span>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <i class="fas fa-info-circle mr-2 text-info"></i>
          Recordatorio: Próximo pago
          <span class="float-right text-muted text-sm">Hace 1 día</span>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/notificaciones') ?>" class="dropdown-item dropdown-footer">
          Ver todas las notificaciones
        </a>
      </div>
    </li>

    <!-- ===== AYUDA ===== -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Ayuda">
        <i class="fas fa-question-circle"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">Centro de Ayuda</span>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/ayuda/faq') ?>" class="dropdown-item">
          <i class="fas fa-question mr-2 text-info"></i>
          Preguntas Frecuentes
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/ayuda/contacto') ?>" class="dropdown-item">
          <i class="fas fa-phone mr-2 text-success"></i>
          Contactar Soporte
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/ayuda/guias') ?>" class="dropdown-item">
          <i class="fas fa-book mr-2 text-warning"></i>
          Guías de Usuario
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/ayuda/videos') ?>" class="dropdown-item">
          <i class="fas fa-video mr-2 text-primary"></i>
          Video Tutoriales
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/ayuda') ?>" class="dropdown-item dropdown-footer">
          Ver centro de ayuda completo
        </a>
      </div>
    </li>

    <!-- ===== CONFIGURACIÓN RÁPIDA ===== -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Configuración">
        <i class="fas fa-cog"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <span class="dropdown-item dropdown-header">Configuración Rápida</span>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/configuracion/notificaciones') ?>" class="dropdown-item">
          <i class="fas fa-bell mr-2 text-info"></i>
          Notificaciones
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/configuracion/privacidad') ?>" class="dropdown-item">
          <i class="fas fa-shield-alt mr-2 text-warning"></i>
          Privacidad
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/configuracion/tema') ?>" class="dropdown-item">
          <i class="fas fa-palette mr-2 text-primary"></i>
          Tema
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="<?= site_url('/cliente/configuracion') ?>" class="dropdown-item dropdown-footer">
          Ver todas las configuraciones
        </a>
      </div>
    </li>

    <!-- ===== FULLSCREEN TOGGLE ===== -->
    <li class="nav-item">
      <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Pantalla completa">
        <i class="fas fa-expand-arrows-alt"></i>
      </a>
    </li>

    <!-- ===== USER MENU DROPDOWN ===== -->
    <li class="nav-item dropdown user-menu">
      <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <img src="https://via.placeholder.com/32x32/28a745/fff?text=<?= substr(userName(), 0, 1) ?>" 
             class="user-image img-circle elevation-2" 
             alt="Mi Foto">
        <span class="d-none d-md-inline"><?= userName() ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        
        <!-- User image -->
        <li class="user-header bg-success">
          <img src="https://via.placeholder.com/90x90/28a745/fff?text=<?= substr(userName(), 0, 1) ?>" 
               class="img-circle elevation-2" 
               alt="Mi Foto">
          <p>
            <?= userName() ?>
            <small>Cliente Anvar Inmobiliaria</small>
            <small>Miembro desde: <?= date('M Y') ?></small>
          </p>
        </li>
        
        <!-- Menu Body -->
        <li class="user-body">
          <div class="row">
            <div class="col-4 text-center">
              <a href="<?= site_url('/cliente/perfil') ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-user"></i><br>
                <small>Mi Perfil</small>
              </a>
            </div>
            <div class="col-4 text-center">
              <a href="<?= site_url('/cliente/documentos') ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-alt"></i><br>
                <small>Documentos</small>
              </a>
            </div>
            <div class="col-4 text-center">
              <a href="<?= site_url('/cliente/pagos') ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-credit-card"></i><br>
                <small>Pagos</small>
              </a>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-6 text-center">
              <a href="<?= site_url('/cliente/propiedad') ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-home"></i><br>
                <small>Mi Propiedad</small>
              </a>
            </div>
            <div class="col-6 text-center">
              <a href="<?= site_url('/cliente/soporte') ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-headset"></i><br>
                <small>Soporte</small>
              </a>
            </div>
          </div>
        </li>
        
        <!-- Menu Stats -->
        <li class="user-body">
          <div class="row">
            <div class="col-4 text-center">
              <div class="description-block">
                <span class="description-header text-success">7/10</span>
                <span class="description-text">DOCS</span>
              </div>
            </div>
            <div class="col-4 text-center">
              <div class="description-block border-right border-left">
                <span class="description-header text-warning">75%</span>
                <span class="description-text">PAGOS</span>
              </div>
            </div>
            <div class="col-4 text-center">
              <div class="description-block">
                <span class="description-header text-info">45%</span>
                <span class="description-text">OBRA</span>
              </div>
            </div>
          </div>
        </li>
        
        <!-- Menu Footer-->
        <li class="user-footer">
          <a href="<?= site_url('/cliente/perfil') ?>" class="btn btn-default btn-flat">
            <i class="fas fa-user-edit mr-1"></i>
            Editar Perfil
          </a>
          <a href="<?= site_url('/logout') ?>" class="btn btn-default btn-flat float-right" onclick="return confirmarCerrarSesion()">
            <i class="fas fa-sign-out-alt mr-1"></i>
            Cerrar Sesión
          </a>
        </li>
      </ul>
    </li>

  </ul>
</nav>

<script>
// Función para confirmar cierre de sesión
function confirmarCerrarSesion() {
  return confirm('¿Estás seguro de que deseas cerrar sesión?');
}

// Actualizar badges en tiempo real
$(document).ready(function() {
  // Simular actualización de badges cada 30 segundos
  setInterval(function() {
    // Aquí harías llamadas AJAX reales para obtener datos actualizados
    // Por ahora simulamos cambios aleatorios
    
    // Actualizar contador de notificaciones
    const notifCount = Math.floor(Math.random() * 5);
    if (notifCount > 0) {
      $('.navbar .badge-info').text(notifCount).show();
    } else {
      $('.navbar .badge-info').hide();
    }
    
    // Actualizar contador de estado de cuenta
    const cuentaAlerts = Math.floor(Math.random() * 3);
    if (cuentaAlerts > 0) {
      $('.navbar .badge-warning').text(cuentaAlerts).show();
    } else {
      $('.navbar .badge-warning').hide();
    }
  }, 30000);

  // Efecto hover en los dropdowns
  $('.navbar .dropdown-item').hover(
    function() {
      $(this).addClass('font-weight-bold');
    },
    function() {
      $(this).removeClass('font-weight-bold');
    }
  );

  // Auto-hide de dropdowns al hacer click fuera
  $(document).click(function(e) {
    if (!$(e.target).closest('.dropdown').length) {
      $('.dropdown-menu').removeClass('show');
    }
  });
});
</script>