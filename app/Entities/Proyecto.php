<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Proyecto extends Entity
{
    // Mapeo de campos para coincidir con la base de datos
    protected $datamap = [
        'empresa_id' => 'empresas_id'
    ];

    protected $casts = [
        'id'         => 'integer',
        'empresas_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function hasCoordinates(): bool
    {
        return !empty($this->longitud) && !empty($this->latitud);
    }

    public function getColorHex(): string
    {
        return $this->color ?: '#007bff';
    }
}