<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->renderSection('page_title') ?: $this->renderSection('title') ?: 'Mi Panel' ?> | Anvar Inmobiliaria</title>

  <!-- ===== FAVICON ===== -->
  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/img/favicon.ico') ?>">

  <!-- ===== GOOGLE FONTS ===== -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  
  <!-- ===== FONT AWESOME ===== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- ===== ADMINLTE THEME ===== -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <!-- ===== TOASTR ===== -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
  
  <!-- ===== DATATABLES ===== -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  
  <!-- ===== ANIMATE.CSS ===== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  
  <!-- ===== JQUERY (requerido para AdminLTE) ===== -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  
  <!-- ===== BOOTSTRAP 4 ===== -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- ===== ESTILOS PERSONALIZADOS CLIENTE ===== -->
  <style>
  :root {
    --cliente-primary: #28a745;     /* Verde principal */
    --cliente-secondary: #20c997;   /* Verde secundario */
    --cliente-accent: #17a2b8;      /* Azul accent */
    --cliente-success: #28a745;     /* Verde éxito */
    --cliente-warning: #ffc107;     /* Amarillo warning */
    --cliente-danger: #dc3545;      /* Rojo danger */
    --cliente-light: #f8f9fa;       /* Gris claro */
    --cliente-dark: #343a40;        /* Gris oscuro */
    --cliente-bg: #ffffff;          /* Fondo blanco */
  }

  /* ===== TEMA CLIENTE - VERDE Y LIMPIO ===== */
  .content-wrapper {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
  }

  /* ===== NAVBAR PERSONALIZADO CLIENTE ===== */
  .main-header.navbar {
    background: linear-gradient(135deg, var(--cliente-primary) 0%, var(--cliente-secondary) 100%) !important;
    border-bottom: 3px solid var(--cliente-accent) !important;
    box-shadow: 0 2px 15px rgba(40, 167, 69, 0.2) !important;
  }

  .navbar-nav .nav-link {
    color: #ffffff !important;
    transition: all 0.3s ease;
    border-radius: 5px;
    margin: 0 2px;
  }

  .navbar-nav .nav-link:hover {
    color: var(--cliente-accent) !important;
    background-color: rgba(255, 255, 255, 0.15) !important;
    transform: translateY(-1px);
  }

  /* ===== SIDEBAR PERSONALIZADO CLIENTE ===== */
  .main-sidebar {
    background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%) !important;
    border-right: 3px solid var(--cliente-primary) !important;
    box-shadow: 2px 0 10px rgba(40, 167, 69, 0.1) !important;
  }

  .sidebar-light-success .nav-sidebar > .nav-item > .nav-link {
    color: var(--cliente-dark) !important;
    transition: all 0.3s ease !important;
    border-left: 3px solid transparent !important;
    margin: 3px 0 !important;
    border-radius: 0 25px 25px 0 !important;
    font-weight: 500 !important;
  }

  .sidebar-light-success .nav-sidebar > .nav-item > .nav-link:hover {
    background: linear-gradient(135deg, var(--cliente-primary) 0%, var(--cliente-secondary) 100%) !important;
    color: #ffffff !important;
    border-left-color: var(--cliente-accent) !important;
    transform: translateX(5px);
    box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
  }

  .sidebar-light-success .nav-sidebar > .nav-item > .nav-link.active {
    background: linear-gradient(135deg, var(--cliente-primary) 0%, var(--cliente-secondary) 100%) !important;
    color: #ffffff !important;
    border-left-color: var(--cliente-accent) !important;
    font-weight: 600 !important;
    box-shadow: 0 3px 15px rgba(40, 167, 69, 0.4);
  }

  /* ===== BRAND LINK CLIENTE ===== */
  .brand-link {
    border-bottom: 2px solid var(--cliente-primary) !important;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
    transition: all 0.3s ease !important;
  }

  .brand-link:hover {
    background: linear-gradient(135deg, var(--cliente-light) 0%, #ffffff 100%) !important;
  }

  .brand-text {
    color: var(--cliente-primary) !important;
    font-weight: 700 !important;
    font-size: 1.2rem !important;
    text-shadow: 0 1px 3px rgba(40, 167, 69, 0.2);
  }

  .brand-image {
    opacity: 0.9 !important;
    transition: all 0.3s ease !important;
    border: 2px solid var(--cliente-primary) !important;
  }

  .brand-image:hover {
    opacity: 1 !important;
    transform: scale(1.1) rotate(5deg) !important;
    border-color: var(--cliente-secondary) !important;
  }

  /* ===== CARDS MEJORADAS CLIENTE ===== */
  .card {
    border: none !important;
    border-radius: 15px !important;
    box-shadow: 0 4px 20px rgba(40, 167, 69, 0.1) !important;
    transition: all 0.3s ease !important;
    background: var(--cliente-bg) !important;
    overflow: hidden !important;
  }

  .card:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 8px 30px rgba(40, 167, 69, 0.2) !important;
  }

  .card-header {
    background: linear-gradient(135deg, var(--cliente-primary) 0%, var(--cliente-secondary) 100%) !important;
    border-bottom: none !important;
    border-radius: 15px 15px 0 0 !important;
    color: #ffffff !important;
    padding: 1rem 1.5rem !important;
  }

  .card-title {
    color: #ffffff !important;
    font-weight: 600 !important;
    margin: 0 !important;
    font-size: 1.1rem !important;
  }

  .card-body {
    padding: 1.5rem !important;
  }

  /* ===== BOTONES PERSONALIZADOS CLIENTE ===== */
  .btn {
    border-radius: 25px !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 0.5rem 1.5rem !important;
  }

  .btn-success {
    background: linear-gradient(135deg, var(--cliente-primary) 0%, var(--cliente-secondary) 100%) !important;
    border: none !important;
    box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3) !important;
  }

  .btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4) !important;
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--cliente-accent) 0%, #138496 100%) !important;
    border: none !important;
  }

  .btn-warning {
    background: linear-gradient(135deg, var(--cliente-warning) 0%, #e0a800 100%) !important;
    border: none !important;
    color: var(--cliente-dark) !important;
  }

  .btn-danger {
    background: linear-gradient(135deg, var(--cliente-danger) 0%, #c82333 100%) !important;
    border: none !important;
  }

  /* ===== SMALL BOXES CLIENTE ===== */
  .small-box {
    border-radius: 15px !important;
    overflow: hidden !important;
    transition: all 0.3s ease !important;
    position: relative !important;
  }

  .small-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 0%, rgba(255,255,255,0.1) 100%);
    pointer-events: none;
  }

  .small-box:hover {
    transform: translateY(-3px) scale(1.02) !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2) !important;
  }

  .small-box .icon {
    transition: all 0.3s ease !important;
  }

  .small-box:hover .icon {
    transform: scale(1.1) rotate(10deg) !important;
  }

  /* ===== ALERTAS MEJORADAS CLIENTE ===== */
  .alert {
    border: none !important;
    border-radius: 10px !important;
    border-left: 5px solid !important;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1) !important;
    font-weight: 500 !important;
  }

  .alert-success {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%) !important;
    color: var(--cliente-primary) !important;
    border-left-color: var(--cliente-primary) !important;
  }

  .alert-danger {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%) !important;
    color: var(--cliente-danger) !important;
    border-left-color: var(--cliente-danger) !important;
  }

  .alert-warning {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%) !important;
    color: #856404 !important;
    border-left-color: var(--cliente-warning) !important;
  }

  .alert-info {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(23, 162, 184, 0.05) 100%) !important;
    color: var(--cliente-accent) !important;
    border-left-color: var(--cliente-accent) !important;
  }

  /* ===== BREADCRUMBS CLIENTE ===== */
  .breadcrumb {
    background: transparent !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .breadcrumb-item a {
    color: var(--cliente-primary) !important;
    text-decoration: none !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
  }

  .breadcrumb-item a:hover {
    color: var(--cliente-secondary) !important;
    text-decoration: underline !important;
  }

  .breadcrumb-item.active {
    color: var(--cliente-dark) !important;
    font-weight: 600 !important;
  }

  /* ===== USER PANEL CLIENTE ===== */
  .user-panel {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(255,255,255,0.1) 100%) !important;
    border-radius: 10px !important;
    padding: 1rem !important;
    margin: 1rem 0.5rem !important;
    border: 1px solid rgba(40, 167, 69, 0.1) !important;
  }

  .user-panel .info a {
    color: var(--cliente-primary) !important;
    font-weight: 600 !important;
    text-decoration: none !important;
  }

  .user-panel .info small {
    color: var(--cliente-secondary) !important;
    font-weight: 500 !important;
  }

  /* ===== RESPONSIVE MEJORAS ===== */
  @media (max-width: 768px) {
    .content-header h1 {
      font-size: 1.5rem !important;
    }
    
    .card {
      margin-bottom: 1rem !important;
    }

    .btn {
      padding: 0.4rem 1rem !important;
      font-size: 0.9rem !important;
    }
  }

  /* ===== ANIMACIONES CLIENTE ===== */
  .content-wrapper {
    animation: fadeInUp 0.5s ease-in-out;
  }

  @keyframes fadeInUp {
    from { 
      opacity: 0; 
      transform: translateY(20px); 
    }
    to { 
      opacity: 1; 
      transform: translateY(0); 
    }
  }

  /* Animación para cards */
  .card {
    animation: slideInUp 0.6s ease-out;
  }

  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* ===== EFECTOS HOVER ESPECIALES ===== */
  .nav-link, .btn, .card {
    position: relative;
    overflow: hidden;
  }

  .nav-link::before, .btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255,255,255,0.2);
    transition: all 0.3s ease;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    z-index: 0;
  }

  .nav-link:hover::before, .btn:hover::before {
    width: 300px;
    height: 300px;
  }

  /* ===== COLORES PRINCIPALES CLIENTE ===== */
  .text-success {
    color: var(--cliente-primary) !important;
  }

  .bg-success {
    background: var(--cliente-primary) !important;
    color: #ffffff !important;
  }

  .border-success {
    border-color: var(--cliente-primary) !important;
  }
  </style>

  <!-- ===== ESTILOS ADICIONALES ===== -->
  <?= $this->renderSection('styles') ?>

  <!-- ===== META TAGS ADICIONALES ===== -->
  <meta name="description" content="Panel de Cliente - Anvar Inmobiliaria">
  <meta name="keywords" content="cliente, inmobiliaria, propiedades, pagos">
  <meta name="author" content="Anvar Inmobiliaria">
  <meta name="robots" content="noindex, nofollow">
  
  <!-- ===== CSRF TOKEN ===== -->
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  
  <!-- ===== BASE URL ===== -->
  <meta name="base-url" content="<?= base_url() ?>">
</head>