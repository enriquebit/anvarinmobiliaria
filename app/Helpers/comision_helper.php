<?php

/**
 * Helper para manejo de comisiones de ventas
 */

if (!function_exists('crear_comision_automatica')) {
    /**
     * Crear comisión automáticamente al registrar una venta
     * 
     * @param array $datos Datos de la venta y configuración
     * @return array Resultado de la operación
     */
    function crear_comision_automatica(array $datos): array
    {
        try {
            // Validar datos requeridos
            $camposRequeridos = ['venta_id', 'vendedor_id', 'precio_venta_final'];
            foreach ($camposRequeridos as $campo) {
                if (empty($datos[$campo])) {
                    return [
                        'success' => false,
                        'error' => "Campo requerido faltante: {$campo}"
                    ];
                }
            }
            
            // Obtener conexión de base de datos
            $db = \Config\Database::connect();
            
            // Obtener configuración de comisión por defecto
            $configComision = $db->table('configuracion_comisiones')
                ->where('activo', 1)
                ->orderBy('prioridad', 'ASC')
                ->limit(1)
                ->get()
                ->getRow();
                
            if (!$configComision) {
                // Debug: contar configuraciones disponibles
                $totalConfiguraciones = $db->table('configuracion_comisiones')->countAllResults();
                $configuracionesActivas = $db->table('configuracion_comisiones')->where('activo', 1)->countAllResults();
                
                log_message('error', "COMISION_HELPER: No se encontró configuración. Total: {$totalConfiguraciones}, Activas: {$configuracionesActivas}");
                
                return [
                    'success' => false,
                    'error' => "No se encontró configuración de comisión activa. Total: {$totalConfiguraciones}, Activas: {$configuracionesActivas}"
                ];
            }
            
            log_message('debug', 'COMISION_HELPER: Configuración encontrada: ' . json_encode($configComision));
            
            // Calcular comisión
            $baseCalculo = $datos['precio_venta_final'];
            $porcentajeComision = $configComision->porcentaje_comision ?? 3.0; // 3% por defecto
            $montoComision = ($baseCalculo * $porcentajeComision) / 100;
            
            // Datos para insertar en comisiones_ventas
            $datosComision = [
                'venta_id' => $datos['venta_id'],
                'vendedor_id' => $datos['vendedor_id'],
                'configuracion_comision_id' => $configComision->id,
                'base_calculo' => $baseCalculo,
                'tipo_calculo' => 'porcentaje',
                'porcentaje_aplicado' => $porcentajeComision,
                'monto_comision_total' => $montoComision,
                'monto_pagado_apartado' => 0,
                'monto_pagado_enganche' => 0,
                'monto_por_cobranza' => 0,
                'monto_pagado_total' => 0,
                'estatus' => 'pendiente',
                'fecha_generacion' => date('Y-m-d'),
                'observaciones' => 'Comisión generada automáticamente'
            ];
            
            // Insertar comisión
            $resultado = $db->table('comisiones_ventas')->insert($datosComision);
            
            if ($resultado) {
                $comisionId = $db->insertID();
                
                return [
                    'success' => true,
                    'comision_id' => $comisionId,
                    'monto_comision' => $montoComision,
                    'porcentaje_aplicado' => $porcentajeComision
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al insertar comisión en base de datos'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error creando comisión automática: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('actualizar_comision_apartado')) {
    /**
     * Actualizar comisión cuando se paga apartado
     * 
     * @param int $ventaId ID de la venta
     * @param float $montoApartado Monto del apartado pagado
     * @return array Resultado de la operación
     */
    function actualizar_comision_apartado(int $ventaId, float $montoApartado): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Buscar comisión existente
            $comision = $db->table('comisiones_ventas')
                ->where('venta_id', $ventaId)
                ->get()
                ->getRow();
                
            if (!$comision) {
                return [
                    'success' => false,
                    'error' => 'No se encontró comisión para la venta'
                ];
            }
            
            // Calcular porcentaje del apartado sobre comisión total
            $porcentajeApartado = 0.1; // 10% de la comisión se paga con apartado
            $montoComisionApartado = $comision->monto_comision_total * $porcentajeApartado;
            
            // Actualizar comisión
            $datosActualizacion = [
                'monto_pagado_apartado' => $montoComisionApartado,
                'monto_pagado_total' => $comision->monto_pagado_total + $montoComisionApartado,
                'estatus' => 'pendiente_aceptacion'
            ];
            
            $resultado = $db->table('comisiones_ventas')
                ->where('id', $comision->id)
                ->update($datosActualizacion);
                
            return [
                'success' => $resultado,
                'monto_comision_apartado' => $montoComisionApartado
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error actualizando comisión de apartado: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('actualizar_comision_mensualidad')) {
    /**
     * Actualizar comisión cuando se paga una mensualidad
     * 
     * @param int $ventaId ID de la venta
     * @param float $montoMensualidad Monto de la mensualidad pagada
     * @return array Resultado de la operación
     */
    function actualizar_comision_mensualidad(int $ventaId, float $montoMensualidad): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Buscar comisión existente
            $comision = $db->table('comisiones_ventas')
                ->where('venta_id', $ventaId)
                ->get()
                ->getRow();
                
            if (!$comision) {
                return [
                    'success' => false,
                    'error' => 'No se encontró comisión para la venta'
                ];
            }
            
            // Calcular porcentaje de la mensualidad sobre comisión total
            $porcentajeMensualidad = 0.05; // 5% de la comisión se paga con cada mensualidad
            $montoComisionMensualidad = $comision->monto_comision_total * $porcentajeMensualidad;
            
            // Actualizar comisión
            $nuevoMontoPagado = $comision->monto_pagado_total + $montoComisionMensualidad;
            $nuevoEstatus = ($nuevoMontoPagado >= $comision->monto_comision_total) ? 'pendiente_aceptacion' : 'parcial';
            
            $datosActualizacion = [
                'monto_por_cobranza' => $comision->monto_por_cobranza + $montoComisionMensualidad,
                'monto_pagado_total' => $nuevoMontoPagado,
                'estatus' => $nuevoEstatus
            ];
            
            $resultado = $db->table('comisiones_ventas')
                ->where('id', $comision->id)
                ->update($datosActualizacion);
                
            return [
                'success' => $resultado,
                'monto_comision_mensualidad' => $montoComisionMensualidad
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error actualizando comisión por mensualidad: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('obtener_empresa_recibo')) {
    /**
     * Obtener información de la empresa para recibos
     * 
     * @return array Información de la empresa
     */
    function obtener_empresa_recibo(): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Buscar configuración de empresa
            $empresa = $db->table('configuracion_empresa')
                ->where('activo', 1)
                ->limit(1)
                ->get()
                ->getRow();
                
            if ($empresa) {
                return [
                    'nombre' => $empresa->nombre_empresa,
                    'direccion' => $empresa->direccion,
                    'telefono' => $empresa->telefono,
                    'email' => $empresa->email,
                    'rfc' => $empresa->rfc,
                    'logo' => $empresa->logo_url ?? null
                ];
            }
            
            // Valores por defecto si no hay configuración
            return [
                'nombre' => 'ANVAR INMOBILIARIA',
                'direccion' => 'Dirección de la empresa',
                'telefono' => 'N/A',
                'email' => 'N/A',
                'rfc' => 'N/A',
                'logo' => null
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error obteniendo empresa para recibo: ' . $e->getMessage());
            
            return [
                'nombre' => 'ANVAR INMOBILIARIA',
                'direccion' => 'Dirección de la empresa',
                'telefono' => 'N/A',
                'email' => 'N/A',
                'rfc' => 'N/A',
                'logo' => null
            ];
        }
    }
}