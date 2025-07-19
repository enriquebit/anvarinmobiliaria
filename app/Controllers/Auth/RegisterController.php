<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class RegisterController extends BaseController
{
    /**
     * Mostrar formulario de registro
     */
    public function index()
    {
        if (auth()->loggedIn()) {
            return redirect()->to('/dashboard');
        }
        
        return view('auth/register');
    }
    
    /**
     * Procesar registro - VERSIÓN CON MANEJO ROBUSTO DE ERRORES
     */
    public function attemptRegister()
    {
        // Habilitar mostrar todos los errores para debug
        if (ENVIRONMENT === 'development') {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
        
        try {
            log_message('info', '🚀 [REGISTRO] Iniciando attemptRegister()');
            
            if (auth()->loggedIn()) {
                log_message('info', '👤 [REGISTRO] Usuario ya logueado, redirigiendo');
                return redirect()->to('/dashboard');
            }
            
            // ✅ Obtener datos del formulario
            $formData = $this->request->getPost();
            log_message('info', '📝 [REGISTRO] Datos recibidos: ' . json_encode($formData, JSON_UNESCAPED_UNICODE));
            
            // Verificar datos básicos
            if (empty($formData)) {
                log_message('error', '❌ [REGISTRO] No se recibieron datos del formulario');
                return redirect()->back()->with('error', 'No se recibieron datos del formulario');
            }
            
            // ✅ Validar datos usando las reglas de Validation.php
            log_message('info', '🔍 [REGISTRO] Iniciando validación...');
            
            if (!$this->validate('registration')) {
                $errors = $this->validator->getErrors();
                log_message('warning', '❌ [REGISTRO] Validación fallida: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
                
                return redirect()->back()
                               ->withInput()
                               ->with('errors', $errors)
                               ->with('error', 'Por favor corrige los errores en el formulario');
            }
            
            log_message('info', '✅ [REGISTRO] Validación pasada correctamente');
            
            // ✅ Validaciones adicionales de negocio
            if (!$this->validateBusinessRules($formData)) {
                log_message('warning', '❌ [REGISTRO] Reglas de negocio fallidas: ' . $this->businessError);
                return redirect()->back()
                               ->withInput()
                               ->with('error', $this->businessError);
            }
            
            log_message('info', '✅ [REGISTRO] Reglas de negocio pasadas');
            
            // ✅ CREAR USUARIO + CLIENTE
            log_message('info', '🔧 [REGISTRO] Iniciando creación de usuario...');
            
            $userModel = new UserModel();
            
            // Verificar que el método existe
            if (!method_exists($userModel, 'createClienteUser')) {
                log_message('error', '💥 [REGISTRO] Método createClienteUser no existe en UserModel');
                throw new \RuntimeException('Error del sistema: Método createClienteUser no encontrado');
            }
            
            log_message('info', '✅ [REGISTRO] UserModel cargado, método createClienteUser existe');
            
            $user = $userModel->createClienteUser($formData);
            
            if (!$user || !$user->id) {
                log_message('error', '💥 [REGISTRO] Usuario no fue creado correctamente');
                throw new \RuntimeException('Error: Usuario no fue creado correctamente');
            }
            
            log_message('info', "🎉 [REGISTRO] Usuario + Cliente creado exitosamente - User ID: {$user->id}, Email: {$formData['email']}");
            
            // ✅ ÉXITO - Redirigir con mensaje
            $nombreCompleto = trim($formData['nombres'] . ' ' . $formData['apellido_paterno']);
            
            return redirect()->to('/login')
                           ->with('success', 
                               "¡Bienvenido(a) {$nombreCompleto}! " .
                               "Tu cuenta ha sido creada exitosamente. " .
                               "Un administrador debe activar tu cuenta para acceso completo. " .
                               "Puedes iniciar sesión ahora."
                           );
            
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', '💥 [REGISTRO] Error de base de datos: ' . $e->getMessage());
            log_message('error', '🔍 [REGISTRO] Database trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error de base de datos. Verifica la conexión y las tablas.');
                           
        } catch (\CodeIgniter\Shield\Exceptions\ValidationException $e) {
            log_message('error', '💥 [REGISTRO] Error de validación de Shield: ' . $e->getMessage());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error de validación: ' . $e->getMessage());
                           
        } catch (\Exception $e) {
            log_message('error', '💥 [REGISTRO] Error general: ' . $e->getMessage());
            log_message('error', '🔍 [REGISTRO] Stack trace: ' . $e->getTraceAsString());
            
            // En desarrollo, mostrar el error completo
            if (ENVIRONMENT === 'development') {
                $errorMsg = 'Error al crear la cuenta: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')';
            } else {
                $errorMsg = 'Error al crear la cuenta. Intenta nuevamente.';
            }
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', $errorMsg);
        }
    }
    
    /**
     * Validaciones de reglas de negocio
     */
    private function validateBusinessRules(array $data): bool
    {
        try {
            $db = \Config\Database::connect();
            
            log_message('info', '🔍 [REGISTRO] Iniciando validaciones de reglas de negocio');
            
            
            // 2. Verificar que el email no exista en auth_identities
            $emailExistsAuth = $db->table('auth_identities')
                                  ->where('secret', strtolower($data['email']))
                                  ->where('type', 'email_password')
                                  ->countAllResults() > 0;
            
            if ($emailExistsAuth) {
                $this->businessError = 'Este email ya está registrado en el sistema.';
                log_message('warning', '❌ [REGISTRO] Email ya existe en auth_identities: ' . $data['email']);
                return false;
            }
            

            
            // 4. Validar que nombres no contengan números
            if (preg_match('/[0-9]/', $data['nombres'] . $data['apellido_paterno'] . $data['apellido_materno'])) {
                $this->businessError = 'Los nombres y apellidos no pueden contener números.';
                log_message('warning', '❌ [REGISTRO] Nombres contienen números');
                return false;
            }
            
            log_message('info', '✅ [REGISTRO] Todas las reglas de negocio pasaron');
            return true;
            
        } catch (\Exception $e) {
            log_message('error', '💥 [REGISTRO] Error en validateBusinessRules: ' . $e->getMessage());
            $this->businessError = 'Error al validar datos. Intenta nuevamente.';
            return false;
        }
    }
    
    private $businessError = '';
    
    /**
     * API para verificar email disponible (AJAX)
     */
    public function checkEmail()
    {
        if (!$this->request->isAJAX()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        $email = trim($this->request->getPost('email'));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'available' => false,
                'message' => 'Email inválido'
            ]);
        }
        
        try {
            $db = \Config\Database::connect();
            
            // Verificar en auth_identities (tabla de Shield)
            $existsInAuth = $db->table('auth_identities')
                               ->where('secret', strtolower($email))
                               ->where('type', 'email_password')
                               ->countAllResults() > 0;
            
            // Verificar en tabla clientes
            $existsInClientes = $db->table('clientes')
                                   ->where('email', strtolower($email))
                                   ->countAllResults() > 0;
            
            $exists = $existsInAuth || $existsInClientes;
            
            return $this->response->setJSON([
                'available' => !$exists,
                'message' => $exists ? 'Email ya registrado' : 'Email disponible'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en checkEmail: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'available' => false,
                'message' => 'Error al verificar email'
            ]);
        }
    }
    
    /**
     * Debug: Ver último cliente creado
     */
    public function debugLastClient()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        try {
            $db = \Config\Database::connect();
            
            echo "<h1>🔍 DEBUG: Último Cliente Creado</h1>";
            
            // Último cliente
            $lastClient = $db->query("
                SELECT c.*, u.active as user_active, ai.secret as email_auth
                FROM clientes c
                LEFT JOIN users u ON u.id = c.user_id  
                LEFT JOIN auth_identities ai ON ai.user_id = c.user_id AND ai.type = 'email_password'
                ORDER BY c.id DESC 
                LIMIT 1
            ")->getRow();
            
            if ($lastClient) {
                echo "<h3>✅ Cliente encontrado:</h3>";
                echo "<table border='1' style='border-collapse: collapse;'>";
                foreach ((array)$lastClient as $field => $value) {
                    echo "<tr><td><strong>$field</strong></td><td>$value</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<h3>❌ No hay clientes registrados</h3>";
            }
            
            // Estadísticas
            echo "<h3>📊 Estadísticas:</h3>";
            echo "<p>Total usuarios: " . $db->table('users')->countAll() . "</p>";
            echo "<p>Total clientes: " . $db->table('clientes')->countAll() . "</p>";
            echo "<p>Total identities: " . $db->table('auth_identities')->countAll() . "</p>";
            
            echo "<p><a href='/register'>← Volver al registro</a></p>";
            
        } catch (\Exception $e) {
            echo "<h1>💥 Error en Debug:</h1>";
            echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
}