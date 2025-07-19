<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;

class DebugBienvenidaController extends BaseController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Debug del flujo completo de bienvenida (sin magic links, direct login)
     */
    public function debugFlujo()
    {
        try {
            $output = "🔍 DEBUG FLUJO COMPLETO DE BIENVENIDA<br>";
            $output .= "======================================<br><br>";
            
            // Crear un cliente de prueba y loguearlo directamente
            $emailTest = 'debug-flujo-' . time() . '@test.com';
            
            $output .= "👤 Creando cliente de prueba para debug...<br>";
            
            $userModel = new \App\Models\UserModel();
            $user = $userModel->createUser([
                'email' => $emailTest,
                'password' => 'temporal123',
                'active' => 1,
                'force_password_change' => true  // SOLO para clientes
            ]);
            
            if (!$user || !$user->id) {
                throw new \Exception('Error al crear usuario de prueba');
            }
            
            $userId = $user->id;
            $output .= "&nbsp;&nbsp;&nbsp;✅ Usuario creado con ID: $userId<br>";
            
            // Crear cliente asociado
            $clienteModel = new \App\Models\ClienteModel();
            $clienteData = [
                'user_id' => $userId,
                'nombres' => 'Usuario',
                'apellido_paterno' => 'Debug',
                'apellido_materno' => 'Flujo',
                'email' => $emailTest,
                'telefono' => '5555555555',
                'debe_cambiar_password' => 1,  // IMPORTANTE: debe cambiar contraseña
                'activo' => 1
            ];
            
            $clienteId = $clienteModel->insert($clienteData);
            $output .= "&nbsp;&nbsp;&nbsp;✅ Cliente creado con ID: $clienteId<br>";
            
            // IMPORTANTE: Asignar grupo 'cliente' al usuario
            $user->addGroup('cliente');
            $output .= "&nbsp;&nbsp;&nbsp;✅ Grupo 'cliente' asignado al usuario<br>";
            
            // Verificar estado inicial
            $output .= "<br>📊 ESTADO INICIAL:<br>";
            $identity = $user->getEmailIdentity();
            $cliente = $clienteModel->find($clienteId);
            
            $output .= "&nbsp;&nbsp;&nbsp;Identity secret2: " . (!empty($identity->secret2) ? 'CONFIGURADO' : 'VACÍO') . "<br>";
            $output .= "&nbsp;&nbsp;&nbsp;Cliente debe_cambiar_password: " . ($cliente->debe_cambiar_password ? 'SÍ' : 'NO') . "<br>";
            
            // Simular login directo
            $output .= "<br>🔑 SIMULANDO LOGIN DIRECTO...<br>";
            auth()->login($user);
            $output .= "&nbsp;&nbsp;&nbsp;✅ Usuario logueado como ID: " . auth()->id() . "<br>";
            
            // Verificar qué pasaría con ClienteFilter
            $output .= "<br>🛡️ SIMULANDO ClienteFilter...<br>";
            $debeConfigurarPassword = $cliente->debe_cambiar_password || 
                                     ($identity && empty($identity->secret2));
            $output .= "&nbsp;&nbsp;&nbsp;Debe configurar contraseña: " . ($debeConfigurarPassword ? 'SÍ' : 'NO') . "<br>";
            
            if ($debeConfigurarPassword) {
                $output .= "&nbsp;&nbsp;&nbsp;✅ Filter debería redirigir a configurar-password<br>";
            } else {
                $output .= "&nbsp;&nbsp;&nbsp;❌ Filter NO redirigirá (problema aquí)<br>";
            }
            
            // URLs de prueba
            $baseUrl = base_url();
            $dashboardUrl = "$baseUrl/cliente/dashboard";
            $perfilUrl = "$baseUrl/cliente/mi-perfil";
            $configUrl = "$baseUrl/cliente/configurar-password";
            
            $output .= "<br>🌐 URLS DE PRUEBA (usuario ya logueado):<br>";
            $output .= "==================<br>";
            $output .= "Dashboard: <a href='$dashboardUrl' target='_blank'>$dashboardUrl</a><br>";
            $output .= "Mi Perfil: <a href='$perfilUrl' target='_blank'>$perfilUrl</a><br>";
            $output .= "Configurar Password: <a href='$configUrl' target='_blank'>$configUrl</a><br><br>";
            
            $output .= "📋 <strong>PRUEBA ESTE FLUJO:</strong><br>";
            $output .= "1. Intenta acceder al Dashboard (debería redirigir)<br>";
            $output .= "2. Intenta acceder a Mi Perfil (debería redirigir)<br>";
            $output .= "3. Ve a Configurar Password (debería funcionar)<br>";
            
            return $this->response->setBody("
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Debug Flujo Bienvenida</title>
                    <style>
                        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
                        .container { background: white; padding: 20px; border-radius: 8px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        $output
                        <hr>
                        <a href='/debug/bienvenida/debug-flujo' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>🔄 Generar Nuevo Usuario de Prueba</a>
                    </div>
                </body>
                </html>
            ");
            
        } catch (\Exception $e) {
            return $this->response->setBody("❌ ERROR: " . $e->getMessage());
        }
    }

    /**
     * Generar magic link de prueba
     */
    public function generarMagicLink()
    {
        try {
            $emailTest = 'debug-' . time() . '@test.com';
            $tokenTest = bin2hex(random_bytes(32));
            
            $output = "🔍 GENERANDO MAGIC LINK DE PRUEBA<br>";
            $output .= "================================<br><br>";
            
            // 1. Buscar o crear usuario usando tu UserModel personalizado
            $userModel = new \App\Models\UserModel();
            $user = $userModel->findByCredentials(['email' => $emailTest]);
            
            if (!$user) {
                $output .= "👤 Creando usuario de prueba...<br>";
                
                // Usar tu método createUser personalizado
                $user = $userModel->createUser([
                    'email' => $emailTest,
                    'password' => 'temporal123',
                    'active' => 1
                ]);
                
                if (!$user || !$user->id) {
                    $errors = $userModel->errors();
                    throw new \Exception('Error al crear usuario: ' . json_encode($errors));
                }
                
                $userId = $user->id;
                
                $output .= "&nbsp;&nbsp;&nbsp;✅ Usuario creado con ID: $userId<br>";
                
                // Crear cliente asociado
                $clienteModel = new \App\Models\ClienteModel();
                $clienteData = [
                    'user_id' => $userId,
                    'nombres' => 'Usuario',
                    'apellido_paterno' => 'Debug',
                    'apellido_materno' => 'Test',
                    'email' => $emailTest,
                    'telefono' => '5555555555',
                    'debe_cambiar_password' => 1,
                    'activo' => 1
                ];
                
                $clienteId = $clienteModel->insert($clienteData);
                $output .= "&nbsp;&nbsp;&nbsp;✅ Cliente creado con ID: $clienteId<br>";
                
            } else {
                $output .= "👤 Usuario encontrado: ID {$user->id}<br>";
            }
            
            // 2. Crear token de magic link
            $output .= "<br>🔑 Creando magic link token...<br>";
            
            $tokenModel = new \CodeIgniter\Shield\Models\UserIdentityModel();
            
            // Eliminar tokens antiguos del usuario
            $tokenModel->where('user_id', $user->id)
                       ->where('type', 'access_token')
                       ->where('name LIKE', 'magic-link-%')
                       ->delete();
            
            // Crear nuevo token
            $tokenData = [
                'user_id' => $user->id,
                'type' => 'access_token',
                'name' => 'magic-link-bienvenida-' . time(),
                'secret' => hash('sha256', $tokenTest),
                'secret2' => null,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                'extra' => json_encode(['purpose' => 'bienvenida'])
            ];
            
            $tokenId = $tokenModel->insert($tokenData);
            
            if (!$tokenId) {
                throw new \Exception('Error al crear token: ' . json_encode($tokenModel->errors()));
            }
            
            $output .= "&nbsp;&nbsp;&nbsp;✅ Token creado con ID: $tokenId<br>";
            
            // 3. Generar URLs de prueba
            $baseUrl = base_url();
            $magicUrl = "$baseUrl/cliente/bienvenida?token=$tokenTest&email=" . urlencode($emailTest);
            $debugUrl = "$baseUrl/debug/bienvenida?token=$tokenTest&email=" . urlencode($emailTest);
            $testAuthUrl = "$baseUrl/debug/bienvenida/test-auth?token=$tokenTest&email=" . urlencode($emailTest);
            
            $output .= "<br>🌐 URLs GENERADAS:<br>";
            $output .= "==================<br>";
            $logoutUrl = "$baseUrl/debug/bienvenida/logout";
            $output .= "🚪 PRIMERO: <a href='$logoutUrl' target='_blank' style='background: red; color: white; padding: 5px;'>LOGOUT FORZADO</a><br><br>";
            $output .= "Magic Link (REAL): <a href='$magicUrl' target='_blank'>$magicUrl</a><br><br>";
            $output .= "Debug Completo: <a href='$debugUrl' target='_blank'>$debugUrl</a><br><br>";
            $output .= "Test Autenticación: <a href='$testAuthUrl' target='_blank'>$testAuthUrl</a><br><br>";
            
            return $this->response->setBody("
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Magic Link Generator</title>
                    <style>
                        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
                        .container { background: white; padding: 20px; border-radius: 8px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        $output
                        <hr>
                        <a href='/debug/bienvenida/generar' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>🔄 Generar Nuevo Magic Link</a>
                    </div>
                </body>
                </html>
            ");
            
        } catch (\Exception $e) {
            return $this->response->setBody("❌ ERROR: " . $e->getMessage());
        }
    }

    /**
     * Debug completo del proceso de bienvenida
     */
    public function index()
    {
        try {
            $token = $this->request->getGet('token');
            $email = $this->request->getGet('email');
            
            $debug = [
                'timestamp' => date('Y-m-d H:i:s'),
                'token_recibido' => $token ? 'SÍ (hash: ' . substr(hash('sha256', $token), 0, 10) . '...)' : 'NO',
                'email_recibido' => $email ?: 'NO',
                'sesion_activa_antes' => auth()->loggedIn() ? 'SÍ (user_id: ' . auth()->id() . ')' : 'NO',
                'usuario_encontrado' => 'NO',
                'token_valido' => 'NO',
                'identity_info' => null,
                'cliente_info' => null,
                'debe_cambiar_password' => 'NO EVALUADO',
                'redireccion' => 'NINGUNA',
                'errores' => []
            ];
            
            if (!$token || !$email) {
                $debug['errores'][] = 'Token o email faltante';
                return $this->mostrarDebug($debug);
            }
            
            // Buscar usuario
            $userModel = new \App\Models\UserModel();
            $user = $userModel->findByCredentials(['email' => $email]);
            
            if ($user) {
                $debug['usuario_encontrado'] = 'SÍ (ID: ' . $user->id . ', Email: ' . $user->email . ', Activo: ' . ($user->active ? 'SÍ' : 'NO') . ')';
                
                // Verificar token
                $tokenModel = new \CodeIgniter\Shield\Models\UserIdentityModel();
                $tokenRecord = $tokenModel->where('secret', hash('sha256', $token))
                                       ->where('user_id', $user->id)
                                       ->where('type', 'access_token')
                                       ->where('name LIKE', 'magic-link-%')
                                       ->first();
                
                if ($tokenRecord) {
                    $debug['token_valido'] = 'SÍ (ID: ' . $tokenRecord->id . ', Creado: ' . $tokenRecord->created_at . ')';
                    
                    // Verificar identity info
                    $identity = $user->getEmailIdentity();
                    if ($identity) {
                        $debug['identity_info'] = [
                            'id' => $identity->id,
                            'secret2_configurado' => !empty($identity->secret2) ? 'SÍ' : 'NO',
                            'last_used_at' => $identity->last_used_at
                        ];
                    }
                    
                    // Buscar cliente
                    $clienteModel = new \App\Models\ClienteModel();
                    $cliente = $clienteModel->where('user_id', $user->id)->first();
                    
                    if ($cliente) {
                        $debug['cliente_info'] = [
                            'id' => $cliente->id,
                            'nombres' => $cliente->nombres,
                            'apellido_paterno' => $cliente->apellido_paterno,
                            'debe_cambiar_password' => $cliente->debe_cambiar_password ? 'SÍ' : 'NO',
                            'ultimo_cambio_password' => $cliente->ultimo_cambio_password
                        ];
                        
                        // Evaluar si debe cambiar contraseña
                        $debeConfigurarPassword = $cliente->debe_cambiar_password || 
                                                 ($identity && empty($identity->secret2));
                        $debug['debe_cambiar_password'] = $debeConfigurarPassword ? 'SÍ' : 'NO';
                        
                        if ($debeConfigurarPassword) {
                            $debug['redireccion'] = '/cliente/configurar-password';
                        } else {
                            $debug['redireccion'] = '/cliente/dashboard';
                        }
                    } else {
                        $debug['errores'][] = 'Cliente no encontrado para user_id: ' . $user->id;
                    }
                } else {
                    $debug['errores'][] = 'Token no válido o expirado';
                }
            } else {
                $debug['errores'][] = 'Usuario no encontrado con email: ' . $email;
            }
            
            return $this->mostrarDebug($debug);
            
        } catch (\Exception $e) {
            $debug['errores'][] = 'Exception: ' . $e->getMessage();
            $debug['errores'][] = 'Trace: ' . $e->getTraceAsString();
            return $this->mostrarDebug($debug);
        }
    }
    
    /**
     * Test de autenticación manual
     */
    public function testAuth()
    {
        try {
            $token = $this->request->getGet('token');
            $email = $this->request->getGet('email');
            
            if (!$token || !$email) {
                return $this->response->setJSON([
                    'error' => 'Token y email requeridos',
                    'url_test' => '/debug/bienvenida/test-auth?token=TOKEN&email=EMAIL'
                ]);
            }
            
            // Forzar logout
            if (auth()->loggedIn()) {
                auth()->logout();
                session()->destroy();
            }
            
            $userModel = new \App\Models\UserModel();
            $user = $userModel->findByCredentials(['email' => $email]);
            
            if (!$user) {
                return $this->response->setJSON(['error' => 'Usuario no encontrado']);
            }
            
            // Verificar token
            $tokenModel = new \CodeIgniter\Shield\Models\UserIdentityModel();
            $tokenRecord = $tokenModel->where('secret', hash('sha256', $token))
                                   ->where('user_id', $user->id)
                                   ->where('type', 'access_token')
                                   ->where('name LIKE', 'magic-link-%')
                                   ->first();
            
            if (!$tokenRecord) {
                return $this->response->setJSON(['error' => 'Token inválido']);
            }
            
            // Intentar autenticar
            auth()->login($user);
            
            // Eliminar token
            $tokenModel->delete($tokenRecord->id);
            
            // Verificar estado
            $clienteModel = new \App\Models\ClienteModel();
            $cliente = $clienteModel->where('user_id', $user->id)->first();
            
            $identity = $user->getEmailIdentity();
            $debeConfigurarPassword = $cliente->debe_cambiar_password || 
                                     ($identity && empty($identity->secret2));
            
            return $this->response->setJSON([
                'success' => true,
                'authenticated' => auth()->loggedIn(),
                'user_id' => auth()->id(),
                'debe_configurar_password' => $debeConfigurarPassword,
                'redirect_to' => $debeConfigurarPassword ? '/cliente/configurar-password' : '/cliente/dashboard'
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Debug de búsqueda de usuarios
     */
    public function debugBusqueda()
    {
        try {
            $token = $this->request->getGet('token');
            $email = $this->request->getGet('email');
            
            if (!$token || !$email) {
                $email = 'debug-1752793886@test.com'; // fallback
                $token = 'no-token-provided';
            }
            
            $output = "🔍 VERIFICANDO BÚSQUEDA DE USUARIOS EN SHIELD<br>";
            $output .= "==============================================<br><br>";
            
            $output .= "📧 Buscando email: $email<br>";
            $output .= "🔑 Token recibido: " . ($token !== 'no-token-provided' ? 'SÍ (' . substr($token, 0, 10) . '...)' : 'NO') . "<br><br>";
            
            // Verificar token específico si se proporcionó
            if ($token !== 'no-token-provided') {
                $tokenHash = hash('sha256', $token);
                $output .= "🔍 VERIFICANDO TOKEN ESPECÍFICO:<br>";
                $output .= "&nbsp;&nbsp;&nbsp;Token hash: " . substr($tokenHash, 0, 20) . "...<br>";
                
                $specificToken = $this->db->table('auth_identities')
                                         ->where('secret', $tokenHash)
                                         ->where('type', 'access_token')
                                         ->where('name LIKE', 'magic-link-%')
                                         ->get()
                                         ->getRow();
                
                if ($specificToken) {
                    $output .= "&nbsp;&nbsp;&nbsp;✅ Token encontrado - ID: {$specificToken->id}, User_ID: {$specificToken->user_id}<br>";
                    $output .= "&nbsp;&nbsp;&nbsp;Expires: {$specificToken->expires_at}<br>";
                    $output .= "&nbsp;&nbsp;&nbsp;Name: {$specificToken->name}<br>";
                } else {
                    $output .= "&nbsp;&nbsp;&nbsp;❌ Token NO encontrado en base de datos<br>";
                }
                $output .= "<br>";
            }
            
            // 1. Verificar tabla users (sin email, solo estructura)
            $output .= "1️⃣ VERIFICANDO TABLA 'users' (estructura):<br>";
            $lastUsers = $this->db->table('users')->orderBy('id', 'DESC')->limit(3)->get()->getResult();
            $output .= "&nbsp;&nbsp;&nbsp;Últimos usuarios creados: " . count($lastUsers) . "<br>";
            
            if (!empty($lastUsers)) {
                foreach ($lastUsers as $user) {
                    $output .= "&nbsp;&nbsp;&nbsp;- ID: {$user->id}, Active: {$user->active}, Created: {$user->created_at}<br>";
                }
            }
            
            // 2. Verificar en tabla auth_identities (email_password)
            $output .= "<br>2️⃣ VERIFICANDO TABLA 'auth_identities' (email_password):<br>";
            $identities = $this->db->table('auth_identities')->where('secret', $email)->where('type', 'email_password')->get()->getResult();
            $output .= "&nbsp;&nbsp;&nbsp;Registros email_password encontrados: " . count($identities) . "<br>";
            
            if (!empty($identities)) {
                foreach ($identities as $identity) {
                    $output .= "&nbsp;&nbsp;&nbsp;- ID: {$identity->id}, User_ID: {$identity->user_id}, Type: {$identity->type}, Secret: {$identity->secret}<br>";
                }
            }
            
            // 2b. Verificar tokens access_token para este email
            $output .= "<br>2️⃣b VERIFICANDO TOKENS 'access_token' para este usuario:<br>";
            // Primero obtener user_id del email
            $userIdResult = $this->db->table('auth_identities')
                                   ->select('user_id')
                                   ->where('secret', $email)
                                   ->where('type', 'email_password')
                                   ->get()
                                   ->getRow();
            
            if ($userIdResult) {
                $userId = $userIdResult->user_id;
                $tokens = $this->db->table('auth_identities')
                                 ->where('user_id', $userId)
                                 ->where('type', 'access_token')
                                 ->where('name LIKE', 'magic-link-%')
                                 ->get()
                                 ->getResult();
                
                $output .= "&nbsp;&nbsp;&nbsp;Tokens magic-link encontrados para user_id {$userId}: " . count($tokens) . "<br>";
                
                foreach ($tokens as $token) {
                    $output .= "&nbsp;&nbsp;&nbsp;- Token ID: {$token->id}, Name: {$token->name}, Expires: {$token->expires_at}<br>";
                    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Secret hash: " . substr($token->secret, 0, 20) . "...<br>";
                }
            } else {
                $output .= "&nbsp;&nbsp;&nbsp;❌ No se pudo obtener user_id para el email<br>";
            }
            
            // 3. Probar findByCredentials directamente
            $output .= "<br>3️⃣ PROBANDO findByCredentials() con UserModel personalizado:<br>";
            $userModel = new \App\Models\UserModel();
            $foundUser = $userModel->findByCredentials(['email' => $email]);
            
            if ($foundUser) {
                $output .= "&nbsp;&nbsp;&nbsp;✅ Usuario encontrado: ID {$foundUser->id}, Email: {$foundUser->email}<br>";
            } else {
                $output .= "&nbsp;&nbsp;&nbsp;❌ Usuario NO encontrado con findByCredentials()<br>";
            }
            
            // 4. Verificar último usuario creado y su cliente
            $output .= "<br>4️⃣ ÚLTIMO USUARIO CREADO Y SU CLIENTE:<br>";
            $lastUser = $this->db->table('users')->orderBy('id', 'DESC')->limit(1)->get()->getRow();
            if ($lastUser) {
                $output .= "&nbsp;&nbsp;&nbsp;User ID: {$lastUser->id}, Active: {$lastUser->active}, Created: {$lastUser->created_at}<br>";
                
                // Verificar sus identities
                $userIdentities = $this->db->table('auth_identities')
                                         ->where('user_id', $lastUser->id)
                                         ->get()
                                         ->getResult();
                $output .= "&nbsp;&nbsp;&nbsp;Identities asociadas: " . count($userIdentities) . "<br>";
                foreach ($userIdentities as $identity) {
                    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Type: {$identity->type}, Secret: " . substr($identity->secret, 0, 30) . "...<br>";
                }
                
                // Verificar cliente asociado
                $cliente = $this->db->table('clientes')->where('user_id', $lastUser->id)->get()->getRow();
                if ($cliente) {
                    $output .= "&nbsp;&nbsp;&nbsp;Cliente asociado: ID {$cliente->id}, Email: {$cliente->email}<br>";
                } else {
                    $output .= "&nbsp;&nbsp;&nbsp;❌ No hay cliente asociado<br>";
                }
            }
            
            return $this->response->setBody("
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Debug Búsqueda Usuarios</title>
                    <style>
                        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
                        .container { background: white; padding: 20px; border-radius: 8px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        $output
                        <hr>
                        <a href='/debug/bienvenida/generar' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>🔄 Volver al Generador</a>
                    </div>
                </body>
                </html>
            ");
            
        } catch (\Exception $e) {
            return $this->response->setBody("❌ ERROR: " . $e->getMessage());
        }
    }

    /**
     * Logout forzado para limpiar sesión
     */
    public function logout()
    {
        try {
            if (auth()->loggedIn()) {
                $userId = auth()->id();
                auth()->logout();
                session()->destroy();
                return $this->response->setBody("
                    <!DOCTYPE html>
                    <html>
                    <head><title>Logout Debug</title></head>
                    <body style='font-family: monospace; margin: 20px; background: #f5f5f5;'>
                        <div style='background: white; padding: 20px; border-radius: 8px;'>
                            <h2>🚪 LOGOUT EXITOSO</h2>
                            <p>✅ Usuario ID {$userId} deslogueado</p>
                            <p>✅ Sesión destruida</p>
                            <hr>
                            <p><strong>Ahora puedes probar el magic link sin interferencias.</strong></p>
                            <a href='/debug/bienvenida/generar' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>🔄 Volver al Generador</a>
                        </div>
                    </body>
                    </html>
                ");
            } else {
                return $this->response->setBody("
                    <!DOCTYPE html>
                    <html>
                    <head><title>Logout Debug</title></head>
                    <body style='font-family: monospace; margin: 20px; background: #f5f5f5;'>
                        <div style='background: white; padding: 20px; border-radius: 8px;'>
                            <h2>ℹ️ NO HAY SESIÓN ACTIVA</h2>
                            <p>No había usuario logueado</p>
                            <hr>
                            <a href='/debug/bienvenida/generar' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>🔄 Volver al Generador</a>
                        </div>
                    </body>
                    </html>
                ");
            }
            
        } catch (\Exception $e) {
            return $this->response->setBody("❌ ERROR: " . $e->getMessage());
        }
    }

    private function mostrarDebug($debug)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Debug Bienvenida ANVAR</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .debug-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .debug-item { margin: 10px 0; padding: 8px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .code { background: #e9ecef; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
        pre { background: #e9ecef; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="debug-container">
        <h1>🐛 Debug Proceso Bienvenida ANVAR</h1>
        
        <div class="debug-item">
            <strong>⏰ Timestamp:</strong> ' . $debug['timestamp'] . '
        </div>
        
        <div class="debug-item">
            <strong>🔑 Token recibido:</strong> ' . $debug['token_recibido'] . '
        </div>
        
        <div class="debug-item">
            <strong>📧 Email recibido:</strong> ' . $debug['email_recibido'] . '
        </div>
        
        <div class="debug-item">
            <strong>👤 Sesión activa antes:</strong> ' . $debug['sesion_activa_antes'] . '
        </div>
        
        <div class="debug-item ' . ($debug['usuario_encontrado'] === 'NO' ? 'error' : 'success') . '">
            <strong>🔍 Usuario encontrado:</strong> ' . $debug['usuario_encontrado'] . '
        </div>
        
        <div class="debug-item ' . ($debug['token_valido'] === 'NO' ? 'error' : 'success') . '">
            <strong>✅ Token válido:</strong> ' . $debug['token_valido'] . '
        </div>';
        
        if ($debug['identity_info']) {
            $html .= '
        <div class="debug-item">
            <strong>🆔 Identity Info:</strong>
            <pre>' . print_r($debug['identity_info'], true) . '</pre>
        </div>';
        }
        
        if ($debug['cliente_info']) {
            $html .= '
        <div class="debug-item">
            <strong>👥 Cliente Info:</strong>
            <pre>' . print_r($debug['cliente_info'], true) . '</pre>
        </div>';
        }
        
        $html .= '
        <div class="debug-item">
            <strong>🔐 Debe cambiar contraseña:</strong> ' . $debug['debe_cambiar_password'] . '
        </div>
        
        <div class="debug-item">
            <strong>📍 Redirección sugerida:</strong> ' . $debug['redireccion'] . '
        </div>';
        
        if (!empty($debug['errores'])) {
            $html .= '
        <div class="debug-item error">
            <strong>❌ Errores encontrados:</strong>
            <ul>';
            foreach ($debug['errores'] as $error) {
                $html .= '<li>' . htmlspecialchars($error) . '</li>';
            }
            $html .= '</ul>
        </div>';
        }
        
        $html .= '
        <hr>
        <div class="debug-item">
            <strong>🔧 URLs de prueba:</strong><br>
            <a href="/debug/bienvenida/test-auth?' . $_SERVER['QUERY_STRING'] . '" target="_blank">🧪 Test Autenticación</a>
        </div>
        
    </div>
</body>
</html>';
        
        return $this->response->setBody($html);
    }
}