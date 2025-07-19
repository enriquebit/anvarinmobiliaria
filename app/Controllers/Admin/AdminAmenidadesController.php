<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AmenidadModel;
use App\Models\LoteAmenidadModel;

class AdminAmenidadesController extends BaseController
{
    protected $amenidadModel;
    protected $loteAmenidadModel;

    public function __construct()
    {
        $this->amenidadModel = new AmenidadModel();
        $this->loteAmenidadModel = new LoteAmenidadModel();
    }

    /**
     * Página principal de amenidades
     */
    public function index()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/dashboard')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Gestión de Amenidades',
            'amenidades' => $this->amenidadModel->getAmenidadesConConteo()
        ];

        return view('admin/catalogos/amenidades/index', $data);
    }

    /**
     * Mostrar formulario para crear nueva amenidad
     */
    public function create()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/amenidades')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Nueva Amenidad',
            'amenidad' => null
        ];

        return view('admin/catalogos/amenidades/form', $data);
    }

    /**
     * Mostrar formulario para editar amenidad
     */
    public function edit($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/catalogos/amenidades')->with('error', 'Acceso denegado');
        }

        $amenidad = $this->amenidadModel->find($id);
        if (!$amenidad) {
            return redirect()->to('/admin/catalogos/amenidades')->with('error', 'Amenidad no encontrada');
        }

        $data = [
            'titulo' => 'Editar Amenidad: ' . $amenidad->nombre,
            'amenidad' => $amenidad
        ];

        return view('admin/catalogos/amenidades/form', $data);
    }

    /**
     * Guardar nueva amenidad
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/amenidades');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion'),
                'icono' => $this->request->getPost('icono') ?: 'fas fa-home',
                'activo' => $this->request->getPost('activo') ? true : false
            ];

            if (!$this->amenidadModel->insert($datos)) {
                throw new \Exception('Error al crear la amenidad: ' . implode(', ', $this->amenidadModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Amenidad creada exitosamente',
                'id' => $this->amenidadModel->getInsertID()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar amenidad existente
     */
    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/amenidades');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $amenidad = $this->amenidadModel->find($id);
            if (!$amenidad) {
                return $this->response->setJSON(['success' => false, 'message' => 'Amenidad no encontrada']);
            }

            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion'),
                'icono' => $this->request->getPost('icono') ?: 'fas fa-home',
                'activo' => $this->request->getPost('activo') ? true : false
            ];

            if (!$this->amenidadModel->update($id, $datos)) {
                throw new \Exception('Error al actualizar la amenidad: ' . implode(', ', $this->amenidadModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Amenidad actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cambiar estado de amenidad
     */
    public function cambiarEstado($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/catalogos/amenidades');
        }

        if (!isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo superadministradores pueden cambiar estados']);
        }

        try {
            $amenidad = $this->amenidadModel->find($id);
            if (!$amenidad) {
                return $this->response->setJSON(['success' => false, 'message' => 'Amenidad no encontrada']);
            }

            $nuevoEstado = $this->request->getPost('activo') ? true : false;
            
            if (!$nuevoEstado && !$amenidad->puedeSerEliminada()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se puede desactivar una amenidad que tiene lotes asignados'
                ]);
            }

            $this->amenidadModel->update($id, ['activo' => $nuevoEstado]);

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
     * Obtener amenidades via AJAX (listado y una específica)
     */
    public function obtenerAmenidades($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        // Si se pasa un ID, obtener una amenidad específica
        if ($id) {
            try {
                $amenidad = $this->amenidadModel->find($id);
                if (!$amenidad) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Amenidad no encontrada']);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'data' => [
                        'id' => $amenidad->id,
                        'nombre' => $amenidad->nombre,
                        'descripcion' => $amenidad->descripcion,
                        'icono' => $amenidad->icono,
                        'activo' => $amenidad->activo
                    ]
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }

        try {
            $amenidades = $this->amenidadModel->getAmenidadesConConteo();

            $data = [];
            foreach ($amenidades as $amenidad) {
                $data[] = [
                    'id' => $amenidad['id'],
                    'nombre' => $amenidad['nombre'],
                    'descripcion' => $amenidad['descripcion'] ?: 'Sin descripción',
                    'icono' => $amenidad['icono'] ?: 'fas fa-home',
                    'total_lotes' => $amenidad['lotes_count'],
                    'activo' => (bool)$amenidad['activo'],
                    'created_at' => date('d/m/Y', strtotime($amenidad['created_at']))
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener amenidades: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener amenidades populares para dashboard
     */
    public function obtenerPopulares()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $amenidades = $this->amenidadModel->getAmenidadesPopulares(10);

            return $this->response->setJSON([
                'success' => true,
                'data' => $amenidades
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener amenidades populares: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar botones de acción para la tabla
     */
    private function generarBotonesAccion($amenidad): string
    {
        $botones = '<div class="btn-group" role="group">';
        
        // Editar
        if ($amenidad['activo']) {
            $botones .= '<a href="' . base_url('admin/catalogos/amenidades/editar/' . $amenidad['id']) . '" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                         </a>';
        }
        
        // Cambiar estado (solo superadmin)
        if (isSuperAdmin()) {
            if ($amenidad['activo']) {
                $botones .= '<button class="btn btn-sm btn-danger btn-cambiar-estado" data-id="' . $amenidad['id'] . '" title="Desactivar">
                                <i class="fas fa-ban"></i>
                             </button>';
            } else {
                $botones .= '<button class="btn btn-sm btn-success btn-cambiar-estado" data-id="' . $amenidad['id'] . '" title="Activar">
                                <i class="fas fa-check"></i>
                             </button>';
            }
        }
        
        $botones .= '</div>';
        
        return $botones;
    }

    /**
     * Duplicar amenidad
     */
    public function duplicate($id)
    {
        if (!isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado'
            ]);
        }

        $amenidadOriginal = $this->amenidadModel->find($id);
        
        if (!$amenidadOriginal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Amenidad no encontrada'
            ]);
        }

        // Crear copia con nombre modificado
        $datosNueva = [
            'nombre' => $amenidadOriginal->nombre . ' (Copia)',
            'clase' => $amenidadOriginal->clase,
            'icono' => $amenidadOriginal->icono,
            'descripcion' => $amenidadOriginal->descripcion,
            'color' => $amenidadOriginal->color,
            'orden' => $amenidadOriginal->orden,
            'activo' => 1
        ];

        if ($nuevaId = $this->amenidadModel->insert($datosNueva)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Amenidad duplicada exitosamente',
                'nueva_id' => $nuevaId
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al duplicar la amenidad'
            ]);
        }
    }

    /**
     * Eliminar amenidad con validaciones
     */
    public function delete($id)
    {
        if (!isSuperAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo el superadministrador puede eliminar amenidades'
            ]);
        }

        $amenidad = $this->amenidadModel->find($id);
        
        if (!$amenidad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Amenidad no encontrada'
            ]);
        }

        // Verificar si tiene lotes asociados
        $lotesAsociados = $this->loteAmenidadModel->where('amenidades_id', $id)
                                                 ->countAllResults();

        if ($lotesAsociados > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No se puede eliminar la amenidad porque tiene {$lotesAsociados} lote(s) asociado(s). Primero debe desactivarla."
            ]);
        }

        // Verificar que esté desactivada
        if ($amenidad->activo == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Para eliminar la amenidad, primero debe desactivarla'
            ]);
        }

        // Eliminar definitivamente
        if ($this->amenidadModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Amenidad eliminada definitivamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar la amenidad'
            ]);
        }
    }
}