<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TipoLote extends Entity
{
    protected $datamap = [];
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $casts = [
        'id'         => 'integer',
        'activo'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'activo' => true,
    ];

    /**
     * Obtener lotes que usan este tipo
     */
    public function getLotesCount(): int
    {
        if (!isset($this->attributes['id'])) {
            return 0;
        }

        $loteModel = model('LoteModel');
        return $loteModel->where('tipos_lotes_id', $this->id)
                        ->where('activo', true)
                        ->countAllResults();
    }

    /**
     * Verificar si el tipo puede ser eliminado
     */
    public function puedeSerEliminado(): bool
    {
        return $this->getLotesCount() === 0;
    }
}