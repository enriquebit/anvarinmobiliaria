<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->renderSection('page_title') ?: $this->renderSection('title') ?: 'Dashboard' ?> | Anvar Inmobiliaria</title>

  <!-- ===== FAVICON ===== -->
  <!-- Usando emoji como favicon temporal para desarrollo -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏠</text></svg>">

  <!-- ===== GOOGLE FONTS ===== -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  
  <!-- ===== FONT AWESOME ===== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <!-- Fallback FontAwesome 6 si el anterior falla -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
  
  <!-- ===== ADMINLTE THEME ===== -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <!-- ===== TOASTR ===== -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
  
  <!-- ===== DATATABLES ===== -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  
  <!-- ===== SELECT2 ===== -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
  
  <!-- ===== JQUERY (requerido para AdminLTE) ===== -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  
  <!-- ===== BOOTSTRAP 4 ===== -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- ===== ESTILOS MÍNIMOS PARA ADMIN ===== -->
  <style>
  /* Avatar initials helper */
  .avatar-initials {
    width: 40px;
    height: 40px;
    background-color: #28a745;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
  }
  
  /* Loading spinner fix */
  body.loaded {
    /* Clase para indicar que la página terminó de cargar */
  }
  </style>

  <!-- ===== ANVAR CUSTOM OVERRIDES ===== -->
  <link rel="stylesheet" href="<?= base_url('assets/css/anvar-override.css') ?>">

  <!-- ===== ESTILOS ADICIONALES ===== -->
  <?= $this->renderSection('styles') ?>

  <!-- ===== META TAGS ADICIONALES ===== -->
  <meta name="description" content="Sistema de Gestión Inmobiliaria - Anvar Inmobiliaria">
  <meta name="keywords" content="inmobiliaria, gestión, propiedades, ventas">
  <meta name="author" content="Anvar Inmobiliaria">
  <meta name="robots" content="noindex, nofollow">
  
  <!-- ===== CSRF TOKEN ===== -->
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  
  <!-- ===== BASE URL ===== -->
  <meta name="base-url" content="<?= base_url() ?>">
  
  <!-- ===== LOADING SPINNER FIX ===== -->
  <script>
  // Detener spinner de carga una vez que la página esté completamente cargada
  $(window).on('load', function() {
    // Ocultar cualquier spinner de carga del navegador
    if (document.readyState === 'complete') {
      // Forzar que el navegador detenga la animación de carga
      setTimeout(function() {
        $('body').addClass('loaded');
      }, 100);
    }
  });
  </script>
</head>