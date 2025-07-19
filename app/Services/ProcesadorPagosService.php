<?php

namespace App\Services;

use App\Models\VentaModel;
use App\Models\AnticipoApartadoModel;
use App\Models\LiquidacionEngancheModel;
use App\Models\CuentaFinanciamientoModel;
use App\Models\MovimientoCuentaModel;
use App\Models\ConceptoPagoModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Entities\ConceptoPago;
use App\Entities\MovimientoCuenta;
use CodeIgniter\Database\BaseConnection;
use Exception;

// Cargar helper de pagos inmobiliarios
helper('pagos_inmobiliarios');

/**
 * Service ProcesadorPagosService
 * 
 * Servicio principal para procesamiento de pagos inmobiliarios
 * Maneja toda la lógica de negocio y orquesta los diferentes helpers
 */
class ProcesadorPagosService
{
    protected VentaModel $ventaModel;
    protected AnticipoApartadoModel $anticipoModel;
    protected LiquidacionEngancheModel $liquidacionModel;
    protected CuentaFinanciamientoModel $cuentaModel;
    protected MovimientoCuentaModel $movimientoModel;
    protected ConceptoPagoModel $conceptoModel;
    protected TablaAmortizacionModel $tablaModel;
    protected PagoVentaModel $pagoVentaModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->anticipoModel = new AnticipoApartadoModel();
        $this->liquidacionModel = new LiquidacionEngancheModel();
        $this->cuentaModel = new CuentaFinanciamientoModel();
        $this->movimientoModel = new MovimientoCuentaModel();
        $this->conceptoModel = new ConceptoPagoModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->pagoVentaModel = new PagoVentaModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Procesar pago de apartado
     */
    public function procesarPagoApartado(array $datos): array
    {
        $this->db->transBegin();

        try {
            // Validar venta
            $venta = $this->ventaModel->find($datos['venta_id']);
            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }

            // Procesar anticipo usando helper
            $resultadoAnticipo = acumular_anticipos($datos['venta_id'], $datos['monto']);
            if (!$resultadoAnticipo['success']) {
                throw new Exception($resultadoAnticipo['error']);
            }

            // Registrar concepto de pago (comentado temporalmente - método no implementado)
            /*$conceptoPago = ConceptoPago::crearApartado([
                'venta_id' => $datos['venta_id'],
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
                'referencia' => $datos['referencia'] ?? '',
                'observaciones' => $datos['observaciones'] ?? ''
            ]);

            $this->conceptoModel->save($conceptoPago);*/
            $conceptoPagoId = null;

            // Verificar si se puede generar liquidación automática
            $validacionLiquidacion = validar_conversion_enganche($datos['venta_id']);
            $liquidacionGenerada = false;
            
            if ($validacionLiquidacion['success'] && $validacionLiquidacion['listo_conversion']) {
                $resultadoLiquidacion = generar_liquidacion_enganche($datos['venta_id']);
                $liquidacionGenerada = $resultadoLiquidacion['success'];
            }

            $this->db->transCommit();

            return [
                'success' => true,
                'anticipo_id' => $resultadoAnticipo['anticipo_id'],
                'concepto_id' => $conceptoPagoId,
                'monto_procesado' => $datos['monto'],
                'monto_acumulado' => $resultadoAnticipo['monto_acumulado'],
                'porcentaje_completado' => $resultadoAnticipo['porcentaje_completado'],
                'liquidacion_generada' => $liquidacionGenerada,
                'mensaje' => 'Pago de apartado procesado exitosamente'
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            return [
                'success' => false,
                'error' => 'Error procesando pago apartado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar liquidación de enganche
     */
    public function procesarLiquidacionEnganche(array $datos): array
    {
        $this->db->transBegin();

        try {
            // Validar venta
            $venta = $this->ventaModel->find($datos['venta_id']);
            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }

            // Verificar si hay liquidación existente
            $liquidacionExistente = $this->liquidacionModel->where('venta_id', $datos['venta_id'])
                                                          ->where('estado !=', 'cancelada')
                                                          ->first();

            if ($liquidacionExistente) {
                throw new Exception('Ya existe una liquidación para esta venta');
            }

            // Crear liquidación directa
            $liquidacion = new \App\Entities\LiquidacionEnganche();
            $liquidacion->venta_id = $datos['venta_id'];
            $liquidacion->monto_enganche_requerido = $datos['monto_enganche_requerido'];
            $liquidacion->monto_pago_directo = $datos['monto'];
            $liquidacion->monto_total_liquidado = $datos['monto'];
            $liquidacion->saldo_pendiente = max(0, $datos['monto_enganche_requerido'] - $datos['monto']);
            $liquidacion->tipo_origen = 'pago_directo';
            $liquidacion->estado = $liquidacion->saldo_pendiente > 0 ? 'parcial' : 'completada';
            $liquidacion->fecha_liquidacion = $datos['fecha_pago'];
            $liquidacion->generarFolio();

            $this->liquidacionModel->save($liquidacion);

            // Registrar concepto de pago (comentado temporalmente - método no implementado)
            /*$conceptoPago = ConceptoPago::crearLiquidacionEnganche([
                'venta_id' => $datos['venta_id'],
                'liquidacion_id' => $liquidacion->id,
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
                'referencia' => $datos['referencia'] ?? '',
                'observaciones' => $datos['observaciones'] ?? ''
            ]);

            $this->conceptoModel->save($conceptoPago);*/
            $conceptoPagoId = null;

            // Si está completada, preparar para apertura de cuenta
            $cuentaAbierta = false;
            if ($liquidacion->estado === 'completada') {
                $resultadoCuenta = $this->abrirCuentaFinanciamiento($liquidacion->id);
                $cuentaAbierta = $resultadoCuenta['success'];
            }

            $this->db->transCommit();

            return [
                'success' => true,
                'liquidacion_id' => $liquidacion->id,
                'folio_liquidacion' => $liquidacion->folio_liquidacion,
                'concepto_id' => $conceptoPagoId,
                'monto_procesado' => $datos['monto'],
                'saldo_pendiente' => $liquidacion->saldo_pendiente,
                'completada' => $liquidacion->estado === 'completada',
                'cuenta_abierta' => $cuentaAbierta,
                'mensaje' => 'Liquidación de enganche procesada exitosamente'
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            return [
                'success' => false,
                'error' => 'Error procesando liquidación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar pago de mensualidad
     */
    public function procesarPagoMensualidad(array $datos): array
    {
        $this->db->transBegin();

        try {
            // Validar cuenta de financiamiento
            $cuenta = $this->cuentaModel->where('venta_id', $datos['venta_id'])
                                      ->where('estado', 'activa')
                                      ->first();

            if (!$cuenta) {
                throw new Exception('No existe cuenta de financiamiento activa para esta venta');
            }

            // DEBUG ESPECÍFICO: Ver qué tipo de objeto es la cuenta
            log_message('debug', 'TIPO DE CUENTA: ' . get_class($cuenta));
            log_message('debug', 'CUENTA DATA: ' . json_encode($cuenta->toArray()));
            
            // Verificar si los campos nuevos existen
            $camposNuevos = ['saldo_capital', 'saldo_interes', 'saldo_moratorio', 'total_abonos_capital'];
            foreach($camposNuevos as $campo) {
                log_message('debug', "Campo {$campo} = " . (property_exists($cuenta, $campo) ? $cuenta->$campo : 'NO EXISTE'));
            }

            // Procesar pago usando el helper
            $resultadoPago = $this->procesarPagoConDistribucion($cuenta, $datos['monto'], $datos);

            if (!$resultadoPago['success']) {
                throw new Exception($resultadoPago['error']);
            }

            // Aplicar pago a tabla de amortización (similar al flujo de ventas cero enganche)
            $this->aplicarPagoATablaAmortizacion($datos['venta_id'], $datos['monto'], $datos['fecha_pago']);

            // Registrar pago en historial (tablas de ingresos y pagos_ventas)
            $this->registrarPagoEnHistorial($datos['venta_id'], $datos['monto'], $datos['fecha_pago'], $datos);

            // Registrar concepto de pago (comentado temporalmente - método no implementado)
            /*$conceptoPago = ConceptoPago::crearMensualidad([
                'venta_id' => $datos['venta_id'],
                'cuenta_id' => $cuenta->id,
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
                'referencia' => $datos['referencia'] ?? '',
                'observaciones' => $datos['observaciones'] ?? ''
            ]);

            $this->conceptoModel->save($conceptoPago);*/
            $conceptoPagoId = null;

            $this->db->transCommit();

            return [
                'success' => true,
                'concepto_id' => $conceptoPagoId,
                'movimiento_id' => $resultadoPago['movimiento_id'],
                'monto_procesado' => $datos['monto'],
                'distribucion' => $resultadoPago['distribucion'],
                'saldo_actualizado' => $resultadoPago['saldo_actualizado'],
                'cuenta_liquidada' => $resultadoPago['cuenta_liquidada'] ?? false,
                'mensaje' => 'Pago de mensualidad procesado exitosamente'
            ];

        } catch (Exception $e) {
            log_message('error', 'EXCEPTION EN PROCESAR PAGO: ' . $e->getMessage());
            $this->db->transRollback();
            return [
                'success' => false,
                'error' => 'Error procesando mensualidad: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar abono a capital
     */
    public function procesarAbonoCapital(array $datos): array
    {
        $this->db->transBegin();

        try {
            // Validar cuenta de financiamiento
            $cuenta = $this->cuentaModel->where('venta_id', $datos['venta_id'])
                                      ->where('estado', 'activa')
                                      ->first();

            if (!$cuenta) {
                throw new Exception('No existe cuenta de financiamiento activa para esta venta');
            }

            // Validar monto
            if ($datos['monto'] <= 0 || $datos['monto'] > $cuenta->saldo_capital) {
                throw new Exception('Monto inválido para abono a capital');
            }

            // Procesar abono usando helper de refactorización
            $resultadoRefactorizacion = ejecutar_refactorizacion_automatica($cuenta->id, $datos['monto']);

            if (!$resultadoRefactorizacion['success']) {
                throw new Exception($resultadoRefactorizacion['error']);
            }

            // Registrar concepto de pago (comentado temporalmente - método no implementado)
            /*$conceptoPago = ConceptoPago::crearAbonoCapital([
                'venta_id' => $datos['venta_id'],
                'cuenta_id' => $cuenta->id,
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
                'referencia' => $datos['referencia'] ?? '',
                'observaciones' => $datos['observaciones'] ?? ''
            ]);

            $this->conceptoModel->save($conceptoPago);*/

            $this->db->transCommit();

            return [
                'success' => true,
                'concepto_id' => null, // $conceptoPago->id, - Comentado temporalmente
                'movimiento_id' => $resultadoRefactorizacion['movimiento_id'],
                'monto_procesado' => $datos['monto'],
                'nueva_mensualidad' => $resultadoRefactorizacion['nueva_mensualidad'],
                'ahorro_intereses' => $resultadoRefactorizacion['ahorro_intereses'],
                'meses_restantes' => $resultadoRefactorizacion['meses_restantes'],
                'mensaje' => 'Abono a capital procesado exitosamente'
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            return [
                'success' => false,
                'error' => 'Error procesando abono a capital: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Abrir cuenta de financiamiento desde liquidación
     */
    private function abrirCuentaFinanciamiento(int $liquidacionId): array
    {
        try {
            $liquidacion = $this->liquidacionModel->find($liquidacionId);
            if (!$liquidacion) {
                throw new Exception('Liquidación no encontrada');
            }

            $venta = $this->ventaModel->find($liquidacion->venta_id);
            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }

            // Calcular monto a financiar
            $montoFinanciar = $venta->precio_venta_final - $liquidacion->monto_total_liquidado;

            if ($montoFinanciar <= 0) {
                throw new Exception('No hay monto pendiente para financiar');
            }

            // Obtener perfil de financiamiento de la venta
            $db = \Config\Database::connect();
            $perfil = $db->table('perfiles_financiamiento')
                        ->where('id', $venta->perfil_financiamiento_id)
                        ->get()
                        ->getRow();
            
            if (!$perfil) {
                throw new Exception('Perfil de financiamiento no encontrado');
            }

            // Crear cuenta de financiamiento
            $cuenta = new \App\Entities\CuentaFinanciamiento();
            $cuenta->venta_id = $venta->id;
            $cuenta->liquidacion_id = $liquidacion->id;
            $cuenta->capital_inicial = $montoFinanciar;
            $cuenta->saldo_capital = $montoFinanciar;
            $cuenta->saldo_actual = $montoFinanciar;
            $cuenta->tasa_interes_anual = $perfil->tasa_interes_anual ?? 12.0;
            $cuenta->plazo_meses = $perfil->plazo_total_meses ?? 60;
            $cuenta->meses_restantes = $perfil->plazo_total_meses ?? 60;
            $cuenta->estado = 'activa';
            $cuenta->fecha_apertura = date('Y-m-d');
            $cuenta->generarNumeroCuenta();
            // $cuenta->calcularMensualidad(); // Método no implementado aún

            $this->cuentaModel->save($cuenta);

            // Marcar liquidación como cuenta abierta
            $liquidacion->cuenta_abierta = true;
            $liquidacion->cuenta_financiamiento_id = $cuenta->id;
            $liquidacion->fecha_apertura_cuenta = date('Y-m-d');
            $this->liquidacionModel->save($liquidacion);

            // Crear movimiento de apertura (comentado temporalmente - método no implementado)
            /*$movimiento = MovimientoCuenta::crearApertura([
                'cuenta_id' => $cuenta->id,
                'monto_inicial' => $montoFinanciar,
                'usuario_id' => auth()->id() ?? 1
            ]);

            $this->movimientoModel->save($movimiento);*/

            return [
                'success' => true,
                'cuenta_id' => $cuenta->id,
                'numero_cuenta' => $cuenta->numero_cuenta,
                'monto_financiado' => $montoFinanciar,
                'mensualidad' => $cuenta->monto_mensualidad
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error abriendo cuenta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar pago con distribución (moratorios → intereses → capital)
     */
    private function procesarPagoConDistribucion($cuenta, float $monto, array $datos): array
    {
        $distribucion = [
            'moratorios' => 0,
            'intereses' => 0,
            'capital' => 0
        ];

        $montoRestante = $monto;

        // 1. Aplicar a moratorios
        if ($cuenta->saldo_moratorio > 0 && $montoRestante > 0) {
            $aplicadoMoratorio = min($montoRestante, $cuenta->saldo_moratorio);
            $distribucion['moratorios'] = $aplicadoMoratorio;
            $cuenta->saldo_moratorio -= $aplicadoMoratorio;
            $cuenta->total_moratorios_pagados += $aplicadoMoratorio;
            $montoRestante -= $aplicadoMoratorio;
        }

        // 2. Aplicar a intereses
        if ($cuenta->saldo_interes > 0 && $montoRestante > 0) {
            $aplicadoInteres = min($montoRestante, $cuenta->saldo_interes);
            $distribucion['intereses'] = $aplicadoInteres;
            $cuenta->saldo_interes -= $aplicadoInteres;
            $cuenta->total_intereses_pagados += $aplicadoInteres;
            $montoRestante -= $aplicadoInteres;
        }

        // 3. Aplicar a capital
        if ($cuenta->saldo_capital > 0 && $montoRestante > 0) {
            $aplicadoCapital = min($montoRestante, $cuenta->saldo_capital);
            $distribucion['capital'] = $aplicadoCapital;
            $cuenta->saldo_capital -= $aplicadoCapital;
            $cuenta->total_abonos_capital += $aplicadoCapital;
            $montoRestante -= $aplicadoCapital;
        }

        // Actualizar saldo actual (total) basado en campos disponibles
        $cuenta->saldo_actual = $cuenta->saldo_capital ?? $cuenta->saldo_actual;
        // NOTA: fecha_ultimo_pago y pagos_realizados se manejan en tabla_amortizacion, no en cuentas_financiamiento

        // Verificar si se liquidó
        $cuentaLiquidada = false;
        if ($cuenta->saldo_actual <= 0) {
            $cuenta->estado = 'liquidada';
            $cuenta->fecha_liquidacion = $datos['fecha_pago'];
            $cuentaLiquidada = true;
        }

        // DEBUG: Verificar estado de la cuenta antes de guardar
        echo "<pre>";
        echo "=== DEBUG CUENTA ANTES DE SAVE ===\n";
        var_dump([
            'cuenta_id' => $cuenta->id ?? 'NO_ID',
            'saldo_actual' => $cuenta->saldo_actual ?? 'NO_SALDO',
            'saldo_capital' => $cuenta->saldo_capital ?? 'NO_SALDO_CAPITAL',
            'hasChanged' => method_exists($cuenta, 'hasChanged') ? $cuenta->hasChanged() : 'NO_METHOD',
            'getDirty' => method_exists($cuenta, 'getDirty') ? $cuenta->getDirty() : 'NO_METHOD',
            'toArray' => $cuenta->toArray() ?? 'NO_ARRAY'
        ]);
        echo "</pre>";

        // Guardar cuenta - PRUEBA SIMPLE
        try {
            // Verificar si los campos nuevos realmente existen
            log_message('debug', 'ANTES DEL SAVE - Cuenta tiene cambios: ' . ($cuenta->hasChanged() ? 'SI' : 'NO'));
            
            // Intentar actualizar manualmente usando el modelo
            $updateData = [
                'saldo_actual' => $cuenta->saldo_actual
            ];
            
            log_message('debug', 'UPDATE DATA: ' . json_encode($updateData));
            
            // Usar update directo en lugar de save
            $result = $this->cuentaModel->update($cuenta->id, $updateData);
            
            log_message('debug', 'UPDATE RESULT: ' . ($result ? 'SUCCESS' : 'FAILED'));
            
            if (!$result) {
                $errors = $this->cuentaModel->errors();
                log_message('error', 'UPDATE ERRORS: ' . json_encode($errors));
                throw new Exception('Error actualizando cuenta: ' . json_encode($errors));
            }
        } catch (\Exception $e) {
            log_message('error', 'SAVE/UPDATE ERROR: ' . $e->getMessage());
            throw $e;
        }

        // Crear movimiento (comentado temporalmente - método no implementado)
        /*$movimiento = MovimientoCuenta::crearAbono([
            'cuenta_id' => $cuenta->id,
            'monto' => $monto,
            'distribucion' => $distribucion,
            'saldo_anterior' => [
                'capital' => $cuenta->saldo_capital + $distribucion['capital'],
                'interes' => $cuenta->saldo_interes + $distribucion['intereses'],
                'moratorio' => $cuenta->saldo_moratorio + $distribucion['moratorios'],
                'total' => $cuenta->saldo_actual + $monto
            ],
            'saldo_nuevo' => [
                'capital' => $cuenta->saldo_capital,
                'interes' => $cuenta->saldo_interes,
                'moratorio' => $cuenta->saldo_moratorio,
                'total' => $cuenta->saldo_actual
            ],
            'usuario_id' => auth()->id() ?? 1
        ]);

        $this->movimientoModel->save($movimiento);*/

        return [
            'success' => true,
            'movimiento_id' => null, // $movimiento->id, // Comentado temporalmente
            'distribucion' => $distribucion,
            'saldo_actualizado' => [
                'capital' => $cuenta->saldo_capital,
                'interes' => $cuenta->saldo_interes,
                'moratorio' => $cuenta->saldo_moratorio,
                'total' => $cuenta->saldo_actual
            ],
            'cuenta_liquidada' => $cuentaLiquidada
        ];
    }

    /**
     * Obtener resumen de cuenta por venta
     */
    public function obtenerResumenCuenta(int $ventaId): array
    {
        try {
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }

            // Buscar cuenta de financiamiento
            $cuenta = $this->cuentaModel->where('venta_id', $ventaId)->first();
            
            if (!$cuenta) {
                // No hay cuenta de financiamiento, verificar si se puede crear
                $db = \Config\Database::connect();
                
                // Verificar pagos de enganche
                $enganchePagado = $db->table('ingresos')
                                    ->selectSum('monto')
                                    ->where('venta_id', $ventaId)
                                    ->whereIn('tipo_ingreso', ['apartado', 'enganche'])
                                    ->get()
                                    ->getRow()->monto ?? 0;
                
                // Verificar si es plan Cero Enganche
                $perfil = $db->table('perfiles_financiamiento')
                            ->where('id', $venta->perfil_financiamiento_id)
                            ->get()
                            ->getRow();
                
                $esCeroEnganche = $perfil && $perfil->promocion_cero_enganche;
                
                // Para planes Cero Enganche, crear cuenta automáticamente
                if ($esCeroEnganche) {
                    // Obtener todos los parámetros del perfil de financiamiento
                    $datosCuenta = [
                        'venta_id' => $ventaId,
                        'plan_financiamiento_id' => $venta->perfil_financiamiento_id,
                        'capital_inicial' => $venta->precio_venta_final,
                        'saldo_capital' => $venta->precio_venta_final,
                        'saldo_actual' => $venta->precio_venta_final,
                        'tasa_interes_anual' => $perfil->tasa_interes_anual ?? 12.0,
                        'plazo_meses' => $perfil->plazo_total_meses ?? 60,
                        'meses_restantes' => $perfil->plazo_total_meses ?? 60,
                        'monto_mensualidad' => $venta->calcularPagoMensual(),
                        'fecha_apertura' => date('Y-m-d'),
                        'estado' => 'activa',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->cuentaModel->insert($datosCuenta);
                    $cuenta = $this->cuentaModel->find($this->cuentaModel->insertID());
                    
                    log_message('info', "Cuenta de financiamiento creada automáticamente para venta Cero Enganche {$ventaId}");
                } else {
                    return [
                        'success' => false,
                        'error' => 'No existe cuenta de financiamiento activa',
                        'enganche_pagado' => $enganchePagado,
                        'precio_venta' => $venta->precio_venta_final,
                        'puede_crear_cuenta' => $enganchePagado >= ($venta->precio_venta_final * 0.20) // 20% mínimo
                    ];
                }
            }

            // Calcular información de la cuenta
            $totalPagos = $this->db->table('ingresos')
                                  ->selectSum('monto')
                                  ->where('venta_id', $ventaId)
                                  ->where('tipo_ingreso', 'mensualidad')
                                  ->get()
                                  ->getRow()->monto ?? 0;

            return [
                'success' => true,
                'cuenta_id' => $cuenta->id,
                'saldo_capital' => $cuenta->saldo_actual ?? 0,
                'saldo_interes' => 0, // Simplificado por ahora
                'saldo_moratorio' => 0, // Simplificado por ahora
                'saldo_actual' => $cuenta->saldo_actual ?? 0,
                'mensualidad' => $this->calcularMensualidadReal($venta),
                'pagos_realizados' => $this->db->table('ingresos')
                                               ->where('venta_id', $ventaId)
                                               ->where('tipo_ingreso', 'mensualidad')
                                               ->countAllResults(),
                'meses_restantes' => 60, // TODO: calcular real
                'proximo_vencimiento' => date('Y-m-d', strtotime('+1 month')),
                'al_corriente' => true, // Simplificado por ahora
                'total_mensualidades_pagadas' => $totalPagos
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error obteniendo resumen: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Simular abono a capital
     */
    public function simularAbonoCapital(int $ventaId, float $monto): array
    {
        try {
            $cuenta = $this->cuentaModel->where('venta_id', $ventaId)
                                      ->where('estado', 'activa')
                                      ->first();

            if (!$cuenta) {
                throw new Exception('No existe cuenta de financiamiento activa');
            }

            return simular_abono_capital($cuenta->id, $monto);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error simulando abono: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Calcular mensualidad real basada en la tabla de amortización
     */
    private function calcularMensualidadReal($venta): float
    {
        // Usar el método de la entity para calcular la mensualidad
        return $venta->calcularPagoMensual();
    }

    /**
     * Aplicar pago a tabla de amortización (siguiendo el patrón de ventas cero enganche)
     */
    private function aplicarPagoATablaAmortizacion(int $ventaId, float $monto, string $fechaPago): void
    {
        // Buscar la siguiente mensualidad pendiente
        $mensualidadPendiente = $this->tablaModel
            ->where('venta_id', $ventaId)
            ->whereNotIn('estatus', ['pagada', 'cancelada'])
            ->orderBy('numero_pago', 'ASC')
            ->first();

        if (!$mensualidadPendiente) {
            log_message('warning', "No hay mensualidades pendientes para venta {$ventaId}");
            return;
        }

        // Calcular cuánto aplicar a esta mensualidad
        $saldoPendiente = $mensualidadPendiente->monto_total - $mensualidadPendiente->monto_pagado;
        $montoAplicar = min($monto, $saldoPendiente);
        
        // Actualizar la mensualidad
        $nuevoMontoPagado = $mensualidadPendiente->monto_pagado + $montoAplicar;
        $nuevoEstatus = ($nuevoMontoPagado >= $mensualidadPendiente->monto_total) ? 'pagada' : 'parcial';
        
        $this->tablaModel->update($mensualidadPendiente->id, [
            'estatus' => $nuevoEstatus,
            'fecha_ultimo_pago' => $fechaPago,
            'monto_pagado' => $nuevoMontoPagado,
            'numero_pagos_aplicados' => $mensualidadPendiente->numero_pagos_aplicados + 1
        ]);

        log_message('info', "Pago aplicado a mensualidad {$mensualidadPendiente->numero_pago} de venta {$ventaId}: {$montoAplicar}");

        // Si sobra dinero y hay más mensualidades, aplicar recursivamente
        $sobrante = $monto - $montoAplicar;
        if ($sobrante > 0.01) { // Evitar recursión infinita con centavos
            $this->aplicarPagoATablaAmortizacion($ventaId, $sobrante, $fechaPago);
        }
    }

    /**
     * Registrar pago en historial (siguiendo el patrón de ventas)
     */
    private function registrarPagoEnHistorial(int $ventaId, float $monto, string $fechaPago, array $datos): void
    {
        // 1. Registrar en tabla ingresos
        $folioIngreso = 'REC-' . date('Ymd') . '-' . str_pad($this->db->table('ingresos')->countAll() + 1, 4, '0', STR_PAD_LEFT);
        
        $ingresoData = [
            'venta_id' => $ventaId,
            'folio' => $folioIngreso,
            'tipo_ingreso' => 'mensualidad',
            'monto' => $monto,
            'fecha_ingreso' => $fechaPago,
            'metodo_pago' => $datos['forma_pago'] ?? 'efectivo',
            'referencia' => $datos['referencia'] ?? '',
            'cliente_id' => $this->obtenerClienteIdDeVenta($ventaId),
            'user_id' => auth()->id() ?? 1
        ];

        $ingresoId = $this->db->table('ingresos')->insert($ingresoData);
        if (!$ingresoId) {
            log_message('error', "Error registrando ingreso para venta {$ventaId}");
            return;
        }

        // 2. Buscar la mensualidad recién actualizada en tabla_amortizacion
        $mensualidadPagada = $this->tablaModel
            ->where('venta_id', $ventaId)
            ->where('fecha_ultimo_pago', $fechaPago)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$mensualidadPagada) {
            log_message('warning', "No se encontró mensualidad actualizada para venta {$ventaId}");
            return;
        }

        // 3. Registrar en tabla pagos_ventas
        $folioPago = 'PAG-' . date('Ymd') . '-' . str_pad($this->db->table('pagos_ventas')->countAll() + 1, 4, '0', STR_PAD_LEFT);
        
        $pagoVentaData = [
            'folio_pago' => $folioPago,
            'venta_id' => $ventaId,
            'fecha_pago' => $fechaPago,
            'monto_pago' => $monto,
            'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
            'referencia_pago' => $datos['referencia'] ?? '',
            'concepto_pago' => 'mensualidad',
            'tabla_amortizacion_id' => $mensualidadPagada->id,
            'numero_mensualidad' => $mensualidadPagada->numero_pago
        ];

        $this->pagoVentaModel->insert($pagoVentaData);
        
        log_message('info', "Pago registrado en historial - Venta: {$ventaId}, Monto: {$monto}, Folio: {$folioIngreso}");
    }

    /**
     * Obtener cliente_id de una venta
     */
    private function obtenerClienteIdDeVenta(int $ventaId): ?int
    {
        $venta = $this->ventaModel->select('cliente_id')->find($ventaId);
        return $venta ? $venta->cliente_id : null;
    }
}