<!-- Main Navigation -->
<nav class="main-header navbar navbar-expand navbar-dark">
  
  <!-- ===== LEFT NAVBAR LINKS ===== -->
  <ul class="navbar-nav">
    <!-- Sidebar Toggle -->
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    
    <!-- Home Link -->
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= site_url('/admin/dashboard') ?>" class="nav-link">
        <i class="fas fa-home mr-1"></i>
        Inicio
      </a>
    </li>
    
    <!-- Usuarios Link (solo si tiene permisos) -->
    <?php if (can('users.read')): ?>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= site_url('/admin/usuarios') ?>" class="nav-link">
        <i class="fas fa-users mr-1"></i>
        Usuarios
      </a>
    </li>
    <?php endif; ?>
  </ul>

  <!-- ===== SEARCH FORM (OPCIONAL) ===== -->
  <form class="form-inline ml-3 d-none d-lg-flex">
    <div class="input-group input-group-sm">
      <input class="form-control form-control-navbar" 
             type="search" 
             placeholder="Buscar..." 
             aria-label="Search"
             style="width: 300px;">
      <div class="input-group-append">
        <button class="btn btn-navbar" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </div>
  </form>

  <!-- ===== RIGHT NAVBAR LINKS ===== -->
  <ul class="navbar-nav ml-auto">
    
    <!-- ===== NOTIFICACIONES DROPDOWN ===== -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Notificaciones">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge" id="notification-count">3</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">3 Notificaciones</span>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <i class="fas fa-user-plus mr-2"></i> Nuevo cliente registrado
          <span class="float-right text-muted text-sm">5 mins</span>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <i class="fas fa-file-invoice mr-2"></i> Pago pendiente de revisión
          <span class="float-right text-muted text-sm">1 hora</span>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <i class="fas fa-home mr-2"></i> Nueva propiedad agregada
          <span class="float-right text-muted text-sm">3 horas</span>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item dropdown-footer">Ver todas las notificaciones</a>
      </div>
    </li>

    <!-- ===== MENSAJES DROPDOWN ===== -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Mensajes">
        <i class="far fa-comments"></i>
        <span class="badge badge-danger navbar-badge">2</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <a href="#" class="dropdown-item">
          <div class="media">
            <?= generateAvatarImg('Cliente Juan Pérez', 50, 'img-size-50 mr-3 img-circle') ?>
            <div class="media-body">
              <h3 class="dropdown-item-title">
                Cliente Juan Pérez
                <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
              </h3>
              <p class="text-sm">Consulta sobre mi propiedad...</p>
              <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> Hace 4 horas</p>
            </div>
          </div>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <div class="media">
            <?= generateAvatarImg('Asesor María García', 50, 'img-size-50 mr-3 img-circle') ?>
            <div class="media-body">
              <h3 class="dropdown-item-title">
                Asesor María García
                <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
              </h3>
              <p class="text-sm">Reporte de ventas semanal</p>
              <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> Hace 2 días</p>
            </div>
          </div>
        </a>
        
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item dropdown-footer">Ver todos los mensajes</a>
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
      <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <?= getUserAvatar(32, 'user-image img-circle elevation-2') ?>
        <span class="d-none d-md-inline"><?= userName() ?> - <?= userRole() ?></span>
      </a>
      
      <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        
        <!-- User Header -->
        <li class="user-header bg-primary">
          <?= getUserAvatar(90, 'img-circle elevation-2') ?>
          <p>
            <?= esc(userName()) ?>
            <small><?= esc(userRole()) ?></small>
            <small>Miembro desde: <?= date('M Y', strtotime(auth()->user()->created_at ?? 'now')) ?></small>
          </p>
        </li>
        
        <!-- Menu Body - Acciones Rápidas -->
        <li class="user-body">
          <div class="row">
            <div class="col-4 text-center">
              <a href="<?= site_url('/admin/mi-perfil') ?>" 
                 class="btn btn-sm btn-outline-primary" 
                 title="Ver y editar mi perfil">
                <i class="fas fa-user-edit"></i><br>
                <small>Mi Perfil</small>
              </a>
            </div>
            <div class="col-4 text-center">
              <a href="<?= site_url('/admin/tareas/mis-tareas') ?>" 
                 class="btn btn-sm btn-outline-info" 
                 title="Ver mis tareas asignadas">
                <i class="fas fa-tasks"></i><br>
                <small>Mis Tareas</small>
              </a>
            </div>
            <div class="col-4 text-center">
              <a href="<?= site_url('/admin/dashboard') ?>" 
                 class="btn btn-sm btn-outline-success" 
                 title="Ir al dashboard">
                <i class="fas fa-tachometer-alt"></i><br>
                <small>Dashboard</small>
              </a>
            </div>
          </div>
        </li>
        
        <!-- Separador con información adicional -->
        <li class="dropdown-divider"></li>
        
        <!-- Menu Items - Enlaces directos -->
        <li>
          <a href="<?= site_url('/admin/mi-perfil') ?>" class="dropdown-item">
            <i class="fas fa-user-cog mr-2 text-primary"></i>
            Editar Mi Perfil
            <?php 
            $staffModel = new \App\Models\StaffModel();
            $staff = $staffModel->where('user_id', auth()->user()->id)->first();
            if ($staff && $staff->debe_cambiar_password): ?>
            <span class="badge badge-warning float-right">Cambiar Password</span>
            <?php endif; ?>
          </a>
        </li>
        
        <?php if (can('usuarios.read')): ?>
        <li>
          <a href="<?= site_url('/admin/usuarios') ?>" class="dropdown-item">
            <i class="fas fa-users-cog mr-2 text-info"></i>
            Gestión de Usuarios
          </a>
        </li>
        <?php endif; ?>
        
        <?php if (isManagerLevel()): ?>
        <li>
          <a href="<?= site_url('/admin/leads') ?>" class="dropdown-item">
            <i class="fas fa-user-clock mr-2 text-warning"></i>
            Gestión de Leads
          </a>
        </li>
        <?php endif; ?>
        
        <li class="dropdown-divider"></li>
        
        <!-- Configuración y Ayuda -->
        <li>
          <a href="#" class="dropdown-item" onclick="toggleFullscreen()">
            <i class="fas fa-expand-arrows-alt mr-2 text-secondary"></i>
            Pantalla Completa
          </a>
        </li>
        
        <li>
          <a href="#" class="dropdown-item" data-toggle="modal" data-target="#helpModal">
            <i class="fas fa-question-circle mr-2 text-info"></i>
            Ayuda y Soporte
          </a>
        </li>
        
        <!-- Menu Footer - Acciones principales -->
        <li class="user-footer">
          <a href="<?= site_url('/admin/mi-perfil') ?>" class="btn btn-default btn-flat">
            <i class="fas fa-user-edit mr-1"></i>
            Mi Perfil
          </a>
          <a href="<?= site_url('/logout') ?>" class="btn btn-danger btn-flat float-right">
            <i class="fas fa-sign-out-alt mr-1"></i>
            Cerrar Sesión
          </a>
        </li>
      </ul>
    </li>

    <!-- ===== CONTROL SIDEBAR ===== -->
    <li class="nav-item">
      <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button" title="Configuración">
        <i class="fas fa-th-large"></i>
      </a>
    </li>
    
  </ul>
</nav>