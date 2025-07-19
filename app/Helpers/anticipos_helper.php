<?php

/**
 * Helper para manejo de anticipos de apartado
 * y conversión a liquidación de enganche
 */

if (!function_exists('calcular_anticipo_requerido')) {
    /**
     * Calcular anticipo requerido para completar enganche
     * 
     * @param int $ventaId ID de la venta
     * @return array Información del anticipo requerido
     */
    function calcular_anticipo_requerido(int $ventaId): array
    {
        try {
            $ventaModel = new \App\Models\VentaModel();
            $venta = $ventaModel->find($ventaId);
            
            if (!$venta) {
                return [
                    'success' => false,
                    'error' => 'Venta no encontrada'
                ];
            }

            // Obtener configuración de enganche (generalmente 20% del precio)
            $porcentajeEnganche = 0.20; // 20% por defecto
            $montoEngancheRequerido = $venta->precio_venta_final * $porcentajeEnganche;

            // Verificar anticipos existentes
            $anticipoModel = new \App\Models\AnticipoApartadoModel();
            $anticipoExistente = $anticipoModel->where('venta_id', $ventaId)
                                              ->where('estado !=', 'vencido')
                                              ->first();

            $montoAcumulado = $anticipoExistente ? $anticipoExistente->monto_acumulado : 0;
            $faltante = max(0, $montoEngancheRequerido - $montoAcumulado);

            return [
                'success' => true,
                'venta_id' => $ventaId,
                'precio_venta' => $venta->precio_venta_final,
                'porcentaje_enganche' => $porcentajeEnganche * 100,
                'monto_enganche_requerido' => $montoEngancheRequerido,
                'monto_acumulado' => $montoAcumulado,
                'monto_faltante' => $faltante,
                'porcentaje_completado' => $montoEngancheRequerido > 0 ? 
                    ($montoAcumulado / $montoEngancheRequerido) * 100 : 0,
                'listo_para_liquidacion' => $faltante <= 0
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error calculando anticipo: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('acumular_anticipos')) {
    /**
     * Acumular nuevo anticipo a los existentes
     * 
     * @param int $ventaId ID de la venta
     * @param float $nuevoAnticipo Monto del nuevo anticipo
     * @return array Resultado de la acumulación
     */
    function acumular_anticipos(int $ventaId, float $nuevoAnticipo): array
    {
        try {
            $anticipoModel = new \App\Models\AnticipoApartadoModel();
            
            // Datos del pago
            $pagoData = [
                'venta_id' => $ventaId,
                'monto_anticipo' => $nuevoAnticipo,
                'monto_enganche_requerido' => 0, // Se calculará automáticamente
                'dias_vigencia' => 30,
                'aplica_interes' => false,
                'tasa_interes' => 0
            ];

            // Calcular monto de enganche requerido
            $infoAnticipo = calcular_anticipo_requerido($ventaId);
            if (!$infoAnticipo['success']) {
                return $infoAnticipo;
            }

            $pagoData['monto_enganche_requerido'] = $infoAnticipo['monto_enganche_requerido'];

            // Procesar anticipo
            $resultado = $anticipoModel->procesarAnticipoApartado($pagoData);

            return $resultado;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error acumulando anticipos: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('validar_conversion_enganche')) {
    /**
     * Validar si los anticipos están listos para conversión
     * 
     * @param int $ventaId ID de la venta
     * @return array Resultado de la validación
     */
    function validar_conversion_enganche(int $ventaId): array
    {
        try {
            $anticipoModel = new \App\Models\AnticipoApartadoModel();
            
            // Verificar completitud
            $esCompleto = $anticipoModel->verificarCompletitudEnganche($ventaId);
            
            if (!$esCompleto) {
                $infoAnticipo = calcular_anticipo_requerido($ventaId);
                return [
                    'success' => false,
                    'listo_conversion' => false,
                    'mensaje' => 'Enganche incompleto. Falta: $' . number_format($infoAnticipo['monto_faltante'], 2),
                    'monto_faltante' => $infoAnticipo['monto_faltante']
                ];
            }

            // Verificar que no haya liquidación existente
            $liquidacionModel = new \App\Models\LiquidacionEngancheModel();
            $liquidacionExistente = $liquidacionModel->where('venta_id', $ventaId)
                                                   ->where('estado !=', 'cancelada')
                                                   ->first();

            if ($liquidacionExistente) {
                return [
                    'success' => false,
                    'listo_conversion' => false,
                    'mensaje' => 'Ya existe una liquidación para esta venta',
                    'liquidacion_id' => $liquidacionExistente->id
                ];
            }

            return [
                'success' => true,
                'listo_conversion' => true,
                'mensaje' => 'Anticipo listo para conversión a liquidación'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error validando conversión: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('generar_liquidacion_enganche')) {
    /**
     * Generar liquidación de enganche desde anticipos
     * 
     * @param int $ventaId ID de la venta
     * @return array Resultado de la generación
     */
    function generar_liquidacion_enganche(int $ventaId): array
    {
        try {
            // Validar que esté listo
            $validacion = validar_conversion_enganche($ventaId);
            if (!$validacion['success'] || !$validacion['listo_conversion']) {
                return $validacion;
            }

            // Convertir anticipos
            $anticipoModel = new \App\Models\AnticipoApartadoModel();
            $resultado = $anticipoModel->convertirALiquidacion($ventaId);

            if ($resultado['success']) {
                // Registrar en el log
                log_message('info', "Liquidación de enganche generada: {$resultado['folio_liquidacion']} para venta {$ventaId}");
                
                // Actualizar estado de la venta si es necesario
                $ventaModel = new \App\Models\VentaModel();
                $ventaModel->update($ventaId, ['estatus_enganche' => 'liquidado']);
            }

            return $resultado;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando liquidación: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('calcular_interes_apartado')) {
    /**
     * Calcular interés sobre anticipo de apartado
     * 
     * @param string $fechaInicio Fecha de inicio del apartado
     * @param float $monto Monto del anticipo
     * @param float $tasaAnual Tasa de interés anual (default 12%)
     * @return array Cálculo del interés
     */
    function calcular_interes_apartado(string $fechaInicio, float $monto, float $tasaAnual = 12.0): array
    {
        try {
            $fechaInicio = new \DateTime($fechaInicio);
            $fechaActual = new \DateTime();
            
            // Calcular días transcurridos
            $diasTranscurridos = $fechaInicio->diff($fechaActual)->days;
            
            if ($diasTranscurridos <= 0) {
                return [
                    'success' => true,
                    'dias_transcurridos' => 0,
                    'interes_calculado' => 0,
                    'monto_total' => $monto
                ];
            }

            // Calcular interés simple diario
            $tasaDiaria = $tasaAnual / 365 / 100;
            $interes = $monto * $tasaDiaria * $diasTranscurridos;

            return [
                'success' => true,
                'monto_principal' => $monto,
                'tasa_anual' => $tasaAnual,
                'tasa_diaria' => $tasaDiaria,
                'dias_transcurridos' => $diasTranscurridos,
                'interes_calculado' => round($interes, 2),
                'monto_total' => round($monto + $interes, 2)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error calculando interés: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('obtener_resumen_anticipos')) {
    /**
     * Obtener resumen completo de anticipos por venta
     * 
     * @param int $ventaId ID de la venta
     * @return array Resumen de anticipos
     */
    function obtener_resumen_anticipos(int $ventaId): array
    {
        try {
            $anticipoModel = new \App\Models\AnticipoApartadoModel();
            $anticipos = $anticipoModel->getAnticiposAcumulados($ventaId);

            if (empty($anticipos)) {
                return [
                    'success' => true,
                    'tiene_anticipos' => false,
                    'mensaje' => 'No hay anticipos registrados para esta venta'
                ];
            }

            $ultimoAnticipo = $anticipos[0];
            $resumen = $ultimoAnticipo->getResumen();

            return [
                'success' => true,
                'tiene_anticipos' => true,
                'anticipo_id' => $ultimoAnticipo->id,
                'resumen' => $resumen,
                'historial' => array_map(function($anticipo) {
                    return [
                        'fecha' => $anticipo->fecha_anticipo,
                        'monto' => $anticipo->monto_anticipo,
                        'estado' => $anticipo->estado
                    ];
                }, $anticipos)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error obteniendo resumen: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('procesar_vencimientos_anticipos')) {
    /**
     * Procesar anticipos vencidos automáticamente
     * 
     * @return array Resultado del procesamiento
     */
    function procesar_vencimientos_anticipos(): array
    {
        try {
            $anticipoModel = new \App\Models\AnticipoApartadoModel();
            
            // Marcar anticipos vencidos
            $anticiposVencidos = $anticipoModel->marcarAnticiposVencidos();
            
            // Obtener anticipos próximos a vencer
            $anticiposProximos = $anticipoModel->getAnticiposProximosVencer(7);

            log_message('info', "Procesamiento de vencimientos: {$anticiposVencidos} anticipos vencidos, " . 
                               count($anticiposProximos) . " próximos a vencer");

            return [
                'success' => true,
                'anticipos_vencidos' => $anticiposVencidos,
                'anticipos_proximos' => $anticiposProximos,
                'mensaje' => "Procesados {$anticiposVencidos} anticipos vencidos"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error procesando vencimientos: ' . $e->getMessage()
            ];
        }
    }
}