<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class LoteAmenidad extends Entity
{
    protected $datamap = [];
    
    protected $dates = ['created_at'];
    
    protected $casts = [
        'id'          => 'integer',
        'lotes_id'    => 'integer',
        'amenidades_id' => 'integer',
        'created_at'  => 'datetime',
    ];

    /**
     * Obtener información del lote relacionado
     */
    public function getLote()
    {
        if (!isset($this->attributes['lotes_id'])) {
            return null;
        }

        $loteModel = model('LoteModel');
        return $loteModel->find($this->attributes['lotes_id']);
    }

    /**
     * Obtener información de la amenidad relacionada
     */
    public function getAmenidad()
    {
        if (!isset($this->attributes['amenidades_id'])) {
            return null;
        }

        $amenidadModel = model('AmenidadModel');
        return $amenidadModel->find($this->attributes['amenidades_id']);
    }

    /**
     * Accessor para obtener el nombre del lote
     */
    public function getNombreLote(): string
    {
        $lote = $this->getLote();
        return $lote ? ($lote->numero . ' - ' . $lote->clave) : 'Lote no encontrado';
    }

    /**
     * Accessor para obtener el nombre de la amenidad
     */
    public function getNombreAmenidad(): string
    {
        $amenidad = $this->getAmenidad();
        return $amenidad ? $amenidad->nombre : 'Amenidad no encontrada';
    }

    /**
     * Verificar si la relación es válida
     */
    public function esRelacionValida(): bool
    {
        return $this->getLote() !== null && $this->getAmenidad() !== null;
    }

    /**
     * Obtener información completa de la relación
     */
    public function getInfoCompleta(): array
    {
        $lote = $this->getLote();
        $amenidad = $this->getAmenidad();

        return [
            'id'               => $this->id,
            'lotes_id'         => $this->lotes_id,
            'amenidades_id'    => $this->amenidades_id,
            'lote'             => $lote ? $lote->getInfoCompleta() : null,
            'amenidad'         => $amenidad ? $amenidad->getInfoCompleta() : null,
            'nombre_lote'      => $this->getNombreLote(),
            'nombre_amenidad'  => $this->getNombreAmenidad(),
            'es_valida'        => $this->esRelacionValida(),
            'created_at'       => $this->created_at,
        ];
    }

    /**
     * Obtener badge HTML para mostrar en UI
     */
    public function getBadgeHTML(): string
    {
        $amenidad = $this->getAmenidad();
        if (!$amenidad) {
            return '<span class="badge badge-secondary">Amenidad no encontrada</span>';
        }

        return $amenidad->getBadgeHTML('info');
    }
}