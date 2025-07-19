<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Manzana extends Entity
{
    protected $datamap = [];
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $casts = [
        'id'           => 'integer',
        'proyectos_id' => 'integer',
        'activo'       => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $attributes = [
        'activo' => true,
        'color'  => '#3498db',
    ];

    /**
     * Genera automáticamente la clave de la manzana
     * Formato: {proyecto.clave}-{nombre}
     */
    public function setClave(?string $valor = null): static
    {
        if ($valor !== null) {
            $this->attributes['clave'] = $valor;
            return $this;
        }

        // Auto-generar clave si tenemos proyecto y nombre
        if (isset($this->attributes['proyectos_id']) && isset($this->attributes['nombre'])) {
            $proyectoModel = model('ProyectoModel');
            $proyecto = $proyectoModel->find($this->attributes['proyectos_id']);
            
            if ($proyecto) {
                $this->attributes['clave'] = $proyecto->clave . '-' . $this->attributes['nombre'];
            }
        }

        return $this;
    }

    /**
     * Mutator para el nombre - siempre en mayúsculas y sin espacios extra
     */
    public function setNombre(string $nombre): static
    {
        $this->attributes['nombre'] = strtoupper(trim($nombre));
        
        // Auto-generar clave cuando se establece el nombre
        $this->setClave();
        
        return $this;
    }

    /**
     * Mutator para coordenadas - validar formato
     */
    public function setLongitud(?string $longitud): static
    {
        if ($longitud !== null && $longitud !== '') {
            // Validar que sea un número válido para coordenadas
            if (is_numeric($longitud) && $longitud >= -180 && $longitud <= 180) {
                $this->attributes['longitud'] = $longitud;
            }
        } else {
            $this->attributes['longitud'] = null;
        }
        
        return $this;
    }

    public function setLatitud(?string $latitud): static
    {
        if ($latitud !== null && $latitud !== '') {
            // Validar que sea un número válido para coordenadas
            if (is_numeric($latitud) && $latitud >= -90 && $latitud <= 90) {
                $this->attributes['latitud'] = $latitud;
            }
        } else {
            $this->attributes['latitud'] = null;
        }
        
        return $this;
    }

    /**
     * Mutator para color - validar formato hex
     */
    public function setColor(?string $color): static
    {
        if ($color !== null && preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            $this->attributes['color'] = $color;
        } else {
            $this->attributes['color'] = '#3498db'; // Color por defecto
        }
        
        return $this;
    }

    /**
     * Accessor para obtener el nombre del proyecto relacionado
     */
    public function getNombreProyecto(): string
    {
        if (isset($this->attributes['proyectos_id'])) {
            $proyectoModel = model('ProyectoModel');
            $proyecto = $proyectoModel->find($this->attributes['proyectos_id']);
            return $proyecto ? $proyecto->nombre : 'Proyecto no encontrado';
        }
        
        return '';
    }

    /**
     * Accessor para obtener las coordenadas como array
     */
    public function getCoordenadas(): ?array
    {
        if ($this->longitud && $this->latitud) {
            return [
                'longitud' => (float) $this->longitud,
                'latitud'  => (float) $this->latitud,
            ];
        }
        
        return null;
    }

    /**
     * Verifica si la manzana tiene coordenadas GPS válidas
     */
    public function tieneCoordenadasValidas(): bool
    {
        return !empty($this->longitud) && !empty($this->latitud) &&
               is_numeric($this->longitud) && is_numeric($this->latitud);
    }

    /**
     * Verifica si la manzana puede ser eliminada
     * (no debe tener lotes asociados)
     */
    public function puedeSerEliminada(): bool
    {
        // Verificar si tiene lotes activos asociados
        $db = \Config\Database::connect();
        $totalLotes = $db->table('lotes')
                        ->where('manzanas_id', $this->id)
                        ->where('activo', 1)
                        ->countAllResults();
        
        return $totalLotes === 0;
    }

    /**
     * Obtiene información completa de la manzana con datos del proyecto
     */
    public function getInfoCompleta(): array
    {
        return [
            'id'              => $this->id,
            'nombre'          => $this->nombre,
            'clave'           => $this->clave,
            'descripcion'     => $this->descripcion,
            'proyectos_id'    => $this->proyectos_id,
            'nombre_proyecto' => $this->getNombreProyecto(),
            'coordenadas'     => $this->getCoordenadas(),
            'color'           => $this->color,
            'activo'          => $this->activo,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}