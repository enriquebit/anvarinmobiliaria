<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumbs') ?>
<?php foreach ($breadcrumb as $item): ?>
    <?php if (empty($item['url'])): ?>
        <li class="breadcrumb-item active"><?= $item['name'] ?></li>
    <?php else: ?>
        <li class="breadcrumb-item"><a href="<?= $item['url'] ?>"><?= $item['name'] ?></a></li>
    <?php endif; ?>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Estadísticas de conversión -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="stat-leads-convertibles">-</h3>
                <p>Leads Convertibles</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="stat-convertidos-mes">-</h3>
                <p>Convertidos Este Mes</p>
            </div>
            <div class="icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="stat-pendientes-revision">-</h3>
                <p>Pendientes Revisión</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3 id="stat-tasa-conversion">-%</h3>
                <p>Tasa de Conversión</p>
            </div>
            <div class="icon">
                <i class="fas fa-percent"></i>
            </div>
        </div>
    </div>
</div>

<!-- Leads convertibles -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users mr-2"></i>
            Leads Listos para Conversión
        </h3>
        <div class="card-tools">
            <div class="btn-group">
                <button type="button" class="btn btn-success btn-sm" onclick="convertirSeleccionados()">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Convertir Seleccionados
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="refrescarTabla()">
                    <i class="fas fa-sync mr-2"></i>
                    Actualizar
                </button>
            </div>
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaLeadsConvertibles" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 30px;">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="selectAll">
                                <label for="selectAll" class="custom-control-label"></label>
                            </div>
                        </th>
                        <th>Folio</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Desarrollo</th>
                        <th>Identificador</th>
                        <th>Fecha Registro</th>
                        <th>Estado HubSpot</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cargado via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para convertir lead individual -->
<div class="modal fade" id="modalConvertirLead" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Convertir Lead a Cliente
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formConvertirLead">
                    <input type="hidden" id="leadId" name="lead_id">
                    
                    <div class="lead-info mb-3">
                        <h5>Información del Lead:</h5>
                        <div class="row">
                            <div class="col-sm-6"><strong>Nombre:</strong> <span id="leadNombre"></span></div>
                            <div class="col-sm-6"><strong>Email:</strong> <span id="leadEmail"></span></div>
                            <div class="col-sm-6"><strong>Teléfono:</strong> <span id="leadTelefono"></span></div>
                            <div class="col-sm-6"><strong>Desarrollo:</strong> <span id="leadDesarrollo"></span></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones de Conversión</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                  placeholder="Agregar notas sobre la conversión (opcional)"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Importante:</strong> Al convertir este lead se creará un nuevo cliente en el sistema.
                        Esta acción no se puede deshacer automáticamente.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarConversion()">
                    <i class="fas fa-check mr-2"></i>
                    Confirmar Conversión
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    const tabla = $('#tablaLeadsConvertibles').DataTable({
        processing: true,
        ajax: {
            url: '<?= base_url('admin/leads/obtener-convertibles') ?>',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'id',
                orderable: false,
                render: function(data) {
                    return `<div class="custom-control custom-checkbox">
                        <input class="custom-control-input lead-checkbox" type="checkbox" id="lead_${data}" value="${data}">
                        <label for="lead_${data}" class="custom-control-label"></label>
                    </div>`;
                }
            },
            { data: 'folio' },
            { data: 'nombre_completo' },
            { data: 'email' },
            { data: 'telefono' },
            { data: 'desarrollo' },
            { data: 'identificador' },
            { data: 'fecha_registro' },
            { data: 'hubspot_sync' },
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-success btn-sm" onclick="abrirModalConversion(${data}, '${row.nombre_completo}', '${row.email}', '${row.telefono}', '${row.desarrollo}')" title="Convertir a cliente">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[7, 'desc']], // Ordenar por fecha de registro
        });

    // Select All checkbox
    $('#selectAll').on('click', function() {
        $('.lead-checkbox').prop('checked', this.checked);
    });

    // Cargar estadísticas
    cargarEstadisticas();
});

function cargarEstadisticas() {
    $.get('<?= base_url('admin/leads/obtener-estadisticas') ?>', function(response) {
        if (response.success) {
            // Actualizar estadísticas de conversión
            $('#stat-leads-convertibles').text(response.estadisticas_registros?.total_registros || 0);
            $('#stat-convertidos-mes').text(response.estadisticas_registros?.registros_este_mes || 0);
            $('#stat-pendientes-revision').text(response.registros_pendientes || 0);
            
            // Calcular tasa de conversión
            const total = response.estadisticas_registros?.total_registros || 1;
            const convertidos = response.estadisticas_registros?.registros_este_mes || 0;
            const tasa = Math.round((convertidos / total) * 100);
            $('#stat-tasa-conversion').text(tasa + '%');
        }
    });
}

function abrirModalConversion(leadId, nombre, email, telefono, desarrollo) {
    $('#leadId').val(leadId);
    $('#leadNombre').text(nombre);
    $('#leadEmail').text(email);
    $('#leadTelefono').text(telefono);
    $('#leadDesarrollo').text(desarrollo);
    $('#observaciones').val('');
    $('#modalConvertirLead').modal('show');
}

function confirmarConversion() {
    const leadId = $('#leadId').val();
    const observaciones = $('#observaciones').val();

    if (!leadId) {
        Swal.fire('Error', 'ID de lead no válido', 'error');
        return;
    }

    Swal.fire({
        title: '¿Confirmar conversión?',
        text: 'Se creará un nuevo cliente en el sistema',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, convertir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            realizarConversion(leadId, observaciones);
        }
    });
}

function realizarConversion(leadId, observaciones) {
    $.ajax({
        url: '<?= base_url('admin/leads/convertir-a-cliente') ?>',
        type: 'POST',
        data: {
            lead_id: leadId,
            observaciones: observaciones
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Procesando...',
                text: 'Convirtiendo lead a cliente',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: '¡Conversión exitosa!',
                    text: `Cliente creado con ID: ${response.data.cliente_id}`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    $('#modalConvertirLead').modal('hide');
                    refrescarTabla();
                    cargarEstadisticas();
                });
            } else {
                Swal.fire('Error', response.error || 'Error en la conversión', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error en conversión:', xhr);
            Swal.fire('Error', 'Error de conexión con el servidor', 'error');
        }
    });
}

function convertirSeleccionados() {
    const seleccionados = $('.lead-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (seleccionados.length === 0) {
        Swal.fire('Información', 'Seleccione al menos un lead para convertir', 'info');
        return;
    }

    Swal.fire({
        title: `¿Convertir ${seleccionados.length} leads?`,
        text: 'Se crearán nuevos clientes en el sistema',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, convertir todos',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Conversión masiva
            realizarConversionMasiva(seleccionados);
        }
    });
}

function realizarConversionMasiva(leadIds) {
    let completados = 0;
    let errores = 0;
    const total = leadIds.length;

    Swal.fire({
        title: 'Procesando conversiones...',
        text: `0 de ${total} completados`,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Procesar uno por uno para evitar sobrecarga
    leadIds.forEach((leadId, index) => {
        setTimeout(() => {
            $.ajax({
                url: '<?= base_url('admin/leads/convertir-a-cliente') ?>',
                type: 'POST',
                data: {
                    lead_id: leadId,
                    observaciones: 'Conversión masiva automática'
                },
                success: function(response) {
                    if (response.success) {
                        completados++;
                    } else {
                        errores++;
                    }
                    
                    // Actualizar progreso
                    Swal.update({
                        text: `${completados + errores} de ${total} procesados`
                    });

                    // Si es el último
                    if (completados + errores === total) {
                        Swal.fire({
                            title: 'Conversión masiva completada',
                            text: `Exitosos: ${completados}, Errores: ${errores}`,
                            icon: completados > 0 ? 'success' : 'warning',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            refrescarTabla();
                            cargarEstadisticas();
                        });
                    }
                },
                error: function() {
                    errores++;
                    Swal.update({
                        text: `${completados + errores} de ${total} procesados`
                    });

                    if (completados + errores === total) {
                        Swal.fire({
                            title: 'Conversión masiva completada',
                            text: `Exitosos: ${completados}, Errores: ${errores}`,
                            icon: 'warning',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            refrescarTabla();
                            cargarEstadisticas();
                        });
                    }
                }
            });
        }, index * 500); // Retraso de 500ms entre cada conversión
    });
}

function refrescarTabla() {
    $('#tablaLeadsConvertibles').DataTable().ajax.reload();
    $('#selectAll').prop('checked', false);
}
</script>

<?= $this->endSection() ?>