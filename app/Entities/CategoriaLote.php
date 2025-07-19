<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CategoriaLote extends Entity
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
     * Mutator para nombre - siempre mayúsculas
     */
    public function setNombre(string $nombre): static
    {
        $this->attributes['nombre'] = strtoupper(trim($nombre));
        return $this;
    }

    /**
     * Obtener lotes que usan esta categoría
     */
    public function getLotesCount(): int
    {
        if (!isset($this->attributes['id'])) {
            return 0;
        }

        $loteModel = model('LoteModel');
        return $loteModel->where('categorias_lotes_id', $this->id)
                        ->where('activo', true)
                        ->countAllResults();
    }

    /**
     * Verificar si la categoría puede ser eliminada
     */
    public function puedeSerEliminada(): bool
    {
        return $this->getLotesCount() === 0;
    }
}