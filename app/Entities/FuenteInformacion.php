<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class FuenteInformacion extends Entity
{
    protected $attributes = [
        'id' => null,
        'nombre' => null,
        'valor' => null,
        'activo' => 1,
        'created_at' => null,
        'updated_at' => null
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'int',
        'activo' => 'boolean'
    ];

    public function isActivo(): bool
    {
        return (bool) $this->activo;
    }
}