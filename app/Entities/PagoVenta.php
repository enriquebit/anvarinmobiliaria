<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class PagoVenta extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'fecha_pago',
        'fecha_cancelacion',
        'created_at',
        'updated_at'
    ];
    protected $casts   = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'plan_financiamiento_id' => '?integer',
        'cuenta_bancaria_id' => '?integer',
        'tabla_amortizacion_id' => '?integer',
        'numero_mensualidad' => '?integer',
        'usuario_cancela_id' => '?integer',
        'registrado_por' => 'integer',
        'monto_pago' => 'float',
        'es_pago_adelantado' => 'boolean'
    ];

    /**
     * Genera el folio del pago
     */
    public static function generarFolio(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Formato: PAG-AAAAMM-XXXXX
        $db = \Config\Database::connect();
        $builder = $db->table('pagos_ventas');
        $builder->like('folio_pago', "PAG-{$year}{$month}", 'after');
        $count = $builder->countAllResults() + 1;
        
        return sprintf("PAG-%s%s-%05d", $year, $month, $count);
    }

    /**
     * Verifica si el pago está aplicado
     */
    public function estaAplicado(): bool
    {
        return $this->estatus_pago === 'aplicado';
    }

    /**
     * Verifica si el pago está cancelado
     */
    public function estaCancelado(): bool
    {
        return $this->estatus_pago === 'cancelado';
    }

    /**
     * Verifica si puede cancelarse
     */
    public function puedeCancelarse(): bool
    {
        return $this->estatus_pago === 'aplicado';
    }

    /**
     * Verifica si tiene comprobante
     */
    public function tieneComprobante(): bool
    {
        return !empty($this->comprobante_url);
    }

    /**
     * Obtiene el concepto formateado
     */
    public function getConceptoFormateado(): string
    {
        $conceptoMap = [
            'apartado' => 'Apartado',
            'enganche' => 'Enganche',
            'mensualidad' => 'Mensualidad',
            'moratorio' => 'Interés Moratorio',
            'penalizacion' => 'Penalización',
            'liquidacion' => 'Liquidación',
            'otro' => 'Otro'
        ];
        
        return $conceptoMap[$this->concepto_pago] ?? $this->concepto_pago;
    }

    /**
     * Obtiene la forma de pago formateada
     */
    public function getFormaPagoFormateada(): string
    {
        $formaMap = [
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'cheque' => 'Cheque',
            'tarjeta' => 'Tarjeta',
            'deposito' => 'Depósito'
        ];
        
        return $formaMap[$this->forma_pago] ?? $this->forma_pago;
    }

    /**
     * Verifica si es pago de mensualidad
     */
    public function esPagoMensualidad(): bool
    {
        return $this->concepto_pago === 'mensualidad';
    }

    /**
     * Verifica si es pago de enganche
     */
    public function esPagoEnganche(): bool
    {
        return $this->concepto_pago === 'enganche';
    }

    /**
     * Obtiene descripción completa del pago
     */
    public function getDescripcionCompleta(): string
    {
        $descripcion = $this->getConceptoFormateado();
        
        if ($this->numero_mensualidad) {
            $descripcion .= " #{$this->numero_mensualidad}";
        }
        
        if ($this->descripcion_concepto) {
            $descripcion .= " - {$this->descripcion_concepto}";
        }
        
        return $descripcion;
    }

    // ==========================================
    // NUEVOS MÉTODOS ENTITY-FIRST AVANZADOS
    // ==========================================

    /**
     * Valida si se puede aplicar un pago con el monto especificado
     */
    public function validarPago(float $montoPago, ?int $tablaAmortizacionId = null): array
    {
        $errores = [];

        // Validar monto
        if ($montoPago <= 0) {
            $errores[] = 'El monto del pago debe ser mayor a cero';
        }

        // Validar estado actual
        if ($this->estaCancelado()) {
            $errores[] = 'No se puede modificar un pago cancelado';
        }

        if ($this->estaAplicado() && !empty($this->id)) {
            $errores[] = 'Este pago ya está aplicado';
        }

        // Validar referencias requeridas
        if (empty($this->venta_id)) {
            $errores[] = 'ID de venta es requerido';
        }

        if (empty($this->concepto_pago)) {
            $errores[] = 'Concepto de pago es requerido';
        }

        if (empty($this->forma_pago)) {
            $errores[] = 'Forma de pago es requerida';
        }

        // Validaciones específicas por concepto
        if ($this->esPagoMensualidad() && empty($tablaAmortizacionId)) {
            $errores[] = 'Pagos de mensualidad requieren ID de tabla de amortización';
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'monto_validado' => $montoPago
        ];
    }

    /**
     * Aplica el pago y actualiza estados automáticamente
     */
    public function aplicarPago(array $datosPago = []): array
    {
        // Generar folio si no existe
        if (empty($this->folio_pago)) {
            $this->folio_pago = self::generarFolio();
        }

        // Validar antes de aplicar
        $validacion = $this->validarPago(
            $this->monto_pago, 
            $this->tabla_amortizacion_id
        );

        if (!$validacion['valido']) {
            return [
                'success' => false,
                'error' => implode(', ', $validacion['errores'])
            ];
        }

        // Actualizar estado y fecha de aplicación
        $this->estatus_pago = 'aplicado';
        $this->fecha_pago = date('Y-m-d H:i:s');

        // Si viene usuario que registra
        if (!empty($datosPago['registrado_por'])) {
            $this->registrado_por = $datosPago['registrado_por'];
        }

        return [
            'success' => true,
            'folio_generado' => $this->folio_pago,
            'monto_aplicado' => $this->monto_pago,
            'estado_nuevo' => $this->estatus_pago,
            'fecha_aplicacion' => $this->fecha_pago
        ];
    }

    /**
     * Cancela el pago con motivo
     */
    public function cancelarPago(string $motivo, int $usuarioCancelaId): array
    {
        if (!$this->puedeCancelarse()) {
            return [
                'success' => false,
                'error' => 'Este pago no puede cancelarse en su estado actual'
            ];
        }

        if (empty($motivo)) {
            return [
                'success' => false,
                'error' => 'El motivo de cancelación es requerido'
            ];
        }

        // Actualizar estado de cancelación
        $this->estatus_pago = 'cancelado';
        $this->fecha_cancelacion = date('Y-m-d H:i:s');
        $this->motivo_cancelacion = $motivo;
        $this->usuario_cancela_id = $usuarioCancelaId;

        return [
            'success' => true,
            'estado_anterior' => 'aplicado',
            'estado_nuevo' => 'cancelado',
            'fecha_cancelacion' => $this->fecha_cancelacion,
            'motivo' => $motivo
        ];
    }

    /**
     * Valida si el pago puede aplicarse a una mensualidad específica
     */
    public function validarAplicacionMensualidad(object $mensualidad): array
    {
        $errores = [];

        if (!$this->esPagoMensualidad()) {
            $errores[] = 'Este pago no es de tipo mensualidad';
        }

        if ($mensualidad->estaPagado()) {
            $errores[] = 'La mensualidad ya está completamente pagada';
        }

        $saldoPendiente = $mensualidad->getSaldoTotalPendiente();
        if ($this->monto_pago > $saldoPendiente) {
            $errores[] = "El monto excede el saldo pendiente ($" . number_format($saldoPendiente, 2) . ")";
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'saldo_pendiente' => $saldoPendiente,
            'monto_pago' => $this->monto_pago,
            'sera_pago_completo' => $this->monto_pago >= $saldoPendiente
        ];
    }

    /**
     * Genera el estado visual para UI
     */
    public function getEstadoVisual(): array
    {
        switch ($this->estatus_pago) {
            case 'aplicado':
                return [
                    'texto' => 'Aplicado',
                    'clase' => 'success',
                    'icono' => 'fas fa-check-circle',
                    'badge' => 'badge-success'
                ];
            
            case 'pendiente':
                return [
                    'texto' => 'Pendiente',
                    'clase' => 'warning',
                    'icono' => 'fas fa-clock',
                    'badge' => 'badge-warning'
                ];
            
            case 'cancelado':
                return [
                    'texto' => 'Cancelado',
                    'clase' => 'danger',
                    'icono' => 'fas fa-times-circle',
                    'badge' => 'badge-danger'
                ];
            
            case 'devuelto':
                return [
                    'texto' => 'Devuelto',
                    'clase' => 'info',
                    'icono' => 'fas fa-undo',
                    'badge' => 'badge-info'
                ];
            
            default:
                return [
                    'texto' => 'Desconocido',
                    'clase' => 'secondary',
                    'icono' => 'fas fa-question',
                    'badge' => 'badge-secondary'
                ];
        }
    }

    /**
     * Verifica si el pago requiere comprobante
     */
    public function requiereComprobante(): bool
    {
        // Pagos de ciertos montos o formas requieren comprobante
        $formasRequierenComprobante = ['transferencia', 'cheque', 'deposito'];
        $montoMinimo = 5000; // Configurable
        
        return in_array($this->forma_pago, $formasRequierenComprobante) || 
               $this->monto_pago >= $montoMinimo;
    }

    /**
     * Valida el comprobante subido
     */
    public function validarComprobante(?string $archivoComprobante = null): array
    {
        if (!$this->requiereComprobante()) {
            return ['valido' => true, 'errores' => []];
        }

        $errores = [];

        if (empty($this->comprobante_url) && empty($archivoComprobante)) {
            $errores[] = 'Este pago requiere comprobante';
        }

        // Validar formato si se proporciona archivo
        if (!empty($archivoComprobante)) {
            $extensionesValidas = ['jpg', 'jpeg', 'png', 'pdf'];
            $extension = strtolower(pathinfo($archivoComprobante, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $extensionesValidas)) {
                $errores[] = 'Formato de comprobante no válido. Use: ' . implode(', ', $extensionesValidas);
            }
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'requiere_comprobante' => $this->requiereComprobante()
        ];
    }

    /**
     * Calcula comisiones si aplica
     */
    public function calcularComisiones(): array
    {
        // Solo ciertos conceptos generan comisiones
        $conceptosConComision = ['enganche', 'mensualidad', 'liquidacion'];
        
        if (!in_array($this->concepto_pago, $conceptosConComision)) {
            return [
                'aplica_comision' => false,
                'monto_comision' => 0
            ];
        }

        // Porcentaje base de comisión (debería venir de configuración)
        $porcentajeComision = 0.02; // 2%
        $montoComision = $this->monto_pago * $porcentajeComision;

        return [
            'aplica_comision' => true,
            'monto_comision' => round($montoComision, 2),
            'porcentaje_aplicado' => $porcentajeComision * 100,
            'base_calculo' => $this->monto_pago
        ];
    }

    /**
     * Formatea el monto para mostrar en UI
     */
    public function getMontoFormateado(): string
    {
        return '$' . number_format($this->monto_pago, 2);
    }

    /**
     * Obtiene información del comprobante
     */
    public function getInfoComprobante(): array
    {
        if (!$this->tieneComprobante()) {
            return [
                'tiene_comprobante' => false,
                'url' => null,
                'nombre_archivo' => null
            ];
        }

        $nombreArchivo = basename($this->comprobante_url);
        
        return [
            'tiene_comprobante' => true,
            'url' => $this->comprobante_url,
            'nombre_archivo' => $nombreArchivo,
            'url_completa' => site_url($this->comprobante_url)
        ];
    }

    /**
     * Exporta datos para JSON API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'folio_pago' => $this->folio_pago,
            'venta_id' => $this->venta_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pago' => (float) $this->monto_pago,
            'forma_pago' => $this->forma_pago,
            'forma_pago_formateada' => $this->getFormaPagoFormateada(),
            'concepto_pago' => $this->concepto_pago,
            'concepto_formateado' => $this->getConceptoFormateado(),
            'descripcion_completa' => $this->getDescripcionCompleta(),
            'estatus_pago' => $this->estatus_pago,
            'estado_visual' => $this->getEstadoVisual(),
            'referencia_pago' => $this->referencia_pago,
            'numero_mensualidad' => $this->numero_mensualidad,
            'es_pago_adelantado' => (bool) $this->es_pago_adelantado,
            'info_comprobante' => $this->getInfoComprobante(),
            'requiere_comprobante' => $this->requiereComprobante(),
            'comisiones' => $this->calcularComisiones()
        ];
    }

    /**
     * Validaciones completas de Entity
     */
    public function validarEntity(): array
    {
        $errores = [];

        // Validar campos requeridos
        if (empty($this->venta_id)) {
            $errores[] = 'ID de venta es requerido';
        }

        if (empty($this->monto_pago) || $this->monto_pago <= 0) {
            $errores[] = 'Monto de pago debe ser mayor a cero';
        }

        if (empty($this->forma_pago)) {
            $errores[] = 'Forma de pago es requerida';
        }

        if (empty($this->concepto_pago)) {
            $errores[] = 'Concepto de pago es requerido';
        }

        // Validar formas de pago válidas
        $formasValidas = ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'deposito'];
        if (!in_array($this->forma_pago, $formasValidas)) {
            $errores[] = 'Forma de pago no válida';
        }

        // Validar conceptos válidos
        $conceptosValidos = ['apartado', 'enganche', 'mensualidad', 'moratorio', 'penalizacion', 'liquidacion', 'otro'];
        if (!in_array($this->concepto_pago, $conceptosValidos)) {
            $errores[] = 'Concepto de pago no válido';
        }

        // Validar estados válidos
        $estadosValidos = ['aplicado', 'pendiente', 'cancelado', 'devuelto'];
        if (!empty($this->estatus_pago) && !in_array($this->estatus_pago, $estadosValidos)) {
            $errores[] = 'Estado de pago no válido';
        }

        // Validar comprobante si es requerido
        $validacionComprobante = $this->validarComprobante();
        if (!$validacionComprobante['valido']) {
            $errores = array_merge($errores, $validacionComprobante['errores']);
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Prepara datos para inserción automática
     */
    public function prepararParaInsercion(int $usuarioRegistra): self
    {
        // Generar folio si no existe
        if (empty($this->folio_pago)) {
            $this->folio_pago = self::generarFolio();
        }

        // Establecer defaults
        if (empty($this->estatus_pago)) {
            $this->estatus_pago = 'pendiente';
        }

        if (empty($this->fecha_pago)) {
            $this->fecha_pago = date('Y-m-d H:i:s');
        }

        $this->registrado_por = $usuarioRegistra;

        return $this;
    }
}