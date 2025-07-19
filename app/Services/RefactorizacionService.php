<?php

namespace App\Services;

use App\Models\CuentaFinanciamientoModel;
use App\Models\MovimientoCuentaModel;
use App\Models\TablaAmortizacionModel;
use App\Entities\MovimientoCuenta;
use CodeIgniter\Database\BaseConnection;
use Exception;

/**
 * Service RefactorizacionService
 * 
 * Servicio especializado para refactorización de tablas de amortización
 * por abonos a capital con cálculo automático de nueva mensualidad
 */
class RefactorizacionService
{
    protected CuentaFinanciamientoModel $cuentaModel;
    protected MovimientoCuentaModel $movimientoModel;
    protected TablaAmortizacionModel $tablaModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->cuentaModel = new CuentaFinanciamientoModel();
        $this->movimientoModel = new MovimientoCuentaModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Ejecutar refactorización completa
     */
    public function ejecutarRefactorizacion(int $cuentaId, float $abonoCapital, array $opciones = []): array
    {
        $this->db->transBegin();

        try {
            // Obtener cuenta
            $cuenta = $this->cuentaModel->find($cuentaId);
            if (!$cuenta) {
                throw new Exception('Cuenta no encontrada');
            }

            // Validar estado
            if ($cuenta->estado !== 'activa') {
                throw new Exception('La cuenta debe estar activa para refactorizar');
            }

            // Validar monto
            if ($abonoCapital <= 0 || $abonoCapital > $cuenta->saldo_capital) {
                throw new Exception('Monto de abono inválido');
            }

            // Calcular escenario actual
            $escenarioActual = $this->calcularEscenarioActual($cuenta);

            // Aplicar abono a capital
            $saldoAnterior = [
                'capital' => $cuenta->saldo_capital,
                'interes' => $cuenta->saldo_interes,
                'moratorio' => $cuenta->saldo_moratorio,
                'total' => $cuenta->saldo_total
            ];

            $cuenta->saldo_capital -= $abonoCapital;
            $cuenta->saldo_total -= $abonoCapital;
            $cuenta->total_abonos_capital += $abonoCapital;

            // Calcular nueva tabla de amortización
            $nuevaTabla = $this->calcularNuevaTabla($cuenta, $opciones);

            // Actualizar mensualidad si se especifica
            if ($opciones['reducir_mensualidad'] ?? true) {
                $cuenta->monto_mensualidad = $nuevaTabla['nueva_mensualidad'];
            } else {
                // Mantener mensualidad, reducir plazo
                $cuenta->meses_restantes = $nuevaTabla['nuevo_plazo'];
            }

            $cuenta->refactorizaciones_aplicadas++;
            $this->cuentaModel->save($cuenta);

            // Actualizar tabla de amortización
            $this->actualizarTablaAmortizacion($cuenta, $nuevaTabla);

            // Crear movimiento de refactorización
            $movimiento = $this->crearMovimientoRefactorizacion(
                $cuenta,
                $saldoAnterior,
                $abonoCapital,
                $nuevaTabla,
                $escenarioActual
            );

            $this->db->transCommit();

            return [
                'success' => true,
                'cuenta_id' => $cuenta->id,
                'abono_capital' => $abonoCapital,
                'movimiento_id' => $movimiento->id,
                'escenario_anterior' => $escenarioActual,
                'escenario_nuevo' => [
                    'saldo_capital' => $cuenta->saldo_capital,
                    'mensualidad' => $cuenta->monto_mensualidad,
                    'meses_restantes' => $cuenta->meses_restantes,
                    'total_a_pagar' => $cuenta->saldo_capital + $nuevaTabla['total_intereses_restantes']
                ],
                'beneficios' => [
                    'ahorro_intereses' => $escenarioActual['total_intereses_restantes'] - $nuevaTabla['total_intereses_restantes'],
                    'reduccion_mensualidad' => $escenarioActual['mensualidad'] - $cuenta->monto_mensualidad,
                    'reduccion_plazo' => $escenarioActual['meses_restantes'] - $cuenta->meses_restantes
                ],
                'mensaje' => 'Refactorización ejecutada exitosamente'
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            return [
                'success' => false,
                'error' => 'Error ejecutando refactorización: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Simular refactorización sin aplicar cambios
     */
    public function simularRefactorizacion(int $cuentaId, float $abonoCapital, array $opciones = []): array
    {
        try {
            $cuenta = $this->cuentaModel->find($cuentaId);
            if (!$cuenta) {
                throw new Exception('Cuenta no encontrada');
            }

            // Validar monto
            if ($abonoCapital <= 0 || $abonoCapital > $cuenta->saldo_capital) {
                throw new Exception('Monto de abono inválido');
            }

            // Calcular escenario actual
            $escenarioActual = $this->calcularEscenarioActual($cuenta);

            // Simular nuevo saldo
            $nuevoSaldoCapital = $cuenta->saldo_capital - $abonoCapital;

            // Calcular nueva tabla simulada
            $nuevaTabla = calcular_nueva_tabla_amortizacion(
                $nuevoSaldoCapital,
                $cuenta->tasa_interes_anual,
                $cuenta->meses_restantes
            );

            if (!$nuevaTabla['success']) {
                throw new Exception($nuevaTabla['error']);
            }

            // Calcular beneficios
            $ahorroIntereses = $escenarioActual['total_intereses_restantes'] - $nuevaTabla['total_intereses'];
            $reduccionMensualidad = $escenarioActual['mensualidad'] - $nuevaTabla['nueva_mensualidad'];

            // Generar recomendación
            $recomendacion = $this->generarRecomendacion($ahorroIntereses, $abonoCapital, $reduccionMensualidad);

            return [
                'success' => true,
                'simulacion' => [
                    'abono_capital' => $abonoCapital,
                    'escenario_actual' => $escenarioActual,
                    'escenario_nuevo' => [
                        'saldo_capital' => $nuevoSaldoCapital,
                        'mensualidad' => $nuevaTabla['nueva_mensualidad'],
                        'meses_restantes' => $cuenta->meses_restantes,
                        'total_a_pagar' => $nuevoSaldoCapital + $nuevaTabla['total_intereses']
                    ],
                    'beneficios' => [
                        'ahorro_intereses' => $ahorroIntereses,
                        'reduccion_mensualidad' => $reduccionMensualidad,
                        'ahorro_total' => $ahorroIntereses + $abonoCapital,
                        'porcentaje_ahorro' => ($ahorroIntereses / $abonoCapital) * 100
                    ],
                    'recomendacion' => $recomendacion
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error simulando refactorización: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener historial de refactorizaciones
     */
    public function obtenerHistorialRefactorizaciones(int $cuentaId): array
    {
        try {
            return obtener_historial_refactorizaciones($cuentaId);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error obteniendo historial: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcular escenario actual de la cuenta
     */
    private function calcularEscenarioActual($cuenta): array
    {
        $tasaMensual = $cuenta->tasa_interes_anual / 12 / 100;
        $totalIntereses = 0;
        $saldoActual = $cuenta->saldo_capital;

        // Calcular intereses restantes con mensualidad actual
        for ($i = 1; $i <= $cuenta->meses_restantes; $i++) {
            $interes = $saldoActual * $tasaMensual;
            $capital = $cuenta->monto_mensualidad - $interes;
            
            if ($capital > $saldoActual) {
                $capital = $saldoActual;
                $interes = $cuenta->monto_mensualidad - $capital;
            }
            
            $saldoActual -= $capital;
            $totalIntereses += $interes;
            
            if ($saldoActual <= 0) break;
        }

        return [
            'saldo_capital' => $cuenta->saldo_capital,
            'mensualidad' => $cuenta->monto_mensualidad,
            'meses_restantes' => $cuenta->meses_restantes,
            'total_intereses_restantes' => $totalIntereses,
            'total_a_pagar' => $cuenta->saldo_capital + $totalIntereses
        ];
    }

    /**
     * Calcular nueva tabla de amortización
     */
    private function calcularNuevaTabla($cuenta, array $opciones): array
    {
        $resultado = calcular_nueva_tabla_amortizacion(
            $cuenta->saldo_capital,
            $cuenta->tasa_interes_anual,
            $cuenta->meses_restantes
        );

        if (!$resultado['success']) {
            throw new Exception($resultado['error']);
        }

        $resultado['total_intereses_restantes'] = $resultado['total_intereses'];
        return $resultado;
    }

    /**
     * Actualizar tabla de amortización existente
     */
    private function actualizarTablaAmortizacion($cuenta, array $nuevaTabla): void
    {
        $resultado = actualizar_tabla_amortizacion_existente(
            $cuenta->venta_id,
            $nuevaTabla,
            $cuenta->meses_transcurridos
        );

        if (!$resultado['success']) {
            throw new Exception($resultado['error']);
        }
    }

    /**
     * Crear movimiento de refactorización
     */
    private function crearMovimientoRefactorizacion($cuenta, array $saldoAnterior, float $abonoCapital, array $nuevaTabla, array $escenarioActual): MovimientoCuenta
    {
        $movimiento = MovimientoCuenta::crearRefactorizacion([
            'cuenta_id' => $cuenta->id,
            'saldo_anterior' => $saldoAnterior,
            'saldo_nuevo' => [
                'capital' => $cuenta->saldo_capital,
                'interes' => $cuenta->saldo_interes,
                'moratorio' => $cuenta->saldo_moratorio,
                'total' => $cuenta->saldo_total
            ],
            'abono_capital' => $abonoCapital,
            'mensualidad_anterior' => $escenarioActual['mensualidad'],
            'nueva_mensualidad' => $nuevaTabla['nueva_mensualidad'],
            'ahorro_intereses' => $escenarioActual['total_intereses_restantes'] - $nuevaTabla['total_intereses'],
            'usuario_id' => auth()->id() ?? 1
        ]);

        $this->movimientoModel->save($movimiento);
        return $movimiento;
    }

    /**
     * Generar recomendación sobre la refactorización
     */
    private function generarRecomendacion(float $ahorroIntereses, float $abonoCapital, float $reduccionMensualidad): array
    {
        $porcentajeAhorro = $abonoCapital > 0 ? ($ahorroIntereses / $abonoCapital) * 100 : 0;

        if ($porcentajeAhorro >= 25) {
            return [
                'tipo' => 'excelente',
                'icono' => 'fas fa-star',
                'color' => 'success',
                'titulo' => 'Excelente Inversión',
                'mensaje' => sprintf(
                    'Ahorro del %.1f%% en intereses. Reducción mensual de $%s.',
                    $porcentajeAhorro,
                    number_format($reduccionMensualidad, 2)
                ),
                'puntuacion' => 5
            ];
        } elseif ($porcentajeAhorro >= 15) {
            return [
                'tipo' => 'buena',
                'icono' => 'fas fa-thumbs-up',
                'color' => 'info',
                'titulo' => 'Buena Inversión',
                'mensaje' => sprintf(
                    'Ahorro del %.1f%% en intereses. Reducción mensual de $%s.',
                    $porcentajeAhorro,
                    number_format($reduccionMensualidad, 2)
                ),
                'puntuacion' => 4
            ];
        } elseif ($porcentajeAhorro >= 8) {
            return [
                'tipo' => 'aceptable',
                'icono' => 'fas fa-check',
                'color' => 'warning',
                'titulo' => 'Inversión Aceptable',
                'mensaje' => sprintf(
                    'Ahorro del %.1f%% en intereses. Considerar alternativas.',
                    $porcentajeAhorro
                ),
                'puntuacion' => 3
            ];
        } else {
            return [
                'tipo' => 'baja',
                'icono' => 'fas fa-info-circle',
                'color' => 'secondary',
                'titulo' => 'Bajo Impacto',
                'mensaje' => sprintf(
                    'Ahorro del %.1f%% en intereses. Evaluar otras opciones.',
                    $porcentajeAhorro
                ),
                'puntuacion' => 2
            ];
        }
    }

    /**
     * Generar reporte de refactorización
     */
    public function generarReporte(int $cuentaId, int $refactorizacionId = null): array
    {
        try {
            $cuenta = $this->cuentaModel->find($cuentaId);
            if (!$cuenta) {
                throw new Exception('Cuenta no encontrada');
            }

            // Obtener historial de refactorizaciones
            $historial = $this->obtenerHistorialRefactorizaciones($cuentaId);

            // Calcular estadísticas
            $estadisticas = $this->calcularEstadisticasRefactorizacion($historial['refactorizaciones']);

            return [
                'success' => true,
                'cuenta' => [
                    'numero' => $cuenta->numero_cuenta,
                    'saldo_actual' => $cuenta->saldo_capital,
                    'mensualidad_actual' => $cuenta->monto_mensualidad,
                    'refactorizaciones_aplicadas' => $cuenta->refactorizaciones_aplicadas
                ],
                'historial' => $historial['refactorizaciones'],
                'estadisticas' => $estadisticas,
                'fecha_generacion' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcular estadísticas de refactorización
     */
    private function calcularEstadisticasRefactorizacion(array $refactorizaciones): array
    {
        if (empty($refactorizaciones)) {
            return [
                'total_refactorizaciones' => 0,
                'total_abonos_capital' => 0,
                'total_ahorro_intereses' => 0,
                'promedio_ahorro' => 0
            ];
        }

        $totalAbonos = array_sum(array_column($refactorizaciones, 'abono_capital'));
        $totalAhorro = array_sum(array_column($refactorizaciones, 'ahorro_intereses'));

        return [
            'total_refactorizaciones' => count($refactorizaciones),
            'total_abonos_capital' => $totalAbonos,
            'total_ahorro_intereses' => $totalAhorro,
            'promedio_ahorro' => count($refactorizaciones) > 0 ? $totalAhorro / count($refactorizaciones) : 0,
            'porcentaje_ahorro_total' => $totalAbonos > 0 ? ($totalAhorro / $totalAbonos) * 100 : 0
        ];
    }
}