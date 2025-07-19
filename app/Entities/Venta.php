<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Venta extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'fecha_venta',
        'fecha_liquidacion',
        'fecha_cancelacion',
        'fecha_contrato',
        'created_at',
        'updated_at'
    ];
    protected $casts   = [
        'id' => 'integer',
        'lote_id' => 'integer',
        'cliente_id' => 'integer',
        'vendedor_id' => 'integer',
        'perfil_financiamiento_id' => 'integer',
        'apartado_id' => '?integer',
        'precio_lista' => 'float',
        'descuento_aplicado' => 'float',
        'precio_venta_final' => 'float',
        'contrato_generado' => 'boolean'
    ];

    /**
     * Genera el folio de la venta
     */
    public static function generarFolio(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Formato: VTA-AAAAMM-XXXXX
        $db = \Config\Database::connect();
        $builder = $db->table('ventas');
        $builder->like('folio_venta', "VTA-{$year}{$month}", 'after');
        $count = $builder->countAllResults() + 1;
        
        return sprintf("VTA-%s%s-%05d", $year, $month, $count);
    }

    /**
     * Calcula el porcentaje de descuento aplicado
     */
    public function getPorcentajeDescuento(): float
    {
        if ($this->precio_lista <= 0) {
            return 0;
        }
        
        return ($this->descuento_aplicado / $this->precio_lista) * 100;
    }

    /**
     * Verifica si la venta está activa
     */
    public function estaActiva(): bool
    {
        return $this->estatus_venta === 'activa';
    }

    /**
     * Verifica si la venta está liquidada
     */
    public function estaLiquidada(): bool
    {
        return $this->estatus_venta === 'liquidada';
    }

    /**
     * Verifica si la venta puede cancelarse
     */
    public function puedeCancelarse(): bool
    {
        return in_array($this->estatus_venta, ['activa', 'juridico']);
    }

    /**
     * Verifica si es venta de contado
     */
    public function esContado(): bool
    {
        return $this->tipo_venta === 'contado';
    }

    /**
     * Verifica si es venta financiada
     */
    public function esFinanciada(): bool
    {
        return $this->tipo_venta === 'financiado';
    }

    /**
     * Verifica si requiere contrato
     */
    public function requiereContrato(): bool
    {
        return !$this->contrato_generado && $this->estaActiva();
    }

    /**
     * Obtiene el estatus formateado
     */
    public function getEstatusFormateado(): string
    {
        $estatusMap = [
            'activa' => 'Activa',
            'cancelada' => 'Cancelada',
            'liquidada' => 'Liquidada',
            'juridico' => 'En Jurídico'
        ];
        
        return $estatusMap[$this->estatus_venta] ?? $this->estatus_venta;
    }

    /**
     * Obtiene el tipo de venta formateado
     */
    public function getTipoVentaFormateado(): string
    {
        $tipoMap = [
            'contado' => 'Contado',
            'financiado' => 'Financiado'
        ];
        
        return $tipoMap[$this->tipo_venta] ?? $this->tipo_venta;
    }

    // ==========================================
    // NUEVOS MÉTODOS ENTITY-FIRST DE AMORTIZACIÓN
    // ==========================================

    /**
     * Genera la tabla de amortización para esta venta
     */
    public function generarTablaAmortizacion(): array
    {
        if (!$this->esFinanciada()) {
            return [
                'success' => false,
                'error' => 'Solo las ventas financiadas requieren tabla de amortización'
            ];
        }

        if (empty($this->perfil_financiamiento_id)) {
            return [
                'success' => false,
                'error' => 'Perfil de financiamiento requerido para generar tabla'
            ];
        }

        // Esta lógica se implementará en el helper correspondiente
        // Por ahora retornamos la estructura esperada
        return [
            'success' => true,
            'venta_id' => $this->id,
            'perfil_financiamiento_id' => $this->perfil_financiamiento_id,
            'precio_financiar' => $this->precio_venta_final,
            'requiere_generacion' => true
        ];
    }

    /**
     * Calcula el saldo total pendiente de la venta
     */
    public function calcularSaldoTotalPendiente(): float
    {
        if (!$this->esFinanciada() || empty($this->id)) {
            return 0.0;
        }

        $db = \Config\Database::connect();
        
        // Sumar saldos pendientes de la tabla de amortización
        $saldoPendiente = $db->table('tabla_amortizacion')
                            ->selectSum('saldo_pendiente', 'total_pendiente')
                            ->join('pagos_ventas', 'pagos_ventas.tabla_amortizacion_id = tabla_amortizacion.id', 'left')
                            ->where('pagos_ventas.venta_id', $this->id)
                            ->where('tabla_amortizacion.estatus !=', 'pagada')
                            ->get()
                            ->getRow();

        return (float) ($saldoPendiente->total_pendiente ?? 0);
    }

    /**
     * Obtiene el total pagado hasta la fecha
     */
    public function calcularTotalPagado(): float
    {
        if (empty($this->id)) {
            return 0.0;
        }

        $db = \Config\Database::connect();
        
        $totalPagado = $db->table('pagos_ventas')
                         ->selectSum('monto_pago', 'total_pagado')
                         ->where('venta_id', $this->id)
                         ->where('estatus_pago', 'aplicado')
                         ->get()
                         ->getRow();

        return (float) ($totalPagado->total_pagado ?? 0);
    }

    /**
     * Calcula el porcentaje de liquidación
     */
    public function calcularPorcentajeLiquidacion(): float
    {
        if ($this->precio_venta_final <= 0) {
            return 0;
        }

        $totalPagado = $this->calcularTotalPagado();
        $porcentaje = ($totalPagado / $this->precio_venta_final) * 100;
        
        return min(100, $porcentaje);
    }

    /**
     * Verifica si la venta está completamente liquidada
     */
    public function estaCompletamenteLiquidada(): bool
    {
        $saldoPendiente = $this->calcularSaldoTotalPendiente();
        return $saldoPendiente <= 0.01; // Tolerancia de 1 centavo
    }

    /**
     * Actualiza el estado de liquidación automáticamente
     */
    public function actualizarEstadoLiquidacion(): self
    {
        if (!$this->esFinanciada()) {
            return $this;
        }

        if ($this->estaCompletamenteLiquidada() && $this->estatus_venta === 'activa') {
            $this->estatus_venta = 'liquidada';
            $this->fecha_liquidacion = date('Y-m-d');
        }

        return $this;
    }

    /**
     * Obtiene información financiera completa
     */
    public function getResumenFinanciero(): array
    {
        $totalPagado = $this->calcularTotalPagado();
        $saldoPendiente = $this->calcularSaldoTotalPendiente();
        $porcentajeLiquidacion = $this->calcularPorcentajeLiquidacion();
        
        // Calcular enganche y monto financiado
        $montoEnganche = $this->calcularMontoEnganche();
        $montoFinanciar = $this->precio_venta_final - $montoEnganche;
        $pagoMensual = $this->calcularPagoMensual();

        return [
            'precio_venta' => (float) $this->precio_venta_final,
            'monto_enganche' => $montoEnganche,
            'monto_financiar' => $montoFinanciar,
            'pago_mensual' => $pagoMensual,
            'total_pagar' => $this->calcularTotalAPagar(),
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $saldoPendiente,
            'porcentaje_liquidacion' => $porcentajeLiquidacion,
            'esta_liquidada' => $this->estaCompletamenteLiquidada(),
            'tipo_venta' => $this->tipo_venta,
            'estatus_venta' => $this->estatus_venta,
            'fecha_venta' => $this->fecha_venta,
            'fecha_liquidacion' => $this->fecha_liquidacion
        ];
    }

    /**
     * Obtiene las mensualidades pendientes
     */
    public function getMensualidadesPendientes(): array
    {
        if (!$this->esFinanciada() || empty($this->id)) {
            return [];
        }

        $db = \Config\Database::connect();
        
        $mensualidades = $db->table('tabla_amortizacion ta')
                           ->select('ta.*')
                           ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'left')
                           ->where('pv.venta_id', $this->id)
                           ->where('ta.estatus !=', 'pagada')
                           ->orderBy('ta.numero_pago', 'ASC')
                           ->get()
                           ->getResult();

        return $mensualidades;
    }

    /**
     * Obtiene las mensualidades vencidas
     */
    public function getMensualidadesVencidas(): array
    {
        if (!$this->esFinanciada() || empty($this->id)) {
            return [];
        }

        $db = \Config\Database::connect();
        $hoy = date('Y-m-d');
        
        $mensualidadesVencidas = $db->table('tabla_amortizacion ta')
                                   ->select('ta.*')
                                   ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'left')
                                   ->where('pv.venta_id', $this->id)
                                   ->where('ta.fecha_vencimiento <', $hoy)
                                   ->where('ta.estatus !=', 'pagada')
                                   ->orderBy('ta.numero_pago', 'ASC')
                                   ->get()
                                   ->getResult();

        return $mensualidadesVencidas;
    }

    /**
     * Obtiene el historial de pagos
     */
    public function getHistorialPagos(): array
    {
        if (empty($this->id)) {
            return [];
        }

        $db = \Config\Database::connect();
        
        $pagos = $db->table('pagos_ventas pv')
                   ->select('
                       pv.*,
                       ta.numero_pago,
                       ta.fecha_vencimiento,
                       CONCAT(s.nombres, " ", s.apellido_paterno) as registrado_por_nombre
                   ')
                   ->join('tabla_amortizacion ta', 'ta.id = pv.tabla_amortizacion_id', 'left')
                   ->join('users u', 'u.id = pv.registrado_por', 'left')
                   ->join('staff s', 's.user_id = u.id', 'left')
                   ->where('pv.venta_id', $this->id)
                   ->orderBy('pv.fecha_pago', 'DESC')
                   ->get()
                   ->getResult();

        return $pagos;
    }

    /**
     * Valida si puede aplicarse un pago
     */
    public function validarAplicacionPago(float $montoPago, string $conceptoPago): array
    {
        $errores = [];

        if (!$this->estaActiva()) {
            $errores[] = 'Solo se pueden aplicar pagos a ventas activas';
        }

        if ($montoPago <= 0) {
            $errores[] = 'El monto debe ser mayor a cero';
        }

        if ($this->esContado() && $conceptoPago === 'mensualidad') {
            $errores[] = 'Las ventas de contado no manejan mensualidades';
        }

        if ($this->esFinanciada() && $conceptoPago === 'liquidacion' && !$this->estaCompletamenteLiquidada()) {
            $saldoPendiente = $this->calcularSaldoTotalPendiente();
            if ($montoPago < $saldoPendiente) {
                $errores[] = "Para liquidación total se requiere: $" . number_format($saldoPendiente, 2);
            }
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'saldo_pendiente' => $this->calcularSaldoTotalPendiente(),
            'total_pagado' => $this->calcularTotalPagado()
        ];
    }

    /**
     * Obtiene la próxima mensualidad a vencer
     */
    public function getProximaMensualidad(): ?object
    {
        if (!$this->esFinanciada() || empty($this->id)) {
            return null;
        }

        $db = \Config\Database::connect();
        
        $proximaMensualidad = $db->table('tabla_amortizacion ta')
                                ->select('ta.*')
                                ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'left')
                                ->where('pv.venta_id', $this->id)
                                ->where('ta.estatus !=', 'pagada')
                                ->orderBy('ta.fecha_vencimiento', 'ASC')
                                ->limit(1)
                                ->get()
                                ->getRow();

        return $proximaMensualidad;
    }

    /**
     * Calcula días hasta la próxima mensualidad
     */
    public function getDiasProximaMensualidad(): int
    {
        $proximaMensualidad = $this->getProximaMensualidad();
        
        if (!$proximaMensualidad) {
            return 0;
        }

        $fechaVencimiento = new \DateTime($proximaMensualidad->fecha_vencimiento);
        $hoy = new \DateTime();
        
        if ($hoy > $fechaVencimiento) {
            return -$hoy->diff($fechaVencimiento)->days; // Días vencidos (negativo)
        }
        
        return $fechaVencimiento->diff($hoy)->days; // Días restantes
    }

    /**
     * Obtiene el estado visual para dashboard
     */
    public function getEstadoVisualCompleto(): array
    {
        $baseEstado = [
            'estatus' => $this->estatus_venta,
            'estatus_formateado' => $this->getEstatusFormateado(),
            'tipo_venta' => $this->getTipoVentaFormateado()
        ];

        if (!$this->esFinanciada()) {
            return array_merge($baseEstado, [
                'clase' => 'info',
                'color_badge' => 'info',
                'icono' => 'fas fa-money-bill',
                'mensaje' => 'Venta de contado'
            ]);
        }

        $porcentajeLiquidacion = $this->calcularPorcentajeLiquidacion();
        $diasProximaMensualidad = $this->getDiasProximaMensualidad();

        if ($this->estaLiquidada()) {
            return array_merge($baseEstado, [
                'clase' => 'success',
                'color_badge' => 'success',
                'icono' => 'fas fa-check-circle',
                'mensaje' => 'Completamente liquidada',
                'porcentaje_liquidacion' => 100
            ]);
        }

        if ($diasProximaMensualidad < 0) {
            return array_merge($baseEstado, [
                'clase' => 'danger',
                'color_badge' => 'danger',
                'icono' => 'fas fa-exclamation-triangle',
                'mensaje' => 'Mensualidad vencida (' . abs($diasProximaMensualidad) . ' días)',
                'porcentaje_liquidacion' => $porcentajeLiquidacion
            ]);
        }

        if ($diasProximaMensualidad <= 7) {
            return array_merge($baseEstado, [
                'clase' => 'warning',
                'color_badge' => 'warning',
                'icono' => 'fas fa-clock',
                'mensaje' => 'Próxima mensualidad (' . $diasProximaMensualidad . ' días)',
                'porcentaje_liquidacion' => $porcentajeLiquidacion
            ]);
        }

        return array_merge($baseEstado, [
            'clase' => 'primary',
            'color_badge' => 'primary',
            'icono' => 'fas fa-chart-line',
            'mensaje' => 'Pagos al corriente',
            'porcentaje_liquidacion' => $porcentajeLiquidacion
        ]);
    }

    /**
     * Formatea montos para mostrar en UI
     */
    public function formatearMontos(): array
    {
        $resumen = $this->getResumenFinanciero();
        
        return [
            'precio_lista' => '$' . number_format($this->precio_lista, 2),
            'descuento_aplicado' => '$' . number_format($this->descuento_aplicado, 2),
            'precio_venta_final' => '$' . number_format($this->precio_venta_final, 2),
            'total_pagado' => '$' . number_format($resumen['total_pagado'], 2),
            'saldo_pendiente' => '$' . number_format($resumen['saldo_pendiente'], 2),
            'porcentaje_liquidacion' => number_format($resumen['porcentaje_liquidacion'], 1) . '%'
        ];
    }

    /**
     * Exporta datos para JSON API
     */
    public function toApiArray(): array
    {
        $resumen = $this->getResumenFinanciero();
        $estadoVisual = $this->getEstadoVisualCompleto();
        
        return [
            'id' => $this->id,
            'folio_venta' => $this->folio_venta,
            'lote_id' => $this->lote_id,
            'cliente_id' => $this->cliente_id,
            'vendedor_id' => $this->vendedor_id,
            'fecha_venta' => $this->fecha_venta,
            'precio_venta_final' => (float) $this->precio_venta_final,
            'tipo_venta' => $this->tipo_venta,
            'estatus_venta' => $this->estatus_venta,
            'resumen_financiero' => $resumen,
            'estado_visual' => $estadoVisual,
            'montos_formateados' => $this->formatearMontos(),
            'contrato_generado' => (bool) $this->contrato_generado,
            'fecha_liquidacion' => $this->fecha_liquidacion,
            'dias_proxima_mensualidad' => $this->getDiasProximaMensualidad()
        ];
    }

    /**
     * Validaciones completas de Entity
     */
    public function validarEntity(): array
    {
        $errores = [];

        // Validar campos requeridos
        if (empty($this->lote_id)) {
            $errores[] = 'ID de lote es requerido';
        }

        if (empty($this->cliente_id)) {
            $errores[] = 'ID de cliente es requerido';
        }

        if (empty($this->vendedor_id)) {
            $errores[] = 'ID de vendedor es requerido';
        }

        if (empty($this->precio_venta_final) || $this->precio_venta_final <= 0) {
            $errores[] = 'Precio de venta final debe ser mayor a cero';
        }

        // Validar tipo de venta
        $tiposValidos = ['contado', 'financiado'];
        if (!in_array($this->tipo_venta, $tiposValidos)) {
            $errores[] = 'Tipo de venta no válido';
        }

        // Validar estado
        $estadosValidos = ['activa', 'cancelada', 'liquidada', 'juridico'];
        if (!empty($this->estatus_venta) && !in_array($this->estatus_venta, $estadosValidos)) {
            $errores[] = 'Estado de venta no válido';
        }

        // Validar coherencia de montos
        if ($this->descuento_aplicado > $this->precio_lista) {
            $errores[] = 'El descuento no puede ser mayor al precio de lista';
        }

        if (abs(($this->precio_lista - $this->descuento_aplicado) - $this->precio_venta_final) > 0.01) {
            $errores[] = 'El precio final no coincide con la operación precio lista - descuento';
        }

        // Validar financiamiento
        if ($this->esFinanciada() && empty($this->perfil_financiamiento_id)) {
            $errores[] = 'Ventas financiadas requieren perfil de financiamiento';
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Prepara datos para inserción automática
     */
    public function prepararParaInsercion(): self
    {
        // Generar folio si no existe
        if (empty($this->folio_venta)) {
            $this->folio_venta = self::generarFolio();
        }

        // Establecer defaults
        if (empty($this->estatus_venta)) {
            $this->estatus_venta = 'activa';
        }

        if (empty($this->fecha_venta)) {
            $this->fecha_venta = date('Y-m-d');
        }

        // Calcular precio final si no se ha establecido
        if (empty($this->precio_venta_final)) {
            $this->precio_venta_final = $this->precio_lista - $this->descuento_aplicado;
        }

        return $this;
    }

    /**
     * Calcula el monto del enganche según el perfil de financiamiento
     */
    public function calcularMontoEnganche(): float
    {
        if (!$this->esFinanciada()) {
            return 0.0;
        }

        // Obtener información del perfil de financiamiento
        $db = \Config\Database::connect();
        $perfil = $db->table('perfiles_financiamiento')
                    ->where('id', $this->perfil_financiamiento_id)
                    ->get()
                    ->getRow();

        if (!$perfil) {
            return 0.0;
        }

        // Si es promoción cero enganche, retornar 0
        if ($perfil->promocion_cero_enganche == 1) {
            return 0.0;
        }

        // Calcular enganche según el tipo
        if ($perfil->tipo_anticipo === 'porcentaje') {
            $enganche = ($this->precio_venta_final * $perfil->porcentaje_anticipo) / 100;
        } else {
            $enganche = $perfil->anticipo_fijo;
        }

        // Verificar enganche mínimo
        if ($perfil->enganche_minimo && $enganche < $perfil->enganche_minimo) {
            $enganche = $perfil->enganche_minimo;
        }

        return round($enganche, 2);
    }

    /**
     * Calcula el pago mensual de la tabla de amortización
     */
    public function calcularPagoMensual(): float
    {
        if (!$this->esFinanciada()) {
            return 0.0;
        }

        // Obtener el pago mensual de la primera mensualidad de la tabla
        $db = \Config\Database::connect();
        $mensualidad = $db->table('tabla_amortizacion')
                         ->select('monto_total')
                         ->where('venta_id', $this->id)
                         ->where('numero_pago', 1)
                         ->get()
                         ->getRow();

        return $mensualidad ? round($mensualidad->monto_total, 2) : 0.0;
    }

    /**
     * Calcula el total a pagar (suma de todas las mensualidades)
     */
    public function calcularTotalAPagar(): float
    {
        if (!$this->esFinanciada()) {
            return $this->precio_venta_final;
        }

        // Obtener el total de todas las mensualidades
        $db = \Config\Database::connect();
        $total = $db->table('tabla_amortizacion')
                   ->selectSum('monto_total', 'total_mensualidades')
                   ->where('venta_id', $this->id)
                   ->get()
                   ->getRow();

        $totalMensualidades = $total->total_mensualidades ?? 0;
        $enganche = $this->calcularMontoEnganche();

        return round($totalMensualidades + $enganche, 2);
    }
}