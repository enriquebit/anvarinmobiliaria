<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DivisionModel;
use App\Models\EmpresaModel;
use App\Models\ProyectoModel;
use App\Entities\Division;

class AdminDivisionesController extends BaseController
{
    protected $divisionModel;
    protected $empresaModel;
    protected $proyectoModel;

    public function __construct()
    {
        $this->divisionModel = new DivisionModel();
        $this->empresaModel = new EmpresaModel();
        $this->proyectoModel = new ProyectoModel();
    }

    /**
     * Página principal del módulo de divisiones
     */
    public function index()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Gestión de Divisiones',
            'empresas' => ['' => 'Todas las empresas...'] + $this->empresaModel->obtenerOpcionesSelect(),
            'proyectos' => ['' => 'Todos los proyectos...'] // Se cargan via AJAX
        ];

        return view('admin/divisiones/index', $data);
    }

    /**
     * Obtener divisiones via AJAX con filtros
     */
    public function obtenerDivisiones()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $filtros = [
                'empresa_id' => $this->request->getPost('empresa_id'),
                'proyecto_id' => $this->request->getPost('proyecto_id'),
                'busqueda' => $this->request->getPost('busqueda'),
                'incluir_inactivas' => $this->request->getPost('incluir_inactivas') === 'true',
                'orden' => $this->request->getPost('orden') ?? 'divisiones.orden',
                'direccion' => $this->request->getPost('direccion') ?? 'ASC'
            ];

            $divisiones = $this->divisionModel->buscarConFiltros($filtros);

            // Formatear datos para DataTables
            $data = [];
            foreach ($divisiones as $division) {
                $data[] = [
                    'id' => $division['id'],
                    'nombre' => $division['nombre'],
                    'clave' => '<span class="badge badge-primary">' . $division['clave'] . '</span>',
                    'empresa' => $division['nombre_empresa'],
                    'proyecto' => $division['nombre_proyecto'] . ' (' . $division['clave_proyecto'] . ')',
                    'orden' => $division['orden'],
                    'descripcion' => $division['descripcion'] ? substr($division['descripcion'], 0, 100) . '...' : '-',
                    'color' => '<span class="badge" style="background-color: ' . $division['color'] . '; color: white;">' . $division['color'] . '</span>',
                    'activo' => $division['activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>',
                    'acciones' => $this->generarBotonesAccion($division)
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener divisiones: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mostrar formulario para crear nueva división
     */
    public function create()
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/divisiones')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Crear Nueva División',
            'division' => null,
            'empresas' => ['' => 'Seleccionar empresa...'] + $this->empresaModel->obtenerOpcionesSelect(),
            'proyectos' => ['' => 'Seleccionar proyecto...'] // Se cargan via AJAX
        ];

        return view('admin/divisiones/form', $data);
    }

    /**
     * Mostrar formulario para editar división
     */
    public function edit($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/divisiones')->with('error', 'Acceso denegado');
        }

        $division = $this->divisionModel->find($id);
        if (!$division) {
            return redirect()->to('/admin/divisiones')->with('error', 'División no encontrada');
        }

        // Obtener todos los proyectos activos para permitir cambio de empresa/proyecto
        $proyectosData = $this->proyectoModel->getProyectosConEmpresa();
        $proyectosSelect = ['' => 'Seleccionar proyecto...'];
        foreach ($proyectosData as $proyecto) {
            $proyectosSelect[$proyecto->id] = $proyecto->nombre_empresa . ' - ' . $proyecto->nombre;
        }

        $data = [
            'titulo' => 'Editar División: ' . $division->nombre,
            'division' => $division,
            'empresas' => ['' => 'Seleccionar empresa...'] + $this->empresaModel->obtenerOpcionesSelect(),
            'proyectos' => $proyectosSelect
        ];

        return view('admin/divisiones/form', $data);
    }

    /**
     * Guardar nueva división
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/divisiones');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'clave' => $this->request->getPost('clave'),
                'empresas_id' => $this->request->getPost('empresas_id'),
                'proyectos_id' => $this->request->getPost('proyectos_id'),
                'descripcion' => $this->request->getPost('descripcion'),
                'orden' => $this->request->getPost('orden') ?: $this->divisionModel->getSiguienteOrden(
                    $this->request->getPost('empresas_id'),
                    $this->request->getPost('proyectos_id')
                ),
                'color' => $this->request->getPost('color') ?: '#007bff'
            ];

            // Validaciones adicionales
            $errores = $this->divisionModel->validarDatos($datos);
            if (!empty($errores)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errores
                ]);
            }

            if (!$this->divisionModel->insert($datos)) {
                throw new \Exception('Error al crear la división: ' . implode(', ', $this->divisionModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'División creada exitosamente',
                'id' => $this->divisionModel->getInsertID()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar división existente
     */
    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/divisiones');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $division = $this->divisionModel->find($id);
            if (!$division) {
                return $this->response->setJSON(['success' => false, 'message' => 'División no encontrada']);
            }

            $datos = [
                'nombre' => $this->request->getPost('nombre'),
                'clave' => $this->request->getPost('clave'),
                'empresas_id' => $this->request->getPost('empresas_id'),
                'proyectos_id' => $this->request->getPost('proyectos_id'),
                'descripcion' => $this->request->getPost('descripcion'),
                'orden' => $this->request->getPost('orden'),
                'color' => $this->request->getPost('color') ?: '#007bff'
            ];

            // Validaciones adicionales
            $errores = $this->divisionModel->validarDatos($datos, $id);
            if (!empty($errores)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errores
                ]);
            }

            if (!$this->divisionModel->update($id, $datos)) {
                throw new \Exception('Error al actualizar la división: ' . implode(', ', $this->divisionModel->errors()));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'División actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar división (soft delete)
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/divisiones');
        }

        if (!isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo superadministradores pueden eliminar divisiones']);
        }

        try {
            $division = $this->divisionModel->find($id);
            if (!$division) {
                return $this->response->setJSON(['success' => false, 'message' => 'División no encontrada']);
            }

            if (!$division->puedeSerEliminada()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se puede eliminar una división que tiene lotes asociados'
                ]);
            }

            $this->divisionModel->update($id, ['activo' => false]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'División eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar división: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Restaurar división eliminada
     */
    public function restaurar($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/divisiones');
        }

        if (!isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $this->divisionModel->update($id, ['activo' => true]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'División restaurada exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al restaurar división: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener proyectos por empresa (AJAX)
     */
    public function obtenerProyectosPorEmpresa($empresaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $proyectos = $this->proyectoModel->select('proyectos.id, proyectos.nombre')
                                            ->join('empresas', 'empresas.id = proyectos.empresas_id')
                                            ->where('proyectos.empresas_id', $empresaId)
                                            ->where('empresas.activo', 1) // Solo empresas activas
                                            ->where('proyectos.estatus', 'activo') // Solo proyectos activos
                                            ->orderBy('proyectos.nombre', 'ASC')
                                            ->findAll();

            $opciones = [];
            foreach ($proyectos as $proyecto) {
                $opciones[$proyecto->id] = $proyecto->nombre;
            }

            return $this->response->setJSON([
                'success' => true,
                'proyectos' => $opciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener proyectos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener divisiones por proyecto (AJAX) - Para uso en otros módulos
     */
    public function obtenerDivisionesPorProyecto($proyectoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $divisiones = $this->divisionModel->where('proyectos_id', $proyectoId)
                                             ->where('activo', true)
                                             ->orderBy('orden', 'ASC')
                                             ->orderBy('nombre', 'ASC')
                                             ->findAll();

            $opciones = [];
            foreach ($divisiones as $division) {
                $opciones[$division->id] = $division->nombre . ' (' . $division->clave . ')';
            }

            return $this->response->setJSON([
                'success' => true,
                'divisiones' => $opciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener divisiones: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sugerir clave automática basada en el nombre
     */
    public function sugerirClave()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $nombre = $this->request->getPost('nombre');
            $empresaId = $this->request->getPost('empresa_id');
            $proyectoId = $this->request->getPost('proyecto_id');

            if (empty($nombre) || empty($empresaId) || empty($proyectoId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Datos insuficientes para generar sugerencia'
                ]);
            }

            $division = new Division();
            $division->nombre = $nombre;
            $division->empresas_id = $empresaId;
            $division->proyectos_id = $proyectoId;

            $claveSugerida = $division->generarClaveSugerida();

            return $this->response->setJSON([
                'success' => true,
                'clave' => $claveSugerida
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar sugerencia: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ver detalles completos de una división
     */
    public function show($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/divisiones')->with('error', 'Acceso denegado');
        }

        $division = $this->divisionModel->find($id);
        if (!$division) {
            return redirect()->to('/admin/divisiones')->with('error', 'División no encontrada');
        }

        $data = [
            'titulo' => 'Detalles de la División: ' . $division->nombre,
            'division' => $division,
            'estadisticas' => $this->obtenerEstadisticasDivision($id)
        ];

        return view('admin/divisiones/show', $data);
    }

    // Métodos privados de utilidad

    /**
     * Generar botones de acción para la tabla
     */
    private function generarBotonesAccion($division): string
    {
        $botones = '<div class="btn-group" role="group">';
        
        // Ver detalles
        $botones .= '<a href="' . base_url('admin/divisiones/show/' . $division['id']) . '" class="btn btn-sm btn-info" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                     </a>';
        
        // Editar
        if ($division['activo']) {
            $botones .= '<a href="' . base_url('admin/divisiones/edit/' . $division['id']) . '" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                         </a>';
        }
        
        // Eliminar/Restaurar (solo superadmin)
        if (isSuperAdmin()) {
            if ($division['activo']) {
                $botones .= '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $division['id'] . '" title="Eliminar">
                                <i class="fas fa-trash"></i>
                             </button>';
            } else {
                $botones .= '<button class="btn btn-sm btn-success btn-restaurar" data-id="' . $division['id'] . '" title="Restaurar">
                                <i class="fas fa-undo"></i>
                             </button>';
            }
        }
        
        $botones .= '</div>';
        
        return $botones;
    }

    /**
     * Obtener estadísticas específicas de una división
     */
    private function obtenerEstadisticasDivision($divisionId): array
    {
        // TODO: Implementar consultas reales cuando se tenga la relación con lotes
        // Por ahora retornamos valores por defecto para evitar errores
        return [
            'total_lotes' => 0,
            'total_manzanas' => 0,
            'disponibles' => 0,
            'apartados' => 0,
            'vendidos' => 0,
            'bloqueados' => 0,
            'area_total' => 0.00,
            'precio_promedio' => 0.00,
            'valor_total' => 0.00
        ];
    }
}