<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Entity CuentaFinanciamiento
 * 
 * Gestiona la cuenta única de financiamiento por inmueble
 * con tracking completo de saldos y movimientos
 */
class CuentaFinanciamiento extends Entity
{
    // Estados de cuenta
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_LIQUIDADA = 'liquidada';
    const ESTADO_SUSPENDIDA = 'suspendida';
    const ESTADO_JURIDICO = 'juridico';
    const ESTADO_CANCELADA = 'cancelada';
    
    // Tipos de tasa
    const TASA_FIJA = 'fija';
    const TASA_VARIABLE = 'variable';
    
    protected $datamap = [];
    protected $dates   = ['fecha_apertura', 'fecha_primer_pago', 'fecha_ultimo_pago', 'fecha_liquidacion', 'created_at', 'updated_at'];
    protected $casts   = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'cliente_id' => 'integer',
        'lote_id' => 'integer',
        'numero_cuenta' => 'string',
        'capital_inicial' => 'float',
        'saldo_capital' => 'float',
        'saldo_interes' => 'float',
        'saldo_moratorio' => 'float',
        'saldo_total' => 'float',
        'monto_mensualidad' => 'float',
        'tasa_interes_anual' => 'float',
        'tasa_moratoria_mensual' => 'float',
        'tipo_tasa' => 'string',
        'plazo_meses' => 'integer',
        'meses_transcurridos' => 'integer',
        'meses_restantes' => 'integer',
        'pagos_realizados' => 'integer',
        'pagos_vencidos' => 'integer',
        'total_abonos_capital' => 'float',
        'total_intereses_pagados' => 'float',
        'total_moratorios_pagados' => 'float',
        'refactorizaciones_aplicadas' => 'integer',
        'estado' => 'string'
    ];

    /**
     * Generar número de cuenta único
     */
    public function generarNumeroCuenta(): string
    {
        if (!$this->numero_cuenta) {
            $this->numero_cuenta = 'CTA-' . 
                str_pad($this->cliente_id, 6, '0', STR_PAD_LEFT) . '-' .
                str_pad($this->lote_id, 6, '0', STR_PAD_LEFT) . '-' .
                date('Ymd');
        }
        
        return $this->numero_cuenta;
    }

    /**
     * Calcular saldo total actual
     */
    public function calcularSaldoTotal(): float
    {
        $this->saldo_total = $this->saldo_capital + $this->saldo_interes + $this->saldo_moratorio;
        return $this->saldo_total;
    }

    /**
     * Aplicar pago a mensualidad
     */
    public function aplicarPagoMensualidad(float $monto): array
    {
        $distribucion = $this->distribuirPago($monto);
        
        // Actualizar saldos
        $this->saldo_moratorio -= $distribucion['moratorio'];
        $this->saldo_interes -= $distribucion['interes'];
        $this->saldo_capital -= $distribucion['capital'];
        
        // Actualizar totales pagados
        $this->total_moratorios_pagados += $distribucion['moratorio'];
        $this->total_intereses_pagados += $distribucion['interes'];
        
        // Actualizar contadores
        $this->pagos_realizados++;
        $this->fecha_ultimo_pago = date('Y-m-d');
        
        // Recalcular
        $this->calcularSaldoTotal();
        $this->actualizarMesesRestantes();
        
        return [
            'success' => true,
            'distribucion' => $distribucion,
            'saldo_total' => $this->saldo_total,
            'mensaje' => 'Pago aplicado exitosamente'
        ];
    }

    /**
     * Aplicar abono a capital
     */
    public function aplicarAbonoCapital(float $monto): array
    {
        if ($monto <= 0) {
            return [
                'success' => false,
                'mensaje' => 'El monto debe ser mayor a 0'
            ];
        }
        
        // Primero pagar moratorios e intereses si hay
        $distribucion = $this->distribuirPago($monto);
        
        // Actualizar saldos
        $this->saldo_moratorio -= $distribucion['moratorio'];
        $this->saldo_interes -= $distribucion['interes'];
        $this->saldo_capital -= $distribucion['capital'];
        
        // Actualizar totales
        $this->total_abonos_capital += $distribucion['capital'];
        $this->total_moratorios_pagados += $distribucion['moratorio'];
        $this->total_intereses_pagados += $distribucion['interes'];
        
        // Marcar para refactorización si se abonó a capital
        $requiere_refactorizacion = $distribucion['capital'] > 0;
        
        // Recalcular
        $this->calcularSaldoTotal();
        
        return [
            'success' => true,
            'distribucion' => $distribucion,
            'saldo_capital_nuevo' => $this->saldo_capital,
            'saldo_total_nuevo' => $this->saldo_total,
            'requiere_refactorizacion' => $requiere_refactorizacion,
            'ahorro_estimado' => $this->calcularAhorroIntereses($distribucion['capital']),
            'mensaje' => $requiere_refactorizacion ? 
                'Abono aplicado. Se requiere refactorización de la tabla.' : 
                'Abono aplicado a moratorios e intereses.'
        ];
    }

    /**
     * Distribuir pago entre moratorio, interés y capital
     */
    private function distribuirPago(float $monto): array
    {
        $monto_disponible = $monto;
        $distribucion = [
            'moratorio' => 0,
            'interes' => 0,
            'capital' => 0
        ];
        
        // Primero moratorios
        if ($this->saldo_moratorio > 0 && $monto_disponible > 0) {
            $distribucion['moratorio'] = min($monto_disponible, $this->saldo_moratorio);
            $monto_disponible -= $distribucion['moratorio'];
        }
        
        // Luego intereses
        if ($this->saldo_interes > 0 && $monto_disponible > 0) {
            $distribucion['interes'] = min($monto_disponible, $this->saldo_interes);
            $monto_disponible -= $distribucion['interes'];
        }
        
        // Finalmente capital
        if ($monto_disponible > 0) {
            $distribucion['capital'] = min($monto_disponible, $this->saldo_capital);
        }
        
        return $distribucion;
    }

    /**
     * Calcular ahorro estimado en intereses por abono a capital
     */
    public function calcularAhorroIntereses(float $abono_capital): float
    {
        if ($abono_capital <= 0 || $this->meses_restantes <= 0) {
            return 0;
        }
        
        // Cálculo simplificado de ahorro en intereses
        $tasa_mensual = $this->tasa_interes_anual / 12 / 100;
        $ahorro = $abono_capital * $tasa_mensual * $this->meses_restantes;
        
        return round($ahorro, 2);
    }

    /**
     * Aplicar refactorización
     */
    public function aplicarRefactorizacion(float $nueva_mensualidad, int $nuevo_plazo_meses): void
    {
        $this->monto_mensualidad = $nueva_mensualidad;
        $this->meses_restantes = $nuevo_plazo_meses;
        $this->refactorizaciones_aplicadas++;
    }

    /**
     * Calcular moratorios pendientes
     */
    public function calcularMoratorios(int $dias_atraso): float
    {
        if ($dias_atraso <= 0) {
            return 0;
        }
        
        // Moratorio sobre la mensualidad
        $moratorio_diario = $this->monto_mensualidad * ($this->tasa_moratoria_mensual / 100 / 30);
        $moratorio_total = $moratorio_diario * $dias_atraso;
        
        return round($moratorio_total, 2);
    }

    /**
     * Actualizar meses restantes
     */
    private function actualizarMesesRestantes(): void
    {
        $this->meses_transcurridos = $this->pagos_realizados;
        $this->meses_restantes = max(0, $this->plazo_meses - $this->meses_transcurridos);
    }

    /**
     * Verificar si está al corriente
     */
    public function estaAlCorriente(): bool
    {
        return $this->pagos_vencidos === 0 && 
               $this->saldo_moratorio <= 0;
    }

    /**
     * Verificar si puede liquidarse
     */
    public function puedeLiquidarse(): bool
    {
        return $this->estado === self::ESTADO_ACTIVA && 
               $this->saldo_total > 0;
    }

    /**
     * Liquidar cuenta
     */
    public function liquidar(): array
    {
        if (!$this->puedeLiquidarse()) {
            return [
                'success' => false,
                'mensaje' => 'La cuenta no puede liquidarse en su estado actual'
            ];
        }
        
        $saldo_liquidacion = $this->saldo_total;
        
        // Actualizar estado
        $this->estado = self::ESTADO_LIQUIDADA;
        $this->fecha_liquidacion = date('Y-m-d');
        $this->saldo_capital = 0;
        $this->saldo_interes = 0;
        $this->saldo_moratorio = 0;
        $this->saldo_total = 0;
        $this->meses_restantes = 0;
        
        return [
            'success' => true,
            'saldo_liquidado' => $saldo_liquidacion,
            'ahorro_intereses' => $this->calcularAhorroIntereses($this->saldo_capital),
            'mensaje' => 'Cuenta liquidada exitosamente'
        ];
    }

    /**
     * Suspender cuenta
     */
    public function suspender(string $motivo): void
    {
        $this->estado = self::ESTADO_SUSPENDIDA;
        $this->observaciones = $motivo;
    }

    /**
     * Reactivar cuenta
     */
    public function reactivar(): array
    {
        if ($this->estado !== self::ESTADO_SUSPENDIDA) {
            return [
                'success' => false,
                'mensaje' => 'Solo se pueden reactivar cuentas suspendidas'
            ];
        }
        
        $this->estado = self::ESTADO_ACTIVA;
        
        return [
            'success' => true,
            'mensaje' => 'Cuenta reactivada exitosamente'
        ];
    }

    /**
     * Obtener resumen de la cuenta
     */
    public function getResumen(): array
    {
        return [
            'numero_cuenta' => $this->numero_cuenta,
            'estado' => $this->estado,
            'capital_inicial' => $this->capital_inicial,
            'saldo_capital' => $this->saldo_capital,
            'saldo_interes' => $this->saldo_interes,
            'saldo_moratorio' => $this->saldo_moratorio,
            'saldo_total' => $this->saldo_total,
            'monto_mensualidad' => $this->monto_mensualidad,
            'plazo_original' => $this->plazo_meses,
            'meses_pagados' => $this->pagos_realizados,
            'meses_restantes' => $this->meses_restantes,
            'pagos_vencidos' => $this->pagos_vencidos,
            'al_corriente' => $this->estaAlCorriente(),
            'total_pagado' => $this->getTotalPagado(),
            'refactorizaciones' => $this->refactorizaciones_aplicadas
        ];
    }

    /**
     * Calcular total pagado
     */
    public function getTotalPagado(): float
    {
        $capital_pagado = $this->capital_inicial - $this->saldo_capital;
        return $capital_pagado + $this->total_intereses_pagados + $this->total_moratorios_pagados;
    }
}