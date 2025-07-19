<?= $this->extend('layouts/cliente') ?>

<?= $this->section('title') ?>Reportar Pago<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Reportar Pago Realizado<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/dashboard') ?>">Inicio</a></li>
<li class="breadcrumb-item"><a href="<?= site_url('/cliente/pagos') ?>">Pagos</a></li>
<li class="breadcrumb-item active">Reportar Pago</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bell mr-2"></i>
                    Formulario de Reporte de Pago
                </h3>
            </div>
            <div class="card-body">
                <form id="form-reportar-pago" method="post" action="<?= site_url('/cliente/pagos/procesarReporte') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <!-- Step 1: Selección de propiedad y mensualidad -->
                    <div class="step" id="step-1">
                        <h5 class="mb-3">Paso 1: Selecciona la propiedad y mensualidad</h5>
                        
                        <div class="form-group">
                            <label for="venta_id">Propiedad <span class="text-danger">*</span></label>
                            <select class="form-control" id="venta_id" name="venta_id" required>
                                <option value="">-- Selecciona una propiedad --</option>
                                <?php foreach ($propiedades as $propiedad): ?>
                                <option value="<?= $propiedad->id ?>" <?= $mensualidad_preseleccionada && $mensualidad_preseleccionada->venta_id == $propiedad->id ? 'selected' : '' ?>>
                                    <?= esc($propiedad->lote_clave) ?> - <?= esc($propiedad->proyecto_nombre) ?> (<?= esc($propiedad->folio_venta) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tabla_amortizacion_id">Mensualidad a Pagar <span class="text-danger">*</span></label>
                            <select class="form-control" id="tabla_amortizacion_id" name="tabla_amortizacion_id" required>
                                <option value="">-- Primero selecciona una propiedad --</option>
                                <?php if ($mensualidad_preseleccionada): ?>
                                <option value="<?= $mensualidad_preseleccionada->id ?>" selected>
                                    Mensualidad #<?= $mensualidad_preseleccionada->numero_pago ?> - 
                                    Vence: <?= date('d/m/Y', strtotime($mensualidad_preseleccionada->fecha_vencimiento)) ?> - 
                                    $<?= number_format($mensualidad_preseleccionada->monto_total + $mensualidad_preseleccionada->interes_moratorio, 2) ?>
                                </option>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Se mostrarán solo las mensualidades pendientes de pago</small>
                        </div>
                        
                        <div class="alert alert-info" id="info-mensualidad" style="display: none;">
                            <!-- Se llenará con AJAX -->
                        </div>
                    </div>
                    
                    <!-- Step 2: Información del pago -->
                    <div class="step" id="step-2" style="display: none;">
                        <h5 class="mb-3">Paso 2: Información del pago realizado</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_pago">Fecha del Pago <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                           max="<?= date('Y-m-d') ?>" required>
                                    <small class="form-text text-muted">Fecha en que realizaste el pago</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hora_pago">Hora del Pago</label>
                                    <input type="time" class="form-control" id="hora_pago" name="hora_pago">
                                    <small class="form-text text-muted">Opcional: Hora aproximada del pago</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="forma_pago">Forma de Pago <span class="text-danger">*</span></label>
                                    <select class="form-control" id="forma_pago" name="forma_pago" required>
                                        <option value="">-- Selecciona --</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia Bancaria</option>
                                        <option value="deposito">Depósito Bancario</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="tarjeta">Tarjeta (Terminal física)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monto_reportado">Monto Pagado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control" id="monto_reportado" name="monto_reportado" 
                                               step="0.01" min="0.01" required>
                                    </div>
                                    <small class="form-text text-muted">Monto exacto que pagaste</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" id="grupo-banco" style="display: none;">
                            <label for="banco_origen">Banco de Origen</label>
                            <input type="text" class="form-control" id="banco_origen" name="banco_origen" 
                                   placeholder="Ej: Bancomer, Banorte, etc.">
                        </div>
                        
                        <div class="form-group">
                            <label for="referencia_pago">Referencia/Folio de la Operación</label>
                            <input type="text" class="form-control" id="referencia_pago" name="referencia_pago" 
                                   placeholder="Número de referencia, folio o autorización">
                            <small class="form-text text-muted">Si tienes algún número de referencia del pago</small>
                        </div>
                    </div>
                    
                    <!-- Step 3: Comprobante -->
                    <div class="step" id="step-3" style="display: none;">
                        <h5 class="mb-3">Paso 3: Adjuntar comprobante (Opcional pero recomendado)</h5>
                        
                        <div class="form-group">
                            <label for="comprobante">Comprobante de Pago</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="comprobante" name="comprobante" 
                                       accept="image/*,.pdf">
                                <label class="custom-file-label" for="comprobante">Seleccionar archivo...</label>
                            </div>
                            <small class="form-text text-muted">
                                Formatos aceptados: JPG, PNG, PDF (máximo 5MB)
                            </small>
                        </div>
                        
                        <div id="preview-comprobante" class="text-center mt-3" style="display: none;">
                            <img id="imagen-preview" src="" alt="Vista previa" style="max-width: 100%; max-height: 300px;">
                        </div>
                        
                        <div class="form-group">
                            <label for="observaciones">Observaciones adicionales</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                      placeholder="Cualquier información adicional sobre el pago..."></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle mr-2"></i>Importante</h6>
                            <ul class="mb-0">
                                <li>Adjuntar el comprobante agiliza la verificación del pago</li>
                                <li>La información debe coincidir con tu comprobante</li>
                                <li>Los pagos se verifican en horario hábil (Lun-Vie 9am-6pm)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Navegación -->
                    <div class="row mt-4">
                        <div class="col-6">
                            <button type="button" class="btn btn-secondary" id="btn-anterior" style="display: none;">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </button>
                        </div>
                        <div class="col-6 text-right">
                            <button type="button" class="btn btn-primary" id="btn-siguiente">
                                Siguiente <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="btn-enviar" style="display: none;">
                                <i class="fas fa-paper-plane"></i> Enviar Reporte
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel lateral de ayuda -->
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Guía de Reporte
                </h3>
            </div>
            <div class="card-body">
                <h6>¿Cuándo reportar un pago?</h6>
                <ul class="pl-3">
                    <li>Realizaste un depósito o transferencia bancaria</li>
                    <li>Pagaste en efectivo en oficina</li>
                    <li>Hiciste un pago pero no se refleja en tu cuenta</li>
                </ul>
                
                <h6 class="mt-3">Información necesaria:</h6>
                <ul class="pl-3">
                    <li>Fecha exacta del pago</li>
                    <li>Monto pagado</li>
                    <li>Forma de pago utilizada</li>
                    <li>Comprobante (foto o PDF)</li>
                </ul>
                
                <div class="alert alert-success mt-3">
                    <h6><i class="fas fa-clock mr-1"></i>Tiempos de verificación:</h6>
                    <ul class="mb-0 pl-3">
                        <li><strong>En horario hábil:</strong> 2-4 horas</li>
                        <li><strong>Fuera de horario:</strong> Siguiente día hábil</li>
                        <li><strong>Fin de semana:</strong> Lunes por la mañana</li>
                    </ul>
                </div>
                
                <div class="text-center mt-3">
                    <p class="text-muted mb-1">¿Necesitas ayuda?</p>
                    <a href="https://wa.me/<?= $config_empresa->whatsapp ?? '' ?>?text=Hola,%20necesito%20ayuda%20para%20reportar%20un%20pago" 
                       class="btn btn-success btn-sm" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Historial de reportes -->
        <?php if (!empty($reportes_anteriores)): ?>
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Reportes Anteriores
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($reportes_anteriores, 0, 5) as $reporte): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>$<?= number_format($reporte->monto_reportado, 0) ?></strong><br>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($reporte->fecha_reporte)) ?>
                                </small>
                            </div>
                            <div>
                                <?php if ($reporte->estado === 'verificado'): ?>
                                    <span class="badge badge-success">Verificado</span>
                                <?php elseif ($reporte->estado === 'rechazado'): ?>
                                    <span class="badge badge-danger">Rechazado</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pendiente</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    var pasoActual = 1;
    var totalPasos = 3;
    
    // Cambio de propiedad - cargar mensualidades
    $('#venta_id').change(function() {
        var ventaId = $(this).val();
        $('#tabla_amortizacion_id').html('<option value="">Cargando...</option>');
        $('#info-mensualidad').hide();
        
        if (ventaId) {
            $.get('<?= site_url('/cliente/pagos/obtenerMensualidadesPendientes') ?>', {
                venta_id: ventaId
            })
            .done(function(data) {
                var options = '<option value="">-- Selecciona una mensualidad --</option>';
                data.mensualidades.forEach(function(m) {
                    var montoTotal = parseFloat(m.monto_total) + parseFloat(m.interes_moratorio || 0);
                    var estado = m.estatus === 'vencida' ? ' (VENCIDA)' : '';
                    options += `<option value="${m.id}" data-info='${JSON.stringify(m)}'>
                        Mensualidad #${m.numero_pago} - Vence: ${formatearFecha(m.fecha_vencimiento)} - 
                        $${formatearMonto(montoTotal)}${estado}
                    </option>`;
                });
                $('#tabla_amortizacion_id').html(options);
            });
        } else {
            $('#tabla_amortizacion_id').html('<option value="">-- Primero selecciona una propiedad --</option>');
        }
    });
    
    // Cambio de mensualidad - mostrar información
    $('#tabla_amortizacion_id').change(function() {
        var selected = $(this).find('option:selected');
        if (selected.val()) {
            var info = JSON.parse(selected.attr('data-info'));
            var montoTotal = parseFloat(info.monto_total) + parseFloat(info.interes_moratorio || 0);
            
            var html = `
                <h6><i class="fas fa-info-circle mr-1"></i>Información de la mensualidad seleccionada:</h6>
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="150">Mensualidad:</th>
                        <td>#${info.numero_pago}</td>
                    </tr>
                    <tr>
                        <th>Vencimiento:</th>
                        <td>${formatearFecha(info.fecha_vencimiento)}</td>
                    </tr>
                    <tr>
                        <th>Monto Original:</th>
                        <td>$${formatearMonto(info.monto_total)}</td>
                    </tr>
            `;
            
            if (info.interes_moratorio > 0) {
                html += `
                    <tr>
                        <th>Interés Moratorio:</th>
                        <td class="text-warning">$${formatearMonto(info.interes_moratorio)}</td>
                    </tr>
                `;
            }
            
            html += `
                    <tr>
                        <th>Total a Pagar:</th>
                        <td><strong class="text-primary">$${formatearMonto(montoTotal)}</strong></td>
                    </tr>
                </table>
            `;
            
            $('#info-mensualidad').html(html).show();
            $('#monto_reportado').val(montoTotal.toFixed(2));
        } else {
            $('#info-mensualidad').hide();
        }
    });
    
    // Cambio de forma de pago
    $('#forma_pago').change(function() {
        var forma = $(this).val();
        if (forma === 'transferencia' || forma === 'deposito' || forma === 'cheque') {
            $('#grupo-banco').show();
            $('#banco_origen').attr('required', true);
        } else {
            $('#grupo-banco').hide();
            $('#banco_origen').attr('required', false);
        }
    });
    
    // Preview de imagen
    $('#comprobante').change(function() {
        var file = this.files[0];
        if (file) {
            $('.custom-file-label').text(file.name);
            
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagen-preview').attr('src', e.target.result);
                    $('#preview-comprobante').show();
                };
                reader.readAsDataURL(file);
            } else {
                $('#preview-comprobante').hide();
            }
        }
    });
    
    // Navegación entre pasos
    $('#btn-siguiente').click(function() {
        if (validarPaso(pasoActual)) {
            if (pasoActual < totalPasos) {
                $('#step-' + pasoActual).hide();
                pasoActual++;
                $('#step-' + pasoActual).show();
                actualizarBotones();
            }
        }
    });
    
    $('#btn-anterior').click(function() {
        if (pasoActual > 1) {
            $('#step-' + pasoActual).hide();
            pasoActual--;
            $('#step-' + pasoActual).show();
            actualizarBotones();
        }
    });
    
    // Función para actualizar botones
    function actualizarBotones() {
        if (pasoActual === 1) {
            $('#btn-anterior').hide();
        } else {
            $('#btn-anterior').show();
        }
        
        if (pasoActual === totalPasos) {
            $('#btn-siguiente').hide();
            $('#btn-enviar').show();
        } else {
            $('#btn-siguiente').show();
            $('#btn-enviar').hide();
        }
    }
    
    // Validar paso
    function validarPaso(paso) {
        var valido = true;
        
        if (paso === 1) {
            if (!$('#venta_id').val()) {
                toastr.error('Selecciona una propiedad');
                valido = false;
            } else if (!$('#tabla_amortizacion_id').val()) {
                toastr.error('Selecciona una mensualidad');
                valido = false;
            }
        } else if (paso === 2) {
            if (!$('#fecha_pago').val()) {
                toastr.error('Ingresa la fecha del pago');
                valido = false;
            } else if (!$('#forma_pago').val()) {
                toastr.error('Selecciona la forma de pago');
                valido = false;
            } else if (!$('#monto_reportado').val() || parseFloat($('#monto_reportado').val()) <= 0) {
                toastr.error('Ingresa el monto pagado');
                valido = false;
            }
        }
        
        return valido;
    }
    
    // Envío del formulario
    $('#form-reportar-pago').submit(function(e) {
        e.preventDefault();
        
        if (!validarPaso(1) || !validarPaso(2)) {
            return false;
        }
        
        // Mostrar confirmación
        Swal.fire({
            title: '¿Confirmar reporte de pago?',
            html: `
                <p>Estás reportando un pago de:</p>
                <h4 class="text-primary">$${formatearMonto($('#monto_reportado').val())}</h4>
                <p>Fecha: ${$('#fecha_pago').val()}</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, reportar',
            cancelButtonText: 'Revisar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar formulario
                this.submit();
            }
        });
    });
    
    // Funciones auxiliares
    function formatearFecha(fecha) {
        var partes = fecha.split('-');
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }
    
    function formatearMonto(monto) {
        return parseFloat(monto).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    }
    
    // Si hay mensualidad preseleccionada, cargar su información
    <?php if ($mensualidad_preseleccionada): ?>
    $('#tabla_amortizacion_id').trigger('change');
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>