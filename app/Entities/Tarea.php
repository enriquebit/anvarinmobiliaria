<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Entidad Tarea - Entity-First metodología
 * 
 * Gestiona las tareas del sistema con estados, prioridades y asignaciones
 */
class Tarea extends Entity
{
    protected $datamap = [];
    
    protected $dates = [
        'created_at',
        'updated_at', 
        'fecha_vencimiento',
        'fecha_completada'
    ];
    
    protected $casts = [
        'id' => 'integer',
        'asignado_por' => 'integer',
        'asignado_a' => 'integer',
        'progreso' => 'integer'
    ];

    /**
     * Estados válidos para las tareas
     */
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En Proceso', 
        'completada' => 'Completada',
        'cancelada' => 'Cancelada'
    ];

    /**
     * Prioridades válidas para las tareas
     */
    const PRIORIDADES = [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta', 
        'urgente' => 'Urgente'
    ];

    /**
     * Colores para las prioridades
     */
    const COLORES_PRIORIDAD = [
        'baja' => 'success',
        'media' => 'info',
        'alta' => 'warning',
        'urgente' => 'danger'
    ];

    /**
     * Colores para los estados
     */
    const COLORES_ESTADO = [
        'pendiente' => 'secondary',
        'en_proceso' => 'primary',
        'completada' => 'success',
        'cancelada' => 'danger'
    ];

    /**
     * Verificar si la tarea está vencida
     */
    public function estaVencida(): bool
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }
        
        // Para comparar solo fechas (sin hora)
        $hoy = date('Y-m-d');
        $fechaVencimiento = $this->fecha_vencimiento instanceof \DateTime ? 
                           $this->fecha_vencimiento->format('Y-m-d') : 
                           date('Y-m-d', strtotime($this->fecha_vencimiento));
        
        return $fechaVencimiento < $hoy && 
               !in_array($this->estado, ['completada', 'cancelada']);
    }

    /**
     * Verificar si la tarea está completada
     */
    public function estaCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    /**
     * Verificar si la tarea está cancelada
     */
    public function estaCancelada(): bool
    {
        return $this->estado === 'cancelada';
    }

    /**
     * Verificar si la tarea está en proceso
     */
    public function estaEnProceso(): bool
    {
        return $this->estado === 'en_proceso';
    }

    /**
     * Obtener el color de la prioridad
     */
    public function getColorPrioridad(): string
    {
        return self::COLORES_PRIORIDAD[$this->prioridad] ?? 'secondary';
    }

    /**
     * Obtener el color del estado
     */
    public function getColorEstado(): string
    {
        return self::COLORES_ESTADO[$this->estado] ?? 'secondary';
    }

    /**
     * Obtener texto legible de la prioridad
     */
    public function getPrioridadTexto(): string
    {
        return self::PRIORIDADES[$this->prioridad] ?? 'Desconocida';
    }

    /**
     * Obtener texto legible del estado
     */
    public function getEstadoTexto(): string
    {
        return self::ESTADOS[$this->estado] ?? 'Desconocido';
    }

    /**
     * Obtener días restantes hasta vencimiento
     */
    public function getDiasRestantes(): ?int
    {
        if (!$this->fecha_vencimiento) {
            return null;
        }
        
        // Trabajar solo con fechas (sin hora) para cálculo más preciso
        $hoy = new \DateTime(date('Y-m-d'));
        $fechaVencimiento = $this->fecha_vencimiento instanceof \DateTime ? 
                           $this->fecha_vencimiento->format('Y-m-d') : 
                           date('Y-m-d', strtotime($this->fecha_vencimiento));
        $vencimiento = new \DateTime($fechaVencimiento);
        
        $diferencia = $hoy->diff($vencimiento);
        
        if ($vencimiento < $hoy) {
            return -$diferencia->days; // Número negativo para vencidas
        }
        
        return $diferencia->days;
    }

    /**
     * Obtener texto de días restantes
     */
    public function getDiasRestantesTexto(): string
    {
        $dias = $this->getDiasRestantes();
        
        if ($dias === null) {
            return 'Sin fecha límite';
        }
        
        if ($dias < 0) {
            return 'Vencida hace ' . abs($dias) . ' día(s)';
        }
        
        if ($dias === 0) {
            return 'Vence hoy';
        }
        
        if ($dias === 1) {
            return 'Vence mañana';
        }
        
        return "Vence en {$dias} días";
    }

    /**
     * Marcar tarea como completada
     */
    public function completar(): void
    {
        $this->estado = 'completada';
        $this->progreso = 100;
        $this->fecha_completada = date('Y-m-d H:i:s');
    }

    /**
     * Marcar tarea como cancelada
     */
    public function cancelar(): void
    {
        $this->estado = 'cancelada';
        $this->fecha_completada = date('Y-m-d H:i:s');
    }

    /**
     * Iniciar tarea (cambiar a en proceso)
     */
    public function iniciar(): void
    {
        if ($this->estado === 'pendiente') {
            $this->estado = 'en_proceso';
            if ($this->progreso === 0) {
                $this->progreso = 10; // Progreso inicial
            }
        }
    }

    /**
     * Actualizar progreso de la tarea
     */
    public function actualizarProgreso(int $progreso): void
    {
        $this->progreso = max(0, min(100, $progreso));
        
        if ($this->progreso === 100 && $this->estado !== 'completada') {
            $this->completar();
        } elseif ($this->progreso > 0 && $this->estado === 'pendiente') {
            $this->iniciar();
        }
    }

    /**
     * Obtener información resumida de la tarea
     */
    public function getResumen(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'estado' => $this->estado,
            'estado_texto' => $this->getEstadoTexto(),
            'prioridad' => $this->prioridad,
            'prioridad_texto' => $this->getPrioridadTexto(),
            'progreso' => $this->progreso,
            'dias_restantes' => $this->getDiasRestantes(),
            'dias_restantes_texto' => $this->getDiasRestantesTexto(),
            'esta_vencida' => $this->estaVencida(),
            'color_prioridad' => $this->getColorPrioridad(),
            'color_estado' => $this->getColorEstado()
        ];
    }
}