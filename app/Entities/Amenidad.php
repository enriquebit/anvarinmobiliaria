<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Amenidad extends Entity
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
        'icono'  => 'fas fa-home',
    ];

    /**
     * Mutator para nombre - siempre capitalizado
     */
    public function setNombre(string $nombre): static
    {
        $this->attributes['nombre'] = ucwords(strtolower(trim($nombre)));
        return $this;
    }

    /**
     * Mutator para icono CSS - validar formato FontAwesome
     */
    public function setIcono(string $icono): static
    {
        // Validar que sea una clase FontAwesome válida
        $icono = trim($icono);
        if (!str_starts_with($icono, 'fas ') && !str_starts_with($icono, 'far ') && !str_starts_with($icono, 'fab ')) {
            $icono = 'fas fa-' . str_replace(['fas ', 'far ', 'fab '], '', $icono);
        }
        
        $this->attributes['icono'] = $icono;
        return $this;
    }

    /**
     * Obtener lotes que tienen esta amenidad
     */
    public function getLotesCount(): int
    {
        if (!isset($this->attributes['id'])) {
            return 0;
        }

        $loteAmenidadModel = model('LoteAmenidadModel');
        return $loteAmenidadModel->where('amenidades_id', $this->id)
                                ->countAllResults();
    }

    /**
     * Obtener lotes relacionados
     */
    public function getLotes(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }

        $loteAmenidadModel = model('LoteAmenidadModel');
        return $loteAmenidadModel->getLotesPorAmenidad($this->id);
    }

    /**
     * Verificar si la amenidad puede ser eliminada
     */
    public function puedeSerEliminada(): bool
    {
        return $this->getLotesCount() === 0;
    }

    /**
     * Verificar si es amenidad básica del sistema
     */
    public function esAmenidadBasica(): bool
    {
        $amenidadesBasicas = [
            'ALBERCA', 'RECÁMARAS', 'BAÑOS', 'COCHERA', 
            'PATIO', 'SALA / COMEDOR / COCINA', 'CUARTO DE SERVICIO'
        ];
        
        return in_array(strtoupper($this->nombre), $amenidadesBasicas);
    }

    /**
     * Obtener HTML del ícono para mostrar en UI
     */
    public function getIconoHTML(string $claseExtra = ''): string
    {
        $clase = $this->icono . ($claseExtra ? ' ' . $claseExtra : '');
        return "<i class=\"{$clase}\" title=\"{$this->nombre}\"></i>";
    }

    /**
     * Obtener badge HTML con ícono y nombre
     */
    public function getBadgeHTML(string $colorClase = 'primary'): string
    {
        return "<span class=\"badge badge-{$colorClase}\">" . 
               $this->getIconoHTML() . " {$this->nombre}" .
               "</span>";
    }

    /**
     * Obtener información completa de la amenidad
     */
    public function getInfoCompleta(): array
    {
        return [
            'id'          => $this->id,
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion,
            'icono'       => $this->icono,
            'lotes_count' => $this->getLotesCount(),
            'es_basica'   => $this->esAmenidadBasica(),
            'puede_eliminar' => $this->puedeSerEliminada(),
            'icono_html'  => $this->getIconoHTML(),
            'badge_html'  => $this->getBadgeHTML(),
            'activo'      => $this->activo,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}