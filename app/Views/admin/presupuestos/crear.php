<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
<li class="breadcrumb-item active">Generar Presupuesto</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-calculator mr-2"></i>
            <strong>Generar Presupuesto de Financiamiento</strong>
        </h3>
    </div>
    <div class="card-body">
        <form id="formPresupuesto" method="POST" action="<?= base_url('admin/presupuestos/generar-tabla') ?>">
            <?= csrf_field() ?>
            
            <div class="row">
                <!-- DATOS DEL CLIENTE -->
                <div class="col-md-6">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                <strong>Datos del Cliente</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="nombre_cliente">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" 
                                       placeholder="Nombre completo del cliente" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email_cliente">Email</label>
                                <input type="email" class="form-control" id="email_cliente" name="email_cliente" 
                                       placeholder="correo@ejemplo.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono_cliente">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente" 
                                       placeholder="Teléfono del cliente">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- DATOS DEL PRESUPUESTO -->
                <div class="col-md-6">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-home mr-2"></i>
                                <strong>Datos del Presupuesto</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="precio_lote">Precio del Lote *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="precio_lote" name="precio_lote" 
                                           placeholder="228000" step="0.01" min="1" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="perfil_financiamiento_id">Configuración Financiera *</label>
                                <select class="form-control" id="perfil_financiamiento_id" name="perfil_financiamiento_id" required>
                                    <option value="">Seleccionar configuración...</option>
                                    <?php foreach ($configuraciones as $config): ?>
                                        <option value="<?= $config->id ?>" 
                                                data-plazo="<?= $config->plazo_meses ?>"
                                                data-tasa="<?= $config->tasa_interes ?>"
                                                data-enganche="<?= $config->enganche_minimo ?>">
                                            <?= $config->nombre ?> - <?= $config->plazo_meses ?> meses
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="enganche_porcentaje">Enganche (%)</label>
                                <input type="number" class="form-control" id="enganche_porcentaje" name="enganche_porcentaje" 
                                       placeholder="0" step="0.01" min="0" max="100" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="descuento_porcentaje">Descuento (%)</label>
                                <input type="number" class="form-control" id="descuento_porcentaje" name="descuento_porcentaje" 
                                       placeholder="0" step="0.01" min="0" max="100" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- PAGOS ANTICIPADOS -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-coins mr-2"></i>
                        <strong>Pagos Anticipados (Opcional)</strong>
                    </h3>
                </div>
                <div class="card-body">
                    <div id="pagos-anticipados-container">
                        <div class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los pagos anticipados permiten al cliente hacer abonos extras en meses específicos.
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-success btn-sm" id="agregar-pago-anticipado">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar Pago Anticipado
                            </button>
                        </div>
                        
                        <div id="pagos-anticipados-list" class="mt-3"></div>
                    </div>
                </div>
            </div>
            
            <!-- CAMPOS OCULTOS -->
            <input type="hidden" id="plazo_meses" name="plazo_meses" value="60">
            <input type="hidden" id="tasa_interes" name="tasa_interes" value="0">
            <input type="hidden" id="pagos_anticipados" name="pagos_anticipados" value="[]">
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-calculator mr-2"></i>
                    Generar Presupuesto
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let contadorPagos = 0;
    
    // Actualizar campos ocultos cuando se selecciona configuración
    $('#perfil_financiamiento_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        $('#plazo_meses').val(selectedOption.data('plazo') || 60);
        $('#tasa_interes').val(selectedOption.data('tasa') || 0);
        $('#enganche_porcentaje').val(selectedOption.data('enganche') || 0);
    });
    
    // Agregar pago anticipado
    $('#agregar-pago-anticipado').click(function() {
        contadorPagos++;
        const html = `
            <div class="row pago-anticipado mb-3" data-id="${contadorPagos}">
                <div class="col-md-3">
                    <label>Mes de Aplicación</label>
                    <input type="number" class="form-control mes-aplicacion" 
                           placeholder="3" min="1" max="60" required>
                </div>
                <div class="col-md-3">
                    <label>Monto</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control monto-pago" 
                               placeholder="10000" step="0.01" min="1" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label>Descripción</label>
                    <input type="text" class="form-control descripcion-pago" 
                           placeholder="Aguinaldo, Vacaciones, etc.">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm btn-block eliminar-pago">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#pagos-anticipados-list').append(html);
    });
    
    // Eliminar pago anticipado
    $(document).on('click', '.eliminar-pago', function() {
        $(this).closest('.pago-anticipado').remove();
    });
    
    // Actualizar campo oculto de pagos anticipados antes de enviar
    $('#formPresupuesto').submit(function() {
        const pagosAnticipados = [];
        
        $('.pago-anticipado').each(function() {
            const mes = $(this).find('.mes-aplicacion').val();
            const monto = $(this).find('.monto-pago').val();
            const descripcion = $(this).find('.descripcion-pago').val();
            
            if (mes && monto) {
                pagosAnticipados.push({
                    mes_aplicacion: parseInt(mes),
                    monto: parseFloat(monto),
                    descripcion: descripcion || `Pago anticipado mes ${mes}`
                });
            }
        });
        
        $('#pagos_anticipados').val(JSON.stringify(pagosAnticipados));
    });
    
    // Validaciones en tiempo real
    $('#precio_lote').on('input', function() {
        const valor = parseFloat($(this).val());
        if (valor < 1) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Formatear números en campos monetarios
    $('#precio_lote').on('blur', function() {
        const valor = parseFloat($(this).val());
        if (!isNaN(valor)) {
            $(this).val(valor.toFixed(2));
        }
    });
});
</script>
<?= $this->endSection() ?>