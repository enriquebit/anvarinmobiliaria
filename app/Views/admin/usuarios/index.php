<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
  <?php if (isset($item['url']) && !empty($item['url'])): ?>
    <li class="breadcrumb-item"><a href="<?= site_url($item['url']) ?>"><?= $item['name'] ?></a></li>
  <?php else: ?>
    <li class="breadcrumb-item active"><?= $item['name'] ?></li>
  <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ===== ESTADÍSTICAS RÁPIDAS ===== -->
<div class="row mb-3">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3><?= $stats['total'] ?></h3>
        <p>Total Staff Administrativo</p>
      </div>
      <div class="icon">
        <i class="fas fa-users"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?= $stats['activos'] ?></h3>
        <p>Activos</p>
      </div>
      <div class="icon">
        <i class="fas fa-user-check"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3><?= $stats['inactivos'] ?></h3>
        <p>Inactivos</p>
      </div>
      <div class="icon">
        <i class="fas fa-user-times"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner">
        <h3><i class="fas fa-plus"></i></h3>
        <p>Nuevo Usuario</p>
      </div>
      <div class="icon">
        <i class="fas fa-user-plus"></i>
      </div>
      <a href="<?= site_url('/admin/usuarios/create') ?>" class="small-box-footer">
        Crear Usuario <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

<!-- ===== ALERTA DE INFORMACIÓN ===== -->
<div class="row mb-3">
  <div class="col-12">
    <div class="alert alert-info">
      <h6><i class="fas fa-info-circle mr-2"></i>Información del Sistema</h6>
      <p class="mb-0">
        Los usuarios marcados en <span class="badge badge-warning">amarillo</span> son usuarios existentes en Shield 
        que necesitan completar su información adicional. Usa el botón 
        <span class="btn btn-success btn-xs"><i class="fas fa-plus"></i> Completar</span> 
        para agregar detalles como nombre, teléfono y agencia.
      </p>
    </div>
  </div>
</div>

<!-- ===== FILTROS Y BÚSQUEDA ===== -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-filter mr-2"></i>
          Filtros de Búsqueda
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- 🎯 MVP: Formulario simple usando GET nativo de CodeIgniter -->
        <form method="GET" action="<?= current_url() ?>" class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="buscar">Buscar por Email</label>
              <input type="text" 
                     class="form-control" 
                     id="buscar" 
                     name="buscar" 
                     value="<?= esc($filtros_actuales['buscar']) ?>" 
                     placeholder="email@ejemplo.com">
            </div>
          </div>
          
          <div class="col-md-3">
            <div class="form-group">
              <label for="grupo">Filtrar por Tipo</label>
              <select class="form-control" id="grupo" name="tipo">
                <option value="">Todos los tipos</option>
                <?php foreach ($tipos_filtro['grupos'] as $key => $nombre): ?>
                  <option value="<?= $key ?>" <?= ($filtros_actuales['tipo'] == $key) ? 'selected' : '' ?>>
                    <?= $nombre ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <div class="col-md-3">
            <div class="form-group">
              <label for="estado">Filtrar por Estado</label>
              <select class="form-control" id="estado" name="estado">
                <option value="">Todos los estados</option>
                <?php foreach ($tipos_filtro['estados'] as $key => $nombre): ?>
                  <option value="<?= $key ?>" <?= ($filtros_actuales['estado'] == $key) ? 'selected' : '' ?>>
                    <?= $nombre ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <div class="col-md-3">
            <div class="form-group">
              <label>&nbsp;</label>
              <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <a href="<?= current_url() ?>" class="btn btn-secondary">
                  <i class="fas fa-undo"></i> Limpiar
                </a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===== TABLA DE USUARIOS ===== -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-users mr-2"></i>
          Listado de Usuarios Administrativos (<?= $usuarios['total'] ?> registros)
        </h3>
        <div class="card-tools">
          <a href="<?= site_url('/admin/usuarios/create') ?>" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Nuevo Usuario
          </a>
        </div>
      </div>
      
      <div class="card-body table-responsive p-0">
        <?php if (!empty($usuarios['data'])): ?>
          
          <!-- 🎯 MVP: Tabla HTML simple sin DataTables - aprovechando Bootstrap -->
          <table class="table table-hover text-nowrap">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Tipo/Agencia</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
                <th width="150">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($usuarios['data'] as $staffEntity): ?>
                <tr class="<?= $staffEntity->esSinInformacionStaff() ? 'table-warning' : '' ?>">
                  <td><?= $staffEntity->user_id ?></td>
                  <td>
                    <!-- 🎯 Entity-First: Mostrar nombre o indicación -->
                    <?= $staffEntity->getNombreFormateado() ?>
                    <?php if ($staffEntity->esSinInformacionStaff()): ?>
                      <br><small class="text-muted">Información incompleta</small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <!-- 🎯 Entity-First: Email desde la Entity (lazy loading o Shield) -->
                    <?= esc($staffEntity->getEmail() ?? 'Sin email') ?>
                  </td>
                  <td>
                    <!-- 🎯 Entity-First: Teléfono formateado desde Entity -->
                    <?= $staffEntity->getTelefonoFormateado() ?>
                  </td>
                  <td>
                    <!-- 🎯 Entity-First: Tipo y Agencia desde Entity -->
                    <div>
                      <?= $staffEntity->getTipoBadge() ?>
                      <?php if ($staffEntity->agencia): ?>
                        <br><small class="text-muted"><?= $staffEntity->getAgenciaFormateada() ?></small>
                      <?php else: ?>
                        <br><small class="text-muted">Sin agencia</small>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <!-- 🎯 Entity-First: Estado desde Entity (delegado a User) -->
                    <?= $staffEntity->getEstadoBadge() ?>
                  </td>
                  <td>
                    <!-- 🎯 Entity-First: Fecha formateada desde Entity -->
                    <?= $staffEntity->getFechaCreacionFormateada() ?>
                  </td>
                  <td>
                    <!-- 🎯 Entity-First: Botones desde Entity -->
                    <?= $staffEntity->getBotonesAccion() ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          
        <?php else: ?>
          <div class="text-center p-4">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay usuarios administrativos registrados</h5>
            <p class="text-muted">Puedes crear el primer usuario administrativo.</p>
            <a href="<?= site_url('/admin/usuarios/create') ?>" class="btn btn-primary">
              <i class="fas fa-plus"></i> Crear Primer Usuario
            </a>
          </div>
        <?php endif; ?>
      </div>
      
      <?php if (!empty($usuarios['data'])): ?>
        <div class="card-footer clearfix">
          <!-- 🎯 MVP: Paginación nativa de CodeIgniter - ¡Muy simple! -->
          <?= $usuarios['pager'] ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// 🎯 MVP: JavaScript simple sin dependencias complejas
function cambiarEstadoUsuario(userId, nuevoEstado) {
  const accion = nuevoEstado ? 'activar' : 'desactivar';
  const mensaje = `¿Estás seguro de ${accion} este usuario?`;
  
  // 🎯 Usar SweetAlert que ya tienes en AdminLTE
  Swal.fire({
    title: '¿Confirmar acción?',
    text: mensaje,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      // 🎯 MVP: Formulario simple HTML en lugar de AJAX
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `<?= site_url('/admin/usuarios/cambiarEstado/') ?>${userId}`;
      
      // CSRF Token
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '<?= csrf_token() ?>';
      csrfInput.value = '<?= csrf_hash() ?>';
      form.appendChild(csrfInput);
      
      // Estado
      const estadoInput = document.createElement('input');
      estadoInput.type = 'hidden';
      estadoInput.name = 'estado';
      estadoInput.value = nuevoEstado;
      form.appendChild(estadoInput);
      
      document.body.appendChild(form);
      form.submit();
    }
  });
}

// 🎯 MVP: Notificaciones automáticas usando Toastr que ya tienes
$(document).ready(function() {
  <?php if (session()->getFlashdata('success')): ?>
    toastr.success('<?= session()->getFlashdata('success') ?>');
  <?php endif; ?>
  
  <?php if (session()->getFlashdata('error')): ?>
    toastr.error('<?= session()->getFlashdata('error') ?>');
  <?php endif; ?>
});
</script>
<?= $this->endSection() ?>