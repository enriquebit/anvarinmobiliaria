<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class InformacionConyuge extends Entity
{
    protected $attributes = [
        'id' => null,
        'cliente_id' => null,
        'nombre_completo' => null,
        'profesion' => null,
        'email' => null,
        'telefono' => null,
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
        'cliente_id' => 'int',
        'activo' => 'boolean'
    ];

    public function isActivo(): bool
    {
        return (bool) $this->activo;
    }

    public function getTelefonoFormateado(): ?string
    {
        if (!$this->telefono) {
            return null;
        }

        $telefono = preg_replace('/\D/', '', $this->telefono);
        
        if (strlen($telefono) === 10) {
            return sprintf('(%s) %s-%s', 
                substr($telefono, 0, 3),
                substr($telefono, 3, 3),
                substr($telefono, 6, 4)
            );
        }
        
        return $this->telefono;
    }
}