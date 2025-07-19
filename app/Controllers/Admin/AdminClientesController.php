<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use CodeIgniter\Shield\Models\UserModel;
use App\Models\DireccionClienteModel;
use App\Models\ReferenciaClienteModel;
use App\Models\EstadoCivilModel;
use App\Models\FuenteInformacionModel;
use App\Models\InformacionConyugeModel;
use App\Models\InformacionLaboralModel;
use App\Models\PersonaMoralModel;

class AdminClientesController extends BaseController
{
    protected $clienteModel;
    protected $userModel;
    protected $db;
    protected $direccionModel;
    protected $referenciaModel;
    protected $estadoCivilModel;
    protected $fuenteInformacionModel;
    protected $conyugeModel;
    protected $laboralModel;
    protected $personaMoralModel;
    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
        
        $this->direccionModel = new DireccionClienteModel();
        $this->referenciaModel = new ReferenciaClienteModel();
        $this->estadoCivilModel = new EstadoCivilModel();
        $this->fuenteInformacionModel = new FuenteInformacionModel();
        $this->conyugeModel = new InformacionConyugeModel();
        $this->laboralModel = new InformacionLaboralModel();
        $this->personaMoralModel = new PersonaMoralModel();
    }

    // =====================================================================
    // VISTA PRINCIPAL: LISTADO DE CLIENTES
    // =====================================================================

    public function index()
    {
        
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        $estadisticas = $this->getEstadisticasClientes();
        
        $data = [
            'titulo' => 'Gestión de Clientes',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Clientes', 'url' => '']
            ],
            'etapas' => $this->getEtapasProceso(),
            'total_clientes' => $estadisticas['total'],
            'estadisticas' => $estadisticas
        ];

        
        return view('admin/clientes/index', $data);
    }

    private function getTotalClientesSimple(): int
    {
        return $this->db->table('clientes')
                       ->join('auth_groups_users', 'auth_groups_users.user_id = clientes.user_id')
                       ->join('users', 'users.id = clientes.user_id')
                       ->where('auth_groups_users.group', 'cliente')
                       ->where('users.deleted_at IS NULL')
                       ->countAllResults();
    }

    /**
     * Obtener estadísticas para las cards del dashboard
     */
    public function getEstadisticasClientes(): array
    {
        $baseQuery = $this->db->table('clientes c')
                             ->join('auth_groups_users agu', 'agu.user_id = c.user_id')
                             ->join('users u', 'u.id = c.user_id')
                             ->where('agu.group', 'cliente')
                             ->where('u.deleted_at IS NULL');

        $total = (clone $baseQuery)->countAllResults();
        $activos = (clone $baseQuery)->where('u.active', 1)->countAllResults();
        $inactivos = (clone $baseQuery)->where('u.active', 0)->countAllResults();
        $pendientes = (clone $baseQuery)->where('c.etapa_proceso', 'interesado')->countAllResults();

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $inactivos,
            'pendientes' => $pendientes
        ];
    }

    /**
     * API para obtener estadísticas (AJAX)
     */
    public function estadisticas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $estadisticas = $this->getEstadisticasClientes();
            return $this->response->setJSON([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo estadísticas: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }

    // =====================================================================
    // API PARA DATATABLES
    // =====================================================================

    public function datatable()
    {
        
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $draw = intval($this->request->getPost('draw') ?? 0);
        $start = intval($this->request->getPost('start') ?? 0);
        $length = intval($this->request->getPost('length') ?? 25);
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $filtros = [
            'search' => $this->request->getPost('busqueda') ?? $searchValue,
            'etapa' => $this->request->getPost('etapa') ?? '',
            'activo' => $this->request->getPost('activo') ?? ''
        ];
        

        try {
            $clientes = $this->clienteModel->getClientesParaDataTable($length, $start, $filtros);
            
            $totalRecords = $this->getTotalClientesSimple();
            $totalFiltered = $this->clienteModel->getTotalClientesAdmin($filtros);

            $data = [];
            foreach ($clientes as $cliente) {
                $data[] = [
                    'id' => $cliente['id'],
                    'nombre_completo' => $this->formatearNombreCompleto($cliente),
                    'email' => $cliente['email_usuario'] ?? $cliente['email'] ?? 'Sin email',
                    'telefono' => $this->formatearTelefono($cliente['telefono'] ?? ''),
                    'etapa_proceso' => $this->formatearEtapa($cliente['etapa_proceso'] ?? 'interesado'),
                    'activo' => $cliente['activo'],
                    'fecha_registro' => $this->formatearFecha($cliente['created_at']),
                    'acciones' => $this->generarBotonesAccion($cliente['id'], $cliente['activo'])
                ];
            }

            $response = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ];
            

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            
            log_message('error', 'Error en datatable de clientes: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al cargar los datos: ' . $e->getMessage()
            ]);
        }
    }

    // =====================================================================
    // CREAR CLIENTE
    // =====================================================================

    public function create()
    { 
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/clientes')->with('error', 'No tienes permisos');
        }

        $data = [
            'titulo' => 'Crear Cliente',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Clientes', 'url' => '/admin/clientes'],
                ['name' => 'Crear', 'url' => '']
            ],
            'asesores' => $this->getAsesoresDisponibles(),
            'usuario_actual' => $this->getUsuarioActualInfo(),
            'estados_civiles' => $this->getEstadosCiviles(),
            'fuentes_informacion' => $this->getFuentesInformacion()
        ];

        return view('admin/clientes/create', $data);
    }

/**
 * Procesar formulario de nuevo cliente
 */
public function store()
{
    // Verificar permisos
    if (!auth()->user()->inGroup('superadmin', 'admin')) {
        return redirect()->back()->with('error', 'No tienes permisos para crear clientes');
    }

    // Validar datos básicos
    $validationRules = [
        'nombres' => 'required|min_length[2]|max_length[100]',
        'apellido_paterno' => 'required|max_length[50]',
        'apellido_materno' => 'required|max_length[50]',
        'email' => 'required|valid_email|max_length[255]|is_unique[auth_identities.secret]',
        'telefono' => 'required|max_length[15]',
        'persona_moral' => 'permit_empty|in_list[0,1]'
    ];
    
    // Validación condicional para persona moral
    if ($this->request->getPost('persona_moral') == 1) {
        $validationRules['empresa_razon_social'] = 'required|max_length[500]';
        $validationRules['empresa_rfc'] = 'permit_empty|max_length[13]';
        $validationRules['empresa_email'] = 'permit_empty|valid_email|max_length[255]';
    }

    $validationMessages = [
        'nombres' => [
            'required' => 'Los nombres son obligatorios',
            'min_lenght' => 'Los nombres deden tener al menos 2 caracteres',
            'max_length' => 'Los nombres no pueden exceder los 100 caracteres',
        ],
        'apellido_paterno' => [
            'required' => 'El apellido paterno es obligatorio',
            'max_length' => 'El apellido paterno no puede exceder los 50 caracteres',
        ],
        'apellido_materno' => [
            'required' => 'El apellido materno es obligatorio',
            'max_length' => 'El apellido materno no puede exceder los 50 caracteres',
        ],
        'email' => [
            'required' => 'El email es obligatorio',
            'valid_email' => 'El email debe ser válido',
            'max_length' => 'El email no puede exceder los 255 caracteres',
            'is_unique' => 'Este email ya está registrado'
        ],
        'persona_moral' => [
            'in_list' => 'El valor de persona moral debe ser 0 o 1'
        ],
        'empresa_razon_social' => [
            'required' => 'La razón social de la empresa es obligatoria para personas morales',
            'max_length' => 'La razón social no puede exceder los 500 caracteres'
        ],
        'empresa_rfc' => [
            'max_length' => 'El RFC de la empresa no puede exceder los 13 caracteres'
        ],
        'empresa_email' => [
            'valid_email' => 'El email de la empresa debe ser válido',
            'max_length' => 'El email de la empresa no puede exceder los 255 caracteres'
        ]
    ];

    if (!$this->validate($validationRules, $validationMessages)) {
        $errors = $this->validator->getErrors();
        log_message('error', 'Error de validación al crear cliente: ' . json_encode($this->validator->getErrors()));
        return redirect()->back()
                       ->withInput()
                          ->with('errors', $errors)
                          ->with('error', 'Corrige los errores marcados en rojo');
    }

    // Validación adicional de negocio (email duplicado en clientes)
    $email = strtolower(trim($this->request->getPost('email')));
    
    $emailExistsInAuth = $this->db->table('auth_identities')
                                 ->where('secret', $email)
                                 ->where('type', 'email_password')
                                 ->countAllResults() > 0;

    $emailExistsInClientes = $this->db->table('clientes')
                                     ->where('email', $email)
                                     ->countAllResults() > 0;

    if ($emailExistsInAuth || $emailExistsInClientes) {
        log_message('info', "Email duplicado detectado: {$email}");
        
        return redirect()->back()
                       ->withInput()
                       ->with('errors', ['email' => 'Este email ya está registrado en el sistema'])
                       ->with('error', 'El email ya está registrado. Por favor, utiliza otro email.');
    }


    // Iniciar transacción
    $this->db->transStart();

    try {
        // =====================================================================
        // PASO 1: CREAR USUARIO SHIELD CON FORCE_RESET = 1
        // =====================================================================
        
        $userModel = new \CodeIgniter\Shield\Models\UserModel();
        
        $userData = [
            'email' => $this->request->getPost('email'),
            'password' => 'anva_' . bin2hex(random_bytes(6)), // Contraseña aleatoria con prefijo
            'active' => true,
        ];
        
        $user = new \CodeIgniter\Shield\Entities\User($userData);
        
        if (!$userModel->save($user)) {
            throw new \RuntimeException('Error al crear usuario: ' . implode(', ', $userModel->errors()));
        }
        
        $userId = $userModel->getInsertID();
        
        // Asignar grupo cliente
        $user = $userModel->find($userId);
        $user->addGroup('cliente');
        
        // ✅ IMPORTANTE: Establecer force_reset = 1 para forzar cambio de contraseña
        $this->db->table('auth_identities')
                ->where('user_id', $userId)
                ->where('type', 'email_password')
                ->update(['force_reset' => 1]);
        
        log_message('info', "Usuario Shield creado - ID: {$userId}, Email: " . $this->request->getPost('email'));

        // =====================================================================
        // PASO 2: CREAR CLIENTE CON user_id
        // =====================================================================
        
        $clienteData = [
            'user_id' => $userId,
            'nombres' => $this->request->getPost('nombres'),
            'apellido_paterno' => $this->request->getPost('apellido_paterno'),
            'apellido_materno' => $this->request->getPost('apellido_materno'),
            'email' => $this->request->getPost('email'),
            'telefono' => preg_replace('/\D/', '', $this->request->getPost('telefono')),
            'genero' => $this->request->getPost('genero') ?? 'M',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?: null,
            'lugar_nacimiento' => $this->request->getPost('lugar_nacimiento'),
            'nacionalidad' => $this->request->getPost('nacionalidad') ?? 'mexicana',
            'profesion' => $this->request->getPost('profesion'),
            'rfc' => $this->request->getPost('rfc'),
            'curp' => $this->request->getPost('curp'),
            'razon_social' => $this->request->getPost('razon_social'),
            'estado_civil' => $this->request->getPost('estado_civil') ?? 'soltero',
            'fuente_informacion' => $this->request->getPost('fuente_informacion') ?? 'referido',
            'otro_origen' => $this->request->getPost('otro_origen'),
            'residente' => $this->request->getPost('residente') ?? 'permanente',
            'identificacion' => $this->request->getPost('identificacion') ?? 'ine',
            'numero_identificacion' => $this->request->getPost('numero_identificacion'),
            'notas_internas' => $this->request->getPost('notas_internas'),
            'etapa_proceso' => $this->request->getPost('etapa_proceso') ?? 'interesado',
            'fecha_primer_contacto' => $this->request->getPost('fecha_primer_contacto') ?: date('Y-m-d H:i:s'),
            'fecha_ultima_actividad' => $this->request->getPost('fecha_ultima_actividad') ?: null,
            'asesor_asignado' => $this->request->getPost('asesor_asignado') ?: auth()->user()->id,
            'activado_por' => auth()->user()->id,
            'fecha_activacion' => date('Y-m-d H:i:s'),
            'persona_moral' => $this->request->getPost('persona_moral') ?? 0
        ];

        // Crear cliente directamente
        $cliente = new \App\Entities\Cliente($clienteData);
        
        if (!$this->clienteModel->save($cliente)) {
            throw new \RuntimeException('Error al crear cliente: ' . implode(', ', $this->clienteModel->errors()));
        }
        
        $clienteId = $this->clienteModel->getInsertID();
        
        log_message('info', "Cliente creado - ID: {$clienteId}");

        // =====================================================================
        // PASO 3: GUARDAR DIRECCIÓN SI EXISTE
        // =====================================================================
        
        if ($this->request->getPost('direccion_domicilio')) {
            $direccionData = [
                'cliente_id' => $clienteId,
                'domicilio' => $this->request->getPost('direccion_domicilio'),
                'numero' => $this->request->getPost('direccion_numero') ?: 'S/N',
                'colonia' => $this->request->getPost('direccion_colonia'),
                'codigo_postal' => $this->request->getPost('direccion_cp'),
                'ciudad' => $this->request->getPost('direccion_ciudad'),
                'estado' => $this->request->getPost('direccion_estado'),
                'tipo_residencia' => $this->request->getPost('direccion_tipo_residencia') ?? 'propia',
                'tiempo_radicando' => $this->request->getPost('tiempo_radicando'),
                'tipo' => 'principal',
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->table('direcciones_clientes')->insert($direccionData);
            log_message('info', "Dirección guardada para cliente {$clienteId}");
        }

        // =====================================================================
        // PASO 4: GUARDAR REFERENCIAS PERSONALES
        // =====================================================================
        
        for ($i = 1; $i <= 2; $i++) {
            $nombreReferencia = $this->request->getPost("referencia{$i}_nombre");
            
            if (!empty($nombreReferencia)) {
                $refData = [
                    'cliente_id' => $clienteId,
                    'numero' => $i,
                    'nombre_completo' => $nombreReferencia,
                    'parentesco' => $this->request->getPost("referencia{$i}_parentesco"),
                    'telefono' => preg_replace('/\D/', '', $this->request->getPost("referencia{$i}_telefono") ?? ''),
                    'tipo' => "referencia_{$i}",
                    'genero' => $this->request->getPost("referencia{$i}_genero"),
                    'activo' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->table('referencias_clientes')->insert($refData);
                log_message('info', "Referencia {$i} guardada para cliente {$clienteId}");
            }
        }

        // =====================================================================
        // PASO 5: GUARDAR INFORMACIÓN DEL CÓNYUGE
        // =====================================================================
        
        if (!empty($this->request->getPost('conyuge_nombre'))) {
            $conyugeData = [
                'cliente_id' => $clienteId,
                'nombre_completo' => $this->request->getPost('conyuge_nombre'),
                'profesion' => $this->request->getPost('conyuge_profesion'),
                'email' => $this->request->getPost('conyuge_email'),
                'telefono' => preg_replace('/\D/', '', $this->request->getPost('conyuge_telefono') ?? ''),
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->table('informacion_conyuge_clientes')->insert($conyugeData);
            log_message('info', "Información del cónyuge guardada para cliente {$clienteId}");
        }

        // =====================================================================
        // PASO 6: GUARDAR INFORMACIÓN LABORAL
        // =====================================================================
        
        if (!empty($this->request->getPost('laboral_empresa'))) {
            $laboralData = [
                'cliente_id' => $clienteId,
                'nombre_empresa' => $this->request->getPost('laboral_empresa'),
                'puesto_cargo' => $this->request->getPost('laboral_puesto'),
                'antiguedad' => $this->request->getPost('laboral_antiguedad'),
                'telefono_trabajo' => preg_replace('/\D/', '', $this->request->getPost('laboral_telefono') ?? ''),
                'direccion_trabajo' => $this->request->getPost('laboral_direccion'),
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->table('informacion_laboral_clientes')->insert($laboralData);
            log_message('info', "Información laboral guardada para cliente {$clienteId}");
        }

        // =====================================================================
        // PASO 7: GUARDAR DATOS DE PERSONA MORAL (SI APLICA)
        // =====================================================================
        
        if ($this->request->getPost('persona_moral') == 1) {
            $empresaData = [
                'razon_social' => $this->request->getPost('empresa_razon_social'),
                'rfc_empresa' => $this->request->getPost('empresa_rfc'),
                'direccion_fiscal' => $this->request->getPost('empresa_direccion_fiscal'),
                'telefono_empresa' => preg_replace('/\D/', '', $this->request->getPost('empresa_telefono') ?? ''),
                'email_empresa' => $this->request->getPost('empresa_email')
            ];
            
            if ($this->personaMoralModel->guardarPersonaMoral($clienteId, $empresaData)) {
                log_message('info', "Datos de persona moral guardados para cliente {$clienteId}");
            } else {
                log_message('error', "❌ Error al guardar datos de persona moral para cliente {$clienteId}");
            }
        }

        // =====================================================================
        // PASO 8: GUARDAR DOCUMENTOS SUBIDOS (FUNCIONALIDAD NO IMPLEMENTADA)
        // =====================================================================
        
        // Funcionalidad de documentos no implementada aún
        // $this->procesarDocumentosCliente($clienteId);

        // =====================================================================
        // PASO 9: FINALIZAR TRANSACCIÓN
        // =====================================================================
        
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Error en la transacción general');
        }

        // =====================================================================
        // PASO 10: ENVIAR MAGIC LINK DE BIENVENIDA AUTOMÁTICAMENTE
        // =====================================================================
        
        try {
            $this->enviarMagicLinkBienvenida($user, $clienteId);
            log_message('info', "Magic link de bienvenida enviado a: {$email}");
        } catch (\Exception $e) {
            log_message('error', "Error enviando magic link de bienvenida: " . $e->getMessage());
        }

        // =====================================================================
        // PASO 11: RESPUESTA EXITOSA
        // =====================================================================
        
        $mensaje = "Cliente creado exitosamente. ID: {$clienteId}. Se ha enviado un email de bienvenida con instrucciones para establecer su contraseña.";
        
        log_message('info', "Cliente completo creado - Usuario: {$userId}, Cliente: {$clienteId}");
        
        return redirect()->to('/admin/clientes/show/' . $clienteId)
                       ->with('success', $mensaje);

    } catch (\Exception $e) {
        // Rollback en caso de error
        $this->db->transRollback();
        
        log_message('error', 'Error al crear cliente: ' . $e->getMessage());
        
        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Error al crear el cliente: ' . $e->getMessage());
    }
}


    // =====================================================================
    // EDITAR CLIENTE
    // =====================================================================

    public function edit($clienteId)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/clientes')->with('error', 'No tienes permisos');
        }

        $cliente = $this->clienteModel->getClienteCompleto($clienteId);
        
        if (!$cliente) {
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado');
        }

        // Obtener datos de persona moral si existe
        $persona_moral = [];
        if ($cliente->persona_moral == 1) {
            $persona_moral = $this->personaMoralModel->getByClienteId($clienteId) ?? [];
        }

        // Valores dummy para documentos (funcionalidad no implementada)
        $documentos = [];
        $estadisticasDocumentos = [
            'subidos' => 0,
            'total_esenciales' => 0,
            'total' => 0,
            'porcentaje' => 0
        ];
        $tiposDocumento = [];

        $data = [
            'titulo' => 'Editar Cliente: ' . $cliente->getNombreCompleto(),
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Clientes', 'url' => '/admin/clientes'],
                ['name' => 'Editar', 'url' => '']
            ],
            'cliente' => $cliente,
            'persona_moral' => $persona_moral,
            'documentos' => $documentos,
            'estadisticas_documentos' => $estadisticasDocumentos,
            'tipos_documento' => $tiposDocumento,
            'empresas' => $this->getEmpresas(),
            'estados_civiles' => $this->getEstadosCiviles(),
            'fuentes_informacion' => $this->getFuentesInformacion()
        ];


        return view('admin/clientes/edit', $data);
    }


    /**
     * ============================================================================
     * REGENERAR MÉTODO UPDATE COMPLETO - FLUJO DE EDICIÓN REGENERADO
     * ============================================================================
     */
    public function update($clienteId)
    {
        // Validar método HTTP
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/admin/clientes')->with('error', 'Método no permitido');
        }

        // Validar permisos
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/clientes')->with('error', 'No tienes permisos para esta acción');
        }

        // Obtener cliente existente
        $cliente = $this->clienteModel->find($clienteId);
        if (!$cliente) {
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado');
        }

        // =====================================================================
        // VALIDACIONES DE DATOS
        // =====================================================================
        
        $formData = $this->request->getPost();
        
        
        // Validaciones básicas
        $rules = [
            'nombres' => 'required|min_length[2]|max_length[100]',
            'apellido_paterno' => 'required|min_length[2]|max_length[50]',
            'apellido_materno' => 'required|min_length[2]|max_length[50]',
            'email' => "required|valid_email|max_length[255]|is_unique[auth_identities.secret,auth_identities.user_id,{$cliente->user_id}]",
            'telefono' => 'required|min_length[10]|max_length[15]',
            'persona_moral' => 'permit_empty|in_list[0,1]',
            'etapa_proceso' => 'permit_empty|in_list[interesado,calificado,documentacion,contrato,cerrado]'
        ];
        
        // Validación condicional para persona moral
        if ($formData['persona_moral'] == 1) {
            $rules['empresa_razon_social'] = 'required|max_length[500]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors())
                           ->with('error', 'Corrige los errores marcados en rojo');
        }

        // =====================================================================
        // INICIAR TRANSACCIÓN
        // =====================================================================
        
        $this->db->transStart();

        try {
            // =====================================================================
            // 1. ACTUALIZAR DATOS PRINCIPALES DEL CLIENTE
            // =====================================================================
            
            $clienteData = [
                'nombres' => strtoupper(trim($formData['nombres'])),
                'apellido_paterno' => strtoupper(trim($formData['apellido_paterno'])),
                'apellido_materno' => strtoupper(trim($formData['apellido_materno'])),
                'genero' => $formData['genero'] ?? 'M',
                'fecha_nacimiento' => !empty($formData['fecha_nacimiento']) ? $formData['fecha_nacimiento'] : null,
                'nacionalidad' => !empty($formData['nacionalidad']) ? strtoupper(trim($formData['nacionalidad'])) : 'MEXICANA',
                'profesion' => !empty($formData['profesion']) ? strtoupper(trim($formData['profesion'])) : null,
                'email' => strtolower(trim($formData['email'])),
                'telefono' => preg_replace('/[^0-9]/', '', $formData['telefono']),
                'rfc' => !empty($formData['rfc']) ? strtoupper(trim($formData['rfc'])) : null,
                'curp' => !empty($formData['curp']) ? strtoupper(trim($formData['curp'])) : null,
                'identificacion' => $formData['identificacion'] ?? 'ine',
                'numero_identificacion' => !empty($formData['numero_identificacion']) ? trim($formData['numero_identificacion']) : null,
                'lugar_nacimiento' => !empty($formData['lugar_nacimiento']) ? strtoupper(trim($formData['lugar_nacimiento'])) : null,
                'estado_civil' => $formData['estado_civil'] ?? 'soltero',
                'fuente_informacion' => $formData['fuente_informacion'] ?? 'referido',
                'etapa_proceso' => $formData['etapa_proceso'] ?? 'interesado',
                'notas_internas' => !empty($formData['notas_internas']) ? trim($formData['notas_internas']) : null,
                'persona_moral' => $formData['persona_moral'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Agregar razon_social si es persona moral
            if ($formData['persona_moral'] == 1) {
                $clienteData['razon_social'] = !empty($formData['empresa_razon_social']) ? 
                    strtoupper(trim($formData['empresa_razon_social'])) : null;
            } else {
                $clienteData['razon_social'] = null; // Limpiar si cambió a persona física
            }

            // Actualizar cliente principal
            if (!$this->db->table('clientes')->where('id', $clienteId)->update($clienteData)) {
                throw new \RuntimeException('Error al actualizar datos del cliente');
            }

            // Actualizar email en Shield si cambió
            $emailActual = $this->db->table('auth_identities')
                                  ->where('user_id', $cliente->user_id)
                                  ->where('type', 'email_password')
                                  ->get()
                                  ->getRow();

            if ($emailActual && $emailActual->secret !== $clienteData['email']) {
                $this->db->table('auth_identities')
                        ->where('user_id', $cliente->user_id)
                        ->where('type', 'email_password')
                        ->update(['secret' => $clienteData['email']]);
                        
                log_message('info', "Email actualizado en Shield para usuario {$cliente->user_id}");
            }

            // =====================================================================
            // 2. ACTUALIZAR/CREAR DIRECCIÓN
            // =====================================================================
            
            if (!empty($formData['calle'])) {
                $direccionData = [
                    'cliente_id' => $clienteId,
                    'domicilio' => strtoupper(trim($formData['calle'])),
                    'numero' => !empty($formData['numero_interior']) ? trim($formData['numero_interior']) : 'S/N',
                    'colonia' => !empty($formData['colonia']) ? strtoupper(trim($formData['colonia'])) : null,
                    'codigo_postal' => !empty($formData['codigo_postal']) ? trim($formData['codigo_postal']) : null,
                    'ciudad' => !empty($formData['ciudad']) ? strtoupper(trim($formData['ciudad'])) : null,
                    'estado' => !empty($formData['estado']) ? strtoupper(trim($formData['estado'])) : null,
                    'tiempo_radicando' => !empty($formData['tiempo_residencia']) ? trim($formData['tiempo_residencia']) : null,
                    'tipo_residencia' => $formData['tipo_vivienda'] ?? 'propia',
                    'tipo' => 'principal',
                    'activo' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Buscar dirección existente
                $direccionExistente = $this->db->table('direcciones_clientes')
                                              ->where('cliente_id', $clienteId)
                                              ->where('tipo', 'principal')
                                              ->where('activo', 1)
                                              ->get()
                                              ->getRowArray();

                if ($direccionExistente) {
                    // Actualizar existente
                    $this->db->table('direcciones_clientes')
                            ->where('id', $direccionExistente['id'])
                            ->update($direccionData);
                } else {
                    // Crear nueva
                    $direccionData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->table('direcciones_clientes')->insert($direccionData);
                }
                
                log_message('info', "Dirección actualizada para cliente {$clienteId}");
            }

            // =====================================================================
            // 3. ACTUALIZAR/CREAR INFORMACIÓN LABORAL
            // =====================================================================
            
            if (!empty($formData['empresa'])) {
                $laboralData = [
                    'cliente_id' => $clienteId,
                    'nombre_empresa' => strtoupper(trim($formData['empresa'])),
                    'puesto_cargo' => !empty($formData['puesto']) ? strtoupper(trim($formData['puesto'])) : null,
                    'antiguedad' => !empty($formData['antiguedad']) ? trim($formData['antiguedad']) : null,
                    'telefono_trabajo' => !empty($formData['telefono_empresa']) ? preg_replace('/[^0-9]/', '', $formData['telefono_empresa']) : null,
                    'direccion_trabajo' => !empty($formData['direccion_empresa']) ? trim($formData['direccion_empresa']) : null,
                    'salario' => !empty($formData['salario_mensual']) ? (float)$formData['salario_mensual'] : 0,
                    'activo' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Buscar información laboral existente
                $laboralExistente = $this->db->table('informacion_laboral_clientes')
                                            ->where('cliente_id', $clienteId)
                                            ->where('activo', 1)
                                            ->get()
                                            ->getRowArray();

                if ($laboralExistente) {
                    // Actualizar existente
                    $this->db->table('informacion_laboral_clientes')
                            ->where('id', $laboralExistente['id'])
                            ->update($laboralData);
                } else {
                    // Crear nueva
                    $laboralData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->table('informacion_laboral_clientes')->insert($laboralData);
                }
                
                log_message('info', "Información laboral actualizada para cliente {$clienteId}");
            }

            // =====================================================================
            // 4. ACTUALIZAR/CREAR REFERENCIAS
            // =====================================================================
            
            // Desactivar referencias existentes
            $this->db->table('referencias_clientes')
                    ->where('cliente_id', $clienteId)
                    ->update(['activo' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

            // Crear nuevas referencias
            for ($i = 1; $i <= 2; $i++) {
                $nombreReferencia = $formData["referencia{$i}_nombre"] ?? '';
                
                if (!empty($nombreReferencia)) {
                    $referenciaData = [
                        'cliente_id' => $clienteId,
                        'numero' => $i,
                        'nombre_completo' => strtoupper(trim($nombreReferencia)),
                        'telefono' => !empty($formData["referencia{$i}_telefono"]) ? 
                            preg_replace('/[^0-9]/', '', $formData["referencia{$i}_telefono"]) : null,
                        'parentesco' => !empty($formData["referencia{$i}_parentesco"]) ? 
                            strtoupper(trim($formData["referencia{$i}_parentesco"])) : null,
                        'tipo' => "referencia_{$i}",
                        'activo' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->db->table('referencias_clientes')->insert($referenciaData);
                    log_message('info', "Referencia {$i} actualizada para cliente {$clienteId}");
                }
            }

            // =====================================================================
            // 5. ACTUALIZAR/CREAR INFORMACIÓN DEL CÓNYUGE
            // =====================================================================
            
            // Solo procesar si el estado civil requiere cónyuge
            if (in_array($formData['estado_civil'], ['casado', 'union_libre'])) {
                $nombreConyuge = $formData['conyuge_nombre'] ?? '';
                
                // Solo procesar si hay datos del cónyuge
                if (!empty($nombreConyuge)) {
                    $conyugeData = [
                        'cliente_id' => $clienteId,
                        'nombre_completo' => strtoupper(trim($nombreConyuge)),
                        'profesion' => !empty($formData['conyuge_profesion']) ? strtoupper(trim($formData['conyuge_profesion'])) : null,
                        'email' => !empty($formData['conyuge_email']) ? strtolower(trim($formData['conyuge_email'])) : null,
                        'telefono' => !empty($formData['conyuge_telefono']) ? preg_replace('/[^0-9]/', '', $formData['conyuge_telefono']) : null,
                        'activo' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];


                    // Buscar información del cónyuge existente
                    $conyugeExistente = $this->db->table('informacion_conyuge_clientes')
                                                ->where('cliente_id', $clienteId)
                                                ->where('activo', 1)
                                                ->get()
                                                ->getRowArray();

                    if ($conyugeExistente) {
                        // Actualizar existente
                        $this->db->table('informacion_conyuge_clientes')
                                ->where('id', $conyugeExistente['id'])
                                ->update($conyugeData);
                        log_message('info', "Información del cónyuge actualizada para cliente {$clienteId}");
                    } else {
                        // Crear nueva
                        $conyugeData['created_at'] = date('Y-m-d H:i:s');
                        $this->db->table('informacion_conyuge_clientes')->insert($conyugeData);
                        log_message('info', "Información del cónyuge creada para cliente {$clienteId}");
                    }
                } else {
                    log_message('info', "No se proporcionaron datos del cónyuge para cliente {$clienteId}");
                }
            } else {
                // El estado civil no requiere cónyuge - desactivar información existente
                $this->db->table('informacion_conyuge_clientes')
                        ->where('cliente_id', $clienteId)
                        ->update(['activo' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
                log_message('info', "Información del cónyuge desactivada para cliente {$clienteId} (estado civil: {$formData['estado_civil']})");
            }

            // =====================================================================
            // 6. ACTUALIZAR/CREAR DATOS DE PERSONA MORAL
            // =====================================================================
            
            if ($formData['persona_moral'] == 1) {
                // Es persona moral - guardar datos empresariales
                $personaMoralData = [
                    'rfc_empresa' => !empty($formData['empresa_rfc']) ? strtoupper(trim($formData['empresa_rfc'])) : null,
                    'direccion_fiscal' => !empty($formData['empresa_direccion_fiscal']) ? trim($formData['empresa_direccion_fiscal']) : null,
                    'telefono_empresa' => !empty($formData['empresa_telefono']) ? preg_replace('/[^0-9]/', '', $formData['empresa_telefono']) : null,
                    'email_empresa' => !empty($formData['empresa_email']) ? strtolower(trim($formData['empresa_email'])) : null,
                    'razon_social' => !empty($formData['empresa_razon_social']) ? strtoupper(trim($formData['empresa_razon_social'])) : null,
                    'activo' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Usar el método del modelo para crear o actualizar
                if (!$this->personaMoralModel->guardarPersonaMoral($clienteId, $personaMoralData)) {
                    throw new \RuntimeException('Error al guardar datos de persona moral');
                }
                
                log_message('info', "Datos de persona moral actualizados para cliente {$clienteId}");
            } else {
                // Es persona física - eliminar datos de persona moral si existen
                $this->personaMoralModel->eliminarPersonaMoral($clienteId);
                log_message('info', "Datos de persona moral eliminados para cliente {$clienteId} (cambió a persona física)");
            }

            // =====================================================================
            // 7. FINALIZAR TRANSACCIÓN
            // =====================================================================
            
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción');
            }

            log_message('info', "Cliente {$clienteId} actualizado exitosamente por usuario " . auth()->user()->id);

            return redirect()->to('/admin/clientes/show/' . $clienteId)
                           ->with('success', 'Cliente actualizado exitosamente');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', "Error actualizando cliente {$clienteId}: " . $e->getMessage());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }


    // =====================================================================
    // VER CLIENTE
    // =====================================================================

    public function show($clienteId)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/clientes')->with('error', 'No tienes permisos');
        }

        // DEBUG: Log para rastrear
        log_message('debug', "[CLIENTE_SHOW] Iniciando show para cliente ID: {$clienteId}");
        
        $cliente = $this->clienteModel->getClienteCompleto($clienteId);
        
        if (!$cliente) {
            log_message('error', "[CLIENTE_SHOW] Cliente {$clienteId} no encontrado");
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado');
        }
        
        log_message('debug', "[CLIENTE_SHOW] Cliente encontrado: " . $cliente->getNombreCompleto());

        // Valores dummy para documentos (funcionalidad no implementada)
        $documentos = [];
        $estadisticasDocumentos = [
            'subidos' => 0,
            'total_esenciales' => 0,
            'total' => 0,
            'porcentaje' => 0
        ];
        $tiposDocumento = [];

        $data = [
            'titulo' => 'Detalles del Cliente: ' . $cliente->getNombreCompleto(),
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Clientes', 'url' => '/admin/clientes'],
                ['name' => 'Ver', 'url' => '']
            ],
            'cliente' => $cliente,
            'documentos' => $documentos,
            'estadisticas_documentos' => $estadisticasDocumentos,
            'tipos_documento' => $tiposDocumento
        ];
        
        log_message('debug', "[CLIENTE_SHOW] Data preparada, llamando a la vista");
        
        try {
            return view('admin/clientes/show', $data);
        } catch (\Exception $e) {
            log_message('error', "[CLIENTE_SHOW] Error al cargar vista: " . $e->getMessage());
            throw $e;
        }
    }

    // =====================================================================
    // ACCIONES AJAX
    // =====================================================================

public function cambiarEstado()
{
    if (!$this->request->isAJAX() || $this->request->getMethod() !== 'POST') {
        return $this->response->setStatusCode(404);
    }

    $clienteId = $this->request->getPost('cliente_id');
    $activo = $this->request->getPost('activo') === 'true';

    try {
        // Obtener cliente
        $cliente = $this->clienteModel->find($clienteId);
        if (!$cliente) {
            throw new \RuntimeException('Cliente no encontrado');
        }

        // Verificar que tenga user_id
        if (!$cliente->user_id) {
            throw new \RuntimeException('Cliente no tiene usuario asociado');
        }

        $userModel = new \App\Models\UserModel();
        
        $resultado = false;
        
        if ($activo) {
            $resultado = $userModel->activateUser($cliente->user_id);
            $mensaje = 'Usuario activado correctamente';
        } else {
            $resultado = $userModel->deactivateUser($cliente->user_id);
            $mensaje = 'Usuario desactivado correctamente';
        }

        if (!$resultado) {
            throw new \RuntimeException('Error al cambiar el estado del usuario');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $mensaje,
            'estado_actual' => $activo ? 'activo' : 'inactivo'
        ]);

    } catch (\Exception $e) {
        log_message('error', "Error en cambiarEstado: " . $e->getMessage());
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}


    public function cambiarEtapa()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(404);
        }

        $clienteId = $this->request->getPost('cliente_id');
        $nuevaEtapa = $this->request->getPost('etapa');

        try {
            if ($this->clienteModel->cambiarEtapa($clienteId, $nuevaEtapa)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Etapa actualizada correctamente'
                ]);
            } else {
                throw new \RuntimeException('Etapa no válida');
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function buscar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $termino = $this->request->getGet('q') ?? '';
        
        if (strlen($termino) < 3) {
            return $this->response->setJSON([]);
        }

        try {
            $clientes = $this->clienteModel->buscarClientes($termino, 10);
            
            $resultados = [];
            foreach ($clientes as $cliente) {
                $resultados[] = [
                    'id' => $cliente['id'],
                    'nombre' => $this->formatearNombreCompleto($cliente),
                    'email' => $cliente['email_usuario'] ?? $cliente['email'],
                    'telefono' => $cliente['telefono']
                ];
            }

            return $this->response->setJSON($resultados);

        } catch (\Exception $e) {
            log_message('error', 'Error en búsqueda de clientes: ' . $e->getMessage());
            return $this->response->setJSON([]);
        }
    }

    // =====================================================================
    // MÉTODOS PRIVADOS DE UTILIDAD
    // =====================================================================

    private function getEmpresas(): array
    {
        return $this->db->table('empresas')
                       ->where('activo', 1)
                       ->orderBy('nombre', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    private function getEstadosCiviles(): array
    {
        return $this->db->table('estados_civiles')
                       ->where('activo', 1)
                       ->orderBy('id', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    private function getFuentesInformacion(): array
    {
        $fuentes = $this->fuenteInformacionModel->obtenerTodosActivos();
        $resultado = [];
        
        foreach ($fuentes as $fuente) {
            $resultado[] = [
                'valor' => $fuente->valor,
                'nombre' => $fuente->nombre
            ];
        }
        
        return $resultado;
    }

    private function getEtapasProceso(): array
    {
        return [
            'interesado' => 'Interesado',
            'calificado' => 'Calificado',
            'documentacion' => 'En Documentación',
            'contrato' => 'En Contrato',
            'cerrado' => 'Cerrado'
        ];
    }

    private function formatearNombreCompleto(array $cliente): string
    {
        return trim($cliente['nombres'] . ' ' . $cliente['apellido_paterno'] . ' ' . $cliente['apellido_materno']);
    }

    private function formatearTelefono(string $telefono): string
    {
        if (empty($telefono)) return 'Sin teléfono';
        
        if (strlen($telefono) === 10) {
            return '(' . substr($telefono, 0, 2) . ') ' . substr($telefono, 2, 4) . '-' . substr($telefono, 6, 4);
        }
        
        return $telefono;
    }

    private function formatearEtapa(string $etapa): string
    {
        $etapas = $this->getEtapasProceso();
        return $etapas[$etapa] ?? $etapa;
    }

    private function formatearFecha(string $fecha): string
    {
        return date('d/m/Y H:i', strtotime($fecha));
    }

    private function generarBotonesAccion(int $clienteId, int $activo): string
    {
        $botones = [];
        
        // Botón ver
        $botones[] = '<a href="' . site_url("/admin/clientes/show/{$clienteId}") . '" 
                         class="btn btn-info btn-sm" title="Ver detalles">
                         <i class="fas fa-eye"></i>
                      </a>';
        
        // Botón editar
        $botones[] = '<a href="' . site_url("/admin/clientes/edit/{$clienteId}") . '" 
                         class="btn btn-warning btn-sm" title="Editar">
                         <i class="fas fa-edit"></i>
                      </a>';
        
        // Botón activar/desactivar
        $iconoEstado = $activo ? 'fa-toggle-on text-success' : 'fa-toggle-off text-danger';
        $tituloEstado = $activo ? 'Desactivar' : 'Activar';
        
        $botones[] = '<button class="btn btn-sm btn-outline-secondary btn-cambiar-estado" 
                             data-cliente-id="' . $clienteId . '" 
                             data-activo="' . ($activo ? 'false' : 'true') . '" 
                             title="' . $tituloEstado . '">
                         <i class="fas ' . $iconoEstado . '"></i>
                      </button>';
        
        // Botón eliminar (soft delete)
        $botones[] = '<button class="btn btn-sm btn-outline-danger btn-eliminar-cliente" 
                             data-cliente-id="' . $clienteId . '" 
                             title="Eliminar Cliente">
                         <i class="fas fa-trash-alt"></i>
                      </button>';
        
        return implode(' ', $botones);
    }

    // =====================================================================
    // MÉTODOS AUXILIARES PARA OBTENER DATOS
    // =====================================================================

    /**
     * Obtener asesores disponibles del staff
     */
    private function getAsesoresDisponibles(): array
    {
        return $this->db->table('staff s')
            ->select('s.user_id, s.nombres, s.tipo, u.active as user_active')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->where('u.active', 1)
            ->whereIn('s.tipo', ['superadmin', 'admin', 'supervendedor', 'vendedor', 'subvendedor'])
            ->orderBy('s.tipo', 'ASC')
            ->orderBy('s.nombres', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener información del usuario actual
     */
    private function getUsuarioActualInfo(): string
    {
        $user = auth()->user();
        
        // Intentar obtener nombre del staff
        $staffInfo = $this->db->table('staff')
            ->select('nombres')
            ->where('user_id', $user->id)
            ->get()
            ->getRow();
            
        if ($staffInfo) {
            return $staffInfo->nombres;
        }
        
        // Fallback al email
        return $user->getEmail();
    }

    



    /**
     * Eliminar cliente mediante soft delete
     */
    public function delete($clienteId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para eliminar clientes'
            ]);
        }

        $this->db->transStart();

        try {
            // Obtener datos del cliente
            $cliente = $this->clienteModel->find($clienteId);
            if (!$cliente) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);
            }

            $fechaEliminacion = date('Y-m-d H:i:s');
            $usuarioEliminacion = auth()->user()->id;

            // 1. Soft delete del usuario en Shield
            $this->db->table('users')
                    ->where('id', $cliente->user_id)
                    ->update([
                        'deleted_at' => $fechaEliminacion,
                        'active' => 0
                    ]);

            // 2. Soft delete en tablas relacionadas usando solo campo 'activo'
            $tablasRelacionadas = [
                'direcciones_clientes',
                'informacion_laboral_clientes', 
                'informacion_conyuge_clientes',
                'referencias_clientes'
            ];

            foreach ($tablasRelacionadas as $tabla) {
                try {
                    // Verificar si la tabla tiene updated_at
                    $fields = $this->db->getFieldNames($tabla);
                    $updateData = ['activo' => 0];
                    
                    if (in_array('updated_at', $fields)) {
                        $updateData['updated_at'] = $fechaEliminacion;
                    }
                    
                    $affectedRows = $this->db->table($tabla)
                            ->where('cliente_id', $clienteId)
                            ->update($updateData);
                    log_message('info', "Tabla {$tabla}: {$affectedRows} registros actualizados");
                } catch (\Exception $e) {
                    log_message('error', "Error actualizando tabla {$tabla}: " . $e->getMessage());
                    throw $e;
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción de eliminación');
            }

            log_message('info', "Cliente {$clienteId} eliminado (soft delete) por usuario {$usuarioEliminacion}");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cliente eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error eliminando cliente: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el cliente: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar magic link de bienvenida automáticamente
     */
    private function enviarMagicLinkBienvenida($user, $clienteId)
    {
        try {
            // Generar token de magic link usando Shield
            $tokenName = 'magic-link-' . bin2hex(random_bytes(16));
            
            // Crear token con 24 horas de duración
            $token = $user->generateAccessToken($tokenName);
            
            // URL del magic link que redirige a bienvenida
            $magicLinkUrl = site_url("cliente/bienvenida?token=" . $token->raw_token . "&email=" . urlencode($user->email));
            
            // Obtener datos del cliente para personalizar el email
            $cliente = $this->clienteModel->find($clienteId);
            $nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno);
            
            // Enviar email de bienvenida
            $email = \Config\Services::email();
            
            $email->setTo($user->email);
            $email->setSubject('Bienvenido a ANVAR - Configura tu cuenta');
            
            $mensaje = view('emails/bienvenida_cliente', [
                'nombre_cliente' => $nombreCompleto,
                'magic_link' => $magicLinkUrl,
                'email_cliente' => $user->email
            ]);
            
            $email->setMessage($mensaje);
            
            if ($email->send()) {
                log_message('info', "Email de bienvenida enviado exitosamente a: {$user->email}");
                return true;
            } else {
                log_message('error', "Error enviando email de bienvenida: " . $email->printDebugger());
                return false;
            }
            
        } catch (\Exception $e) {
            log_message('error', "Excepción enviando magic link de bienvenida: " . $e->getMessage());
            throw $e;
        }
    }

}