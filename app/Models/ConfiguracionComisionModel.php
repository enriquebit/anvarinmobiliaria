<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfiguracionComisionModel extends Model
{
    protected $table            = 'configuracion_comisiones';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'object';
    protected $useSoftDeletes    = false;
    protected $protectFields     = true;
    protected $allowedFields     = [
        'tipo_plan_financiamiento_id', 'nombre_config', 'tipo_calculo', 'porcentaje_sobre',
        'porcentaje_comision', 'monto_fijo', 'superficie_minima_m2', 'superficie_maxima_m2',
        'monto_venta_minimo', 'monto_venta_maximo', 'paga_al_apartar', 'paga_al_enganchar',
        'paga_por_cobranza', 'porcentaje_al_apartar', 'porcentaje_al_enganchar',
        'porcentaje_por_cobranza', 'prioridad', 'activo'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'tipo_plan_financiamiento_id' => 'required|integer',
        'tipo_calculo' => 'required|in_list[porcentaje,monto_fijo,escalonado]',
        'porcentaje_sobre' => 'in_list[precio_venta,enganche,mensualidades]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    /**
     * Obtener configuración activa por plan de financiamiento
     */
    public function getConfiguracionPorPlan(int $planId): ?object
    {
        return $this->where('tipo_plan_financiamiento_id', $planId)
                   ->where('activo', 1)
                   ->orderBy('prioridad', 'ASC')
                   ->first();
    }

    /**
     * Obtener todas las configuraciones activas
     */
    public function getConfiguracionesActivas(): array
    {
        return $this->where('activo', 1)
                   ->orderBy('prioridad', 'ASC')
                   ->findAll();
    }
}