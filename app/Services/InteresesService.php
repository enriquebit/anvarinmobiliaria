<?php

namespace App\Services;

use App\Models\PlanPagoModel;
use App\Models\CobranzaInteresModel;
use App\Models\ConfiguracionCobranzaModel;
use App\Models\HistorialCobranzaModel;
use App\Entities\PlanPago;
use App\Entities\CobranzaInteres;
use App\Entities\ConfiguracionCobranza;
use App\Entities\HistorialCobranza;
use App\Services\VentaCalculoService;

/**
 * InteresesService
 * 
 * Servicio para el cálculo automático de intereses moratorios
 * Replica la lógica legacy de cálculo de intereses por atraso
 */
class InteresesService
{
    protected PlanPagoModel $planPagoModel;
    protected CobranzaInteresModel $interesModel;
    protected ConfiguracionCobranzaModel $configuracionModel;
    protected HistorialCobranzaModel $historialModel;
    protected VentaCalculoService $calculoService;

    public function __construct()
    {
        $this->planPagoModel = new PlanPagoModel();
        $this->interesModel = new CobranzaInteresModel();
        $this->configuracionModel = new ConfiguracionCobranzaModel();
        $this->historialModel = new HistorialCobranzaModel();
        $this->calculoService = new VentaCalculoService();
    }

    /**
     * Calcular intereses moratorios automáticamente
     */
    public function calcularInteresesMoratorios(int $diasGracia = 5): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Obtener planes de pago vencidos
            $planesVencidos = $this->obtenerPlanesVencidos($diasGracia);
            
            $interesesCalculados = [];
            $totalIntereses = 0;

            foreach ($planesVencidos as $plan) {
                // Verificar si ya se calculó interés para este período
                if ($this->yaSeCalculoInteresHoy($plan->id)) {
                    continue;
                }

                // Obtener configuración de cobranza
                $configuracion = $this->obtenerConfiguracionCobranza($plan->proyectos_id);
                
                if (!$configuracion->cobrar_interes_moratorio) {
                    continue;
                }

                // Calcular días de atraso
                $diasAtraso = $this->calcularDiasAtraso($plan->fecha_vencimiento);
                
                if ($diasAtraso <= $diasGracia) {
                    continue;
                }

                // Calcular interés
                $montoInteres = $this->calcularMontoInteres($plan, $configuracion, $diasAtraso);
                
                if ($montoInteres <= 0) {
                    continue;
                }

                // Registrar interés
                $interesId = $this->registrarInteres($plan, $montoInteres, $diasAtraso);
                
                // Actualizar plan de pago
                $this->aplicarInteresAPlan($plan, $montoInteres);
                
                // Registrar en historial
                $this->registrarHistorial($plan, $montoInteres, $diasAtraso);

                $interesesCalculados[] = [
                    'plan_pago_id' => $plan->id,
                    'dias_atraso' => $diasAtraso,
                    'monto_interes' => $montoInteres,
                    'interes_id' => $interesId,
                ];

                $totalIntereses += $montoInteres;
            }

            $db->transComplete();

            return [
                'success' => true,
                'planes_procesados' => count($planesVencidos),
                'intereses_calculados' => count($interesesCalculados),
                'total_intereses' => $totalIntereses,
                'detalle' => $interesesCalculados,
                'message' => 'Intereses moratorios calculados exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => 'Error al calcular intereses: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcular interés específico para un plan de pago
     */
    public function calcularInteresEspecifico(int $planPagoId, bool $forzar = false): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $plan = $this->planPagoModel->find($planPagoId);
            
            if (!$plan) {
                throw new \Exception('Plan de pago no encontrado');
            }

            if ($plan->cobrado && !$forzar) {
                throw new \Exception('El plan de pago ya está cobrado');
            }

            // Verificar si ya se calculó interés hoy
            if (!$forzar && $this->yaSeCalculoInteresHoy($planPagoId)) {
                throw new \Exception('Ya se calculó interés para este plan hoy');
            }

            $configuracion = $this->obtenerConfiguracionCobranza($plan->proyectos_id);
            
            if (!$configuracion->cobrar_interes_moratorio) {
                throw new \Exception('No está configurado el cobro de intereses para este proyecto');
            }

            $diasAtraso = $this->calcularDiasAtraso($plan->fecha_vencimiento);
            
            if ($diasAtraso <= 0) {
                throw new \Exception('El plan de pago no está vencido');
            }

            $montoInteres = $this->calcularMontoInteres($plan, $configuracion, $diasAtraso);
            
            if ($montoInteres <= 0) {
                throw new \Exception('No se generó interés para este plan');
            }

            // Registrar interés
            $interesId = $this->registrarInteres($plan, $montoInteres, $diasAtraso);
            
            // Actualizar plan de pago
            $this->aplicarInteresAPlan($plan, $montoInteres);
            
            // Registrar en historial
            $this->registrarHistorial($plan, $montoInteres, $diasAtraso);

            $db->transComplete();

            return [
                'success' => true,
                'interes_id' => $interesId,
                'monto_interes' => $montoInteres,
                'dias_atraso' => $diasAtraso,
                'nuevo_total' => $plan->total + $montoInteres,
                'message' => 'Interés calculado exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => 'Error al calcular interés: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener historial de intereses de un plan de pago
     */
    public function obtenerHistorialIntereses(int $planPagoId): array
    {
        $intereses = $this->interesModel
            ->where('plan_pago_id', $planPagoId)
            ->orderBy('fecha_calculo', 'DESC')
            ->findAll();

        $resumen = [
            'total_intereses' => count($intereses),
            'monto_total' => 0,
            'ultimo_calculo' => null,
            'dias_atraso_acumulados' => 0,
        ];

        foreach ($intereses as $interes) {
            $resumen['monto_total'] += $interes->monto;
            $resumen['dias_atraso_acumulados'] += $interes->dias_atraso;
            
            if (!$resumen['ultimo_calculo']) {
                $resumen['ultimo_calculo'] = $interes->fecha_calculo;
            }
        }

        return [
            'intereses' => $intereses,
            'resumen' => $resumen,
        ];
    }

    /**
     * Generar reporte de intereses
     */
    public function generarReporteIntereses(array $filtros = []): array
    {
        $builder = $this->interesModel->builder();

        // Aplicar filtros
        if (!empty($filtros['fecha_inicio'])) {
            $builder->where('fecha_calculo >=', $filtros['fecha_inicio']);
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $builder->where('fecha_calculo <=', $filtros['fecha_fin']);
        }

        if (!empty($filtros['proyecto_id'])) {
            $builder->where('proyectos_id', $filtros['proyecto_id']);
        }

        if (!empty($filtros['vendedor_id'])) {
            $builder->where('vendedor_id', $filtros['vendedor_id']);
        }

        $intereses = $builder->get()->getResult();

        $totales = [
            'cantidad_intereses' => count($intereses),
            'monto_total' => 0,
            'dias_atraso_promedio' => 0,
            'por_proyecto' => [],
            'por_vendedor' => [],
        ];

        $totalDias = 0;

        foreach ($intereses as $interes) {
            $totales['monto_total'] += $interes->monto;
            $totalDias += $interes->dias_atraso;
            
            // Agrupar por proyecto
            if (!isset($totales['por_proyecto'][$interes->proyectos_id])) {
                $totales['por_proyecto'][$interes->proyectos_id] = [
                    'cantidad' => 0,
                    'monto' => 0,
                ];
            }
            
            $totales['por_proyecto'][$interes->proyectos_id]['cantidad']++;
            $totales['por_proyecto'][$interes->proyectos_id]['monto'] += $interes->monto;
            
            // Agrupar por vendedor
            if (!isset($totales['por_vendedor'][$interes->vendedor_id])) {
                $totales['por_vendedor'][$interes->vendedor_id] = [
                    'cantidad' => 0,
                    'monto' => 0,
                ];
            }
            
            $totales['por_vendedor'][$interes->vendedor_id]['cantidad']++;
            $totales['por_vendedor'][$interes->vendedor_id]['monto'] += $interes->monto;
        }

        if (count($intereses) > 0) {
            $totales['dias_atraso_promedio'] = $totalDias / count($intereses);
        }

        return [
            'intereses' => $intereses,
            'totales' => $totales,
            'filtros_aplicados' => $filtros,
        ];
    }

    /**
     * Condonar intereses de un plan de pago
     */
    public function condonarIntereses(int $planPagoId, string $motivo = ''): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $plan = $this->planPagoModel->find($planPagoId);
            
            if (!$plan) {
                throw new \Exception('Plan de pago no encontrado');
            }

            if ($plan->interes <= 0) {
                throw new \Exception('El plan de pago no tiene intereses para condonar');
            }

            $interesCondonado = $plan->interes;

            // Actualizar plan de pago
            $plan->condonarInteres();
            $this->planPagoModel->save($plan);

            // Registrar condonación en historial
            $historialData = [
                'plan_pago_id' => $planPagoId,
                'ventas_id' => $plan->ventas_id,
                'proyectos_id' => $plan->proyectos_id,
                'vendedor_id' => $plan->vendedor_id,
                'usuario_id' => auth()->id(),
                'accion' => 'condonacion_interes',
                'monto_anterior' => $plan->total + $interesCondonado,
                'monto_nuevo' => $plan->total,
                'diferencia' => -$interesCondonado,
                'observaciones' => $motivo,
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s'),
            ];

            $historial = new HistorialCobranza($historialData);
            $this->historialModel->insert($historial);

            $db->transComplete();

            return [
                'success' => true,
                'interes_condonado' => $interesCondonado,
                'nuevo_total' => $plan->total,
                'message' => 'Intereses condonados exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => 'Error al condonar intereses: ' . $e->getMessage()
            ];
        }
    }

    /**
     * MÉTODOS PRIVADOS
     */

    private function obtenerPlanesVencidos(int $diasGracia): array
    {
        $fechaLimite = date('Y-m-d', strtotime("-$diasGracia days"));
        
        return $this->planPagoModel
            ->where('fecha_vencimiento <', $fechaLimite)
            ->where('cobrado', false)
            ->where('estatus', 1)
            ->findAll();
    }

    private function yaSeCalculoInteresHoy(int $planPagoId): bool
    {
        $interesHoy = $this->interesModel
            ->where('plan_pago_id', $planPagoId)
            ->where('fecha_calculo', date('Y-m-d'))
            ->first();

        return $interesHoy !== null;
    }

    private function obtenerConfiguracionCobranza(int $proyectoId): ConfiguracionCobranza
    {
        $configuracion = $this->configuracionModel
            ->where('proyectos_id', $proyectoId)
            ->first();
        
        if (!$configuracion) {
            throw new \Exception('No hay configuración de cobranza para este proyecto');
        }

        return $configuracion;
    }

    private function calcularDiasAtraso(string $fechaVencimiento): int
    {
        $fechaVenc = new \DateTime($fechaVencimiento);
        $fechaActual = new \DateTime();
        
        $diferencia = $fechaActual->diff($fechaVenc);
        
        return $diferencia->days;
    }

    private function calcularMontoInteres(PlanPago $plan, ConfiguracionCobranza $configuracion, int $diasAtraso): float
    {
        $saldoPendiente = $plan->getSaldoPendiente();
        
        if ($saldoPendiente <= 0) {
            return 0;
        }

        // Usar VentaCalculoService para cálculo preciso
        $tasaAnual = $configuracion->tasa_interes_moratorio ?? VentaCalculoService::TASA_MORATORIA_ANUAL_DEFAULT;
        
        $montoInteres = $this->calculoService->calcularInteresMoratorio(
            $saldoPendiente,
            $diasAtraso,
            $tasaAnual
        );

        // Aplicar límite máximo si está configurado
        if ($configuracion->limite_interes_moratorio > 0) {
            $montoMaximo = $this->calculoService->validarPrecision(
                $saldoPendiente * ($configuracion->limite_interes_moratorio / 100)
            );
            $montoInteres = min($montoInteres, $montoMaximo);
        }

        return $this->calculoService->validarPrecision($montoInteres);
    }

    private function registrarInteres(PlanPago $plan, float $montoInteres, int $diasAtraso): int
    {
        $interesData = [
            'plan_pago_id' => $plan->id,
            'ventas_id' => $plan->ventas_id,
            'proyectos_id' => $plan->proyectos_id,
            'vendedor_id' => $plan->vendedor_id,
            'usuario_id' => auth()->id(),
            'monto' => $montoInteres,
            'dias_atraso' => $diasAtraso,
            'fecha_vencimiento_original' => $plan->fecha_vencimiento,
            'fecha_calculo' => date('Y-m-d'),
            'hora_calculo' => date('H:i:s'),
            'saldo_original' => $plan->getSaldoPendiente(),
            'observaciones' => "Interés moratorio calculado automáticamente por $diasAtraso días de atraso",
        ];

        $interes = new CobranzaInteres($interesData);
        return $this->interesModel->insert($interes);
    }

    private function aplicarInteresAPlan(PlanPago $plan, float $montoInteres): void
    {
        $plan->agregarInteres($montoInteres);
        $this->planPagoModel->save($plan);
    }

    private function registrarHistorial(PlanPago $plan, float $montoInteres, int $diasAtraso): void
    {
        $historialData = [
            'plan_pago_id' => $plan->id,
            'ventas_id' => $plan->ventas_id,
            'proyectos_id' => $plan->proyectos_id,
            'vendedor_id' => $plan->vendedor_id,
            'usuario_id' => auth()->id(),
            'accion' => 'aplicacion_interes',
            'monto_anterior' => $plan->total - $montoInteres,
            'monto_nuevo' => $plan->total,
            'diferencia' => $montoInteres,
            'observaciones' => "Interés moratorio por $diasAtraso días de atraso",
            'fecha' => date('Y-m-d'),
            'hora' => date('H:i:s'),
        ];

        $historial = new HistorialCobranza($historialData);
        $this->historialModel->insert($historial);
    }

    /**
     * Recalcular todos los intereses de un proyecto
     */
    public function recalcularInteresesProyecto(int $proyectoId): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Obtener todos los planes de pago vencidos del proyecto
            $planes = $this->planPagoModel
                ->where('proyectos_id', $proyectoId)
                ->where('cobrado', false)
                ->where('estatus', 1)
                ->findAll();

            $recalculados = 0;
            $totalIntereses = 0;

            foreach ($planes as $plan) {
                $diasAtraso = $this->calcularDiasAtraso($plan->fecha_vencimiento);
                
                if ($diasAtraso <= 0) {
                    continue;
                }

                // Obtener configuración
                $configuracion = $this->obtenerConfiguracionCobranza($proyectoId);
                
                // Resetear intereses existentes
                $plan->resetearIntereses();
                
                // Calcular nuevo interés
                $montoInteres = $this->calcularMontoInteres($plan, $configuracion, $diasAtraso);
                
                if ($montoInteres > 0) {
                    $this->aplicarInteresAPlan($plan, $montoInteres);
                    $recalculados++;
                    $totalIntereses += $montoInteres;
                }
            }

            $db->transComplete();

            return [
                'success' => true,
                'planes_recalculados' => $recalculados,
                'total_intereses' => $totalIntereses,
                'message' => 'Intereses recalculados exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => 'Error al recalcular intereses: ' . $e->getMessage()
            ];
        }
    }
}