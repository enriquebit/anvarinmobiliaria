<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TipoLoteModel;

class AdminTiposLotesController extends BaseController
{
    protected $tipoLoteModel;

    public function __construct()
    {
        $this->tipoLoteModel = new TipoLoteModel();
    }

    /**
     * Página principal de tipos de lotes
     */
    public function index()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Gestión de Tipos de Lotes',
            'tipos' => $this->tipoLoteModel->getTiposConConteo()
        ];

        return view('admin/catalogos/tipos-lotes/index', $data);
    }

    /**
     * Mostrar formulario para crear nuevo tipo
     */
    public function crear()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/tipos-lotes')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Nuevo Tipo de Lote',
            'tipo' => null
        ];

        return view('admin/catalogos/tipos-lotes/form', $data);
    }

    /**
     * Mostrar formulario para editar tipo
     */
    public function editar($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/tipos-lotes')->with('error', 'Acceso denegado');
        }

        $tipo = $this->tipoLoteModel->find($id);
        if (!$tipo) {
            return redirect()->to('/admin/catalogos/tipos-lotes')->with('error', 'Tipo no encontrado');
        }

        $data = [
            'titulo' => 'Editar Tipo: ' . $tipo->nombre,
            'tipo' => $tipo
        ];

        return view('admin/catalogos/tipos-lotes/form', $data);
    }

    /**
     * Guardar nuevo tipo
     */
    public function guardar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/tipos-lotes');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion')
            ];

            if (!$this->tipoLoteModel->insert($datos)) {
                throw new \Exception('Error al crear el tipo: ' . implode(', ', $this->tipoLoteModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tipo creado exitosamente',
                'id' => $this->tipoLoteModel->getInsertID()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar tipo existente
     */
    public function actualizar($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/tipos-lotes');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $tipo = $this->tipoLoteModel->find($id);
            if (!$tipo) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tipo no encontrado']);
            }

            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion')
            ];

            if (!$this->tipoLoteModel->update($id, $datos)) {
                throw new \Exception('Error al actualizar el tipo: ' . implode(', ', $this->tipoLoteModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tipo actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cambiar estado de tipo
     */
    public function cambiarEstado($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/tipos-lotes');
        }

        if (!isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo superadministradores pueden cambiar estados']);
        }

        try {
            $tipo = $this->tipoLoteModel->find($id);
            if (!$tipo) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tipo no encontrado']);
            }

            $nuevoEstado = !$tipo->activo;
            
            if (!$nuevoEstado && !$tipo->puedeSerEliminado()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se puede desactivar un tipo que tiene lotes asignados'
                ]);
            }

            $this->tipoLoteModel->update($id, ['activo' => $nuevoEstado]);

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
     * Obtener tipos via AJAX
     */
    public function obtenerTipos()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $tipos = $this->tipoLoteModel->getTiposConConteo();

            $data = [];
            foreach ($tipos as $tipo) {
                $data[] = [
                    'id' => $tipo['id'],
                    'nombre' => $tipo['nombre'],
                    'descripcion' => $tipo['descripcion'] ?: 'Sin descripción',
                    'lotes_count' => $tipo['lotes_count'],
                    'activo' => $tipo['activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>',
                    'created_at' => date('d/m/Y', strtotime($tipo['created_at'])),
                    'acciones' => $this->generarBotonesAccion($tipo)
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener tipos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar botones de acción para la tabla
     */
    private function generarBotonesAccion($tipo): string
    {
        $botones = '<div class="btn-group" role="group">';
        
        // Editar
        if ($tipo['activo']) {
            $botones .= '<a href="' . base_url('admin/catalogos/tipos-lotes/edit/' . $tipo['id']) . '" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                         </a>';
        }
        
        // Cambiar estado (solo superadmin)
        if (isSuperAdmin()) {
            if ($tipo['activo']) {
                $botones .= '<button class="btn btn-sm btn-danger btn-cambiar-estado" data-id="' . $tipo['id'] . '" title="Desactivar">
                                <i class="fas fa-ban"></i>
                             </button>';
            } else {
                $botones .= '<button class="btn btn-sm btn-success btn-cambiar-estado" data-id="' . $tipo['id'] . '" title="Activar">
                                <i class="fas fa-check"></i>
                             </button>';
            }
        }
        
        $botones .= '</div>';
        
        return $botones;
    }

    // =========================================
    // MÉTODOS ESTÁNDAR (REST CONVENTIONS)
    // =========================================

    /**
     * Alias estándar para crear()
     */
    public function create()
    {
        return $this->crear();
    }

    /**
     * Alias estándar para editar()
     */
    public function edit($id)
    {
        return $this->editar($id);
    }

    /**
     * Alias estándar para guardar()
     */
    public function store()
    {
        return $this->guardar();
    }

    /**
     * Alias estándar para actualizar()
     */
    public function update($id)
    {
        return $this->actualizar($id);
    }
}