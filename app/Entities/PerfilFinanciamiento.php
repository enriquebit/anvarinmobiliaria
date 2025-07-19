<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class PerfilFinanciamiento extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                        => 'integer',
        'empresa_id'                => 'integer',
        'proyecto_id'               => '?integer',
        'porcentaje_anticipo'       => 'float',
        'anticipo_fijo'             => 'float',
        'enganche_minimo'           => '?float',
        'apartado_minimo'           => 'float',
        'porcentaje_comision'       => 'float',
        'comision_fija'             => 'float',
        'tipo_financiamiento'       => 'string',
        'meses_sin_intereses'       => 'integer',
        'meses_con_intereses'       => 'integer',
        'porcentaje_interes_anual'  => 'float',
        'dias_anticipo'             => 'integer',
        'plazo_liquidar_enganche'   => 'integer',
        'penalizacion_apartado'     => 'float',
        'porcentaje_cancelacion'    => 'float',
        'es_default'                => 'boolean',
        'permite_apartado'          => 'boolean',
        'aplica_terreno_habitacional' => 'boolean',
        'aplica_terreno_comercial'  => 'boolean',
        'metros_cuadrados_max'      => '?float',
        'prioridad'                 => 'integer',
        'promocion_cero_enganche'   => 'boolean',
        'mensualidades_comision'    => 'integer',
        'penalizacion_enganche_tardio' => 'float',
        'activo'                    => 'boolean',
        'created_by'                => '?integer',
        'updated_by'                => '?integer',
    ];

    /**
     * Calcula el monto de anticipo basado en el precio total
     */
    public function calcularAnticipo(float $precioTotal): float
    {
        if ($this->tipo_anticipo === 'fijo') {
            return $this->anticipo_fijo;
        }
        
        return ($precioTotal * $this->porcentaje_anticipo) / 100;
    }

    /**
     * Calcula el monto de comisión basado en el precio total
     */
    public function calcularComision(float $precioTotal): float
    {
        if ($this->tipo_comision === 'fijo') {
            return $this->comision_fija;
        }
        
        return ($precioTotal * $this->porcentaje_comision) / 100;
    }

    /**
     * Calcula la mensualidad según tipo de financiamiento
     */
    public function calcularMensualidad(float $montoFinanciar): float
    {
        $totalMeses = $this->getTotalMeses();
        
        if ($totalMeses <= 0) {
            return 0;
        }
        
        // Determinar tipo de cálculo según tipo_financiamiento
        $usarIntereses = false;
        if (isset($this->tipo_financiamiento)) {
            $usarIntereses = ($this->tipo_financiamiento === 'mci' && $this->porcentaje_interes_anual > 0);
        } else {
            // Fallback: usar lógica basada en valores
            $usarIntereses = ($this->meses_con_intereses > 0 && $this->porcentaje_interes_anual > 0);
        }
        
        if ($usarIntereses) {
            return $this->calcularMensualidadConIntereses($montoFinanciar);
        }
        
        return $montoFinanciar / $totalMeses;
    }

    /**
     * Calcula la mensualidad con intereses usando la fórmula de anualidades
     */
    private function calcularMensualidadConIntereses(float $montoFinanciar): float
    {
        $tasaMensual = ($this->porcentaje_interes_anual / 100) / 12;
        $numeroMeses = $this->getTotalMeses(); // Usar total según tipo
        
        if ($tasaMensual <= 0) {
            return $montoFinanciar / $numeroMeses;
        }
        
        // Fórmula de anualidad: PMT = PV * [r(1+r)^n] / [(1+r)^n - 1]
        $mensualidad = $montoFinanciar * ($tasaMensual * pow(1 + $tasaMensual, $numeroMeses)) / 
                      (pow(1 + $tasaMensual, $numeroMeses) - 1);
        
        return $mensualidad;
    }

    /**
     * Verifica si es configuración de cero enganche
     */
    public function esCeroEnganche(): bool
    {
        return $this->tipo_anticipo === 'fijo' && $this->anticipo_fijo == 0;
    }

    /**
     * Calcula el enganche mínimo requerido basado en el precio y tipo de terreno
     */
    public function calcularEnganchemMinimo(float $precioTotal, string $tipoTerreno = 'habitacional', float $metrosCuadrados = 0): float
    {
        // Si es cero enganche, no hay mínimo
        if ($this->esCeroEnganche()) {
            return 0;
        }

        // Si tiene enganche mínimo fijo definido
        if ($this->enganche_minimo > 0) {
            return $this->enganche_minimo;
        }

        // Calcular basado en porcentaje
        return $this->calcularAnticipo($precioTotal);
    }

    /**
     * Verifica si la configuración aplica para un tipo de terreno específico
     */
    public function aplicaParaTipoTerreno(string $tipoTerreno): bool
    {
        if ($tipoTerreno === 'habitacional') {
            return $this->aplica_terreno_habitacional;
        } elseif ($tipoTerreno === 'comercial') {
            return $this->aplica_terreno_comercial;
        }
        
        return true; // Por defecto aplica
    }

    /**
     * Verifica si la configuración aplica según los metros cuadrados
     */
    public function aplicaParaMetrosCuadrados(float $metrosCuadrados): bool
    {
        if ($this->metros_cuadrados_max === null) {
            return true; // Sin límite
        }
        
        return $metrosCuadrados <= $this->metros_cuadrados_max;
    }

    /**
     * Obtiene el texto descriptivo de la acción cuando no se completa el anticipo
     */
    public function getTextoAccionAnticipoIncompleto(): string
    {
        switch ($this->accion_anticipo_incompleto) {
            case 'liberar_lote':
                return 'Lote se libera y apartado se pierde';
            case 'mantener_apartado':
                return 'Mantener apartado activo';
            case 'aplicar_penalizacion':
                return 'Aplicar penalización del ' . $this->penalizacion_apartado . '%';
            default:
                return 'Sin acción definida';
        }
    }

    /**
     * Calcula la comisión en esquema de promoción cero enganche
     */
    public function calcularComisionCeroEnganche(float $precioTotal): float
    {
        if (!$this->promocion_cero_enganche) {
            return 0;
        }

        // Usar getTotalMeses para obtener meses según tipo
        $totalMeses = $this->getTotalMeses();
        
        // Validar que haya al menos un mes
        if ($totalMeses <= 0) {
            return 0;
        }
        
        // Calcular mensualidad según el monto total
        $mensualidad = $this->calcularMensualidad($precioTotal);

        // La comisión son N mensualidades completas
        return $mensualidad * $this->mensualidades_comision;
    }

    /**
     * Verifica si es una promoción cero enganche
     */
    public function esPromocionCeroEnganche(): bool
    {
        return $this->promocion_cero_enganche === true;
    }

    /**
     * Calcula la penalización por enganche tardío
     */
    public function calcularPenalizacionEngancheTardio(float $montoEnganche): float
    {
        if ($this->penalizacion_enganche_tardio <= 0) {
            return 0;
        }

        return ($montoEnganche * $this->penalizacion_enganche_tardio) / 100;
    }

    /**
     * Obtiene el total de meses de financiamiento según tipo
     */
    public function getTotalMeses(): int
    {
        // Lógica exclusiva: usar tipo_financiamiento si existe
        if (isset($this->tipo_financiamiento)) {
            if ($this->tipo_financiamiento === 'msi') {
                return $this->meses_sin_intereses > 0 ? $this->meses_sin_intereses : 1;
            } else {
                return $this->meses_con_intereses > 0 ? $this->meses_con_intereses : 1;
            }
        }
        
        // Fallback: usar lógica basada en tasa (para compatibilidad)
        if ($this->porcentaje_interes_anual == 0) {
            return $this->meses_sin_intereses > 0 ? $this->meses_sin_intereses : 1;
        } else {
            return $this->meses_con_intereses > 0 ? $this->meses_con_intereses : 1;
        }
    }

    /**
     * Obtiene el badge HTML para el tipo de anticipo
     */
    public function getBadgeTipoAnticipo(): string
    {
        $class = $this->tipo_anticipo === 'porcentaje' ? 'badge-info' : 'badge-warning';
        $texto = $this->tipo_anticipo === 'porcentaje' ? 'Porcentaje' : 'Fijo';
        
        return '<span class="badge ' . $class . '">' . $texto . '</span>';
    }

    /**
     * Obtiene el badge HTML para el estado
     */
    public function getBadgeEstado(): string
    {
        if (!$this->activo) {
            return '<span class="badge badge-danger">Inactivo</span>';
        }
        
        if ($this->es_default) {
            return '<span class="badge badge-success">Por Defecto</span>';
        }
        
        return '<span class="badge badge-secondary">Activo</span>';
    }

    /**
     * Obtiene el valor de anticipo formateado
     */
    public function getAnticipoFormateado(): string
    {
        if ($this->tipo_anticipo === 'porcentaje') {
            return $this->porcentaje_anticipo . '%';
        }
        
        return '$' . number_format($this->anticipo_fijo, 2);
    }

    /**
     * Obtiene el valor de comisión formateado
     */
    public function getComisionFormateada(): string
    {
        if ($this->tipo_comision === 'porcentaje') {
            return $this->porcentaje_comision . '%';
        }
        
        return '$' . number_format($this->comision_fija, 2);
    }

    /**
     * Obtiene resumen de la configuración
     */
    public function getResumen(): array
    {
        $resumen = [
            'anticipo' => $this->getAnticipoFormateado(),
            'comision' => $this->getComisionFormateada(),
            'plazo_total' => $this->getTotalMeses() . ' meses',
            'sin_intereses' => $this->meses_sin_intereses . ' meses',
            'con_intereses' => $this->meses_con_intereses . ' meses',
            'tasa_anual' => $this->porcentaje_interes_anual . '%',
            'es_cero_enganche' => $this->esCeroEnganche(),
            'apartado_minimo' => '$' . number_format($this->apartado_minimo, 2),
            'permite_apartado' => $this->permite_apartado,
            'plazo_liquidar_enganche' => $this->plazo_liquidar_enganche . ' días'
        ];

        if ($this->enganche_minimo > 0) {
            $resumen['enganche_minimo'] = '$' . number_format($this->enganche_minimo, 2);
        }

        if ($this->metros_cuadrados_max > 0) {
            $resumen['metros_max'] = number_format($this->metros_cuadrados_max, 2) . ' m²';
        }

        return $resumen;
    }

    /**
     * Simula una venta con esta configuración
     */
    public function simularVenta(float $precioTotal): array
    {
        $anticipo = $this->calcularAnticipo($precioTotal);
        $montoFinanciar = $precioTotal - $anticipo;
        $comision = $this->calcularComision($precioTotal);
        $mensualidad = $this->calcularMensualidad($montoFinanciar);
        
        return [
            'precio_total' => $precioTotal,
            'anticipo' => $anticipo,
            'monto_financiar' => $montoFinanciar,
            'comision' => $comision,
            'mensualidad' => $mensualidad,
            'total_meses' => $this->getTotalMeses(),
            'total_pagos' => $anticipo + ($mensualidad * $this->getTotalMeses())
        ];
    }

    /**
     * Valida si la configuración es válida
     */
    public function isValid(): array
    {
        $errores = [];

        if (empty($this->nombre)) {
            $errores[] = 'El nombre es requerido';
        }

        if ($this->tipo_anticipo === 'porcentaje' && ($this->porcentaje_anticipo < 0 || $this->porcentaje_anticipo > 100)) {
            $errores[] = 'El porcentaje de anticipo debe estar entre 0 y 100';
        }

        if ($this->tipo_anticipo === 'fijo' && $this->anticipo_fijo < 0) {
            $errores[] = 'El anticipo fijo no puede ser negativo';
        }

        if ($this->tipo_comision === 'porcentaje' && ($this->porcentaje_comision < 0 || $this->porcentaje_comision > 100)) {
            $errores[] = 'El porcentaje de comisión debe estar entre 0 y 100';
        }

        if ($this->tipo_comision === 'fijo' && $this->comision_fija < 0) {
            $errores[] = 'La comisión fija no puede ser negativa';
        }

        if ($this->meses_sin_intereses < 0 || $this->meses_con_intereses <= 0) {
            $errores[] = 'Los meses de financiamiento deben ser válidos';
        }

        if ($this->porcentaje_interes_anual < 0 || $this->porcentaje_interes_anual > 100) {
            $errores[] = 'La tasa de interés anual debe estar entre 0 y 100';
        }

        return $errores;
    }
}