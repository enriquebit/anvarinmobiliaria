<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CuentaBancariaModel;
use App\Models\EmpresaModel;
use App\Models\ProyectoModel;

class AdminCuentasBancariasController extends BaseController
{
    protected $cuentaBancariaModel;
    protected $empresaModel;
    protected $proyectoModel;

    public function __construct()
    {
        $this->cuentaBancariaModel = new CuentaBancariaModel();
        $this->empresaModel = new EmpresaModel();
        $this->proyectoModel = new ProyectoModel();
    }

    // =====================================================================
    // VISTA PRINCIPAL: LISTADO DE CUENTAS BANCARIAS
    // =====================================================================

    public function index()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        $estadisticas = $this->cuentaBancariaModel->obtenerEstadisticas();
        
        $data = [
            'titulo' => 'Gestión de Cuentas Bancarias',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/admin/dashboard'],
                ['name' => 'Cuentas Bancarias', 'url' => '']
            ],
            'estadisticas' => $estadisticas
        ];

        return view('admin/cuentas-bancarias/index', $data);
    }

    // =====================================================================
    // CREAR CUENTA BANCARIA
    // =====================================================================

    public function create()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/cuentas-bancarias')->with('error', 'No tienes permisos');
        }

        $data = [
            'titulo' => 'Crear Cuenta Bancaria',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/admin/dashboard'],
                ['name' => 'Cuentas Bancarias', 'url' => '/admin/cuentas-bancarias'],
                ['name' => 'Crear', 'url' => '']
            ],
            'empresas' => $this->getEmpresasActivas(),
            'proyectos' => $this->getProyectosActivos(),
            'bancos' => $this->getBancosComunes(),
            'tipos_cuenta' => $this->getTiposCuenta(),
            'monedas' => $this->getMonedas()
        ];

        return view('admin/cuentas-bancarias/create', $data);
    }

    public function store()
    {
        // Debug: Log inicio del método
        log_message('debug', 'STORE - Iniciando creación de cuenta bancaria');
        log_message('debug', 'STORE - Usuario autenticado: ' . (auth()->user() ? auth()->user()->email : 'NO'));
        
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            log_message('warning', 'STORE - Usuario sin permisos: ' . (auth()->user() ? auth()->user()->email : 'NO_AUTH'));
            return redirect()->to('/admin/cuentas-bancarias')->with('error', 'No tienes permisos');
        }

        // Debug: Log datos POST recibidos
        log_message('debug', 'STORE - Datos POST recibidos: ' . json_encode($this->request->getPost()));
        log_message('debug', 'STORE - Método HTTP: ' . $this->request->getMethod());

        $validationRules = $this->cuentaBancariaModel->getValidationRules();
        log_message('debug', 'STORE - Reglas de validación obtenidas: ' . json_encode($validationRules));

        if (!$this->validate($validationRules)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'STORE - Errores de validación: ' . json_encode($errors));
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'descripcion' => $this->request->getPost('descripcion'),
            'banco' => $this->request->getPost('banco'),
            'numero_cuenta' => $this->request->getPost('numero_cuenta'),
            'clabe' => $this->request->getPost('clabe') ?: null,
            'swift' => $this->request->getPost('swift') ?: null,
            'titular' => $this->request->getPost('titular'),
            'convenio' => $this->request->getPost('convenio') ?: null,
            'saldo_inicial' => $this->request->getPost('saldo_inicial') ?: 0,
            'saldo_actual' => $this->request->getPost('saldo_inicial') ?: 0,
            'moneda' => $this->request->getPost('moneda'),
            'tipo_cuenta' => $this->request->getPost('tipo_cuenta'),
            'permite_depositos' => $this->request->getPost('permite_depositos') ? 1 : 0,
            'permite_retiros' => $this->request->getPost('permite_retiros') ? 1 : 0,
            'color_identificacion' => $this->request->getPost('color_identificacion') ?: '#007bff',
            'proyecto_id' => $this->request->getPost('proyecto_id') ?: null,
            'empresa_id' => $this->request->getPost('empresa_id'),
            'activo' => 1,
            'notas' => $this->request->getPost('notas') ?: null,
            'fecha_apertura' => $this->request->getPost('fecha_apertura') ?: null,
        ];

        // Debug: Log datos para insertar
        log_message('debug', 'STORE - Datos para insertar: ' . json_encode($data));

        $insertResult = $this->cuentaBancariaModel->insert($data);
        log_message('debug', 'STORE - Resultado de insert(): ' . json_encode($insertResult));
        
        if ($insertResult) {
            log_message('info', 'STORE - Cuenta bancaria creada exitosamente con ID: ' . $insertResult);
            return redirect()->to('/admin/cuentas-bancarias')->with('success', 'Cuenta bancaria creada exitosamente');
        } else {
            $modelErrors = $this->cuentaBancariaModel->errors();
            log_message('error', 'STORE - Error del modelo al insertar: ' . json_encode($modelErrors));
            log_message('error', 'STORE - Último error BD: ' . $this->cuentaBancariaModel->db->getLastQuery());
            return redirect()->back()->withInput()->with('error', 'Error al crear la cuenta bancaria: ' . implode(', ', $modelErrors));
        }
    }

    // =====================================================================
    // EDITAR CUENTA BANCARIA
    // =====================================================================

    public function edit($id)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/cuentas-bancarias')->with('error', 'No tienes permisos');
        }

        $cuenta = $this->cuentaBancariaModel->find($id);
        if (!$cuenta) {
            return redirect()->to('/admin/cuentas-bancarias')->with('error', 'Cuenta bancaria no encontrada');
        }
        

        $data = [
            'titulo' => 'Editar Cuenta Bancaria',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/admin/dashboard'],
                ['name' => 'Cuentas Bancarias', 'url' => '/admin/cuentas-bancarias'],
                ['name' => 'Editar', 'url' => '']
            ],
            'cuenta' => $cuenta,
            'empresas' => $this->getEmpresasActivas($cuenta->empresa_id),
            'proyectos' => $this->getProyectosActivos($cuenta->proyecto_id),
            'bancos' => $this->getBancosComunes(),
            'tipos_cuenta' => $this->getTiposCuenta(),
            'monedas' => $this->getMonedas()
        ];

        return view('admin/cuentas-bancarias/edit', $data);
    }

    public function update($id)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/cuentas-bancarias')->with('error', 'No tienes permisos');
        }

        $cuenta = $this->cuentaBancariaModel->find($id);
        if (!$cuenta) {
            return redirect()->to('/admin/cuentas-bancarias')->with('error', 'Cuenta bancaria no encontrada');
        }

        // Debug: Log de datos recibidos
        log_message('debug', 'UPDATE - Datos POST recibidos: ' . json_encode($this->request->getPost()));
        log_message('debug', 'UPDATE - ID cuenta: ' . $id);

        // Validaciones personalizadas para edición
        $clabeValue = trim($this->request->getPost('clabe') ?? '');
        $rules = [
            'descripcion' => 'required|max_length[255]',
            'banco' => 'required|max_length[100]',
            'numero_cuenta' => "required|max_length[20]|is_unique[cuentas_bancarias.numero_cuenta,id,{$id}]",
            'swift' => 'permit_empty|min_length[8]|max_length[11]|alpha_numeric',
            'titular' => 'required|max_length[255]',
            'convenio' => 'permit_empty|max_length[50]',
            'moneda' => 'required|in_list[MXN,USD,EUR]',
            'tipo_cuenta' => 'required|in_list[corriente,ahorro,inversion,efectivo]',
            'permite_depositos' => 'permit_empty|in_list[0,1]',
            'permite_retiros' => 'permit_empty|in_list[0,1]',
            'color_identificacion' => 'permit_empty|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'proyecto_id' => 'permit_empty|is_natural_no_zero',
            'empresa_id' => 'required|is_natural_no_zero',
            'fecha_apertura' => 'permit_empty|valid_date[Y-m-d]',
        ];
        
        // Agregar validación de CLABE solo si no está vacía
        if (!empty($clabeValue)) {
            $rules['clabe'] = "exact_length[18]|numeric|is_unique[cuentas_bancarias.clabe,id,{$id}]";
        }

        // Debug: Log de reglas de validación
        log_message('debug', 'UPDATE - Reglas de validación: ' . json_encode($rules));

        if (!$this->validate($rules)) {
            // Debug: Log de errores de validación
            log_message('error', 'UPDATE - Errores de validación: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'descripcion' => $this->request->getPost('descripcion'),
            'banco' => $this->request->getPost('banco'),
            'numero_cuenta' => $this->request->getPost('numero_cuenta'),
            'clabe' => $this->request->getPost('clabe') ?: null,
            'swift' => $this->request->getPost('swift') ?: null,
            'titular' => $this->request->getPost('titular'),
            'convenio' => $this->request->getPost('convenio') ?: null,
            'moneda' => $this->request->getPost('moneda'),
            'tipo_cuenta' => $this->request->getPost('tipo_cuenta'),
            'permite_depositos' => $this->request->getPost('permite_depositos') ? 1 : 0,
            'permite_retiros' => $this->request->getPost('permite_retiros') ? 1 : 0,
            'color_identificacion' => $this->request->getPost('color_identificacion') ?: '#007bff',
            'proyecto_id' => $this->request->getPost('proyecto_id') ?: null,
            'empresa_id' => $this->request->getPost('empresa_id'),
            'notas' => $this->request->getPost('notas') ?: null,
            'fecha_apertura' => $this->request->getPost('fecha_apertura') ?: null,
        ];

        // Debug: Log de datos a actualizar
        log_message('debug', 'UPDATE - Datos para actualizar: ' . json_encode($data));

        if ($this->cuentaBancariaModel->update($id, $data)) {
            log_message('info', 'UPDATE - Cuenta bancaria actualizada exitosamente: ID ' . $id);
            return redirect()->to('/admin/cuentas-bancarias')->with('success', 'Cuenta bancaria actualizada exitosamente');
        } else {
            // Debug: Log del error del modelo
            $modelErrors = $this->cuentaBancariaModel->errors();
            log_message('error', 'UPDATE - Error del modelo: ' . json_encode($modelErrors));
            return redirect()->back()->withInput()->with('error', 'Error al actualizar la cuenta bancaria');
        }
    }

    // =====================================================================
    // OBTENER DATOS PARA DATATABLES
    // =====================================================================

    public function obtenerCuentas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $cuentas = $this->cuentaBancariaModel->obtenerParaDataTable();
            
            $data = [];
            foreach ($cuentas as $cuenta) {
                $badgeEstado = $cuenta['activo'] 
                    ? '<span class="badge badge-success">Activa</span>' 
                    : '<span class="badge badge-danger">Inactiva</span>';
                    
                $saldoFormateado = '$' . number_format($cuenta['saldo_actual'], 2, '.', ',');
                $colorSaldo = $cuenta['saldo_actual'] > 0 ? 'text-success' : 'text-danger';
                
                $data[] = [
                    'id' => $cuenta['id'],
                    'descripcion' => $cuenta['descripcion'],
                    'banco' => $cuenta['banco'],
                    'numero_cuenta' => $cuenta['numero_cuenta'],
                    'titular' => $cuenta['titular'],
                    'saldo_actual' => '<span class="' . $colorSaldo . '">' . $saldoFormateado . '</span>',
                    'tipo_cuenta' => ucfirst($cuenta['tipo_cuenta']),
                    'proyecto_nombre' => $cuenta['proyecto_nombre'],
                    'empresa_nombre' => $cuenta['empresa_nombre'],
                    'estado' => $badgeEstado,
                    'acciones' => $this->generarBotonesAccion($cuenta)
                ];
            }

            return $this->response->setJSON(['data' => $data]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error al obtener las cuentas: ' . $e->getMessage()]);
        }
    }

    // =====================================================================
    // CAMBIAR ESTADO
    // =====================================================================

    public function cambiarEstado($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitud no válida']);
        }

        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        $cuenta = $this->cuentaBancariaModel->find($id);
        if (!$cuenta) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cuenta no encontrada']);
        }

        $nuevoEstado = !$cuenta->activo;
        
        if ($this->cuentaBancariaModel->cambiarEstado($id, $nuevoEstado)) {
            $mensaje = $nuevoEstado ? 'Cuenta activada' : 'Cuenta desactivada';
            return $this->response->setJSON(['success' => true, 'message' => $mensaje]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al cambiar el estado']);
        }
    }

    // =====================================================================
    // MÉTODOS AUXILIARES
    // =====================================================================

    private function getEmpresasActivas($empresaActualId = null): array
    {
        try {
            // Obtener empresas activas
            $empresas = $this->empresaModel->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();
            $opciones = [];
            foreach ($empresas as $empresa) {
                $opciones[$empresa->id] = $empresa->nombre;
            }
            
            // Si hay una empresa actual y no está en la lista, agregarla
            if ($empresaActualId && !isset($opciones[$empresaActualId])) {
                try {
                    $empresaActual = $this->empresaModel->find($empresaActualId);
                    if ($empresaActual) {
                        $opciones[$empresaActual->id] = $empresaActual->nombre . ' (Inactiva)';
                    }
                } catch (\Exception $e) {
                    log_message('warning', 'No se pudo obtener empresa actual ID ' . $empresaActualId);
                }
            }
            
            return $opciones;
        } catch (\Exception $e) {
            // Fallback: obtener todas las empresas sin filtro de activo
            log_message('warning', 'Error al obtener empresas activas: ' . $e->getMessage());
            try {
                $empresas = $this->empresaModel->orderBy('nombre', 'ASC')->findAll();
                $opciones = [];
                foreach ($empresas as $empresa) {
                    $opciones[$empresa->id] = $empresa->nombre;
                }
                return $opciones;
            } catch (\Exception $e2) {
                log_message('error', 'Error crítico al obtener empresas: ' . $e2->getMessage());
                return ['1' => 'Empresa por defecto (Error al cargar)'];
            }
        }
    }

    private function getProyectosActivos($proyectoActualId = null): array
    {
        try {
            // Obtener todos los proyectos (sin filtrar por activo ya que la tabla no tiene esa columna)
            $proyectos = $this->proyectoModel->getProyectosConEmpresa();
            $opciones = ['' => 'Sin proyecto específico'];
            foreach ($proyectos as $proyecto) {
                $opciones[$proyecto->id] = $proyecto->nombre;
            }
            
            // Si hay un proyecto actual y no está en la lista, intentar agregarlo
            if ($proyectoActualId && !isset($opciones[$proyectoActualId])) {
                try {
                    $proyectoActual = $this->proyectoModel->find($proyectoActualId);
                    if ($proyectoActual) {
                        $opciones[$proyectoActual->id] = $proyectoActual->nombre . ' (No disponible)';
                    }
                } catch (\Exception $e) {
                    log_message('warning', 'No se pudo obtener proyecto actual ID ' . $proyectoActualId);
                }
            }
            
            return $opciones;
        } catch (\Exception $e) {
            log_message('error', 'Error al obtener proyectos: ' . $e->getMessage());
            return ['' => 'Sin proyecto específico (Error al cargar)'];
        }
    }

    private function getBancosComunes(): array
    {
        return [
            'BBVA' => 'BBVA',
            'Banorte' => 'Banorte',
            'HSBC' => 'HSBC',
            'Santander' => 'Santander',
            'Banamex' => 'Banamex',
            'Scotiabank' => 'Scotiabank',
            'Banregio' => 'Banregio',
            'Inbursa' => 'Inbursa',
            'EFECTIVO' => 'EFECTIVO',
            'Otro' => 'Otro'
        ];
    }

    private function getTiposCuenta(): array
    {
        return [
            'corriente' => 'Cuenta Corriente',
            'ahorro' => 'Cuenta de Ahorro', 
            'inversion' => 'Cuenta de Inversión',
            'efectivo' => 'Efectivo'
        ];
    }

    private function getMonedas(): array
    {
        return [
            'MXN' => 'Peso Mexicano (MXN)',
            'USD' => 'Dólar Americano (USD)',
            'EUR' => 'Euro (EUR)'
        ];
    }

    private function generarBotonesAccion(array $cuenta): string
    {
        $estadoTexto = $cuenta['activo'] ? 'Desactivar' : 'Activar';
        $estadoColor = $cuenta['activo'] ? 'warning' : 'success';
        $estadoIcon = $cuenta['activo'] ? 'fa-ban' : 'fa-check';

        return '
            <div class="btn-group" role="group">
                <a href="' . base_url("/admin/cuentas-bancarias/edit/{$cuenta['id']}") . '" 
                   class="btn btn-sm btn-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="btn btn-sm btn-' . $estadoColor . '" 
                        onclick="cambiarEstado(' . $cuenta['id'] . ')" title="' . $estadoTexto . '">
                    <i class="fas ' . $estadoIcon . '"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" 
                        onclick="eliminarCuenta(' . $cuenta['id'] . ')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        ';
    }

    // =====================================================================
    // ELIMINAR CUENTA BANCARIA
    // =====================================================================

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitud no válida']);
        }

        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos']);
        }

        $cuenta = $this->cuentaBancariaModel->find($id);
        if (!$cuenta) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cuenta no encontrada']);
        }

        // Verificar si la cuenta tiene ventas asociadas a través del proyecto
        if ($this->tieneVentasAsociadas($cuenta->proyecto_id)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'No se puede eliminar la cuenta porque tiene ventas asociadas al proyecto'
            ]);
        }

        // Usar soft delete
        if ($this->cuentaBancariaModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Cuenta bancaria eliminada exitosamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error al eliminar la cuenta bancaria'
            ]);
        }
    }

    /**
     * Verificar si un proyecto tiene ventas asociadas
     */
    private function tieneVentasAsociadas($proyectoId): bool
    {
        if (empty($proyectoId)) {
            return false;
        }

        $db = \Config\Database::connect();
        
        // Consulta para verificar ventas asociadas al proyecto
        $sql = "SELECT COUNT(v.id) as total
                FROM ventas v
                INNER JOIN lotes l ON v.lote_id = l.id
                INNER JOIN manzanas m ON l.manzanas_id = m.id
                WHERE m.proyectos_id = ? AND v.estatus_venta NOT IN ('cancelada')";
        
        $result = $db->query($sql, [$proyectoId])->getRow();
        
        return $result->total > 0;
    }
}