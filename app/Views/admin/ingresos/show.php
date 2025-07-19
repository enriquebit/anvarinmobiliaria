<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>Detalle de Ingreso - <?= esc($ingreso->folio) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/admin/ingresos') ?>">Ingresos</a></li>
<li class="breadcrumb-item active">Detalle</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- Información del Ingreso -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-receipt mr-2"></i>
                    Información del Ingreso
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('/admin/ingresos/recibo/' . $ingreso->id) ?>" 
                       class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-print mr-1"></i>
                        Imprimir Recibo
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="150">Folio:</th>
                                <td>
                                    <span class="badge badge-primary badge-lg"><?= esc($ingreso->folio) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tipo de Ingreso:</th>
                                <td>
                                    <?php
                                    $badges = [
                                        'apartado' => 'badge-info',
                                        'enganche' => 'badge-success', 
                                        'mensualidad' => 'badge-primary',
                                        'abono_enganche' => 'badge-warning',
                                        'otros' => 'badge-secondary'
                                    ];
                                    $badgeClass = $badges[$ingreso->tipo_ingreso] ?? 'badge-secondary';
                                    $label = ucfirst(str_replace('_', ' ', $ingreso->tipo_ingreso));
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Monto:</th>
                                <td><strong class="text-success">$<?= number_format($ingreso->monto, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Método de Pago:</th>
                                <td><?= ucfirst($ingreso->metodo_pago) ?></td>
                            </tr>
                            <tr>
                                <th>Referencia:</th>
                                <td><?= esc($ingreso->referencia ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="150">Fecha de Ingreso:</th>
                                <td><?= date('d/m/Y H:i', strtotime($ingreso->fecha_ingreso)) ?></td>
                            </tr>
                            <tr>
                                <th>Registrado por:</th>
                                <td><?= esc($ingreso->usuario_nombre ?? 'N/A') ?></td>
                            </tr>
                            <?php if (isset($ingreso->created_at) && $ingreso->created_at): ?>
                            <tr>
                                <th>Fecha de Registro:</th>
                                <td><?= date('d/m/Y H:i', strtotime($ingreso->created_at)) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($ingreso->observaciones)): ?>
                            <tr>
                                <th>Observaciones:</th>
                                <td><?= esc($ingreso->observaciones) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php if (!empty($ingreso->descripcion)): ?>
                <hr>
                <h6>Descripción:</h6>
                <p class="text-muted"><?= esc($ingreso->descripcion) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información Relacionada -->
        <?php if (!empty($ingreso->apartado_id) || (!empty($ingreso->venta_id) && isset($ingreso->venta_id))): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-link mr-2"></i>
                    Operación Relacionada
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($ingreso->apartado_id)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-home mr-2"></i>
                        <strong>Apartado:</strong> AP-<?= $ingreso->apartado_id ?>
                        <?php if (!empty($ingreso->fecha_apartado)): ?>
                            - Fecha: <?= date('d/m/Y', strtotime($ingreso->fecha_apartado)) ?>
                        <?php endif; ?>
                        <br>
                        <a href="<?= site_url('/admin/apartados/' . $ingreso->apartado_id) ?>" class="btn btn-sm btn-info mt-2">
                            <i class="fas fa-eye mr-1"></i>
                            Ver Apartado
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($ingreso->venta_id) && isset($ingreso->venta_id)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-handshake mr-2"></i>
                        <strong>Venta:</strong> VT-<?= $ingreso->venta_id ?>
                        <br>
                        <a href="<?= site_url('/admin/ventas/' . $ingreso->venta_id) ?>" class="btn btn-sm btn-success mt-2">
                            <i class="fas fa-eye mr-1"></i>
                            Ver Venta
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Información del Cliente -->
    <div class="col-lg-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-user mr-2"></i>
                    Información del Cliente
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-circle bg-primary text-white mx-auto mb-2" style="width: 60px; height: 60px; line-height: 60px; border-radius: 50%; font-size: 24px;">
                        <?= strtoupper(substr($ingreso->cliente_nombre, 0, 1)) ?>
                    </div>
                    <h5><?= esc($ingreso->cliente_nombre) ?> <?= esc($ingreso->apellido_paterno) ?> <?= esc($ingreso->apellido_materno) ?></h5>
                </div>
                
                <table class="table table-sm table-borderless">
                    <?php if (!empty($ingreso->cliente_email)): ?>
                    <tr>
                        <th><i class="fas fa-envelope mr-2"></i>Email:</th>
                        <td><a href="mailto:<?= $ingreso->cliente_email ?>"><?= esc($ingreso->cliente_email) ?></a></td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php if (!empty($ingreso->cliente_telefono)): ?>
                    <tr>
                        <th><i class="fas fa-phone mr-2"></i>Teléfono:</th>
                        <td><a href="tel:<?= $ingreso->cliente_telefono ?>"><?= esc($ingreso->cliente_telefono) ?></a></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <div class="mt-3">
                    <a href="<?= site_url('/admin/clientes/' . $ingreso->cliente_id) ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-eye mr-1"></i>
                        Ver Expediente Completo
                    </a>
                </div>
            </div>
        </div>

        <!-- Información del Lote (si aplica) -->
        <?php if (!empty($ingreso->lote_clave)): ?>
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">
                    <i class="fas fa-home mr-2"></i>
                    Información del Lote
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>Clave:</th>
                        <td><strong><?= esc($ingreso->lote_clave) ?></strong></td>
                    </tr>
                    <?php if (!empty($ingreso->lote_area)): ?>
                    <tr>
                        <th>Superficie:</th>
                        <td><?= number_format($ingreso->lote_area, 2) ?> m²</td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($ingreso->proyecto_nombre)): ?>
                    <tr>
                        <th>Proyecto:</th>
                        <td><?= esc($ingreso->proyecto_nombre) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>