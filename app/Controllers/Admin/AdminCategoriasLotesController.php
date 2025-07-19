<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoriaLoteModel;

class AdminCategoriasLotesController extends BaseController
{
    protected $categoriaLoteModel;

    public function __construct()
    {
        $this->categoriaLoteModel = new CategoriaLoteModel();
    }

    /**
     * Página principal de categorías de lotes
     */
    public function index()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Gestión de Categorías de Lotes',
            'categorias' => $this->categoriaLoteModel->getCategoriasConConteo()
        ];

        return view('admin/catalogos/categorias-lotes/index', $data);
    }

    /**
     * Mostrar formulario para crear nueva categoría
     */
    public function create()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/categorias-lotes')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Nueva Categoría de Lote',
            'categoria' => null
        ];

        return view('admin/catalogos/categorias-lotes/create', $data);
    }

    /**
     * Mostrar formulario para editar categoría
     */
    public function edit($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/categorias-lotes')->with('error', 'Acceso denegado');
        }

        $categoria = $this->categoriaLoteModel->find($id);
        if (!$categoria) {
            return redirect()->to('/admin/catalogos/categorias-lotes')->with('error', 'Categoría no encontrada');
        }

        $data = [
            'titulo' => 'Editar Categoría: ' . $categoria->nombre,
            'categoria' => $categoria
        ];

        return view('admin/catalogos/categorias-lotes/edit', $data);
    }

    /**
     * Guardar nueva categoría
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/categorias-lotes');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion')
            ];

            if (!$this->categoriaLoteModel->insert($datos)) {
                throw new \Exception('Error al crear la categoría: ' . implode(', ', $this->categoriaLoteModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'id' => $this->categoriaLoteModel->getInsertID()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar categoría existente
     */
    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/categorias-lotes');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $categoria = $this->categoriaLoteModel->find($id);
            if (!$categoria) {
                return $this->response->setJSON(['success' => false, 'message' => 'Categoría no encontrada']);
            }

            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion')
            ];

            if (!$this->categoriaLoteModel->update($id, $datos)) {
                throw new \Exception('Error al actualizar la categoría: ' . implode(', ', $this->categoriaLoteModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cambiar estado de categoría
     */
    public function cambiarEstado($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/categorias-lotes');
        }

        if (!isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo superadministradores pueden cambiar estados']);
        }

        try {
            $categoria = $this->categoriaLoteModel->find($id);
            if (!$categoria) {
                return $this->response->setJSON(['success' => false, 'message' => 'Categoría no encontrada']);
            }

            $nuevoEstado = !$categoria->activo;
            
            if (!$nuevoEstado && !$categoria->puedeSerEliminada()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se puede desactivar una categoría que tiene lotes asignados'
                ]);
            }

            $this->categoriaLoteModel->update($id, ['activo' => $nuevoEstado]);

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
     * Obtener categorías via AJAX
     */
    public function obtenerCategorias()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $categorias = $this->categoriaLoteModel->getCategoriasConConteo();

            $data = [];
            foreach ($categorias as $categoria) {
                $data[] = [
                    'id' => $categoria['id'],
                    'nombre' => $categoria['nombre'],
                    'descripcion' => $categoria['descripcion'] ?: 'Sin descripción',
                    'lotes_count' => $categoria['lotes_count'],
                    'activo' => $categoria['activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>',
                    'created_at' => date('d/m/Y', strtotime($categoria['created_at'])),
                    'acciones' => $this->generarBotonesAccion($categoria)
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar botones de acción para la tabla
     */
    private function generarBotonesAccion($categoria): string
    {
        $botones = '<div class="btn-group" role="group">';
        
        // Editar
        if ($categoria['activo']) {
            $botones .= '<a href="' . base_url('admin/catalogos/categorias-lotes/edit/' . $categoria['id']) . '" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                         </a>';
        }
        
        // Cambiar estado (solo superadmin)
        if (isSuperAdmin()) {
            if ($categoria['activo']) {
                $botones .= '<button class="btn btn-sm btn-danger btn-cambiar-estado" data-id="' . $categoria['id'] . '" title="Desactivar">
                                <i class="fas fa-ban"></i>
                             </button>';
            } else {
                $botones .= '<button class="btn btn-sm btn-success btn-cambiar-estado" data-id="' . $categoria['id'] . '" title="Activar">
                                <i class="fas fa-check"></i>
                             </button>';
            }
        }
        
        $botones .= '</div>';
        
        return $botones;
    }
}