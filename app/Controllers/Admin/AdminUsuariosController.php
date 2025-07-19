<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StaffModel;

class AdminUsuariosController extends BaseController
{
    protected $userModel;
    protected $staffModel;
    protected $db;

    public function __construct()
    {
        // 🎯 Usar tu UserModel personalizado que retorna Entities
        $this->userModel = new \App\Models\UserModel();
        $this->staffModel = new StaffModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * =====================================================================
     * VISTA PRINCIPAL - MVP: Usuario Entities + filtros desde controlador
     * =====================================================================
     */
    public function index()
    {
        // 🔧 FIX: Verificación simple por grupos (aprovechar Shield nativo)
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/dashboard')->with('error', 'No tienes permisos para ver usuarios');
        }

        // 🎯 Obtener filtros desde el request (controlador maneja request, no vista)
        $filtros = [
            'buscar' => $this->request->getGet('buscar') ?? '',
            'tipo' => $this->request->getGet('tipo') ?? '',
            'estado' => $this->request->getGet('estado') ?? ''
        ];

        // 🎯 Obtener usuarios como Entities
        $usuarios = $this->getUsuariosAdminEntities($filtros);

        $data = [
            'titulo' => 'Gestión de Usuarios Administrativos',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/admin/dashboard'],
                ['name' => 'Usuarios', 'url' => '']
            ],
            // 🎯 MVP: Estadísticas simples
            'stats' => $this->getStatsSimple(),
            // 🎯 MVP: Filtros para la vista
            'tipos_filtro' => $this->getTiposFiltroSimple(),
            // 🎯 Entity-First: Pasar entities y filtros actuales
            'usuarios' => $usuarios,
            'filtros_actuales' => $filtros
        ];

        return view('admin/usuarios/index', $data);
    }

    /**
     * =====================================================================
     * CRUD SIMPLE - Crear usuario
     * =====================================================================
     */
    public function create()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/usuarios')->with('error', 'No tienes permisos para crear usuarios');
        }

        $data = [
            'titulo' => 'Crear Usuario Administrativo',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => '/admin/dashboard'],
                ['name' => 'Usuarios', 'url' => '/admin/usuarios'],
                ['name' => 'Crear', 'url' => '']
            ],
            'grupos_disponibles' => $this->getGruposDisponibles(),
            'agencias_disponibles' => $this->getAgenciasDisponibles()
        ];

        return view('admin/usuarios/create', $data);
    }

    /**
     * Procesar creación - MVP con tabla Staff + Shield
     */
    public function store()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/usuarios')->with('error', 'No tienes permisos');
        }

        // 🎯 MVP: Validación completa usando CodeIgniter nativo
        $rules = [
            'nombres' => 'required|min_length[2]|max_length[100]',
            'apellido_paterno' => 'permit_empty|max_length[100]',
            'apellido_materno' => 'permit_empty|max_length[100]',
            'fecha_nacimiento' => 'permit_empty|valid_date',
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'telefono' => 'permit_empty|min_length[10]|max_length[15]',
            'agencia' => 'permit_empty|max_length[100]',
            'grupo' => 'required|in_list[superadmin,admin,supervendedor,vendedor,subvendedor,visor]'
        ];

        $messages = [
            'nombres' => [
                'required' => 'El nombre es obligatorio',
                'min_length' => 'El nombre debe tener al menos 2 caracteres'
            ],
            'apellido_paterno' => [
                'max_length' => 'El apellido paterno no puede exceder 100 caracteres'
            ],
            'apellido_materno' => [
                'max_length' => 'El apellido materno no puede exceder 100 caracteres'
            ],
            'fecha_nacimiento' => [
                'valid_date' => 'La fecha de nacimiento no es válida'
            ],
            'email' => [
                'required' => 'El email es obligatorio',
                'valid_email' => 'El email debe ser válido',
                'is_unique' => 'Este email ya está registrado'
            ],
            'password' => [
                'required' => 'La contraseña es obligatoria',
                'min_length' => 'La contraseña debe tener al menos 8 caracteres'
            ],
            'password_confirm' => [
                'required' => 'Debe confirmar la contraseña',
                'matches' => 'Las contraseñas no coinciden'
            ],
            'telefono' => [
                'min_length' => 'El teléfono debe tener al menos 10 dígitos'
            ],
            'grupo' => [
                'required' => 'Debe seleccionar un grupo',
                'in_list' => 'Grupo no válido'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 🎯 Transacción para crear Usuario + Staff
        $this->db->transStart();

        try {
            // PASO 1: Crear usuario usando el UserModel con métodos de Shield
            $email = strtolower(trim($this->request->getPost('email')));
            $password = $this->request->getPost('password');
            
            // 🔧 FIX: Usar el método createUser del UserModel que maneja Shield correctamente
            $userEntity = $this->userModel->createUser([
                'email' => $email,
                'password' => $password,
                'active' => 1
            ]);
            
            if (!$userEntity || !$userEntity->id) {
                throw new \Exception('Error al crear usuario: ' . implode(', ', $this->userModel->errors()));
            }

            $userId = $userEntity->id;
            
            // PASO 2: Asignar grupo usando Shield nativo
            $userEntity->addGroup($this->request->getPost('grupo'));

            // PASO 3: Crear Staff Entity - Los mutadores se encargan de la limpieza automática
            $staffData = [
                'user_id' => $userId,
                'nombres' => $this->request->getPost('nombres'), // Mutator limpia automáticamente
                'apellido_paterno' => $this->request->getPost('apellido_paterno'),
                'apellido_materno' => $this->request->getPost('apellido_materno'),
                'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?: null,
                'telefono' => $this->request->getPost('telefono'), // Mutator limpia automáticamente
                'agencia' => $this->request->getPost('agencia'), // Mutator limpia automáticamente
                'tipo' => $this->request->getPost('grupo'), // El grupo es el tipo
                'notas' => trim($this->request->getPost('notas')) ?: null,
                'creado_por' => auth()->user()->id, // 🎯 ID de quien lo creó
            ];

            // 🎯 Entity-First: Crear usando Staff Entity - Los mutadores se ejecutan automáticamente
            $staffEntity = new \App\Entities\Staff($staffData);
            
            if (!$this->staffModel->save($staffEntity)) {
                throw new \Exception('Error al crear información de staff: ' . implode(', ', $this->staffModel->errors()));
            }

            $this->db->transCommit();

            return redirect()->to('/admin/usuarios')->with('success', 'Usuario creado exitosamente');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error creando usuario: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }


    /**
     * Método editar usuario
     */
    /**
 * =====================================================================
 * EDITAR USUARIO - Entity-First + MVP
 * =====================================================================
 */
public function edit($userId)
{
    // 🎯 PASO 1: Verificar permisos
    $usuarioActual = auth()->user();
    $puedeEditar = false;
    $esPropia = false;
    
    if ($usuarioActual->inGroup('superadmin', 'admin')) {
        $puedeEditar = true;
    } elseif ($usuarioActual->id == $userId) {
        $puedeEditar = true;
        $esPropia = true;
    }
    
    if (!$puedeEditar) {
        return redirect()->to('/admin/usuarios')->with('error', 'No tienes permisos para editar este usuario');
    }

    // 🎯 PASO 2: Obtener Usuario desde Shield (Entity-First)
    $user = $this->userModel->find($userId);
    if (!$user) {
        return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado');
    }

    // 🎯 PASO 3: Obtener información de Staff (puede no existir)
    $staffInfo = $this->staffModel->getByUserId($userId);
    $esNuevoStaff = !$staffInfo;

    // 🎯 PASO 4: Obtener email directamente desde el usuario (Shield nativo)
    $email = $user->email ?? '';
    
    // 🎯 PASO 5: Obtener grupo actual del usuario (Shield nativo)
    $grupoActual = $this->getGrupoFromShield($userId);

    // 🎯 PASO 6: Preparar datos para la vista (MVP)
    $data = [
        'titulo' => $esNuevoStaff ? 'Completar Información de Usuario' : 'Editar Usuario Administrativo',
        'breadcrumb' => [
            ['name' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['name' => 'Usuarios', 'url' => '/admin/usuarios'],
            ['name' => $esNuevoStaff ? 'Completar' : 'Editar', 'url' => '']
        ],
        // 🎯 Entity-First: Pasar entities directamente
        'user' => $user,
        'staffInfo' => $staffInfo, // Puede ser null
        'email' => $email,
        'grupo' => $grupoActual,
        // 🎯 MVP: Flags simples para la vista
        'es_nuevo_staff' => $esNuevoStaff,
        'es_edicion_propia' => $esPropia,
        // 🎯 Catálogos para formularios
        'grupos_disponibles' => $this->getGruposDisponiblesParaEdicion($usuarioActual, $grupoActual, $esPropia),
        'agencias_disponibles' => $this->getAgenciasDisponibles()
    ];

    return view('admin/usuarios/edit', $data);
}


/**
 * =====================================================================
 * MÉTODOS AUXILIARES PARA EDICIÓN - CodeIgniter Nativo
 * =====================================================================
 */

private function getGrupoFromShield(int $userId): string
{
    $result = $this->db->table('auth_groups_users')
        ->select('group')
        ->where('user_id', $userId)
        ->get()
        ->getRow();
    
    return $result ? $result->group : '';
    
}

/**
 * Obtener grupos disponibles según permisos
 */
private function getGruposDisponiblesParaEdicion($usuarioActual, $grupoActual, $esPropia): array
{
    $todosLosGrupos = [
        'superadmin' => 'Super Administrador',
        'admin' => 'Administrador', 
        'supervendedor' => 'Super Vendedor',
        'vendedor' => 'Vendedor',
        'subvendedor' => 'Sub-Vendedor',
        'visor' => 'Visor'
    ];

    // 🎯 Si es edición propia, solo mostrar el grupo actual (no editable)
    if ($esPropia) {
        return [$grupoActual => $todosLosGrupos[$grupoActual] ?? 'Sin grupo'];
    }

    // 🎯 Superadmin puede asignar cualquier grupo
    if ($usuarioActual->inGroup('superadmin')) {
        return $todosLosGrupos;
    }

    // 🎯 Admin puede asignar todos excepto superadmin
    if ($usuarioActual->inGroup('admin')) {
        unset($todosLosGrupos['superadmin']);
        return $todosLosGrupos;
    }

    // 🎯 Otros casos (no deberían llegar aquí por verificación de permisos)
    return [$grupoActual => $todosLosGrupos[$grupoActual] ?? 'Sin grupo'];
}


/**
 * =====================================================================
 * ACTUALIZAR USUARIO - Entity-First + Transacciones
 * =====================================================================
 */
public function update($userId)
{
    if ($this->request->getMethod() !== 'POST') {
        return redirect()->to('/admin/usuarios');
    }

    // 🎯 PASO 1: Verificar permisos (mismo que edit)
    $usuarioActual = auth()->user();
    $puedeEditar = false;
    $esPropia = false;
    
    if ($usuarioActual->inGroup('superadmin', 'admin')) {
        $puedeEditar = true;
    } elseif ($usuarioActual->id == $userId) {
        $puedeEditar = true;
        $esPropia = true;
    }
    
    if (!$puedeEditar) {
        return redirect()->to('/admin/usuarios')->with('error', 'No tienes permisos para editar este usuario');
    }

    // 🎯 PASO 2: Verificar que el usuario existe
    $user = $this->userModel->find($userId);
    if (!$user) {
        return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado');
    }

    // 🎯 PASO 3: Obtener información actual de Staff
    $staffInfo = $this->staffModel->getByUserId($userId);
    $esNuevoStaff = !$staffInfo;

    // 🎯 PASO 4: Validación según el tipo de edición
    $rules = $this->getValidationRulesForUpdate($userId, $esPropia, $esNuevoStaff);
    
    if (!$this->validate($rules['rules'], $rules['messages'])) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    // 🎯 PASO 5: Procesar actualización con transacción
    $this->db->transStart();

    try {
        $formData = $this->request->getPost();
        
        // 🎯 PASO 5.1: Actualizar contraseña si se proporcionó (Shield nativo)
        if (!empty($formData['password'])) {
            $this->updatePasswordShield($userId, $formData['password']);
        }

        // 🎯 PASO 5.2: Actualizar grupo si no es edición propia (Shield nativo)
        if (!$esPropia && !empty($formData['grupo'])) {
            $this->updateGroupShield($userId, $formData['grupo']);
        }

        // 🎯 PASO 5.3: Crear o actualizar información de Staff (Entity-First)
        if ($esNuevoStaff) {
            $this->createStaffInfo($userId, $formData);
        } else {
            $this->updateStaffInfo($staffInfo, $formData);
        }

        $this->db->transCommit();

        $mensaje = $esNuevoStaff ? 'Información completada exitosamente' : 'Usuario actualizado exitosamente';
        return redirect()->to('/admin/usuarios')->with('success', $mensaje);

    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', 'Error actualizando usuario: ' . $e->getMessage());
        return redirect()->back()->withInput()->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
    }
}
    /**
     * Cambiar estado del usuario
     */
    public function cambiarEstado($id)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->back()->with('error', 'No tienes permisos');
        }

        $estado = $this->request->getPost('estado') ?? 1;
        
        // 🎯 Usar Query Builder nativo de CodeIgniter
        $updated = $this->db->table('users')
                           ->where('id', $id)
                           ->update(['active' => $estado]);

        if ($updated) {
            $mensaje = $estado ? 'Usuario activado' : 'Usuario desactivado';
            return redirect()->back()->with('success', $mensaje);
        }

        return redirect()->back()->with('error', 'Error al cambiar estado');
    }

    /**
     * =====================================================================
     * MÉTODOS AUXILIARES - MVP SIMPLE
     * =====================================================================
     */

    /**
     * Estadísticas de usuarios administrativos (desde Shield + Staff)
     */
    private function getStatsSimple(): array
    {
        // 🎯 Contar todos los usuarios administrativos desde Shield
        $totalAdmins = $this->db->table('auth_groups_users agu')
            ->join('users u', 'u.id = agu.user_id')
            ->whereIn('agu.group', ['superadmin', 'admin', 'supervendedor', 'vendedor', 'subvendedor', 'visor'])
            ->countAllResults();

        $activosAdmins = $this->db->table('auth_groups_users agu')
            ->join('users u', 'u.id = agu.user_id')
            ->whereIn('agu.group', ['superadmin', 'admin', 'supervendedor', 'vendedor', 'subvendedor', 'visor'])
            ->where('u.active', 1)
            ->countAllResults();

        return [
            'total' => $totalAdmins,
            'activos' => $activosAdmins,
            'inactivos' => $totalAdmins - $activosAdmins
        ];
    }

    /**
     * Filtros simples para formularios
     */
    private function getTiposFiltroSimple(): array
    {
        return [
            'grupos' => [
                'superadmin' => 'Super Administrador',
                'admin' => 'Administrador', 
                'supervendedor' => 'Super Vendedor',
                'vendedor' => 'Vendedor',
                'subvendedor' => 'Sub-Vendedor',
                'visor' => 'Visor'
            ],
            'estados' => [
                '1' => 'Activo',
                '0' => 'Inactivo'
            ]
        ];
    }

    /**
     * 🎯 Lógica Híbrida: Usuarios desde Shield + Staff opcional
     */
    private function getUsuariosAdminEntities(array $filtros = []): array
    {
        $pager = \Config\Services::pager();
        $perPage = 10;
        $page = $this->request->getVar('page') ?? 1;

        // 🎯 PASO 1: Obtener usuarios administrativos desde Shield
        $query = $this->db->table('users u')
            ->select('
                u.id,
                u.active,
                u.created_at,
                ai.secret as email,
                agu.group as user_group
            ')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'inner')
            ->whereIn('agu.group', ['superadmin', 'admin', 'supervendedor', 'vendedor', 'subvendedor', 'visor'])
            ->orderBy('u.created_at', 'DESC');

        // Aplicar filtros
        if (!empty($filtros['tipo'])) {
            $query->where('agu.group', $filtros['tipo']);
        }

        if ($filtros['estado'] !== '' && $filtros['estado'] !== null) {
            $query->where('u.active', $filtros['estado']);
        }

        if (!empty($filtros['buscar'])) {
            $query->groupStart()
                ->like('ai.secret', $filtros['buscar'])
                ->groupEnd();
        }

        // Contar total y obtener datos paginados
        $total = $query->countAllResults(false);
        $usuarios = $query->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        // 🎯 PASO 2: Para cada usuario, obtener o crear Staff Entity virtual
        $entitiesCompletas = [];
        foreach ($usuarios as $userData) {
            // Buscar información de staff existente
            $staffInfo = $this->staffModel->getByUserId($userData['id']);
            
            if ($staffInfo) {
                // 🎯 Usuario tiene información de staff completa
                $entitiesCompletas[] = $staffInfo;
            } else {
                // 🎯 Usuario sin información de staff - Crear Entity virtual
                $staffVirtual = new \App\Entities\Staff([
                    'user_id' => $userData['id'],
                    'nombres' => 'SIN INFORMACIÓN', // Se puede editar después
                    'telefono' => null,
                    'agencia' => null,
                    'tipo' => $userData['user_group'],
                    'notas' => null,
                    'creado_por' => null,
                    'created_at' => $userData['created_at']
                ]);
                
                // Agregar información del usuario Shield
                $staffVirtual->_shield_data = [
                    'email' => $userData['email'],
                    'active' => $userData['active'],
                    'user_group' => $userData['user_group']
                ];
                
                $entitiesCompletas[] = $staffVirtual;
            }
        }

        return [
            'data' => $entitiesCompletas,
            'pager' => $pager->makeLinks($page, $perPage, $total, 'default_full'),
            'total' => $total
        ];
    }

    /**
     * Grupos disponibles para crear usuarios
     */
    private function getGruposDisponibles(): array
    {
        // Solo superadmin puede crear superadmin
        $grupos = [
            'admin' => 'Administrador',
            'supervendedor' => 'Super Vendedor',
            'vendedor' => 'Vendedor',
            'subvendedor' => 'Sub-Vendedor',
            'visor' => 'Visor'
        ];

        // Si es superadmin, puede crear otros superadmin
        if (auth()->user()->inGroup('superadmin')) {
            $grupos = ['superadmin' => 'Super Administrador'] + $grupos;
        }

        return $grupos;
    }

    /**
     * Agencias disponibles (puedes personalizar según tu negocio)
     */
    private function getAgenciasDisponibles(): array
    {
        return [
            'MATRIZ' => 'Matriz/Principal',
            'NORTE' => 'Sucursal Norte',
            'SUR' => 'Sucursal Sur',
            'ORIENTE' => 'Sucursal Oriente',
            'PONIENTE' => 'Sucursal Poniente',
            'CENTRO' => 'Sucursal Centro'
        ];
    }

    /**
 * =====================================================================
 * MÉTODOS DE VALIDACIÓN Y ACTUALIZACIÓN
 * =====================================================================
 */

/**
 * Obtener reglas de validación según el contexto
 */
private function getValidationRulesForUpdate(int $userId, bool $esPropia, bool $esNuevoStaff): array
{
    $rules = [
        'nombres' => 'required|min_length[2]|max_length[100]',
        'apellido_paterno' => 'permit_empty|max_length[100]',
        'apellido_materno' => 'permit_empty|max_length[100]',
        'fecha_nacimiento' => 'permit_empty|valid_date',
        'telefono' => 'permit_empty|min_length[10]|max_length[15]',
        'agencia' => 'permit_empty|max_length[100]',
        'notas' => 'permit_empty'
        // 🎯 EMAIL NO SE VALIDA - No es editable según especificación
    ];

    $messages = [
        'nombres' => [
            'required' => 'El nombre es obligatorio',
            'min_length' => 'El nombre debe tener al menos 2 caracteres',
            'max_length' => 'El nombre no puede exceder 100 caracteres'
        ],
        'apellido_paterno' => [
            'max_length' => 'El apellido paterno no puede exceder 100 caracteres'
        ],
        'apellido_materno' => [
            'max_length' => 'El apellido materno no puede exceder 100 caracteres'
        ],
        'fecha_nacimiento' => [
            'valid_date' => 'La fecha de nacimiento no es válida'
        ],
        'telefono' => [
            'min_length' => 'El teléfono debe tener al menos 10 dígitos',
            'max_length' => 'El teléfono no puede exceder 15 dígitos'
        ]
    ];

    // 🎯 Solo validar contraseña si se proporcionó
    $password = $this->request->getPost('password');
    if (!empty($password)) {
        $rules['password'] = 'min_length[8]';
        $rules['password_confirm'] = 'matches[password]';
        
        $messages['password'] = [
            'min_length' => 'La contraseña debe tener al menos 8 caracteres'
        ];
        $messages['password_confirm'] = [
            'matches' => 'Las contraseñas no coinciden'
        ];
    }

    // 🎯 Solo validar grupo si no es edición propia
    if (!$esPropia) {
        $rules['grupo'] = 'required|in_list[superadmin,admin,supervendedor,vendedor,subvendedor,visor]';
        $messages['grupo'] = [
            'required' => 'Debe seleccionar un grupo',
            'in_list' => 'Grupo no válido'
        ];
    }

    return ['rules' => $rules, 'messages' => $messages];
}

/**
 * Actualizar contraseña usando Shield nativo
 */
private function updatePasswordShield(int $userId, string $newPassword): void
{
    $user = $this->userModel->find($userId);
    if ($user) {
        // 🎯 Usar método nativo de Shield/User Entity
        $user->password = $newPassword;
        $this->userModel->save($user);
    }
}
/**
 * Actualizar grupo usando Shield nativo
 */
private function updateGroupShield(int $userId, string $newGroup): void
{
    // 🎯 Obtener usuario y cambiar grupo (Shield nativo)
    $user = $this->userModel->find($userId);
    if ($user) {
        // Remover de todos los grupos administrativos
        $gruposAdmin = ['superadmin', 'admin', 'supervendedor', 'vendedor', 'subvendedor', 'visor'];
        foreach ($gruposAdmin as $grupo) {
            if ($user->inGroup($grupo)) {
                $user->removeGroup($grupo); // 🔧 FIX: Método correcto es removeGroup()
            }
        }
        
        // Agregar al nuevo grupo
        $user->addGroup($newGroup); // 🔧 FIX: Método correcto es addGroup()
    }
}

/**
 * Crear información de Staff (Entity-First)
 */
private function createStaffInfo(int $userId, array $formData): void
{
    $staffData = [
        'user_id' => $userId,
        'nombres' => $formData['nombres'], // El mutator se encarga de la limpieza
        'apellido_paterno' => $formData['apellido_paterno'] ?? null,
        'apellido_materno' => $formData['apellido_materno'] ?? null,
        'fecha_nacimiento' => $formData['fecha_nacimiento'] ?: null,
        'telefono' => $formData['telefono'], // El mutator se encarga de la limpieza
        'agencia' => $formData['agencia'] ?? null,
        'tipo' => $formData['grupo'] ?? 'visor', // Sincronizar con el grupo
        'notas' => trim($formData['notas']) ?: null,
        'creado_por' => auth()->user()->id
    ];

    // 🎯 Entity-First: Crear usando Staff Entity
    $staffEntity = new \App\Entities\Staff($staffData);
    
    if (!$this->staffModel->save($staffEntity)) {
        throw new \Exception('Error al crear información de staff: ' . implode(', ', $this->staffModel->errors()));
    }
}

/**
 * Actualizar información de Staff existente (Entity-First)
 */
private function updateStaffInfo(\App\Entities\Staff $staffInfo, array $formData): void
{
    // 🎯 Entity-First: Actualizar usando la entidad existente
    $staffInfo->nombres = $formData['nombres']; // Mutator automático
    $staffInfo->apellido_paterno = $formData['apellido_paterno'] ?? null;
    $staffInfo->apellido_materno = $formData['apellido_materno'] ?? null;
    $staffInfo->fecha_nacimiento = $formData['fecha_nacimiento'] ?: null;
    $staffInfo->telefono = $formData['telefono']; // Mutator automático
    $staffInfo->agencia = $formData['agencia'] ?? null;
    $staffInfo->tipo = $formData['grupo'] ?? $staffInfo->tipo; // Sincronizar con grupo
    $staffInfo->notas = trim($formData['notas']) ?: null;
    
    if (!$this->staffModel->save($staffInfo)) {
        throw new \Exception('Error al actualizar información de staff: ' . implode(', ', $this->staffModel->errors()));
    }
}

}