<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/lotes') ?>">Lotes</a></li>
<li class="breadcrumb-item active">Ver Detalles</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
            <div class="row">
                <!-- Información Principal -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Información del Lote
                            </h3>
                            <div class="card-tools">
                                <?php if (isAdmin()): ?>
                                <a href="<?= base_url('admin/lotes/edit/' . $lote->id) ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <?php endif; ?>
                                <a href="<?= base_url('admin/lotes') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Número:</th>
                                            <td><strong><?= $lote->numero ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Clave:</th>
                                            <td><code><?= $lote->clave ?></code></td>
                                        </tr>
                                        <tr>
                                            <th>Empresa:</th>
                                            <td><?= $lote->getNombreEmpresa() ?></td>
                                        </tr>
                                        <tr>
                                            <th>Proyecto:</th>
                                            <td><?= $lote->getNombreProyecto() ?></td>
                                        </tr>
                                        <tr>
                                            <th>División:</th>
                                            <td><?= $lote->getNombreDivision() ?></td>
                                        </tr>
                                        <tr>
                                            <th>Manzana:</th>
                                            <td><?= $lote->getNombreManzana() ?></td>
                                        </tr>
                                        <tr>
                                            <th>Categoría:</th>
                                            <td><?= $lote->getNombreCategoria() ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tipo:</th>
                                            <td><?= $lote->getNombreTipo() ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Estado:</th>
                                            <td>
                                                <span class="badge badge-<?php
                                                    echo match($lote->estados_lotes_id) {
                                                        1 => 'success',    // Disponible - Verde
                                                        2 => 'warning',    // Apartado - Amarillo
                                                        3 => 'danger',     // Vendido - Rojo
                                                        4 => 'secondary',  // Suspendido - Gris
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?= $lote->getNombreEstado() ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Área Total:</th>
                                            <td><strong><?= number_format($lote->area, 2) ?> m²</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Construcción:</th>
                                            <td><?= number_format($lote->construccion, 2) ?> m²</td>
                                        </tr>
                                        <tr>
                                            <th>Precio por m²:</th>
                                            <td><strong>$<?= number_format($lote->precio_m2, 2) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Precio Total:</th>
                                            <td><h4 class="text-success">$<?= number_format($lote->precio_total, 2) ?></h4></td>
                                        </tr>
                                        <tr>
                                            <th>Activo:</th>
                                            <td>
                                                <span class="badge badge-<?= $lote->activo ? 'success' : 'danger' ?>">
                                                    <?= $lote->activo ? 'Sí' : 'No' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Creado:</th>
                                            <td><?= $lote->created_at->format('d/m/Y H:i') ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <?php if ($lote->descripcion): ?>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                    <h5><i class="fas fa-comment-alt"></i> Descripción</h5>
                                    <p><?= nl2br(esc($lote->descripcion)) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dimensiones -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-ruler-combined"></i> Dimensiones
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-arrows-alt-h"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Frente</span>
                                            <span class="info-box-number"><?= $lote->frente ? number_format($lote->frente, 2) . ' m' : 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-arrows-alt-v"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fondo</span>
                                            <span class="info-box-number"><?= $lote->fondo ? number_format($lote->fondo, 2) . ' m' : 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-ruler"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Lateral Izq.</span>
                                            <span class="info-box-number"><?= $lote->lateral_izquierdo ? number_format($lote->lateral_izquierdo, 2) . ' m' : 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-ruler"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Lateral Der.</span>
                                            <span class="info-box-number"><?= $lote->lateral_derecho ? number_format($lote->lateral_derecho, 2) . ' m' : 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <?php if ($lote->latitud && $lote->longitud): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-map-marker-alt"></i> Ubicación GPS
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Latitud:</strong> <?= $lote->latitud ?></p>
                                    <p><strong>Longitud:</strong> <?= $lote->longitud ?></p>
                                    <a href="https://www.google.com/maps?q=<?= $lote->latitud ?>,<?= $lote->longitud ?>" 
                                       target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Ver en Google Maps
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <div id="mapa" style="height: 200px; background: #f4f4f4; border: 1px solid #ddd; border-radius: 4px;">
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <span class="text-muted">Mapa no disponible</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Panel Lateral -->
                <div class="col-md-4">
                    <!-- Amenidades -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-star"></i> Amenidades
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($amenidades)): ?>
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($amenidades as $amenidad): ?>
                                        <span class="badge badge-info mr-1 mb-1 p-2">
                                            <i class="<?= $amenidad['icono'] ?>"></i> <?= $amenidad['nombre'] ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-star mr-1"></i>
                                    No hay amenidades asignadas
                                    <?php if (isAdmin()): ?>
                                    - <a href="<?= base_url('admin/lotes/edit/' . $lote->id) ?>" class="text-primary">
                                        <i class="fas fa-plus"></i> Agregar
                                    </a>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estados Disponibles -->
                    <?php if (isAdmin()): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exchange-alt"></i> Cambiar Estado
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group-vertical w-100">
                                <?php 
                                $estados = [
                                    1 => ['nombre' => 'Disponible', 'color' => 'success', 'icono' => 'check-circle'],
                                    2 => ['nombre' => 'Apartado', 'color' => 'warning', 'icono' => 'clock'],
                                    3 => ['nombre' => 'Vendido', 'color' => 'danger', 'icono' => 'home'],
                                    4 => ['nombre' => 'Suspendido', 'color' => 'secondary', 'icono' => 'ban']
                                ];
                                ?>
                                <?php foreach ($estados as $estadoId => $estado): ?>
                                    <?php if ($estadoId != $lote->estados_lotes_id): ?>
                                    <button class="btn btn-outline-<?= $estado['color'] ?> btn-sm mb-1 btn-cambiar-estado" 
                                            data-estado="<?= $estadoId ?>">
                                        <i class="fas fa-<?= $estado['icono'] ?>"></i> <?= $estado['nombre'] ?>
                                    </button>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Lotes Similares -->
                    <?php if (!empty($lotes_similares)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search"></i> Lotes Similares
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($lotes_similares as $similar): ?>
                            <div class="card card-outline card-secondary mb-2">
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-1">
                                        <a href="<?= base_url('admin/lotes/show/' . $similar->id) ?>">
                                            <?= $similar->numero ?> - <?= $similar->clave ?>
                                        </a>
                                    </h6>
                                    <p class="card-text small mb-1">
                                        <strong><?= number_format($similar->area, 2) ?> m²</strong> - 
                                        $<?= number_format($similar->precio_total, 2) ?>
                                    </p>
                                    <span class="badge badge-<?php
                                        echo match($similar->estados_lotes_id) {
                                            1 => 'success',    // Disponible - Verde
                                            2 => 'warning',    // Apartado - Amarillo
                                            3 => 'danger',     // Vendido - Rojo
                                            4 => 'secondary',  // Suspendido - Gris
                                            default => 'secondary'
                                        };
                                    ?> badge-sm">
                                        <?= $similar->getNombreEstado() ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Información Adicional -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info"></i> Información Adicional
                            </h3>
                        </div>
                        <div class="card-body">
                            <small>
                                <p><strong>ID:</strong> <?= $lote->id ?></p>
                                <p><strong>Superficie Total:</strong> <?= number_format($lote->getSuperficieTotal(), 2) ?> m²</p>
                                <p><strong>Color Mapa:</strong> 
                                    <span class="badge" style="background-color: <?= $lote->color ?>; color: white;">
                                        <?= $lote->color ?>
                                    </span>
                                </p>
                                <p><strong>Última Actualización:</strong> <?= $lote->updated_at->format('d/m/Y H:i') ?></p>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    
    // Cambiar estado del lote
    $('.btn-cambiar-estado').click(function() {
        const nuevoEstado = $(this).data('estado');
        const estadoNombre = $(this).text().trim();
        
        Swal.fire({
            title: '¿Confirmar cambio de estado?',
            text: `El lote cambiará a estado: ${estadoNombre}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                cambiarEstado(nuevoEstado);
            }
        });
    });

    function cambiarEstado(estado) {
        $.ajax({
            url: '<?= base_url('admin/lotes/cambiar-estado/' . $lote->id) ?>',
            type: 'POST',
            data: { estado: estado },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error de conexión');
            }
        });
    }

    // Inicializar mapa si hay coordenadas
    <?php if ($lote->latitud && $lote->longitud): ?>
    // Aquí se podría implementar un mapa real con Google Maps o Leaflet
    $('#mapa').html('<iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $lote->longitud - 0.001 ?>,<?= $lote->latitud - 0.001 ?>,<?= $lote->longitud + 0.001 ?>,<?= $lote->latitud + 0.001 ?>&marker=<?= $lote->latitud ?>,<?= $lote->longitud ?>"></iframe>');
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>