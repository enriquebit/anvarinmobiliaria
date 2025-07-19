<!-- Modal para Simulación de Tabla de Amortización -->
<div class="modal fade" id="modalSimulacion" tabindex="-1" role="dialog" aria-labelledby="modalSimulacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- ENCABEZADO DEL MODAL -->
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalSimulacionLabel">
                    <i class="fas fa-handshake mr-2"></i>
                    Simulación de Tabla de Amortización - <span id="modalLoteClave">Apartado</span>
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- CUERPO DEL MODAL -->
            <div class="modal-body" style="padding: 15px;">
                <!-- ENCABEZADO CORPORATIVO -->
                <div id="encabezadoCorporativo">
                    <!-- Se genera dinámicamente con JavaScript -->
                </div>

                <!-- RESUMEN EJECUTIVO COMPACTO -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card text-white" style="background: linear-gradient(135deg, #ff9500 0%, #ff6b35 100%);">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-money-bill-wave mb-1" style="font-size: 1.2rem;"></i>
                                <div class="small mb-0">Monto a Financiar</div>
                                <h6 class="mb-0 font-weight-bold" id="resumen-monto-financiar"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-calendar-check mb-1" style="font-size: 1.2rem;"></i>
                                <div class="small mb-0">Pago Mensual</div>
                                <h6 class="mb-0 font-weight-bold" id="resumen-pago-mensual"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-clock mb-1" style="font-size: 1.2rem;"></i>
                                <div class="small mb-0">Plazo Total</div>
                                <h6 class="mb-0 font-weight-bold" id="resumen-plazo-meses"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                            <div class="card-body text-center py-2">
                                <i class="fas fa-percentage mb-1" style="font-size: 1.2rem;"></i>
                                <div class="small mb-0">Tasa Anual</div>
                                <h6 class="mb-0 font-weight-bold" id="resumen-tasa-anual"></h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE AMORTIZACIÓN -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered" style="font-size: 0.8rem;">
                        <thead class="thead-dark" style="position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th class="text-center" style="width: 8%;">Período</th>
                                <th class="text-center" style="width: 12%;">Fecha Pago</th>
                                <th class="text-center" style="width: 15%;">Saldo Inicial</th>
                                <th class="text-center" style="width: 15%;">Pago Mensual</th>
                                <th class="text-center" style="width: 12%;">Interés</th>
                                <th class="text-center" style="width: 15%;">Capital</th>
                                <th class="text-center" style="width: 15%;">Saldo Final</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaAmortizacion">
                            <!-- Se genera dinámicamente con JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- PIE DE TABLA CON TOTALES -->
                <div class="row mt-3 p-2" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.25rem;">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <strong>Total Intereses:</strong>
                            <span id="totalIntereses" class="text-danger font-weight-bold">$0.00</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <strong>Total Capital:</strong>
                            <span id="totalCapital" class="text-primary font-weight-bold">$0.00</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <strong>Total Pagado:</strong>
                            <span id="totalPagado" class="text-success font-weight-bold">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- INFORMACIÓN ADICIONAL -->
                <div class="alert alert-info mt-3 mb-0" style="font-size: 0.85rem;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <div>
                            <strong>Leyenda:</strong>
                            <span class="badge badge-success ml-2">MSI</span> Meses Sin Interés
                            <span class="badge badge-warning ml-2">MCI</span> Meses Con Interés
                            <span class="badge badge-danger ml-2">APT</span> Apartado (requiere liquidar enganche)
                        </div>
                    </div>
                </div>
            </div>

            <!-- PIE DEL MODAL -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
                <button type="button" class="btn btn-warning" onclick="imprimirSimulacion()">
                    <i class="fas fa-print mr-1"></i>
                    Imprimir
                </button>
            </div>
        </div>
    </div>
</div>