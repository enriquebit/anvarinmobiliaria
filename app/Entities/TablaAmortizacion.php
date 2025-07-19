<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TablaAmortizacion extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'fecha_vencimiento',
        'fecha_ultimo_pago',
        'fecha_inicio_atraso',
        'created_at',
        'updated_at'
    ];
    protected $casts   = [
        'id' => 'integer',
        'plan_financiamiento_id' => 'integer',
        'numero_pago' => 'integer',
        'saldo_inicial' => 'float',
        'capital' => 'float',
        'interes' => 'float',
        'monto_total' => 'float',
        'saldo_final' => 'float',
        'monto_pagado' => 'float',
        'saldo_pendiente' => 'float',
        'numero_pagos_aplicados' => 'integer',
        'dias_atraso' => 'integer',
        'interes_moratorio' => 'float'
    ];

    /**
     * Verifica si el pago está vencido
     */
    public function estaVencido(): bool
    {
        if ($this->estatus !== 'pendiente') {
            return false;
        }
        
        return strtotime($this->fecha_vencimiento) < strtotime('today');
    }

    /**
     * Calcula los días de atraso actual
     */
    public function calcularDiasAtraso(): int
    {
        if (!$this->estaVencido()) {
            return 0;
        }
        
        $hoy = strtotime('today');
        $vencimiento = strtotime($this->fecha_vencimiento);
        
        return max(0, (int)(($hoy - $vencimiento) / 86400));
    }

    /**
     * Verifica si está totalmente pagado
     */
    public function estaPagado(): bool
    {
        return $this->estatus === 'pagada' || $this->saldo_pendiente <= 0;
    }

    /**
     * Verifica si tiene pago parcial
     */
    public function tienePagoParcial(): bool
    {
        return $this->monto_pagado > 0 && $this->saldo_pendiente > 0;
    }

    /**
     * Calcula el porcentaje pagado
     */
    public function getPorcentajePagado(): float
    {
        if ($this->monto_total <= 0) {
            return 100;
        }
        
        return min(100, ($this->monto_pagado / $this->monto_total) * 100);
    }

    /**
     * Obtiene el total con moratorios
     */
    public function getTotalConMoratorios(): float
    {
        return $this->monto_total + $this->interes_moratorio;
    }

    /**
     * Obtiene el saldo total pendiente incluyendo moratorios
     */
    public function getSaldoTotalPendiente(): float
    {
        return $this->saldo_pendiente + $this->interes_moratorio;
    }

    /**
     * Verifica si es pago para el vendedor
     */
    public function esParaVendedor(): bool
    {
        return $this->beneficiario === 'vendedor';
    }

    /**
     * Obtiene el estatus formateado
     */
    public function getEstatusFormateado(): string
    {
        $estatusMap = [
            'pendiente' => 'Pendiente',
            'pagada' => 'Pagada',
            'parcial' => 'Pago Parcial',
            'vencida' => 'Vencida',
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
            'pagada' => 'success',
            'parcial' => 'info',
            'vencida' => 'danger',
            'cancelada' => 'secondary'
        ];
        
        return $colorMap[$this->estatus] ?? 'secondary';
    }

    // ==========================================
    // NUEVOS MÉTODOS ENTITY-FIRST AVANZADOS
    // ==========================================

    /**
     * Actualiza el estado automáticamente basado en la lógica de negocio
     */
    public function actualizarEstadoAutomatico(): self
    {
        $saldoPendiente = $this->getSaldoTotalPendiente();
        $diasAtraso = $this->calcularDiasAtraso();

        // Actualizar estado basado en pagos y atrasos
        if ($saldoPendiente <= 0.01) {
            $this->estatus = 'pagada';
            $this->dias_atraso = 0;
            $this->fecha_inicio_atraso = null;
        } elseif ($diasAtraso > 0) {
            $this->estatus = 'vencida';
            $this->dias_atraso = $diasAtraso;
            
            // Establecer fecha inicio atraso si no existe
            if (empty($this->fecha_inicio_atraso)) {
                $fechaVencimiento = new \DateTime($this->fecha_vencimiento);
                $fechaVencimiento->modify('+1 day');
                $this->fecha_inicio_atraso = $fechaVencimiento->format('Y-m-d');
            }
        } elseif ($this->tienePagoParcial()) {
            $this->estatus = 'parcial';
            $this->dias_atraso = 0;
            $this->fecha_inicio_atraso = null;
        } else {
            $this->estatus = 'pendiente';
            $this->dias_atraso = 0;
            $this->fecha_inicio_atraso = null;
        }

        return $this;
    }

    /**
     * Calcula el interés moratorio automáticamente
     */
    public function calcularInteresMoratorio(float $tasaMoratoriaAnual = 36.0): self
    {
        $diasAtraso = $this->calcularDiasAtraso();
        
        if ($diasAtraso <= 0 || $this->estaPagado()) {
            $this->interes_moratorio = 0.00;
            return $this;
        }

        $saldoPendiente = max(0, $this->saldo_pendiente);
        $tasaDiaria = $tasaMoratoriaAnual / 365 / 100;
        $interesMoratorio = $saldoPendiente * $tasaDiaria * $diasAtraso;

        $this->interes_moratorio = round($interesMoratorio, 2);
        
        return $this;
    }

    /**
     * Aplica un pago a esta mensualidad
     */
    public function aplicarPago(float $montoPago, string $metodoPago = 'efectivo', ?string $referencia = null): array
    {
        $validacion = $this->validarPago($montoPago);
        
        if (!$validacion['valido']) {
            return [
                'success' => false,
                'error' => implode(', ', $validacion['errores'])
            ];
        }

        $saldoAnterior = $this->getSaldoTotalPendiente();
        $nuevoMontoPagado = $this->monto_pagado + $montoPago;
        
        // Actualizar montos
        $this->monto_pagado = $nuevoMontoPagado;
        $this->numero_pagos_aplicados += 1;
        $this->fecha_ultimo_pago = date('Y-m-d H:i:s');
        
        // Recalcular saldo pendiente (stored generated column se actualiza automáticamente)
        // pero podemos forzar el cálculo manual
        $this->saldo_pendiente = max(0, $this->monto_total - $this->monto_pagado);
        
        // Recalcular estado e intereses moratorios
        $this->calcularInteresMoratorio();
        $this->actualizarEstadoAutomatico();
        
        $saldoNuevo = $this->getSaldoTotalPendiente();
        $sobrante = max(0, $montoPago - $saldoAnterior);

        return [
            'success' => true,
            'saldo_anterior' => $saldoAnterior,
            'monto_aplicado' => min($montoPago, $saldoAnterior),
            'saldo_nuevo' => $saldoNuevo,
            'sobrante' => $sobrante,
            'estado_nuevo' => $this->estatus,
            'completamente_pagada' => $this->estaPagado(),
            'metodo_pago' => $metodoPago,
            'referencia' => $referencia
        ];
    }

    /**
     * Valida si se puede aplicar un pago
     */
    public function validarPago(float $montoPago): array
    {
        $errores = [];

        if ($montoPago <= 0) {
            $errores[] = 'El monto debe ser mayor a cero';
        }

        if ($this->estatus === 'cancelada') {
            $errores[] = 'No se pueden aplicar pagos a mensualidades canceladas';
        }

        if ($this->estaPagado()) {
            $errores[] = 'Esta mensualidad ya está completamente pagada';
        }

        $saldoPendiente = $this->getSaldoTotalPendiente();
        
        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'saldo_pendiente' => $saldoPendiente,
            'puede_pagar_total' => $montoPago >= $saldoPendiente,
            'sera_pago_parcial' => $montoPago < $saldoPendiente
        ];
    }

    /**
     * Obtiene el estado visual detallado para la UI
     */
    public function getEstadoVisualDetallado(): array
    {
        if ($this->estaPagado()) {
            return [
                'texto' => 'Pagada',
                'clase' => 'success',
                'icono' => 'fas fa-check-circle',
                'badge' => 'badge-success'
            ];
        }

        if ($this->estaVencido()) {
            $dias = $this->calcularDiasAtraso();
            return [
                'texto' => "Vencida ({$dias} días)",
                'clase' => 'danger',
                'icono' => 'fas fa-exclamation-triangle',
                'badge' => 'badge-danger'
            ];
        }

        // Próxima a vencer (7 días o menos)
        $fechaVencimiento = new \DateTime($this->fecha_vencimiento);
        $hoy = new \DateTime();
        
        if ($hoy <= $fechaVencimiento) {
            $diasParaVencer = $fechaVencimiento->diff($hoy)->days;
            
            if ($diasParaVencer <= 7) {
                return [
                    'texto' => "Próxima ({$diasParaVencer} días)",
                    'clase' => 'warning',
                    'icono' => 'fas fa-clock',
                    'badge' => 'badge-warning'
                ];
            }
        }

        if ($this->tienePagoParcial()) {
            $porcentaje = round($this->getPorcentajePagado(), 1);
            return [
                'texto' => "Parcial ({$porcentaje}%)",
                'clase' => 'info',
                'icono' => 'fas fa-percentage',
                'badge' => 'badge-info'
            ];
        }

        return [
            'texto' => 'Pendiente',
            'clase' => 'secondary',
            'icono' => 'fas fa-calendar',
            'badge' => 'badge-secondary'
        ];
    }

    /**
     * Genera descripción completa para reportes
     */
    public function getDescripcionCompleta(): string
    {
        $descripcion = "Mensualidad #{$this->numero_pago}";
        
        if (!empty($this->concepto_especial)) {
            $descripcion .= " - {$this->concepto_especial}";
        }
        
        $descripcion .= " | Capital: $" . number_format($this->capital, 2);
        $descripcion .= " | Interés: $" . number_format($this->interes, 2);
        
        if ($this->interes_moratorio > 0) {
            $descripcion .= " | Mora: $" . number_format($this->interes_moratorio, 2);
        }
        
        return $descripcion;
    }

    /**
     * Obtiene información detallada de vencimiento
     */
    public function getInfoVencimiento(): array
    {
        $fechaVencimiento = new \DateTime($this->fecha_vencimiento);
        $hoy = new \DateTime();
        
        if ($hoy > $fechaVencimiento) {
            $diff = $hoy->diff($fechaVencimiento);
            return [
                'estado' => 'vencida',
                'dias' => $diff->days,
                'mensaje' => "Vencida hace {$diff->days} días",
                'fecha_vencimiento_formateada' => $fechaVencimiento->format('d/m/Y')
            ];
        } else {
            $diff = $fechaVencimiento->diff($hoy);
            return [
                'estado' => 'vigente',
                'dias' => $diff->days,
                'mensaje' => "Vence en {$diff->days} días",
                'fecha_vencimiento_formateada' => $fechaVencimiento->format('d/m/Y')
            ];
        }
    }

    /**
     * Formatea todos los montos para mostrar en UI
     */
    public function formatearMontos(): array
    {
        return [
            'saldo_inicial' => '$' . number_format($this->saldo_inicial, 2),
            'capital' => '$' . number_format($this->capital, 2),
            'interes' => '$' . number_format($this->interes, 2),
            'monto_total' => '$' . number_format($this->monto_total, 2),
            'monto_pagado' => '$' . number_format($this->monto_pagado, 2),
            'saldo_pendiente' => '$' . number_format($this->saldo_pendiente, 2),
            'interes_moratorio' => '$' . number_format($this->interes_moratorio, 2),
            'saldo_final' => '$' . number_format($this->saldo_final, 2),
            'total_con_moratorios' => '$' . number_format($this->getTotalConMoratorios(), 2),
            'saldo_total_pendiente' => '$' . number_format($this->getSaldoTotalPendiente(), 2)
        ];
    }

    /**
     * Exporta datos para JSON API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'numero_pago' => $this->numero_pago,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'monto_total' => (float) $this->monto_total,
            'monto_pagado' => (float) $this->monto_pagado,
            'saldo_pendiente' => (float) $this->saldo_pendiente,
            'interes_moratorio' => (float) $this->interes_moratorio,
            'estatus' => $this->estatus,
            'dias_atraso' => $this->calcularDiasAtraso(),
            'esta_vencida' => $this->estaVencido(),
            'esta_pagada' => $this->estaPagado(),
            'porcentaje_pago' => $this->getPorcentajePagado(),
            'estado_visual' => $this->getEstadoVisualDetallado(),
            'info_vencimiento' => $this->getInfoVencimiento(),
            'beneficiario' => $this->beneficiario,
            'concepto_especial' => $this->concepto_especial
        ];
    }

    /**
     * Validaciones de Entity antes de guardar
     */
    public function validarEntity(): array
    {
        $errores = [];

        // Validar montos
        if ($this->monto_total <= 0) {
            $errores[] = 'El monto total debe ser mayor a cero';
        }

        if ($this->capital < 0) {
            $errores[] = 'El capital no puede ser negativo';
        }

        if ($this->interes < 0) {
            $errores[] = 'El interés no puede ser negativo';
        }

        if ($this->monto_pagado < 0) {
            $errores[] = 'El monto pagado no puede ser negativo';
        }

        // Validar fechas
        if (empty($this->fecha_vencimiento)) {
            $errores[] = 'La fecha de vencimiento es requerida';
        }

        // Validar coherencia de montos (con tolerancia de 0.01)
        $sumaCalculada = $this->capital + $this->interes;
        if (abs($sumaCalculada - $this->monto_total) > 0.01) {
            $errores[] = 'La suma de capital e interés debe igualar el monto total';
        }

        // Validar número de pago
        if ($this->numero_pago <= 0) {
            $errores[] = 'El número de pago debe ser mayor a cero';
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    // ==========================================
    // MÉTODOS DE REFACTORIZACIÓN
    // ==========================================

    /**
     * Refactorizar mensualidad por abono a capital
     */
    public function refactorizar(float $nuevoSaldoCapital, float $tasaInteresAnual, int $plazoRestanteMeses): array
    {
        if ($this->estatus === 'pagada' || $this->estatus === 'cancelada') {
            return [
                'success' => false,
                'mensaje' => 'No se puede refactorizar una mensualidad ' . $this->estatus
            ];
        }

        // Calcular nueva mensualidad con fórmula francesa
        $tasaMensual = $tasaInteresAnual / 12 / 100;
        $nuevaMensualidad = $this->calcularMensualidadFrancesa($nuevoSaldoCapital, $tasaMensual, $plazoRestanteMeses);
        
        // Calcular nuevo capital e interés para esta mensualidad
        $nuevoInteres = $nuevoSaldoCapital * $tasaMensual;
        $nuevoCapital = $nuevaMensualidad - $nuevoInteres;
        
        // Guardar valores anteriores para historial
        $valoresAnteriores = [
            'saldo_inicial' => $this->saldo_inicial,
            'capital' => $this->capital,
            'interes' => $this->interes,
            'monto_total' => $this->monto_total,
            'saldo_final' => $this->saldo_final
        ];
        
        // Actualizar valores
        $this->saldo_inicial = $nuevoSaldoCapital;
        $this->capital = round($nuevoCapital, 2);
        $this->interes = round($nuevoInteres, 2);
        $this->monto_total = round($nuevaMensualidad, 2);
        $this->saldo_final = round($nuevoSaldoCapital - $nuevoCapital, 2);
        
        // Recalcular saldo pendiente si hay pagos parciales
        if ($this->monto_pagado > 0) {
            $this->saldo_pendiente = max(0, $this->monto_total - $this->monto_pagado);
        } else {
            $this->saldo_pendiente = $this->monto_total;
        }
        
        // Marcar como refactorizada
        $this->refactorizado = true;
        $this->fecha_refactorizacion = date('Y-m-d H:i:s');
        
        return [
            'success' => true,
            'valores_anteriores' => $valoresAnteriores,
            'valores_nuevos' => [
                'saldo_inicial' => $this->saldo_inicial,
                'capital' => $this->capital,
                'interes' => $this->interes,
                'monto_total' => $this->monto_total,
                'saldo_final' => $this->saldo_final
            ],
            'ahorro_interes' => $valoresAnteriores['interes'] - $this->interes,
            'mensaje' => 'Mensualidad refactorizada exitosamente'
        ];
    }

    /**
     * Calcular mensualidad con sistema francés
     */
    private function calcularMensualidadFrancesa(float $capital, float $tasaMensual, int $plazoMeses): float
    {
        if ($tasaMensual == 0) {
            return $capital / $plazoMeses;
        }
        
        $factor = pow(1 + $tasaMensual, $plazoMeses);
        $mensualidad = $capital * ($tasaMensual * $factor) / ($factor - 1);
        
        return round($mensualidad, 2);
    }

    /**
     * Ajustar fechas de vencimiento después de refactorización
     */
    public function ajustarFechaVencimiento(int $mesesAdicionales = 0): void
    {
        $fechaVencimiento = new \DateTime($this->fecha_vencimiento);
        
        if ($mesesAdicionales > 0) {
            $fechaVencimiento->modify("+{$mesesAdicionales} months");
        }
        
        $this->fecha_vencimiento = $fechaVencimiento->format('Y-m-d');
    }

    /**
     * Obtener historial de refactorizaciones
     */
    public function getHistorialRefactorizacion(): array
    {
        if (!$this->refactorizado) {
            return [];
        }
        
        return [
            'refactorizado' => true,
            'fecha_refactorizacion' => $this->fecha_refactorizacion,
            'numero_refactorizacion' => $this->numero_refactorizacion ?? 1
        ];
    }

    /**
     * Simular impacto de abono a capital
     */
    public function simularAbonoCapital(float $abonoCapital, float $tasaInteresAnual): array
    {
        if ($abonoCapital <= 0 || $abonoCapital > $this->saldo_final) {
            return [
                'valido' => false,
                'mensaje' => 'El abono debe ser mayor a 0 y menor o igual al saldo capital'
            ];
        }
        
        $nuevoSaldoCapital = $this->saldo_final - $abonoCapital;
        $tasaMensual = $tasaInteresAnual / 12 / 100;
        
        // Simular nueva mensualidad
        $plazoRestante = $this->calcularPlazoRestante();
        $nuevaMensualidad = $this->calcularMensualidadFrancesa($nuevoSaldoCapital, $tasaMensual, $plazoRestante);
        
        // Calcular ahorro
        $ahorroMensual = $this->monto_total - $nuevaMensualidad;
        $ahorroTotal = $ahorroMensual * $plazoRestante;
        
        return [
            'valido' => true,
            'saldo_capital_actual' => $this->saldo_final,
            'abono_capital' => $abonoCapital,
            'nuevo_saldo_capital' => $nuevoSaldoCapital,
            'mensualidad_actual' => $this->monto_total,
            'nueva_mensualidad' => $nuevaMensualidad,
            'ahorro_mensual' => $ahorroMensual,
            'ahorro_total_estimado' => $ahorroTotal,
            'plazo_restante' => $plazoRestante
        ];
    }

    /**
     * Calcular plazo restante basado en el número de pago
     */
    private function calcularPlazoRestante(): int
    {
        // Asumiendo un plazo original que debe venir del plan de financiamiento
        $plazoTotal = $this->plan_financiamiento_plazo_meses ?? 36;
        return max(1, $plazoTotal - $this->numero_pago + 1);
    }

    /**
     * Marcar como afectada por refactorización
     */
    public function marcarComoRefactorizada(int $numeroRefactorizacion = 1): void
    {
        $this->refactorizado = true;
        $this->fecha_refactorizacion = date('Y-m-d H:i:s');
        $this->numero_refactorizacion = $numeroRefactorizacion;
    }
}