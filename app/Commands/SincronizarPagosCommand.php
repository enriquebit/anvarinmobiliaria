<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SincronizarPagosCommand extends BaseCommand
{
    protected $group       = 'ANVAR';
    protected $name        = 'sync:pagos';
    protected $description = 'Sincronizar datos entre tabla_amortizacion, pagos_ventas e ingresos';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        CLI::write('🔄 INICIANDO SINCRONIZACIÓN DE PAGOS', 'yellow');
        CLI::newLine();
        
        // Obtener ventas que necesitan sincronización
        $ventasDesincronizadas = $db->query("
            SELECT DISTINCT v.id, v.folio_venta
            FROM ventas v
            INNER JOIN tabla_amortizacion ta ON ta.venta_id = v.id
            INNER JOIN pagos_ventas pv ON pv.venta_id = v.id AND pv.tabla_amortizacion_id = ta.id
            WHERE ta.estatus = 'pagada' AND pv.estatus_pago = 'pendiente'
        ")->getResult();
        
        CLI::write("📊 Encontradas " . count($ventasDesincronizadas) . " ventas con desincronización", 'cyan');
        CLI::newLine();
        
        $totalSincronizadas = 0;
        
        foreach ($ventasDesincronizadas as $venta) {
            CLI::write("🔧 Sincronizando venta {$venta->folio_venta} (ID: {$venta->id})", 'white');
            
            $resultado = $this->sincronizarVenta($venta->id);
            
            if ($resultado['success']) {
                CLI::write("  ✅ Sincronizada: {$resultado['mensualidades_sincronizadas']} mensualidades", 'green');
                CLI::write("  💰 Total sincronizado: \${$resultado['monto_total_sincronizado']}", 'green');
                $totalSincronizadas++;
            } else {
                CLI::write("  ❌ Error: {$resultado['error']}", 'red');
            }
        }
        
        CLI::newLine();
        CLI::write("🎉 SINCRONIZACIÓN COMPLETADA", 'green');
        CLI::write("📈 Ventas sincronizadas: {$totalSincronizadas}/" . count($ventasDesincronizadas), 'cyan');
    }
    
    private function sincronizarVenta(int $ventaId): array
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // 1. Obtener mensualidades pagadas de tabla_amortizacion
            $mensualidadesPagadas = $db->query("
                SELECT ta.*, i.monto as monto_ingreso, i.fecha_ingreso, i.metodo_pago, i.referencia
                FROM tabla_amortizacion ta
                LEFT JOIN ingresos i ON i.venta_id = ta.venta_id AND i.tipo_ingreso = 'mensualidad'
                WHERE ta.venta_id = ? AND ta.estatus = 'pagada'
                ORDER BY ta.numero_pago
            ", [$ventaId])->getResult();
            
            $mensualidadesSincronizadas = 0;
            $montoTotalSincronizado = 0;
            
            foreach ($mensualidadesPagadas as $mensualidad) {
                // 2. Buscar el registro correspondiente en pagos_ventas
                $pagoVenta = $db->table('pagos_ventas')
                    ->where('venta_id', $ventaId)
                    ->where('tabla_amortizacion_id', $mensualidad->id)
                    ->get()
                    ->getRow();
                
                if ($pagoVenta && $pagoVenta->estatus_pago === 'pendiente') {
                    // 3. Actualizar el registro de pago_venta
                    $datosActualizacion = [
                        'monto_pago' => $mensualidad->monto_ingreso ?? $mensualidad->monto_total,
                        'fecha_pago' => $mensualidad->fecha_ingreso ?? $mensualidad->fecha_ultimo_pago,
                        'forma_pago' => $mensualidad->metodo_pago ?? 'transferencia',
                        'referencia_pago' => $mensualidad->referencia ?? '',
                        'estatus_pago' => 'aplicado',
                        'folio_pago' => 'PAG-' . date('Ymd') . '-' . str_pad($pagoVenta->id, 4, '0', STR_PAD_LEFT),
                        'numero_mensualidad' => $mensualidad->numero_pago
                    ];
                    
                    $db->table('pagos_ventas')
                        ->where('id', $pagoVenta->id)
                        ->update($datosActualizacion);
                    
                    $mensualidadesSincronizadas++;
                    $montoTotalSincronizado += ($mensualidad->monto_ingreso ?? $mensualidad->monto_total);
                }
            }
            
            // 4. Actualizar saldo en cuentas_financiamiento
            $totalPagado = $db->table('pagos_ventas')
                ->selectSum('monto_pago')
                ->where('venta_id', $ventaId)
                ->where('estatus_pago', 'aplicado')
                ->get()
                ->getRow()
                ->monto_pago ?? 0;
            
            $cuenta = $db->table('cuentas_financiamiento')
                ->where('venta_id', $ventaId)
                ->get()
                ->getRow();
            
            if ($cuenta) {
                $nuevoSaldo = $cuenta->saldo_inicial - $totalPagado;
                $db->table('cuentas_financiamiento')
                    ->where('venta_id', $ventaId)
                    ->update(['saldo_actual' => $nuevoSaldo]);
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de sincronización');
            }
            
            return [
                'success' => true,
                'mensualidades_sincronizadas' => $mensualidadesSincronizadas,
                'monto_total_sincronizado' => $montoTotalSincronizado
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