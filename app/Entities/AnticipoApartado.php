<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Entity AnticipoApartado
 * 
 * Maneja el tracking de anticipos acumulados para apartados
 * y su conversión automática a liquidación de enganche
 */
class AnticipoApartado extends Entity
{
    // Estados del anticipo
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APLICADO = 'aplicado';
    const ESTADO_VENCIDO = 'vencido';
    const ESTADO_CONVERTIDO = 'convertido';
    
    protected $datamap = [];
    protected $dates   = ['fecha_anticipo', 'fecha_vencimiento', 'fecha_conversion', 'created_at', 'updated_at'];
    protected $casts   = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'numero_anticipo' => 'integer',
        'monto_anticipo' => 'float',
        'monto_acumulado' => 'float',
        'monto_requerido_enganche' => 'float',
        'porcentaje_completado' => 'float',
        'estado' => 'string',
        'convertido_a_liquidacion' => 'boolean',
        'liquidacion_enganche_id' => '?integer',
        'dias_vigencia' => 'integer',
        'aplica_interes' => 'boolean',
        'tasa_interes_mensual' => 'float'
    ];

    /**
     * Calcular porcentaje completado hacia el enganche
     */
    public function calcularPorcentajeCompletado(): float
    {
        if ($this->monto_requerido_enganche <= 0) {
            return 0;
        }
        
        $porcentaje = ($this->monto_acumulado / $this->monto_requerido_enganche) * 100;
        return min(round($porcentaje, 2), 100);
    }

    /**
     * Calcular monto faltante para completar enganche
     */
    public function getMontoFaltante(): float
    {
        $faltante = $this->monto_requerido_enganche - $this->monto_acumulado;
        return max($faltante, 0);
    }

    /**
     * Verificar si está listo para conversión
     */
    public function listoParaConversion(): bool
    {
        return $this->monto_acumulado >= $this->monto_requerido_enganche && 
               $this->estado === self::ESTADO_APLICADO &&
               !$this->convertido_a_liquidacion;
    }

    /**
     * Verificar si está vencido
     */
    public function estaVencido(): bool
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }
        
        return strtotime($this->fecha_vencimiento) < strtotime('today') &&
               $this->estado === self::ESTADO_PENDIENTE;
    }

    /**
     * Calcular días restantes de vigencia
     */
    public function getDiasRestantes(): int
    {
        if (!$this->fecha_vencimiento || $this->estaVencido()) {
            return 0;
        }
        
        $fecha_vencimiento = new \DateTime($this->fecha_vencimiento);
        $hoy = new \DateTime();
        
        return $fecha_vencimiento->diff($hoy)->days;
    }

    /**
     * Calcular intereses acumulados si aplica
     */
    public function calcularIntereses(): float
    {
        if (!$this->aplica_interes || $this->tasa_interes_mensual <= 0) {
            return 0;
        }
        
        $fecha_inicio = new \DateTime($this->fecha_anticipo);
        $fecha_actual = new \DateTime();
        $meses_transcurridos = $fecha_inicio->diff($fecha_actual)->m;
        
        // Interés simple mensual
        $interes = $this->monto_anticipo * ($this->tasa_interes_mensual / 100) * $meses_transcurridos;
        
        return round($interes, 2);
    }

    /**
     * Aplicar nuevo anticipo
     */
    public function aplicarNuevoAnticipo(float $monto): array
    {
        if ($this->estado !== self::ESTADO_APLICADO && $this->estado !== self::ESTADO_PENDIENTE) {
            return [
                'success' => false,
                'mensaje' => 'No se pueden aplicar anticipos a un apartado ' . $this->estado
            ];
        }
        
        // Validar monto mínimo
        $monto_minimo = 5000.00;
        if ($monto < $monto_minimo) {
            return [
                'success' => false,
                'mensaje' => "El monto mínimo de anticipo es $" . number_format($monto_minimo, 2)
            ];
        }
        
        // Actualizar montos
        $this->monto_anticipo = $monto;
        $this->monto_acumulado += $monto;
        $this->numero_anticipo++;
        $this->porcentaje_completado = $this->calcularPorcentajeCompletado();
        $this->estado = self::ESTADO_APLICADO;
        $this->fecha_anticipo = date('Y-m-d');
        
        // Verificar si completó el enganche
        $completo = $this->listoParaConversion();
        
        return [
            'success' => true,
            'monto_acumulado' => $this->monto_acumulado,
            'porcentaje_completado' => $this->porcentaje_completado,
            'monto_faltante' => $this->getMontoFaltante(),
            'listo_conversion' => $completo,
            'mensaje' => $completo ? 
                'Enganche completado. Listo para conversión a liquidación.' : 
                'Anticipo aplicado exitosamente. Falta $' . number_format($this->getMontoFaltante(), 2) . ' para completar el enganche.'
        ];
    }

    /**
     * Convertir a liquidación de enganche
     */
    public function convertirALiquidacion(int $liquidacion_id): void
    {
        $this->estado = self::ESTADO_CONVERTIDO;
        $this->convertido_a_liquidacion = true;
        $this->liquidacion_enganche_id = $liquidacion_id;
        $this->fecha_conversion = date('Y-m-d H:i:s');
    }

    /**
     * Validar vigencia para aplicar anticipos
     */
    public function validarVigencia(): array
    {
        if ($this->estaVencido()) {
            return [
                'vigente' => false,
                'mensaje' => 'El apartado venció el ' . date('d/m/Y', strtotime($this->fecha_vencimiento))
            ];
        }
        
        $dias_restantes = $this->getDiasRestantes();
        
        return [
            'vigente' => true,
            'dias_restantes' => $dias_restantes,
            'mensaje' => $dias_restantes <= 7 ? 
                "Atención: El apartado vence en {$dias_restantes} días" : 
                "Apartado vigente por {$dias_restantes} días más"
        ];
    }

    /**
     * Obtener resumen del estado del anticipo
     */
    public function getResumen(): array
    {
        return [
            'numero_anticipos' => $this->numero_anticipo,
            'monto_acumulado' => $this->monto_acumulado,
            'monto_requerido' => $this->monto_requerido_enganche,
            'monto_faltante' => $this->getMontoFaltante(),
            'porcentaje_completado' => $this->porcentaje_completado,
            'estado' => $this->estado,
            'dias_restantes' => $this->getDiasRestantes(),
            'listo_conversion' => $this->listoParaConversion(),
            'intereses_acumulados' => $this->calcularIntereses()
        ];
    }
}