<?php

/**
 * Helper principal para pagos inmobiliarios
 * Funciones centrales para procesamiento de pagos por cliente-lote
 */

if (!function_exists('procesar_pago_inmobiliario')) {
    /**
     * Procesar pago inmobiliario según su tipo
     * 
     * @param array $datosPago Datos del pago a procesar
     * @return array Resultado del procesamiento
     */
    function procesar_pago_inmobiliario(array $datosPago): array
    {
        try {
            // Validar datos requeridos
            $camposRequeridos = ['venta_id', 'tipo_concepto', 'monto', 'fecha_pago'];
            foreach ($camposRequeridos as $campo) {
                if (empty($datosPago[$campo])) {
                    return [
                        'success' => false,
                        'error' => "Campo requerido faltante: {$campo}"
                    ];
                }
            }

            // Procesar según tipo de concepto
            switch ($datosPago['tipo_concepto']) {
                case 'apartado':
                    return procesar_pago_apartado($datosPago);
                
                case 'liquidacion_enganche':
                    return procesar_pago_liquidacion_enganche($datosPago);
                
                case 'mensualidad':
                    return procesar_pago_mensualidad($datosPago);
                
                case 'abono_capital':
                    return procesar_pago_abono_capital($datosPago);
                
                case 'liquidacion_total':
                    return procesar_pago_liquidacion_total($datosPago);
                
                default:
                    return [
                        'success' => false,
                        'error' => 'Tipo de concepto no válido: ' . $datosPago['tipo_concepto']
                    ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error procesando pago: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('obtener_historial_pagos_lote')) {
    /**
     * Obtener historial completo de pagos por cliente-lote
     * 
     * @param int $clienteId ID del cliente
     * @param int $loteId ID del lote
     * @return array Historial completo de pagos
     */
    function obtener_historial_pagos_lote(int $clienteId, int $loteId): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Obtener información base del cliente y lote
            $info = $db->query("
                SELECT 
                    c.id as cliente_id,
                    c.nombres,
                    c.apellido_paterno,
                    c.apellido_materno,
                    c.email,
                    c.telefono,
                    l.id as lote_id,
                    l.clave as lote_clave,
                    l.precio_total,
                    l.area,
                    p.nombre as proyecto_nombre,
                    v.id as venta_id,
                    v.folio_venta,
                    v.estatus_venta,
                    v.tipo_venta,
                    v.precio_venta_final,
                    v.fecha_venta
                FROM clientes c
                JOIN ventas v ON v.cliente_id = c.id
                JOIN lotes l ON l.id = v.lote_id
                JOIN proyectos p ON p.id = l.proyectos_id
                WHERE c.id = ? AND l.id = ?
            ", [$clienteId, $loteId])->getRow();

            if (!$info) {
                return [
                    'success' => false,
                    'error' => 'No se encontró información del cliente-lote'
                ];
            }

            // Obtener historial de pagos de apartado/enganche
            $historialApartado = obtener_historial_apartado($info->venta_id);
            
            // Obtener historial de cuenta de financiamiento
            $historialFinanciamiento = obtener_historial_financiamiento($info->venta_id);
            
            // Calcular resumen financiero
            $resumenFinanciero = calcular_resumen_cliente_lote($info, $historialApartado, $historialFinanciamiento);

            // Crear historial completo para determinar acciones
            $historialCompleto = [
                'info_base' => [
                    'cliente' => [
                        'id' => $info->cliente_id,
                        'nombre_completo' => trim($info->nombres . ' ' . $info->apellido_paterno . ' ' . $info->apellido_materno),
                        'email' => $info->email,
                        'telefono' => $info->telefono
                    ],
                    'lote' => [
                        'id' => $info->lote_id,
                        'clave' => $info->lote_clave,
                        'precio_venta' => $info->precio_total,
                        'superficie' => $info->area,
                        'proyecto' => $info->proyecto_nombre
                    ],
                    'venta' => [
                        'id' => $info->venta_id,
                        'folio' => $info->folio_venta,
                        'estatus' => $info->estatus_venta,
                        'tipo' => $info->tipo_venta,
                        'precio_final' => $info->precio_venta_final,
                        'fecha_venta' => $info->fecha_venta
                    ]
                ],
                'historial_apartado' => $historialApartado,
                'historial_financiamiento' => $historialFinanciamiento,
                'resumen_financiero' => $resumenFinanciero
            ];

            return [
                'success' => true,
                'info_base' => $historialCompleto['info_base'],
                'historial_apartado' => $historialApartado,
                'historial_financiamiento' => $historialFinanciamiento,
                'resumen_financiero' => $resumenFinanciero,
                'acciones_disponibles' => determinar_acciones_disponibles($historialCompleto),
                'timeline_pagos' => crear_timeline_pagos($historialCompleto),
                'graficos' => generar_datos_graficos($historialCompleto),
                'alertas' => generar_alertas($historialCompleto)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error obteniendo historial: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('obtener_historial_apartado')) {
    /**
     * Obtener historial de pagos de apartado/enganche
     * 
     * @param int $ventaId ID de la venta
     * @return array Historial de apartado
     */
    function obtener_historial_apartado(int $ventaId): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Obtener anticipos
            $anticipos = $db->query("
                SELECT 
                    id,
                    folio_apartado,
                    monto_apartado,
                    monto_enganche_requerido,
                    fecha_apartado,
                    fecha_limite_enganche,
                    estatus_apartado,
                    created_at
                FROM apartados 
                WHERE venta_id = ? 
                ORDER BY fecha_apartado DESC
            ", [$ventaId])->getResult();

            // Obtener liquidación de enganche desde conceptos_pago
            $liquidacion = $db->query("
                SELECT 
                    id,
                    monto,
                    fecha_aplicacion,
                    estado,
                    created_at
                FROM conceptos_pago 
                WHERE venta_id = ? AND tipo_concepto = 'liquidacion_enganche'
                ORDER BY fecha_aplicacion DESC
                LIMIT 1
            ", [$ventaId])->getRow();

            // Obtener pagos directos al enganche (si los hay)
            $pagosDirectos = $db->query("
                SELECT 
                    id,
                    tipo_concepto,
                    monto,
                    fecha_aplicacion,
                    estado,
                    created_at
                FROM conceptos_pago 
                WHERE venta_id = ? 
                AND tipo_concepto = 'liquidacion_enganche'
                ORDER BY fecha_aplicacion DESC
            ", [$ventaId])->getResult();
            
            // Obtener todos los pagos de la venta (incluyendo el primer pago)
            $todosPagos = $db->query("
                SELECT 
                    pv.id,
                    pv.folio_pago as folio,
                    pv.monto_pago as monto,
                    pv.fecha_pago,
                    pv.concepto_pago as concepto,
                    pv.forma_pago,
                    pv.estatus_pago as estado,
                    pv.created_at
                FROM pagos_ventas pv
                WHERE pv.venta_id = ?
                AND pv.estatus_pago = 'aplicado'
                ORDER BY pv.fecha_pago ASC
            ", [$ventaId])->getResult();

            return [
                'anticipos' => $anticipos ?: [],
                'liquidacion' => $liquidacion,
                'pagos_directos' => $pagosDirectos ?: [],
                'todos_pagos' => $todosPagos ?: [],
                'total_anticipos' => array_sum(array_column($anticipos, 'monto_apartado')),
                'enganche_completado' => $liquidacion ? $liquidacion->estado === 'COMPLETADO' : false
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo historial apartado: ' . $e->getMessage());
            return [
                'anticipos' => [],
                'liquidacion' => null,
                'pagos_directos' => [],
                'todos_pagos' => [],
                'total_anticipos' => 0,
                'enganche_completado' => false
            ];
        }
    }
}

if (!function_exists('obtener_historial_financiamiento')) {
    /**
     * Obtener historial de cuenta de financiamiento
     * 
     * @param int $ventaId ID de la venta
     * @return array Historial de financiamiento
     */
    function obtener_historial_financiamiento(int $ventaId): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Obtener cuenta de financiamiento
            $cuenta = $db->query("
                SELECT 
                    id,
                    venta_id,
                    saldo_inicial,
                    saldo_actual,
                    fecha_apertura,
                    created_at,
                    updated_at
                FROM cuentas_financiamiento 
                WHERE venta_id = ?
            ", [$ventaId])->getRow();

            if (!$cuenta) {
                return [
                    'cuenta' => null,
                    'movimientos' => [],
                    'mensualidades' => [],
                    'cuenta_activa' => false
                ];
            }

            // Obtener movimientos de la cuenta desde conceptos_pago
            $movimientos = $db->query("
                SELECT 
                    id,
                    tipo_concepto,
                    monto,
                    fecha_aplicacion,
                    estado,
                    created_at
                FROM conceptos_pago 
                WHERE venta_id = ?
                ORDER BY fecha_aplicacion DESC
                LIMIT 50
            ", [$ventaId])->getResult();

            // Obtener tabla de amortización específica de esta venta
            $mensualidades = $db->query("
                SELECT 
                    id,
                    numero_pago,
                    fecha_vencimiento,
                    saldo_inicial,
                    capital,
                    interes,
                    monto_total,
                    monto_pagado,
                    saldo_pendiente,
                    estatus,
                    dias_atraso,
                    interes_moratorio,
                    fecha_ultimo_pago
                FROM tabla_amortizacion 
                WHERE venta_id = ?
                ORDER BY numero_pago ASC
            ", [$ventaId])->getResult();

            return [
                'cuenta' => $cuenta,
                'movimientos' => $movimientos ?: [],
                'mensualidades' => $mensualidades ?: [],
                'cuenta_activa' => true // Simplificado por ahora
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo historial financiamiento: ' . $e->getMessage());
            return [
                'cuenta' => null,
                'movimientos' => [],
                'mensualidades' => [],
                'cuenta_activa' => false
            ];
        }
    }
}

if (!function_exists('calcular_resumen_cliente_lote')) {
    /**
     * Calcular resumen financiero completo del cliente-lote
     * 
     * @param object $info Información base
     * @param array $historialApartado Historial de apartado
     * @param array $historialFinanciamiento Historial de financiamiento
     * @return array Resumen financiero
     */
    function calcular_resumen_cliente_lote($info, $historialApartado, $historialFinanciamiento): array
    {
        // Obtener datos del perfil de financiamiento
        $db = \Config\Database::connect();
        $perfil = $db->query("
            SELECT 
                porcentaje_anticipo,
                promocion_cero_enganche,
                meses_sin_intereses,
                meses_con_intereses
            FROM perfiles_financiamiento pf
            JOIN ventas v ON v.perfil_financiamiento_id = pf.id
            WHERE v.id = ?
        ", [$info->venta_id])->getRow();

        // Calcular enganche según el perfil
        $porcentajeEnganche = $perfil ? $perfil->porcentaje_anticipo : 20;
        $esCeroEnganche = $perfil ? $perfil->promocion_cero_enganche : 0;
        $engancheRequerido = $esCeroEnganche ? 0 : ($info->precio_venta_final * ($porcentajeEnganche / 100));

        $resumen = [
            'precio_venta' => $info->precio_venta_final,
            'enganche_requerido' => $engancheRequerido,
            'enganche_pagado' => 0,
            'saldo_enganche' => 0,
            'porcentaje_enganche' => 0,
            'capital_financiado' => 0,
            'saldo_capital' => 0,
            'total_pagado' => 0,
            'saldo_total_pendiente' => 0,
            'porcentaje_avance' => 0,
            'estatus_financiero' => 'Sin información',
            'proxima_accion' => 'Evaluar',
            'al_corriente' => true,
            'dias_atraso' => 0,
            'es_cero_enganche' => $esCeroEnganche,
            'porcentaje_anticipo' => $porcentajeEnganche
        ];

        // Calcular datos del enganche
        if ($historialApartado['liquidacion']) {
            $resumen['enganche_pagado'] = $historialApartado['liquidacion']->monto ?? 0;
            $resumen['saldo_enganche'] = max(0, $resumen['enganche_requerido'] - $resumen['enganche_pagado']);
        } else {
            $resumen['enganche_pagado'] = $historialApartado['total_anticipos'];
            $resumen['saldo_enganche'] = $resumen['enganche_requerido'] - $historialApartado['total_anticipos'];
        }

        $resumen['porcentaje_enganche'] = $resumen['enganche_requerido'] > 0 ? 
            ($resumen['enganche_pagado'] / $resumen['enganche_requerido']) * 100 : 0;

        // Calcular datos del financiamiento
        if ($historialFinanciamiento['cuenta']) {
            $cuenta = $historialFinanciamiento['cuenta'];
            $resumen['capital_financiado'] = $cuenta->saldo_inicial ?? 0;
            
            // Si es cero enganche, obtener pagos aplicados desde ingresos
            if ($resumen['es_cero_enganche']) {
                $db = \Config\Database::connect();
                $totalPagosAplicados = $db->query("
                    SELECT COALESCE(SUM(monto), 0) as total_pagado 
                    FROM ingresos 
                    WHERE venta_id = ? AND tipo_ingreso IN ('enganche', 'mensualidad', 'abono_capital')
                ", [$info->venta_id])->getRow()->total_pagado ?? 0;
                
                $resumen['total_pagado'] = $totalPagosAplicados;
                $resumen['saldo_capital'] = $resumen['capital_financiado'] - $totalPagosAplicados;
            } else {
                $resumen['saldo_capital'] = $cuenta->saldo_actual ?? 0;
                $resumen['total_pagado'] = ($cuenta->saldo_inicial ?? 0) - ($cuenta->saldo_actual ?? 0);
            }
            
            $resumen['saldo_total_pendiente'] = $resumen['saldo_capital'];
            $resumen['al_corriente'] = true; // Simplificado por ahora
            
            // Calcular porcentaje de avance total
            $totalContrato = $resumen['precio_venta'];
            $totalPagadoCompleto = $resumen['enganche_pagado'] + $resumen['total_pagado'];
            $resumen['porcentaje_avance'] = $totalContrato > 0 ? 
                ($totalPagadoCompleto / $totalContrato) * 100 : 0;
        }

        // Determinar estatus financiero
        if ($resumen['saldo_enganche'] > 0) {
            $resumen['estatus_financiero'] = 'Enganche pendiente';
            $resumen['proxima_accion'] = 'Completar enganche';
        } elseif ($historialFinanciamiento['cuenta_activa']) {
            if ($resumen['al_corriente']) {
                $resumen['estatus_financiero'] = 'Financiamiento activo';
                $resumen['proxima_accion'] = 'Seguir pagando mensualidades';
            } else {
                $resumen['estatus_financiero'] = 'Con atraso';
                $resumen['proxima_accion'] = 'Ponerse al corriente';
            }
        } elseif ($resumen['porcentaje_avance'] >= 100) {
            $resumen['estatus_financiero'] = 'Liquidado';
            $resumen['proxima_accion'] = 'Completado';
        } else {
            $resumen['estatus_financiero'] = 'En proceso';
            $resumen['proxima_accion'] = 'Verificar siguiente paso';
        }

        return $resumen;
    }
}

if (!function_exists('crear_vista_detalle_lote')) {
    /**
     * Crear datos para la vista de detalle de lote
     * 
     * @param int $clienteId ID del cliente
     * @param int $loteId ID del lote
     * @return array Datos para la vista
     */
    function crear_vista_detalle_lote(int $clienteId, int $loteId): array
    {
        try {
            // Obtener historial completo
            $historial = obtener_historial_pagos_lote($clienteId, $loteId);
            
            if (!$historial['success']) {
                return $historial;
            }

            // Preparar datos para la vista
            $datosVista = [
                'info_base' => $historial['info_base'],
                'resumen_financiero' => $historial['resumen_financiero'],
                'timeline_pagos' => crear_timeline_pagos($historial),
                'acciones_disponibles' => determinar_acciones_disponibles($historial),
                'graficos' => generar_datos_graficos($historial),
                'alertas' => generar_alertas($historial)
            ];

            return [
                'success' => true,
                'datos_vista' => $datosVista
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error creando vista: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('crear_timeline_pagos')) {
    /**
     * Crear timeline de pagos para la vista
     * 
     * @param array $historial Historial completo
     * @return array Timeline de pagos
     */
    function crear_timeline_pagos(array $historial): array
    {
        $timeline = [];

        // Agregar anticipos
        foreach ($historial['historial_apartado']['anticipos'] as $anticipo) {
            $timeline[] = [
                'fecha' => $anticipo->fecha_anticipo,
                'tipo' => 'anticipo',
                'concepto' => 'Anticipo de Apartado #' . $anticipo->numero_anticipo,
                'monto' => $anticipo->monto_anticipo,
                'estado' => $anticipo->estado,
                'icono' => 'fas fa-hand-holding-usd',
                'color' => 'info'
            ];
        }

        // Agregar liquidación de enganche
        if ($historial['historial_apartado']['liquidacion']) {
            $liquidacion = $historial['historial_apartado']['liquidacion'];
            $timeline[] = [
                'fecha' => $liquidacion->fecha_liquidacion,
                'tipo' => 'liquidacion',
                'concepto' => 'Liquidación de Enganche',
                'monto' => $liquidacion->monto_total_liquidado,
                'estado' => $liquidacion->estado,
                'icono' => 'fas fa-check-circle',
                'color' => 'success'
            ];
        }

        // Agregar movimientos de financiamiento
        foreach ($historial['historial_financiamiento']['movimientos'] as $movimiento) {
            if ($movimiento->tipo_movimiento === 'abono') {
                $timeline[] = [
                    'fecha' => $movimiento->fecha_movimiento,
                    'tipo' => 'pago',
                    'concepto' => $movimiento->descripcion,
                    'monto' => $movimiento->monto_abono,
                    'estado' => 'aplicado',
                    'icono' => 'fas fa-money-bill-wave',
                    'color' => 'primary'
                ];
            }
        }

        // Ordenar por fecha descendente
        usort($timeline, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return $timeline;
    }
}

if (!function_exists('determinar_acciones_disponibles')) {
    /**
     * Determinar acciones disponibles según el estado
     * 
     * @param array $historial Historial completo
     * @return array Acciones disponibles
     */
    function determinar_acciones_disponibles(array $historial): array
    {
        $acciones = [];
        $info = $historial['info_base'];
        $resumen = $historial['resumen_financiero'];

        // Acciones para apartado
        if ($resumen['saldo_enganche'] > 0) {
            $acciones[] = [
                'tipo' => 'procesar_anticipo',
                'texto' => 'Procesar Anticipo',
                'icono' => 'fas fa-plus-circle',
                'clase' => 'btn-primary',
                'url' => site_url("admin/pagos/procesar-anticipo/{$info['venta']['id']}")
            ];
        }

        // Acciones para financiamiento
        if ($historial['historial_financiamiento']['cuenta_activa']) {
            $acciones[] = [
                'tipo' => 'procesar_mensualidad',
                'texto' => 'Procesar Mensualidad',
                'icono' => 'fas fa-calendar-check',
                'clase' => 'btn-success',
                'url' => site_url("admin/pagos/procesar-mensualidad/{$info['venta']['id']}")
            ];

            $acciones[] = [
                'tipo' => 'abono_capital',
                'texto' => 'Abono a Capital',
                'icono' => 'fas fa-arrow-up',
                'clase' => 'btn-info',
                'url' => site_url("admin/pagos/abono-capital/{$info['venta']['id']}")
            ];
        }

        // Acciones generales
        $acciones[] = [
            'tipo' => 'generar_estado',
            'texto' => 'Generar Estado de Cuenta',
            'icono' => 'fas fa-file-pdf',
            'clase' => 'btn-secondary',
            'url' => site_url("admin/estado-cuenta/generar/{$info['venta']['id']}")
        ];

        return $acciones;
    }
}

if (!function_exists('generar_datos_graficos')) {
    /**
     * Generar datos para gráficos
     * 
     * @param array $historial Historial completo
     * @return array Datos para gráficos
     */
    function generar_datos_graficos(array $historial): array
    {
        $resumen = $historial['resumen_financiero'];
        
        return [
            'avance_pago' => [
                'pagado' => $resumen['porcentaje_avance'],
                'pendiente' => 100 - $resumen['porcentaje_avance']
            ],
            'distribucion_enganche' => [
                'pagado' => $resumen['porcentaje_enganche'],
                'pendiente' => 100 - $resumen['porcentaje_enganche']
            ]
        ];
    }
}

if (!function_exists('generar_alertas')) {
    /**
     * Generar alertas según el estado
     * 
     * @param array $historial Historial completo
     * @return array Alertas
     */
    function generar_alertas(array $historial): array
    {
        $alertas = [];
        $resumen = $historial['resumen_financiero'];

        // Alerta de enganche pendiente
        if ($resumen['saldo_enganche'] > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'fas fa-exclamation-triangle',
                'titulo' => 'Enganche Pendiente',
                'mensaje' => 'Falta por liquidar: $' . number_format($resumen['saldo_enganche'], 2)
            ];
        }

        // Alerta de pagos vencidos
        if (!$resumen['al_corriente']) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'fas fa-times-circle',
                'titulo' => 'Pagos Vencidos',
                'mensaje' => 'Cuenta con pagos vencidos. Requiere atención inmediata.'
            ];
        }

        return $alertas;
    }
}