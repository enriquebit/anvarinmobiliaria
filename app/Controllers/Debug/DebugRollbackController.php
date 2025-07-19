<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;

class DebugRollbackController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Menú principal del sistema de rollback
     */
    public function index()
    {
        return view('debug/rollback_menu');
    }

    /**
     * Rollback completo del sistema - SOLO PARA DESARROLLO
     */
    public function rollbackCompleto()
    {
        // Verificar que estamos en desarrollo
        if (ENVIRONMENT !== 'development') {
            return $this->response->setJSON([
                'error' => 'Esta operación solo está disponible en desarrollo'
            ]);
        }

        // Deshabilitar foreign key checks temporalmente para evitar errores de restricciones
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        
        $this->db->transStart();

        try {
            echo "<h1>🔄 ROLLBACK COMPLETO DEL SISTEMA</h1>\n";
            echo "<p><strong>⚠️ ADVERTENCIA:</strong> Esta operación eliminará TODOS los datos de ventas, pagos y comisiones.</p>\n";
            echo "<p><strong>🔧 INFO:</strong> Foreign key checks deshabilitados temporalmente.</p>\n";
            echo "<hr>\n";

            // 0. NUEVO: Eliminar pagos_aplicados PRIMERO (tiene FK a pagos_ventas y tabla_amortizacion)
            echo "<h3>0. Eliminando pagos aplicados (FK constraints)...</h3>\n";
            if ($this->db->tableExists('pagos_aplicados')) {
                $totalPagosAplicados = $this->db->table('pagos_aplicados')->countAll();
                $this->db->table('pagos_aplicados')->truncate();
                echo "✅ Eliminados {$totalPagosAplicados} registros de pagos_aplicados\n";
            } else {
                echo "ℹ️ Tabla pagos_aplicados no existe\n";
            }

            // 1. Eliminar tabla de amortización
            echo "<h3>1. Eliminando tabla de amortización...</h3>\n";
            $totalAmortizacion = $this->db->table('tabla_amortizacion')->countAll();
            $this->db->table('tabla_amortizacion')->truncate();
            echo "✅ Eliminados {$totalAmortizacion} registros de tabla_amortizacion\n";

            // 2. Eliminar cuentas de financiamiento
            echo "<h3>2. Eliminando cuentas de financiamiento...</h3>\n";
            $totalCuentas = $this->db->table('cuentas_financiamiento')->countAll();
            $this->db->table('cuentas_financiamiento')->truncate();
            echo "✅ Eliminados {$totalCuentas} registros de cuentas_financiamiento\n";

            // 3. Eliminar pagos de ventas
            echo "<h3>3. Eliminando pagos de ventas...</h3>\n";
            $totalPagos = $this->db->table('pagos_ventas')->countAll();
            $this->db->table('pagos_ventas')->truncate();
            echo "✅ Eliminados {$totalPagos} registros de pagos_ventas\n";

            // 4. Eliminar ingresos
            echo "<h3>4. Eliminando ingresos...</h3>\n";
            $totalIngresos = $this->db->table('ingresos')->countAll();
            $this->db->table('ingresos')->truncate();
            echo "✅ Eliminados {$totalIngresos} registros de ingresos\n";

            // 5. Eliminar registros relacionados a ventas
            echo "<h3>5. Eliminando registros relacionados a ventas...</h3>\n";
            $tablasRelacionadas = [
                'comisiones_ventas' => 'comisiones de ventas',
                'bonos_comisiones' => 'bonos de comisiones',
                'pagos_comisiones' => 'pagos de comisiones',
                'devoluciones_ventas' => 'devoluciones de ventas',
                'ventas_documentos' => 'documentos de ventas',
                'ventas_historial' => 'historial de ventas'
            ];
            
            foreach ($tablasRelacionadas as $tabla => $descripcion) {
                if ($this->db->tableExists($tabla)) {
                    $totalRegistros = $this->db->table($tabla)->countAll();
                    $this->db->table($tabla)->truncate();
                    echo "✅ Eliminados {$totalRegistros} registros de {$descripcion}\n";
                } else {
                    echo "ℹ️ Tabla {$descripcion} no existe\n";
                }
            }

            // 6. Eliminar apartados
            echo "<h3>6. Eliminando apartados...</h3>\n";
            if ($this->db->tableExists('apartados')) {
                $totalApartados = $this->db->table('apartados')->countAll();
                $this->db->table('apartados')->truncate();
                echo "✅ Eliminados {$totalApartados} registros de apartados\n";
            } else {
                echo "ℹ️ Tabla apartados no existe\n";
            }

            // 7. Eliminar conceptos de pago
            echo "<h3>7. Eliminando conceptos de pago...</h3>\n";
            if ($this->db->tableExists('conceptos_pago')) {
                $totalConceptos = $this->db->table('conceptos_pago')->countAll();
                $this->db->table('conceptos_pago')->truncate();
                echo "✅ Eliminados {$totalConceptos} registros de conceptos_pago\n";
            } else {
                echo "ℹ️ Tabla conceptos_pago no existe\n";
            }

            // 8. Actualizar estado de lotes a "disponible" (ID 1)
            echo "<h3>8. Liberando lotes...</h3>\n";
            $lotesActualizados = $this->db->table('lotes')
                ->where('estados_lotes_id !=', 1)
                ->update(['estados_lotes_id' => 1]);
            echo "✅ Liberados {$lotesActualizados} lotes (estado = 'Disponible')\n";

            // 9. Obtener ventas antes de eliminar
            echo "<h3>9. Obteniendo información de ventas...</h3>\n";
            $ventas = $this->db->table('ventas v')
                ->select('v.id, v.folio_venta, v.cliente_id, v.lote_id, c.nombres, c.apellido_paterno, l.clave')
                ->join('clientes c', 'c.id = v.cliente_id', 'left')
                ->join('lotes l', 'l.id = v.lote_id', 'left')
                ->get()
                ->getResult();

            echo "<table border='1' cellpadding='5'>\n";
            echo "<tr><th>ID</th><th>Folio</th><th>Cliente</th><th>Lote</th></tr>\n";
            foreach ($ventas as $venta) {
                echo "<tr><td>{$venta->id}</td><td>{$venta->folio_venta}</td><td>{$venta->nombres} {$venta->apellido_paterno}</td><td>{$venta->clave}</td></tr>\n";
            }
            echo "</table>\n";

            // 10. Eliminar ventas
            echo "<h3>10. Eliminando ventas...</h3>\n";
            $totalVentas = $this->db->table('ventas')->countAll();
            $this->db->table('ventas')->truncate();
            echo "✅ Eliminados {$totalVentas} registros de ventas\n";

            // 11. Resetear secuencias AUTO_INCREMENT
            echo "<h3>11. Reseteando secuencias AUTO_INCREMENT...</h3>\n";
            $tablas = [
                'ventas',
                'pagos_ventas',
                'pagos_aplicados',  // AGREGADO: nueva tabla con FK
                'ingresos',
                'tabla_amortizacion',
                'cuentas_financiamiento',
                'apartados',
                'conceptos_pago',
                'comisiones_ventas',
                'bonos_comisiones',
                'pagos_comisiones',
                'devoluciones_ventas',
                'ventas_documentos',
                'ventas_historial'
            ];

            foreach ($tablas as $tabla) {
                if ($this->db->tableExists($tabla)) {
                    $this->db->query("ALTER TABLE {$tabla} AUTO_INCREMENT = 1");
                    echo "✅ Reseteado AUTO_INCREMENT para {$tabla}\n";
                }
            }

            // Re-habilitar foreign key checks
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
            echo "<h3>12. Re-habilitando foreign key checks...</h3>\n";
            echo "✅ Foreign key checks reactivados\n";

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transacción de rollback');
            }

            echo "<hr>\n";
            echo "<h2>🎉 ROLLBACK COMPLETADO EXITOSAMENTE</h2>\n";
            echo "<p><strong>Resumen:</strong></p>\n";
            echo "<ul>\n";
            echo "<li>✅ Tabla de amortización limpia</li>\n";
            echo "<li>✅ Cuentas de financiamiento eliminadas</li>\n";
            echo "<li>✅ Pagos eliminados</li>\n";
            echo "<li>✅ Ingresos eliminados</li>\n";
            echo "<li>✅ Comisiones eliminadas</li>\n";
            echo "<li>✅ Ventas eliminadas</li>\n";
            echo "<li>✅ Lotes liberados</li>\n";
            echo "<li>✅ Secuencias reseteadas</li>\n";
            echo "</ul>\n";
            echo "<p><strong>🔄 Sistema listo para nuevas pruebas</strong></p>\n";

        } catch (\Exception $e) {
            // Re-habilitar foreign key checks incluso en caso de error
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
            $this->db->transRollback();
            echo "<h2>❌ ERROR EN ROLLBACK</h2>\n";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>\n";
            echo "<p><strong>Trace:</strong> " . $e->getTraceAsString() . "</p>\n";
            echo "<p><strong>🔧 INFO:</strong> Foreign key checks re-habilitados después del error.</p>\n";
        }
    }

    /**
     * Rollback específico de una venta por query parameter
     */
    public function rollbackVentaByQuery()
    {
        $ventaId = $this->request->getGet('venta_id');
        return $this->rollbackVenta($ventaId);
    }

    /**
     * Rollback específico de una venta
     */
    public function rollbackVenta($ventaId = null)
    {
        if (!$ventaId) {
            echo "❌ ID de venta requerido\n";
            return;
        }

        $this->db->transStart();

        try {
            echo "<h1>🔄 ROLLBACK DE VENTA {$ventaId}</h1>\n";

            // Obtener información de la venta
            $venta = $this->db->table('ventas v')
                ->select('v.*, c.nombres, c.apellido_paterno, l.clave')
                ->join('clientes c', 'c.id = v.cliente_id', 'left')
                ->join('lotes l', 'l.id = v.lote_id', 'left')
                ->where('v.id', $ventaId)
                ->get()
                ->getRow();

            if (!$venta) {
                echo "❌ Venta no encontrada\n";
                return;
            }

            echo "<p><strong>Venta:</strong> {$venta->folio_venta}</p>\n";
            echo "<p><strong>Cliente:</strong> {$venta->nombres} {$venta->apellido_paterno}</p>\n";
            echo "<p><strong>Lote:</strong> {$venta->clave}</p>\n";
            echo "<hr>\n";

            // 1. Eliminar pagos aplicados primero (si existen, tienen FK a pagos_ventas y tabla_amortizacion)
            if ($this->db->tableExists('pagos_aplicados')) {
                try {
                    // Método más simple: obtener IDs primero y luego eliminar
                    $pagosVentasIds = $this->db->table('pagos_ventas')
                        ->select('id')
                        ->where('venta_id', $ventaId)
                        ->get()
                        ->getResultArray();
                    
                    $eliminados = 0;
                    if (!empty($pagosVentasIds)) {
                        $ids = array_column($pagosVentasIds, 'id');
                        $eliminados = $this->db->table('pagos_aplicados')
                            ->whereIn('pago_venta_id', $ids)
                            ->delete();
                    }
                    
                    echo "✅ Eliminados pagos aplicados: {$eliminados} registros\n";
                } catch (\Exception $e) {
                    echo "⚠️ Error eliminando pagos aplicados: " . $e->getMessage() . "\n";
                }
            }

            // 2. Eliminar pagos (para respetar FK constraints)
            try {
                $pagos = $this->db->table('pagos_ventas')
                    ->where('venta_id', $ventaId)
                    ->delete();
                echo "✅ Eliminados pagos: {$pagos} registros\n";
            } catch (\Exception $e) {
                echo "⚠️ Error eliminando pagos: " . $e->getMessage() . "\n";
            }

            // 3. Eliminar tabla de amortización
            try {
                $amortizacion = $this->db->table('tabla_amortizacion')
                    ->where('venta_id', $ventaId)
                    ->delete();
                echo "✅ Eliminada tabla de amortización: {$amortizacion} registros\n";
            } catch (\Exception $e) {
                echo "⚠️ Error eliminando tabla de amortización: " . $e->getMessage() . "\n";
            }

            // 4. Eliminar cuenta de financiamiento
            try {
                $cuentas = $this->db->table('cuentas_financiamiento')
                    ->where('venta_id', $ventaId)
                    ->delete();
                echo "✅ Eliminada cuenta de financiamiento: {$cuentas} registros\n";
            } catch (\Exception $e) {
                echo "⚠️ Error eliminando cuenta de financiamiento: " . $e->getMessage() . "\n";
            }

            // 5. Eliminar ingresos
            try {
                $ingresos = $this->db->table('ingresos')
                    ->where('venta_id', $ventaId)
                    ->delete();
                echo "✅ Eliminados ingresos: {$ingresos} registros\n";
            } catch (\Exception $e) {
                echo "⚠️ Error eliminando ingresos: " . $e->getMessage() . "\n";
            }

            // 6. Eliminar registros relacionados por foreign key
            $tablasRelacionadas = [
                'comisiones_ventas' => 'comisiones',
                'devoluciones_ventas' => 'devoluciones',
                'ventas_documentos' => 'documentos de venta',
                'ventas_historial' => 'historial de venta'
            ];
            
            foreach ($tablasRelacionadas as $tabla => $descripcion) {
                if ($this->db->tableExists($tabla)) {
                    try {
                        $eliminados = $this->db->table($tabla)
                            ->where('venta_id', $ventaId)
                            ->delete();
                        echo "✅ Eliminados {$descripcion}: {$eliminados} registros\n";
                    } catch (\Exception $e) {
                        echo "⚠️ Error eliminando {$descripcion}: " . $e->getMessage() . "\n";
                    }
                }
            }

            // 7. Liberar lote (estado = 1 es "Disponible")
            if ($venta->lote_id) {
                $this->db->table('lotes')
                    ->where('id', $venta->lote_id)
                    ->update(['estados_lotes_id' => 1]);
                echo "✅ Lote {$venta->clave} liberado\n";
            }

            // 8. Eliminar venta
            try {
                $ventaEliminada = $this->db->table('ventas')
                    ->where('id', $ventaId)
                    ->delete();
                echo "✅ Venta eliminada: {$ventaEliminada} registro\n";
            } catch (\Exception $e) {
                echo "⚠️ Error eliminando venta: " . $e->getMessage() . "\n";
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transacción de rollback');
            }

            echo "<h2>🎉 ROLLBACK DE VENTA COMPLETADO</h2>\n";

        } catch (\Exception $e) {
            $this->db->transRollback();
            echo "<h2>❌ ERROR EN ROLLBACK</h2>\n";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>\n";
        }
    }

    /**
     * Rollback del último pago de una venta específica
     */
    public function rollbackUltimoPago($ventaId = null)
    {
        if (!$ventaId) {
            $ventaId = $this->request->getGet('venta_id');
        }
        
        if (!$ventaId) {
            echo "❌ ID de venta requerido\n";
            return;
        }

        $this->db->transStart();

        try {
            echo "<h1>🔄 ROLLBACK DEL ÚLTIMO PAGO - VENTA {$ventaId}</h1>\n";
            echo "<hr>\n";

            // 1. Obtener información de la venta
            $venta = $this->db->table('ventas v')
                ->select('v.*, c.nombres, c.apellido_paterno, l.clave')
                ->join('clientes c', 'c.id = v.cliente_id', 'left')
                ->join('lotes l', 'l.id = v.lote_id', 'left')
                ->where('v.id', $ventaId)
                ->get()
                ->getRow();

            if (!$venta) {
                echo "❌ Venta no encontrada\n";
                return;
            }

            echo "<p><strong>Venta:</strong> {$venta->folio_venta}</p>\n";
            echo "<p><strong>Cliente:</strong> {$venta->nombres} {$venta->apellido_paterno}</p>\n";
            echo "<p><strong>Lote:</strong> {$venta->clave}</p>\n";
            echo "<hr>\n";

            // 2. Obtener el último pago registrado en ingresos
            $ultimoIngreso = $this->db->table('ingresos')
                ->where('venta_id', $ventaId)
                ->where('tipo_ingreso', 'mensualidad')
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();

            if ($ultimoIngreso) {
                echo "<h3>🗑️ Eliminando último ingreso:</h3>\n";
                echo "<p>ID: {$ultimoIngreso->id}, Folio: {$ultimoIngreso->folio}, Monto: \${$ultimoIngreso->monto}</p>\n";
                
                $this->db->table('ingresos')->where('id', $ultimoIngreso->id)->delete();
                echo "✅ Ingreso eliminado\n";
            } else {
                echo "ℹ️ No se encontraron ingresos de mensualidad\n";
            }

            // 3. Obtener el último pago en pagos_ventas
            $ultimoPagoVenta = $this->db->table('pagos_ventas')
                ->where('venta_id', $ventaId)
                ->where('concepto_pago', 'mensualidad')
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();

            if ($ultimoPagoVenta) {
                echo "<h3>🗑️ Eliminando último pago_venta:</h3>\n";
                echo "<p>ID: {$ultimoPagoVenta->id}, Folio: {$ultimoPagoVenta->folio_pago}, Monto: \${$ultimoPagoVenta->monto_pago}</p>\n";
                
                $this->db->table('pagos_ventas')->where('id', $ultimoPagoVenta->id)->delete();
                echo "✅ Pago_venta eliminado\n";
            } else {
                echo "ℹ️ No se encontraron pagos de mensualidad\n";
            }

            // 4. Revertir la mensualidad en tabla_amortizacion
            $mensualidadPagada = $this->db->table('tabla_amortizacion')
                ->where('venta_id', $ventaId)
                ->where('estatus', 'pagada')
                ->orderBy('numero_pago', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();

            if ($mensualidadPagada) {
                echo "<h3>🔄 Revirtiendo mensualidad:</h3>\n";
                echo "<p>Mensualidad #{$mensualidadPagada->numero_pago}, Monto: \${$mensualidadPagada->monto_total}</p>\n";
                
                $this->db->table('tabla_amortizacion')
                    ->where('id', $mensualidadPagada->id)
                    ->update([
                        'estatus' => 'pendiente',
                        'fecha_ultimo_pago' => null,
                        'monto_pagado' => 0,
                        'numero_pagos_aplicados' => 0
                    ]);
                echo "✅ Mensualidad revertida a estado pendiente\n";
            } else {
                echo "ℹ️ No se encontraron mensualidades pagadas\n";
            }

            // 5. Actualizar cuenta de financiamiento
            $cuenta = $this->db->table('cuentas_financiamiento')
                ->where('venta_id', $ventaId)
                ->get()
                ->getRow();

            if ($cuenta && $ultimoPagoVenta) {
                echo "<h3>🔄 Actualizando cuenta de financiamiento:</h3>\n";
                echo "<p>Saldo actual: \${$cuenta->saldo_actual}</p>\n";
                
                $nuevoSaldo = $cuenta->saldo_actual + $ultimoPagoVenta->monto_pago;
                
                $this->db->table('cuentas_financiamiento')
                    ->where('id', $cuenta->id)
                    ->update([
                        'saldo_actual' => $nuevoSaldo,
                        'estado' => 'activa'
                    ]);
                    
                echo "<p>Nuevo saldo: \${$nuevoSaldo}</p>\n";
                echo "✅ Cuenta actualizada\n";
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transacción de rollback');
            }

            echo "<hr>\n";
            echo "<h2>🎉 ROLLBACK COMPLETADO</h2>\n";
            echo "<p>El último pago de la venta {$ventaId} ha sido revertido exitosamente.</p>\n";
            echo "<p><a href='" . site_url('/admin/pagos/procesar-mensualidad/' . $ventaId) . "'>➡️ Volver a intentar el pago</a></p>\n";

        } catch (\Exception $e) {
            $this->db->transRollback();
            echo "<h2>❌ ERROR EN ROLLBACK</h2>\n";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>\n";
            echo "<p><strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre></p>\n";
        }
    }

    /**
     * Mostrar estado actual del sistema
     */
    public function estadoSistema()
    {
        echo "<h1>📊 ESTADO ACTUAL DEL SISTEMA</h1>\n";
        echo "<hr>\n";

        // Contar registros en tablas principales
        $tablas = [
            'ventas' => 'Ventas',
            'pagos_ventas' => 'Pagos de Ventas',
            'ingresos' => 'Ingresos',
            'tabla_amortizacion' => 'Tabla de Amortización',
            'cuentas_financiamiento' => 'Cuentas de Financiamiento',
            'apartados' => 'Apartados',
            'conceptos_pago' => 'Conceptos de Pago',
            'comisiones_ventas' => 'Comisiones de Ventas',
            'bonos_comisiones' => 'Bonos de Comisiones',
            'pagos_comisiones' => 'Pagos de Comisiones',
            'devoluciones_ventas' => 'Devoluciones de Ventas',
            'ventas_documentos' => 'Documentos de Ventas',
            'ventas_historial' => 'Historial de Ventas'
        ];

        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>Tabla</th><th>Registros</th><th>Estado</th></tr>\n";

        foreach ($tablas as $tabla => $nombre) {
            if ($this->db->tableExists($tabla)) {
                $count = $this->db->table($tabla)->countAll();
                $estado = $count > 0 ? "🟢 Con datos" : "🔴 Vacía";
                echo "<tr><td>{$nombre}</td><td>{$count}</td><td>{$estado}</td></tr>\n";
            } else {
                echo "<tr><td>{$nombre}</td><td>-</td><td>⚫ No existe</td></tr>\n";
            }
        }

        echo "</table>\n";

        // Estado de lotes
        echo "<h3>📍 Estado de Lotes</h3>\n";
        $estadosLotes = $this->db->table('lotes l')
            ->select('el.nombre as estado, COUNT(*) as total')
            ->join('estados_lotes el', 'el.id = l.estados_lotes_id', 'inner')
            ->groupBy('el.nombre')
            ->get()
            ->getResult();

        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>Estado</th><th>Cantidad</th></tr>\n";
        foreach ($estadosLotes as $estado) {
            echo "<tr><td>{$estado->estado}</td><td>{$estado->total}</td></tr>\n";
        }
        echo "</table>\n";

        echo "<hr>\n";
        echo "<p><strong>🔗 Acciones disponibles:</strong></p>\n";
        echo "<ul>\n";
        echo "<li><a href='" . site_url('debug/rollback/rollback-completo') . "'>🔄 Rollback Completo</a></li>\n";
        echo "<li><a href='" . site_url('debug/rollback/estado-sistema') . "'>📊 Actualizar Estado</a></li>\n";
        echo "</ul>\n";
    }
}