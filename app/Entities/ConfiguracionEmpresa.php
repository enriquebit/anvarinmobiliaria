<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * ConfiguracionEmpresa Entity
 * 
 * Implementa la configuración financiera por empresa del sistema legacy
 * Maneja folios automáticos, parámetros de apartados, enganches y comisiones
 * Compatible con todas las configuraciones del legacy (tb_configuracion)
 */
class ConfiguracionEmpresa extends Entity
{
    protected $datamap = [];
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $casts = [
        'id'                          => 'integer',
        'empresas_id'                 => 'integer',
        'apartado_minimo'             => 'float',
        'dias_apartado'               => 'integer',
        'porcentaje_enganche'         => 'float',
        'dias_enganche'               => 'integer',
        'comision_apartado'           => 'float',
        'porcentaje_comision_total'   => 'float',
        'tasa_interes_anual'          => 'float',
        'tasa_moratoria_anual'        => 'float',
        'folio_apartado'              => 'integer',
        'folio_enganche'              => 'integer',
        'folio_venta'                 => 'integer',
        'folio_varios_pagos'          => 'integer',
        'redondear_pagos_mensuales'   => 'boolean',
        'permitir_pagos_parciales'    => 'boolean',
        'created_at'                  => 'datetime',
        'updated_at'                  => 'datetime',
    ];

    /**
     * Atributos por defecto (valores típicos del legacy)
     */
    protected $attributes = [
        'apartado_minimo'             => 5000.00,
        'dias_apartado'               => 15,
        'porcentaje_enganche'         => 20.00,
        'dias_enganche'               => 30,
        'comision_apartado'           => 500.00,
        'porcentaje_comision_total'   => 3.00,
        'tasa_interes_anual'          => 12.00,
        'tasa_moratoria_anual'        => 24.00,
        'folio_apartado'              => 1,
        'folio_enganche'              => 1,
        'folio_venta'                 => 1,
        'folio_varios_pagos'          => 1,
        'redondear_pagos_mensuales'   => true,
        'permitir_pagos_parciales'    => true,
    ];

    /**
     * Configurar parámetros de apartado
     */
    public function configurarApartado(float $montoMinimo, int $diasLimite): static
    {
        if ($montoMinimo < 0) {
            throw new \InvalidArgumentException('El monto mínimo de apartado no puede ser negativo');
        }
        
        if ($diasLimite < 1 || $diasLimite > 365) {
            throw new \InvalidArgumentException('Los días de apartado deben estar entre 1 y 365');
        }

        $this->attributes['apartado_minimo'] = round($montoMinimo, 2);
        $this->attributes['dias_apartado'] = $diasLimite;

        return $this;
    }

    /**
     * Configurar parámetros de enganche
     */
    public function configurarEnganche(float $porcentajeRequerido, int $diasLiquidacion): static
    {
        if ($porcentajeRequerido < 0 || $porcentajeRequerido > 100) {
            throw new \InvalidArgumentException('El porcentaje de enganche debe estar entre 0% y 100%');
        }
        
        if ($diasLiquidacion < 1 || $diasLiquidacion > 365) {
            throw new \InvalidArgumentException('Los días de liquidación deben estar entre 1 y 365');
        }

        $this->attributes['porcentaje_enganche'] = round($porcentajeRequerido, 2);
        $this->attributes['dias_enganche'] = $diasLiquidacion;

        return $this;
    }

    /**
     * Configurar parámetros de comisiones
     */
    public function configurarComisiones(float $comisionApartado, float $porcentajeComisionTotal): static
    {
        if ($comisionApartado < 0) {
            throw new \InvalidArgumentException('La comisión de apartado no puede ser negativa');
        }
        
        if ($porcentajeComisionTotal < 0 || $porcentajeComisionTotal > 50) {
            throw new \InvalidArgumentException('El porcentaje de comisión total debe estar entre 0% y 50%');
        }

        $this->attributes['comision_apartado'] = round($comisionApartado, 2);
        $this->attributes['porcentaje_comision_total'] = round($porcentajeComisionTotal, 2);

        return $this;
    }

    /**
     * Configurar tasas de interés
     */
    public function configurarIntereses(float $tasaAnual, float $tasaMoratoria): static
    {
        if ($tasaAnual < 0 || $tasaAnual > 100) {
            throw new \InvalidArgumentException('La tasa de interés anual debe estar entre 0% y 100%');
        }
        
        if ($tasaMoratoria < 0 || $tasaMoratoria > 100) {
            throw new \InvalidArgumentException('La tasa moratoria debe estar entre 0% y 100%');
        }

        $this->attributes['tasa_interes_anual'] = round($tasaAnual, 2);
        $this->attributes['tasa_moratoria_anual'] = round($tasaMoratoria, 2);

        return $this;
    }

    /**
     * Configurar opciones adicionales
     */
    public function configurarOpciones(bool $redondearPagos = true, bool $permitirPagosParicales = true): static
    {
        $this->attributes['redondear_pagos_mensuales'] = $redondearPagos;
        $this->attributes['permitir_pagos_parciales'] = $permitirPagosParicales;

        return $this;
    }

    /**
     * Obtener siguiente folio de apartado
     */
    public function getSiguienteFolioApartado(): string
    {
        $siguienteNumero = $this->folio_apartado + 1;
        return 'APT' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener siguiente folio de enganche
     */
    public function getSiguienteFolioEnganche(): string
    {
        $siguienteNumero = $this->folio_enganche + 1;
        return 'ENG' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener siguiente folio de venta
     */
    public function getSiguienteFolioVenta(): string
    {
        $siguienteNumero = $this->folio_venta + 1;
        return 'VTA' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener siguiente folio de varios pagos
     */
    public function getSiguienteFolioVariosPagos(): string
    {
        $siguienteNumero = $this->folio_varios_pagos + 1;
        return 'VAR' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Incrementar folio de apartado
     */
    public function incrementarFolioApartado(): static
    {
        $this->attributes['folio_apartado'] = $this->folio_apartado + 1;
        return $this;
    }

    /**
     * Incrementar folio de enganche
     */
    public function incrementarFolioEnganche(): static
    {
        $this->attributes['folio_enganche'] = $this->folio_enganche + 1;
        return $this;
    }

    /**
     * Incrementar folio de venta
     */
    public function incrementarFolioVenta(): static
    {
        $this->attributes['folio_venta'] = $this->folio_venta + 1;
        return $this;
    }

    /**
     * Incrementar folio de varios pagos
     */
    public function incrementarFolioVariosPagos(): static
    {
        $this->attributes['folio_varios_pagos'] = $this->folio_varios_pagos + 1;
        return $this;
    }

    /**
     * Calcular monto de enganche requerido
     */
    public function calcularMontoEnganche(float $precioTotal): float
    {
        $montoEnganche = ($precioTotal * $this->porcentaje_enganche) / 100;
        return round($montoEnganche, 2);
    }

    /**
     * Calcular comisión de apartado
     */
    public function calcularComisionApartado(): float
    {
        return round($this->comision_apartado, 2);
    }

    /**
     * Calcular comisión total sobre venta
     */
    public function calcularComisionTotal(float $montoVenta): float
    {
        $comision = ($montoVenta * $this->porcentaje_comision_total) / 100;
        return round($comision, 2);
    }

    /**
     * Validar si un monto cumple el mínimo de apartado
     */
    public function cumpleMontoMinimoApartado(float $monto): bool
    {
        return $monto >= $this->apartado_minimo;
    }

    /**
     * Calcular fecha límite de apartado
     */
    public function calcularFechaLimiteApartado(?\DateTime $fechaBase = null): \DateTime
    {
        $fecha = $fechaBase ?? new \DateTime();
        return $fecha->modify("+{$this->dias_apartado} days");
    }

    /**
     * Calcular fecha límite de enganche
     */
    public function calcularFechaLimiteEnganche(?\DateTime $fechaBase = null): \DateTime
    {
        $fecha = $fechaBase ?? new \DateTime();
        return $fecha->modify("+{$this->dias_enganche} days");
    }

    /**
     * Obtener tasa de interés mensual
     */
    public function getTasaInteresMensual(): float
    {
        return round($this->tasa_interes_anual / 12, 4);
    }

    /**
     * Obtener tasa moratoria diaria
     */
    public function getTasaMoratoriaDiaria(): float
    {
        return round($this->tasa_moratoria_anual / 365, 4);
    }

    /**
     * Obtener configuración completa
     */
    public function getConfiguracionCompleta(): array
    {
        return [
            'apartado' => [
                'monto_minimo' => $this->apartado_minimo,
                'monto_minimo_formateado' => formatPrecio($this->apartado_minimo),
                'dias_limite' => $this->dias_apartado,
                'comision_fija' => $this->comision_apartado,
                'comision_formateada' => formatPrecio($this->comision_apartado),
            ],
            'enganche' => [
                'porcentaje_requerido' => $this->porcentaje_enganche,
                'dias_liquidacion' => $this->dias_enganche,
            ],
            'comisiones' => [
                'apartado_fijo' => $this->comision_apartado,
                'porcentaje_total' => $this->porcentaje_comision_total,
            ],
            'intereses' => [
                'tasa_anual' => $this->tasa_interes_anual,
                'tasa_mensual' => $this->getTasaInteresMensual(),
                'tasa_moratoria_anual' => $this->tasa_moratoria_anual,
                'tasa_moratoria_diaria' => $this->getTasaMoratoriaDiaria(),
            ],
            'folios' => [
                'apartado_actual' => $this->folio_apartado,
                'enganche_actual' => $this->folio_enganche,
                'venta_actual' => $this->folio_venta,
                'varios_pagos_actual' => $this->folio_varios_pagos,
            ],
            'opciones' => [
                'redondear_pagos' => $this->redondear_pagos_mensuales,
                'permitir_parciales' => $this->permitir_pagos_parciales,
            ]
        ];
    }

    /**
     * Validar integridad de la configuración
     */
    public function validarConfiguracion(): bool
    {
        // Validar empresa
        if (empty($this->empresas_id)) {
            throw new \Exception('Debe especificar una empresa para la configuración');
        }

        // Validar apartado
        if ($this->apartado_minimo < 0) {
            throw new \Exception('El monto mínimo de apartado no puede ser negativo');
        }

        // Validar enganche
        if ($this->porcentaje_enganche < 0 || $this->porcentaje_enganche > 100) {
            throw new \Exception('El porcentaje de enganche debe estar entre 0% y 100%');
        }

        // Validar comisiones
        if ($this->comision_apartado < 0) {
            throw new \Exception('La comisión de apartado no puede ser negativa');
        }

        if ($this->porcentaje_comision_total < 0 || $this->porcentaje_comision_total > 50) {
            throw new \Exception('El porcentaje de comisión total debe estar entre 0% y 50%');
        }

        // Validar tasas
        if ($this->tasa_interes_anual < 0 || $this->tasa_interes_anual > 100) {
            throw new \Exception('La tasa de interés anual debe estar entre 0% y 100%');
        }

        if ($this->tasa_moratoria_anual < 0 || $this->tasa_moratoria_anual > 100) {
            throw new \Exception('La tasa moratoria debe estar entre 0% y 100%');
        }

        return true;
    }

    /**
     * Aplicar configuración predeterminada
     */
    public function aplicarConfiguracionPredeterminada(): static
    {
        $this->configurarApartado(5000.00, 15);
        $this->configurarEnganche(20.00, 30);
        $this->configurarComisiones(500.00, 3.00);
        $this->configurarIntereses(12.00, 24.00);
        $this->configurarOpciones(true, true);

        return $this;
    }
}