<?php

/**
 * Helper para manejo de templates de recibos
 * Implementa diferentes tipos de recibos según las buenas prácticas de CodeIgniter 4
 */

if (!function_exists('determinar_tipo_recibo')) {
    /**
     * Determina el tipo de recibo basándose en los datos del ingreso
     */
    function determinar_tipo_recibo(object $ingreso): string
    {
        // Prioridad 1: Tipo de ingreso explícito
        if (!empty($ingreso->tipo_ingreso)) {
            switch (strtolower($ingreso->tipo_ingreso)) {
                case 'apartado':
                case 'anticipo':
                    return 'apartado_anticipo';
                case 'enganche':
                    return 'pago_enganche';
                case 'mensualidad':
                    return 'mensualidad_ordinaria';
                case 'mensualidad_mora':
                    return 'mensualidad_mora';
                case 'liquidacion':
                case 'liquidacion_inmueble':
                    return 'liquidacion_inmueble';
                case 'otros':
                default:
                    return 'pago_general';
            }
        }
        
        // Prioridad 2: Relación con apartado
        if (!empty($ingreso->apartado_id)) {
            return 'apartado_anticipo';
        }
        
        // Prioridad 3: Relación con venta
        if (!empty($ingreso->venta_id)) {
            return 'pago_enganche';
        }
        
        // Default
        return 'pago_general';
    }
}

if (!function_exists('obtener_template_recibo')) {
    /**
     * Obtiene el nombre del template según el tipo de recibo
     */
    function obtener_template_recibo(string $tipoRecibo): string
    {
        $templates = [
            'apartado_anticipo' => 'templates/recibos/apartado_anticipo',
            'pago_enganche' => 'templates/recibos/pago_enganche', 
            'mensualidad_ordinaria' => 'templates/recibos/mensualidad_ordinaria',
            'mensualidad_mora' => 'templates/recibos/mensualidad_mora',
            'liquidacion_inmueble' => 'templates/recibos/liquidacion_inmueble',
            'pago_general' => 'templates/recibos/pago_general'
        ];
        
        return $templates[$tipoRecibo] ?? $templates['pago_general'];
    }
}

if (!function_exists('preparar_datos_recibo_especializado')) {
    /**
     * Prepara datos específicos según el tipo de recibo
     */
    function preparar_datos_recibo_especializado(object $ingreso, string $tipoRecibo): array
    {
        $datosBase = [
            'tipo_recibo' => $tipoRecibo,
            'titulo_recibo' => obtener_titulo_recibo($tipoRecibo),
            'descripcion_pago' => obtener_descripcion_pago($tipoRecibo),
            'observaciones_especiales' => obtener_observaciones_recibo($tipoRecibo),
            'empresa' => obtener_empresa_recibo(),
            'fecha_emision' => date('d/m/Y H:i:s'),
            'fecha_formateada' => formatear_fecha_espanol($ingreso->fecha_ingreso ?? date('Y-m-d')),
            'monto_letras' => convertir_numero_a_letras($ingreso->monto ?? 0),
            'monto_formateado' => formatear_moneda_mexicana($ingreso->monto ?? 0)
        ];
        
        // Datos específicos por tipo
        switch ($tipoRecibo) {
            case 'apartado_anticipo':
                $datosBase = array_merge($datosBase, [
                    'concepto_principal' => 'ANTICIPO PARA APARTADO DE LOTE',
                    'validez_apartado' => '90 días naturales',
                    'nota_importante' => 'Este anticipo reserva el lote por el tiempo especificado'
                ]);
                break;
                
            case 'pago_enganche':
                $datosBase = array_merge($datosBase, [
                    'concepto_principal' => 'PAGO DE ENGANCHE',
                    'nota_importante' => 'Pago correspondiente al enganche del inmueble'
                ]);
                break;
                
            case 'mensualidad_ordinaria':
                $datosBase = array_merge($datosBase, [
                    'concepto_principal' => 'PAGO DE MENSUALIDAD',
                    'periodo_pago' => determinar_periodo_mensualidad($ingreso),
                    'nota_importante' => 'Pago de mensualidad correspondiente al periodo indicado'
                ]);
                break;
                
            case 'mensualidad_mora':
                $datosBase = array_merge($datosBase, [
                    'concepto_principal' => 'PAGO DE MENSUALIDAD CON RECARGO',
                    'periodo_pago' => determinar_periodo_mensualidad($ingreso),
                    'recargo_mora' => calcular_recargo_mora($ingreso),
                    'nota_importante' => 'Pago incluye recargo por mora'
                ]);
                break;
                
            case 'liquidacion_inmueble':
                $datosBase = array_merge($datosBase, [
                    'concepto_principal' => 'LIQUIDACIÓN TOTAL DEL INMUEBLE',
                    'nota_importante' => 'Pago final que liquida totalmente el inmueble'
                ]);
                break;
                
            default:
                $datosBase = array_merge($datosBase, [
                    'concepto_principal' => 'PAGO DE SERVICIOS INMOBILIARIOS',
                    'nota_importante' => 'Comprobante de pago de servicios'
                ]);
                break;
        }
        
        return $datosBase;
    }
}

if (!function_exists('obtener_titulo_recibo')) {
    /**
     * Obtiene el título específico para cada tipo de recibo
     */
    function obtener_titulo_recibo(string $tipoRecibo): string
    {
        $titulos = [
            'apartado_anticipo' => 'RECIBO DE APARTADO',
            'pago_enganche' => 'RECIBO DE ENGANCHE',
            'mensualidad_ordinaria' => 'RECIBO DE MENSUALIDAD',
            'mensualidad_mora' => 'RECIBO DE MENSUALIDAD CON MORA',
            'liquidacion_inmueble' => 'RECIBO DE LIQUIDACIÓN',
            'pago_general' => 'RECIBO DE PAGO'
        ];
        
        return $titulos[$tipoRecibo] ?? $titulos['pago_general'];
    }
}

if (!function_exists('obtener_descripcion_pago')) {
    /**
     * Obtiene la descripción del concepto de pago
     */
    function obtener_descripcion_pago(string $tipoRecibo): string
    {
        $descripciones = [
            'apartado_anticipo' => 'Anticipo para reserva de lote',
            'pago_enganche' => 'Enganche del inmueble seleccionado',
            'mensualidad_ordinaria' => 'Mensualidad ordinaria',
            'mensualidad_mora' => 'Mensualidad con recargo por mora',
            'liquidacion_inmueble' => 'Liquidación total del inmueble',
            'pago_general' => 'Pago de servicios inmobiliarios'
        ];
        
        return $descripciones[$tipoRecibo] ?? $descripciones['pago_general'];
    }
}

if (!function_exists('obtener_observaciones_recibo')) {
    /**
     * Obtiene observaciones específicas para cada tipo de recibo
     */
    function obtener_observaciones_recibo(string $tipoRecibo): array
    {
        $observaciones = [
            'apartado_anticipo' => [
                '• Este apartado tiene vigencia de 90 días naturales',
                '• Para conservar el lote debe liquidar el enganche antes del vencimiento',
                '• El anticipo no es reembolsable una vez vencido el plazo',
                '• Conserve este recibo como comprobante de apartado'
            ],
            'pago_enganche' => [
                '• Pago correspondiente al enganche del inmueble',
                '• Con este pago inicia el proceso de financiamiento',
                '• Conserve este recibo para sus registros contables',
                '• Documento generado automáticamente por el sistema'
            ],
            'mensualidad_ordinaria' => [
                '• Pago de mensualidad en tiempo y forma',
                '• Conserve este recibo como comprobante de pago',
                '• Próximo vencimiento consulte su estado de cuenta',
                '• Evite recargos pagando antes de la fecha límite'
            ],
            'mensualidad_mora' => [
                '• Pago incluye recargo por pago extemporáneo',
                '• Evite futuros recargos pagando en fecha',
                '• Conserve este recibo como comprobante',
                '• Consulte su estado de cuenta actualizado'
            ],
            'liquidacion_inmueble' => [
                '• Este pago liquida totalmente el inmueble',
                '• Se procederá con la escrituración correspondiente',
                '• Conserve este recibo para el proceso de escrituras',
                '• Felicidades por liquidar su patrimonio'
            ],
            'pago_general' => [
                '• Comprobante de pago de servicios inmobiliarios',
                '• Conserve este recibo para sus registros',
                '• Documento generado automáticamente',
                '• Gracias por su confianza'
            ]
        ];
        
        return $observaciones[$tipoRecibo] ?? $observaciones['pago_general'];
    }
}

if (!function_exists('determinar_periodo_mensualidad')) {
    /**
     * Determina el periodo de la mensualidad (placeholder)
     */
    function determinar_periodo_mensualidad(object $ingreso): string
    {
        // Aquí se implementaría la lógica para determinar el periodo
        // Por ahora retornamos el mes actual
        return date('F Y', strtotime($ingreso->fecha_ingreso ?? 'now'));
    }
}

if (!function_exists('calcular_recargo_mora')) {
    /**
     * Calcula el recargo por mora (placeholder)
     */
    function calcular_recargo_mora(object $ingreso): float
    {
        // Aquí se implementaría la lógica para calcular mora
        // Por ahora retornamos 0
        return 0.0;
    }
}