<?php

use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\ClienteModel;

/**
 * Helper para gestión de estados de cuenta
 */

if (!function_exists('generar_resumen_cliente')) {
    /**
     * Genera un resumen completo del estado de cuenta de un cliente
     */
    function generar_resumen_cliente(int $clienteId): array
    {
        // Obtener datos del cliente
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->find($clienteId);
        
        if (!$cliente) {
            return [
                'success' => false,
                'error' => 'Cliente no encontrado'
            ];
        }

        // Obtener resumen financiero usando VentaModel
        $ventaModel = new VentaModel();
        $resumenFinanciero = $ventaModel->getResumenFinanciero($clienteId);
        
        // Obtener mensualidades vencidas
        $tablaModel = new TablaAmortizacionModel();
        $mensualidadesVencidas = $tablaModel->getMensualidadesVencidas($clienteId);
        
        // Obtener próximas mensualidades
        $proximasMensualidades = $ventaModel->getProximasMensualidadesCliente($clienteId, 30);
        
        // Obtener historial de pagos recientes
        $pagoModel = new PagoVentaModel();
        $pagosRecientes = $pagoModel->buscarPagos([
            'cliente_id' => $clienteId,
            'estatus_pago' => 'aplicado',
            'orden_por' => 'pv.fecha_pago',
            'direccion' => 'DESC',
            'limite' => 10
        ]);

        // Calcular estadísticas adicionales
        $estadisticas = [
            'total_vencido' => 0,
            'dias_atraso_promedio' => 0,
            'monto_moratorios' => 0,
            'proximo_vencimiento' => null,
            'comportamiento_pago' => 'regular' // bueno, regular, malo
        ];

        if (count($mensualidadesVencidas) > 0) {
            $totalDiasAtraso = 0;
            foreach ($mensualidadesVencidas as $vencida) {
                $estadisticas['total_vencido'] += $vencida->getSaldoTotalPendiente();
                $estadisticas['monto_moratorios'] += $vencida->interes_moratorio;
                $totalDiasAtraso += $vencida->dias_atraso;
            }
            $estadisticas['dias_atraso_promedio'] = round($totalDiasAtraso / count($mensualidadesVencidas));
            
            // Determinar comportamiento
            if ($estadisticas['dias_atraso_promedio'] > 30) {
                $estadisticas['comportamiento_pago'] = 'malo';
            } elseif ($estadisticas['dias_atraso_promedio'] > 7) {
                $estadisticas['comportamiento_pago'] = 'regular';
            }
        } else {
            $estadisticas['comportamiento_pago'] = 'bueno';
        }

        // Determinar próximo vencimiento
        if (!empty($proximasMensualidades)) {
            $proximoVencimiento = $proximasMensualidades[0];
            $estadisticas['proximo_vencimiento'] = [
                'fecha' => $proximoVencimiento['mensualidad']->fecha_vencimiento,
                'monto' => $proximoVencimiento['mensualidad']->monto_total,
                'dias_restantes' => $proximoVencimiento['dias_hasta_vencimiento'],
                'numero_pago' => $proximoVencimiento['mensualidad']->numero_pago
            ];
        }

        return [
            'success' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nombre_completo' => trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno),
                'email' => $cliente->email,
                'telefono' => $cliente->telefono
            ],
            'resumen_financiero' => $resumenFinanciero,
            'estadisticas' => $estadisticas,
            'mensualidades_vencidas' => $mensualidadesVencidas,
            'proximas_mensualidades' => $proximasMensualidades,
            'pagos_recientes' => $pagosRecientes,
            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }
}

if (!function_exists('calcular_dias_atraso')) {
    /**
     * Calcula los días de atraso de una fecha de vencimiento
     * Reutiliza lógica del InteresesService
     */
    function calcular_dias_atraso(string $fechaVencimiento): int
    {
        // Usar el método del InteresesService si está disponible
        if (class_exists('\App\Services\InteresesService')) {
            $service = new \App\Services\InteresesService();
            $reflector = new \ReflectionClass($service);
            $method = $reflector->getMethod('calcularDiasAtraso');
            $method->setAccessible(true);
            return $method->invoke($service, $fechaVencimiento);
        }
        
        // Fallback si el servicio no está disponible
        $hoy = new DateTime();
        $vencimiento = new DateTime($fechaVencimiento);
        
        if ($hoy <= $vencimiento) {
            return 0;
        }
        
        $diferencia = $hoy->diff($vencimiento);
        return $diferencia->days;
    }
}

if (!function_exists('calcular_interes_moratorio')) {
    /**
     * Calcula el interés moratorio con base en monto, días y tasa
     * Compatible con la lógica de InteresesService
     */
    function calcular_interes_moratorio(float $monto, int $diasAtraso, float $tasaAnual = 36.0): float
    {
        if ($diasAtraso <= 0 || $monto <= 0) {
            return 0.0;
        }
        
        // Convertir tasa anual a diaria (misma fórmula que InteresesService)
        $tasaDiaria = $tasaAnual / 365 / 100;
        
        // Calcular interés moratorio
        $interesMoratorio = $monto * $tasaDiaria * $diasAtraso;
        
        // Aplicar validación de precisión si existe el método
        if (method_exists('\App\Services\VentaCalculoService', 'validarPrecision')) {
            $calculoService = new \App\Services\VentaCalculoService();
            return $calculoService->validarPrecision($interesMoratorio);
        }
        
        return round($interesMoratorio, 2);
    }
}

if (!function_exists('formatear_estado_cuenta')) {
    /**
     * Formatea los datos del estado de cuenta para presentación
     */
    function formatear_estado_cuenta(array $datos): array
    {
        if (!isset($datos['success']) || !$datos['success']) {
            return $datos;
        }

        $formateado = [
            'cliente' => $datos['cliente'],
            'resumen_general' => [
                'total_propiedades' => $datos['resumen_financiero']['total_propiedades'],
                'monto_total_invertido' => '$' . number_format($datos['resumen_financiero']['monto_total_ventas'], 2),
                'saldo_total_pendiente' => '$' . number_format($datos['resumen_financiero']['saldo_total_pendiente'], 2),
                'total_pagado' => '$' . number_format($datos['resumen_financiero']['total_pagado'], 2),
                'porcentaje_liquidacion' => number_format($datos['resumen_financiero']['porcentaje_liquidacion_promedio'], 1) . '%',
                'propiedades_con_atrasos' => $datos['resumen_financiero']['propiedades_con_atrasos']
            ],
            'indicadores' => [
                'comportamiento_pago' => ucfirst($datos['estadisticas']['comportamiento_pago']),
                'total_vencido' => '$' . number_format($datos['estadisticas']['total_vencido'], 2),
                'dias_atraso_promedio' => $datos['estadisticas']['dias_atraso_promedio'],
                'moratorios_acumulados' => '$' . number_format($datos['estadisticas']['monto_moratorios'], 2)
            ],
            'proximo_vencimiento' => null,
            'mensualidades_vencidas' => [],
            'proximas_mensualidades' => [],
            'propiedades' => []
        ];

        // Formatear próximo vencimiento
        if (!empty($datos['estadisticas']['proximo_vencimiento'])) {
            $proximo = $datos['estadisticas']['proximo_vencimiento'];
            $formateado['proximo_vencimiento'] = [
                'fecha' => date('d/m/Y', strtotime($proximo['fecha'])),
                'monto' => '$' . number_format($proximo['monto'], 2),
                'dias_restantes' => $proximo['dias_restantes'],
                'numero_pago' => $proximo['numero_pago'],
                'estado' => $proximo['dias_restantes'] <= 7 ? 'urgente' : 'normal'
            ];
        }

        // Formatear mensualidades vencidas
        foreach ($datos['mensualidades_vencidas'] as $vencida) {
            $formateado['mensualidades_vencidas'][] = [
                'venta_folio' => $vencida->folio_venta ?? 'N/A',
                'numero_pago' => $vencida->numero_pago,
                'fecha_vencimiento' => date('d/m/Y', strtotime($vencida->fecha_vencimiento)),
                'dias_atraso' => $vencida->dias_atraso,
                'monto_pendiente' => '$' . number_format($vencida->getSaldoTotalPendiente(), 2),
                'interes_moratorio' => '$' . number_format($vencida->interes_moratorio, 2)
            ];
        }

        // Formatear próximas mensualidades
        foreach ($datos['proximas_mensualidades'] as $proxima) {
            $mensualidad = $proxima['mensualidad'];
            $formateado['proximas_mensualidades'][] = [
                'venta_folio' => $proxima['folio_venta'],
                'numero_pago' => $mensualidad->numero_pago,
                'fecha_vencimiento' => date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)),
                'dias_hasta_vencimiento' => $proxima['dias_hasta_vencimiento'],
                'monto' => '$' . number_format($mensualidad->monto_total, 2),
                'estado' => $proxima['esta_vencida'] ? 'vencida' : 
                           ($proxima['dias_hasta_vencimiento'] <= 7 ? 'proximo' : 'normal')
            ];
        }

        // Formatear detalle de propiedades
        if (isset($datos['resumen_financiero']['detalle_propiedades'])) {
            foreach ($datos['resumen_financiero']['detalle_propiedades'] as $propiedad) {
                $formateado['propiedades'][] = [
                    'venta_id' => $propiedad['venta_id'],
                    'folio' => $propiedad['folio_venta'],
                    'descripcion' => $propiedad['descripcion_lote'],
                    'proyecto' => $propiedad['proyecto_nombre'],
                    'precio_venta' => '$' . number_format($propiedad['resumen_financiero']['precio_venta'], 2),
                    'saldo_pendiente' => '$' . number_format($propiedad['resumen_financiero']['saldo_pendiente'], 2),
                    'porcentaje_pagado' => number_format($propiedad['resumen_financiero']['porcentaje_liquidacion'], 1) . '%',
                    'estado' => $propiedad['estado_visual']['estatus_formateado'],
                    'estado_visual' => $propiedad['estado_visual'],
                    'proxima_mensualidad' => $propiedad['dias_proxima_mensualidad']
                ];
            }
        }

        // Agregar metadatos
        $formateado['metadata'] = [
            'fecha_generacion' => $datos['fecha_generacion'],
            'fecha_formato' => date('d/m/Y H:i:s', strtotime($datos['fecha_generacion'])),
            'moneda' => 'MXN'
        ];

        return $formateado;
    }
}

if (!function_exists('generar_alertas_vencimiento')) {
    /**
     * Genera alertas de vencimiento para un cliente
     */
    function generar_alertas_vencimiento(int $clienteId): array
    {
        $ventaModel = new VentaModel();
        $tablaModel = new TablaAmortizacionModel();
        
        // Obtener mensualidades vencidas
        $mensualidadesVencidas = $tablaModel->getMensualidadesVencidas($clienteId);
        
        // Obtener próximas mensualidades (7 días)
        $proximasMensualidades = $ventaModel->getProximasMensualidadesCliente($clienteId, 7);
        
        $alertas = [
            'criticas' => [],
            'urgentes' => [],
            'proximas' => [],
            'total_alertas' => 0
        ];

        // Alertas críticas (vencidas más de 30 días)
        foreach ($mensualidadesVencidas as $vencida) {
            if ($vencida->dias_atraso > 30) {
                $alertas['criticas'][] = [
                    'tipo' => 'critica',
                    'titulo' => 'Mensualidad vencida crítica',
                    'mensaje' => sprintf(
                        'La mensualidad #%d está vencida hace %d días. Monto pendiente: $%s (incluye $%s de moratorios)',
                        $vencida->numero_pago,
                        $vencida->dias_atraso,
                        number_format($vencida->getSaldoTotalPendiente(), 2),
                        number_format($vencida->interes_moratorio, 2)
                    ),
                    'fecha_vencimiento' => $vencida->fecha_vencimiento,
                    'dias_atraso' => $vencida->dias_atraso,
                    'monto_total' => $vencida->getSaldoTotalPendiente(),
                    'acciones' => [
                        'pagar_ahora' => true,
                        'negociar_pago' => true,
                        'ver_detalle' => true
                    ]
                ];
            }
        }

        // Alertas urgentes (vencidas menos de 30 días)
        foreach ($mensualidadesVencidas as $vencida) {
            if ($vencida->dias_atraso <= 30) {
                $alertas['urgentes'][] = [
                    'tipo' => 'urgente',
                    'titulo' => 'Mensualidad vencida',
                    'mensaje' => sprintf(
                        'La mensualidad #%d venció hace %d días. Monto pendiente: $%s',
                        $vencida->numero_pago,
                        $vencida->dias_atraso,
                        number_format($vencida->getSaldoTotalPendiente(), 2)
                    ),
                    'fecha_vencimiento' => $vencida->fecha_vencimiento,
                    'dias_atraso' => $vencida->dias_atraso,
                    'monto_total' => $vencida->getSaldoTotalPendiente(),
                    'acciones' => [
                        'pagar_ahora' => true,
                        'ver_detalle' => true
                    ]
                ];
            }
        }

        // Alertas próximas (próximos 7 días)
        foreach ($proximasMensualidades as $proxima) {
            if (!$proxima['esta_vencida'] && $proxima['dias_hasta_vencimiento'] <= 7) {
                $mensualidad = $proxima['mensualidad'];
                $alertas['proximas'][] = [
                    'tipo' => 'proxima',
                    'titulo' => 'Próximo vencimiento',
                    'mensaje' => sprintf(
                        'La mensualidad #%d vence en %d días. Monto: $%s',
                        $mensualidad->numero_pago,
                        $proxima['dias_hasta_vencimiento'],
                        number_format($mensualidad->monto_total, 2)
                    ),
                    'fecha_vencimiento' => $mensualidad->fecha_vencimiento,
                    'dias_hasta_vencimiento' => $proxima['dias_hasta_vencimiento'],
                    'monto_total' => $mensualidad->monto_total,
                    'folio_venta' => $proxima['folio_venta'],
                    'acciones' => [
                        'pagar_anticipado' => true,
                        'programar_recordatorio' => true,
                        'ver_detalle' => true
                    ]
                ];
            }
        }

        // Calcular totales
        $alertas['total_alertas'] = count($alertas['criticas']) + count($alertas['urgentes']) + count($alertas['proximas']);
        
        // Agregar resumen
        $alertas['resumen'] = [
            'alertas_criticas' => count($alertas['criticas']),
            'alertas_urgentes' => count($alertas['urgentes']),
            'alertas_proximas' => count($alertas['proximas']),
            'monto_total_vencido' => array_sum(array_column($alertas['criticas'], 'monto_total')) + 
                                    array_sum(array_column($alertas['urgentes'], 'monto_total')),
            'requiere_atencion_inmediata' => count($alertas['criticas']) > 0
        ];

        // Agregar recomendaciones
        $alertas['recomendaciones'] = [];
        
        if ($alertas['resumen']['alertas_criticas'] > 0) {
            $alertas['recomendaciones'][] = [
                'prioridad' => 'alta',
                'mensaje' => 'Es urgente regularizar los pagos vencidos para evitar mayores intereses moratorios.',
                'accion' => 'contactar_asesor'
            ];
        }
        
        if ($alertas['resumen']['alertas_urgentes'] > 0) {
            $alertas['recomendaciones'][] = [
                'prioridad' => 'media',
                'mensaje' => 'Realice sus pagos pendientes lo antes posible para evitar cargos adicionales.',
                'accion' => 'pagar_en_linea'
            ];
        }
        
        if ($alertas['resumen']['alertas_proximas'] > 0) {
            $alertas['recomendaciones'][] = [
                'prioridad' => 'baja',
                'mensaje' => 'Programe sus próximos pagos para mantener su cuenta al corriente.',
                'accion' => 'programar_pago'
            ];
        }

        return $alertas;
    }
}

if (!function_exists('calcular_proyeccion_liquidacion')) {
    /**
     * Calcula la proyección de liquidación de una venta
     */
    function calcular_proyeccion_liquidacion(int $ventaId): array
    {
        $ventaModel = new VentaModel();
        $venta = $ventaModel->find($ventaId);
        
        if (!$venta) {
            return [
                'success' => false,
                'error' => 'Venta no encontrada'
            ];
        }

        $resumenFinanciero = $venta->getResumenFinanciero();
        $mensualidadesPendientes = $venta->getMensualidadesPendientes();
        
        // Calcular proyección
        $proyeccion = [
            'saldo_actual' => $resumenFinanciero['saldo_pendiente'],
            'mensualidades_restantes' => count($mensualidadesPendientes),
            'monto_mensualidad_promedio' => 0,
            'fecha_liquidacion_estimada' => null,
            'interes_total_proyectado' => 0,
            'ahorro_por_liquidacion_anticipada' => 0
        ];

        if (count($mensualidadesPendientes) > 0) {
            // Calcular promedio de mensualidades
            $totalMensualidades = array_sum(array_column($mensualidadesPendientes, 'monto_total'));
            $proyeccion['monto_mensualidad_promedio'] = $totalMensualidades / count($mensualidadesPendientes);
            
            // Estimar fecha de liquidación
            $ultimaMensualidad = end($mensualidadesPendientes);
            $proyeccion['fecha_liquidacion_estimada'] = $ultimaMensualidad->fecha_vencimiento;
            
            // Calcular interés total proyectado
            $proyeccion['interes_total_proyectado'] = array_sum(array_column($mensualidadesPendientes, 'interes'));
            
            // Calcular ahorro por liquidación anticipada (intereses futuros)
            $proyeccion['ahorro_por_liquidacion_anticipada'] = $proyeccion['interes_total_proyectado'];
        }

        return [
            'success' => true,
            'venta_id' => $ventaId,
            'proyeccion' => $proyeccion,
            'escenarios' => [
                'liquidacion_hoy' => [
                    'monto_pagar' => $resumenFinanciero['saldo_pendiente'],
                    'ahorro' => $proyeccion['interes_total_proyectado'],
                    'porcentaje_ahorro' => $proyeccion['interes_total_proyectado'] > 0 ? 
                        round(($proyeccion['interes_total_proyectado'] / $resumenFinanciero['saldo_pendiente']) * 100, 2) : 0
                ],
                'pago_puntual' => [
                    'fecha_termino' => $proyeccion['fecha_liquidacion_estimada'],
                    'total_pagar' => $resumenFinanciero['saldo_pendiente'] + $proyeccion['interes_total_proyectado'],
                    'mensualidades_restantes' => $proyeccion['mensualidades_restantes']
                ]
            ]
        ];
    }
}

if (!function_exists('generar_estado_cuenta_periodo')) {
    /**
     * Genera estado de cuenta por período específico
     */
    function generar_estado_cuenta_periodo(int $clienteId, string $fechaInicio, string $fechaFin): array
    {
        $pagoModel = new PagoVentaModel();
        
        // Obtener pagos del período
        $pagos = $pagoModel->buscarPagos([
            'cliente_id' => $clienteId,
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin,
            'estatus_pago' => 'aplicado'
        ]);

        // Agrupar por venta
        $pagosPorVenta = [];
        $totalPeriodo = 0;
        
        foreach ($pagos as $pago) {
            if (!isset($pagosPorVenta[$pago->venta_id])) {
                $pagosPorVenta[$pago->venta_id] = [
                    'folio_venta' => $pago->folio_venta,
                    'pagos' => [],
                    'total' => 0
                ];
            }
            
            $pagosPorVenta[$pago->venta_id]['pagos'][] = $pago;
            $pagosPorVenta[$pago->venta_id]['total'] += $pago->monto_pago;
            $totalPeriodo += $pago->monto_pago;
        }

        return [
            'cliente_id' => $clienteId,
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
                'dias' => (new DateTime($fechaInicio))->diff(new DateTime($fechaFin))->days
            ],
            'resumen' => [
                'total_pagos' => count($pagos),
                'monto_total' => $totalPeriodo,
                'ventas_con_pagos' => count($pagosPorVenta)
            ],
            'detalle_por_venta' => $pagosPorVenta,
            'pagos_detallados' => $pagos
        ];
    }
}