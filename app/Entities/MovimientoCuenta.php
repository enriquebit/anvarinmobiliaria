<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Entity MovimientoCuenta
 * 
 * Bitácora completa de todos los movimientos realizados
 * en las cuentas de financiamiento
 */
class MovimientoCuenta extends Entity
{
    // Tipos de movimiento
    const TIPO_CARGO = 'cargo';
    const TIPO_ABONO = 'abono';
    const TIPO_AJUSTE = 'ajuste';
    const TIPO_REFACTORIZACION = 'refactorizacion';
    const TIPO_APERTURA = 'apertura';
    const TIPO_LIQUIDACION = 'liquidacion';
    
    // Conceptos de movimiento
    const CONCEPTO_MENSUALIDAD = 'mensualidad';
    const CONCEPTO_ABONO_CAPITAL = 'abono_capital';
    const CONCEPTO_MORATORIO = 'moratorio';
    const CONCEPTO_INTERES = 'interes';
    const CONCEPTO_AJUSTE_SALDO = 'ajuste_saldo';
    const CONCEPTO_REFACTORIZACION_TABLA = 'refactorizacion_tabla';
    const CONCEPTO_APERTURA_CUENTA = 'apertura_cuenta';
    const CONCEPTO_LIQUIDACION_TOTAL = 'liquidacion_total';
    
    protected $datamap = [];
    protected $dates   = ['fecha_movimiento', 'fecha_aplicacion', 'created_at', 'updated_at'];
    protected $casts   = [
        'id' => 'integer',
        'cuenta_financiamiento_id' => 'integer',
        'pago_id' => '?integer',
        'refactorizacion_id' => '?integer',
        'tipo_movimiento' => 'string',
        'concepto' => 'string',
        'descripcion' => 'string',
        'monto_cargo' => 'float',
        'monto_abono' => 'float',
        'saldo_anterior_capital' => 'float',
        'saldo_anterior_interes' => 'float',
        'saldo_anterior_moratorio' => 'float',
        'saldo_anterior_total' => 'float',
        'saldo_nuevo_capital' => 'float',
        'saldo_nuevo_interes' => 'float',
        'saldo_nuevo_moratorio' => 'float',
        'saldo_nuevo_total' => 'float',
        'aplicado_a_capital' => 'float',
        'aplicado_a_interes' => 'float',
        'aplicado_a_moratorio' => 'float',
        'usuario_id' => 'integer',
        'es_automatico' => 'boolean',
        'datos_adicionales' => 'array'
    ];

    /**
     * Obtener monto del movimiento
     */
    public function getMonto(): float
    {
        return $this->tipo_movimiento === self::TIPO_CARGO ? 
            $this->monto_cargo : 
            $this->monto_abono;
    }

    /**
     * Obtener descripción del tipo de movimiento
     */
    public function getTipoDescripcion(): string
    {
        $tipos = [
            self::TIPO_CARGO => 'Cargo',
            self::TIPO_ABONO => 'Abono',
            self::TIPO_AJUSTE => 'Ajuste',
            self::TIPO_REFACTORIZACION => 'Refactorización',
            self::TIPO_APERTURA => 'Apertura',
            self::TIPO_LIQUIDACION => 'Liquidación'
        ];
        
        return $tipos[$this->tipo_movimiento] ?? 'Otro';
    }

    /**
     * Obtener descripción del concepto
     */
    public function getConceptoDescripcion(): string
    {
        $conceptos = [
            self::CONCEPTO_MENSUALIDAD => 'Pago de Mensualidad',
            self::CONCEPTO_ABONO_CAPITAL => 'Abono a Capital',
            self::CONCEPTO_MORATORIO => 'Interés Moratorio',
            self::CONCEPTO_INTERES => 'Interés Ordinario',
            self::CONCEPTO_AJUSTE_SALDO => 'Ajuste de Saldo',
            self::CONCEPTO_REFACTORIZACION_TABLA => 'Refactorización de Tabla',
            self::CONCEPTO_APERTURA_CUENTA => 'Apertura de Cuenta',
            self::CONCEPTO_LIQUIDACION_TOTAL => 'Liquidación Total'
        ];
        
        return $conceptos[$this->concepto] ?? 'Otro Concepto';
    }

    /**
     * Calcular diferencia en saldo total
     */
    public function getDiferenciaSaldo(): float
    {
        return $this->saldo_nuevo_total - $this->saldo_anterior_total;
    }

    /**
     * Calcular diferencia en capital
     */
    public function getDiferenciaCapital(): float
    {
        return $this->saldo_nuevo_capital - $this->saldo_anterior_capital;
    }

    /**
     * Verificar si es un movimiento de pago
     */
    public function esPago(): bool
    {
        return in_array($this->concepto, [
            self::CONCEPTO_MENSUALIDAD,
            self::CONCEPTO_ABONO_CAPITAL,
            self::CONCEPTO_LIQUIDACION_TOTAL
        ]);
    }

    /**
     * Verificar si afectó el capital
     */
    public function afectoCapital(): bool
    {
        return $this->aplicado_a_capital > 0;
    }

    /**
     * Obtener distribución del pago
     */
    public function getDistribucionPago(): array
    {
        if (!$this->esPago()) {
            return [];
        }
        
        return [
            'capital' => $this->aplicado_a_capital,
            'interes' => $this->aplicado_a_interes,
            'moratorio' => $this->aplicado_a_moratorio,
            'total' => $this->aplicado_a_capital + $this->aplicado_a_interes + $this->aplicado_a_moratorio
        ];
    }

    /**
     * Crear registro de apertura
     */
    public static function crearApertura(array $datos): self
    {
        $movimiento = new self();
        
        $movimiento->fill([
            'cuenta_financiamiento_id' => $datos['cuenta_id'],
            'tipo_movimiento' => self::TIPO_APERTURA,
            'concepto' => self::CONCEPTO_APERTURA_CUENTA,
            'descripcion' => 'Apertura de cuenta de financiamiento',
            'fecha_movimiento' => date('Y-m-d'),
            'fecha_aplicacion' => date('Y-m-d'),
            'monto_cargo' => $datos['capital_inicial'],
            'monto_abono' => 0,
            'saldo_anterior_capital' => 0,
            'saldo_anterior_interes' => 0,
            'saldo_anterior_moratorio' => 0,
            'saldo_anterior_total' => 0,
            'saldo_nuevo_capital' => $datos['capital_inicial'],
            'saldo_nuevo_interes' => 0,
            'saldo_nuevo_moratorio' => 0,
            'saldo_nuevo_total' => $datos['capital_inicial'],
            'usuario_id' => $datos['usuario_id'],
            'es_automatico' => false,
            'datos_adicionales' => [
                'plazo_meses' => $datos['plazo_meses'],
                'tasa_interes' => $datos['tasa_interes'],
                'monto_mensualidad' => $datos['monto_mensualidad']
            ]
        ]);
        
        return $movimiento;
    }

    /**
     * Crear registro de pago
     */
    public static function crearPago(array $datos): self
    {
        $movimiento = new self();
        
        $movimiento->fill([
            'cuenta_financiamiento_id' => $datos['cuenta_id'],
            'pago_id' => $datos['pago_id'] ?? null,
            'tipo_movimiento' => self::TIPO_ABONO,
            'concepto' => $datos['concepto'],
            'descripcion' => $datos['descripcion'] ?? 'Pago aplicado',
            'fecha_movimiento' => date('Y-m-d'),
            'fecha_aplicacion' => $datos['fecha_aplicacion'] ?? date('Y-m-d'),
            'monto_cargo' => 0,
            'monto_abono' => $datos['monto'],
            'saldo_anterior_capital' => $datos['saldo_anterior']['capital'],
            'saldo_anterior_interes' => $datos['saldo_anterior']['interes'],
            'saldo_anterior_moratorio' => $datos['saldo_anterior']['moratorio'],
            'saldo_anterior_total' => $datos['saldo_anterior']['total'],
            'saldo_nuevo_capital' => $datos['saldo_nuevo']['capital'],
            'saldo_nuevo_interes' => $datos['saldo_nuevo']['interes'],
            'saldo_nuevo_moratorio' => $datos['saldo_nuevo']['moratorio'],
            'saldo_nuevo_total' => $datos['saldo_nuevo']['total'],
            'aplicado_a_capital' => $datos['distribucion']['capital'] ?? 0,
            'aplicado_a_interes' => $datos['distribucion']['interes'] ?? 0,
            'aplicado_a_moratorio' => $datos['distribucion']['moratorio'] ?? 0,
            'usuario_id' => $datos['usuario_id'],
            'es_automatico' => $datos['es_automatico'] ?? false
        ]);
        
        return $movimiento;
    }

    /**
     * Crear registro de refactorización
     */
    public static function crearRefactorizacion(array $datos): self
    {
        $movimiento = new self();
        
        $movimiento->fill([
            'cuenta_financiamiento_id' => $datos['cuenta_id'],
            'refactorizacion_id' => $datos['refactorizacion_id'] ?? null,
            'tipo_movimiento' => self::TIPO_REFACTORIZACION,
            'concepto' => self::CONCEPTO_REFACTORIZACION_TABLA,
            'descripcion' => 'Refactorización de tabla de amortización por abono a capital',
            'fecha_movimiento' => date('Y-m-d'),
            'fecha_aplicacion' => date('Y-m-d'),
            'monto_cargo' => 0,
            'monto_abono' => 0,
            'saldo_anterior_capital' => $datos['saldo_anterior']['capital'],
            'saldo_anterior_interes' => $datos['saldo_anterior']['interes'],
            'saldo_anterior_moratorio' => $datos['saldo_anterior']['moratorio'],
            'saldo_anterior_total' => $datos['saldo_anterior']['total'],
            'saldo_nuevo_capital' => $datos['saldo_nuevo']['capital'],
            'saldo_nuevo_interes' => $datos['saldo_nuevo']['interes'],
            'saldo_nuevo_moratorio' => $datos['saldo_nuevo']['moratorio'],
            'saldo_nuevo_total' => $datos['saldo_nuevo']['total'],
            'usuario_id' => $datos['usuario_id'],
            'es_automatico' => false,
            'datos_adicionales' => [
                'nueva_mensualidad' => $datos['nueva_mensualidad'],
                'nuevo_plazo' => $datos['nuevo_plazo'],
                'ahorro_intereses' => $datos['ahorro_intereses'] ?? 0
            ]
        ]);
        
        return $movimiento;
    }

    /**
     * Obtener resumen del movimiento
     */
    public function getResumen(): array
    {
        return [
            'fecha' => $this->fecha_movimiento,
            'tipo' => $this->getTipoDescripcion(),
            'concepto' => $this->getConceptoDescripcion(),
            'descripcion' => $this->descripcion,
            'monto' => $this->getMonto(),
            'saldo_anterior' => $this->saldo_anterior_total,
            'saldo_nuevo' => $this->saldo_nuevo_total,
            'diferencia' => $this->getDiferenciaSaldo(),
            'distribucion' => $this->getDistribucionPago(),
            'es_automatico' => $this->es_automatico
        ];
    }
}