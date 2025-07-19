<h5 class="mb-3">
    <i class="fas fa-file-upload text-primary mr-2"></i>
    Gestión de Documentos
    <small class="text-muted ml-2">
        (<?= $estadisticas_documentos['subidos'] ?>/<?= $estadisticas_documentos['total_esenciales'] ?> documentos esenciales - <?= $estadisticas_documentos['porcentaje'] ?>% completado)
    </small>
</h5>

<!-- ESTADÍSTICAS DE DOCUMENTOS -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-info">
            <div class="card-body text-center">
                <h6 class="text-info">Progreso de Documentación</h6>
                <div class="progress mb-2">
                    <div class="progress-bar bg-info" 
                         role="progressbar" 
                         style="width: <?= $estadisticas_documentos['porcentaje'] ?>%">
                    </div>
                </div>
                <small><strong><?= $estadisticas_documentos['porcentaje'] ?>%</strong> completado</small>
            </div>
        </div>
    </div>
</div>

<!-- LISTA DE DOCUMENTOS -->
<div class="row">
    <?php 
    $tiposEsenciales = ['identificacion_oficial', 'comprobante_domicilio', 'rfc', 'curp'];
    $tiposOpcionales = ['carta_domicilio', 'hoja_requerimientos', 'ofac'];
    ?>
    
    <!-- DOCUMENTOS ESENCIALES -->
    <div class="col-md-6">
        <h6 class="text-danger mb-3">
            <i class="fas fa-exclamation-circle mr-1"></i>
            Documentos Esenciales
        </h6>
        
        <?php foreach ($tiposEsenciales as $tipo): ?>
            <?php $documento = $documentos[$tipo] ?? null; ?>
            <div class="card mb-3 border-<?= $documento ? 'success' : 'warning' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1"><?= $tipos_documento[$tipo] ?></h6>
                            <?php if ($documento): ?>
                                <small class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Subido: <?= date('d/m/Y', strtotime($documento['created_at'])) ?>
                                </small>
                                <br>
                                <small class="text-muted"><?= $documento['nombre_archivo'] ?? 'Archivo sin nombre' ?></small>
                            <?php else: ?>
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Pendiente
                                </small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($documento): ?>
                                <a href="<?= site_url('/admin/clientes/documento/' . $documento['id']) ?>" 
                                   class="btn btn-sm btn-outline-primary mr-1" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="eliminarDocumento(<?= $documento['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-<?= $documento ? 'outline-' : '' ?>success ml-1" 
                                    onclick="subirDocumento('<?= $tipo ?>')">
                                <i class="fas fa-upload"></i> <?= $documento ? 'Reemplazar' : 'Subir' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- DOCUMENTOS OPCIONALES -->
    <div class="col-md-6">
        <h6 class="text-info mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            Documentos Opcionales
        </h6>
        
        <?php foreach ($tiposOpcionales as $tipo): ?>
            <?php $documento = $documentos[$tipo] ?? null; ?>
            <div class="card mb-3 border-<?= $documento ? 'success' : 'light' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1"><?= $tipos_documento[$tipo] ?></h6>
                            <?php if ($documento): ?>
                                <small class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Subido: <?= date('d/m/Y', strtotime($documento['created_at'])) ?>
                                </small>
                                <br>
                                <small class="text-muted"><?= $documento['nombre_archivo'] ?? 'Archivo sin nombre' ?></small>
                            <?php else: ?>
                                <small class="text-muted">
                                    <i class="fas fa-minus-circle mr-1"></i>
                                    Opcional
                                </small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($documento): ?>
                                <a href="<?= site_url('/admin/clientes/documento/' . $documento['id']) ?>" 
                                   class="btn btn-sm btn-outline-primary mr-1" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="eliminarDocumento(<?= $documento['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-<?= $documento ? 'outline-' : '' ?>info ml-1" 
                                    onclick="subirDocumento('<?= $tipo ?>')">
                                <i class="fas fa-upload"></i> <?= $documento ? 'Reemplazar' : 'Subir' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- INSTRUCCIONES -->
<div class="alert alert-info mt-4">
    <h6><i class="fas fa-info-circle mr-2"></i>Instrucciones:</h6>
    <ul class="mb-0">
        <li>Los documentos esenciales son <strong>obligatorios</strong> para completar el expediente del cliente</li>
        <li>Formatos permitidos: PDF, JPG, JPEG, PNG, GIF</li>
        <li>Tamaño máximo: 5MB por archivo</li>
        <li>Al subir un nuevo documento del mismo tipo, se reemplazará el anterior automáticamente</li>
        <li>Los archivos se organizan por RFC del cliente: <strong><?= !empty($cliente->rfc) ? $cliente->rfc : 'SIN_RFC_' . $cliente->id ?></strong></li>
    </ul>
</div>