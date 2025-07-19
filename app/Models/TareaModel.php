<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Tarea;

class TareaModel extends Model
{
    protected $table            = 'tareas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Tarea';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'titulo', 'descripcion', 'prioridad', 'estado', 
        'fecha_vencimiento', 'fecha_completada', 'asignado_por', 
        'asignado_a', 'comentarios_admin', 'comentarios_usuario', 'progreso'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'titulo'       => 'required|max_length[255]',
        'prioridad'    => 'required|in_list[baja,media,alta,urgente]',
        'estado'       => 'permit_empty|in_list[pendiente,en_proceso,completada,cancelada]',
        'asignado_por' => 'required|integer|is_not_unique[users.id]',
        'asignado_a'   => 'required|integer|is_not_unique[users.id]',
        'progreso'     => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[100]'
    ];

    protected $validationMessages = [
        'titulo' => [
            'required' => 'El título de la tarea es obligatorio',
            'max_length' => 'El título no puede exceder 255 caracteres'
        ],
        'prioridad' => [
            'required' => 'La prioridad es obligatoria',
            'in_list' => 'La prioridad debe ser: baja, media, alta o urgente'
        ],
        'asignado_por' => [
            'required' => 'Debe especificar quién asigna la tarea',
            'is_not_unique' => 'El usuario que asigna no existe'
        ],
        'asignado_a' => [
            'required' => 'Debe especificar a quién se asigna la tarea',
            'is_not_unique' => 'El usuario asignado no existe'
        ]
    ];

    /**
     * Override insert para debug
     */
    public function insert($data = null, bool $returnID = true)
    {
        log_message('info', '[TAREA_MODEL_DEBUG] Insert llamado con datos: ' . json_encode($data));
        
        try {
            $result = parent::insert($data, $returnID);
            log_message('info', '[TAREA_MODEL_DEBUG] Insert resultado: ' . ($result ? $result : 'FALSE'));
            
            if (!$result) {
                $errors = $this->errors();
                log_message('error', '[TAREA_MODEL_DEBUG] Errores del modelo: ' . json_encode($errors));
                
                // Debug de validación manual
                if (!$this->skipValidation) {
                    $validation = \Config\Services::validation();
                    $validation->setRules($this->validationRules, $this->validationMessages);
                    
                    if (!$validation->run($data)) {
                        log_message('error', '[TAREA_MODEL_DEBUG] Errores de validación detallados: ' . json_encode($validation->getErrors()));
                    }
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', '[TAREA_MODEL_DEBUG] Exception en insert: ' . $e->getMessage());
            log_message('error', '[TAREA_MODEL_DEBUG] Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Obtener tareas asignadas a un usuario específico
     */
    public function getTareasUsuario(int $userId, array $filtros = []): array
    {
        $builder = $this->select('tareas.*, 
                                 CONCAT(sa.nombres, " ", IFNULL(sa.apellido_paterno, ""), " ", IFNULL(sa.apellido_materno, "")) as asignado_por_nombre,
                                 CONCAT(sb.nombres, " ", IFNULL(sb.apellido_paterno, ""), " ", IFNULL(sb.apellido_materno, "")) as asignado_a_nombre')
                        ->join('users ua', 'ua.id = tareas.asignado_por', 'inner')
                        ->join('staff sa', 'sa.user_id = ua.id', 'inner')
                        ->join('users ub', 'ub.id = tareas.asignado_a', 'inner')
                        ->join('staff sb', 'sb.user_id = ub.id', 'inner')
                        ->where('tareas.asignado_a', $userId);

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $builder->where('tareas.estado', $filtros['estado']);
        }

        if (!empty($filtros['prioridad'])) {
            $builder->where('tareas.prioridad', $filtros['prioridad']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $builder->where('tareas.created_at >=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $builder->where('tareas.created_at <=', $filtros['fecha_hasta']);
        }

        return $builder->orderBy('tareas.prioridad', 'DESC')
                       ->orderBy('tareas.fecha_vencimiento', 'ASC')
                       ->findAll();
    }

    /**
     * Obtener tareas creadas por un administrador
     */
    public function getTareasCreadas(int $adminId, array $filtros = []): array
    {
        $builder = $this->select('tareas.*, 
                                 CONCAT(sa.nombres, " ", IFNULL(sa.apellido_paterno, ""), " ", IFNULL(sa.apellido_materno, "")) as asignado_por_nombre,
                                 CONCAT(sb.nombres, " ", IFNULL(sb.apellido_paterno, ""), " ", IFNULL(sb.apellido_materno, "")) as asignado_a_nombre')
                        ->join('users ua', 'ua.id = tareas.asignado_por', 'inner')
                        ->join('staff sa', 'sa.user_id = ua.id', 'inner')
                        ->join('users ub', 'ub.id = tareas.asignado_a', 'inner')
                        ->join('staff sb', 'sb.user_id = ub.id', 'inner')
                        ->where('tareas.asignado_por', $adminId);

        // Aplicar filtros
        if (!empty($filtros['asignado_a'])) {
            $builder->where('tareas.asignado_a', $filtros['asignado_a']);
        }

        if (!empty($filtros['estado'])) {
            $builder->where('tareas.estado', $filtros['estado']);
        }

        if (!empty($filtros['prioridad'])) {
            $builder->where('tareas.prioridad', $filtros['prioridad']);
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'hoy':
                    $builder->where('DATE(tareas.created_at)', date('Y-m-d'));
                    break;
                case 'esta_semana':
                    $builder->where('WEEK(tareas.created_at)', date('W'));
                    break;
                case 'este_mes':
                    $builder->where('MONTH(tareas.created_at)', date('m'))
                            ->where('YEAR(tareas.created_at)', date('Y'));
                    break;
            }
        }

        return $builder->orderBy('tareas.created_at', 'DESC')
                       ->findAll();
    }

    /**
     * Obtener estadísticas de tareas
     */
    public function getEstadisticas(int $userId = null, bool $soloCreadas = false): array
    {
        $builder = $this->select('estado, COUNT(*) as total')
                        ->groupBy('estado');

        if ($userId) {
            if ($soloCreadas) {
                $builder->where('asignado_por', $userId);
            } else {
                $builder->where('asignado_a', $userId);
            }
        }

        $estadisticas = $builder->findAll();
        
        $resultado = [
            'pendiente' => 0,
            'en_proceso' => 0,
            'completada' => 0,
            'cancelada' => 0,
            'total' => 0
        ];

        foreach ($estadisticas as $stat) {
            $resultado[$stat->estado] = $stat->total;
            $resultado['total'] += $stat->total;
        }

        return $resultado;
    }

    /**
     * Obtener tareas vencidas
     */
    public function getTareasVencidas(int $userId = null): array
    {
        $builder = $this->select('tareas.*, 
                                 CONCAT(sa.nombres, " ", IFNULL(sa.apellido_paterno, ""), " ", IFNULL(sa.apellido_materno, "")) as asignado_por_nombre,
                                 CONCAT(sb.nombres, " ", IFNULL(sb.apellido_paterno, ""), " ", IFNULL(sb.apellido_materno, "")) as asignado_a_nombre')
                        ->join('users ua', 'ua.id = tareas.asignado_por', 'inner')
                        ->join('staff sa', 'sa.user_id = ua.id', 'inner')
                        ->join('users ub', 'ub.id = tareas.asignado_a', 'inner')
                        ->join('staff sb', 'sb.user_id = ub.id', 'inner')
                        ->where('tareas.fecha_vencimiento <', date('Y-m-d'))
                        ->whereNotIn('tareas.estado', ['completada', 'cancelada']);

        if ($userId) {
            $builder->where('tareas.asignado_a', $userId);
        }

        return $builder->orderBy('tareas.fecha_vencimiento', 'ASC')
                       ->findAll();
    }

    /**
     * Obtener tareas por vencer (próximas 3 días)
     */
    public function getTareasProximasVencer(int $userId = null): array
    {
        $fechaLimite = date('Y-m-d', strtotime('+3 days'));
        
        $builder = $this->select('tareas.*, 
                                 CONCAT(sa.nombres, " ", IFNULL(sa.apellido_paterno, ""), " ", IFNULL(sa.apellido_materno, "")) as asignado_por_nombre,
                                 CONCAT(sb.nombres, " ", IFNULL(sb.apellido_paterno, ""), " ", IFNULL(sb.apellido_materno, "")) as asignado_a_nombre')
                        ->join('users ua', 'ua.id = tareas.asignado_por', 'inner')
                        ->join('staff sa', 'sa.user_id = ua.id', 'inner')
                        ->join('users ub', 'ub.id = tareas.asignado_a', 'inner')
                        ->join('staff sb', 'sb.user_id = ub.id', 'inner')
                        ->where('tareas.fecha_vencimiento <=', $fechaLimite)
                        ->where('tareas.fecha_vencimiento >=', date('Y-m-d'))
                        ->whereNotIn('tareas.estado', ['completada', 'cancelada']);

        if ($userId) {
            $builder->where('tareas.asignado_a', $userId);
        }

        return $builder->orderBy('tareas.fecha_vencimiento', 'ASC')
                       ->findAll();
    }

    /**
     * Actualizar progreso de una tarea
     */
    public function actualizarProgreso(int $tareaId, int $progreso, int $userId): bool
    {
        $tarea = $this->find($tareaId);
        if (!$tarea) {
            return false;
        }

        // Verificar que el usuario tiene permisos para actualizar
        if ($tarea->asignado_a !== $userId && $tarea->asignado_por !== $userId) {
            return false;
        }

        $tarea->actualizarProgreso($progreso);
        return $this->save($tarea);
    }

    /**
     * Completar una tarea
     */
    public function completarTarea(int $tareaId, int $userId, string $comentario = ''): bool
    {
        $tarea = $this->find($tareaId);
        if (!$tarea) {
            return false;
        }

        // Verificar permisos
        if ($tarea->asignado_a !== $userId && $tarea->asignado_por !== $userId) {
            return false;
        }

        $tarea->completar();
        
        if (!empty($comentario)) {
            if ($tarea->asignado_a === $userId) {
                $tarea->comentarios_usuario = $comentario;
            } else {
                $tarea->comentarios_admin = $comentario;
            }
        }

        return $this->save($tarea);
    }

    /**
     * Cancelar una tarea (solo admin)
     */
    public function cancelarTarea(int $tareaId, int $adminId, string $motivo = ''): bool
    {
        $tarea = $this->find($tareaId);
        if (!$tarea || $tarea->asignado_por !== $adminId) {
            return false;
        }

        $tarea->cancelar();
        
        if (!empty($motivo)) {
            $tarea->comentarios_admin = $motivo;
        }

        return $this->save($tarea);
    }

    /**
     * Buscar tareas con filtros avanzados
     */
    public function buscarTareas(array $filtros): array
    {
        $builder = $this->select('tareas.*, 
                                 CONCAT(sa.nombres, " ", IFNULL(sa.apellido_paterno, ""), " ", IFNULL(sa.apellido_materno, "")) as asignado_por_nombre,
                                 CONCAT(sb.nombres, " ", IFNULL(sb.apellido_paterno, ""), " ", IFNULL(sb.apellido_materno, "")) as asignado_a_nombre')
                        ->join('users ua', 'ua.id = tareas.asignado_por', 'inner')
                        ->join('staff sa', 'sa.user_id = ua.id', 'inner')
                        ->join('users ub', 'ub.id = tareas.asignado_a', 'inner')
                        ->join('staff sb', 'sb.user_id = ub.id', 'inner');

        // Búsqueda por texto
        if (!empty($filtros['buscar'])) {
            $builder->groupStart()
                    ->like('tareas.titulo', $filtros['buscar'])
                    ->orLike('tareas.descripcion', $filtros['buscar'])
                    ->groupEnd();
        }

        // Filtros específicos
        foreach (['estado', 'prioridad', 'asignado_a', 'asignado_por'] as $campo) {
            if (!empty($filtros[$campo])) {
                $builder->where("tareas.{$campo}", $filtros[$campo]);
            }
        }

        // Filtros de fecha
        if (!empty($filtros['fecha_desde'])) {
            $builder->where('tareas.created_at >=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $builder->where('tareas.created_at <=', $filtros['fecha_hasta']);
        }

        return $builder->orderBy('tareas.created_at', 'DESC')
                       ->findAll();
    }

    /**
     * Obtener tareas personales (creadas por el usuario para sí mismo)
     */
    public function getMisTareasPersonales(int $userId, array $filtros = []): array
    {
        $builder = $this->select('tareas.*')
                        ->where('tareas.asignado_por', $userId)
                        ->where('tareas.asignado_a', $userId); // Solo tareas propias

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $builder->where('tareas.estado', $filtros['estado']);
        }

        if (!empty($filtros['prioridad'])) {
            $builder->where('tareas.prioridad', $filtros['prioridad']);
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'hoy':
                    $builder->where('DATE(tareas.created_at)', date('Y-m-d'));
                    break;
                case 'esta_semana':
                    $builder->where('WEEK(tareas.created_at)', date('W'));
                    break;
                case 'este_mes':
                    $builder->where('MONTH(tareas.created_at)', date('m'))
                            ->where('YEAR(tareas.created_at)', date('Y'));
                    break;
            }
        }

        return $builder->orderBy('tareas.created_at', 'DESC')
                       ->findAll();
    }

    /**
     * Obtener tareas asignadas por otros usuarios a mí
     */
    public function getTareasAsignadasAMi(int $userId, array $filtros = []): array
    {
        $builder = $this->select('tareas.*, 
                                 CONCAT(sa.nombres, " ", IFNULL(sa.apellido_paterno, ""), " ", IFNULL(sa.apellido_materno, "")) as asignado_por_nombre')
                        ->join('users ua', 'ua.id = tareas.asignado_por', 'inner')
                        ->join('staff sa', 'sa.user_id = ua.id', 'inner')
                        ->where('tareas.asignado_a', $userId)
                        ->where('tareas.asignado_por !=', $userId); // Solo asignadas por otros

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $builder->where('tareas.estado', $filtros['estado']);
        }

        if (!empty($filtros['prioridad'])) {
            $builder->where('tareas.prioridad', $filtros['prioridad']);
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'hoy':
                    $builder->where('DATE(tareas.created_at)', date('Y-m-d'));
                    break;
                case 'esta_semana':
                    $builder->where('WEEK(tareas.created_at)', date('W'));
                    break;
                case 'este_mes':
                    $builder->where('MONTH(tareas.created_at)', date('m'))
                            ->where('YEAR(tareas.created_at)', date('Y'));
                    break;
            }
        }

        return $builder->orderBy('tareas.prioridad', 'DESC')
                       ->orderBy('tareas.fecha_vencimiento', 'ASC')
                       ->findAll();
    }

    /**
     * Obtener tareas que yo asigné a otros usuarios
     */
    public function getTareasQueAsigneAOtros(int $userId, array $filtros = []): array
    {
        
        $builder = $this->select('tareas.*, 
                                 CONCAT(sa.nombres, " ", IFNULL(sa.apellido_paterno, ""), " ", IFNULL(sa.apellido_materno, "")) as asignado_a_nombre')
                        ->join('users ua', 'ua.id = tareas.asignado_a', 'inner')
                        ->join('staff sa', 'sa.user_id = ua.id', 'inner')
                        ->where('tareas.asignado_por', $userId)
                        ->where('tareas.asignado_a !=', $userId); // Solo asignadas a otros

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $builder->where('tareas.estado', $filtros['estado']);
        }

        if (!empty($filtros['prioridad'])) {
            $builder->where('tareas.prioridad', $filtros['prioridad']);
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'hoy':
                    $builder->where('DATE(tareas.created_at)', date('Y-m-d'));
                    break;
                case 'esta_semana':
                    $builder->where('WEEK(tareas.created_at)', date('W'));
                    break;
                case 'este_mes':
                    $builder->where('MONTH(tareas.created_at)', date('m'))
                            ->where('YEAR(tareas.created_at)', date('Y'));
                    break;
            }
        }

        $resultado = $builder->orderBy('tareas.prioridad', 'DESC')
                       ->orderBy('tareas.fecha_vencimiento', 'ASC')
                       ->findAll();
        
        
        return $resultado;
    }

    /**
     * Obtener estadísticas personales del usuario
     */
    public function getEstadisticasMisTareas(int $userId): array
    {
        // Estadísticas de tareas propias
        $propias = $this->select('estado, COUNT(*) as total')
                        ->where('asignado_por', $userId)
                        ->where('asignado_a', $userId)
                        ->groupBy('estado')
                        ->findAll();

        // Estadísticas de tareas asignadas por otros
        $asignadas = $this->select('estado, COUNT(*) as total')
                          ->where('asignado_a', $userId)
                          ->where('asignado_por !=', $userId)
                          ->groupBy('estado')
                          ->findAll();

        $resultado = [
            'propias' => [
                'pendiente' => 0,
                'en_proceso' => 0,
                'completada' => 0,
                'cancelada' => 0,
                'total' => 0
            ],
            'asignadas' => [
                'pendiente' => 0,
                'en_proceso' => 0,
                'completada' => 0,
                'cancelada' => 0,
                'total' => 0
            ],
            'general' => [
                'pendiente' => 0,
                'en_proceso' => 0,
                'completada' => 0,
                'cancelada' => 0,
                'total' => 0
            ]
        ];

        // Procesar estadísticas propias
        foreach ($propias as $stat) {
            $resultado['propias'][$stat->estado] = $stat->total;
            $resultado['propias']['total'] += $stat->total;
            $resultado['general'][$stat->estado] += $stat->total;
            $resultado['general']['total'] += $stat->total;
        }

        // Procesar estadísticas asignadas
        foreach ($asignadas as $stat) {
            $resultado['asignadas'][$stat->estado] = $stat->total;
            $resultado['asignadas']['total'] += $stat->total;
            $resultado['general'][$stat->estado] += $stat->total;
            $resultado['general']['total'] += $stat->total;
        }

        return $resultado;
    }
}