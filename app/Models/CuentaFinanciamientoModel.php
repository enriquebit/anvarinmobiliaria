<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\CuentaFinanciamiento;

/**
 * Model CuentaFinanciamientoModel
 * 
 * Gestiona las cuentas de financiamiento individuales
 * con seguimiento completo de saldos y pagos
 */
class CuentaFinanciamientoModel extends Model
{
    protected $table            = 'cuentas_financiamiento';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = CuentaFinanciamiento::class;
    protected $useSoftDeletes    = false;
    protected $protectFields     = true;
    protected $allowedFields     = [
        'venta_id',
        'cliente_id',
        'lote_id',
        'numero_cuenta',
        'capital_inicial',
        'saldo_inicial',  // AGREGADO
        'saldo_actual',   // AGREGADO - ESTE ES EL IMPORTANTE!
        'saldo_capital',
        'saldo_interes',
        'saldo_moratorio',
        'saldo_total',
        'monto_mensualidad',
        'tasa_interes_anual',
        'tasa_moratoria_mensual',
        'tipo_tasa',
        'plazo_meses',
        'meses_transcurridos',
        'meses_restantes',
        'pagos_realizados',
        'pagos_vencidos',
        'total_abonos_capital',
        'total_intereses_pagados',
        'total_moratorios_pagados',
        'refactorizaciones_aplicadas',
        'estado',
        'fecha_apertura',
        'fecha_primer_pago',
        'fecha_ultimo_pago',
        'fecha_liquidacion',
        'observaciones',
        'plan_financiamiento_id'  // AGREGADO por si acaso
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation (relajada para desarrollo)
    protected $validationRules = [
        'venta_id' => 'permit_empty|integer',
        'estado' => 'permit_empty|in_list[activa,liquidada,suspendida,juridico,cancelada]'
    ];

    /**
     * Abrir nueva cuenta de financiamiento
     */
    public function abrirCuenta(int $ventaId, array $liquidacionData): array
    {
        try {
            $this->db->transStart();

            // Verificar que no exista cuenta activa
            $cuentaExistente = $this->where('venta_id', $ventaId)
                                   ->where('estado', 'activa')
                                   ->first();

            if ($cuentaExistente) {
                return [
                    'success' => false,
                    'mensaje' => 'Ya existe una cuenta activa para esta venta'
                ];
            }

            // Crear nueva cuenta
            $cuenta = new CuentaFinanciamiento();
            $cuenta->venta_id = $ventaId;
            $cuenta->cliente_id = $liquidacionData['cliente_id'];
            $cuenta->lote_id = $liquidacionData['lote_id'];
            $cuenta->capital_inicial = $liquidacionData['capital_inicial'];
            $cuenta->saldo_capital = $liquidacionData['capital_inicial'];
            $cuenta->saldo_interes = 0;
            $cuenta->saldo_moratorio = 0;
            $cuenta->monto_mensualidad = $liquidacionData['monto_mensualidad'];
            $cuenta->tasa_interes_anual = $liquidacionData['tasa_interes_anual'];
            $cuenta->tasa_moratoria_mensual = $liquidacionData['tasa_moratoria_mensual'] ?? 3.0;
            $cuenta->tipo_tasa = $liquidacionData['tipo_tasa'] ?? 'fija';
            $cuenta->plazo_meses = $liquidacionData['plazo_meses'];
            $cuenta->meses_transcurridos = 0;
            $cuenta->meses_restantes = $liquidacionData['plazo_meses'];
            $cuenta->pagos_realizados = 0;
            $cuenta->pagos_vencidos = 0;
            $cuenta->total_abonos_capital = 0;
            $cuenta->total_intereses_pagados = 0;
            $cuenta->total_moratorios_pagados = 0;
            $cuenta->refactorizaciones_aplicadas = 0;
            $cuenta->estado = 'activa';
            $cuenta->fecha_apertura = date('Y-m-d');
            
            // Calcular fecha del primer pago
            $fechaPrimerPago = new \DateTime();
            $fechaPrimerPago->modify('+1 month');
            $cuenta->fecha_primer_pago = $fechaPrimerPago->format('Y-m-d');
            
            // Generar número de cuenta
            $cuenta->generarNumeroCuenta();
            $cuenta->calcularSaldoTotal();

            // Guardar cuenta
            $this->save($cuenta);

            // Crear registro de movimiento de apertura
            $movimientoModel = new \App\Models\MovimientoCuentaModel();
            $movimiento = \App\Entities\MovimientoCuenta::crearApertura([
                'cuenta_id' => $cuenta->id,
                'capital_inicial' => $cuenta->capital_inicial,
                'plazo_meses' => $cuenta->plazo_meses,
                'tasa_interes' => $cuenta->tasa_interes_anual,
                'monto_mensualidad' => $cuenta->monto_mensualidad,
                'usuario_id' => auth()->id() ?? 1
            ]);
            
            $movimientoModel->save($movimiento);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'mensaje' => 'Error al abrir la cuenta de financiamiento'
                ];
            }

            return [
                'success' => true,
                'cuenta_id' => $cuenta->id,
                'numero_cuenta' => $cuenta->numero_cuenta,
                'capital_inicial' => $cuenta->capital_inicial,
                'monto_mensualidad' => $cuenta->monto_mensualidad,
                'fecha_primer_pago' => $cuenta->fecha_primer_pago,
                'mensaje' => 'Cuenta de financiamiento abierta exitosamente'
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error abriendo cuenta: ' . $e->getMessage());
            
            return [
                'success' => false,
                'mensaje' => 'Error interno al abrir la cuenta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener saldo actual de una cuenta
     */
    public function getSaldoActual(int $cuentaId): array
    {
        $cuenta = $this->find($cuentaId);
        
        if (!$cuenta) {
            return [
                'success' => false,
                'mensaje' => 'Cuenta no encontrada'
            ];
        }

        return [
            'success' => true,
            'saldo_capital' => $cuenta->saldo_capital,
            'saldo_interes' => $cuenta->saldo_interes,
            'saldo_moratorio' => $cuenta->saldo_moratorio,
            'saldo_total' => $cuenta->saldo_total,
            'numero_cuenta' => $cuenta->numero_cuenta,
            'estado' => $cuenta->estado,
            'al_corriente' => $cuenta->estaAlCorriente()
        ];
    }

    /**
     * Aplicar pago de mensualidad
     */
    public function aplicarMensualidad(int $cuentaId, array $pagoData): array
    {
        try {
            $this->db->transStart();

            $cuenta = $this->find($cuentaId);
            if (!$cuenta) {
                return [
                    'success' => false,
                    'mensaje' => 'Cuenta no encontrada'
                ];
            }

            // Guardar saldos anteriores
            $saldosAnteriores = [
                'capital' => $cuenta->saldo_capital,
                'interes' => $cuenta->saldo_interes,
                'moratorio' => $cuenta->saldo_moratorio,
                'total' => $cuenta->saldo_total
            ];

            // Aplicar pago
            $resultado = $cuenta->aplicarPagoMensualidad($pagoData['monto']);
            
            if (!$resultado['success']) {
                $this->db->transRollback();
                return $resultado;
            }

            // Actualizar cuenta
            $this->save($cuenta);

            // Crear registro de movimiento
            $movimientoModel = new \App\Models\MovimientoCuentaModel();
            $movimiento = \App\Entities\MovimientoCuenta::crearPago([
                'cuenta_id' => $cuentaId,
                'pago_id' => $pagoData['pago_id'] ?? null,
                'concepto' => 'mensualidad',
                'descripcion' => 'Pago de mensualidad #' . $cuenta->pagos_realizados,
                'monto' => $pagoData['monto'],
                'fecha_aplicacion' => $pagoData['fecha_aplicacion'] ?? date('Y-m-d'),
                'saldo_anterior' => $saldosAnteriores,
                'saldo_nuevo' => [
                    'capital' => $cuenta->saldo_capital,
                    'interes' => $cuenta->saldo_interes,
                    'moratorio' => $cuenta->saldo_moratorio,
                    'total' => $cuenta->saldo_total
                ],
                'distribucion' => $resultado['distribucion'],
                'usuario_id' => auth()->id() ?? 1,
                'es_automatico' => $pagoData['es_automatico'] ?? false
            ]);
            
            $movimientoModel->save($movimiento);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'mensaje' => 'Error al aplicar el pago'
                ];
            }

            return array_merge($resultado, [
                'movimiento_id' => $movimiento->id,
                'nuevo_saldo_total' => $cuenta->saldo_total,
                'pagos_realizados' => $cuenta->pagos_realizados
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error aplicando mensualidad: ' . $e->getMessage());
            
            return [
                'success' => false,
                'mensaje' => 'Error interno al aplicar el pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Aplicar abono a capital
     */
    public function aplicarAbonoCapital(int $cuentaId, float $monto): array
    {
        try {
            $this->db->transStart();

            $cuenta = $this->find($cuentaId);
            if (!$cuenta) {
                return [
                    'success' => false,
                    'mensaje' => 'Cuenta no encontrada'
                ];
            }

            // Guardar saldos anteriores
            $saldosAnteriores = [
                'capital' => $cuenta->saldo_capital,
                'interes' => $cuenta->saldo_interes,
                'moratorio' => $cuenta->saldo_moratorio,
                'total' => $cuenta->saldo_total
            ];

            // Aplicar abono
            $resultado = $cuenta->aplicarAbonoCapital($monto);
            
            if (!$resultado['success']) {
                $this->db->transRollback();
                return $resultado;
            }

            // Actualizar cuenta
            $this->save($cuenta);

            // Crear registro de movimiento
            $movimientoModel = new \App\Models\MovimientoCuentaModel();
            $movimiento = \App\Entities\MovimientoCuenta::crearPago([
                'cuenta_id' => $cuentaId,
                'concepto' => 'abono_capital',
                'descripcion' => 'Abono a capital por $' . number_format($monto, 2),
                'monto' => $monto,
                'saldo_anterior' => $saldosAnteriores,
                'saldo_nuevo' => [
                    'capital' => $cuenta->saldo_capital,
                    'interes' => $cuenta->saldo_interes,
                    'moratorio' => $cuenta->saldo_moratorio,
                    'total' => $cuenta->saldo_total
                ],
                'distribucion' => $resultado['distribucion'],
                'usuario_id' => auth()->id() ?? 1
            ]);
            
            $movimientoModel->save($movimiento);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'mensaje' => 'Error al aplicar el abono'
                ];
            }

            return array_merge($resultado, [
                'movimiento_id' => $movimiento->id
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error aplicando abono: ' . $e->getMessage());
            
            return [
                'success' => false,
                'mensaje' => 'Error interno al aplicar el abono: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener historial de movimientos de una cuenta
     */
    public function getHistorialMovimientos(int $cuentaId, int $limit = 50): array
    {
        $movimientoModel = new \App\Models\MovimientoCuentaModel();
        
        return $movimientoModel->where('cuenta_financiamiento_id', $cuentaId)
                              ->orderBy('fecha_movimiento', 'DESC')
                              ->limit($limit)
                              ->findAll();
    }

    /**
     * Obtener cuentas activas
     */
    public function getCuentasActivas(): array
    {
        return $this->select('
                cuentas_financiamiento.*,
                v.folio_venta,
                c.nombres,
                c.apellido_paterno,
                c.apellido_materno,
                c.email,
                c.telefono,
                l.clave as lote_clave,
                p.nombre as proyecto_nombre
            ')
            ->join('ventas v', 'v.id = cuentas_financiamiento.venta_id')
            ->join('clientes c', 'c.id = cuentas_financiamiento.cliente_id')
            ->join('lotes l', 'l.id = cuentas_financiamiento.lote_id')
            ->join('proyectos p', 'p.id = l.proyectos_id')
            ->where('cuentas_financiamiento.estado', 'activa')
            ->orderBy('cuentas_financiamiento.fecha_apertura', 'DESC')
            ->findAll();
    }

    /**
     * Obtener cuentas con pagos vencidos
     */
    public function getCuentasVencidas(): array
    {
        return $this->select('
                cuentas_financiamiento.*,
                v.folio_venta,
                c.nombres,
                c.apellido_paterno,
                c.apellido_materno,
                c.telefono,
                l.clave as lote_clave
            ')
            ->join('ventas v', 'v.id = cuentas_financiamiento.venta_id')
            ->join('clientes c', 'c.id = cuentas_financiamiento.cliente_id')
            ->join('lotes l', 'l.id = cuentas_financiamiento.lote_id')
            ->where('cuentas_financiamiento.estado', 'activa')
            ->where('cuentas_financiamiento.pagos_vencidos >', 0)
            ->orderBy('cuentas_financiamiento.pagos_vencidos', 'DESC')
            ->findAll();
    }

    /**
     * Obtener estadísticas de cartera
     */
    public function getEstadisticasCartera(): array
    {
        $stats = $this->select('
                COUNT(*) as total_cuentas,
                SUM(CASE WHEN estado = "activa" THEN 1 ELSE 0 END) as cuentas_activas,
                SUM(CASE WHEN estado = "liquidada" THEN 1 ELSE 0 END) as cuentas_liquidadas,
                SUM(CASE WHEN estado = "juridico" THEN 1 ELSE 0 END) as cuentas_juridico,
                SUM(capital_inicial) as capital_total_colocado,
                SUM(saldo_capital) as saldo_capital_total,
                SUM(saldo_total) as saldo_total_cartera,
                SUM(total_abonos_capital) as total_abonos_capital,
                SUM(total_intereses_pagados) as total_intereses_cobrados,
                AVG(tasa_interes_anual) as tasa_promedio
            ')
            ->get()
            ->getRowArray();

        return $stats ?: [];
    }
}