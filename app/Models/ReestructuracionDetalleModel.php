<?php

namespace App\Models;

use CodeIgniter\Model;

class ReestructuracionDetalleModel extends Model
{
    protected $table            = 'reestructuraciones_detalle';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'reestructuracion_id', 'numero_pago', 'fecha_vencimiento',
        'saldo_inicial', 'capital', 'interes', 'monto_total',
        'saldo_final', 'estatus', 'monto_pagado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'reestructuracion_id' => 'integer',
        'numero_pago' => 'integer',
        'saldo_inicial' => 'float',
        'capital' => 'float',
        'interes' => 'float',
        'monto_total' => 'float',
        'saldo_final' => 'float',
        'monto_pagado' => 'float'
    ];

    protected array $castHandlers = [];

    // Validation
    protected $validationRules = [
        'reestructuracion_id' => 'required|is_natural_no_zero',
        'numero_pago' => 'required|is_natural_no_zero',
        'fecha_vencimiento' => 'required|valid_date',
        'monto_total' => 'required|decimal|greater_than[0]',
        'capital' => 'required|decimal|greater_than_equal_to[0]',
        'interes' => 'required|decimal|greater_than_equal_to[0]'
    ];

    protected $validationMessages = [
        'reestructuracion_id' => [
            'required' => 'La reestructuración es requerida',
            'is_natural_no_zero' => 'Debe ser un ID válido'
        ],
        'numero_pago' => [
            'required' => 'El número de pago es requerido',
            'is_natural_no_zero' => 'Debe ser un número positivo'
        ],
        'fecha_vencimiento' => [
            'required' => 'La fecha de vencimiento es requerida',
            'valid_date' => 'Debe ser una fecha válida'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // ==========================================
    // MÉTODOS BUSINESS LOGIC
    // ==========================================

    /**
     * Obtiene la tabla de amortización de una reestructuración
     */
    public function getTablaAmortizacion(int $reestructuracionId): array
    {
        return $this->where('reestructuracion_id', $reestructuracionId)
                   ->orderBy('numero_pago', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene mensualidades pendientes de una reestructuración
     */
    public function getMensualidadesPendientes(int $reestructuracionId): array
    {
        return $this->where('reestructuracion_id', $reestructuracionId)
                   ->whereNotIn('estatus', ['pagada'])
                   ->orderBy('fecha_vencimiento', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene mensualidades vencidas de una reestructuración
     */
    public function getMensualidadesVencidas(int $reestructuracionId): array
    {
        $hoy = date('Y-m-d');
        
        return $this->where('reestructuracion_id', $reestructuracionId)
                   ->where('fecha_vencimiento <', $hoy)
                   ->where('estatus', 'pendiente')
                   ->orderBy('fecha_vencimiento', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene la próxima mensualidad a vencer
     */
    public function getProximaMensualidad(int $reestructuracionId): ?object
    {
        return $this->where('reestructuracion_id', $reestructuracionId)
                   ->where('estatus', 'pendiente')
                   ->orderBy('fecha_vencimiento', 'ASC')
                   ->first();
    }

    /**
     * Calcula el progreso de pagos de una reestructuración
     */
    public function getProgresoReestructuracion(int $reestructuracionId): array
    {
        $totalMensualidades = $this->where('reestructuracion_id', $reestructuracionId)
                                  ->countAllResults();
        
        $pagadas = $this->where('reestructuracion_id', $reestructuracionId)
                       ->where('estatus', 'pagada')
                       ->countAllResults();
        
        $vencidas = $this->where('reestructuracion_id', $reestructuracionId)
                        ->where('estatus', 'vencida')
                        ->countAllResults();
        
        $pendientes = $this->where('reestructuracion_id', $reestructuracionId)
                          ->where('estatus', 'pendiente')
                          ->countAllResults();

        // Calcular montos
        $montos = $this->select('
            SUM(monto_total) as total_programado,
            SUM(monto_pagado) as total_pagado,
            SUM(CASE WHEN estatus != "pagada" THEN monto_total - monto_pagado ELSE 0 END) as saldo_pendiente
        ')
        ->where('reestructuracion_id', $reestructuracionId)
        ->first();

        $porcentajeLiquidacion = $montos->total_programado > 0 
            ? ($montos->total_pagado / $montos->total_programado) * 100 
            : 0;

        return [
            'total_mensualidades' => $totalMensualidades,
            'mensualidades_pagadas' => $pagadas,
            'mensualidades_vencidas' => $vencidas,
            'mensualidades_pendientes' => $pendientes,
            'total_programado' => $montos->total_programado,
            'total_pagado' => $montos->total_pagado,
            'saldo_pendiente' => $montos->saldo_pendiente,
            'porcentaje_liquidacion' => round($porcentajeLiquidacion, 2)
        ];
    }

    /**
     * Aplica un pago a una mensualidad específica
     */
    public function aplicarPago(int $detalleId, float $montoPago, string $metodoPago = 'efectivo'): array
    {
        $mensualidad = $this->find($detalleId);
        
        if (!$mensualidad) {
            return ['success' => false, 'error' => 'Mensualidad no encontrada'];
        }

        if ($mensualidad->estatus === 'pagada') {
            return ['success' => false, 'error' => 'La mensualidad ya está pagada'];
        }

        $saldoPendiente = $mensualidad->monto_total - $mensualidad->monto_pagado;
        
        if ($montoPago <= 0) {
            return ['success' => false, 'error' => 'El monto debe ser mayor a cero'];
        }

        $nuevoMontoPagado = $mensualidad->monto_pagado + $montoPago;
        $nuevoEstatus = $mensualidad->estatus;
        $sobrante = 0;

        if ($nuevoMontoPagado >= $mensualidad->monto_total) {
            $nuevoEstatus = 'pagada';
            $sobrante = $nuevoMontoPagado - $mensualidad->monto_total;
            $nuevoMontoPagado = $mensualidad->monto_total;
        } else {
            $nuevoEstatus = 'parcial';
        }

        // Actualizar la mensualidad
        $updated = $this->update($detalleId, [
            'monto_pagado' => $nuevoMontoPagado,
            'estatus' => $nuevoEstatus
        ]);

        if (!$updated) {
            return ['success' => false, 'error' => 'Error al actualizar la mensualidad'];
        }

        return [
            'success' => true,
            'monto_aplicado' => $montoPago - $sobrante,
            'sobrante' => $sobrante,
            'estatus_actualizado' => $nuevoEstatus,
            'saldo_restante' => $mensualidad->monto_total - $nuevoMontoPagado
        ];
    }

    /**
     * Genera tabla de amortización para una reestructuración
     */
    public function generarTablaAmortizacion(int $reestructuracionId, float $capital, float $tasaAnual, int $plazoMeses, string $fechaInicio): bool
    {
        // Limpiar tabla existente
        $this->where('reestructuracion_id', $reestructuracionId)->delete();

        $tasaMensual = $tasaAnual / 100 / 12;
        $pagoMensual = $capital * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / (pow(1 + $tasaMensual, $plazoMeses) - 1);
        
        $saldoInicial = $capital;
        $fechaVencimiento = new \DateTime($fechaInicio);
        
        $pagosData = [];
        
        for ($i = 1; $i <= $plazoMeses; $i++) {
            $interes = $saldoInicial * $tasaMensual;
            $capitalPago = $pagoMensual - $interes;
            $saldoFinal = $saldoInicial - $capitalPago;
            
            // Ajustar último pago
            if ($i === $plazoMeses) {
                $capitalPago += $saldoFinal;
                $pagoMensual = $capitalPago + $interes;
                $saldoFinal = 0;
            }
            
            $pagosData[] = [
                'reestructuracion_id' => $reestructuracionId,
                'numero_pago' => $i,
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'saldo_inicial' => round($saldoInicial, 2),
                'capital' => round($capitalPago, 2),
                'interes' => round($interes, 2),
                'monto_total' => round($pagoMensual, 2),
                'saldo_final' => round($saldoFinal, 2),
                'estatus' => 'pendiente',
                'monto_pagado' => 0.00,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $saldoInicial = $saldoFinal;
            $fechaVencimiento->modify('+1 month');
        }
        
        return $this->insertBatch($pagosData);
    }

    /**
     * Actualiza estados de mensualidades vencidas
     */
    public function actualizarEstadosVencidas(): int
    {
        $hoy = date('Y-m-d');
        
        return $this->where('fecha_vencimiento <', $hoy)
                   ->where('estatus', 'pendiente')
                   ->update(null, ['estatus' => 'vencida']);
    }
}