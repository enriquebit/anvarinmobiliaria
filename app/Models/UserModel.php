<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;

class UserModel extends ShieldUserModel
{
    /**
     * ============================================================================
     * CONFIGURACIÓN BÁSICA - SOLO SHIELD
     * ============================================================================
     */
    public function __construct()
    {
        $this->returnType = \App\Entities\User::class;
        parent::__construct();
        
        //log_message('info', '🔧 UserModel: Configurado SOLO para usuarios y Shield');
    }
    
    protected function initialize(): void
    {
        parent::initialize();
        $this->allowedFields = [
            ...$this->allowedFields,
        ];
    }

    /**
     * ============================================================================
     * ✅ MÉTODOS DE ACTIVACIÓN - SOLO USUARIOS (CORREGIDOS)
     * ============================================================================
     */
    
    /**
     * Activar usuario usando SOLO Shield
     * 
     * @param int $userId ID del usuario
     * @return bool
     */
    public function activateUser(int $userId): bool
    {
        try {
            log_message('info', "🟢 Activando usuario ID: $userId usando Shield");
            
            $user = $this->find($userId);
            if (!$user) {
                throw new \RuntimeException("Usuario ID $userId no encontrado");
            }
            
            // ✅ Usar método nativo de Shield
            $user->activate();
            
            log_message('info', "✅ Usuario ID $userId activado correctamente");
            return true;
            
        } catch (\Exception $e) {
            log_message('error', "❌ Error activando usuario ID $userId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desactivar usuario usando SOLO Shield
     * 
     * @param int $userId ID del usuario
     * @return bool
     */
    public function deactivateUser(int $userId): bool
    {
        try {
            log_message('info', "🔴 Desactivando usuario ID: $userId usando Shield");
            
            $user = $this->find($userId);
            if (!$user) {
                throw new \RuntimeException("Usuario ID $userId no encontrado");
            }
            
            // ✅ Usar método nativo de Shield
            $user->deactivate();
            
            log_message('info', "✅ Usuario ID $userId desactivado correctamente");
            return true;
            
        } catch (\Exception $e) {
            log_message('error', "❌ Error desactivando usuario ID $userId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si un usuario está activo
     * 
     * @param int $userId
     * @return bool|null
     */
    public function isUserActive(int $userId): ?bool
    {
        try {
            $user = $this->find($userId);
            return $user ? $user->active : null;
        } catch (\Exception $e) {
            log_message('error', "Error verificando estado de usuario $userId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ============================================================================
     * ✅ MÉTODOS DE GESTIÓN DE USUARIOS - SOLO SHIELD
     * ============================================================================
     */
    
    /**
     * Crear usuario con Shield (incluye auth_identities automáticamente)
     * 
     * @param array $userData - Debe incluir: email, password, active, force_password_change (opcional)
     */
    public function createUser(array $userData): ?\App\Entities\User
    {
        try {
            log_message('info', '🔧 Creando usuario con Shield: ' . $userData['email']);
            
            $email = $userData['email'];
            $password = $userData['password'];
            
            // 🔧 FIX: Usar el enfoque más simple - crear en steps separados
            
            // Step 1: Crear registro en tabla users
            $userInsertData = ['active' => $userData['active'] ?? 1];
            
            if (!$this->insert($userInsertData)) {
                $errors = $this->errors();
                log_message('error', '❌ Error al insertar en tabla users: ' . json_encode($errors));
                throw new \RuntimeException('Error al crear registro de usuario: ' . implode(', ', $errors));
            }
            
            $userId = $this->getInsertID();
            log_message('info', "✅ Registro usuario creado con ID: $userId");
            
            // Step 2: Crear auth_identity manualmente
            $forcePasswordChange = $userData['force_password_change'] ?? false;
            $this->createAuthIdentity($userId, $email, $password, $forcePasswordChange);
            log_message('info', "✅ Auth identity creada para usuario $userId (force_password_change: " . ($forcePasswordChange ? 'SÍ' : 'NO') . ")");
            
            // Verificar que se creó la identidad
            $db = \Config\Database::connect();
            $identity = $db->table('auth_identities')
                          ->where('user_id', $userId)
                          ->where('type', 'email_password')
                          ->get()
                          ->getRow();
            
            if ($identity) {
                log_message('info', "✅ Auth identity verificada para usuario {$userId}: {$identity->secret}");
            } else {
                log_message('warning', "⚠️ No se encontró auth_identity para usuario {$userId}, pero puede haberse creado");
            }
            
            // Recargar el usuario completo
            return $this->find($userId);
            
        } catch (\Exception $e) {
            log_message('error', '💥 Error en createUser(): ' . $e->getMessage());
            log_message('error', '🔍 Stack trace createUser: ' . $e->getTraceAsString());
            
            // Agregar el error a los errores del modelo para que se puedan recuperar
            $this->errors = ['createUser' => $e->getMessage()];
            
            return null;
        }
    }
    
    /**
     * Asignar grupo a usuario
     */
    public function assignGroup(int $userId, string $groupName): bool
    {
        try {
            $user = $this->find($userId);
            if (!$user) {
                return false;
            }
            
            $user->addGroup($groupName);
            log_message('info', "✅ Grupo '$groupName' asignado al usuario $userId");
            
            return true;
            
        } catch (\Exception $e) {
            log_message('error', "Error asignando grupo $groupName al usuario $userId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remover grupo de usuario
     */
    public function removeGroup(int $userId, string $groupName): bool
    {
        try {
            $user = $this->find($userId);
            if (!$user) {
                return false;
            }
            
            $user->removeGroup($groupName);
            log_message('info', "✅ Grupo '$groupName' removido del usuario $userId");
            
            return true;
            
        } catch (\Exception $e) {
            log_message('error', "Error removiendo grupo $groupName del usuario $userId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ============================================================================
     * ✅ CONSULTAS DE USUARIOS - SIN JOINS CON CLIENTES
     * ============================================================================
     */
    
    /**
     * Obtener usuarios por grupo
     */
    public function getUsersByGroup(string $group, int $limit = 50): array
    {
        return $this->select('
            users.id,
            users.active,
            users.created_at,
            users.updated_at,
            auth_identities.secret as email
        ')
        ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
        ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
        ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
        ->where('auth_groups.name', $group)
        ->limit($limit)
        ->orderBy('users.created_at', 'DESC')
        ->findAll();
    }
    
    /**
     * Buscar usuarios por email
     */
    public function searchUsersByEmail(string $email, int $limit = 20): array
    {
        return $this->select('
            users.id, 
            users.active,
            users.created_at,
            auth_identities.secret as email
        ')
        ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
        ->like('auth_identities.secret', $email)
        ->limit($limit)
        ->orderBy('users.created_at', 'DESC')
        ->findAll();
    }
    
    /**
     * Estadísticas de usuarios (SOLO usuarios)
     */
    public function getUserStats(): array
    {
        return [
            'total' => $this->countAll(),
            'activos' => $this->where('active', 1)->countAllResults(false),
            'inactivos' => $this->where('active', 0)->countAllResults(false),
        ];
    }
    
    /**
     * Contar usuarios por grupo
     */
    public function countUsersByGroup(): array
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('auth_groups_users agu')
                    ->select('ag.name as group_name, ag.title, COUNT(*) as total')
                    ->join('auth_groups ag', 'ag.id = agu.group_id')
                    ->groupBy('ag.id, ag.name, ag.title')
                    ->get()
                    ->getResultArray();
        
        return $result;
    }
    
    /**
     * Obtener usuario con información básica
     */
    public function getUserWithEmail(int $userId): ?object
    {
        return $this->select('
            users.id,
            users.active,
            users.created_at,
            users.updated_at,
            auth_identities.secret as email
        ')
        ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
        ->where('users.id', $userId)
        ->first();
    }
    
    /**
     * Verificar si existe usuario por email
     */
    public function existsByEmail(string $email): bool
    {
        $db = \Config\Database::connect();
        
        return $db->table('auth_identities')
                 ->where('secret', strtolower($email))
                 ->where('type', 'email_password')
                 ->countAllResults() > 0;
    }

    /**
     * ============================================================================
     * ✅ MÉTODO ESPECÍFICO PARA REGISTRO DE CLIENTES
     * ============================================================================
     */
    
    /**
     * Crear usuario con información de cliente (para registro público)
     */
    public function createClienteUser(array $formData): ?\App\Entities\User
    {
        try {
            log_message('info', '🔧 Creando usuario cliente: ' . $formData['email']);
            
            $db = \Config\Database::connect();
            $db->transStart();
            
            // PASO 1: Crear usuario básico con Shield
            $email = strtolower(trim($formData['email']));
            $password = $formData['password'];
            
            log_message('info', "🔧 Intentando crear usuario con email: $email");
            
            $userEntity = $this->createUser([
                'email' => $email,
                'password' => $password,
                'active' => 0,  // SEGURIDAD: Los clientes se crean INACTIVOS hasta aprobación administrativa
                'force_password_change' => true  // CLIENTES deben cambiar contraseña al primer login
            ]);
            
            if (!$userEntity) {
                $errors = $this->errors();
                log_message('error', '❌ createUser devolvió null. Errores del modelo: ' . json_encode($errors));
                throw new \RuntimeException('Error al crear usuario base: ' . implode(', ', $errors));
            }
            
            if (!$userEntity->id) {
                log_message('error', '❌ Usuario creado pero sin ID. UserEntity: ' . json_encode($userEntity->toArray()));
                throw new \RuntimeException('Error: Usuario creado pero sin ID válido');
            }
            
            $userId = $userEntity->id;
            log_message('info', "✅ Usuario base creado con ID: $userId");
            log_message('warning', "🔒 SEGURIDAD: Cliente creado INACTIVO - requiere aprobación administrativa para acceso");
            
            // PASO 2: Asignar grupo 'cliente'
            $userEntity->addGroup('cliente');
            log_message('info', "✅ Grupo 'cliente' asignado al usuario $userId");
            
            // PASO 3: Crear información de cliente
            $clienteModel = new \App\Models\ClienteModel();
            
            // 🔧 Preparar datos del cliente - solo campos que existen en la tabla
            $clienteData = [
                'user_id' => $userId,
                'nombres' => strtoupper(trim($formData['nombres'])),
                'apellido_paterno' => strtoupper(trim($formData['apellido_paterno'])),
                'apellido_materno' => strtoupper(trim($formData['apellido_materno'] ?? '')),
                'email' => $email,
                'telefono' => preg_replace('/[^0-9]/', '', $formData['telefono'] ?? ''),
                'genero' => strtoupper($formData['genero'] ?? 'M'),  // M,F,1,2 según ENUM
                'fecha_nacimiento' => !empty($formData['fecha_nacimiento']) ? $formData['fecha_nacimiento'] : null,
                'rfc' => !empty($formData['rfc']) ? strtoupper(trim($formData['rfc'])) : null,
                'etapa_proceso' => 'interesado',  // Valor inicial válido del ENUM
                'fecha_primer_contacto' => date('Y-m-d H:i:s'),  // Registro = primer contacto
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            log_message('info', "📝 Datos del cliente preparados: " . json_encode($clienteData, JSON_UNESCAPED_UNICODE));
            
            $clienteEntity = new \App\Entities\Cliente($clienteData);
            
            if (!$clienteModel->save($clienteEntity)) {
                $errors = $clienteModel->errors();
                log_message('error', '❌ Error al crear cliente: ' . json_encode($errors));
                throw new \RuntimeException('Error al crear información de cliente: ' . implode(', ', $errors));
            }
            
            $clienteId = $clienteModel->getInsertID();
            log_message('info', "✅ Cliente creado con ID: $clienteId");
            
            $db->transCommit();
            
            // PASO 4: Recargar el usuario completo
            $completeUser = $this->find($userId);
            
            log_message('info', "🎉 Usuario cliente creado exitosamente - User ID: $userId, Cliente ID: $clienteId");
            
            return $completeUser;
            
        } catch (\Exception $e) {
            if (isset($db)) {
                $db->transRollback();
            }
            log_message('error', '💥 Error en createClienteUser(): ' . $e->getMessage());
            log_message('error', '🔍 Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * ============================================================================
     * ✅ MÉTODOS AUXILIARES PARA CREACIÓN DE USUARIOS
     * ============================================================================
     */
    
    /**
     * Crear auth_identity para un usuario
     * 
     * @param int $userId
     * @param string $email
     * @param string $password
     * @param bool $forcePasswordChange - Si es true, NO se hashea la contraseña (fuerza cambio)
     */
    private function createAuthIdentity(int $userId, string $email, string $password, bool $forcePasswordChange = false): void
    {
        $db = \Config\Database::connect();
        
        // Si forcePasswordChange es true, NO guardamos la contraseña (secret2 = null)
        // Esto fuerza al usuario a configurar su propia contraseña
        $secret2 = $forcePasswordChange ? null : password_hash($password, PASSWORD_DEFAULT);
        
        $identityData = [
            'user_id' => $userId,
            'type' => 'email_password',
            'secret' => $email,
            'secret2' => $secret2,
            'expires' => null,
            'extra' => null,
            'force_reset' => $forcePasswordChange ? 1 : 0,
            'last_used_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $inserted = $db->table('auth_identities')->insert($identityData);
        
        if (!$inserted) {
            throw new \RuntimeException('Error al crear auth_identity');
        }
        
        log_message('info', "✅ Auth identity insertada para user_id $userId con email $email");
    }

    /**
     * Obtener usuarios con rol de vendedor o admin (Shield v1.1 compatible)
     */
    public function getVendedores(): array
    {
        // Shield v1.1: Los grupos se manejan en AuthGroups.php, NO en base de datos
        // Necesitamos obtener todos los usuarios activos y filtrar por grupos usando Shield
        $users = $this->select('users.id, auth_identities.secret as email, users.username')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->where('users.active', 1)
            ->orderBy('auth_identities.secret', 'ASC')
            ->findAll();

        $vendedores = [];
        $gruposVendedor = ['admin', 'superadmin', 'vendedor', 'supervendedor', 'subvendedor'];

        foreach ($users as $user) {
            // Usar Shield para verificar grupos (compatible con v1.1)
            foreach ($gruposVendedor as $grupo) {
                if ($user->inGroup($grupo)) {
                    $vendedores[] = (object)[
                        'id' => $user->id,
                        'email' => $user->email ?? $user->username,
                        'username' => $user->username
                    ];
                    break; // Evitar duplicados si tiene múltiples grupos
                }
            }
        }
        
        return $vendedores;
    }
}