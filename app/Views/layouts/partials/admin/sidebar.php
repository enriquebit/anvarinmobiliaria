<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  
  <!-- Brand Logo -->
  <a href="<?= site_url('/admin/dashboard') ?>" class="brand-link">
    <img src="<?= base_url('assets/img/logo_admin.png') ?>" 
         alt="ANVAR Logo" 
         class="brand-image img-circle elevation-3" 
         style="opacity: .8">
    <span class="brand-text font-weight-light">ANVAR</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/dashboard') ?>" class="nav-link <?= (current_url() == site_url('/admin/dashboard')) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Separador Módulos Principales -->
        <li class="nav-header">MÓDULOS PRINCIPALES</li>

        <!-- Ventas (Más usado) -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/ventas') ?>" class="nav-link <?= (strpos(current_url(), '/admin/ventas') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-shopping-cart text-success"></i>
            <p>Ventas</p>
          </a>
        </li>

        <!-- Clientes (Muy usado) -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/clientes') ?>" class="nav-link <?= (strpos(current_url(), '/admin/clientes') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-users text-info"></i>
            <p>Clientes</p>
          </a>
        </li>

        <!-- Estado de Cuenta (Muy usado) -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/estado-cuenta') ?>" class="nav-link <?= (strpos(current_url(), '/admin/estado-cuenta') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chart-line text-primary"></i>
            <p>Estado de Cuenta</p>
          </a>
        </li>

        <!-- Pagos Inmobiliarios (Nuevo módulo) -->
        <li class="nav-item has-treeview <?= (strpos(current_url(), '/admin/pagos') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/admin/pagos') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-money-check-alt text-success"></i>
            <p>
              Pagos Inmobiliarios
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/admin/pagos') ?>" class="nav-link <?= (current_url() == site_url('/admin/pagos')) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/ventas/registradas') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Ventas Registradas</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/apartados') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Gestión de Apartados</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/ventas/registradas') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Liquidar Enganche</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/mensualidades') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mensualidades</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/ventas/registradas') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Abonos a Capital</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Apartados (Usado frecuentemente) -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/apartados') ?>" class="nav-link <?= (strpos(current_url(), '/admin/apartados') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-clock text-warning"></i>
            <p>Apartados</p>
          </a>
        </li>

        <!-- Ingresos (Usado frecuentemente) -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/ingresos') ?>" class="nav-link <?= (strpos(current_url(), '/admin/ingresos') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-money-bill-wave text-success"></i>
            <p>Ingresos</p>
          </a>
        </li>

        <!-- Separador Inventario -->
        <li class="nav-header">INVENTARIO Y PROYECTOS</li>

        <!-- Lotes -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/lotes') ?>" class="nav-link <?= (strpos(current_url(), '/admin/lotes') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-map text-secondary"></i>
            <p>Lotes</p>
          </a>
        </li>

        <!-- Presupuestos -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/presupuestos') ?>" class="nav-link <?= (strpos(current_url(), '/admin/presupuestos') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-calculator text-info"></i>
            <p>Presupuestos</p>
          </a>
        </li>


        <!-- Separador Estructura -->
        <li class="nav-header">ESTRUCTURA DE PROYECTOS</li>

        <!-- Proyectos -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/proyectos') ?>" class="nav-link <?= (strpos(current_url(), '/admin/proyectos') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-city text-primary"></i>
            <p>Proyectos</p>
          </a>
        </li>

        <!-- Manzanas -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/manzanas') ?>" class="nav-link <?= (strpos(current_url(), '/admin/manzanas') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-th-large text-secondary"></i>
            <p>Manzanas</p>
          </a>
        </li>

        <!-- Divisiones -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/divisiones') ?>" class="nav-link <?= (strpos(current_url(), '/admin/divisiones') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-border-all text-info"></i>
            <p>Divisiones</p>
          </a>
        </li>

        <!-- Empresas -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/empresas') ?>" class="nav-link <?= (strpos(current_url(), '/admin/empresas') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-building text-warning"></i>
            <p>Empresas</p>
          </a>
        </li>

        <!-- Separador Administración -->
        <li class="nav-header">ADMINISTRACIÓN</li>

        <!-- Usuarios -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/usuarios') ?>" class="nav-link <?= (strpos(current_url(), '/admin/usuarios') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-shield text-primary"></i>
            <p>Usuarios</p>
          </a>
        </li>

        <!-- Tareas -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/tareas') ?>" class="nav-link <?= (strpos(current_url(), '/admin/tareas') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tasks text-info"></i>
            <p>Gestión de Tareas</p>
          </a>
        </li>

        <!-- Separador Financiero -->
        <li class="nav-header">GESTIÓN FINANCIERA</li>

        <!-- Estado de Cuenta (Expandido) -->
        <li class="nav-item has-treeview <?= (strpos(current_url(), '/admin/estado-cuenta') !== false || strpos(current_url(), '/admin/mensualidades') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/admin/estado-cuenta') !== false || strpos(current_url(), '/admin/mensualidades') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chart-line text-success"></i>
            <p>
              Estado de Cuenta
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/admin/estado-cuenta') ?>" class="nav-link <?= (current_url() == site_url('/admin/estado-cuenta')) ? 'active' : '' ?>">
                <i class="far fa-chart-bar nav-icon"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/mensualidades') ?>" class="nav-link <?= (current_url() == site_url('/admin/mensualidades') || strpos(current_url(), '/admin/mensualidades/index') !== false) ? 'active' : '' ?>">
                <i class="far fa-calendar-alt nav-icon"></i>
                <p>Mensualidades</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/mensualidades/pendientes') ?>" class="nav-link <?= (strpos(current_url(), '/admin/mensualidades/pendientes') !== false) ? 'active' : '' ?>">
                <i class="far fa-exclamation-triangle nav-icon text-warning"></i>
                <p>Mensualidades Críticas</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/estado-cuenta/alertas') ?>" class="nav-link <?= (strpos(current_url(), '/admin/estado-cuenta/alertas') !== false) ? 'active' : '' ?>">
                <i class="far fa-bell nav-icon text-danger"></i>
                <p>Alertas de Vencimiento</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/mensualidades/reporteMensual') ?>" class="nav-link <?= (strpos(current_url(), '/admin/mensualidades/reporteMensual') !== false) ? 'active' : '' ?>">
                <i class="far fa-file-alt nav-icon text-info"></i>
                <p>Reportes</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Reestructuración de Cartera (NUEVO) -->
        <li class="nav-item has-treeview <?= (strpos(current_url(), '/admin/reestructuracion') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/admin/reestructuracion') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-handshake text-warning"></i>
            <p>
              Reestructuración
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/admin/reestructuracion') ?>" class="nav-link <?= (current_url() == site_url('/admin/reestructuracion')) ? 'active' : '' ?>">
                <i class="far fa-tachometer-alt nav-icon"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/reestructuracion/ventas-elegibles') ?>" class="nav-link <?= (strpos(current_url(), '/admin/reestructuracion/ventas-elegibles') !== false) ? 'active' : '' ?>">
                <i class="far fa-gavel nav-icon text-danger"></i>
                <p>Ventas Elegibles</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/reestructuracion/view') ?>" class="nav-link <?= (strpos(current_url(), '/admin/reestructuracion/view') !== false) ? 'active' : '' ?>">
                <i class="far fa-list nav-icon"></i>
                <p>Todas las Reestructuraciones</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/reestructuracion/view?estatus=propuesta') ?>" class="nav-link <?= (strpos(current_url(), '/admin/reestructuracion/view') !== false && strpos(current_url(), 'estatus=propuesta') !== false) ? 'active' : '' ?>">
                <i class="far fa-clock nav-icon text-warning"></i>
                <p>Pendientes Autorización</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/reestructuracion/view?estatus=activa') ?>" class="nav-link <?= (strpos(current_url(), '/admin/reestructuracion/view') !== false && strpos(current_url(), 'estatus=activa') !== false) ? 'active' : '' ?>">
                <i class="far fa-check-circle nav-icon text-success"></i>
                <p>Activas</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Comisiones -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/comisiones') ?>" class="nav-link <?= (strpos(current_url(), '/admin/comisiones') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-percentage text-primary"></i>
            <p>Comisiones</p>
          </a>
        </li>

        <!-- Financiamiento -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/financiamiento') ?>" class="nav-link <?= (strpos(current_url(), '/admin/financiamiento') !== false && strpos(current_url(), '/admin/estado-cuenta') === false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-sliders-h text-info"></i>
            <p>Financiamiento</p>
          </a>
        </li>

        <!-- Cuentas Bancarias -->
        <li class="nav-item">
          <a href="<?= site_url('/admin/cuentas-bancarias') ?>" class="nav-link <?= (strpos(current_url(), '/admin/cuentas-bancarias') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-university text-secondary"></i>
            <p>Cuentas Bancarias</p>
          </a>
        </li>

        <!-- Separador Configuración -->
        <li class="nav-header">CONFIGURACIÓN</li>

        <!-- Catálogos (Dropdown) -->
        <li class="nav-item has-treeview <?= (strpos(current_url(), '/admin/catalogos') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/admin/catalogos') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-list"></i>
            <p>
              Catálogos
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/admin/catalogos/fuentes-informacion') ?>" class="nav-link <?= (strpos(current_url(), '/admin/catalogos/fuentes-informacion') !== false) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Fuentes de Información</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/catalogos/categorias-lotes') ?>" class="nav-link <?= (strpos(current_url(), '/admin/catalogos/categorias-lotes') !== false) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Categorías de Lotes</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/catalogos/tipos-lotes') ?>" class="nav-link <?= (strpos(current_url(), '/admin/catalogos/tipos-lotes') !== false) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Tipos de Lotes</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/catalogos/amenidades') ?>" class="nav-link <?= (strpos(current_url(), '/admin/catalogos/amenidades') !== false) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Amenidades</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Separador Herramientas -->
        <li class="nav-header">HERRAMIENTAS</li>

        <!-- Gestión de Leads (Dropdown) -->
        <li class="nav-item has-treeview <?= (strpos(current_url(), '/admin/leads') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/admin/leads') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-clock"></i>
            <p>
              Gestión de Leads
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/admin/leads') ?>" class="nav-link <?= (current_url() == site_url('/admin/leads')) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Lista de Leads</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/leads/metricas') ?>" class="nav-link <?= (strpos(current_url(), '/admin/leads/metricas') !== false) ? 'active' : '' ?>">
                <i class="fas fa-chart-line nav-icon"></i>
                <p>Métricas y Agentes</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/leads/errores') ?>" class="nav-link <?= (strpos(current_url(), '/admin/leads/errores') !== false) ? 'active' : '' ?>">
                <i class="fas fa-exclamation-triangle nav-icon text-danger"></i>
                <p>Leads con Errores</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/admin/leads/logs') ?>" class="nav-link <?= (strpos(current_url(), '/admin/leads/logs') !== false) ? 'active' : '' ?>">
                <i class="far fa-list-alt nav-icon"></i>
                <p>Logs de API</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/registro-clientes') ?>" class="nav-link" target="_blank">
                <i class="fas fa-external-link-alt nav-icon text-success"></i>
                <p>Formulario Público</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Debug (Solo en desarrollo) -->
        <?php if (ENVIRONMENT === 'development'): ?>
        <li class="nav-item has-treeview <?= (strpos(current_url(), '/debug') !== false) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= (strpos(current_url(), '/debug') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-bug"></i>
            <p>
              Debug Sistema
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= site_url('/debug') ?>" class="nav-link <?= (current_url() == site_url('/debug')) ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Debug General</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('/debug/google-drive') ?>" class="nav-link <?= (strpos(current_url(), '/debug/google-drive') !== false) ? 'active' : '' ?>">
                <i class="fab fa-google-drive nav-icon text-success"></i>
                <p>Google Drive API</p>
              </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Cerrar Sesión -->
        <li class="nav-item">
          <a href="<?= site_url('/logout') ?>" class="nav-link text-warning">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Cerrar Sesión</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>