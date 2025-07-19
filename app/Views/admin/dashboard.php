<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dashboard Administrativo<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Panel de Control<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item active">Dashboard</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ===== ROW DE ESTADÍSTICAS PRINCIPALES ===== -->
<div class="row">
  <!-- Total Contactos -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3 id="total-contactos"><?= number_format($metricas['totalContactos']) ?></h3>
        <p>Total Contactos</p>
        <small><?= $metricas['totalProspectos'] ?> prospectos, <?= $metricas['totalClientesOficiales'] ?> clientes</small>
      </div>
      <div class="icon">
        <i class="fas fa-users"></i>
      </div>
      <a href="<?= site_url('/admin/leads') ?>" class="small-box-footer">
        Ver más <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Ventas del Mes -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3 id="ventas-mes"><?= $metricas['ventasMes'] ?></h3>
        <p>Ventas del Mes</p>
      </div>
      <div class="icon">
        <i class="fas fa-handshake"></i>
      </div>
      <a href="<?= site_url('/admin/ventas') ?>" class="small-box-footer">
        Ver más <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Proyectos Activos -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3 id="proyectos-activos"><?= $metricas['proyectosActivos'] ?></h3>
        <p>Proyectos Activos</p>
      </div>
      <div class="icon">
        <i class="fas fa-project-diagram"></i>
      </div>
      <a href="<?= site_url('/admin/proyectos') ?>" class="small-box-footer">
        Ver más <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <!-- Ingresos del Mes -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3 id="ingresos-mes">$<?= number_format($metricas['ingresosMes'], 0) ?></h3>
        <p>Ingresos del Mes</p>
      </div>
      <div class="icon">
        <i class="fas fa-dollar-sign"></i>
      </div>
      <a href="<?= site_url('/admin/pagos') ?>" class="small-box-footer">
        Ver más <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

<!-- ===== ROW PRINCIPAL ===== -->
<div class="row">
  <!-- ===== GRÁFICO DE VENTAS ===== -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-area mr-2"></i>
          Reporte de Ventas - Últimos 12 Meses
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
        <canvas id="ventasChart" width="400" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- ===== INFORMACIÓN RÁPIDA ===== -->
  <div class="col-md-4">
    <!-- ===== CLIENTES RECIENTES ===== -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-users mr-2"></i>
          Actividad Reciente de Clientes
        </h3>
      </div>
      <div class="card-body p-0">
        <?php if (!empty($clientesRecientes)): ?>
          <ul class="users-list clearfix">
            <?php 
            $colores = ['28a745', '007bff', 'ffc107', 'dc3545'];
            foreach ($clientesRecientes as $index => $cliente): 
              $nombre = $cliente->nombre_mostrar;
              $apellido = $cliente->apellido_mostrar;
              $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
              $color = $colores[$index % count($colores)];
              $fechaRegistro = date('d/m', strtotime($cliente->fecha_registro));
              
              // Determinar URL y badge según tipo
              if ($cliente->tipo === 'prospecto') {
                $url = site_url('/admin/leads/show/' . $cliente->id);
                $badge = '<small class="badge badge-info">Prospecto</small>';
              } else {
                $url = site_url('/admin/clientes/show/' . $cliente->id);
                $badge = '<small class="badge badge-success">Cliente</small>';
              }
            ?>
            <li>
              <div class="avatar-placeholder" style="width: 50px; height: 50px; background-color: #<?= $color ?>; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; font-size: 16px;">
                <?= $iniciales ?>
              </div>
              <a class="users-list-name" href="<?= $url ?>">
                <?= esc($nombre . ' ' . $apellido) ?>
                <?= $badge ?>
              </a>
              <span class="users-list-date"><?= $fechaRegistro ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-center p-3">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">No hay registros recientes</p>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-footer text-center">
        <a href="<?= site_url('/admin/leads') ?>" class="btn btn-primary btn-sm">
          Ver Todos los Registros
        </a>
      </div>
    </div>

    <!-- ===== TAREAS PENDIENTES ===== -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-tasks mr-2"></i>
          Tareas Pendientes
        </h3>
      </div>
      <div class="card-body p-0">
        <ul class="todo-list">
          <li>
            <span class="handle">
              <i class="fas fa-ellipsis-v"></i>
              <i class="fas fa-ellipsis-v"></i>
            </span>
            <span class="text">Revisar documentos de Juan Pérez</span>
            <small class="badge badge-danger"><i class="far fa-clock"></i> Urgente</small>
          </li>
          <li>
            <span class="handle">
              <i class="fas fa-ellipsis-v"></i>
              <i class="fas fa-ellipsis-v"></i>
            </span>
            <span class="text">Generar reporte mensual</span>
            <small class="badge badge-info"><i class="far fa-clock"></i> 2 días</small>
          </li>
          <li>
            <span class="handle">
              <i class="fas fa-ellipsis-v"></i>
              <i class="fas fa-ellipsis-v"></i>
            </span>
            <span class="text">Llamar a María García</span>
            <small class="badge badge-warning"><i class="far fa-clock"></i> Mañana</small>
          </li>
        </ul>
      </div>
      <div class="card-footer clearfix">
        <button type="button" class="btn btn-primary float-right btn-sm">
          <i class="fas fa-plus"></i> Agregar tarea
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===== ROW SECUNDARIO ===== -->
<div class="row">
  <!-- ===== PROPIEDADES DESTACADAS ===== -->
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-home mr-2"></i>
          Propiedades Destacadas
        </h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="info-box bg-light">
              <span class="info-box-icon bg-success">
                <i class="fas fa-building"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Disponibles</span>
                <span class="info-box-number"><?= $proyectosEstadisticas['lotesDisponibles'] ?></span>
                <div class="progress">
                  <div class="progress-bar bg-success" style="width: <?= $proyectosEstadisticas['porcentajeDisponible'] ?>%"></div>
                </div>
                <span class="progress-description"><?= $proyectosEstadisticas['porcentajeDisponible'] ?>% disponibles</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-box bg-light">
              <span class="info-box-icon bg-warning">
                <i class="fas fa-handshake"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Vendidos</span>
                <span class="info-box-number"><?= $proyectosEstadisticas['lotesVendidos'] ?></span>
                <div class="progress">
                  <div class="progress-bar bg-warning" style="width: <?= $proyectosEstadisticas['porcentajeVendido'] ?>%"></div>
                </div>
                <span class="progress-description"><?= $proyectosEstadisticas['porcentajeVendido'] ?>% vendidos</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Lista de propiedades -->
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Propiedad</th>
                <th>Proyecto</th>
                <th>Precio</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Casa 101 - Manzana A</td>
                <td>Residencial Los Pinos</td>
                <td>$1,250,000</td>
                <td><span class="badge badge-success">Disponible</span></td>
              </tr>
              <tr>
                <td>Terreno 205 - Manzana B</td>
                <td>Fraccionamiento El Lago</td>
                <td>$850,000</td>
                <td><span class="badge badge-warning">Apartada</span></td>
              </tr>
              <tr>
                <td>Depto 301 - Torre C</td>
                <td>Condominios Vista Mar</td>
                <td>$2,100,000</td>
                <td><span class="badge badge-danger">Vendida</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <a href="<?= site_url('/admin/propiedades') ?>" class="btn btn-primary btn-sm">
          Ver Todas las Propiedades
        </a>
      </div>
    </div>
  </div>

  <!-- ===== ACTIVIDAD RECIENTE ===== -->
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-clock mr-2"></i>
          Actividad Reciente
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
            <i class="fas fa-user bg-primary"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> hace 5 min</span>
              <h3 class="timeline-header"><a href="#">Juan Pérez</a> se registró como cliente</h3>
              <div class="timeline-body">
                Nuevo cliente registrado y documentos pendientes de revisión.
              </div>
              <div class="timeline-footer">
                <a href="#" class="btn btn-primary btn-sm">Ver perfil</a>
              </div>
            </div>
          </div>
          
          <!-- Timeline item -->
          <div>
            <i class="fas fa-handshake bg-success"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> hace 1 hora</span>
              <h3 class="timeline-header"><a href="#">María García</a> realizó un pago</h3>
              <div class="timeline-body">
                Pago de $50,000 aplicado al contrato #VNT-2024-001
              </div>
              <div class="timeline-footer">
                <a href="#" class="btn btn-warning btn-sm">Ver recibo</a>
              </div>
            </div>
          </div>
          
          <!-- Timeline time label -->
          <div class="time-label">
            <span class="bg-info">Ayer</span>
          </div>
          
          <!-- Timeline item -->
          <div>
            <i class="fas fa-home bg-warning"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> ayer 15:30</span>
              <h3 class="timeline-header">Nueva propiedad agregada</h3>
              <div class="timeline-body">
                Casa 105 - Residencial Los Pinos agregada al inventario
              </div>
            </div>
          </div>
          
          <!-- Timeline item -->
          <div>
            <i class="fas fa-file bg-info"></i>
            <div class="timeline-item">
              <span class="time"><i class="far fa-clock"></i> ayer 10:15</span>
              <h3 class="timeline-header">Documentos aprobados</h3>
              <div class="timeline-body">
                Documentos de Carlos Ruiz aprobados por el equipo legal
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
        <a href="<?= site_url('/admin/actividad') ?>" class="btn btn-primary btn-sm">
          Ver Toda la Actividad
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ===== ROW DE REPORTES RÁPIDOS ===== -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-pie mr-2"></i>
          Resumen Financiero del Mes
        </h3>
        <div class="card-tools">
          <div class="btn-group">
            <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
              <i class="fas fa-calendar"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right" role="menu">
              <a href="#" class="dropdown-item">Este mes</a>
              <a href="#" class="dropdown-item">Mes anterior</a>
              <a href="#" class="dropdown-item">Últimos 3 meses</a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">Personalizado</a>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Ingresos -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
              <span class="info-box-icon bg-success">
                <i class="fas fa-arrow-up"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Ingresos</span>
                <span class="info-box-number">$<?= number_format($resumenFinanciero['ingresos'], 0) ?></span>
                <span class="progress-description">
                  <?php if ($resumenFinanciero['cambioIngresos'] >= 0): ?>
                    <i class="fas fa-caret-up text-success"></i> <?= abs($resumenFinanciero['cambioIngresos']) ?>% vs mes anterior
                  <?php else: ?>
                    <i class="fas fa-caret-down text-danger"></i> <?= abs($resumenFinanciero['cambioIngresos']) ?>% vs mes anterior
                  <?php endif; ?>
                </span>
              </div>
            </div>
          </div>
          
          <!-- Comisiones -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
              <span class="info-box-icon bg-info">
                <i class="fas fa-percentage"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Comisiones</span>
                <span class="info-box-number">$<?= number_format($resumenFinanciero['comisiones'], 0) ?></span>
                <span class="progress-description">
                  <i class="fas fa-info-circle text-info"></i> <?= $resumenFinanciero['porcentajeComisiones'] ?>% del total
                </span>
              </div>
            </div>
          </div>
          
          <!-- Gastos -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
              <span class="info-box-icon bg-warning">
                <i class="fas fa-arrow-down"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Gastos</span>
                <span class="info-box-number">$<?= number_format($resumenFinanciero['gastos'], 0) ?></span>
                <span class="progress-description">
                  <i class="fas fa-info-circle text-muted"></i> Estimado 15%
                </span>
              </div>
            </div>
          </div>
          
          <!-- Ganancia Neta -->
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
              <span class="info-box-icon bg-danger">
                <i class="fas fa-chart-line"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text">Ganancia Neta</span>
                <span class="info-box-number">$<?= number_format($resumenFinanciero['gananciaNeta'], 0) ?></span>
                <span class="progress-description">
                  <i class="fas fa-calculator text-primary"></i> Después de gastos
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
  // ===== GRÁFICO DE VENTAS =====
  const ctx = document.getElementById('ventasChart').getContext('2d');
  const ventasChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      datasets: [{
        label: 'Ventas <?= $ventasChart['anioActual'] ?>',
        data: <?= json_encode($ventasChart['ventasActual']) ?>,
        borderColor: 'rgb(54, 162, 235)',
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        tension: 0.1,
        fill: true
      }, {
        label: 'Ventas <?= $ventasChart['anioAnterior'] ?>',
        data: <?= json_encode($ventasChart['ventasAnterior']) ?>,
        borderColor: 'rgb(255, 99, 132)',
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        tension: 0.1,
        fill: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
        title: {
          display: true,
          text: 'Comparativo de Ventas por Mes'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return value + ' ventas';
            }
          }
        }
      }
    }
  });

  // ===== ACTUALIZACIÓN DE DATOS EN TIEMPO REAL =====
  setInterval(function() {
    // Simular actualización de datos
    const random = Math.floor(Math.random() * 10) + 145;
    $('#total-usuarios').text(random);
  }, 30000); // Cada 30 segundos

  // ===== CONFIGURACIÓN DE TOOLTIPS =====
  $('[data-toggle="tooltip"]').tooltip();

  // ===== AUTO-REFRESH DE NOTIFICACIONES =====
  function actualizarNotificaciones() {
    // Aquí harías una llamada AJAX para obtener notificaciones nuevas
    // $.get('/admin/api/notificaciones', function(data) {
    //   $('#notification-count').text(data.count);
    // });
  }

  // Actualizar notificaciones cada 2 minutos
  setInterval(actualizarNotificaciones, 120000);

  // ===== ANIMACIONES DE ENTRADA =====
  $('.small-box').each(function(index) {
    $(this).delay(index * 100).animate({
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
});

// ===== FUNCIÓN PARA MOSTRAR DETALLES =====
function mostrarDetalles(tipo, id) {
  // Usar SweetAlert para mostrar detalles
  Swal.fire({
    title: 'Cargando detalles...',
    html: 'Por favor espere mientras cargamos la información',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
      
      // Simular carga de datos
      setTimeout(() => {
        Swal.fire({
          title: `Detalles de ${tipo}`,
          html: `<p>Información detallada del elemento con ID: ${id}</p>`,
          icon: 'info',
          confirmButtonText: 'Cerrar'
        });
      }, 1000);
    }
  });
}

// ===== FUNCIÓN PARA ACCIONES RÁPIDAS =====
function accionRapida(accion) {
  const acciones = {
    'nueva-venta': {
      url: '<?= base_url('admin/ventas/crear') ?>',
      titulo: 'Nueva Venta'
    },
    'nuevo-cliente': {
      url: '<?= base_url('admin/usuarios/crear?role=cliente') ?>',
      titulo: 'Nuevo Cliente'
    },
    'nueva-propiedad': {
      url: '<?= base_url('admin/propiedades/crear') ?>',
      titulo: 'Nueva Propiedad'
    }
  };

  if (acciones[accion]) {
    window.location.href = acciones[accion].url;
  }
}
</script>
<?= $this->endSection() ?>