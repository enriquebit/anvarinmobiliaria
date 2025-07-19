<?php

use App\Models\TablaAmortizacionModel;
use App\Models\PerfilFinanciamientoModel;

/**
 * Helper para gestión de tablas de amortización
 */

if (!function_exists('generar_tabla_amortizacion')) {
    /**
     * Genera una tabla de amortización completa para una venta
     */
    function generar_tabla_amortizacion(int $ventaId, array $configFinanciera): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Extraer configuración primero
            $montoFinanciar = $configFinanciera['monto_financiar'];
            $tasaAnual = $configFinanciera['tasa_interes_anual'];
            $numeroPagos = $configFinanciera['numero_pagos'];
            $fechaPrimerPago = $configFinanciera['fecha_primer_pago'];

            // Validar configuración
            $validacion = validar_configuracion_financiera($configFinanciera);
            if (!$validacion['valido']) {
                throw new \Exception('Configuración inválida: ' . implode(', ', $validacion['errores']));
            }

            // Obtener la venta para referencia
            $ventaModel = new \App\Models\VentaModel();
            $venta = $ventaModel->find($ventaId);
            
            if (!$venta) {
                throw new \Exception('Venta no encontrada');
            }

            // Verificar si ya existe cuenta de financiamiento para esta venta
            $cuentaExistente = $db->table('cuentas_financiamiento')
                ->where('venta_id', $ventaId)
                ->get()
                ->getRow();
            
            if ($cuentaExistente) {
                $cuentaFinanciamientoId = $cuentaExistente->id;
            } else {
                // Crear cuenta de financiamiento
                
                $datosCuenta = [
                    'venta_id' => $ventaId,
                    'plan_financiamiento_id' => $venta->perfil_financiamiento_id,
                    'saldo_inicial' => $montoFinanciar,
                    'saldo_actual' => $montoFinanciar,
                    'fecha_apertura' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $cuentaFinanciamientoId = $db->table('cuentas_financiamiento')->insert($datosCuenta);
                
                if (!$cuentaFinanciamientoId) {
                    throw new \Exception('Error creando cuenta de financiamiento');
                }
                
                log_message('info', "Cuenta de financiamiento creada para venta {$ventaId}: ID {$cuentaFinanciamientoId}");
            }

            // Calcular pago mensual
            $pagoMensual = calcular_pago_mensual($montoFinanciar, $tasaAnual, $numeroPagos);
            
            // Generar cada mensualidad
            $mensualidades = [];
            $saldoActual = $montoFinanciar;
            $fechaVencimiento = new DateTime($fechaPrimerPago);
            
            for ($numeroPago = 1; $numeroPago <= $numeroPagos; $numeroPago++) {
                // Calcular interés del período
                $interesPeriodo = calcular_interes_periodo($saldoActual, $tasaAnual);
                
                // Calcular capital a pagar
                $capitalPago = $pagoMensual - $interesPeriodo;
                
                // Ajustar última mensualidad para liquidar completamente
                if ($numeroPago === $numeroPagos) {
                    $capitalPago = $saldoActual;
                    $pagoTotal = $capitalPago + $interesPeriodo;
                } else {
                    $pagoTotal = $pagoMensual;
                }
                
                // Calcular nuevo saldo
                $nuevoSaldo = $saldoActual - $capitalPago;
                
                $mensualidad = [
                    'plan_financiamiento_id' => $venta->perfil_financiamiento_id, // Referencia al perfil de financiamiento
                    'venta_id' => $ventaId, // Referencia directa a la venta
                    'numero_pago' => $numeroPago,
                    'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                    'saldo_inicial' => round($saldoActual, 2),
                    'capital' => round($capitalPago, 2),
                    'interes' => round($interesPeriodo, 2),
                    'monto_total' => round($pagoTotal, 2),
                    'saldo_final' => round($nuevoSaldo, 2),
                    'monto_pagado' => 0.00,
                    // 'saldo_pendiente' => NO SE ASIGNA - Es columna generada por MySQL
                    'numero_pagos_aplicados' => 0,
                    'estatus' => 'pendiente',
                    'dias_atraso' => 0,
                    'interes_moratorio' => 0.00,
                    'beneficiario' => 'empresa',
                    'concepto_especial' => "VENTA-{$ventaId}-CUENTA-{$cuentaFinanciamientoId}" // Identificador único para la venta
                ];
                
                $mensualidades[] = $mensualidad;
                
                // Actualizar para siguiente iteración
                $saldoActual = $nuevoSaldo;
                $fechaVencimiento->modify('+1 month');
            }

            // Verificar si ya existe tabla para esta venta específica
            $tablaExistente = $db->table('tabla_amortizacion')
                ->where('venta_id', $ventaId)
                ->get()
                ->getResult();
            
            if (!empty($tablaExistente)) {
                return [
                    'success' => false,
                    'error' => 'Ya existe una tabla de amortización para esta venta'
                ];
            }

            // Insertar mensualidades en la base de datos
            $tablaModel = new TablaAmortizacionModel();
            $idsInsertados = [];
            foreach ($mensualidades as $mensualidad) {
                $id = $tablaModel->insert($mensualidad);
                if (!$id) {
                    throw new \Exception('Error al insertar mensualidad: ' . implode(', ', $tablaModel->errors()));
                }
                $idsInsertados[] = $id;
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de generación');
            }

            return [
                'success' => true,
                'venta_id' => $ventaId,
                'mensualidades_generadas' => count($mensualidades),
                'monto_financiado' => $montoFinanciar,
                'pago_mensual' => round($pagoMensual, 2),
                'total_intereses' => round(array_sum(array_column($mensualidades, 'interes')), 2),
                'total_a_pagar' => round(array_sum(array_column($mensualidades, 'monto_total')), 2),
                'mensualidades' => $mensualidades,
                'ids_insertados' => $idsInsertados
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('calcular_pago_mensual')) {
    /**
     * Calcula el pago mensual usando fórmula de amortización francesa
     */
    function calcular_pago_mensual(float $capital, float $tasaAnual, int $numeroPagos): float
    {
        if ($capital <= 0 || $numeroPagos <= 0) {
            return 0.0;
        }

        // Si no hay tasa de interés, es pago lineal
        if ($tasaAnual <= 0) {
            return $capital / $numeroPagos;
        }

        // Convertir tasa anual a mensual
        $tasaMensual = $tasaAnual / 12 / 100;
        
        // Fórmula de amortización francesa
        // PMT = C * (r * (1 + r)^n) / ((1 + r)^n - 1)
        $factor = pow(1 + $tasaMensual, $numeroPagos);
        $pagoMensual = $capital * ($tasaMensual * $factor) / ($factor - 1);
        
        return round($pagoMensual, 2);
    }
}

if (!function_exists('calcular_interes_periodo')) {
    /**
     * Calcula el interés de un período sobre el saldo
     */
    function calcular_interes_periodo(float $saldo, float $tasaAnual): float
    {
        if ($saldo <= 0 || $tasaAnual <= 0) {
            return 0.0;
        }
        
        $tasaMensual = $tasaAnual / 12 / 100;
        return $saldo * $tasaMensual;
    }
}

if (!function_exists('distribuir_pago')) {
    /**
     * Distribuye un pago entre capital e intereses según orden de prioridad
     */
    function distribuir_pago(float $montoPago, float $saldoPendiente, float $intereses): array
    {
        $distribucion = [
            'aplicado_intereses' => 0.00,
            'aplicado_capital' => 0.00,
            'sobrante' => 0.00,
            'total_aplicado' => 0.00
        ];

        if ($montoPago <= 0) {
            return $distribucion;
        }

        $montoRestante = $montoPago;

        // Primero: Aplicar a intereses moratorios
        if ($intereses > 0 && $montoRestante > 0) {
            $aplicarIntereses = min($intereses, $montoRestante);
            $distribucion['aplicado_intereses'] = $aplicarIntereses;
            $montoRestante -= $aplicarIntereses;
        }

        // Segundo: Aplicar a capital
        if ($saldoPendiente > 0 && $montoRestante > 0) {
            $aplicarCapital = min($saldoPendiente, $montoRestante);
            $distribucion['aplicado_capital'] = $aplicarCapital;
            $montoRestante -= $aplicarCapital;
        }

        // Tercero: Sobrante
        $distribucion['sobrante'] = $montoRestante;
        $distribucion['total_aplicado'] = $montoPago - $montoRestante;

        return $distribucion;
    }
}

if (!function_exists('actualizar_saldos_tabla')) {
    /**
     * Actualiza los saldos de la tabla de amortización después de aplicar un pago
     */
    function actualizar_saldos_tabla(int $ventaId, array $pagoAplicado): array
    {
        $tablaModel = new TablaAmortizacionModel();
        
        try {
            // Obtener mensualidad específica si se proporciona
            if (!empty($pagoAplicado['tabla_amortizacion_id'])) {
                $mensualidad = $tablaModel->find($pagoAplicado['tabla_amortizacion_id']);
                
                if (!$mensualidad) {
                    throw new \Exception('Mensualidad no encontrada');
                }

                // Aplicar pago usando Entity method
                $resultado = $mensualidad->aplicarPago(
                    $pagoAplicado['monto_pago'],
                    $pagoAplicado['metodo_pago'] ?? 'efectivo',
                    $pagoAplicado['referencia'] ?? null
                );

                if ($resultado['success']) {
                    $tablaModel->save($mensualidad);
                }

                return $resultado;
            }

            // Si no se especifica mensualidad, aplicar a la primera pendiente
            $mensualidadesPendientes = $tablaModel->getMensualidadesPendientes($ventaId);
            
            if (empty($mensualidadesPendientes)) {
                throw new \Exception('No hay mensualidades pendientes para esta venta');
            }

            $montoPendiente = $pagoAplicado['monto_pago'];
            $resultados = [];
            
            foreach ($mensualidadesPendientes as $mensualidad) {
                if ($montoPendiente <= 0) {
                    break;
                }

                $resultado = $mensualidad->aplicarPago(
                    $montoPendiente,
                    $pagoAplicado['metodo_pago'] ?? 'efectivo',
                    $pagoAplicado['referencia'] ?? null
                );

                if ($resultado['success']) {
                    $tablaModel->save($mensualidad);
                    $montoPendiente = $resultado['sobrante'];
                    $resultados[] = $resultado;
                }
            }

            return [
                'success' => true,
                'mensualidades_actualizadas' => count($resultados),
                'monto_aplicado_total' => $pagoAplicado['monto_pago'] - $montoPendiente,
                'sobrante_final' => $montoPendiente,
                'detalle_aplicaciones' => $resultados
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('validar_configuracion_financiera')) {
    /**
     * Valida la configuración financiera para generar tabla de amortización
     */
    function validar_configuracion_financiera(array $config): array
    {
        $errores = [];

        // Validar campos requeridos
        $camposRequeridos = ['monto_financiar', 'tasa_interes_anual', 'numero_pagos', 'fecha_primer_pago'];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($config[$campo])) {
                $errores[] = "Campo requerido: {$campo}";
            } elseif ($campo === 'tasa_interes_anual') {
                // Para tasa de interés, permitir 0 (planes MSI)
                if (!is_numeric($config[$campo]) || $config[$campo] < 0) {
                    $errores[] = "Tasa de interés debe ser un número mayor o igual a 0";
                }
            } elseif (empty($config[$campo])) {
                $errores[] = "Campo requerido: {$campo}";
            }
        }

        // Validar tipos y rangos
        if (isset($config['monto_financiar']) && $config['monto_financiar'] <= 0) {
            $errores[] = 'El monto a financiar debe ser mayor a cero';
        }

        if (isset($config['tasa_interes_anual']) && $config['tasa_interes_anual'] < 0) {
            $errores[] = 'La tasa de interés no puede ser negativa';
        }

        if (isset($config['numero_pagos']) && $config['numero_pagos'] <= 0) {
            $errores[] = 'El número de pagos debe ser mayor a cero';
        }

        if (isset($config['fecha_primer_pago'])) {
            $fecha = DateTime::createFromFormat('Y-m-d', $config['fecha_primer_pago']);
            if (!$fecha) {
                $errores[] = 'Formato de fecha inválido para primer pago (usar Y-m-d)';
            }
        }

        // Validaciones de negocio
        if (isset($config['tasa_interes_anual']) && $config['tasa_interes_anual'] > 50) {
            $errores[] = 'La tasa de interés anual parece excesiva (>50%)';
        }

        if (isset($config['numero_pagos']) && $config['numero_pagos'] > 600) {
            $errores[] = 'El número de pagos parece excesivo (>600 meses)';
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}

if (!function_exists('simular_amortizacion')) {
    /**
     * Simula una tabla de amortización sin guardar en base de datos
     */
    function simular_amortizacion(array $configFinanciera): array
    {
        try {
            // Validar configuración
            $validacion = validar_configuracion_financiera($configFinanciera);
            if (!$validacion['valido']) {
                throw new \Exception('Configuración inválida: ' . implode(', ', $validacion['errores']));
            }

            // Extraer configuración
            $montoFinanciar = $configFinanciera['monto_financiar'];
            $tasaAnual = $configFinanciera['tasa_interes_anual'];
            $numeroPagos = $configFinanciera['numero_pagos'];
            $fechaPrimerPago = $configFinanciera['fecha_primer_pago'];

            // Calcular pago mensual
            $pagoMensual = calcular_pago_mensual($montoFinanciar, $tasaAnual, $numeroPagos);
            
            // Simular mensualidades
            $simulacion = [];
            $saldoActual = $montoFinanciar;
            $fechaVencimiento = new DateTime($fechaPrimerPago);
            $totalIntereses = 0;
            
            for ($numeroPago = 1; $numeroPago <= $numeroPagos; $numeroPago++) {
                $interesPeriodo = calcular_interes_periodo($saldoActual, $tasaAnual);
                $capitalPago = $pagoMensual - $interesPeriodo;
                
                // Ajustar última mensualidad
                if ($numeroPago === $numeroPagos) {
                    $capitalPago = $saldoActual;
                    $pagoTotal = $capitalPago + $interesPeriodo;
                } else {
                    $pagoTotal = $pagoMensual;
                }
                
                $nuevoSaldo = $saldoActual - $capitalPago;
                $totalIntereses += $interesPeriodo;
                
                $simulacion[] = [
                    'numero_pago' => $numeroPago,
                    'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                    'saldo_inicial' => round($saldoActual, 2),
                    'capital' => round($capitalPago, 2),
                    'interes' => round($interesPeriodo, 2),
                    'pago_total' => round($pagoTotal, 2),
                    'saldo_final' => round($nuevoSaldo, 2)
                ];
                
                $saldoActual = $nuevoSaldo;
                $fechaVencimiento->modify('+1 month');
            }

            return [
                'success' => true,
                'configuracion' => $configFinanciera,
                'resumen' => [
                    'monto_financiado' => $montoFinanciar,
                    'pago_mensual' => round($pagoMensual, 2),
                    'numero_pagos' => $numeroPagos,
                    'total_intereses' => round($totalIntereses, 2),
                    'total_a_pagar' => round($montoFinanciar + $totalIntereses, 2),
                    'fecha_inicio' => $fechaPrimerPago,
                    'fecha_final' => (clone (new DateTime($fechaPrimerPago)))->modify('+' . $numeroPagos . ' months')->format('Y-m-d')
                ],
                'tabla_simulada' => $simulacion
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('obtener_resumen_amortizacion')) {
    /**
     * Obtiene el resumen de una tabla de amortización existente
     */
    function obtener_resumen_amortizacion(int $ventaId): array
    {
        $tablaModel = new TablaAmortizacionModel();
        $mensualidades = $tablaModel->getByVenta($ventaId);
        
        if (empty($mensualidades)) {
            return [
                'success' => false,
                'error' => 'No existe tabla de amortización para esta venta'
            ];
        }

        $resumen = [
            'total_mensualidades' => count($mensualidades),
            'mensualidades_pagadas' => 0,
            'mensualidades_pendientes' => 0,
            'mensualidades_vencidas' => 0,
            'monto_total_tabla' => 0,
            'monto_pagado' => 0,
            'saldo_pendiente' => 0,
            'intereses_moratorios' => 0,
            'proxima_mensualidad' => null,
            'porcentaje_avance' => 0
        ];

        foreach ($mensualidades as $mensualidad) {
            $resumen['monto_total_tabla'] += $mensualidad->monto_total;
            $resumen['monto_pagado'] += $mensualidad->monto_pagado;
            $resumen['saldo_pendiente'] += $mensualidad->saldo_pendiente;
            $resumen['intereses_moratorios'] += $mensualidad->interes_moratorio;

            switch ($mensualidad->estatus) {
                case 'pagada':
                    $resumen['mensualidades_pagadas']++;
                    break;
                case 'vencida':
                    $resumen['mensualidades_vencidas']++;
                    $resumen['mensualidades_pendientes']++;
                    break;
                default:
                    $resumen['mensualidades_pendientes']++;
                    break;
            }

            // Determinar próxima mensualidad
            if (!$resumen['proxima_mensualidad'] && $mensualidad->estatus !== 'pagada') {
                $resumen['proxima_mensualidad'] = [
                    'numero_pago' => $mensualidad->numero_pago,
                    'fecha_vencimiento' => $mensualidad->fecha_vencimiento,
                    'monto' => $mensualidad->monto_total,
                    'dias_para_vencer' => (new DateTime())->diff(new DateTime($mensualidad->fecha_vencimiento))->days
                ];
            }
        }

        // Calcular porcentaje de avance
        if ($resumen['monto_total_tabla'] > 0) {
            $resumen['porcentaje_avance'] = round(($resumen['monto_pagado'] / $resumen['monto_total_tabla']) * 100, 2);
        }

        return [
            'success' => true,
            'venta_id' => $ventaId,
            'resumen' => $resumen,
            'mensualidades' => $mensualidades
        ];
    }
}