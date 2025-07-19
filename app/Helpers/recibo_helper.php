<?php

/**
 * Helper para generación de recibos reutilizables
 * Metodología DRY - usado para apartados, ventas, y otros ingresos
 */

if (!function_exists('generar_folio_recibo')) {
    /**
     * Genera folio automático para recibos
     * Formato: REC-YYYYMMDD-#### 
     */
    function generar_folio_recibo(string $tipo = 'REC'): string
    {
        $fecha = date('Ymd');
        $db = \Config\Database::connect();
        
        // Obtener último folio del día
        $lastFolio = $db->table('ingresos')
                       ->like('folio', $tipo . '-' . $fecha . '-', 'after')
                       ->orderBy('id', 'DESC')
                       ->limit(1)
                       ->get()
                       ->getRow();
        
        $numero = 1;
        if ($lastFolio) {
            $parts = explode('-', $lastFolio->folio);
            $numero = isset($parts[2]) ? (int)$parts[2] + 1 : 1;
        }
        
        return $tipo . '-' . $fecha . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('formatear_moneda_mexicana')) {
    /**
     * Formatea monto en pesos mexicanos
     */
    function formatear_moneda_mexicana(float $monto): string
    {
        return '$' . number_format($monto, 2, '.', ',') . ' MXN';
    }
}

if (!function_exists('convertir_numero_a_letras')) {
    /**
     * Convierte número a letras para recibos
     * Implementación completa mejorada para el nuevo ecosistema
     */
    function convertir_numero_a_letras(float $numero): string
    {
        $entero = intval($numero);
        $centavos = round(($numero - $entero) * 100);
        
        if ($entero == 0) {
            return "CERO PESOS " . sprintf("%02d", $centavos) . "/100 M.N.";
        }
        
        $palabras = numero_a_palabras($entero);
        
        // Manejar caso especial de "UN PESO"
        if ($entero == 1) {
            return "UN PESO " . sprintf("%02d", $centavos) . "/100 M.N.";
        }
        
        return $palabras . " PESOS " . sprintf("%02d", $centavos) . "/100 M.N.";
    }
}

if (!function_exists('numero_a_palabras')) {
    /**
     * Función auxiliar para convertir números enteros a palabras
     * Implementación limpia y moderna
     */
    function numero_a_palabras(int $numero): string
    {
        if ($numero == 0) return 'CERO';
        
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        
        $resultado = '';
        
        // Millones
        if ($numero >= 1000000) {
            $millones = intval($numero / 1000000);
            if ($millones == 1) {
                $resultado .= 'UN MILLÓN ';
            } else {
                $resultado .= convertir_tres_digitos($millones, $unidades, $decenas, $especiales, $centenas) . ' MILLONES ';
            }
            $numero %= 1000000;
        }
        
        // Miles
        if ($numero >= 1000) {
            $miles = intval($numero / 1000);
            if ($miles == 1) {
                $resultado .= 'MIL ';
            } else {
                $resultado .= convertir_tres_digitos($miles, $unidades, $decenas, $especiales, $centenas) . ' MIL ';
            }
            $numero %= 1000;
        }
        
        // Centenas, decenas y unidades
        if ($numero > 0) {
            $resultado .= convertir_tres_digitos($numero, $unidades, $decenas, $especiales, $centenas);
        }
        
        return trim($resultado);
    }
}

if (!function_exists('convertir_tres_digitos')) {
    /**
     * Convierte un número de 0-999 a palabras
     */
    function convertir_tres_digitos(int $numero, array $unidades, array $decenas, array $especiales, array $centenas): string
    {
        $resultado = '';
        
        // Centenas
        $centena = intval($numero / 100);
        if ($centena > 0) {
            if ($numero == 100) {
                $resultado .= 'CIEN';
                return $resultado;
            } else {
                $resultado .= $centenas[$centena] . ' ';
            }
            $numero %= 100;
        }
        
        // Decenas y unidades
        if ($numero >= 10 && $numero <= 19) {
            $resultado .= $especiales[$numero - 10];
        } elseif ($numero >= 20) {
            $decena = intval($numero / 10);
            $unidad = $numero % 10;
            
            if ($unidad == 0) {
                $resultado .= $decenas[$decena];
            } else {
                if ($numero >= 21 && $numero <= 29) {
                    $resultado .= 'VEINTI' . $unidades[$unidad];
                } else {
                    $resultado .= $decenas[$decena] . ' Y ' . $unidades[$unidad];
                }
            }
        } elseif ($numero > 0) {
            $resultado .= $unidades[$numero];
        }
        
        return trim($resultado);
    }
}

if (!function_exists('num_to_words')) {
    /**
     * Alias para compatibilidad con vistas que requieren num_to_words()
     */
    function num_to_words(float $numero): string
    {
        return convertir_numero_a_letras($numero);
    }
}

if (!function_exists('generar_datos_recibo')) {
    /**
     * Prepara datos estándar para cualquier tipo de recibo
     */
    function generar_datos_recibo(array $datos): array
    {
        $empresa = obtener_empresa_recibo();
        $fechaRecibo = $datos['fecha'] ?? date('Y-m-d');
        
        return array_merge([
            'empresa' => $empresa,
            'fecha_emision' => date('d/m/Y H:i:s'),
            'fecha_formateada' => formatear_fecha_espanol($fechaRecibo),
            'monto_letras' => convertir_numero_a_letras($datos['monto'] ?? 0),
            'monto_formateado' => formatear_moneda_mexicana($datos['monto'] ?? 0)
        ], $datos);
    }
}


if (!function_exists('obtener_financiamiento')) {
    /**
     * Obtiene financiamiento por ID
     */
    function obtener_financiamiento(int $id): ?object
    {
        $db = \Config\Database::connect();
        return $db->table('perfiles_financiamiento')
                 ->where('id', $id)
                 ->get()
                 ->getRow();
    }
}

// Backward compatibility function
if (!function_exists('obtener_configuracion_financiera')) {
    /**
     * @deprecated Use obtener_financiamiento() instead
     * Backward compatibility for old function name
     */
    function obtener_configuracion_financiera(int $id): ?object
    {
        return obtener_financiamiento($id);
    }
}

if (!function_exists('formatear_fecha_espanol')) {
    /**
     * Formatea fecha en español para recibos
     * Ejemplo: "20-julio-2024"
     */
    function formatear_fecha_espanol(string $fecha): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $timestamp = strtotime($fecha);
        $dia = date('j', $timestamp);
        $mes = $meses[(int)date('n', $timestamp)];
        $anio = date('Y', $timestamp);
        
        return "{$dia}-{$mes}-{$anio}";
    }
}

if (!function_exists('crear_ingreso_automatico')) {
    /**
     * Crear ingreso automático al registrar ventas
     */
    function crear_ingreso_automatico(array $datos): array
    {
        try {
            $db = \Config\Database::connect();
            
            $dataIngreso = [
                'folio' => generar_folio_recibo('REC'),
                'referencia' => $datos['referencia'] ?? null,
                'tipo_ingreso' => $datos['tipo_ingreso'],
                'monto' => $datos['monto'],
                'fecha_ingreso' => date('Y-m-d H:i:s'),
                'metodo_pago' => $datos['metodo_pago'],
                'cliente_id' => $datos['cliente_id'],
                'venta_id' => $datos['venta_id'] ?? null,
                'apartado_id' => $datos['apartado_id'] ?? null,
                'user_id' => $datos['user_id']
            ];
            
            
            $insertId = $db->table('ingresos')->insert($dataIngreso);
            
            if ($insertId) {
                return [
                    'success' => true,
                    'ingreso_id' => $db->insertID(),
                    'folio' => $dataIngreso['folio']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al guardar ingreso: ' . $db->error()['message']
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en crear_ingreso_automatico: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('crear_comision_automatica')) {
    /**
     * Crear comisión automática al registrar ventas
     */
    function crear_comision_automatica(array $datos): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Calcular comisión (aquí puedes ajustar la lógica según tus reglas)
            $porcentajeComision = 0.05; // 5% por defecto, esto debería venir de configuración
            $montoComision = $datos['precio_venta_final'] * $porcentajeComision;
            
            $dataComision = [
                'venta_id' => $datos['venta_id'],
                'vendedor_id' => $datos['vendedor_id'],
                'base_calculo' => $datos['precio_venta_final'],
                'tipo_calculo' => 'porcentaje',
                'porcentaje_aplicado' => $porcentajeComision * 100,
                'monto_comision_total' => $montoComision,
                'estatus' => 'pendiente',
                'fecha_generacion' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $insertId = $db->table('comisiones_ventas')->insert($dataComision);
            
            if ($insertId) {
                return [
                    'success' => true,
                    'comision_id' => $db->insertID(),
                    'monto' => $montoComision
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al guardar comisión: ' . $db->error()['message']
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en crear_comision_automatica: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('formatear_telefono_recibo')) {
    /**
     * Formatear teléfono para recibos
     * Asegura formato consistente en todos los recibos
     */
    function formatear_telefono_recibo(?string $telefono): string
    {
        if (empty($telefono)) {
            return '(669) 238-5285'; // Fallback
        }
        
        // Limpiar solo números
        $numeros = preg_replace('/\D/', '', $telefono);
        
        // Si ya tiene 10 dígitos, formatear
        if (strlen($numeros) === 10) {
            return '(' . substr($numeros, 0, 3) . ') ' . substr($numeros, 3, 3) . '-' . substr($numeros, 6);
        }
        
        // Si no, retornar como está pero limpio
        return $telefono;
    }
}

if (!function_exists('obtener_empresa_recibo')) {
    /**
     * Obtener información de empresa para recibos
     * Función centralizada para evitar "N/A" en datos de empresa
     */
    function obtener_empresa_recibo(): array
    {
        try {
            $db = \Config\Database::connect();
            $empresaData = $db->table('empresas')
                             ->where('activo', 1)
                             ->orderBy('id', 'ASC')
                             ->limit(1)
                             ->get()
                             ->getRow();
            
            if ($empresaData) {
                return [
                    'nombre' => $empresaData->nombre,
                    'razon_social' => $empresaData->razon_social,
                    'direccion' => $empresaData->domicilio,
                    'telefono' => formatear_telefono_recibo($empresaData->telefono),
                    'email' => $empresaData->email,
                    'rfc' => $empresaData->rfc,
                    'representante' => $empresaData->representante ?? ''
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo empresa para recibo: ' . $e->getMessage());
        }
        
        // Fallback con datos reales conocidos
        return [
            'nombre' => 'Anvar Inmobiliaria',
            'razon_social' => 'Lcg Desarrollos Habitacionales Sa De Cv',
            'direccion' => 'AV. CRUZ LIZARRAGA 901 L21-22, PALOS PRIETOS, MAZATLAN, SINALOA. 82010',
            'telefono' => '(669) 238-5285',
            'email' => 'contacto@anvarinmobiliaria.com',
            'rfc' => 'LDH191210PI0',
            'representante' => 'Lic. Rodolfo Sandoval Pelayo'
        ];
    }
}

if (!function_exists('actualizar_etapa_cliente')) {
    /**
     * Actualizar etapa_proceso del cliente a "cerrado"
     */
    function actualizar_etapa_cliente(int $clienteId): array
    {
        try {
            $clienteModel = new \App\Models\ClienteModel();
            
            $actualizado = $clienteModel->update($clienteId, [
                'etapa_proceso' => 'cerrado'
            ]);
            
            if ($actualizado) {
                return [
                    'success' => true,
                    'message' => 'Etapa del cliente actualizada a cerrado'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al actualizar etapa del cliente'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en actualizar_etapa_cliente: ' . $e->getMessage()
            ];
        }
    }
}

// ==========================================
// FUNCIONES DE ESTADO DE CUENTA Y MENSUALIDADES
// ==========================================

if (!function_exists('generar_recibo_mensualidad')) {
    /**
     * Genera datos para recibo de pago de mensualidad
     */
    function generar_recibo_mensualidad(int $pagoId): array
    {
        try {
            $pagoModel = new \App\Models\PagoVentaModel();
            $pago = $pagoModel->find($pagoId);
            
            if (!$pago) {
                throw new \Exception('Pago no encontrado');
            }

            // Obtener datos relacionados
            $ventaModel = new \App\Models\VentaModel();
            $venta = $ventaModel->find($pago->venta_id);
            
            $clienteModel = new \App\Models\ClienteModel();
            $cliente = $clienteModel->find($venta->cliente_id);
            
            $tablaModel = new \App\Models\TablaAmortizacionModel();
            $mensualidad = null;
            if (!empty($pago->tabla_amortizacion_id)) {
                $mensualidad = $tablaModel->find($pago->tabla_amortizacion_id);
            }

            // Datos base del recibo
            $datosRecibo = [
                'folio' => $pago->folio_pago,
                'fecha' => $pago->fecha_pago,
                'monto' => $pago->monto_pago,
                'concepto' => $pago->getDescripcionCompleta(),
                'metodo_pago' => $pago->getFormaPagoFormateada(),
                'tipo_recibo' => 'mensualidad'
            ];

            // Información del cliente
            $datosRecibo['cliente'] = [
                'id' => $cliente->id,
                'nombre_completo' => trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno),
                'direccion' => $cliente->direccion ?? '',
                'telefono' => formatear_telefono_recibo($cliente->telefono),
                'email' => $cliente->email
            ];

            // Información de la venta/propiedad
            $datosRecibo['propiedad'] = [
                'folio_venta' => $venta->folio_venta,
                'tipo_venta' => $venta->getTipoVentaFormateado(),
                'precio_total' => $venta->precio_venta_final
            ];

            // Información de la mensualidad si existe
            if ($mensualidad) {
                $datosRecibo['mensualidad'] = [
                    'numero_pago' => $mensualidad->numero_pago,
                    'fecha_vencimiento' => $mensualidad->fecha_vencimiento,
                    'monto_capital' => $mensualidad->capital,
                    'monto_interes' => $mensualidad->interes,
                    'saldo_anterior' => $mensualidad->saldo_inicial,
                    'saldo_nuevo' => $mensualidad->saldo_final,
                    'interes_moratorio' => $mensualidad->interes_moratorio,
                    'esta_vencida' => $mensualidad->estaVencido(),
                    'dias_atraso' => $mensualidad->calcularDiasAtraso()
                ];
            }

            // Preparar datos estándar del recibo
            return generar_datos_recibo($datosRecibo);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando recibo de mensualidad: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('generar_estado_cuenta_pdf')) {
    /**
     * Genera datos para PDF de estado de cuenta
     */
    function generar_estado_cuenta_pdf(int $clienteId): array
    {
        try {
            // Generar resumen completo del cliente
            $resumenCliente = generar_resumen_cliente($clienteId);
            
            if (!$resumenCliente['success']) {
                throw new \Exception($resumenCliente['error']);
            }

            // Formatear datos para PDF
            $datosFormateados = formatear_estado_cuenta($resumenCliente);
            
            // Preparar datos específicos para PDF
            $datosPDF = [
                'titulo' => 'Estado de Cuenta',
                'subtitulo' => 'Resumen de Propiedades y Pagos',
                'fecha_emision' => date('d/m/Y H:i:s'),
                'cliente' => $datosFormateados['cliente'],
                'empresa' => obtener_empresa_recibo(),
                'resumen_general' => $datosFormateados['resumen_general'],
                'indicadores' => $datosFormateados['indicadores'],
                'proximo_vencimiento' => $datosFormateados['proximo_vencimiento'],
                'mensualidades_vencidas' => $datosFormateados['mensualidades_vencidas'],
                'proximas_mensualidades' => $datosFormateados['proximas_mensualidades'],
                'propiedades' => $datosFormateados['propiedades']
            ];

            // Calcular totales para el pie del documento
            $datosPDF['totales'] = [
                'propiedades_activas' => count($datosPDF['propiedades']),
                'total_invertido' => $datosFormateados['resumen_general']['monto_total_invertido'],
                'saldo_pendiente' => $datosFormateados['resumen_general']['saldo_total_pendiente'],
                'porcentaje_liquidacion' => $datosFormateados['resumen_general']['porcentaje_liquidacion']
            ];

            // Agregar metadata
            $datosPDF['metadata'] = [
                'generado_por' => 'Sistema Anvar',
                'fecha_generacion' => $datosFormateados['metadata']['fecha_formato'],
                'folio_documento' => 'EC-' . $clienteId . '-' . date('Ymd-His')
            ];

            return [
                'success' => true,
                'datos_pdf' => $datosPDF
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando estado de cuenta PDF: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('generar_recibo_liquidacion_parcial')) {
    /**
     * Genera recibo para liquidaciones parciales
     */
    function generar_recibo_liquidacion_parcial(array $pagosIds): array
    {
        try {
            $pagoModel = new \App\Models\PagoVentaModel();
            $pagos = [];
            $montoTotal = 0;
            $ventaId = null;
            
            // Obtener todos los pagos
            foreach ($pagosIds as $pagoId) {
                $pago = $pagoModel->find($pagoId);
                if ($pago) {
                    $pagos[] = $pago;
                    $montoTotal += $pago->monto_pago;
                    if (!$ventaId) {
                        $ventaId = $pago->venta_id;
                    }
                }
            }

            if (empty($pagos)) {
                throw new \Exception('No se encontraron pagos válidos');
            }

            // Obtener información general
            $ventaModel = new \App\Models\VentaModel();
            $venta = $ventaModel->find($ventaId);
            
            $clienteModel = new \App\Models\ClienteModel();
            $cliente = $clienteModel->find($venta->cliente_id);

            // Datos del recibo consolidado
            $datosRecibo = [
                'folio' => 'LIQPAR-' . date('Ymd-His'),
                'fecha' => date('Y-m-d'),
                'monto' => $montoTotal,
                'concepto' => 'Liquidación Parcial (' . count($pagos) . ' mensualidades)',
                'metodo_pago' => 'multiple',
                'tipo_recibo' => 'liquidacion_parcial'
            ];

            // Información del cliente
            $datosRecibo['cliente'] = [
                'id' => $cliente->id,
                'nombre_completo' => trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno),
                'direccion' => $cliente->direccion ?? '',
                'telefono' => formatear_telefono_recibo($cliente->telefono),
                'email' => $cliente->email
            ];

            // Detalle de pagos incluidos
            $datosRecibo['detalle_pagos'] = [];
            foreach ($pagos as $pago) {
                $datosRecibo['detalle_pagos'][] = [
                    'folio' => $pago->folio_pago,
                    'concepto' => $pago->getDescripcionCompleta(),
                    'monto' => formatear_moneda_mexicana($pago->monto_pago),
                    'fecha' => date('d/m/Y', strtotime($pago->fecha_pago)),
                    'metodo' => $pago->getFormaPagoFormateada()
                ];
            }

            // Información de la venta
            $resumenVenta = $venta->getResumenFinanciero();
            $datosRecibo['propiedad'] = [
                'folio_venta' => $venta->folio_venta,
                'saldo_anterior' => $resumenVenta['saldo_pendiente'] + $montoTotal,
                'monto_abonado' => $montoTotal,
                'saldo_nuevo' => $resumenVenta['saldo_pendiente'],
                'porcentaje_liquidacion' => $resumenVenta['porcentaje_liquidacion']
            ];

            return generar_datos_recibo($datosRecibo);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando recibo de liquidación parcial: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('generar_template_mensualidad_mora')) {
    /**
     * Genera template especial para mensualidades con mora
     */
    function generar_template_mensualidad_mora(int $pagoId): array
    {
        try {
            $recibo = generar_recibo_mensualidad($pagoId);
            
            if (!$recibo['success']) {
                return $recibo;
            }

            // Agregar información específica de mora
            if (isset($recibo['mensualidad']) && $recibo['mensualidad']['interes_moratorio'] > 0) {
                $recibo['template_especial'] = 'mensualidad_con_mora';
                $recibo['alertas_mora'] = [
                    'dias_atraso' => $recibo['mensualidad']['dias_atraso'],
                    'monto_mora' => formatear_moneda_mexicana($recibo['mensualidad']['interes_moratorio']),
                    'mensaje' => 'Este pago incluye intereses moratorios por atraso de ' . 
                                $recibo['mensualidad']['dias_atraso'] . ' días.'
                ];
                
                // Desglose detallado
                $recibo['desglose_detallado'] = [
                    'capital' => formatear_moneda_mexicana($recibo['mensualidad']['monto_capital']),
                    'interes_normal' => formatear_moneda_mexicana($recibo['mensualidad']['monto_interes']),
                    'interes_moratorio' => formatear_moneda_mexicana($recibo['mensualidad']['interes_moratorio']),
                    'total_mensualidad' => formatear_moneda_mexicana(
                        $recibo['mensualidad']['monto_capital'] + 
                        $recibo['mensualidad']['monto_interes'] + 
                        $recibo['mensualidad']['interes_moratorio']
                    )
                ];
            }

            return $recibo;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando template con mora: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('validar_datos_recibo_mensualidad')) {
    /**
     * Valida que los datos del recibo de mensualidad estén completos
     */
    function validar_datos_recibo_mensualidad(array $datos): array
    {
        $errores = [];

        // Validaciones básicas
        if (empty($datos['folio'])) {
            $errores[] = 'Folio de pago requerido';
        }

        if (empty($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = 'Monto válido requerido';
        }

        if (empty($datos['cliente']['nombre_completo'])) {
            $errores[] = 'Nombre del cliente requerido';
        }

        if (empty($datos['propiedad']['folio_venta'])) {
            $errores[] = 'Folio de venta requerido';
        }

        // Validaciones específicas de mensualidad
        if (isset($datos['mensualidad'])) {
            if (empty($datos['mensualidad']['numero_pago'])) {
                $errores[] = 'Número de mensualidad requerido';
            }

            if (empty($datos['mensualidad']['fecha_vencimiento'])) {
                $errores[] = 'Fecha de vencimiento requerida';
            }
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}

if (!function_exists('generar_template_comprobante_pago')) {
    /**
     * Genera template HTML para comprobante de pago
     * Integrado con el sistema de Estado de Cuenta
     */
    function generar_template_comprobante_pago(array $datos): string
    {
        $pago = $datos['pago'];
        $venta = $datos['venta'];
        $cliente = $datos['cliente'];
        $mensualidad = $datos['mensualidad'];
        
        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Comprobante de Pago - ' . esc($pago->folio_pago) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .info-section { background: #f5f5f5; padding: 15px; margin-bottom: 20px; }
                .tabla { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .tabla th, .tabla td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .tabla th { background-color: #f2f2f2; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-success { color: #28a745; }
                .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>COMPROBANTE DE PAGO</h1>
                <h2>' . esc($pago->folio_pago) . '</h2>
                <p>Fecha: ' . date('d/m/Y H:i', strtotime($pago->fecha_pago)) . '</p>
            </div>
            
            <div class="info-section">
                <h3>Información del Cliente</h3>
                <p><strong>Nombre:</strong> ' . esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) . '</p>
                <p><strong>Email:</strong> ' . esc($cliente->email) . '</p>
            </div>
            
            <div class="info-section">
                <h3>Detalle del Pago</h3>
                <table class="tabla">
                    <tr>
                        <th>Concepto:</th>
                        <td>Mensualidad #' . ($mensualidad->numero_pago ?? 'N/A') . ' - ' . esc($venta->lote_clave ?? 'N/A') . '</td>
                    </tr>
                    <tr>
                        <th>Monto Pagado:</th>
                        <td class="text-success"><strong>$' . number_format($pago->monto_pago, 2) . '</strong></td>
                    </tr>
                    <tr>
                        <th>Forma de Pago:</th>
                        <td>' . ucfirst($pago->forma_pago) . '</td>
                    </tr>
                    <tr>
                        <th>Referencia:</th>
                        <td>' . esc($pago->referencia_pago ?? 'N/A') . '</td>
                    </tr>
                </table>
            </div>
            
            <div class="footer">
                <p>Este comprobante fue generado electrónicamente</p>
                <p>Para verificación contacte a nuestro departamento de cobranza</p>
            </div>
        </body>
        </html>';
    }
}