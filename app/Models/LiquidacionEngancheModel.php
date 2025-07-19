<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\LiquidacionEnganche;

/**
 * Model LiquidacionEngancheModel
 * 
 * Gestiona las liquidaciones de enganche desde anticipos
 * y la apertura de cuentas de financiamiento
 */
class LiquidacionEngancheModel extends Model
{
    protected $table            = 'liquidaciones_enganche';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = LiquidacionEnganche::class;
    protected $useSoftDeletes    = false;
    protected $protectFields     = true;
    protected $allowedFields     = [
        'venta_id',
        'cuenta_financiamiento_id',
        'monto_enganche_requerido',
        'monto_anticipos_aplicados',
        'monto_pago_directo',
        'monto_total_liquidado',
        'saldo_pendiente',
        'tipo_origen',
        'estado',
        'cuenta_abierta',
        'tabla_amortizacion_generada',
        'anticipos_ids',
        'folio_liquidacion',
        'fecha_liquidacion',
        'fecha_apertura_cuenta',
        'observaciones'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'venta_id' => 'required|integer',
        'monto_enganche_requerido' => 'required|decimal|greater_than[0]',
        'estado' => 'in_list[pendiente,completada,parcial,cancelada]',
        'tipo_origen' => 'in_list[anticipos,pago_directo,mixto]'
    ];

    /**
     * Obtener liquidaciones por venta
     */
    public function getLiquidacionesPorVenta(int $ventaId): array
    {
        return $this->where('venta_id', $ventaId)
                   ->orderBy('fecha_liquidacion', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener liquidaciones completadas listas para apertura de cuenta
     */
    public function getLiquidacionesListasParaCuenta(): array
    {
        return $this->select('
                liquidaciones_enganche.*,
                v.folio_venta,
                c.nombres,
                c.apellido_paterno,
                c.apellido_materno,
                l.clave as lote_clave
            ')
            ->join('ventas v', 'v.id = liquidaciones_enganche.venta_id')
            ->join('clientes c', 'c.id = v.cliente_id')
            ->join('lotes l', 'l.id = v.lote_id')
            ->where('liquidaciones_enganche.estado', 'completada')
            ->where('liquidaciones_enganche.cuenta_abierta', false)
            ->orderBy('liquidaciones_enganche.fecha_liquidacion', 'ASC')
            ->findAll();
    }
}