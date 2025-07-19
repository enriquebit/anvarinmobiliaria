<?php

namespace App\Models;

use CodeIgniter\Model;

class TareaHistorialModel extends Model
{
    protected $table            = 'tareas_historial';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tarea_id', 'usuario_id', 'accion', 'estado_anterior', 
        'estado_nuevo', 'progreso_anterior', 'progreso_nuevo', 'comentario'
    ];

    // Dates
    protected $useTimestamps = false; // Solo created_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    /**
     * Registrar un cambio en el historial
     */
    public function registrarCambio(array $datos): bool
    {
        return $this->insert($datos);
    }

    /**
     * Obtener historial de una tarea específica
     */
    public function getHistorialTarea(int $tareaId): array
    {
        return $this->select('tareas_historial.*, 
                             CONCAT(staff.nombres, " ", IFNULL(staff.apellido_paterno, ""), " ", IFNULL(staff.apellido_materno, "")) as usuario_nombre,
                             users.username as username')
                    ->join('users', 'users.id = tareas_historial.usuario_id', 'inner')
                    ->join('staff', 'staff.user_id = users.id', 'inner')
                    ->where('tarea_id', $tareaId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Registrar creación de tarea
     */
    public function registrarCreacion(int $tareaId, int $adminId): bool
    {
        return $this->registrarCambio([
            'tarea_id' => $tareaId,
            'usuario_id' => $adminId,
            'accion' => 'creada',
            'estado_nuevo' => 'pendiente'
        ]);
    }

    /**
     * Registrar cambio de estado
     */
    public function registrarCambioEstado(int $tareaId, int $usuarioId, string $estadoAnterior, string $estadoNuevo, string $comentario = ''): bool
    {
        $accion = match($estadoNuevo) {
            'en_proceso' => 'iniciada',
            'completada' => 'completada',
            'cancelada' => 'cancelada',
            default => 'estado_cambiado'
        };

        return $this->registrarCambio([
            'tarea_id' => $tareaId,
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'comentario' => $comentario
        ]);
    }

    /**
     * Registrar cambio de progreso
     */
    public function registrarCambioProgreso(int $tareaId, int $usuarioId, int $progresoAnterior, int $progresoNuevo, string $comentario = ''): bool
    {
        return $this->registrarCambio([
            'tarea_id' => $tareaId,
            'usuario_id' => $usuarioId,
            'accion' => 'progreso_actualizado',
            'progreso_anterior' => $progresoAnterior,
            'progreso_nuevo' => $progresoNuevo,
            'comentario' => $comentario
        ]);
    }

    /**
     * Registrar comentario
     */
    public function registrarComentario(int $tareaId, int $usuarioId, string $comentario): bool
    {
        return $this->registrarCambio([
            'tarea_id' => $tareaId,
            'usuario_id' => $usuarioId,
            'accion' => 'comentario_agregado',
            'comentario' => $comentario
        ]);
    }

    /**
     * Registrar actualización de progreso
     */
    public function registrarActualizacionProgreso(int $tareaId, int $usuarioId, int $progresoAnterior, int $progresoNuevo, string $comentario = ''): bool
    {
        return $this->registrarCambio([
            'tarea_id' => $tareaId,
            'usuario_id' => $usuarioId,
            'accion' => 'progreso_actualizado',
            'progreso_anterior' => $progresoAnterior,
            'progreso_nuevo' => $progresoNuevo,
            'comentario' => $comentario
        ]);
    }
}