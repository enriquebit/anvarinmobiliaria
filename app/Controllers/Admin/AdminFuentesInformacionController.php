<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FuenteInformacionModel;

class AdminFuentesInformacionController extends BaseController
{
    protected $fuenteInformacionModel;

    public function __construct()
    {
        $this->fuenteInformacionModel = new FuenteInformacionModel();
    }

    /**
     * Página principal de fuentes de información
     */
    public function index()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Gestión de Fuentes de Información',
            'fuentes' => $this->fuenteInformacionModel->obtenerTodos()
        ];

        return view('admin/catalogos/fuentes-informacion/index', $data);
    }

    /**
     * Mostrar formulario para crear nueva fuente
     */
    public function create()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/fuentes-informacion')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Nueva Fuente de Información',
            'fuente' => null
        ];

        return view('admin/catalogos/fuentes-informacion/form', $data);
    }

    /**
     * Mostrar formulario para editar fuente
     */
    public function edit($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/fuentes-informacion')->with('error', 'Acceso denegado');
        }

        $fuente = $this->fuenteInformacionModel->find($id);
        if (!$fuente) {
            return redirect()->to('/admin/catalogos/fuentes-informacion')->with('error', 'Fuente no encontrada');
        }

        $data = [
            'titulo' => 'Editar Fuente: ' . $fuente->nombre,
            'fuente' => $fuente
        ];

        return view('admin/catalogos/fuentes-informacion/form', $data);
    }

    /**
     * Guardar nueva fuente
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/fuentes-informacion');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'valor' => $this->request->getPost('valor'),
                'activo' => $this->request->getPost('activo') ?? 1
            ];

            // Limpiar y normalizar el valor
            $datos['valor'] = strtolower(str_replace(' ', '_', trim($datos['valor'])));

            if (!$this->fuenteInformacionModel->insert($datos)) {
                throw new \Exception('Error al crear la fuente: ' . implode(', ', $this->fuenteInformacionModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Fuente creada exitosamente',
                'id' => $this->fuenteInformacionModel->getInsertID()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar fuente existente
     */
    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/fuentes-informacion');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $fuente = $this->fuenteInformacionModel->find($id);
            if (!$fuente) {
                return $this->response->setJSON(['success' => false, 'message' => 'Fuente no encontrada']);
            }

            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'valor' => $this->request->getPost('valor'),
                'activo' => $this->request->getPost('activo') ?? 1
            ];

            // Limpiar y normalizar el valor
            $datos['valor'] = strtolower(str_replace(' ', '_', trim($datos['valor'])));

            if (!$this->fuenteInformacionModel->update($id, $datos)) {
                throw new \Exception('Error al actualizar la fuente: ' . implode(', ', $this->fuenteInformacionModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Fuente actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cambiar estado de fuente
     */
    public function cambiarEstado($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/fuentes-informacion');
        }

        if (!isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo superadministradores pueden cambiar estados']);
        }

        try {
            $fuente = $this->fuenteInformacionModel->find($id);
            if (!$fuente) {
                return $this->response->setJSON(['success' => false, 'message' => 'Fuente no encontrada']);
            }

            $nuevoEstado = !$fuente->activo;
            
            // Verificar si la fuente está siendo usada por clientes
            if (!$nuevoEstado) {
                $estadisticas = $this->fuenteInformacionModel->obtenerEstadisticasUso();
                foreach ($estadisticas as $stat) {
                    if ($stat['valor'] === $fuente->valor && $stat['total_clientes'] > 0) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'No se puede desactivar una fuente que tiene ' . $stat['total_clientes'] . ' clientes asignados'
                        ]);
                    }
                }
            }

            $this->fuenteInformacionModel->update($id, ['activo' => $nuevoEstado]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Estado cambiado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener fuentes via AJAX
     */
    public function obtenerFuentes()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $fuentes = $this->fuenteInformacionModel->obtenerTodos();
            $estadisticas = $this->fuenteInformacionModel->obtenerEstadisticasUso();
            
            // Crear un mapa de estadísticas por valor
            $statsMap = [];
            foreach ($estadisticas as $stat) {
                $statsMap[$stat['valor']] = $stat['total_clientes'];
            }

            $data = [];
            foreach ($fuentes as $fuente) {
                $clientesCount = $statsMap[$fuente->valor] ?? 0;
                
                $data[] = [
                    'id' => $fuente->id,
                    'nombre' => $fuente->nombre,
                    'valor' => $fuente->valor,
                    'clientes_count' => $clientesCount,
                    'activo' => $fuente->activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>',
                    'created_at' => $fuente->created_at ? date('d/m/Y', strtotime($fuente->created_at)) : 'N/A',
                    'acciones' => $this->generarBotonesAccion($fuente, $clientesCount)
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener fuentes: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar botones de acción para la tabla
     */
    private function generarBotonesAccion($fuente, $clientesCount = 0): string
    {
        $botones = '<div class="btn-group" role="group">';
        
        // Editar
        if ($fuente->activo) {
            $botones .= '<a href="' . base_url('admin/catalogos/fuentes-informacion/edit/' . $fuente->id) . '" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                         </a>';
        }
        
        // Cambiar estado (solo superadmin)
        if (isSuperAdmin()) {
            if ($fuente->activo) {
                if ($clientesCount == 0) {
                    $botones .= '<button class="btn btn-sm btn-danger btn-cambiar-estado" data-id="' . $fuente->id . '" title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                 </button>';
                } else {
                    $botones .= '<button class="btn btn-sm btn-secondary" title="No se puede desactivar (tiene ' . $clientesCount . ' clientes)" disabled>
                                    <i class="fas fa-ban"></i>
                                 </button>';
                }
            } else {
                $botones .= '<button class="btn btn-sm btn-success btn-cambiar-estado" data-id="' . $fuente->id . '" title="Activar">
                                <i class="fas fa-check"></i>
                             </button>';
            }
        }
        
        $botones .= '</div>';
        
        return $botones;
    }

    /**
     * Obtener estadísticas de uso
     */
    public function estadisticas()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/fuentes-informacion')->with('error', 'Acceso denegado');
        }

        try {
            $estadisticas = $this->fuenteInformacionModel->obtenerEstadisticasUso();

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => $estadisticas
                ]);
            }

            $data = [
                'titulo' => 'Estadísticas de Fuentes de Información',
                'estadisticas' => $estadisticas
            ];

            return view('admin/catalogos/fuentes-informacion/estadisticas', $data);

        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/admin/catalogos/fuentes-informacion')->with('error', 'Error al obtener estadísticas');
        }
    }
}