<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfiguracionCobranzaModel extends Model
{
    protected $table            = 'configuraciones_cobranza';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'descripcion',
        'dias_gracia',
        'penalizacion_porcentaje',
        'activo',
        'created_by',
        'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombre' => 'required|max_length[100]',
        'dias_gracia' => 'permit_empty|is_natural',
        'penalizacion_porcentaje' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre es requerido',
            'max_length' => 'El nombre no puede exceder 100 caracteres'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setCreatedBy'];
    protected $beforeUpdate   = ['setUpdatedBy'];

    /**
     * Establece el usuario que crea el registro
     */
    protected function setCreatedBy(array $data)
    {
        if (auth()->loggedIn()) {
            $data['data']['created_by'] = auth()->id();
        }
        return $data;
    }

    /**
     * Establece el usuario que actualiza el registro
     */
    protected function setUpdatedBy(array $data)
    {
        if (auth()->loggedIn()) {
            $data['data']['updated_by'] = auth()->id();
        }
        return $data;
    }
}