<?php

namespace App\Services;

use App\Models\VentaModel;
use App\Models\CuentaFinanciamientoModel;
use App\Models\MovimientoCuentaModel;
use App\Models\TablaAmortizacionModel;
use CodeIgniter\Database\BaseConnection;
use Exception;

/**
 * Service EstadoCuentaService
 * 
 * Servicio para generación de estados de cuenta
 * con información completa del cliente-lote
 */
class EstadoCuentaService
{
    protected VentaModel $ventaModel;
    protected CuentaFinanciamientoModel $cuentaModel;
    protected MovimientoCuentaModel $movimientoModel;
    protected TablaAmortizacionModel $tablaModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->cuentaModel = new CuentaFinanciamientoModel();
        $this->movimientoModel = new MovimientoCuentaModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Generar estado de cuenta completo
     */
    public function generarEstadoCuenta(int $ventaId, array $opciones = []): array
    {
        try {
            // Obtener información base usando helper
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }

            $historialCompleto = obtener_historial_pagos_lote($venta->cliente_id, $venta->lote_id);
            
            if (!$historialCompleto['success']) {
                throw new Exception($historialCompleto['error']);
            }

            // Obtener datos adicionales
            $datosExtendidos = $this->obtenerDatosExtendidos($ventaId, $opciones);

            // Generar análisis financiero
            $analisisFinanciero = $this->generarAnalisisFinanciero($historialCompleto);

            // Generar proyecciones
            $proyecciones = $this->generarProyecciones($historialCompleto);

            return [
                'success' => true,
                'estado_cuenta' => [
                    'informacion_base' => $historialCompleto['info_base'],
                    'resumen_financiero' => $historialCompleto['resumen_financiero'],
                    'historial_apartado' => $historialCompleto['historial_apartado'],
                    'historial_financiamiento' => $historialCompleto['historial_financiamiento'],
                    'datos_extendidos' => $datosExtendidos,
                    'analisis_financiero' => $analisisFinanciero,
                    'proyecciones' => $proyecciones,
                    'fecha_generacion' => date('Y-m-d H:i:s'),
                    'opciones' => $opciones
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando estado de cuenta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos extendidos para el estado de cuenta
     */
    private function obtenerDatosExtendidos(int $ventaId, array $opciones): array
    {
        $datos = [];

        // Tabla de amortización completa
        if ($opciones['incluir_tabla_amortizacion'] ?? true) {
            $datos['tabla_amortizacion'] = $this->obtenerTablaAmortizacion($ventaId);
        }

        // Historial de movimientos detallado
        if ($opciones['incluir_movimientos_detallados'] ?? true) {
            $datos['movimientos_detallados'] = $this->obtenerMovimientosDetallados($ventaId);
        }

        // Historial de refactorizaciones
        if ($opciones['incluir_refactorizaciones'] ?? true) {
            $datos['refactorizaciones'] = $this->obtenerRefactorizaciones($ventaId);
        }

        // Análisis de cumplimiento
        if ($opciones['incluir_analisis_cumplimiento'] ?? true) {
            $datos['analisis_cumplimiento'] = $this->analizarCumplimiento($ventaId);
        }

        return $datos;
    }

    /**
     * Obtener tabla de amortización
     */
    private function obtenerTablaAmortizacion(int $ventaId): array
    {
        try {
            $query = $this->db->query("
                SELECT 
                    ta.*,
                    cf.numero_cuenta,
                    cf.tasa_interes_anual
                FROM tabla_amortizacion ta
                JOIN cuentas_financiamiento cf ON cf.id = ta.cuenta_financiamiento_id
                WHERE cf.venta_id = ?
                ORDER BY ta.numero_pago ASC
            ", [$ventaId]);

            $tabla = $query->getResult();

            return [
                'mensualidades' => $tabla ?: [],
                'total_mensualidades' => count($tabla),
                'mensualidades_pagadas' => count(array_filter($tabla, fn($m) => $m->estatus === 'pagada')),
                'mensualidades_pendientes' => count(array_filter($tabla, fn($m) => $m->estatus === 'pendiente')),
                'mensualidades_vencidas' => count(array_filter($tabla, fn($m) => $m->estatus === 'vencida'))
            ];

        } catch (Exception $e) {
            return [
                'mensualidades' => [],
                'error' => 'Error obteniendo tabla de amortización: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener movimientos detallados
     */
    private function obtenerMovimientosDetallados(int $ventaId): array
    {
        try {
            $query = $this->db->query("
                SELECT 
                    mc.*,
                    cf.numero_cuenta,
                    u.username as usuario_nombre
                FROM movimientos_cuenta mc
                JOIN cuentas_financiamiento cf ON cf.id = mc.cuenta_financiamiento_id
                LEFT JOIN users u ON u.id = mc.usuario_id
                WHERE cf.venta_id = ?
                ORDER BY mc.fecha_movimiento DESC
                LIMIT 100
            ", [$ventaId]);

            $movimientos = $query->getResult();

            return [
                'movimientos' => $movimientos ?: [],
                'total_movimientos' => count($movimientos),
                'tipos_movimiento' => $this->contarTiposMovimiento($movimientos)
            ];

        } catch (Exception $e) {
            return [
                'movimientos' => [],
                'error' => 'Error obteniendo movimientos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener historial de refactorizaciones
     */
    private function obtenerRefactorizaciones(int $ventaId): array
    {
        try {
            $cuenta = $this->cuentaModel->where('venta_id', $ventaId)->first();
            if (!$cuenta) {
                return ['refactorizaciones' => [], 'total' => 0];
            }

            $refactorizaciones = obtener_historial_refactorizaciones($cuenta->id);
            return $refactorizaciones;

        } catch (Exception $e) {
            return [
                'refactorizaciones' => [],
                'error' => 'Error obteniendo refactorizaciones: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analizar cumplimiento de pagos
     */
    private function analizarCumplimiento(int $ventaId): array
    {
        try {
            $cuenta = $this->cuentaModel->where('venta_id', $ventaId)->first();
            if (!$cuenta) {
                return ['cumplimiento' => 'Sin información'];
            }

            $totalPagos = $cuenta->pagos_realizados + $cuenta->pagos_vencidos;
            $porcentajeCumplimiento = $totalPagos > 0 ? ($cuenta->pagos_realizados / $totalPagos) * 100 : 0;

            $nivel = 'Excelente';
            if ($porcentajeCumplimiento < 95) $nivel = 'Bueno';
            if ($porcentajeCumplimiento < 85) $nivel = 'Regular';
            if ($porcentajeCumplimiento < 70) $nivel = 'Malo';

            return [
                'porcentaje_cumplimiento' => $porcentajeCumplimiento,
                'nivel_cumplimiento' => $nivel,
                'pagos_realizados' => $cuenta->pagos_realizados,
                'pagos_vencidos' => $cuenta->pagos_vencidos,
                'dias_promedio_atraso' => $this->calcularDiasPromedioAtraso($ventaId),
                'racha_pagos_puntuales' => $this->calcularRachaPagos($ventaId)
            ];

        } catch (Exception $e) {
            return [
                'cumplimiento' => 'Error calculando cumplimiento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar análisis financiero
     */
    private function generarAnalisisFinanciero(array $historial): array
    {
        $resumen = $historial['resumen_financiero'];
        $cuenta = $historial['historial_financiamiento']['cuenta'] ?? null;

        $analisis = [
            'comportamiento_pago' => $this->analizarComportamientoPago($historial),
            'riesgo_crediticio' => $this->evaluarRiesgoCrediticio($historial),
            'recomendaciones' => $this->generarRecomendaciones($historial),
            'metricas_clave' => [
                'porcentaje_avance' => $resumen['porcentaje_avance'],
                'porcentaje_enganche' => $resumen['porcentaje_enganche'],
                'al_corriente' => $resumen['al_corriente'],
                'total_pagado' => $resumen['total_pagado'],
                'saldo_pendiente' => $resumen['saldo_total_pendiente']
            ]
        ];

        if ($cuenta) {
            $analisis['metricas_financieras'] = [
                'roi_cliente' => $this->calcularROICliente($cuenta),
                'eficiencia_pago' => $this->calcularEficienciaPago($cuenta),
                'score_financiero' => $this->calcularScoreFinanciero($historial)
            ];
        }

        return $analisis;
    }

    /**
     * Generar proyecciones
     */
    private function generarProyecciones(array $historial): array
    {
        $cuenta = $historial['historial_financiamiento']['cuenta'] ?? null;
        
        if (!$cuenta) {
            return ['proyecciones' => 'No disponible sin cuenta de financiamiento'];
        }

        return [
            'fecha_liquidacion_estimada' => $this->calcularFechaLiquidacion($cuenta),
            'total_intereses_proyectados' => $this->calcularInteresesProyectados($cuenta),
            'escenarios_abono' => $this->generarEscenariosAbono($cuenta),
            'impacto_refactorizacion' => $this->calcularImpactoRefactorizacion($cuenta)
        ];
    }

    /**
     * Contar tipos de movimiento
     */
    private function contarTiposMovimiento(array $movimientos): array
    {
        $tipos = [];
        foreach ($movimientos as $mov) {
            $tipos[$mov->tipo_movimiento] = ($tipos[$mov->tipo_movimiento] ?? 0) + 1;
        }
        return $tipos;
    }

    /**
     * Calcular días promedio de atraso
     */
    private function calcularDiasPromedioAtraso(int $ventaId): float
    {
        try {
            $query = $this->db->query("
                SELECT AVG(dias_atraso) as promedio_atraso
                FROM tabla_amortizacion ta
                JOIN cuentas_financiamiento cf ON cf.id = ta.cuenta_financiamiento_id
                WHERE cf.venta_id = ? AND ta.dias_atraso > 0
            ", [$ventaId]);

            $resultado = $query->getRow();
            return $resultado ? (float)$resultado->promedio_atraso : 0;

        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Calcular racha de pagos puntuales
     */
    private function calcularRachaPagos(int $ventaId): int
    {
        try {
            $query = $this->db->query("
                SELECT COUNT(*) as racha
                FROM (
                    SELECT dias_atraso,
                           @racha := CASE WHEN dias_atraso = 0 THEN @racha + 1 ELSE 0 END as racha_actual
                    FROM tabla_amortizacion ta
                    JOIN cuentas_financiamiento cf ON cf.id = ta.cuenta_financiamiento_id
                    CROSS JOIN (SELECT @racha := 0) r
                    WHERE cf.venta_id = ? AND ta.estatus = 'pagada'
                    ORDER BY ta.numero_pago DESC
                ) subconsulta
                WHERE racha_actual > 0
            ", [$ventaId]);

            $resultado = $query->getRow();
            return $resultado ? (int)$resultado->racha : 0;

        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Analizar comportamiento de pago
     */
    private function analizarComportamientoPago(array $historial): array
    {
        $cuenta = $historial['historial_financiamiento']['cuenta'] ?? null;
        
        if (!$cuenta) {
            return ['comportamiento' => 'Sin información'];
        }

        $comportamiento = 'Excelente';
        if ($cuenta->pagos_vencidos > 0) $comportamiento = 'Irregular';
        if ($cuenta->pagos_vencidos > 2) $comportamiento = 'Problemático';

        return [
            'nivel' => $comportamiento,
            'pagos_puntuales' => $cuenta->pagos_realizados - $cuenta->pagos_vencidos,
            'pagos_tardios' => $cuenta->pagos_vencidos,
            'patron_pago' => $this->determinarPatronPago($cuenta)
        ];
    }

    /**
     * Evaluar riesgo crediticio
     */
    private function evaluarRiesgoCrediticio(array $historial): array
    {
        $resumen = $historial['resumen_financiero'];
        $cuenta = $historial['historial_financiamiento']['cuenta'] ?? null;

        $riesgo = 'Bajo';
        $puntuacion = 100;

        if (!$resumen['al_corriente']) {
            $riesgo = 'Medio';
            $puntuacion -= 30;
        }

        if ($cuenta && $cuenta->pagos_vencidos > 2) {
            $riesgo = 'Alto';
            $puntuacion -= 50;
        }

        return [
            'nivel' => $riesgo,
            'puntuacion' => max(0, $puntuacion),
            'factores' => [
                'al_corriente' => $resumen['al_corriente'],
                'porcentaje_avance' => $resumen['porcentaje_avance'],
                'comportamiento_historico' => $cuenta ? $cuenta->pagos_vencidos : 0
            ]
        ];
    }

    /**
     * Generar recomendaciones
     */
    private function generarRecomendaciones(array $historial): array
    {
        $recomendaciones = [];
        $resumen = $historial['resumen_financiero'];
        $cuenta = $historial['historial_financiamiento']['cuenta'] ?? null;

        if ($resumen['saldo_enganche'] > 0) {
            $recomendaciones[] = [
                'tipo' => 'urgente',
                'titulo' => 'Completar Enganche',
                'descripcion' => 'Liquidar saldo pendiente de enganche por $' . number_format($resumen['saldo_enganche'], 2)
            ];
        }

        if (!$resumen['al_corriente']) {
            $recomendaciones[] = [
                'tipo' => 'importante',
                'titulo' => 'Ponerse al Corriente',
                'descripcion' => 'Regularizar pagos vencidos para evitar intereses moratorios'
            ];
        }

        if ($cuenta && $cuenta->saldo_capital > 50000) {
            $recomendaciones[] = [
                'tipo' => 'oportunidad',
                'titulo' => 'Considerar Abono a Capital',
                'descripcion' => 'Evaluar abono a capital para reducir intereses totales'
            ];
        }

        return $recomendaciones;
    }

    /**
     * Calcular ROI del cliente
     */
    private function calcularROICliente($cuenta): float
    {
        if ($cuenta->capital_inicial <= 0) return 0;
        
        $capitalPagado = $cuenta->capital_inicial - $cuenta->saldo_capital;
        return ($capitalPagado / $cuenta->capital_inicial) * 100;
    }

    /**
     * Calcular eficiencia de pago
     */
    private function calcularEficienciaPago($cuenta): float
    {
        $totalPagos = $cuenta->pagos_realizados + $cuenta->pagos_vencidos;
        if ($totalPagos <= 0) return 0;
        
        return ($cuenta->pagos_realizados / $totalPagos) * 100;
    }

    /**
     * Calcular score financiero
     */
    private function calcularScoreFinanciero(array $historial): int
    {
        $score = 500; // Base
        $resumen = $historial['resumen_financiero'];
        $cuenta = $historial['historial_financiamiento']['cuenta'] ?? null;

        // Factores positivos
        if ($resumen['al_corriente']) $score += 100;
        if ($resumen['porcentaje_avance'] > 50) $score += 50;
        if ($resumen['porcentaje_enganche'] >= 100) $score += 50;

        // Factores negativos
        if ($cuenta && $cuenta->pagos_vencidos > 0) $score -= $cuenta->pagos_vencidos * 25;
        if ($resumen['saldo_enganche'] > 0) $score -= 50;

        return max(300, min(850, $score));
    }

    /**
     * Determinar patrón de pago
     */
    private function determinarPatronPago($cuenta): string
    {
        if ($cuenta->pagos_vencidos == 0) return 'Siempre puntual';
        if ($cuenta->pagos_vencidos <= 2) return 'Ocasionalmente tardío';
        if ($cuenta->pagos_vencidos <= 5) return 'Frecuentemente tardío';
        return 'Irregular';
    }

    /**
     * Calcular fecha de liquidación estimada
     */
    private function calcularFechaLiquidacion($cuenta): string
    {
        if ($cuenta->meses_restantes <= 0) return 'Ya liquidado';
        
        $fecha = new \DateTime($cuenta->fecha_ultimo_pago ?: $cuenta->fecha_primer_pago);
        $fecha->add(new \DateInterval("P{$cuenta->meses_restantes}M"));
        
        return $fecha->format('Y-m-d');
    }

    /**
     * Calcular intereses proyectados
     */
    private function calcularInteresesProyectados($cuenta): float
    {
        $escenario = calcular_escenario_actual($cuenta);
        return $escenario['total_intereses'];
    }

    /**
     * Generar escenarios de abono
     */
    private function generarEscenariosAbono($cuenta): array
    {
        $escenarios = [];
        $montos = [10000, 25000, 50000, 100000];
        
        foreach ($montos as $monto) {
            if ($monto <= $cuenta->saldo_capital) {
                $simulacion = simular_abono_capital($cuenta->id, $monto);
                if ($simulacion['success']) {
                    $escenarios[] = [
                        'monto_abono' => $monto,
                        'ahorro_intereses' => $simulacion['beneficios']['ahorro_total_intereses'],
                        'nueva_mensualidad' => $simulacion['beneficios']['nueva_mensualidad'],
                        'recomendacion' => $simulacion['recomendacion']
                    ];
                }
            }
        }
        
        return $escenarios;
    }

    /**
     * Calcular impacto de refactorización
     */
    private function calcularImpactoRefactorizacion($cuenta): array
    {
        if ($cuenta->refactorizaciones_aplicadas == 0) {
            return ['impacto' => 'Sin refactorizaciones aplicadas'];
        }

        $historial = obtener_historial_refactorizaciones($cuenta->id);
        $totalAhorro = array_sum(array_column($historial['refactorizaciones'], 'ahorro_intereses'));
        $totalAbonos = array_sum(array_column($historial['refactorizaciones'], 'abono_capital'));

        return [
            'total_refactorizaciones' => $cuenta->refactorizaciones_aplicadas,
            'total_ahorro_intereses' => $totalAhorro,
            'total_abonos_capital' => $totalAbonos,
            'eficiencia' => $totalAbonos > 0 ? ($totalAhorro / $totalAbonos) * 100 : 0
        ];
    }
}