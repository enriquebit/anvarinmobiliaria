<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Entity ConceptoPago
 * 
 * Define los tipos de conceptos de pago inmobiliario
 * y sus reglas de negocio asociadas
 */
class ConceptoPago extends Entity
{
    // Tipos de concepto de pago
    const APARTADO = 'apartado';                           // Anticipo para reservar
    const LIQUIDACION_ENGANCHE = 'liquidacion_enganche';   // Convierte apartados
    const MENSUALIDAD = 'mensualidad';                     // Pago programado
    const ABONO_CAPITAL = 'abono_capital';                 // Pago anticipado
    const INTERES_MORATORIO = 'interes_moratorio';         // Penalización
    const LIQUIDACION_TOTAL = 'liquidacion_total';         // Finiquito
    
    // Estados de pago
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APLICADO = 'aplicado';
    const ESTADO_VENCIDO = 'vencido';
    const ESTADO_CANCELADO = 'cancelado';
    
    protected $datamap = [];
    protected $dates   = ['fecha_pago', 'fecha_vencimiento', 'created_at', 'updated_at'];
    protected $casts   = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'cuenta_financiamiento_id' => '?integer',
        'concepto' => 'string',
        'monto' => 'float',
        'monto_aplicado' => 'float',
        'saldo_pendiente' => 'float',
        'estado' => 'string',
        'aplicado_a_capital' => 'float',
        'aplicado_a_interes' => 'float',
        'aplicado_a_moratorio' => 'float',
        'refactoriza_tabla' => 'boolean'
    ];

    /**
     * Obtener descripción del concepto
     */
    public function getDescripcionConcepto(): string
    {
        $conceptos = [
            self::APARTADO => 'Apartado',
            self::LIQUIDACION_ENGANCHE => 'Liquidación de Enganche',
            self::MENSUALIDAD => 'Mensualidad',
            self::ABONO_CAPITAL => 'Abono a Capital',
            self::INTERES_MORATORIO => 'Interés Moratorio',
            self::LIQUIDACION_TOTAL => 'Liquidación Total'
        ];
        
        return $conceptos[$this->concepto] ?? 'Concepto no definido';
    }

    /**
     * Validar si el pago está vencido
     */
    public function estaVencido(): bool
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }
        
        return $this->estado === self::ESTADO_PENDIENTE && 
               strtotime($this->fecha_vencimiento) < strtotime('today');
    }

    /**
     * Calcular días de atraso
     */
    public function getDiasAtraso(): int
    {
        if (!$this->estaVencido()) {
            return 0;
        }
        
        $fecha_vencimiento = new \DateTime($this->fecha_vencimiento);
        $hoy = new \DateTime();
        
        return $fecha_vencimiento->diff($hoy)->days;
    }

    /**
     * Verificar si es un pago que afecta capital
     */
    public function afectaCapital(): bool
    {
        return in_array($this->concepto, [
            self::ABONO_CAPITAL,
            self::LIQUIDACION_TOTAL,
            self::MENSUALIDAD
        ]);
    }

    /**
     * Verificar si requiere refactorización
     */
    public function requiereRefactorizacion(): bool
    {
        return $this->concepto === self::ABONO_CAPITAL && 
               $this->aplicado_a_capital > 0;
    }

    /**
     * Obtener porcentaje pagado
     */
    public function getPorcentajePagado(): float
    {
        if ($this->monto == 0) {
            return 0;
        }
        
        return round(($this->monto_aplicado / $this->monto) * 100, 2);
    }

    /**
     * Validar monto mínimo según concepto
     */
    public function validarMontoMinimo(): array
    {
        $montos_minimos = [
            self::APARTADO => 5000.00,
            self::LIQUIDACION_ENGANCHE => 10000.00,
            self::MENSUALIDAD => 1000.00,
            self::ABONO_CAPITAL => 5000.00,
            self::INTERES_MORATORIO => 0.01,
            self::LIQUIDACION_TOTAL => 0.01
        ];
        
        $minimo = $montos_minimos[$this->concepto] ?? 0;
        
        return [
            'valido' => $this->monto >= $minimo,
            'monto_minimo' => $minimo,
            'mensaje' => $this->monto < $minimo ? 
                "El monto mínimo para {$this->getDescripcionConcepto()} es $" . number_format($minimo, 2) : 
                ''
        ];
    }

    /**
     * Distribuir pago entre capital, interés y moratorios
     */
    public function distribuirPago(float $interes_pendiente = 0, float $moratorio_pendiente = 0): array
    {
        $monto_disponible = $this->monto;
        $distribucion = [
            'moratorio' => 0,
            'interes' => 0,
            'capital' => 0
        ];
        
        // Primero pagar moratorios
        if ($moratorio_pendiente > 0) {
            $distribucion['moratorio'] = min($monto_disponible, $moratorio_pendiente);
            $monto_disponible -= $distribucion['moratorio'];
        }
        
        // Luego intereses
        if ($monto_disponible > 0 && $interes_pendiente > 0) {
            $distribucion['interes'] = min($monto_disponible, $interes_pendiente);
            $monto_disponible -= $distribucion['interes'];
        }
        
        // Finalmente capital
        if ($monto_disponible > 0) {
            $distribucion['capital'] = $monto_disponible;
        }
        
        return $distribucion;
    }

    /**
     * Aplicar pago
     */
    public function aplicarPago(array $distribucion): void
    {
        $this->aplicado_a_moratorio = $distribucion['moratorio'] ?? 0;
        $this->aplicado_a_interes = $distribucion['interes'] ?? 0;
        $this->aplicado_a_capital = $distribucion['capital'] ?? 0;
        $this->monto_aplicado = array_sum($distribucion);
        $this->saldo_pendiente = $this->monto - $this->monto_aplicado;
        $this->estado = $this->saldo_pendiente <= 0 ? self::ESTADO_APLICADO : self::ESTADO_PENDIENTE;
        $this->fecha_pago = date('Y-m-d');
        
        // Marcar si requiere refactorización
        if ($this->concepto === self::ABONO_CAPITAL && $this->aplicado_a_capital > 0) {
            $this->refactoriza_tabla = true;
        }
    }
}