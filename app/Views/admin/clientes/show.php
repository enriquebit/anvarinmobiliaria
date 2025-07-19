<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/clientes') ?>">Clientes</a></li>
<li class="breadcrumb-item active">Ver Cliente</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">

        <!-- INFORMACIÓN SUPERIOR (igual que edit.php) -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Información del Cliente:</strong> <?= $cliente->getNombreCompleto() ?>
                    <br><small>Cliente desde: <?= $cliente->created_at ? $cliente->created_at->humanize() : 'N/A' ?></small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h6 class="text-primary">Estado del Cliente</h6>
                        <div class="progress mb-2">
                            <?php $completitud = (!empty($cliente->nombres) && !empty($cliente->email) && !empty($cliente->telefono)) ? 75 : 35; ?>
                            <div class="progress-bar <?= $completitud >= 75 ? 'bg-success' : ($completitud >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                 role="progressbar"
                                 style="width: <?= $completitud ?>%"></div>
                        </div>
                        <small><strong><?= $completitud ?>%</strong> completado</small>
                        <br>
                        <span class="badge badge-<?= $cliente->isActivo() ? 'success' : 'warning' ?> mt-1">
                            <?= $cliente->isActivo() ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD PRINCIPAL (mismo diseño que edit.php) -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="card-title mb-0">
                    <i class="fas fa-user mr-2"></i>
                    Ver Cliente: <?= $cliente->getNombreCompleto() ?>
                </h4>
                <div class="card-tools">
                    <a href="<?= site_url("/admin/clientes/edit/{$cliente->id}") ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="card-body">

                <!-- PESTAÑAS (mismo diseño que edit.php) -->
                <ul class="nav nav-tabs" id="show-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="basicos-tab" data-toggle="tab" href="#basicos" role="tab">
                            <i class="fas fa-user mr-1"></i>
                            Datos Básicos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="direccion-tab" data-toggle="tab" href="#direccion" role="tab">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dirección
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="laboral-tab" data-toggle="tab" href="#laboral" role="tab">
                            <i class="fas fa-briefcase mr-1"></i>
                            Información Laboral
                        </a>
                    </li>
                    <?php if ($cliente->estado_civil === 'casado'): ?>
                    <li class="nav-item">
                        <a class="nav-link" id="conyuge-tab" data-toggle="tab" href="#conyuge" role="tab">
                            <i class="fas fa-heart mr-1"></i>
                            Cónyuge
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" id="referencias-tab" data-toggle="tab" href="#referencias" role="tab">
                            <i class="fas fa-users mr-1"></i>
                            Referencias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="documentos-tab" data-toggle="tab" href="#documentos" role="tab">
                            <i class="fas fa-file-alt mr-1"></i>
                            Documentos
                            <span class="badge badge-secondary ml-1">0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="show-tabs-content">

                    <!-- TAB: DATOS BÁSICOS -->
                    <div class="tab-pane fade show active" id="basicos" role="tabpanel">
                        <div class="row">
                            <!-- INFORMACIÓN PERSONAL -->
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-id-card text-primary mr-2"></i>
                                            Información Personal
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><strong>Nombre Completo</strong></label>
                                            <p class="form-control-plaintext"><?= $cliente->getNombreCompleto() ?></p>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Género</strong></label>
                                                    <p class="form-control-plaintext">
                                                        <i class="fas fa-<?= $cliente->genero === 'M' ? 'mars text-primary' : 'venus text-danger' ?> mr-1"></i>
                                                        <?= $cliente->genero === 'M' ? 'Masculino' : 'Femenino' ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Estado Civil</strong></label>
                                                    <p class="form-control-plaintext">
                                                        <span class="badge badge-secondary"><?= $cliente->estado_civil ?? 'No especificado' ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Fecha de Nacimiento</strong></label>
                                                    <p class="form-control-plaintext">
                                                        <?php if ($cliente->fecha_nacimiento): ?>
                                                            <?= date('d/m/Y', strtotime($cliente->fecha_nacimiento)) ?>
                                                            <small class="text-muted d-block">(<?= date_diff(date_create($cliente->fecha_nacimiento), date_create('today'))->y ?> años)</small>
                                                        <?php else: ?>
                                                            <span class="text-muted">No especificada</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Nacionalidad</strong></label>
                                                    <p class="form-control-plaintext"><?= ucfirst($cliente->nacionalidad ?? 'No especificada') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Lugar de Nacimiento</strong></label>
                                            <p class="form-control-plaintext"><?= $cliente->lugar_nacimiento ?? 'No especificado' ?></p>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Profesión</strong></label>
                                            <p class="form-control-plaintext"><?= $cliente->profesion ?? 'No especificada' ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DATOS DE CONTACTO -->
                            <div class="col-md-6">
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-phone text-success mr-2"></i>
                                            Contacto e Identificación
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label><strong>Email</strong></label>
                                            <p class="form-control-plaintext">
                                                <a href="mailto:<?= $cliente->email ?>" class="text-decoration-none">
                                                    <i class="fas fa-envelope mr-1"></i> <?= $cliente->email ?>
                                                </a>
                                            </p>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Teléfono</strong></label>
                                            <p class="form-control-plaintext">
                                                <a href="tel:<?= $cliente->telefono ?>" class="text-decoration-none">
                                                    <i class="fas fa-phone mr-1"></i> <?= $cliente->telefono ?>
                                                </a>
                                            </p>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>RFC</strong></label>
                                            <p class="form-control-plaintext">
                                                <?php if (!empty($cliente->rfc)): ?>
                                                    <?= strtoupper($cliente->rfc) ?>
                                                    <!-- <span class="badge badge-success ml-2">
                                                        <i class="fas fa-check-circle"></i> Registrado
                                                    </span> -->
                                                <?php else: ?>
                                                    <span class="text-muted">No proporcionado</span>
                                                    <span class="badge badge-warning ml-2">Pendiente</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>CURP</strong></label>
                                            <p class="form-control-plaintext">
                                                <?php if (!empty($cliente->curp)): ?>
                                                    <?= strtoupper($cliente->curp) ?>
                                                    <!-- <span class="badge badge-success ml-2">
                                                        <i class="fas fa-check-circle"></i> Registrado
                                                    </span> -->
                                                <?php else: ?>
                                                    <span class="text-muted">No proporcionado</span>
                                                    <span class="badge badge-warning ml-2">Pendiente</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Fuente de Información</strong></label>
                                            <p class="form-control-plaintext">
                                                <span class="badge badge-primary"><?= $cliente->fuente_informacion ?? 'No especificada' ?></span>
                                            </p>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Etapa del Proceso</strong></label>
                                            <p class="form-control-plaintext">
                                                <span class="badge badge-info"><?= $cliente->etapa_proceso ?? 'Sin etapa' ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: DIRECCIÓN -->
                    <div class="tab-pane fade" id="direccion" role="tabpanel">
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt text-warning mr-2"></i>
                                    Dirección Principal
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $direccion = $cliente->getDireccion();
                                if (!empty($direccion) && !empty($direccion->domicilio)):
                                ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Domicilio</strong></label>
                                                <p class="form-control-plaintext">
                                                    <?= $direccion->domicilio ?>
                                                    <?php if (!empty($direccion->numero)): ?>
                                                        #<?= $direccion->numero ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Código Postal</strong></label>
                                                <p class="form-control-plaintext"><?= $direccion->codigo_postal ?? 'No especificado' ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Colonia</strong></label>
                                                <p class="form-control-plaintext"><?= $direccion->colonia ?? 'No especificada' ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Ciudad</strong></label>
                                                <p class="form-control-plaintext"><?= $direccion->ciudad ?? 'No especificada' ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Estado</strong></label>
                                                <p class="form-control-plaintext"><?= $direccion->estado ?? 'No especificado' ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Tiempo Radicando</strong></label>
                                                <p class="form-control-plaintext"><?= $direccion->tiempo_radicando ?? 'No especificado' ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                        <h6>Sin dirección registrada</h6>
                                        <p>No se ha proporcionado información de dirección para este cliente.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: INFORMACIÓN LABORAL -->
                    <div class="tab-pane fade" id="laboral" role="tabpanel">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-briefcase text-info mr-2"></i>
                                    Información Laboral
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $laboral = $cliente->getInformacionLaboral();
                                if (!empty($laboral) && !empty($laboral->nombre_empresa)):
                                ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Empresa</strong></label>
                                                <p class="form-control-plaintext"><?= $laboral->nombre_empresa ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Puesto/Cargo</strong></label>
                                                <p class="form-control-plaintext"><?= $laboral->puesto_cargo ?? 'No especificado' ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Antigüedad</strong></label>
                                                <p class="form-control-plaintext"><?= $laboral->antiguedad ?? 'No especificada' ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Teléfono de Trabajo</strong></label>
                                                <p class="form-control-plaintext">
                                                    <?php if (!empty($laboral->telefono_trabajo)): ?>
                                                        <a href="tel:<?= $laboral->telefono_trabajo ?>" class="text-decoration-none">
                                                            <i class="fas fa-phone mr-1"></i> <?= $laboral->telefono_trabajo ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No proporcionado</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($laboral->direccion_trabajo)): ?>
                                    <div class="form-group">
                                        <label><strong>Dirección de Trabajo</strong></label>
                                        <p class="form-control-plaintext"><?= $laboral->direccion_trabajo ?></p>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-briefcase fa-3x mb-3"></i>
                                        <h6>Sin información laboral</h6>
                                        <p>No se ha proporcionado información laboral para este cliente.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: CÓNYUGE -->
                    <?php if ($cliente->estado_civil === 'casado'): ?>
                    <div class="tab-pane fade" id="conyuge" role="tabpanel">
                        <div class="card card-outline card-danger">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-heart text-danger mr-2"></i>
                                    Información del Cónyuge
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $conyuge = $cliente->getInformacionConyuge();
                                if (!empty($conyuge) && !empty($conyuge->nombre_completo)):
                                ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Nombre Completo</strong></label>
                                                <p class="form-control-plaintext"><?= $conyuge->nombre_completo ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Profesión</strong></label>
                                                <p class="form-control-plaintext"><?= $conyuge->profesion ?? 'No especificada' ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Email</strong></label>
                                                <p class="form-control-plaintext">
                                                    <?php if (!empty($conyuge->email)): ?>
                                                        <a href="mailto:<?= $conyuge->email ?>" class="text-decoration-none">
                                                            <i class="fas fa-envelope mr-1"></i> <?= $conyuge->email ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No proporcionado</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Teléfono</strong></label>
                                                <p class="form-control-plaintext">
                                                    <?php if (!empty($conyuge->telefono)): ?>
                                                        <a href="tel:<?= $conyuge->telefono ?>" class="text-decoration-none">
                                                            <i class="fas fa-phone mr-1"></i> <?= $conyuge->telefono ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No proporcionado</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-heart fa-3x mb-3"></i>
                                        <h6>Sin información del cónyuge</h6>
                                        <p>No se ha proporcionado información del cónyuge para este cliente.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- TAB: REFERENCIAS -->
                    <div class="tab-pane fade" id="referencias" role="tabpanel">
                        <?php
                        $referencias = $cliente->getReferencias();
                        if (!empty($referencias)):
                        ?>
                            <div class="row">
                                <?php
                                $contador = 1;
                                foreach ($referencias as $referencia):
                                    if ($contador > 2) break;
                                ?>
                                    <div class="col-md-6">
                                        <div class="card card-outline card-success">
                                            <div class="card-header">
                                                <h5 class="mb-0">
                                                    <i class="fas fa-user-friends text-success mr-2"></i>
                                                    Referencia <?= $contador ?>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label><strong>Nombre Completo</strong></label>
                                                    <p class="form-control-plaintext"><?= $referencia->nombre_completo ?? 'No proporcionado' ?></p>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label><strong>Parentesco</strong></label>
                                                            <p class="form-control-plaintext"><?= $referencia->parentesco ?? 'No especificado' ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <!-- <div class="form-group">
                                                            <label><strong>Género</strong></label>
                                                            <p class="form-control-plaintext">
                                                                <?= !empty($referencia->genero) ?
                                                                    ($referencia->genero === 'M' ? 'Masculino' : 'Femenino') :
                                                                    'No especificado' ?>
                                                            </p>
                                                        </div> -->
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Teléfono</strong></label>
                                                    <p class="form-control-plaintext">
                                                        <?php if (!empty($referencia->telefono)): ?>
                                                            <a href="tel:<?= $referencia->telefono ?>" class="text-decoration-none">
                                                                <i class="fas fa-phone mr-1"></i> <?= $referencia->telefono ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No proporcionado</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                $contador++;
                                endforeach;
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="card card-outline card-secondary">
                                <div class="card-body">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <h6>Sin referencias registradas</h6>
                                        <p>No se han proporcionado referencias personales para este cliente.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB: DOCUMENTOS (FUNCIONALIDAD NO IMPLEMENTADA) -->
                    <div class="tab-pane fade" id="documentos" role="tabpanel">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-alt text-info mr-2"></i>
                                    Documentos del Cliente
                                    <small class="text-muted ml-2">
                                        (Módulo en desarrollo)
                                    </small>
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- MENSAJE TEMPORAL -->
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle mr-2"></i>Módulo de Documentos</h5>
                                    <p>La funcionalidad de gestión de documentos se encuentra en desarrollo.</p>
                                    <p class="mb-0">Próximamente podrás:</p>
                                    <ul class="mb-0">
                                        <li>Subir documentos del cliente (INE, Comprobante de domicilio, etc.)</li>
                                        <li>Ver el progreso de documentación</li>
                                        <li>Descargar documentos subidos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- BOTONES DE ACCIÓN (mismo estilo que edit.php) -->
        <div class="text-center mb-4">
            <a href="<?= site_url("/admin/clientes/edit/{$cliente->id}") ?>" class="btn btn-warning btn-lg">
                <i class="fas fa-edit mr-1"></i> Editar Cliente
            </a>
            <button class="btn btn-<?= $cliente->isActivo() ? 'danger' : 'success' ?> btn-lg btn-cambiar-estado"
                    data-cliente-id="<?= $cliente->id ?>"
                    data-activo="<?= $cliente->isActivo() ? 'false' : 'true' ?>">
                <i class="fas fa-<?= $cliente->isActivo() ? 'user-times' : 'user-check' ?> mr-1"></i>
                <?= $cliente->isActivo() ? 'Desactivar Cliente' : 'Activar Cliente' ?>
            </button>
            <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-default btn-lg">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {

    // Cambiar estado del cliente
    $('.btn-cambiar-estado').on('click', function(e) {
        e.preventDefault();

        const clienteId = $(this).data('cliente-id');
        const nuevoEstado = $(this).data('activo');
        const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';

        if (confirm(`¿Estás seguro de que deseas ${accion} a este cliente?`)) {
            // Implementar llamada AJAX aquí si es necesario
        }
    });
});
</script>
<?= $this->endSection() ?>