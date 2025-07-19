<?php

/**
 * Helper para procesos de reestructuración de cartera
 * Funciones globales para cálculos y operaciones comunes
 */

if (!function_exists('calcular_pago_mensual_reestructuracion')) {
    /**
     * Calcula el pago mensual para una reestructuración
     * 
     * @param float $capital Capital a financiar
     * @param float $tasaAnual Tasa de interés anual
     * @param int $plazoMeses Plazo en meses
     * @return float Pago mensual calculado
     */
    function calcular_pago_mensual_reestructuracion(float $capital, float $tasaAnual, int $plazoMeses): float
    {
        if ($capital <= 0 || $plazoMeses <= 0) {
            return 0.0;
        }
        
        if ($tasaAnual == 0) {
            return $capital / $plazoMeses;
        }
        
        $tasaMensual = $tasaAnual / 100 / 12;
        $pago = $capital * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / (pow(1 + $tasaMensual, $plazoMeses) - 1);
        
        return round($pago, 2);
    }
}

if (!function_exists('calcular_nuevo_saldo_con_descuentos')) {
    /**
     * Calcula el nuevo saldo después de aplicar descuentos y quitas
     * 
     * @param float $saldoOriginal Saldo original
     * @param float $quita Quita aplicada
     * @param float $descuentoIntereses Descuento en intereses
     * @param float $descuentoMoratorios Descuento en moratorios
     * @return array Información del nuevo saldo
     */
    function calcular_nuevo_saldo_con_descuentos(
        float $saldoOriginal, 
        float $quita = 0, 
        float $descuentoIntereses = 0, 
        float $descuentoMoratorios = 0
    ): array {
        $totalDescuentos = $quita + $descuentoIntereses + $descuentoMoratorios;
        $nuevoSaldo = max(0, $saldoOriginal - $totalDescuentos);
        $porcentajeDescuento = $saldoOriginal > 0 ? ($totalDescuentos / $saldoOriginal) * 100 : 0;
        
        return [
            'saldo_original' => $saldoOriginal,
            'total_descuentos' => $totalDescuentos,
            'nuevo_saldo' => $nuevoSaldo,
            'ahorro' => $totalDescuentos,
            'porcentaje_descuento' => round($porcentajeDescuento, 2)
        ];
    }
}

if (!function_exists('generar_folio_reestructuracion')) {
    /**
     * Genera un folio único para reestructuración
     * 
     * @param int $contador Contador secuencial
     * @return string Folio generado
     */
    function generar_folio_reestructuracion(int $contador = null): string
    {
        $year = date('Y');
        $month = date('m');
        
        if ($contador === null) {
            $contador = 1;
        }
        
        return sprintf("REEST-%s%s-%05d", $year, $month, $contador);
    }
}

if (!function_exists('validar_estado_reestructuracion')) {
    /**
     * Valida si una reestructuración puede cambiar de estado
     * 
     * @param string $estadoActual Estado actual
     * @param string $nuevoEstado Nuevo estado deseado
     * @return array Resultado de validación
     */
    function validar_estado_reestructuracion(string $estadoActual, string $nuevoEstado): array
    {
        $transicionesValidas = [
            'propuesta' => ['autorizada', 'cancelada'],
            'autorizada' => ['firmada', 'activa', 'cancelada'],
            'firmada' => ['activa', 'cancelada'],
            'activa' => ['cancelada'],
            'cancelada' => []
        ];
        
        $esValida = isset($transicionesValidas[$estadoActual]) && 
                   in_array($nuevoEstado, $transicionesValidas[$estadoActual]);
        
        return [
            'valida' => $esValida,
            'mensaje' => $esValida ? 
                        'Transición válida' : 
                        "No se puede cambiar de '$estadoActual' a '$nuevoEstado'"
        ];
    }
}

if (!function_exists('obtener_color_estado_reestructuracion')) {
    /**
     * Obtiene el color de badge según el estado
     * 
     * @param string $estado Estado de la reestructuración
     * @return string Clase CSS para el color
     */
    function obtener_color_estado_reestructuracion(string $estado): string
    {
        $colores = [
            'propuesta' => 'warning',
            'autorizada' => 'info',
            'firmada' => 'primary',
            'activa' => 'success',
            'cancelada' => 'danger'
        ];
        
        return $colores[$estado] ?? 'secondary';
    }
}

if (!function_exists('calcular_progreso_reestructuracion')) {
    /**
     * Calcula el progreso de una reestructuración
     * 
     * @param array $pagos Array con información de pagos
     * @return array Información del progreso
     */
    function calcular_progreso_reestructuracion(array $pagos): array
    {
        $totalPagos = count($pagos);
        $pagosPagados = 0;
        $pagosVencidos = 0;
        $pagosPendientes = 0;
        $totalProgramado = 0;
        $totalPagado = 0;
        
        foreach ($pagos as $pago) {
            $totalProgramado += $pago['monto_total'];
            $totalPagado += $pago['monto_pagado'];
            
            switch ($pago['estatus']) {
                case 'pagada':
                    $pagosPagados++;
                    break;
                case 'vencida':
                    $pagosVencidos++;
                    break;
                case 'pendiente':
                    $pagosPendientes++;
                    break;
            }
        }
        
        $porcentajeLiquidacion = $totalProgramado > 0 ? 
                                ($totalPagado / $totalProgramado) * 100 : 0;
        
        return [
            'total_pagos' => $totalPagos,
            'pagos_pagados' => $pagosPagados,
            'pagos_vencidos' => $pagosVencidos,
            'pagos_pendientes' => $pagosPendientes,
            'total_programado' => $totalProgramado,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $totalProgramado - $totalPagado,
            'porcentaje_liquidacion' => round($porcentajeLiquidacion, 2)
        ];
    }
}

if (!function_exists('formatear_moneda_mx')) {
    /**
     * Formatea un número como moneda mexicana
     * 
     * @param float $monto Monto a formatear
     * @param bool $simbolo Si incluir símbolo de peso
     * @return string Monto formateado
     */
    function formatear_moneda_mx(float $monto, bool $simbolo = true): string
    {
        $formato = number_format($monto, 2, '.', ',');
        return $simbolo ? '$' . $formato : $formato;
    }
}

if (!function_exists('calcular_dias_mora')) {
    /**
     * Calcula los días de mora de un pago
     * 
     * @param string $fechaVencimiento Fecha de vencimiento
     * @param string $fechaActual Fecha actual (opcional)
     * @return int Días de mora
     */
    function calcular_dias_mora(string $fechaVencimiento, string $fechaActual = null): int
    {
        $fechaActual = $fechaActual ?? date('Y-m-d');
        
        $vencimiento = new DateTime($fechaVencimiento);
        $actual = new DateTime($fechaActual);
        
        if ($actual <= $vencimiento) {
            return 0;
        }
        
        $diferencia = $actual->diff($vencimiento);
        return $diferencia->days;
    }
}

if (!function_exists('obtener_proxima_fecha_pago')) {
    /**
     * Obtiene la próxima fecha de pago basada en una fecha inicial
     * 
     * @param string $fechaInicial Fecha inicial
     * @param int $numeroMes Número de mes a calcular
     * @return string Fecha calculada
     */
    function obtener_proxima_fecha_pago(string $fechaInicial, int $numeroMes): string
    {
        $fecha = new DateTime($fechaInicial);
        $fecha->modify('+' . ($numeroMes - 1) . ' months');
        return $fecha->format('Y-m-d');
    }
}

if (!function_exists('validar_monto_pago')) {
    /**
     * Valida un monto de pago
     * 
     * @param float $montoPago Monto del pago
     * @param float $montoDeuda Monto de la deuda
     * @return array Resultado de validación
     */
    function validar_monto_pago(float $montoPago, float $montoDeuda): array
    {
        if ($montoPago <= 0) {
            return [
                'valido' => false,
                'mensaje' => 'El monto del pago debe ser mayor a cero'
            ];
        }
        
        if ($montoPago > $montoDeuda) {
            return [
                'valido' => false,
                'mensaje' => 'El monto del pago no puede ser mayor a la deuda',
                'sobrante' => $montoPago - $montoDeuda
            ];
        }
        
        return [
            'valido' => true,
            'mensaje' => 'Monto válido'
        ];
    }
}

if (!function_exists('calcular_interes_moratorio')) {
    /**
     * Calcula el interés moratorio
     * 
     * @param float $capital Capital vencido
     * @param float $tasaMoratoria Tasa moratoria mensual
     * @param int $diasMora Días de mora
     * @return float Interés moratorio calculado
     */
    function calcular_interes_moratorio(float $capital, float $tasaMoratoria, int $diasMora): float
    {
        if ($capital <= 0 || $tasaMoratoria <= 0 || $diasMora <= 0) {
            return 0.0;
        }
        
        $tasaDiaria = ($tasaMoratoria / 100) / 30;
        $interesMoratorio = $capital * $tasaDiaria * $diasMora;
        
        return round($interesMoratorio, 2);
    }
}

if (!function_exists('obtener_estadisticas_reestructuraciones')) {
    /**
     * Obtiene estadísticas generales de reestructuraciones
     * 
     * @param array $reestructuraciones Array de reestructuraciones
     * @return array Estadísticas calculadas
     */
    function obtener_estadisticas_reestructuraciones(array $reestructuraciones): array
    {
        $estadisticas = [
            'total' => count($reestructuraciones),
            'por_estado' => [],
            'montos' => [
                'saldo_original_total' => 0,
                'nuevo_saldo_total' => 0,
                'quitas_total' => 0,
                'ahorro_total' => 0
            ],
            'promedios' => []
        ];
        
        foreach ($reestructuraciones as $reestructuracion) {
            // Conteo por estado
            $estado = $reestructuracion['estatus'] ?? 'desconocido';
            $estadisticas['por_estado'][$estado] = ($estadisticas['por_estado'][$estado] ?? 0) + 1;
            
            // Suma de montos
            $estadisticas['montos']['saldo_original_total'] += $reestructuracion['saldo_pendiente_original'] ?? 0;
            $estadisticas['montos']['nuevo_saldo_total'] += $reestructuracion['nuevo_saldo_capital'] ?? 0;
            $estadisticas['montos']['quitas_total'] += $reestructuracion['quita_aplicada'] ?? 0;
        }
        
        // Calcular ahorro total
        $estadisticas['montos']['ahorro_total'] = 
            $estadisticas['montos']['saldo_original_total'] - 
            $estadisticas['montos']['nuevo_saldo_total'];
        
        // Calcular promedios
        if ($estadisticas['total'] > 0) {
            $estadisticas['promedios'] = [
                'saldo_original_promedio' => $estadisticas['montos']['saldo_original_total'] / $estadisticas['total'],
                'nuevo_saldo_promedio' => $estadisticas['montos']['nuevo_saldo_total'] / $estadisticas['total'],
                'quita_promedio' => $estadisticas['montos']['quitas_total'] / $estadisticas['total'],
                'ahorro_promedio' => $estadisticas['montos']['ahorro_total'] / $estadisticas['total']
            ];
        }
        
        return $estadisticas;
    }
}

if (!function_exists('generar_reporte_reestructuracion')) {
    /**
     * Genera un reporte de reestructuración
     * 
     * @param array $reestructuracion Datos de la reestructuración
     * @return array Reporte generado
     */
    function generar_reporte_reestructuracion(array $reestructuracion): array
    {
        $calculos = calcular_nuevo_saldo_con_descuentos(
            $reestructuracion['saldo_pendiente_original'],
            $reestructuracion['quita_aplicada'] ?? 0,
            $reestructuracion['descuento_intereses'] ?? 0,
            $reestructuracion['descuento_moratorios'] ?? 0
        );
        
        $pagoMensual = calcular_pago_mensual_reestructuracion(
            $reestructuracion['nuevo_saldo_capital'],
            $reestructuracion['nueva_tasa_interes'],
            $reestructuracion['nuevo_plazo_meses']
        );
        
        return [
            'folio' => $reestructuracion['folio_reestructuracion'],
            'fecha' => $reestructuracion['fecha_reestructuracion'],
            'estado' => $reestructuracion['estatus'],
            'color_estado' => obtener_color_estado_reestructuracion($reestructuracion['estatus']),
            'calculos' => $calculos,
            'pago_mensual' => $pagoMensual,
            'total_a_pagar' => $pagoMensual * $reestructuracion['nuevo_plazo_meses'],
            'fecha_finalizacion' => obtener_proxima_fecha_pago(
                $reestructuracion['fecha_primer_pago'],
                $reestructuracion['nuevo_plazo_meses']
            )
        ];
    }
}