<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TareaModel;
use App\Models\TareaHistorialModel;
use App\Entities\Tarea;

/**
 * Controlador de Tareas para Staff/Usuarios en Admin
 * 
 * Permite a los miembros del staff ver y actualizar sus tareas asignadas
 * siguiendo la metodología Entity-First
 */
class StaffTareasController extends BaseController
{
    protected $tareaModel;
    protected $historialModel;

    public function __construct()
    {
        $this->tareaModel = new TareaModel();
        $this->historialModel = new TareaHistorialModel();
    }

    /**
     * Vista principal de tareas del usuario con reglas de visibilidad estrictas
     */
    public function index()
    {
        $user = auth()->user();
        
        // Obtener filtros
        $filtros = [
            'estado' => $this->request->getGet('estado'),
            'prioridad' => $this->request->getGet('prioridad'),
            'tipo' => $this->request->getGet('tipo') ?: 'todas', // propias, asignadas, todas
            'periodo' => $this->request->getGet('periodo') ?: 'este_mes'
        ];

        // Limpiar filtros vacíos pero mantener 'este_mes' como default
        $filtrosLimpios = array_filter($filtros, function($value) {
            return $value !== '' && $value !== null;
        });

        $tareasPersonales = $this->tareaModel->getMisTareasPersonales($user->id, $filtrosLimpios);
        $tareasAsignadas = $this->tareaModel->getTareasAsignadasAMi($user->id, $filtrosLimpios);
        $tareasQueAsigne = $this->tareaModel->getTareasQueAsigneAOtros($user->id, $filtrosLimpios);
        
        

        $data = [
            'titulo' => 'Mis Tareas',
            'tareasPersonales' => $tareasPersonales,
            'tareasAsignadas' => $tareasAsignadas,
            'tareasQueAsigne' => $tareasQueAsigne,
            'currentUserId' => $user->id, // Para verificar permisos de eliminación
            'estadisticas' => $this->tareaModel->getEstadisticasMisTareas($user->id),
            'filtros' => $filtros,
            'estados' => Tarea::ESTADOS,
            'prioridades' => Tarea::PRIORIDADES,
            'tareasVencidas' => $this->tareaModel->getTareasVencidas($user->id),
            'tareasProximasVencer' => $this->tareaModel->getTareasProximasVencer($user->id)
        ];

        return view('admin/tareas/mis-tareas/index', $data);
    }

    /**
     * Ver detalles de una tarea (solo si es propia o asignada a mí)
     */
    public function show($id)
    {
        $tarea = $this->tareaModel->find($id);
        
        if (!$tarea) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Tarea no encontrada');
        }

        $user = auth()->user();
        
        // Verificar permisos: solo propias o asignadas a mí
        if ($tarea->asignado_por !== $user->id && $tarea->asignado_a !== $user->id) {
            return redirect()->to('/admin/tareas/mis-tareas')
                           ->with('error', 'No tienes permisos para ver esta tarea');
        }

        $data = [
            'titulo' => 'Detalle de Tarea: ' . $tarea->titulo,
            'tarea' => $tarea,
            'historial' => $this->historialModel->getHistorialTarea($id),
            'esPropia' => $tarea->asignado_por === $user->id,
            'esAsignada' => $tarea->asignado_a === $user->id && $tarea->asignado_por !== $user->id
        ];

        return view('admin/tareas/mis-tareas/show', $data);
    }

    /**
     * Vista para crear nueva tarea personal
     */
    public function create()
    {
        $data = [
            'titulo' => 'Crear Nueva Tarea Personal',
            'prioridades' => Tarea::PRIORIDADES
        ];

        return view('admin/tareas/mis-tareas/create', $data);
    }

    /**
     * Procesar creación de nueva tarea personal
     */
    public function store()
    {
        $rules = [
            'titulo' => 'required|max_length[255]',
            'descripcion' => 'permit_empty|max_length[1000]',
            'prioridad' => 'required|in_list[baja,media,alta,urgente]',
            'fecha_vencimiento' => 'permit_empty|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $user = auth()->user();
        
        $tareaData = [
            'titulo' => $this->request->getPost('titulo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'prioridad' => $this->request->getPost('prioridad'),
            'asignado_por' => $user->id,  // Usuario se asigna a sí mismo
            'asignado_a' => $user->id,    // Usuario se asigna a sí mismo
            'estado' => 'pendiente',
            'progreso' => 0
        ];

        // Procesar fecha de vencimiento
        $fechaVencimiento = $this->request->getPost('fecha_vencimiento');
        if (!empty($fechaVencimiento)) {
            $tareaData['fecha_vencimiento'] = $fechaVencimiento;
        }

        if ($this->tareaModel->insert($tareaData)) {
            $tareaId = $this->tareaModel->getInsertID();
            
            // Registrar en historial
            $this->historialModel->registrarCreacion($tareaId, $user->id);
            
            return redirect()->to('/admin/tareas/mis-tareas')
                           ->with('success', 'Tarea personal creada exitosamente');
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Error al crear la tarea');
    }

    /**
     * Actualizar progreso de tarea
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
        
        // Verificar permisos
        if ($tarea->asignado_a !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos']);
        }

        $progresoAnterior = $tarea->progreso;
        $estadoAnterior = $tarea->estado;

        // Actualizar progreso usando entity
        $tarea->actualizarProgreso($progreso);
        
        // Agregar comentario del usuario si se proporciona
        if (!empty($comentario)) {
            $tarea->comentarios_usuario = $comentario;
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
                    'Cambio automático por progreso'
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
     * Marcar tarea como completada
     */
    public function completar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $tareaId = $this->request->getPost('tarea_id');
        $comentario = $this->request->getPost('comentario') ?: '';

        $user = auth()->user();

        if ($this->tareaModel->completarTarea($tareaId, $user->id, $comentario)) {
            // Registrar en historial
            $this->historialModel->registrarCambioEstado(
                $tareaId, 
                $user->id, 
                'en_proceso', // Asumimos que venía de en_proceso
                'completada',
                $comentario
            );
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Tarea marcada como completada'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al completar la tarea']);
    }

    /**
     * Iniciar tarea (cambiar a en proceso)
     */
    public function iniciar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $tareaId = $this->request->getPost('tarea_id');
        $comentario = $this->request->getPost('comentario') ?: 'Tarea iniciada';

        $tarea = $this->tareaModel->find($tareaId);
        
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tarea no encontrada']);
        }

        $user = auth()->user();
        
        // Verificar permisos
        if ($tarea->asignado_a !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos']);
        }

        // Verificar que esté pendiente
        if ($tarea->estado !== 'pendiente') {
            return $this->response->setJSON(['success' => false, 'error' => 'La tarea no está pendiente']);
        }

        $estadoAnterior = $tarea->estado;
        
        // Iniciar tarea usando entity
        $tarea->iniciar();
        
        if (!empty($comentario)) {
            $tarea->comentarios_usuario = $comentario;
        }

        if ($this->tareaModel->save($tarea)) {
            // Registrar en historial
            $this->historialModel->registrarCambioEstado(
                $tareaId, 
                $user->id, 
                $estadoAnterior, 
                'en_proceso',
                $comentario
            );
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Tarea iniciada exitosamente',
                'nuevo_estado' => 'en_proceso'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al iniciar la tarea']);
    }

    /**
     * Agregar comentario a una tarea
     */
    public function agregarComentario()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $tareaId = $this->request->getPost('tarea_id');
        $comentario = trim($this->request->getPost('comentario'));

        if (empty($comentario)) {
            return $this->response->setJSON(['success' => false, 'error' => 'El comentario no puede estar vacío']);
        }

        $tarea = $this->tareaModel->find($tareaId);
        
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tarea no encontrada']);
        }

        $user = auth()->user();
        
        // Verificar permisos
        if ($tarea->asignado_a !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos']);
        }

        // Actualizar comentario
        $comentarioExistente = $tarea->comentarios_usuario;
        $nuevoComentario = $comentarioExistente ? 
            $comentarioExistente . "\n\n[" . date('d/m/Y H:i') . "] " . $comentario :
            "[" . date('d/m/Y H:i') . "] " . $comentario;

        $updateData = ['comentarios_usuario' => $nuevoComentario];

        if ($this->tareaModel->update($tareaId, $updateData)) {
            // Registrar en historial
            $this->historialModel->registrarComentario($tareaId, $user->id, $comentario);
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Comentario agregado exitosamente'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al agregar el comentario']);
    }

    /**
     * Obtener estadísticas del usuario
     */
    public function estadisticas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $user = auth()->user();
        
        $estadisticas = $this->tareaModel->getEstadisticas($user->id);
        $tareasVencidas = count($this->tareaModel->getTareasVencidas($user->id));
        $tareasProximasVencer = count($this->tareaModel->getTareasProximasVencer($user->id));

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
     * Buscar tareas del usuario
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
            'fecha_desde' => $this->request->getPost('fecha_desde'),
            'fecha_hasta' => $this->request->getPost('fecha_hasta')
        ];

        // Limpiar filtros vacíos
        $filtros = array_filter($filtros);

        // Solo tareas asignadas a este usuario
        $user = auth()->user();
        $filtros['asignado_a'] = $user->id;

        $tareas = $this->tareaModel->buscarTareas($filtros);

        return $this->response->setJSON([
            'success' => true,
            'data' => $tareas
        ]);
    }

    /**
     * Cancelar una tarea (solo para tareas que yo asigné a otros)
     */
    public function cancelar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $tareaId = $this->request->getPost('tarea_id');
        $motivo = trim($this->request->getPost('motivo') ?: 'Tarea cancelada por el creador');

        $tarea = $this->tareaModel->find($tareaId);
        
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tarea no encontrada']);
        }

        $user = auth()->user();
        
        // Solo el creador puede cancelar tareas que asignó a otros
        if ($tarea->asignado_por !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos para cancelar esta tarea']);
        }

        // No se puede cancelar si ya está completada
        if ($tarea->estado === 'completada') {
            return $this->response->setJSON(['success' => false, 'error' => 'No se puede cancelar una tarea completada']);
        }

        $estadoAnterior = $tarea->estado;
        
        // Cancelar usando entity
        $tarea->cancelar();
        
        // Agregar motivo de cancelación
        $tarea->comentarios_admin = $motivo;

        if ($this->tareaModel->save($tarea)) {
            // Registrar en historial
            $this->historialModel->registrarCambioEstado(
                $tareaId, 
                $user->id, 
                $estadoAnterior, 
                'cancelada',
                $motivo
            );
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Tarea cancelada exitosamente',
                'nuevo_estado' => 'cancelada'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al cancelar la tarea']);
    }

    /**
     * Agregar comentario de administrador a una tarea delegada
     */
    public function agregarComentarioAdmin()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $tareaId = $this->request->getPost('tarea_id');
        $comentario = trim($this->request->getPost('comentario'));

        if (empty($comentario)) {
            return $this->response->setJSON(['success' => false, 'error' => 'El comentario no puede estar vacío']);
        }

        $tarea = $this->tareaModel->find($tareaId);
        
        if (!$tarea) {
            return $this->response->setJSON(['success' => false, 'error' => 'Tarea no encontrada']);
        }

        $user = auth()->user();
        
        // Solo el creador puede agregar comentarios de admin a tareas que asignó
        if ($tarea->asignado_por !== $user->id) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permisos para comentar esta tarea']);
        }

        // Actualizar comentario de admin
        $comentarioExistente = $tarea->comentarios_admin;
        $nuevoComentario = $comentarioExistente ? 
            $comentarioExistente . "\n\n[" . date('d/m/Y H:i') . "] " . $comentario :
            "[" . date('d/m/Y H:i') . "] " . $comentario;

        $updateData = ['comentarios_admin' => $nuevoComentario];

        if ($this->tareaModel->update($tareaId, $updateData)) {
            // Registrar en historial
            $this->historialModel->registrarComentario($tareaId, $user->id, $comentario);
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Comentario agregado exitosamente'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Error al agregar el comentario']);
    }
}