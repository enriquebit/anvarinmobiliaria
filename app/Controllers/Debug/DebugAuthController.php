<?php
namespace App\Controllers\Debug;
use App\Controllers\BaseController;

class DebugAuthController extends BaseController
{
    /**
     * Menú principal de depuración 
     */
    public function index()
    {
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
            .container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .title { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            .menu { list-style: none; padding: 0; }
            .menu li { margin: 10px 0; }
            .menu a { display: inline-block; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            .menu a:hover { background: #0056b3; }
            .code { background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; font-family: monospace; }
            .success { color: #28a745; font-weight: bold; }
            .error { color: #dc3545; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
            .info { color: #17a2b8; font-weight: bold; }
        </style>";
        
        echo "<div class='container'>";
        echo "<h1 class='titulo'>🔍 DEBUG AUTH() - SISTEMA ANVAR</h1>";
        echo "<p>Herramientas para debuggear el sistema de autenticación de CodeIgniter Shield</p>";
        
        echo "<h2>📋 Menú de Tests:</h2>";
        echo "<ul class='menu'>";
        echo "<li><a href='" . site_url('debug-auth/tipos') . "'>1. 🔬 Tipos de Datos</a> - ¿Qué devuelve auth() vs auth()->user()?</li>";
        echo "<li><a href='" . site_url('debug-auth/usuario') . "'>2. 👤 Info del Usuario</a> - Toda la información del usuario actual</li>";
        echo "<li><a href='" . site_url('debug-auth/grupos') . "'>3. 👥 Grupos y Roles</a> - Métodos de grupos/roles</li>";
        echo "<li><a href='" . site_url('debug-auth/permisos') . "'>4. 🔐 Permisos</a> - Métodos de permisos granulares</li>";
        echo "<li><a href='" . site_url('debug-auth/metodos') . "'>5. ⚙️ Todos los Métodos</a> - Lista completa de métodos disponibles</li>";
        echo "<li><a href='" . site_url('debug-auth/helpers') . "'>6. 🛠️ Helpers Personalizados</a> - Tus funciones custom</li>";
        echo "<li><a href='" . site_url('debug-auth/ejemplos') . "'>7. 💡 Ejemplos Prácticos</a> - Casos de uso reales</li>";
        echo "<li><a href='" . site_url('debug-auth/completo') . "'>8. 🎯 Test Completo</a> - Todo en una página</li>";
        echo "</ul>";
        
        // Estado actual rápido
        echo "<h2>⚡ Estado Actual:</h2>";
        if (auth()->loggedIn()) {
            echo "<div class='success'>✅ Usuario LOGUEADO: " . auth()->user()->email . "</div>";
        } else {
            echo "<div class='error'>❌ Usuario NO logueado</div>";
            echo "<div class='info'>🔗 <a href='" . site_url('login') . "'>Ir al Login</a></div>";
        }
        
        echo "</div>";
    }
    /**
     * Tipos de Datos
     */
     public function tipos()
    {
        $this->headerDebug("🔬 TEST 1: TIPOS DE DATOS");
        
        echo "<h2>Paso 1: Obtener el servicio de autenticación</h2>";
        echo "<div class='code'>\$authService = auth();</div>";
        
        $authService = auth();
        echo '<pre>'.dd($authService).'</pre>';
        echo "<strong>Resultado:</strong><br>";
        echo "Tipo: <span class='info'>" . get_class($authService) . "</span><br>";
        echo "Es objeto: <span class='success'>" . (is_object($authService) ? 'SÍ' : 'NO') . "</span><br>";
        
        echo "<hr>";
        
        echo "<h2>Paso 2: Verificar si hay usuario logueado</h2>";
        echo "<div class='code'>\$isLoggedIn = \$authService->loggedIn();</div>";
        
        $isLoggedIn = $authService->loggedIn();
        echo "<strong>Resultado:</strong><br>";
        echo "¿Logueado?: <span class='" . ($isLoggedIn ? 'success' : 'error') . "'>" . ($isLoggedIn ? 'SÍ' : 'NO') . "</span><br>";
        echo "Tipo: <span class='info'>" . gettype($isLoggedIn) . "</span><br>";
        
        if (!$isLoggedIn) {
            echo "<div class='warning'>⚠️ Debes estar logueado para ver los siguientes tests</div>";
            echo "<a href='" . site_url('login') . "' class='menu'>Ir al Login</a>";
            return;
        }
        
        echo "<hr>";
        
        echo "<h2>Paso 3: Obtener la entidad del usuario</h2>";
        echo "<div class='code'>\$user = \$authService->user();</div>";
        
        $user = $authService->user();
        echo "<strong>Resultado:</strong><br>";
        echo "Tipo: <span class='info'>" . get_class($user) . "</span><br>";
        echo "Es Entity: <span class='success'>" . (is_a($user, 'CodeIgniter\\Entity\\Entity') ? 'SÍ' : 'NO') . "</span><br>";
        echo "Es User Entity: <span class='success'>" . (is_a($user, 'CodeIgniter\\Shield\\Entities\\User') ? 'SÍ' : 'NO') . "</span><br>";
        
        echo "<hr>";
        
        echo "<h2>Paso 4: Acceder a propiedades y métodos</h2>";
        echo "<div class='code'>echo \$user->email;<br>echo \$user->inGroup('admin');</div>";
        
        echo "<strong>Propiedades:</strong><br>";
        echo "Email: <span class='info'>" . esc($user->email) . "</span><br>";
        echo "ID: <span class='info'>" . $user->id . "</span><br>";
        echo "Activo: <span class='info'>" . ($user->active ? 'SÍ' : 'NO') . "</span><br>";
        
        echo "<strong>Métodos:</strong><br>";
        echo "inGroup('admin'): <span class='info'>" . ($user->inGroup('admin') ? 'SÍ' : 'NO') . "</span><br>";
        echo "inGroup('cliente'): <span class='info'>" . ($user->inGroup('cliente') ? 'SÍ' : 'NO') . "</span><br>";
        
        $this->footerDebug();
    }
    
    /**
     * Helpers para el debug
     * 
     */
      /**
     * 🎨 HELPERS PARA EL DEBUG
     */
    private function headerDebug(string $title)
    {
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
            .container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .title { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            .code { background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; font-family: monospace; }
            .success { color: #28a745; font-weight: bold; }
            .error { color: #dc3545; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
            .info { color: #17a2b8; font-weight: bold; }
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>";
        
        echo "<div class='container'>";
        echo "<h1 class='titulo'>$title</h1>";
    }
    
    private function footerDebug()
    {
        echo "<hr>";
        echo "<p><a href='" . site_url('debug-auth') . "'>← Volver al menú principal</a></p>";
        echo "</div>";
    }
    
    private function formatValue($value)
    {
        if (is_bool($value)) {
            return $value ? '<span class="success">true</span>' : '<span class="error">false</span>';
        }
        
        if (is_null($value)) {
            return '<span class="warning">null</span>';
        }
        
        if (is_object($value)) {
            if (method_exists($value, 'format')) {
                return $value->format('Y-m-d H:i:s');
            }
            return '<em>' . get_class($value) . '</em>';
        }
        
        if (is_array($value)) {
            return '[' . implode(', ', $value) . ']';
        }
        
        return esc($value);
    }
}