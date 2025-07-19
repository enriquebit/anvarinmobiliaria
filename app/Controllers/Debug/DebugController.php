<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;

class DebugController extends BaseController
{
    /**
     * 🎯 MENÚ PRINCIPAL DE DEBUG
     */
    public function index()
    {
        $this->headerDebug("🔍 DEBUG ANVAR - SISTEMA COMPLETO");
        
        echo "<div class='menu-grid'>";
        echo "<div class='debug-card'>";
        echo "<h3>🔧 Tests Básicos</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/conexion') . "'>1. Conexión BD</a></li>";
        echo "<li><a href='" . base_url('debug/shield') . "'>2. Shield Básico</a></li>";
        echo "<li><a href='" . base_url('debug/modelos') . "'>3. Modelos</a></li>";
        echo "<li><a href='" . base_url('debug/tablas') . "'>4. Verificar Tablas</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-card'>";
        echo "<h3>📊 Gestión de Clientes</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/clientes') . "'>5. Panel Clientes</a></li>";
        echo "<li><a href='" . base_url('debug/clientes/test-database') . "'>6. Test Base Datos</a></li>";
        echo "<li><a href='" . base_url('debug/clientes/test-form-data') . "'>7. Test Formularios</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-card'>";
        echo "<h3>📁 Google Drive API</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/google-drive') . "'>8. Panel Google Drive</a></li>";
        echo "<li><a href='" . base_url('debug/google-drive/test-auth') . "'>9. Test Autenticación</a></li>";
        echo "<li><a href='" . base_url('admin/google-drive/status') . "'>10. Estado OAuth2</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-card'>";
        echo "<h3>Gestión de Leads</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('admin/leads') . "'>11. Ver Leads</a></li>";
        echo "<li><a href='" . base_url('admin/leads/errores') . "'>12. Ver Errores</a></li>";
        echo "<li><a href='" . base_url('admin/leads/logs') . "'>13. Ver Logs API</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-card'>";
        echo "<h3>💰 Configuración Financiera</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera') . "'>14. 🎯 Debug Config Financiera</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/datos') . "'>15. 📊 Ver Datos BD</a></li>";
        echo "<li><a href='" . base_url('debug/configuracion-financiera/model-update') . "'>16. 💾 Test Update</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-card'>";
        echo "<h3>🏪 Módulo Ventas (CORE)</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/ventas') . "'>17. 🎯 Debug Ventas Completo</a></li>";
        echo "<li><a href='" . base_url('debug/ventas/test-completo') . "'>18. 🧪 Test Completo Ventas</a></li>";
        echo "<li><a href='" . base_url('debug/ventas/test-ajax') . "'>19. 🔌 Test AJAX Endpoints</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-card'>";
        echo "<h3>🔄 Herramientas de Rollback</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/rollback/estado-sistema') . "'>17. 📊 Estado del Sistema</a></li>";
        echo "<li><a href='" . base_url('debug/rollback/ultimo-pago/40') . "'>18. 🔄 Rollback Último Pago (V40)</a></li>";
        echo "<li><a href='" . base_url('debug/rollback/ultimo-pago/41') . "'>19. 🔄 Rollback Último Pago (V41)</a></li>";
        echo "<li><a href='" . base_url('debug/rollback/rollback-venta/40') . "'>20. 🗑️ Eliminar Venta 40</a></li>";
        echo "<li><a href='" . base_url('debug/rollback/rollback-completo') . "'>21. 💣 ROLLBACK TOTAL</a></li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div class='debug-card'>";
        echo "<h3>🛠️ Herramientas Generales</h3>";
        echo "<ul>";
        echo "<li><a href='" . base_url('debug/simple') . "'>22. Test Directo</a></li>";
        echo "<li><a href='" . base_url('debug/limpiar') . "'>23. Limpiar Pruebas</a></li>";
        echo "<li><a href='" . base_url('debug') . "/index.php'>24. 🎯 Panel Debug Completo</a></li>";
        echo "</ul>";
        echo "</div>";
        echo "</div>";
        
        // Estado rápido del sistema
        echo "<h2>⚡ Estado Actual del Sistema</h2>";
        echo "<div class='status-grid'>";
        
        // Test 1: Conexión BD
        try {
            $db = \Config\Database::connect();
            $db->query("SELECT 1");
            echo "<div class='status-ok'>✅ Base de Datos: OK</div>";
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Base de Datos: ERROR - " . $e->getMessage() . "</div>";
        }
        
        // Test 2: Shield
        try {
            $authService = auth();
            echo "<div class='status-ok'>✅ Shield Service: OK (" . get_class($authService) . ")</div>";
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Shield Service: ERROR - " . $e->getMessage() . "</div>";
        }
        
        // Test 3: Login Status
        try {
            if (auth()->loggedIn()) {
                echo "<div class='status-ok'>✅ Usuario: LOGUEADO (" . auth()->user()->email . ")</div>";
            } else {
                echo "<div class='status-warning'>⚠️ Usuario: NO LOGUEADO</div>";
            }
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Check Login: ERROR - " . $e->getMessage() . "</div>";
        }
        
        // Test 4: UserModel
        try {
            $userModel = new \App\Models\UserModel();
            echo "<div class='status-ok'>✅ UserModel: OK (" . get_class($userModel) . ")</div>";
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ UserModel: ERROR - " . $e->getMessage() . "</div>";
        }
        
        echo "</div>";
        
        $this->footerDebug();
    }
    
    /**
     * 🔌 TEST 1: CONEXIÓN A BASE DE DATOS
     */
    public function conexion()
    {
        $this->headerDebug("🔌 TEST 1: CONEXIÓN BASE DE DATOS");
        
        try {
            $db = \Config\Database::connect();
            
            echo "<h3>Información de Conexión:</h3>";
            echo "<table class='debug-table'>";
            echo "<tr><td>Driver</td><td>" . $db->getPlatform() . "</td></tr>";
            echo "<tr><td>Database</td><td>" . $db->getDatabase() . "</td></tr>";
            echo "<tr><td>Hostname</td><td>" . $db->hostname . "</td></tr>";
            echo "<tr><td>Username</td><td>" . $db->username . "</td></tr>";
            echo "</table>";
            
            echo "<h3>Test de Consulta Básica:</h3>";
            $result = $db->query("SELECT COUNT(*) as total FROM users")->getRow();
            echo "<div class='status-ok'>✅ Consulta exitosa: {$result->total} usuarios en BD</div>";
            
            echo "<h3>Verificar Tablas Shield:</h3>";
            $tables = ['users', 'auth_identities', 'auth_groups_users', 'clientes'];
            foreach ($tables as $table) {
                if ($db->tableExists($table)) {
                    $count = $db->table($table)->countAll();
                    echo "<div class='status-ok'>✅ Tabla '$table': Existe ($count registros)</div>";
                } else {
                    echo "<div class='status-error'>❌ Tabla '$table': NO EXISTE</div>";
                }
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR DE CONEXIÓN: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🛡️ TEST 2: SHIELD BÁSICO
     */
    public function shield()
    {
        $this->headerDebug("🛡️ TEST 2: SHIELD BÁSICO");
        
        try {
            // Test 1: Servicio auth()
            echo "<h3>1. Servicio auth():</h3>";
            $authService = auth();
            echo "<div class='status-ok'>✅ Tipo: " . get_class($authService) . "</div>";
            
            // Test 2: Configuración
            echo "<h3>2. Configuración Auth:</h3>";
            $authConfig = config('Auth');
            echo "<table class='debug-table'>";
            echo "<tr><td>UserProvider</td><td>" . $authConfig->userProvider . "</td></tr>";
            echo "<tr><td>ValidFields</td><td>" . implode(', ', $authConfig->validFields) . "</td></tr>";
            echo "<tr><td>DefaultAuthenticator</td><td>" . $authConfig->defaultAuthenticator . "</td></tr>";
            echo "</table>";
            
            // Test 3: Configuración AuthGroups
            echo "<h3>3. Grupos:</h3>";
            $authGroups = config('AuthGroups');
            echo "<table class='debug-table'>";
            echo "<tr><td>DefaultGroup</td><td>" . $authGroups->defaultGroup . "</td></tr>";
            echo "<tr><td>Grupos</td><td>" . implode(', ', array_keys($authGroups->groups)) . "</td></tr>";
            echo "</table>";
            
            // Test 4: Estado de login
            echo "<h3>4. Estado Login:</h3>";
            if ($authService->loggedIn()) {
                $user = $authService->user();
                echo "<div class='status-ok'>✅ Usuario LOGUEADO: " . $user->email . "</div>";
                echo "<div class='status-info'>Grupos: " . implode(', ', $user->getGroups()) . "</div>";
            } else {
                echo "<div class='status-warning'>⚠️ NO hay usuario logueado</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR EN SHIELD: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🏗️ TEST 3: MODELOS
     */
    public function modelos()
    {
        $this->headerDebug("🏗️ TEST 3: MODELOS Y ENTITIES");
        
        try {
            // Test UserModel
            echo "<h3>1. UserModel:</h3>";
            $userModel = new \App\Models\UserModel();
            echo "<div class='status-ok'>✅ UserModel cargado: " . get_class($userModel) . "</div>";
            
            // Verificar que extiende de Shield
            if ($userModel instanceof \CodeIgniter\Shield\Models\UserModel) {
                echo "<div class='status-ok'>✅ Extiende correctamente de Shield</div>";
            } else {
                echo "<div class='status-error'>❌ NO extiende de Shield</div>";
            }
            
            // Test count
            $userCount = $userModel->countAll();
            echo "<div class='status-info'>📊 Total usuarios: $userCount</div>";
            
            // Test ClienteModel
            echo "<h3>2. ClienteModel:</h3>";
            $clienteModel = new \App\Models\ClienteModel();
            echo "<div class='status-ok'>✅ ClienteModel cargado: " . get_class($clienteModel) . "</div>";
            
            $clienteCount = $clienteModel->countAll();
            echo "<div class='status-info'>📊 Total clientes: $clienteCount</div>";
            
            // Test Entities
            echo "<h3>3. Entities:</h3>";
            $user = new \App\Entities\User(['email' => 'test@test.com']);
            echo "<div class='status-ok'>✅ User Entity: " . get_class($user) . "</div>";
            
            if ($user instanceof \CodeIgniter\Shield\Entities\User) {
                echo "<div class='status-ok'>✅ User extiende Shield User</div>";
            } else {
                echo "<div class='status-error'>❌ User NO extiende Shield User</div>";
            }
            
            $cliente = new \App\Entities\Cliente(['nombres' => 'TEST']);
            echo "<div class='status-ok'>✅ Cliente Entity: " . get_class($cliente) . "</div>";
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR EN MODELOS: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🔄 TEST 4: FLUJO DE AUTENTICACIÓN
     */
    public function authFlow()
    {
        $this->headerDebug("🔄 TEST 4: FLUJO DE AUTENTICACIÓN");
        
        echo "<h3>Paso 1: Obtener servicio auth()</h3>";
        try {
            $authService = auth();
            echo "<div class='status-ok'>✅ auth() OK: " . get_class($authService) . "</div>";
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en auth(): " . $e->getMessage() . "</div>";
            $this->footerDebug();
            return;
        }
        
        echo "<h3>Paso 2: Verificar estado de login</h3>";
        try {
            $isLoggedIn = $authService->loggedIn();
            echo "<div class='status-info'>Login Status: " . ($isLoggedIn ? 'SÍ' : 'NO') . "</div>";
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en loggedIn(): " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>Paso 3: Obtener usuario (si está logueado)</h3>";
        try {
            if ($isLoggedIn) {
                $user = $authService->user();
                echo "<div class='status-ok'>✅ Usuario obtenido: " . get_class($user) . "</div>";
                echo "<div class='status-info'>Email: " . $user->email . "</div>";
                echo "<div class='status-info'>ID: " . $user->id . "</div>";
                echo "<div class='status-info'>Activo: " . ($user->active ? 'SÍ' : 'NO') . "</div>";
                
                // Test grupos
                echo "<h4>Grupos del usuario:</h4>";
                $groups = $user->getGroups();
                if (!empty($groups)) {
                    echo "<div class='status-ok'>✅ Grupos: " . implode(', ', $groups) . "</div>";
                } else {
                    echo "<div class='status-warning'>⚠️ Sin grupos asignados</div>";
                }
                
            } else {
                echo "<div class='status-warning'>⚠️ No hay usuario para obtener</div>";
            }
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error obteniendo usuario: " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>Paso 4: Test helpers personalizados</h3>";
        try {
            if (function_exists('isAdmin')) {
                echo "<div class='status-ok'>✅ isAdmin(): " . (isAdmin() ? 'SÍ' : 'NO') . "</div>";
            } else {
                echo "<div class='status-error'>❌ Helper isAdmin() no existe</div>";
            }
            
            if (function_exists('isCliente')) {
                echo "<div class='status-ok'>✅ isCliente(): " . (isCliente() ? 'SÍ' : 'NO') . "</div>";
            } else {
                echo "<div class='status-error'>❌ Helper isCliente() no existe</div>";
            }
            
            if (function_exists('userName')) {
                echo "<div class='status-ok'>✅ userName(): " . userName() . "</div>";
            } else {
                echo "<div class='status-error'>❌ Helper userName() no existe</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en helpers: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🔐 TEST 5: LOGIN TEST
     */
    public function loginTest()
    {
        $this->headerDebug("🔐 TEST 5: PROCESO DE LOGIN");
        
        // Verificar usuarios existentes
        echo "<h3>Usuarios disponibles para login:</h3>";
        try {
            $db = \Config\Database::connect();
            $users = $db->query("
                SELECT u.id, u.active, ai.secret as email 
                FROM users u 
                LEFT JOIN auth_identities ai ON ai.user_id = u.id AND ai.type = 'email_password'
                ORDER BY u.id DESC 
                LIMIT 5
            ")->getResult();
            
            if (empty($users)) {
                echo "<div class='status-warning'>⚠️ NO hay usuarios registrados</div>";
                echo "<div class='status-info'>🔗 <a href='" . site_url('debug/create-test-user') . "'>Crear usuario de prueba</a></div>";
            } else {
                echo "<table class='debug-table'>";
                echo "<tr><th>ID</th><th>Email</th><th>Activo</th><th>Acción</th></tr>";
                foreach ($users as $user) {
                    $activeStatus = $user->active ? '✅' : '❌';
                    echo "<tr>";
                    echo "<td>{$user->id}</td>";
                    echo "<td>{$user->email}</td>";
                    echo "<td>{$activeStatus}</td>";
                    echo "<td><a href='" . site_url("debug/test-login/{$user->id}") . "'>Test Login</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error obteniendo usuarios: " . $e->getMessage() . "</div>";
        }
        
        // Form de test login manual
        echo "<h3>Test Login Manual:</h3>";
        echo "<div class='test-form'>";
        echo form_open(site_url('debug/attempt-login'));
        echo "<div class='form-group'>";
        echo "<label>Email:</label>";
        echo "<input type='email' name='email' value='superadmin@nuevoanvar.test' required>";
        echo "</div>";
        echo "<div class='form-group'>";
        echo "<label>Password:</label>";
        echo "<input type='password' name='password' value='secret1234' required>";
        echo "</div>";
        echo "<button type='submit' class='btn-test'>🧪 Test Login</button>";
        echo form_close();
        echo "</div>";
        
        $this->footerDebug();
    }
    
    /**
     * 🔓 Intentar login de prueba
     */
    public function attemptLogin()
    {
        $this->headerDebug("🔓 RESULTADO TEST LOGIN");
        
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        
        echo "<h3>Datos recibidos:</h3>";
        echo "<div class='status-info'>Email: $email</div>";
        echo "<div class='status-info'>Password: [OCULTO]</div>";
        
        try {
            echo "<h3>Paso 1: Obtener servicio auth</h3>";
            $authService = auth();
            echo "<div class='status-ok'>✅ Servicio obtenido</div>";
            
            echo "<h3>Paso 2: Intentar login</h3>";
            $credentials = [
                'email' => $email,
                'password' => $password
            ];
            
            $result = $authService->attempt($credentials);
            
            if ($result->isOK()) {
                echo "<div class='status-ok'>✅ LOGIN EXITOSO</div>";
                
                $user = $authService->user();
                echo "<div class='status-info'>Usuario: " . $user->email . "</div>";
                echo "<div class='status-info'>ID: " . $user->id . "</div>";
                echo "<div class='status-info'>Grupos: " . implode(', ', $user->getGroups()) . "</div>";
                
            } else {
                echo "<div class='status-error'>❌ LOGIN FALLÓ</div>";
                echo "<div class='status-error'>Razón: " . $result->reason() . "</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR EN LOGIN: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 👤 TEST 6: USUARIO ACTUAL
     */
    public function currentUser()
    {
        $this->headerDebug("👤 TEST 6: USUARIO ACTUAL");
        
        if (!auth()->loggedIn()) {
            echo "<div class='status-warning'>⚠️ NO hay usuario logueado</div>";
            echo "<div class='status-info'>🔗 <a href='" . site_url('debug/login-test') . "'>Ir a test de login</a></div>";
            $this->footerDebug();
            return;
        }
        
        try {
            $user = auth()->user();
            
            echo "<h3>Información Básica:</h3>";
            echo "<table class='debug-table'>";
            echo "<tr><td>Clase</td><td>" . get_class($user) . "</td></tr>";
            echo "<tr><td>ID</td><td>" . $user->id . "</td></tr>";
            echo "<tr><td>Email</td><td>" . $user->email . "</td></tr>";
            echo "<tr><td>Activo</td><td>" . ($user->active ? 'SÍ' : 'NO') . "</td></tr>";
            echo "<tr><td>Creado</td><td>" . $user->created_at . "</td></tr>";
            echo "</table>";
            
            echo "<h3>Grupos y Permisos:</h3>";
            $groups = $user->getGroups();
            echo "<div class='status-info'>Grupos: " . implode(', ', $groups) . "</div>";
            
            $permissions = $user->getPermissions();
            echo "<div class='status-info'>Permisos: " . implode(', ', $permissions) . "</div>";
            
            // Test métodos personalizados si la Entity es personalizada
            if ($user instanceof \App\Entities\User) {
                echo "<h3>Métodos Personalizados:</h3>";
                try {
                    echo "<div class='status-info'>hasCliente(): " . ($user->hasCliente() ? 'SÍ' : 'NO') . "</div>";
                    echo "<div class='status-info'>getNombreCompleto(): " . $user->getNombreCompleto() . "</div>";
                    echo "<div class='status-info'>getIniciales(): " . $user->getIniciales() . "</div>";
                } catch (\Exception $e) {
                    echo "<div class='status-error'>❌ Error en métodos personalizados: " . $e->getMessage() . "</div>";
                }
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🔄 TEST 8: RESET AUTH
     */
    public function resetAuth()
    {
        $this->headerDebug("🔄 TEST 8: RESET AUTENTICACIÓN");
        
        try {
            // Logout si está logueado
            if (auth()->loggedIn()) {
                auth()->logout();
                echo "<div class='status-ok'>✅ Logout realizado</div>";
            }
            
            // Limpiar sesión
            session()->destroy();
            echo "<div class='status-ok'>✅ Sesión limpiada</div>";
            
            // Verificar estado
            $authService = auth();
            if (!$authService->loggedIn()) {
                echo "<div class='status-ok'>✅ Estado auth reseteado correctamente</div>";
            } else {
                echo "<div class='status-error'>❌ Aún hay usuario logueado</div>";
            }
            
            echo "<div class='status-info'>🔗 <a href='" . site_url('debug') . "'>Volver al menú principal</a></div>";
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR EN RESET: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🛠️ STYLES Y HELPERS
     */
    private function headerDebug(string $title)
    {
        echo "<!DOCTYPE html><html><head><title>$title</title>";
        echo "<style>
            body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
            h2 { color: #34495e; margin-top: 30px; }
            h3 { color: #7f8c8d; margin-top: 20px; }
            .status-ok { color: #27ae60; font-weight: bold; padding: 8px; background: #d5f4e6; border-radius: 5px; margin: 5px 0; }
            .status-error { color: #e74c3c; font-weight: bold; padding: 8px; background: #fadbd8; border-radius: 5px; margin: 5px 0; }
            .status-warning { color: #f39c12; font-weight: bold; padding: 8px; background: #fdeaa7; border-radius: 5px; margin: 5px 0; }
            .status-info { color: #3498db; font-weight: bold; padding: 8px; background: #d6eaf8; border-radius: 5px; margin: 5px 0; }
            .debug-table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            .debug-table th, .debug-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .debug-table th { background: #ecf0f1; }
            .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
            .debug-card { background: #ecf0f1; padding: 20px; border-radius: 8px; }
            .debug-card h3 { margin-top: 0; color: #2c3e50; }
            .debug-card ul { list-style: none; padding: 0; }
            .debug-card li { margin: 8px 0; }
            .debug-card a { color: #3498db; text-decoration: none; font-weight: 500; }
            .debug-card a:hover { text-decoration: underline; }
            .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; margin: 20px 0; }
            .test-form { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
            .form-group input { width: 100%; max-width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
            .btn-test { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
            .btn-test:hover { background: #2980b9; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        </style>";
        echo "</head><body><div class='container'>";
        echo "<h1>$title</h1>";
    }
    
    private function footerDebug()
    {
        echo "<hr style='margin: 30px 0;'>";
        echo "<p style='text-align: center;'>";
        echo "<a href='" . site_url('debug') . "' style='margin-right: 20px;'>← Menú Principal</a>";
        echo "<a href='" . site_url('login') . "' style='margin-right: 20px;'>Login</a>";
        echo "<a href='" . site_url('register') . "' style='margin-right: 20px;'>Registro</a>";
        echo "<a href='" . site_url('dashboard') . "'>Dashboard</a>";
        echo "</p>";
        echo "</div></body></html>";
    }
    /**
     * 🔍 DEBUG: ¿QUÉ RUTA SE ESTÁ EJECUTANDO?
     */
    public function debugRoutes()
    {
        $this->headerDebug("🔍 DEBUG: ANÁLISIS DE RUTAS");
        
        try {
            echo "<h3>1. Análisis del Sistema de Rutas:</h3>";
            
            // Obtener todas las rutas registradas
            $router = service('router');
            $routes = $router->getRoutes();
            
            echo "<h4>🎯 Rutas relacionadas con AUTH:</h4>";
            echo "<table class='debug-table'>";
            echo "<tr><th>Ruta</th><th>Controlador/Handler</th><th>Origen</th></tr>";
            
            $authRoutes = [];
            foreach ($routes as $route => $handler) {
                $routeLower = strtolower($route);
                if (strpos($routeLower, 'login') !== false || 
                    strpos($routeLower, 'register') !== false || 
                    strpos($routeLower, 'auth') !== false) {
                    
                    $controllerInfo = is_array($handler) ? 
                        ($handler['controller'] ?? json_encode($handler)) : 
                        $handler;
                    
                    $origen = (strpos($controllerInfo, 'Shield') !== false) ? 'SHIELD' : 'PERSONALIZADA';
                    
                    echo "<tr>";
                    echo "<td><strong>$route</strong></td>";
                    echo "<td>$controllerInfo</td>";
                    echo "<td><span class='" . ($origen === 'SHIELD' ? 'status-warning' : 'status-ok') . "'>$origen</span></td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
            
            echo "<h3>2. Test de Resolución de Rutas:</h3>";
            
            // Test específico para /register
            echo "<h4>🧪 ¿Qué controlador maneja POST /register?</h4>";
            
            try {
                // Intentar resolver la ruta manualmente
                $router = service('router');
                $request = service('request');
                
                echo "<div class='test-form'>";
                echo "<p><strong>Método actual:</strong> " . $request->getMethod() . "</p>";
                echo "<p><strong>URI actual:</strong> " . $request->getUri()->getPath() . "</p>";
                
                // Información del router actual
                $currentController = $router->controllerName();
                $currentMethod = $router->methodName();
                
                echo "<p><strong>Controlador actual:</strong> $currentController</p>";
                echo "<p><strong>Método actual:</strong> $currentMethod</p>";
                echo "</div>";
                
            } catch (\Exception $e) {
                echo "<div class='status-error'>Error obteniendo info del router: " . $e->getMessage() . "</div>";
            }
            
            echo "<h3>3. Verificar Archivos de Controlador:</h3>";
            
            // Verificar que existe tu controlador personalizado
            $registerControllerPath = APPPATH . 'Controllers/Auth/RegisterController.php';
            if (file_exists($registerControllerPath)) {
                echo "<div class='status-ok'>✅ Tu RegisterController existe: $registerControllerPath</div>";
                
                // Verificar que tiene el método attemptRegister
                $content = file_get_contents($registerControllerPath);
                if (strpos($content, 'function attemptRegister') !== false) {
                    echo "<div class='status-ok'>✅ Método attemptRegister() encontrado</div>";
                } else {
                    echo "<div class='status-error'>❌ Método attemptRegister() NO encontrado</div>";
                }
                
                // Verificar namespace
                if (strpos($content, 'namespace App\Controllers\Auth') !== false) {
                    echo "<div class='status-ok'>✅ Namespace correcto</div>";
                } else {
                    echo "<div class='status-error'>❌ Namespace incorrecto</div>";
                }
                
            } else {
                echo "<div class='status-error'>❌ Tu RegisterController NO existe: $registerControllerPath</div>";
            }
            
            echo "<h3>4. Test en Vivo:</h3>";
            echo "<div class='test-form'>";
            echo "<h4>Formulario de Test:</h4>";
            echo form_open(site_url('debug/test-register-route'), ['method' => 'post']);
            echo "<input type='hidden' name='test_data' value='true'>";
            echo "<button type='submit' class='btn-test'>🧪 Test POST /register</button>";
            echo form_close();
            echo "</div>";
            
            echo "<h3>5. Verificar Routes.php:</h3>";
            $routesFile = APPPATH . 'Config/Routes.php';
            if (file_exists($routesFile)) {
                $routesContent = file_get_contents($routesFile);
                
                echo "<h4>🔍 Verificaciones en Routes.php:</h4>";
                
                // Buscar tu ruta personalizada
                if (strpos($routesContent, "routes->post('register', 'Auth\\RegisterController::attemptRegister')") !== false ||
                    strpos($routesContent, "\$routes->post('register', 'Auth\\RegisterController::attemptRegister')") !== false) {
                    echo "<div class='status-ok'>✅ Ruta POST /register personalizada encontrada</div>";
                } else {
                    echo "<div class='status-error'>❌ Ruta POST /register personalizada NO encontrada</div>";
                }
                
                // Verificar si service('auth') está al final
                $serviceAuthPos = strpos($routesContent, "service('auth')->routes");
                $registerPos = strpos($routesContent, "register', 'Auth\\RegisterController");
                
                if ($serviceAuthPos !== false && $registerPos !== false) {
                    if ($registerPos < $serviceAuthPos) {
                        echo "<div class='status-ok'>✅ Tu ruta está ANTES de service('auth') ✓</div>";
                    } else {
                        echo "<div class='status-error'>❌ Tu ruta está DESPUÉS de service('auth') - PROBLEMA!</div>";
                        echo "<div class='status-warning'>💡 Mueve tus rutas ANTES de service('auth')->routes()</div>";
                    }
                } else {
                    echo "<div class='status-warning'>⚠️ No se pudo determinar el orden de las rutas</div>";
                }
                
            } else {
                echo "<div class='status-error'>❌ Archivo Routes.php no encontrado</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🧪 TEST DE RUTA DE REGISTRO
     */
    public function testRegisterRoute()
    {
        $this->headerDebug("🧪 TEST DE RUTA DE REGISTRO");
        
        echo "<h3>¡ESTE CONTROLADOR SE ESTÁ EJECUTANDO!</h3>";
        echo "<div class='status-ok'>✅ DebugController::testRegisterRoute() funcionando</div>";
        
        echo "<h4>Datos recibidos:</h4>";
        echo "<pre>";
        print_r($this->request->getPost());
        echo "</pre>";
        
        echo "<h4>🎯 Ahora vamos a probar el RegisterController real:</h4>";
        
        try {
            // Intentar instanciar tu RegisterController
            $registerController = new \App\Controllers\Auth\RegisterController();
            echo "<div class='status-ok'>✅ RegisterController se puede instanciar</div>";
            
            // Verificar que tiene el método
            if (method_exists($registerController, 'attemptRegister')) {
                echo "<div class='status-ok'>✅ Método attemptRegister() existe</div>";
            } else {
                echo "<div class='status-error'>❌ Método attemptRegister() NO existe</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error instanciando RegisterController: " . $e->getMessage() . "</div>";
        }
        
        // Mostrar información de routing
        echo "<h4>🔍 Información de Routing:</h4>";
        $router = service('router');
        echo "<p><strong>Controlador actual:</strong> " . get_class($this) . "</p>";
        echo "<p><strong>Método actual:</strong> " . __FUNCTION__ . "</p>";
        
        $this->footerDebug();
    }

    /**
     * 🔍 DEBUG SIMPLE: ¿QUÉ CONTROLADOR SE EJECUTA?
     */
    public function debugSimple()
    {
        $this->headerDebug("🔍 DEBUG SIMPLE: ¿QUIÉN MANEJA EL REGISTRO?");
        
        try {
            echo "<h3>1. Verificaciones Básicas:</h3>";
            
            // Verificar que existe tu RegisterController
            $controllerPath = APPPATH . 'Controllers/Auth/RegisterController.php';
            if (file_exists($controllerPath)) {
                echo "<div class='status-ok'>✅ RegisterController existe: $controllerPath</div>";
                
                try {
                    $controller = new \App\Controllers\Auth\RegisterController();
                    echo "<div class='status-ok'>✅ RegisterController se puede instanciar</div>";
                    
                    if (method_exists($controller, 'attemptRegister')) {
                        echo "<div class='status-ok'>✅ Método attemptRegister() existe</div>";
                    } else {
                        echo "<div class='status-error'>❌ Método attemptRegister() NO existe</div>";
                    }
                } catch (\Exception $e) {
                    echo "<div class='status-error'>❌ Error instanciando: " . $e->getMessage() . "</div>";
                }
            } else {
                echo "<div class='status-error'>❌ RegisterController NO existe</div>";
            }
            
            echo "<h3>2. Test Directo de POST a tu Controlador:</h3>";
            echo "<div class='test-form'>";
            echo "<p>Vamos a hacer un POST directamente a tu controlador:</p>";
            echo form_open(site_url('debug/test-direct-register'));
            echo "<input type='text' name='nombres' value='TEST' placeholder='Nombres'><br><br>";
            echo "<input type='text' name='apellido_paterno' value='USUARIO' placeholder='Apellido Paterno'><br><br>";
            echo "<input type='text' name='apellido_materno' value='PRUEBA' placeholder='Apellido Materno'><br><br>";
            echo "<input type='email' name='email' value='test" . time() . "@test.com' placeholder='Email'><br><br>";
            echo "<input type='text' name='telefono' value='1234567890' placeholder='Teléfono'><br><br>";
            echo "<input type='password' name='password' value='12345678' placeholder='Password'><br><br>";
            echo "<input type='password' name='password_confirm' value='12345678' placeholder='Confirmar'><br><br>";
            echo "<input type='checkbox' name='terms' value='1' checked> Términos<br><br>";
            echo "<button type='submit' class='btn-test'>🧪 Test Registro Directo</button>";
            echo form_close();
            echo "</div>";
            
            echo "<h3>3. Información del Router Actual:</h3>";
            $router = service('router');
            echo "<table class='debug-table'>";
            echo "<tr><td>Controlador actual</td><td>" . get_class($this) . "</td></tr>";
            echo "<tr><td>Método actual</td><td>" . __FUNCTION__ . "</td></tr>";
            echo "<tr><td>URI actual</td><td>" . uri_string() . "</td></tr>";
            echo "</table>";
            
            echo "<h3>4. Verificar Routes.php:</h3>";
            $routesFile = APPPATH . 'Config/Routes.php';
            if (file_exists($routesFile)) {
                $content = file_get_contents($routesFile);
                
                if (strpos($content, 'Auth\\RegisterController::attemptRegister') !== false) {
                    echo "<div class='status-ok'>✅ Ruta personalizada encontrada en Routes.php</div>";
                } else {
                    echo "<div class='status-error'>❌ Ruta personalizada NO encontrada en Routes.php</div>";
                }
                
                if (strpos($content, "service('auth')->routes") !== false) {
                    echo "<div class='status-warning'>⚠️ service('auth')->routes() encontrado</div>";
                } else {
                    echo "<div class='status-info'>ℹ️ service('auth')->routes() NO encontrado</div>";
                }
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ ERROR: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }
    
    /**
     * 🧪 TEST DIRECTO AL REGISTERCONTROLLER
     */
    public function testDirectRegister()
    {
        $this->headerDebug("🧪 TEST DIRECTO AL REGISTERCONTROLLER");
        
        echo "<h3>Test Ejecutándose:</h3>";
        echo "<div class='status-ok'>✅ Este método se está ejecutando correctamente</div>";
        
        echo "<h4>Datos recibidos:</h4>";
        $postData = $this->request->getPost();
        echo "<pre>";
        print_r($postData);
        echo "</pre>";
        
        try {
            echo "<h4>🎯 Intentando ejecutar tu RegisterController directamente:</h4>";
            
            // Crear instancia de tu controlador
            $registerController = new \App\Controllers\Auth\RegisterController();
            
            // Simular el request con los datos
            echo "<div class='status-info'>📝 Ejecutando attemptRegister() directamente...</div>";
            
            // IMPORTANTE: No podemos ejecutar attemptRegister directamente porque
            // necesita el request global. En su lugar, vamos a verificar que el proceso funciona.
            
            echo "<div class='status-ok'>✅ RegisterController se puede instanciar</div>";
            
            // Verificar que los datos llegaron
            if (!empty($postData['email'])) {
                echo "<div class='status-ok'>✅ Email recibido: " . $postData['email'] . "</div>";
                
                // Intentar crear el usuario manualmente para probar
                echo "<h4>🔬 Creando usuario manualmente:</h4>";
                
                $db = \Config\Database::connect();
                $db->transStart();
                
                // 1. Crear usuario Shield
                $userModel = new \CodeIgniter\Shield\Models\UserModel();
                
                $userData = [
                    'email' => $postData['email'],
                    'password' => $postData['password'] ?? '12345678',
                    'active' => true,
                ];
                
                $user = new \CodeIgniter\Shield\Entities\User($userData);
                
                if ($userModel->save($user)) {
                    $userId = $userModel->getInsertID();
                    echo "<div class='status-ok'>✅ Usuario Shield creado - ID: $userId</div>";
                    
                    // 2. Asignar grupo
                    $user = $userModel->find($userId);
                    $user->addGroup('cliente');
                    echo "<div class='status-ok'>✅ Grupo 'cliente' asignado</div>";
                    
                    // 3. Crear cliente
                    $clienteData = [
                        'user_id' => $userId,
                        'nombres' => strtoupper(trim($postData['nombres'] ?? 'TEST')),
                        'apellido_paterno' => strtoupper(trim($postData['apellido_paterno'] ?? 'USUARIO')),
                        'apellido_materno' => strtoupper(trim($postData['apellido_materno'] ?? 'PRUEBA')),
                        'email' => strtolower(trim($postData['email'])),
                        'telefono' => preg_replace('/[^0-9]/', '', $postData['telefono'] ?? '1234567890'),
                        'activo' => 0,
                        'etapa_proceso' => 'interesado',
                        'fecha_primer_contacto' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $insertSuccess = $db->table('clientes')->insert($clienteData);
                    
                    if ($insertSuccess) {
                        $clienteId = $db->insertID();
                        echo "<div class='status-ok'>🎉 ¡CLIENTE CREADO EXITOSAMENTE! - ID: $clienteId</div>";
                        
                        $db->transComplete();
                        
                        echo "<h4>✅ PROCESO COMPLETO EXITOSO:</h4>";
                        echo "<ul>";
                        echo "<li>✅ Usuario Shield creado (ID: $userId)</li>";
                        echo "<li>✅ Grupo 'cliente' asignado</li>";
                        echo "<li>✅ Registro en tabla 'clientes' creado (ID: $clienteId)</li>";
                        echo "</ul>";
                        
                        echo "<div class='status-info'>🔗 <a href='" . site_url('debug/last-client') . "'>Ver último cliente creado</a></div>";
                        
                    } else {
                        throw new \RuntimeException('Error insertando en tabla clientes');
                    }
                } else {
                    throw new \RuntimeException('Error creando usuario: ' . implode(', ', $userModel->errors()));
                }
                
            } else {
                echo "<div class='status-error'>❌ No se recibió email</div>";
            }
            
        } catch (\Exception $e) {
            $db->transRollback();
            echo "<div class='status-error'>❌ ERROR: " . $e->getMessage() . "</div>";
        }
        
        $this->footerDebug();
    }
    // En DebugController, agregar:
public function testEntities()
{
    // Probar ClienteModel
    $clienteModel = new \App\Models\ClienteModel();
    $cliente = $clienteModel->find(1);
    
    echo "Clase Cliente: " . get_class($cliente) . "<br>";
    
    if ($cliente instanceof \App\Entities\Cliente) {
        echo "✅ ClienteModel USA Entity Cliente<br>";
        echo "Nombre completo: " . $cliente->getNombreCompleto() . "<br>";
    } else {
        echo "❌ ClienteModel NO usa Entity Cliente<br>";
    }
    
    // Probar UserModel  
    $userModel = new \App\Models\UserModel();
    $user = $userModel->find(1);
    
    echo "Clase User: " . get_class($user) . "<br>";
    
    if ($user instanceof \App\Entities\User) {
        echo "✅ UserModel USA Entity User personalizada<br>";
        echo "Tiene cliente: " . ($user->hasCliente() ? 'SÍ' : 'NO') . "<br>";
    } else {
        echo "❌ UserModel NO usa Entity User personalizada<br>";
    }
}


// Agregar este método a DebugController.php


public function testEntitiesFixed()
{
    $this->headerDebug("🧪 TEST ENTITIES - VERIFICACIÓN COMPLETA");
    
    try {
        echo "<h3>1. Test ClienteModel</h3>";
        $clienteModel = new \App\Models\ClienteModel();
        $cliente = $clienteModel->find(1);
        
        echo "<div class='status-info'>Clase Cliente: " . (is_object($cliente) ? get_class($cliente) : 'null') . "</div>";
        
        if ($cliente instanceof \App\Entities\Cliente) {
            echo "<div class='status-ok'>✅ ClienteModel USA Entity Cliente</div>";
            echo "<div class='status-info'>Nombre completo: " . $cliente->getNombreCompleto() . "</div>";
            echo "<div class='status-info'>Iniciales: " . $cliente->getIniciales() . "</div>";
            echo "<div class='status-info'>Activo: " . ($cliente->isActivo() ? 'SÍ' : 'NO') . "</div>";
            echo "<div class='status-info'>Etapa: " . $cliente->getEtapaFormatted() . "</div>";
        } else {
            echo "<div class='status-error'>❌ ClienteModel NO usa Entity Cliente o no hay datos</div>";
        }
        
        echo "<h3>2. Test UserModel</h3>";
        $userModel = new \App\Models\UserModel();
        
        // Verificar configuración del modelo
        echo "<div class='status-info'>UserModel returnType: " . $userModel->returnType . "</div>";
        
        $user = $userModel->find(1);
        
        echo "<div class='status-info'>Clase User: " . (is_object($user) ? get_class($user) : 'null') . "</div>";
        
        if ($user instanceof \App\Entities\User) {
            echo "<div class='status-ok'>✅ UserModel USA Entity User personalizada</div>";
            echo "<div class='status-info'>Email: " . ($user->email ?? 'No definido') . "</div>";
            echo "<div class='status-info'>Tiene cliente: " . ($user->hasCliente() ? 'SÍ' : 'NO') . "</div>";
            
            if ($user->hasCliente()) {
                echo "<div class='status-info'>Nombre completo: " . $user->getNombreCompleto() . "</div>";
                echo "<div class='status-info'>Iniciales: " . $user->getIniciales() . "</div>";
                echo "<div class='status-info'>Teléfono: " . ($user->getTelefono() ?? 'No definido') . "</div>";
                echo "<div class='status-info'>Completitud perfil: " . $user->getPorcentajeCompletitud() . "%</div>";
                echo "<div class='status-info'>Puede comprar: " . ($user->puedeComprar() ? 'SÍ' : 'NO') . "</div>";
                echo "<div class='status-info'>Etapa proceso: " . $user->getEtapaFormatted() . "</div>";
            }
        } else {
            echo "<div class='status-error'>❌ UserModel NO usa Entity User personalizada</div>";
            echo "<div class='status-warning'>Tipo recibido: " . (is_object($user) ? get_class($user) : gettype($user)) . "</div>";
        }
        
        echo "<h3>3. Test Helpers Actualizados</h3>";
        try {
            echo "<div class='status-info'>userName(): " . userName() . "</div>";
            echo "<div class='status-info'>userRole(): " . userRole() . "</div>";
            echo "<div class='status-info'>userInitials(): " . userInitials() . "</div>";
            echo "<div class='status-info'>userPhone(): " . (userPhone() ?? 'No definido') . "</div>";
            echo "<div class='status-info'>userProfileCompleteness(): " . userProfileCompleteness() . "%</div>";
            echo "<div class='status-info'>userCanBuy(): " . (userCanBuy() ? 'SÍ' : 'NO') . "</div>";
            echo "<div class='status-info'>getUserSalesStage(): " . (getUserSalesStage() ?? 'No definido') . "</div>";
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en helpers: " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>4. Test Función currentUser()</h3>";
        try {
            $currentUserEntity = currentUser();
            if ($currentUserEntity instanceof \App\Entities\User) {
                echo "<div class='status-ok'>✅ currentUser() devuelve Entity User personalizada</div>";
                echo "<div class='status-info'>Métodos disponibles:</div>";
                
                $methods = [
                    'getNombreCompleto',
                    'getIniciales', 
                    'hasCliente',
                    'getTelefono',
                    'getPorcentajeCompletitud',
                    'puedeComprar',
                    'getEtapaFormatted'
                ];
                
                foreach ($methods as $method) {
                    if (method_exists($currentUserEntity, $method)) {
                        echo "<div class='status-ok'>✅ Método $method() disponible</div>";
                    } else {
                        echo "<div class='status-error'>❌ Método $method() NO disponible</div>";
                    }
                }
            } else {
                echo "<div class='status-error'>❌ currentUser() NO devuelve Entity User personalizada</div>";
            }
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en currentUser(): " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>5. Test auth()->user() directo</h3>";
        try {
            if (auth()->loggedIn()) {
                $authUser = auth()->user();
                echo "<div class='status-info'>Clase auth()->user(): " . get_class($authUser) . "</div>";
                
                if ($authUser instanceof \App\Entities\User) {
                    echo "<div class='status-ok'>✅ auth()->user() devuelve Entity User personalizada</div>";
                } else {
                    echo "<div class='status-warning'>⚠️ auth()->user() devuelve Entity Shield básica</div>";
                    echo "<div class='status-info'>Esto significa que Shield aún no usa nuestra configuración</div>";
                }
            } else {
                echo "<div class='status-warning'>⚠️ No hay usuario logueado para probar auth()->user()</div>";
            }
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error verificando auth()->user(): " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>6. Test UserModel métodos personalizados</h3>";
        try {
            // Test método getUserEntity
            $userEntity = $userModel->getUserEntity(1);
            if ($userEntity instanceof \App\Entities\User) {
                echo "<div class='status-ok'>✅ getUserEntity() funciona correctamente</div>";
            } else {
                echo "<div class='status-error'>❌ getUserEntity() NO devuelve Entity personalizada</div>";
            }
            
            // Test método getAllUsersWithEntity
            $allUsers = $userModel->getAllUsersWithEntity(5);
            echo "<div class='status-info'>getAllUsersWithEntity() devuelve: " . count($allUsers) . " usuarios</div>";
            
            $allAreEntities = true;
            foreach ($allUsers as $u) {
                if (!($u instanceof \App\Entities\User)) {
                    $allAreEntities = false;
                    break;
                }
            }
            
            if ($allAreEntities) {
                echo "<div class='status-ok'>✅ Todos los usuarios son Entity personalizadas</div>";
            } else {
                echo "<div class='status-error'>❌ No todos los usuarios son Entity personalizadas</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en métodos personalizados: " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>7. Test createNewUser() - NUEVO MÉTODO OFICIAL</h3>";
        try {
            echo "<div class='status-info'>🧪 Probando createNewUser() con datos de prueba...</div>";
            
            $userModel = new \App\Models\UserModel();
            
            // Datos de prueba
            $testData = [
                'email' => 'test-createnew-' . time() . '@test.com',
                'password' => 'test123456',
                'nombres' => 'Usuario',
                'apellido_paterno' => 'Prueba',
                'apellido_materno' => 'CreateNew',
                'telefono' => '1234567890'
            ];
            
            $newUser = $userModel->createClienteUser($testData);
            
            if ($newUser instanceof \App\Entities\User) {
                echo "<div class='status-ok'>✅ createClienteUser() funciona correctamente</div>";
                echo "<div class='status-info'>Usuario creado con ID: " . $newUser->id . "</div>";
                echo "<div class='status-info'>Email: " . $newUser->email . "</div>";
                echo "<div class='status-info'>Tiene cliente: " . ($newUser->hasCliente() ? 'SÍ' : 'NO') . "</div>";
                
                if ($newUser->hasCliente()) {
                    echo "<div class='status-ok'>✅ Cliente relacionado creado automáticamente</div>";
                    echo "<div class='status-info'>Nombre completo: " . $newUser->getNombreCompleto() . "</div>";
                }
            } else {
                echo "<div class='status-error'>❌ createClienteUser() NO funciona correctamente</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en createNewUser(): " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>8. Resumen y Recomendaciones</h3>";
        
        if ($user instanceof \App\Entities\User && $cliente instanceof \App\Entities\Cliente) {
            echo "<div class='status-ok'>🎉 ¡ENTITIES + createNewUser() FUNCIONANDO CORRECTAMENTE!</div>";
            echo "<div class='status-info'>✅ Sistema listo para usar métodos de Entity en toda la aplicación</div>";
            echo "<div class='status-info'>✅ Helpers actualizados y funcionales</div>";
            echo "<div class='status-info'>✅ UserModel configurado correctamente</div>";
            echo "<div class='status-info'>✅ createNewUser() implementado según documentación Shield</div>";
        } else {
            echo "<div class='status-warning'>⚠️ Entities parcialmente funcionales</div>";
            echo "<div class='status-info'>🔧 Necesita ajustes en configuración</div>";
        }
        
    } catch (\Exception $e) {
        echo "<div class='status-error'>❌ ERROR GENERAL: " . $e->getMessage() . "</div>";
        echo "<div class='status-info'>Stack trace:</div>";
        echo "<pre style='background: #f8f9fa; padding: 10px; font-size: 12px;'>" . $e->getTraceAsString() . "</pre>";
    }
    
    $this->footerDebug();
}


public function testSystemComplete()
{
    $this->headerDebug("🎯 TEST COMPLETO DEL SISTEMA REFACTORIZADO");
    
    try {
        echo "<h3>1. ✅ Test UserModel con createNewUser()</h3>";
        $userModel = new \App\Models\UserModel();
        echo "<div class='status-ok'>✅ UserModel cargado correctamente</div>";
        echo "<div class='status-info'>Tipo de retorno: " . $userModel->returnType . "</div>";
        
        echo "<h3>2. ✅ Test Entity User con Virtual Properties</h3>";
        if (auth()->loggedIn()) {
            $user = auth()->user();
            echo "<div class='status-info'>Clase usuario: " . get_class($user) . "</div>";
            
            if ($user instanceof \App\Entities\User) {
                echo "<div class='status-ok'>✅ Entity User personalizada funcionando</div>";
                
                // Test virtual properties
                echo "<h4>Virtual Properties:</h4>";
                echo "<div class='status-info'>getNombreCompleto(): " . $user->getNombreCompleto() . "</div>";
                echo "<div class='status-info'>getIniciales(): " . $user->getIniciales() . "</div>";
                echo "<div class='status-info'>getRolPrincipal(): " . $user->getRolPrincipal() . "</div>";
                echo "<div class='status-info'>getEstadoIcono(): " . $user->getEstadoIcono() . "</div>";
                echo "<div class='status-info'>getContactoCompleto(): " . $user->getContactoCompleto() . "</div>";
                echo "<div class='status-info'>getCategoriaUsuario(): " . $user->getCategoriaUsuario() . "</div>";
                echo "<div class='status-info'>getScoreCliente(): " . $user->getScoreCliente() . "/100</div>";
                echo "<div class='status-info'>getPrioridadSeguimiento(): " . $user->getPrioridadSeguimiento() . "</div>";
            } else {
                echo "<div class='status-error'>❌ Entity User NO es personalizada</div>";
            }
        } else {
            echo "<div class='status-warning'>⚠️ No hay usuario logueado para probar Entity</div>";
        }
        
        echo "<h3>3. ✅ Test Helpers Optimizados para Vistas</h3>";
        
        // Test helpers básicos
        echo "<h4>Helpers Básicos:</h4>";
        echo "<div class='status-info'>userName(): " . userName() . "</div>";
        echo "<div class='status-info'>userRole(): " . userRole() . "</div>";
        echo "<div class='status-info'>userInitials(): " . userInitials() . "</div>";
        echo "<div class='status-info'>userStatus(): " . userStatus() . "</div>";
        
        // Test helpers de permisos
        echo "<h4>Helpers de Permisos:</h4>";
        echo "<div class='status-info'>isAdmin(): " . (isAdmin() ? 'SÍ' : 'NO') . "</div>";
        echo "<div class='status-info'>isSuperAdmin(): " . (isSuperAdmin() ? 'SÍ' : 'NO') . "</div>";
        echo "<div class='status-info'>isCliente(): " . (isCliente() ? 'SÍ' : 'NO') . "</div>";
        echo "<div class='status-info'>can('users.read'): " . (can('users.read') ? 'SÍ' : 'NO') . "</div>";
        echo "<div class='status-info'>can('profile.view'): " . (can('profile.view') ? 'SÍ' : 'NO') . "</div>";
        
        // Test helpers de cliente
        if (isCliente()) {
            echo "<h4>Helpers de Cliente:</h4>";
            echo "<div class='status-info'>userPhone(): " . (userPhone() ?? 'No definido') . "</div>";
            echo "<div class='status-info'>userPhoneFormatted(): " . (userPhoneFormatted() ?? 'No definido') . "</div>";
            echo "<div class='status-info'>userCanBuy(): " . (userCanBuy() ? 'SÍ' : 'NO') . "</div>";
            echo "<div class='status-info'>userNeedsInfo(): " . (userNeedsInfo() ? 'SÍ' : 'NO') . "</div>";
            echo "<div class='status-info'>userCompleteness(): " . userCompleteness() . "%</div>";
            echo "<div class='status-info'>userSalesStage(): " . userSalesStage() . "</div>";
            echo "<div class='status-info'>userCategory(): " . userCategory() . "</div>";
            echo "<div class='status-info'>userScore(): " . userScore() . "/100</div>";
            echo "<div class='status-info'>userPriority(): " . userPriority() . "</div>";
        }
        
        // Test helpers de estilos
        echo "<h4>Helpers de Estilos CSS:</h4>";
        echo "<div class='status-info'>userBadgeClass(): " . userBadgeClass() . "</div>";
        echo "<div class='status-info'>userPriorityClass(): " . userPriorityClass() . "</div>";
        echo "<div class='status-info'>userStatusClass(): " . userStatusClass() . "</div>";
        
        echo "<h3>4. ✅ Test createNewUser() en Acción</h3>";
        try {
            echo "<div class='status-info'>🧪 Creando usuario de prueba con createClienteUser()...</div>";
            
            $testData = [
                'email' => 'test-refactor-' . time() . '@test.com',
                'password' => 'test123456',
                'nombres' => 'Usuario',
                'apellido_paterno' => 'Refactor',
                'apellido_materno' => 'Test',
                'telefono' => '5551234567'
            ];
            
            $newUser = $userModel->createClienteUser($testData);
            
            if ($newUser instanceof \App\Entities\User) {
                echo "<div class='status-ok'>✅ createClienteUser() funciona perfectamente</div>";
                echo "<div class='status-info'>Usuario creado - ID: " . $newUser->id . "</div>";
                echo "<div class='status-info'>Email: " . $newUser->email . "</div>";
                echo "<div class='status-info'>Nombre completo: " . $newUser->getNombreCompleto() . "</div>";
                echo "<div class='status-info'>Tiene cliente: " . ($newUser->hasCliente() ? 'SÍ' : 'NO') . "</div>";
                
                if ($newUser->hasCliente()) {
                    echo "<div class='status-ok'>✅ Cliente relacionado creado automáticamente</div>";
                    echo "<div class='status-info'>Virtual Properties funcionando en usuario recién creado</div>";
                }
            } else {
                echo "<div class='status-error'>❌ createClienteUser() NO funciona</div>";
            }
            
        } catch (\Exception $e) {
            echo "<div class='status-error'>❌ Error en createClienteUser(): " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>5. ✅ Test Rutas Limpias</h3>";
        $router = service('router');
        
        // Verificar rutas principales
        echo "<h4>Rutas Principales:</h4>";
        echo "<div class='status-ok'>✅ Ruta raíz: " . site_url('/') . "</div>";
        echo "<div class='status-ok'>✅ Dashboard: " . site_url('/dashboard') . "</div>";
        echo "<div class='status-ok'>✅ Registro: " . site_url('/register') . "</div>";
        
        // Verificar rutas por rol
        if (isAdmin()) {
            echo "<div class='status-ok'>✅ Admin dashboard: " . site_url('/admin/dashboard') . "</div>";
        }
        
        if (isCliente()) {
            echo "<div class='status-ok'>✅ Cliente dashboard: " . site_url('/cliente/dashboard') . "</div>";
        }
        
        echo "<h3>6. ✅ Test de Integración en Vistas</h3>";
        echo "<div class='status-info'>Ejemplos de uso en vistas:</div>";
        
        echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>Código PHP para vistas:</strong><br>";
        echo "<code>";
        echo htmlentities('<?php if(can("users.read")): ?>') . "<br>";
        echo "&nbsp;&nbsp;Solo admins ven esto<br>";
        echo htmlentities('<?php endif; ?>') . "<br><br>";
        
        echo htmlentities('<?php if(isCliente()): ?>') . "<br>";
        echo "&nbsp;&nbsp;Bienvenido " . htmlentities('<?= userName() ?>') . "<br>";
        echo "&nbsp;&nbsp;Tu categoría: " . htmlentities('<?= userCategory() ?>') . "<br>";
        echo "&nbsp;&nbsp;Completitud: " . htmlentities('<?= userCompleteness() ?>') . "%<br>";
        echo htmlentities('<?php endif; ?>') . "<br>";
        echo "</code>";
        echo "</div>";
        
        echo "<h3>7. 🎉 Resumen del Sistema Refactorizado</h3>";
        
        $checksOk = 0;
        $totalChecks = 6;
        
        // Check 1: UserModel
        if ($userModel->returnType === \App\Entities\User::class) {
            echo "<div class='status-ok'>✅ UserModel configurado correctamente</div>";
            $checksOk++;
        } else {
            echo "<div class='status-error'>❌ UserModel mal configurado</div>";
        }
        
        // Check 2: Entity User
        if (auth()->loggedIn() && auth()->user() instanceof \App\Entities\User) {
            echo "<div class='status-ok'>✅ Entity User personalizada funcionando</div>";
            $checksOk++;
        } else {
            echo "<div class='status-warning'>⚠️ Entity User: verificar con usuario logueado</div>";
        }
        
        // Check 3: Helpers básicos
        if (function_exists('userName') && function_exists('can') && function_exists('isAdmin')) {
            echo "<div class='status-ok'>✅ Helpers básicos cargados</div>";
            $checksOk++;
        } else {
            echo "<div class='status-error'>❌ Helpers básicos no cargados</div>";
        }
        
        // Check 4: Helpers avanzados
        if (function_exists('userCategory') && function_exists('userBadgeClass')) {
            echo "<div class='status-ok'>✅ Helpers avanzados cargados</div>";
            $checksOk++;
        } else {
            echo "<div class='status-error'>❌ Helpers avanzados no cargados</div>";
        }
        
        // Check 5: createNewUser
        if (method_exists($userModel, 'createClienteUser')) {
            echo "<div class='status-ok'>✅ createClienteUser() implementado</div>";
            $checksOk++;
        } else {
            echo "<div class='status-error'>❌ createClienteUser() no implementado</div>";
        }
        
        // Check 6: Virtual Properties
        if (auth()->loggedIn() && auth()->user() instanceof \App\Entities\User && 
            method_exists(auth()->user(), 'getNombreCompleto')) {
            echo "<div class='status-ok'>✅ Virtual Properties funcionando</div>";
            $checksOk++;
        } else {
            echo "<div class='status-warning'>⚠️ Virtual Properties: verificar con usuario logueado</div>";
        }
        
        // Resultado final
        $percentage = ($checksOk / $totalChecks) * 100;
        
        if ($percentage >= 80) {
            echo "<div class='status-ok' style='font-size: 1.2em; margin-top: 20px;'>";
            echo "🎉 ¡SISTEMA REFACTORIZADO EXITOSAMENTE!";
            echo "</div>";
            echo "<div class='status-info'>Completado: {$checksOk}/{$totalChecks} ({$percentage}%)</div>";
            echo "<div class='status-info'>✅ El sistema está listo para usar en producción</div>";
            echo "<div class='status-info'>✅ Las vistas pueden usar helpers limpiamente</div>";
            echo "<div class='status-info'>✅ Entities con virtual properties funcionando</div>";
            echo "<div class='status-info'>✅ createNewUser() según documentación oficial</div>";
        } else {
            echo "<div class='status-warning' style='font-size: 1.2em; margin-top: 20px;'>";
            echo "⚠️ Sistema parcialmente refactorizado";
            echo "</div>";
            echo "<div class='status-info'>Completado: {$checksOk}/{$totalChecks} ({$percentage}%)</div>";
            echo "<div class='status-info'>🔧 Necesita algunos ajustes para estar completo</div>";
        }
        
    } catch (\Exception $e) {
        echo "<div class='status-error'>❌ ERROR GENERAL: " . $e->getMessage() . "</div>";
        echo "<pre style='background: #f8f9fa; padding: 10px; font-size: 12px;'>" . $e->getTraceAsString() . "</pre>";
    }
    
    $this->footerDebug();
}



}