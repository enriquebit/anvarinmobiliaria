<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-success elevation-4">
  
  <!-- ===== BRAND LOGO ===== -->
  <a href="<?= site_url('/cliente/dashboard') ?>" class="brand-link">
    <img src="<?= base_url('assets/img/logo_cliente.png') ?>" 
         alt="Anvar Logo" 
         class="brand-image img-circle elevation-3" 
         style="opacity: .8">
    <span class="brand-text font-weight-light">MI ANVAR</span>
  </a>

  <!-- ===== SIDEBAR ===== -->
  <div class="sidebar">
    
    <!-- ===== USER PANEL ===== -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="https://via.placeholder.com/40x40/28a745/fff?text=<?= substr(userName(), 0, 1) ?>" 
             class="img-circle elevation-2" 
             alt="Mi Foto">
      </div>
      <div class="info">
        <a href="<?= site_url('/cliente/mi-perfil') ?>" class="d-block"><?= userName() ?></a>
        <small class="text-muted">Cliente Anvar</small>
      </div>
    </div>

    <!-- ===== SIDEBAR MENU ===== -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- ===== MI PANEL ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/cliente/dashboard') ?>" class="nav-link <?= (current_url() == site_url('/cliente/dashboard')) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Mi Panel Personal</p>
          </a>
        </li>

        <!-- ===== SEPARADOR ===== -->
        <li class="nav-header">MI INFORMACIÓN</li>

        <!-- ===== MI PERFIL ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/cliente/mi-perfil') ?>" class="nav-link <?= (strpos(current_url(), '/cliente/mi-perfil') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-circle"></i>
            <p>Mi Perfil</p>
          </a>
        </li>

        <!-- ===== MIS DOCUMENTOS ===== -->
        <li class="nav-item <?= (strpos(current_url(), '/cliente/documentos') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/cliente/documentos') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>
              Mis Documentos
              <i class="right fas fa-angle-left"></i>
              <span class="badge badge-warning right">3</span>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/cliente/documentos') ?>" class="nav-link <?= (current_url() == site_url('/cliente/documentos')) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Ver Documentos</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/documentos/subir') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Subir Documentos</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/documentos/pendientes') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Pendientes 
                  <span class="badge badge-warning right">3</span>
                </p>
              </a>
            </li>
          </ul>
        </li>

        <!-- ===== SEPARADOR ===== -->
        <li class="nav-header">MI PROPIEDAD</li>

        <!-- ===== MI PROPIEDAD ===== -->
        <li class="nav-item <?= (strpos(current_url(), '/cliente/propiedad') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/cliente/propiedad') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-home"></i>
            <p>
              Mi Propiedad
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/cliente/propiedad') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Detalles</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/propiedad/progreso') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Progreso de Obra</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/propiedad/fotos') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Galería de Fotos</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/propiedad/planos') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Planos</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- ===== SEPARADOR ===== -->
        <li class="nav-header">PAGOS Y FINANZAS</li>

        <!-- ===== ESTADO DE CUENTA ===== -->
        <li class="nav-item <?= (strpos(current_url(), '/cliente/estado-cuenta') !== false || strpos(current_url(), '/cliente/pagos') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/cliente/estado-cuenta') !== false || strpos(current_url(), '/cliente/pagos') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chart-line text-success"></i>
            <p>
              Estado de Cuenta
              <i class="right fas fa-angle-left"></i>
              <span class="badge badge-info right" id="badge-pendientes">0</span>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/cliente/estado-cuenta') ?>" class="nav-link <?= (current_url() == site_url('/cliente/estado-cuenta')) ? 'active' : '' ?>">
                <i class="far fa-chart-bar nav-icon"></i>
                <p>Mi Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/estado-cuenta/propiedad') ?>" class="nav-link <?= (strpos(current_url(), '/cliente/estado-cuenta/propiedad') !== false) ? 'active' : '' ?>">
                <i class="far fa-home nav-icon"></i>
                <p>Detalle Propiedad</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/estado-cuenta/historialPagos') ?>" class="nav-link <?= (strpos(current_url(), '/cliente/estado-cuenta/historialPagos') !== false) ? 'active' : '' ?>">
                <i class="far fa-history nav-icon"></i>
                <p>Historial de Pagos</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/estado-cuenta/proximosVencimientos') ?>" class="nav-link <?= (strpos(current_url(), '/cliente/estado-cuenta/proximosVencimientos') !== false) ? 'active' : '' ?>">
                <i class="far fa-calendar-alt nav-icon text-warning"></i>
                <p>Próximos Vencimientos</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- ===== MIS PAGOS ===== -->
        <li class="nav-item <?= (strpos(current_url(), '/cliente/pagos') !== false && strpos(current_url(), '/cliente/estado-cuenta') === false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/cliente/pagos') !== false && strpos(current_url(), '/cliente/estado-cuenta') === false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-credit-card text-primary"></i>
            <p>
              Mis Pagos
              <i class="right fas fa-angle-left"></i>
              <span class="badge badge-success right" id="badge-pagos">0</span>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/cliente/pagos') ?>" class="nav-link <?= (current_url() == site_url('/cliente/pagos')) ? 'active' : '' ?>">
                <i class="far fa-dollar-sign nav-icon"></i>
                <p>Realizar Pago</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/pagos/mensualidad') ?>" class="nav-link <?= (strpos(current_url(), '/cliente/pagos/mensualidad') !== false) ? 'active' : '' ?>">
                <i class="far fa-calendar-check nav-icon"></i>
                <p>Pago Mensualidad</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/pagos/comprobante') ?>" class="nav-link <?= (strpos(current_url(), '/cliente/pagos/comprobante') !== false) ? 'active' : '' ?>">
                <i class="far fa-file-upload nav-icon"></i>
                <p>Subir Comprobante</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/pagos/reportarPago') ?>" class="nav-link <?= (strpos(current_url(), '/cliente/pagos/reportarPago') !== false) ? 'active' : '' ?>">
                <i class="far fa-bell nav-icon"></i>
                <p>Reportar Pago</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- ===== MI CONTRATO ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/cliente/contrato') ?>" class="nav-link">
            <i class="nav-icon fas fa-file-contract"></i>
            <p>Mi Contrato</p>
          </a>
        </li>

        <!-- ===== SEPARADOR ===== -->
        <li class="nav-header">COMUNICACIÓN</li>

        <!-- ===== MENSAJES ===== -->
        <li class="nav-item <?= (strpos(current_url(), '/cliente/mensajes') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/cliente/mensajes') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-comments"></i>
            <p>
              Mensajes
              <i class="right fas fa-angle-left"></i>
              <span class="badge badge-success right">2</span>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/cliente/mensajes') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Bandeja de Entrada</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/mensajes/nuevo') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Nuevo Mensaje</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/mensajes/enviados') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mensajes Enviados</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- ===== TICKETS DE SOPORTE ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/cliente/soporte') ?>" class="nav-link">
            <i class="nav-icon fas fa-headset"></i>
            <p>
              Soporte Técnico
              <span class="badge badge-danger right">!</span>
            </p>
          </a>
        </li>

        <!-- ===== SEPARADOR ===== -->
        <li class="nav-header">RECURSOS</li>

        <!-- ===== NOTIFICACIONES ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/cliente/notificaciones') ?>" class="nav-link">
            <i class="nav-icon fas fa-bell"></i>
            <p>
              Notificaciones
              <span class="badge badge-info right">2</span>
            </p>
          </a>
        </li>

        <!-- ===== CENTRO DE AYUDA ===== -->
        <li class="nav-item <?= (strpos(current_url(), '/cliente/ayuda') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/cliente/ayuda') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-question-circle"></i>
            <p>
              Centro de Ayuda
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/cliente/ayuda/faq') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Preguntas Frecuentes</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/ayuda/guias') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Guías de Usuario</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/cliente/ayuda/contacto') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Contactar Anvar</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- ===== DESCARGAS ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/cliente/descargas') ?>" class="nav-link">
            <i class="nav-icon fas fa-download"></i>
            <p>Descargas</p>
          </a>
        </li>

        <!-- ===== SEPARADOR ===== -->
        <li class="nav-header">CUENTA</li>

        <!-- ===== CONFIGURACIÓN ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/cliente/configuracion') ?>" class="nav-link">
            <i class="nav-icon fas fa-cog"></i>
            <p>Configuración</p>
          </a>
        </li>

        <!-- ===== CERRAR SESIÓN ===== -->
        <li class="nav-item">
          <a href="<?= site_url('/logout') ?>" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Cerrar Sesión</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>