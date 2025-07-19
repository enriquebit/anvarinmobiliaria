<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo ConfiguracionAnualidad
 * 
 * Gestiona la configuración de anualidades por empresa
 */
class ConfiguracionAnualidadModel extends Model
{
    protected $table            = 'configuracion_anualidades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'empresas_id', 'nombre_anualidad', 'porcentaje_valor_lote', 'monto_fijo',
        'fecha_aplicacion', 'activo', 'aplicar_automaticamente', 'solo_creditos'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = true;

    /**
     * Obtener configuraciones activas por empresa
     */
    public function getConfiguracionesPorEmpresa(int $empresaId): array
    {
        return $this->where('empresas_id', $empresaId)
                   ->where('activo', 1)
                   ->orderBy('fecha_aplicacion', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener configuraciones que se aplican automáticamente
     */
    public function getConfiguracionesAutomaticas(int $empresaId): array
    {
        return $this->where('empresas_id', $empresaId)
                   ->where('activo', 1)
                   ->where('aplicar_automaticamente', 1)
                   ->orderBy('fecha_aplicacion', 'ASC')
                   ->findAll();
    }
}