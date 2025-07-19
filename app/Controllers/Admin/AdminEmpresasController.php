<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;

/**
 * Controlador de Administración de Empresas
 * 
 * Propósito: Gestión CRUD completa de empresas inmobiliarias
 * Funcionalidades: Crear, leer, actualizar, eliminar empresas
 * Enfoque: MVP con plugins confiables (DataTables, SweetAlert, Toastr)
 * 
 * @author Sistema Inmobiliario ANVAR
 * @version 1.0 MVP
 */
class AdminEmpresasController extends BaseController
{
    // =====================================================
    // PROPIEDADES DEL CONTROLADOR
    // =====================================================
    
    protected $empresaModel;
    protected $db;

    /**
     * Constructor - Inicializar dependencias
     */
    public function __construct()
    {
        $this->empresaModel = new EmpresaModel();
        $this->db = \Config\Database::connect();
    }

    // =====================================================
    // VISTA PRINCIPAL: LISTADO DE EMPRESAS
    // =====================================================

    /**
     * Método index - Vista principal del módulo empresas
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface|string Vista principal
     */
    public function index()
    {
        // ✅ Preparar datos para la vista
        $data = [
            'titulo' => 'Gestión de Empresas',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Empresas', 'url' => '']
            ],
            'total_empresas' => $this->empresaModel->contarEmpresasActivas()
        ];

        return view('admin/empresas/index', $data);
    }

    // =====================================================
    // API PARA DATATABLES - AJAX
    // =====================================================

    /**
     * Método datatable - Proveer datos para DataTables
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface JSON para DataTables
     */
    public function datatable()
    {
        // ✅ Verificar que sea petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            // ✅ Parámetros de DataTables
            $draw = intval($this->request->getPost('draw') ?? 1);
            $start = intval($this->request->getPost('start') ?? 0);
            $length = intval($this->request->getPost('length') ?? 10);
            $searchValue = $this->request->getPost('search')['value'] ?? '';

            // ✅ Query para empresas con conteo dinámico de proyectos
            $builder = $this->db->table('empresas e')
                               ->select('e.*, COUNT(p.id) as total_proyectos')
                               ->join('proyectos p', 'p.empresas_id = e.id', 'left')
                               ->where('e.activo', 1)
                               ->groupBy('e.id');

            // ✅ Aplicar búsqueda si existe
            if (!empty($searchValue)) {
                $builder->groupStart()
                       ->like('e.nombre', $searchValue)
                       ->orLike('e.rfc', $searchValue)
                       ->orLike('e.razon_social', $searchValue)
                       ->orLike('e.email', $searchValue)
                       ->groupEnd();
            }

            // ✅ Contar total de registros
            $totalRecords = $this->empresaModel->contarEmpresasActivas();
            
            // ✅ Para el conteo filtrado, crear query separado sin GROUP BY
            $countBuilder = $this->db->table('empresas e')
                                    ->where('e.activo', 1);
            if (!empty($searchValue)) {
                $countBuilder->groupStart()
                            ->like('e.nombre', $searchValue)
                            ->orLike('e.rfc', $searchValue)
                            ->orLike('e.razon_social', $searchValue)
                            ->orLike('e.email', $searchValue)
                            ->groupEnd();
            }
            $totalFiltered = $countBuilder->countAllResults();

            // ✅ Obtener datos con paginación
            $empresas = $builder->orderBy('e.nombre', 'ASC')
                               ->limit($length, $start)
                               ->get()
                               ->getResult();

            // ✅ Formatear datos para DataTables
            $data = [];
            foreach ($empresas as $empresa) {
                $data[] = [
                    'id' => $empresa->id,
                    'nombre' => $empresa->nombre,
                    'rfc' => $empresa->rfc,
                    'razon_social' => $empresa->razon_social ?? '<span class="text-muted">No especificada</span>',
                    'telefono' => $empresa->telefono ? 
                        $this->formatearTelefono($empresa->telefono) : 
                        '<span class="text-muted">No especificado</span>',
                    'email' => $empresa->email ?? '<span class="text-muted">No especificado</span>',
                    'proyectos' => $empresa->total_proyectos,
                    'estado' => '<span class="badge badge-success">Activo</span>',
                    'acciones' => $this->generarBotonesAccion($empresa->id, $empresa->nombre)
                ];
            }

            // ✅ Respuesta JSON para DataTables
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            // ✅ Manejar errores y enviar respuesta JSON para debug
            log_message('error', 'Error en datatable empresas: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => $draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al cargar datos: ' . $e->getMessage()
            ]);
        }
    }

    // =====================================================
    // CREAR EMPRESA - FORMULARIO
    // =====================================================

    /**
     * Método create - Mostrar formulario de creación
     * 
     * @return string Vista del formulario
     */
    public function create()
    {
        $data = [
            'titulo' => 'Nueva Empresa',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Empresas', 'url' => '/admin/empresas'],
                ['name' => 'Nueva', 'url' => '']
            ]
        ];

        return view('admin/empresas/create', $data);
    }

    // =====================================================
    // GUARDAR EMPRESA - PROCESAR FORMULARIO
    // =====================================================

    /**
     * Método store - Procesar creación de empresa
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface Redirección con resultado
     */
    public function store()
    {
        // ✅ Verificar método POST
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/admin/empresas');
        }

        // ✅ Validar datos básicos del lado del servidor
        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
            'rfc' => 'required|min_length[12]|max_length[13]',
            'razon_social' => 'permit_empty|max_length[300]',
            'domicilio' => 'permit_empty',
            'telefono' => 'permit_empty|max_length[15]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'representante' => 'permit_empty|max_length[200]'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('warning', 'Errores de validación en creación de empresa: ' . json_encode($errors));
            
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $errors)
                           ->with('error', 'Corrige los errores marcados en rojo');
        }

        // ✅ Validación adicional de negocio (RFC duplicado)
        $rfc = strtoupper(trim($this->request->getPost('rfc')));
        if ($this->empresaModel->existeRFC($rfc)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', ['rfc' => 'Este RFC ya está registrado en el sistema'])
                           ->with('error', 'El RFC ya está registrado. Por favor, utiliza otro RFC.');
        }

        // ✅ Iniciar transacción
        $this->db->transStart();

        try {
            // ✅ Crear nueva empresa usando la entidad
            $empresaData = [
                'nombre' => $this->request->getPost('nombre'),
                'rfc' => $rfc,
                'razon_social' => $this->request->getPost('razon_social'),
                'domicilio' => $this->request->getPost('domicilio'),
                'telefono' => $this->request->getPost('telefono'),
                'email' => $this->request->getPost('email'),
                'representante' => $this->request->getPost('representante'),
                'activo' => 1
            ];

            // ✅ Guardar empresa
            $empresaId = $this->empresaModel->crearEmpresa($empresaData);

            if (!$empresaId) {
                throw new \RuntimeException('Error al guardar la empresa: ' . implode(', ', $this->empresaModel->errors()));
            }

            // ✅ Confirmar transacción
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción de base de datos');
            }

            // ✅ Log de éxito
            log_message('info', "Empresa creada exitosamente - ID: {$empresaId}, Nombre: {$empresaData['nombre']}, RFC: {$rfc}");

            // ✅ Redirección con mensaje de éxito
            return redirect()->to('/admin/empresas')
                           ->with('success', 'Empresa creada exitosamente')
                           ->with('toast_message', 'La empresa "' . $empresaData['nombre'] . '" ha sido registrada correctamente');

        } catch (\Exception $e) {
            // ✅ Rollback en caso de error
            $this->db->transRollback();
            
            log_message('error', 'Error al crear empresa: ' . $e->getMessage());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error interno. Por favor, inténtalo nuevamente.')
                           ->with('toast_message', 'Ocurrió un error al guardar la empresa');
        }
    }

    // =====================================================
    // EDITAR EMPRESA - FORMULARIO
    // =====================================================

    /**
     * Método edit - Mostrar formulario de edición
     * 
     * @param int $empresaId ID de la empresa
     * @return \CodeIgniter\HTTP\ResponseInterface|string Vista del formulario o redirección
     */
    public function edit($empresaId)
    {
        // ✅ Obtener empresa
        $empresa = $this->empresaModel->obtenerEmpresaPorId($empresaId);
        if (!$empresa) {
            return redirect()->to('/admin/empresas')->with('error', 'Empresa no encontrada');
        }

        $data = [
            'titulo' => 'Editar Empresa: ' . $empresa->nombre,
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Empresas', 'url' => '/admin/empresas'],
                ['name' => 'Editar', 'url' => '']
            ],
            'empresa' => $empresa
        ];

        return view('admin/empresas/edit', $data);
    }

    // =====================================================
    // ACTUALIZAR EMPRESA - PROCESAR EDICIÓN
    // =====================================================

    /**
     * Método update - Procesar actualización de empresa
     * 
     * @param int $empresaId ID de la empresa
     * @return \CodeIgniter\HTTP\ResponseInterface Redirección con resultado
     */
    public function update($empresaId)
    {
        // ✅ Verificar método POST
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/admin/empresas');
        }

        // ✅ Verificar que la empresa existe
        $empresa = $this->empresaModel->obtenerEmpresaPorId($empresaId);
        if (!$empresa) {
            return redirect()->to('/admin/empresas')->with('error', 'Empresa no encontrada');
        }

        // ✅ Validar datos (incluyendo RFC único excluyendo el actual)
        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
            'rfc' => 'required|min_length[12]|max_length[13]',
            'razon_social' => 'permit_empty|max_length[300]',
            'domicilio' => 'permit_empty',
            'telefono' => 'permit_empty|max_length[15]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'representante' => 'permit_empty|max_length[200]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors())
                           ->with('error', 'Corrige los errores marcados en rojo');
        }

        // ✅ Validar RFC único (excluyendo la empresa actual)
        $rfc = strtoupper(trim($this->request->getPost('rfc')));
        if ($this->empresaModel->existeRFC($rfc, $empresaId)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', ['rfc' => 'Este RFC ya está registrado en otra empresa'])
                           ->with('error', 'El RFC ya está registrado en otra empresa.');
        }

        // ✅ Iniciar transacción
        $this->db->transStart();

        try {
            // ✅ Preparar datos para actualización
            $datosActualizacion = [
                'nombre' => $this->request->getPost('nombre'),
                'rfc' => $rfc,
                'razon_social' => $this->request->getPost('razon_social'),
                'domicilio' => $this->request->getPost('domicilio'),
                'telefono' => $this->request->getPost('telefono'),
                'email' => $this->request->getPost('email'),
                'representante' => $this->request->getPost('representante'),
                
            ];

            // ✅ Actualizar empresa
            if (!$this->empresaModel->actualizarEmpresa($empresaId, $datosActualizacion)) {
                throw new \RuntimeException('Error al actualizar la empresa: ' . implode(', ', $this->empresaModel->errors()));
            }

            // ✅ Confirmar transacción
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción de base de datos');
            }

            // ✅ Log de éxito
            log_message('info', "Empresa actualizada exitosamente - ID: {$empresaId}, Nombre: {$datosActualizacion['nombre']}");

            // ✅ Redirección con mensaje de éxito
            return redirect()->to('/admin/empresas')
                           ->with('success', 'Empresa actualizada exitosamente')
                           ->with('toast_message', 'La empresa "' . $datosActualizacion['nombre'] . '" ha sido actualizada correctamente');

        } catch (\Exception $e) {
            // ✅ Rollback en caso de error
            $this->db->transRollback();
            
            log_message('error', 'Error al actualizar empresa: ' . $e->getMessage());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error interno. Por favor, inténtalo nuevamente.')
                           ->with('toast_message', 'Ocurrió un error al actualizar la empresa');
        }
    }

    // =====================================================
    // ELIMINAR EMPRESA - SOFT DELETE CON AJAX
    // =====================================================

    /**
     * Método delete - Eliminar empresa (soft delete)
     * 
     * @param int $empresaId ID de la empresa
     * @return \CodeIgniter\HTTP\ResponseInterface JSON con resultado
     */
    public function delete($empresaId)
    {
        // ✅ Verificar que sea petición AJAX POST
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['success' => false, 'message' => 'Petición inválida']);
        }

        // ✅ Verificar que la empresa existe
        $empresa = $this->empresaModel->obtenerEmpresaPorId($empresaId);
        if (!$empresa) {
            return $this->response->setJSON(['success' => false, 'message' => 'Empresa no encontrada']);
        }

        try {
            // ✅ Verificar si puede eliminarse (lógica de negocio futura)
            if (!$empresa->puedeEliminarse()) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'No se puede eliminar la empresa porque tiene proyectos asociados'
                ]);
            }

            // ✅ Realizar soft delete
            if ($this->empresaModel->eliminarEmpresa($empresaId)) {
                log_message('info', "Empresa eliminada exitosamente - ID: {$empresaId}, Nombre: {$empresa->nombre}");
                
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Empresa eliminada exitosamente',
                    'empresa_nombre' => $empresa->nombre
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Error al eliminar la empresa'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al eliminar empresa: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    // =====================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =====================================================

    /**
     * Generar botones de acción para DataTables
     * 
     * @param int $empresaId ID de la empresa
     * @param string $nombreEmpresa Nombre de la empresa para SweetAlert
     * @return string HTML de los botones
     */
    private function generarBotonesAccion(int $empresaId, string $nombreEmpresa): string
    {
        return '
            <div class="btn-group btn-group-sm" role="group">
                <a href="' . site_url("/admin/empresas/edit/{$empresaId}") . '" 
                   class="btn btn-info btn-sm" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="btn btn-danger btn-sm" 
                        onclick="eliminarEmpresa(' . $empresaId . ', \'' . htmlspecialchars($nombreEmpresa, ENT_QUOTES) . '\')" 
                        title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        ';
    }

    /**
     * Formatear teléfono para visualización en DataTables
     * 
     * @param string $telefono Teléfono sin formato
     * @return string Teléfono formateado
     */
    private function formatearTelefono(string $telefono): string
    {
        if (strlen($telefono) === 10) {
            return substr($telefono, 0, 3) . ' ' . substr($telefono, 3, 3) . ' ' . substr($telefono, 6, 4);
        }
        return $telefono;
    }
}
