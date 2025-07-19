<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PerfilFinanciamientoModel;

/**
 * Controlador para debug y pruebas de amortización
 */
class DebugAmortizacionController extends BaseController
{
    protected $ventaModel;
    protected $tablaModel;
    protected $perfilModel;
    protected $db;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->perfilModel = new PerfilFinanciamientoModel();
        $this->db = \Config\Database::connect();
        
        // Cargar helper de amortización
        helper('amortizacion');
    }

    /**
     * Generar tabla de amortización para venta 18
     */
    public function generarVenta18()
    {
        echo "<h1>🚀 GENERAR TABLA DE AMORTIZACIÓN - VENTA 18</h1>";
        echo "<pre>";

        try {
            // Verificar que existe la venta 18
            echo "🔍 Verificando venta 18...\n";
            $venta = $this->ventaModel->find(18);
            
            if (!$venta) {
                throw new \Exception('Venta 18 no encontrada');
            }

            echo "✅ Venta encontrada:\n";
            echo "- Folio: {$venta->folio_venta}\n";
            echo "- Cliente ID: {$venta->cliente_id}\n";
            echo "- Tipo venta: {$venta->tipo_venta}\n";
            echo "- Precio final: $" . number_format($venta->precio_venta_final, 2) . "\n";
            echo "- Perfil financiamiento ID: {$venta->perfil_financiamiento_id}\n\n";

            // Verificar perfil de financiamiento
            echo "🔍 Verificando perfil de financiamiento...\n";
            $perfil = $this->perfilModel->find($venta->perfil_financiamiento_id);
            
            if (!$perfil) {
                throw new \Exception('Perfil de financiamiento no encontrado');
            }

            echo "✅ Perfil encontrado:\n";
            echo "- Nombre: {$perfil->nombre}\n";
            echo "- Meses con intereses: {$perfil->meses_con_intereses}\n";
            echo "- Tasa anual: {$perfil->porcentaje_interes_anual}%\n\n";

            // Verificar si ya existe tabla de amortización
            echo "🔍 Verificando tabla existente...\n";
            $tablaExistente = $this->tablaModel->where('plan_financiamiento_id', 18)->findAll();
            
            if (!empty($tablaExistente)) {
                echo "⚠️ Ya existe tabla de amortización con " . count($tablaExistente) . " registros\n";
                echo "¿Desea regenerar? Acceda a: /debug/amortizacion/regenerarVenta18\n\n";
                
                // Mostrar resumen de tabla existente
                echo "📊 RESUMEN DE TABLA EXISTENTE:\n";
                $totalMonto = 0;
                $totalPagado = 0;
                $totalPendiente = 0;
                
                foreach ($tablaExistente as $mensualidad) {
                    $totalMonto += $mensualidad->monto_total;
                    $totalPagado += $mensualidad->monto_pagado;
                    $totalPendiente += $mensualidad->saldo_pendiente;
                }
                
                echo "- Mensualidades: " . count($tablaExistente) . "\n";
                echo "- Monto total: $" . number_format($totalMonto, 2) . "\n";
                echo "- Total pagado: $" . number_format($totalPagado, 2) . "\n";
                echo "- Saldo pendiente: $" . number_format($totalPendiente, 2) . "\n\n";
                
                echo "✅ Para acceder al estado de cuenta: http://localhost/admin/estado-cuenta/18\n";
                return;
            }

            echo "✅ No existe tabla previa, procediendo a generar...\n\n";

            // Preparar configuración financiera
            $configFinanciera = [
                'monto_financiar' => $venta->precio_venta_final,
                'tasa_interes_anual' => $perfil->porcentaje_interes_anual,
                'numero_pagos' => $perfil->meses_con_intereses,
                'fecha_primer_pago' => '2025-08-15' // Primer pago siguiente mes
            ];

            echo "📋 CONFIGURACIÓN FINANCIERA:\n";
            echo json_encode($configFinanciera, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

            // Simular primero para verificar cálculos
            echo "🧮 SIMULANDO CÁLCULOS...\n";
            $simulacion = simular_amortizacion($configFinanciera);

            if (!$simulacion['success']) {
                throw new \Exception('Error en simulación: ' . $simulacion['error']);
            }

            $resumen = $simulacion['resumen'];
            echo "✅ Simulación exitosa:\n";
            echo "- Pago mensual: $" . number_format($resumen['pago_mensual'], 2) . "\n";
            echo "- Total intereses: $" . number_format($resumen['total_intereses'], 2) . "\n";
            echo "- Total a pagar: $" . number_format($resumen['total_a_pagar'], 2) . "\n";
            echo "- Fecha inicio: " . $resumen['fecha_inicio'] . "\n";
            echo "- Fecha final: " . $resumen['fecha_final'] . "\n\n";

            // Mostrar primeras 5 mensualidades
            echo "📅 PRIMERAS 5 MENSUALIDADES:\n";
            for ($i = 0; $i < 5 && $i < count($simulacion['tabla_simulada']); $i++) {
                $mens = $simulacion['tabla_simulada'][$i];
                echo sprintf(
                    "  %2d. %s - Capital: $%8s - Interés: $%7s - Total: $%8s - Saldo: $%9s\n",
                    $mens['numero_pago'],
                    $mens['fecha_vencimiento'],
                    number_format($mens['capital'], 2),
                    number_format($mens['interes'], 2),
                    number_format($mens['pago_total'], 2),
                    number_format($mens['saldo_final'], 2)
                );
            }
            echo "\n";

            // Generar tabla real
            echo "💾 GENERANDO TABLA REAL EN BASE DE DATOS...\n";
            $resultado = generar_tabla_amortizacion(18, $configFinanciera);

            if (!$resultado['success']) {
                throw new \Exception('Error generando tabla: ' . $resultado['error']);
            }

            echo "🎉 TABLA DE AMORTIZACIÓN GENERADA EXITOSAMENTE!\n\n";
            echo "📊 RESULTADOS:\n";
            echo "- Mensualidades generadas: " . $resultado['mensualidades_generadas'] . "\n";
            echo "- Monto financiado: $" . number_format($resultado['monto_financiado'], 2) . "\n";
            echo "- Pago mensual: $" . number_format($resultado['pago_mensual'], 2) . "\n";
            echo "- Total intereses: $" . number_format($resultado['total_intereses'], 2) . "\n";
            echo "- Total a pagar: $" . number_format($resultado['total_a_pagar'], 2) . "\n";
            echo "- IDs insertados: " . implode(', ', array_slice($resultado['ids_insertados'], 0, 10));
            if (count($resultado['ids_insertados']) > 10) {
                echo " ... (+" . (count($resultado['ids_insertados']) - 10) . " más)";
            }
            echo "\n\n";

            echo "🔗 ENLACES ÚTILES:\n";
            echo "- Estado de cuenta: http://localhost/admin/estado-cuenta/18\n";
            echo "- Detalle venta: http://localhost/admin/ventas/ver/18\n";
            echo "- Mensualidades: http://localhost/admin/mensualidades\n\n";

            echo "✅ PROCESO COMPLETADO EXITOSAMENTE\n";

        } catch (\Exception $e) {
            echo "\n💥 ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }

        echo "</pre>";
    }

    /**
     * Regenerar tabla eliminando la existente
     */
    public function regenerarVenta18()
    {
        echo "<h1>🔄 REGENERAR TABLA DE AMORTIZACIÓN - VENTA 18</h1>";
        echo "<pre>";

        try {
            echo "🗑️ Eliminando tabla existente...\n";
            
            // Eliminar registros existentes
            $eliminados = $this->db->table('tabla_amortizacion')
                                  ->where('plan_financiamiento_id', 18)
                                  ->delete();
            
            echo "✅ Eliminados {$eliminados} registros\n\n";

            // Continuar con generación normal
            echo "🔄 Redirigiendo a generación...\n";
            return redirect()->to('/debug/amortizacion/generarVenta18');

        } catch (\Exception $e) {
            echo "\n💥 ERROR: " . $e->getMessage() . "\n";
        }

        echo "</pre>";
    }

    /**
     * Ver resumen de tabla existente
     */
    public function verResumenVenta18()
    {
        echo "<h1>📊 RESUMEN TABLA AMORTIZACIÓN - VENTA 18</h1>";
        echo "<pre>";

        try {
            $tabla = $this->tablaModel->where('plan_financiamiento_id', 18)
                                     ->orderBy('numero_pago', 'ASC')
                                     ->findAll();

            if (empty($tabla)) {
                echo "❌ No existe tabla de amortización para venta 18\n";
                echo "🔗 Generar: http://localhost/debug/amortizacion/generarVenta18\n";
                return;
            }

            echo "✅ Tabla encontrada con " . count($tabla) . " mensualidades\n\n";

            // Calcular totales
            $totalMonto = 0;
            $totalPagado = 0;
            $totalPendiente = 0;
            $mensualidadesPagadas = 0;
            $mensualidadesVencidas = 0;

            echo "📋 DETALLE DE MENSUALIDADES:\n";
            echo str_repeat("-", 100) . "\n";
            echo sprintf(
                "%-4s %-12s %-10s %-10s %-10s %-12s %-10s %-10s\n",
                "No.", "Fecha", "Capital", "Interés", "Total", "Pagado", "Pendiente", "Estado"
            );
            echo str_repeat("-", 100) . "\n";

            foreach ($tabla as $mensualidad) {
                $totalMonto += $mensualidad->monto_total;
                $totalPagado += $mensualidad->monto_pagado;
                $totalPendiente += $mensualidad->saldo_pendiente;

                if ($mensualidad->estatus === 'pagada') {
                    $mensualidadesPagadas++;
                } elseif ($mensualidad->estatus === 'vencida') {
                    $mensualidadesVencidas++;
                }

                echo sprintf(
                    "%-4d %-12s $%-9s $%-9s $%-9s $%-11s $%-9s %-10s\n",
                    $mensualidad->numero_pago,
                    $mensualidad->fecha_vencimiento,
                    number_format($mensualidad->capital, 2),
                    number_format($mensualidad->interes, 2),
                    number_format($mensualidad->monto_total, 2),
                    number_format($mensualidad->monto_pagado, 2),
                    number_format($mensualidad->saldo_pendiente, 2),
                    strtoupper($mensualidad->estatus)
                );
            }

            echo str_repeat("-", 100) . "\n";

            echo "\n📊 RESUMEN FINANCIERO:\n";
            echo "- Total mensualidades: " . count($tabla) . "\n";
            echo "- Mensualidades pagadas: {$mensualidadesPagadas}\n";
            echo "- Mensualidades vencidas: {$mensualidadesVencidas}\n";
            echo "- Monto total tabla: $" . number_format($totalMonto, 2) . "\n";
            echo "- Total pagado: $" . number_format($totalPagado, 2) . "\n";
            echo "- Saldo pendiente: $" . number_format($totalPendiente, 2) . "\n";
            
            if ($totalMonto > 0) {
                $porcentajeAvance = ($totalPagado / $totalMonto) * 100;
                echo "- Porcentaje liquidación: " . number_format($porcentajeAvance, 2) . "%\n";
            }

            echo "\n🔗 ENLACES:\n";
            echo "- Estado de cuenta: http://localhost/admin/estado-cuenta/18\n";
            echo "- Regenerar tabla: http://localhost/debug/amortizacion/regenerarVenta18\n";

        } catch (\Exception $e) {
            echo "\n💥 ERROR: " . $e->getMessage() . "\n";
        }

        echo "</pre>";
    }

    /**
     * Probar simulación de amortización
     */
    public function probarSimulacion()
    {
        echo "<h1>🧮 PROBAR SIMULACIÓN DE AMORTIZACIÓN</h1>";
        echo "<pre>";

        $configuraciones = [
            [
                'nombre' => 'Venta 18 Real',
                'monto_financiar' => 208800.00,
                'tasa_interes_anual' => 5.00,
                'numero_pagos' => 48,
                'fecha_primer_pago' => '2025-08-15'
            ],
            [
                'nombre' => 'Ejemplo Pequeño',
                'monto_financiar' => 100000.00,
                'tasa_interes_anual' => 12.00,
                'numero_pagos' => 24,
                'fecha_primer_pago' => '2025-08-01'
            ],
            [
                'nombre' => 'Sin Intereses',
                'monto_financiar' => 50000.00,
                'tasa_interes_anual' => 0.00,
                'numero_pagos' => 12,
                'fecha_primer_pago' => '2025-08-01'
            ]
        ];

        foreach ($configuraciones as $config) {
            echo "🧮 SIMULACIÓN: {$config['nombre']}\n";
            echo str_repeat("-", 50) . "\n";

            $resultado = simular_amortizacion($config);

            if ($resultado['success']) {
                $resumen = $resultado['resumen'];
                echo "✅ Configuración válida:\n";
                echo "- Monto: $" . number_format($config['monto_financiar'], 2) . "\n";
                echo "- Tasa anual: {$config['tasa_interes_anual']}%\n";
                echo "- Pagos: {$config['numero_pagos']} meses\n";
                echo "- Pago mensual: $" . number_format($resumen['pago_mensual'], 2) . "\n";
                echo "- Total intereses: $" . number_format($resumen['total_intereses'], 2) . "\n";
                echo "- Total a pagar: $" . number_format($resumen['total_a_pagar'], 2) . "\n";
            } else {
                echo "❌ Error: {$resultado['error']}\n";
            }

            echo "\n";
        }

        echo "</pre>";
    }

    /**
     * Debug para venta específica - Detecta automáticamente el problema
     */
    public function debugVenta(int $ventaId)
    {
        echo "<h1>🔍 DEBUG COMPLETO VENTA {$ventaId}</h1>";
        echo "<pre>";

        try {
            // 1. Verificar venta
            echo "=== 1. VERIFICACIÓN DE VENTA ===\n";
            $venta = $this->ventaModel->find($ventaId);
            
            if (!$venta) {
                throw new \Exception("Venta {$ventaId} no encontrada");
            }

            echo "✅ Venta encontrada:\n";
            echo "- ID: {$venta->id}\n";
            echo "- Folio: {$venta->folio_venta}\n";
            echo "- Cliente ID: {$venta->cliente_id}\n";
            echo "- Tipo venta: {$venta->tipo_venta}\n";
            echo "- Estatus: {$venta->estatus_venta}\n";
            echo "- Precio final: $" . number_format($venta->precio_venta_final, 2) . "\n";
            echo "- Perfil financiamiento ID: {$venta->perfil_financiamiento_id}\n";
            echo "- Fecha venta: {$venta->fecha_venta}\n\n";

            // 2. Verificar perfil de financiamiento
            echo "=== 2. VERIFICACIÓN DE PERFIL FINANCIAMIENTO ===\n";
            $perfil = $this->perfilModel->find($venta->perfil_financiamiento_id);
            
            if (!$perfil) {
                throw new \Exception("Perfil de financiamiento {$venta->perfil_financiamiento_id} no encontrado");
            }

            echo "✅ Perfil encontrado:\n";
            echo "- ID: {$perfil->id}\n";
            echo "- Nombre: {$perfil->nombre}\n";
            echo "- Tipo: {$perfil->tipo_financiamiento}\n";
            echo "- Cero enganche: " . ($perfil->promocion_cero_enganche ? 'SÍ' : 'NO') . "\n";
            echo "- MSI: {$perfil->meses_sin_intereses}\n";
            echo "- MCI: {$perfil->meses_con_intereses}\n";
            echo "- Tasa anual: {$perfil->porcentaje_interes_anual}%\n";
            echo "- Porcentaje anticipo: {$perfil->porcentaje_anticipo}%\n\n";

            // 3. Aplicar lógica de determinación de configuración
            echo "=== 3. LÓGICA DE DETERMINACIÓN DE CONFIGURACIÓN ===\n";
            $numeroPagos = 0;
            $tasaInteres = $perfil->porcentaje_interes_anual;
            $modalidad = '';

            if ($perfil->promocion_cero_enganche) {
                echo "✅ Es plan CERO ENGANCHE\n";
                if ($perfil->tipo_financiamiento === 'msi' && $perfil->meses_sin_intereses > 0) {
                    $numeroPagos = $perfil->meses_sin_intereses;
                    $tasaInteres = 0;
                    $modalidad = 'Cero Enganche + MSI';
                } elseif ($perfil->tipo_financiamiento === 'mci' && $perfil->meses_con_intereses > 0) {
                    $numeroPagos = $perfil->meses_con_intereses;
                    $modalidad = 'Cero Enganche + MCI';
                }
            } elseif ($perfil->tipo_financiamiento === 'msi' && $perfil->meses_sin_intereses > 0) {
                $numeroPagos = $perfil->meses_sin_intereses;
                $tasaInteres = 0;
                $modalidad = 'MSI tradicional';
            } elseif ($perfil->tipo_financiamiento === 'mci' && $perfil->meses_con_intereses > 0) {
                $numeroPagos = $perfil->meses_con_intereses;
                $modalidad = 'MCI tradicional';
            }

            echo "- Modalidad detectada: {$modalidad}\n";
            echo "- Número de pagos: {$numeroPagos}\n";
            echo "- Tasa aplicada: {$tasaInteres}%\n\n";

            // 4. Generar configuración
            echo "=== 4. CONFIGURACIÓN GENERADA ===\n";
            $configParaAmortizacion = [
                'monto_financiar' => $venta->precio_venta_final - 0, // Asumiendo cero enganche
                'tasa_interes_anual' => $tasaInteres,
                'numero_pagos' => $numeroPagos,
                'fecha_primer_pago' => date('Y-m-d', strtotime($venta->fecha_venta . ' +1 month'))
            ];

            echo "📋 Configuración final:\n";
            foreach ($configParaAmortizacion as $key => $value) {
                echo "- {$key}: {$value}\n";
            }
            echo "\n";

            // 5. Validar configuración
            echo "=== 5. VALIDACIÓN DE CONFIGURACIÓN ===\n";
            $validacion = validar_configuracion_financiera($configParaAmortizacion);
            
            if ($validacion['valido']) {
                echo "✅ CONFIGURACIÓN VÁLIDA\n\n";
            } else {
                echo "❌ CONFIGURACIÓN INVÁLIDA:\n";
                foreach ($validacion['errores'] as $error) {
                    echo "- {$error}\n";
                }
                echo "\n";
            }

            // 6. Verificar cuenta de financiamiento
            echo "=== 6. VERIFICACIÓN DE CUENTA DE FINANCIAMIENTO ===\n";
            $cuenta = $this->db->table('cuentas_financiamiento')
                ->where('venta_id', $ventaId)
                ->get()
                ->getRow();

            if ($cuenta) {
                echo "✅ Cuenta existe:\n";
                echo "- ID: {$cuenta->id}\n";
                echo "- Saldo inicial: $" . number_format($cuenta->saldo_inicial, 2) . "\n";
                echo "- Saldo actual: $" . number_format($cuenta->saldo_actual, 2) . "\n";
                echo "- Fecha apertura: {$cuenta->fecha_apertura}\n";
            } else {
                echo "❌ NO EXISTE CUENTA DE FINANCIAMIENTO\n";
                echo "- Para planes Cero Enganche debería crearse automáticamente\n";
            }
            echo "\n";

            // 7. Verificar tabla de amortización
            echo "=== 7. VERIFICACIÓN DE TABLA DE AMORTIZACIÓN ===\n";
            $tabla = $this->db->table('tabla_amortizacion')
                ->where('plan_financiamiento_id', $cuenta->id ?? 0)
                ->get()
                ->getResult();

            if (!empty($tabla)) {
                echo "✅ Tabla existe con " . count($tabla) . " mensualidades\n";
            } else {
                echo "❌ NO EXISTE TABLA DE AMORTIZACIÓN\n";
            }
            echo "\n";

            // 8. Intentar generar tabla si es válida
            if ($validacion['valido']) {
                echo "=== 8. INTENTO DE GENERACIÓN ===\n";
                
                try {
                    $resultado = generar_tabla_amortizacion($ventaId, $configParaAmortizacion);
                    
                    if ($resultado['success']) {
                        echo "✅ TABLA GENERADA EXITOSAMENTE!\n";
                        echo "- Mensualidades: {$resultado['mensualidades_generadas']}\n";
                        echo "- Pago mensual: $" . number_format($resultado['pago_mensual'], 2) . "\n";
                        echo "- Total intereses: $" . number_format($resultado['total_intereses'], 2) . "\n";
                    } else {
                        echo "❌ Error generando tabla: {$resultado['error']}\n";
                    }
                } catch (\Exception $e) {
                    echo "❌ Excepción generando tabla: " . $e->getMessage() . "\n";
                }
            } else {
                echo "=== 8. GENERACIÓN OMITIDA ===\n";
                echo "❌ No se puede generar tabla con configuración inválida\n";
            }

            echo "\n=== RESUMEN FINAL ===\n";
            echo "- Venta: {$venta->folio_venta}\n";
            echo "- Plan: {$perfil->nombre}\n";
            echo "- Modalidad: {$modalidad}\n";
            echo "- Configuración: " . ($validacion['valido'] ? 'VÁLIDA' : 'INVÁLIDA') . "\n";
            echo "- Cuenta: " . ($cuenta ? 'EXISTE' : 'NO EXISTE') . "\n";
            echo "- Tabla: " . (!empty($tabla) ? 'EXISTE' : 'NO EXISTE') . "\n";

            echo "\n🔗 ENLACES ÚTILES:\n";
            echo "- Estado de cuenta: http://localhost/admin/estado-cuenta/venta/{$ventaId}\n";
            echo "- Procesar mensualidad: http://localhost/admin/pagos/procesar-mensualidad/{$ventaId}\n";

        } catch (\Exception $e) {
            echo "\n💥 ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }

        echo "</pre>";
    }

    /**
     * Corregir ventas existentes que tienen tabla pero no cuenta
     */
    public function corregirVentasExistentes()
    {
        echo "<h1>🔧 CORREGIR VENTAS EXISTENTES</h1>";
        echo "<pre>";

        try {
            // Buscar ventas con tabla pero sin cuenta
            $ventasProblema = $this->db->query("
                SELECT DISTINCT 
                    ta.plan_financiamiento_id as venta_id,
                    COUNT(ta.id) as mensualidades,
                    v.folio_venta,
                    v.precio_venta_final,
                    v.perfil_financiamiento_id
                FROM tabla_amortizacion ta
                LEFT JOIN cuentas_financiamiento cf ON cf.venta_id = ta.plan_financiamiento_id
                LEFT JOIN ventas v ON v.id = ta.plan_financiamiento_id
                WHERE cf.id IS NULL
                GROUP BY ta.plan_financiamiento_id, v.folio_venta, v.precio_venta_final, v.perfil_financiamiento_id
                ORDER BY ta.plan_financiamiento_id
            ")->getResult();

            if (empty($ventasProblema)) {
                echo "✅ No se encontraron ventas con problemas\n";
                return;
            }

            echo "🔍 Ventas encontradas con tabla pero sin cuenta:\n";
            foreach ($ventasProblema as $venta) {
                echo "- Venta {$venta->venta_id} ({$venta->folio_venta}): {$venta->mensualidades} mensualidades\n";
            }
            echo "\n";

            // Corregir cada venta
            foreach ($ventasProblema as $venta) {
                echo "🔧 Corrigiendo venta {$venta->venta_id} ({$venta->folio_venta})...\n";
                
                // Crear cuenta de financiamiento
                $datosCuenta = [
                    'venta_id' => $venta->venta_id,
                    'plan_financiamiento_id' => $venta->perfil_financiamiento_id,
                    'saldo_inicial' => $venta->precio_venta_final,
                    'saldo_actual' => $venta->precio_venta_final,
                    'fecha_apertura' => date('Y-m-d'),
                    'estado' => 'activa',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $cuentaId = $this->db->table('cuentas_financiamiento')->insert($datosCuenta);
                
                if (!$cuentaId) {
                    echo "❌ Error creando cuenta para venta {$venta->venta_id}\n";
                    continue;
                }
                
                // Actualizar tabla_amortizacion para usar la cuenta correcta
                $actualizados = $this->db->table('tabla_amortizacion')
                    ->where('plan_financiamiento_id', $venta->venta_id)
                    ->update(['plan_financiamiento_id' => $cuentaId]);
                
                echo "✅ Cuenta creada (ID: {$cuentaId}) y {$actualizados} mensualidades actualizadas\n";
            }

            echo "\n🎉 CORRECCIÓN COMPLETADA!\n";
            echo "Ahora todas las ventas deberían tener su cuenta de financiamiento correcta.\n";

        } catch (\Exception $e) {
            echo "\n💥 ERROR: " . $e->getMessage() . "\n";
        }

        echo "</pre>";
    }
}