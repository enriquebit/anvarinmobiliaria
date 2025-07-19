<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Entity LiquidacionEnganche
 * 
 * Gestiona la liquidación de enganche desde anticipos acumulados
 * y la apertura de cuenta de financiamiento
 */
class LiquidacionEnganche extends Entity
{
    // Estados de liquidación
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_PARCIAL = 'parcial';
    const ESTADO_CANCELADA = 'cancelada';
    
    // Tipos de origen
    const ORIGEN_ANTICIPOS = 'anticipos';
    const ORIGEN_PAGO_DIRECTO = 'pago_directo';
    const ORIGEN_MIXTO = 'mixto';
    
    protected $datamap = [];
    protected $dates   = ['fecha_liquidacion', 'fecha_apertura_cuenta', 'created_at', 'updated_at'];
    protected $casts   = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'cuenta_financiamiento_id' => '?integer',
        'monto_enganche_requerido' => 'float',
        'monto_anticipos_aplicados' => 'float',
        'monto_pago_directo' => 'float',
        'monto_total_liquidado' => 'float',
        'saldo_pendiente' => 'float',
        'tipo_origen' => 'string',
        'estado' => 'string',
        'cuenta_abierta' => 'boolean',
        'tabla_amortizacion_generada' => 'boolean',
        'anticipos_ids' => 'array',
        'folio_liquidacion' => 'string'
    ];

    /**
     * Calcular monto total liquidado
     */
    public function calcularMontoTotalLiquidado(): float
    {
        return $this->monto_anticipos_aplicados + $this->monto_pago_directo;
    }

    /**
     * Calcular saldo pendiente de enganche
     */
    public function calcularSaldoPendiente(): float
    {
        $total_liquidado = $this->calcularMontoTotalLiquidado();
        $pendiente = $this->monto_enganche_requerido - $total_liquidado;
        return max($pendiente, 0);
    }

    /**
     * Verificar si está completamente liquidado
     */
    public function estaCompletamenteLiquidado(): bool
    {
        return $this->calcularSaldoPendiente() <= 0;
    }

    /**
     * Determinar estado actual
     */
    public function determinarEstado(): string
    {
        if ($this->estado === self::ESTADO_CANCELADA) {
            return self::ESTADO_CANCELADA;
        }
        
        $saldo_pendiente = $this->calcularSaldoPendiente();
        
        if ($saldo_pendiente <= 0) {
            return self::ESTADO_COMPLETADA;
        } elseif ($this->calcularMontoTotalLiquidado() > 0) {
            return self::ESTADO_PARCIAL;
        } else {
            return self::ESTADO_PENDIENTE;
        }
    }

    /**
     * Aplicar anticipos acumulados
     */
    public function aplicarAnticipos(array $anticipos, float $monto_total_anticipos): array
    {
        $this->monto_anticipos_aplicados = $monto_total_anticipos;
        $this->anticipos_ids = array_column($anticipos, 'id');
        $this->tipo_origen = $this->monto_pago_directo > 0 ? self::ORIGEN_MIXTO : self::ORIGEN_ANTICIPOS;
        
        return $this->actualizarEstado();
    }

    /**
     * Aplicar pago directo
     */
    public function aplicarPagoDirecto(float $monto): array
    {
        if ($monto <= 0) {
            return [
                'success' => false,
                'mensaje' => 'El monto debe ser mayor a 0'
            ];
        }
        
        $this->monto_pago_directo += $monto;
        $this->tipo_origen = $this->monto_anticipos_aplicados > 0 ? self::ORIGEN_MIXTO : self::ORIGEN_PAGO_DIRECTO;
        
        return $this->actualizarEstado();
    }

    /**
     * Actualizar estado y montos
     */
    private function actualizarEstado(): array
    {
        $this->monto_total_liquidado = $this->calcularMontoTotalLiquidado();
        $this->saldo_pendiente = $this->calcularSaldoPendiente();
        $this->estado = $this->determinarEstado();
        
        $completado = $this->estaCompletamenteLiquidado();
        
        return [
            'success' => true,
            'monto_total_liquidado' => $this->monto_total_liquidado,
            'saldo_pendiente' => $this->saldo_pendiente,
            'estado' => $this->estado,
            'enganche_completado' => $completado,
            'mensaje' => $completado ? 
                'Enganche liquidado completamente. Listo para apertura de cuenta.' : 
                'Liquidación parcial aplicada. Pendiente: $' . number_format($this->saldo_pendiente, 2)
        ];
    }

    /**
     * Abrir cuenta de financiamiento
     */
    public function abrirCuentaFinanciamiento(int $cuenta_id): array
    {
        if (!$this->estaCompletamenteLiquidado()) {
            return [
                'success' => false,
                'mensaje' => 'No se puede abrir cuenta. Enganche pendiente: $' . number_format($this->saldo_pendiente, 2)
            ];
        }
        
        if ($this->cuenta_abierta) {
            return [
                'success' => false,
                'mensaje' => 'La cuenta de financiamiento ya fue abierta'
            ];
        }
        
        $this->cuenta_financiamiento_id = $cuenta_id;
        $this->cuenta_abierta = true;
        $this->fecha_apertura_cuenta = date('Y-m-d H:i:s');
        
        return [
            'success' => true,
            'cuenta_id' => $cuenta_id,
            'mensaje' => 'Cuenta de financiamiento abierta exitosamente'
        ];
    }

    /**
     * Marcar tabla de amortización como generada
     */
    public function marcarTablaGenerada(): void
    {
        $this->tabla_amortizacion_generada = true;
    }

    /**
     * Generar folio de liquidación
     */
    public function generarFolio(): string
    {
        if (!$this->folio_liquidacion) {
            $this->folio_liquidacion = 'LIQ-' . date('Ymd') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        }
        
        return $this->folio_liquidacion;
    }

    /**
     * Obtener resumen de liquidación
     */
    public function getResumen(): array
    {
        return [
            'folio' => $this->folio_liquidacion,
            'monto_requerido' => $this->monto_enganche_requerido,
            'monto_anticipos' => $this->monto_anticipos_aplicados,
            'monto_pago_directo' => $this->monto_pago_directo,
            'monto_total_liquidado' => $this->monto_total_liquidado,
            'saldo_pendiente' => $this->saldo_pendiente,
            'porcentaje_liquidado' => $this->getPorcentajeLiquidado(),
            'estado' => $this->estado,
            'tipo_origen' => $this->tipo_origen,
            'cuenta_abierta' => $this->cuenta_abierta,
            'tabla_generada' => $this->tabla_amortizacion_generada,
            'fecha_liquidacion' => $this->fecha_liquidacion
        ];
    }

    /**
     * Calcular porcentaje liquidado
     */
    public function getPorcentajeLiquidado(): float
    {
        if ($this->monto_enganche_requerido <= 0) {
            return 0;
        }
        
        $porcentaje = ($this->monto_total_liquidado / $this->monto_enganche_requerido) * 100;
        return min(round($porcentaje, 2), 100);
    }

    /**
     * Validar si puede cancelarse
     */
    public function puedeCancelarse(): bool
    {
        return !$this->cuenta_abierta && 
               $this->estado !== self::ESTADO_CANCELADA;
    }

    /**
     * Cancelar liquidación
     */
    public function cancelar(string $motivo): array
    {
        if (!$this->puedeCancelarse()) {
            return [
                'success' => false,
                'mensaje' => 'No se puede cancelar esta liquidación'
            ];
        }
        
        $this->estado = self::ESTADO_CANCELADA;
        $this->observaciones = $motivo;
        
        return [
            'success' => true,
            'mensaje' => 'Liquidación cancelada exitosamente'
        ];
    }
}