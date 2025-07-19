<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TareaModel;
use App\Models\TareaHistorialModel;
use App\Models\UserModel;
use App\Models\StaffModel;
use App\Entities\Tarea;

/**
 * Controlador de Tareas para Administradores
 * 
 * Permite a los administradores crear, asignar y gestionar tareas
 * para los usuarios del sistema siguiendo la metodología Entity-First
 */
class AdminTareasController extends BaseController
{
    protected $tareaModel;
    protected $historialModel;
    protected $userModel;
    protected $staffModel;

    public function __construct()
    {
        $this->tareaModel = new TareaModel();
        $this->historialModel = new TareaHistorialModel();
        $this->userModel = new UserModel();
        $this->staffModel = new StaffModel();
    }

    /**
     * Vista principal de gestión de tareas
     */
    public function index()
    {
        $user = auth()->user();
        
        // Obtener filtros
        $filtros = [
            'estado' => $this->request->getGet('estado'),
            'prioridad' => $this->request->getGet('prioridad'),
            'asignado_a' => $this->request->getGet('asignado_a'),
            'periodo' => $this->request->getGet('periodo') ?: 'este_mes'
        ];

        // Limpiar filtros vacíos
        $filtros = array_filter($filtros);

        $data = [
            'titulo' => 'Gestión de Tareas',
            'tareas' => $this->tareaModel->getTareasCreadas($user->id, $filtros),
            'estadisticas' => $this->tareaModel->getEstadisticas($user->id, true),
            'usuarios' => $this->getUsuariosStaff(),
            'currentUserId' => $user->id, // Para verificar permisos de eliminación
            'filtros' => $filtros,
            'estados' => Tarea::ESTADOS,
            'prioridades' => Tarea::PRIORIDADES,
            'tareasVencidas' => $this->tareaModel->getTareasVencidas(),
            'tareasProximasVencer' => $this->tareaModel->getTareasProximasVencer()
        ];

        return view('admin/tareas/index', $data);
    }

    /**
     * Vista para crear nueva tarea
     */
    public function create()
    {
        $currentUser = auth()->user();
        
        $data = [
            'titulo' => 'Crear Nueva Tarea',
            'usuarios' => $this->getUsuariosStaff(),
            'currentUserId' => $currentUser->id, // Para identificar "(yo)" en la vista
            'prioridades' => Tarea::PRIORIDADES,
            'estados' => Tarea::ESTADOS
        ];

        return view('admin/tareas/create', $data);
    }

    /**
     * Procesar creación de nueva tarea
     */
    public function store()
    {
        // Debug: Log datos recibidos
        log_message('info', '[TAREAS_DEBUG] Iniciando store() - Datos POST: ' . json_encode($this->request->getPost()));
        
        $rules = [
            'titulo' => 'required|max_length[255]',
            'descripcion' => 'permit_empty|max_length[1000]',
            'prioridad' => 'required|in_list[baja,media,alta,urgente]',
            'asignado_a' => 'required|integer|is_not_unique[users.id]',
            'fecha_vencimiento' => 'permit_empty|valid_date[Y-m-d]'
        ];

        log_message('info', '[TAREAS_DEBUG] Reglas de validación configuradas');

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('error', '[TAREAS_DEBUG] Validación falló: ' . json_encode($errors));
            
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $errors);
        }
        
        log_message('info', '[TAREAS_DEBUG] Validación exitosa');

        $user = auth()->user();
        log_message('info', '[TAREAS_DEBUG] Usuario actual ID: ' . $user->id);
        
        $tareaData = [
            'titulo' => $this->request->getPost('titulo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'prioridad' => $this->request->getPost('prioridad'),
            'asignado_por' => $user->id,
            'asignado_a' => $this->request->getPost('asignado_a'),
            'comentarios_admin' => $this->request->getPost('comentarios_admin'),
            'estado' => 'pendiente',
            'progreso' => 0
        ];

        // Procesar fecha de vencimiento (solo fecha, sin hora)
        $fechaVencimiento = $this->request->getPost('fecha_vencimiento');
        if (!empty($fechaVencimiento)) {
            // Para tipo DATE solo necesitamos formato Y-m-d
            $tareaData['fecha_vencimiento'] = $fechaVencimiento;
            log_message('info', '[TAREAS_DEBUG] Fecha de vencimiento: ' . $fechaVencimiento);
        }

        log_message('info', '[TAREAS_DEBUG] Datos para insertar: ' . json_encode($tareaData));

        try {
            $tareaId = $this->tareaModel->insert($tareaData);
            log_message('info', '[TAREAS_DEBUG] Resultado insert: ' . ($tareaId ? $tareaId : 'FALSE'));
            
            if (!$tareaId) {
                $errors = $this->tareaModel->errors();
                log_message('error', '[TAREAS_DEBUG] Errores del modelo: ' . json_encode($errors));
            }

            if ($tareaId) {
                log_message('info', '[TAREAS_DEBUG] Tarea creada con ID: ' . $tareaId);
                
                // Registrar en historial
                try {
                    $this->historialModel->registrarCreacion($tareaId, $user->id);
                    log_message('info', '[TAREAS_DEBUG] Historial registrado exitosamente');
                } catch (\Exception $e) {
                    log_message('error', '[TAREAS_DEBUG] Error registrando historial: ' . $e->getMessage());
                }
                
                log_message('info', '[TAREAS_DEBUG] Redirigiendo con success');
                return redirect()->to('/admin/tareas')
                               ->with('success', 'Tarea creada y asignada exitosamente');
            }
        } catch (\Exception $e) {
            log_message('error', '[TAREAS_DEBUG] Exception en insert: ' . $e->getMessage());
            log_message('error', '[TAREAS_DEBUG] Stack trace: ' . $e->getTraceAsString());
        }

        log_message('error', '[TAREAS_DEBUG] Insert falló, redirigiendo con error');
        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Error al crear la tarea');
    }

    /**
     * Ver detalles de una tarea específica
     */
    public function show($id)
    {
        $tarea = $this->tareaModel->find($id);
        
        if (!$tarea) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Tarea no encontrada');
        }

        $user = auth()->user();
        
        // Verificar permisos (solo el creador puede ver)
        if ($tarea->asignado_por !== $user->id) {
            return redirect()->to('/admin/tareas')
                           ->with('error', 'No tienes permisos para ver esta tarea');
        }

        $data = [
            'titulo' => 'Detalle de Tarea: ' . $tarea->titulo,
            'tarea' => $tarea,
            'historial' => $this->historialModel->getHistorialTarea($id),
            'asignado_por' => $this->userModel->find($tarea->asignado_por),
            'asignado_a' => $this->userModel->find($tarea->asignado_a)
        ];

        return view('admin/tareas/show', $data);
    }

    /**
     * Vista para editar tarea
     */
    public function edit($id)
    {
        $tarea = $this->tareaModel->find($id);
        
        if (!$tarea) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Tarea no encontrada');
        }

        $user = auth()->user();
        
        // Verificar permisos
        if ($tarea->asignado_por !== $user->id) {
            return redirect()->to('/admin/tareas')
                           ->with('error', 'No tienes permisos para editar esta tarea');
        }

        $data = [
            'titulo' => 'Editar Tarea',
            'tarea' => $tarea,
            'usuarios' => $this->userModel->where('active', 1)->findAll(),
            'prioridades' => Tarea::PRIORIDADES,
            'estados' => Tarea::ESTADOS
        ];

        return view('admin/tareas/edit', $data);
    }

    /**
     * Procesar actualización de tarea
     */
    public function update($id)
    {
        $tarea = $this->tareaModel->find($id);
        
        if (!$tarea) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Tarea no encontrada');
        }

        $user = auth()->user();
        
        // Verificar permisos
        if ($tarea->asignado_por !== $user->id) {
            return redirect()->to('/admin/tareas')
                           ->with('error', 'No tienes permisos para editar esta tarea');
        }

        $rules = [
            'titulo' => 'required|max_length[255]',
            'descripcion' => 'permit_empty|max_length[1000]',
            'prioridad' => 'required|in_list[baja,media,alta,urgente]',
            'estado' => 'required|in_list[pendiente,en_proceso,completada,cancelada]',
            'asignado_a' => 'required|integer|is_not_unique[users.id]',
            'fecha_vencimiento' => 'permit_empty|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $estadoAnterior = $tarea->estado;
        
        $updateData = [
            'titulo' => $this->request->getPost('titulo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'prioridad' => $this->request->getPost('prioridad'),
            'estado' => $this->request->getPost('estado'),
            'asignado_a' => $this->request->getPost('asignado_a'),
            'comentarios_admin' => $this->request->getPost('comentarios_admin')
        ];

        // Procesar fecha de vencimiento (solo fecha, sin hora)
        $fechaVencimiento = $this->request->getPost('fecha_vencimiento');
        if (!empty($fechaVencimiento)) {
            // Para tipo DATE solo necesitamos formato Y-m-d
            $updateData['fecha_vencimiento'] = $fechaVencimiento;
        }

        // ===== SINCRONIZACIÓN BIDIRECCIONAL: ESTADO → PROGRESO =====
        
        // Si se marca como completada, progreso = 100%
        if ($updateData['estado'] === 'completada' && $estadoAnterior !== 'completada') {
            $updateData['fecha_completada'] = date('Y-m-d H:i:s');
            $updateData['progreso'] = 100;
            log_message('info', "[SYNC] Estado: completada → Progreso: 100% para tarea {$id}");
        }
        // Si se marca como cancelada, progreso = 0%
        elseif ($updateData['estado'] === 'cancelada' && $estadoAnterior !== 'cancelada') {
            $updateData['progreso'] = 0;
            $updateData['fecha_completada'] = date('Y-m-d H:i:s');
            log_message('info', "[SYNC] Estado: cancelada → Progreso: 0% para tarea {$id}");
        }
        // Para pendiente y en_proceso, mantener el progreso actual (no se sincroniza)
        // Esto permite flexibilidad: una tarea puede estar "en_proceso" con cualquier progreso

        if ($this->tareaModel->update($id, $updateData)) {
            // Registrar cambio de estado si aplica
            if ($estadoAnterior !== $updateData['estado']) {
                $this->historialModel->registrarCambioEstado(
                    $id, 
                    $user->id, 
                    $estadoAnterior, 
                    $updateData['estado'],
                    'Actualizado por administrador'
                );
            }
            
            return redirect()->to('/admin/tareas')
                           ->with('success', 'Tarea actualizada exitosamente');
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Error al actualizar la tarea');
    }

    /**
     * Eliminar tarea (solo admin creador)
     */
    public function delete($id)
    {
        $tarea = $this->tareaModel->find($id);
        
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tarea no encontrada']);
        }

        $user = auth()->user();
        
        // Verificar permisos
        if ($tarea->asignado_por !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos para eliminar esta tarea']);
        }

        if ($this->tareaModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Tarea eliminada exitosamente']);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al eliminar la tarea']);
    }

    /**
     * Cambiar estado de tarea via AJAX
     */
    public function cambiarEstado()
    {
        log_message('info', '[CAMBIAR_ESTADO_DEBUG] Método llamado');
        log_message('info', '[CAMBIAR_ESTADO_DEBUG] Request method: ' . $this->request->getMethod());
        log_message('info', '[CAMBIAR_ESTADO_DEBUG] Es AJAX: ' . ($this->request->isAJAX() ? 'SI' : 'NO'));
        log_message('info', '[CAMBIAR_ESTADO_DEBUG] POST data: ' . json_encode($this->request->getPost()));
        log_message('info', '[CAMBIAR_ESTADO_DEBUG] Headers: ' . json_encode($this->request->headers()));
        
        // Return success for testing even if not AJAX
        if (!$this->request->isAJAX()) {
            log_message('error', '[CAMBIAR_ESTADO_DEBUG] No es una solicitud AJAX - pero continuando para testing');
            // Don't return early for debugging
            // return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $tareaId = $this->request->getPost('tarea_id');
        $nuevoEstado = $this->request->getPost('estado');
        $comentario = $this->request->getPost('comentario') ?: '';

        $tarea = $this->tareaModel->find($tareaId);
        
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tarea no encontrada']);
        }

        $user = auth()->user();
        
        // Verificar permisos
        if ($tarea->asignado_por !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos']);
        }

        $estadoAnterior = $tarea->estado;
        
        $updateData = ['estado' => $nuevoEstado];
        
        // ===== SINCRONIZACIÓN BIDIRECCIONAL: ESTADO → PROGRESO =====
        if ($nuevoEstado === 'completada') {
            $updateData['fecha_completada'] = date('Y-m-d H:i:s');
            $updateData['progreso'] = 100;
            log_message('info', "[SYNC] Estado AJAX: completada → Progreso: 100% para tarea {$tareaId}");
        } elseif ($nuevoEstado === 'cancelada') {
            $updateData['fecha_completada'] = date('Y-m-d H:i:s');
            $updateData['progreso'] = 0;
            log_message('info', "[SYNC] Estado AJAX: cancelada → Progreso: 0% para tarea {$tareaId}");
        }
        // pendiente y en_proceso mantienen su progreso actual

        if (!empty($comentario)) {
            $updateData['comentarios_admin'] = $comentario;
        }

        if ($this->tareaModel->update($tareaId, $updateData)) {
            // Registrar en historial
            $this->historialModel->registrarCambioEstado(
                $tareaId, 
                $user->id, 
                $estadoAnterior, 
                $nuevoEstado, 
                $comentario
            );
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Estado actualizado exitosamente',
                'nuevo_estado' => $nuevoEstado
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al actualizar el estado']);
    }

    /**
     * Obtener estadísticas para dashboard
     */
    public function estadisticas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $user = auth()->user();
        
        $estadisticas = $this->tareaModel->getEstadisticas($user->id, true);
        $tareasVencidas = count($this->tareaModel->getTareasVencidas());
        $tareasProximasVencer = count($this->tareaModel->getTareasProximasVencer());

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'estadisticas' => $estadisticas,
                'vencidas' => $tareasVencidas,
                'proximas_vencer' => $tareasProximasVencer
            ]
        ]);
    }

    /**
     * Buscar tareas via AJAX
     */
    public function buscar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $filtros = [
            'buscar' => $this->request->getPost('buscar'),
            'estado' => $this->request->getPost('estado'),
            'prioridad' => $this->request->getPost('prioridad'),
            'asignado_a' => $this->request->getPost('asignado_a'),
            'fecha_desde' => $this->request->getPost('fecha_desde'),
            'fecha_hasta' => $this->request->getPost('fecha_hasta')
        ];

        // Limpiar filtros vacíos
        $filtros = array_filter($filtros);

        // Solo tareas creadas por este admin
        $user = auth()->user();
        $filtros['asignado_por'] = $user->id;

        $tareas = $this->tareaModel->buscarTareas($filtros);

        return $this->response->setJSON([
            'success' => true,
            'data' => $tareas
        ]);
    }

    /**
     * Actualizar progreso de tarea via AJAX
     */
    public function actualizarProgreso()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $tareaId = $this->request->getPost('tarea_id');
        $progreso = (int) $this->request->getPost('progreso');
        $comentario = $this->request->getPost('comentario') ?: '';

        // Validar progreso
        if ($progreso < 0 || $progreso > 100) {
            return $this->response->setJSON(['success' => false, 'error' => 'El progreso debe estar entre 0 y 100']);
        }

        $tarea = $this->tareaModel->find($tareaId);
        
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tarea no encontrada']);
        }

        $user = auth()->user();
        
        // Verificar permisos (solo quien creó la tarea puede actualizar progreso)
        if ($tarea->asignado_por !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos para actualizar esta tarea']);
        }

        $progresoAnterior = $tarea->progreso;
        $estadoAnterior = $tarea->estado;

        // Actualizar progreso usando entity
        $tarea->actualizarProgreso($progreso);
        
        // ===== SINCRONIZACIÓN BIDIRECCIONAL: PROGRESO ↔ ESTADO =====
        
        // Lógica de sincronización automática según progreso
        if ($progreso === 100 && $estadoAnterior !== 'completada' && $estadoAnterior !== 'cancelada') {
            // 100% → Completada (solo si no estaba completada/cancelada)
            $tarea->estado = 'completada';
            $tarea->fecha_completada = date('Y-m-d H:i:s');
            log_message('info', "[SYNC] Progreso 100% → Estado: completada para tarea {$tareaId}");
        } elseif ($progreso >= 25 && $progreso <= 75 && $estadoAnterior === 'completada') {
            // Bajar de 100% a 25-75% → En Proceso (solo si estaba completada)
            $tarea->estado = 'en_proceso';
            $tarea->fecha_completada = null; // Quitar fecha de completada
            log_message('info', "[SYNC] Progreso {$progreso}% (bajó de 100%) → Estado: en_proceso para tarea {$tareaId}");
        } elseif ($progreso === 0 && ($estadoAnterior === 'completada' || $estadoAnterior === 'en_proceso')) {
            // 0% → Pendiente (solo si estaba completada o en proceso)
            $tarea->estado = 'pendiente';
            $tarea->fecha_completada = null; // Quitar fecha de completada
            log_message('info', "[SYNC] Progreso 0% → Estado: pendiente para tarea {$tareaId}");
        }
        
        // Agregar comentario del administrador si se proporciona
        if (!empty($comentario)) {
            $tarea->comentarios_admin = $comentario;
        }

        if ($this->tareaModel->save($tarea)) {
            // Registrar cambio de progreso en historial
            $this->historialModel->registrarActualizacionProgreso(
                $tareaId, 
                $user->id, 
                $progresoAnterior, 
                $progreso, 
                $comentario
            );

            // Si cambió el estado, registrarlo también
            if ($estadoAnterior !== $tarea->estado) {
                $this->historialModel->registrarCambioEstado(
                    $tareaId, 
                    $user->id, 
                    $estadoAnterior, 
                    $tarea->estado,
                    'Cambio automático por actualización de progreso'
                );
            }
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Progreso actualizado exitosamente',
                'nuevo_progreso' => $progreso,
                'nuevo_estado' => $tarea->estado
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al actualizar el progreso']);
    }

    /**
     * Obtener usuarios del staff para asignación de tareas
     */
    private function getUsuariosStaff(): array
    {
        // Siempre incluir TODOS los usuarios staff activos (incluyendo el usuario actual)
        $usuarios = $this->staffModel->select('users.id, users.username, 
                                             CONCAT(staff.nombres, " ", IFNULL(staff.apellido_paterno, ""), " ", IFNULL(staff.apellido_materno, "")) as nombre_completo,
                                             staff.email, staff.tipo')
                                    ->join('users', 'users.id = staff.user_id', 'inner')
                                    ->where('users.active', 1)
                                    ->where('staff.activo', 1)
                                    ->orderBy('staff.nombres', 'ASC')
                                    ->findAll();
                                    
        // Log para debug: verificar cuántos usuarios se encontraron
        log_message('info', '[TAREAS_DEBUG] Usuarios staff encontrados: ' . count($usuarios));
        foreach ($usuarios as $user) {
            log_message('info', "[TAREAS_DEBUG] Usuario: ID={$user->id}, Username={$user->username}, Nombre={$user->nombre_completo}");
        }
        
        return $usuarios;
    }
}