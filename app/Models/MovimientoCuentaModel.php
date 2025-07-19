<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\MovimientoCuenta;

/**
 * Model MovimientoCuentaModel
 * 
 * Gestiona los movimientos de las cuentas de financiamiento
 * para auditoría y trazabilidad completa
 */
class MovimientoCuentaModel extends Model
{
    protected $table            = 'movimientos_cuenta';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = MovimientoCuenta::class;
    protected $useSoftDeletes    = false;
    protected $protectFields     = true;
    protected $allowedFields     = [
        'cuenta_financiamiento_id',
        'pago_id',
        'refactorizacion_id',
        'tipo_movimiento',
        'concepto',
        'descripcion',
        'monto_cargo',
        'monto_abono',
        'saldo_anterior_capital',
        'saldo_anterior_interes',
        'saldo_anterior_moratorio',
        'saldo_anterior_total',
        'saldo_nuevo_capital',
        'saldo_nuevo_interes',
        'saldo_nuevo_moratorio',
        'saldo_nuevo_total',
        'aplicado_a_capital',
        'aplicado_a_interes',
        'aplicado_a_moratorio',
        'fecha_movimiento',
        'fecha_aplicacion',
        'usuario_id',
        'es_automatico',
        'datos_adicionales'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'cuenta_financiamiento_id' => 'required|integer',
        'tipo_movimiento' => 'required|in_list[cargo,abono,ajuste,refactorizacion,apertura,liquidacion]',
        'concepto' => 'required|string',
        'usuario_id' => 'required|integer'
    ];

    /**
     * Obtener movimientos por cuenta
     */
    public function getMovimientosPorCuenta(int $cuentaId, int $limit = 50): array
    {
        return $this->where('cuenta_financiamiento_id', $cuentaId)
                   ->orderBy('fecha_movimiento', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
}