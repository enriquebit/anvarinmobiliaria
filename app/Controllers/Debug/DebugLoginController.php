<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;

class DebugLoginController extends BaseController
{
    public function testLoginValidation()
    {
        $this->debugHeader("🔍 DEBUG LOGIN VALIDATION");
        
        echo "<h2>1. Verificar configuración de validaciones:</h2>";
        
        try {
            $validation = config('Validation');
            
            echo "<h3>Validaciones definidas en Validation.php:</h3>";
            
            // Verificar si existe 'login' en validaciones
            if (isset($validation->login)) {
                echo "<p class='success'>✅ Validación 'login' EXISTE</p>";
                echo "<pre>" . json_encode($validation->login, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p class='error'>❌ Validación 'login' NO EXISTE</p>";
                echo "<p class='warning'>⚠️ Shield usará validaciones por defecto</p>";
            }
            
            // Verificar validación 'registration'
            if (isset($validation->registration)) {
                echo "<h3>Validación 'registration' (para referencia):</h3>";
                echo "<pre>" . json_encode($validation->registration, JSON_PRETTY_PRINT) . "</pre>";
            }
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error leyendo Validation.php: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
        echo "<h2>2. Verificar configuración Auth.php:</h2>";
        
        try {
            $auth = config('Auth');
            
            echo "<p><strong>Valid Fields:</strong> " . implode(', ', $auth->validFields) . "</p>";
            echo "<p><strong>User Provider:</strong> " . $auth->userProvider . "</p>";
            echo "<p><strong>Default Authenticator:</strong> " . $auth->defaultAuthenticator . "</p>";
            
            // Verificar si tiene validaciones de email/username
            echo "<h3>Email Validation Rules:</h3>";
            if (!empty($auth->emailValidationRules)) {
                echo "<pre>" . json_encode($auth->emailValidationRules, JSON_PRETTY_PRINT) . "</pre>";
                
                // ⚠️ AQUÍ ESTÁ EL PROBLEMA POTENCIAL
                if (in_array('is_unique[auth_identities.secret]', $auth->emailValidationRules)) {
                    echo "<p class='error'>❌ PROBLEMA ENCONTRADO: Auth.php tiene regla 'is_unique' para email</p>";
                    echo "<p class='warning'>⚠️ Esta regla es para REGISTRO, no para LOGIN</p>";
                }
            } else {
                echo "<p class='info'>Email validation rules están vacías</p>";
            }
            
            echo "<h3>Username Validation Rules:</h3>";
            if (!empty($auth->usernameValidationRules)) {
                echo "<pre>" . json_encode($auth->usernameValidationRules, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p class='success'>✅ Username validation rules están vacías (correcto)</p>";
            }
            
        } catch (\Exception $e) {
            echo "<p class='error'>❌ Error leyendo Auth.php: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
        echo "<h2>3. Test de validación manual:</h2>";
        
        // Simular datos de login
        $loginData = [
            'email' => 'admin@nuevoanvar.test',
            'password' => 'test123'
        ];
        
        echo "<p>Probando validación con datos:</p>";
        echo "<pre>" . json_encode($loginData, JSON_PRETTY_PRINT) . "</pre>";
        
        // Probar validación 'login' si existe
        $validator = \Config\Services::validation();
        
        if (method_exists($validator, 'run') && isset(config('Validation')->login)) {
            echo "<h4>Probando validación 'login':</h4>";
            
            if ($validator->run($loginData, 'login')) {
                echo "<p class='success'>✅ Validación 'login' PASÓ</p>";
            } else {
                echo "<p class='error'>❌ Validación 'login' FALLÓ:</p>";
                foreach ($validator->getErrors() as $error) {
                    echo "<p class='error'>- $error</p>";
                }
            }
        } else {
            echo "<p class='warning'>⚠️ No se puede probar validación 'login' (no existe)</p>";
        }
        
        // Probar validación 'registration' (NO debería usarse para login)
        if (isset(config('Validation')->registration)) {
            echo "<h4>Probando validación 'registration' (NO debería usarse en login):</h4>";
            
            if ($validator->run($loginData, 'registration')) {
                echo "<p class='info'>Validación 'registration' pasó</p>";
            } else {
                echo "<p class='error'>❌ Validación 'registration' FALLÓ (esperado):</p>";
                foreach ($validator->getErrors() as $error) {
                    echo "<p class='error'>- $error</p>";
                }
                echo "<p class='warning'>⚠️ Si el login usa 'registration', AQUÍ está el problema</p>";
            }
        }
        
        echo "<hr>";
        echo "<h2>4. Verificar rutas de login:</h2>";
        
        try {
            $routes = \Config\Services::routes();
            
            echo "<p>Verificando qué controlador maneja '/login'...</p>";
            
            // Esto es complicado de hacer programáticamente, mejor mostrar info
            echo "<p class='info'>Verifica en tu Routes.php:</p>";
            echo "<p>¿Tienes definido: <code>\$routes->get('login', '\\CodeIgniter\\Shield\\Controllers\\LoginController::loginView');</code>?</p>";
            echo "<p>¿O tienes: <code>\$routes->get('login', 'Auth\\LoginController::index');</code>?</p>";
            
        } catch (\Exception $e) {
            echo "<p class='error'>Error verificando rutas: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
        echo "<h2>🎯 SOLUCIONES SUGERIDAS:</h2>";
        
        echo "<div class='success'>";
        echo "<h3>SOLUCIÓN 1: Agregar validación 'login' correcta</h3>";
        echo "<p>Agregar esto a tu Validation.php:</p>";
        echo "<pre>";
        echo "public \$login = [
    'email' => [
        'label' => 'Email',
        'rules' => [
            'required',
            'valid_email',
            // NO incluir 'is_unique' aquí
        ],
    ],
    'password' => [
        'label' => 'Contraseña', 
        'rules' => [
            'required',
        ],
    ],
];";
        echo "</pre>";
        echo "</div>";
        
        echo "<div class='warning'>";
        echo "<h3>SOLUCIÓN 2: Limpiar Auth.php</h3>";
        echo "<p>Remover 'is_unique' de emailValidationRules en Auth.php</p>";
        echo "</div>";
        
        $this->debugFooter();
    }
    
    public function testLoginAttempt()
    {
        $this->debugHeader("🧪 TEST LOGIN ATTEMPT");
        
        echo "<h2>Simulando intento de login:</h2>";
        
        $email = $this->request->getPost('email') ?: 'admin@nuevoanvar.test';
        $password = $this->request->getPost('password') ?: 'test123';
        
        echo "<form method='post'>";
        echo "<div class='mb-3'>";
        echo "<label>Email:</label>";
        echo "<input type='email' name='email' value='$email' class='form-control'>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label>Password:</label>";
        echo "<input type='password' name='password' value='$password' class='form-control'>";
        echo "</div>";
        echo "<button type='submit' class='btn btn-primary'>Test Login</button>";
        echo "</form>";
        
        if ($this->request->getMethod() === 'POST') {
            echo "<hr>";
            echo "<h3>Resultado del test:</h3>";
            
            try {
                // Probar login directo
                $authenticator = auth('session')->getAuthenticator();
                
                $credentials = [
                    'email' => $email,
                    'password' => $password
                ];
                
                echo "<p>Probando credenciales:</p>";
                echo "<pre>" . json_encode($credentials, JSON_PRETTY_PRINT) . "</pre>";
                
                $result = $authenticator->attempt($credentials);
                
                if ($result->isOK()) {
                    echo "<p class='success'>✅ LOGIN EXITOSO</p>";
                    echo "<p>Usuario logueado: " . auth()->user()->email . "</p>";
                } else {
                    echo "<p class='error'>❌ LOGIN FALLÓ</p>";
                    echo "<p>Razón: " . $result->reason() . "</p>";
                }
                
            } catch (\Exception $e) {
                echo "<p class='error'>❌ ERROR EN LOGIN: " . $e->getMessage() . "</p>";
                echo "<p>Archivo: " . $e->getFile() . "</p>";
                echo "<p>Línea: " . $e->getLine() . "</p>";
            }
        }
        
        $this->debugFooter();
    }
    
    private function debugHeader(string $title)
    {
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
            .container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; }
            .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; }
            .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; }
            .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; }
            pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        </style>";
        
        echo "<div class='container'>";
        echo "<h1>$title</h1>";
    }
    
    private function debugFooter()
    {
        echo "<hr>";
        echo "<p><a href='" . site_url('debug-login') . "'>← Volver al test principal</a></p>";
        echo "</div>";
    }
}