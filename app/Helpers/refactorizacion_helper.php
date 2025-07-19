<?php

/**
 * Helper para refactorización de tablas de amortización
 * por abonos a capital
 */

if (!function_exists('ejecutar_refactorizacion_automatica')) {
    /**
     * Ejecutar refactorización automática por abono a capital
     * 
     * @param int $cuentaId ID de la cuenta de financiamiento
     * @param float $abonoCapital Monto abonado a capital
     * @return array Resultado de la refactorización
     */
    function ejecutar_refactorizacion_automatica(int $cuentaId, float $abonoCapital): array
    {
        try {
            $cuentaModel = new \App\Models\CuentaFinanciamientoModel();
            $cuenta = $cuentaModel->find($cuentaId);

            if (!$cuenta) {
                return [
                    'success' => false,
                    'error' => 'Cuenta no encontrada'
                ];
            }

            // Calcular nueva tabla de amortización
            $nuevaTabla = calcular_nueva_tabla_amortizacion(
                $cuenta->saldo_capital,
                $cuenta->tasa_interes_anual,
                $cuenta->meses_restantes
            );

            if (!$nuevaTabla['success']) {
                return $nuevaTabla;
            }

            // Aplicar refactorización a la cuenta
            $cuenta->aplicarRefactorizacion(
                $nuevaTabla['nueva_mensualidad'],
                $cuenta->meses_restantes
            );

            // Guardar cambios
            $cuentaModel->save($cuenta);

            // Actualizar tabla de amortización existente
            $resultadoTabla = actualizar_tabla_amortizacion_existente(
                $cuenta->venta_id,
                $nuevaTabla,
                $cuenta->meses_transcurridos
            );

            // Crear registro de movimiento de refactorización
            $movimientoModel = new \App\Models\MovimientoCuentaModel();
            $movimiento = \App\Entities\MovimientoCuenta::crearRefactorizacion([
                'cuenta_id' => $cuentaId,
                'saldo_anterior' => [
                    'capital' => $cuenta->saldo_capital + $abonoCapital,
                    'interes' => $cuenta->saldo_interes,
                    'moratorio' => $cuenta->saldo_moratorio,
                    'total' => $cuenta->saldo_total + $abonoCapital
                ],
                'saldo_nuevo' => [
                    'capital' => $cuenta->saldo_capital,
                    'interes' => $cuenta->saldo_interes,
                    'moratorio' => $cuenta->saldo_moratorio,
                    'total' => $cuenta->saldo_total
                ],
                'nueva_mensualidad' => $nuevaTabla['nueva_mensualidad'],
                'nuevo_plazo' => $cuenta->meses_restantes,
                'ahorro_intereses' => $nuevaTabla['ahorro_total_estimado'],
                'usuario_id' => auth()->id() ?? 1
            ]);

            $movimientoModel->save($movimiento);

            return [
                'success' => true,
                'nueva_mensualidad' => $nuevaTabla['nueva_mensualidad'],
                'ahorro_intereses' => $nuevaTabla['ahorro_total_estimado'],
                'meses_restantes' => $cuenta->meses_restantes,
                'refactorizaciones_total' => $cuenta->refactorizaciones_aplicadas,
                'movimiento_id' => $movimiento->id,
                'mensaje' => 'Refactorización ejecutada exitosamente'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error ejecutando refactorización: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('calcular_nueva_tabla_amortizacion')) {
    /**
     * Calcular nueva tabla de amortización
     * 
     * @param float $saldoCapital Saldo capital actual
     * @param float $tasaAnual Tasa de interés anual
     * @param int $mesesRestantes Meses restantes
     * @return array Nueva tabla calculada
     */
    function calcular_nueva_tabla_amortizacion(float $saldoCapital, float $tasaAnual, int $mesesRestantes): array
    {
        try {
            if ($saldoCapital <= 0 || $mesesRestantes <= 0) {
                return [
                    'success' => false,
                    'error' => 'Parámetros inválidos para el cálculo'
                ];
            }

            $tasaMensual = $tasaAnual / 12 / 100;
            
            // Calcular nueva mensualidad con fórmula francesa
            if ($tasaMensual == 0) {
                $nuevaMensualidad = $saldoCapital / $mesesRestantes;
            } else {
                $factor = pow(1 + $tasaMensual, $mesesRestantes);
                $nuevaMensualidad = $saldoCapital * ($tasaMensual * $factor) / ($factor - 1);
            }

            // Generar nueva tabla
            $tabla = [];
            $saldoActual = $saldoCapital;
            $totalIntereses = 0;

            for ($i = 1; $i <= $mesesRestantes; $i++) {
                $interes = $saldoActual * $tasaMensual;
                $capital = $nuevaMensualidad - $interes;
                
                // Ajustar último pago
                if ($i == $mesesRestantes) {
                    $capital = $saldoActual;
                    $nuevaMensualidad = $capital + $interes;
                }

                $tabla[] = [
                    'numero_pago' => $i,
                    'saldo_inicial' => $saldoActual,
                    'capital' => round($capital, 2),
                    'interes' => round($interes, 2),
                    'monto_total' => round($capital + $interes, 2),
                    'saldo_final' => round($saldoActual - $capital, 2)
                ];

                $saldoActual -= $capital;
                $totalIntereses += $interes;
            }

            return [
                'success' => true,
                'nueva_mensualidad' => round($nuevaMensualidad, 2),
                'total_intereses' => round($totalIntereses, 2),
                'tabla_amortizacion' => $tabla,
                'ahorro_total_estimado' => 0 // Se calculará comparando con tabla anterior
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error calculando nueva tabla: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('actualizar_tabla_amortizacion_existente')) {
    /**
     * Actualizar tabla de amortización existente con refactorización
     * 
     * @param int $ventaId ID de la venta
     * @param array $nuevaTabla Nueva tabla calculada
     * @param int $mesesTranscurridos Meses ya transcurridos
     * @return array Resultado de la actualización
     */
    function actualizar_tabla_amortizacion_existente(int $ventaId, array $nuevaTabla, int $mesesTranscurridos): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Obtener tabla actual
            $tablaActual = $db->query("
                SELECT * FROM tabla_amortizacion 
                WHERE plan_financiamiento_id = (
                    SELECT perfil_financiamiento_id FROM ventas WHERE id = ?
                )
                AND numero_pago > ?
                AND estatus NOT IN ('pagada', 'cancelada')
                ORDER BY numero_pago ASC
            ", [$ventaId, $mesesTranscurridos])->getResult();

            if (empty($tablaActual)) {
                return [
                    'success' => false,
                    'error' => 'No se encontraron mensualidades pendientes para refactorizar'
                ];
            }

            $actualizados = 0;
            $numeroTabla = 0;

            // Actualizar cada mensualidad pendiente
            foreach ($tablaActual as $mensualidad) {
                if ($numeroTabla < count($nuevaTabla['tabla_amortizacion'])) {
                    $nuevosPagos = $nuevaTabla['tabla_amortizacion'][$numeroTabla];
                    
                    // Actualizar en base de datos
                    $db->query("
                        UPDATE tabla_amortizacion 
                        SET 
                            saldo_inicial = ?,
                            capital = ?,
                            interes = ?,
                            monto_total = ?,
                            saldo_final = ?,
                            refactorizado = 1,
                            fecha_refactorizacion = NOW()
                        WHERE id = ?
                    ", [
                        $nuevosPagos['saldo_inicial'],
                        $nuevosPagos['capital'],
                        $nuevosPagos['interes'],
                        $nuevosPagos['monto_total'],
                        $nuevosPagos['saldo_final'],
                        $mensualidad->id
                    ]);

                    $actualizados++;
                    $numeroTabla++;
                }
            }

            return [
                'success' => true,
                'mensualidades_actualizadas' => $actualizados,
                'mensaje' => "Se actualizaron {$actualizados} mensualidades"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error actualizando tabla: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('simular_abono_capital')) {
    /**
     * Simular impacto de abono a capital
     * 
     * @param int $cuentaId ID de la cuenta
     * @param float $montoAbono Monto a simular
     * @return array Simulación del impacto
     */
    function simular_abono_capital(int $cuentaId, float $montoAbono): array
    {
        try {
            $cuentaModel = new \App\Models\CuentaFinanciamientoModel();
            $cuenta = $cuentaModel->find($cuentaId);

            if (!$cuenta) {
                return [
                    'success' => false,
                    'error' => 'Cuenta no encontrada'
                ];
            }

            // Validar monto
            if ($montoAbono <= 0 || $montoAbono > $cuenta->saldo_capital) {
                return [
                    'success' => false,
                    'error' => 'Monto inválido para abono a capital'
                ];
            }

            // Calcular escenario actual
            $escenarioActual = calcular_escenario_actual($cuenta);

            // Calcular escenario con abono
            $nuevoSaldoCapital = $cuenta->saldo_capital - $montoAbono;
            $escenarioConAbono = calcular_nueva_tabla_amortizacion(
                $nuevoSaldoCapital,
                $cuenta->tasa_interes_anual,
                $cuenta->meses_restantes
            );

            if (!$escenarioConAbono['success']) {
                return $escenarioConAbono;
            }

            // Calcular ahorros
            $ahorroMensual = $escenarioActual['mensualidad_actual'] - $escenarioConAbono['nueva_mensualidad'];
            $ahorroTotalIntereses = $escenarioActual['total_intereses'] - $escenarioConAbono['total_intereses'];

            return [
                'success' => true,
                'escenario_actual' => [
                    'saldo_capital' => $cuenta->saldo_capital,
                    'mensualidad' => $escenarioActual['mensualidad_actual'],
                    'meses_restantes' => $cuenta->meses_restantes,
                    'total_intereses' => $escenarioActual['total_intereses'],
                    'pago_total_restante' => $escenarioActual['pago_total_restante']
                ],
                'escenario_con_abono' => [
                    'saldo_capital' => $nuevoSaldoCapital,
                    'mensualidad' => $escenarioConAbono['nueva_mensualidad'],
                    'meses_restantes' => $cuenta->meses_restantes,
                    'total_intereses' => $escenarioConAbono['total_intereses'],
                    'pago_total_restante' => $nuevoSaldoCapital + $escenarioConAbono['total_intereses']
                ],
                'beneficios' => [
                    'abono_capital' => $montoAbono,
                    'reduccion_mensualidad' => $ahorroMensual,
                    'ahorro_total_intereses' => $ahorroTotalIntereses,
                    'ahorro_total_pagos' => $ahorroTotalIntereses + $montoAbono,
                    'nueva_mensualidad' => $escenarioConAbono['nueva_mensualidad']
                ],
                'recomendacion' => generar_recomendacion_abono($ahorroTotalIntereses, $montoAbono)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error simulando abono: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('calcular_escenario_actual')) {
    /**
     * Calcular escenario actual de la cuenta
     * 
     * @param object $cuenta Cuenta de financiamiento
     * @return array Escenario actual
     */
    function calcular_escenario_actual($cuenta): array
    {
        $tasaMensual = $cuenta->tasa_interes_anual / 12 / 100;
        $totalIntereses = 0;
        $saldoActual = $cuenta->saldo_capital;

        // Calcular intereses restantes
        for ($i = 1; $i <= $cuenta->meses_restantes; $i++) {
            $interes = $saldoActual * $tasaMensual;
            $capital = $cuenta->monto_mensualidad - $interes;
            $saldoActual -= $capital;
            $totalIntereses += $interes;
        }

        return [
            'mensualidad_actual' => $cuenta->monto_mensualidad,
            'total_intereses' => $totalIntereses,
            'pago_total_restante' => $cuenta->saldo_capital + $totalIntereses
        ];
    }
}

if (!function_exists('generar_recomendacion_abono')) {
    /**
     * Generar recomendación sobre el abono
     * 
     * @param float $ahorroIntereses Ahorro en intereses
     * @param float $montoAbono Monto del abono
     * @return array Recomendación
     */
    function generar_recomendacion_abono(float $ahorroIntereses, float $montoAbono): array
    {
        $porcentajeAhorro = $montoAbono > 0 ? ($ahorroIntereses / $montoAbono) * 100 : 0;

        if ($porcentajeAhorro >= 30) {
            return [
                'tipo' => 'excelente',
                'icono' => 'fas fa-star',
                'color' => 'success',
                'mensaje' => 'Excelente inversión. Ahorro del ' . round($porcentajeAhorro, 1) . '% en intereses.'
            ];
        } elseif ($porcentajeAhorro >= 15) {
            return [
                'tipo' => 'buena',
                'icono' => 'fas fa-thumbs-up',
                'color' => 'info',
                'mensaje' => 'Buena inversión. Ahorro del ' . round($porcentajeAhorro, 1) . '% en intereses.'
            ];
        } else {
            return [
                'tipo' => 'consideracion',
                'icono' => 'fas fa-info-circle',
                'color' => 'warning',
                'mensaje' => 'Considerar otras opciones. Ahorro del ' . round($porcentajeAhorro, 1) . '% en intereses.'
            ];
        }
    }
}

if (!function_exists('obtener_historial_refactorizaciones')) {
    /**
     * Obtener historial de refactorizaciones de una cuenta
     * 
     * @param int $cuentaId ID de la cuenta
     * @return array Historial de refactorizaciones
     */
    function obtener_historial_refactorizaciones(int $cuentaId): array
    {
        try {
            $db = \Config\Database::connect();
            
            $historial = $db->query("
                SELECT 
                    mc.id,
                    mc.descripcion,
                    mc.fecha_movimiento,
                    mc.saldo_anterior_capital,
                    mc.saldo_nuevo_capital,
                    mc.datos_adicionales,
                    u.username as usuario_nombre
                FROM movimientos_cuenta mc
                LEFT JOIN users u ON u.id = mc.usuario_id
                WHERE mc.cuenta_financiamiento_id = ?
                AND mc.tipo_movimiento = 'refactorizacion'
                ORDER BY mc.fecha_movimiento DESC
            ", [$cuentaId])->getResult();

            $refactorizaciones = [];
            foreach ($historial as $mov) {
                $datos = json_decode($mov->datos_adicionales, true);
                
                $refactorizaciones[] = [
                    'id' => $mov->id,
                    'fecha' => $mov->fecha_movimiento,
                    'descripcion' => $mov->descripcion,
                    'saldo_anterior' => $mov->saldo_anterior_capital,
                    'saldo_nuevo' => $mov->saldo_nuevo_capital,
                    'abono_capital' => $mov->saldo_anterior_capital - $mov->saldo_nuevo_capital,
                    'nueva_mensualidad' => $datos['nueva_mensualidad'] ?? 0,
                    'ahorro_intereses' => $datos['ahorro_intereses'] ?? 0,
                    'usuario' => $mov->usuario_nombre
                ];
            }

            return [
                'success' => true,
                'refactorizaciones' => $refactorizaciones,
                'total_refactorizaciones' => count($refactorizaciones)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error obteniendo historial: ' . $e->getMessage()
            ];
        }
    }
}