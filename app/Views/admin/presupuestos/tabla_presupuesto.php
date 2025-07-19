<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
Presupuesto de Financiamiento
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item"><a href="<?= base_url('admin/presupuestos') ?>">Presupuestos</a></li>
<li class="breadcrumb-item active">Tabla de Amortización</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table mr-2"></i>
            <strong>Presupuesto de Financiamiento</strong>
        </h3>
        <div class="card-tools">
            <a href="<?= base_url('admin/presupuestos/crear') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i>
                Nuevo Presupuesto
            </a>
        </div>
    </div>
    <div class="card-body">
        
        <!-- DATOS DEL CLIENTE -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user mr-2"></i>
                            Datos del Cliente
                        </h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <?= $cliente['nombre_completo'] ?></p>
                        <p><strong>Email:</strong> <?= $cliente['email'] ?: 'No especificado' ?></p>
                        <p><strong>Teléfono:</strong> <?= $cliente['telefono'] ?: 'No especificado' ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calculator mr-2"></i>
                            Resumen Financiero
                        </h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Precio del Lote:</strong> $<?= number_format($datos['precio_total'], 2) ?></p>
                        <p><strong>Enganche:</strong> $<?= number_format($datos['enganche_monto'], 2) ?></p>
                        <p><strong>Monto a Financiar:</strong> $<?= number_format($datos['monto_financiar'], 2) ?></p>
                        <p><strong>Plazo:</strong> <?= $datos['plazo_meses'] ?> meses</p>
                        <p><strong>Mensualidad:</strong> $<?= number_format($datos['mensualidad'], 2) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- BOTONES DE ACCIÓN -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i>
                        Imprimir
                    </button>
                    <button type="button" class="btn btn-success" onclick="abrirPDF()">
                        <i class="fas fa-file-pdf mr-1"></i>
                        Ver PDF
                    </button>
                    <button type="button" class="btn btn-warning" onclick="exportarPDF()">
                        <i class="fas fa-download mr-1"></i>
                        Descargar PDF
                    </button>
                    <button type="button" class="btn btn-info" onclick="enviarEmail()">
                        <i class="fas fa-envelope mr-1"></i>
                        Enviar por Email
                    </button>
                </div>
            </div>
        </div>
        
        <!-- INCLUIR LA TABLA DE AMORTIZACIÓN EXISTENTE -->
        <?php
        // Reutilizar la vista existente pero con pequeñas modificaciones
        // Usar un mini-layout para incluir solo la tabla
        ?>
        
        <div class="presupuesto-tablas">
            <?php 
            // Incluir el contenido de la tabla de amortización
            // Necesitamos incluir la lógica de cálculo aquí
            include(APPPATH . 'Views/admin/ventas/tabla_amortizacion_content.php');
            ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function abrirPDF() {
    // Construir URL con parámetros para la vista PDF
    const params = new URLSearchParams({
        precio_lote: <?= $datos['precio_total'] ?>,
        perfil_financiamiento_id: <?= $configuracion->id ?>,
        enganche_porcentaje: <?= $datos['enganche_porcentaje'] ?>,
        plazo_meses: <?= $datos['plazo_meses'] ?>,
        tasa_interes: <?= $datos['tasa_interes'] ?>,
        descuento_porcentaje: <?= $datos['descuento_porcentaje'] ?>,
        pagos_anticipados: '<?= json_encode($pagos_anticipados) ?>',
        nombre_cliente: '<?= $cliente['nombre_completo'] ?>',
        email_cliente: '<?= $cliente['email'] ?>',
        telefono_cliente: '<?= $cliente['telefono'] ?>'
    });
    
    const url = '<?= base_url('admin/presupuestos/tabla-pdf') ?>?' + params.toString();
    window.open(url, '_blank');
}

function exportarPDF() {
    // Construir URL con parámetros para exportar PDF
    const params = new URLSearchParams({
        precio_lote: <?= $datos['precio_total'] ?>,
        perfil_financiamiento_id: <?= $configuracion->id ?>,
        enganche_porcentaje: <?= $datos['enganche_porcentaje'] ?>,
        plazo_meses: <?= $datos['plazo_meses'] ?>,
        tasa_interes: <?= $datos['tasa_interes'] ?>,
        descuento_porcentaje: <?= $datos['descuento_porcentaje'] ?>,
        pagos_anticipados: '<?= json_encode($pagos_anticipados) ?>',
        nombre_cliente: '<?= $cliente['nombre_completo'] ?>',
        email_cliente: '<?= $cliente['email'] ?>',
        telefono_cliente: '<?= $cliente['telefono'] ?>'
    });
    
    const url = '<?= base_url('admin/presupuestos/exportar-pdf') ?>?' + params.toString();
    window.location.href = url;
}

function enviarEmail() {
    // Crear modal para envío por email
    const modalHtml = `
    <div class="modal fade" id="modalEnviarEmail" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope mr-2"></i>
                        Enviar Presupuesto por Email
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formEnviarEmail" method="POST" action="<?= base_url('admin/presupuestos/enviar-email') ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="email_destino">
                                <i class="fas fa-envelope mr-1"></i>
                                Email de Destino <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email_destino" name="email_destino" 
                                   value="<?= $cliente['email'] ?>" required>
                            <small class="form-text text-muted">
                                Email donde se enviará el presupuesto con el PDF adjunto
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="asunto">
                                <i class="fas fa-tag mr-1"></i>
                                Asunto
                            </label>
                            <input type="text" class="form-control" id="asunto" name="asunto" 
                                   value="Presupuesto de Financiamiento - <?= esc($cliente['nombre_completo']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="mensaje">
                                <i class="fas fa-comment mr-1"></i>
                                Mensaje
                            </label>
                            <textarea class="form-control" id="mensaje" name="mensaje" rows="4" 
                                      placeholder="Mensaje personalizado para el cliente...">Estimado(a) <?= esc($cliente['nombre_completo']) ?>,

Le enviamos su presupuesto de financiamiento personalizado. En el archivo adjunto encontrará todos los detalles de su plan de pagos.

Para cualquier consulta o para proceder con la solicitud, no dude en contactarnos.

Saludos cordiales,
Equipo ANVAR</textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Información:</strong> Se enviará un PDF con la tabla de amortización completa como archivo adjunto.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i>
                            Enviar Email
                        </button>
                    </div>
                    
                    <!-- Campos ocultos con datos del presupuesto -->
                    <input type="hidden" name="precio_lote" value="<?= $datos['precio_total'] ?>">
                    <input type="hidden" name="perfil_financiamiento_id" value="<?= $configuracion->id ?>">
                    <input type="hidden" name="enganche_porcentaje" value="<?= $datos['enganche_porcentaje'] ?>">
                    <input type="hidden" name="plazo_meses" value="<?= $datos['plazo_meses'] ?>">
                    <input type="hidden" name="tasa_interes" value="<?= $datos['tasa_interes'] ?>">
                    <input type="hidden" name="descuento_porcentaje" value="<?= $datos['descuento_porcentaje'] ?>">
                    <input type="hidden" name="pagos_anticipados" value='<?= json_encode($pagos_anticipados) ?>'>
                    <input type="hidden" name="nombre_cliente" value="<?= esc($cliente['nombre_completo']) ?>">
                    <input type="hidden" name="email_cliente" value="<?= esc($cliente['email']) ?>">
                    <input type="hidden" name="telefono_cliente" value="<?= esc($cliente['telefono']) ?>">
                </form>
            </div>
        </div>
    </div>
    `;
    
    // Remover modal existente si existe
    $('#modalEnviarEmail').remove();
    
    // Agregar modal al body
    $('body').append(modalHtml);
    
    // Mostrar modal
    $('#modalEnviarEmail').modal('show');
    
    // Manejar envío del formulario
    $('#formEnviarEmail').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Deshabilitar botón y mostrar loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Enviando...');
        
        // Validar email
        const email = $('#email_destino').val();
        if (!email || !isValidEmail(email)) {
            alert('Por favor ingrese un email válido');
            submitBtn.prop('disabled', false).html(originalText);
            e.preventDefault();
            return false;
        }
    });
    
    // Limpiar modal al cerrar
    $('#modalEnviarEmail').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Mejorar impresión
window.addEventListener('beforeprint', function() {
    // Mostrar todas las tablas para impresión
    $('.tab-pane').addClass('show active');
});

window.addEventListener('afterprint', function() {
    // Restaurar vista normal
    $('.tab-pane').removeClass('show active');
    $('.tab-pane:first').addClass('show active');
});
</script>
<?= $this->endSection() ?>