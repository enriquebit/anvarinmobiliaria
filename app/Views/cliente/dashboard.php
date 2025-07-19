<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Mi Panel Personal<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Mi Panel Personal<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Mi Panel</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ===== MENSAJE DE BIENVENIDA ===== -->
<div class="row">
  <div class="col-12">
    <div class="callout callout-success">
      <h5><i class="fas fa-home"></i> ¡Bienvenido a tu panel personal!</h5>
      Aquí puedes gestionar toda tu información, documentos, pagos y ver el progreso de tu propiedad.
    </div>
  </div>
</div>

<!-- ===== ROW DE ESTADÍSTICAS PRINCIPALES ===== -->
<div class="row">
  <!-- Estado de Documentos -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3 id="documentos-aprobados">7/10</h3>
        <p>Documentos Aprobados</p>
      </div>
      <div class="icon">
        <i class="fas fa-file-check"></i>
      </div>
      <a href="<?= site_url('/cliente/documentos') ?>" class="small-box-footer">
        Ver documentos <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Progreso de Pagos -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3 id="progreso-pagos">75<sup style="font-size: 20px">%</sup></h3>
        <p>Pagos Completados</p>
      </div>
      <div class="icon">
        <i class="fas fa-percentage"></i>
      </div>
      <a href="<?= site_url('/cliente/pagos') ?>" class="small-box-footer">
        Ver pagos <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Progreso de Obra -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3 id="progreso-obra">45<sup style="font-size: 20px">%</sup></h3>
        <p>Progreso de Obra</p>
      </div>
      <div class="icon">
        <i class="fas fa-hard-hat"></i>
      </div>
      <a href="<?= site_url('/cliente/propiedad/progreso') ?>" class="small-box-footer">
        Ver progreso <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Próximo Pago -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3 id="proximo-pago">15</h3>
        <p>Días para próximo pago</p>
      </div>
      <div class="icon">
        <i class="fas fa-calendar-alt"></i>
      </div>
      <a href="<?= site_url('/cliente/pagos/pendientes') ?>" class="small-box-footer">
        Ver detalle <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

<!-- ===== ROW PRINCIPAL ===== -->
<div class="row">
  <!-- ===== MI PROPIEDAD ===== -->
  <div class="col-md-8">
    <div class="card card-success">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-home mr-2"></i>
          Mi Propiedad - Casa 105, Manzana A
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="maximize">
            <i class="fas fa-expand"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <!-- Imagen de la propiedad -->
            <img src="https://via.placeholder.com/400x250/28a745/fff?text=Mi+Casa" 
                 class="img-fluid rounded" 
                 alt="Mi Propiedad">
          </div>
          <div class="col-md-6">
            <h5>Residencial Los Pinos</h5>
            <dl class="row">
              <dt class="col-sm-4">Tipo:</dt>
              <dd class="col-sm-8">Casa habitación</dd>
              
              <dt class="col-sm-4">Superficie:</dt>
              <dd class="col-sm-8">120 m² construcción<br>200 m² terreno</dd>
              
              <dt class="col-sm-4">Estado:</dt>
              <dd class="col-sm-8">
                <span class="badge badge-warning">En construcción</span>
              </dd>
              
              <dt class="col-sm-4">Entrega:</dt>
              <dd class="col-sm-8">Diciembre 2024</dd>
            </dl>
            
            <!-- Progreso de construcción -->
            <div class="progress mb-3">
              <div class="progress-bar bg-warning" role="progressbar" 
                   style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                45% Completado
              </div>
            </div>
            
            <a href="<?= site_url('/cliente/propiedad') ?>" class="btn btn-success">
              <i class="fas fa-eye mr-1"></i>
              Ver detalles completos
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== INFORMACIÓN RÁPIDA ===== -->
  <div class="col-md-4">
    <!-- ===== ESTADO DE CUENTA ===== -->
    <div class="card card-success">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-dollar-sign mr-2"></i>
          Estado de Cuenta
        </h3>
      </div>
      <div class="card-body">
        <div class="info-box bg-light">
          <span class="info-box-icon bg-success">
            <i class="fas fa-check-circle"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Pagado</span>
            <span class="info-box-number">$750,000</span>
            <div class="progress">
              <div class="progress-bar bg-success" style="width: 75%"></div>
            </div>
            <span class="progress-description">75% del total</span>
          </div>
        </div>
        
        <div class="info-box bg-light">
          <span class="info-box-icon bg-warning">
            <i class="fas fa-clock"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Pendiente</span>
            <span class="info-box-number">$250,000</span>
            <div class="progress">
              <div class="progress-bar bg-warning" style="width: 25%"></div>
            </div>
            <span class="progress-description">25% restante</span>
          </div>
        </div>
        
        <div class="text-center mt-3">
          <a href="<?= site_url('/cliente/pagos') ?>" class="btn btn-success btn-sm">
            <i class="fas fa-credit-card mr-1"></i>
            Ver Estado Completo
          </a>
        </div>
      </div>
    </div>

    <!-- ===== PRÓXIMOS PAGOS ===== -->
    <div class="card card-warning">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-calendar-check mr-2"></i>
          Próximos Pagos
        </h3>
      </div>
      <div class="card-body p-0">
        <ul class="todo-list">
          <li>
            <span class="text">Pago mensual #8</span>
            <small class="badge badge-warning">
              <i class="far fa-calendar"></i> 15 Enero
            </small>
            <div class="tools">
              <span class="text-success font-weight-bold">$25,000</span>
            </div>
          </li>
          <li>
            <span class="text">Pago mensual #9</span>
            <small class="badge badge-info">
              <i class="far fa-calendar"></i> 15 Febrero
            </small>
            <div class="tools">
              <span class="text-success font-weight-bold">$25,000</span>
            </div>
          </li>
          <li>
            <span class="text">Pago final</span>
            <small class="badge badge-success">
              <i class="far fa-calendar"></i> 15 Marzo
            </small>
            <div class="tools">
              <span class="text-success font-weight-bold">$200,000</span>
            </div>
          </li>
        </ul>
      </div>
      <div class="card-footer clearfix">
        <a href="<?= site_url('/cliente/pagos/calendario') ?>" class="btn btn-warning btn-sm">
          <i class="fas fa-calendar-alt"></i> Ver Calendario
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ===== ROW SECUNDARIO ===== -->
<div class="row">
  <!-- ===== DOCUMENTOS PENDIENTES ===== -->
  <div class="col-md-6">
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-file-alt mr-2"></i>
          Estado de Documentos
        </h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Documento</th>
                <th>Estado</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Identificación Oficial</td>
                <td><span class="badge badge-success">Aprobado</span></td>
                <td><i class="fas fa-check text-success"></i></td>
              </tr>
              <tr>
                <td>Comprobante Domicilio</td>
                <td><span class="badge badge-success">Aprobado</span></td>
                <td><i class="fas fa-check text-success"></i></td>
              </tr>
              <tr>
                <td>RFC</td>
                <td><span class="badge badge-warning">Pendiente</span></td>
                <td>
                  <a href="<?= site_url('/cliente/documentos/subir') ?>" class="btn btn-xs btn-warning">
                    Subir
                  </a>
                </td>
              </tr>
              <tr>
                <td>CURP</td>
                <td><span class="badge badge-warning">Pendiente</span></td>
                <td>
                  <a href="<?= site_url('/cliente/documentos/subir') ?>" class="btn btn-xs btn-warning">
                    Subir
                  </a>
                </td>
              </tr>
              <tr>
                <td>Aviso de Privacidad</td>
                <td><span class="badge badge-danger">Rechazado</span></td>
                <td>
                  <a href="<?= site_url('/cliente/documentos/subir') ?>" class="btn btn-xs btn-danger">
                    Re-subir
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <a href="<?= site_url('/cliente/documentos') ?>" class="btn btn-info btn-sm">
          Ver Todos los Documentos
        </a>
      </div>
    </div>
  </div>

  <!-- ===== ACTIVIDAD RECIENTE ===== -->
  <div class="col-md-6">
    <div class="card card-success">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-clock mr-2"></i>
          Mi Actividad Reciente
        </h3>
      </div>
      <div class="card-body">
        <!-- Timeline -->
        <div class="timeline timeline-inverse">
          
          <!-- Timeline time label -->
          <div class="time-label">
            <span class="bg-success">Hoy</span>
          </div>
          
          <!-- Timeline item -->
          <div>
            <i class="fas fa-sign-in-alt bg-success"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> hace 10 min</span>
              <h3 class="timeline-header">Iniciaste sesión</h3>
              <div class="timeline-body">
                Acceso desde tu dispositivo habitual
              </div>
            </div>
          </div>
          
          <!-- Timeline time label -->
          <div class="time-label">
            <span class="bg-info">Ayer</span>
          </div>
          
          <!-- Timeline item -->
          <div>
            <i class="fas fa-file bg-info"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> ayer 14:30</span>
              <h3 class="timeline-header">Documento aprobado</h3>
              <div class="timeline-body">
                Tu comprobante de domicilio fue aprobado por nuestro equipo
              </div>
            </div>
          </div>
          
          <!-- Timeline item -->
          <div>
            <i class="fas fa-dollar-sign bg-warning"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> hace 2 días</span>
              <h3 class="timeline-header">Pago registrado</h3>
              <div class="timeline-body">
                Pago de $25,000 aplicado a tu cuenta correctamente
              </div>
            </div>
          </div>
          
          <!-- Timeline item -->
          <div>
            <i class="fas fa-camera bg-purple"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> hace 3 días</span>
              <h3 class="timeline-header">Fotos de obra actualizadas</h3>
              <div class="timeline-body">
                Nuevas fotos del progreso de tu propiedad disponibles
              </div>
              <div class="timeline-footer">
                <a href="<?= site_url('/cliente/propiedad/fotos') ?>" class="btn btn-primary btn-sm">
                  Ver fotos
                </a>
              </div>
            </div>
          </div>
          
          <!-- END timeline item -->
          <div>
            <i class="far fa-clock bg-gray"></i>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <a href="<?= site_url('/cliente/actividad') ?>" class="btn btn-success btn-sm">
          Ver Toda mi Actividad
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ===== ROW DE ACCESOS RÁPIDOS ===== -->
<div class="row">
  <div class="col-12">
    <div class="card card-outline card-success">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-bolt mr-2"></i>
          Accesos Rápidos
        </h3>
      </div>
      <div class="card-body">
        <div class="row">
          
          <!-- Subir Documento -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-gradient-info">
              <span class="info-box-icon">
                <i class="fas fa-upload"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Subir</span>
                <span class="info-box-number">Documento</span>
                <div class="progress">
                  <div class="progress-bar" style="width: 70%"></div>
                </div>
                <a href="<?= site_url('/cliente/documentos/subir') ?>" class="btn btn-info btn-sm mt-2">
                  <i class="fas fa-plus"></i> Subir ahora
                </a>
              </div>
            </div>
          </div>
          
          <!-- Realizar Pago -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-gradient-success">
              <span class="info-box-icon">
                <i class="fas fa-credit-card"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Realizar</span>
                <span class="info-box-number">Pago</span>
                <div class="progress">
                  <div class="progress-bar" style="width: 75%"></div>
                </div>
                <a href="<?= site_url('/cliente/pagos/realizar') ?>" class="btn btn-success btn-sm mt-2">
                  <i class="fas fa-dollar-sign"></i> Pagar ahora
                </a>
              </div>
            </div>
          </div>
          
          <!-- Contactar Soporte -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-gradient-warning">
              <span class="info-box-icon">
                <i class="fas fa-headset"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Contactar</span>
                <span class="info-box-number">Soporte</span>
                <div class="progress">
                  <div class="progress-bar" style="width: 100%"></div>
                </div>
                <a href="<?= site_url('/cliente/soporte') ?>" class="btn btn-warning btn-sm mt-2">
                  <i class="fas fa-comments"></i> Contactar
                </a>
              </div>
            </div>
          </div>
          
          <!-- Ver Progreso -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-gradient-danger">
              <span class="info-box-icon">
                <i class="fas fa-chart-line"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Ver</span>
                <span class="info-box-number">Progreso</span>
                <div class="progress">
                  <div class="progress-bar" style="width: 45%"></div>
                </div>
                <a href="<?= site_url('/cliente/propiedad/progreso') ?>" class="btn btn-danger btn-sm mt-2">
                  <i class="fas fa-hard-hat"></i> Ver obra
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== ROW DE INFORMACIÓN ADICIONAL ===== -->
<div class="row">
  <div class="col-md-6">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-phone mr-2"></i>
          Contactos Importantes
        </h3>
      </div>
      <div class="card-body">
        <dl class="row">
          <dt class="col-sm-4">Asesor:</dt>
          <dd class="col-sm-8">
            <strong>María García</strong><br>
            <i class="fas fa-phone"></i> 55 1234 5678<br>
            <i class="fas fa-envelope"></i> maria.garcia@anvar.com
          </dd>
          
          <dt class="col-sm-4">Soporte:</dt>
          <dd class="col-sm-8">
            <strong>Centro de Ayuda</strong><br>
            <i class="fas fa-phone"></i> 55 8765 4321<br>
            <i class="fas fa-envelope"></i> soporte@anvar.com
          </dd>
          
          <dt class="col-sm-4">Emergencias:</dt>
          <dd class="col-sm-8">
            <strong>24/7</strong><br>
            <i class="fas fa-phone"></i> 55 911 0000<br>
            <i class="fas fa-whatsapp"></i> 55 911 0001
          </dd>
        </dl>
      </div>
    </div>
  </div>
  
  <div class="col-md-6">
    <div class="card card-secondary">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-bullhorn mr-2"></i>
          Anuncios y Noticias
        </h3>
      </div>
      <div class="card-body">
        <div class="post">
          <div class="user-block">
            <span class="username">
              <a href="#">Anvar Inmobiliaria</a>
            </span>
            <span class="description">Hace 1 día</span>
          </div>
          <p>
            🎉 ¡Gran noticia! El avance de obra en Residencial Los Pinos va 
            según lo programado. Esperamos entregas puntuales en diciembre.
          </p>
        </div>
        
        <div class="post">
          <div class="user-block">
            <span class="username">
              <a href="#">Anvar Inmobiliaria</a>
            </span>
            <span class="description">Hace 3 días</span>
          </div>
          <p>
            📋 Recordatorio: Los documentos pendientes deben subirse antes 
            del día 20 para agilizar el proceso de escrituración.
          </p>
        </div>
      </div>
      <div class="card-footer">
        <a href="<?= site_url('/cliente/anuncios') ?>" class="btn btn-secondary btn-sm">
          Ver Todos los Anuncios
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
  // ===== ACTUALIZACIÓN DE DATOS EN TIEMPO REAL =====
  setInterval(function() {
    // Simular actualización de próximo pago
    const diasRestantes = Math.floor(Math.random() * 30) + 1;
    $('#proximo-pago').text(diasRestantes);
  }, 60000); // Cada minuto

  // ===== ANIMACIONES DE ENTRADA =====
  $('.small-box').each(function(index) {
    $(this).delay(index * 150).animate({
      opacity: 1
    }, 500);
  });

  // ===== EVENTOS DE CARDS =====
  $('.card').hover(
    function() {
      $(this).addClass('shadow-lg');
    },
    function() {
      $(this).removeClass('shadow-lg');
    }
  );

  // ===== CONFIGURACIÓN DE TOOLTIPS =====
  $('[data-toggle="tooltip"]').tooltip();

  // ===== PROGRESO ANIMADO =====
  $('.progress-bar').each(function() {
    const $this = $(this);
    const width = $this.attr('style').match(/width:\s*(\d+)%/);
    if (width) {
      $this.css('width', '0%').animate({
        width: width[0]
      }, 1000);
    }
  });
});

// ===== FUNCIÓN PARA ACCIONES RÁPIDAS =====
function accionRapida(accion) {
  const acciones = {
    'subir-documento': {
      url: '/cliente/documentos/subir',
      titulo: 'Subir Documento'
    },
    'realizar-pago': {
      url: '/cliente/pagos/realizar',
      titulo: 'Realizar Pago'
    },
    'contactar-soporte': {
      url: '/cliente/soporte',
      titulo: 'Contactar Soporte'
    },
    'ver-progreso': {
      url: '/cliente/propiedad/progreso',
      titulo: 'Ver Progreso de Obra'
    }
  };

  if (acciones[accion]) {
    window.location.href = acciones[accion].url;
  }
}

// ===== FUNCIÓN PARA MOSTRAR DETALLES DE PAGO =====
function mostrarDetallePago(id) {
  Swal.fire({
    title: 'Detalle del Pago',
    html: `
      <div class="text-left">
        <p><strong>ID:</strong> ${id}</p>
        <p><strong>Monto:</strong> $25,000</p>
        <p><strong>Fecha límite:</strong> 15 de Enero 2024</p>
        <p><strong>Concepto:</strong> Pago mensual #8</p>
        <p><strong>Estado:</strong> <span class="badge badge-warning">Pendiente</span></p>
      </div>
    `,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Pagar Ahora',
    cancelButtonText: 'Cerrar',
    confirmButtonColor: '#28a745'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '/cliente/pagos/realizar';
    }
  });
}

// ===== NOTIFICACIONES AUTOMÁTICAS =====
function verificarNotificaciones() {
  // Simular verificación de notificaciones
  // En una implementación real, harías una llamada AJAX
  const notificaciones = [
    'Nuevo documento aprobado',
    'Recordatorio: Próximo pago en 5 días',
    'Fotos de obra actualizadas'
  ];
  
  // Mostrar notificación aleatoria cada cierto tiempo
  if (Math.random() > 0.8) {
    const notif = notificaciones[Math.floor(Math.random() * notificaciones.length)];
    mostrarInfo(notif);
  }
}

// Verificar notificaciones cada 5 minutos
setInterval(verificarNotificaciones, 300000);
</script>
<?= $this->endSection() ?>