<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EstadoLote extends Entity
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

    // Estados del sistema legacy
    public const DISPONIBLE = 1;
    public const APARTADO   = 2;
    public const VENDIDO    = 3;
    public const SUSPENDIDO = 4;

    /**
     * Mutator para nombre - siempre mayúsculas
     */
    public function setNombre(string $nombre): static
    {
        $this->attributes['nombre'] = strtoupper(trim($nombre));
        return $this;
    }

    /**
     * Obtener lotes que usan este estado
     */
    public function getLotesCount(): int
    {
        if (!isset($this->attributes['id'])) {
            return 0;
        }

        $loteModel = model('LoteModel');
        return $loteModel->where('estados_lotes_id', $this->id)
                        ->where('activo', true)
                        ->countAllResults();
    }

    /**
     * Verificar si el estado puede ser eliminado
     */
    public function puedeSerEliminado(): bool
    {
        // Estados críticos del negocio no pueden eliminarse
        $estadosCriticos = [
            self::DISPONIBLE,
            self::APARTADO,
            self::VENDIDO,
            self::SUSPENDIDO
        ];

        if (in_array($this->id, $estadosCriticos)) {
            return false;
        }

        return $this->getLotesCount() === 0;
    }

    /**
     * Verificar si es un estado de venta activa
     */
    public function esVentaActiva(): bool
    {
        return in_array($this->id, [self::DISPONIBLE, self::APARTADO]);
    }

    /**
     * Verificar si permite cambios al lote
     */
    public function permiteCambios(): bool
    {
        // Solo disponible y suspendido permiten modificaciones
        return in_array($this->id, [self::DISPONIBLE, self::SUSPENDIDO]);
    }

    /**
     * Obtener color asociado para UI
     */
    public function getColorUI(): string
    {
        return match($this->id) {
            self::DISPONIBLE => 'success',
            self::APARTADO   => 'warning', 
            self::VENDIDO    => 'primary',
            self::SUSPENDIDO => 'danger',
            default          => 'secondary'
        };
    }

    /**
     * Obtener ícono asociado para UI
     */
    public function getIcono(): string
    {
        return match($this->id) {
            self::DISPONIBLE => 'fas fa-check-circle',
            self::APARTADO   => 'fas fa-clock',
            self::VENDIDO    => 'fas fa-home',
            self::SUSPENDIDO => 'fas fa-ban',
            default          => 'fas fa-question-circle'
        };
    }
}