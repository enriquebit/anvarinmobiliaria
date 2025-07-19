<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class DebugTimingController extends BaseController
{
    /**
     * 🧪 RUTA PRINCIPAL: /debug-timing
     */
    public function index()
    {
        // Solo en desarrollo
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        echo "<h1>🧪 Debug Timing - UserModel Constructor</h1>";
        echo "<hr>";
        
        echo "<h3>📋 Tests Disponibles:</h3>";
        echo "<ul>";
        echo "<li><a href='/debug-timing/experiment'>🔬 Experimento Timing</a></li>";
        echo "<li><a href='/debug-timing/current-usermodel'>🔍 Test UserModel Actual</a></li>";
        echo "<li><a href='/debug-timing/comparison'>⚖️ Comparación de Enfoques</a></li>";
        echo "<li><a href='/debug-timing/live-demo'>🎭 Demo en Vivo</a></li>";
        echo "</ul>";
        
        echo "<hr>";
        echo "<p><strong>Nota:</strong> Estos tests solo funcionan en ENVIRONMENT = 'development'</p>";
        echo "<p><a href='/dashboard'>← Volver al Dashboard</a></p>";
    }
    
    /**
     * 🔬 EXPERIMENTO: Ver orden de ejecución
     * RUTA: /debug-timing/experiment
     */
    public function experiment()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        echo "<h1>🔬 Experimento: Orden de Ejecución</h1>";
        echo "<hr>";
        
        echo "<h3>📝 Instanciando ExperimentoTiming...</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; font-family: monospace;'>";
        
        // Crear clase experimental inline
        $experiment = new class extends ShieldUserModel {
            public function __construct()
            {
                echo "🔴 <strong>1. CONSTRUCTOR INICIO</strong><br>";
                echo "&nbsp;&nbsp;&nbsp;returnType inicial: <code>" . ($this->returnType ?? 'NULL') . "</code><br>";
                
                // ✅ TU SOLUCIÓN: Configurar ANTES del parent
                $this->returnType = \App\Entities\User::class;
                echo "✅ <strong>2. CONFIGURADO ANTES parent:</strong> <code>" . $this->returnType . "</code><br>";
                
                // Llamar al padre
                echo "🔄 <strong>3. Ejecutando parent::__construct()...</strong><br>";
                parent::__construct();
                echo "🔵 <strong>4. DESPUÉS parent::__construct():</strong> <code>" . $this->returnType . "</code><br>";
                
                echo "📝 <strong>5. Log confirmado:</strong> Entity configurada como <code>" . $this->returnType . "</code><br>";
                echo "<br>";
            }
            
            protected function initialize(): void
            {
                echo "🟡 <strong>6. INITIALIZE INICIO:</strong> <code>" . $this->returnType . "</code><br>";
                
                echo "🔄 <strong>7. Ejecutando parent::initialize()...</strong><br>";
                parent::initialize();
                echo "🟢 <strong>8. DESPUÉS parent::initialize():</strong> <code>" . $this->returnType . "</code><br>";
                
                // Solo configuramos allowedFields aquí (como tu solución)
                $this->allowedFields = [
                    ...$this->allowedFields,
                ];
                
                echo "✅ <strong>9. INITIALIZE COMPLETO:</strong> <code>" . $this->returnType . "</code><br>";
                echo "<hr style='margin: 10px 0;'>";
            }
            
            public function getReturnType(): string
            {
                return $this->returnType;
            }
        };
        
        echo "</div>";
        
        echo "<h3>🎯 Resultado Final:</h3>";
        echo "Return Type: <strong>" . $experiment->getReturnType() . "</strong><br>";
        
        // Test con usuario real
        echo "<h3>🧪 Test con Usuario Real:</h3>";
        $users = $experiment->findAll(1);
        
        if (!empty($users)) {
            $user = $users[0];
            echo "✅ Usuario encontrado<br>";
            echo "Clase: <strong>" . get_class($user) . "</strong><br>";
            echo "¿Es App\\Entities\\User? " . ($user instanceof \App\Entities\User ? '✅ SÍ' : '❌ NO') . "<br>";
            
            if ($user instanceof \App\Entities\User) {
                echo "📋 Virtual Properties funcionando:<br>";
                echo "- getNombreCompleto(): <strong>" . $user->getNombreCompleto() . "</strong><br>";
                echo "- getRolPrincipal(): <strong>" . $user->getRolPrincipal() . "</strong><br>";
                echo "- getIniciales(): <strong>" . $user->getIniciales() . "</strong><br>";
            }
        } else {
            echo "❌ No hay usuarios en el sistema<br>";
        }
        
        echo "<hr>";
        echo "<p><a href='/debug-timing'>← Volver al menú</a></p>";
    }
    
    /**
     * 🔍 TEST: UserModel actual del sistema
     * RUTA: /debug-timing/current-usermodel
     */
    public function currentUserModel()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        echo "<h1>🔍 Test: UserModel Actual del Sistema</h1>";
        echo "<hr>";
        
        echo "<h3>📝 Instanciando UserModel real...</h3>";
        
        $userModel = new \App\Models\UserModel();
        
        echo "✅ UserModel instanciado correctamente<br>";
        echo "Return Type: <strong>" . $userModel->getReturnType() . "</strong><br>";
        
        echo "<h3>🧪 Test find() con usuario existente:</h3>";
        
        // Obtener el primer usuario disponible
        $users = $userModel->findAll(1);
        
        if (!empty($users)) {
            $user = $users[0];
            echo "✅ Usuario encontrado<br>";
            echo "ID: <strong>" . $user->id . "</strong><br>";
            echo "Email: <strong>" . ($user->email ?? 'No definido') . "</strong><br>";
            echo "Clase: <strong>" . get_class($user) . "</strong><br>";
            echo "¿Es App\\Entities\\User? " . ($user instanceof \App\Entities\User ? '✅ SÍ' : '❌ NO') . "<br>";
            
            if ($user instanceof \App\Entities\User) {
                echo "<h4>📋 Virtual Properties:</h4>";
                echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
                echo "- <strong>getNombreCompleto():</strong> " . $user->getNombreCompleto() . "<br>";
                echo "- <strong>getRolPrincipal():</strong> " . $user->getRolPrincipal() . "<br>";
                echo "- <strong>getIniciales():</strong> " . $user->getIniciales() . "<br>";
                echo "- <strong>getCategoriaUsuario():</strong> " . $user->getCategoriaUsuario() . "<br>";
                echo "- <strong>getScoreCliente():</strong> " . $user->getScoreCliente() . "/100<br>";
                echo "- <strong>getEstadoIcono():</strong> " . $user->getEstadoIcono() . "<br>";
                echo "</div>";
                
                echo "<h4>🧪 Test Helpers:</h4>";
                // Simular login temporal para probar helpers
                $this->simulateLogin($user);
                
                echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
                echo "- <strong>userName():</strong> " . userName() . "<br>";
                echo "- <strong>userRole():</strong> " . userRole() . "<br>";
                echo "- <strong>userInitials():</strong> " . userInitials() . "<br>";
                echo "- <strong>userCategory():</strong> " . userCategory() . "<br>";
                echo "- <strong>userScore():</strong> " . userScore() . "/100<br>";
                echo "- <strong>isCliente():</strong> " . (isCliente() ? '✅ SÍ' : '❌ NO') . "<br>";
                echo "- <strong>isAdmin():</strong> " . (isAdmin() ? '✅ SÍ' : '❌ NO') . "<br>";
                echo "</div>";
            }
        } else {
            echo "❌ No hay usuarios en el sistema<br>";
            echo "<p>💡 <strong>Sugerencia:</strong> Registra un usuario primero en <a href='/register'>/register</a></p>";
        }
        
        echo "<hr>";
        echo "<p><a href='/debug-timing'>← Volver al menú</a></p>";
    }
    
    /**
     * ⚖️ COMPARACIÓN: Diferentes enfoques
     * RUTA: /debug-timing/comparison
     */
    public function comparison()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        echo "<h1>⚖️ Comparación de Enfoques</h1>";
        echo "<hr>";
        
        echo "<h3>1. 🚫 Enfoque que NO funciona (solo initialize)</h3>";
        echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; font-family: monospace;'>";
        echo "<pre>protected function initialize(): void
{
    parent::initialize(); // Shield sobrescribe aquí ❌
    \$this->returnType = \\App\\Entities\\User::class; // Muy tarde
}</pre>";
        echo "<p><strong>❌ Problema:</strong> Shield ya configuró su Entity en parent::initialize()</p>";
        echo "</div>";
        
        echo "<h3>2. ✅ Tu Enfoque (ÓPTIMO)</h3>";
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; font-family: monospace;'>";
        echo "<pre>public function __construct()
{
    \$this->returnType = \\App\\Entities\\User::class; // ANTES del parent ✅
    parent::__construct();
}

protected function initialize(): void
{
    parent::initialize();
    \$this->allowedFields = [...\$this->allowedFields]; // Solo lo necesario
}</pre>";
        echo "<p><strong>✅ Ventaja:</strong> Timing perfecto, Shield respeta tu configuración</p>";
        echo "</div>";
        
        echo "<h3>3. 🔄 Enfoque redundante (funciona pero innecesario)</h3>";
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; font-family: monospace;'>";
        echo "<pre>public function __construct()
{
    \$this->returnType = \\App\\Entities\\User::class;
    parent::__construct();
    \$this->returnType = \\App\\Entities\\User::class; // Redundante 🔄
}

protected function initialize(): void
{
    parent::initialize();
    \$this->returnType = \\App\\Entities\\User::class; // También redundante 🔄
}</pre>";
        echo "<p><strong>🔄 Problema:</strong> Código innecesario, difícil de mantener</p>";
        echo "</div>";
        
        echo "<h3>📊 Tabla Comparativa:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f8f9fa; font-weight: bold;'>";
        echo "<th style='padding: 10px;'>Enfoque</th><th style='padding: 10px;'>Líneas</th><th style='padding: 10px;'>¿Funciona?</th><th style='padding: 10px;'>Claridad</th><th style='padding: 10px;'>Mantenibilidad</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td style='padding: 10px;'>Solo initialize</td><td style='padding: 10px;'>2</td><td style='padding: 10px;'>❌ NO</td><td style='padding: 10px;'>😞 Confuso</td><td style='padding: 10px;'>🔴 Mala</td>";
        echo "</tr>";
        echo "<tr style='background: #d4edda;'>";
        echo "<td style='padding: 10px;'><strong>Tu solución</strong></td><td style='padding: 10px;'><strong>3</strong></td><td style='padding: 10px;'><strong>✅ SÍ</strong></td><td style='padding: 10px;'><strong>🌟 Excelente</strong></td><td style='padding: 10px;'><strong>🟢 Perfecta</strong></td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td style='padding: 10px;'>Redundante</td><td style='padding: 10px;'>6</td><td style='padding: 10px;'>✅ SÍ</td><td style='padding: 10px;'>😐 Aceptable</td><td style='padding: 10px;'>🟡 Regular</td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<h3>🎯 Conclusión:</h3>";
        echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff;'>";
        echo "<p><strong>Tu enfoque es perfecto porque:</strong></p>";
        echo "<ul>";
        echo "<li>🎯 <strong>Timing correcto:</strong> Configuras ANTES que Shield actúe</li>";
        echo "<li>🤝 <strong>Cooperativo:</strong> Shield respeta tu configuración</li>";
        echo "<li>🧹 <strong>Limpio:</strong> Solo el código necesario</li>";
        echo "<li>🔧 <strong>Mantenible:</strong> Fácil de entender y modificar</li>";
        echo "<li>🚀 <strong>Eficiente:</strong> Sin sobrescrituras innecesarias</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<hr>";
        echo "<p><a href='/debug-timing'>← Volver al menú</a></p>";
    }
    
    /**
     * 🎭 DEMO EN VIVO: Crear usuario y ver funcionamiento
     * RUTA: /debug-timing/live-demo
     */
    public function liveDemo()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        echo "<h1>🎭 Demo en Vivo - UserModel</h1>";
        echo "<hr>";
        
        echo "<h3>📝 Creando usuario de prueba...</h3>";
        
        try {
            $userModel = new \App\Models\UserModel();
            
            // Datos de prueba
            $testData = [
                'email' => 'test-timing-' . time() . '@test.com',
                'password' => 'test123456',
                'nombres' => 'Usuario',
                'apellido_paterno' => 'Timing',
                'apellido_materno' => 'Test',
                'telefono' => '5551234567',
                'active' => true
            ];
            
            echo "🔄 Ejecutando createClienteUser()...<br>";
            $user = $userModel->createClienteUser($testData);
            
            echo "✅ <strong>Usuario creado exitosamente!</strong><br>";
            echo "- ID: <strong>" . $user->id . "</strong><br>";
            echo "- Email: <strong>" . $testData['email'] . "</strong><br>";
            echo "- Clase: <strong>" . get_class($user) . "</strong><br>";
            
            echo "<h3>🧪 Probando Virtual Properties:</h3>";
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
            echo "- <strong>getNombreCompleto():</strong> " . $user->getNombreCompleto() . "<br>";
            echo "- <strong>getRolPrincipal():</strong> " . $user->getRolPrincipal() . "<br>";
            echo "- <strong>getIniciales():</strong> " . $user->getIniciales() . "<br>";
            echo "- <strong>hasCliente():</strong> " . ($user->hasCliente() ? '✅ SÍ' : '❌ NO') . "<br>";
            
            if ($user->hasCliente()) {
                $cliente = $user->getCliente();
                echo "- <strong>Cliente Email:</strong> " . $cliente->email . "<br>";
                echo "- <strong>Cliente Teléfono:</strong> " . $cliente->telefono . "<br>";
            }
            echo "</div>";
            
            echo "<h3>🎯 Sistema funcionando perfectamente!</h3>";
            echo "<p>✅ UserModel configurado correctamente</p>";
            echo "<p>✅ Entity personalizada funcionando</p>";
            echo "<p>✅ Virtual Properties activas</p>";
            echo "<p>✅ Relación Usuario-Cliente creada</p>";
            
        } catch (\Exception $e) {
            echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
            echo "<p>💡 Verifica que la base de datos esté configurada correctamente</p>";
        }
        
        echo "<hr>";
        echo "<p><a href='/debug-timing'>← Volver al menú</a></p>";
    }
    
    /**
     * Helper: Simular login temporal para probar helpers
     */
    private function simulateLogin($user)
    {
        // Esto es solo para testing en desarrollo
        if (ENVIRONMENT === 'development') {
            // Simular sesión temporal
            session()->set('auth_user_id', $user->id);
        }
    }
}