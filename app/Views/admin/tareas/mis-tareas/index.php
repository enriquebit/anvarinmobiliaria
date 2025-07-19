<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-tasks"></i>
                    Mis Tareas
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Mis Tareas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Usuario -->
<section class="content">
    <div class="container-fluid">
        <!-- Estadísticas Generales -->
        <div class="row">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $estadisticas['general']['total'] ?></h3>
                        <p>Total Tareas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?= $estadisticas['propias']['total'] ?></h3>
                        <p>Mis Tareas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-dark">
                    <div class="inner">
                        <h3><?= $estadisticas['asignadas']['total'] ?></h3>
                        <p>Asignadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $estadisticas['general']['pendiente'] ?></h3>
                        <p>Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $estadisticas['general']['en_proceso'] ?></h3>
                        <p>En Proceso</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $estadisticas['general']['completada'] ?></h3>
                        <p>Completadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas del Usuario -->
        <?php if (!empty($tareasVencidas)): ?>
        <div class="alert alert-danger">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Tareas Vencidas</h5>
            Tienes <?= count($tareasVencidas) ?> tarea(s) vencida(s). Por favor revísalas y actualiza su estado.
        </div>
        <?php endif; ?>

        <?php if (!empty($tareasProximasVencer)): ?>
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-clock"></i> Tareas Próximas a Vencer</h5>
            Tienes <?= count($tareasProximasVencer) ?> tarea(s) que vencen en los próximos 3 días.
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i>
                    Filtros
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/tareas/mis-tareas/create') ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Crear Tarea Personal
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= site_url('/admin/tareas/mis-tareas') ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select name="estado" id="estado" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <?php foreach ($estados as $valor => $texto): ?>
                                        <option value="<?= $valor ?>" <?= ($filtros['estado'] ?? '') === $valor ? 'selected' : '' ?>>
                                            <?= $texto ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="prioridad">Prioridad</label>
                                <select name="prioridad" id="prioridad" class="form-control">
                                    <option value="">Todas las prioridades</option>
                                    <?php foreach ($prioridades as $valor => $texto): ?>
                                        <option value="<?= $valor ?>" <?= ($filtros['prioridad'] ?? '') === $valor ? 'selected' : '' ?>>
                                            <?= $texto ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo">Tipo de Tareas</label>
                                <select name="tipo" id="tipo" class="form-control">
                                    <option value="todas" <?= ($filtros['tipo'] ?? 'todas') === 'todas' ? 'selected' : '' ?>>Todas mis tareas</option>
                                    <option value="propias" <?= ($filtros['tipo'] ?? '') === 'propias' ? 'selected' : '' ?>>Solo mis tareas</option>
                                    <option value="asignadas" <?= ($filtros['tipo'] ?? '') === 'asignadas' ? 'selected' : '' ?>>Asignadas a mí</option>
                                    <option value="que_asigne" <?= ($filtros['tipo'] ?? '') === 'que_asigne' ? 'selected' : '' ?>>Que asigné a otros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="periodo">Período</label>
                                <select name="periodo" id="periodo" class="form-control">
                                    <option value="este_mes" <?= ($filtros['periodo'] ?? 'este_mes') === 'este_mes' ? 'selected' : '' ?>>Este mes</option>
                                    <option value="esta_semana" <?= ($filtros['periodo'] ?? '') === 'esta_semana' ? 'selected' : '' ?>>Esta semana</option>
                                    <option value="hoy" <?= ($filtros['periodo'] ?? '') === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                                    <option value="" <?= ($filtros['periodo'] ?? '') === '' ? 'selected' : '' ?>>Todas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="<?= site_url('/admin/tareas/mis-tareas') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mis Tareas Personales -->
        <?php if (($filtros['tipo'] ?? 'todas') === 'todas' || ($filtros['tipo'] ?? '') === 'propias'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i>
                    Mis Tareas Personales (<?= count($tareasPersonales) ?>)
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">Tareas que creé para mí</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Tabla de tareas personales -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="misTareasTable_propias">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Progreso</th>
                                <th>Vencimiento</th>
                                <th>Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tareasPersonales)): ?>
                                <?php foreach ($tareasPersonales as $tarea): ?>
                                <tr data-tarea-id="<?= $tarea->id ?>">
                                    <td><?= $tarea->id ?></td>
                                    <td>
                                        <a href="<?= site_url('/admin/tareas/mis-tareas/show/' . $tarea->id) ?>">
                                            <?= esc($tarea->titulo) ?>
                                        </a>
                                        <?php if (!empty($tarea->descripcion)): ?>
                                            <br><small class="text-muted"><?= esc(substr($tarea->descripcion, 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorEstado() ?>">
                                            <?= $tarea->getEstadoTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorPrioridad() ?>">
                                            <?= $tarea->getPrioridadTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Barra de progreso interactiva directa -->
                                        <div class="progress-container-interactive" data-tarea-id="<?= $tarea->id ?>">
                                            <div class="progress progress-clickable" style="height: 25px; cursor: pointer;" 
                                                 title="Clic o arrastra para cambiar progreso">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                     id="progress-bar-<?= $tarea->id ?>"
                                                     role="progressbar" 
                                                     style="width: <?= $tarea->progreso ?>%"
                                                     aria-valuenow="<?= $tarea->progreso ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <span class="progress-text font-weight-bold"><?= $tarea->progreso ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tarea->fecha_vencimiento): ?>
                                            <?= date('d/m/Y', strtotime($tarea->fecha_vencimiento)) ?>
                                            <br>
                                            <small class="text-muted"><?= $tarea->getDiasRestantesTexto() ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin fecha límite</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $tarea->created_at->format('d/m/Y') ?>
                                        <br>
                                        <small class="text-muted"><?= $tarea->created_at->format('H:i') ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <!-- Botón Ver detalles -->
                                            <a href="<?= site_url('/admin/tareas/mis-tareas/show/' . $tarea->id) ?>" 
                                               class="btn btn-sm btn-info mb-1" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            
                                            <?php if ($tarea->estado !== 'completada'): ?>
                                                <!-- Botón Editar progreso -->
                                                <button class="btn btn-sm btn-warning btn-actualizar-progreso mb-1" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        data-progreso="<?= $tarea->progreso ?>"
                                                        title="Actualizar progreso">
                                                    <i class="fas fa-tasks"></i> Progreso
                                                </button>
                                                
                                                <?php if ($tarea->estado === 'pendiente'): ?>
                                                    <button class="btn btn-sm btn-success btn-iniciar mb-1" 
                                                            data-id="<?= $tarea->id ?>" 
                                                            title="Iniciar tarea">
                                                        <i class="fas fa-play"></i> Iniciar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($tarea->estado === 'en_proceso'): ?>
                                                    <button class="btn btn-sm btn-primary btn-completar mb-1" 
                                                            data-id="<?= $tarea->id ?>" 
                                                            title="Marcar como completada">
                                                        <i class="fas fa-check"></i> Completar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <!-- Botón Cambiar estado -->
                                                <button class="btn btn-sm btn-secondary btn-cambiar-estado mb-1" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        title="Cambiar estado">
                                                    <i class="fas fa-exchange-alt"></i> Estado
                                                </button>
                                                
                                                <button class="btn btn-sm btn-warning btn-agregar-comentario" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        title="Agregar comentario">
                                                    <i class="fas fa-comment"></i> Comentar
                                                </button>
                                                
                                                <!-- Eliminar tarea personal (solo propietario) -->
                                                <?php if ($tarea->asignado_por == $currentUserId): ?>
                                                    <button class="btn btn-sm btn-danger btn-eliminar mt-1" 
                                                            data-id="<?= $tarea->id ?>" 
                                                            title="Eliminar tarea personal">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <!-- Tarea completada -->
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> Completada
                                                </span>
                                                
                                                <!-- Aún puede agregar comentarios -->
                                                <button class="btn btn-sm btn-warning btn-agregar-comentario mt-1" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        title="Agregar comentario">
                                                    <i class="fas fa-comment"></i> Comentar
                                                </button>
                                                
                                                <!-- Eliminar tarea personal completada (solo propietario) -->
                                                <?php if ($tarea->asignado_por == $currentUserId): ?>
                                                    <button class="btn btn-sm btn-danger btn-eliminar mt-1" 
                                                            data-id="<?= $tarea->id ?>" 
                                                            title="Eliminar tarea personal">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <br>
                                        No tienes tareas personales. <a href="<?= site_url('/admin/tareas/mis-tareas/create') ?>">Crear una nueva</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tareas Asignadas por Otros -->
        <?php if (($filtros['tipo'] ?? 'todas') === 'todas' || ($filtros['tipo'] ?? '') === 'asignadas'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-friends"></i>
                    Tareas Asignadas a Mí (<?= count($tareasAsignadas) ?>)
                </h3>
                <div class="card-tools">
                    <span class="badge badge-warning">Tareas asignadas por otros</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Tabla de tareas asignadas a mí -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="misTareasTable_asignadas">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Asignado por</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Progreso</th>
                                <th>Vencimiento</th>
                                <th>Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tareasAsignadas)): ?>
                                <?php foreach ($tareasAsignadas as $tarea): ?>
                                <tr data-tarea-id="<?= $tarea->id ?>">
                                    <td><?= $tarea->id ?></td>
                                    <td>
                                        <a href="<?= site_url('/admin/tareas/mis-tareas/show/' . $tarea->id) ?>">
                                            <?= esc($tarea->titulo) ?>
                                        </a>
                                        <?php if ($tarea->estaVencida()): ?>
                                            <span class="badge badge-danger ml-1">VENCIDA</span>
                                        <?php endif; ?>
                                        <?php if (!empty($tarea->descripcion)): ?>
                                            <br><small class="text-muted"><?= esc(substr($tarea->descripcion, 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-primary">
                                            <i class="fas fa-user"></i>
                                            <?= esc($tarea->asignado_por_nombre ?? 'Usuario no encontrado') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorEstado() ?>">
                                            <?= $tarea->getEstadoTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorPrioridad() ?>">
                                            <?= $tarea->getPrioridadTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Barra de progreso interactiva -->
                                        <div class="progress-container-interactive" data-tarea-id="<?= $tarea->id ?>">
                                            <div class="progress progress-clickable" style="height: 25px; cursor: pointer;" 
                                                 title="Clic o arrastra para cambiar progreso">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                     id="progress-bar-<?= $tarea->id ?>"
                                                     role="progressbar" 
                                                     style="width: <?= $tarea->progreso ?>%"
                                                     aria-valuenow="<?= $tarea->progreso ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <span class="progress-text font-weight-bold"><?= $tarea->progreso ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tarea->fecha_vencimiento): ?>
                                            <?= date('d/m/Y', strtotime($tarea->fecha_vencimiento)) ?>
                                            <br>
                                            <small class="text-muted"><?= $tarea->getDiasRestantesTexto() ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin fecha límite</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $tarea->created_at->format('d/m/Y') ?>
                                        <br>
                                        <small class="text-muted"><?= $tarea->created_at->format('H:i') ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <!-- Ver detalles -->
                                            <a href="<?= site_url('/admin/tareas/mis-tareas/show/' . $tarea->id) ?>" 
                                               class="btn btn-sm btn-info mb-1" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            
                                            <?php if ($tarea->estado !== 'completada'): ?>
                                                <!-- Acciones según el estado -->
                                                <?php if ($tarea->estado === 'pendiente'): ?>
                                                    <button class="btn btn-sm btn-success btn-iniciar-tarea mb-1" 
                                                            data-id="<?= $tarea->id ?>" 
                                                            title="Iniciar tarea">
                                                        <i class="fas fa-play"></i> Iniciar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($tarea->estado === 'en_proceso'): ?>
                                                    <button class="btn btn-sm btn-primary btn-completar-tarea mb-1" 
                                                            data-id="<?= $tarea->id ?>" 
                                                            title="Marcar como completada">
                                                        <i class="fas fa-check"></i> Completar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <!-- Agregar comentario -->
                                                <button class="btn btn-sm btn-warning btn-agregar-comentario" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        title="Agregar comentario">
                                                    <i class="fas fa-comment"></i> Comentar
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> Completada
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <br>
                                        No tienes tareas asignadas por otros usuarios.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tareas que Yo Asigné a Otros -->
        <?php if (($filtros['tipo'] ?? 'todas') === 'todas' || ($filtros['tipo'] ?? '') === 'que_asigne'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-cog"></i>
                    Tareas que Asigné a Otros (<?= count($tareasQueAsigne) ?>)
                </h3>
                <div class="card-tools">
                    <span class="badge badge-secondary">Tareas que delegué</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Tabla para tareas que asigné a otros (solo lectura y cancelar) -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="misTareasTable_que_asigne">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Asignado a</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Progreso</th>
                                <th>Vencimiento</th>
                                <th>Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tareasQueAsigne)): ?>
                                <?php foreach ($tareasQueAsigne as $tarea): ?>
                                <tr data-tarea-id="<?= $tarea->id ?>">
                                    <td><?= $tarea->id ?></td>
                                    <td>
                                        <a href="<?= site_url('/admin/tareas/show/' . $tarea->id) ?>">
                                            <?= esc($tarea->titulo) ?>
                                        </a>
                                        <?php if ($tarea->estaVencida()): ?>
                                            <span class="badge badge-danger ml-1">VENCIDA</span>
                                        <?php endif; ?>
                                        <?php if (!empty($tarea->descripcion)): ?>
                                            <br><small class="text-muted"><?= esc(substr($tarea->descripcion, 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-primary">
                                            <i class="fas fa-user"></i>
                                            <?= esc($tarea->asignado_a_nombre ?? 'Usuario no encontrado') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorEstado() ?>">
                                            <?= $tarea->getEstadoTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $tarea->getColorPrioridad() ?>">
                                            <?= $tarea->getPrioridadTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Barra de progreso solo lectura para tareas delegadas -->
                                        <div class="progress-container-readonly" data-tarea-id="<?= $tarea->id ?>">
                                            <div class="progress" style="height: 25px;" 
                                                 title="Progreso: <?= $tarea->progreso ?>% (Solo lectura)">
                                                <div class="progress-bar progress-bar-striped" 
                                                     id="progress-bar-delegadas-<?= $tarea->id ?>"
                                                     role="progressbar" 
                                                     style="width: <?= $tarea->progreso ?>%"
                                                     aria-valuenow="<?= $tarea->progreso ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <span class="progress-text font-weight-bold"><?= $tarea->progreso ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tarea->fecha_vencimiento): ?>
                                            <?= date('d/m/Y', strtotime($tarea->fecha_vencimiento)) ?>
                                            <br>
                                            <small class="text-muted"><?= $tarea->getDiasRestantesTexto() ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin fecha límite</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $tarea->created_at->format('d/m/Y') ?>
                                        <br>
                                        <small class="text-muted"><?= $tarea->created_at->format('H:i') ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <!-- Ver detalles (vista admin completa) -->
                                            <a href="<?= site_url('/admin/tareas/show/' . $tarea->id) ?>" 
                                               class="btn btn-sm btn-info mb-1" 
                                               title="Ver detalles completos">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            
                                            <!-- Editar tarea (vista admin) -->
                                            <a href="<?= site_url('/admin/tareas/edit/' . $tarea->id) ?>" 
                                               class="btn btn-sm btn-warning mb-1" 
                                               title="Editar tarea">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            
                                            <?php if ($tarea->estado !== 'completada' && $tarea->estado !== 'cancelada'): ?>
                                                <!-- Cancelar tarea asignada -->
                                                <button class="btn btn-sm btn-warning btn-cancelar-delegada mb-1" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        title="Cancelar tarea asignada">
                                                    <i class="fas fa-times"></i> Cancelar
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Eliminar tarea (solo propietario) -->
                                            <?php if ($tarea->asignado_por == $currentUserId): ?>
                                                <button class="btn btn-sm btn-danger btn-eliminar mb-1" 
                                                        data-id="<?= $tarea->id ?>" 
                                                        title="Eliminar tarea (propietario)">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Agregar comentario administrativo -->
                                            <button class="btn btn-sm btn-secondary btn-comentario-admin" 
                                                    data-id="<?= $tarea->id ?>" 
                                                    title="Agregar comentario administrativo">
                                                <i class="fas fa-comment-dots"></i> Comentar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                                        <br>
                                        No has asignado tareas a otros usuarios. <a href="<?= site_url('/admin/tareas/create') ?>">Asignar nueva tarea</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal para actualizar progreso -->
<div class="modal fade" id="progresoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Progreso</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formProgreso">
                    <input type="hidden" id="tarea_id_progreso" name="tarea_id">
                    
                    <div class="form-group">
                        <label for="progreso">Progreso (%)</label>
                        <input type="range" 
                               class="form-control-range" 
                               id="progreso" 
                               name="progreso" 
                               min="0" 
                               max="100" 
                               step="5">
                        <div class="text-center">
                            <span id="progreso-value" class="badge badge-primary">0%</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comentario_progreso">Comentario sobre el avance</label>
                        <textarea class="form-control" 
                                  id="comentario_progreso" 
                                  name="comentario" 
                                  rows="3" 
                                  placeholder="Describe el avance realizado..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnActualizarProgreso">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para completar tarea -->
<div class="modal fade" id="completarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Completar Tarea</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCompletar">
                    <input type="hidden" id="tarea_id_completar" name="tarea_id">
                    
                    <div class="form-group">
                        <label for="comentario_completar">Comentario final</label>
                        <textarea class="form-control" 
                                  id="comentario_completar" 
                                  name="comentario" 
                                  rows="3" 
                                  placeholder="Describe cómo se completó la tarea..." 
                                  required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnCompletar">Marcar como Completada</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="estadoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado de Tarea</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEstado">
                    <input type="hidden" id="tarea_id_estado" name="tarea_id">
                    
                    <div class="form-group">
                        <label for="nuevo_estado">Nuevo Estado</label>
                        <select class="form-control" id="nuevo_estado" name="estado" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completada">Completada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="comentario_estado">Comentario (opcional)</label>
                        <textarea class="form-control" id="comentario_estado" name="comentario" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEstado">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar comentario -->
<div class="modal fade" id="comentarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Comentario</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formComentario">
                    <input type="hidden" id="tarea_id_comentario" name="tarea_id">
                    
                    <div class="form-group">
                        <label for="comentario_texto">Comentario</label>
                        <textarea class="form-control" 
                                  id="comentario_texto" 
                                  name="comentario" 
                                  rows="4" 
                                  placeholder="Escribe tu comentario..." 
                                  required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAgregarComentario">Agregar Comentario</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cancelar tarea delegada -->
<div class="modal fade" id="cancelarDelegadaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Tarea Asignada</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCancelarDelegada">
                    <input type="hidden" id="tarea_id_cancelar_delegada" name="tarea_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>¿Estás seguro?</strong><br>
                        Esta acción cancelará la tarea asignada y notificará al usuario asignado.
                    </div>
                    
                    <div class="form-group">
                        <label for="comentario_cancelar_delegada">Motivo de cancelación</label>
                        <textarea class="form-control" 
                                  id="comentario_cancelar_delegada" 
                                  name="comentario" 
                                  rows="3" 
                                  placeholder="Explica el motivo de la cancelación..." 
                                  required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Mantener Tarea</button>
                <button type="button" class="btn btn-danger" id="btnCancelarDelegada">Cancelar Tarea</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para comentario administrativo -->
<div class="modal fade" id="comentarioAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comentario Administrativo</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formComentarioAdmin">
                    <input type="hidden" id="tarea_id_comentario_admin" name="tarea_id">
                    
                    <div class="form-group">
                        <label for="comentario_admin_texto">Comentario Administrativo</label>
                        <textarea class="form-control" 
                                  id="comentario_admin_texto" 
                                  name="comentario" 
                                  rows="4" 
                                  placeholder="Agrega comentarios, instrucciones adicionales o retroalimentación..." 
                                  required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnComentarioAdmin">Agregar Comentario</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable solo si hay datos
    var tableRows = $('#tareasTable tbody tr').length;
    var hasData = tableRows > 0 && !$('#tareasTable tbody tr:first td[colspan]').length;
    
    if (hasData) {
                // Configuración global de DataTables aplicada desde datatables-config.js

        $('#tareasTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 25,
            "columnDefs": [
                { "orderable": false, "targets": [7] } // Columna de acciones no ordenable
            ]
        });
    } else {
        // Si no hay datos, solo aplicar estilos básicos sin DataTables
    }

    // Actualizar visualización del progreso
    $('#progreso').on('input', function() {
        $('#progreso-value').text($(this).val() + '%');
    });

    // Manejar actualización de progreso
    $('.btn-actualizar-progreso').on('click', function() {
        const tareaId = $(this).data('id');
        const progresoActual = $(this).data('progreso');
        
        $('#tarea_id_progreso').val(tareaId);
        $('#progreso').val(progresoActual);
        $('#progreso-value').text(progresoActual + '%');
        $('#progresoModal').modal('show');
    });

    // Confirmar actualización de progreso
    $('#btnActualizarProgreso').on('click', function() {
        const formData = $('#formProgreso').serialize();
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/actualizar-progreso') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#progresoModal').modal('hide');
    });

    // Manejar actualizar progreso
    $('.btn-actualizar-progreso').on('click', function() {
        const tareaId = $(this).data('id');
        const progresoActual = $(this).data('progreso');
        
        $('#tarea_id_progreso').val(tareaId);
        $('#progreso').val(progresoActual);
        $('#progreso-value').text(progresoActual + '%');
        $('#progresoModal').modal('show');
    });

    // Manejar iniciar tarea
    $('.btn-iniciar').on('click', function() {
        const tareaId = $(this).data('id');
        
        Swal.fire({
            title: '¿Iniciar esta tarea?',
            text: "Se marcará como 'En Proceso'",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, iniciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('/admin/tareas/mis-tareas/iniciar') ?>',
                    type: 'POST',
                    data: {
                        tarea_id: tareaId,
                        comentario: 'Tarea iniciada por el usuario',
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('¡Iniciada!', response.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error de conexión', 'error');
                    }
                });
            }
        });
    });

    // Manejar completar tarea
    $('.btn-completar').on('click', function() {
        const tareaId = $(this).data('id');
        
        $('#tarea_id_completar').val(tareaId);
        $('#completarModal').modal('show');
    });

    // Confirmar completar tarea
    $('#btnCompletar').on('click', function() {
        const formData = $('#formCompletar').serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>';
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/completar') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Completada!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#completarModal').modal('hide');
    });

    // Manejar cambiar estado
    $('.btn-cambiar-estado').on('click', function() {
        const tareaId = $(this).data('id');
        
        $('#tarea_id_estado').val(tareaId);
        $('#estadoModal').modal('show');
    });

    // Confirmar cambio de estado
    $('#btnConfirmarEstado').on('click', function() {
        const formData = $('#formEstado').serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>';
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/cambiar-estado') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Estado actualizado!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#estadoModal').modal('hide');
    });

    // Manejar agregar comentario
    $('.btn-agregar-comentario').on('click', function() {
        const tareaId = $(this).data('id');
        
        $('#tarea_id_comentario').val(tareaId);
        $('#comentario_texto').val('');
        $('#comentarioModal').modal('show');
    });

    // Confirmar agregar comentario
    $('#btnAgregarComentario').on('click', function() {
        const formData = $('#formComentario').serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>';
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/mis-tareas/agregar-comentario') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Comentario agregado!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#comentarioModal').modal('hide');
    });

    // ===== FUNCIONALIDAD PARA TAREAS DELEGADAS (TERCERA BANDEJA) =====

    // Manejar cancelar tarea delegada
    $('.btn-cancelar-delegada').on('click', function() {
        const tareaId = $(this).data('id');
        
        $('#tarea_id_cancelar_delegada').val(tareaId);
        $('#comentario_cancelar_delegada').val('');
        $('#cancelarDelegadaModal').modal('show');
    });

    // Confirmar cancelar tarea delegada
    $('#btnCancelarDelegada').on('click', function() {
        const formData = $('#formCancelarDelegada').serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>&estado=cancelada';
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/cambiar-estado') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Tarea cancelada!', 'La tarea ha sido cancelada y el usuario ha sido notificado.', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#cancelarDelegadaModal').modal('hide');
    });

    // Manejar comentario administrativo
    $('.btn-comentario-admin').on('click', function() {
        const tareaId = $(this).data('id');
        
        $('#tarea_id_comentario_admin').val(tareaId);
        $('#comentario_admin_texto').val('');
        $('#comentarioAdminModal').modal('show');
    });

    // Confirmar comentario administrativo
    $('#btnComentarioAdmin').on('click', function() {
        const tareaId = $('#tarea_id_comentario_admin').val();
        const comentario = $('#comentario_admin_texto').val();
        
        $.ajax({
            url: '<?= site_url('/admin/tareas/cambiar-estado') ?>',
            type: 'POST',
            data: {
                tarea_id: tareaId,
                estado: '', // No cambiar estado, solo agregar comentario
                comentario: '[ADMIN] ' + comentario,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Comentario agregado!', 'El comentario administrativo ha sido registrado.', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
        
        $('#comentarioAdminModal').modal('hide');
    });

    // Inicializar DataTables para la tercera tabla
    var tablaQueAsigne = $('#misTareasTable_que_asigne');
    var filasQueAsigne = tablaQueAsigne.find('tbody tr');
    
    if (filasQueAsigne.length > 0 && !filasQueAsigne.first().find('td[colspan]').length) {
        try {
            tablaQueAsigne.DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "columnDefs": [
                    { "orderable": false, "targets": [8] }, // Columna de acciones
                    { "width": "200px", "targets": [5] }   // Columna de progreso
                ],
                "responsive": true,
                "destroy": true
            });
        } catch (error) {
            console.error('❌ Error inicializando DataTable para tareas delegadas:', error);
        }
    }

    // Inicializar colores de progreso para tareas delegadas
    $('.progress-container-readonly').each(function() {
        const tareaId = $(this).data('tarea-id');
        const progreso = parseInt($(`#progress-bar-delegadas-${tareaId}`).attr('aria-valuenow'));
        
        const progressBar = $(`#progress-bar-delegadas-${tareaId}`);
        
        // Aplicar colores según progreso (solo lectura)
        progressBar.removeClass('bg-danger bg-warning bg-info bg-success');
        if (progreso === 0) {
            progressBar.addClass('bg-danger');
        } else if (progreso === 25) {
            progressBar.addClass('bg-warning');
        } else if (progreso === 50) {
            progressBar.addClass('bg-info');
        } else if (progreso === 75) {
            progressBar.addClass('bg-warning');
        } else if (progreso === 100) {
            progressBar.addClass('bg-success');
        }
    });

    // Manejar eliminación de tareas (usando delegación de eventos)
    $(document).on('click', '.btn-eliminar', function() {
        const tareaId = $(this).data('id');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = '<?= site_url('/admin/tareas/delete/') ?>' + tareaId;
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        <?= csrf_token() ?>: $('meta[name="<?= csrf_token() ?>"]').attr('content') || '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('¡Eliminado!', response.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error de conexión', 'error');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>