<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CuentaBancaria extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at', 'fecha_apertura', 'fecha_ultimo_corte'];
    protected $casts   = [
        'id' => 'integer',
        'descripcion' => 'string',
        'banco' => 'string',
        'numero_cuenta' => 'string',
        'clabe' => '?string',
        'swift' => '?string',
        'titular' => 'string',
        'convenio' => '?string',
        'saldo_inicial' => 'float',
        'saldo_actual' => 'float',
        'moneda' => 'string',
        'tipo_cuenta' => 'string',
        'permite_depositos' => 'boolean',
        'permite_retiros' => 'boolean',
        'color_identificacion' => '?string',
        'logotipo_banco' => '?string',
        'proyecto_id' => '?integer',
        'empresa_id' => 'integer',
        'activo' => 'boolean',
        'notas' => '?string',
    ];

    // =====================================================================
    // MÉTODOS DE ACCESO Y FORMATEO
    // =====================================================================

    /**
     * Obtener el nombre completo de la cuenta
     */
    public function getNombreCompleto(): string
    {
        return $this->descripcion . ' (' . $this->banco . ')';
    }

    /**
     * Obtener número de cuenta formateado (método personalizado)
     */
    public function getNumeroFormateado(): string
    {
        return $this->numero_cuenta ?? '';
    }

    /**
     * Obtener CLABE formateada (método personalizado)
     */
    public function getClabeFormateada(): string
    {
        return $this->clabe ?? 'N/A';
    }

    /**
     * Formatear saldo con moneda
     */
    public function getSaldoFormateado(): string
    {
        $simbolos = [
            'MXN' => '$',
            'USD' => '$',
            'EUR' => '€'
        ];
        
        $simbolo = $simbolos[$this->moneda] ?? '$';
        return $simbolo . ' ' . number_format($this->saldo_actual, 2, '.', ',');
    }

    /**
     * Obtener color de estado según saldo
     */
    public function getColorEstadoSaldo(): string
    {
        if ($this->saldo_actual > 1000000) {
            return 'success'; // Verde para saldos altos
        } elseif ($this->saldo_actual > 100000) {
            return 'info'; // Azul para saldos medios
        } elseif ($this->saldo_actual > 0) {
            return 'warning'; // Amarillo para saldos bajos
        } else {
            return 'danger'; // Rojo para saldo negativo o cero
        }
    }

    /**
     * Verificar si la cuenta está operativa
     */
    public function estaOperativa(): bool
    {
        return $this->activo && ($this->permite_depositos || $this->permite_retiros);
    }

    /**
     * Obtener icono según tipo de cuenta
     */
    public function getIconoTipoCuenta(): string
    {
        $iconos = [
            'corriente' => 'fas fa-university',
            'ahorro' => 'fas fa-piggy-bank', 
            'inversion' => 'fas fa-chart-line',
            'efectivo' => 'fas fa-money-bill-wave'
        ];
        
        return $iconos[$this->tipo_cuenta] ?? 'fas fa-credit-card';
    }

    /**
     * Obtener badge de estado
     */
    public function getBadgeEstado(): string
    {
        if (!$this->activo) {
            return '<span class="badge badge-danger">Inactiva</span>';
        }
        
        if (!$this->permite_depositos && !$this->permite_retiros) {
            return '<span class="badge badge-warning">Solo Consulta</span>';
        }
        
        if ($this->permite_depositos && $this->permite_retiros) {
            return '<span class="badge badge-success">Operativa</span>';
        }
        
        if ($this->permite_depositos) {
            return '<span class="badge badge-info">Solo Depósitos</span>';
        }
        
        return '<span class="badge badge-secondary">Solo Retiros</span>';
    }

    /**
     * Validar formato de CLABE mexicana
     */
    public function validarClabe(): bool
    {
        if (empty($this->clabe)) {
            return true; // Es opcional
        }
        
        // CLABE debe tener exactamente 18 dígitos
        if (!preg_match('/^\d{18}$/', $this->clabe)) {
            return false;
        }
        
        // Validación de dígito verificador de CLABE
        return $this->validarDigitoVerificadorClabe();
    }

    /**
     * Algoritmo de validación del dígito verificador de CLABE
     */
    private function validarDigitoVerificadorClabe(): bool
    {
        $clabe = $this->clabe;
        $factores = [3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7];
        
        $suma = 0;
        for ($i = 0; $i < 17; $i++) {
            $suma += intval($clabe[$i]) * $factores[$i];
        }
        
        $residuo = $suma % 10;
        $digitoVerificador = $residuo === 0 ? 0 : 10 - $residuo;
        
        return intval($clabe[17]) === $digitoVerificador;
    }


    /**
     * Obtener información del proyecto asociado
     */
    public function getInfoProyecto(): ?array
    {
        if (empty($this->proyecto_id)) {
            return null;
        }
        
        // En implementación real, esto vendría de la relación con el modelo
        return [
            'id' => $this->proyecto_id,
            'nombre' => 'Proyecto ' . $this->proyecto_id,
            'activo' => true
        ];
    }

    /**
     * Obtener información de la empresa asociada
     */
    public function getInfoEmpresa(): ?array
    {
        if (empty($this->empresa_id)) {
            return null;
        }
        
        // En implementación real, esto vendría de la relación con el modelo
        return [
            'id' => $this->empresa_id,
            'nombre' => 'Empresa ' . $this->empresa_id,
            'activo' => true
        ];
    }

    /**
     * Calcular días desde último corte
     */
    public function getDiasDesdeUltimoCorte(): ?int
    {
        if (empty($this->fecha_ultimo_corte)) {
            return null;
        }
        
        $fechaCorte = new \DateTime($this->fecha_ultimo_corte);
        $fechaActual = new \DateTime();
        
        return $fechaActual->diff($fechaCorte)->days;
    }

    /**
     * Determinar si necesita conciliación
     */
    public function necesitaConciliacion(): bool
    {
        $dias = $this->getDiasDesdeUltimoCorte();
        return $dias === null || $dias > 30; // Más de 30 días sin corte
    }
}