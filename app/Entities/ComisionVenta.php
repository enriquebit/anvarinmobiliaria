<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ComisionVenta extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'fecha_generacion',
        'fecha_ultimo_pago',
        'fecha_cancelacion',
        'created_at',
        'updated_at'
    ];
    protected $casts   = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'vendedor_id' => 'integer',
        'configuracion_comision_id' => '?integer',
        'cancelado_por' => '?integer',
        'base_calculo' => 'float',
        'porcentaje_aplicado' => '?float',
        'monto_comision_total' => 'float',
        'monto_pagado_apartado' => 'float',
        'monto_pagado_enganche' => 'float',
        'monto_por_cobranza' => 'float',
        'monto_pagado_total' => 'float',
        'saldo_pendiente' => 'float'
    ];

    /**
     * Verifica si la comisión está totalmente pagada
     */
    public function estaPagada(): bool
    {
        return $this->estatus === 'pagada' || $this->saldo_pendiente <= 0;
    }

    /**
     * Verifica si tiene pagos parciales
     */
    public function tienePagosParciales(): bool
    {
        return $this->monto_pagado_total > 0 && $this->saldo_pendiente > 0;
    }

    /**
     * Calcula el porcentaje pagado
     */
    public function getPorcentajePagado(): float
    {
        if ($this->monto_comision_total <= 0) {
            return 100;
        }
        
        return min(100, ($this->monto_pagado_total / $this->monto_comision_total) * 100);
    }

    /**
     * Verifica si está pendiente de pago
     */
    public function estaPendiente(): bool
    {
        return $this->estatus === 'pendiente' && $this->saldo_pendiente > 0;
    }

    /**
     * Verifica si puede cancelarse
     */
    public function puedeCancelarse(): bool
    {
        return $this->estatus !== 'cancelada';
    }

    /**
     * Obtiene el monto pendiente por concepto
     */
    public function getMontoPendientePorConcepto(string $concepto): float
    {
        switch ($concepto) {
            case 'apartado':
                $total = $this->monto_comision_total * 0.3; // Ejemplo: 30% al apartar
                return max(0, $total - $this->monto_pagado_apartado);
                
            case 'enganche':
                $total = $this->monto_comision_total * 0.7; // Ejemplo: 70% al enganchar
                return max(0, $total - $this->monto_pagado_enganche);
                
            case 'cobranza':
                return max(0, $this->monto_por_cobranza);
                
            default:
                return 0;
        }
    }

    /**
     * Obtiene el estatus formateado
     */
    public function getEstatusFormateado(): string
    {
        $estatusMap = [
            'pendiente' => 'Pendiente',
            'parcial' => 'Pago Parcial',
            'pagada' => 'Pagada',
            'cancelada' => 'Cancelada'
        ];
        
        return $estatusMap[$this->estatus] ?? $this->estatus;
    }

    /**
     * Obtiene el color del estatus para UI
     */
    public function getEstatusColor(): string
    {
        $colorMap = [
            'pendiente' => 'warning',
            'parcial' => 'info',
            'pagada' => 'success',
            'cancelada' => 'danger'
        ];
        
        return $colorMap[$this->estatus] ?? 'secondary';
    }

    /**
     * Obtiene el tipo de cálculo formateado
     */
    public function getTipoCalculoFormateado(): string
    {
        $tipoMap = [
            'porcentaje' => 'Porcentaje',
            'monto_fijo' => 'Monto Fijo',
            'escalonado' => 'Escalonado'
        ];
        
        return $tipoMap[$this->tipo_calculo] ?? $this->tipo_calculo;
    }

    /**
     * Obtiene descripción del cálculo
     */
    public function getDescripcionCalculo(): string
    {
        switch ($this->tipo_calculo) {
            case 'porcentaje':
                return sprintf("%.2f%% sobre $%s", $this->porcentaje_aplicado, number_format($this->base_calculo, 2));
            case 'monto_fijo':
                return sprintf("Monto fijo: $%s", number_format($this->monto_comision_total, 2));
            case 'escalonado':
                return "Cálculo escalonado según configuración";
            default:
                return "N/A";
        }
    }
}