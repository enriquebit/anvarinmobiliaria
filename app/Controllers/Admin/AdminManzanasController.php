<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ManzanaModel;
use App\Models\ProyectoModel;

class AdminManzanasController extends BaseController
{
    protected $manzanaModel;
    protected $proyectoModel;
    
    public function __construct()
    {
        $this->manzanaModel = new ManzanaModel();
        $this->proyectoModel = new ProyectoModel();
    }

    /**
     * Vista principal - listado de manzanas
     */
    public function index()
    {
        // Verificar permisos
        if (!auth()->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'titulo' => 'Gestión de Manzanas',
            'proyectos' => $this->proyectoModel->getProyectosConEmpresa(),
            'estadisticas' => $this->manzanaModel->getEstadisticas(),
        ];

        return view('admin/manzanas/index', $data);
    }

    /**
     * AJAX: Obtener listado de manzanas con filtros
     */
    public function obtenerManzanas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $filtros = [
                'proyecto_id' => $this->request->getPost('proyecto_id'),
                'busqueda' => $this->request->getPost('busqueda'),
                'incluir_inactivas' => $this->request->getPost('incluir_inactivas') === 'true',
                'orden' => $this->request->getPost('orden') ?? 'manzanas.nombre',
                'direccion' => $this->request->getPost('direccion') ?? 'ASC',
            ];

            $manzanas = $this->manzanaModel->buscarConFiltros($filtros);

            // Formatear datos para DataTables
            $data = [];
            foreach ($manzanas as $manzana) {
                $data[] = [
                    'id' => $manzana['id'],
                    'nombre' => $manzana['nombre'],
                    'clave' => $manzana['clave'],
                    'descripcion' => $manzana['descripcion'] ?? '',
                    'proyecto' => $manzana['nombre_proyecto'] ?? '',
                    'coordenadas' => $this->formatearCoordenadas($manzana['longitud'], $manzana['latitud']),
                    'color' => $manzana['color'] ?? '#3498db',
                    'activo' => $manzana['activo'],
                    'acciones' => $this->generarBotonesAccion($manzana['id'], $manzana['activo'])
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error al obtener manzanas: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cargar las manzanas'
            ]);
        }
    }

    /**
     * Vista para agregar nueva manzana
     */
    public function create()
    {
        // Verificar permisos
        if (!auth()->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'titulo' => 'Agregar Manzana',
            'proyectos' => $this->proyectoModel->getProyectosConEmpresa(),
            'manzana' => null,
        ];

        return view('admin/manzanas/form', $data);
    }

    /**
     * Vista para editar manzana existente
     */
    public function edit(int $id)
    {
        // Verificar permisos
        if (!auth()->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $manzana = $this->manzanaModel->find($id);
        if (!$manzana) {
            return redirect()->to('/admin/manzanas')->with('error', 'Manzana no encontrada');
        }

        $data = [
            'titulo' => 'Editar Manzana',
            'proyectos' => $this->proyectoModel->getProyectosConEmpresa(),
            'manzana' => $manzana,
        ];

        return view('admin/manzanas/form', $data);
    }

    /**
     * AJAX: Guardar nueva manzana
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $datos = $this->request->getPost();
            
            // Validaciones adicionales de negocio
            $erroresNegocio = $this->manzanaModel->validarDatos($datos);
            if (!empty($erroresNegocio)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $erroresNegocio
                ]);
            }

            // Preparar datos para inserción
            $datosLimpios = [
                'nombre' => strtoupper(trim($datos['nombre'])),
                'descripcion' => trim($datos['descripcion'] ?? ''),
                'proyectos_id' => (int) $datos['proyectos_id'],
                'longitud' => !empty($datos['longitud']) ? $datos['longitud'] : null,
                'latitud' => !empty($datos['latitud']) ? $datos['latitud'] : null,
                'color' => $datos['color'] ?? null,
            ];

            // Obtener color del proyecto si no se especifica
            if (empty($datosLimpios['color'])) {
                $proyecto = $this->proyectoModel->find($datosLimpios['proyectos_id']);
                $datosLimpios['color'] = $proyecto ? $proyecto->color : '#3498db';
            }

            $id = $this->manzanaModel->insert($datosLimpios);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Manzana creada exitosamente',
                    'id' => $id
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al crear la manzana',
                    'errors' => $this->manzanaModel->errors()
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al guardar manzana: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * AJAX: Actualizar manzana existente
     */
    public function update(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $manzana = $this->manzanaModel->find($id);
            if (!$manzana) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Manzana no encontrada'
                ]);
            }

            $datos = $this->request->getPost();
            
            // Validaciones adicionales de negocio
            $erroresNegocio = $this->manzanaModel->validarDatos($datos, $id);
            if (!empty($erroresNegocio)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $erroresNegocio
                ]);
            }

            // Preparar datos para actualización
            $datosLimpios = [
                'nombre' => strtoupper(trim($datos['nombre'])),
                'descripcion' => trim($datos['descripcion'] ?? ''),
                'proyectos_id' => (int) $datos['proyectos_id'],
                'longitud' => !empty($datos['longitud']) ? $datos['longitud'] : null,
                'latitud' => !empty($datos['latitud']) ? $datos['latitud'] : null,
                'color' => $datos['color'] ?? $manzana->color,
            ];

            $resultado = $this->manzanaModel->update($id, $datosLimpios);

            if ($resultado) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Manzana actualizada exitosamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al actualizar la manzana',
                    'errors' => $this->manzanaModel->errors()
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al actualizar manzana: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * AJAX: Eliminar manzana (soft delete)
     */
    public function delete(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $manzana = $this->manzanaModel->find($id);
            if (!$manzana) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Manzana no encontrada'
                ]);
            }

            // Verificar si se puede eliminar (sin lotes asociados)
            if (!$manzana->puedeSerEliminada()) {
                $totalLotes = $this->db->table('lotes')
                                      ->where('manzanas_id', $id)
                                      ->where('activo', 1)
                                      ->countAllResults();
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "No se puede eliminar la manzana porque tiene {$totalLotes} lote(s) asociado(s)"
                ]);
            }

            $resultado = $this->manzanaModel->softDelete($id);

            if ($resultado) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Manzana eliminada exitosamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar la manzana'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al eliminar manzana: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * AJAX: Restaurar manzana eliminada
     */
    public function restaurar(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $resultado = $this->manzanaModel->restaurar($id);

            if ($resultado) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Manzana restaurada exitosamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al restaurar la manzana'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al restaurar manzana: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * AJAX: Obtener manzanas por proyecto (para selects)
     */
    public function obtenerPorProyecto(int $proyectoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $manzanas = $this->manzanaModel->getPorProyecto($proyectoId);
            
            $opciones = [];
            foreach ($manzanas as $manzana) {
                $opciones[] = [
                    'id' => $manzana->id,
                    'nombre' => $manzana->nombre,
                    'clave' => $manzana->clave,
                    'texto' => $manzana->nombre . ' (' . $manzana->clave . ')'
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $opciones
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error al obtener manzanas por proyecto: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cargar las manzanas'
            ]);
        }
    }

    /**
     * AJAX: Obtener estadísticas actualizadas
     */
    public function estadisticas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $estadisticas = $this->manzanaModel->getEstadisticas();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $estadisticas
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error al obtener estadísticas: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cargar estadísticas'
            ]);
        }
    }

    // ========================================
    // MÉTODOS PRIVADOS DE UTILIDAD
    // ========================================

    /**
     * Formatear coordenadas para mostrar
     */
    private function formatearCoordenadas(?string $longitud, ?string $latitud): string
    {
        if (empty($longitud) || empty($latitud)) {
            return '<span class="text-muted">Sin coordenadas</span>';
        }

        return "<small>{$latitud}, {$longitud}</small>";
    }

    /**
     * Generar botones de acción para cada fila
     */
    private function generarBotonesAccion(int $id, bool $activo): string
    {
        $botones = '';

        if ($activo) {
            // Botón editar
            $botones .= '<a href="' . base_url("admin/manzanas/edit/{$id}") . '" 
                           class="btn btn-sm btn-primary me-1" 
                           title="Editar">
                           <i class="fas fa-edit"></i>
                        </a>';

            // Botón eliminar
            $botones .= '<button type="button" 
                           class="btn btn-sm btn-danger" 
                           onclick="eliminarManzana(' . $id . ')" 
                           title="Eliminar">
                           <i class="fas fa-trash"></i>
                        </button>';
        } else {
            // Botón restaurar
            $botones .= '<button type="button" 
                           class="btn btn-sm btn-success" 
                           onclick="restaurarManzana(' . $id . ')" 
                           title="Restaurar">
                           <i class="fas fa-undo"></i>
                        </button>';
        }

        return $botones;
    }
}